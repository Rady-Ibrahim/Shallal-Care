@extends('admin.layout')

@section('title', 'الاشتراكات')
@section('page-title', 'اشتراكات الأطباء')
@section('page-description', 'إدارة خطط الاشتراك واشتراكات الأطباء وإعدادات الدفع')

@section('content')
<!-- Payment Settings -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">إعدادات الدفع</h3>
    <form id="paymentSettingsForm" onsubmit="savePaymentSettings(event)" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">رقم فودافون كاش</label>
            <input type="text" id="vodafone_cash_number" class="w-full px-4 py-2 border rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">اسم البنك</label>
            <input type="text" id="bank_name" class="w-full px-4 py-2 border rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">اسم صاحب الحساب</label>
            <input type="text" id="bank_account_name" class="w-full px-4 py-2 border rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">رقم الحساب البنكي</label>
            <input type="text" id="bank_account_number" class="w-full px-4 py-2 border rounded-lg">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">حفظ إعدادات الدفع</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm text-gray-600">اشتراكات نشطة</p>
        <p class="text-2xl font-bold text-green-600" id="activeCount">0</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm text-gray-600">منتهية</p>
        <p class="text-2xl font-bold text-gray-800" id="expiredCount">0</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm text-gray-600">بانتظار الدفع</p>
        <p class="text-2xl font-bold text-yellow-600" id="pendingCount">0</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm text-gray-600">إجمالي الإيرادات</p>
        <p class="text-2xl font-bold text-blue-600" id="totalRevenue">0</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">خطط الاشتراك</h3>
        <button onclick="openPlanModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
            <i class="fas fa-plus ml-1"></i> إضافة خطة
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="plansGrid">
        <p class="text-gray-500">جاري التحميل...</p>
    </div>
</div>

<div id="planModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl">
        <div class="p-5 border-b flex items-center justify-between">
            <h3 id="planModalTitle" class="text-lg font-bold text-gray-800">إضافة خطة اشتراك</h3>
            <button onclick="closePlanModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="planForm" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4" onsubmit="savePlan(event)">
            <input type="hidden" id="planId">
            <div>
                <label class="text-sm font-semibold text-gray-700">اسم الخطة</label>
                <input id="planName" class="mt-1 w-full border rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">السعر</label>
                <input id="planPrice" type="number" min="0" class="mt-1 w-full border rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">مدة الاشتراك (يوم)</label>
                <input id="planDuration" type="number" min="1" class="mt-1 w-full border rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">حد الحجوزات الشهري (اختياري)</label>
                <input id="planMaxAppointments" type="number" min="1" class="mt-1 w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">ترتيب العرض</label>
                <input id="planSortOrder" type="number" min="0" class="mt-1 w-full border rounded-lg px-3 py-2" value="0">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">الحالة</label>
                <select id="planStatus" class="mt-1 w-full border rounded-lg px-3 py-2">
                    <option value="active">نشطة</option>
                    <option value="inactive">موقفة</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-gray-700">الوصف بالعربي</label>
                <textarea id="planDescriptionAr" rows="2" class="mt-1 w-full border rounded-lg px-3 py-2"></textarea>
            </div>
            <div class="md:col-span-2 flex gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" id="planFeatured"> مميزة
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" id="planAnalytics"> تحليلات
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" id="planBanner"> Banner
                </label>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" onclick="closePlanModal()" class="px-4 py-2 border rounded-lg">إلغاء</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">حفظ</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">اشتراكات الأطباء</h3>
        <select id="statusFilter" onchange="loadSubscriptions()" class="px-3 py-2 border rounded-lg text-sm">
            <option value="">جميع الحالات</option>
            <option value="active">نشط</option>
            <option value="expired">منتهي</option>
            <option value="pending_payment">بانتظار الدفع</option>
            <option value="cancelled">ملغي</option>
        </select>
    </div>
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الطبيب</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الخطة</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">المبلغ</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الإيصال</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">الحالة</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">إجراءات</th>
            </tr>
        </thead>
        <tbody id="subscriptionsTableBody">
            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">جاري التحميل...</td></tr>
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
let plansCache = [];

window.addEventListener('load', () => {
    loadPaymentSettings();
    loadSubscriptions();
    loadPlansCrud();
});

async function loadPaymentSettings() {
    const data = await apiCall('/admin/api/payment-settings');
    if (!data?.success) return;
    document.getElementById('vodafone_cash_number').value = data.data.vodafone_cash_number || '';
    document.getElementById('bank_name').value = data.data.bank_name || '';
    document.getElementById('bank_account_name').value = data.data.bank_account_name || '';
    document.getElementById('bank_account_number').value = data.data.bank_account_number || '';
}

async function savePaymentSettings(e) {
    e.preventDefault();
    const body = {
        vodafone_cash_number: document.getElementById('vodafone_cash_number').value,
        bank_name: document.getElementById('bank_name').value,
        bank_account_name: document.getElementById('bank_account_name').value,
        bank_account_number: document.getElementById('bank_account_number').value,
    };
    const data = await apiCall('/admin/api/payment-settings', { method: 'PUT', body: JSON.stringify(body) });
    if (data?.success) alert(data.message || 'تم الحفظ');
}

async function loadSubscriptions() {
    try {
        showLoading();
        const status = document.getElementById('statusFilter').value;
        const params = new URLSearchParams({ limit: 50 });
        if (status) params.set('status', status);

        const data = await apiCall(`/admin/api/subscriptions?${params}`);
        if (!data?.success) {
            document.getElementById('subscriptionsTableBody').innerHTML =
                '<tr><td colspan="6" class="px-6 py-8 text-center text-red-600">تعذر تحميل البيانات</td></tr>';
            return;
        }

        const { summary, subscriptions } = data.data;
        document.getElementById('activeCount').textContent = summary.active || 0;
        document.getElementById('expiredCount').textContent = summary.expired || 0;
        document.getElementById('pendingCount').textContent = summary.pending_payment || 0;
        document.getElementById('totalRevenue').textContent = formatCurrency(summary.total_revenue || 0);

        renderSubscriptions(subscriptions || []);
    } catch (e) {
        console.error(e);
    } finally {
        hideLoading();
    }
}

function renderPlans(plans) {
    const grid = document.getElementById('plansGrid');
    plansCache = plans || [];
    if (!plans.length) {
        grid.innerHTML = '<p class="text-gray-500">لا توجد خطط</p>';
        return;
    }
    grid.innerHTML = plans.map(plan => `
        <div class="p-4 bg-gray-50 rounded-lg border">
            <p class="font-bold text-gray-800">${plan.name}</p>
            <p class="text-blue-600 font-semibold mt-1">${formatCurrency(plan.price)} / ${plan.duration_days} يوم</p>
            <p class="text-sm text-gray-600 mt-2">${plan.subscribers_count || 0} مشترك</p>
            <div class="mt-3 flex gap-2">
                <button onclick="editPlanById(${plan.id})" class="px-3 py-1 text-xs bg-indigo-100 text-indigo-700 rounded">تعديل</button>
                <button onclick="deletePlan(${plan.id})" class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded">حذف</button>
            </div>
        </div>
    `).join('');
}

async function loadPlansCrud() {
    const res = await apiCall('/admin/api/subscriptions/plans');
    if (res?.success) renderPlans(res.data || []);
}

function openPlanModal() {
    document.getElementById('planModalTitle').textContent = 'إضافة خطة اشتراك';
    document.getElementById('planForm').reset();
    document.getElementById('planId').value = '';
    document.getElementById('planModal').classList.remove('hidden');
    document.getElementById('planModal').classList.add('flex');
}

function closePlanModal() {
    document.getElementById('planModal').classList.add('hidden');
    document.getElementById('planModal').classList.remove('flex');
}

function editPlanById(id) {
    const plan = plansCache.find((item) => String(item.id) === String(id));
    if (!plan) return;

    document.getElementById('planModalTitle').textContent = 'تعديل خطة الاشتراك';
    document.getElementById('planId').value = plan.id || '';
    document.getElementById('planName').value = plan.name || '';
    document.getElementById('planPrice').value = plan.price || 0;
    document.getElementById('planDuration').value = plan.duration_days || 30;
    document.getElementById('planMaxAppointments').value = plan.max_appointments || '';
    document.getElementById('planDescriptionAr').value = plan.description_ar || '';
    document.getElementById('planSortOrder').value = plan.sort_order || 0;
    document.getElementById('planStatus').value = plan.status || 'active';
    document.getElementById('planFeatured').checked = !!plan.is_featured;
    document.getElementById('planAnalytics').checked = !!plan.has_analytics;
    document.getElementById('planBanner').checked = !!plan.has_banner;
    document.getElementById('planModal').classList.remove('hidden');
    document.getElementById('planModal').classList.add('flex');
}

async function savePlan(e) {
    e.preventDefault();
    const id = document.getElementById('planId').value;
    const payload = {
        name: document.getElementById('planName').value,
        price: Number(document.getElementById('planPrice').value),
        duration_days: Number(document.getElementById('planDuration').value),
        max_appointments: document.getElementById('planMaxAppointments').value ? Number(document.getElementById('planMaxAppointments').value) : null,
        description_ar: document.getElementById('planDescriptionAr').value,
        sort_order: Number(document.getElementById('planSortOrder').value || 0),
        status: document.getElementById('planStatus').value,
        is_featured: document.getElementById('planFeatured').checked,
        has_analytics: document.getElementById('planAnalytics').checked,
        has_banner: document.getElementById('planBanner').checked,
    };

    const endpoint = id ? `/admin/api/subscriptions/plans/${id}` : '/admin/api/subscriptions/plans';
    const method = id ? 'PUT' : 'POST';
    const res = await apiCall(endpoint, { method, body: JSON.stringify(payload) });
    if (res?.success) {
        alert(res.message || 'تم حفظ الخطة');
        closePlanModal();
        await loadPlansCrud();
    }
}

async function deletePlan(id) {
    if (!await confirmAction('هل أنت متأكد من حذف هذه الخطة؟')) return;
    const res = await apiCall(`/admin/api/subscriptions/plans/${id}`, { method: 'DELETE' });
    if (res?.success) {
        alert(res.message || 'تم حذف الخطة');
        await loadPlansCrud();
    }
}

function renderSubscriptions(items) {
    const tbody = document.getElementById('subscriptionsTableBody');
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">لا توجد اشتراكات</td></tr>';
        return;
    }
    tbody.innerHTML = items.map(sub => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold">${sub.doctor_name || '-'}</td>
            <td class="px-6 py-4">${sub.plan_name || '-'}</td>
            <td class="px-6 py-4">${formatCurrency(sub.submitted_amount || sub.amount_paid || 0)}</td>
            <td class="px-6 py-4">${sub.payment_receipt ? `<a href="${sub.payment_receipt}" target="_blank" class="text-blue-600 text-sm">عرض الإيصال</a>` : '-'}</td>
            <td class="px-6 py-4"><span class="px-2 py-1 rounded-full text-xs ${statusClass(sub.status)}">${statusText(sub.status)}</span></td>
            <td class="px-6 py-4">
                ${sub.status === 'pending_payment' ? `
                    <button onclick="confirmSub(${sub.id})" class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg ml-1">تأكيد</button>
                    <button onclick="rejectSub(${sub.id})" class="px-3 py-1 bg-red-600 text-white text-xs rounded-lg">رفض</button>
                ` : '-'}
            </td>
        </tr>
    `).join('');
}

async function confirmSub(id) {
    if (!await confirmAction('تأكيد هذا الاشتراك؟')) return;
    const data = await apiCall(`/admin/api/subscriptions/${id}/confirm`, { method: 'POST', body: '{}' });
    if (data?.success) { alert(data.message); loadSubscriptions(); }
}

async function rejectSub(id) {
    const reason = prompt('سبب الرفض (اختياري):');
    if (reason === null) return;
    const data = await apiCall(`/admin/api/subscriptions/${id}/reject`, { method: 'POST', body: JSON.stringify({ reason }) });
    if (data?.success) { alert(data.message); loadSubscriptions(); }
}

function statusClass(s) {
    return ({ active: 'bg-green-100 text-green-800', expired: 'bg-gray-100 text-gray-800', pending_payment: 'bg-yellow-100 text-yellow-800', cancelled: 'bg-red-100 text-red-800' })[s] || 'bg-gray-100';
}
function statusText(s) {
    return ({ active: 'نشط', expired: 'منتهي', pending_payment: 'بانتظار الدفع', cancelled: 'ملغي' })[s] || s;
}
function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-EG', { style: 'decimal', minimumFractionDigits: 0 }).format(amount || 0) + ' ج.م';
}
</script>
@endsection
