<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير يومي — {{ $date }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-white p-8 max-w-3xl mx-auto">
    <div class="no-print mb-4 flex gap-2">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg">طباعة</button>
        <button onclick="window.close()" class="px-4 py-2 bg-slate-200 rounded-lg">إغلاق</button>
    </div>

    <header class="border-b pb-4 mb-6">
        <h1 class="text-2xl font-bold">تقرير يومي — {{ $branchName }}</h1>
        <p class="text-slate-600">{{ $date }}</p>
        <p class="text-sm text-slate-400">Shallal Care</p>
    </header>

    <div class="grid grid-cols-3 gap-4 mb-8" id="summary">
        <div class="p-4 border rounded-lg"><p class="text-sm text-slate-500">إيرادات</p><p class="text-2xl font-bold text-green-700" id="income">—</p></div>
        <div class="p-4 border rounded-lg"><p class="text-sm text-slate-500">مصروفات</p><p class="text-2xl font-bold text-red-700" id="expense">—</p></div>
        <div class="p-4 border rounded-lg"><p class="text-sm text-slate-500">صافي</p><p class="text-2xl font-bold text-blue-700" id="net">—</p></div>
    </div>

    <h2 class="font-bold mb-3">تفصيل الحركات</h2>
    <table class="w-full text-sm border">
        <thead class="bg-slate-100"><tr><th class="p-2 text-right">الوقت</th><th class="p-2 text-right">النوع</th><th class="p-2 text-right">التصنيف</th><th class="p-2 text-right">المبلغ</th></tr></thead>
        <tbody id="txBody"><tr><td colspan="4" class="p-4 text-center text-slate-400">جاري التحميل...</td></tr></tbody>
    </table>

    <footer class="mt-8 pt-4 border-t text-xs text-slate-400 text-center">
        {{ config('clinic.company.name_ar') }} — {{ config('clinic.company.phone') }}
    </footer>

    <script>
    const sym = @json($currencySymbol);
    async function load() {
        const sum = await fetch('/doctor/api/finance/summary?date={{ $date }}', { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json());
        if (sum?.success) {
            document.getElementById('income').textContent = sum.data.income + ' ' + sym;
            document.getElementById('expense').textContent = sum.data.expense + ' ' + sym;
            document.getElementById('net').textContent = sum.data.net + ' ' + sym;
        }
        const tx = await fetch('/doctor/api/finance/transactions?date={{ $date }}', { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json());
        const rows = tx?.data || [];
        document.getElementById('txBody').innerHTML = rows.length ? rows.map(t => `
            <tr class="border-t"><td class="p-2">${t.time || '—'}</td><td class="p-2">${t.type_label}</td><td class="p-2">${t.category_label}</td><td class="p-2 font-semibold">${t.amount} ${sym}</td></tr>
        `).join('') : '<tr><td colspan="4" class="p-4 text-center">لا حركات</td></tr>';
    }
    load();
    </script>
</body>
</html>
