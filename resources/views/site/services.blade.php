@extends('layouts.site')

@php
    $pillars = [
        [
            'title' => __('services.pillar1_title'),
            'text' => __('services.pillar1_text'),
            'link' => route('site.modules'),
            'link_label' => __('services.pillar1_link'),
            'icon' => 'fa-desktop',
        ],
        [
            'title' => __('services.pillar2_title'),
            'text' => __('services.pillar2_text'),
            'link' => route('pricing'),
            'link_label' => __('services.pillar2_link'),
            'icon' => 'fa-graduation-cap',
        ],
        [
            'title' => __('services.pillar3_title'),
            'text' => __('services.pillar3_text'),
            'link' => route('site.contact'),
            'link_label' => __('services.pillar3_link'),
            'icon' => 'fa-comments',
        ],
    ];

    $services = [
        [
            'icon' => 'fa-cogs',
            'title' => __('services.svc1_title'),
            'text' => __('services.svc1_text'),
            'points' => [__('services.svc1_p1'), __('services.svc1_p2'), __('services.svc1_p3')],
        ],
        [
            'icon' => 'fa-graduation-cap',
            'title' => __('services.svc2_title'),
            'text' => __('services.svc2_text'),
            'points' => [__('services.svc2_p1'), __('services.svc2_p2'), __('services.svc2_p3')],
        ],
        [
            'icon' => 'fa-hand-o-up',
            'title' => __('services.svc3_title'),
            'text' => __('services.svc3_text'),
            'points' => [__('services.svc3_p1'), __('services.svc3_p2'), __('services.svc3_p3')],
        ],
        [
            'icon' => 'fa-comments',
            'title' => __('services.svc4_title'),
            'text' => __('services.svc4_text'),
            'points' => [__('services.svc4_p1'), __('services.svc4_p2'), __('services.svc4_p3')],
        ],
        [
            'icon' => 'fa-life-ring',
            'title' => __('services.svc5_title'),
            'text' => __('services.svc5_text'),
            'points' => [__('services.svc5_p1'), __('services.svc5_p2'), __('services.svc5_p3')],
        ],
        [
            'icon' => 'fa-shield',
            'title' => __('services.svc6_title'),
            'text' => __('services.svc6_text'),
            'points' => [__('services.svc6_p1'), __('services.svc6_p2'), __('services.svc6_p3')],
        ],
    ];

    $steps = [
        ['num' => '01', 'title' => __('services.step1_title'), 'text' => __('services.step1_text')],
        ['num' => '02', 'title' => __('services.step2_title'), 'text' => __('services.step2_text')],
        ['num' => '03', 'title' => __('services.step3_title'), 'text' => __('services.step3_text')],
        ['num' => '04', 'title' => __('services.step4_title'), 'text' => __('services.step4_text')],
    ];
@endphp

@section('content')
<section class="banner-area relative about-banner sx-svc-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="sx-svc-banner__glow sx-svc-banner__glow--a" aria-hidden="true"></div>
    <div class="sx-svc-banner__glow sx-svc-banner__glow--b" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12 text-center">
                <h1 class="text-white">{{ __('services.title') }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.services') }}">{{ __('services.title') }}</a>
                </p>
                <p class="sx-svc-lead text-white">
                    {{ __('services.lead') }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-svc-pillars">
    <div class="container">
        <div class="row">
            @foreach($pillars as $pillar)
                <div class="col-lg-4 col-md-4 mb-3 mb-lg-0">
                    <div class="sx-svc-pillar">
                        <span class="sx-svc-pillar__icon" aria-hidden="true"><i class="fa {{ $pillar['icon'] }}"></i></span>
                        <h3>{{ $pillar['title'] }}</h3>
                        <p>{{ $pillar['text'] }}</p>
                        <a href="{{ $pillar['link'] }}">{{ $pillar['link_label'] }} <i class="fa fa-angle-right"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="sx-svc-page section-gap">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-40 mb-lg-0">
                <div class="sx-svc-intro">
                    <p class="sx-svc-kicker">{{ __('services.kicker') }}</p>
                    <h2>{{ __('services.intro_title') }}</h2>
                    <p>{{ __('services.intro_text') }}</p>
                </div>

                <div class="row">
                    @foreach($services as $svc)
                        <div class="col-md-6 mb-30">
                            <article class="sx-svc-card">
                                <div class="sx-svc-card__top">
                                    <span class="sx-svc-card__icon" aria-hidden="true"><i class="fa {{ $svc['icon'] }}"></i></span>
                                    <h3>{{ $svc['title'] }}</h3>
                                </div>
                                <p>{{ $svc['text'] }}</p>
                                <ul>
                                    @foreach($svc['points'] as $point)
                                        <li>{{ $point }}</li>
                                    @endforeach
                                </ul>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="sx-svc-aside">
                    <div class="sx-svc-aside__card sx-svc-aside__card--primary">
                        <p class="sx-svc-aside__eyebrow">{{ __('services.aside_eyebrow') }}</p>
                        <h3>{{ __('services.aside_title') }}</h3>
                        <p>{{ __('services.aside_text') }}</p>
                        <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase">{{ __('site.request_quotation') }}</a>
                        <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-svc-aside__link">
                            <i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}
                        </a>
                        <a href="mailto:emca@emca.tech" class="sx-svc-aside__link">
                            <i class="fa fa-envelope"></i> emca@emca.tech
                        </a>
                    </div>

                    <div class="sx-svc-aside__card">
                        <h4>{{ __('services.included_title') }}</h4>
                        <ul class="sx-point-list">
                            <li>{{ __('services.included_1') }}</li>
                            <li>{{ __('services.included_2') }}</li>
                            <li>{{ __('services.included_3') }}</li>
                            <li>{{ __('services.included_4') }}</li>
                            <li>{{ __('services.included_5') }}</li>
                        </ul>
                        <a href="{{ route('pricing') }}" class="sx-svc-aside__more">{{ __('site.view_full_pricing') }} <i class="fa fa-angle-right"></i></a>
                    </div>

                    <div class="sx-svc-aside__card sx-svc-aside__card--soft">
                        <h4>{{ __('services.explore_next') }}</h4>
                        <ul class="sx-svc-aside__links">
                            <li><a href="{{ route('site.modules') }}"><i class="fa fa-angle-right"></i> {{ __('site.browse_modules') }}</a></li>
                            <li><a href="{{ route('site.faq') }}"><i class="fa fa-angle-right"></i> {{ __('site.read_faq') }}</a></li>
                            <li><a href="{{ route('site.register_school') }}"><i class="fa fa-angle-right"></i> {{ __('site.register_school_short') }}</a></li>
                            <li><a href="{{ route('site.team') }}"><i class="fa fa-angle-right"></i> {{ __('site.meet_the_team') }}</a></li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="sx-svc-process sx-soft-band">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-40 col-lg-8">
                <div class="title text-center">
                    <h1 class="mb-10">{{ __('services.process_title') }}</h1>
                    <p>{{ __('services.process_desc') }}</p>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($steps as $step)
                <div class="col-lg-3 col-md-6 mb-30">
                    <div class="sx-svc-step">
                        <span class="sx-svc-step__num">{{ $step['num'] }}</span>
                        <h4>{{ $step['title'] }}</h4>
                        <p>{{ $step['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="cta-two-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 cta-left">
                <h1>Ready to start your school digitization?</h1>
            </div>
            <div class="col-lg-4 cta-right">
                <a class="primary-btn wh" href="{{ route('site.contact') }}">Get in Touch</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-svc-banner.about-banner {
    min-height: 340px !important;
    padding-top: 168px !important;
    padding-bottom: 72px !important;
    position: relative;
    overflow: hidden;
}
.sx-svc-banner__glow {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
}
.sx-svc-banner__glow--a {
    width: 320px; height: 320px;
    top: -80px; right: -60px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
}
.sx-svc-banner__glow--b {
    width: 260px; height: 260px;
    bottom: -100px; left: -40px;
    background: radial-gradient(circle, rgba(196,58,58,0.35) 0%, transparent 70%);
}
.sx-svc-banner .container { position: relative; z-index: 2; }
.sx-svc-eyebrow {
    display: inline-block;
    color: #ffd6d6;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.sx-svc-banner .about-content h1 {
    font-size: 38px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sx-svc-lead {
    max-width: 640px;
    margin: 14px auto 0;
    opacity: .92;
    font-size: 15px;
    line-height: 1.7;
}

.sx-svc-pillars {
    margin-top: -36px;
    position: relative;
    z-index: 3;
    padding-bottom: 8px;
}
.sx-svc-pillar {
    background: #fff;
    border: 1px solid #f0dada;
    padding: 24px 20px;
    height: 100%;
    box-shadow: 0 10px 28px rgba(148,0,0,0.06);
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
.sx-svc-pillar:hover {
    transform: translateY(-3px);
    border-color: #e8b4b4;
    box-shadow: 0 14px 32px rgba(148,0,0,0.12);
}
.sx-svc-pillar__icon {
    width: 48px; height: 48px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(145deg, #940000, #c43a3a);
    color: #fff;
    margin-bottom: 14px;
}
.sx-svc-pillar h3 {
    color: #222;
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-svc-pillar p {
    color: #666;
    font-size: 14px;
    line-height: 1.65;
    margin-bottom: 12px;
}
.sx-svc-pillar a {
    color: #940000 !important;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none !important;
}
.sx-svc-pillar a:hover { color: #7a0000 !important; }

.sx-svc-page {
    padding-top: 40px !important;
    padding-bottom: 40px !important;
}
.sx-svc-intro { margin-bottom: 28px; }
.sx-svc-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.sx-svc-intro h2 {
    color: #222;
    font-weight: 800;
    font-size: 28px;
    margin-bottom: 10px;
}
.sx-svc-intro p {
    color: #666;
    font-size: 15px;
    line-height: 1.7;
    max-width: 560px;
    margin: 0;
}

.sx-svc-card {
    border: 1px solid #f0dada;
    background: linear-gradient(180deg, #fff 0%, #FFF5F5 100%);
    padding: 22px 20px;
    height: 100%;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.sx-svc-card:hover {
    border-color: #e8b4b4;
    box-shadow: 0 10px 28px rgba(148,0,0,0.08);
    transform: translateY(-2px);
}
.sx-svc-card__top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.sx-svc-card__icon {
    width: 42px; height: 42px;
    display: flex; align-items: center; justify-content: center;
    background: #fff;
    border: 1px solid #f0dada;
    color: #940000;
    flex-shrink: 0;
}
.sx-svc-card h3 {
    color: #940000;
    font-weight: 800;
    font-size: 17px;
    margin: 0;
}
.sx-svc-card > p {
    color: #555;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 12px;
}
.sx-svc-card ul {
    list-style: none;
    margin: 0;
    padding: 0;
    border-top: 1px solid #f7e6e6;
    padding-top: 12px;
}
.sx-svc-card li {
    position: relative;
    padding-left: 16px;
    color: #444;
    font-size: 13px;
    line-height: 1.55;
    margin-bottom: 6px;
}
.sx-svc-card li:before {
    content: "";
    position: absolute;
    left: 0; top: 7px;
    width: 7px; height: 7px;
    background: #940000;
}

.sx-svc-aside { display: flex; flex-direction: column; gap: 16px; }
.sx-svc-aside__card {
    border: 1px solid #f0dada;
    background: #fff;
    padding: 22px 20px;
}
.sx-svc-aside__card--primary {
    background: linear-gradient(160deg, rgba(148,0,0,0.96) 0%, rgba(92,0,0,0.94) 55%, rgba(26,12,22,0.96) 100%);
    border-color: transparent;
    color: #fff;
}
.sx-svc-aside__card--primary h3 {
    color: #fff;
    font-weight: 800;
    font-size: 22px;
    margin: 6px 0 10px;
}
.sx-svc-aside__card--primary p { color: rgba(255,255,255,0.88); font-size: 14px; line-height: 1.65; }
.sx-svc-aside__eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #ffd6d6;
    margin: 0;
}
.sx-svc-aside__card--primary .primary-btn {
    display: inline-block;
    margin: 8px 0 14px;
    background: #fff !important;
    color: #940000 !important;
    border: 0;
}
.sx-svc-aside__link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffd6d6 !important;
    font-size: 13px;
    margin-top: 8px;
    text-decoration: none !important;
}
.sx-svc-aside__link:hover { color: #fff !important; }
.sx-svc-aside__card h4 {
    color: #940000;
    font-weight: 800;
    font-size: 16px;
    margin-bottom: 12px;
}
.sx-svc-aside__more {
    display: inline-block;
    margin-top: 12px;
    color: #940000 !important;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none !important;
}
.sx-svc-aside__links {
    list-style: none;
    margin: 0;
    padding: 0;
}
.sx-svc-aside__links li { margin-bottom: 8px; }
.sx-svc-aside__links a {
    color: #333;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sx-svc-aside__links a:hover { color: #940000; }
.sx-svc-aside__links i { color: #940000; }
.sx-svc-aside__card--soft {
    background: linear-gradient(180deg, #FFF5F5 0%, #fff 100%);
}

.sx-svc-process {
    padding: 56px 0 40px !important;
}
.sx-svc-step {
    background: #fff;
    border: 1px solid #f0dada;
    padding: 24px 18px;
    height: 100%;
    position: relative;
}
.sx-svc-step__num {
    display: block;
    color: #940000;
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 12px;
    opacity: .85;
}
.sx-svc-step h4 {
    color: #222;
    font-weight: 800;
    font-size: 17px;
    margin-bottom: 8px;
}
.sx-svc-step p {
    color: #666;
    font-size: 14px;
    line-height: 1.65;
    margin: 0;
}

@media (max-width: 991.98px) {
    .sx-svc-banner.about-banner {
        min-height: 300px !important;
        padding-top: 148px !important;
        padding-bottom: 60px !important;
    }
    .sx-svc-banner .about-content h1 { font-size: 30px; }
    .sx-svc-pillars { margin-top: -28px; }
    .sx-svc-intro h2 { font-size: 24px; }
}
@media (max-width: 575.98px) {
    .sx-svc-banner .about-content h1 { font-size: 26px; }
}
</style>
@endpush

@push('schema')
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => 'ShuleXpert School Digitization Services',
        'provider' => ['@type' => 'Organization', 'name' => 'EmCa Techonology'],
        'areaServed' => 'TZ',
        'description' => 'Implementation, training, biometric support, SMS communication, hosting and continuous support for ShuleXpert.',
        'url' => url('/services'),
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => 'ShuleXpert Services',
            'itemListElement' => collect($services)->map(function ($svc) {
                return [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Service',
                        'name' => $svc['title'],
                        'description' => $svc['text'],
                    ],
                ];
            })->values()->all(),
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
