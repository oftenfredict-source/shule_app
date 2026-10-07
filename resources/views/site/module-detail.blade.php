@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-module-detail-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="sx-module-detail-banner__glow sx-module-detail-banner__glow--a" aria-hidden="true"></div>
    <div class="sx-module-detail-banner__glow sx-module-detail-banner__glow--b" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12 text-center">
                <h1 class="text-white">{{ $module['title'] }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.modules') }}">{{ __('site.modules') }}</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.module', $module['slug']) }}">{{ $module['title'] }}</a>
                </p>
                @if(!empty($module['short']))
                    <p class="sx-module-detail-lead text-white">{{ $module['short'] }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="course-details-area section-gap">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 left-contents">
                <div class="main-image mb-30 sx-module-hero-icon">
                    <i class="fa {{ $module['icon'] }}" aria-hidden="true"></i>
                </div>
                <h2 style="color:#940000;font-weight:800;margin-bottom:16px;">{{ $module['title'] }}</h2>
                <p style="font-size:15px;line-height:1.8;">{{ $module['body'] }}</p>
                <h4 class="mt-40 mb-20" style="color:#222;">Key capabilities</h4>
                <ul class="sx-point-list">
                    @foreach($module['points'] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>

                @if(!empty($module['seo_sections']))
                    <div class="mt-40 sx-module-seo-content">
                        @foreach($module['seo_sections'] as $section)
                            <h3 style="color:#940000;font-weight:800;margin:28px 0 12px;font-size:20px;">{{ $section['heading'] }}</h3>
                            <p style="font-size:15px;line-height:1.85;color:#333;">{{ $section['text'] }}</p>
                        @endforeach
                        @if(($module['slug'] ?? '') === 'lesson-plans')
                            <p style="font-size:15px;line-height:1.85;color:#333;margin-top:16px;">
                                Related: <a href="{{ route('site.module', 'scheme-of-work') }}">Scheme of Work</a>,
                                <a href="{{ route('site.module', 'timetable-academics') }}">Timetable</a>,
                                <a href="{{ route('site.module', 'teacher-portal') }}">Teacher Portal</a>.
                            </p>
                        @elseif(($module['slug'] ?? '') === 'scheme-of-work')
                            <p style="font-size:15px;line-height:1.85;color:#333;margin-top:16px;">
                                Related: <a href="{{ route('site.module', 'lesson-plans') }}">Lesson Plans</a>,
                                <a href="{{ route('site.module', 'timetable-academics') }}">Timetable</a>,
                                <a href="{{ route('site.module', 'teacher-portal') }}">Teacher Portal</a>.
                            </p>
                        @endif
                    </div>
                @endif

                <div class="mt-40 sx-module-faq">
                    <h3 style="color:#222;font-weight:800;margin-bottom:16px;">Common questions</h3>
                    <div class="mb-20">
                        <h4 style="font-size:16px;color:#940000;font-weight:700;">What does {{ $module['title'] }} do in ShuleXpert?</h4>
                        <p style="font-size:14px;line-height:1.7;color:#444;">{{ $module['body'] }}</p>
                    </div>
                    <div class="mb-20">
                        <h4 style="font-size:16px;color:#940000;font-weight:700;">What are the key capabilities?</h4>
                        <p style="font-size:14px;line-height:1.7;color:#444;">{{ implode(', ', $module['points'] ?? []) }}</p>
                    </div>
                    @if(($module['slug'] ?? '') === 'lesson-plans')
                        <div class="mb-20">
                            <h4 style="font-size:16px;color:#940000;font-weight:700;">Is this digital lesson plan software for teachers?</h4>
                            <p style="font-size:14px;line-height:1.7;color:#444;">Yes, create from sessions, reuse plans, send to admin, get supervisor signature, download PDF (mpango wa somo online).</p>
                        </div>
                        <div class="mb-20">
                            <h4 style="font-size:16px;color:#940000;font-weight:700;">Is Lesson Plans separate from Scheme of Work?</h4>
                            <p style="font-size:14px;line-height:1.7;color:#444;">Yes. Scheme of Work is the long-range plan; Lesson Plans are period-by-period. They are two modules.</p>
                        </div>
                    @elseif(($module['slug'] ?? '') === 'scheme-of-work')
                        <div class="mb-20">
                            <h4 style="font-size:16px;color:#940000;font-weight:700;">Is this scheme of work software for schools?</h4>
                            <p style="font-size:14px;line-height:1.7;color:#444;">Yes, create/manage schemes, reuse yearly, track progress, export PDF/Excel.</p>
                        </div>
                        <div class="mb-20">
                            <h4 style="font-size:16px;color:#940000;font-weight:700;">How is it different from Lesson Plans?</h4>
                            <p style="font-size:14px;line-height:1.7;color:#444;">Scheme maps topics across months; Lesson Plans deliver each period. Separate modules on purpose.</p>
                        </div>
                    @endif
                    <div class="mb-10">
                        <h4 style="font-size:16px;color:#940000;font-weight:700;">How can a school get this module?</h4>
                        <p style="font-size:14px;line-height:1.7;color:#444;">
                            Request a demo via <a href="{{ route('site.contact') }}">Contact</a> or review
                            <a href="{{ route('pricing') }}">pricing</a> (installation plus monthly per-student fee).
                        </p>
                    </div>
                </div>

                <div class="mt-40">
                    <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase">Request Demo</a>
                    <a href="{{ route('site.modules') }}" class="primary-btn wh text-uppercase ml-10">All Modules</a>
                </div>
            </div>
            <div class="col-lg-4 right-contents">
                <ul>
                    <li>
                        <a class="justify-content-between d-flex" href="#">
                            <p>Module</p>
                            <span class="or">{{ $module['title'] }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="justify-content-between d-flex" href="{{ route('pricing') }}">
                            <p>Pricing</p>
                            <span>View plans</span>
                        </a>
                    </li>
                    <li>
                        <a class="justify-content-between d-flex" href="{{ route('login') }}">
                            <p>Access</p>
                            <span>School Login</span>
                        </a>
                    </li>
                    <li>
                        <a class="justify-content-between d-flex" href="{{ route('site.contact') }}">
                            <p>Support</p>
                            <span>Contact</span>
                        </a>
                    </li>
                </ul>
                <a href="{{ route('online_application') }}" class="primary-btn text-uppercase mt-20" style="display:block;">Online Application</a>

                <h4 class="mt-40 mb-20" style="color:#940000;">Other modules</h4>
                @foreach(collect($modules)->where('slug', '!=', $module['slug'])->take(8) as $other)
                        <div style="border-bottom:1px solid #f0dada;padding:10px 0;">
                            <a href="{{ route('site.module', $other['slug']) }}" style="color:#222;font-weight:600;">
                                <i class="fa {{ $other['icon'] }}" style="color:#940000;margin-right:8px;"></i>{{ $other['title'] }}
                            </a>
                        </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-module-detail-banner.about-banner {
    min-height: 340px !important;
    padding-top: 168px !important;
    padding-bottom: 72px !important;
    position: relative;
    overflow: hidden;
}
.sx-module-detail-banner__glow {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
}
.sx-module-detail-banner__glow--a {
    width: 320px; height: 320px;
    top: -80px; right: -60px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
}
.sx-module-detail-banner__glow--b {
    width: 260px; height: 260px;
    bottom: -100px; left: -40px;
    background: radial-gradient(circle, rgba(196,58,58,0.35) 0%, transparent 70%);
}
.sx-module-detail-banner .container { position: relative; z-index: 2; }
.sx-module-detail-banner .about-content h1 {
    font-size: 36px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sx-module-detail-lead {
    max-width: 640px;
    margin: 14px auto 0;
    opacity: .92;
    font-size: 15px;
    line-height: 1.7;
}
@media (max-width: 991.98px) {
    .sx-module-detail-banner.about-banner {
        min-height: 300px !important;
        padding-top: 148px !important;
        padding-bottom: 60px !important;
    }
    .sx-module-detail-banner .about-content h1 { font-size: 28px; }
}
</style>
@endpush

@push('schema')
@php
    $moduleUrl = url('/modules/' . $module['slug']);
    $moduleImage = !empty($seo['image']) ? $seo['image'] : url('/images/shuleXpert.jpg');

    $webpage = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => $moduleUrl . '#webpage',
        'url' => $moduleUrl,
        'name' => $module['title'] . ', ShuleXpert',
        'description' => $module['short'],
        'inLanguage' => 'en-TZ',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'ShuleXpert',
            'url' => url('/'),
        ],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => $moduleImage,
        ],
        'about' => ['@id' => $moduleUrl . '#service'],
    ];

    $svc = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        '@id' => $moduleUrl . '#service',
        'name' => $module['title'],
        'alternateName' => $module['title'] . ', ShuleXpert',
        'description' => $module['body'],
        'serviceType' => 'School management software module',
        'category' => 'EducationTechnology',
        'provider' => [
            '@type' => 'Organization',
            'name' => 'ShuleXpert',
            'url' => url('/'),
            'parentOrganization' => [
                '@type' => 'Organization',
                'name' => 'EmCa Techonology',
                'url' => 'https://emca.tech',
            ],
        ],
        'areaServed' => [
            '@type' => 'Place',
            'name' => 'Worldwide',
        ],
        'url' => $moduleUrl,
        'image' => $moduleImage,
        'isRelatedTo' => [
            '@type' => 'SoftwareApplication',
            'name' => 'ShuleXpert',
            'url' => url('/'),
        ],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => $module['title'] . ' capabilities',
            'itemListElement' => collect($module['points'] ?? [])->values()->map(function ($point, $i) {
                return [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Service',
                        'name' => $point,
                        'position' => $i + 1,
                    ],
                ];
            })->all(),
        ],
    ];

    $faqEntities = [
        [
            '@type' => 'Question',
            'name' => 'What does the ' . $module['title'] . ' module do in ShuleXpert?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $module['body'],
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'What are the key capabilities of ' . $module['title'] . '?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => implode(' ', $module['points'] ?? []),
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'How can a school get ' . $module['title'] . ' in ShuleXpert?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Contact EmCa Techonology via the ShuleXpert contact page or request a demo. Pricing is listed on the ShuleXpert pricing page (installation plus monthly per-student fee).',
            ],
        ],
    ];

    if (($module['slug'] ?? '') === 'lesson-plans') {
        $faqEntities = array_merge($faqEntities, [
            [
                '@type' => 'Question',
                'name' => 'Is ShuleXpert a digital lesson plan software for teachers?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. ShuleXpert Lesson Plans lets teachers create digital lesson plans from class sessions, reuse previous plans, send plans to admin, receive supervisor signatures, and download PDF lesson plans, built for schools in Tanzania and Africa.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Can admins sign lesson plans in ShuleXpert?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. Teachers send lesson plans to admin. Admins filter by subject and class, open the plan, and Sign Lesson Plan with a digital supervisor signature.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Is Lesson Plans separate from Scheme of Work?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. Scheme of Work is the long-range subject plan. Lesson Plans are period-by-period teaching plans. They are separate modules in ShuleXpert.',
                ],
            ],
        ]);
    } elseif (($module['slug'] ?? '') === 'scheme-of-work') {
        $faqEntities = array_merge($faqEntities, [
            [
                '@type' => 'Question',
                'name' => 'Is ShuleXpert a scheme of work software for schools?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. Teachers create and manage schemes of work by subject, reuse existing schemes, and export PDF or Excel. Admins track Scheme of Work Progress across the school.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'How is Scheme of Work different from Lesson Plans?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Scheme of Work maps topics across months for a subject. Lesson Plans deliver each teaching period. ShuleXpert keeps them as two separate modules.',
                ],
            ],
        ]);
    }

    $faq = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqEntities,
    ];
@endphp
<script type="application/ld+json">{!! json_encode($webpage, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($svc, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($faq, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
