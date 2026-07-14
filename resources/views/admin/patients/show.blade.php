@extends('admin.layout')

@section('title', 'تفاصيل المريض')
@section('page-title', 'تفاصيل المريض')
@section('page-description', 'عرض معلومات المريض وحجوزات العيادات')

@section('content')
<div class="mb-6">
    <a href="/admin/dashboard/patients" class="text-blue-600 hover:text-blue-700 flex items-center gap-2">
        <i class="fas fa-arrow-right"></i>
        <span>العودة إلى قائمة المرضى</span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-start gap-6 mb-6">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-green-600 text-4xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-800" id="patientName">جاري التحميل...</h2>
                    <p class="text-gray-600 mt-1" id="patientPhone">-</p>
                    <div class="flex gap-4 mt-4">
                        <div>
                            <p class="text-sm text-gray-600">الحجوزات</p>
                            <p class="text-lg font-semibold text-gray-800" id="totalBookings">0</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">ملفات العيادات</p>
                            <p class="text-lg font-semibold text-gray-800" id="clinicProfilesCount">0</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">النوع</p>
                            <p class="text-lg font-semibold text-gray-800" id="patientType">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">معلومات الاتصال</h3>
                <div class="space-y-2">
                    <p class="text-gray-700"><span class="font-semibold">الهاتف:</span> <span id="patientPhoneFull">-</span></p>
                    <p class="text-gray-700"><span class="font-semibold">البريد الإلكتروني:</span> <span id="patientEmail">-</span></p>
                    <p class="text-gray-700"><span class="font-semibold">تاريخ التسجيل:</span> <span id="patientJoinedDate">-</span></p>
                </div>
            </div>

            <div class="border-t mt-6 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">الحالة</h3>
                <div class="flex items-center gap-4">
                    <span class="px-4 py-2 rounded-full text-sm font-semibold" id="patientStatusBadge">-</span>
                    <div id="actionButtons" class="flex gap-2"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">ملفات العيادات</h3>
            <div id="clinicProfiles" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">آخر حجوزات العيادات</h3>
            <div id="recentBookings" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">الإحصائيات</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">إجمالي الحجوزات</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalBookingsCount">0</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">زيارات مكتملة</p>
                    <p class="text-2xl font-bold text-gray-800" id="completedBookingsCount">0</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">حجوزات ملغاة</p>
                    <p class="text-2xl font-bold text-gray-800" id="cancelledBookingsCount">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">الإجراءات السريعة</h3>
            <div class="space-y-3">
                <a id="viewBookingsLink" href="#" class="block w-full px-4 py-3 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition text-center">
                    <i class="fas fa-calendar-check ml-2"></i>عرض حجوزات العيادات
                </a>
                <button onclick="resetPassword()" class="block w-full px-4 py-3 bg-yellow-100 text-yellow-600 rounded-lg hover:bg-yellow-200 transition text-center">
                    <i class="fas fa-key ml-2"></i>إعادة تعيين كلمة المرور
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const patientId = window.location.pathname.split('/').pop();

window.addEventListener('load', async function() {
    document.getElementById('viewBookingsLink').href = `/admin/dashboard/clinic-bookings?search=${encodeURIComponent(patientId)}`;
    await loadPatientDetails();
});

async function loadPatientDetails() {
    try {
        showLoading();
        const data = await apiCall(`/admin/api/patients/${patientId}`);
        if (data.success) renderPatientDetails(data.data);
        else alert(data.error?.message || 'فشل تحميل البيانات');
    } catch (error) {
        console.error(error);
        alert('حدث خطأ أثناء تحميل البيانات');
    } finally {
        hideLoading();
    }
}

function renderPatientDetails(patient) {
    document.getElementById('patientName').textContent = patient.name || 'غير محدد';
    document.getElementById('patientPhone').textContent = patient.phone || '-';
    document.getElementById('patientType').textContent = patient.is_ghost ? 'Ghost' : 'عادي';
    document.getElementById('patientPhoneFull').textContent = patient.phone || '-';
    document.getElementById('patientEmail').textContent = patient.email || '-';
    document.getElementById('patientJoinedDate').textContent = formatDate(patient.created_at);

    const total = patient.total_bookings || patient.total_appointments || 0;
    document.getElementById('totalBookings').textContent = total;
    document.getElementById('clinicProfilesCount').textContent = patient.clinic_profiles_count || 0;
    document.getElementById('totalBookingsCount').textContent = total;
    document.getElementById('completedBookingsCount').textContent = patient.completed_bookings || patient.completed_appointments || 0;
    document.getElementById('cancelledBookingsCount').textContent = patient.cancelled_bookings || patient.cancelled_appointments || 0;

    const statusBadge = document.getElementById('patientStatusBadge');
    statusBadge.className = `px-4 py-2 rounded-full text-sm font-semibold ${patient.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
    statusBadge.textContent = patient.status === 'active' ? 'نشط' : 'محظور';

    const actionButtons = document.getElementById('actionButtons');
    actionButtons.innerHTML = patient.status === 'active'
        ? `<button onclick="blockPatient()" class="px-4 py-2 bg-red-600 text-white rounded-lg"><i class="fas fa-ban ml-2"></i>حظر</button>
           <button onclick="deletePatient()" class="px-4 py-2 bg-red-600 text-white rounded-lg"><i class="fas fa-trash ml-2"></i>حذف</button>`
        : `<button onclick="unblockPatient()" class="px-4 py-2 bg-green-600 text-white rounded-lg"><i class="fas fa-check ml-2"></i>إلغاء الحظر</button>
           <button onclick="deletePatient()" class="px-4 py-2 bg-red-600 text-white rounded-lg"><i class="fas fa-trash ml-2"></i>حذف</button>`;

    renderClinicProfiles(patient.clinic_profiles || []);
    renderRecentBookings(patient.recent_bookings || patient.recent_appointments || []);
}

function renderClinicProfiles(profiles) {
    const container = document.getElementById('clinicProfiles');
    if (!profiles.length) {
        container.innerHTML = '<p class="text-gray-500">لا توجد ملفات عيادات</p>';
        return;
    }
    container.innerHTML = profiles.map(p => `
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold text-gray-800">ملف #${p.file_number || p.id}</p>
                    <p class="text-sm text-gray-600 mt-1">الطبيب: ${p.doctor_name || '-'} · ${p.branch_name || 'فرع'}</p>
                    <p class="text-sm text-gray-500 mt-1">${p.bookings_count || 0} حجز</p>
                </div>
                <a href="/admin/dashboard/doctors/${p.doctor_id}" class="text-blue-600 text-sm">عرض الطبيب</a>
            </div>
            ${p.allergies ? `<p class="text-xs text-red-600 mt-2">حساسية: ${p.allergies}</p>` : ''}
            ${p.chronic_conditions ? `<p class="text-xs text-amber-700 mt-1">أمراض مزمنة: ${p.chronic_conditions}</p>` : ''}
        </div>
    `).join('');
}

function renderRecentBookings(bookings) {
    const container = document.getElementById('recentBookings');
    if (!bookings.length) {
        container.innerHTML = '<p class="text-gray-500">لا توجد حجوزات</p>';
        return;
    }
    container.innerHTML = bookings.map(b => `
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold text-gray-800">#${b.booking_number || b.id} — ${b.doctor_name || 'طبيب'}</p>
                    <p class="text-sm text-gray-600">${b.speciality || '-'} · ${b.branch_name || 'فرع'}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusClass(b.status)}">${b.status_label || getStatusText(b.status)}</span>
            </div>
            <p class="text-sm text-gray-600 mt-2">${formatDate(b.visit_date)} · ${formatCurrency(b.consultation_fee || 0)}</p>
        </div>
    `).join('');
}

async function blockPatient() {
    if (!await confirmAction('هل أنت متأكد من حظر هذا المريض؟')) return;
    const data = await apiCall(`/admin/api/patients/${patientId}/block`, { method: 'POST' });
    if (data.success) { alert('تم الحظر بنجاح'); loadPatientDetails(); }
}

async function unblockPatient() {
    if (!await confirmAction('هل أنت متأكد من إلغاء الحظر؟')) return;
    const data = await apiCall(`/admin/api/patients/${patientId}/unblock`, { method: 'POST' });
    if (data.success) { alert('تم إلغاء الحظر'); loadPatientDetails(); }
}

async function deletePatient() {
    if (!await confirmAction('هل أنت متأكد من حذف هذا المريض؟')) return;
    const data = await apiCall(`/admin/api/patients/${patientId}`, { method: 'DELETE' });
    if (data.success) window.location.href = '/admin/dashboard/patients';
}

async function resetPassword() {
    if (!await confirmAction('إعادة تعيين كلمة المرور؟')) return;
    const data = await apiCall(`/admin/api/patients/${patientId}/reset-password`, { method: 'POST' });
    if (data.success) alert('تم إرسال كلمة المرور الجديدة');
}

function getStatusClass(status) {
    return {
        scheduled: 'bg-blue-100 text-blue-800', checked_in: 'bg-cyan-100 text-cyan-800',
        waiting: 'bg-amber-100 text-amber-800', with_doctor: 'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800',
        no_show: 'bg-gray-100 text-gray-800',
    }[status] || 'bg-gray-100 text-gray-800';
}

function getStatusText(status) {
    return {
        scheduled: 'محجوز', checked_in: 'حضر', waiting: 'في الدور', with_doctor: 'عند الطبيب',
        completed: 'مكتمل', cancelled: 'ملغي', no_show: 'لم يحضر',
    }[status] || status;
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
