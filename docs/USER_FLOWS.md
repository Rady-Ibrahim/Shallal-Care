# فلو المستخدمين — Shallal Care CMS

هذا المستند يصف الحالة الحالية للكود (ميراث من iraq-doctors عند الكوميت `fa87f02`) أثناء التحويل إلى **Clinic Management System**.

**آخر تحديث:** يوليو 2026 — تنظيف marketplace (صيدليات/معامل خارجية).

---

## حالة الحالي

| المجال | الحالة |
|--------|--------|
| تسجيل دكتور + OTP + موافقة أدمن | ✅ |
| اشتراكات + تدقيق + تجديدات أدمن | ✅ |
| طلبات مواعيد (قبول/رفض/إتمام) | ✅ |
| سجل طبي مشترك من طرف الطبيب | ✅ |
| روشتات مبسطة من لوحة الطبيب | ✅ جزئياً |
| جلسات منفصلة أدمن/دكتور | ✅ cookies منفصلة |
| إشعارات push عبر OneSignal | ✅ اختياري لتطبيق المريض |
| Notifications يوم العيادات | ✅ في Dashboard الدكتور |

---

## 1. فلو الدكتور

### 1.1 التسجيل والموافقة

```
/doctor/register
  → بيانات شخصية + تخصص + محافظة + منطقة + عنوان + موقع (اختياري)
  → صورة شخصية + ترخيص + صورة عيادة
  → انتظار فحص أساس "العيادة الرئيسية"
  ↓
/doctor/verify-email  → OTP على الإيميل
  ↓
/doctor/pending  → انتظار موافقة الأدمن
  ↓
موافقة → /doctor/dashboard
رفض   → /doctor/rejected → إعادة رفع مستندات
```

### 1.2 الاشتراك

```
/doctor/dashboard/subscription/plans
  → عرض الباقات + إرسال الدفع (من إعدادات الأدمن)
  → اختيار باقة → رفع إيصال + الحالة = بانتظار الدفع
  ↓
pending_payment
  ↓
أدمن يراجع في /admin/dashboard/subscriptions
  ↓
active → تذكير تنتهي قبل 3 أيام من الانتهاء (cron)
```

### 1.3 طلبات المواعيد

```
مريض يحجز (API) → pending
  ↓
/doctor/dashboard/requests
  → معلق: [قبول] / [رفض]
  → مؤكد: [إتمام]
  → مكتمل: [إنشاء سجل طبي]
```

### 1.4 صفحات الداشبورد

| الصفحة | المسار |
|--------|--------|
| الرئيسية | `/doctor/dashboard` |
| طلبات المواعيد | `/doctor/dashboard/requests` |
| الاشتراكات | `/doctor/dashboard/subscription/plans` |
| المرضى | `/doctor/dashboard/patients` |
| الروشتات | `/doctor/dashboard/prescriptions` |
| السجلات الطبية | `/doctor/dashboard/records` |
| التقويم | `/doctor/dashboard/calendar` |
| الإعدادات | `/doctor/dashboard/settings` |

---

## جلسات منفصلة

- الأدمن: `ADMIN_SESSION_COOKIE` (افتراضي: `shallal_care_admin_session`)
- الدكتور: `DOCTOR_SESSION_COOKIE` (افتراضي: `shallal_care_doctor_session`)

```
ADMIN_SESSION_COOKIE=shallal_care_admin_session
DOCTOR_SESSION_COOKIE=shallal_care_doctor_session
```

---

## ملاحظات التحويل إلى CMS

ما تم تنظيفه:
- جداول marketplace: صيدليات، معامل، أدوية، طلبات
- أدوار `pharmacy` / `laboratory` من `users.role`
- أعمدة FK في `medical_records` و `reviews`
- أمر `catalog:recalculate-product-scores` (منتج ميت)

ما سيُبنى لاحقاً حسب الـ BRD:
- دور السكرتيرة (Reception / Queue / Finance)
- Clinic entity (عيادة متعددة الفروع تحت SaaS)
- Doctor Visit workflow كامل
- Laboratory / Radiology كوحدات *داخل العيادة* (مش marketplace)
- صلاحيات Permissions دقيقة
- خزنة يومية + مصروفات + تقارير

انظر: `docs/CMS_ROADMAP.md`
