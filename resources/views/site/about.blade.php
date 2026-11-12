@extends('layouts.site')

@php
    $pillars = [
        [
            'icon' => 'fa-th-large',
            'title' => __('about.pillar1_title'),
            'text' => __('about.pillar1_text'),
            'link' => route('site.modules'),
            'link_label' => __('about.pillar1_link'),
        ],
        [
            'icon' => 'fa-users',
            'title' => __('about.pillar2_title'),
            'text' => __('about.pillar2_text'),
            'link' => route('login'),
            'link_label' => __('about.pillar2_link'),
        ],
        [
            'icon' => 'fa-map-marker',
            'title' => __('about.pillar3_title'),
            'text' => __('about.pillar3_text'),
            'link' => route('site.contact'),
            'link_label' => __('about.pillar3_link'),
        ],
    ];

    $values = [
        [
            'icon' => 'fa-check-circle',
            'title' => __('about.value1_title'),
            'text' => __('about.value1_text'),
        ],
        [
            'icon' => 'fa-lock',
            'title' => __('about.value2_title'),
            'text' => __('about.value2_text'),
        ],
        [
            'icon' => 'fa-comments',
            'title' => __('about.value3_title'),
            'text' => __('about.value3_text'),
        ],
        [
            'icon' => 'fa-line-chart',
            'title' => __('about.value4_title'),
            'text' => __('about.value4_text'),
        ],
    ];

    $capabilities = [
        __('about.cap1'),
        __('about.cap2'),
        __('about.cap3'),
        __('about.cap4'),
        __('about.cap5'),
        __('about.cap6'),
    ];
@endphp

@section('content')
<section class="banner-area relative about-banner sx-about-page-banner" id="home" style="background-image:url('{{ asset('images/about-hero.jpg') }}')">
    <div class="sx-about-page-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ __('about.title') }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('site.about') }}">{{ __('about.title') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-about-page">
    {{-- Intro --}}
    <div class="container sx-about-block">
        <div class="sx-about-intro text-center">
            <p class="sx-about-intro__lead mb-0">{{ __('about.lead') }}</p>
        </div>
    </div>

    {{-- Who we are --}}
    <div class="container sx-about-block">
        <div class="row align-items-center gx-4 gy-4">
            <div class="col-lg-6">
                <div class="sx-about-photo">
                    <img src="{{ asset('images/why-choose-school.jpg') }}" alt="About ShuleXpert" loading="eager">
                    <div class="sx-about-photo__badge">
                        <strong>EmCa Techonologies</strong>
                        <span>{{ __('about.powering') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="sx-about-heading line-bottom line-bottom-theme-colored1">{{ __('about.who_kicker') }}</h2>
                <p class="sx-about-text sx-about-lead">{{ __('about.who_title') }}</p>
                <p class="sx-about-text">{{ __('about.who_p1') }}</p>
                <p class="sx-about-text">{{ __('about.who_p2') }}</p>
                <ul class="sx-about-caps">
                    @foreach($capabilities as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <div class="sx-about-actions">
                    <a href="{{ route('site.modules') }}" class="btn btn-theme-colored1 btn-circled">{{ __('site.explore_modules') }}</a>
                    <a href="{{ route('site.contact') }}" class="btn btn-dark btn-circled">{{ __('site.contact_us') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Pillars --}}
    <div class="sx-about-soft">
        <div class="container sx-about-block">
            <div class="sx-about-section-head text-center">
                <h2 class="sx-about-heading line-bottom line-bottom-theme-colored1 d-inline-block sx-about-heading--center">{{ __('about.pillar_section_title') }}</h2>
            </div>
            <div class="row">
                @foreach($pillars as $pillar)
                    <div class="col-md-4 mb-30">
                        <article class="sx-about-card">
                            <span class="sx-about-card__icon"><i class="fa {{ $pillar['icon'] }}"></i></span>
                            <h3>{{ $pillar['title'] }}</h3>
                            <p>{{ $pillar['text'] }}</p>
                            <a href="{{ $pillar['link'] }}">{{ $pillar['link_label'] }} <i class="fa fa-angle-right"></i></a>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Values --}}
    <div class="container sx-about-block">
        <div class="sx-about-section-head text-center">
            <h2 class="sx-about-heading line-bottom line-bottom-theme-colored1 d-inline-block sx-about-heading--center">{{ __('about.values_title') }}</h2>
            <p class="sx-about-text sx-about-section-head__lead mx-auto mb-0">{{ __('about.values_desc') }}</p>
        </div>
        <div class="row">
            @foreach($values as $value)
                <div class="col-sm-6 col-lg-3 mb-30">
                    <article class="sx-about-value-card">
                        <span class="sx-about-value-card__icon"><i class="fa {{ $value['icon'] }}"></i></span>
                        <h4>{{ $value['title'] }}</h4>
                        <p>{{ $value['text'] }}</p>
                    </article>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Partner + next steps --}}
    <div class="container sx-about-block sx-about-block--last">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="sx-about-partner-panel">
                    <p class="sx-about-kicker">{{ __('about.partner_kicker') }}</p>
                    <h2 class="sx-about-heading line-bottom line-bottom-theme-colored1">{{ __('about.partner_title') }}</h2>
                    <p class="sx-about-text">{{ __('about.partner_text') }}</p>
                    <ul class="sx-about-partner-points">
                        <li>{{ __('about.partner_point1') }}</li>
                        <li>{{ __('about.partner_point2') }}</li>
                        <li>{{ __('about.partner_point3') }}</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sx-about-side-cta">
                    <h3>{{ __('about.next_steps') }}</h3>
                    <ul>
                        <li><a href="{{ route('pricing') }}"><i class="fa fa-angle-right"></i> {{ __('about.view_pricing') }}</a></li>
                        <li><a href="{{ route('site.services') }}"><i class="fa fa-angle-right"></i> {{ __('site.our_services') }}</a></li>
                        <li><a href="{{ route('site.register_school') }}"><i class="fa fa-angle-right"></i> {{ __('site.register_school_short') }}</a></li>
                        <li><a href="{{ route('site.faq') }}"><i class="fa fa-angle-right"></i> {{ __('site.read_faq') }}</a></li>
                        <li><a href="{{ route('site.contact') }}"><i class="fa fa-angle-right"></i> {{ __('about.contact_emca') }}</a></li>
                    </ul>
                    <a href="{{ route('site.contact') }}" class="btn btn-light btn-block">{{ __('site.get_in_touch') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
.sx-public-site .banner-area.sx-about-page-banner.about-banner,
.sx-about-page-banner.about-banner {
    min-height: 300px !important;
    height: 300px !important;
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
.sx-about-page-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(92,0,0,.58) 0%, rgba(148,0,0,.42) 55%, rgba(26,10,10,.35) 100%);
    z-index: 1;
}
.sx-about-page-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-about-page-banner .about-content h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-about-page-banner .link-nav a { color: #fff !important; }

.sx-about-page > .container.sx-about-block {
    padding-top: 48px !important;
    padding-bottom: 0 !important;
}
.sx-about-page > .sx-about-soft > .container.sx-about-block {
    padding-top: 48px !important;
    padding-bottom: 20px !important;
}
.sx-about-page > .container.sx-about-block.sx-about-block--last {
    padding-bottom: 30px !important;
    margin-bottom: 0 !important;
}
.sx-about-page {
    padding-bottom: 20px;
}
@media (max-width: 991.98px) {
    .sx-about-page > .container.sx-about-block.sx-about-block--last {
        padding-bottom: 70px !important;
    }
}
.sx-about-intro {
    max-width: 760px;
    margin: 0 auto;
    background: linear-gradient(160deg, #fff 0%, #fff8f8 100%);
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    padding: 22px 28px;
    box-shadow: 0 8px 22px rgba(148,0,0,.06);
}
.sx-about-intro__lead {
    color: #444;
    font-size: 16px;
    line-height: 1.7;
}
.sx-about-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.sx-about-heading {
    color: #1a1a1a;
    font-weight: 800;
    font-size: 26px;
    line-height: 1.3;
    margin: 0 0 14px;
}
.sx-about-heading.line-bottom {
    margin-bottom: 10px !important;
    padding-bottom: 8px !important;
}
.sx-about-heading.line-bottom:after,
.sx-about-heading.line-bottom-theme-colored1:after {
    bottom: 0 !important;
}
.sx-about-heading--center.line-bottom:after,
.sx-about-heading--center.line-bottom-theme-colored1:after {
    left: 50% !important;
    transform: translateX(-50%);
}
.sx-about-text {
    color: #555;
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 12px;
}
.sx-about-lead {
    color: #2a0a0c;
    font-size: 17px;
    font-weight: 600;
    line-height: 1.45;
    margin-top: 6px;
    margin-bottom: 14px;
}
.sx-about-section-head {
    margin-bottom: 28px;
}
.sx-about-section-head__lead {
    margin-top: 6px;
    max-width: 560px;
}
.sx-about-photo {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #f0e4e4;
    box-shadow: 0 12px 28px rgba(148,0,0,.1);
}
.sx-about-photo img {
    width: 100%;
    height: 360px;
    object-fit: cover;
    object-position: center;
    display: block;
}
.sx-about-photo__badge {
    position: absolute;
    left: 18px;
    bottom: 18px;
    background: linear-gradient(145deg, #940000, #5c0000);
    color: #fff;
    padding: 12px 14px;
    border-radius: 10px;
    max-width: 220px;
}
.sx-about-photo__badge strong {
    display: block;
    font-size: 13px;
    font-weight: 800;
}
.sx-about-photo__badge span {
    display: block;
    font-size: 12px;
    color: #ffd6d6;
    margin-top: 2px;
}
.sx-about-caps {
    list-style: none;
    margin: 16px 0 20px;
    padding: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 14px;
}
.sx-about-caps li {
    position: relative;
    padding-left: 20px;
    color: #444;
    font-size: 13.5px;
    line-height: 1.45;
}
.sx-about-caps li:before {
    content: "\f00c";
    font-family: FontAwesome, "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    left: 0;
    top: 1px;
    color: #940000;
    font-size: 12px;
}
.sx-about-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.sx-about-soft {
    background: #fff8f8;
    margin-top: 48px;
}
.sx-about-card {
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 24px 20px;
    height: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
    transition: transform .25s ease, box-shadow .25s ease;
}
.sx-about-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px rgba(148,0,0,.1);
}
.sx-about-card__icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #940000;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    font-size: 18px;
}
.sx-about-card h3 {
    color: #1a1a1a;
    font-size: 17px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-about-card p {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 12px;
    min-height: 66px;
}
.sx-about-card a {
    color: #940000 !important;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none !important;
}
.sx-about-value-card {
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 20px 16px;
    height: 100%;
    transition: transform .25s ease, box-shadow .25s ease;
}
.sx-about-value-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(148,0,0,.08);
}
.sx-about-value-card__icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #fff0f0;
    color: #940000;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}
.sx-about-value-card h4 {
    color: #1a1a1a;
    font-weight: 800;
    font-size: 15px;
    margin-bottom: 8px;
}
.sx-about-value-card p {
    color: #666;
    font-size: 13.5px;
    line-height: 1.55;
    margin: 0;
}
.sx-about-partner-panel {
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    background: linear-gradient(180deg, #fff 0%, #fff8f8 100%);
    padding: 28px 24px;
    height: 100%;
}
.sx-about-partner-points {
    list-style: none;
    margin: 18px 0 0;
    padding: 0;
    display: grid;
    gap: 10px;
}
.sx-about-partner-points li {
    position: relative;
    padding: 12px 14px 12px 36px;
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 10px;
    color: #444;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.45;
}
.sx-about-partner-points li:before {
    content: "\f00c";
    font-family: FontAwesome, "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    left: 14px;
    top: 13px;
    color: #940000;
    font-size: 13px;
}
.sx-about-side-cta {
    border-radius: 14px;
    background: linear-gradient(165deg, #940000 0%, #7a0000 48%, #5c0000 100%);
    padding: 26px 22px;
    color: #fff;
    height: 100%;
    box-shadow: 0 14px 30px rgba(148,0,0,.22);
}
.sx-about-side-cta h3 {
    color: #fff;
    font-weight: 800;
    font-size: 20px;
    margin-bottom: 14px;
}
.sx-about-side-cta ul {
    list-style: none;
    margin: 0 0 18px;
    padding: 0;
}
.sx-about-side-cta li { margin-bottom: 10px; }
.sx-about-side-cta a {
    color: #ffd6d6 !important;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sx-about-side-cta a:hover { color: #fff !important; }
.sx-about-side-cta .btn-light {
    background: #fff !important;
    color: #940000 !important;
    font-weight: 800;
    border: 0;
}
@media (max-width: 991.98px) {
    .sx-about-page-banner.about-banner {
        min-height: 240px !important;
        height: 240px !important;
        background-position: center 30% !important;
    }
    .sx-about-page-banner .about-content h1 { font-size: 24px; }
    .sx-about-heading { font-size: 22px; }
    .sx-about-photo img { height: 280px; }
    .sx-about-caps { grid-template-columns: 1fr; }
}
@media (max-width: 575.98px) {
    .sx-about-page-banner.about-banner {
        min-height: 200px !important;
        height: 200px !important;
        background-position: center 28% !important;
    }
    .sx-about-page-banner .about-content h1 { font-size: 22px; }
    .sx-about-intro { padding: 18px 16px; }
}
</style>
@endpush

@push('schema')
@php
    $about = [
        '@context' => 'https://schema.org',
        '@type' => 'AboutPage',
        'name' => 'About ShuleXpert',
        'url' => url('/about'),
        'description' => 'ShuleXpert is a multi-tenant school management SaaS by EmCa Techonologies in Moshi, Kilimanjaro.',
        'mainEntity' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonologies',
            'url' => url('/'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Moshi',
                'addressRegion' => 'Kilimanjaro',
                'addressCountry' => 'TZ',
            ],
            'makesOffer' => [
                '@type' => 'Offer',
                'itemOffered' => [
                    '@type' => 'SoftwareApplication',
                    'name' => 'ShuleXpert',
                    'applicationCategory' => 'EducationalApplication',
                    'operatingSystem' => 'Web',
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($about, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
