@extends('admin.layout')

@section('title', 'إدارة المحافظات')
@section('page-title', 'المحافظات')
@section('page-description', 'إدارة محافظات مصر')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">إضافة محافظة جديدة</h3>
    <form id="governorateForm" class="grid grid-cols-1 md:grid-cols-3 gap-4" onsubmit="saveGovernorate(event)">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">الاسم بالعربية</label>
            <input type="text" id="nameAr" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">الاسم بالإنجليزية</label>
            <input type="text" id="nameEn" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus ml-2"></i>إضافة
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الاسم (عربي)</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الاسم (إنجليزي)</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الحالة</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الإجراء</th>
            </tr>
        </thead>
        <tbody id="governoratesTableBody">
            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">جاري التحميل...</td></tr>
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
window.addEventListener('load', loadGovernorates);

async function loadGovernorates() {
    const data = await apiCall('/admin/api/governorates');
    const tbody = document.getElementById('governoratesTableBody');

    if (!data?.success || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">لا توجد محافظات</td></tr>';
        return;
    }

    tbody.innerHTML = data.data.map(item => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold">${item.name_ar}</td>
            <td class="px-6 py-4">${item.name_en || '-'}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs ${item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${item.is_active ? 'نشط' : 'غير نشط'}
                </span>
            </td>
            <td class="px-6 py-4">
                <button onclick="toggleGovernorate(${item.id}, ${item.is_active ? 'false' : 'true'})" class="px-3 py-1 bg-blue-100 text-blue-600 rounded text-sm ml-2">
                    ${item.is_active ? 'تعطيل' : 'تفعيل'}
                </button>
                <button onclick="deleteGovernorate(${item.id})" class="px-3 py-1 bg-red-100 text-red-600 rounded text-sm">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

async function saveGovernorate(event) {
    event.preventDefault();
    const data = await apiCall('/admin/api/governorates', {
        method: 'POST',
        body: JSON.stringify({
            name_ar: document.getElementById('nameAr').value,
            name_en: document.getElementById('nameEn').value || null,
            is_active: true,
        }),
    });

    if (data?.success) {
        showSuccess('تم إضافة المحافظة');
        document.getElementById('governorateForm').reset();
        loadGovernorates();
    }
}

async function toggleGovernorate(id, isActive) {
    const data = await apiCall(`/admin/api/governorates/${id}`, {
        method: 'PUT',
        body: JSON.stringify({ is_active: isActive }),
    });
    if (data?.success) loadGovernorates();
}

async function deleteGovernorate(id) {
    if (!await confirmAction('هل أنت متأكد من حذف هذه المحافظة؟')) return;
    const data = await apiCall(`/admin/api/governorates/${id}`, { method: 'DELETE' });
    if (data?.success) {
        showSuccess('تم الحذف');
        loadGovernorates();
    }
}
</script>
@endsection
