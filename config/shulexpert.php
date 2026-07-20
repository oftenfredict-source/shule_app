<?php

return [
    'phone_e164' => '+255749719998',
    'phone_display' => '+255 749 719 998',
    'email' => 'emca@emca.tech',
    'address' => 'Moshi, Kilimanjaro',

    /**
     * Public website base URL for shareable links (copy link, sitemap, etc.).
     * Always use production domain — not local Laragon / artisan serve.
     */
    'public_url' => rtrim((string) env('SHULEXPERT_PUBLIC_URL', 'https://shulexpert.com'), '/'),
];
