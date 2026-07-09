@extends('doctor.layout')

@section('title', 'خزنة الفرع')
@section('page-title', 'خزنة الفرع')
@section('page-description', 'الإيرادات والمصروفات والتقرير اليومي')

@section('content')
<div class="flex flex-wrap gap-3 items-end justify-between mb-6">
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-1">تاريخ التقرير</label>
    <input type="date" id="reportDate" value="{{ today()->format('Y-m-d') }}"
      class="border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
  </div>
  @if($canManageFinance)
  <button type="button" id="openExpenseModal"
    class="px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold">
    + تسجيل مصروف
  </button>
  @endif
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <p class="text-xs text-slate-500">إجمالي الإيرادات</p>
    <p class="text-2xl font-bold text-green-600" id="sumIncome">—</p>
  </div>
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <p class="text-xs text-slate-500">إجمالي المصروفات</p>
    <p class="text-2xl font-bold text-red-600" id="sumExpense">—</p>
  </div>
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <p class="text-xs text-slate-500">صافي اليوم</p>
    <p class="text-2xl font-bold text-blue-700" id="sumNet">—</p>
  </div>
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <p class="text-xs text-slate-500">عدد الكشوف المحصّلة</p>
    <p class="text-2xl font-bold text-slate-800" id="sumConsultations">—</p>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-6">
  <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6">
    <div class="flex flex-wrap gap-2 justify-between items-center mb-4">
      <h2 class="font-bold text-slate-800">حركة الخزنة</h2>
      <div class="flex gap-2">
        <button type="button" data-type="all" class="type-filter px-3 py-1 rounded-full text-sm border">الكل</button>
        <button type="button" data-type="income" class="type-filter px-3 py-1 rounded-full text-sm border">إيرادات</button>
        <button type="button" data-type="expense" class="type-filter px-3 py-1 rounded-full text-sm border">مصروفات</button>
      </div>
    </div>
    <div id="transactionsList" class="space-y-2">
      <p class="text-center text-slate-500 py-8">جاري التحميل...</p>
    </div>
  </div>

  <div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
      <h2 class="font-bold text-slate-800 mb-3">حسب التصنيف</h2>
      <div id="categoryBreakdown" class="space-y-2 text-sm">
        <p class="text-slate-400">—</p>
      </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-6">
      <h2 class="font-bold text-slate-800 mb-3">حسب طريقة الدفع</h2>
      <div id="paymentBreakdown" class="space-y-2 text-sm">
        <p class="text-slate-400">—</p>
      </div>
    </div>
  </div>
</div>

@if($canManageFinance)
<div id="expenseModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-xl">
    <h3 class="text-lg font-bold text-slate-800 mb-4">تسجيل مصروف جديد</h3>
    <form id="expenseForm" class="space-y-3">
      <div>
        <label class="block text-sm font-semibold mb-1">نوع المصروف *</label>
        <select name="category" required class="w-full border rounded-lg px-3 py-2">
          @foreach($expenseCategories as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">المبلغ ({{ config('clinic.currency_symbol') }}) *</label>
        <input name="amount" type="number" min="0.01" step="0.01" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">طريقة الدفع</label>
        <select name="payment_method" class="w-full border rounded-lg px-3 py-2">
          @foreach($paymentMethods as $key => $label)
          <option value="{{ $key }}" @selected($key === 'cash')>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">ملاحظات</label>
        <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
      </div>
      <div class="flex gap-2 justify-end pt-2">
        <button type="button" id="closeExpenseModal" class="px-4 py-2 rounded-lg bg-slate-100">إلغاء</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold">حفظ المصروف</button>
      </div>
    </form>
  </div>
</div>
@endif
@endsection

@section('scripts')
<script>
const currency = @json(config('clinic.currency_symbol'));
let currentType = 'all';

function fmt(n) {
  return Number(n || 0).toLocaleString('ar-EG', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ' + currency;
}

function selectedDate() {
  return document.getElementById('reportDate').value;
}

async function loadSummary() {
  const res = await apiCall('/doctor/api/finance/summary?date=' + selectedDate());
  if (!res?.success) return;
  const d = res.data;
  document.getElementById('sumIncome').textContent = fmt(d.income);
  document.getElementById('sumExpense').textContent = fmt(d.expense);
  document.getElementById('sumNet').textContent = fmt(d.net);
  document.getElementById('sumConsultations').textContent = d.consultations_count ?? 0;

  const catBox = document.getElementById('categoryBreakdown');
  if (!d.by_category?.length) {
    catBox.innerHTML = '<p class="text-slate-400">لا توجد بيانات</p>';
  } else {
    catBox.innerHTML = d.by_category.map(c => `
      <div class="flex justify-between border-b border-slate-100 pb-2">
        <span class="${c.type === 'income' ? 'text-green-700' : 'text-red-700'}">${c.category_label}</span>
        <span class="font-semibold">${fmt(c.total)}</span>
      </div>
    `).join('');
  }

  const payBox = document.getElementById('paymentBreakdown');
  if (!d.by_payment_method?.length) {
    payBox.innerHTML = '<p class="text-slate-400">لا توجد بيانات</p>';
  } else {
    payBox.innerHTML = d.by_payment_method.map(p => `
      <div class="border-b border-slate-100 pb-2">
        <p class="font-semibold text-slate-700">${p.payment_method_label}</p>
        <p class="text-green-600 text-xs">إيراد: ${fmt(p.income)}</p>
        <p class="text-red-600 text-xs">مصروف: ${fmt(p.expense)}</p>
      </div>
    `).join('');
  }
}

async function loadTransactions() {
  const params = new URLSearchParams({ date: selectedDate() });
  if (currentType !== 'all') params.set('type', currentType);
  const res = await apiCall('/doctor/api/finance/transactions?' + params.toString());
  const box = document.getElementById('transactionsList');
  const items = res?.data || [];
  if (!items.length) {
    box.innerHTML = '<p class="text-center text-slate-500 py-8">لا توجد حركات في هذا اليوم</p>';
    return;
  }
  box.innerHTML = items.map(t => `
    <div class="flex flex-wrap justify-between gap-2 border border-slate-100 rounded-lg p-3">
      <div>
        <div class="flex items-center gap-2">
          <span class="text-xs px-2 py-0.5 rounded-full ${t.type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${t.type_label}</span>
          <span class="font-semibold text-slate-800">${t.category_label}</span>
          ${t.booking_number ? `<span class="text-xs font-mono text-blue-600">${t.booking_number}</span>` : ''}
        </div>
        <p class="text-xs text-slate-500 mt-1">${t.payment_method_label} · ${t.recorded_by || '—'} · ${t.created_at}</p>
        ${t.notes ? `<p class="text-xs text-slate-600 mt-1">${t.notes}</p>` : ''}
      </div>
      <p class="font-bold ${t.type === 'income' ? 'text-green-600' : 'text-red-600'}">${t.type === 'income' ? '+' : '-'}${fmt(t.amount)}</p>
    </div>
  `).join('');
}

async function refreshAll() {
  await Promise.all([loadSummary(), loadTransactions()]);
}

document.getElementById('reportDate').addEventListener('change', refreshAll);

document.querySelectorAll('.type-filter').forEach(btn => {
  btn.addEventListener('click', () => {
    currentType = btn.dataset.type;
    document.querySelectorAll('.type-filter').forEach(b => b.classList.remove('bg-blue-100', 'border-blue-300'));
    btn.classList.add('bg-blue-100', 'border-blue-300');
    loadTransactions();
  });
});

@if($canManageFinance)
document.getElementById('openExpenseModal')?.addEventListener('click', () => {
  document.getElementById('expenseForm').reset();
  document.getElementById('expenseModal').classList.remove('hidden');
});
document.getElementById('closeExpenseModal')?.addEventListener('click', () => {
  document.getElementById('expenseModal').classList.add('hidden');
});
document.getElementById('expenseForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target).entries());
  const res = await apiCall('/doctor/api/finance/expenses', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
  if (res?.success) {
    document.getElementById('expenseModal').classList.add('hidden');
    showSuccess('تم تسجيل المصروف');
    refreshAll();
  }
});
@endif

window.addEventListener('load', () => {
  document.querySelector('.type-filter[data-type="all"]')?.classList.add('bg-blue-100', 'border-blue-300');
  refreshAll();
});
</script>
@endsection
