# Shallal Care — Production Plan (مصر)

**المنتج:** نظام إدارة عيادة للطبيب بفروع متعددة  
**السوق:** مصر — EGP — +20  
**المرجع UI:** Google Stitch mockups (MediFlow style)  
**Base commit:** `fa87f02` (iraq-doctors) بعد تنظيف marketplace

---

## 1. قرارات المنتج (مُقفلة)

| البند | القرار |
|-------|--------|
| Tenant | **الطبيب** (الاشتراك على الطبيب) |
| الفروع | `doctor_branches` — خزنة/دور/مواعيد مستقلة لكل فرع (Phase 1+) |
| السكرتير | role `secretary` — فرع واحد — الطبيب ينشئه — `/doctor/login` |
| المريض | يُضاف من الاستقبال برقم الهاتف (المفتاح الأساسي) |
| الموبايل | **عرض فقط** — أطباء/فروع/محافظات/تخصصات — **بدون حجز** — بدون تسجيل |
| الروشتة | الطبيب أو السكرتير يكتبها — طباعة A4 أو نصف A4 |
| طباعة الروشتة | شعار + بيانات عيادة/طبيب + تشخيص + QR + ختم/توقيع + فوتر Shallal Tech |
| الواجهة | Blade + Tailwind — أزرق احترافي (Stitch) |
| الدفع (اشتراك) | فودافون كاش / تحويل بنكي → بوابة لاحقاً |
| الدفع (عيادة) | كاش أولاً عند الاستقبال (Phase 1) |

---

## 2. ما تم في Phase 0 (هذه الجلسة)

- [x] `config/clinic.php` — مصر، EGP، Shallal Tech footer
- [x] محافظات مصر (migration)
- [x] دور `secretary` + جدول `clinic_staff_members` (مع `branch_id`)
- [x] صلاحيات السكرتير (`SecretaryPermissions`)
- [x] `ClinicDashboardContext` — طبيب أو سكرتير + فرع
- [x] Middleware: `clinic.context`, `clinic.permission`, `clinic.owner`
- [x] إدارة السكرتارية (API + صفحة `/doctor/dashboard/staff`)
- [x] طباعة روشتة احترافية (`/doctor/dashboard/prescriptions/{id}/print`)
- [x] حقول الطبيب: `clinic_name`, `syndicate_number`, `signature_path`, `stamp_path`
- [x] تحديث sidebar — أزرق + روشتات + سكرتارية

---

## 3. Phases القادمة

### Phase 1 — الاستقبال والدور (أسبوع 3-4)
- [x] Reception Desk — بحث برقم الحجز أو اسم المريض (الهاتف في بحث متقدم)
- [x] ربط/إنشاء مريض تلقائياً من الهاتف عند الحجز
- [x] `clinic_patients` — رقم ملف فريد per doctor (`SC-0001`)
- [x] `clinic_bookings` — رقم حجز (`BK-{ymd}-{branch}-{seq}`)
- [x] Queue per branch (`/doctor/dashboard/queue`)
- [x] تحصيل كاش → `branch_transactions`

### Phase 2 — المالية per Branch
- [x] خزنة الفرع — عرض إيرادات ومصروفات اليوم
- [x] تسجيل مصروفات يدوياً (إيجار، رواتب، مستلزمات...)
- [x] تقرير يومي — صافي + تفصيل حسب التصنيف وطريقة الدفع
- [x] ربط إيرادات الكشف من الاستقبال → `branch_transactions`

### Phase 3 — الزيارة والسجل
- [x] Doctor Visit workflow — `/doctor/dashboard/visits/{bookingId}`
- [x] ربط الكشف بـ `clinic_bookings` + `medical_records`
- [x] Medical timeline على ملف المريض
- [x] QR على ملف المريض — `/doctor/dashboard/patients/{id}/file`

### Phase 4 — الموبايل — **مؤجّل للمنتج، مضبوط للسيدر والاختبار**
- [x] `config/mobile.php` — feature flags (تعطيل بدون حذف)
- [x] `GET /api/v1/mobile/config` — bootstrap للتطبيق
- [x] `POST /api/v1/auth/guest` — زائر بدون تسجيل
- [x] تسجيل دخول مريض اختياري (`MOBILE_AUTH_ENABLED`)
- [x] دليل أطباء + محافظات عامة
- [x] الحجز/تقييمات/سجل — معطّل افتراضياً (`MOBILE_BOOKING_ENABLED=false`)
- [ ] تطبيق Flutter/React Native (لاحقاً)

### Phase 5 — Lab / Radiology / Reports
- [x] طلبات تحاليل وأشعة داخل العيادة (`clinic_orders`)
- [x] كتالوج فحوصات جاهز + رفع نتائج (ملفات)
- [x] صفحة `/doctor/dashboard/orders` — تحاليل | أشعة
- [x] تقارير العيادة `/doctor/dashboard/reports` — زيارات، إيرادات، تشخيصات

### Phase 6 — Production
- [ ] بوابة دفع اشتراك
- [ ] Audit log + backup
- [ ] اختبارات + deploy

---

## 4. الفلو التشغيلي

```
تسجيل طبيب → موافقة أدمن → اشتراك → إعداد فروع + سكرتارية
                                              ↓
مريض يصل العيادة → سكرتير يدخل برقم الهاتف → ملف/دور → تحصيل كاش
                                              ↓
طبيب يكشف → تشخيص → روشتة (طبيب أو سكرتير) → طباعة
                                              ↓
موبايل (منفصل): تصفح أطباء وفروع للاكتشاف فقط
```

---

## 5. هيكل البيانات (مستهدف)

```
doctors (+ clinic_name, syndicate, signature, stamp)
doctor_branches
clinic_staff_members (doctor_id, branch_id, user_id, permissions)
users (patient | doctor | admin | secretary)
clinic_patients (doctor_id, patient_user_id, file_number)  ← Phase 1
queue_entries (branch_id, ...)                           ← Phase 1
branch_treasuries / branch_transactions                    ← Phase 1-2
medical_records (prescription + prescription_number)
doctor_subscriptions (كما هو)
```

---

## 6. الأدمن (منصة)

- موافقة أطباء
- باقات واشتراكات
- مراجعة مدفوعات (فودافون كاش / بنك)
- محافظات مصر + تخصصات
- تقارير المنصة

**ليس مسؤولاً عن:** استقبال العيادة، روشتات، خزنة الفروع

---

## 7. متغيرات البيئة

```env
SHALLAL_TECH_PHONE=01xxxxxxxxx
SHALLAL_TECH_EMAIL=info@shallalcare.com
SHALLAL_TECH_WEBSITE=https://shallalcare.com
```

---

*آخر تحديث: يوليو 2026 — Phase 0*
