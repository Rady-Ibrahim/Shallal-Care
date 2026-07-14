@extends('admin.layout')

@section('title', 'إدارة المستخدمين')
@section('page-title', 'مسؤولو المنصة')
@section('page-description', 'إدارة حسابات المسؤولين — للمرضى والأطباء استخدم الصفحات المخصصة')

@section('content')
<div class="flex justify-end mb-4">
    <button onclick="openAddAdminModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
        <i class="fas fa-user-plus ml-2"></i>إضافة مسؤول
    </button>
</div>

<!-- Add Admin Modal -->
<div id="addAdminModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">إضافة مسؤول جديد</h3>
            <button onclick="closeAddAdminModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="addAdminForm" onsubmit="createAdmin(event)" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">الاسم</label>
                <input type="text" id="adminName" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">رقم الهاتف</label>
                <input type="tel" id="adminPhone" required class="w-full px-4 py-2 border rounded-lg" placeholder="07xxxxxxxxx">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">البريد الإلكتروني</label>
                <input type="email" id="adminEmail" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">كلمة المرور</label>
                <input type="password" id="adminPassword" required minlength="8" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">تأكيد كلمة المرور</label>
                <input type="password" id="adminPasswordConfirmation" required minlength="8" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <button type="submit" class="w-full py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">إنشاء الحساب</button>
        </form>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">البحث</label>
            <input type="text" id="searchInput" placeholder="ابحث عن مستخدم..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">الدور</label>
            <select id="roleFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">جميع الأدوار</option>
                <option value="patient">مريض</option>
                <option value="doctor">طبيب</option>
                <option value="secretary">سكرتير</option>
                <option value="admin">مسؤول</option>
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

<!-- Users Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الاسم</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">البريد الإلكتروني</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الدور</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الحالة</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">التاريخ</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الإجراء</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                <tr class="text-center py-8">
                    <td colspan="6" class="py-8">
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
    await loadUsers();
});

async function loadUsers(page = 1) {
    try {
        showLoading();
        
        const params = new URLSearchParams({
            page: page,
            limit: 20,
            ...currentFilters
        });

        const data = await apiCall(`/admin/api/users?${params}`);
        
        if (data.success) {
            renderUsersTable(data.data);
            renderPagination(data.meta);
            currentPage = page;
        } else {
            alert(data.error?.message || 'فشل تحميل المستخدمين');
        }
    } catch (error) {
        console.error('Error loading users:', error);
        alert('حدث خطأ أثناء تحميل المستخدمين');
    } finally {
        hideLoading();
    }
}

function renderUsersTable(users) {
    const tbody = document.getElementById('usersTableBody');
    
    if (users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 text-gray-400"></i>
                    <p>لا يوجد مستخدمين</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = users.map(user => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">${user.name || 'غير محدد'}</p>
                        <p class="text-sm text-gray-500">${user.phone || '-'}</p>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-gray-700">${user.email || '-'}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getRoleClass(user.role)}">
                    ${getRoleText(user.role)}
                </span>
            </td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${user.status === 'active' ? 'نشط' : 'محظور'}
                </span>
            </td>
            <td class="px-6 py-4 text-gray-600 text-sm">${formatDate(user.created_at)}</td>
            <td class="px-6 py-4">
                <div class="flex gap-2">
                    ${getUserProfileUrl(user) ? `
                    <a href="${getUserProfileUrl(user)}" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition text-sm" title="عرض الملف">
                        <i class="fas fa-eye"></i>
                    </a>` : ''}
                    ${user.status === 'active' ? `
                        <button onclick="blockUser('${user.id}')" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm">
                            <i class="fas fa-ban"></i>
                        </button>
                    ` : `
                        <button onclick="unblockUser('${user.id}')" class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition text-sm">
                            <i class="fas fa-check"></i>
                        </button>
                    `}
                    <button onclick="deleteUser('${user.id}')" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm">
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
        buttons += `<button onclick="loadUsers(${meta.page - 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">السابق</button>`;
    }
    
    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.page) {
            buttons += `<button class="px-3 py-1 bg-blue-600 text-white rounded">${i}</button>`;
        } else if (i <= 3 || i > meta.last_page - 2 || Math.abs(i - meta.page) <= 1) {
            buttons += `<button onclick="loadUsers(${i})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">${i}</button>`;
        }
    }
    
    if (meta.page < meta.last_page) {
        buttons += `<button onclick="loadUsers(${meta.page + 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">التالي</button>`;
    }
    
    paginationButtons.innerHTML = buttons;
}

function applyFilters() {
    currentFilters = {
        search: document.getElementById('searchInput').value,
        role: document.getElementById('roleFilter').value,
        status: document.getElementById('statusFilter').value,
    };
    
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) delete currentFilters[key];
    });
    
    loadUsers(1);
}

async function blockUser(userId) {
    if (!await confirmAction('هل أنت متأكد من حظر هذا المستخدم؟')) return;

    try {
        const data = await apiCall(`/admin/api/users/${userId}/block`, {
            method: 'POST'
        });

        if (data.success) {
            alert('تم الحظر بنجاح');
            loadUsers(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحظر');
    }
}

async function unblockUser(userId) {
    if (!await confirmAction('هل أنت متأكد من إلغاء الحظر عن هذا المستخدم؟')) return;

    try {
        const data = await apiCall(`/admin/api/users/${userId}/unblock`, {
            method: 'POST'
        });

        if (data.success) {
            alert('تم إلغاء الحظر بنجاح');
            loadUsers(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء إلغاء الحظر');
    }
}

async function deleteUser(userId) {
    if (!await confirmAction('هل أنت متأكد من حذف هذا المستخدم؟')) return;

    try {
        const data = await apiCall(`/admin/api/users/${userId}`, {
            method: 'DELETE'
        });

        if (data.success) {
            alert('تم الحذف بنجاح');
            loadUsers(currentPage);
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحذف');
    }
}

function getUserProfileUrl(user) {
    if (user.role === 'patient') {
        return `/admin/dashboard/patients/${user.id}`;
    }
    if (user.role === 'doctor' && user.doctor?.id) {
        return `/admin/dashboard/doctors/${user.doctor.id}`;
    }
    return null;
}

function getRoleClass(role) {
    const classes = {
        'patient': 'bg-green-100 text-green-800',
        'doctor': 'bg-blue-100 text-blue-800',
        'secretary': 'bg-amber-100 text-amber-800',
        'admin': 'bg-purple-100 text-purple-800',
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
}

function getRoleText(role) {
    const texts = {
        'patient': 'مريض',
        'doctor': 'طبيب',
        'secretary': 'سكرتير',
        'admin': 'مسؤول',
    };
    return texts[role] || role;
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}

function openAddAdminModal() {
    document.getElementById('addAdminModal').classList.remove('hidden');
    document.getElementById('addAdminModal').classList.add('flex');
}

function closeAddAdminModal() {
    document.getElementById('addAdminModal').classList.add('hidden');
    document.getElementById('addAdminModal').classList.remove('flex');
    document.getElementById('addAdminForm').reset();
}

async function createAdmin(e) {
    e.preventDefault();
    const password = document.getElementById('adminPassword').value;
    const confirmation = document.getElementById('adminPasswordConfirmation').value;
    if (password !== confirmation) {
        alert('كلمات المرور غير متطابقة');
        return;
    }

    try {
        showLoading();
        const data = await apiCall('/admin/api/users/admins', {
            method: 'POST',
            body: JSON.stringify({
                name: document.getElementById('adminName').value,
                phone: document.getElementById('adminPhone').value,
                email: document.getElementById('adminEmail').value,
                password,
                password_confirmation: confirmation,
            }),
        });
        if (data?.success) {
            alert(data.message || 'تم إنشاء المسؤول بنجاح');
            closeAddAdminModal();
            loadUsers(1);
        }
    } catch (err) {
        alert('حدث خطأ أثناء إنشاء المسؤول');
    } finally {
        hideLoading();
    }
}
</script>
@endsection
