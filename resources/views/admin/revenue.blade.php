@extends('admin.layout')

@section('title', 'الإيرادات')
@section('page-title', 'الإيرادات')
@section('page-description', 'إحصائيات الإيرادات والتقارير المالية')

@section('content')
<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">الفترة</label>
            <select id="periodFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="today">اليوم</option>
                <option value="week" selected>هذا الأسبوع</option>
                <option value="month">هذا الشهر</option>
                <option value="year">هذه السنة</option>
                <option value="custom">مخصص</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">من تاريخ</label>
            <input type="date" id="dateFrom"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">إلى تاريخ</label>
            <input type="date" id="dateTo"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">&nbsp;</label>
            <button onclick="applyFilters()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-filter ml-2"></i>تصفية
            </button>
        </div>
    </div>
</div>

<!-- Revenue Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">إجمالي الإيرادات</p>
                <p class="text-2xl font-bold text-gray-800" id="totalRevenue">0</p>
                <p class="text-xs text-gray-500 mt-1" id="clinicNetHint">-</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-dollar-sign text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">زيارات مكتملة</p>
                <p class="text-2xl font-bold text-gray-800" id="completedVisits">0</p>
                <p class="text-xs text-gray-500 mt-1">من حجوزات العيادات</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">إيراد العيادات</p>
                <p class="text-2xl font-bold text-gray-800" id="clinicIncome">0</p>
                <p class="text-xs text-gray-500 mt-1" id="clinicExpenseHint">-</p>
            </div>
            <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clinic-medical text-teal-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">اشتراكات الأطباء</p>
                <p class="text-2xl font-bold text-gray-800" id="subscriptionRevenue">0</p>
                <p class="text-xs text-gray-500 mt-1">متوسط: <span id="averageRevenue">0</span></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-crown text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">تطور الإيرادات</h3>
    <div class="h-64 flex items-center justify-center" id="revenueChart">
        <p class="text-gray-500">سيتم عرض الرسم البياني هنا</p>
    </div>
</div>

<!-- Revenue Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Revenue by Category -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">الإيرادات حسب الفئة</h3>
        <div class="space-y-4" id="revenueByCategory">
            <p class="text-gray-500">جاري التحميل...</p>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">أعلى الأطباء أداءً</h3>
        <div class="space-y-4" id="topPerformers">
            <p class="text-gray-500">جاري التحميل...</p>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-xl shadow-sm p-6 mt-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">المعاملات الأخيرة</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">التاريخ</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">النوع</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الوصف</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">المبلغ</th>
                </tr>
            </thead>
            <tbody id="transactionsTableBody">
                <tr class="text-center py-8">
                    <td colspan="4" class="py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                        <p class="text-gray-500">جاري التحميل...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
window.addEventListener('load', async function() {
    await loadRevenueData();
});

async function loadRevenueData() {
    try {
        showLoading();
        
        const period = document.getElementById('periodFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        const params = new URLSearchParams({ period });
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);

        const data = await apiCall(`/admin/api/revenue?${params}`);
        
        if (data.success) {
            renderRevenueData(data.data);
        } else {
            alert(data.error?.message || 'فشل تحميل البيانات');
        }
    } catch (error) {
        console.error('Error loading revenue data:', error);
        alert('حدث خطأ أثناء تحميل البيانات');
    } finally {
        hideLoading();
    }
}

function renderRevenueData(data) {
    document.getElementById('totalRevenue').textContent = formatCurrency(data.total_revenue || 0);
    document.getElementById('clinicNetHint').textContent = `صافي العيادات: ${formatCurrency(data.clinic_net || 0)}`;
    document.getElementById('completedVisits').textContent = data.completed_visits || data.completed_appointments || 0;
    document.getElementById('clinicIncome').textContent = formatCurrency(data.clinic_income || 0);
    document.getElementById('clinicExpenseHint').textContent = `مصروفات: ${formatCurrency(data.clinic_expense || 0)}`;
    document.getElementById('subscriptionRevenue').textContent = formatCurrency(data.subscription_revenue || 0);
    document.getElementById('averageRevenue').textContent = formatCurrency(data.average_revenue || 0);

    renderRevenueByCategory(data.revenue_by_category || []);
    renderTopPerformers(data.top_performers || []);
    renderTransactions(data.recent_transactions || []);
    renderRevenueChart(data.daily_revenue || []);
}

function renderRevenueChart(days) {
    const container = document.getElementById('revenueChart');

    if (!days.length) {
        container.innerHTML = '<p class="text-gray-500">لا توجد بيانات في هذه الفترة</p>';
        return;
    }

    const max = Math.max(...days.map(d => d.total || 0), 1);
    const showLabels = days.length <= 14;

    container.innerHTML = `
        <div class="w-full h-full flex items-end gap-1 px-2 pb-2">
            ${days.map(day => {
                const height = Math.max(4, Math.round(((day.total || 0) / max) * 100));
                const label = new Date(day.date).toLocaleDateString('ar-EG', { day: 'numeric', month: 'short' });
                return `
                    <div class="flex-1 min-w-0 flex flex-col items-center justify-end h-full group" title="${label}: ${formatCurrency(day.total || 0)}">
                        <div class="w-full max-w-[28px] rounded-t bg-gradient-to-t from-blue-600 to-indigo-400 transition-all group-hover:from-blue-700" style="height: ${height}%"></div>
                        ${showLabels ? `<span class="text-[10px] text-gray-500 mt-1 truncate w-full text-center">${label}</span>` : ''}
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function renderRevenueByCategory(categories) {
    const container = document.getElementById('revenueByCategory');
    
    if (categories.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد بيانات</p>';
        return;
    }

    container.innerHTML = categories.map((category, index) => `
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-700">${category.name}</span>
                <span class="text-sm font-semibold text-gray-800">${formatCurrency(category.amount)}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: ${category.percentage}%"></div>
            </div>
        </div>
    `).join('');
}

function renderTopPerformers(performers) {
    const container = document.getElementById('topPerformers');
    
    if (performers.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد بيانات</p>';
        return;
    }

    container.innerHTML = performers.map((performer, index) => `
        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="font-semibold text-blue-600">${index + 1}</span>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-800">${performer.name || 'غير محدد'}</p>
                <p class="text-sm text-gray-600">${performer.visits || performer.appointments || 0} زيارة</p>
            </div>
            <div class="text-left">
                <p class="font-semibold text-gray-800">${formatCurrency(performer.revenue)}</p>
            </div>
        </div>
    `).join('');
}

function renderTransactions(transactions) {
    const tbody = document.getElementById('transactionsTableBody');
    
    if (transactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                    لا توجد معاملات
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = transactions.map(transaction => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-6 py-4 text-gray-700">${formatDate(transaction.date)}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getTransactionTypeClass(transaction.type)}">
                    ${getTransactionTypeText(transaction.type)}
                </span>
            </td>
            <td class="px-6 py-4 text-gray-700">${transaction.description || '-'}</td>
            <td class="px-6 py-4 font-semibold ${transaction.amount >= 0 ? 'text-green-600' : 'text-red-600'}">
                ${transaction.amount >= 0 ? '+' : ''}${formatCurrency(transaction.amount)}
            </td>
        </tr>
    `).join('');
}

function applyFilters() {
    loadRevenueData();
}

function getTransactionTypeClass(type) {
    const classes = {
        clinic_income: 'bg-teal-100 text-teal-800',
        appointment: 'bg-blue-100 text-blue-800',
        subscription: 'bg-purple-100 text-purple-800',
        refund: 'bg-red-100 text-red-800',
        commission: 'bg-orange-100 text-orange-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
}

function getTransactionTypeText(type) {
    const texts = {
        clinic_income: 'إيراد عيادة',
        appointment: 'زيارة',
        subscription: 'اشتراك',
        refund: 'استرداد',
        commission: 'عمولة',
    };
    return texts[type] || type;
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
