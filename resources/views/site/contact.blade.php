@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-contact-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="sx-contact-banner__glow sx-contact-banner__glow--a" aria-hidden="true"></div>
    <div class="sx-contact-banner__glow sx-contact-banner__glow--b" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12 text-center">
                <h1 class="text-white">{{ __('contact.title') }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.contact') }}">{{ __('contact.title') }}</a>
                </p>
                <p class="sx-contact-lead text-white">
                    {{ __('contact.lead') }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-contact-highlights">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4 mb-3 mb-lg-0">
                <a href="https://maps.google.com/?q=Moshi+Kilimanjaro+Tanzania" class="sx-contact-highlight" target="_blank" rel="noopener">
                    <span class="sx-contact-highlight__icon"><i class="fa fa-map-marker"></i></span>
                    <span class="sx-contact-highlight__body">
                        <strong>Moshi, Kilimanjaro</strong>
                        <span>{{ __('contact.office_label') }}</span>
                    </span>
                </a>
            </div>
            <div class="col-lg-4 col-md-4 mb-3 mb-lg-0">
                <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-contact-highlight">
                    <span class="sx-contact-highlight__icon"><i class="fa fa-phone"></i></span>
                    <span class="sx-contact-highlight__body">
                        <strong>{{ config('shulexpert.phone_display') }}</strong>
                        <span>{{ __('contact.hours') }}</span>
                    </span>
                </a>
            </div>
            <div class="col-lg-4 col-md-4">
                <a href="mailto:emca@emca.tech" class="sx-contact-highlight">
                    <span class="sx-contact-highlight__icon"><i class="fa fa-envelope"></i></span>
                    <span class="sx-contact-highlight__body">
                        <strong>emca@emca.tech</strong>
                        <span>{{ __('contact.email_label') }}</span>
                    </span>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="sx-contact-page section-gap">
    <div class="container">
        @if(session('success'))
            <div class="sx-contact-alert">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-40 mb-lg-0">
                <div class="sx-contact-form-card">
                    <div class="sx-contact-form-card__intro">
                        <p class="sx-contact-kicker">{{ __('contact.form_kicker') }}</p>
                        <h2>{{ __('contact.form_title') }}</h2>
                        <p>{{ __('contact.form_desc') }}</p>
                    </div>

                    <form class="form-area sx-contact-form" id="contactForm" action="{{ route('site.contact.submit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="otp_token" id="otp_token" value="">
                        <div class="row">
                            <div class="col-lg-6 form-group">
                                <label class="sx-contact-label" for="contact_name">{{ __('contact.full_name') }}</label>
                                <input id="contact_name" name="name" placeholder="{{ __('contact.name_ph') }}" value="{{ old('name') }}"
                                       onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('contact.name_ph') }}'"
                                       class="common-input mb-20 form-control" required type="text">
                                @error('name')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror

                                <label class="sx-contact-label" for="contact_email">{{ __('contact.email') }}</label>
                                <input id="contact_email" name="email" placeholder="{{ __('contact.email_ph') }}" value="{{ old('email') }}"
                                       pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$"
                                       onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('contact.email_ph') }}'"
                                       class="common-input mb-20 form-control" required type="email">
                                @error('email')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror

                                <label class="sx-contact-label" for="contact_phone">{{ __('contact.phone') }}</label>
                                <div class="sx-contact-otp-row mb-10">
                                    <input name="phone" id="contact_phone" placeholder="2557XXXXXXXX"
                                           value="{{ old('phone', '255') }}"
                                           maxlength="12" inputmode="numeric" autocomplete="tel"
                                           class="common-input form-control" required type="tel">
                                    <button type="button" id="btnSendOtp" class="primary-btn text-uppercase sx-contact-otp-btn">
                                        {{ __('contact.send_otp') }}
                                    </button>
                                </div>
                                @error('phone')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror
                                <small id="phoneHint" class="d-block mb-15 sx-contact-hint">{{ __('contact.phone_hint') }}</small>

                                <div id="otpBox" class="sx-contact-otp-box mb-20" style="display:none;">
                                    <label class="sx-contact-label" for="contact_otp">{{ __('contact.otp_label') }}</label>
                                    <div class="sx-contact-otp-row">
                                        <input type="text" id="contact_otp" maxlength="6" inputmode="numeric"
                                               placeholder="{{ __('contact.otp_ph') }}" class="common-input form-control">
                                        <button type="button" id="btnVerifyOtp" class="primary-btn text-uppercase sx-contact-otp-btn">
                                            {{ __('contact.verify') }}
                                        </button>
                                    </div>
                                    <small id="otpStatus" class="d-block mt-10"></small>
                                </div>

                                <label class="sx-contact-label" for="contact_subject">{{ __('contact.subject') }}</label>
                                <input id="contact_subject" name="subject" placeholder="{{ __('contact.subject_ph') }}" value="{{ old('subject') }}"
                                       onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('contact.subject_ph') }}'"
                                       class="common-input mb-20 form-control" required type="text">
                                @error('subject')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-lg-6 form-group">
                                <label class="sx-contact-label" for="contact_message">{{ __('contact.message') }}</label>
                                <textarea id="contact_message" class="common-textarea form-control sx-contact-textarea" name="message" placeholder="{{ __('contact.message_ph') }}"
                                          onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('contact.message_ph') }}'"
                                          required>{{ old('message') }}</textarea>
                                @error('message')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-lg-12">
                                <div class="sx-contact-submit-row">
                                    <p id="verifyGateMsg" class="sx-contact-gate mb-0">
                                        {{ __('contact.gate_msg') }}
                                    </p>
                                    <button class="primary-btn text-uppercase" id="btnSubmitContact" type="submit"
                                            disabled title="Verify phone OTP first">
                                        {{ __('contact.send_message') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="sx-contact-aside">
                    <div class="sx-contact-aside__card sx-contact-aside__card--primary">
                        <p class="sx-contact-aside__eyebrow">{{ __('contact.aside_eyebrow') }}</p>
                        <h3>{{ __('contact.aside_title') }}</h3>
                        <p>{{ __('contact.aside_text') }}</p>
                        <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-contact-aside__link">
                            <i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}
                        </a>
                        <a href="mailto:emca@emca.tech" class="sx-contact-aside__link">
                            <i class="fa fa-envelope"></i> emca@emca.tech
                        </a>
                        <p class="sx-contact-aside__hours">
                            <i class="fa fa-clock-o"></i> {{ __('contact.hours') }}
                        </p>
                    </div>

                    <div class="sx-contact-aside__card">
                        <h4>{{ __('contact.before_title') }}</h4>
                        <ul class="sx-point-list">
                            <li>{{ __('contact.before_1') }}</li>
                            <li>{{ __('contact.before_2') }}</li>
                            <li>{{ __('contact.before_3') }}</li>
                        </ul>
                    </div>

                    <div class="sx-contact-aside__card sx-contact-aside__card--soft">
                        <h4>{{ __('contact.quick_links') }}</h4>
                        <ul class="sx-contact-aside__links">
                            <li><a href="{{ route('pricing') }}"><i class="fa fa-angle-right"></i> {{ __('about.view_pricing') }}</a></li>
                            <li><a href="{{ route('site.services') }}"><i class="fa fa-angle-right"></i> {{ __('site.our_services') }}</a></li>
                            <li><a href="{{ route('site.faq') }}"><i class="fa fa-angle-right"></i> {{ __('site.faq') }}</a></li>
                            <li><a href="{{ route('site.register_school') }}"><i class="fa fa-angle-right"></i> {{ __('site.register_school_short') }}</a></li>
                        </ul>
                    </div>

                    <p class="sx-powered mb-0">Powered by EmCa Techonology</p>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="cta-two-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 cta-left">
                <h1>Prefer to start registration now?</h1>
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
.sx-contact-banner.about-banner {
    min-height: 340px !important;
    padding-top: 168px !important;
    padding-bottom: 72px !important;
    position: relative;
    overflow: hidden;
}
.sx-contact-banner__glow {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
}
.sx-contact-banner__glow--a {
    width: 320px; height: 320px;
    top: -80px; right: -60px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
}
.sx-contact-banner__glow--b {
    width: 260px; height: 260px;
    bottom: -100px; left: -40px;
    background: radial-gradient(circle, rgba(196,58,58,0.35) 0%, transparent 70%);
}
.sx-contact-banner .container { position: relative; z-index: 2; }
.sx-contact-eyebrow {
    display: inline-block;
    color: #ffd6d6;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.sx-contact-banner .about-content h1 {
    font-size: 38px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sx-contact-lead {
    max-width: 620px;
    margin: 14px auto 0;
    opacity: .92;
    font-size: 15px;
    line-height: 1.7;
}

.sx-contact-highlights {
    margin-top: -36px;
    position: relative;
    z-index: 3;
    padding-bottom: 8px;
}
.sx-contact-highlight {
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
.sx-contact-highlight:hover {
    transform: translateY(-3px);
    border-color: #e8b4b4;
    box-shadow: 0 14px 32px rgba(148,0,0,0.12);
}
.sx-contact-highlight__icon {
    width: 46px; height: 46px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(145deg, #940000, #c43a3a);
    color: #fff;
    flex-shrink: 0;
}
.sx-contact-highlight__body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}
.sx-contact-highlight__body strong {
    color: #222;
    font-size: 15px;
    font-weight: 800;
}
.sx-contact-highlight__body span {
    color: #666;
    font-size: 13px;
}

.sx-contact-page {
    padding-top: 40px !important;
    padding-bottom: 48px !important;
}
.sx-contact-alert {
    background: #f0faf2;
    border: 1px solid #c6e6cd;
    border-left: 4px solid #940000;
    color: #1a7f37;
    padding: 14px 16px;
    margin-bottom: 22px;
}
.sx-contact-form-card {
    border: 1px solid #f0dada;
    background: #fff;
    padding: 28px 24px;
}
.sx-contact-form-card__intro { margin-bottom: 22px; }
.sx-contact-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.sx-contact-form-card__intro h2 {
    color: #222;
    font-weight: 800;
    font-size: 26px;
    margin-bottom: 8px;
}
.sx-contact-form-card__intro p {
    color: #666;
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}
.sx-contact-label {
    display: block;
    font-size: 13px;
    color: #555;
    font-weight: 600;
    margin-bottom: 6px;
}
.sx-contact-hint { color: #666; }
.sx-contact-otp-row {
    display: flex;
    gap: 8px;
    align-items: stretch;
}
.sx-contact-otp-row .form-control { flex: 1; }
.sx-contact-otp-btn {
    white-space: nowrap;
    padding: 0 16px !important;
    height: auto !important;
    min-height: 45px;
}
.sx-contact-otp-box {
    background: #FFF5F5;
    border: 1px solid #f0dada;
    padding: 14px;
}
.sx-contact-textarea {
    min-height: 280px !important;
}
.sx-contact-submit-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    border-top: 1px solid #f0dada;
    padding-top: 18px;
    margin-top: 8px;
}
.sx-contact-gate {
    color: #940000;
    font-size: 13px;
    max-width: 420px;
}

.sx-contact-aside { display: flex; flex-direction: column; gap: 16px; }
.sx-contact-aside__card {
    border: 1px solid #f0dada;
    background: #fff;
    padding: 22px 20px;
}
.sx-contact-aside__card--primary {
    background: linear-gradient(160deg, rgba(148,0,0,0.96) 0%, rgba(92,0,0,0.94) 55%, rgba(26,12,22,0.96) 100%);
    border-color: transparent;
    color: #fff;
}
.sx-contact-aside__card--primary h3 {
    color: #fff;
    font-weight: 800;
    font-size: 22px;
    margin: 6px 0 10px;
}
.sx-contact-aside__card--primary p { color: rgba(255,255,255,0.88); font-size: 14px; line-height: 1.65; }
.sx-contact-aside__eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #ffd6d6;
    margin: 0;
}
.sx-contact-aside__link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffd6d6 !important;
    font-size: 13px;
    margin-top: 8px;
    text-decoration: none !important;
}
.sx-contact-aside__link:hover { color: #fff !important; }
.sx-contact-aside__hours {
    margin: 14px 0 0 !important;
    font-size: 12px !important;
    color: rgba(255,255,255,0.8) !important;
}
.sx-contact-aside__card h4 {
    color: #940000;
    font-weight: 800;
    font-size: 16px;
    margin-bottom: 12px;
}
.sx-contact-aside__links {
    list-style: none;
    margin: 0;
    padding: 0;
}
.sx-contact-aside__links li { margin-bottom: 8px; }
.sx-contact-aside__links a {
    color: #333;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sx-contact-aside__links a:hover { color: #940000; }
.sx-contact-aside__links i { color: #940000; }
.sx-contact-aside__card--soft {
    background: linear-gradient(180deg, #FFF5F5 0%, #fff 100%);
}

@media (max-width: 991.98px) {
    .sx-contact-banner.about-banner {
        min-height: 300px !important;
        padding-top: 148px !important;
        padding-bottom: 60px !important;
    }
    .sx-contact-banner .about-content h1 { font-size: 30px; }
    .sx-contact-highlights { margin-top: -28px; }
    .sx-contact-textarea { min-height: 180px !important; }
}
@media (max-width: 575.98px) {
    .sx-contact-banner .about-content h1 { font-size: 26px; }
    .sx-contact-form-card { padding: 20px 16px; }
    .sx-contact-otp-row { flex-direction: column; }
    .sx-contact-submit-row { flex-direction: column; align-items: stretch; }
    .sx-contact-submit-row .primary-btn { width: 100%; }
}
</style>
@endpush

@push('schema')
@php
    $contact = [
        '@context' => 'https://schema.org',
        '@type' => 'ContactPage',
        'name' => 'Contact ShuleXpert',
        'url' => url('/contact'),
        'mainEntity' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'email' => 'emca@emca.tech',
            'telephone' => config('shulexpert.phone_e164'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Moshi',
                'addressRegion' => 'Kilimanjaro',
                'addressCountry' => 'TZ',
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($contact, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@push('scripts')
<script>
(function () {
    var phoneInput = document.getElementById('contact_phone');
    var otpToken = document.getElementById('otp_token');
    var otpBox = document.getElementById('otpBox');
    var otpInput = document.getElementById('contact_otp');
    var otpStatus = document.getElementById('otpStatus');
    var btnSend = document.getElementById('btnSendOtp');
    var btnVerify = document.getElementById('btnVerifyOtp');
    var btnSubmit = document.getElementById('btnSubmitContact');
    var gateMsg = document.getElementById('verifyGateMsg');
    var form = document.getElementById('contactForm');
    var verified = false;
    var verifiedPhone = '';

    function csrf() {
        var el = document.querySelector('meta[name="csrf-token"]') || document.querySelector('input[name="_token"]');
        return el ? (el.getAttribute('content') || el.value) : '';
    }

    function digitsOnly(v) {
        return String(v || '').replace(/\D+/g, '');
    }

    function normalizePhone(v) {
        var d = digitsOnly(v);
        if (d.indexOf('255') === 0) d = d.slice(3);
        if (d.indexOf('0') === 0) d = d.slice(1);
        d = '255' + d;
        return d.slice(0, 12);
    }

    function isValidTzMobile(d) {
        return /^255[67]\d{8}$/.test(d);
    }

    function setVerified(state, phone) {
        verified = !!state;
        verifiedPhone = phone || '';
        btnSubmit.disabled = !verified;
        if (gateMsg) {
            gateMsg.style.display = verified ? 'none' : 'block';
        }
        if (verified) {
            otpStatus.style.color = '#1a7f37';
            otpStatus.textContent = 'Phone verified. You can send your message.';
        }
    }

    function resetVerification() {
        setVerified(false, '');
        otpToken.value = '';
        otpStatus.textContent = '';
        otpStatus.style.color = '#666';
    }

    // Keep 255 prefix; digits only; max 12
    phoneInput.addEventListener('input', function () {
        var d = digitsOnly(phoneInput.value);
        if (!d.startsWith('255')) {
            d = '255' + d.replace(/^255/, '').replace(/^0+/, '');
        }
        phoneInput.value = d.slice(0, 12);
        if (verified && phoneInput.value !== verifiedPhone) {
            resetVerification();
            otpBox.style.display = 'none';
        }
    });

    phoneInput.addEventListener('blur', function () {
        phoneInput.value = normalizePhone(phoneInput.value);
    });

    // Ensure starts with 255 on load
    if (!digitsOnly(phoneInput.value).startsWith('255')) {
        phoneInput.value = '255';
    }

    btnSend.addEventListener('click', function () {
        var phone = normalizePhone(phoneInput.value);
        phoneInput.value = phone;

        if (!isValidTzMobile(phone)) {
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Enter a valid number: 2557XXXXXXXX or 2556XXXXXXXX';
            otpBox.style.display = 'block';
            return;
        }

        resetVerification();
        btnSend.disabled = true;
        btnSend.textContent = 'Sending...';
        otpBox.style.display = 'block';
        otpStatus.style.color = '#666';
        otpStatus.textContent = 'Sending OTP...';

        fetch('{{ route('site.contact.send_otp') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ phone: phone })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, status: r.status, body: j }; }); })
        .then(function (res) {
            if (!res.body || !res.body.success) {
                otpStatus.style.color = '#940000';
                otpStatus.textContent = (res.body && res.body.message) ? res.body.message : 'Failed to send OTP.';
                return;
            }
            otpToken.value = res.body.otp_token || '';
            otpStatus.style.color = '#1a7f37';
            otpStatus.textContent = res.body.message || 'OTP sent.';
            otpInput.focus();
        })
        .catch(function () {
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Network error while sending OTP.';
        })
        .finally(function () {
            btnSend.disabled = false;
            btnSend.textContent = 'Send OTP';
        });
    });

    btnVerify.addEventListener('click', function () {
        var phone = normalizePhone(phoneInput.value);
        var otp = digitsOnly(otpInput.value);
        phoneInput.value = phone;

        if (!otpToken.value) {
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Request OTP first.';
            return;
        }
        if (otp.length < 4) {
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Enter the OTP from SMS.';
            return;
        }

        btnVerify.disabled = true;
        btnVerify.textContent = 'Verifying...';

        fetch('{{ route('site.contact.verify_otp') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                phone: phone,
                otp: otp,
                otp_token: otpToken.value
            })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
            if (!res.body || !res.body.success) {
                setVerified(false, '');
                otpStatus.style.color = '#940000';
                otpStatus.textContent = (res.body && res.body.message) ? res.body.message : 'OTP verification failed.';
                return;
            }
            setVerified(true, res.body.phone || phone);
        })
        .catch(function () {
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Network error while verifying OTP.';
        })
        .finally(function () {
            btnVerify.disabled = false;
            btnVerify.textContent = 'Verify';
        });
    });

    form.addEventListener('submit', function (e) {
        phoneInput.value = normalizePhone(phoneInput.value);
        if (!verified || !otpToken.value || phoneInput.value !== verifiedPhone) {
            e.preventDefault();
            setVerified(false, '');
            otpBox.style.display = 'block';
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Verify your phone number with OTP before sending.';
            return false;
        }
    });
})();
</script>
@endpush
