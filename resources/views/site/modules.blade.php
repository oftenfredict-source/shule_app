@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-modules-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="sx-modules-banner__glow sx-modules-banner__glow--a" aria-hidden="true"></div>
    <div class="sx-modules-banner__glow sx-modules-banner__glow--b" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12 text-center">
                <h1 class="text-white">{{ __('site.modules') }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.modules') }}">{{ __('site.modules') }}</a>
                </p>
                <p class="sx-modules-lead text-white">
                    {{ __('site.modules_banner_lead') }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="popular-course-area section-gap courses-page sx-modules-page">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-70 col-lg-8">
                <div class="title text-center">
                    <h1 class="mb-10">{{ __('site.modules_page_title') }}</h1>
                    <p>{!! __('site.modules_page_desc') !!}</p>
                </div>
            </div>
        </div>
        <div class="row sx-modules-grid">
            @foreach($modules as $module)
                <div class="col-lg-3 col-md-6 sx-module-col">
                    <div class="sx-module-card">
                        <div class="thumb thumb-icon" aria-hidden="true">
                            <i class="fa {{ $module['icon'] }}"></i>
                        </div>
                        <div class="details">
                            <a href="{{ route('site.module', $module['slug']) }}"><h4>{{ $module['title'] }}</h4></a>
                            <p>{{ $module['short'] }}</p>
                            <a href="{{ route('site.module', $module['slug']) }}" class="primary-btn sx-module-btn">{{ __('site.details') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-modules-banner.about-banner {
    min-height: 340px !important;
    padding-top: 168px !important;
    padding-bottom: 72px !important;
    position: relative;
    overflow: hidden;
}
.sx-modules-banner__glow {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
}
.sx-modules-banner__glow--a {
    width: 320px; height: 320px;
    top: -80px; right: -60px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
}
.sx-modules-banner__glow--b {
    width: 260px; height: 260px;
    bottom: -100px; left: -40px;
    background: radial-gradient(circle, rgba(196,58,58,0.35) 0%, transparent 70%);
}
.sx-modules-banner .container { position: relative; z-index: 2; }
.sx-modules-banner .about-content h1 {
    font-size: 38px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sx-modules-lead {
    max-width: 640px;
    margin: 14px auto 0;
    opacity: .92;
    font-size: 15px;
    line-height: 1.7;
}
@media (max-width: 991.98px) {
    .sx-modules-banner.about-banner {
        min-height: 300px !important;
        padding-top: 148px !important;
        padding-bottom: 60px !important;
    }
    .sx-modules-banner .about-content h1 { font-size: 30px; }
}
@media (max-width: 575.98px) {
    .sx-modules-banner .about-content h1 { font-size: 26px; }
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
        'description' => 'Complete list of ShuleXpert school management modules by EmCa Techonology.',
        'inLanguage' => 'en-TZ',
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
