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
        ['num' => '1', 'title' => __('services.step1_title'), 'text' => __('services.step1_text'), 'icon' => 'fa-search'],
        ['num' => '2', 'title' => __('services.step2_title'), 'text' => __('services.step2_text'), 'icon' => 'fa-cogs'],
        ['num' => '3', 'title' => __('services.step3_title'), 'text' => __('services.step3_text'), 'icon' => 'fa-users'],
        ['num' => '4', 'title' => __('services.step4_title'), 'text' => __('services.step4_text'), 'icon' => 'fa-rocket'],
    ];

    $highlights = [
        __('services.included_1'),
        __('services.included_2'),
        __('services.included_3'),
        __('services.included_4'),
        __('services.included_5'),
        __('services.included_6'),
    ];
@endphp

@section('content')
<section class="banner-area relative about-banner sx-svc-page-banner" id="home" style="background-image:url('{{ asset('images/services-hero.jpg') }}')">
    <div class="sx-svc-page-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ __('services.title') }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('site.services') }}">{{ __('services.title') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-svc-page-wrap">
    {{-- Intro --}}
    <div class="container sx-svc-block">
        <div class="sx-svc-intro-lead text-center">
            <p class="mb-0">{{ __('services.lead') }}</p>
        </div>
    </div>

    {{-- What we deliver --}}
    <div class="container sx-svc-block">
        <div class="row align-items-center gx-4 gy-4">
            <div class="col-lg-6">
                <div class="sx-svc-photo">
                    <img src="{{ asset('images/services-hero.jpg') }}" alt="ShuleXpert services" loading="eager">
                    <div class="sx-svc-photo__badge">
                        <strong>EmCa Techonologies</strong>
                        <span>{{ __('services.photo_badge') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="sx-svc-heading line-bottom line-bottom-theme-colored1">{{ __('services.kicker') }}</h2>
                <p class="sx-svc-text sx-svc-lead">{{ __('services.intro_title') }}</p>
                <p class="sx-svc-text">{{ __('services.intro_text') }}</p>
                <ul class="sx-svc-caps">
                    @foreach($highlights as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <div class="sx-svc-actions">
                    <a href="{{ route('site.contact') }}" class="btn btn-theme-colored1 btn-circled">{{ __('site.request_quotation') }}</a>
                    <a href="{{ route('pricing') }}" class="btn btn-dark btn-circled">{{ __('site.see_pricing') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Pillars --}}
    <div class="sx-svc-soft">
        <div class="container sx-svc-block">
            <div class="sx-svc-section-head text-center">
                <h2 class="sx-svc-heading line-bottom line-bottom-theme-colored1 d-inline-block sx-svc-heading--center">{{ __('services.pillar_section_title') }}</h2>
            </div>
            <div class="row">
                @foreach($pillars as $pillar)
                    <div class="col-md-4 mb-30">
                        <article class="sx-svc-feature-card">
                            <span class="sx-svc-feature-card__icon"><i class="fa {{ $pillar['icon'] }}"></i></span>
                            <h3>{{ $pillar['title'] }}</h3>
                            <p>{{ $pillar['text'] }}</p>
                            <a href="{{ $pillar['link'] }}">{{ $pillar['link_label'] }} <i class="fa fa-angle-right"></i></a>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Service cards --}}
    <div class="container sx-svc-block">
        <div class="sx-svc-section-head text-center">
            <h2 class="sx-svc-heading line-bottom line-bottom-theme-colored1 d-inline-block sx-svc-heading--center">{{ __('services.suite_title') }}</h2>
            <p class="sx-svc-text sx-svc-section-head__lead mx-auto mb-0">{{ __('services.suite_desc') }}</p>
        </div>
        <div class="row">
            @foreach($services as $svc)
                <div class="col-md-6 col-lg-4 mb-30">
                    <article class="sx-svc-item-card">
                        <span class="sx-svc-item-card__icon"><i class="fa {{ $svc['icon'] }}"></i></span>
                        <h3>{{ $svc['title'] }}</h3>
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

    {{-- Process --}}
    <div class="sx-svc-soft">
        <div class="container sx-svc-block">
            <div class="sx-svc-section-head text-center">
                <h2 class="sx-svc-heading line-bottom line-bottom-theme-colored1 d-inline-block sx-svc-heading--center">{{ __('services.process_eyebrow') }}</h2>
                <p class="sx-svc-text sx-svc-section-head__lead mx-auto mb-0">{{ __('services.process_desc') }}</p>
            </div>
            <div class="row sx-svc-process-row">
                @foreach($steps as $step)
                    <div class="col-sm-6 col-lg-3 mb-30">
                        <div class="sx-svc-step-card">
                            <div class="sx-svc-step-card__icon-wrap">
                                <span class="sx-svc-step-card__num">{{ $step['num'] }}</span>
                                <span class="sx-svc-step-card__icon"><i class="fa {{ $step['icon'] }}"></i></span>
                            </div>
                            <h4>{{ $step['title'] }}</h4>
                            <p>{{ $step['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Next steps --}}
    <div class="container sx-svc-block sx-svc-block--last">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="sx-svc-partner-panel">
                    <h2 class="sx-svc-heading line-bottom line-bottom-theme-colored1">{{ __('services.aside_eyebrow') }}</h2>
                    <p class="sx-svc-text sx-svc-lead">{{ __('services.aside_title') }}</p>
                    <p class="sx-svc-text">{{ __('services.aside_text') }}</p>
                    <ul class="sx-svc-partner-points">
                        <li>{{ __('services.partner_point1') }}</li>
                        <li>{{ __('services.partner_point2') }}</li>
                        <li>{{ __('services.partner_point3') }}</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sx-svc-side-cta">
                    <h3>{{ __('services.explore_next') }}</h3>
                    <ul>
                        <li><a href="{{ route('pricing') }}"><i class="fa fa-angle-right"></i> {{ __('site.see_pricing') }}</a></li>
                        <li><a href="{{ route('site.modules') }}"><i class="fa fa-angle-right"></i> {{ __('site.browse_modules') }}</a></li>
                        <li><a href="{{ route('site.register_school') }}"><i class="fa fa-angle-right"></i> {{ __('site.register_school_short') }}</a></li>
                        <li><a href="{{ route('site.faq') }}"><i class="fa fa-angle-right"></i> {{ __('site.read_faq') }}</a></li>
                        <li><a href="{{ route('site.contact') }}"><i class="fa fa-angle-right"></i> {{ __('site.contact_us') }}</a></li>
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
.sx-public-site .banner-area.sx-svc-page-banner.about-banner,
.sx-svc-page-banner.about-banner {
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
.sx-svc-page-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(92,0,0,.58) 0%, rgba(148,0,0,.42) 55%, rgba(26,10,10,.35) 100%);
    z-index: 1;
}
.sx-svc-page-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-svc-page-banner .about-content h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-svc-page-banner .link-nav a { color: #fff !important; }

.sx-svc-page-wrap > .container.sx-svc-block {
    padding-top: 48px !important;
    padding-bottom: 0 !important;
}
.sx-svc-page-wrap > .sx-svc-soft > .container.sx-svc-block {
    padding-top: 48px !important;
    padding-bottom: 20px !important;
}
.sx-svc-page-wrap > .container.sx-svc-block.sx-svc-block--last {
    padding-bottom: 90px !important;
}
.sx-svc-page-wrap { padding-bottom: 20px; }

.sx-svc-intro-lead {
    max-width: 760px;
    margin: 0 auto;
    background: linear-gradient(160deg, #fff 0%, #fff8f8 100%);
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    padding: 22px 28px;
    box-shadow: 0 8px 22px rgba(148,0,0,.06);
    color: #444;
    font-size: 16px;
    line-height: 1.7;
}
.sx-svc-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.sx-svc-heading {
    color: #1a1a1a;
    font-weight: 800;
    font-size: 26px;
    line-height: 1.3;
    margin: 0 0 14px;
}
.sx-svc-heading.line-bottom {
    margin-bottom: 10px !important;
    padding-bottom: 8px !important;
}
.sx-svc-heading.line-bottom:after,
.sx-svc-heading.line-bottom-theme-colored1:after {
    bottom: 0 !important;
}
.sx-svc-heading--center.line-bottom:after,
.sx-svc-heading--center.line-bottom-theme-colored1:after {
    left: 50% !important;
    transform: translateX(-50%);
}
.sx-svc-text {
    color: #555;
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 12px;
}
.sx-svc-lead {
    color: #2a0a0c;
    font-size: 17px;
    font-weight: 600;
    line-height: 1.45;
    margin-top: 6px;
    margin-bottom: 14px;
}
.sx-svc-section-head { margin-bottom: 28px; }
.sx-svc-section-head__lead {
    margin-top: 6px;
    max-width: 560px;
}
.sx-svc-photo {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #f0e4e4;
    box-shadow: 0 12px 28px rgba(148,0,0,.1);
}
.sx-svc-photo img {
    width: 100%;
    height: 360px;
    object-fit: cover;
    object-position: center 30%;
    display: block;
}
.sx-svc-photo__badge {
    position: absolute;
    left: 18px;
    bottom: 18px;
    background: linear-gradient(145deg, #940000, #5c0000);
    color: #fff;
    padding: 12px 14px;
    border-radius: 10px;
    max-width: 230px;
}
.sx-svc-photo__badge strong { display: block; font-size: 13px; font-weight: 800; }
.sx-svc-photo__badge span { display: block; font-size: 12px; color: #ffd6d6; margin-top: 2px; }
.sx-svc-caps {
    list-style: none;
    margin: 16px 0 20px;
    padding: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 14px;
}
.sx-svc-caps li {
    position: relative;
    padding-left: 20px;
    color: #444;
    font-size: 13.5px;
    line-height: 1.45;
}
.sx-svc-caps li:before {
    content: "\f00c";
    font-family: FontAwesome, "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    left: 0;
    top: 1px;
    color: #940000;
    font-size: 12px;
}
.sx-svc-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.sx-svc-soft { background: #fff8f8; margin-top: 48px; }

.sx-svc-feature-card {
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 24px 20px;
    height: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
    transition: transform .25s ease, box-shadow .25s ease;
}
.sx-svc-feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px rgba(148,0,0,.1);
}
.sx-svc-feature-card__icon {
    width: 48px; height: 48px; border-radius: 50%;
    background: #940000; color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 14px; font-size: 18px;
}
.sx-svc-feature-card h3 { color: #1a1a1a; font-size: 17px; font-weight: 800; margin-bottom: 8px; }
.sx-svc-feature-card p { color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 12px; min-height: 66px; }
.sx-svc-feature-card a { color: #940000 !important; font-weight: 700; font-size: 13px; text-decoration: none !important; }

.sx-svc-item-card {
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 22px 18px;
    height: 100%;
    transition: transform .25s ease, box-shadow .25s ease;
}
.sx-svc-item-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(148,0,0,.08);
}
.sx-svc-item-card__icon {
    width: 44px; height: 44px; border-radius: 50%;
    background: #fff0f0; color: #940000;
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 12px;
}
.sx-svc-item-card h3 { color: #1a1a1a; font-weight: 800; font-size: 16px; margin-bottom: 8px; }
.sx-svc-item-card > p { color: #666; font-size: 13.5px; line-height: 1.55; margin-bottom: 12px; min-height: 60px; }
.sx-svc-item-card ul { list-style: none; margin: 0; padding: 0; border-top: 1px solid #f0e4e4; padding-top: 10px; }
.sx-svc-item-card li {
    position: relative; padding-left: 18px; margin-bottom: 6px;
    color: #555; font-size: 13px; line-height: 1.4;
}
.sx-svc-item-card li:before {
    content: "\f00c";
    font-family: FontAwesome, "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute; left: 0; top: 1px;
    color: #940000; font-size: 11px;
}

.sx-svc-step-card {
    text-align: center;
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 22px 14px;
    height: 100%;
}
.sx-svc-step-card__icon-wrap {
    position: relative;
    width: 72px; height: 72px;
    margin: 0 auto 14px;
}
.sx-svc-step-card__icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: #fff; border: 1px solid #f0e4e4;
    box-shadow: 0 10px 22px rgba(148,0,0,.1);
    display: inline-flex; align-items: center; justify-content: center;
    color: #940000; font-size: 24px;
}
.sx-svc-step-card__num {
    position: absolute; top: -4px; left: -4px;
    width: 26px; height: 26px; border-radius: 50%;
    background: #940000; color: #fff;
    font-size: 12px; font-weight: 800;
    display: inline-flex; align-items: center; justify-content: center;
}
.sx-svc-step-card h4 { color: #1a1a1a; font-weight: 800; font-size: 15px; margin-bottom: 8px; }
.sx-svc-step-card p { color: #666; font-size: 13px; line-height: 1.5; margin: 0; }

.sx-svc-partner-panel {
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    background: linear-gradient(180deg, #fff 0%, #fff8f8 100%);
    padding: 28px 24px;
    height: 100%;
}
.sx-svc-partner-points {
    list-style: none; margin: 18px 0 0; padding: 0;
    display: grid; gap: 10px;
}
.sx-svc-partner-points li {
    position: relative;
    padding: 12px 14px 12px 36px;
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 10px;
    color: #444; font-size: 14px; font-weight: 600; line-height: 1.45;
}
.sx-svc-partner-points li:before {
    content: "\f00c";
    font-family: FontAwesome, "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute; left: 14px; top: 13px;
    color: #940000; font-size: 13px;
}
.sx-svc-side-cta {
    border-radius: 14px;
    background: linear-gradient(165deg, #940000 0%, #7a0000 48%, #5c0000 100%);
    padding: 26px 22px;
    color: #fff;
    height: 100%;
    box-shadow: 0 14px 30px rgba(148,0,0,.22);
}
.sx-svc-side-cta h3 { color: #fff; font-weight: 800; font-size: 20px; margin-bottom: 14px; }
.sx-svc-side-cta ul { list-style: none; margin: 0 0 18px; padding: 0; }
.sx-svc-side-cta li { margin-bottom: 10px; }
.sx-svc-side-cta a {
    color: #ffd6d6 !important; text-decoration: none !important;
    font-size: 14px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 8px;
}
.sx-svc-side-cta a:hover { color: #fff !important; }
.sx-svc-side-cta .btn-light {
    background: #fff !important; color: #940000 !important;
    font-weight: 800; border: 0;
}

@media (max-width: 991.98px) {
    .sx-svc-page-banner.about-banner {
        min-height: 240px !important;
        height: 240px !important;
        background-position: center 30% !important;
    }
    .sx-svc-page-banner .about-content h1 { font-size: 24px; }
    .sx-svc-heading { font-size: 22px; }
    .sx-svc-photo img { height: 280px; }
    .sx-svc-caps { grid-template-columns: 1fr; }
    .sx-svc-page-wrap > .container.sx-svc-block.sx-svc-block--last {
        padding-bottom: 70px !important;
    }
}
@media (max-width: 575.98px) {
    .sx-svc-page-banner.about-banner {
        min-height: 200px !important;
        height: 200px !important;
    }
    .sx-svc-page-banner .about-content h1 { font-size: 22px; }
    .sx-svc-intro-lead { padding: 18px 16px; }
}
</style>
@endpush
