@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">Contact</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.contact') }}">Contact</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="contact-page-area section-gap">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" style="border-radius:0;border-left:4px solid #940000;">{{ session('success') }}</div>
        @endif
        <div class="row">
            <div class="col-lg-4 d-flex flex-column address-wrap">
                <div class="sx-contact-box single-contact-address d-flex flex-row">
                    <div class="icon"><i class="fa fa-map-marker"></i></div>
                    <div class="contact-details">
                        <h5>Moshi, Kilimanjaro</h5>
                        <p>Tanzania — EmCa Techonology office</p>
                    </div>
                </div>
                <div class="sx-contact-box single-contact-address d-flex flex-row">
                    <div class="icon"><i class="fa fa-phone"></i></div>
                    <div class="contact-details">
                        <h5><a href="tel:{{ config('shulexpert.phone_e164') }}" style="color:#222;">{{ config('shulexpert.phone_display') }}</a></h5>
                        <p>Mon–Fri 09:00–19:00 · Sat 09:00–12:00</p>
                    </div>
                </div>
                <div class="sx-contact-box single-contact-address d-flex flex-row">
                    <div class="icon"><i class="fa fa-envelope"></i></div>
                    <div class="contact-details">
                        <h5><a href="mailto:emca@emca.tech" style="color:#222;">emca@emca.tech</a></h5>
                        <p>Sales, onboarding &amp; support</p>
                    </div>
                </div>
                <p class="sx-powered mt-20">Powered by EmCa Techonology</p>
            </div>
            <div class="col-lg-8">
                <form class="form-area" id="contactForm" action="{{ route('site.contact.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="otp_token" id="otp_token" value="">
                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <input name="name" placeholder="Enter your name" value="{{ old('name') }}"
                                   onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter your name'"
                                   class="common-input mb-20 form-control" required type="text">
                            @error('name')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror

                            <input name="email" placeholder="Enter email address" value="{{ old('email') }}"
                                   pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$"
                                   onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter email address'"
                                   class="common-input mb-20 form-control" required type="email">
                            @error('email')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror

                            <label class="mb-5" style="font-size:13px;color:#555;">Phone (Tanzania — starts with 255)</label>
                            <div class="d-flex mb-10" style="gap:8px;">
                                <input name="phone" id="contact_phone" placeholder="2557XXXXXXXX"
                                       value="{{ old('phone', '255') }}"
                                       maxlength="12" inputmode="numeric" autocomplete="tel"
                                       class="common-input form-control" required type="tel"
                                       style="flex:1;">
                                <button type="button" id="btnSendOtp" class="primary-btn text-uppercase"
                                        style="white-space:nowrap;padding:0 16px;height:auto;min-height:45px;">
                                    Send OTP
                                </button>
                            </div>
                            @error('phone')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror
                            <small id="phoneHint" class="d-block mb-15" style="color:#666;">Verify your phone with OTP before sending the message.</small>

                            <div id="otpBox" class="mb-20" style="display:none;">
                                <label class="mb-5" style="font-size:13px;color:#555;">Enter OTP sent by SMS</label>
                                <div class="d-flex" style="gap:8px;">
                                    <input type="text" id="contact_otp" maxlength="6" inputmode="numeric"
                                           placeholder="6-digit OTP" class="common-input form-control" style="flex:1;">
                                    <button type="button" id="btnVerifyOtp" class="primary-btn text-uppercase"
                                            style="white-space:nowrap;padding:0 16px;height:auto;min-height:45px;">
                                        Verify
                                    </button>
                                </div>
                                <small id="otpStatus" class="d-block mt-10"></small>
                            </div>

                            <input name="subject" placeholder="Enter subject" value="{{ old('subject') }}"
                                   onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter subject'"
                                   class="common-input mb-20 form-control" required type="text">
                            @error('subject')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-lg-6 form-group">
                            <textarea class="common-textarea form-control" name="message" placeholder="Enter message"
                                      onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter message'"
                                      required>{{ old('message') }}</textarea>
                            @error('message')<small class="text-danger d-block mb-10">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-lg-12">
                            <p id="verifyGateMsg" class="mb-10" style="color:#940000;font-size:13px;">
                                Verify your phone number with OTP first — you cannot send a message without verification.
                            </p>
                            <button class="primary-btn text-uppercase" id="btnSubmitContact" type="submit"
                                    style="float:right;" disabled title="Verify phone OTP first">
                                Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

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
