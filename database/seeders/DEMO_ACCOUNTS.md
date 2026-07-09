# حسابات تجريبية — موبايل / Postman

```bash
php artisan migrate
php artisan db:seed
# أو فقط:
php artisan db:seed --class=DemoDataSeeder
```

كلمة المرور: `password123`

## فلو الموبايل البسيط (الافتراضي)

1. **Bootstrap** — `GET /api/v1/mobile/config`  
   يعرض الميزات المفعّلة (حجز، تقييمات، …).

2. **زائر (بدون تسجيل)** — `POST /api/v1/auth/guest`
   ```json
   { "device_id": "demo-device-001" }
   ```
   يرجع `token` + `is_guest: true` — للتصفح فقط.

3. **تصفح بدون توكن** (عام):
   - `GET /api/v1/governorates`
   - `GET /api/v1/doctors`
   - `GET /api/v1/doctors/{id}`
   - `GET /api/v1/doctors/{id}/branches`

4. **تسجيل دخول مريض** (اختياري) — `POST /api/v1/auth/login`
   | الحقل | القيمة |
   |-------|--------|
   | الهاتف | `01088880000` |
   | الإيميل | `patient@shallal-care.test` |

## ميزات معطّلة افتراضياً (الكود موجود)

في `.env` — فعّلها للاختبار لاحقاً:

```env
MOBILE_BOOKING_ENABLED=true      # حجز المواعيد
MOBILE_REVIEWS_ENABLED=true      # التقييمات
MOBILE_MEDICAL_HISTORY_ENABLED=true
MOBILE_PUSH_ENABLED=true
```

عند التعطيل: الـ API يرجع `403 FEATURE_DISABLED` — **لم يُحذف أي كود**.

## حجز (عند التفعيل)

1. Login أو Guest token
2. `GET /api/v1/doctors/{id}/schedule`
3. `POST /api/v1/appointments` مع `doctor_id`, `schedule_id`, `appointment_date`, `appointment_time`

## عيادة (ويب)

| الدور | الهاتف | الرابط |
|-------|--------|--------|
| طبيب | `01088880001` | `/doctor/login` |
| طبيب 2 | `01088880002` | `/doctor/login` |
| أدمن | `07700000001` | `/admin/login` |

## IDs بعد السيدر

تظهر في terminal بعد `DemoDataSeeder`: `doctor_id`, `doctor2_id`, `schedule_id`, `branch_id`, `appointment_id`.
