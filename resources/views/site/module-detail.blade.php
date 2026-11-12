@extends('layouts.site')

@php
    // One realistic school photo for every module page (no cartoon art).
    $modulePhotoRel = 'images/modules-school-hero.jpg';
    if (!is_file(public_path($modulePhotoRel))) {
        $modulePhotoRel = 'images/services-hero.jpg';
    }
    $heroUrl = asset($modulePhotoRel);
    $photoUrl = $heroUrl;

    $points = collect($module['points'] ?? [])->take(6)->values();

    $otherModules = collect($modules ?? [])
        ->where('slug', '!=', $module['slug'])
        ->take(3)
        ->values();

    $faqItems = [
        [
            'q' => __('site.module_faq_what_q', ['title' => $module['title']]),
            'a' => $module['short'],
        ],
        [
            'q' => __('site.module_faq_get_q'),
            'a' => __('site.module_faq_get_a'),
        ],
    ];

    if (($module['slug'] ?? '') === 'lesson-plans') {
        $faqItems = [
            [
                'q' => __('site.module_faq_lp_vs_scheme_q'),
                'a' => __('site.module_faq_lp_vs_scheme_a'),
            ],
            [
                'q' => __('site.module_faq_get_q'),
                'a' => __('site.module_faq_get_a'),
            ],
        ];
    } elseif (($module['slug'] ?? '') === 'scheme-of-work') {
        $faqItems = [
            [
                'q' => __('site.module_faq_sow_vs_lp_q'),
                'a' => __('site.module_faq_sow_vs_lp_a'),
            ],
            [
                'q' => __('site.module_faq_get_q'),
                'a' => __('site.module_faq_get_a'),
            ],
        ];
    }
@endphp

@section('content')
<section class="banner-area relative about-banner sx-module-page-banner" id="home" style="background-image:url('{{ $heroUrl }}')">
    <div class="sx-module-page-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ $module['title'] }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('site.modules') }}">{{ __('site.modules') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('site.module', $module['slug']) }}">{{ $module['title'] }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-module-page">
    <div class="container sx-module-block">
        <div class="sx-module-intro text-center">
            <p class="sx-module-intro__lead mb-0">{{ $module['short'] }}</p>
        </div>
    </div>

    <div class="container sx-module-block">
        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="sx-module-main-panel">
                    <div class="sx-module-main-top">
                        <div class="sx-module-photo sx-module-photo--compact">
                            <img src="{{ $photoUrl }}" alt="{{ $module['title'] }}" loading="eager">
                            <div class="sx-module-photo__badge">
                                <span class="sx-module-photo__icon"><i class="fa {{ $module['icon'] }}"></i></span>
                                <strong>{{ $module['title'] }}</strong>
                            </div>
                        </div>
                        <div>
                            <p class="sx-module-kicker">{{ __('site.module_overview') }}</p>
                            <h2 class="sx-module-heading">{{ $module['title'] }}</h2>
                            <p class="sx-module-text mb-0">{{ $module['body'] }}</p>
                        </div>
                    </div>

                    <div class="sx-module-caps-wrap">
                        <h3 class="sx-module-subhead">{{ __('site.module_capabilities') }}</h3>
                        <ul class="sx-module-caps">
                            @foreach($points as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                    </div>

                    @if(in_array($module['slug'] ?? '', ['lesson-plans', 'scheme-of-work'], true))
                        <p class="sx-module-related-line mb-0">
                            {{ __('site.related') }}:
                            @if(($module['slug'] ?? '') === 'lesson-plans')
                                <a href="{{ route('site.module', 'scheme-of-work') }}">{{ __('site.scheme_of_work') }}</a>,
                            @else
                                <a href="{{ route('site.module', 'lesson-plans') }}">{{ __('site.lesson_plans') }}</a>,
                            @endif
                            <a href="{{ route('site.module', 'timetable-academics') }}">{{ __('site.timetable') }}</a>,
                            <a href="{{ route('site.module', 'teacher-portal') }}">{{ __('site.teacher_portal') }}</a>
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sx-module-side-cta">
                    <h3>{{ __('site.module_next_steps') }}</h3>
                    <ul>
                        <li><a href="{{ route('site.contact') }}"><i class="fa fa-angle-right"></i> {{ __('site.request_demo') }}</a></li>
                        <li><a href="{{ route('pricing') }}"><i class="fa fa-angle-right"></i> {{ __('about.view_pricing') }}</a></li>
                        <li><a href="{{ route('site.modules') }}"><i class="fa fa-angle-right"></i> {{ __('site.all_modules') }}</a></li>
                        <li><a href="{{ route('login') }}"><i class="fa fa-angle-right"></i> {{ __('site.school_login') }}</a></li>
                    </ul>
                    <a href="{{ route('site.contact') }}" class="btn btn-light btn-block">{{ __('site.get_in_touch') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container sx-module-block">
        <div class="sx-module-faq-panel">
            <p class="sx-module-kicker">{{ __('site.module_faq_kicker') }}</p>
            <h2 class="sx-module-heading sx-module-heading--sm">{{ __('site.module_faq_title') }}</h2>
            <div class="row">
                @foreach($faqItems as $item)
                    <div class="col-md-6">
                        <div class="sx-module-faq-item">
                            <h4>{{ $item['q'] }}</h4>
                            <p>{{ $item['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if($otherModules->isNotEmpty())
        <div class="container sx-module-block sx-module-block--last">
            <div class="sx-module-section-head">
                <h2 class="sx-module-heading sx-module-heading--sm mb-0">{{ __('site.other_modules') }}</h2>
            </div>
            <div class="row">
                @foreach($otherModules as $other)
                    <div class="col-md-4 mb-20">
                        <a href="{{ route('site.module', $other['slug']) }}" class="sx-module-related-card">
                            <span class="sx-module-related-card__icon"><i class="fa {{ $other['icon'] }}"></i></span>
                            <strong>{{ $other['title'] }}</strong>
                            <span>{{ $other['short'] }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection

@push('styles')
<style>
.sx-public-site .banner-area.sx-module-page-banner.about-banner,
.sx-module-page-banner.about-banner {
    min-height: 260px !important;
    height: 260px !important;
    padding: 0 !important;
    position: relative;
    overflow: hidden;
    background-color: #5c0000 !important;
    background-size: cover !important;
    background-position: center 35% !important;
    background-repeat: no-repeat !important;
    display: flex;
    align-items: center;
}
.sx-module-page-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(92,0,0,.62) 0%, rgba(148,0,0,.46) 55%, rgba(26,10,10,.38) 100%);
    z-index: 1;
}
.sx-module-page-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-module-page-banner .about-content h1 {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-module-page-banner .link-nav a { color: #fff !important; }

.sx-module-page { padding-bottom: 16px; }
.sx-module-page > .container.sx-module-block {
    padding-top: 36px !important;
    padding-bottom: 0 !important;
}
.sx-module-page > .container.sx-module-block.sx-module-block--last {
    padding-bottom: 28px !important;
}
@media (max-width: 991.98px) {
    .sx-module-page > .container.sx-module-block.sx-module-block--last {
        padding-bottom: 70px !important;
    }
    .sx-module-page-banner.about-banner {
        min-height: 220px !important;
        height: 220px !important;
    }
    .sx-module-page-banner .about-content h1 { font-size: 24px; }
}

.sx-module-intro {
    max-width: 720px;
    margin: 0 auto;
    background: #fff8f8;
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    padding: 18px 24px;
}
.sx-module-intro__lead {
    color: #444;
    font-size: 15px;
    line-height: 1.65;
}
.sx-module-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.sx-module-heading {
    color: #1a1a1a;
    font-weight: 800;
    font-size: 24px;
    line-height: 1.3;
    margin: 0 0 10px;
}
.sx-module-heading--sm { font-size: 18px; }
.sx-module-subhead {
    color: #1a1a1a;
    font-weight: 800;
    font-size: 16px;
    margin: 0 0 12px;
}
.sx-module-text {
    color: #555;
    font-size: 14.5px;
    line-height: 1.7;
    margin-bottom: 12px;
}
.sx-module-section-head { margin-bottom: 16px; }

.sx-module-main-panel {
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    background: #fff;
    padding: 22px 20px;
}
.sx-module-main-top {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 18px;
    align-items: start;
    margin-bottom: 18px;
}
@media (max-width: 767.98px) {
    .sx-module-main-top { grid-template-columns: 1fr; }
}
.sx-module-photo {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #f0e4e4;
    background: #fff5f5;
}
.sx-module-photo--compact img {
    width: 100%;
    height: 140px;
    object-fit: cover;
    display: block;
}
.sx-module-photo__badge {
    position: absolute;
    left: 8px;
    right: 8px;
    bottom: 8px;
    background: #940000;
    color: #fff;
    padding: 8px 10px;
    border-radius: 8px;
}
.sx-module-photo__icon {
    display: inline-flex;
    width: 22px;
    height: 22px;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.18);
    border-radius: 50%;
    margin-bottom: 4px;
    font-size: 11px;
}
.sx-module-photo__badge strong {
    display: block;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.3;
}

.sx-module-caps-wrap {
    border-top: 1px solid #f0e4e4;
    padding-top: 16px;
}
.sx-module-caps {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 14px;
}
@media (max-width: 575.98px) {
    .sx-module-caps { grid-template-columns: 1fr; }
}
.sx-module-caps li {
    position: relative;
    padding: 10px 12px 10px 32px;
    background: #fff8f8;
    border: 1px solid #f0e4e4;
    border-radius: 10px;
    color: #444;
    font-size: 13px;
    line-height: 1.45;
    font-weight: 600;
}
.sx-module-caps li:before {
    content: "\f00c";
    font-family: FontAwesome, "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    left: 11px;
    top: 11px;
    color: #940000;
    font-size: 12px;
}
.sx-module-related-line {
    margin-top: 16px;
    color: #666;
    font-size: 13.5px;
}
.sx-module-related-line a {
    color: #940000;
    font-weight: 700;
}

.sx-module-side-cta {
    background: #940000;
    color: #fff;
    border-radius: 14px;
    padding: 20px 18px;
    height: auto;
    position: sticky;
    top: 90px;
}
.sx-module-side-cta h3 {
    color: #fff;
    font-size: 18px;
    font-weight: 800;
    margin: 0 0 12px;
}
.sx-module-side-cta ul {
    list-style: none;
    margin: 0 0 14px;
    padding: 0;
}
.sx-module-side-cta li { margin-bottom: 8px; }
.sx-module-side-cta a {
    color: #ffe3e3 !important;
    text-decoration: none !important;
    font-weight: 600;
    font-size: 13.5px;
}
.sx-module-side-cta a:hover { color: #fff !important; }
.sx-module-side-cta .btn-light {
    background: #fff !important;
    color: #940000 !important;
    border: 0 !important;
    font-weight: 800;
}

.sx-module-faq-panel {
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    background: #fff;
    padding: 20px 20px 8px;
}
.sx-module-faq-item {
    padding: 4px 0 14px;
}
.sx-module-faq-item h4 {
    color: #940000;
    font-size: 14px;
    font-weight: 800;
    margin: 0 0 6px;
}
.sx-module-faq-item p {
    color: #555;
    font-size: 13.5px;
    line-height: 1.6;
    margin: 0;
}

.sx-module-related-card {
    display: block;
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 14px;
    height: 100%;
    text-decoration: none !important;
}
.sx-module-related-card__icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #fff0f0;
    color: #940000;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
}
.sx-module-related-card strong {
    display: block;
    color: #1a1a1a;
    font-size: 14px;
    font-weight: 800;
    margin-bottom: 4px;
}
.sx-module-related-card span:last-child {
    display: block;
    color: #666;
    font-size: 12.5px;
    line-height: 1.45;
}
.mb-20 { margin-bottom: 20px; }
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
        'inLanguage' => app()->getLocale() === 'sw' ? 'sw-TZ' : 'en-TZ',
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
        'description' => $module['body'],
        'serviceType' => 'School management software module',
        'provider' => [
            '@type' => 'Organization',
            'name' => 'ShuleXpert',
            'url' => url('/'),
            'parentOrganization' => [
                '@type' => 'Organization',
                'name' => 'EmCa Techonologies',
                'url' => 'https://emca.tech',
            ],
        ],
        'url' => $moduleUrl,
        'image' => $moduleImage,
    ];

    // Keep SEO section text in schema only (not shown on page)
    if (!empty($module['seo_sections'])) {
        $svc['additionalProperty'] = collect($module['seo_sections'])->map(function ($section) {
            return [
                '@type' => 'PropertyValue',
                'name' => $section['heading'],
                'value' => $section['text'],
            ];
        })->values()->all();
    }

    $faq = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faqItems)->map(function ($item) {
            return [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['a'],
                ],
            ];
        })->values()->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($webpage, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($svc, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($faq, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
