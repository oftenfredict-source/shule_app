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
<section class="banner-area relative about-banner sx-price-page-banner" id="home" style="background-image:url('{{ asset('images/pricing-hero.jpg') }}')">
    <div class="sx-price-page-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ __('pricing.title') }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('pricing') }}">{{ __('pricing.title') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-price-page-wrap">
    {{-- Intro --}}
    <div class="container sx-price-block">
        <div class="sx-price-intro-lead text-center">
            <p class="mb-0">{{ __('pricing.lead') }}</p>
        </div>
    </div>

    {{-- Highlights --}}
    <div class="container sx-price-block">
        <div class="row">
            <div class="col-md-6 mb-30">
                <div class="sx-price-highlight-card">
                    <span class="sx-price-highlight-card__badge">{{ __('pricing.one_time') }}</span>
                    <div>
                        <strong>{{ __('pricing.installation') }}</strong>
                        <span>{{ __('pricing.paid_once') }}</span>
                    </div>
                    <div class="sx-price-highlight-card__amount">TZS 1,000,000</div>
                </div>
            </div>
            <div class="col-md-6 mb-30">
                <div class="sx-price-highlight-card sx-price-highlight-card--alt">
                    <span class="sx-price-highlight-card__badge sx-price-highlight-card__badge--soft">{{ __('pricing.monthly') }}</span>
                    <div>
                        <strong>{{ __('pricing.per_student') }}</strong>
                        <span>{{ __('pricing.active_students') }}</span>
                    </div>
                    <div class="sx-price-highlight-card__amount">TZS 500</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Plans --}}
    <div class="sx-price-soft">
        <div class="container sx-price-block">
            <div class="sx-price-section-head text-center">
                <p class="sx-price-kicker">{{ __('pricing.title') }}</p>
                <h2 class="sx-price-heading">{{ __('pricing.plans_title') }}</h2>
            </div>
            <div class="row">
                <div class="col-lg-6 mb-30">
                    <article class="sx-price-plan-card sx-price-plan-card--featured">
                        <div class="sx-price-plan-card__head">
                            <span class="sx-price-plan-card__tag">{{ __('pricing.one_time') }}</span>
                            <h3>{{ __('pricing.fee_title') }}</h3>
                            <p>{{ __('pricing.fee_desc') }}</p>
                            <div class="sx-price-plan-card__amount">TZS 1,000,000</div>
                            <small>{{ __('pricing.one_time_setup') }}</small>
                        </div>
                        <h5>{{ __('pricing.covers') }}</h5>
                        <ul class="sx-price-list">
                            @foreach($installPoints as $point)
                                <li><strong>{{ $point['label'] }}</strong>: {{ $point['text'] }}</li>
                            @endforeach
                        </ul>
                    </article>
                </div>
                <div class="col-lg-6 mb-30">
                    <article class="sx-price-plan-card">
                        <div class="sx-price-plan-card__head">
                            <span class="sx-price-plan-card__tag sx-price-plan-card__tag--soft">{{ __('pricing.monthly') }}</span>
                            <h3>{{ __('pricing.sub_title') }}</h3>
                            <p>{{ __('pricing.sub_desc') }}</p>
                            <div class="sx-price-plan-card__amount">TZS 500</div>
                            <small>{{ __('pricing.per_student_month') }}</small>
                        </div>
                        <h5>{{ __('pricing.covers') }}</h5>
                        <ul class="sx-price-list">
                            @foreach($monthlyPoints as $point)
                                <li><strong>{{ $point['label'] }}</strong>: {{ $point['text'] }}</li>
                            @endforeach
                        </ul>
                    </article>
                </div>
            </div>
        </div>
    </div>

    {{-- Examples --}}
    <div class="container sx-price-block">
        <div class="sx-price-section-head text-center">
            <p class="sx-price-kicker">{{ __('pricing.cost_kicker') }}</p>
            <h2 class="sx-price-heading">{{ __('pricing.cost_title') }}</h2>
            <p class="sx-price-text mx-auto mb-0" style="max-width:560px">{!! __('pricing.cost_formula') !!}</p>
        </div>
        <div class="row">
            @foreach($examples as $ex)
                <div class="col-md-4 mb-30">
                    <div class="sx-price-example-card">
                        <span>{{ number_format($ex['students']) }} {{ __('pricing.students') }}</span>
                        <strong>TZS {{ $ex['monthly'] }}</strong>
                        <small>{{ __('pricing.per_month') }}</small>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- FAQ + next steps --}}
    <div class="container sx-price-block sx-price-block--last">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="sx-price-faq-panel">
                    <h2 class="sx-price-heading">{{ __('pricing.faq_title') }}</h2>
                    @foreach($faqs as $i => $item)
                        <details class="sx-price-faq-item" @if($i === 0) open @endif>
                            <summary>{{ $item['q'] }}</summary>
                            <p>{{ $item['a'] }}</p>
                        </details>
                    @endforeach
                    <a href="{{ route('site.faq') }}" class="sx-price-faq-more">{{ __('pricing.more_faq') }} <i class="fa fa-angle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sx-price-side-cta">
                    <p class="sx-price-side-cta__eyebrow">{{ __('pricing.aside_eyebrow') }}</p>
                    <h3>{{ __('pricing.aside_title') }}</h3>
                    <p>{{ __('pricing.aside_text') }}</p>
                    <ul>
                        <li><a href="{{ route('site.services') }}"><i class="fa fa-angle-right"></i> {{ __('site.our_services') }}</a></li>
                        <li><a href="{{ route('site.modules') }}"><i class="fa fa-angle-right"></i> {{ __('site.modules') }}</a></li>
                        <li><a href="{{ route('site.register_school') }}"><i class="fa fa-angle-right"></i> {{ __('site.register_school_short') }}</a></li>
                        <li><a href="{{ route('site.contact') }}"><i class="fa fa-angle-right"></i> {{ __('site.contact_us') }}</a></li>
                    </ul>
                    <a href="{{ route('site.contact') }}" class="btn btn-light btn-block">{{ __('site.request_quotation') }}</a>
                    <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-price-side-cta__link">
                        <i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-public-site .banner-area.sx-price-page-banner.about-banner,
.sx-price-page-banner.about-banner {
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
.sx-price-page-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(92,0,0,.58) 0%, rgba(148,0,0,.42) 55%, rgba(26,10,10,.35) 100%);
    z-index: 1;
}
.sx-price-page-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-price-page-banner .about-content h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-price-page-banner .link-nav a { color: #fff !important; }

.sx-price-page-wrap > .container.sx-price-block {
    padding-top: 48px !important;
    padding-bottom: 0 !important;
}
.sx-price-page-wrap > .sx-price-soft > .container.sx-price-block {
    padding-top: 48px !important;
    padding-bottom: 20px !important;
}
.sx-price-page-wrap > .container.sx-price-block.sx-price-block--last {
    padding-bottom: 90px !important;
}
.sx-price-page-wrap { padding-bottom: 20px; }

.sx-price-intro-lead {
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
.sx-price-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.sx-price-heading {
    color: #1a1a1a;
    font-weight: 800;
    font-size: 26px;
    line-height: 1.3;
    margin: 0 0 14px;
}
.sx-price-text {
    color: #555;
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 12px;
}
.sx-price-section-head { margin-bottom: 28px; }
.sx-price-soft { background: #fff8f8; margin-top: 20px; }

.sx-price-highlight-card {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 20px 18px;
    height: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
}
.sx-price-highlight-card--alt {
    background: linear-gradient(180deg, #fff 0%, #fff8f8 100%);
}
.sx-price-highlight-card__badge {
    background: #940000;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 5px 10px;
    border-radius: 999px;
}
.sx-price-highlight-card__badge--soft {
    background: #fff0f0;
    color: #940000;
}
.sx-price-highlight-card strong {
    display: block;
    color: #1a1a1a;
    font-size: 15px;
    font-weight: 800;
}
.sx-price-highlight-card span {
    display: block;
    color: #777;
    font-size: 12px;
}
.sx-price-highlight-card__amount {
    margin-left: auto;
    color: #940000;
    font-size: 22px;
    font-weight: 800;
}

.sx-price-plan-card {
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    padding: 26px 22px;
    height: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
    transition: transform .25s ease, box-shadow .25s ease;
}
.sx-price-plan-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(148,0,0,.1);
}
.sx-price-plan-card--featured {
    border-color: rgba(148,0,0,.22);
    background: linear-gradient(180deg, #fff 0%, #fff8f8 100%);
}
.sx-price-plan-card__tag {
    display: inline-block;
    background: #940000;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 5px 10px;
    border-radius: 999px;
    margin-bottom: 12px;
}
.sx-price-plan-card__tag--soft {
    background: #fff0f0;
    color: #940000;
}
.sx-price-plan-card h3 {
    color: #1a1a1a;
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 8px;
}
.sx-price-plan-card__head p {
    color: #666;
    font-size: 14px;
    line-height: 1.55;
    margin-bottom: 12px;
}
.sx-price-plan-card__amount {
    color: #940000;
    font-size: 28px;
    font-weight: 800;
    line-height: 1.1;
}
.sx-price-plan-card__head small {
    display: block;
    color: #888;
    margin: 4px 0 16px;
}
.sx-price-plan-card h5 {
    color: #1a1a1a;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    border-top: 1px solid #f0e4e4;
    padding-top: 14px;
    margin-bottom: 12px;
}
.sx-price-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.sx-price-list li {
    position: relative;
    padding-left: 20px;
    margin-bottom: 10px;
    color: #555;
    font-size: 13.5px;
    line-height: 1.5;
}
.sx-price-list li:before {
    content: "\f00c";
    font-family: FontAwesome, "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    left: 0;
    top: 2px;
    color: #940000;
    font-size: 12px;
}

.sx-price-example-card {
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 22px 16px;
    text-align: center;
    height: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
}
.sx-price-example-card span {
    display: block;
    color: #777;
    font-size: 13px;
    margin-bottom: 6px;
}
.sx-price-example-card strong {
    display: block;
    color: #940000;
    font-size: 22px;
    font-weight: 800;
}
.sx-price-example-card small { color: #888; }

.sx-price-faq-panel {
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    background: linear-gradient(180deg, #fff 0%, #fff8f8 100%);
    padding: 28px 24px;
    height: 100%;
}
.sx-price-faq-item {
    border-bottom: 1px solid #f0e4e4;
    padding: 12px 0;
}
.sx-price-faq-item summary {
    cursor: pointer;
    font-weight: 700;
    color: #1a1a1a;
    font-size: 14.5px;
    list-style: none;
}
.sx-price-faq-item summary::-webkit-details-marker { display: none; }
.sx-price-faq-item p {
    color: #666;
    font-size: 14px;
    line-height: 1.65;
    margin: 10px 0 4px;
}
.sx-price-faq-more {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 16px;
    color: #940000 !important;
    font-weight: 700;
    text-decoration: none !important;
    font-size: 14px;
}

.sx-price-side-cta {
    border-radius: 14px;
    background: linear-gradient(165deg, #940000 0%, #7a0000 48%, #5c0000 100%);
    padding: 26px 22px;
    color: #fff;
    height: 100%;
    box-shadow: 0 14px 30px rgba(148,0,0,.22);
}
.sx-price-side-cta__eyebrow {
    color: #ffd6d6;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.sx-price-side-cta h3 {
    color: #fff;
    font-weight: 800;
    font-size: 20px;
    margin-bottom: 10px;
}
.sx-price-side-cta > p {
    color: rgba(255,255,255,.88);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 16px;
}
.sx-price-side-cta ul { list-style: none; margin: 0 0 18px; padding: 0; }
.sx-price-side-cta li { margin-bottom: 10px; }
.sx-price-side-cta a {
    color: #ffd6d6 !important;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sx-price-side-cta a:hover { color: #fff !important; }
.sx-price-side-cta .btn-light {
    background: #fff !important;
    color: #940000 !important;
    font-weight: 800;
    border: 0;
    margin-bottom: 12px;
}
.sx-price-side-cta__link {
    display: inline-flex !important;
    margin-top: 4px;
}

@media (max-width: 991.98px) {
    .sx-price-page-banner.about-banner {
        min-height: 240px !important;
        height: 240px !important;
        background-position: center 30% !important;
    }
    .sx-price-page-banner .about-content h1 { font-size: 24px; }
    .sx-price-heading { font-size: 22px; }
    .sx-price-highlight-card__amount { margin-left: 0; width: 100%; }
    .sx-price-page-wrap > .container.sx-price-block.sx-price-block--last {
        padding-bottom: 70px !important;
    }
}
@media (max-width: 575.98px) {
    .sx-price-page-banner.about-banner {
        min-height: 200px !important;
        height: 200px !important;
    }
    .sx-price-page-banner .about-content h1 { font-size: 22px; }
    .sx-price-intro-lead { padding: 18px 16px; }
}
</style>
@endpush
