# حسابات تجريبية — Postman / API

شغّل أولاً:

```bash
php artisan migrate
php artisan db:seed
```

إذا فشل السيدر عند `StaticPageSeeder`، شغّل:
```bash
php artisan db:seed --class=DemoDataSeeder
```

كلمة المرور لجميع الحسابات: `password123`

## مريض (تطبيق الموبايل / Postman)

| الحقل | القيمة |
|-------|--------|
| الهاتف | `07708888000` |
| الإيميل | `patient@shallal-care.test` |
| Login | `POST /api/v1/auth/login` |

## دكتور (لوحة الويب)

| الحقل | القيمة |
|-------|--------|
| الهاتف | `07708888001` |
| الإيميل | `doctor@shallal-care.test` |
| الدخول | `/doctor/login` |

> **مهم:** المواعيد التجريبية (معلقة/مؤكدة/مكتملة) مربوطة بهذا الحساب فقط. إذا سجّلت دخول بحساب دكتور آخر، صفحة «طلبات المواعيد» ستظهر فارغة.

## أدمن (لوحة الويب)

| الحقل | القيمة |
|-------|--------|
| الهاتف | `07700000001` |
| الإيميل | `admin@shallal-care.test` |
| الدخول | `/admin/login` |

## بيانات جاهزة بعد السيدر

| المتغير | الوصف |
|---------|--------|
| `doctor_id` | طبيب معتمد بفرع وجدول |
| `schedule_id` | جدول يوم الأحد |
| `branch_id` | العيادة الرئيسية — بغداد |
| `appointment_id` | موعد **مكتمل** (سجل طبي + تقييم) |

بعد `php artisan db:seed --class=DemoDataSeeder` تظهر الـ IDs الفعلية في الـ terminal.

## فلو المريض في Postman

1. **Login** → يحفظ `token` تلقائياً
2. **Search Doctors** → قائمة أطباء
3. **My Appointments** → 3 مواعيد (مكتمل / مؤكد / معلق)
4. **Patient History** → سجل طبي للموعد المكتمل
5. **Create Review** → على الموعد المكتمل (مرة واحدة)
6. **Book Appointment** → حجز جديد (`schedule_id` + تاريخ مستقبلي)
