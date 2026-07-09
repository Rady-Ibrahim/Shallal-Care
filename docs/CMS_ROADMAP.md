# Shallal Care — Clinic Management System Roadmap

**Base commit:** `fa87f02` (iraq-doctors freeze)  
**Product:** Clinic Management System (CMS) — SaaS للعيادات الخاصة  
**Out of scope:** صيدليات ومعامل كـ marketplace / أعمال مستقلة

---

## ما نحتفظ به من الكود الحالي (Reuse)

| Module | لماذا يبقى |
|--------|------------|
| `Auth` | Users, OTP, Sanctum, sessions — سيُوسَّع بـ secretary |
| `Doctor` | ملف الطبيب، الفروع، الجداول، الداشبورد |
| `Appointment` | نواة المواعيد → تُحوَّل لاحقاً لـ Queue + Visit |
| `MedicalRecord` | نواة السجل الطبي / الروشتات |
| `Subscription` | نواة SaaS — يحتاج توسيع (عيادات، حدود مستخدمين) |
| `Admin` | لوحة المنصة الرئيسية |
| `Review` | اختياري لفترة؛ يمكن إيقافه من الواجهة لاحقاً |
| `StaticPage` | صفحات قانونية للنظام |

---

## ما تم إزالته في مرحلة التنظيف

- جداول DB: `pharmacies*`, `laboratories*`, `lab_tests*`, `medicines*`, …
- أدوار `pharmacy` / `laboratory`
- أعمدة marketplace على `medical_records` و `reviews`
- `RecalculateProductScoresCommand` (Module Product غير موجود)

> ملاحظة: `lab_test` / `imaging` في واجهة السجلات = أنواع عرض داخل عيادات (تُخزَّن كـ `report`) وليست معامل خارجية.

---

## BRD Modules → خطة بناء

### Phase A — Foundations (الخطوة القادمة)
1. **Clinic** entity (عيادة ↔ اشتراك ↔ فروع ↔ مستخدمين)
2. دور **secretary** + صلاحيات أساسية
3. توسيع ملف المريض (حقول الـ BRD + رقم ملف + QR)
4. فصل سياق: Admin (المنصة) vs Clinic (طبيب/سكرتيرة)

### Phase B — Reception & Queue
- Reception dashboard
- Queue states (منتظر / داخل / تم / ملغي / لم يحضر)
- تحصيل كشف + طرق الدفع → Treasury

### Phase C — Doctor Visit
- زيارة كاملة (شكوى، فحص، تشخيص، خطة، أدوية، طلبات)
- صلاحيات: طبيب يكتب التشخيص؛ سكرتيرة لا تعدّل

### Phase D — Clinical records (BRD جزء 2)
- روشتة PDF احترافية
- Medical timeline
- Radiology / Laboratory *داخل العيادة* (طلبات + نتائج مرفوعة)

### Phase E — Finance & Reports
- Expenses, Treasury, Daily/Monthly reports
- Calendar enhancements

### Phase F — Platform SaaS
- باقات مفصّلة (أطباء / سكرتارية / فروع / تخزين)
- بوابات دفع + فواتير
- إيقاف عيادة بعد انتهاء الاشتراك

---

## أدوار المستخدمين المستهدفة

| الدور | النطاق |
|-------|--------|
| `admin` | لوحة المنصة (كل العيادات + الاشتراكات) |
| `doctor` | الكشف، التشخيص، الروشتة |
| `secretary` | استقبال، مواعيد، دور، تحصيل، نتائج مرفوعة |
| `patient` | (اختياري) تطبيق مريض أو بوابة — يمكن تأجيله |

---

## قرار معماري مهم

**Laboratory / Radiology في الـ BRD** ≠ معامل/مراكز أشعة marketplace.  
هما وحدات تشغيل داخل العيادة مرتبطة بالمريض والزيارة.

الصيدليات ومعامل التحاليل كأعمال مستقلة **خارج النطاق** لهذا المنتج.
