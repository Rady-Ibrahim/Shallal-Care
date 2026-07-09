@extends('doctor.layout')

@section('title', 'التحاليل والأشعة')
@section('page-title', 'التحاليل والأشعة')
@section('page-description', 'طلبات المعمل والأشعة داخل العيادة')

@section('content')
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-xl border p-4">
    <p class="text-xs text-slate-500">تحاليل معلقة</p>
    <p class="text-2xl font-bold text-amber-600" id="pendingLab">—</p>
  </div>
  <div class="bg-white rounded-xl border p-4">
    <p class="text-xs text-slate-500">أشعة معلقة</p>
    <p class="text-2xl font-bold text-purple-600" id="pendingRad">—</p>
  </div>
  <div class="bg-white rounded-xl border p-4">
    <p class="text-xs text-slate-500">إجمالي المعلق</p>
    <p class="text-2xl font-bold text-slate-800" id="pendingTotal">—</p>
  </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6">
  <div class="flex flex-wrap gap-3 justify-between items-end mb-4">
    <div class="flex gap-2">
      <button type="button" data-tab="lab" class="tab-btn px-4 py-2 rounded-lg border font-semibold">تحاليل</button>
      <button type="button" data-tab="radiology" class="tab-btn px-4 py-2 rounded-lg border font-semibold">أشعة</button>
    </div>
    <div class="flex gap-2 flex-wrap">
      <input type="text" id="searchQ" placeholder="رقم الطلب أو اسم المريض..." class="border rounded-lg px-3 py-2 min-w-[220px]">
      <select id="statusFilter" class="border rounded-lg px-3 py-2">
        <option value="all">كل الحالات</option>
        <option value="ordered">مطلوب</option>
        <option value="in_progress">قيد التنفيذ</option>
        <option value="completed">مكتمل</option>
      </select>
      @if($canManageOrders)
      <button type="button" id="openOrderModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">+ طلب جديد</button>
      @endif
    </div>
  </div>
  <div id="ordersList" class="space-y-3">
    <p class="text-center text-slate-500 py-10">جاري التحميل...</p>
  </div>
</div>

@if($canManageOrders)
<div id="orderModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-xl w-full max-w-lg p-6 shadow-xl my-8">
    <h3 class="text-lg font-bold mb-4" id="orderModalTitle">طلب تحليل جديد</h3>
    <form id="orderForm" class="space-y-3">
      <input type="hidden" name="type" id="orderType" value="lab">
      <div>
        <label class="block text-sm font-semibold mb-1">بحث عن المريض *</label>
        <input type="text" id="patientSearch" placeholder="اسم أو هاتف..." class="w-full border rounded-lg px-3 py-2">
        <input type="hidden" name="patient_id" id="patientId" required>
        <div id="patientResults" class="mt-1 border rounded-lg hidden max-h-40 overflow-y-auto"></div>
        <p class="text-xs text-green-700 mt-1 hidden" id="selectedPatient"></p>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">الفحوصات *</label>
        <div id="testsCheckboxes" class="grid grid-cols-1 gap-1 max-h-48 overflow-y-auto border rounded-lg p-3"></div>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">ملاحظات سريرية</label>
        <textarea name="clinical_notes" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
      </div>
      <div class="flex gap-2 justify-end pt-2">
        <button type="button" id="closeOrderModal" class="px-4 py-2 bg-slate-100 rounded-lg">إلغاء</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">إرسال الطلب</button>
      </div>
    </form>
  </div>
</div>

<div id="resultModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-xl">
    <h3 class="text-lg font-bold mb-4">رفع نتائج</h3>
    <form id="resultForm" class="space-y-3">
      <input type="hidden" id="resultOrderId">
      <div>
        <label class="block text-sm font-semibold mb-1">ملخص النتيجة</label>
        <textarea name="result_summary" rows="3" class="w-full border rounded-lg px-3 py-2"></textarea>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">ملفات (PDF / صور)</label>
        <input type="file" name="files[]" multiple accept=".pdf,image/*" class="w-full border rounded-lg px-3 py-2">
      </div>
      <div class="flex gap-2 justify-end">
        <button type="button" id="closeResultModal" class="px-4 py-2 bg-slate-100 rounded-lg">إلغاء</button>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold">حفظ النتائج</button>
      </div>
    </form>
  </div>
</div>
@endif
@endsection

@section('scripts')
<script>
let currentTab = 'lab';
let catalog = { lab: @json($labTests), radiology: @json($radiologyTypes) };
const canManage = @json($canManageOrders);

const statusColors = {
  ordered: 'bg-blue-100 text-blue-800',
  sample_collected: 'bg-cyan-100 text-cyan-800',
  in_progress: 'bg-amber-100 text-amber-800',
  completed: 'bg-green-100 text-green-800',
  cancelled: 'bg-slate-100 text-slate-600',
};

async function loadStats() {
  const res = await apiCall('/doctor/api/orders/stats');
  if (!res?.success) return;
  document.getElementById('pendingLab').textContent = res.data.lab;
  document.getElementById('pendingRad').textContent = res.data.radiology;
  document.getElementById('pendingTotal').textContent = res.data.total;
}

function buildQuery() {
  const p = new URLSearchParams({ type: currentTab });
  const q = document.getElementById('searchQ').value.trim();
  const st = document.getElementById('statusFilter').value;
  if (q) p.set('q', q);
  if (st !== 'all') p.set('status', st);
  return p.toString();
}

async function loadOrders() {
  const res = await apiCall('/doctor/api/orders?' + buildQuery());
  const box = document.getElementById('ordersList');
  const items = res?.data || [];
  if (!items.length) {
    box.innerHTML = '<p class="text-center text-slate-500 py-10">لا توجد طلبات</p>';
    return;
  }
  box.innerHTML = items.map(o => `
    <div class="border rounded-xl p-4">
      <div class="flex flex-wrap justify-between gap-3">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-mono font-bold text-blue-700">${o.order_number}</span>
            <span class="text-xs px-2 py-0.5 rounded-full ${statusColors[o.status] || ''}">${o.status_label}</span>
          </div>
          <p class="font-semibold mt-1">${o.patient_name} · ملف ${o.file_number || '—'}</p>
          <p class="text-sm text-slate-600">${o.tests.map(t => t.label).join('، ')}</p>
          <p class="text-xs text-slate-400 mt-1">${o.created_at}</p>
        </div>
        <div class="flex flex-col gap-1">
          ${canManage && o.status !== 'completed' && o.status !== 'cancelled' ? `
            <button onclick="openResultModal(${o.id})" class="text-xs px-2 py-1 bg-green-600 text-white rounded">رفع نتائج</button>
            <button onclick="setOrderStatus(${o.id}, 'in_progress')" class="text-xs px-2 py-1 bg-amber-600 text-white rounded">قيد التنفيذ</button>
          ` : ''}
        </div>
      </div>
    </div>
  `).join('');
}

function renderTestCheckboxes(type) {
  const items = catalog[type] || {};
  document.getElementById('testsCheckboxes').innerHTML = Object.entries(items).map(([k, v]) => `
    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="tests[]" value="${k}"> ${v}
    </label>
  `).join('');
}

document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    currentTab = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('bg-blue-100', 'border-blue-400'));
    btn.classList.add('bg-blue-100', 'border-blue-400');
    loadOrders();
  });
});

['searchQ', 'statusFilter'].forEach(id => {
  document.getElementById(id).addEventListener('change', loadOrders);
  document.getElementById(id).addEventListener('input', () => clearTimeout(window._os) || (window._os = setTimeout(loadOrders, 350)));
});

@if($canManageOrders)
let patientTimer = null;
document.getElementById('patientSearch')?.addEventListener('input', () => {
  clearTimeout(patientTimer);
  patientTimer = setTimeout(async () => {
    const q = document.getElementById('patientSearch').value.trim();
    const box = document.getElementById('patientResults');
    if (q.length < 2) { box.classList.add('hidden'); return; }
    const res = await apiCall('/doctor/api/patients?search=' + encodeURIComponent(q) + '&limit=8');
    const items = res?.data?.items || res?.data || [];
    if (!items.length) { box.innerHTML = '<p class="p-2 text-sm text-slate-500">لا نتائج</p>'; box.classList.remove('hidden'); return; }
    box.innerHTML = items.map(p => `
      <button type="button" class="w-full text-right p-2 hover:bg-blue-50 text-sm border-b" onclick="selectPatient(${p.id}, '${p.name.replace(/'/g, '')}')">
        ${p.name} — ${p.phone || ''}
      </button>
    `).join('');
    box.classList.remove('hidden');
  }, 300);
});

function selectPatient(id, name) {
  document.getElementById('patientId').value = id;
  document.getElementById('selectedPatient').textContent = 'المريض: ' + name;
  document.getElementById('selectedPatient').classList.remove('hidden');
  document.getElementById('patientResults').classList.add('hidden');
}

document.getElementById('openOrderModal')?.addEventListener('click', () => {
  document.getElementById('orderType').value = currentTab;
  document.getElementById('orderModalTitle').textContent = currentTab === 'lab' ? 'طلب تحليل جديد' : 'طلب أشعة جديد';
  renderTestCheckboxes(currentTab);
  document.getElementById('orderForm').reset();
  document.getElementById('patientId').value = '';
  document.getElementById('selectedPatient').classList.add('hidden');
  document.getElementById('orderModal').classList.remove('hidden');
});

document.getElementById('closeOrderModal')?.addEventListener('click', () => {
  document.getElementById('orderModal').classList.add('hidden');
});

document.getElementById('orderForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const tests = [...document.querySelectorAll('#testsCheckboxes input:checked')].map(c => c.value);
  if (!document.getElementById('patientId').value || !tests.length) {
    showError('اختر المريض وفحصاً واحداً على الأقل');
    return;
  }
  const res = await apiCall('/doctor/api/orders', {
    method: 'POST',
    body: JSON.stringify({
      type: document.getElementById('orderType').value,
      patient_id: parseInt(document.getElementById('patientId').value),
      tests,
      clinical_notes: e.target.clinical_notes?.value || '',
    }),
  });
  if (res?.success) {
    document.getElementById('orderModal').classList.add('hidden');
    showSuccess('تم إنشاء الطلب: ' + res.data.order_number);
    loadStats(); loadOrders();
  }
});

function openResultModal(id) {
  document.getElementById('resultOrderId').value = id;
  document.getElementById('resultForm').reset();
  document.getElementById('resultModal').classList.remove('hidden');
}

document.getElementById('closeResultModal')?.addEventListener('click', () => {
  document.getElementById('resultModal').classList.add('hidden');
});

document.getElementById('resultForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id = document.getElementById('resultOrderId').value;
  const fd = new FormData(e.target);
  const res = await apiUpload(`/doctor/api/orders/${id}/results`, fd);
  if (res?.success) {
    document.getElementById('resultModal').classList.add('hidden');
    showSuccess('تم رفع النتائج');
    loadStats(); loadOrders();
  }
});

async function setOrderStatus(id, status) {
  await apiCall(`/doctor/api/orders/${id}/status`, {
    method: 'PATCH',
    body: JSON.stringify({ status }),
  });
  loadOrders();
}
@endif

window.addEventListener('load', () => {
  document.querySelector('.tab-btn[data-tab="lab"]')?.classList.add('bg-blue-100', 'border-blue-400');
  loadStats();
  loadOrders();
});
</script>
@endsection
