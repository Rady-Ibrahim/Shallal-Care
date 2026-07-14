@extends('doctor.layout')

@section('title', 'عرض السجل الطبي')
@section('page-title', 'تفاصيل السجل')
@section('page-description', 'عرض السجل الطبي للمريض')

@section('content')
<div class="mb-6 flex flex-wrap gap-3 items-center justify-between no-print">
    <a href="/doctor/dashboard/records" class="text-teal-600 hover:text-teal-700 flex items-center gap-2">
        <i class="fas fa-arrow-right"></i>
        <span>العودة إلى السجلات</span>
    </a>
    <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-700 text-white rounded-lg text-sm">
        <i class="fas fa-print ml-1"></i> طباعة
    </button>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    aside, header, nav { display: none !important; }
}
</style>

<div class="bg-white rounded-xl shadow-sm p-6" id="recordContent">
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-spinner fa-spin text-3xl text-teal-600 mb-4"></i>
        <p>جاري التحميل...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
const recordId = {{ (int) $recordId }};
const shouldPrint = new URLSearchParams(window.location.search).get('print') === '1';

window.addEventListener('load', loadRecord);

async function loadRecord() {
    const container = document.getElementById('recordContent');
    try {
        const data = await apiCall(`/doctor/api/records/${recordId}`);
        if (!data?.success) {
            container.innerHTML = '<p class="text-center text-red-600 py-8">تعذر تحميل السجل</p>';
            return;
        }
        const r = data.data;
        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div><p class="text-sm text-gray-500">المريض</p><p class="font-bold text-lg">${r.patient_name || '-'}</p></div>
                <div><p class="text-sm text-gray-500">النوع</p><p class="font-bold">${r.type || '-'}</p></div>
            </div>
            <div class="mb-6"><p class="text-sm text-gray-500 mb-1">العنوان</p><p class="text-gray-800">${r.title || '-'}</p></div>
            <div class="mb-6"><p class="text-sm text-gray-500 mb-1">الوصف</p><p class="text-gray-800">${r.description || '-'}</p></div>
            <div class="mb-6"><p class="text-sm text-gray-500 mb-1">ملاحظات</p><p class="text-gray-800">${r.notes || '-'}</p></div>
            <div class="flex gap-3 no-print">
                <a href="/doctor/dashboard/records/${recordId}/edit" class="px-4 py-2 bg-teal-600 text-white rounded-lg">تعديل</a>
            </div>
        `;
        if (shouldPrint) setTimeout(() => window.print(), 400);
    } catch (e) {
        container.innerHTML = '<p class="text-center text-red-600 py-8">حدث خطأ أثناء التحميل</p>';
    }
}
</script>
@endsection
