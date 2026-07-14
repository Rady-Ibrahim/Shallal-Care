@extends('admin.layout')

@section('title', 'إدارة المرضى')
@section('page-title', 'حسابات المرضى')
@section('page-description', 'حسابات مستخدمي تطبيق المنصة — للملفات داخل العيادات راجع «ملفات مرضى العيادات»')

@section('content')
<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">البحث</label>
            <input type="text" id="searchInput" placeholder="ابحث عن مريض..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">النوع</label>
            <select id="typeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">جميع الأنواع</option>
                <option value="regular">عادي</option>
                <option value="ghost">مريض بدون تطبيق</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">الحالة</label>
            <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">جميع الحالات</option>
                <option value="active">نشط</option>
                <option value="blocked">محظور</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">&nbsp;</label>
            <button onclick="applyFilters()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-search ml-2"></i>بحث
            </button>
        </div>
    </div>
</div>

<!-- Patients Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الاسم</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الهاتف</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">النوع</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الحالة</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الحجوزات</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">التاريخ</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الإجراء</th>
                </tr>
            </thead>
            <tbody id="patientsTableBody">
                <tr class="text-center py-8">
                    <td colspan="7" class="py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
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
    await loadPatients();
});

async function loadPatients(page = 1) {
    try {
        showLoading();
        
        const params = new URLSearchParams({
            page: page,
            limit: 20,
            ...currentFilters
        });

        const data = await apiCall(`/admin/api/patients?${params}`);
        
        if (data.success) {
            renderPatientsTable(data.data);
            renderPagination(data.meta);
            currentPage = page;
        } else {
            alert(data.error?.message || 'فشل تحميل المرضى');
        }
    } catch (error) {
        console.error('Error loading patients:', error);
        alert('حدث خطأ أثناء تحميل المرضى');
    } finally {
        hideLoading();
    }
}

function renderPatientsTable(patients) {
    const tbody = document.getElementById('patientsTableBody');
    
    if (patients.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 text-gray-400"></i>
                    <p>لا يوجد مرضى</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = patients.map(patient => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-green-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">${patient.name || 'غير محدد'}</p>
                        <p class="text-sm text-gray-500">${patient.email || '-'}</p>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-gray-700">${patient.phone || '-'}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${patient.is_ghost ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}">
                    ${patient.is_ghost ? 'Ghost' : 'عادي'}
                </span>
            </td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${patient.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${patient.status === 'active' ? 'نشط' : 'محظور'}
                </span>
            </td>
            <td class="px-6 py-4 text-gray-700">${patient.total_bookings || patient.total_appointments || 0}</td>
            <td class="px-6 py-4 text-gray-600 text-sm">${formatDate(patient.created_at)}</td>
            <td class="px-6 py-4">
                <div class="flex gap-2">
                    <a href="/admin/dashboard/patients/${patient.id}" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition text-sm">
                        <i class="fas fa-eye"></i>
                    </a>
                    ${patient.status === 'active' ? `
                        <button onclick="blockPatient('${patient.id}')" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm">
                            <i class="fas fa-ban"></i>
                        </button>
                    ` : `
                        <button onclick="unblockPatient('${patient.id}')" class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition text-sm">
                            <i class="fas fa-check"></i>
                        </button>
                    `}
                    <button onclick="deletePatient('${patient.id}')" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm">
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
        buttons += `<button onclick="loadPatients(${meta.page - 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">السابق</button>`;
    }
    
    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.page) {
            buttons += `<button class="px-3 py-1 bg-blue-600 text-white rounded">${i}</button>`;
        } else if (i <= 3 || i > meta.last_page - 2 || Math.abs(i - meta.page) <= 1) {
            buttons += `<button onclick="loadPatients(${i})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">${i}</button>`;
        }
    }
    
    if (meta.page < meta.last_page) {
        buttons += `<button onclick="loadPatients(${meta.page + 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">التالي</button>`;
    }
    
    paginationButtons.innerHTML = buttons;
}

function applyFilters() {
    currentFilters = {
        search: document.getElementById('searchInput').value,
        is_ghost: document.getElementById('typeFilter').value,
        status: document.getElementById('statusFilter').value,
    };
    
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) delete currentFilters[key];
    });
    
    loadPatients(1);
}

async function blockPatient(patientId) {
    if (!await confirmAction('هل أنت متأكد من حظر هذا المريض؟')) return;

    try {
        const data = await apiCall(`/admin/api/patients/${patientId}/block`, {
            method: 'POST'
        });

        if (data.success) {
            alert('تم الحظر بنجاح');
            loadPatients(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحظر');
    }
}

async function unblockPatient(patientId) {
    if (!await confirmAction('هل أنت متأكد من إلغاء الحظر عن هذا المريض؟')) return;

    try {
        const data = await apiCall(`/admin/api/patients/${patientId}/unblock`, {
            method: 'POST'
        });

        if (data.success) {
            alert('تم إلغاء الحظر بنجاح');
            loadPatients(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء إلغاء الحظر');
    }
}

async function deletePatient(patientId) {
    if (!await confirmAction('هل أنت متأكد من حذف هذا المريض؟')) return;

    try {
        const data = await apiCall(`/admin/api/patients/${patientId}`, {
            method: 'DELETE'
        });

        if (data.success) {
            alert('تم الحذف بنجاح');
            loadPatients(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحذف');
    }
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}
</script>
@endsection
