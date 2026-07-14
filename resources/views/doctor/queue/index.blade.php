@extends('doctor.layout')

@section('title', 'الدور')
@section('page-title', 'إدارة الدور')
@section('page-description', 'المرضى المنتظرون وداخل عند الطبيب')

@section('content')
@if(!$canManageQueue)
<div class="mb-6 p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 text-sm">
    <i class="fas fa-eye ml-1"></i> وضع العرض فقط — لا يمكنك استدعاء المرضى أو إنهاء الزيارات.
</div>
@endif

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="font-bold text-amber-700 mb-4 flex items-center gap-2">
            <i class="fas fa-hourglass-half"></i> في الانتظار
        </h2>
        <div id="waitingList" class="space-y-3">
            <p class="text-slate-500 text-center py-6">جاري التحميل...</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="font-bold text-purple-700 mb-4 flex items-center gap-2">
            <i class="fas fa-user-md"></i> عند الطبيب
        </h2>
        <div id="withDoctorList" class="space-y-3">
            <p class="text-slate-500 text-center py-6">جاري التحميل...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const canManageQueue = @json($canManageQueue);
const canViewRecords = @json($canViewRecords);

function renderItem(b, actions) {
    return `
        <div class="border rounded-lg p-4 border-slate-200">
            <div class="flex justify-between items-start gap-2">
                <div>
                    <p class="font-mono text-blue-700 font-bold">${b.booking_number}</p>
                    <p class="font-semibold">${b.patient_name}</p>
                    <p class="text-xs text-slate-500">ملف ${b.file_number || '—'} · دور #${b.queue_position || '—'}</p>
                </div>
                <div class="flex flex-col gap-1">${actions}</div>
            </div>
        </div>
    `;
}

async function loadQueue() {
    const res = await apiCall('/doctor/api/queue');
    const items = res?.data || [];
    const waiting = items.filter(i => i.status === 'waiting');
    const withDoc = items.filter(i => i.status === 'with_doctor');

    document.getElementById('waitingList').innerHTML = waiting.length
        ? waiting.map(b => renderItem(b, canManageQueue ? `
            <button onclick="toDoctor(${b.id})" class="text-xs px-2 py-1 bg-purple-600 text-white rounded">استدعاء</button>
        ` : '')).join('')
        : '<p class="text-slate-400 text-center py-4">لا يوجد مرضى منتظرين</p>';

    document.getElementById('withDoctorList').innerHTML = withDoc.length
        ? withDoc.map(b => renderItem(b, `
            ${canViewRecords ? `<a href="/doctor/dashboard/visits/${b.id}" class="text-xs px-2 py-1 bg-blue-600 text-white rounded text-center">بدء الكشف</a>` : ''}
            ${canManageQueue ? `<button onclick="complete(${b.id})" class="text-xs px-2 py-1 bg-green-600 text-white rounded">إنهاء</button>` : ''}
        `)).join('')
        : '<p class="text-slate-400 text-center py-4">لا يوجد مرضى حالياً</p>';
}

async function toDoctor(id) {
    await apiCall(`/doctor/api/reception/bookings/${id}/status`, {
        method: 'PATCH', body: JSON.stringify({ status: 'with_doctor' }),
    });
    loadQueue();
}

async function complete(id) {
    await apiCall(`/doctor/api/reception/bookings/${id}/status`, {
        method: 'PATCH', body: JSON.stringify({ status: 'completed' }),
    });
    loadQueue();
}

window.addEventListener('load', loadQueue);
setInterval(loadQueue, 15000);
</script>
@endsection
