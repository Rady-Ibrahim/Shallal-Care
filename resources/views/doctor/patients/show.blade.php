@extends('doctor.layout')

@section('title', 'تفاصيل المريض')
@section('page-title', 'تفاصيل المريض')
@section('page-description', 'عرض معلومات المريض الكاملة والسجل الطبي')

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="/doctor/dashboard/patients" class="text-teal-600 hover:text-teal-700 flex items-center gap-2">
        <i class="fas fa-arrow-right"></i>
        <span>العودة إلى قائمة المرضى</span>
    </a>
</div>

<!-- Patient Info -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Info -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-start gap-6 mb-6">
                <div class="w-24 h-24 bg-teal-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-teal-600 text-4xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-800" id="patientName">جاري التحميل...</h2>
                    <p class="text-gray-600 mt-1" id="patientPhone">-</p>
                    <p class="text-sm text-blue-600 font-mono font-semibold mt-1" id="fileNumber"></p>
                    <div class="flex gap-4 mt-4">
                        <div>
                            <p class="text-sm text-gray-600">العمر</p>
                            <p class="text-lg font-semibold text-gray-800" id="patientAge">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">الجنس</p>
                            <p class="text-lg font-semibold text-gray-800" id="patientGender">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">الزيارات</p>
                            <p class="text-lg font-semibold text-gray-800" id="totalAppointments">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">معلومات الاتصال</h3>
                <div class="space-y-2">
                    <p class="text-gray-700">
                        <span class="font-semibold">الهاتف:</span>
                        <span id="patientPhoneFull">-</span>
                    </p>
                    <p class="text-gray-700">
                        <span class="font-semibold">البريد الإلكتروني:</span>
                        <span id="patientEmail">-</span>
                    </p>
                    <p class="text-gray-700">
                        <span class="font-semibold">العنوان:</span>
                        <span id="patientAddress">-</span>
                    </p>
                </div>
            </div>

            <!-- Medical History -->
            <div class="border-t mt-6 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">التاريخ المرضي</h3>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <p class="text-sm font-semibold text-amber-800 mb-1">الحساسية</p>
                        <p class="text-gray-700" id="patientAllergies">-</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-sm font-semibold text-blue-800 mb-1">أمراض مزمنة</p>
                        <p class="text-gray-700" id="patientChronic">-</p>
                    </div>
                </div>
                <p class="text-gray-700 leading-relaxed" id="medicalHistory">-</p>
            </div>
        </div>

        <!-- Recent Appointments -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">الزيارات الأخيرة</h3>
            <div id="recentAppointments" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">إجراءات سريعة</h3>
            <div class="space-y-3" id="quickActions">
                <a id="linkFile" href="#" class="block w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center">
                    <i class="fas fa-qrcode ml-2"></i>ملف المريض + QR
                </a>
                <a id="linkPrescription" href="#" class="block w-full px-4 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition text-center">
                    <i class="fas fa-prescription ml-2"></i>وصفة جديدة
                </a>
                <a id="linkRecord" href="#" class="block w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-center">
                    <i class="fas fa-file-medical ml-2"></i>سجل طبي جديد
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">الإحصائيات</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">إجمالي الزيارات</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalAppointmentsCount">0</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">الوصفات</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalPrescriptions">0</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">السجلات الطبية</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalRecords">0</p>
                </div>
            </div>
        </div>

        <!-- Medical Records -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">السجل الزمني</h3>
            <div id="patientTimeline" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">السجلات الطبية</h3>
            <div id="medicalRecords" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
            <a href="/doctor/dashboard/patients/{{ $patientId }}/records" class="block mt-4 text-center text-teal-600 hover:text-teal-700">
                عرض جميع السجلات <i class="fas fa-arrow-left mr-1"></i>
            </a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const patientId = {{ (int) $patientId }};

window.addEventListener('load', async function() {
    await loadPatientDetails();
});

async function loadPatientDetails() {
    try {
        showLoading();
        
        const data = await apiCall(`/doctor/api/patients/${patientId}`);
        
        if (data.success) {
            renderPatientDetails(data.data);
        } else {
            alert(data.error?.message || 'فشل تحميل البيانات');
        }
    } catch (error) {
        console.error('Error loading patient details:', error);
        alert('حدث خطأ أثناء تحميل البيانات');
    } finally {
        hideLoading();
    }
}

function renderPatientDetails(patient) {
    // Basic Info
    document.getElementById('patientName').textContent = patient.name || 'غير محدد';
    document.getElementById('patientPhone').textContent = patient.phone || '-';
    if (patient.file_number) {
        document.getElementById('fileNumber').textContent = 'ملف: ' + patient.file_number;
    }
    document.getElementById('patientAge').textContent = patient.age || '-';
    document.getElementById('patientGender').textContent = patient.gender === 'male'
        ? 'ذكر'
        : patient.gender === 'female'
            ? 'أنثى'
            : '-';
    document.getElementById('patientPhoneFull').textContent = patient.phone || '-';
    document.getElementById('patientEmail').textContent = patient.email || '-';
    document.getElementById('patientAddress').textContent = patient.address || '-';
    document.getElementById('patientAllergies').textContent = patient.allergies || 'لا يوجد';
    document.getElementById('patientChronic').textContent = patient.chronic_conditions || 'لا يوجد';
    document.getElementById('medicalHistory').textContent = patient.medical_history || 'لا يوجد تاريخ مرضي مسجّل';

    // Statistics
    const totalVisits = patient.total_visits ?? 0;
    document.getElementById('totalAppointments').textContent = totalVisits;
    document.getElementById('totalAppointmentsCount').textContent = totalVisits;
    document.getElementById('totalPrescriptions').textContent = patient.total_prescriptions || 0;
    document.getElementById('totalRecords').textContent = patient.total_records || 0;

    // Recent visits
    renderRecentAppointments(patient.recent_visits || []);

    // Medical Records
    renderMedicalRecords(patient.medical_records || []);
    loadTimeline();

    document.getElementById('linkFile').href = `/doctor/dashboard/patients/${patientId}/file`;
    document.getElementById('linkPrescription').href = `/doctor/dashboard/prescriptions/create?patient_id=${patientId}`;
    document.getElementById('linkRecord').href = `/doctor/dashboard/records/create?patient_id=${patientId}`;
}

async function loadTimeline() {
    const res = await apiCall(`/doctor/api/patients/${patientId}/timeline`);
    const box = document.getElementById('patientTimeline');
    const items = res?.data || [];
    if (!items.length) {
        box.innerHTML = '<p class="text-gray-500">لا توجد زيارات أو سجلات</p>';
        return;
    }
    box.innerHTML = items.slice(0, 8).map(item => `
        <div class="p-3 bg-gray-50 rounded-lg flex justify-between gap-2">
            <div>
                <p class="font-semibold text-gray-800">${item.title}</p>
                <p class="text-xs text-gray-500">${item.created_at || item.date}</p>
            </div>
            ${item.type === 'visit' ? `<a href="/doctor/dashboard/visits/${item.id}" class="text-xs text-blue-600">كشف</a>` : ''}
            ${item.type === 'prescription' ? `<a href="/doctor/dashboard/prescriptions/${item.id}" class="text-xs text-teal-600">روشتة</a>` : ''}
        </div>
    `).join('');
}

function renderRecentAppointments(appointments) {
    const container = document.getElementById('recentAppointments');
    
    if (appointments.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد زيارات</p>';
        return;
    }

    container.innerHTML = appointments.map(appointment => `
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between gap-2">
                <div>
                    ${appointment.booking_number ? `<p class="font-mono text-blue-700 font-bold text-sm">حجز #${appointment.booking_number}</p>` : ''}
                    <p class="text-sm text-gray-600">${formatDate(appointment.visit_date || appointment.appointment_date)}</p>
                    <p class="text-sm text-gray-500">${appointment.appointment_time || appointment.status_label || '-'}</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${getStatusClass(appointment.status)}">
                        ${appointment.status_label || getStatusText(appointment.status)}
                    </span>
                    ${appointment.id ? `<a href="/doctor/dashboard/visits/${appointment.id}" class="text-xs text-blue-600">فتح الكشف</a>` : ''}
                </div>
            </div>
            ${appointment.notes ? `<p class="text-sm text-gray-600 mt-2">${appointment.notes}</p>` : ''}
        </div>
    `).join('');
}

function renderMedicalRecords(records) {
    const container = document.getElementById('medicalRecords');
    
    if (records.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد سجلات طبية</p>';
        return;
    }

    container.innerHTML = records.slice(0, 3).map(record => `
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold text-gray-800">${record.type || 'غير محدد'}</p>
                    <p class="text-sm text-gray-600">${formatDate(record.created_at)}</p>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    ${record.attachments_count || 0} ملف
                </span>
            </div>
        </div>
    `).join('');
}

function getStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'scheduled': 'bg-blue-100 text-blue-800',
        'waiting': 'bg-amber-100 text-amber-800',
        'with_doctor': 'bg-purple-100 text-purple-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'no_show': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusText(status) {
    const texts = {
        'pending': 'معلق',
        'confirmed': 'مؤكد',
        'scheduled': 'محجوز',
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
