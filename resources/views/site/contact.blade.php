@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-contact-page-banner" id="home" style="background-image:url('{{ asset('images/contact-hero.jpg') }}')">
    <div class="sx-contact-page-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ __('contact.title') }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('site.contact') }}">{{ __('contact.title') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-contact-page-wrap">
    <div class="container sx-contact-block">
        <div class="sx-contact-intro-lead text-center">
            <p class="mb-0">{{ __('contact.lead') }}</p>
        </div>
    </div>

    <div class="container sx-contact-block">
        <div class="row">
            <div class="col-md-4 mb-30">
                <a href="https://maps.google.com/?q=Moshi+Kilimanjaro+Tanzania" class="sx-contact-quick-card" target="_blank" rel="noopener">
                    <span class="sx-contact-quick-card__icon"><i class="fa fa-map-marker"></i></span>
                    <strong>Moshi, Kilimanjaro</strong>
                    <span>{{ __('contact.office_label') }}</span>
                </a>
            </div>
            <div class="col-md-4 mb-30">
                <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-contact-quick-card">
                    <span class="sx-contact-quick-card__icon"><i class="fa fa-phone"></i></span>
                    <strong>{{ config('shulexpert.phone_display') }}</strong>
                    <span>{{ __('contact.hours') }}</span>
                </a>
            </div>
            <div class="col-md-4 mb-30">
                <a href="mailto:emca@emcatech.co.tz" class="sx-contact-quick-card">
                    <span class="sx-contact-quick-card__icon"><i class="fa fa-envelope"></i></span>
                    <strong>emca@emcatech.co.tz</strong>
                    <span>{{ __('contact.email_label') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="container sx-contact-block sx-contact-block--last">
        @if(session('success'))
            <div class="sx-contact-alert">{{ session('success') }}</div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="sx-contact-form-card">
                    <div class="sx-contact-form-card__intro">
                        <p class="sx-contact-kicker">{{ __('contact.form_kicker') }}</p>
                        <h2 class="sx-contact-heading">{{ __('contact.form_title') }}</h2>
                        <p class="sx-contact-text">{{ __('contact.form_desc') }}</p>
                    </div>

                    <form class="form-area sx-contact-form" id="contactForm" action="{{ route('site.contact.submit') }}" method="POST">
                        @csrf
                        <div class="sx-hp" aria-hidden="true">
                            <label for="contact_website">Website</label>
                            <input type="text" name="website" id="contact_website" value="" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="row">
                            <div class="col-lg-6 form-group">
                                <label class="sx-contact-label" for="contact_name">{{ __('contact.full_name') }}</label>
                                <input id="contact_name" name="name" placeholder="{{ __('contact.name_ph') }}" value="{{ old('name') }}"
                                       class="common-input mb-20 form-control" required type="text">
                                @error('name')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror

                                <label class="sx-contact-label" for="contact_email">{{ __('contact.email') }}</label>
                                <input id="contact_email" name="email" placeholder="{{ __('contact.email_ph') }}" value="{{ old('email') }}"
                                       pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$"
                                       class="common-input mb-20 form-control" required type="email">
                                @error('email')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror

                                <label class="sx-contact-label" for="contact_phone">{{ __('contact.phone') }}</label>
                                <input name="phone" id="contact_phone" placeholder="2557XXXXXXXX"
                                       value="{{ old('phone', '255') }}"
                                       maxlength="12" inputmode="numeric" autocomplete="tel"
                                       class="common-input mb-10 form-control" required type="tel">
                                @error('phone')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror
                                <small class="d-block mb-15 sx-contact-hint">{{ __('contact.phone_hint') }}</small>

                                <label class="sx-contact-label" for="contact_subject">{{ __('contact.subject') }}</label>
                                <input id="contact_subject" name="subject" placeholder="{{ __('contact.subject_ph') }}" value="{{ old('subject') }}"
                                       class="common-input mb-20 form-control" required type="text">
                                @error('subject')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-lg-6 form-group">
                                <label class="sx-contact-label" for="contact_message">{{ __('contact.message') }}</label>
                                <textarea id="contact_message" class="common-textarea form-control sx-contact-textarea" name="message" placeholder="{{ __('contact.message_ph') }}"
                                          required>{{ old('message') }}</textarea>
                                @error('message')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror

                                <div class="sx-contact-captcha" id="contactCaptcha">
                                    <div class="sx-contact-captcha__head">
                                        <span class="sx-contact-captcha__badge" aria-hidden="true">
                                            <i class="fa fa-shield-alt"></i>
                                        </span>
                                        <div>
                                            <strong>{{ __('contact.captcha_title') }}</strong>
                                            <p>{{ __('contact.captcha_help') }}</p>
                                        </div>
                                    </div>
                                    <div class="sx-contact-captcha__body">
                                        <div class="sx-contact-captcha__image-wrap">
                                            <img id="contactCaptchaImg"
                                                 src="{{ route('site.contact.captcha') }}?t={{ time() }}"
                                                 alt="{{ __('contact.captcha_title') }}"
                                                 width="220"
                                                 height="72"
                                                 loading="eager">
                                            <button type="button"
                                                    class="sx-contact-captcha__refresh"
                                                    id="btnRefreshCaptcha"
                                                    title="{{ __('contact.captcha_refresh') }}"
                                                    aria-label="{{ __('contact.captcha_refresh') }}">
                                                <i class="fa fa-refresh" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        <input id="contact_captcha"
                                               name="captcha"
                                               type="text"
                                               maxlength="12"
                                               autocomplete="off"
                                               autocapitalize="characters"
                                               spellcheck="false"
                                               placeholder="{{ __('contact.captcha_ph') }}"
                                               class="common-input form-control"
                                               required>
                                    </div>
                                    @error('captcha')<small class="text-danger d-block mt-10">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="sx-contact-submit-row">
                                    <button class="btn btn-theme-colored1" id="btnSubmitContact" type="submit">
                                        {{ __('contact.send_message') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sx-contact-side-wrap">
                    <div class="sx-contact-side-cta">
                        <p class="sx-contact-side-cta__eyebrow">{{ __('contact.aside_eyebrow') }}</p>
                        <h3>{{ __('contact.aside_title') }}</h3>
                        <p>{{ __('contact.aside_text') }}</p>
                        <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-contact-side-cta__link">
                            <i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}
                        </a>
                        <a href="mailto:emca@emcatech.co.tz" class="sx-contact-side-cta__link">
                            <i class="fa fa-envelope"></i> emca@emcatech.co.tz
                        </a>
                        <p class="sx-contact-side-cta__hours">
                            <i class="fa fa-clock-o"></i> {{ __('contact.hours') }}
                        </p>
                    </div>
                    <div class="sx-contact-side-links">
                        <h4>{{ __('contact.quick_links') }}</h4>
                        <ul>
                            <li><a href="{{ route('pricing') }}"><i class="fa fa-angle-right"></i> {{ __('about.view_pricing') }}</a></li>
                            <li><a href="{{ route('site.services') }}"><i class="fa fa-angle-right"></i> {{ __('site.our_services') }}</a></li>
                            <li><a href="{{ route('site.faq') }}"><i class="fa fa-angle-right"></i> {{ __('site.faq') }}</a></li>
                            <li><a href="{{ route('site.register_school') }}"><i class="fa fa-angle-right"></i> {{ __('site.register_school_short') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-public-site .banner-area.sx-contact-page-banner.about-banner,
.sx-contact-page-banner.about-banner {
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
.sx-contact-page-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(92,0,0,.58) 0%, rgba(148,0,0,.42) 55%, rgba(26,10,10,.35) 100%);
    z-index: 1;
}
.sx-contact-page-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-contact-page-banner .about-content h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-contact-page-banner .link-nav a { color: #fff !important; }

.sx-contact-page-wrap > .container.sx-contact-block {
    padding-top: 48px !important;
    padding-bottom: 0 !important;
}
.sx-contact-page-wrap > .container.sx-contact-block.sx-contact-block--last {
    padding-bottom: 90px !important;
}
.sx-contact-page-wrap { padding-bottom: 20px; }

.sx-contact-intro-lead {
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
.sx-contact-alert {
    background: #e8f7ee;
    border: 1px solid #b7e4c7;
    color: #1a7f37;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 18px;
    font-weight: 600;
}

.sx-contact-quick-card {
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
.sx-contact-quick-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(148,0,0,.1);
}
.sx-contact-quick-card__icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #940000 !important;
    color: #fff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    flex-shrink: 0;
    box-shadow: 0 6px 14px rgba(148, 0, 0, 0.22);
}
.sx-contact-quick-card__icon i,
.sx-contact-quick-card__icon .fa {
    color: #fff !important;
    font-size: 18px;
    line-height: 1;
    font-family: "Font Awesome 5 Free", FontAwesome !important;
    font-weight: 900 !important;
    font-style: normal !important;
    -webkit-font-smoothing: antialiased;
    display: inline-block !important;
}
.sx-contact-quick-card strong { color: #1a1a1a; font-size: 15px; font-weight: 800; }
.sx-contact-quick-card > span:not(.sx-contact-quick-card__icon) {
    color: #666;
    font-size: 13px;
    line-height: 1.45;
}

.sx-contact-form-card {
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    background: #fff;
    padding: 26px 22px;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
}
.sx-contact-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.sx-contact-heading {
    color: #1a1a1a;
    font-weight: 800;
    font-size: 24px;
    margin: 0 0 10px;
}
.sx-contact-text {
    color: #555;
    font-size: 14.5px;
    line-height: 1.65;
    margin-bottom: 18px;
}
.sx-contact-label {
    display: block;
    color: #333;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
}
.sx-contact-form .form-control {
    border: 1px solid #eddada;
    border-radius: 8px;
    min-height: 44px;
}
.sx-contact-textarea {
    min-height: 220px !important;
    height: 100%;
}
.sx-hp {
    position: absolute !important;
    left: -10000px !important;
    width: 1px !important;
    height: 1px !important;
    overflow: hidden !important;
}
.sx-contact-hint { color: #888; font-size: 12px; }
.sx-contact-captcha {
    margin-top: 18px;
    border: 1px solid #f0dada;
    border-radius: 14px;
    background: linear-gradient(180deg, #fffafa 0%, #fff 100%);
    padding: 14px;
    box-shadow: 0 8px 18px rgba(148, 0, 0, 0.05);
}
.sx-contact-captcha__head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}
.sx-contact-captcha__badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #940000;
    color: #fff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 6px 14px rgba(148, 0, 0, 0.22);
}
.sx-contact-captcha__badge i,
.sx-contact-captcha__badge .fa,
.sx-contact-captcha__refresh i,
.sx-contact-captcha__refresh .fa {
    color: #fff !important;
    font-family: "Font Awesome 5 Free", FontAwesome !important;
    font-weight: 900 !important;
}
.sx-contact-captcha__head strong {
    display: block;
    color: #1a1a1a;
    font-size: 14px;
    font-weight: 800;
    margin-bottom: 2px;
}
.sx-contact-captcha__head p {
    margin: 0;
    color: #777;
    font-size: 12.5px;
    line-height: 1.4;
}
.sx-contact-captcha__body {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 12px;
    align-items: center;
}
.sx-contact-captcha__image-wrap {
    position: relative;
    display: inline-flex;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #edd6d6;
    background: #fff;
}
.sx-contact-captcha__image-wrap img {
    display: block;
    width: 220px;
    max-width: 100%;
    height: 72px;
    object-fit: cover;
}
.sx-contact-captcha__refresh {
    position: absolute;
    right: 6px;
    bottom: 6px;
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    background: rgba(148, 0, 0, 0.92);
    color: #fff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
}
.sx-contact-captcha__refresh:hover { background: #7a0000; }
.sx-contact-captcha input.form-control {
    min-height: 46px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    font-weight: 700;
}
.sx-contact-submit-row {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 8px;
    padding-top: 14px;
    border-top: 1px solid #f0e4e4;
}

.sx-contact-side-wrap {
    position: sticky;
    top: 100px;
    display: grid;
    gap: 14px;
}
.sx-contact-side-cta {
    border-radius: 14px;
    background: linear-gradient(165deg, #940000 0%, #7a0000 48%, #5c0000 100%);
    padding: 22px 18px;
    color: #fff;
    box-shadow: 0 14px 30px rgba(148,0,0,.22);
}
.sx-contact-side-cta__eyebrow {
    color: #ffd6d6;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.sx-contact-side-cta h3 {
    color: #fff;
    font-weight: 800;
    font-size: 18px;
    margin-bottom: 8px;
}
.sx-contact-side-cta > p {
    color: rgba(255,255,255,.88);
    font-size: 13px;
    line-height: 1.55;
    margin-bottom: 14px;
}
.sx-contact-side-cta__link {
    display: flex !important;
    align-items: center;
    gap: 8px;
    color: #ffd6d6 !important;
    text-decoration: none !important;
    font-size: 13.5px;
    font-weight: 600;
    margin-bottom: 8px;
}
.sx-contact-side-cta__link:hover { color: #fff !important; }
.sx-contact-side-cta__hours {
    margin: 12px 0 0;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,.18);
    color: #ffd6d6;
    font-size: 12.5px;
}
.sx-contact-side-links {
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 18px 16px;
}
.sx-contact-side-links h4 {
    color: #1a1a1a;
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sx-contact-side-links ul { list-style: none; margin: 0; padding: 0; }
.sx-contact-side-links li { margin-bottom: 8px; }
.sx-contact-side-links a {
    color: #555 !important;
    text-decoration: none !important;
    font-size: 13.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sx-contact-side-links a:hover { color: #940000 !important; }
.sx-contact-side-links i { color: #940000; }

@media (max-width: 991.98px) {
    .sx-contact-page-banner.about-banner {
        min-height: 240px !important;
        height: 240px !important;
        background-position: center 30% !important;
    }
    .sx-contact-page-banner .about-content h1 { font-size: 24px; }
    .sx-contact-side-wrap { position: static; }
    .sx-contact-page-wrap > .container.sx-contact-block.sx-contact-block--last {
        padding-bottom: 70px !important;
    }
}
@media (max-width: 575.98px) {
    .sx-contact-page-banner.about-banner {
        min-height: 200px !important;
        height: 200px !important;
    }
    .sx-contact-page-banner .about-content h1 { font-size: 22px; }
    .sx-contact-intro-lead { padding: 18px 16px; }
    .sx-contact-captcha__body { grid-template-columns: 1fr; }
    .sx-contact-captcha__image-wrap { width: 100%; }
    .sx-contact-captcha__image-wrap img { width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    var form = document.getElementById('contactForm');
    if (!form) return;

    var phoneInput = document.getElementById('contact_phone');
    var captchaImg = document.getElementById('contactCaptchaImg');
    var captchaInput = document.getElementById('contact_captcha');
    var btnRefresh = document.getElementById('btnRefreshCaptcha');
    var captchaUrl = @json(route('site.contact.captcha'));

    function digitsOnly(v) {
        return String(v || '').replace(/\D/g, '');
    }

    function normalizePhone(v) {
        var d = digitsOnly(v);
        if (d.startsWith('0') && d.length === 10) {
            d = '255' + d.slice(1);
        }
        if (d.startsWith('255')) {
            return d.slice(0, 12);
        }
        d = '255' + d;
        return d.slice(0, 12);
    }

    function refreshCaptcha() {
        if (!captchaImg) return;
        captchaImg.src = captchaUrl + '?t=' + Date.now();
        if (captchaInput) {
            captchaInput.value = '';
            captchaInput.focus();
        }
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            var d = digitsOnly(phoneInput.value);
            if (!d.startsWith('255')) {
                d = '255' + d.replace(/^255/, '').replace(/^0+/, '');
            }
            phoneInput.value = d.slice(0, 12);
        });
        phoneInput.addEventListener('blur', function () {
            phoneInput.value = normalizePhone(phoneInput.value);
        });
        if (!digitsOnly(phoneInput.value).startsWith('255')) {
            phoneInput.value = '255';
        }
    }

    if (btnRefresh) {
        btnRefresh.addEventListener('click', refreshCaptcha);
    }

    form.addEventListener('submit', function () {
        if (phoneInput) {
            phoneInput.value = normalizePhone(phoneInput.value);
        }
    });

    @if ($errors->has('captcha'))
        refreshCaptcha();
    @endif
})();
</script>
@endpush
