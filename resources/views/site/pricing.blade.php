@extends('layouts.site')

@php
    $installPoints = [
        ['label' => __('pricing.inst1_label'), 'text' => __('pricing.inst1_text')],
        ['label' => __('pricing.inst2_label'), 'text' => __('pricing.inst2_text')],
        ['label' => __('pricing.inst3_label'), 'text' => __('pricing.inst3_text')],
        ['label' => __('pricing.inst4_label'), 'text' => __('pricing.inst4_text')],
        ['label' => __('pricing.inst5_label'), 'text' => __('pricing.inst5_text')],
        ['label' => __('pricing.inst6_label'), 'text' => __('pricing.inst6_text')],
    ];

    $monthlyPoints = [
        ['label' => __('pricing.mon1_label'), 'text' => __('pricing.mon1_text')],
        ['label' => __('pricing.mon2_label'), 'text' => __('pricing.mon2_text')],
        ['label' => __('pricing.mon3_label'), 'text' => __('pricing.mon3_text')],
        ['label' => __('pricing.mon4_label'), 'text' => __('pricing.mon4_text')],
        ['label' => __('pricing.mon5_label'), 'text' => __('pricing.mon5_text')],
        ['label' => __('pricing.mon6_label'), 'text' => __('pricing.mon6_text')],
    ];

    $examples = [
        ['students' => 200, 'monthly' => '100,000'],
        ['students' => 500, 'monthly' => '250,000'],
        ['students' => 1000, 'monthly' => '500,000'],
    ];

    $faqs = [
        ['q' => __('pricing.faq1_q'), 'a' => __('pricing.faq1_a')],
        ['q' => __('pricing.faq2_q'), 'a' => __('pricing.faq2_a')],
        ['q' => __('pricing.faq3_q'), 'a' => __('pricing.faq3_a')],
    ];
@endphp

@section('content')
<section class="banner-area relative about-banner sx-price-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="sx-price-banner__glow sx-price-banner__glow--a" aria-hidden="true"></div>
    <div class="sx-price-banner__glow sx-price-banner__glow--b" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12 text-center">
                <h1 class="text-white">{{ __('pricing.title') }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('pricing') }}">{{ __('pricing.title') }}</a>
                </p>
                <p class="sx-price-lead text-white">
                    {{ __('pricing.lead') }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-price-highlights">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="sx-price-highlight">
                    <span class="sx-price-highlight__badge">{{ __('pricing.one_time') }}</span>
                    <div>
                        <strong>{{ __('pricing.installation') }}</strong>
                        <span>{{ __('pricing.paid_once') }}</span>
                    </div>
                    <div class="sx-price-highlight__amount">TZS 1,000,000</div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="sx-price-highlight sx-price-highlight--alt">
                    <span class="sx-price-highlight__badge sx-price-highlight__badge--soft">{{ __('pricing.monthly') }}</span>
                    <div>
                        <strong>{{ __('pricing.per_student') }}</strong>
                        <span>{{ __('pricing.active_students') }}</span>
                    </div>
                    <div class="sx-price-highlight__amount">TZS 500</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="sx-price-page section-gap">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-40 mb-lg-0">
                <div class="row">
                    <div class="col-md-6 mb-30">
                        <article class="sx-price-plan sx-price-plan--featured">
                            <div class="sx-price-plan__head">
                                <span class="sx-price-plan__tag">{{ __('pricing.one_time') }}</span>
                                <h3>{{ __('pricing.fee_title') }}</h3>
                                <p>{{ __('pricing.fee_desc') }}</p>
                                <div class="sx-price-plan__amount">TZS 1,000,000</div>
                                <small>{{ __('pricing.one_time_setup') }}</small>
                            </div>
                            <h5>{{ __('pricing.covers') }}</h5>
                            <ul class="sx-point-list">
                                @foreach($installPoints as $point)
                                    <li><strong>{{ $point['label'] }}</strong>: {{ $point['text'] }}</li>
                                @endforeach
                            </ul>
                        </article>
                    </div>
                    <div class="col-md-6 mb-30">
                        <article class="sx-price-plan">
                            <div class="sx-price-plan__head">
                                <span class="sx-price-plan__tag sx-price-plan__tag--soft">{{ __('pricing.monthly') }}</span>
                                <h3>{{ __('pricing.sub_title') }}</h3>
                                <p>{{ __('pricing.sub_desc') }}</p>
                                <div class="sx-price-plan__amount">TZS 500</div>
                                <small>{{ __('pricing.per_student_month') }}</small>
                            </div>
                            <h5>{{ __('pricing.covers') }}</h5>
                            <ul class="sx-point-list">
                                @foreach($monthlyPoints as $point)
                                    <li><strong>{{ $point['label'] }}</strong>: {{ $point['text'] }}</li>
                                @endforeach
                            </ul>
                        </article>
                    </div>
                </div>

                <div class="sx-price-calc">
                    <div class="sx-price-calc__intro">
                        <p class="sx-price-kicker">{{ __('pricing.cost_kicker') }}</p>
                        <h2>{{ __('pricing.cost_title') }}</h2>
                        <p>{!! __('pricing.cost_formula') !!}</p>
                    </div>
                    <div class="row">
                        @foreach($examples as $ex)
                            <div class="col-md-4 mb-20 mb-md-0">
                                <div class="sx-price-example">
                                    <span>{{ number_format($ex['students']) }} {{ __('pricing.students') }}</span>
                                    <strong>TZS {{ $ex['monthly'] }}</strong>
                                    <small>{{ __('pricing.per_month') }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="sx-price-faq">
                    <h3>{{ __('pricing.faq_title') }}</h3>
                    @foreach($faqs as $i => $item)
                        <details class="sx-price-faq__item" @if($i === 0) open @endif>
                            <summary>{{ $item['q'] }}</summary>
                            <p>{{ $item['a'] }}</p>
                        </details>
                    @endforeach
                    <a href="{{ route('site.faq') }}" class="sx-price-faq__more">{{ __('pricing.more_faq') }} <i class="fa fa-angle-right"></i></a>
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="sx-price-aside">
                    <div class="sx-price-aside__card sx-price-aside__card--primary">
                        <p class="sx-price-aside__eyebrow">{{ __('pricing.aside_eyebrow') }}</p>
                        <h3>{{ __('pricing.aside_title') }}</h3>
                        <p>{{ __('pricing.aside_text') }}</p>
                        <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase">{{ __('site.request_quotation') }}</a>
                        <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-price-aside__link">
                            <i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}
                        </a>
                        <a href="mailto:emca@emca.tech" class="sx-price-aside__link">
                            <i class="fa fa-envelope"></i> emca@emca.tech
                        </a>
                    </div>

                    <div class="sx-price-aside__card">
                        <h4>At a glance</h4>
                        <ul class="sx-point-list">
                            <li>Install once: TZS 1,000,000</li>
                            <li>Then TZS 500 / active student / month</li>
                            <li>Training &amp; go-live included in install</li>
                            <li>SMS, support &amp; hosting in monthly fee</li>
                        </ul>
                    </div>

                    <div class="sx-price-aside__card sx-price-aside__card--soft">
                        <h4>Also explore</h4>
                        <ul class="sx-price-aside__links">
                            <li><a href="{{ route('site.services') }}"><i class="fa fa-angle-right"></i> Services included</a></li>
                            <li><a href="{{ route('site.modules') }}"><i class="fa fa-angle-right"></i> Module suite</a></li>
                            <li><a href="{{ route('site.register_school') }}"><i class="fa fa-angle-right"></i> Register school</a></li>
                            <li><a href="{{ route('online_application') }}"><i class="fa fa-angle-right"></i> Online application</a></li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="cta-two-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 cta-left">
                <h1>Start with TZS 1,000,000 installation</h1>
            </div>
            <div class="col-lg-4 cta-right">
                <a class="primary-btn wh" href="{{ route('site.contact') }}">Contact Sales</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-price-banner.about-banner {
    min-height: 340px !important;
    padding-top: 168px !important;
    padding-bottom: 72px !important;
    position: relative;
    overflow: hidden;
}
.sx-price-banner__glow {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
}
.sx-price-banner__glow--a {
    width: 320px; height: 320px;
    top: -80px; right: -60px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
}
.sx-price-banner__glow--b {
    width: 260px; height: 260px;
    bottom: -100px; left: -40px;
    background: radial-gradient(circle, rgba(196,58,58,0.35) 0%, transparent 70%);
}
.sx-price-banner .container { position: relative; z-index: 2; }
.sx-price-eyebrow {
    display: inline-block;
    color: #ffd6d6;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.sx-price-banner .about-content h1 {
    font-size: 38px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sx-price-lead {
    max-width: 620px;
    margin: 14px auto 0;
    opacity: .92;
    font-size: 15px;
    line-height: 1.7;
}

.sx-price-highlights {
    margin-top: -36px;
    position: relative;
    z-index: 3;
    padding-bottom: 8px;
}
.sx-price-highlight {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border: 1px solid #f0dada;
    padding: 18px 20px;
    height: 100%;
    box-shadow: 0 10px 28px rgba(148,0,0,0.06);
}
.sx-price-highlight--alt { background: linear-gradient(180deg, #fff 0%, #FFF5F5 100%); }
.sx-price-highlight__badge {
    display: inline-block;
    background: #940000;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 6px 10px;
    flex-shrink: 0;
}
.sx-price-highlight__badge--soft {
    background: #FFF5F5;
    color: #940000;
    border: 1px solid #f0dada;
}
.sx-price-highlight strong {
    display: block;
    color: #222;
    font-size: 15px;
    font-weight: 800;
}
.sx-price-highlight span {
    display: block;
    color: #666;
    font-size: 13px;
}
.sx-price-highlight__amount {
    margin-left: auto;
    color: #940000;
    font-size: 22px;
    font-weight: 800;
    white-space: nowrap;
}

.sx-price-page {
    padding-top: 40px !important;
    padding-bottom: 48px !important;
}
.sx-price-plan {
    border: 1px solid #f0dada;
    background: #fff;
    padding: 24px 20px;
    height: 100%;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.sx-price-plan:hover {
    border-color: #e8b4b4;
    box-shadow: 0 12px 30px rgba(148,0,0,0.08);
    transform: translateY(-2px);
}
.sx-price-plan--featured {
    border-top: 3px solid #940000;
    background: linear-gradient(180deg, #fff 0%, #FFF5F5 100%);
}
.sx-price-plan__head { margin-bottom: 18px; }
.sx-price-plan__tag {
    display: inline-block;
    background: #940000;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 4px 10px;
}
.sx-price-plan__tag--soft {
    background: #FFF5F5;
    color: #940000;
    border: 1px solid #f0dada;
}
.sx-price-plan h3 {
    color: #940000;
    font-weight: 800;
    font-size: 20px;
    margin: 12px 0 6px;
}
.sx-price-plan__head p {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 12px;
}
.sx-price-plan__amount {
    color: #222;
    font-size: 30px;
    font-weight: 800;
    line-height: 1.1;
}
.sx-price-plan__head small {
    color: #888;
    font-size: 12px;
}
.sx-price-plan h5 {
    color: #222;
    font-weight: 700;
    margin: 0 0 12px;
    border-top: 1px solid #f0dada;
    padding-top: 16px;
    font-size: 14px;
}

.sx-price-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.sx-price-calc {
    border: 1px solid #f0dada;
    background: #fff;
    padding: 26px 22px;
    margin-bottom: 24px;
}
.sx-price-calc__intro h2 {
    color: #222;
    font-weight: 800;
    font-size: 24px;
    margin-bottom: 8px;
}
.sx-price-calc__intro p {
    color: #666;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 18px;
}
.sx-price-example {
    background: linear-gradient(135deg, #FFF5F5, #fff);
    border: 1px solid #f0dada;
    padding: 18px 14px;
    text-align: center;
    height: 100%;
}
.sx-price-example span {
    display: block;
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: .04em;
}
.sx-price-example strong {
    display: block;
    font-size: 24px;
    font-weight: 800;
    color: #940000;
    margin-top: 8px;
}
.sx-price-example small {
    color: #666;
    font-size: 12px;
}

.sx-price-faq {
    border: 1px solid #f0dada;
    background: linear-gradient(180deg, #FFF5F5 0%, #fff 100%);
    padding: 24px 20px;
}
.sx-price-faq h3 {
    color: #940000;
    font-weight: 800;
    font-size: 18px;
    margin-bottom: 14px;
}
.sx-price-faq__item {
    border-top: 1px solid #f0dada;
    padding: 12px 0;
}
.sx-price-faq__item summary {
    cursor: pointer;
    font-weight: 700;
    color: #222;
    font-size: 14px;
    outline: none;
}
.sx-price-faq__item p {
    margin: 8px 0 0;
    color: #555;
    font-size: 14px;
    line-height: 1.65;
}
.sx-price-faq__more {
    display: inline-block;
    margin-top: 12px;
    color: #940000 !important;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none !important;
}

.sx-price-aside { display: flex; flex-direction: column; gap: 16px; }
.sx-price-aside__card {
    border: 1px solid #f0dada;
    background: #fff;
    padding: 22px 20px;
}
.sx-price-aside__card--primary {
    background: linear-gradient(160deg, rgba(148,0,0,0.96) 0%, rgba(92,0,0,0.94) 55%, rgba(26,12,22,0.96) 100%);
    border-color: transparent;
    color: #fff;
}
.sx-price-aside__card--primary h3 {
    color: #fff;
    font-weight: 800;
    font-size: 22px;
    margin: 6px 0 10px;
}
.sx-price-aside__card--primary p { color: rgba(255,255,255,0.88); font-size: 14px; line-height: 1.65; }
.sx-price-aside__eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #ffd6d6;
    margin: 0;
}
.sx-price-aside__card--primary .primary-btn {
    display: inline-block;
    margin: 8px 0 14px;
    background: #fff !important;
    color: #940000 !important;
    border: 0;
}
.sx-price-aside__link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffd6d6 !important;
    font-size: 13px;
    margin-top: 8px;
    text-decoration: none !important;
}
.sx-price-aside__link:hover { color: #fff !important; }
.sx-price-aside__card h4 {
    color: #940000;
    font-weight: 800;
    font-size: 16px;
    margin-bottom: 12px;
}
.sx-price-aside__links {
    list-style: none;
    margin: 0;
    padding: 0;
}
.sx-price-aside__links li { margin-bottom: 8px; }
.sx-price-aside__links a {
    color: #333;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sx-price-aside__links a:hover { color: #940000; }
.sx-price-aside__links i { color: #940000; }
.sx-price-aside__card--soft {
    background: linear-gradient(180deg, #FFF5F5 0%, #fff 100%);
}

@media (max-width: 991.98px) {
    .sx-price-banner.about-banner {
        min-height: 300px !important;
        padding-top: 148px !important;
        padding-bottom: 60px !important;
    }
    .sx-price-banner .about-content h1 { font-size: 30px; }
    .sx-price-highlights { margin-top: -28px; }
    .sx-price-highlight { flex-wrap: wrap; }
    .sx-price-highlight__amount { margin-left: 0; width: 100%; }
}
@media (max-width: 575.98px) {
    .sx-price-banner .about-content h1 { font-size: 26px; }
    .sx-price-plan__amount { font-size: 26px; }
}

html[data-sx-theme="dark"] .sx-price-highlight,
html[data-sx-theme="dark"] .sx-price-highlight--alt,
html[data-sx-theme="dark"] .sx-price-plan,
html[data-sx-theme="dark"] .sx-price-plan--featured,
html[data-sx-theme="dark"] .sx-price-calc,
html[data-sx-theme="dark"] .sx-price-example,
html[data-sx-theme="dark"] .sx-price-faq,
html[data-sx-theme="dark"] .sx-price-aside__card {
    background: #1f1418 !important;
    border-color: rgba(255,180,180,0.16) !important;
}
html[data-sx-theme="dark"] .sx-price-highlight strong,
html[data-sx-theme="dark"] .sx-price-plan h3,
html[data-sx-theme="dark"] .sx-price-plan h5,
html[data-sx-theme="dark"] .sx-price-calc h2,
html[data-sx-theme="dark"] .sx-price-faq h3,
html[data-sx-theme="dark"] .sx-price-faq h4,
html[data-sx-theme="dark"] .sx-price-aside__card h3,
html[data-sx-theme="dark"] .sx-price-aside__card h4,
html[data-sx-theme="dark"] .sx-price-plan .sx-point-list li,
html[data-sx-theme="dark"] .sx-price-aside__card .sx-point-list li {
    color: #fff !important;
}
html[data-sx-theme="dark"] .sx-price-highlight > div > span,
html[data-sx-theme="dark"] .sx-price-plan p,
html[data-sx-theme="dark"] .sx-price-plan small,
html[data-sx-theme="dark"] .sx-price-calc p,
html[data-sx-theme="dark"] .sx-price-example span,
html[data-sx-theme="dark"] .sx-price-example small,
html[data-sx-theme="dark"] .sx-price-faq p,
html[data-sx-theme="dark"] .sx-price-aside__card:not(.sx-price-aside__card--primary) p,
html[data-sx-theme="dark"] .sx-price-aside__links a,
html[data-sx-theme="dark"] .sx-price-kicker {
    color: #e8d6da !important;
}
html[data-sx-theme="dark"] .sx-price-faq__item {
    border-top-color: rgba(255,180,180,0.16) !important;
}
html[data-sx-theme="dark"] .sx-price-faq__item summary {
    color: #fff !important;
}
html[data-sx-theme="dark"] .sx-price-faq__item p {
    color: #e8d6da !important;
}
html[data-sx-theme="dark"] .sx-price-faq__more {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-price-highlight__amount,
html[data-sx-theme="dark"] .sx-price-plan__amount,
html[data-sx-theme="dark"] .sx-price-example strong {
    color: #ffc2c2 !important;
}
html[data-sx-theme="dark"] .sx-price-highlight__badge,
html[data-sx-theme="dark"] .sx-price-plan__tag {
    background: #940000 !important;
    color: #fff !important;
}
html[data-sx-theme="dark"] .sx-price-highlight__badge--soft,
html[data-sx-theme="dark"] .sx-price-plan__tag--soft {
    background: rgba(255,180,180,0.22) !important;
    color: #ffd6d6 !important;
    border: 1px solid rgba(255,180,180,0.35) !important;
}
html[data-sx-theme="dark"] .sx-price-plan h5 { border-top-color: rgba(255,180,180,0.16) !important; }
html[data-sx-theme="dark"] .sx-price-aside__card--primary {
    background: linear-gradient(160deg, #5c0000 0%, #940000 48%, #3a0000 100%) !important;
    border-color: transparent !important;
}
html[data-sx-theme="dark"] .sx-price-aside__card--primary,
html[data-sx-theme="dark"] .sx-price-aside__card--primary h3,
html[data-sx-theme="dark"] .sx-price-aside__card--primary p,
html[data-sx-theme="dark"] .sx-price-aside__card--primary a,
html[data-sx-theme="dark"] .sx-price-aside__card--primary li {
    color: #fff !important;
}
html[data-sx-theme="dark"] .sx-price-aside__card--soft {
    background: #1f1418 !important;
}
</style>
@endpush

@push('schema')
@php
    $offerSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => 'ShuleXpert School Management System',
        'description' => 'Complete school management platform for schools worldwide with one-time installation and monthly per-student subscription.',
        'brand' => [
            '@type' => 'Brand',
            'name' => 'ShuleXpert',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => url('/'),
        ],
        'offers' => [
            [
                '@type' => 'Offer',
                'name' => 'Installation Fee',
                'price' => '1000000',
                'priceCurrency' => 'TZS',
                'category' => 'One-time setup',
                'description' => 'One-time installation covering training, expert facilitators, cloud storage setup, configuration, go-live support and data migration assistance.',
                'url' => url('/pricing'),
                'availability' => 'https://schema.org/InStock',
            ],
            [
                '@type' => 'Offer',
                'name' => 'Monthly Per Student Subscription',
                'price' => '500',
                'priceCurrency' => 'TZS',
                'unitText' => 'MONTH',
                'category' => 'Subscription',
                'description' => 'TZS 500 per active student per month covering SMS notifications, user support, updates, parent portal access, uptime and backups.',
                'url' => url('/pricing'),
                'availability' => 'https://schema.org/InStock',
            ],
        ],
    ];

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faqs)->map(function ($item) {
            return [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['a'],
                ],
            ];
        })->merge([
            [
                '@type' => 'Question',
                'name' => 'How much is ShuleXpert installation?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'ShuleXpert installation is a one-time fee of TZS 1,000,000 covering training, expert facilitators, cloud storage setup, system configuration, go-live support and data migration assistance.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is the monthly ShuleXpert fee?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'The monthly subscription is TZS 500 per active student. It covers SMS notification services, continuous user support, platform updates, parent portal access, uptime and backups.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Who provides ShuleXpert?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'ShuleXpert is powered by EmCa Techonology, based in Moshi, Kilimanjaro, Tanzania.',
                ],
            ],
        ])->values()->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($offerSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
