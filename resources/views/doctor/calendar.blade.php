@extends('doctor.layout')

@section('title', 'التقويم')
@section('page-title', 'التقويم')
@section('page-description', 'حجوزات العيادة والجدول الزمني')

@section('content')
<!-- Calendar Header -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800" id="currentMonth">يونيو 2026</h2>
            <p class="text-gray-600 mt-1">حجوزات العيادة حسب اليوم</p>
        </div>
        <div class="flex gap-2">
            <button onclick="previousMonth()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-chevron-right"></i>
            </button>
            <button onclick="goToToday()" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                اليوم
            </button>
            <button onclick="nextMonth()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
    </div>
</div>

<!-- Calendar Grid -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Calendar -->
    <div class="lg:col-span-3 bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-7 gap-2 mb-4">
            <div class="text-center text-sm font-semibold text-gray-600 p-2">الأحد</div>
            <div class="text-center text-sm font-semibold text-gray-600 p-2">الإثنين</div>
            <div class="text-center text-sm font-semibold text-gray-600 p-2">الثلاثاء</div>
            <div class="text-center text-sm font-semibold text-gray-600 p-2">الأربعاء</div>
            <div class="text-center text-sm font-semibold text-gray-600 p-2">الخميس</div>
            <div class="text-center text-sm font-semibold text-gray-600 p-2">الجمعة</div>
            <div class="text-center text-sm font-semibold text-gray-600 p-2">السبت</div>
        </div>
        <div class="grid grid-cols-7 gap-2" id="calendarGrid">
            <!-- Calendar days will be added here -->
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Today's Schedule -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">حجوزات اليوم</h3>
            <div id="todaySchedule" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>

        <!-- Quick links -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">اختصارات</h3>
            <div class="space-y-2 text-sm">
                <a href="/doctor/dashboard/reception" class="block text-blue-600 hover:underline">مكتب الاستقبال</a>
                <a href="/doctor/dashboard/today" class="block text-blue-600 hover:underline">يوم العيادة</a>
                <a href="/doctor/dashboard/waiting-screen" target="_blank" class="block text-blue-600 hover:underline">شاشة الانتظار</a>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">حجوزات اليوم</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modalContent">
            <!-- Modal content will be added here -->
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentDate = new Date();
let calendarData = {};

window.addEventListener('load', async function() {
    await loadCalendar();
    await loadTodaySchedule();
});

async function loadCalendar() {
    try {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth() + 1;
        
        document.getElementById('currentMonth').textContent = getMonthName(month) + ' ' + year;
        
        const data = await apiCall(`/doctor/api/calendar?year=${year}&month=${month}`);
        
        if (data.success) {
            calendarData = data.data || {};
            renderCalendar(calendarData);
        }
    } catch (error) {
        console.error('Error loading calendar:', error);
    }
}

function renderCalendar(bookingsByDate) {
    const grid = document.getElementById('calendarGrid');
    const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
    const startDay = firstDay.getDay();
    const totalDays = lastDay.getDate();
    
    let html = '';
    
    for (let i = 0; i < startDay; i++) {
        html += '<div class="p-2"></div>';
    }
    
    const today = new Date();
    for (let day = 1; day <= totalDays; day++) {
        const dateStr = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = day === today.getDate() && currentDate.getMonth() === today.getMonth() && currentDate.getFullYear() === today.getFullYear();
        const bookings = bookingsByDate[dateStr] || [];
        
        html += `
            <div onclick="selectDate('${dateStr}')" 
                 class="p-2 rounded-lg cursor-pointer hover:bg-gray-100 transition min-h-[72px] ${isToday ? 'bg-teal-100 border-2 border-teal-600' : 'border border-transparent'}">
                <div class="text-sm font-semibold ${isToday ? 'text-teal-600' : 'text-gray-700'}">${day}</div>
                ${bookings.length > 0 ? `
                    <div class="mt-1 space-y-1">
                        ${bookings.slice(0, 2).map(b => `
                            <div class="text-xs px-1 py-0.5 rounded ${getStatusBgClass(b.status)} text-white truncate">
                                #${b.booking_number}
                            </div>
                        `).join('')}
                        ${bookings.length > 2 ? `<div class="text-xs text-gray-500">+${bookings.length - 2}</div>` : ''}
                    </div>
                ` : ''}
            </div>
        `;
    }
    
    grid.innerHTML = html;
}

async function loadTodaySchedule() {
    try {
        const data = await apiCall('/doctor/api/today-activity');
        
        if (data.success) {
            renderTodaySchedule(data.data.bookings || []);
        }
    } catch (error) {
        console.error('Error loading today schedule:', error);
    }
}

function renderTodaySchedule(bookings) {
    const container = document.getElementById('todaySchedule');
    
    if (bookings.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد حجوزات اليوم</p>';
        return;
    }
    
    container.innerHTML = bookings.map(booking => `
        <a href="/doctor/dashboard/visits/${booking.id}" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold text-gray-800">#${booking.booking_number} — ${booking.patient_name || 'مريض'}</p>
                    <p class="text-sm text-gray-600">${booking.status_label || booking.status}</p>
                </div>
            </div>
        </a>
    `).join('');
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    loadCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    loadCalendar();
}

function goToToday() {
    currentDate = new Date();
    loadCalendar();
    loadTodaySchedule();
}

function selectDate(dateStr) {
    const bookings = calendarData[dateStr] || [];
    showDateBookings(bookings, dateStr);
}

function showDateBookings(bookings, dateStr) {
    const modal = document.getElementById('appointmentModal');
    const content = document.getElementById('modalContent');
    
    if (bookings.length === 0) {
        content.innerHTML = `
            <p class="text-gray-500 text-center py-4">لا توجد حجوزات في ${formatDate(dateStr)}</p>
        `;
    } else {
        content.innerHTML = `
            <p class="text-sm text-gray-500 mb-3">${formatDate(dateStr)} — ${bookings.length} حجز</p>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                ${bookings.map(booking => `
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <p class="font-semibold text-gray-800">#${booking.booking_number} — ${booking.patient_name || 'مريض'}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold ${getStatusClass(booking.status)}">
                                ${getStatusText(booking.status)}
                            </span>
                        </div>
                        <a href="/doctor/dashboard/visits/${booking.id}" class="text-xs text-blue-600 mt-2 inline-block">فتح الكشف</a>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('appointmentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function getMonthName(month) {
    const months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
    return months[month - 1];
}

function getStatusClass(status) {
    const classes = {
        'scheduled': 'bg-blue-100 text-blue-800',
        'checked_in': 'bg-cyan-100 text-cyan-800',
        'waiting': 'bg-amber-100 text-amber-800',
        'with_doctor': 'bg-purple-100 text-purple-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'no_show': 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusBgClass(status) {
    const classes = {
        'scheduled': 'bg-blue-500',
        'checked_in': 'bg-cyan-500',
        'waiting': 'bg-amber-500',
        'with_doctor': 'bg-purple-500',
        'completed': 'bg-green-500',
        'cancelled': 'bg-red-500',
        'no_show': 'bg-gray-500',
    };
    return classes[status] || 'bg-gray-500';
}

function getStatusText(status) {
    const texts = {
        'scheduled': 'محجوز',
        'checked_in': 'حضر',
        'waiting': 'في الدور',
        'with_doctor': 'عند الطبيب',
        'completed': 'مكتمل',
        'cancelled': 'ملغي',
        'no_show': 'لم يحضر',
    };
    return texts[status] || status;
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}
</script>
@endsection
