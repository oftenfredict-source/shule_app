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
<section class="banner-area relative about-banner sx-faq-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="sx-faq-banner__glow sx-faq-banner__glow--a" aria-hidden="true"></div>
    <div class="sx-faq-banner__glow sx-faq-banner__glow--b" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12 text-center">
                <h1 class="text-white">{{ __('faq.title') }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.faq') }}">{{ __('site.faq') }}</a>
                </p>
                <p class="sx-faq-lead text-white">
                    {{ __('faq.lead') }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-faq-highlights">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4 mb-3 mb-lg-0">
                <a href="{{ route('pricing') }}" class="sx-faq-highlight">
                    <span class="sx-faq-highlight__icon"><i class="fa fa-tag"></i></span>
                    <span class="sx-faq-highlight__body">
                        <strong>Transparent pricing</strong>
                        <span>TZS 1M install, TZS 500 / student</span>
                    </span>
                </a>
            </div>
            <div class="col-lg-4 col-md-4 mb-3 mb-lg-0">
                <a href="{{ route('site.modules') }}" class="sx-faq-highlight">
                    <span class="sx-faq-highlight__icon"><i class="fa fa-th-large"></i></span>
                    <span class="sx-faq-highlight__body">
                        <strong>Full module suite</strong>
                        <span>Fees, exams, attendance &amp; more</span>
                    </span>
                </a>
            </div>
            <div class="col-lg-4 col-md-4">
                <a href="{{ route('site.contact') }}" class="sx-faq-highlight">
                    <span class="sx-faq-highlight__icon"><i class="fa fa-life-ring"></i></span>
                    <span class="sx-faq-highlight__body">
                        <strong>Local support</strong>
                        <span>Moshi, Kilimanjaro, EmCa</span>
                    </span>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="sx-faq-page section-gap">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-40 mb-lg-0">
                <div class="sx-faq-toolbar">
                    <label class="sx-faq-search" for="faqSearch">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <input type="search" id="faqSearch" placeholder="Search questionsâ€¦" autocomplete="off">
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
                <p class="sx-faq-empty" id="faqEmpty" hidden>No questions match your search. Try another keyword or contact us.</p>
            </div>

            <aside class="col-lg-4">
                <div class="sx-faq-aside">
                    <div class="sx-faq-aside__card sx-faq-aside__card--primary">
                        <p class="sx-faq-aside__eyebrow">{{ __('faq.aside_eyebrow') }}</p>
                        <h3>{{ __('faq.aside_title') }}</h3>
                        <p>{{ __('faq.aside_text') }}</p>
                        <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase">{{ __('site.ask_question') }}</a>
                        <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-faq-aside__link">
                            <i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}
                        </a>
                        <a href="mailto:emca@emca.tech" class="sx-faq-aside__link">
                            <i class="fa fa-envelope"></i> emca@emca.tech
                        </a>
                    </div>

                    <div class="sx-faq-aside__card">
                        <h4>Quick links</h4>
                        <ul class="sx-faq-aside__links">
                            <li><a href="{{ route('pricing') }}"><i class="fa fa-angle-right"></i> View pricing</a></li>
                            <li><a href="{{ route('site.modules') }}"><i class="fa fa-angle-right"></i> Browse modules</a></li>
                            <li><a href="{{ route('site.register_school') }}"><i class="fa fa-angle-right"></i> Register your school</a></li>
                            <li><a href="{{ route('site.download') }}"><i class="fa fa-angle-right"></i> Download the app</a></li>
                        </ul>
                    </div>

                    <div class="sx-faq-aside__card sx-faq-aside__card--soft">
                        <h4>At a glance</h4>
                        <ul class="sx-point-list">
                            <li>One-time install: TZS 1,000,000</li>
                            <li>Monthly: TZS 500 / student</li>
                            <li>Preschool â†’ A-level ready</li>
                            <li>ZKTeco biometric support</li>
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
                <h1>Ready to digitize your school?</h1>
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
.sx-faq-banner.about-banner {
    min-height: 340px !important;
    padding-top: 168px !important;
    padding-bottom: 72px !important;
    position: relative;
    overflow: hidden;
}
.sx-faq-banner__glow {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
}
.sx-faq-banner__glow--a {
    width: 320px; height: 320px;
    top: -80px; right: -60px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
}
.sx-faq-banner__glow--b {
    width: 260px; height: 260px;
    bottom: -100px; left: -40px;
    background: radial-gradient(circle, rgba(196,58,58,0.35) 0%, transparent 70%);
}
.sx-faq-banner .container { position: relative; z-index: 2; }
.sx-faq-eyebrow {
    display: inline-block;
    color: #ffd6d6;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.sx-faq-banner .about-content h1 {
    font-size: 38px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sx-faq-lead {
    max-width: 620px;
    margin: 14px auto 0;
    opacity: .92;
    font-size: 15px;
    line-height: 1.7;
}

.sx-faq-highlights {
    margin-top: -36px;
    position: relative;
    z-index: 3;
    padding-bottom: 8px;
}
.sx-faq-highlight {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border: 1px solid #f0dada;
    padding: 18px 16px;
    text-decoration: none !important;
    height: 100%;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    box-shadow: 0 10px 28px rgba(148,0,0,0.06);
}
.sx-faq-highlight:hover {
    transform: translateY(-3px);
    border-color: #e8b4b4;
    box-shadow: 0 14px 32px rgba(148,0,0,0.12);
}
.sx-faq-highlight__icon {
    width: 46px; height: 46px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(145deg, #940000, #c43a3a);
    color: #fff;
    flex-shrink: 0;
}
.sx-faq-highlight__body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}
.sx-faq-highlight__body strong {
    color: #222;
    font-size: 15px;
    font-weight: 800;
}
.sx-faq-highlight__body span {
    color: #666;
    font-size: 13px;
}

.sx-faq-page {
    padding-top: 40px !important;
    padding-bottom: 56px !important;
}
.sx-faq-toolbar { margin-bottom: 22px; }
.sx-faq-search {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid #f0dada;
    background: linear-gradient(180deg, #fff 0%, #FFF5F5 100%);
    padding: 0 16px;
    margin-bottom: 14px;
}
.sx-faq-search i { color: #940000; }
.sx-faq-search input {
    border: 0;
    background: transparent;
    width: 100%;
    height: 48px;
    outline: none;
    font-family: inherit;
    font-size: 14px;
    color: #222;
}
.sx-faq-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.sx-faq-filter {
    border: 1px solid #f0dada;
    background: #fff;
    color: #555;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 8px 14px;
    cursor: pointer;
    transition: background .2s ease, color .2s ease, border-color .2s ease;
}
.sx-faq-filter:hover { border-color: #e8b4b4; color: #940000; }
.sx-faq-filter.is-active {
    background: #940000;
    border-color: #940000;
    color: #fff;
}

.sx-faq-item {
    border: 1px solid #f0dada;
    background: #fff;
    margin-bottom: 12px;
    overflow: hidden;
    transition: border-color .2s ease, box-shadow .2s ease;
}
.sx-faq-item.is-open,
.sx-faq-item:hover {
    border-color: #e8b4b4;
    box-shadow: 0 8px 24px rgba(148,0,0,0.06);
}
.sx-faq-item__q {
    width: 100%;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    text-align: left;
    background: transparent;
    border: 0;
    padding: 18px 16px;
    cursor: pointer;
    font-family: inherit;
}
.sx-faq-item__icon {
    width: 40px; height: 40px;
    display: flex; align-items: center; justify-content: center;
    background: #FFF5F5;
    color: #940000;
    border: 1px solid #f0dada;
    flex-shrink: 0;
}
.sx-faq-item.is-open .sx-faq-item__icon {
    background: linear-gradient(145deg, #940000, #c43a3a);
    color: #fff;
    border-color: transparent;
}
.sx-faq-item__text { flex: 1; min-width: 0; }
.sx-faq-item__cat {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #940000;
    margin-bottom: 4px;
}
.sx-faq-item__title {
    display: block;
    color: #222;
    font-size: 16px;
    font-weight: 800;
    line-height: 1.4;
}
.sx-faq-item__chev {
    color: #940000;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 6px;
    transition: transform .2s ease;
}
.sx-faq-item.is-open .sx-faq-item__chev { transform: rotate(45deg); }
.sx-faq-item__a {
    padding: 0 16px 18px 70px;
}
.sx-faq-item__a p {
    margin: 0;
    color: #555;
    font-size: 14px;
    line-height: 1.75;
    border-top: 1px solid #f7e6e6;
    padding-top: 14px;
}
.sx-faq-empty {
    text-align: center;
    color: #777;
    padding: 28px 12px;
    border: 1px dashed #f0dada;
    background: #FFF5F5;
}

.sx-faq-aside { display: flex; flex-direction: column; gap: 16px; }
.sx-faq-aside__card {
    border: 1px solid #f0dada;
    background: #fff;
    padding: 22px 20px;
}
.sx-faq-aside__card--primary {
    background:
        linear-gradient(160deg, rgba(148,0,0,0.96) 0%, rgba(92,0,0,0.94) 55%, rgba(26,12,22,0.96) 100%);
    border-color: transparent;
    color: #fff;
}
.sx-faq-aside__card--primary h3 {
    color: #fff;
    font-weight: 800;
    font-size: 22px;
    margin: 6px 0 10px;
}
.sx-faq-aside__card--primary p { color: rgba(255,255,255,0.88); font-size: 14px; line-height: 1.65; }
.sx-faq-aside__eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #ffd6d6;
    margin: 0;
}
.sx-faq-aside__card--primary .primary-btn {
    display: inline-block;
    margin: 8px 0 14px;
    background: #fff !important;
    color: #940000 !important;
    border: 0;
}
.sx-faq-aside__link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffd6d6 !important;
    font-size: 13px;
    margin-top: 8px;
    text-decoration: none !important;
}
.sx-faq-aside__link:hover { color: #fff !important; }
.sx-faq-aside__card h4 {
    color: #940000;
    font-weight: 800;
    font-size: 16px;
    margin-bottom: 12px;
}
.sx-faq-aside__links {
    list-style: none;
    margin: 0;
    padding: 0;
}
.sx-faq-aside__links li { margin-bottom: 8px; }
.sx-faq-aside__links a {
    color: #333;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sx-faq-aside__links a:hover { color: #940000; }
.sx-faq-aside__links i { color: #940000; }
.sx-faq-aside__card--soft {
    background: linear-gradient(180deg, #FFF5F5 0%, #fff 100%);
}

@media (max-width: 991.98px) {
    .sx-faq-banner.about-banner {
        min-height: 300px !important;
        padding-top: 148px !important;
        padding-bottom: 60px !important;
    }
    .sx-faq-banner .about-content h1 { font-size: 30px; }
    .sx-faq-highlights { margin-top: -28px; }
    .sx-faq-item__a { padding-left: 16px; }
}
@media (max-width: 575.98px) {
    .sx-faq-banner .about-content h1 { font-size: 26px; }
    .sx-faq-item__q { padding: 14px 12px; gap: 10px; }
    .sx-faq-item__title { font-size: 15px; }
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

    function applyFilter() {
        var q = (search.value || '').trim().toLowerCase();
        var visible = 0;
        list.querySelectorAll('.sx-faq-item').forEach(function (item) {
            var catOk = activeCat === 'all' || item.getAttribute('data-cat') === activeCat;
            var textOk = !q || (item.getAttribute('data-q') || '').indexOf(q) !== -1;
            var show = catOk && textOk;
            item.hidden = !show;
            if (show) visible++;
        });
        empty.hidden = visible > 0;
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

    if (search) {
        search.addEventListener('input', applyFilter);
    }

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
