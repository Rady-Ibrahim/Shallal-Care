<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة تحكم الطبيب')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.dashboard-ui', ['confirmColor' => '#14b8a6'])
    @include('partials.dashboard-api', [
        'loginUrl' => route('doctor.login'),
        'csrfRefreshUrl' => '/doctor/api/csrf-token',
    ])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        body {
            font-family: 'Cairo', sans-serif;
        }
        .sidebar-link.active {
            background: linear-gradient(90deg, #14b8a6 0%, #06b6d4 100%);
            color: white;
        }
        .sidebar-link:hover:not(.active) {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-teal-600 to-cyan-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-md text-white"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-gray-800">Shallal Care</h1>
                        <p class="text-xs text-gray-500">لوحة تحكم الطبيب</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            @php
                $navClass = fn (bool $active) => $active
                    ? 'sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg transition'
                    : 'sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition text-gray-700';
            @endphp
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="/doctor/dashboard" class="{{ $navClass(request()->routeIs('doctor.dashboard')) }}">
                    <i class="fas fa-home w-5"></i>
                    <span>الرئيسية</span>
                </a>
                <a href="/doctor/dashboard/patients" class="{{ $navClass(request()->routeIs('doctor.patients.*')) }}">
                    <i class="fas fa-users w-5"></i>
                    <span>المرضى</span>
                </a>
                <a href="/doctor/dashboard/requests" class="{{ $navClass(request()->routeIs('doctor.requests')) }}">
                    <i class="fas fa-inbox w-5"></i>
                    <span class="flex-1">طلبات المواعيد</span>
                    <span id="pendingRequestsBadge" class="hidden bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">0</span>
                </a>
                <a href="/doctor/dashboard/subscription/plans" class="{{ $navClass(request()->routeIs('doctor.subscription.*')) }}">
                    <i class="fas fa-crown w-5"></i>
                    <span>الاشتراكات</span>
                </a>
                <a href="/doctor/dashboard/prescriptions" class="{{ $navClass(request()->routeIs('doctor.prescriptions.*')) }}">
                    <i class="fas fa-prescription w-5"></i>
                    <span>الوصفات</span>
                </a>
                <a href="/doctor/dashboard/records" class="{{ $navClass(request()->routeIs('doctor.records.*')) }}">
                    <i class="fas fa-file-medical w-5"></i>
                    <span>السجلات الطبية</span>
                </a>
                <a href="/doctor/dashboard/calendar" class="{{ $navClass(request()->routeIs('doctor.calendar')) }}">
                    <i class="fas fa-calendar-alt w-5"></i>
                    <span>التقويم</span>
                </a>
                <a href="/doctor/dashboard/settings" class="{{ $navClass(request()->routeIs('doctor.settings')) }}">
                    <i class="fas fa-cog w-5"></i>
                    <span>الإعدادات</span>
                </a>
            </nav>

            <!-- User Info -->
            <div class="p-4 border-t">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-teal-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm" id="doctorName">د. {{ auth()->user()->name ?? 'الاسم' }}</p>
                        <p class="text-xs text-gray-500">طبيب</p>
                    </div>
                </div>
                <form id="logoutForm" method="POST" action="{{ route('doctor.logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">@yield('page-title', 'الرئيسية')</h2>
                        <p class="text-sm text-gray-500">@yield('page-description', 'نظرة عامة على حسابك')</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                        <button onclick="toggleNotificationsMenu()" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            <i class="fas fa-bell"></i>
                            <span id="notificationDot" class="hidden absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        <div id="notificationsMenu" class="hidden absolute left-0 mt-2 w-96 bg-white border rounded-xl shadow-xl z-40">
                            <div class="p-3 border-b flex items-center justify-between">
                                <p class="font-semibold text-sm">الإشعارات</p>
                                <button onclick="markAllNotificationsRead()" class="text-xs text-teal-600 hover:text-teal-700">تعليم الكل كمقروء</button>
                            </div>
                            <div id="notificationsList" class="max-h-96 overflow-y-auto">
                                <p class="p-4 text-sm text-gray-500">لا توجد إشعارات جديدة</p>
                            </div>
                        </div>
                        </div>
                        <button onclick="refreshData()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-8 flex flex-col items-center">
            <i class="fas fa-spinner fa-spin text-4xl text-teal-600 mb-4"></i>
            <p class="text-gray-700">جاري التحميل...</p>
        </div>
    </div>

    <!-- Error Toast -->
    <div id="errorToast" class="fixed top-4 left-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg hidden z-50">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorMessage">حدث خطأ</span>
            <button onclick="hideError()" class="ml-4 hover:text-red-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="successToast" class="fixed top-4 left-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg hidden z-50">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <span id="successMessage">تم بنجاح</span>
            <button onclick="hideSuccess()" class="ml-4 hover:text-green-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
        let loadingTimer = null;

        function showLoading() {
            clearTimeout(loadingTimer);
            loadingTimer = setTimeout(() => {
                document.getElementById('loadingOverlay').classList.remove('hidden');
            }, 350);
        }

        function hideLoading() {
            clearTimeout(loadingTimer);
            document.getElementById('loadingOverlay').classList.add('hidden');
        }

        async function refreshData() {
            location.reload();
        }

        let notificationPolling = null;
        let lastNotificationIds = new Set();

        @if (session('success'))
            window.addEventListener('load', function() {
                showSuccess(@json(session('success')));
            });
        @endif

        async function apiPost(endpoint, body = {}) {
            return apiCall(endpoint, { method: 'POST', body: JSON.stringify(body) });
        }

        function toggleNotificationsMenu() {
            const menu = document.getElementById('notificationsMenu');
            menu.classList.toggle('hidden');
        }

        async function loadUnreadNotifications(showToastForNew = false) {
            const data = await apiCall('/doctor/api/notifications/unread');
            if (!data?.success) return;

            const items = data.data?.items || [];
            const list = document.getElementById('notificationsList');
            const dot = document.getElementById('notificationDot');
            dot.classList.toggle('hidden', items.length === 0);

            if (!items.length) {
                list.innerHTML = '<p class="p-4 text-sm text-gray-500">لا توجد إشعارات جديدة</p>';
                return;
            }

            if (showToastForNew) {
                const newOnes = items.filter((item) => !lastNotificationIds.has(item.id));
                newOnes.forEach((item) => showSuccess(item.message || 'إشعار جديد'));
            }
            lastNotificationIds = new Set(items.map((item) => item.id));

            list.innerHTML = items.map((item) => `
                <div class="p-3 border-b hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-2">
                        <div class="cursor-pointer flex-1" onclick="openNotification('${item.id}', '${item.action_url || ''}')">
                            <p class="text-sm font-semibold text-gray-800">${item.title || 'إشعار'}</p>
                            <p class="text-xs text-gray-600 mt-1">${item.message || ''}</p>
                            <p class="text-[11px] text-gray-400 mt-1">${item.created_at || ''}</p>
                        </div>
                        <button onclick="markNotificationRead('${item.id}')" class="text-xs text-teal-600 hover:text-teal-700">تمت القراءة</button>
                    </div>
                </div>
            `).join('');
        }

        async function markNotificationRead(id) {
            await apiPost(`/doctor/api/notifications/${id}/read`, {});
            await loadUnreadNotifications(false);
        }

        async function markAllNotificationsRead() {
            await apiPost('/doctor/api/notifications/read-all', {});
            await loadUnreadNotifications(false);
        }

        async function openNotification(id, actionUrl) {
            await markNotificationRead(id);
            if (actionUrl) window.location.href = actionUrl;
        }

        // Error Handling
        function showError(message) {
            const errorToast = document.getElementById('errorToast');
            document.getElementById('errorMessage').textContent = message;
            errorToast.classList.remove('hidden');
            setTimeout(() => {
                errorToast.classList.add('hidden');
            }, 5000);
        }

        function hideError() {
            document.getElementById('errorToast').classList.add('hidden');
        }

        function showSuccess(message) {
            const successToast = document.getElementById('successToast');
            document.getElementById('successMessage').textContent = message;
            successToast.classList.remove('hidden');
            setTimeout(() => {
                successToast.classList.add('hidden');
            }, 3000);
        }

        function hideSuccess() {
            document.getElementById('successToast').classList.add('hidden');
        }

        // Form Validation
        function validateForm(formId, rules) {
            const form = document.getElementById(formId);
            if (!form) return false;

            let isValid = true;
            const errors = {};

            for (const [field, rule] of Object.entries(rules)) {
                const input = form.querySelector(`[name="${field}"]`);
                if (!input) continue;

                const value = input.value.trim();
                const errorElement = document.getElementById(`${field}_error`);

                if (rule.required && !value) {
                    errors[field] = `${rule.label || field} مطلوب`;
                    isValid = false;
                } else if (rule.minLength && value.length < rule.minLength) {
                    errors[field] = `${rule.label || field} يجب أن يكون ${rule.minLength} أحرف على الأقل`;
                    isValid = false;
                } else if (rule.email && !isValidEmail(value)) {
                    errors[field] = `${rule.label || field} يجب أن يكون بريد إلكتروني صحيح`;
                    isValid = false;
                } else if (rule.phone && !isValidPhone(value)) {
                    errors[field] = `${rule.label || field} يجب أن يكون رقم هاتف صحيح`;
                    isValid = false;
                } else {
                    delete errors[field];
                }

                if (errorElement) {
                    if (errors[field]) {
                        errorElement.textContent = errors[field];
                        errorElement.classList.remove('hidden');
                        input.classList.add('border-red-500');
                    } else {
                        errorElement.classList.add('hidden');
                        input.classList.remove('border-red-500');
                    }
                }
            }

            return isValid;
        }

        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function isValidPhone(phone) {
            const re = /^[0-9]{10,15}$/;
            return re.test(phone);
        }

        // Clear Form Errors
        function clearFormErrors(formId) {
            const form = document.getElementById(formId);
            if (!form) return;

            const errorElements = form.querySelectorAll('[id$="_error"]');
            errorElements.forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });

            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.classList.remove('border-red-500');
            });
        }

        window.addEventListener('load', async function() {
            try {
                const data = await apiCall('/doctor/api/metrics');
                const badge = document.getElementById('pendingRequestsBadge');
                if (badge && data?.success) {
                    const count = data.data?.appointments?.pending_requests || 0;
                    badge.textContent = count;
                    badge.classList.toggle('hidden', count === 0);
                }
                await loadUnreadNotifications(false);
                notificationPolling = setInterval(() => loadUnreadNotifications(true), 20000);
            } catch (e) {}
        });
    </script>
    @yield('scripts')
</body>
</html>
