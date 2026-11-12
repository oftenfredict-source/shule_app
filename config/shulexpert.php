<?php

return [
    'phone_e164' => '+255749719998',
    'phone_display' => '+255 749 719 998',
    'email' => 'emca@emcatech.co.tz',
    'email_company' => 'emca@emcatech.co.tz',
    'address' => 'Ben Bella Street, Moshi Municipality',
    'address_line_2' => 'Opposite High Court of Tanzania',
    'po_box' => 'P.O. Box 20, Moshi - Kilimanjaro',
    'maps_url' => 'https://maps.google.com/?cid=3807362721384388336',

    'company_name' => 'EmCa Techonologies',
    'company_legal_name' => 'EmCa Techonologies LTD',
    'company_website' => 'https://emcatech.co.tz',

    'social_links' => [
        ['key' => 'facebook', 'url' => 'https://www.facebook.com/share/1TgUEHFpz5/', 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook'],
        ['key' => 'youtube', 'url' => 'https://www.youtube.com/@emcatechn', 'icon' => 'fab fa-youtube', 'label' => 'YouTube'],
        ['key' => 'instagram', 'url' => 'https://www.instagram.com/emcatechn', 'icon' => 'fab fa-instagram', 'label' => 'Instagram'],
        ['key' => 'tiktok', 'url' => 'https://tiktok.com/@emca.techn', 'icon' => 'fab fa-tiktok', 'label' => 'TikTok'],
    ],

    /** Popular modules shown in public footer ("Our Solutions"). */
    'footer_modules' => [
        ['label_key' => 'fees_title', 'slug' => 'fees-payments'],
        ['label_key' => 'biometric_title', 'slug' => 'attendance-biometric'],
        ['label_key' => 'exams_results', 'slug' => 'exams-results'],
        ['label_key' => 'lesson_plans', 'slug' => 'lesson-plans'],
        ['label_key' => 'parent_title', 'slug' => 'parent-portal'],
        ['label_key' => 'pillar_modules', 'slug' => null], // all modules index
    ],

    /**
     * Public website base URL for shareable links (copy link, sitemap, etc.).
     * Always use production domain - not local Laragon / artisan serve.
     */
    'public_url' => rtrim((string) env('SHULEXPERT_PUBLIC_URL', 'https://shulexpert.com'), '/'),
];
