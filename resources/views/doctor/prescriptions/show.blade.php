@extends('doctor.layout')

@section('title', 'عرض وصفة طبية')
@section('page-title', 'تفاصيل الوصفة')
@section('page-description', 'عرض بيانات الوصفة الطبية')

@section('content')
<div class="mb-6">
    <a href="/doctor/dashboard/prescriptions" class="text-teal-600 hover:text-teal-700 flex items-center gap-2">
        <i class="fas fa-arrow-right"></i>
        <span>العودة إلى الوصفات</span>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm p-6" id="prescriptionContent">
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-spinner fa-spin text-3xl text-teal-600 mb-4"></i>
        <p>جاري التحميل...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
const prescriptionId = {{ (int) $prescriptionId }};

window.addEventListener('load', loadPrescription);

async function loadPrescription() {
    const container = document.getElementById('prescriptionContent');
    try {
        const data = await apiCall(`/doctor/api/prescriptions/${prescriptionId}`);
        if (!data?.success) {
            container.innerHTML = '<p class="text-center text-red-600 py-8">تعذر تحميل الوصفة</p>';
            return;
        }
        const p = data.data;
        const medicines = (p.medicines || []).map(m => `
            <li class="py-2 border-b">${m.name || m.medicine_name || '-'} — ${m.dosage || ''} ${m.duration || ''}</li>
        `).join('');

        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div><p class="text-sm text-gray-500">المريض</p><p class="font-bold text-lg">${p.patient_name || '-'}</p></div>
                <div><p class="text-sm text-gray-500">التاريخ</p><p class="font-bold">${formatDate(p.created_at)}</p></div>
            </div>
            <div class="mb-6"><p class="text-sm text-gray-500 mb-1">التشخيص</p><p class="text-gray-800">${p.diagnosis || '-'}</p></div>
            <div class="mb-6"><p class="text-sm text-gray-500 mb-2">الأدوية</p><ul class="bg-gray-50 rounded-lg p-4">${medicines || '<li>لا توجد أدوية</li>'}</ul></div>
            <div class="mb-6"><p class="text-sm text-gray-500 mb-1">ملاحظات</p><p class="text-gray-800">${p.notes || '-'}</p></div>
            <div class="flex gap-3 mt-6 flex-wrap">
                <a href="/doctor/dashboard/prescriptions/${prescriptionId}/print?paper=a4" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg">طباعة A4</a>
                <a href="/doctor/dashboard/prescriptions/${prescriptionId}/print?paper=half_a4" target="_blank" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg">طباعة نصف A4</a>
                <a href="/doctor/dashboard/prescriptions/${prescriptionId}/edit" class="px-4 py-2 bg-slate-700 text-white rounded-lg">تعديل</a>
            </div>
        `;
    } catch (e) {
        container.innerHTML = '<p class="text-center text-red-600 py-8">حدث خطأ أثناء التحميل</p>';
    }
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}
</script>
@endsection
