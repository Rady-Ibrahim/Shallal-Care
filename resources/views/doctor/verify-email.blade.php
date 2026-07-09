<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفعيل البريد الإلكتروني - Shallal Care</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-teal-50 to-cyan-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md mx-4">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-r from-teal-600 to-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-envelope-open-text text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">تفعيل البريد الإلكتروني</h1>
            <p class="text-gray-500 mt-2 text-sm">أدخل الكود المكون من 6 أرقام المرسل إلى</p>
            <p class="text-teal-700 font-semibold mt-1">{{ $email }}</p>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-6">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg text-sm mb-6">{{ session('info') }}</div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('doctor.verify-email.submit') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">كود التفعيل</label>
                <input type="text" name="code" value="{{ old('code') }}" required maxlength="6" pattern="[0-9]{6}"
                    inputmode="numeric" autocomplete="one-time-code"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 text-center text-2xl tracking-widest"
                    placeholder="000000">
            </div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-teal-600 to-cyan-600 text-white py-3 rounded-lg font-semibold hover:from-teal-700 hover:to-cyan-700 transition">
                تأكيد التفعيل
            </button>
        </form>

        <form method="POST" action="{{ route('doctor.verify-email.resend') }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full text-teal-600 hover:text-teal-700 text-sm font-semibold py-2">
                إعادة إرسال الكود
            </button>
        </form>

        <form method="POST" action="{{ route('doctor.logout') }}" class="mt-6 text-center">
            @csrf
            <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm">تسجيل الخروج</button>
        </form>
    </div>
</body>
</html>
