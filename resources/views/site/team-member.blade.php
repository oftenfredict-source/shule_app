@php
    $skipSplash = true;
    $name = $member['name'];
    $role = $member['role'];
    $imageUrl = url($member['image']);
    $profileUrl = route('site.team.member', $member['slug']);
    $phone = $member['phone'] ?? null;
    $email = $member['email'] ?? null;
    $phoneHref = $phone ? ('tel:' . preg_replace('/\D+/', '', $phone)) : null;
    if ($phoneHref && str_starts_with($phoneHref, 'tel:0')) {
        $phoneHref = 'tel:+255' . substr(preg_replace('/\D+/', '', $phone), 1);
    }
@endphp
@extends('layouts.site')

@section('content')
<section class="sx-member-hero">
    <div class="sx-member-hero__art" aria-hidden="true"></div>
    <div class="sx-member-hero__glow sx-member-hero__glow--a" aria-hidden="true"></div>
    <div class="sx-member-hero__glow sx-member-hero__glow--b" aria-hidden="true"></div>
    <div class="container">
        <nav class="sx-member-crumb" aria-label="Breadcrumb">
            <a href="{{ route('site.home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('site.team') }}">Team</a>
            <span>/</span>
            <span>{{ $name }}</span>
        </nav>

        <div class="sx-member-hero__grid">
            <div class="sx-member-hero__photo-wrap">
                <div class="sx-member-hero__ring" aria-hidden="true"></div>
                <div class="sx-member-hero__photo">
                    <img src="{{ asset($member['image']) }}" alt="{{ $name }} — {{ $role }} at EmCa Techonology" width="480" height="480">
                </div>
            </div>

            <div class="sx-member-hero__copy">
                <p class="sx-member-eyebrow">EmCa Techonology · ShuleXpert</p>
                <h1>{{ $name }}</h1>
                <p class="sx-member-role">{{ $role }}</p>
                <p class="sx-member-tagline">{{ $member['tagline'] }}</p>

                <div class="sx-member-actions">
                    @if($phone && $phoneHref)
                        <a href="{{ $phoneHref }}" class="sx-member-btn sx-member-btn--solid">
                            <i class="fa fa-phone"></i> Call {{ $phone }}
                        </a>
                    @endif
                    @if($email)
                        <a href="mailto:{{ $email }}" class="sx-member-btn sx-member-btn--ghost">
                            <i class="fa fa-envelope"></i> {{ $email }}
                        </a>
                    @endif
                    <a href="{{ route('site.contact') }}" class="sx-member-btn sx-member-btn--ghost">
                        <i class="fa fa-comments"></i> Work with us
                    </a>
                </div>

                @if($phone || $email || !empty($member['location']))
                    <ul class="sx-member-meta">
                        @if($phone)
                            <li><i class="fa fa-phone"></i> <a href="{{ $phoneHref }}">{{ $phone }}</a></li>
                        @endif
                        @if($email)
                            <li><i class="fa fa-envelope"></i> <a href="mailto:{{ $email }}">{{ $email }}</a></li>
                        @endif
                        @if(!empty($member['location']))
                            <li><i class="fa fa-map-marker"></i> {{ $member['location'] }}</li>
                        @endif
                    </ul>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="sx-member-body">
    <div class="container">
        <div class="row g-3">
            <div class="col-lg-7">
                <article class="sx-member-panel">
                    <h2>About {{ $name }}</h2>
                    <p class="sx-member-lead">{{ $member['bio'] }}</p>
                    <div class="sx-member-bio">
                        @foreach(preg_split("/\n\s*\n/", trim($member['more_bio'] ?? '')) as $para)
                            @if(trim($para) !== '')
                                <p>{!! nl2br(e(trim($para))) !!}</p>
                            @endif
                        @endforeach
                    </div>
                </article>

                @if(!empty($member['projects']))
                    <article class="sx-member-panel mt-3">
                        <h2>Key projects</h2>
                        <ul class="sx-member-projects">
                            @foreach($member['projects'] as $project)
                                <li>{{ $project }}</li>
                            @endforeach
                        </ul>
                    </article>
                @endif
            </div>

            <div class="col-lg-5">
                <aside class="sx-member-panel sx-member-panel--cta">
                    <h2>Let’s talk</h2>
                    <p>Need ShuleXpert, a custom school system, or a mobile app? Reach {{ $name }} directly — we build software that schools and organisations rely on.</p>
                    <div class="sx-member-actions sx-member-actions--stack">
                        @if($phone && $phoneHref)
                            <a href="{{ $phoneHref }}" class="sx-member-btn sx-member-btn--solid">
                                <i class="fa fa-phone"></i> {{ $phone }}
                            </a>
                        @endif
                        @if($email)
                            <a href="mailto:{{ $email }}" class="sx-member-btn sx-member-btn--ghost">
                                <i class="fa fa-envelope"></i> Email now
                            </a>
                        @endif
                    </div>
                </aside>

                @if(!empty($member['social']))
                    <aside class="sx-member-panel mt-3">
                        <h2>Connect</h2>
                        <div class="sx-member-social">
                            @foreach($member['social'] as $social)
                                <a href="{{ $social['url'] }}" @if(!str_starts_with($social['url'], 'tel:') && !str_starts_with($social['url'], 'mailto:')) target="_blank" rel="noopener noreferrer" @endif title="{{ $social['label'] }}">
                                    <i class="fa {{ $social['icon'] }}"></i>
                                    <span>{{ $social['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </aside>
                @endif
            </div>
        </div>

        @if(!empty($otherMembers))
            <div class="sx-member-others">
                <h2>More from the EmCa team</h2>
                <div class="row">
                    @foreach($otherMembers as $other)
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('site.team.member', $other['slug']) }}" class="sx-member-other-card">
                                <img src="{{ asset($other['image']) }}" alt="{{ $other['name'] }}">
                                <div>
                                    <strong>{{ $other['name'] }}</strong>
                                    <span>{{ $other['role'] }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-member-hero,
.sx-member-body {
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
}
.sx-member-hero *:not(.fa):not(i),
.sx-member-body *:not(.fa):not(i) {
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}
.sx-member-hero .fa,
.sx-member-body .fa,
.sx-member-hero i.fa,
.sx-member-body i.fa {
    font-family: FontAwesome !important;
    font-style: normal;
    font-weight: normal;
    speak: none;
    display: inline-block;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
.sx-member-hero {
    position: relative;
    overflow: hidden;
    padding: 88px 0 28px;
    color: #fff;
    background:
        linear-gradient(145deg, #1a0610 0%, #3a0a14 35%, #940000 70%, #5c0000 100%);
}
.sx-member-hero__art {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 50% 45% at 15% 20%, rgba(255,201,201,0.22) 0%, transparent 55%),
        radial-gradient(ellipse 45% 40% at 90% 80%, rgba(0,0,0,0.35) 0%, transparent 50%),
        url('{{ asset($member['image']) }}') right center / cover no-repeat;
    opacity: 0.18;
    filter: saturate(1.1);
    pointer-events: none;
}
.sx-member-hero__glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    pointer-events: none;
}
.sx-member-hero__glow--a {
    width: 280px; height: 280px;
    top: -40px; left: -40px;
    background: rgba(255, 150, 150, 0.35);
    animation: sxMemberPulse 6s ease-in-out infinite;
}
.sx-member-hero__glow--b {
    width: 320px; height: 320px;
    bottom: -80px; right: 10%;
    background: rgba(148, 0, 0, 0.45);
    animation: sxMemberPulse 7s ease-in-out infinite reverse;
}
@keyframes sxMemberPulse {
    0%, 100% { transform: scale(1); opacity: 0.55; }
    50% { transform: scale(1.15); opacity: 0.9; }
}
.sx-member-hero .container { position: relative; z-index: 2; }
.sx-member-crumb {
    display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
    margin-bottom: 16px; font-size: 13px; color: rgba(255,255,255,0.75);
}
.sx-member-crumb a { color: #ffc9c9; }
.sx-member-crumb a:hover { color: #fff; }
.sx-member-hero__grid {
    display: grid;
    grid-template-columns: minmax(200px, 280px) 1fr;
    gap: 28px;
    align-items: start;
}
.sx-member-hero__photo-wrap { position: relative; justify-self: center; margin-bottom: 8px; }
.sx-member-hero__ring {
    position: absolute; inset: -10px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, #ffc9c9, #940000, #fff, #c43a3a, #ffc9c9);
    animation: sxTeamRingSpin 10s linear infinite;
    opacity: 0.95;
}
.sx-member-hero__photo {
    position: relative;
    width: min(240px, 70vw);
    height: min(240px, 70vw);
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid rgba(255,255,255,0.85);
    box-shadow: 0 16px 40px rgba(0,0,0,0.45);
    background: #2a0a12;
}
.sx-member-hero__photo img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}
.sx-member-hero__badge {
    position: absolute; left: 50%; bottom: -6px; transform: translateX(-50%);
    white-space: nowrap;
    background: linear-gradient(135deg, #fff 0%, #ffc9c9 100%);
    color: #940000;
    font-size: 12px; font-weight: 800;
    padding: 8px 14px;
    border-radius: 999px;
    box-shadow: 0 10px 24px rgba(0,0,0,0.25);
}
.sx-member-eyebrow {
    text-transform: uppercase; letter-spacing: 0.18em;
    font-size: 11px; font-weight: 700; color: #ffc9c9; margin-bottom: 6px;
}
.sx-member-hero__copy h1 {
    font-size: clamp(28px, 4.5vw, 44px);
    font-weight: 800; line-height: 1.1; margin: 0 0 6px;
    background: linear-gradient(120deg, #fff 0%, #ffc9c9 50%, #fff 100%);
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.sx-member-role { font-size: 17px; font-weight: 700; color: #ffd6d6; margin-bottom: 8px; }
.sx-member-tagline { font-size: 15px; color: rgba(255,255,255,0.88); max-width: 540px; margin-bottom: 14px; }
.sx-member-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
.sx-member-actions--stack { flex-direction: column; }
.sx-member-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 14px; font-weight: 700; font-size: 13px;
    border: 2px solid transparent; text-decoration: none !important;
    transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
}
.sx-member-btn:hover { transform: translateY(-2px); text-decoration: none !important; }
.sx-member-btn--solid {
    background: #fff; color: #940000 !important; border-color: #fff;
}
.sx-member-btn--solid:hover { background: #ffc9c9; color: #5c0000 !important; }
.sx-member-btn--ghost {
    background: transparent; color: #fff !important; border-color: rgba(255,255,255,0.65);
}
.sx-member-btn--ghost:hover { background: rgba(255,255,255,0.12); color: #fff !important; }
.sx-member-meta {
    list-style: none; padding: 0; margin: 0;
    display: flex; flex-direction: column; gap: 8px;
    color: rgba(255,255,255,0.9); font-size: 14px;
}
.sx-member-meta a { color: #ffc9c9; }
.sx-member-meta a:hover { color: #fff; }
.sx-member-meta i { width: 18px; color: #ffc9c9; }

.sx-member-body {
    position: relative;
    padding: 24px 0 40px;
    background:
        radial-gradient(ellipse at 0% 0%, rgba(148,0,0,0.12) 0%, transparent 42%),
        radial-gradient(ellipse at 100% 20%, rgba(196,58,58,0.1) 0%, transparent 40%),
        linear-gradient(180deg, #FFF5F5 0%, #ffffff 45%, #fdf2f2 100%);
}
.sx-member-panel {
    background: linear-gradient(160deg, #ffffff 0%, #fff8f8 100%);
    border: 1px solid #f0dada;
    padding: 18px 20px;
    box-shadow: 0 12px 28px rgba(148,0,0,0.05);
}
.sx-member-panel--side {
    background: linear-gradient(160deg, #fff 0%, #fdeeee 100%);
}
.sx-member-panel--cta {
    background: linear-gradient(145deg, #940000 0%, #5c0000 55%, #2a0a12 100%);
    color: #fff; border: none;
}
.sx-member-panel--cta h2 { color: #fff; }
.sx-member-panel--cta p { color: rgba(255,255,255,0.9); margin-bottom: 12px; }
.sx-member-panel h2 {
    color: #940000; font-size: 20px; font-weight: 800; margin-bottom: 10px;
}
.sx-member-lead { font-size: 15px; color: #444; font-weight: 600; margin-bottom: 10px; }
.sx-member-bio { color: #555; line-height: 1.55; }
.sx-member-bio p { margin: 0 0 10px; }
.sx-member-bio p:last-child { margin-bottom: 0; }
.sx-member-projects { list-style: none; padding: 0; margin: 0; }
.sx-member-projects li {
    position: relative; padding: 8px 8px 8px 24px;
    border-bottom: 1px dashed #f0dada; color: #444;
}
.sx-member-projects li:last-child { border-bottom: none; }
.sx-member-projects li::before {
    content: ""; position: absolute; left: 0; top: 14px;
    width: 8px; height: 8px; border-radius: 50%;
    background: linear-gradient(135deg, #ed9999, #940000);
}
.sx-member-skills {
    list-style: none; padding: 0; margin: 0;
    display: flex; flex-wrap: wrap; gap: 8px;
}
.sx-member-skills li {
    background: linear-gradient(135deg, #fff 0%, #fde8e8 100%);
    border: 1px solid #f0dada; color: #940000;
    font-weight: 700; font-size: 13px; padding: 8px 12px;
}
.sx-member-social { display: flex; flex-direction: column; gap: 10px; }
.sx-member-social a {
    display: flex; align-items: center; gap: 10px;
    color: #940000 !important; font-weight: 700;
    padding: 10px 12px; border: 1px solid #f0dada;
    background: #fff; text-decoration: none !important;
}
.sx-member-social a:hover { background: #940000; color: #fff !important; }
.sx-member-social a:hover i { color: #fff !important; }
.sx-member-others { margin-top: 28px; }
.sx-member-others h2 { color: #940000; font-weight: 800; margin-bottom: 12px; font-size: 20px; }
.sx-member-other-card {
    display: flex; gap: 12px; align-items: center;
    padding: 12px; background: #fff; border: 1px solid #f0dada;
    text-decoration: none !important; color: inherit;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.sx-member-other-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(148,0,0,0.1);
}
.sx-member-other-card img {
    width: 52px; height: 52px; border-radius: 50%; object-fit: cover;
    border: 2px solid #940000;
}
.sx-member-other-card strong { display: block; color: #333; }
.sx-member-other-card span { color: #777; font-size: 13px; }

@media (max-width: 991px) {
    .sx-member-hero { padding-top: 88px; padding-bottom: 24px; }
    .sx-member-hero__grid { grid-template-columns: 1fr; text-align: center; gap: 18px; }
    .sx-member-tagline { margin-left: auto; margin-right: auto; }
    .sx-member-actions, .sx-member-meta { justify-content: center; align-items: center; }
    .sx-member-meta { text-align: left; }
}
</style>
@endpush

@push('schema')
@php
    $personSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        '@id' => $profileUrl . '#person',
        'name' => $name,
        'jobTitle' => $role,
        'description' => $member['more_bio'] ?? $member['bio'] ?? $member['tagline'],
        'image' => $imageUrl,
        'url' => $profileUrl,
        'email' => $email ?: null,
        'telephone' => $phone ? ('+255' . ltrim(preg_replace('/\D+/', '', $phone), '0')) : null,
        'address' => !empty($member['location']) ? [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Moshi',
            'addressRegion' => 'Kilimanjaro',
            'addressCountry' => 'TZ',
        ] : null,
        'worksFor' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => url('/'),
            'email' => 'emca@emca.tech',
        ],
    ]);

    $webpageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ProfilePage',
        '@id' => $profileUrl . '#webpage',
        'url' => $profileUrl,
        'name' => $name . ' — ' . $role,
        'description' => $seo['description'] ?? ($member['bio'] ?? ''),
        'inLanguage' => 'en-TZ',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'ShuleXpert',
            'url' => url('/'),
        ],
        'mainEntity' => ['@id' => $profileUrl . '#person'],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => $imageUrl,
        ],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Team', 'item' => route('site.team')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $profileUrl],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($personSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($webpageSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
