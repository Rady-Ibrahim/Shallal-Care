@extends('doctor.layout')

@section('title', 'سجلات المريض')
@section('page-title', 'السجلات الطبية')
@section('page-description', 'جميع السجلات الطبية للمريض')

@section('content')
<div class="mb-6">
    <a href="/doctor/dashboard/patients/{{ $patientId }}" class="text-teal-600 hover:text-teal-700 flex items-center gap-2">
        <i class="fas fa-arrow-right"></i>
        <span>العودة إلى المريض</span>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">النوع</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">التاريخ</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الإجراء</th>
            </tr>
        </thead>
        <tbody id="recordsTableBody">
            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">جاري التحميل...</td></tr>
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
const patientId = {{ $patientId }};

window.addEventListener('load', loadRecords);

async function loadRecords() {
    const tbody = document.getElementById('recordsTableBody');
    try {
        const data = await apiCall(`/doctor/api/patients/${patientId}`);
        if (!data?.success) {
            tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-8 text-center text-red-600">تعذر تحميل السجلات</td></tr>';
            return;
        }
        const records = data.data.medical_records || [];
        if (records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">لا توجد سجلات</td></tr>';
            return;
        }
        tbody.innerHTML = records.map(r => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-4">${r.type || '-'}</td>
                <td class="px-6 py-4">${formatDate(r.created_at)}</td>
                <td class="px-6 py-4">
                    <a href="/doctor/dashboard/records/${r.id}" class="text-teal-600 hover:text-teal-700"><i class="fas fa-eye"></i></a>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-8 text-center text-red-600">حدث خطأ</td></tr>';
    }
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}
</script>
@endsection
