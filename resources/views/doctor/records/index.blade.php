@extends('doctor.layout')

@section('title', 'السجلات الطبية')
@section('page-title', 'السجلات')
@section('page-description', 'إدارة السجلات الطبية للمرضى')

@section('content')
<!-- Header with Create Button -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">السجلات الطبية</h2>
        <p class="text-gray-600 mt-1">إدارة السجلات الطبية للمرضى</p>
    </div>
    <a href="/doctor/dashboard/records/create" class="px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
        <i class="fas fa-plus ml-2"></i>سجل جديد
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">البحث</label>
            <input type="text" id="searchInput" placeholder="ابحث عن مريض..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">نوع السجل</label>
            <select id="typeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                <option value="">جميع الأنواع</option>
                <option value="diagnosis">التشخيص</option>
                <option value="treatment">العلاج</option>
                <option value="lab_test">اختبار معملي</option>
                <option value="imaging">تصوير</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">من التاريخ</label>
            <input type="date" id="dateFromFilter"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">&nbsp;</label>
            <button onclick="applyFilters()" class="w-full px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-search ml-2"></i>بحث
            </button>
        </div>
    </div>
</div>

<!-- Records Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">المريض</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">العنوان</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">النوع</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">التاريخ</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الملفات</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الإجراء</th>
                </tr>
            </thead>
            <tbody id="recordsTableBody">
                <tr class="text-center py-8">
                    <td colspan="6" class="py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-teal-600 mb-2"></i>
                        <p class="text-gray-500">جاري التحميل...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t flex items-center justify-between">
        <div class="text-sm text-gray-600" id="paginationInfo">
            عرض 0 من 0
        </div>
        <div class="flex gap-2" id="paginationButtons">
            <!-- Pagination buttons will be added here -->
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentPage = 1;
let currentFilters = {};

window.addEventListener('load', async function() {
    await loadRecords();
});

async function loadRecords(page = 1) {
    try {
        showLoading();
        
        const params = new URLSearchParams({
            page: page,
            limit: 20,
            ...currentFilters
        });

        const data = await apiCall(`/doctor/api/records?${params}`);
        
        if (data.success) {
            renderRecordsTable(data.data);
            renderPagination(data.meta);
            currentPage = page;
        } else {
            alert(data.error?.message || 'فشل تحميل السجلات');
        }
    } catch (error) {
        console.error('Error loading records:', error);
        alert('حدث خطأ أثناء تحميل السجلات');
    } finally {
        hideLoading();
    }
}

function renderRecordsTable(records) {
    const tbody = document.getElementById('recordsTableBody');
    
    if (records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 text-gray-400"></i>
                    <p>لا توجد سجلات</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(record => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-teal-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">${record.patient_name || 'غير محدد'}</p>
                        <p class="text-sm text-gray-500">${record.patient_phone || '-'}</p>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-gray-700">${record.title || '-'}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getTypeClass(record.type)}">
                    ${getTypeText(record.type)}
                </span>
            </td>
            <td class="px-6 py-4 text-gray-700">${formatDate(record.created_at)}</td>
            <td class="px-6 py-4">
                <span class="text-sm text-gray-600">${record.attachments_count || 0} ملف</span>
            </td>
            <td class="px-6 py-4">
                <div class="flex gap-2">
                    <a href="/doctor/dashboard/records/${record.id}" class="px-3 py-1 bg-teal-100 text-teal-600 rounded hover:bg-teal-200 transition text-sm">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="/doctor/dashboard/records/${record.id}/edit" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition text-sm">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="deleteRecord('${record.id}')" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderPagination(meta) {
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationButtons = document.getElementById('paginationButtons');
    
    paginationInfo.textContent = `عرض ${(meta.page - 1) * meta.limit + 1} إلى ${Math.min(meta.page * meta.limit, meta.total)} من ${meta.total}`;
    
    let buttons = '';
    
    if (meta.page > 1) {
        buttons += `<button onclick="loadRecords(${meta.page - 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">السابق</button>`;
    }
    
    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.page) {
            buttons += `<button class="px-3 py-1 bg-teal-600 text-white rounded">${i}</button>`;
        } else if (i <= 3 || i > meta.last_page - 2 || Math.abs(i - meta.page) <= 1) {
            buttons += `<button onclick="loadRecords(${i})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">${i}</button>`;
        }
    }
    
    if (meta.page < meta.last_page) {
        buttons += `<button onclick="loadRecords(${meta.page + 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">التالي</button>`;
    }
    
    paginationButtons.innerHTML = buttons;
}

function applyFilters() {
    currentFilters = {
        search: document.getElementById('searchInput').value,
        type: document.getElementById('typeFilter').value,
        date_from: document.getElementById('dateFromFilter').value,
    };
    
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) delete currentFilters[key];
    });
    
    loadRecords(1);
}

async function deleteRecord(recordId) {
    if (!await confirmAction('هل أنت متأكد من حذف هذا السجل؟')) return;

    try {
        const data = await apiCall(`/doctor/api/records/${recordId}`, {
            method: 'DELETE'
        });

        if (data.success) {
            alert('تم الحذف بنجاح');
            loadRecords(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحذف');
    }
}

function getTypeClass(type) {
    const classes = {
        'diagnosis': 'bg-blue-100 text-blue-800',
        'treatment': 'bg-green-100 text-green-800',
        'lab_test': 'bg-purple-100 text-purple-800',
        'imaging': 'bg-orange-100 text-orange-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
}

function getTypeText(type) {
    const texts = {
        'diagnosis': 'تشخيص',
        'treatment': 'علاج',
        'lab_test': 'اختبار معملي',
        'imaging': 'تصوير',
    };
    return texts[type] || type;
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-IQ');
}
</script>
@endsection
