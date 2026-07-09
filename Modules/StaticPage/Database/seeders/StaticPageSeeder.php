<?php

namespace Modules\StaticPage\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\StaticPage\Models\StaticPage;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title_ar' => 'سياسة الخصوصية',
                'title_en' => 'Privacy Policy',
                'content_ar' => '<h1>سياسة الخصوصية</h1><p>نحن نحترم خصوصيتك ونلتزم بحماية بياناتك الشخصية...</p>',
                'content_en' => '<h1>Privacy Policy</h1><p>We respect your privacy and are committed to protecting your personal data...</p>',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'slug' => 'terms-conditions',
                'title_ar' => 'الشروط والأحكام',
                'title_en' => 'Terms and Conditions',
                'content_ar' => '<h1>الشروط والأحكام</h1><p>باستخدامك لهذا التطبيق، أنت توافق على الالتزام بالشروط التالية...</p>',
                'content_en' => '<h1>Terms and Conditions</h1><p>By using this app, you agree to be bound by the following terms...</p>',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'slug' => 'about-us',
                'title_ar' => 'عن التطبيق',
                'title_en' => 'About Us',
                'content_ar' => '<h1>عن النظام</h1><p>Shallal Care هو نظام إدارة عيادات طبية يخدم الطبيب والسكرتيرة وإدارة المنصة...</p>',
                'content_en' => '<h1>About Us</h1><p>Shallal Care is a Clinic Management System for doctors, secretaries, and platform admins...</p>',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'slug' => 'contact-us',
                'title_ar' => 'اتصل بنا',
                'title_en' => 'Contact Us',
                'content_ar' => '<h1>اتصل بنا</h1><p>يمكنك التواصل معنا عبر...</p>',
                'content_en' => '<h1>Contact Us</h1><p>You can reach us via...</p>',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'slug' => 'faq',
                'title_ar' => 'الأسئلة الشائعة',
                'title_en' => 'FAQ',
                'content_ar' => '<h1>الأسئلة الشائعة</h1><p>إجابات على الأسئلة المتكررة...</p>',
                'content_en' => '<h1>FAQ</h1><p>Answers to frequently asked questions...</p>',
                'is_active' => true,
                'order' => 5,
            ],
        ];

        foreach ($pages as $page) {
            StaticPage::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
