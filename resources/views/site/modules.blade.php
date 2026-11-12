@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-modules-page-banner" id="home" style="background-image:url('{{ asset('images/modules-school-hero.jpg') }}')">
    <div class="sx-modules-page-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ __('site.modules') }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('site.modules') }}">{{ __('site.modules') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-modules-page">
    <div class="container sx-modules-block">
        <div class="sx-modules-intro text-center">
            <p class="sx-modules-intro__lead mb-0">{!! __('site.modules_intro') !!}</p>
        </div>
    </div>

    <div class="container sx-modules-block sx-modules-block--last">
        <div class="sx-modules-section-head text-center">
            <p class="sx-modules-kicker">{{ __('site.modules_grid_kicker') }}</p>
            <h2 class="sx-modules-heading">{{ __('site.modules_page_title') }}</h2>
            <p class="sx-modules-text mx-auto mb-0">{!! __('site.modules_page_desc') !!}</p>
        </div>

        <div class="row">
            @foreach($modules as $module)
                <div class="col-sm-6 col-lg-3 mb-30">
                    <article class="sx-modules-card">
                        <span class="sx-modules-card__icon"><i class="fa {{ $module['icon'] }}"></i></span>
                        <h3>{{ $module['title'] }}</h3>
                        <p>{{ $module['short'] }}</p>
                        <a href="{{ route('site.module', $module['slug']) }}">
                            {{ __('site.details') }} <i class="fa fa-angle-right"></i>
                        </a>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-public-site .banner-area.sx-modules-page-banner.about-banner,
.sx-modules-page-banner.about-banner {
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
.sx-modules-page-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(92,0,0,.62) 0%, rgba(148,0,0,.46) 55%, rgba(26,10,10,.38) 100%);
    z-index: 1;
}
.sx-modules-page-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-modules-page-banner .about-content h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-modules-page-banner .link-nav a { color: #fff !important; }

.sx-modules-page { padding-bottom: 20px; }
.sx-modules-page > .container.sx-modules-block {
    padding-top: 48px !important;
    padding-bottom: 0 !important;
}
.sx-modules-page > .container.sx-modules-block.sx-modules-block--last {
    padding-bottom: 30px !important;
}
@media (max-width: 991.98px) {
    .sx-modules-page > .container.sx-modules-block.sx-modules-block--last {
        padding-bottom: 70px !important;
    }
    .sx-modules-page-banner .about-content h1 { font-size: 26px; }
}

.sx-modules-intro {
    max-width: 760px;
    margin: 0 auto;
    background: #fff8f8;
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    padding: 22px 28px;
    box-shadow: 0 8px 22px rgba(148,0,0,.06);
}
.sx-modules-intro__lead {
    color: #444;
    font-size: 16px;
    line-height: 1.7;
}
.sx-modules-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.sx-modules-heading {
    color: #1a1a1a;
    font-weight: 800;
    font-size: 26px;
    line-height: 1.3;
    margin: 0 0 12px;
}
.sx-modules-text {
    color: #555;
    font-size: 15px;
    line-height: 1.7;
    max-width: 720px;
}
.sx-modules-section-head { margin-bottom: 28px; }

.sx-modules-card {
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 22px 18px;
    height: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
    transition: transform .25s ease, box-shadow .25s ease;
    display: flex;
    flex-direction: column;
}
.sx-modules-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px rgba(148,0,0,.1);
}
.sx-modules-card__icon {
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
.sx-modules-card h3 {
    color: #1a1a1a;
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 8px;
    line-height: 1.35;
}
.sx-modules-card p {
    color: #666;
    font-size: 13.5px;
    line-height: 1.55;
    margin-bottom: 14px;
    flex: 1;
}
.sx-modules-card a {
    color: #940000 !important;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none !important;
    margin-top: auto;
}
</style>
@endpush

@push('schema')
@php
    $modulesUrl = url('/modules');
    $collection = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $modulesUrl . '#webpage',
        'url' => $modulesUrl,
        'name' => 'ShuleXpert Modules',
        'description' => 'Complete list of ShuleXpert school management modules by EmCa Techonologies.',
        'inLanguage' => app()->getLocale() === 'sw' ? 'sw-TZ' : 'en-TZ',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'ShuleXpert',
            'url' => url('/'),
        ],
        'mainEntity' => ['@id' => $modulesUrl . '#itemlist'],
    ];

    $itemList = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        '@id' => $modulesUrl . '#itemlist',
        'name' => 'ShuleXpert Modules',
        'numberOfItems' => count($modules),
        'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
        'itemListElement' => collect($modules)->values()->map(function ($m, $i) {
            return [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $m['title'],
                'url' => url('/modules/' . $m['slug']),
                'description' => $m['short'],
                'item' => [
                    '@type' => 'Service',
                    'name' => $m['title'],
                    'description' => $m['short'],
                    'url' => url('/modules/' . $m['slug']),
                    'provider' => [
                        '@type' => 'Organization',
                        'name' => 'ShuleXpert',
                    ],
                ],
            ];
        })->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($collection, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($itemList, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
