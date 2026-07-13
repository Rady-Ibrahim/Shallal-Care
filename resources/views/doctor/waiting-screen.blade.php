<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شاشة الانتظار — {{ $branchName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap');
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 to-indigo-900 min-h-screen text-white p-8">
    <div class="max-w-4xl mx-auto text-center">
        <p class="text-blue-200 text-lg mb-2">{{ $branchName }}</p>
        <h1 class="text-3xl font-bold mb-8">شاشة انتظار المرضى</h1>

        <div class="bg-white/10 backdrop-blur rounded-2xl p-8 mb-8 border border-white/20">
            <p class="text-blue-200 mb-2">الدور الحالي</p>
            <p id="currentNumber" class="text-8xl font-black text-yellow-300">—</p>
            <p id="currentName" class="text-2xl mt-4 font-semibold">—</p>
        </div>

        <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
            <p class="text-blue-200 mb-4">في الانتظار (<span id="waitingCount">0</span>)</p>
            <div id="waitingList" class="flex flex-wrap justify-center gap-4"></div>
        </div>

        <p class="text-blue-300 text-sm mt-8" id="updatedAt">—</p>
    </div>

    <audio id="queueSound" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleWdfW1VNSkE+Ojk3NDEvLCsqJyQjHyAeHRwbGBcVFBMSERAPDg0MCwoJCAcGBQQDAgEA" type="audio/wav">
    </audio>

    <script>
    let lastCurrentId = null;

    async function refresh() {
        try {
            const res = await fetch('/doctor/api/waiting-screen', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) return;
            const d = data.data;
            document.getElementById('updatedAt').textContent = 'آخر تحديث: ' + d.updated_at;

            if (d.current) {
                document.getElementById('currentNumber').textContent = '#' + d.current.booking_number;
                document.getElementById('currentName').textContent = d.current.patient_name || '';
                if (lastCurrentId && lastCurrentId !== d.current.id) {
                    document.getElementById('queueSound').play().catch(() => {});
                }
                lastCurrentId = d.current.id;
            } else {
                document.getElementById('currentNumber').textContent = '—';
                document.getElementById('currentName').textContent = 'لا يوجد مريض حالياً';
            }

            document.getElementById('waitingCount').textContent = d.waiting_count;
            document.getElementById('waitingList').innerHTML = (d.waiting || []).map(w =>
                `<span class="text-4xl font-bold bg-white/10 px-6 py-3 rounded-xl">#${w.booking_number}</span>`
            ).join('') || '<span class="text-blue-300">لا يوجد انتظار</span>';
        } catch (e) {}
    }

    refresh();
    setInterval(refresh, 5000);
    </script>
</body>
</html>
