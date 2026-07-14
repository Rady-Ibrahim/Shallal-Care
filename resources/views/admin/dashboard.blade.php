@extends('admin.layout')

@section('title', 'لوحة تحكم الإدارة - الرئيسية')
@section('page-title', 'نظرة عامة')
@section('page-description', 'إحصائيات منصة العيادات والاشتراكات')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">الأطباء</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2" id="totalDoctors">-</h3>
                <p class="text-sm text-green-600 mt-2" id="activeDoctorsPercent">-</p>
            </div>
            <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-md text-blue-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-teal-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">حجوزات اليوم</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2" id="bookingsToday">-</h3>
                <p class="text-sm text-teal-600 mt-2" id="bookingsWaiting">-</p>
            </div>
            <div class="w-14 h-14 bg-teal-100 rounded-full flex items-center justify-center">
                <i class="fas fa-door-open text-teal-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">مرضى العيادات</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2" id="clinicPatientsTotal">-</h3>
                <p class="text-sm text-gray-500 mt-2"><span id="clinicBranchesTotal">0</span> فرع · <span id="clinicStaffTotal">0</span> سكرتير</p>
            </div>
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-green-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-amber-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">إيراد العيادات (الشهر)</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2" id="clinicRevenueMonth">-</h3>
                <p class="text-sm text-amber-600 mt-2" id="subscriptionRevenueMonth">-</p>
            </div>
            <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center">
                <i class="fas fa-cash-register text-amber-600 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4">ملخص نشاط العيادات</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="p-4 rounded-lg bg-slate-50">
            <p class="text-xs text-gray-600">إجمالي الحجوزات</p>
            <p id="clinicBookingsTotal" class="text-2xl font-bold text-slate-800 mt-1">0</p>
        </div>
        <div class="p-4 rounded-lg bg-green-50">
            <p class="text-xs text-gray-600">زيارات مكتملة</p>
            <p id="clinicBookingsCompleted" class="text-2xl font-bold text-green-700 mt-1">0</p>
        </div>
        <div class="p-4 rounded-lg bg-red-50">
            <p class="text-xs text-gray-600">ملغاة</p>
            <p id="clinicBookingsCancelled" class="text-2xl font-bold text-red-700 mt-1">0</p>
        </div>
        <div class="p-4 rounded-lg bg-blue-50">
            <p class="text-xs text-gray-600">إيراد اليوم</p>
            <p id="clinicRevenueToday" class="text-2xl font-bold text-blue-700 mt-1">0</p>
        </div>
        <div class="p-4 rounded-lg bg-purple-50">
            <p class="text-xs text-gray-600">اشتراكات نشطة</p>
            <p id="activeSubscriptions" class="text-2xl font-bold text-purple-700 mt-1">0</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-800">الأطباء المعلقين</h3>
        <a href="/admin/dashboard/doctors?status=pending" class="text-blue-600 hover:text-blue-700 text-sm">عرض الكل</a>
    </div>
    <div id="pendingDoctors" class="space-y-4">
        <div class="text-center text-gray-500 py-8">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p>جاري التحميل...</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">آخر حجوزات العيادات</h3>
            <a href="/admin/dashboard/clinic-bookings" class="text-blue-600 hover:text-blue-700 text-sm">عرض الكل</a>
        </div>
        <div id="recentBookings" class="space-y-4">
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p>جاري التحميل...</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">روابط سريعة</h3>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <a href="/admin/dashboard/clinic-bookings" class="p-4 rounded-lg bg-teal-50 text-teal-800 hover:bg-teal-100 transition">
                <i class="fas fa-calendar-check mb-2"></i><br>حجوزات العيادات
            </a>
            <a href="/admin/dashboard/revenue" class="p-4 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100 transition">
                <i class="fas fa-chart-line mb-2"></i><br>الإيرادات
            </a>
            <a href="/admin/dashboard/analytics" class="p-4 rounded-lg bg-blue-50 text-blue-800 hover:bg-blue-100 transition">
                <i class="fas fa-chart-pie mb-2"></i><br>التحليلات
            </a>
            <a href="/admin/dashboard/clinic-patients" class="p-4 rounded-lg bg-green-50 text-green-800 hover:bg-green-100 transition">
                <i class="fas fa-folder-open mb-2"></i><br>ملفات العيادات
            </a>
            <a href="/admin/dashboard/patients" class="p-4 rounded-lg bg-slate-50 text-slate-800 hover:bg-slate-100 transition">
                <i class="fas fa-users mb-2"></i><br>حسابات المرضى
            </a>
            <a href="/admin/dashboard/subscriptions" class="p-4 rounded-lg bg-purple-50 text-purple-800 hover:bg-purple-100 transition">
                <i class="fas fa-crown mb-2"></i><br>الاشتراكات
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.addEventListener('load', async function() {
        await loadDashboardMetrics();
        await loadPendingDoctors();
        await loadRecentBookings();
    });

    async function loadDashboardMetrics() {
        try {
            const data = await apiCall('/admin/api/metrics');
            if (!data.success) return;

            const metrics = data.data;
            const clinic = metrics.clinic || {};
            const subs = metrics.subscriptions || {};

            document.getElementById('totalDoctors').textContent = metrics.doctors?.total || 0;
            document.getElementById('activeDoctorsPercent').textContent = `${metrics.doctors?.active || 0} نشط`;

            document.getElementById('bookingsToday').textContent = clinic.bookings_today || 0;
            document.getElementById('bookingsWaiting').textContent = `${clinic.bookings_waiting || 0} في الدور الآن`;

            document.getElementById('clinicPatientsTotal').textContent = clinic.patients || 0;
            document.getElementById('clinicBranchesTotal').textContent = clinic.branches || 0;
            document.getElementById('clinicStaffTotal').textContent = clinic.staff || 0;

            document.getElementById('clinicRevenueMonth').textContent = formatCurrency(clinic.revenue_month || 0);
            document.getElementById('subscriptionRevenueMonth').textContent = `اشتراكات: ${formatCurrency(subs.monthly || 0)}`;

            document.getElementById('clinicBookingsTotal').textContent = clinic.bookings_total || 0;
            document.getElementById('clinicBookingsCompleted').textContent = clinic.bookings_completed || 0;
            document.getElementById('clinicBookingsCancelled').textContent = clinic.bookings_cancelled || 0;
            document.getElementById('clinicRevenueToday').textContent = formatCurrency(clinic.revenue_today || 0);
            document.getElementById('activeSubscriptions').textContent = subs.active || 0;
        } catch (error) {
            console.error('Error loading metrics:', error);
        }
    }

    async function loadPendingDoctors() {
        try {
            const data = await apiCall('/admin/api/doctors?status=pending&limit=5');
            const container = document.getElementById('pendingDoctors');
            if (!data.success) return;

            if (!data.data.length) {
                container.innerHTML = `<div class="text-center text-gray-500 py-8"><i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i><p>لا يوجد أطباء معلقين</p></div>`;
                return;
            }

            container.innerHTML = data.data.map(doctor => `
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center"><i class="fas fa-user-md text-blue-600"></i></div>
                        <div>
                            <h4 class="font-semibold text-gray-800">${doctor.name || 'غير محدد'}</h4>
                            <p class="text-sm text-gray-500">${doctor.speciality || 'غير محدد'}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="approveDoctor('${doctor.id}')" class="px-4 py-2 bg-green-500 text-white rounded-lg"><i class="fas fa-check"></i></button>
                        <button onclick="rejectDoctor('${doctor.id}')" class="px-4 py-2 bg-red-500 text-white rounded-lg"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading pending doctors:', error);
        }
    }

    async function loadRecentBookings() {
        try {
            const data = await apiCall('/admin/api/clinic-bookings?limit=5');
            const container = document.getElementById('recentBookings');
            if (!data.success) return;

            if (!data.data.length) {
                container.innerHTML = '<p class="text-center text-gray-500 py-8">لا توجد حجوزات</p>';
                return;
            }

            container.innerHTML = data.data.map(b => `
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-800">#${b.booking_number} — ${b.patient_name || 'مريض'}</p>
                        <p class="text-sm text-gray-500">${b.doctor_name || 'طبيب'} · ${b.branch_name || 'فرع'}</p>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-semibold">${b.visit_date || '-'}</p>
                        <span class="text-xs px-2 py-1 rounded-full ${getStatusClass(b.status)}">${b.status_label || b.status}</span>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading bookings:', error);
        }
    }

    async function approveDoctor(doctorId) {
        if (!await confirmAction('هل أنت متأكد من الموافقة على هذا الطبيب؟')) return;
        const data = await apiCall(`/admin/api/doctors/${doctorId}/approve`, { method: 'POST' });
        if (data.success) { alert('تمت الموافقة بنجاح'); loadPendingDoctors(); }
        else alert(data.error?.message || 'فشلت العملية');
    }

    async function rejectDoctor(doctorId) {
        const reason = prompt('سبب الرفض (اختياري):');
        if (reason === null) return;
        const data = await apiCall(`/admin/api/doctors/${doctorId}/reject`, {
            method: 'POST',
            body: JSON.stringify({ reject_reason: reason }),
        });
        if (data.success) { alert('تم الرفض بنجاح'); loadPendingDoctors(); }
        else alert(data.error?.message || 'فشلت العملية');
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('ar-EG', { style: 'decimal', minimumFractionDigits: 0 }).format(amount || 0) + ' ج.م';
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
</script>
@endsection
