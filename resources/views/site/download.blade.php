@extends('layouts.site')

@php
    $apkPath = public_path('shuleXpert.apk');
    $apkBytes = is_file($apkPath) ? filesize($apkPath) : 0;
    $apkSize = $apkBytes >= 1048576
        ? number_format($apkBytes / 1048576, 0).' MB'
        : ($apkBytes > 0 ? number_format($apkBytes / 1024, 0).' KB' : '—');
@endphp

@section('content')
<section class="banner-area relative about-banner sx-download-banner" id="home"
         style="background-image:url('{{ asset('images/modules-school-hero.jpg') }}')">
    <div class="sx-download-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ __('site.download_app') }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('site.download') }}">{{ __('site.download_app') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-download-page">
    <div class="container">
        <div class="sx-download-intro text-center sx-dl-reveal">
            <p class="sx-download-intro__lead mb-0">{{ __('site.download_banner_lead') }}</p>
        </div>

        <div class="sx-download-hero-card sx-dl-reveal">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7 order-1 order-lg-2">
                    <p class="sx-download-kicker">{{ __('site.download_mobile_kicker') }}</p>
                    <h2 class="sx-download-heading">{{ __('site.download_mobile_title') }}</h2>
                    <p class="sx-download-lead">{{ __('site.download_mobile_lead') }}</p>

                    <div class="sx-download-chips" aria-label="{{ __('site.download_apk_meta', ['size' => $apkSize]) }}">
                        <span class="sx-download-chip"><i class="fas fa-gift" aria-hidden="true"></i>{{ __('site.download_chip_free') }}</span>
                        <span class="sx-download-chip"><i class="fab fa-android" aria-hidden="true"></i>{{ __('site.download_chip_android') }}</span>
                        <span class="sx-download-chip"><i class="fas fa-hdd" aria-hidden="true"></i>{{ $apkSize }}</span>
                    </div>

                    <ul class="sx-download-points">
                        <li><i class="fas fa-check" aria-hidden="true"></i><span>{{ __('site.download_point_1') }}</span></li>
                        <li><i class="fas fa-check" aria-hidden="true"></i><span>{{ __('site.download_point_2') }}</span></li>
                        <li><i class="fas fa-check" aria-hidden="true"></i><span>{{ __('site.download_point_3') }}</span></li>
                    </ul>

                    <div class="sx-download-actions">
                        <a href="{{ asset('shuleXpert.apk') }}" class="sx-download-btn sx-download-btn--primary" download rel="nofollow">
                            <i class="fab fa-android" aria-hidden="true"></i>
                            <span>
                                <strong>{{ __('site.download_apk_btn') }}</strong>
                                <small>{{ __('site.download_apk_meta', ['size' => $apkSize]) }}</small>
                            </span>
                        </a>
                        <a href="{{ route('site.contact') }}" class="sx-download-btn sx-download-btn--ghost">
                            <i class="fas fa-headset" aria-hidden="true"></i>
                            <span>{{ __('site.download_help_btn') }}</span>
                        </a>
                    </div>
                    <p class="sx-download-powered mb-0">{{ __('site.powered_by') }}</p>
                </div>
                <div class="col-lg-5 order-2 order-lg-1">
                    <div class="sx-download-visual">
                        <div class="sx-download-phone" role="img" aria-label="{{ __('site.download_phone_aria') }}">
                            <div class="sx-download-phone__notch" aria-hidden="true"></div>
                            <div class="sx-download-phone__screen" aria-hidden="true">
                                <div class="sx-app-mock">
                                    <div class="sx-app-mock__status">
                                        <span>9:41</span>
                                        <span class="sx-app-mock__status-icons">
                                            <i class="fas fa-signal"></i>
                                            <i class="fas fa-wifi"></i>
                                            <i class="fas fa-battery-full"></i>
                                        </span>
                                    </div>
                                    <div class="sx-app-mock__header">
                                        <div class="sx-app-mock__brand">
                                            <span class="sx-app-mock__logo">SX</span>
                                            <div>
                                                <strong>ShuleXpert</strong>
                                                <small>{{ __('site.download_phone_school') }}</small>
                                            </div>
                                        </div>
                                        <span class="sx-app-mock__bell"><i class="fas fa-bell"></i></span>
                                    </div>
                                    <div class="sx-app-mock__hello">
                                        <p>{{ __('site.download_phone_hello') }}</p>
                                        <strong>{{ __('site.download_phone_ready') }}</strong>
                                    </div>
                                    <div class="sx-app-mock__grid">
                                        <div class="sx-app-mock__tile">
                                            <span class="sx-app-mock__icon" style="--sx-tile:#940000"><i class="fas fa-money-bill-wave"></i></span>
                                            <span>{{ __('site.download_phone_mod_fees') }}</span>
                                        </div>
                                        <div class="sx-app-mock__tile">
                                            <span class="sx-app-mock__icon" style="--sx-tile:#0d6e4f"><i class="fas fa-fingerprint"></i></span>
                                            <span>{{ __('site.download_phone_mod_attendance') }}</span>
                                        </div>
                                        <div class="sx-app-mock__tile">
                                            <span class="sx-app-mock__icon" style="--sx-tile:#1a4f8b"><i class="fas fa-chart-bar"></i></span>
                                            <span>{{ __('site.download_phone_mod_results') }}</span>
                                        </div>
                                        <div class="sx-app-mock__tile">
                                            <span class="sx-app-mock__icon" style="--sx-tile:#9a5b00"><i class="fas fa-calendar-alt"></i></span>
                                            <span>{{ __('site.download_phone_mod_timetable') }}</span>
                                        </div>
                                        <div class="sx-app-mock__tile">
                                            <span class="sx-app-mock__icon" style="--sx-tile:#5b2d8e"><i class="fas fa-user-friends"></i></span>
                                            <span>{{ __('site.download_phone_mod_parents') }}</span>
                                        </div>
                                        <div class="sx-app-mock__tile">
                                            <span class="sx-app-mock__icon" style="--sx-tile:#8a1c3a"><i class="fas fa-bullhorn"></i></span>
                                            <span>{{ __('site.download_phone_mod_alerts') }}</span>
                                        </div>
                                    </div>
                                    <div class="sx-app-mock__card">
                                        <div>
                                            <small>{{ __('site.download_phone_next') }}</small>
                                            <strong>{{ __('site.download_phone_session') }}</strong>
                                        </div>
                                        <span>08:00</span>
                                    </div>
                                    <div class="sx-app-mock__nav">
                                        <span class="is-active"><i class="fas fa-home"></i></span>
                                        <span><i class="fas fa-th-large"></i></span>
                                        <span><i class="fas fa-bell"></i></span>
                                        <span><i class="fas fa-user"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="sx-download-visual__badge">
                            <i class="fab fa-android" aria-hidden="true"></i>
                            Android APK
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 sx-download-audience">
            <div class="col-md-6">
                <div class="sx-download-audience-card sx-dl-reveal" style="--sx-dl-delay:.08s">
                    <span class="sx-download-audience-card__icon" aria-hidden="true"><i class="fas fa-chalkboard-teacher"></i></span>
                    <div>
                        <h3>{{ __('site.download_for_teachers') }}</h3>
                        <p>{{ __('site.download_for_teachers_text') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sx-download-audience-card sx-dl-reveal" style="--sx-dl-delay:.16s">
                    <span class="sx-download-audience-card__icon" aria-hidden="true"><i class="fas fa-user-friends"></i></span>
                    <div>
                        <h3>{{ __('site.download_for_parents') }}</h3>
                        <p>{{ __('site.download_for_parents_text') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="sx-download-install">
            <p class="sx-download-kicker text-center">{{ __('site.download_install_kicker') }}</p>
            <h2 class="sx-download-heading text-center">{{ __('site.download_install_title') }}</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="sx-download-step sx-dl-reveal" style="--sx-dl-delay:.08s">
                        <span class="sx-download-step__num">1</span>
                        <h3>{{ __('site.download_step_1_title') }}</h3>
                        <p>{{ __('site.download_step_1_text') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sx-download-step sx-dl-reveal" style="--sx-dl-delay:.16s">
                        <span class="sx-download-step__num">2</span>
                        <h3>{{ __('site.download_step_2_title') }}</h3>
                        <p>{{ __('site.download_step_2_text') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sx-download-step sx-dl-reveal" style="--sx-dl-delay:.24s">
                        <span class="sx-download-step__num">3</span>
                        <h3>{{ __('site.download_step_3_title') }}</h3>
                        <p>{{ __('site.download_step_3_text') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="sx-download-apply-card sx-dl-reveal" style="--sx-dl-delay:.1s">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <p class="sx-download-kicker">{{ __('site.download_apply_kicker') }}</p>
                    <h2 class="sx-download-heading">{{ __('site.download_apply_title') }}</h2>
                    <p class="sx-download-lead">{{ __('site.download_apply_lead') }}</p>
                    <ul class="sx-download-points mb-0">
                        <li><i class="fas fa-check" aria-hidden="true"></i><span>{{ __('site.download_apply_point_1') }}</span></li>
                        <li><i class="fas fa-check" aria-hidden="true"></i><span>{{ __('site.download_apply_point_2') }}</span></li>
                        <li><i class="fas fa-check" aria-hidden="true"></i><span>{{ __('site.download_apply_point_3') }}</span></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <div class="sx-download-apply-cta">
                        <a href="{{ route('online_application') }}" class="sx-download-btn sx-download-btn--primary sx-download-btn--block">
                            <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                            <span>{{ __('site.download_apply_btn') }}</span>
                        </a>
                        <p class="sx-download-apply-note mb-0">{{ __('site.download_apply_note') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-public-site .banner-area.sx-download-banner.about-banner,
.sx-download-banner.about-banner {
    min-height: 300px !important;
    height: 300px !important;
    padding: 0 !important;
    position: relative;
    overflow: hidden;
    background-color: #5c0000 !important;
    background-size: cover !important;
    background-position: center 30% !important;
    background-repeat: no-repeat !important;
    display: flex;
    align-items: center;
}
.sx-download-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(92,0,0,.58) 0%, rgba(148,0,0,.42) 55%, rgba(26,10,10,.35) 100%);
    z-index: 1;
}
.sx-download-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-download-banner .about-content h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-download-banner .link-nav a { color: #fff !important; }

.sx-download-page {
    padding: 0 0 72px;
    background: linear-gradient(180deg, #fff8f8 0%, #fff 42%, #faf7f7 100%);
}
.sx-download-page > .container {
    padding-top: 40px;
    position: relative;
    z-index: 3;
}
.sx-download-intro {
    max-width: 760px;
    margin: 0 auto 22px;
    background: linear-gradient(160deg, #fff 0%, #fff8f8 100%);
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    padding: 22px 28px;
    box-shadow: 0 8px 22px rgba(148,0,0,.06);
}
.sx-download-intro__lead {
    color: #444;
    font-size: 16px;
    line-height: 1.7;
}
.sx-download-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin: 0 0 8px;
}
.sx-download-heading {
    color: #2a0a0c;
    font-size: 30px;
    font-weight: 800;
    margin: 0 0 12px;
    line-height: 1.2;
}
.sx-download-lead {
    color: #5a4548;
    font-size: 15px;
    line-height: 1.75;
    margin: 0 0 16px;
    max-width: 54ch;
}
.sx-download-hero-card,
.sx-download-apply-card {
    background: #fff;
    border: 1px solid #f0e0e0;
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 18px 40px rgba(80, 0, 10, 0.08);
}
.sx-download-visual {
    position: relative;
    background:
        radial-gradient(circle at 30% 20%, rgba(148,0,0,.12), transparent 45%),
        linear-gradient(160deg, #fff5f5 0%, #f8ecec 100%);
    border-radius: 16px;
    padding: 28px 18px 22px;
    text-align: center;
    min-height: 360px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sx-download-phone {
    width: min(210px, 68%);
    aspect-ratio: 9 / 17;
    border-radius: 28px;
    border: 3px solid #2a0a0c;
    background: #111;
    padding: 10px 8px 12px;
    box-shadow: 0 22px 40px rgba(42, 10, 12, 0.28);
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    animation: sx-dl-float 4.5s ease-in-out infinite;
}
.sx-download-phone__notch {
    width: 42%;
    height: 8px;
    border-radius: 999px;
    background: #333;
    margin: 0 auto 8px;
    flex-shrink: 0;
}
.sx-download-phone__screen {
    flex: 1;
    min-height: 0;
    border-radius: 18px;
    overflow: hidden;
    background: #f7f2f2;
}
.sx-app-mock {
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 6px 8px 0;
    background: linear-gradient(180deg, #fff 0%, #f8f1f1 55%, #f3eaea 100%);
    text-align: left;
    color: #2a0a0c;
}
.sx-app-mock__status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 8px;
    font-weight: 700;
    color: #4a3033;
    margin-bottom: 6px;
}
.sx-app-mock__status-icons {
    display: inline-flex;
    gap: 3px;
    font-size: 7px;
}
.sx-app-mock__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.sx-app-mock__brand {
    display: flex;
    align-items: center;
    gap: 6px;
}
.sx-app-mock__logo {
    width: 22px;
    height: 22px;
    border-radius: 7px;
    background: linear-gradient(145deg, #940000, #5c0000);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    font-weight: 800;
}
.sx-app-mock__brand strong {
    display: block;
    font-size: 10px;
    line-height: 1.1;
}
.sx-app-mock__brand small {
    display: block;
    font-size: 7px;
    color: #8a7074;
    font-weight: 600;
}
.sx-app-mock__bell {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #efd4d4;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #940000;
    font-size: 8px;
}
.sx-app-mock__hello {
    margin-bottom: 8px;
}
.sx-app-mock__hello p {
    margin: 0;
    font-size: 8px;
    color: #8a7074;
    font-weight: 600;
}
.sx-app-mock__hello strong {
    display: block;
    font-size: 11px;
    line-height: 1.2;
}
.sx-app-mock__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    margin-bottom: 8px;
}
.sx-app-mock__tile {
    background: #fff;
    border: 1px solid #f0e0e0;
    border-radius: 10px;
    padding: 7px 4px 6px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(80,0,10,.04);
}
.sx-app-mock__icon {
    width: 22px;
    height: 22px;
    border-radius: 8px;
    background: var(--sx-tile, #940000);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    margin-bottom: 4px;
}
.sx-app-mock__tile > span:last-child {
    display: block;
    font-size: 7px;
    font-weight: 700;
    line-height: 1.15;
    color: #3d2a2d;
}
.sx-app-mock__card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    background: linear-gradient(135deg, #940000, #5c0000);
    color: #fff;
    border-radius: 10px;
    padding: 8px 9px;
    margin-bottom: 8px;
}
.sx-app-mock__card small {
    display: block;
    font-size: 7px;
    opacity: .85;
    font-weight: 600;
}
.sx-app-mock__card strong {
    display: block;
    font-size: 9px;
    line-height: 1.2;
}
.sx-app-mock__card > span {
    font-size: 9px;
    font-weight: 800;
    background: rgba(255,255,255,.16);
    border-radius: 999px;
    padding: 3px 6px;
}
.sx-app-mock__nav {
    margin-top: auto;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    background: #fff;
    border-top: 1px solid #f0e0e0;
    border-radius: 12px 12px 0 0;
    padding: 7px 0 8px;
}
.sx-app-mock__nav span {
    text-align: center;
    color: #b08a90;
    font-size: 10px;
}
.sx-app-mock__nav span.is-active { color: #940000; }
.sx-download-visual__badge {
    position: absolute;
    left: 14px;
    bottom: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(145deg, #940000, #5c0000);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    padding: 8px 12px;
    border-radius: 999px;
    box-shadow: 0 8px 18px rgba(148,0,0,.28);
    z-index: 2;
}
.sx-download-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 16px;
}
.sx-download-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff5f5;
    border: 1px solid #efd4d4;
    color: #5c0000;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 10px;
    border-radius: 999px;
}
.sx-download-chip i { color: #940000; }
.sx-download-points {
    list-style: none;
    margin: 0 0 22px;
    padding: 0;
}
.sx-download-points li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin: 0 0 10px;
    color: #3d2a2d;
    font-size: 14px;
    line-height: 1.45;
}
.sx-download-points i {
    color: #940000;
    margin-top: 3px;
    flex-shrink: 0;
}
.sx-download-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 14px;
}
.sx-download-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    border-radius: 12px;
    text-decoration: none !important;
    font-weight: 700;
    transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
}
.sx-download-btn i { font-size: 20px; }
.sx-download-btn span { display: flex; flex-direction: column; line-height: 1.2; }
.sx-download-btn small { font-weight: 600; opacity: .85; font-size: 11px; margin-top: 2px; }
.sx-download-btn--primary {
    background: #940000;
    color: #fff !important;
    box-shadow: 0 10px 24px rgba(148, 0, 0, 0.28);
}
.sx-download-btn--primary:hover {
    background: #7a0000;
    color: #fff !important;
    transform: translateY(-1px);
}
.sx-download-btn--ghost {
    background: #fff;
    color: #940000 !important;
    border: 1px solid #e8c8c8;
}
.sx-download-btn--ghost:hover {
    background: #fff5f5;
    color: #7a0000 !important;
}
.sx-download-btn--block {
    width: 100%;
    justify-content: center;
}
.sx-download-powered {
    color: #8a7074;
    font-size: 12px;
    font-weight: 600;
}
.sx-download-audience { margin-top: 22px; }
.sx-download-audience-card {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    background: #fff;
    border: 1px solid #f0e0e0;
    border-radius: 14px;
    padding: 18px;
    height: 100%;
    transition: transform .2s ease, box-shadow .2s ease;
}
.sx-download-audience-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(148,0,0,.08);
}
.sx-download-audience-card__icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: #940000;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sx-download-audience-card h3 {
    font-size: 16px;
    font-weight: 800;
    color: #2a0a0c;
    margin: 0 0 6px;
}
.sx-download-audience-card p {
    margin: 0;
    color: #5a4548;
    font-size: 13px;
    line-height: 1.55;
}
.sx-download-install {
    margin-top: 48px;
    margin-bottom: 28px;
}
.sx-download-step {
    background: #fff;
    border: 1px solid #f0e0e0;
    border-radius: 14px;
    padding: 22px 18px;
    height: 100%;
    position: relative;
    transition: transform .2s ease, box-shadow .2s ease;
}
.sx-download-step:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(148,0,0,.08);
}
.sx-download-step__num {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #940000;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
    margin-bottom: 12px;
}
.sx-download-step h3 {
    font-size: 16px;
    font-weight: 800;
    color: #2a0a0c;
    margin: 0 0 8px;
}
.sx-download-step p {
    margin: 0;
    color: #5a4548;
    font-size: 13px;
    line-height: 1.55;
}
.sx-download-apply-cta {
    background: linear-gradient(160deg, #fff5f5 0%, #f8ecec 100%);
    border-radius: 14px;
    padding: 20px;
    text-align: center;
}
.sx-download-apply-note {
    margin-top: 12px;
    color: #8a7074;
    font-size: 12px;
    font-weight: 600;
}

@keyframes sx-dl-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}
@keyframes sx-dl-rise {
    from { opacity: 0; transform: translateY(18px); }
    to { opacity: 1; transform: translateY(0); }
}
.sx-dl-reveal {
    animation: sx-dl-rise .55s ease both;
    animation-delay: var(--sx-dl-delay, 0s);
}
@media (prefers-reduced-motion: reduce) {
    .sx-download-phone,
    .sx-dl-reveal,
    .sx-download-audience-card,
    .sx-download-step {
        animation: none !important;
        transition: none !important;
    }
}
@media (max-width: 991.98px) {
    .sx-download-banner.about-banner {
        min-height: 260px !important;
        height: 260px !important;
    }
    .sx-download-banner .about-content h1 { font-size: 28px; }
    .sx-download-heading { font-size: 26px; }
    .sx-download-page { padding-bottom: 64px; }
    .sx-download-page > .container { padding-top: 28px; }
    .sx-download-intro { padding: 18px 16px; margin-bottom: 18px; }
    .sx-download-intro__lead { font-size: 15px; }
    .sx-download-visual { min-height: 0; margin-top: 8px; }
    .sx-download-hero-card { padding: 22px 18px; }
    .sx-download-actions .sx-download-btn { flex: 1 1 180px; }
}
</style>
@endpush

@push('schema')
@php
    $downloadPage = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => url('/download') . '#webpage',
        'url' => url('/download'),
        'name' => 'Download ShuleXpert App & Online School Application',
        'description' => 'Download the ShuleXpert Android APK or apply to participating schools online through ShuleXpert.',
        'inLanguage' => app()->getLocale() === 'sw' ? 'sw-TZ' : 'en-TZ',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'ShuleXpert',
            'url' => url('/'),
        ],
        'about' => [
            '@type' => 'SoftwareApplication',
            'name' => 'ShuleXpert',
        ],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => asset('images/teacher.png'),
        ],
    ];

    $mobileApp = [
        '@context' => 'https://schema.org',
        '@type' => 'MobileApplication',
        '@id' => url('/download') . '#mobileapp',
        'name' => 'ShuleXpert Mobile App',
        'operatingSystem' => 'Android',
        'applicationCategory' => 'EducationalApplication',
        'applicationSubCategory' => 'School Management',
        'description' => 'Android app for ShuleXpert teachers and parents: session alerts, attendance, results and school notifications.',
        'url' => url('/download'),
        'downloadUrl' => url('/shuleXpert.apk'),
        'installUrl' => url('/download'),
        'fileSize' => $apkSize,
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'TZS',
            'availability' => 'https://schema.org/InStock',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonologies',
            'url' => url('/'),
            'email' => config('shulexpert.email', 'emca@emcatech.co.tz'),
            'telephone' => config('shulexpert.phone_e164'),
        ],
    ];

    $onlineService = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        '@id' => route('online_application') . '#service',
        'name' => 'ShuleXpert Online School Application',
        'serviceType' => 'School admission and online application',
        'description' => 'Online school admission portal for parents to apply to participating ShuleXpert schools in Tanzania.',
        'url' => route('online_application'),
        'areaServed' => ['@type' => 'Country', 'name' => 'Tanzania'],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonologies',
            'url' => url('/'),
        ],
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'TZS',
            'description' => 'Free online application for parents and guardians',
            'url' => route('online_application'),
        ],
    ];

    $breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => __('site.home'), 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => __('site.download_app'), 'item' => url('/download')],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($downloadPage, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($mobileApp, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($onlineService, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
