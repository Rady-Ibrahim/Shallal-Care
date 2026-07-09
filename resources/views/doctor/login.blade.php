<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة تحكم الطبيب</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-teal-50 to-cyan-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md mx-4">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-r from-teal-600 to-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-md text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">لوحة تحكم الطبيب</h1>
            <p class="text-gray-500 mt-2">قم بتسجيل الدخول للمتابعة</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('doctor.login') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">رقم الهاتف</label>
                <div class="relative">
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <i class="fas fa-phone"></i>
                    </span>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                        class="w-full pr-10 pl-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                        placeholder="07xxxxxxxxx">
                </div>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">كلمة المرور</label>
                <div class="relative">
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                        class="w-full pr-10 pl-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePassword()" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" value="1" class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500">
                    <span class="mr-2 text-sm text-gray-600">تذكرني</span>
                </label>
                <a href="#" class="text-sm text-teal-600 hover:text-teal-700">نسيت كلمة المرور؟</a>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-teal-600 to-cyan-600 text-white py-3 rounded-lg font-semibold hover:from-teal-700 hover:to-cyan-700 transition transform hover:scale-[1.02] active:scale-[0.98]">
                تسجيل الدخول
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <span class="text-gray-500">ليس لديك حساب؟</span>
            <a href="{{ route('doctor.register') }}" class="text-teal-600 hover:text-teal-700 font-semibold">سجّل كطبيب</a>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>© 2026 Shallal Care. جميع الحقوق محفوظة.</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
