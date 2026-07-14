@extends('doctor.layout')

@section('title', 'الإعدادات')
@section('page-title', 'الإعدادات')
@section('page-description', 'إدارة إعدادات حسابك والاشتراك')

@section('content')
<!-- Settings Tabs -->
<div class="mb-6">
    <div class="flex gap-4 border-b">
        <button onclick="showTab('profile')" id="tab-profile" class="px-4 py-3 border-b-2 border-teal-600 text-teal-600 font-semibold">
            الملف الشخصي
        </button>
        @if($isClinicOwner)
        <button onclick="showTab('schedule')" id="tab-schedule" class="px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-gray-800">
            الجدول الزمني
        </button>
        <button onclick="showTab('subscription')" id="tab-subscription" class="px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-gray-800">
            الاشتراك
        </button>
        @endif
        <button onclick="showTab('security')" id="tab-security" class="px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-gray-800">
            الأمان
        </button>
        @if($isClinicOwner)
        <button onclick="showTab('branches')" id="tab-branches" class="px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-gray-800">
            فروع العيادة
        </button>
        @endif
    </div>
</div>

<!-- Profile Tab -->
<div id="content-profile" class="tab-content">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Form -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">معلومات شخصية</h3>
            <form id="profileForm" onsubmit="updateProfile(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">الاسم</label>
                        <input type="text" id="profileName" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">الهاتف</label>
                        <input type="tel" id="profilePhone"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">البريد الإلكتروني</label>
                        <input type="email" id="profileEmail"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">العنوان</label>
                        <textarea id="profileAddress" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>

        <!-- Professional Info -->
        @if($isClinicOwner)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">معلومات مهنية</h3>
            <form id="professionalForm" onsubmit="updateProfessional(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">السيرة الذاتية (عربي)</label>
                        <textarea id="bioAr" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">السيرة الذاتية (إنجليزي)</label>
                        <textarea id="bioEn" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">سنوات الخبرة</label>
                        <input type="number" id="experienceYears" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">سعر الكشف</label>
                        <input type="number" id="consultationFee" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

<!-- Schedule Tab -->
@if($isClinicOwner)
<div id="content-schedule" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">الجدول الزمني</h3>
        <div class="space-y-4" id="schedulesList">
            <p class="text-gray-500">جاري التحميل...</p>
        </div>
        <button onclick="openScheduleModal()" class="mt-4 px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
            <i class="fas fa-plus ml-2"></i>إضافة موعد
        </button>
    </div>
</div>

<div id="scheduleModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="text-lg font-bold mb-4">إضافة جدول عمل</h3>
        <form id="scheduleForm" onsubmit="saveSchedule(event)" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">اليوم *</label>
                <select id="scheduleDay" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="Sunday">الأحد</option>
                    <option value="Monday">الإثنين</option>
                    <option value="Tuesday">الثلاثاء</option>
                    <option value="Wednesday">الأربعاء</option>
                    <option value="Thursday">الخميس</option>
                    <option value="Friday">الجمعة</option>
                    <option value="Saturday">السبت</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">من *</label>
                    <input type="time" id="scheduleStart" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">إلى *</label>
                    <input type="time" id="scheduleEnd" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 bg-gray-100 rounded-lg">إلغاء</button>
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- Subscription Tab -->
<div id="content-subscription" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">اشتراكك الحالي</h3>
        <div id="subscriptionInfo" class="space-y-4">
            <p class="text-gray-500">جاري التحميل...</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">خطط الاشتراك</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="subscriptionPlans">
            <!-- Plans will be loaded here -->
        </div>
    </div>
</div>
@endif

<!-- Security Tab -->
<div id="content-security" class="tab-content hidden">
    <div class="max-w-lg">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">تغيير كلمة المرور</h3>
            <form id="passwordForm" onsubmit="changePassword(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">كلمة المرور الحالية</label>
                        <input type="password" id="currentPassword"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">كلمة المرور الجديدة</label>
                        <input type="password" id="newPassword"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" id="confirmPassword"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                        تغيير كلمة المرور
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($isClinicOwner)
<!-- Branches Tab -->
<div id="content-branches" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">فروع العيادة</h3>
                <p class="text-sm text-gray-500">أضف فروعاً إضافية واربط كل سكرتير بفرعه</p>
            </div>
            <button onclick="openBranchModal()" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-plus ml-2"></i>إضافة فرع
            </button>
        </div>
        <div id="branchesList" class="space-y-3">
            <p class="text-gray-500">جاري التحميل...</p>
        </div>
    </div>
</div>

<div id="branchModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-xl w-full max-w-lg p-6 my-8">
        <h3 class="text-lg font-bold mb-4" id="branchModalTitle">إضافة فرع</h3>
        <form id="branchForm" onsubmit="saveBranch(event)" class="space-y-4">
            <input type="hidden" id="branchId">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">اسم الفرع *</label>
                <input type="text" id="branchName" required placeholder="مثال: فرع مدينة نصر"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
            </div>
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">المحافظة</label>
                    <input type="text" id="branchGovernorate" placeholder="القاهرة"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">المنطقة</label>
                    <input type="text" id="branchDistrict" placeholder="مدينة نصر"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">العنوان</label>
                <textarea id="branchAddress" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">هاتف الفرع</label>
                <input type="tel" id="branchPhone" placeholder="01xxxxxxxxx"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" id="branchIsPrimary" class="rounded border-gray-300 text-teal-600">
                <span>تعيين كفرع رئيسي</span>
            </label>
            <div class="flex gap-2 justify-end pt-2">
                <button type="button" onclick="closeBranchModal()" class="px-4 py-2 rounded-lg bg-gray-100">إلغاء</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 text-white font-semibold">حفظ</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
window.addEventListener('load', async function() {
    await loadProfile();
    @if($isClinicOwner)
    await loadSchedules();
    await loadSubscription();
    await loadBranches();
    @endif
});

let clinicBranches = [];

function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });

    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('border-teal-600', 'text-teal-600');
        btn.classList.add('border-transparent', 'text-gray-600');
    });

    const content = document.getElementById(`content-${tabName}`);
    if (content) content.classList.remove('hidden');

    const activeBtn = document.getElementById(`tab-${tabName}`);
    if (activeBtn) {
        activeBtn.classList.remove('border-transparent', 'text-gray-600');
        activeBtn.classList.add('border-teal-600', 'text-teal-600');
    }

    if (tabName === 'branches') loadBranches();
}

async function loadProfile() {
    try {
        const data = await apiCall('/doctor/api/profile');
        
        if (data.success) {
            const profile = data.data;
            document.getElementById('profileName').value = profile.name || '';
            document.getElementById('profilePhone').value = profile.phone || '';
            document.getElementById('profileEmail').value = profile.email || '';
            document.getElementById('profileAddress').value = profile.address || '';
            document.getElementById('bioAr').value = profile.bio_ar || '';
            document.getElementById('bioEn').value = profile.bio_en || '';
            document.getElementById('experienceYears').value = profile.experience_years || '';
            document.getElementById('consultationFee').value = profile.consultation_fee || '';
        }
    } catch (error) {
        console.error('Error loading profile:', error);
    }
}

async function updateProfile(event) {
    event.preventDefault();
    
    try {
        const data = await apiCall('/doctor/api/profile', {
            method: 'PUT',
            body: JSON.stringify({
                name: document.getElementById('profileName').value,
                phone: document.getElementById('profilePhone').value,
                email: document.getElementById('profileEmail').value,
                address: document.getElementById('profileAddress').value,
            })
        });

        if (data.success) {
            alert('تم تحديث الملف الشخصي بنجاح');
        } else {
            alert(data.error?.message || 'فشل التحديث');
        }
    } catch (error) {
        alert('حدث خطأ أثناء التحديث');
    }
}

async function updateProfessional(event) {
    event.preventDefault();
    
    try {
        const data = await apiCall('/doctor/api/professional', {
            method: 'PUT',
            body: JSON.stringify({
                bio_ar: document.getElementById('bioAr').value,
                bio_en: document.getElementById('bioEn').value,
                experience_years: document.getElementById('experienceYears').value,
                consultation_fee: document.getElementById('consultationFee').value,
            })
        });

        if (data.success) {
            alert('تم تحديث المعلومات المهنية بنجاح');
        } else {
            alert(data.error?.message || 'فشل التحديث');
        }
    } catch (error) {
        alert('حدث خطأ أثناء التحديث');
    }
}

async function loadSchedules() {
    try {
        const data = await apiCall('/doctor/api/schedules');
        
        if (data.success) {
            renderSchedules(data.data);
        }
    } catch (error) {
        console.error('Error loading schedules:', error);
    }
}

function renderSchedules(schedules) {
    const container = document.getElementById('schedulesList');
    
    if (schedules.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد جداول</p>';
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

    container.innerHTML = schedules.map(schedule => `
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold text-gray-800">${daysAr[schedule.day_of_week] || schedule.day_of_week}</p>
                    <p class="text-sm text-gray-600">${schedule.start_time} - ${schedule.end_time}</p>
                    ${schedule.branch_name ? `<p class="text-xs text-gray-400">${schedule.branch_name}</p>` : ''}
                </div>
                <button onclick="deleteSchedule('${schedule.id}')" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-sm">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function openScheduleModal() {
    document.getElementById('scheduleForm')?.reset();
    document.getElementById('scheduleModal')?.classList.remove('hidden');
}

function closeScheduleModal() {
    document.getElementById('scheduleModal')?.classList.add('hidden');
}

async function saveSchedule(event) {
    event.preventDefault();
    try {
        const data = await apiCall('/doctor/api/schedules', {
            method: 'POST',
            body: JSON.stringify({
                day_of_week: document.getElementById('scheduleDay').value,
                start_time: document.getElementById('scheduleStart').value,
                end_time: document.getElementById('scheduleEnd').value,
            }),
        });
        if (data.success) {
            closeScheduleModal();
            loadSchedules();
            alert('تم إضافة الجدول بنجاح');
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحفظ');
    }
}

async function deleteSchedule(scheduleId) {
    if (!await confirmAction('هل أنت متأكد من حذف هذا الجدول؟')) return;

    try {
        const data = await apiCall(`/doctor/api/schedules/${scheduleId}`, {
            method: 'DELETE'
        });

        if (data.success) {
            alert('تم الحذف بنجاح');
            loadSchedules();
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحذف');
    }
}

async function loadSubscription() {
    try {
        const data = await apiCall('/doctor/api/subscription');
        
        if (data.success) {
            renderSubscription(data.data);
        }
    } catch (error) {
        console.error('Error loading subscription:', error);
    }
}

function renderSubscription(subscription) {
    const container = document.getElementById('subscriptionInfo');
    
    if (!subscription) {
        container.innerHTML = `
            <p class="text-gray-600">ليس لديك اشتراك نشط حالياً</p>
            <a href="/doctor/dashboard/subscription/plans" class="inline-block mt-4 px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                عرض الخطط المتاحة
            </a>
        `;
        return;
    }

    const statusLabels = { active: 'نشط', pending_payment: 'بانتظار الدفع' };
    const statusColors = { active: 'bg-green-100 text-green-800', pending_payment: 'bg-yellow-100 text-yellow-800' };

    container.innerHTML = `
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="font-semibold text-gray-800">${subscription.plan_name || 'غير محدد'}</p>
                    <p class="text-sm text-gray-600">${subscription.price || 0} {{ config('clinic.currency_symbol') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColors[subscription.status] || 'bg-red-100 text-red-800'}">
                    ${statusLabels[subscription.status] || 'غير نشط'}
                </span>
            </div>
            ${subscription.status === 'active' ? `<div class="space-y-2 text-sm">
                <p class="text-gray-600"><span class="font-semibold">تاريخ البدء:</span> ${formatDate(subscription.start_date)}</p>
                <p class="text-gray-600"><span class="font-semibold">تاريخ الانتهاء:</span> ${formatDate(subscription.end_date)}</p>
            </div>` : subscription.status === 'pending_payment' ? '<p class="text-sm text-yellow-700">طلبك قيد المراجعة من الإدارة</p>' : ''}
            <a href="/doctor/dashboard/subscription/plans" class="inline-block mt-4 px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition text-sm">
                ${subscription.status === 'active' ? 'تغيير الباقة' : 'عرض الخطط'}
            </a>
        </div>
    `;
}

async function changePassword(event) {
    event.preventDefault();
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        alert('كلمات المرور غير متطابقة');
        return;
    }

    try {
        const data = await apiCall('/doctor/api/change-password', {
            method: 'POST',
            body: JSON.stringify({
                current_password: document.getElementById('currentPassword').value,
                new_password: newPassword,
            })
        });

        if (data.success) {
            alert('تم تغيير كلمة المرور بنجاح');
            document.getElementById('passwordForm').reset();
        } else {
            alert(data.error?.message || 'فشل تغيير كلمة المرور');
        }
    } catch (error) {
        alert('حدث خطأ أثناء تغيير كلمة المرور');
    }
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}

@if($isClinicOwner)
async function loadBranches() {
    const container = document.getElementById('branchesList');
    if (!container) return;

    try {
        const data = await apiCall('/doctor/api/branches');
        clinicBranches = data?.data || [];
        renderBranches();
    } catch (error) {
        container.innerHTML = '<p class="text-red-500">تعذّر تحميل الفروع</p>';
    }
}

function renderBranches() {
    const container = document.getElementById('branchesList');

    if (!clinicBranches.length) {
        container.innerHTML = '<p class="text-gray-500">لا توجد فروع — أضف فرعاً جديداً</p>';
        return;
    }

    container.innerHTML = clinicBranches.map(branch => `
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-gray-800">
                        ${branch.branch_name}
                        ${branch.is_primary ? '<span class="mr-2 px-2 py-0.5 text-xs bg-teal-100 text-teal-700 rounded-full">رئيسي</span>' : ''}
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        ${[branch.governorate, branch.district].filter(Boolean).join(' — ') || 'بدون موقع'}
                    </p>
                    ${branch.address ? `<p class="text-sm text-gray-500">${branch.address}</p>` : ''}
                    ${branch.phone ? `<p class="text-sm text-gray-500">${branch.phone}</p>` : ''}
                </div>
                <div class="flex gap-2">
                    <button onclick="editBranch('${branch.id}')" class="px-3 py-1 bg-white border rounded-lg text-sm hover:bg-gray-50">تعديل</button>
                    ${!branch.is_primary ? `<button onclick="deleteBranch('${branch.id}')" class="px-3 py-1 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200">حذف</button>` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

function openBranchModal(branch = null) {
    document.getElementById('branchModalTitle').textContent = branch ? 'تعديل فرع' : 'إضافة فرع';
    document.getElementById('branchId').value = branch?.id || '';
    document.getElementById('branchName').value = branch?.branch_name || '';
    document.getElementById('branchGovernorate').value = branch?.governorate || '';
    document.getElementById('branchDistrict').value = branch?.district || '';
    document.getElementById('branchAddress').value = branch?.address || '';
    document.getElementById('branchPhone').value = branch?.phone || '';
    document.getElementById('branchIsPrimary').checked = !!branch?.is_primary;
    document.getElementById('branchModal').classList.remove('hidden');
}

function closeBranchModal() {
    document.getElementById('branchModal').classList.add('hidden');
    document.getElementById('branchForm').reset();
    document.getElementById('branchId').value = '';
}

function editBranch(branchId) {
    const branch = clinicBranches.find(b => String(b.id) === String(branchId));
    if (branch) openBranchModal(branch);
}

async function saveBranch(event) {
    event.preventDefault();

    const branchId = document.getElementById('branchId').value;
    const payload = {
        branch_name: document.getElementById('branchName').value,
        governorate: document.getElementById('branchGovernorate').value || null,
        district: document.getElementById('branchDistrict').value || null,
        address: document.getElementById('branchAddress').value || null,
        phone: document.getElementById('branchPhone').value || null,
        is_primary: document.getElementById('branchIsPrimary').checked,
    };

    try {
        const data = await apiCall(branchId ? `/doctor/api/branches/${branchId}` : '/doctor/api/branches', {
            method: branchId ? 'PUT' : 'POST',
            body: JSON.stringify(payload),
        });

        if (data.success) {
            closeBranchModal();
            await loadBranches();
            alert(branchId ? 'تم تعديل الفرع بنجاح' : 'تم إضافة الفرع بنجاح');
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحفظ');
    }
}

async function deleteBranch(branchId) {
    if (!await confirmAction('هل أنت متأكد من حذف هذا الفرع؟')) return;

    try {
        const data = await apiCall(`/doctor/api/branches/${branchId}`, { method: 'DELETE' });

        if (data.success) {
            await loadBranches();
            alert('تم حذف الفرع بنجاح');
        } else {
            alert(data.error?.message || 'لا يمكن حذف الفرع الرئيسي');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحذف');
    }
}
@endif
</script>
@endsection
