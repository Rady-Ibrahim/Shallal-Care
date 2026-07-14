<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اشتراك العيادة غير نشط</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-lg max-w-md w-full p-8 text-center">
        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-pause-circle text-amber-600 text-3xl"></i>
        </div>
        <h1 class="text-xl font-bold text-slate-800 mb-2">العيادة غير متاحة حالياً</h1>
        <p class="text-slate-600 text-sm leading-relaxed mb-4">
            اشتراك عيادة <strong>{{ $doctorName }}</strong>
            @if($branchName)
                — فرع <strong>{{ $branchName }}</strong>
            @endif
            غير نشط. لا يمكنك استخدام لوحة السكرتير حتى يُجدَّد الاشتراك.
        </p>
        <p class="text-slate-500 text-sm mb-6">تواصل مع الطبيب أو مدير العيادة لتفعيل الاشتراك.</p>
        <form method="POST" action="{{ route('doctor.logout') }}">
            @csrf
            <button type="submit" class="w-full px-4 py-2.5 bg-slate-800 text-white rounded-lg font-semibold hover:bg-slate-900 transition">
                تسجيل الخروج
            </button>
        </form>
    </div>
</body>
</html>
