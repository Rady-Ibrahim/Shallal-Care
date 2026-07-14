@extends('admin.layout')

@section('title', 'إدارة الأطباء')
@section('page-title', 'الأطباء')
@section('page-description', 'إدارة بيانات الأطباء والموافقة على الطلبات')

@section('content')
<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">البحث</label>
            <input type="text" id="searchInput" placeholder="ابحث عن طبيب..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">الحالة</label>
            <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">جميع الحالات</option>
                <option value="pending">معلق</option>
                <option value="approved">موافق عليه</option>
                <option value="rejected">مرفوض</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">التخصص</label>
            <select id="specialityFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">جميع التخصصات</option>
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

<!-- Doctors Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الاسم</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">التخصص</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الفروع</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">حجوزات اليوم</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">التقييم</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الحالة</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">التاريخ</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الإجراء</th>
                </tr>
            </thead>
            <tbody id="doctorsTableBody">
                <tr class="text-center py-8">
                    <td colspan="8" class="py-8">
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
    await loadSpecialityOptions();
    await loadDoctors();
});

async function loadSpecialityOptions() {
    const data = await apiCall('/admin/api/specialities');
    const select = document.getElementById('specialityFilter');
    if (!data?.success) return;

    data.data.forEach(speciality => {
        const option = document.createElement('option');
        option.value = speciality.id;
        option.textContent = speciality.name_ar;
        select.appendChild(option);
    });
}

async function loadDoctors(page = 1) {
    try {
        showLoading();
        
        const params = new URLSearchParams({
            page: page,
            limit: 20,
            ...currentFilters
        });

        const data = await apiCall(`/admin/api/doctors?${params}`);
        
        if (data.success) {
            renderDoctorsTable(data.data);
            renderPagination(data.meta);
            currentPage = page;
        } else {
            alert(data.error?.message || 'فشل تحميل الأطباء');
        }
    } catch (error) {
        console.error('Error loading doctors:', error);
        alert('حدث خطأ أثناء تحميل الأطباء');
    } finally {
        hideLoading();
    }
}

function renderDoctorsTable(doctors) {
    const tbody = document.getElementById('doctorsTableBody');
    
    if (doctors.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 text-gray-400"></i>
                    <p>لا توجد أطباء</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = doctors.map(doctor => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-md text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">${doctor.name || 'غير محدد'}</p>
                        <p class="text-sm text-gray-500">${doctor.phone || '-'}</p>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-gray-700">${doctor.speciality || 'غير محدد'}</td>
            <td class="px-6 py-4 text-gray-700">${doctor.branches_count || 0}</td>
            <td class="px-6 py-4 text-gray-700">${doctor.bookings_today || 0}</td>
            <td class="px-6 py-4">
                <div class="flex items-center gap-1">
                    <i class="fas fa-star text-yellow-400"></i>
                    <span class="font-semibold">${doctor.rating || '0.0'}</span>
                </div>
            </td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusClass(doctor.status)}">
                    ${getStatusText(doctor.status)}
                </span>
            </td>
            <td class="px-6 py-4 text-gray-600 text-sm">${formatDate(doctor.created_at)}</td>
            <td class="px-6 py-4">
                <div class="flex gap-2">
                    <a href="/admin/dashboard/doctors/${doctor.id}" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition text-sm">
                        <i class="fas fa-eye"></i>
                    </a>
                    ${doctor.status === 'pending' ? `
                        <button onclick="approveDoctor('${doctor.id}')" class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition text-sm">
                            <i class="fas fa-check"></i>
                        </button>
                        <button onclick="rejectDoctor('${doctor.id}')" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                    <button onclick="deleteDoctor('${doctor.id}')" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm">
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
        buttons += `<button onclick="loadDoctors(${meta.page - 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">السابق</button>`;
    }
    
    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.page) {
            buttons += `<button class="px-3 py-1 bg-blue-600 text-white rounded">${i}</button>`;
        } else if (i <= 3 || i > meta.last_page - 2 || Math.abs(i - meta.page) <= 1) {
            buttons += `<button onclick="loadDoctors(${i})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">${i}</button>`;
        } else if (i === 4 || i === meta.last_page - 2) {
            buttons += `<span class="px-2">...</span>`;
        }
    }
    
    if (meta.page < meta.last_page) {
        buttons += `<button onclick="loadDoctors(${meta.page + 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">التالي</button>`;
    }
    
    paginationButtons.innerHTML = buttons;
}

function applyFilters() {
    currentFilters = {
        search: document.getElementById('searchInput').value,
        status: document.getElementById('statusFilter').value,
        speciality_id: document.getElementById('specialityFilter').value,
    };
    
    // Remove empty filters
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) delete currentFilters[key];
    });
    
    loadDoctors(1);
}

async function approveDoctor(doctorId) {
    if (!await confirmAction('هل أنت متأكد من الموافقة على هذا الطبيب؟')) return;

    try {
        const data = await apiCall(`/admin/api/doctors/${doctorId}/approve`, {
            method: 'POST'
        });

        if (data.success) {
            alert('تمت الموافقة بنجاح');
            loadDoctors(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الموافقة');
    }
}

async function rejectDoctor(doctorId) {
    const reason = prompt('سبب الرفض (اختياري):');
    if (reason === null) return;

    try {
        const data = await apiCall(`/admin/api/doctors/${doctorId}/reject`, {
            method: 'POST',
            body: JSON.stringify({ reject_reason: reason }),
        });

        if (data.success) {
            alert('تم الرفض بنجاح');
            loadDoctors(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الرفض');
    }
}

async function deleteDoctor(doctorId) {
    if (!await confirmAction('هل أنت متأكد من حذف هذا الطبيب؟')) return;

    try {
        const data = await apiCall(`/admin/api/doctors/${doctorId}`, {
            method: 'DELETE'
        });

        if (data.success) {
            alert('تم الحذف بنجاح');
            loadDoctors(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحذف');
    }
}

function getStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusText(status) {
    const texts = {
        'pending': 'معلق',
        'approved': 'موافق عليه',
        'rejected': 'مرفوض',
    };
    return texts[status] || status;
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}
</script>
@endsection
