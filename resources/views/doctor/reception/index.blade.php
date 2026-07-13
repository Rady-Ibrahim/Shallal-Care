@extends('doctor.layout')

@section('title', 'الاستقبال')
@section('page-title', 'مكتب الاستقبال')
@section('page-description', 'حجز المرضى وتسجيل الحضور — ابحث برقم الحجز أو اسم المريض')

@section('content')
<div id="pageAlert" class="hidden mb-6 p-4 rounded-xl border text-sm font-semibold" role="alert"></div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6" id="statsCards">
    @foreach(['total'=>'إجمالي اليوم','scheduled'=>'محجوز','waiting'=>'في الانتظار','with_doctor'=>'عند الطبيب','completed'=>'تم','pending_payment'=>'بانتظار الدفع'] as $key => $label)
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-xs text-slate-500">{{ $label }}</p>
        <p class="text-2xl font-bold text-slate-800" id="stat_{{ $key }}">—</p>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
    <div class="flex flex-wrap gap-3 items-end justify-between mb-4">
        <div class="flex-1 min-w-[280px]">
            <label class="block text-sm font-semibold text-slate-700 mb-1">بحث سريع</label>
            <input type="text" id="searchQ" placeholder="رقم الحجز أو اسم المريض..."
                class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <p class="text-xs text-slate-400 mt-1">مثال: 1 أو أحمد محمد</p>
        </div>
        <div class="flex gap-2">
            <button type="button" id="toggleAdvanced" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm">بحث متقدم</button>
            <button type="button" id="openBookingModal" class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                + حجز جديد
            </button>
        </div>
    </div>

    <div id="advancedSearch" class="hidden mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
        <label class="block text-sm font-semibold text-slate-700 mb-1">رقم الهاتف</label>
        <input type="text" id="searchPhone" placeholder="01xxxxxxxxx" class="w-full max-w-sm border rounded-lg px-3 py-2">
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        @foreach(['all'=>'الكل','scheduled'=>'محجوز','waiting'=>'في الدور','with_doctor'=>'عند الطبيب','completed'=>'تم'] as $val => $lbl)
        <button type="button" data-status="{{ $val }}" class="status-filter px-3 py-1.5 rounded-full text-sm border border-slate-200 hover:bg-blue-50">{{ $lbl }}</button>
        @endforeach
    </div>

    <div id="bookingsList" class="space-y-3">
        <p class="text-center text-slate-500 py-10">جاري التحميل...</p>
    </div>
</div>

<div id="bookingModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-800 mb-1">حجز مريض جديد</h3>
        <p class="text-sm text-slate-500 mb-4">سيُنشأ رقم حجز تلقائياً بعد الحفظ</p>
        <form id="bookingForm" class="space-y-3">
            <div>
                <label class="block text-sm font-semibold mb-1">رقم الهاتف *</label>
                <input name="phone" id="bookingPhone" required class="w-full border rounded-lg px-3 py-2" placeholder="01xxxxxxxxx">
            </div>
            <div id="patientLookupCard" class="hidden p-4 rounded-xl border border-blue-200 bg-blue-50 text-sm space-y-1">
                <p class="font-bold text-blue-900" id="lookupName">—</p>
                <p class="text-blue-700" id="lookupMeta">—</p>
                <p class="text-blue-600 text-xs" id="lookupDiagnosis">—</p>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">اسم المريض *</label>
                <input name="name" id="bookingName" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">الجنس</label>
                <select name="gender" class="w-full border rounded-lg px-3 py-2">
                    <option value="">—</option>
                    <option value="male">ذكر</option>
                    <option value="female">أنثى</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">رسوم الكشف ({{ config('clinic.currency_symbol') }})</label>
                <input name="consultation_fee" type="number" min="0" step="0.01" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">ملاحظات</label>
                <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
            </div>
            <div class="flex gap-2 justify-end pt-2">
                <button type="button" id="closeBookingModal" class="px-4 py-2 rounded-lg bg-slate-100">إلغاء</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold">حفظ وإصدار رقم حجز</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentStatus = 'all';
let searchTimer = null;
let pageAlertTimer = null;

function showPageAlert(message, type = 'success') {
    const alert = document.getElementById('pageAlert');
    if (!alert) return;

    alert.textContent = message;
    alert.className = type === 'success'
        ? 'mb-6 p-4 rounded-xl border border-green-200 bg-green-50 text-green-800 text-sm font-semibold'
        : 'mb-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-800 text-sm font-semibold';
    alert.classList.remove('hidden');
    alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    clearTimeout(pageAlertTimer);
    pageAlertTimer = setTimeout(() => alert.classList.add('hidden'), 10000);
}

function hidePageAlert() {
    document.getElementById('pageAlert')?.classList.add('hidden');
}

const statusColors = {
    scheduled: 'bg-blue-100 text-blue-800',
    checked_in: 'bg-cyan-100 text-cyan-800',
    waiting: 'bg-amber-100 text-amber-800',
    with_doctor: 'bg-purple-100 text-purple-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-slate-100 text-slate-600',
    no_show: 'bg-red-100 text-red-800',
};

async function loadStats() {
    const res = await apiCall('/doctor/api/reception/stats');
    if (!res?.success) return;
    Object.entries(res.data).forEach(([k, v]) => {
        const el = document.getElementById('stat_' + k);
        if (el) el.textContent = v;
    });
}

function buildQuery() {
    const params = new URLSearchParams();
    const q = document.getElementById('searchQ').value.trim();
    const phone = document.getElementById('searchPhone').value.trim();
    if (q) params.set('q', q);
    if (phone) params.set('phone', phone);
    if (currentStatus !== 'all') params.set('status', currentStatus);
    return params.toString();
}

async function loadBookings() {
    const container = document.getElementById('bookingsList');
    try {
        const res = await apiCall('/doctor/api/reception/bookings?' + buildQuery());
        const items = res?.data || [];
        if (!items.length) {
            container.innerHTML = '<p class="text-center text-slate-500 py-10">لا توجد حجوزات مطابقة</p>';
            return;
        }
        container.innerHTML = items.map(b => `
            <div class="border border-slate-200 rounded-xl p-4 hover:border-blue-200 transition">
                <div class="flex flex-wrap justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono font-bold text-blue-700 text-lg">${b.booking_number}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full ${statusColors[b.status] || 'bg-slate-100'}">${b.status_label}</span>
                            ${b.file_number ? `<span class="text-xs text-slate-500">ملف: ${b.file_number}</span>` : ''}
                        </div>
                        <p class="font-semibold text-slate-800 mt-1">${b.patient_name}</p>
                        <p class="text-sm text-slate-500">${b.consultation_fee} ${'{{ config('clinic.currency_symbol') }}'} — دفع: ${b.payment_status === 'paid' ? 'مدفوع' : 'معلق'}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 items-center">
                        ${b.status === 'scheduled' ? `<button onclick="checkIn(${b.id})" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm">تسجيل حضور</button>` : ''}
                        ${b.payment_status !== 'paid' && b.status !== 'cancelled' ? `<button onclick="collect(${b.id})" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm">تحصيل كاش</button>` : ''}
                        ${b.status === 'with_doctor' ? `<a href="/doctor/dashboard/visits/${b.id}" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm">متابعة الكشف</a>` : ''}
                        ${b.status === 'waiting' ? `<button onclick="setStatus(${b.id}, 'with_doctor')" class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm">دخل للطبيب</button>` : ''}
                        ${b.status === 'with_doctor' ? `<button onclick="setStatus(${b.id}, 'completed')" class="px-3 py-1.5 bg-slate-700 text-white rounded-lg text-sm">إنهاء</button>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    } catch (e) {
        container.innerHTML = '<p class="text-center text-red-600 py-10">تعذر تحميل الحجوزات</p>';
    }
}

async function checkIn(id) {
    await apiCall(`/doctor/api/reception/bookings/${id}/check-in`, { method: 'POST' });
    loadStats(); loadBookings();
}

async function collect(id) {
    await apiCall(`/doctor/api/reception/bookings/${id}/collect`, {
        method: 'POST',
        body: JSON.stringify({ payment_method: 'cash' }),
    });
    loadStats(); loadBookings();
}

async function setStatus(id, status) {
    await apiCall(`/doctor/api/reception/bookings/${id}/status`, {
        method: 'PATCH',
        body: JSON.stringify({ status }),
    });
    loadStats(); loadBookings();
}

document.getElementById('toggleAdvanced').addEventListener('click', () => {
    document.getElementById('advancedSearch').classList.toggle('hidden');
});

document.querySelectorAll('.status-filter').forEach(btn => {
    btn.addEventListener('click', () => {
        currentStatus = btn.dataset.status;
        document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('bg-blue-100', 'border-blue-300'));
        btn.classList.add('bg-blue-100', 'border-blue-300');
        loadBookings();
    });
});

['searchQ', 'searchPhone'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadBookings, 350);
    });
});

let lookupTimer = null;

async function lookupPatientByPhone(phone) {
    const card = document.getElementById('patientLookupCard');
    if (!phone || phone.length < 8) {
        card?.classList.add('hidden');
        return;
    }
    const res = await apiCall('/doctor/api/reception/patient-lookup?phone=' + encodeURIComponent(phone));
    if (!res?.success || !res.data?.found) {
        card?.classList.add('hidden');
        return;
    }
    const p = res.data.patient;
    document.getElementById('lookupName').textContent = p.name + (p.file_number ? ` — ملف ${p.file_number}` : '');
    document.getElementById('lookupMeta').textContent = [
        p.visits_count ? `${p.visits_count} زيارة` : null,
        p.last_visit ? `آخر زيارة: ${p.last_visit}` : null,
        p.allergies ? `حساسية: ${p.allergies}` : null,
    ].filter(Boolean).join(' · ');
    document.getElementById('lookupDiagnosis').textContent = p.last_diagnosis ? `آخر تشخيص: ${p.last_diagnosis}` : '';
    if (p.name && !document.getElementById('bookingName').value) {
        document.getElementById('bookingName').value = p.name;
    }
    if (p.gender) {
        const genderSelect = document.querySelector('#bookingForm [name="gender"]');
        if (genderSelect && !genderSelect.value) genderSelect.value = p.gender;
    }
    card?.classList.remove('hidden');
}

document.getElementById('bookingPhone')?.addEventListener('input', (e) => {
    clearTimeout(lookupTimer);
    lookupTimer = setTimeout(() => lookupPatientByPhone(e.target.value.trim()), 400);
});

document.getElementById('openBookingModal').addEventListener('click', () => {
    document.getElementById('bookingForm').reset();
    document.getElementById('patientLookupCard')?.classList.add('hidden');
    document.getElementById('bookingModal').classList.remove('hidden');
});

document.getElementById('closeBookingModal').addEventListener('click', () => {
    document.getElementById('bookingModal').classList.add('hidden');
});

document.getElementById('bookingForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = new FormData(e.target);
    const payload = Object.fromEntries(form.entries());
    const res = await apiCall('/doctor/api/reception/bookings', {
        method: 'POST',
        body: JSON.stringify(payload),
    });
    if (res?.success) {
        document.getElementById('bookingModal').classList.add('hidden');
        e.target.reset();
        showPageAlert(`تم الحجز بنجاح — رقم الحجز: ${res.data.booking_number}`);
        loadStats();
        loadBookings();
    }
});

window.addEventListener('load', () => {
    loadStats();
    loadBookings();
    document.querySelector('.status-filter[data-status="all"]')?.classList.add('bg-blue-100', 'border-blue-300');
});
</script>
@endsection
