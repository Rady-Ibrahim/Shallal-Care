@extends('doctor.layout')

@section('title', 'إدارة السكرتارية')
@section('page-title', 'الموظفون')
@section('page-description', 'إضافة وإدارة سكرتارية الفروع والصلاحيات')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">سكرتارية العيادة</h2>
            <p class="text-sm text-slate-500">كل سكرتير مربوط بفرع واحد — الصلاحيات يحددها الطبيب فقط</p>
        </div>
        <button id="openStaffModal" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
            + إضافة سكرتير
        </button>
    </div>

    <div id="staffList" class="space-y-3">
        <p class="text-center text-slate-500 py-8">جاري التحميل...</p>
    </div>
</div>

<div id="staffModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-xl w-full max-w-2xl p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-1" id="staffModalTitle">إضافة سكرتير</h3>
        <p class="text-sm text-slate-500 mb-4">حدّد الصلاحيات التي يحتاجها السكرتير في فرعه</p>
        <form id="staffForm" class="space-y-4">
            <input type="hidden" id="staffId">
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold mb-1">الاسم *</label>
                    <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">الهاتف *</label>
                    <input type="text" name="phone" required class="w-full border rounded-lg px-3 py-2" placeholder="01xxxxxxxxx">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">الفرع *</label>
                <select name="branch_id" required id="branchSelect" class="w-full border rounded-lg px-3 py-2"></select>
            </div>
            <div id="passwordFields">
                <label class="block text-sm font-semibold mb-1" id="passwordLabel">كلمة المرور *</label>
                <input type="password" name="password" id="passwordInput" class="w-full border rounded-lg px-3 py-2">
                <input type="password" name="password_confirmation" id="passwordConfirm" placeholder="تأكيد كلمة المرور" class="w-full border rounded-lg px-3 py-2 mt-2">
                <p class="text-xs text-slate-400 mt-1" id="passwordHint"></p>
            </div>
            <div class="border rounded-lg p-4 bg-slate-50">
                <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                    <label class="block text-sm font-bold text-slate-800">الصلاحيات</label>
                    <div class="flex gap-2 text-xs">
                        <button type="button" id="permSelectAll" class="text-blue-600 hover:underline">تحديد الكل</button>
                        <button type="button" id="permSelectDefault" class="text-blue-600 hover:underline">الافتراضي</button>
                        <button type="button" id="permClearAll" class="text-slate-500 hover:underline">إلغاء الكل</button>
                    </div>
                </div>
                <div id="permissionsGrid" class="grid sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                    @foreach($permissionLabels as $key => $label)
                    <label class="flex items-start gap-2 text-sm bg-white border rounded-lg p-2 cursor-pointer hover:border-blue-300">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" class="perm-check mt-0.5">
                        <span>{{ $label }}<br><span class="text-[10px] text-slate-400 font-mono">{{ $key }}</span></span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-2 justify-end pt-2">
                <button type="button" id="closeStaffModal" class="px-4 py-2 rounded-lg bg-slate-100">إلغاء</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold">حفظ</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const permissionLabels = @json($permissionLabels);
const defaultPermissions = @json($defaultPermissions);
let branches = [];
let staffMembers = [];

function setPermissions(selected) {
    document.querySelectorAll('.perm-check').forEach(cb => {
        cb.checked = selected.includes(cb.value);
    });
}

function getSelectedPermissions() {
    return [...document.querySelectorAll('.perm-check:checked')].map(cb => cb.value);
}

function permissionSummary(keys) {
    if (!keys?.length) return 'بدون صلاحيات';
    return keys.slice(0, 4).map(k => permissionLabels[k] || k).join('، ')
        + (keys.length > 4 ? ` (+${keys.length - 4})` : '');
}

async function loadBranches() {
    const res = await apiCall('/doctor/api/branches');
    branches = res?.data || [];
    document.getElementById('branchSelect').innerHTML = branches.map(b =>
        `<option value="${b.id}">${b.branch_name}</option>`
    ).join('');
}

async function loadStaff() {
    const container = document.getElementById('staffList');
    try {
        const res = await apiCall('/doctor/api/staff');
        staffMembers = res?.data || [];
        if (!staffMembers.length) {
            container.innerHTML = '<p class="text-center text-slate-500 py-8">لا يوجد سكرتارية بعد</p>';
            return;
        }
        container.innerHTML = staffMembers.map(m => `
            <div class="border rounded-lg p-4">
                <div class="flex flex-wrap justify-between gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <p class="font-bold">${m.user.name}</p>
                        <p class="text-sm text-slate-500">${m.user.phone} — ${m.branch?.name || ''}</p>
                        <p class="text-xs text-blue-700 mt-1">${permissionSummary(m.permissions)}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs px-2 py-1 rounded-full ${m.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100'}">${m.status === 'active' ? 'نشط' : 'موقوف'}</span>
                        <button type="button" onclick="editStaff(${m.id})" class="text-sm px-3 py-1 rounded bg-blue-50 text-blue-700">تعديل / صلاحيات</button>
                        <button type="button" onclick="toggleStaff(${m.id}, '${m.status}')" class="text-sm px-3 py-1 rounded bg-slate-100">${m.status === 'active' ? 'إيقاف' : 'تفعيل'}</button>
                        <button type="button" onclick="deleteStaff(${m.id})" class="text-sm px-3 py-1 rounded bg-red-50 text-red-600">حذف</button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (e) {
        container.innerHTML = '<p class="text-center text-red-600 py-8">تعذر تحميل السكرتارية</p>';
    }
}

function openStaffModal(isEdit = false) {
    document.getElementById('staffModalTitle').textContent = isEdit ? 'تعديل السكرتير والصلاحيات' : 'إضافة سكرتير';
    document.getElementById('passwordLabel').textContent = isEdit ? 'كلمة مرور جديدة (اختياري)' : 'كلمة المرور *';
    document.getElementById('passwordInput').required = !isEdit;
    document.getElementById('passwordConfirm').required = !isEdit;
    document.getElementById('passwordHint').textContent = isEdit ? 'اتركها فارغة إن لم تُرد تغييرها' : '';
    document.getElementById('staffModal').classList.remove('hidden');
}

function editStaff(id) {
    const m = staffMembers.find(s => s.id === id);
    if (!m) return;
    const form = document.getElementById('staffForm');
    form.reset();
    document.getElementById('staffId').value = id;
    form.name.value = m.user.name;
    form.phone.value = m.user.phone;
    form.branch_id.value = m.branch?.id || '';
    setPermissions(m.permissions || []);
    openStaffModal(true);
}

document.getElementById('openStaffModal').addEventListener('click', () => {
    document.getElementById('staffForm').reset();
    document.getElementById('staffId').value = '';
    setPermissions(defaultPermissions);
    openStaffModal(false);
});

document.getElementById('closeStaffModal').addEventListener('click', () => {
    document.getElementById('staffModal').classList.add('hidden');
});

document.getElementById('permSelectAll').addEventListener('click', () => {
    setPermissions(Object.keys(permissionLabels));
});
document.getElementById('permSelectDefault').addEventListener('click', () => {
    setPermissions(defaultPermissions);
});
document.getElementById('permClearAll').addEventListener('click', () => {
    setPermissions([]);
});

document.getElementById('staffForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const id = document.getElementById('staffId').value;
    const payload = {
        name: form.name.value,
        phone: form.phone.value,
        branch_id: parseInt(form.branch_id.value, 10),
        permissions: getSelectedPermissions(),
    };
    if (form.password.value) {
        payload.password = form.password.value;
        payload.password_confirmation = form.password_confirmation.value;
    } else if (!id) {
        showError('كلمة المرور مطلوبة للسكرتير الجديد');
        return;
    }
    const url = id ? `/doctor/api/staff/${id}` : '/doctor/api/staff';
    const method = id ? 'PUT' : 'POST';
    const res = await apiCall(url, { method, body: JSON.stringify(payload) });
    if (res?.success) {
        document.getElementById('staffModal').classList.add('hidden');
        showSuccess(id ? 'تم تحديث السكرتير' : 'تم إضافة السكرتير');
        loadStaff();
    }
});

async function toggleStaff(id, status) {
    const next = status === 'active' ? 'inactive' : 'active';
    await apiCall(`/doctor/api/staff/${id}/status`, { method: 'PATCH', body: JSON.stringify({ status: next }) });
    loadStaff();
}

async function deleteStaff(id) {
    if (!await confirmAction('حذف السكرتير؟')) return;
    await apiCall(`/doctor/api/staff/${id}`, { method: 'DELETE' });
    loadStaff();
}

window.addEventListener('load', async () => {
    await loadBranches();
    await loadStaff();
});
</script>
@endsection
