@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">Download &amp; Apply</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.download') }}">Download App</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-30">
                <img class="img-fluid" src="{{ asset('images/teacher.png') }}" alt="ShuleXpert mobile app for teachers and parents in Tanzania" width="600" height="auto" loading="eager">
            </div>
            <div class="col-lg-6">
                <h2 style="color:#940000;font-weight:800;">ShuleXpert on mobile</h2>
                <p style="font-size:15px;line-height:1.8;margin-top:14px;">
                    Stay connected with real-time alerts for teachers and parents. Download the Android APK to access
                    sessions, notifications and school updates on the go — anywhere in Tanzania and beyond.
                </p>
                <ul class="sx-point-list mt-30">
                    <li>Teacher session &amp; timetable alerts</li>
                    <li>Parent notifications for results &amp; attendance</li>
                    <li>Faster communication between school and home</li>
                </ul>
                <div class="mt-40 d-flex flex-wrap gap-2">
                    <a href="{{ asset('shuleXpert.apk') }}" class="primary-btn text-uppercase" download rel="nofollow">
                        <i class="fa fa-android"></i> Download Android APK
                    </a>
                    <a href="{{ route('online_application') }}" class="primary-btn wh text-uppercase">
                        <i class="fa fa-graduation-cap"></i> Online Application
                    </a>
                    <a href="{{ route('site.contact') }}" class="primary-btn wh text-uppercase">
                        <i class="fa fa-envelope"></i> Need Help?
                    </a>
                </div>
                <p class="sx-powered mt-30">Powered by EmCa Techonology</p>
            </div>
        </div>
    </div>
</section>

<section class="section-gap sx-download-apply" style="background:linear-gradient(180deg,#fff5f5 0%,#ffffff 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-30 mb-lg-0">
                <h2 style="color:#940000;font-weight:800;">Apply to school online</h2>
                <p style="font-size:15px;line-height:1.8;margin-top:14px;max-width:560px;">
                    Parents and guardians can browse participating schools, check available class spaces,
                    view school details, and submit a student application online — no paperwork queues.
                </p>
                <ul class="sx-point-list mt-20">
                    <li>Search schools by name, region or district</li>
                    <li>See classes with open admission spaces</li>
                    <li>Submit application and track status online</li>
                </ul>
            </div>
            <div class="col-lg-5 text-lg-end">
                <a href="{{ route('online_application') }}" class="primary-btn text-uppercase" style="min-width:240px;">
                    <i class="fa fa-pencil-square-o"></i> Start Online Application
                </a>
                <p class="small text-muted mt-3 mb-0">Free for parents · Schools on ShuleXpert</p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('schema')
@php
    $downloadPage = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => url('/download') . '#webpage',
        'url' => url('/download'),
        'name' => 'Download ShuleXpert App & Online School Application',
        'description' => 'Download the ShuleXpert Android APK or apply to participating schools online through ShuleXpert.',
        'inLanguage' => 'en-TZ',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'ShuleXpert',
            'url' => url('/'),
        ],
        'about' => [
            '@type' => 'SoftwareApplication',
            'name' => 'ShuleXpert',
        ],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => asset('images/teacher.png'),
        ],
    ];

    $mobileApp = [
        '@context' => 'https://schema.org',
        '@type' => 'MobileApplication',
        '@id' => url('/download') . '#mobileapp',
        'name' => 'ShuleXpert Mobile App',
        'operatingSystem' => 'Android',
        'applicationCategory' => 'EducationalApplication',
        'applicationSubCategory' => 'School Management',
        'description' => 'Android app for ShuleXpert teachers and parents — session alerts, attendance, results and school notifications.',
        'url' => url('/download'),
        'downloadUrl' => url('/shuleXpert.apk'),
        'installUrl' => url('/download'),
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'TZS',
            'availability' => 'https://schema.org/InStock',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => url('/'),
            'email' => 'emca@emca.tech',
            'telephone' => config('shulexpert.phone_e164'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Moshi',
                'addressRegion' => 'Kilimanjaro',
                'addressCountry' => 'TZ',
            ],
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'ShuleXpert',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('images/shuleXpert.jpg'),
            ],
        ],
    ];

    $onlineService = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        '@id' => route('online_application') . '#service',
        'name' => 'ShuleXpert Online School Application',
        'serviceType' => 'School admission and online application',
        'description' => 'Online school admission portal for parents to apply to participating ShuleXpert schools in Tanzania.',
        'url' => route('online_application'),
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Tanzania',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => url('/'),
        ],
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'TZS',
            'description' => 'Free online application for parents and guardians',
            'url' => route('online_application'),
        ],
    ];

    $breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Download App', 'item' => url('/download')],
        ],
    ];

    $faq = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'How do I download the ShuleXpert Android app?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Visit the ShuleXpert download page and tap Download Android APK. Install the file on your Android device to receive teacher and parent alerts from your school.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Can I apply to a school online with ShuleXpert?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. Use the Online Application page to search participating schools, view available class spaces, and submit a student application online.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Is the ShuleXpert mobile app free?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'The ShuleXpert Android APK is free to download for teachers and parents whose schools use ShuleXpert.',
                ],
            ],
        ],
    ];

    $siteNav = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'ShuleXpert download and application links',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Download Android APK', 'url' => url('/shuleXpert.apk')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Online School Application', 'url' => route('online_application')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => 'Contact Support', 'url' => url('/contact')],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($downloadPage, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($mobileApp, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($onlineService, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($faq, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($siteNav, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
