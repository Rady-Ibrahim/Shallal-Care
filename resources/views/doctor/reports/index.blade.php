@extends('doctor.layout')

@section('title', 'تقارير العيادة')
@section('page-title', 'تقارير وتحليلات')
@section('page-description', 'إحصائيات الزيارات والإيرادات والتشخيصات')

@section('content')
<div class="flex flex-wrap gap-3 mb-6">
  @foreach(['today'=>'اليوم','week'=>'هذا الأسبوع','month'=>'هذا الشهر'] as $val => $lbl)
  <button type="button" data-period="{{ $val }}" class="period-btn px-4 py-2 rounded-lg border font-semibold">{{ $lbl }}</button>
  @endforeach
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-xl border p-5">
    <p class="text-xs text-slate-500">الزيارات</p>
    <p class="text-2xl font-bold text-slate-800" id="rVisits">—</p>
    <p class="text-xs text-green-600" id="rCompleted">—</p>
  </div>
  <div class="bg-white rounded-xl border p-5">
    <p class="text-xs text-slate-500">إيرادات</p>
    <p class="text-2xl font-bold text-green-600" id="rRevenue">—</p>
    <p class="text-xs text-slate-500" id="rNet">—</p>
  </div>
  <div class="bg-white rounded-xl border p-5">
    <p class="text-xs text-slate-500">مرضى جدد</p>
    <p class="text-2xl font-bold text-blue-700" id="rPatients">—</p>
    <p class="text-xs text-slate-500" id="rPrescriptions">—</p>
  </div>
  <div class="bg-white rounded-xl border p-5">
    <p class="text-xs text-slate-500">طلبات معلقة</p>
    <p class="text-sm text-amber-700">تحاليل: <strong id="rLab">—</strong></p>
    <p class="text-sm text-purple-700">أشعة: <strong id="rRad">—</strong></p>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-xl border p-6">
    <h2 class="font-bold text-slate-800 mb-4">الزيارات اليومية</h2>
    <div id="visitsChart" class="flex items-end gap-1 h-40 border-b border-slate-200 pb-2">
      <p class="text-slate-400 text-sm w-full text-center self-center">جاري التحميل...</p>
    </div>
  </div>
  <div class="bg-white rounded-xl border p-6">
    <h2 class="font-bold text-slate-800 mb-4">أكثر التشخيصات</h2>
    <div id="topDiagnoses" class="space-y-2 text-sm">
      <p class="text-slate-400">—</p>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const currency = @json(config('clinic.currency_symbol'));
let currentPeriod = 'month';

function fmt(n) {
  return Number(n || 0).toLocaleString('ar-EG', { maximumFractionDigits: 0 }) + ' ' + currency;
}

async function loadReport() {
  const res = await apiCall('/doctor/api/reports/overview?period=' + currentPeriod);
  if (!res?.success) return;
  const d = res.data;

  document.getElementById('rVisits').textContent = d.visits_total;
  document.getElementById('rCompleted').textContent = `مكتمل: ${d.visits_completed} (${d.completion_rate}%)`;
  document.getElementById('rRevenue').textContent = fmt(d.revenue);
  document.getElementById('rNet').textContent = `صافي: ${fmt(d.net)}`;
  document.getElementById('rPatients').textContent = d.new_patients;
  document.getElementById('rPrescriptions').textContent = `روشتات: ${d.prescriptions}`;
  document.getElementById('rLab').textContent = d.pending_lab;
  document.getElementById('rRad').textContent = d.pending_radiology;

  const chart = document.getElementById('visitsChart');
  const days = d.daily_visits || [];
  if (!days.length) {
    chart.innerHTML = '<p class="text-slate-400 text-sm w-full text-center">لا بيانات</p>';
  } else {
    const max = Math.max(...days.map(x => x.total), 1);
    chart.innerHTML = days.map(day => `
      <div class="flex-1 flex flex-col items-center gap-1 min-w-0">
        <span class="text-[10px] text-slate-600 font-semibold">${day.total}</span>
        <div class="w-full bg-blue-500 rounded-t" style="height:${Math.max(4, (day.total / max) * 120)}px"></div>
        <span class="text-[9px] text-slate-400 truncate w-full text-center">${day.label}</span>
      </div>
    `).join('');
  }

  const diagBox = document.getElementById('topDiagnoses');
  if (!d.top_diagnoses?.length) {
    diagBox.innerHTML = '<p class="text-slate-400">لا توجد تشخيصات مسجّلة</p>';
  } else {
    diagBox.innerHTML = d.top_diagnoses.map((item, i) => `
      <div class="flex justify-between border-b pb-2">
        <span class="text-slate-700">${i + 1}. ${item.diagnosis}</span>
        <span class="font-bold text-blue-700">${item.count}</span>
      </div>
    `).join('');
  }
}

document.querySelectorAll('.period-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    currentPeriod = btn.dataset.period;
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('bg-blue-100', 'border-blue-400'));
    btn.classList.add('bg-blue-100', 'border-blue-400');
    loadReport();
  });
});

window.addEventListener('load', () => {
  document.querySelector('.period-btn[data-period="month"]')?.classList.add('bg-blue-100', 'border-blue-400');
  loadReport();
});
</script>
@endsection
