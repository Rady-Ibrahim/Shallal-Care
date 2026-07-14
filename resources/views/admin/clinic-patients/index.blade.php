@extends('admin.layout')

@section('title', 'ملفات مرضى العيادات')
@section('page-title', 'ملفات مرضى العيادات')
@section('page-description', 'سجلات المرضى داخل العيادات — مرتبطة بالطبيب والفرع وليست حسابات التطبيق')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">البحث</label>
            <input type="text" id="searchInput" placeholder="اسم مريض، هاتف، رقم ملف، أو طبيب..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">&nbsp;</label>
            <button onclick="applyFilters()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-search ml-2"></i>بحث
            </button>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">رقم الملف</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">المريض</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الطبيب</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الفرع</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الحجوزات</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">تاريخ التسجيل</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">إجراء</th>
                </tr>
            </thead>
            <tbody id="profilesTableBody">
                <tr>
                    <td colspan="7" class="py-8 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                        <p>جاري التحميل...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t flex items-center justify-between">
        <div class="text-sm text-gray-600" id="paginationInfo">عرض 0 من 0</div>
        <div class="flex gap-2" id="paginationButtons"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentPage = 1;
let currentFilters = {};

window.addEventListener('load', () => loadProfiles());

async function loadProfiles(page = 1) {
    try {
        showLoading();
        const params = new URLSearchParams({ page, limit: 20, ...currentFilters });
        const data = await apiCall(`/admin/api/clinic-patients?${params}`);

        if (data.success) {
            renderProfilesTable(data.data);
            renderPagination(data.meta);
            currentPage = page;
        }
    } catch (e) {
        console.error(e);
    } finally {
        hideLoading();
    }
}

function renderProfilesTable(profiles) {
    const tbody = document.getElementById('profilesTableBody');
    if (!profiles.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">لا توجد ملفات</td></tr>';
        return;
    }

    tbody.innerHTML = profiles.map(p => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold text-gray-800">${p.file_number || '-'}</td>
            <td class="px-6 py-4">
                <p class="font-semibold">${p.patient_name || 'غير محدد'}</p>
                <p class="text-sm text-gray-500">${p.patient_phone || '-'}</p>
            </td>
            <td class="px-6 py-4">
                <a href="/admin/dashboard/doctors/${p.doctor_id}" class="text-blue-600 hover:underline">${p.doctor_name || '-'}</a>
            </td>
            <td class="px-6 py-4 text-gray-700">${p.branch_name || '-'}</td>
            <td class="px-6 py-4 text-gray-700">${p.bookings_count || 0}</td>
            <td class="px-6 py-4 text-gray-600 text-sm">${formatDate(p.created_at)}</td>
            <td class="px-6 py-4">
                ${p.patient_id ? `<a href="/admin/dashboard/patients/${p.patient_id}" class="px-3 py-1 bg-blue-100 text-blue-600 rounded text-sm"><i class="fas fa-eye"></i></a>` : '-'}
            </td>
        </tr>
    `).join('');
}

function renderPagination(meta) {
    const info = document.getElementById('paginationInfo');
    const buttons = document.getElementById('paginationButtons');
    if (!meta) return;

    info.textContent = `عرض ${(meta.page - 1) * meta.limit + 1} إلى ${Math.min(meta.page * meta.limit, meta.total)} من ${meta.total}`;
    let html = '';
    if (meta.page > 1) html += `<button onclick="loadProfiles(${meta.page - 1})" class="px-3 py-1 border rounded">السابق</button>`;
    if (meta.page < meta.last_page) html += `<button onclick="loadProfiles(${meta.page + 1})" class="px-3 py-1 border rounded">التالي</button>`;
    buttons.innerHTML = html;
}

function applyFilters() {
    currentFilters = { search: document.getElementById('searchInput').value };
    if (!currentFilters.search) delete currentFilters.search;
    loadProfiles(1);
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}
</script>
@endsection
