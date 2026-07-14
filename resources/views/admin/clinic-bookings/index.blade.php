@extends('admin.layout')

@section('title', 'حجوزات العيادات')
@section('page-title', 'حجوزات العيادات')
@section('page-description', 'عرض حجوزات العيادات عبر المنصة')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">البحث</label>
            <input type="text" id="searchInput" placeholder="مريض، طبيب، أو رقم الحجز..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">الحالة</label>
            <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">جميع الحالات</option>
                <option value="scheduled">محجوز</option>
                <option value="checked_in">حضر</option>
                <option value="waiting">في الانتظار</option>
                <option value="with_doctor">عند الطبيب</option>
                <option value="completed">مكتمل</option>
                <option value="cancelled">ملغي</option>
                <option value="no_show">لم يحضر</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">من التاريخ</label>
            <input type="date" id="dateFromFilter"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">إلى التاريخ</label>
            <input type="date" id="dateToFilter"
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
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">رقم الحجز</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">المريض</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الطبيب / الفرع</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">تاريخ الزيارة</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الكشف</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الحالة</th>
                </tr>
            </thead>
            <tbody id="bookingsTableBody">
                <tr class="text-center py-8">
                    <td colspan="6" class="py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                        <p class="text-gray-500">جاري التحميل...</p>
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

window.addEventListener('load', async function() {
    await loadBookings();
});

async function loadBookings(page = 1) {
    try {
        showLoading();

        const params = new URLSearchParams({
            page: page,
            limit: 20,
            ...currentFilters
        });

        const data = await apiCall(`/admin/api/clinic-bookings?${params}`);

        if (data.success) {
            renderBookingsTable(data.data);
            renderPagination(data.meta);
            currentPage = page;
        } else {
            alert(data.error?.message || 'فشل تحميل الحجوزات');
        }
    } catch (error) {
        console.error('Error loading bookings:', error);
        alert('حدث خطأ أثناء تحميل الحجوزات');
    } finally {
        hideLoading();
    }
}

function renderBookingsTable(bookings) {
    const tbody = document.getElementById('bookingsTableBody');

    if (!bookings.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 text-gray-400"></i>
                    <p>لا توجد حجوزات</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = bookings.map(booking => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold text-gray-800">#${booking.booking_number || booking.id}</td>
            <td class="px-6 py-4">
                <p class="font-semibold text-gray-800">${booking.patient_name || 'غير محدد'}</p>
                <p class="text-sm text-gray-500">${booking.patient_phone || '-'}</p>
            </td>
            <td class="px-6 py-4">
                <p class="font-semibold text-gray-800">${booking.doctor_name || 'غير محدد'}</p>
                <p class="text-sm text-gray-500">${booking.speciality || '-'} · ${booking.branch_name || 'فرع'}</p>
            </td>
            <td class="px-6 py-4 text-gray-700">${formatDate(booking.visit_date)}</td>
            <td class="px-6 py-4 text-gray-700">${formatCurrency(booking.consultation_fee || booking.price || 0)}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusClass(booking.status)}">
                    ${booking.status_label || getStatusText(booking.status)}
                </span>
            </td>
        </tr>
    `).join('');
}

function renderPagination(meta) {
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationButtons = document.getElementById('paginationButtons');

    if (!meta) {
        paginationInfo.textContent = 'عرض 0 من 0';
        paginationButtons.innerHTML = '';
        return;
    }

    paginationInfo.textContent = `عرض ${(meta.page - 1) * meta.limit + 1} إلى ${Math.min(meta.page * meta.limit, meta.total)} من ${meta.total}`;

    let buttons = '';

    if (meta.page > 1) {
        buttons += `<button onclick="loadBookings(${meta.page - 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">السابق</button>`;
    }

    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.page) {
            buttons += `<button class="px-3 py-1 bg-blue-600 text-white rounded">${i}</button>`;
        } else if (i <= 3 || i > meta.last_page - 2 || Math.abs(i - meta.page) <= 1) {
            buttons += `<button onclick="loadBookings(${i})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">${i}</button>`;
        }
    }

    if (meta.page < meta.last_page) {
        buttons += `<button onclick="loadBookings(${meta.page + 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">التالي</button>`;
    }

    paginationButtons.innerHTML = buttons;
}

function applyFilters() {
    currentFilters = {
        search: document.getElementById('searchInput').value,
        status: document.getElementById('statusFilter').value,
        date_from: document.getElementById('dateFromFilter').value,
        date_to: document.getElementById('dateToFilter').value,
    };

    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) delete currentFilters[key];
    });

    loadBookings(1);
}

function getStatusClass(status) {
    const classes = {
        scheduled: 'bg-blue-100 text-blue-800',
        checked_in: 'bg-cyan-100 text-cyan-800',
        waiting: 'bg-amber-100 text-amber-800',
        with_doctor: 'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        no_show: 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusText(status) {
    const texts = {
        scheduled: 'محجوز',
        checked_in: 'حضر',
        waiting: 'في الانتظار',
        with_doctor: 'عند الطبيب',
        completed: 'مكتمل',
        cancelled: 'ملغي',
        no_show: 'لم يحضر',
    };
    return texts[status] || status;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-EG', { style: 'decimal', minimumFractionDigits: 0 }).format(amount || 0) + ' ج.م';
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}
</script>
@endsection
