@extends('doctor.layout')

@section('title', 'يوم العيادة')
@section('page-title', 'يوم العيادة')
@section('page-description', 'شاشة تشغيل موحّدة — استقبال، دور، وخزنة اليوم')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6" id="dayStats">
    @foreach(['total'=>'إجمالي','scheduled'=>'محجوز','waiting'=>'في الانتظار','with_doctor'=>'عند الطبيب','completed'=>'تم','pending_payment'=>'بانتظار الدفع'] as $k => $lbl)
    <div class="bg-white rounded-xl border p-4">
        <p class="text-xs text-slate-500">{{ $lbl }}</p>
        <p class="text-2xl font-bold" id="stat_{{ $k }}">—</p>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-slate-800">حجوزات اليوم</h2>
                <a href="/doctor/dashboard/reception" class="text-blue-600 text-sm">الاستقبال الكامل</a>
            </div>
            <div id="dayBookings" class="space-y-2 max-h-96 overflow-y-auto">
                <p class="text-slate-500 text-center py-6">جاري التحميل...</p>
            </div>
        </div>
        @if($canViewFinance)
        <div class="bg-white rounded-xl border p-6">
            <h2 class="font-bold text-slate-800 mb-4">خزنة اليوم</h2>
            <div class="grid grid-cols-3 gap-4" id="dayFinance">
                <div class="p-3 bg-green-50 rounded-lg"><p class="text-xs text-green-700">إيراد</p><p class="text-xl font-bold text-green-800" id="finIncome">—</p></div>
                <div class="p-3 bg-red-50 rounded-lg"><p class="text-xs text-red-700">مصروف</p><p class="text-xl font-bold text-red-800" id="finExpense">—</p></div>
                <div class="p-3 bg-blue-50 rounded-lg"><p class="text-xs text-blue-700">صافي</p><p class="text-xl font-bold text-blue-800" id="finNet">—</p></div>
            </div>
            <a href="/doctor/dashboard/finance/print" target="_blank" class="inline-block mt-4 text-sm text-blue-600">طباعة تقرير اليوم</a>
        </div>
        @endif
    </div>
    <div class="space-y-6">
        <div class="bg-white rounded-xl border p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-slate-800">الدور الآن</h2>
                <a href="/doctor/dashboard/waiting-screen" target="_blank" class="text-xs text-blue-600">شاشة الانتظار</a>
            </div>
            <div id="dayQueue" class="space-y-2">
                <p class="text-slate-500 text-center py-4">جاري التحميل...</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border p-6">
            <h2 class="font-bold text-slate-800 mb-3">إجراءات سريعة</h2>
            <div class="space-y-2">
                @if($canManageReception)
                <a href="/doctor/dashboard/reception" class="block w-full text-center px-4 py-2.5 bg-blue-600 text-white rounded-lg">+ حجز مريض</a>
                @endif
                <a href="/doctor/dashboard/queue" class="block w-full text-center px-4 py-2.5 bg-amber-600 text-white rounded-lg">إدارة الدور</a>
                @if($canViewFinance)
                <a href="/doctor/dashboard/finance" class="block w-full text-center px-4 py-2.5 bg-green-600 text-white rounded-lg">الخزنة</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const sym = @json($currencySymbol);
const canViewFinance = @json($canViewFinance);
const canViewRecords = @json($canViewRecords);
let refreshTimer;

function formatMoney(amount) {
    return new Intl.NumberFormat('ar-EG', { style: 'decimal', minimumFractionDigits: 0 }).format(amount || 0) + ' ' + sym;
}

async function loadDay() {
    const res = await apiCall('/doctor/api/clinic-day/overview');
    if (!res?.success) return;
    const d = res.data;
    Object.entries(d.stats || {}).forEach(([k,v]) => {
        const el = document.getElementById('stat_' + k);
        if (el) el.textContent = v;
    });
    if (canViewFinance) {
        const fin = d.finance || {};
        document.getElementById('finIncome').textContent = formatMoney(fin.income);
        document.getElementById('finExpense').textContent = formatMoney(fin.expense);
        document.getElementById('finNet').textContent = formatMoney(fin.net);
    }

    const bookings = d.bookings || [];
    document.getElementById('dayBookings').innerHTML = bookings.length ? bookings.map(b => `
        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg text-sm">
            <div><span class="font-mono font-bold text-blue-700">#${b.booking_number}</span> ${b.patient_name}</div>
            <span class="text-xs">${b.status_label}</span>
        </div>
    `).join('') : '<p class="text-slate-400 text-center py-4">لا حجوزات اليوم</p>';

    const queue = d.queue || [];
    document.getElementById('dayQueue').innerHTML = queue.length ? queue.map((b,i) => `
        <div class="p-3 rounded-lg ${b.status === 'with_doctor' ? 'bg-purple-100 border border-purple-200' : 'bg-slate-50'}">
            <p class="font-bold">#${b.booking_number} — ${b.patient_name}</p>
            <p class="text-xs text-slate-500">${b.status_label}</p>
            ${canViewRecords && b.status === 'with_doctor' ? `<a href="/doctor/dashboard/visits/${b.id}" class="text-xs text-blue-600">متابعة الكشف</a>` : ''}
        </div>
    `).join('') : '<p class="text-slate-400 text-center py-4">الدور فارغ</p>';
}

window.addEventListener('load', () => {
    loadDay();
    refreshTimer = setInterval(loadDay, 15000);
});
window.addEventListener('beforeunload', () => clearInterval(refreshTimer));
</script>
@endsection
