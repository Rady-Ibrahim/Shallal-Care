<?php

return [

    'country' => 'EG',
    'country_name_ar' => 'مصر',
    'currency' => 'EGP',
    'currency_symbol' => 'ج.م',
    'phone_country_code' => '+20',
    'locale' => 'ar_EG',
    'timezone' => 'Africa/Cairo',

    'map' => [
        'default_latitude' => 30.0444,
        'default_longitude' => 31.2357,
        'default_zoom' => 12,
        'country' => 'مصر',
    ],

    'company' => [
        'name' => 'Shallal Tech',
        'name_ar' => 'شلال تك',
        'phone' => env('SHALLAL_TECH_PHONE', '01000000000'),
        'email' => env('SHALLAL_TECH_EMAIL', 'info@shallalcare.com'),
        'website' => env('SHALLAL_TECH_WEBSITE', 'https://shallalcare.com'),
    ],

    'prescription' => [
        'default_paper' => 'a4', // a4 | half_a4
        'number_prefix' => 'RX',
        'show_qr' => true,
        'show_diagnosis' => true,
        'show_stamp' => true,
        'show_signature' => true,
    ],

    'payment_methods' => [
        'cash' => 'كاش',
        'visa' => 'فيزا',
        'mastercard' => 'ماستركارد',
        'meeza' => 'ميزة',
        'instapay' => 'إنستا باي',
        'vodafone_cash' => 'فودافون كاش',
        'bank_transfer' => 'تحويل بنكي',
    ],

    'income_categories' => [
        'consultation' => 'كشف',
        'follow_up' => 'متابعة',
        'procedure' => 'إجراء طبي',
        'other_income' => 'إيراد آخر',
    ],

    'expense_categories' => [
        'rent' => 'إيجار',
        'salaries' => 'رواتب',
        'supplies' => 'مستلزمات طبية',
        'utilities' => 'مرافق (كهرباء/مياه)',
        'maintenance' => 'صيانة',
        'marketing' => 'تسويق',
        'other_expense' => 'مصروف آخر',
    ],

    'lab_tests' => [
        'cbc' => 'صورة دم كاملة (CBC)',
        'fbs' => 'سكر صائم',
        'hba1c' => 'HbA1c',
        'lipid_profile' => 'دهون (Lipid Profile)',
        'liver_function' => 'وظائف كبد',
        'kidney_function' => 'وظائف كلى',
        'thyroid' => 'غدة درقية (TSH)',
        'urine_analysis' => 'تحليل بول',
        'crp' => 'CRP',
        'vitamin_d' => 'فيتامين د',
    ],

    'radiology_types' => [
        'xray_chest' => 'أشعة صدر',
        'xray_bone' => 'أشعة عظام',
        'ultrasound_abdomen' => 'سونار بطن',
        'ultrasound_pelvis' => 'سونار حوض',
        'ecg' => 'رسم قلب (ECG)',
        'mri' => 'رنين مغناطيسي (MRI)',
        'ct' => 'أشعة مقطعية (CT)',
        'mammography' => 'ماموجرام',
    ],

    'diagnosis_templates' => [
        'التهاب الحلق الفيروسي',
        'التهاب الشعب الهوائية الحاد',
        'ارتفاع ضغط الدم',
        'داء السكري النوع الثاني — متابعة',
        'التهاب المعدة والأمعاء',
        'التهاب المسالك البولية',
        'آلام الظهر العضلي',
        'التهاب الجيوب الأنفية',
        'فقر الدم',
        'الربو — متابعة',
    ],

    'prescription_snippets' => [
        'يُنصح بشرب سوائل وفيرة والراحة',
        'متابعة بعد أسبوع أو عند استمرار الأعراض',
        'تجنب المجهود الشاق لمدة 3 أيام',
        'مراجعة نتائج التحاليل عند ظهورها',
    ],

];
