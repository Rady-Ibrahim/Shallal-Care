<?php

namespace Modules\Doctor\Support;

class SecretaryPermissions
{
    public const RECEPTION_VIEW = 'reception.view';

    public const RECEPTION_MANAGE = 'reception.manage';

    public const QUEUE_VIEW = 'queue.view';

    public const QUEUE_MANAGE = 'queue.manage';

    public const APPOINTMENTS_VIEW = 'appointments.view';

    public const APPOINTMENTS_MANAGE = 'appointments.manage';

    public const PATIENTS_VIEW = 'patients.view';

    public const PATIENTS_MANAGE = 'patients.manage';

    public const PRESCRIPTIONS_VIEW = 'prescriptions.view';

    public const PRESCRIPTIONS_MANAGE = 'prescriptions.manage';

    public const PRESCRIPTIONS_PRINT = 'prescriptions.print';

    public const RECORDS_VIEW = 'records.view';

    public const RECORDS_MANAGE = 'records.manage';

    public const LAB_VIEW = 'lab.view';

    public const LAB_MANAGE = 'lab.manage';

    public const REPORTS_VIEW = 'reports.view';

    public const FINANCE_VIEW = 'finance.view';

    public const FINANCE_COLLECT = 'finance.collect';

    public const CALENDAR_VIEW = 'calendar.view';

    public const SETTINGS_VIEW = 'settings.view';

    public const ALL = [
        self::RECEPTION_VIEW => 'عرض الاستقبال',
        self::RECEPTION_MANAGE => 'إدارة الاستقبال',
        self::QUEUE_VIEW => 'عرض الدور',
        self::QUEUE_MANAGE => 'إدارة الدور',
        self::APPOINTMENTS_VIEW => 'عرض المواعيد',
        self::APPOINTMENTS_MANAGE => 'إدارة المواعيد',
        self::PATIENTS_VIEW => 'عرض المرضى',
        self::PATIENTS_MANAGE => 'إضافة وتعديل المرضى',
        self::PRESCRIPTIONS_VIEW => 'عرض الروشتات',
        self::PRESCRIPTIONS_MANAGE => 'كتابة الروشتات',
        self::PRESCRIPTIONS_PRINT => 'طباعة الروشتات',
        self::RECORDS_VIEW => 'عرض السجلات الطبية',
        self::RECORDS_MANAGE => 'رفع ملفات ونتائج',
        self::LAB_VIEW => 'عرض التحاليل والأشعة',
        self::LAB_MANAGE => 'طلب تحاليل ورفع نتائج',
        self::REPORTS_VIEW => 'تقارير العيادة',
        self::FINANCE_VIEW => 'عرض الحسابات',
        self::FINANCE_COLLECT => 'تحصيل الرسوم',
        self::CALENDAR_VIEW => 'عرض التقويم',
        self::SETTINGS_VIEW => 'الإعدادات الشخصية',
    ];

    public const DEFAULT = [
        self::RECEPTION_VIEW,
        self::RECEPTION_MANAGE,
        self::QUEUE_VIEW,
        self::QUEUE_MANAGE,
        self::APPOINTMENTS_VIEW,
        self::PATIENTS_VIEW,
        self::PATIENTS_MANAGE,
        self::PRESCRIPTIONS_VIEW,
        self::PRESCRIPTIONS_MANAGE,
        self::PRESCRIPTIONS_PRINT,
        self::RECORDS_VIEW,
        self::LAB_VIEW,
        self::LAB_MANAGE,
        self::FINANCE_VIEW,
        self::FINANCE_COLLECT,
        self::CALENDAR_VIEW,
    ];

    public static function labels(): array
    {
        return self::ALL;
    }

    public static function isValid(string $permission): bool
    {
        return array_key_exists($permission, self::ALL);
    }

    public static function sanitize(array $permissions): array
    {
        return array_values(array_unique(array_filter(
            $permissions,
            fn (string $permission) => self::isValid($permission)
        )));
    }
}
