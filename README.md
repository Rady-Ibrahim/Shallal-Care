# Shallal Care — Clinic Management System

نظام SaaS احترافي لإدارة العيادات الطبية الخاصة.

- **الطبيب:** الكشف، التشخيص، الروشتات، السجل الطبي  
- **السكرتيرة:** الاستقبال، المواعيد، الدور، التحصيل، الحسابات  
- **مدير المنصة:** الاشتراكات، المدفوعات، إدارة العيادات  

**مرجع المنتج:** BRD الجزء 1 + 2 (Clinic Management System)  
**أساس الكود:** iraq-doctors عند الكوميت `fa87f02` بعد تنظيف marketplace

---

## الحالة الحالية

الكود الحالي ما زال **لوحة أدمن + لوحة طبيب + API مريض** من منصة أطباء العراق، بعد إزالة طبقات الصيدليات/المعامل الخارجية.

تم التنظيف:
- حذف جداول الصيدليات والمعامل والأدوية من قاعدة البيانات
- إزالة أدوار `pharmacy` / `laboratory`
- إعادة البراند إلى Shallal Care
- حذف أمر ranking المنتجات الميت

خريطة التنفيذ: [`docs/PRODUCTION_PLAN.md`](docs/PRODUCTION_PLAN.md)  
فلو الحالي: [`docs/USER_FLOWS.md`](docs/USER_FLOWS.md)

---

## المتطلبات

- PHP 8.2+
- MySQL 8+
- Composer
- Node.js (Vite — واجهات Blade)

```bash
composer install
cp .env.example .env
php artisan key:generate
# عدّل DB_* في .env
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

---

## الدخول السريع

| الدور | المسار | حساب تجريبي |
|-------|--------|-------------|
| أدمن | `/admin/login` | انظر `database/seeders/DEMO_ACCOUNTS.md` |
| طبيب | `/doctor/login` | `07708888001` / `password123` |
| API مريض | `POST /api/v1/auth/login` | `07708888000` / `password123` |

مجموعة Postman المرجعية: `iraq-doctors.json` (سيُعاد تسميتها لاحقاً).

---

## الهيكل (Modular Monolith)

```
Modules/
├── Admin/           # لوحة المنصة
├── Auth/            # مستخدمين + OTP + Devices
├── Doctor/          # ملف الطبيب + فروع + جداول
├── Appointment/     # مواعيد
├── MedicalRecord/   # سجل طبي / روشتات
├── Subscription/    # باقات واشتراكات
├── Review/          # تقييمات
└── StaticPage/      # صفحات ثابتة
```

---

## خارج النطاق (لا يُعاد بناؤه)

- صيدليات كأعمال marketplace  
- معامل تحاليل كأعمال marketplace  
- كتالوج أدوية للبيع أونلاين  

**داخل النطاق لاحقاً:** Laboratory و Radiology كطلبات/نتائج *داخل العيادة* مرتبطة بالزيارة (حسب الـ BRD).

---

## الخطوات القادمة (Phase A)

1. كيان **Clinic** وربطه بالاشتراك  
2. دور **secretary** + صلاحيات  
3. توسيع ملف المريض (رقم ملف، QR، حقول BRD)  
4. Reception + Queue كأساس لدورة العمل اليومية
