@extends('layouts.site')

@php
    $faqs = [
        ['cat' => 'pricing', 'icon' => 'fa-tag', 'q' => __('faq.q1'), 'a' => __('faq.a1')],
        ['cat' => 'pricing', 'icon' => 'fa-calendar', 'q' => __('faq.q2'), 'a' => __('faq.a2')],
        ['cat' => 'features', 'icon' => 'fa-graduation-cap', 'q' => __('faq.q3'), 'a' => __('faq.a3')],
        ['cat' => 'features', 'icon' => 'fa-credit-card', 'q' => __('faq.q4'), 'a' => __('faq.a4')],
        ['cat' => 'attendance', 'icon' => 'fa-hand-o-up', 'q' => __('faq.q5'), 'a' => __('faq.a5')],
        ['cat' => 'features', 'icon' => 'fa-th-large', 'q' => __('faq.q6'), 'a' => __('faq.a6')],
        ['cat' => 'getting-started', 'icon' => 'fa-rocket', 'q' => __('faq.q7'), 'a' => __('faq.a7')],
        ['cat' => 'getting-started', 'icon' => 'fa-mobile', 'q' => __('faq.q8'), 'a' => __('faq.a8')],
        ['cat' => 'support', 'icon' => 'fa-users', 'q' => __('faq.q9'), 'a' => __('faq.a9', ['phone' => config('shulexpert.phone_display')])],
        ['cat' => 'support', 'icon' => 'fa-life-ring', 'q' => __('faq.q10'), 'a' => __('faq.a10')],
    ];

    $categories = [
        'all' => __('faq.cat_all'),
        'pricing' => __('faq.cat_pricing'),
        'features' => __('faq.cat_features'),
        'attendance' => __('faq.cat_attendance'),
        'getting-started' => __('faq.cat_getting'),
        'support' => __('faq.cat_support'),
    ];
@endphp

@section('content')
<section class="banner-area relative about-banner sx-faq-page-banner" id="home" style="background-image:url('{{ asset('images/faq-hero.jpg') }}')">
    <div class="sx-faq-page-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ __('faq.title') }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('site.faq') }}">{{ __('site.faq') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-faq-page-wrap">
    <div class="container sx-faq-block">
        <div class="sx-faq-intro-lead text-center">
            <p class="mb-0">{{ __('faq.lead') }}</p>
        </div>
    </div>

    <div class="container sx-faq-block">
        <div class="row">
            <div class="col-md-4 mb-30">
                <a href="{{ route('pricing') }}" class="sx-faq-quick-card">
                    <span class="sx-faq-quick-card__icon"><i class="fa fa-tag"></i></span>
                    <strong>{{ __('faq.hl1_title') }}</strong>
                    <span>{{ __('faq.hl1_text') }}</span>
                </a>
            </div>
            <div class="col-md-4 mb-30">
                <a href="{{ route('site.modules') }}" class="sx-faq-quick-card">
                    <span class="sx-faq-quick-card__icon"><i class="fa fa-th-large"></i></span>
                    <strong>{{ __('faq.hl2_title') }}</strong>
                    <span>{{ __('faq.hl2_text') }}</span>
                </a>
            </div>
            <div class="col-md-4 mb-30">
                <a href="{{ route('site.contact') }}" class="sx-faq-quick-card">
                    <span class="sx-faq-quick-card__icon"><i class="fa fa-life-ring"></i></span>
                    <strong>{{ __('faq.hl3_title') }}</strong>
                    <span>{{ __('faq.hl3_text') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="container sx-faq-block sx-faq-block--last">
        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="sx-faq-main-panel">
                    <div class="sx-faq-toolbar">
                        <label class="sx-faq-search" for="faqSearch">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            <input type="search" id="faqSearch" placeholder="{{ __('faq.search_ph') }}" autocomplete="off">
                        </label>
                        <div class="sx-faq-filters" role="tablist" aria-label="FAQ categories">
                            @foreach($categories as $key => $label)
                                <button type="button"
                                        class="sx-faq-filter{{ $key === 'all' ? ' is-active' : '' }}"
                                        data-cat="{{ $key }}"
                                        role="tab"
                                        aria-selected="{{ $key === 'all' ? 'true' : 'false' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="sx-faq-list" id="faqList">
                        @foreach($faqs as $i => $item)
                            <article class="sx-faq-item{{ $i === 0 ? ' is-open' : '' }}"
                                     data-cat="{{ $item['cat'] }}"
                                     data-q="{{ strtolower($item['q'].' '.$item['a']) }}">
                                <button type="button" class="sx-faq-item__q" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">
                                    <span class="sx-faq-item__icon" aria-hidden="true"><i class="fa {{ $item['icon'] }}"></i></span>
                                    <span class="sx-faq-item__text">
                                        <span class="sx-faq-item__cat">{{ $categories[$item['cat']] ?? $item['cat'] }}</span>
                                        <span class="sx-faq-item__title">{{ $item['q'] }}</span>
                                    </span>
                                    <span class="sx-faq-item__chev" aria-hidden="true"><i class="fa fa-plus"></i></span>
                                </button>
                                <div class="sx-faq-item__a" @if($i !== 0) hidden @endif>
                                    <p>{{ $item['a'] }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <p class="sx-faq-empty" id="faqEmpty" hidden>{{ __('faq.empty') }}</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sx-faq-side-wrap">
                    <div class="sx-faq-side-cta">
                        <p class="sx-faq-side-cta__eyebrow">{{ __('faq.aside_eyebrow') }}</p>
                        <h3>{{ __('faq.aside_title') }}</h3>
                        <p>{{ __('faq.aside_text_short') }}</p>
                        <a href="{{ route('site.contact') }}" class="btn btn-light btn-block">{{ __('site.ask_question') }}</a>
                        <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-faq-side-cta__link">
                            <i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-public-site .banner-area.sx-faq-page-banner.about-banner,
.sx-faq-page-banner.about-banner {
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
.sx-faq-page-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(92,0,0,.58) 0%, rgba(148,0,0,.42) 55%, rgba(26,10,10,.35) 100%);
    z-index: 1;
}
.sx-faq-page-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-faq-page-banner .about-content h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-faq-page-banner .link-nav a { color: #fff !important; }

.sx-faq-page-wrap > .container.sx-faq-block {
    padding-top: 48px !important;
    padding-bottom: 0 !important;
}
.sx-faq-page-wrap > .container.sx-faq-block.sx-faq-block--last {
    padding-bottom: 90px !important;
}
.sx-faq-page-wrap { padding-bottom: 20px; }

.sx-faq-intro-lead {
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

.sx-faq-quick-card {
    display: flex;
    flex-direction: column;
    gap: 6px;
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 20px 18px;
    height: 100%;
    text-decoration: none !important;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
    transition: transform .25s ease, box-shadow .25s ease;
}
.sx-faq-quick-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(148,0,0,.1);
}
.sx-faq-quick-card__icon {
    width: 42px; height: 42px; border-radius: 50%;
    background: #940000; color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 6px;
}
.sx-faq-quick-card strong { color: #1a1a1a; font-size: 15px; font-weight: 800; }
.sx-faq-quick-card span { color: #666; font-size: 13px; line-height: 1.45; }

.sx-faq-main-panel {
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    background: #fff;
    padding: 22px 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
}
.sx-faq-toolbar { margin-bottom: 18px; }
.sx-faq-search {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid #f0e4e4;
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 12px;
    background: #fff8f8;
}
.sx-faq-search i { color: #940000; }
.sx-faq-search input {
    border: 0;
    outline: 0;
    width: 100%;
    background: transparent;
    font-size: 14px;
    color: #333;
}
.sx-faq-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.sx-faq-filter {
    border: 1px solid #f0e4e4;
    background: #fff;
    color: #555;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 999px;
    cursor: pointer;
}
.sx-faq-filter.is-active,
.sx-faq-filter:hover {
    background: #940000;
    border-color: #940000;
    color: #fff;
}

.sx-faq-item {
    border: 1px solid #f0e4e4;
    border-radius: 10px;
    margin-bottom: 10px;
    overflow: hidden;
    background: #fff;
}
.sx-faq-item__q {
    width: 100%;
    border: 0;
    background: transparent;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    text-align: left;
    padding: 14px 14px;
    cursor: pointer;
}
.sx-faq-item__icon {
    width: 36px; height: 36px; border-radius: 50%;
    background: #fff0f0; color: #940000;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sx-faq-item__text { flex: 1; min-width: 0; }
.sx-faq-item__cat {
    display: block;
    color: #940000;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.sx-faq-item__title {
    display: block;
    color: #1a1a1a;
    font-size: 14.5px;
    font-weight: 700;
    line-height: 1.4;
}
.sx-faq-item__chev {
    color: #940000;
    margin-top: 8px;
}
.sx-faq-item.is-open .sx-faq-item__chev i:before { content: "\f068"; }
.sx-faq-item__a {
    padding: 0 14px 14px 62px;
}
.sx-faq-item__a p {
    margin: 0;
    color: #666;
    font-size: 14px;
    line-height: 1.65;
}
.sx-faq-empty {
    color: #777;
    font-size: 14px;
    margin-top: 12px;
}

.sx-faq-side-wrap {
    position: sticky;
    top: 100px;
}
.sx-faq-side-cta {
    border-radius: 14px;
    background: linear-gradient(165deg, #940000 0%, #7a0000 48%, #5c0000 100%);
    padding: 20px 18px;
    color: #fff;
    height: auto;
    box-shadow: 0 14px 30px rgba(148,0,0,.22);
}
.sx-faq-side-cta__eyebrow {
    color: #ffd6d6;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.sx-faq-side-cta h3 {
    color: #fff;
    font-weight: 800;
    font-size: 18px;
    margin-bottom: 8px;
    line-height: 1.3;
}
.sx-faq-side-cta > p {
    color: rgba(255,255,255,.88);
    font-size: 13px;
    line-height: 1.5;
    margin-bottom: 14px;
}
.sx-faq-side-cta a {
    color: #ffd6d6 !important;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sx-faq-side-cta a:hover { color: #fff !important; }
.sx-faq-side-cta .btn-light {
    background: #fff !important;
    color: #940000 !important;
    font-weight: 800;
    border: 0;
    margin-bottom: 10px;
    padding: 8px 14px;
    font-size: 13px;
}
.sx-faq-side-cta__link { display: inline-flex !important; margin-top: 2px; font-size: 13px !important; }

@media (max-width: 991.98px) {
    .sx-faq-page-banner.about-banner {
        min-height: 240px !important;
        height: 240px !important;
        background-position: center 30% !important;
    }
    .sx-faq-page-banner .about-content h1 { font-size: 24px; }
    .sx-faq-item__a { padding-left: 14px; }
    .sx-faq-page-wrap > .container.sx-faq-block.sx-faq-block--last {
        padding-bottom: 70px !important;
    }
}
@media (max-width: 575.98px) {
    .sx-faq-page-banner.about-banner {
        min-height: 200px !important;
        height: 200px !important;
    }
    .sx-faq-page-banner .about-content h1 { font-size: 22px; }
    .sx-faq-intro-lead { padding: 18px 16px; }
}
</style>
@endpush

@push('schema')
@php
    $faq = [
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
        })->values()->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faq, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@push('scripts')
<script>
(function () {
    var list = document.getElementById('faqList');
    var empty = document.getElementById('faqEmpty');
    var search = document.getElementById('faqSearch');
    var filters = document.querySelectorAll('.sx-faq-filter');
    var activeCat = 'all';
    if (!list) return;

    function applyFilter() {
        var q = (search && search.value ? search.value : '').trim().toLowerCase();
        var visible = 0;
        list.querySelectorAll('.sx-faq-item').forEach(function (item) {
            var catOk = activeCat === 'all' || item.getAttribute('data-cat') === activeCat;
            var textOk = !q || (item.getAttribute('data-q') || '').indexOf(q) !== -1;
            var show = catOk && textOk;
            item.hidden = !show;
            if (show) visible++;
        });
        if (empty) empty.hidden = visible > 0;
    }

    filters.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filters.forEach(function (b) {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-selected', 'true');
            activeCat = btn.getAttribute('data-cat') || 'all';
            applyFilter();
        });
    });

    if (search) search.addEventListener('input', applyFilter);

    list.querySelectorAll('.sx-faq-item__q').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.sx-faq-item');
            var open = item.classList.contains('is-open');
            list.querySelectorAll('.sx-faq-item').forEach(function (other) {
                other.classList.remove('is-open');
                var oBtn = other.querySelector('.sx-faq-item__q');
                var oAns = other.querySelector('.sx-faq-item__a');
                if (oBtn) oBtn.setAttribute('aria-expanded', 'false');
                if (oAns) oAns.hidden = true;
            });
            if (!open) {
                item.classList.add('is-open');
                btn.setAttribute('aria-expanded', 'true');
                var ans = item.querySelector('.sx-faq-item__a');
                if (ans) ans.hidden = false;
            }
        });
    });
})();
</script>
@endpush
