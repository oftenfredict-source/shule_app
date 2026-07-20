@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-team-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12 text-center">
                <h1 class="text-white">Our Team</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.team') }}">Team</a>
                </p>
                <p class="text-white mt-3" style="max-width:640px;margin-left:auto;margin-right:auto;opacity:.92;">
                    The EmCa Techonology specialists who design, build, and refine ShuleXpert for schools worldwide.
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
                <p class="sx-team-page-note">Powered by <strong>EmCa Techonology</strong> — Moshi, Kilimanjaro</p>
                <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase mt-20">Work With Us</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-team-banner {
    background:
        linear-gradient(128deg, rgba(148,0,0,0.94) 0%, rgba(26,12,22,0.92) 55%, rgba(4,9,30,0.9) 100%),
        url('{{ asset('img/carousel-1.jpg') }}') center/cover no-repeat !important;
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
        'name' => 'ShuleXpert Team — EmCa Techonology',
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
