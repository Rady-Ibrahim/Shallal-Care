<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب طبيب - Shallal Care</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; }
        #clinic-map { height: 280px; border-radius: 0.5rem; z-index: 0; }
    </style>
</head>
<body class="bg-gradient-to-br from-teal-50 to-cyan-100 min-h-screen py-10">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-3xl mx-4 md:mx-auto">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-r from-teal-600 to-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-md text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">تسجيل حساب طبيب</h1>
            <p class="text-gray-500 mt-2">أكمل بياناتك وسيتم مراجعة حسابك من قبل الإدارة</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('doctor.register') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">البيانات الشخصية</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">الاسم الكامل</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">رقم الهاتف</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                            placeholder="01xxxxxxxxx">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">البريد الإلكتروني <span class="text-gray-400 font-normal">(اختياري)</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">التخصص</label>
                        <select name="speciality_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">اختر التخصص</option>
                            @foreach ($specialities as $speciality)
                                <option value="{{ $speciality->id }}" @selected(old('speciality_id') == $speciality->id)>
                                    {{ $speciality->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">كلمة المرور</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">سنوات الخبرة</label>
                        <input type="number" name="experience_years" value="{{ old('experience_years', 0) }}" min="0" max="60"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">الصورة الشخصية (حتى 10 ميجا)</label>
                        <input type="file" name="avatar" accept=".jpg,.jpeg,.png" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>
                <div class="mt-5">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">نبذة عن الطبيب</label>
                    <textarea name="bio_ar" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">{{ old('bio_ar') }}</textarea>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">موقع العيادة</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">المحافظة</label>
                        <select name="governorate_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">اختر المحافظة</option>
                            @foreach ($governorates as $governorate)
                                <option value="{{ $governorate->id }}" @selected(old('governorate_id') == $governorate->id)>
                                    {{ $governorate->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">المنطقة</label>
                        <input type="text" name="area" value="{{ old('area') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                            placeholder="مثال: مدينة نصر، المعادي">
                    </div>
                </div>
                <div class="mt-5">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">عنوان العيادة بالتفصيل</label>
                    <input type="text" name="address" value="{{ old('address') }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                        placeholder="الشارع، رقم المبنى، معلم قريب">
                </div>
                <p class="text-sm text-gray-500 mt-4 mb-2">اضغط على الخريطة لتحديد موقع العيادة</p>
                <div id="clinic-map" class="border border-gray-300"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">خط العرض (Latitude)</label>
                        <input type="number" step="any" name="latitude" id="latitude" value="{{ old('latitude', config('clinic.map.default_latitude')) }}" required readonly
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">خط الطول (Longitude)</label>
                        <input type="number" step="any" name="longitude" id="longitude" value="{{ old('longitude', config('clinic.map.default_longitude')) }}" required readonly
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">المستندات</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">صورة الترخيص (PDF أو صورة)</label>
                        <input type="file" name="license_document" accept=".pdf,.jpg,.jpeg,.png" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">صورة العيادة (اختياري)</label>
                        <input type="file" name="clinic_image" accept=".jpg,.jpeg,.png"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-teal-600 to-cyan-600 text-white py-3 rounded-lg font-semibold hover:from-teal-700 hover:to-cyan-700 transition">
                إنشاء الحساب
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <span class="text-gray-500">لديك حساب بالفعل؟</span>
            <a href="{{ route('doctor.login') }}" class="text-teal-600 hover:text-teal-700 font-semibold">تسجيل الدخول</a>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const defaultLat = parseFloat(latInput.value) || {{ config('clinic.map.default_latitude') }};
        const defaultLng = parseFloat(lngInput.value) || {{ config('clinic.map.default_longitude') }};
        const defaultZoom = {{ config('clinic.map.default_zoom') }};

        const map = L.map('clinic-map').setView([defaultLat, defaultLng], defaultZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        function updateCoords(lat, lng) {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
        }

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateCoords(e.latlng.lat, e.latlng.lng);
        });

        marker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            updateCoords(pos.lat, pos.lng);
        });
    </script>
</body>
</html>
