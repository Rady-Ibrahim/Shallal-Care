@extends('admin.layout')

@section('title', 'تفاصيل الطبيب')
@section('page-title', 'تفاصيل الطبيب')
@section('page-description', 'عرض معلومات الطبيب الكاملة')

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="/admin/dashboard/doctors" class="text-blue-600 hover:text-blue-700 flex items-center gap-2">
        <i class="fas fa-arrow-right"></i>
        <span>العودة إلى قائمة الأطباء</span>
    </a>
</div>

<!-- Doctor Info -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Info -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-start gap-6 mb-6">
                <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-md text-blue-600 text-4xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-800" id="doctorName">جاري التحميل...</h2>
                    <p class="text-gray-600 mt-1" id="doctorSpeciality">-</p>
                    <div class="flex gap-4 mt-4">
                        <div>
                            <p class="text-sm text-gray-600">التقييم</p>
                            <p class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-star text-yellow-400"></i>
                                <span id="doctorRating">0.0</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">سنوات الخبرة</p>
                            <p class="text-lg font-semibold text-gray-800" id="doctorExperience">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">سعر الكشف</p>
                            <p class="text-lg font-semibold text-gray-800" id="doctorFee">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bio -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">السيرة الذاتية</h3>
                <p class="text-gray-700 leading-relaxed" id="doctorBio">-</p>
            </div>

            <!-- Verification Documents -->
            <div class="border-t mt-6 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">مستندات التحقق</h3>
                <div class="flex flex-wrap gap-4" id="doctorDocuments">
                    <p class="text-gray-500">جاري التحميل...</p>
                </div>
                <p class="text-sm text-red-600 mt-3 hidden" id="rejectReasonBox"></p>
            </div>

            <!-- Contact Info -->
            <div class="border-t mt-6 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">معلومات الاتصال</h3>
                <div class="space-y-2">
                    <p class="text-gray-700">
                        <span class="font-semibold">الهاتف:</span>
                        <span id="doctorPhone">-</span>
                    </p>
                    <p class="text-gray-700">
                        <span class="font-semibold">البريد الإلكتروني:</span>
                        <span id="doctorEmail">-</span>
                    </p>
                    <p class="text-gray-700">
                        <span class="font-semibold">العنوان:</span>
                        <span id="doctorAddress">-</span>
                    </p>
                </div>
            </div>

            <!-- Status -->
            <div class="border-t mt-6 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">الحالة</h3>
                <div class="flex items-center gap-4">
                    <span class="px-4 py-2 rounded-full text-sm font-semibold" id="doctorStatusBadge">
                        -
                    </span>
                    <div id="actionButtons" class="flex gap-2">
                        <!-- Action buttons will be added here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Branches -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">الفروع</h3>
            <div id="branchesList" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>

        <!-- Schedules -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">الجداول</h3>
            <div id="schedulesList" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Statistics -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">الإحصائيات</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">إجمالي الزيارات</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalVisits">0</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">زيارات مكتملة</p>
                    <p class="text-2xl font-bold text-gray-800" id="completedVisits">0</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">مرضى العيادة</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalPatients">0</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">السكرتارية / الفريق</p>
                    <p class="text-2xl font-bold text-gray-800" id="staffCount">0</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">إيراد العيادات</p>
                    <p class="text-2xl font-bold text-gray-800" id="clinicRevenue">0</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">عدد التقييمات</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalReviews">0</p>
                </div>
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">آخر التقييمات</h3>
            <div id="recentReviews" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const doctorId = window.location.pathname.split('/').pop();

window.addEventListener('load', async function() {
    await loadDoctorDetails();
});

async function loadDoctorDetails() {
    try {
        showLoading();
        
        const data = await apiCall(`/admin/api/doctors/${doctorId}`);
        
        if (data.success) {
            renderDoctorDetails(data.data);
        } else {
            alert(data.error?.message || 'فشل تحميل البيانات');
        }
    } catch (error) {
        console.error('Error loading doctor details:', error);
        alert('حدث خطأ أثناء تحميل البيانات');
    } finally {
        hideLoading();
    }
}

function renderDoctorDetails(doctor) {
    // Basic Info
    document.getElementById('doctorName').textContent = doctor.name || 'غير محدد';
    document.getElementById('doctorSpeciality').textContent = doctor.speciality || 'غير محدد';
    document.getElementById('doctorRating').textContent = doctor.rating || '0.0';
    document.getElementById('doctorExperience').textContent = (doctor.experience_years || 0) + ' سنة';
    document.getElementById('doctorFee').textContent = formatCurrency(doctor.consultation_fee || 0);
    document.getElementById('doctorBio').textContent = doctor.bio || '-';

    const docs = document.getElementById('doctorDocuments');
    let docsHtml = '';
    if (doctor.license_document) {
        docsHtml += `<a href="${doctor.license_document}" target="_blank" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg"><i class="fas fa-file ml-2"></i>رخصة المزاولة</a>`;
    }
    if (doctor.clinic_image) {
        docsHtml += `<a href="${doctor.clinic_image}" target="_blank" class="px-4 py-2 bg-green-100 text-green-700 rounded-lg"><i class="fas fa-image ml-2"></i>صورة العيادة</a>`;
    }
    docs.innerHTML = docsHtml || '<p class="text-gray-500">لا توجد مستندات</p>';

    if (doctor.reject_reason) {
        const box = document.getElementById('rejectReasonBox');
        box.textContent = 'سبب الرفض: ' + doctor.reject_reason;
        box.classList.remove('hidden');
    }

    document.getElementById('doctorPhone').textContent = doctor.phone || '-';
    document.getElementById('doctorEmail').textContent = doctor.email || '-';
    document.getElementById('doctorAddress').textContent = doctor.address || '-';

    // Status
    const statusBadge = document.getElementById('doctorStatusBadge');
    statusBadge.className = `px-4 py-2 rounded-full text-sm font-semibold ${getStatusClass(doctor.status)}`;
    statusBadge.textContent = getStatusText(doctor.status);

    // Action Buttons
    const actionButtons = document.getElementById('actionButtons');
    let buttons = '';
    
    if (doctor.status === 'pending') {
        buttons += `
            <button onclick="approveDoctor()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-check ml-2"></i>موافقة
            </button>
            <button onclick="rejectDoctor()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-times ml-2"></i>رفض
            </button>
        `;
    }
    
    buttons += `
        <button onclick="deleteDoctor()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            <i class="fas fa-trash ml-2"></i>حذف
        </button>
    `;
    
    actionButtons.innerHTML = buttons;

    // Statistics
    document.getElementById('totalVisits').textContent = doctor.total_visits || doctor.total_appointments || 0;
    document.getElementById('completedVisits').textContent = doctor.completed_visits || doctor.completed_appointments || 0;
    document.getElementById('totalPatients').textContent = doctor.total_patients || 0;
    document.getElementById('staffCount').textContent = doctor.staff_count || 0;
    document.getElementById('clinicRevenue').textContent = formatCurrency(doctor.clinic_revenue || 0);
    document.getElementById('totalReviews').textContent = doctor.total_reviews || 0;

    // Branches
    renderBranches(doctor.branches || []);

    // Schedules
    renderSchedules(doctor.schedules || []);

    // Reviews
    renderReviews(doctor.recent_reviews || []);
}

function renderBranches(branches) {
    const branchesList = document.getElementById('branchesList');
    
    if (branches.length === 0) {
        branchesList.innerHTML = '<p class="text-gray-500">لا توجد فروع</p>';
        return;
    }

    branchesList.innerHTML = branches.map(branch => `
        <div class="p-3 bg-gray-50 rounded-lg">
            <p class="font-semibold text-gray-800">${branch.name || 'غير محدد'}</p>
            <p class="text-sm text-gray-600">${branch.address || '-'}</p>
            <p class="text-sm text-gray-600">${branch.phone || '-'}</p>
            ${branch.is_primary ? '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded mt-2 inline-block">الفرع الرئيسي</span>' : ''}
        </div>
    `).join('');
}

function renderSchedules(schedules) {
    const schedulesList = document.getElementById('schedulesList');
    
    if (schedules.length === 0) {
        schedulesList.innerHTML = '<p class="text-gray-500">لا توجد جداول</p>';
        return;
    }

    const daysAr = {
        'Monday': 'الاثنين',
        'Tuesday': 'الثلاثاء',
        'Wednesday': 'الأربعاء',
        'Thursday': 'الخميس',
        'Friday': 'الجمعة',
        'Saturday': 'السبت',
        'Sunday': 'الأحد'
    };

    schedulesList.innerHTML = schedules.map(schedule => `
        <div class="p-3 bg-gray-50 rounded-lg">
            <p class="font-semibold text-gray-800">${daysAr[schedule.day_of_week] || schedule.day_of_week}</p>
            <p class="text-sm text-gray-600">${schedule.start_time} - ${schedule.end_time}</p>
        </div>
    `).join('');
}

function renderReviews(reviews) {
    const recentReviews = document.getElementById('recentReviews');
    
    if (reviews.length === 0) {
        recentReviews.innerHTML = '<p class="text-gray-500">لا توجد تقييمات</p>';
        return;
    }

    recentReviews.innerHTML = reviews.map(review => `
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex gap-1">
                    ${[...Array(5)].map((_, i) => `
                        <i class="fas fa-star ${i < review.rating ? 'text-yellow-400' : 'text-gray-300'}"></i>
                    `).join('')}
                </div>
                <span class="text-sm text-gray-600">${review.rating}/5</span>
            </div>
            <p class="text-sm text-gray-700">${review.comment || 'بدون تعليق'}</p>
            <p class="text-xs text-gray-500 mt-2">${review.patient_name || 'مريض'}</p>
        </div>
    `).join('');
}

async function approveDoctor() {
    if (!await confirmAction('هل أنت متأكد من الموافقة على هذا الطبيب؟')) return;

    try {
        const data = await apiCall(`/admin/api/doctors/${doctorId}/approve`, {
            method: 'POST'
        });

        if (data.success) {
            alert('تمت الموافقة بنجاح');
            loadDoctorDetails();
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الموافقة');
    }
}

async function rejectDoctor() {
    const reason = prompt('سبب الرفض (اختياري):');
    if (reason === null) return;

    try {
        const data = await apiCall(`/admin/api/doctors/${doctorId}/reject`, {
            method: 'POST',
            body: JSON.stringify({ reject_reason: reason }),
        });

        if (data.success) {
            alert('تم الرفض بنجاح');
            loadDoctorDetails();
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الرفض');
    }
}

async function deleteDoctor() {
    if (!await confirmAction('هل أنت متأكد من حذف هذا الطبيب؟')) return;

    try {
        const data = await apiCall(`/admin/api/doctors/${doctorId}`, {
            method: 'DELETE'
        });

        if (data.success) {
            alert('تم الحذف بنجاح');
            window.location.href = '/admin/dashboard/doctors';
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحذف');
    }
}

function getStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusText(status) {
    const texts = {
        'pending': 'معلق',
        'approved': 'موافق عليه',
        'rejected': 'مرفوض',
    };
    return texts[status] || status;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-EG', { style: 'decimal', minimumFractionDigits: 0 }).format(amount || 0) + ' ج.م';
}
</script>
@endsection
