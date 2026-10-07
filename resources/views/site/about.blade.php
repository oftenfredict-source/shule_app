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
<section class="banner-area relative about-banner sx-about-page-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="sx-about-page-banner__glow sx-about-page-banner__glow--a" aria-hidden="true"></div>
    <div class="sx-about-page-banner__glow sx-about-page-banner__glow--b" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12 text-center">
                <h1 class="text-white">{{ __('about.title') }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.about') }}">{{ __('about.title') }}</a>
                </p>
                <p class="sx-about-lead text-white">
                    {{ __('about.lead') }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-about-pillars">
    <div class="container">
        <div class="row">
            @foreach($pillars as $pillar)
                <div class="col-lg-4 col-md-4 mb-3 mb-lg-0">
                    <div class="sx-about-pillar">
                        <span class="sx-about-pillar__icon" aria-hidden="true"><i class="fa {{ $pillar['icon'] }}"></i></span>
                        <h3>{{ $pillar['title'] }}</h3>
                        <p>{{ $pillar['text'] }}</p>
                        <a href="{{ $pillar['link'] }}">{{ $pillar['link_label'] }} <i class="fa fa-angle-right"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="sx-about-who section-gap">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-40 mb-lg-0">
                <div class="sx-about-photo">
                    <img class="img-fluid" src="{{ asset('images/school.png') }}" alt="About ShuleXpert school management platform" width="640" height="auto" loading="eager">
                    <div class="sx-about-photo__badge">
                        <strong>EmCa Techonology</strong>
                        <span>{{ __('about.powering') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <p class="sx-about-kicker">{{ __('about.who_kicker') }}</p>
                <h2>{{ __('about.who_title') }}</h2>
                <p>{{ __('about.who_p1') }}</p>
                <p>{{ __('about.who_p2') }}</p>
                <ul class="sx-point-list sx-about-points">
                    @foreach($capabilities as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <div class="sx-about-actions">
                    <a href="{{ route('site.modules') }}" class="primary-btn text-uppercase">{{ __('site.explore_modules') }}</a>
                    <a href="{{ route('site.team') }}" class="primary-btn wh text-uppercase">{{ __('site.meet_the_team') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="sx-about-values sx-soft-band">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-40 col-lg-8">
                <div class="title text-center">
                    <h1 class="mb-10">{{ __('about.values_title') }}</h1>
                    <p>{{ __('about.values_desc') }}</p>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($values as $value)
                <div class="col-lg-3 col-md-6 mb-30">
                    <article class="sx-about-value">
                        <span class="sx-about-value__icon" aria-hidden="true"><i class="fa {{ $value['icon'] }}"></i></span>
                        <h4>{{ $value['title'] }}</h4>
                        <p>{{ $value['text'] }}</p>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="sx-about-partner section-gap">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-30 mb-lg-0">
                <div class="sx-about-partner__panel">
                    <p class="sx-about-kicker">{{ __('about.partner_kicker') }}</p>
                    <h2>{{ __('about.partner_title') }}</h2>
                    <p>{{ __('about.partner_text') }}</p>
                    <div class="sx-about-stats">
                        <div>
                            <strong>TZS 1M</strong>
                            <span>{{ __('about.stat_install') }}</span>
                        </div>
                        <div>
                            <strong>TZS 500</strong>
                            <span>{{ __('about.stat_monthly') }}</span>
                        </div>
                        <div>
                            <strong>{{ __('about.stat_levels_label') }}</strong>
                            <span>{{ __('about.stat_levels') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sx-about-partner__cta">
                    <h3>{{ __('about.next_steps') }}</h3>
                    <ul>
                        <li><a href="{{ route('pricing') }}"><i class="fa fa-angle-right"></i> {{ __('about.view_pricing') }}</a></li>
                        <li><a href="{{ route('site.services') }}"><i class="fa fa-angle-right"></i> {{ __('site.our_services') }}</a></li>
                        <li><a href="{{ route('site.register_school') }}"><i class="fa fa-angle-right"></i> {{ __('site.register_school_short') }}</a></li>
                        <li><a href="{{ route('site.faq') }}"><i class="fa fa-angle-right"></i> {{ __('site.read_faq') }}</a></li>
                        <li><a href="{{ route('site.contact') }}"><i class="fa fa-angle-right"></i> {{ __('about.contact_emca') }}</a></li>
                    </ul>
                    <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase">{{ __('site.get_in_touch') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-two-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 cta-left">
                <h1>Ready to digitize your school with ShuleXpert?</h1>
            </div>
            <div class="col-lg-4 cta-right">
                <a class="primary-btn wh" href="{{ route('site.register_school') }}">Register School</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-about-page-banner.about-banner {
    min-height: 340px !important;
    padding-top: 168px !important;
    padding-bottom: 72px !important;
    position: relative;
    overflow: hidden;
}
.sx-about-page-banner__glow {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
}
.sx-about-page-banner__glow--a {
    width: 320px; height: 320px;
    top: -80px; right: -60px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
}
.sx-about-page-banner__glow--b {
    width: 260px; height: 260px;
    bottom: -100px; left: -40px;
    background: radial-gradient(circle, rgba(196,58,58,0.35) 0%, transparent 70%);
}
.sx-about-page-banner .container { position: relative; z-index: 2; }
.sx-about-eyebrow {
    display: inline-block;
    color: #ffd6d6;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.sx-about-page-banner .about-content h1 {
    font-size: 38px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sx-about-lead {
    max-width: 640px;
    margin: 14px auto 0;
    opacity: .92;
    font-size: 15px;
    line-height: 1.7;
}

.sx-about-pillars {
    margin-top: -36px;
    position: relative;
    z-index: 3;
    padding-bottom: 8px;
}
.sx-about-pillar {
    background: #fff;
    border: 1px solid #f0dada;
    padding: 24px 20px;
    height: 100%;
    box-shadow: 0 10px 28px rgba(148,0,0,0.06);
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
.sx-about-pillar:hover {
    transform: translateY(-3px);
    border-color: #e8b4b4;
    box-shadow: 0 14px 32px rgba(148,0,0,0.12);
}
.sx-about-pillar__icon {
    width: 48px; height: 48px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(145deg, #940000, #c43a3a);
    color: #fff;
    margin-bottom: 14px;
}
.sx-about-pillar h3 {
    color: #222;
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-about-pillar p {
    color: #666;
    font-size: 14px;
    line-height: 1.65;
    margin-bottom: 12px;
}
.sx-about-pillar a {
    color: #940000 !important;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none !important;
}
.sx-about-pillar a:hover { color: #7a0000 !important; }

.sx-about-who {
    padding-top: 48px !important;
    padding-bottom: 48px !important;
}
.sx-about-photo {
    position: relative;
    border: 1px solid #f0dada;
    background: #FFF5F5;
    padding: 12px;
}
.sx-about-photo img {
    width: 100%;
    display: block;
}
.sx-about-photo__badge {
    position: absolute;
    left: 24px;
    bottom: 24px;
    background: linear-gradient(145deg, #940000, #5c0000);
    color: #fff;
    padding: 14px 16px;
    max-width: 220px;
}
.sx-about-photo__badge strong {
    display: block;
    font-size: 14px;
    font-weight: 800;
    color: #fff;
}
.sx-about-photo__badge span {
    display: block;
    font-size: 12px;
    color: #ffd6d6;
    margin-top: 2px;
}
.sx-about-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.sx-about-who h2,
.sx-about-partner__panel h2 {
    color: #222;
    font-weight: 800;
    font-size: 28px;
    margin-bottom: 14px;
}
.sx-about-who p {
    color: #555;
    font-size: 15px;
    line-height: 1.75;
    margin-bottom: 12px;
}
.sx-about-points { margin-top: 18px; margin-bottom: 22px; }
.sx-about-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.sx-about-values {
    padding: 56px 0 36px !important;
}
.sx-about-value {
    background: #fff;
    border: 1px solid #f0dada;
    padding: 22px 18px;
    height: 100%;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.sx-about-value:hover {
    border-color: #e8b4b4;
    box-shadow: 0 10px 28px rgba(148,0,0,0.08);
    transform: translateY(-2px);
}
.sx-about-value__icon {
    width: 42px; height: 42px;
    display: flex; align-items: center; justify-content: center;
    background: #FFF5F5;
    border: 1px solid #f0dada;
    color: #940000;
    margin-bottom: 12px;
}
.sx-about-value h4 {
    color: #222;
    font-weight: 800;
    font-size: 16px;
    margin-bottom: 8px;
}
.sx-about-value p {
    color: #666;
    font-size: 14px;
    line-height: 1.65;
    margin: 0;
}

.sx-about-partner {
    padding-top: 48px !important;
    padding-bottom: 48px !important;
}
.sx-about-partner__panel {
    border: 1px solid #f0dada;
    background: linear-gradient(180deg, #fff 0%, #FFF5F5 100%);
    padding: 28px 24px;
    height: 100%;
}
.sx-about-partner__panel p {
    color: #555;
    font-size: 15px;
    line-height: 1.75;
    margin-bottom: 22px;
}
.sx-about-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}
.sx-about-stats > div {
    background: #fff;
    border: 1px solid #f0dada;
    padding: 14px 12px;
    text-align: center;
}
.sx-about-stats strong {
    display: block;
    color: #940000;
    font-size: 18px;
    font-weight: 800;
}
.sx-about-stats span {
    display: block;
    color: #666;
    font-size: 12px;
    margin-top: 4px;
}
.sx-about-partner__cta {
    border: 1px solid #f0dada;
    background: linear-gradient(160deg, rgba(148,0,0,0.96) 0%, rgba(92,0,0,0.94) 55%, rgba(26,12,22,0.96) 100%);
    padding: 26px 22px;
    color: #fff;
    height: 100%;
}
.sx-about-partner__cta h3 {
    color: #fff;
    font-weight: 800;
    font-size: 20px;
    margin-bottom: 14px;
}
.sx-about-partner__cta ul {
    list-style: none;
    margin: 0 0 18px;
    padding: 0;
}
.sx-about-partner__cta li { margin-bottom: 10px; }
.sx-about-partner__cta a {
    color: #ffd6d6 !important;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sx-about-partner__cta a:hover { color: #fff !important; }
.sx-about-partner__cta .primary-btn {
    background: #fff !important;
    color: #940000 !important;
    border: 0;
}

@media (max-width: 991.98px) {
    .sx-about-page-banner.about-banner {
        min-height: 300px !important;
        padding-top: 148px !important;
        padding-bottom: 60px !important;
    }
    .sx-about-page-banner .about-content h1 { font-size: 30px; }
    .sx-about-pillars { margin-top: -28px; }
    .sx-about-who h2,
    .sx-about-partner__panel h2 { font-size: 24px; }
    .sx-about-stats { grid-template-columns: 1fr; }
}
@media (max-width: 575.98px) {
    .sx-about-page-banner .about-content h1 { font-size: 26px; }
    .sx-about-photo__badge { left: 16px; bottom: 16px; }
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
        'description' => 'ShuleXpert is a multi-tenant school management SaaS by EmCa Techonology in Moshi, Kilimanjaro.',
        'mainEntity' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
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
