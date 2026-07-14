@extends('doctor.layout')

@section('title', 'باقات الاشتراك')
@section('page-title', 'باقات الاشتراك')
@section('page-description', 'اختر الباقة المناسبة وارفع إيصال الدفع')

@section('content')
<div id="subscriptionStatus" class="mb-6 hidden"></div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="plansGrid">
    <p class="text-gray-500 col-span-3">جاري التحميل...</p>
</div>

<div id="subscribeModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800" id="modalPlanName">الاشتراك في الباقة</h3>
            <button onclick="closeSubscribeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="subscribeForm" onsubmit="submitSubscription(event)" class="p-6 space-y-5">
            <input type="hidden" id="selectedPlanId">
            <input type="hidden" id="selectedPlanPrice">

            <div class="bg-teal-50 border border-teal-200 rounded-lg p-4">
                <p class="text-sm text-teal-800 font-semibold mb-2">معلومات الدفع</p>
                <div class="space-y-2 text-sm text-gray-700" id="paymentInfo">
                    <p>جاري التحميل...</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">طريقة الدفع</label>
                <select id="paymentMethod" required class="w-full px-4 py-2 border rounded-lg">
                    <option value="vodafone_cash">فودافون كاش</option>
                    <option value="bank_transfer">تحويل بنكي</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">المبلغ المُحوّل ({{ config('clinic.currency_symbol') }})</label>
                <input type="number" id="submittedAmount" required readonly
                    class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                <p class="text-xs text-gray-500 mt-1">يجب أن يساوي سعر الباقة بالضبط</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">إيصال الدفع</label>
                <input type="file" id="paymentReceipt" accept=".jpg,.jpeg,.png,.pdf" required
                    class="w-full px-4 py-2 border rounded-lg">
            </div>

            <button type="submit" class="w-full bg-teal-600 text-white py-3 rounded-lg font-semibold hover:bg-teal-700">
                إرسال طلب الاشتراك
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let paymentSettings = {};

window.addEventListener('load', async () => {
    await Promise.all([loadSubscriptionStatus(), loadPlans(), loadPaymentSettings()]);
});

async function loadSubscriptionStatus() {
    const data = await apiCall('/doctor/api/subscription');
    const box = document.getElementById('subscriptionStatus');
    if (!data?.success || !data.data) return;

    box.classList.remove('hidden');
    const s = data.data;
    const colors = {
        active: 'bg-green-50 border-green-200 text-green-800',
        pending_payment: 'bg-yellow-50 border-yellow-200 text-yellow-800',
    };
    box.className = `mb-6 p-4 rounded-lg border ${colors[s.status] || 'bg-gray-50 border-gray-200'}`;
    box.innerHTML = s.status === 'pending_payment'
        ? `<p class="font-semibold">طلب اشتراكك في باقة "${s.plan_name}" قيد المراجعة من الإدارة.</p>`
        : `<p class="font-semibold">اشتراكك النشط: ${s.plan_name} — ينتهي في ${s.end_date} (${s.days_remaining} يوم متبقي)</p>`;
}

async function loadPlans() {
    const data = await apiCall('/doctor/api/subscription/plans');
    const grid = document.getElementById('plansGrid');
    if (!data?.success) {
        grid.innerHTML = '<p class="text-red-600 col-span-3">تعذر تحميل الباقات</p>';
        return;
    }

    grid.innerHTML = data.data.map(plan => `
        <div class="bg-white rounded-xl shadow-sm p-6 border ${plan.is_featured ? 'border-amber-400 ring-2 ring-amber-100' : ''}">
            ${plan.is_featured ? '<span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full">مميزة</span>' : ''}
            <h3 class="text-xl font-bold text-gray-800 mt-2">${plan.name}</h3>
            <p class="text-2xl font-bold text-teal-600 my-3">${formatCurrency(plan.price)}</p>
            <p class="text-sm text-gray-500 mb-4">${plan.duration_days} يوم</p>
            <p class="text-sm text-gray-600 mb-4">${plan.description_ar || ''}</p>
            <button onclick="openSubscribeModal(${plan.id}, '${plan.name}', ${plan.price})"
                class="w-full py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 ${plan.price <= 0 ? 'opacity-50 cursor-not-allowed' : ''}"
                ${plan.price <= 0 ? 'disabled' : ''}>
                ${plan.price <= 0 ? 'مجانية' : 'اشترك الآن'}
            </button>
        </div>
    `).join('');
}

async function loadPaymentSettings() {
    const data = await apiCall('/doctor/api/payment-settings');
    if (data?.success) paymentSettings = data.data;
}

function openSubscribeModal(id, name, price) {
    document.getElementById('selectedPlanId').value = id;
    document.getElementById('selectedPlanPrice').value = price;
    document.getElementById('modalPlanName').textContent = 'الاشتراك في ' + name;
    document.getElementById('submittedAmount').value = price;

    const info = document.getElementById('paymentInfo');
    const hasBank = paymentSettings.bank_name && paymentSettings.bank_account_number;
    info.innerHTML = `
        <p><strong>فودافون كاش:</strong> ${paymentSettings.vodafone_cash_number || 'غير مُعرَّف'}
            ${paymentSettings.vodafone_cash_number ? `<button type="button" onclick="copyText('${paymentSettings.vodafone_cash_number}')" class="text-teal-600 text-xs mr-2">نسخ</button>` : ''}</p>
        ${hasBank ? `
        <p><strong>البنك:</strong> ${paymentSettings.bank_name}</p>
        <p><strong>اسم الحساب:</strong> ${paymentSettings.bank_account_name || '-'}</p>
        <p><strong>رقم الحساب:</strong> ${paymentSettings.bank_account_number}
            <button type="button" onclick="copyText('${paymentSettings.bank_account_number}')" class="text-teal-600 text-xs mr-2">نسخ</button></p>
        ` : `<p class="text-amber-700 text-xs mt-2">بيانات التحويل البنكي تُضبط من لوحة الإدارة → الاشتراكات → إعدادات الدفع</p>`}
    `;

    document.getElementById('subscribeModal').classList.remove('hidden');
    document.getElementById('subscribeModal').classList.add('flex');
}

function closeSubscribeModal() {
    document.getElementById('subscribeModal').classList.add('hidden');
    document.getElementById('subscribeModal').classList.remove('flex');
}

async function submitSubscription(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('subscription_id', document.getElementById('selectedPlanId').value);
    formData.append('submitted_amount', document.getElementById('submittedAmount').value);
    formData.append('payment_method', document.getElementById('paymentMethod').value);
    formData.append('payment_receipt', document.getElementById('paymentReceipt').files[0]);

    try {
        showLoading();
        const data = await apiUpload('/doctor/api/subscription/subscribe', formData);
        if (!data) return;

        if (data.success) {
            alert(data.message || 'تم إرسال الطلب');
            closeSubscribeModal();
            await loadSubscriptionStatus();
        } else {
            alert(data.error?.message || data.message || 'حدث خطأ');
        }
    } catch (err) {
        console.error(err);
        alert('حدث خطأ أثناء الإرسال');
    } finally {
        hideLoading();
    }
}

function copyText(text) {
    if (!text) return;
    navigator.clipboard.writeText(text);
    alert('تم النسخ');
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-EG', { style: 'decimal', minimumFractionDigits: 0 }).format(amount || 0) + ' {{ config('clinic.currency_symbol') }}';
}
</script>
@endsection
