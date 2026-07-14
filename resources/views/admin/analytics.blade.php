@extends('admin.layout')

@section('title', 'التحليلات')
@section('page-title', 'التحليلات')
@section('page-description', 'إحصائيات متقدمة وتحليلات النظام')

@section('content')
<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">الفترة</label>
            <select id="periodFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="today">اليوم</option>
                <option value="week" selected>هذا الأسبوع</option>
                <option value="month">هذا الشهر</option>
                <option value="year">هذه السنة</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">النوع</label>
            <select id="typeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">جميع الأنواع</option>
                <option value="doctors">الأطباء</option>
                <option value="patients">المرضى</option>
                <option value="bookings">الحجوزات</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">&nbsp;</label>
            <button onclick="applyFilters()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-filter ml-2"></i>تصفية
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">إجمالي المستخدمين</p>
                <p class="text-2xl font-bold text-gray-800" id="totalUsers">0</p>
                <p class="text-xs text-gray-500 mt-1"><span id="clinicPatientsCount">0</span> مريض عيادة</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">الأطباء النشطين</p>
                <p class="text-2xl font-bold text-gray-800" id="activeDoctors">0</p>
                <p class="text-xs text-gray-500 mt-1"><span id="clinicBranchesCount">0</span> فرع عيادة</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-md text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">حجوزات اليوم</p>
                <p class="text-2xl font-bold text-gray-800" id="dailyBookings">0</p>
                <p class="text-xs text-gray-500 mt-1">زيارات العيادات</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-check text-purple-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">معدل الحجز</p>
                <p class="text-2xl font-bold text-gray-800" id="conversionRate">0%</p>
                <p class="text-xs text-gray-500 mt-1">حجوزات / مرضى العيادات</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-percentage text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- User Growth Chart -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">نمو المستخدمين</h3>
        <div class="h-64 flex items-center justify-center" id="userGrowthChart">
            <p class="text-gray-500">سيتم عرض الرسم البياني هنا</p>
        </div>
    </div>

    <!-- Appointments Chart -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">الحجوزات حسب الحالة</h3>
        <div class="h-64 flex items-center justify-center" id="bookingsChart">
            <p class="text-gray-500">سيتم عرض الرسم البياني هنا</p>
        </div>
    </div>
</div>

<!-- Detailed Analytics -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Top Specialities -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">أكثر التخصصات طلباً</h3>
        <div class="space-y-4" id="topSpecialities">
            <p class="text-gray-500">جاري التحميل...</p>
        </div>
    </div>

    <!-- User Demographics -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">التوزيع الديموغرافي</h3>
        <div class="space-y-4" id="demographics">
            <p class="text-gray-500">جاري التحميل...</p>
        </div>
    </div>

    <!-- Peak Hours -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">ساعات الذروة</h3>
        <div class="space-y-4" id="peakHours">
            <p class="text-gray-500">جاري التحميل...</p>
        </div>
    </div>
</div>

<!-- Geographic Distribution -->
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">التوزيع الجغرافي</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" id="geographicDistribution">
        <p class="text-gray-500">جاري التحميل...</p>
    </div>
</div>

@endsection

@section('scripts')
<script>
window.addEventListener('load', async function() {
    await loadAnalyticsData();
});

async function loadAnalyticsData() {
    try {
        showLoading();
        
        const period = document.getElementById('periodFilter').value;
        const type = document.getElementById('typeFilter').value;
        
        const params = new URLSearchParams({
            period,
            type,
        });

        const data = await apiCall(`/admin/api/analytics?${params}`);
        
        if (data.success) {
            renderAnalyticsData(data.data);
        } else {
            alert(data.error?.message || 'فشل تحميل البيانات');
        }
    } catch (error) {
        console.error('Error loading analytics data:', error);
        alert('حدث خطأ أثناء تحميل البيانات');
    } finally {
        hideLoading();
    }
}

function renderAnalyticsData(data) {
    document.getElementById('totalUsers').textContent = data.total_users || 0;
    document.getElementById('clinicPatientsCount').textContent = data.clinic_patients || 0;
    document.getElementById('activeDoctors').textContent = data.active_doctors || 0;
    document.getElementById('clinicBranchesCount').textContent = data.clinic_branches || 0;
    document.getElementById('dailyBookings').textContent = data.daily_bookings || data.daily_appointments || 0;
    document.getElementById('conversionRate').textContent = (data.conversion_rate || 0) + '%';

    renderTopSpecialities(data.top_specialities || []);
    renderDemographics(data.demographics || {});
    renderPeakHours(data.peak_hours || []);
    renderGeographicDistribution(data.geographic_distribution || []);
    renderTimeSeriesChart('userGrowthChart', data.users || [], 'count', 'مستخدم جديد');
    renderTimeSeriesChart('bookingsChart', data.bookings || [], 'count', 'حجز');
}

function renderTimeSeriesChart(containerId, series, valueKey, unitLabel) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const items = Array.isArray(series) ? series : [];
    if (!items.length) {
        container.innerHTML = '<p class="text-gray-500 text-sm">لا توجد بيانات للفترة المحددة</p>';
        return;
    }

    const max = Math.max(...items.map(item => Number(item[valueKey] || item.total || 0)), 1);
    const showLabels = items.length <= 14;

    container.innerHTML = `
        <div class="w-full h-full flex items-end gap-1 px-2 pb-2">
            ${items.map(item => {
                const value = Number(item[valueKey] || item.total || 0);
                const height = Math.max(4, Math.round((value / max) * 100));
                const label = item.date ? new Date(item.date).toLocaleDateString('ar-EG', { day: 'numeric', month: 'short' }) : '';
                return `
                    <div class="flex-1 min-w-0 flex flex-col items-center justify-end h-full group" title="${label}: ${value} ${unitLabel}">
                        <div class="w-full max-w-[28px] rounded-t bg-gradient-to-t from-indigo-600 to-blue-400 group-hover:from-indigo-700" style="height: ${height}%"></div>
                        ${showLabels ? `<span class="text-[10px] text-gray-500 mt-1 truncate w-full text-center">${label}</span>` : ''}
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function renderTopSpecialities(specialities) {
    const container = document.getElementById('topSpecialities');
    
    if (specialities.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد بيانات</p>';
        return;
    }

    container.innerHTML = specialities.map((spec, index) => `
        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="font-semibold text-blue-600">${index + 1}</span>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-800">${spec.name || 'غير محدد'}</p>
                <p class="text-sm text-gray-600">${spec.count || 0} طبيب</p>
            </div>
            <div class="text-left">
                <p class="font-semibold text-gray-800">${spec.percentage || 0}%</p>
            </div>
        </div>
    `).join('');
}

function renderDemographics(demographics) {
    const container = document.getElementById('demographics');
    
    if (!demographics || Object.keys(demographics).length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد بيانات</p>';
        return;
    }

    const values = Object.values(demographics).map(v => Number(v) || 0);
    const max = Math.max(...values, 1);

    container.innerHTML = Object.entries(demographics).map(([key, value]) => `
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-700">${getDemographicLabel(key)}</span>
                <span class="text-sm font-semibold text-gray-800">${value || 0}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.round(((Number(value) || 0) / max) * 100)}%"></div>
            </div>
        </div>
    `).join('');
}

function renderPeakHours(hours) {
    const container = document.getElementById('peakHours');
    
    if (hours.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد بيانات</p>';
        return;
    }

    container.innerHTML = hours.map((hour, index) => `
        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
            <div class="flex-1">
                <p class="font-semibold text-gray-800">${hour.time_range}</p>
                <p class="text-sm text-gray-600">${hour.count || 0} حجز</p>
            </div>
            <div class="text-left">
                <p class="font-semibold text-gray-800">${hour.percentage || 0}%</p>
            </div>
        </div>
    `).join('');
}

function renderGeographicDistribution(locations) {
    const container = document.getElementById('geographicDistribution');
    
    if (locations.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد بيانات</p>';
        return;
    }

    container.innerHTML = locations.map(location => `
        <div class="p-4 bg-gray-50 rounded-lg">
            <p class="font-semibold text-gray-800">${location.name || 'غير محدد'}</p>
            <p class="text-2xl font-bold text-blue-600 mt-2">${location.count || 0}</p>
            <p class="text-sm text-gray-600">${location.percentage || 0}%</p>
        </div>
    `).join('');
}

function getDemographicLabel(key) {
    const labels = {
        'male': 'ذكور',
        'female': 'إناث',
        'age_18_25': '18-25',
        'age_26_35': '26-35',
        'age_36_45': '36-45',
        'age_46_plus': '46+',
    };
    return labels[key] || key;
}

function applyFilters() {
    loadAnalyticsData();
}
</script>
@endsection
