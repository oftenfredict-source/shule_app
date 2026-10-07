@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-team-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="sx-team-banner__glow sx-team-banner__glow--a" aria-hidden="true"></div>
    <div class="sx-team-banner__glow sx-team-banner__glow--b" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12 text-center">
                <h1 class="text-white">{{ __('site.our_team') }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.team') }}">{{ __('site.team') }}</a>
                </p>
                <p class="sx-team-lead text-white">
                    {{ __('site.team_behind_desc') }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-team-page section-gap">
    <div class="container">
        <div class="row sx-team-grid justify-content-center">
            @foreach($teamMembers as $member)
                @include('site.partials.team-card', ['member' => $member, 'variant' => 'page'])
            @endforeach
        </div>
        <div class="row mt-50">
            <div class="col-12 text-center">
                <p class="sx-team-page-note">Powered by <strong>EmCa Techonology</strong>: Moshi, Kilimanjaro</p>
                <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase mt-20">{{ __('site.work_with_us') }}</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-team-banner.about-banner {
    min-height: 340px !important;
    padding-top: 168px !important;
    padding-bottom: 72px !important;
    position: relative;
    overflow: hidden;
    background:
        linear-gradient(128deg, rgba(148,0,0,0.94) 0%, rgba(26,12,22,0.92) 55%, rgba(4,9,30,0.9) 100%),
        url('{{ asset('img/carousel-1.jpg') }}') center/cover no-repeat !important;
}
.sx-team-banner__glow {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
}
.sx-team-banner__glow--a {
    width: 320px; height: 320px;
    top: -80px; right: -60px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
}
.sx-team-banner__glow--b {
    width: 260px; height: 260px;
    bottom: -100px; left: -40px;
    background: radial-gradient(circle, rgba(196,58,58,0.35) 0%, transparent 70%);
}
.sx-team-banner .container { position: relative; z-index: 2; }
.sx-team-banner .about-content h1 {
    font-size: 38px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sx-team-lead {
    max-width: 640px;
    margin: 14px auto 0;
    opacity: .92;
    font-size: 15px;
    line-height: 1.7;
}
@media (max-width: 991.98px) {
    .sx-team-banner.about-banner {
        min-height: 300px !important;
        padding-top: 148px !important;
        padding-bottom: 60px !important;
    }
    .sx-team-banner .about-content h1 { font-size: 30px; }
}

.sx-team-page {
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
}
.sx-team-page *:not(.fa):not(i):not(.lnr) {
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}
.sx-team-page .fa,
.sx-team-page i.fa,
.sx-team-card .fa,
.sx-team-card i.fa,
.sx-team-social .fa,
.sx-team-badge .fa {
    font-family: FontAwesome !important;
    font-style: normal;
    font-weight: normal;
    display: inline-block;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
.sx-team-page .lnr {
    font-family: Linearicons-Free !important;
}
</style>
@endpush

@push('scripts')
@php
    $peopleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'ShuleXpert Team | EmCa Techonology',
        'description' => 'The team behind ShuleXpert school management platform.',
        'itemListElement' => collect($teamMembers)->values()->map(function ($member, $index) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => [
                    '@type' => 'Person',
                    'name' => $member['name'],
                    'jobTitle' => $member['role'],
                    'description' => $member['more_bio'] ?? ($member['bio'] ?? $member['tagline']),
                    'image' => asset($member['image']),
                    'url' => url('/team/' . ($member['slug'] ?? '')),
                    'worksFor' => [
                        '@type' => 'Organization',
                        'name' => 'EmCa Techonology',
                    ],
                    'telephone' => !empty($member['phone'])
                        ? ('+255' . ltrim(preg_replace('/\D+/', '', $member['phone']), '0'))
                        : null,
                    'email' => $member['email'] ?? null,
                ],
            ];
        })->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($peopleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
