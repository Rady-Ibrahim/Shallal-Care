@extends('doctor.layout')

@section('title', 'كشف المريض')
@section('page-title', 'كشف المريض')
@section('page-description', 'تسجيل العلامات الحيوية والتشخيص')

@section('content')
<div class="mb-4">
  <a href="/doctor/dashboard/queue" class="text-blue-600 hover:text-blue-700 text-sm flex items-center gap-1">
    <i class="fas fa-arrow-right"></i> العودة للدور
  </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 mb-6" id="patientHeader">
  <p class="text-slate-500 text-center py-6">جاري التحميل...</p>
</div>

<form id="visitForm" class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
    <h2 class="font-bold text-slate-800 mb-2">العلامات الحيوية</h2>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-semibold mb-1">الوزن (كجم)</label>
        <input name="weight" id="weight" class="w-full border rounded-lg px-3 py-2" @unless($canManageVisit) disabled @endunless>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">الطول (سم)</label>
        <input name="height" id="height" class="w-full border rounded-lg px-3 py-2" @unless($canManageVisit) disabled @endunless>
      </div>
      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">ضغط الدم</label>
        <input name="blood_pressure" id="blood_pressure" placeholder="120/80" class="w-full border rounded-lg px-3 py-2" @unless($canManageVisit) disabled @endunless>
      </div>
    </div>
    <div>
      <label class="block text-sm font-semibold mb-1">الحساسية</label>
      <textarea name="allergies" id="allergies" rows="2" class="w-full border rounded-lg px-3 py-2" @unless($canManageVisit) disabled @endunless></textarea>
    </div>
    <div>
      <label class="block text-sm font-semibold mb-1">الأمراض المزمنة</label>
      <textarea name="chronic_conditions" id="chronic_conditions" rows="2" class="w-full border rounded-lg px-3 py-2" @unless($canManageVisit) disabled @endunless></textarea>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
    <h2 class="font-bold text-slate-800 mb-2">التشخيص والملاحظات</h2>
    <div>
      <label class="block text-sm font-semibold mb-1">التشخيص</label>
      <textarea name="diagnosis" id="diagnosis" rows="4" class="w-full border rounded-lg px-3 py-2" @unless($canManageVisit) disabled @endunless></textarea>
    </div>
    <div>
      <label class="block text-sm font-semibold mb-1">ملاحظات</label>
      <textarea name="notes" id="notes" rows="4" class="w-full border rounded-lg px-3 py-2" @unless($canManageVisit) disabled @endunless></textarea>
    </div>

    @if($canManageVisit)
    <div class="flex flex-wrap gap-2 pt-2">
      <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg font-semibold">حفظ الكشف</button>
      <a id="btnOrderLab" href="#" class="px-4 py-2.5 bg-amber-600 text-white rounded-lg font-semibold">طلب تحليل/أشعة</a>
      <button type="button" id="btnPrescription" class="px-4 py-2.5 bg-teal-600 text-white rounded-lg font-semibold">كتابة روشتة</button>
      <button type="button" id="btnComplete" class="px-4 py-2.5 bg-slate-700 text-white rounded-lg font-semibold">إنهاء الزيارة</button>
    </div>
    @endif
  </div>
</form>
@endsection

@section('scripts')
<script>
const bookingId = {{ $bookingId }};
let patientId = null;
let bookingData = null;

function renderHeader(booking) {
  bookingData = booking;
  patientId = booking.patient_id;
  document.getElementById('patientHeader').innerHTML = `
    <div class="flex flex-wrap justify-between gap-4">
      <div>
        <p class="font-mono text-blue-700 font-bold text-lg">${booking.booking_number}</p>
        <h2 class="text-2xl font-bold text-slate-800">${booking.patient_name}</h2>
        <p class="text-slate-500">${booking.patient_phone || ''} · ملف ${booking.file_number || '—'}</p>
      </div>
      <div class="text-left">
        <span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-800">${booking.status}</span>
        <p class="text-sm text-slate-500 mt-2">${booking.visit_date}</p>
      </div>
    </div>
  `;
}

function fillVisit(visit) {
  if (!visit) return;
  ['weight','height','blood_pressure','allergies','diagnosis','notes'].forEach(f => {
    const el = document.getElementById(f);
    if (el && visit[f]) el.value = visit[f];
  });
}

async function loadVisit() {
  let res = await apiCall(`/doctor/api/visits/${bookingId}`);
  if (!res?.success) return;

  if (!res.data.visit) {
    @if($canManageVisit)
    res = await apiCall(`/doctor/api/visits/${bookingId}/start`, { method: 'POST' });
    @endif
  }

  if (res?.success) {
    renderHeader(res.data.booking);
    fillVisit(res.data.visit);
    document.getElementById('btnOrderLab')?.setAttribute('href', `/doctor/dashboard/orders?patient_id=${res.data.booking.patient_id}&booking_id=${bookingId}`);
    if (res.data.booking?.patient_id) {
      const fileRes = await apiCall(`/doctor/api/patients/${res.data.booking.patient_id}/file`);
      if (fileRes?.success) {
        if (fileRes.data.allergies && !document.getElementById('allergies').value) {
          document.getElementById('allergies').value = fileRes.data.allergies;
        }
        if (fileRes.data.chronic_conditions && !document.getElementById('chronic_conditions').value) {
          document.getElementById('chronic_conditions').value = fileRes.data.chronic_conditions;
        }
      }
    }
  }
}

document.getElementById('visitForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target).entries());
  const res = await apiCall(`/doctor/api/visits/${bookingId}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  });
  if (res?.success) {
    showSuccess('تم حفظ بيانات الكشف');
    fillVisit(res.data.visit);
  }
});

document.getElementById('btnPrescription')?.addEventListener('click', async () => {
  if (patientId) {
    window.location.href = `/doctor/dashboard/prescriptions/create?patient_id=${patientId}&booking_id=${bookingId}`;
  }
});

document.getElementById('btnComplete')?.addEventListener('click', async () => {
  if (!await confirmAction('إنهاء الزيارة؟ سيتم إغلاق الكشف وإخراج المريض من الدور')) return;
  const res = await apiCall(`/doctor/api/visits/${bookingId}/complete`, { method: 'POST' });
  if (res?.success) {
    showSuccess('تم إنهاء الزيارة');
    setTimeout(() => window.location.href = '/doctor/dashboard/queue', 800);
  }
});

window.addEventListener('load', loadVisit);
</script>
@endsection
