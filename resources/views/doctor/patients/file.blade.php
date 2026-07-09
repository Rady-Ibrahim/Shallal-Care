@extends('doctor.layout')

@section('title', 'ملف المريض')
@section('page-title', 'ملف المريض')
@section('page-description', 'بطاقة الملف والـ QR والسجل الزمني')

@section('content')
<div class="mb-4 flex flex-wrap gap-3">
  <a href="/doctor/dashboard/patients" class="text-blue-600 hover:text-blue-700 text-sm flex items-center gap-1">
    <i class="fas fa-arrow-right"></i> قائمة المرضى
  </a>
  <a href="/doctor/dashboard/patients/{{ $patientId }}" class="text-blue-600 hover:text-blue-700 text-sm flex items-center gap-1">
    <i class="fas fa-user"></i> تفاصيل المريض
  </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="bg-white rounded-xl border border-slate-200 p-6 text-center">
    <div id="qrBox" class="mb-4 flex justify-center">
      <p class="text-slate-400">جاري التحميل...</p>
    </div>
    <p class="font-mono text-blue-700 font-bold text-xl" id="fileNumber">—</p>
    <h2 class="text-xl font-bold text-slate-800 mt-2" id="patientName">—</h2>
    <p class="text-slate-500" id="patientPhone">—</p>
    <div class="mt-4 text-right text-sm space-y-2 border-t pt-4">
      <p><span class="font-semibold">الحساسية:</span> <span id="allergies">—</span></p>
      <p><span class="font-semibold">أمراض مزمنة:</span> <span id="chronic">—</span></p>
    </div>
    <button type="button" onclick="window.print()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm print:hidden">
      <i class="fas fa-print ml-1"></i> طباعة البطاقة
    </button>
  </div>

  <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6">
    <h2 class="font-bold text-slate-800 mb-4">السجل الزمني</h2>
    <div id="timeline" class="space-y-3">
      <p class="text-slate-500 text-center py-8">جاري التحميل...</p>
    </div>
  </div>
</div>

<style>
@media print {
  aside, .toolbar, nav, button, .print\\:hidden { display: none !important; }
  body { background: white; }
}
</style>
@endsection

@section('scripts')
<script>
const patientId = {{ $patientId }};

const typeIcons = {
  visit: 'fa-stethoscope text-blue-600',
  prescription: 'fa-prescription text-teal-600',
  record: 'fa-file-medical text-slate-600',
};

async function loadFile() {
  const res = await apiCall(`/doctor/api/patients/${patientId}/file`);
  if (!res?.success) {
    document.getElementById('timeline').innerHTML = '<p class="text-red-600 text-center">تعذر تحميل الملف</p>';
    return;
  }
  const d = res.data;
  document.getElementById('fileNumber').textContent = d.file_number || 'بدون رقم ملف';
  document.getElementById('patientName').textContent = d.name;
  document.getElementById('patientPhone').textContent = d.phone || '—';
  document.getElementById('allergies').textContent = d.allergies || 'لا يوجد';
  document.getElementById('chronic').textContent = d.chronic_conditions || 'لا يوجد';

  if (d.qr_url) {
    const qrImg = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(d.qr_url)}`;
    document.getElementById('qrBox').innerHTML = `<img src="${qrImg}" alt="QR" class="rounded-lg border">`;
  }

  const timeline = d.timeline || [];
  const box = document.getElementById('timeline');
  if (!timeline.length) {
    box.innerHTML = '<p class="text-slate-400 text-center py-6">لا توجد زيارات أو سجلات بعد</p>';
    return;
  }

  box.innerHTML = timeline.map(item => `
    <div class="flex gap-3 border-r-2 border-blue-200 pr-4 pb-3">
      <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
        <i class="fas ${typeIcons[item.type] || 'fa-circle text-slate-400'}"></i>
      </div>
      <div class="flex-1">
        <p class="font-semibold text-slate-800">${item.title}</p>
        <p class="text-xs text-slate-500">${item.created_at || item.date}</p>
        ${item.diagnosis ? `<p class="text-sm text-slate-600 mt-1">${item.diagnosis}</p>` : ''}
        ${item.type === 'visit' && item.status === 'with_doctor' ? `
          <a href="/doctor/dashboard/visits/${item.id}" class="text-xs text-blue-600 mt-1 inline-block">متابعة الكشف</a>
        ` : ''}
        ${item.type === 'prescription' ? `
          <a href="/doctor/dashboard/prescriptions/${item.id}" class="text-xs text-teal-600 mt-1 inline-block">عرض الروشتة</a>
        ` : ''}
      </div>
    </div>
  `).join('');
}

window.addEventListener('load', loadFile);
</script>
@endsection
