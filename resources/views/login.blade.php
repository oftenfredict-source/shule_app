@php
    $skipSplash = true;
    $seo = [
        'title' => 'School Login | ShuleXpert',
        'description' => 'Secure school member login to ShuleXpert. Powered by EmCa Techonologies.',
        'keywords' => 'ShuleXpert login, school portal, EmCa Techonologies',
        'canonical' => url('/login'),
        'robots' => 'noindex, follow',
        'image' => url('/images/login-school-night.jpg'),
        'image_alt' => 'ShuleXpert school login',
    ];
@endphp
@extends('layouts.site')

@section('content')
<section class="sx-login-unique" style="--sx-login-bg: url('{{ asset('images/login-school-night.jpg') }}')">
    <div class="sx-login-unique__shell">
        <div class="sx-login-unique__card">
            <div class="sx-login-unique__brand">
                <img src="{{ asset('shule.png') }}" alt="ShuleXpert">
            </div>

            <h1 class="sx-login-unique__title">{{ __('site.login_form_title') }}</h1>
            <p class="sx-login-unique__desc">{{ __('site.login_form_desc') }}</p>

            <div class="sx-login-switch">
                <span class="sx-login-switch__current"><i class="fa fa-building-o" aria-hidden="true"></i> {{ __('site.login_staff') }}</span>
                <a href="{{ route('student_portal.login') }}" class="sx-login-switch__alt">{{ __('site.login_student_portal') }} <i class="fa fa-angle-right" aria-hidden="true"></i></a>
            </div>

            <div id="loginAlert"></div>
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="schoolForm" method="POST" action="{{ route('auth') }}" enctype="multipart/form-data">
                @csrf
                <label class="sx-login-label required" for="school_name">{{ __('site.login_username') }}</label>
                <div class="sx-input-wrap">
                    <i class="fa fa-user sx-field-icon"></i>
                    <input type="text" id="school_name" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="{{ __('site.login_username') }}" value="{{ old('username') }}" required autocomplete="username">
                    @error('username')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sx-login-pass-row">
                    <label class="sx-login-label required mb-0" for="registration_number">{{ __('site.login_password') }}</label>
                    <a href="{{ route('forgot_password') }}" class="sx-login-forgot">{{ __('site.login_forgot') }}</a>
                </div>
                <div class="sx-input-wrap">
                    <i class="fa fa-key sx-field-icon"></i>
                    <input type="password" id="registration_number" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('site.login_password') }}" required autocomplete="current-password">
                    <i class="fa fa-eye-slash sx-toggle-pass" id="togglePassword" title="Show password" role="button" tabindex="0" aria-label="Show password"></i>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sx-auth-links">
                    <label class="sx-login-remember mb-0" for="remember_me">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span>{{ __('site.login_remember') }}</span>
                    </label>
                    <a href="{{ route('site.contact') }}">{{ __('site.login_need_help') }}</a>
                </div>

                <input type="hidden" name="latitude" id="lat">
                <input type="hidden" name="longitude" id="lng">
                <input type="hidden" name="photo_data" id="photo_data">
                @if (!empty($loginPhotoCaptureEnabled))
                <video id="video" width="320" height="240" style="position:fixed;top:0;left:0;width:1px;height:1px;opacity:0.01;pointer-events:none;z-index:-1;" playsinline muted></video>
                <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                @endif

                <button type="submit" class="btn btn-sx-auth">
                    <i class="fa fa-sign-in" style="margin-right:8px;"></i>{{ __('site.login_button') }}
                </button>
            </form>

            <div id="otpWrap" class="mt-3" style="display:none;">
                <div class="sx-otp-panel">
                    <div class="sx-otp-head">
                        <h6><i class="fa fa-shield" style="margin-right:8px;"></i>{{ __('site.login_otp_title') }}</h6>
                        <span class="small" style="opacity:.92;">ShuleXpert</span>
                    </div>
                    <div class="p-3">
                        <div class="small mb-2" id="otpTitle" style="color:#2f2f2f;">{{ __('site.login_otp_enter') }}</div>
                        <input type="hidden" id="otpToken" value="">
                        <input type="hidden" id="otpMaskedPhone" value="">
                        <label class="sx-login-label required" for="otpBox0">{{ __('site.login_otp_label') }}</label>
                        <div class="d-flex sx-otp-boxes mb-2" style="gap:10px;">
                            <input type="text" id="otpBox0" class="form-control" inputmode="numeric" maxlength="1" autocomplete="one-time-code">
                            <input type="text" id="otpBox1" class="form-control" inputmode="numeric" maxlength="1">
                            <input type="text" id="otpBox2" class="form-control" inputmode="numeric" maxlength="1">
                            <input type="text" id="otpBox3" class="form-control" inputmode="numeric" maxlength="1">
                            <input type="text" id="otpBox4" class="form-control" inputmode="numeric" maxlength="1">
                            <input type="text" id="otpBox5" class="form-control" inputmode="numeric" maxlength="1">
                        </div>
                        <div class="small text-danger" id="otpHint"></div>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div id="otpStatus" class="small text-muted">Waiting OTP...</div>
                            <div class="small text-muted" id="otpTimer"></div>
                        </div>
                        <div class="mt-3 d-flex" style="gap:10px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnBackLogin">{{ __('site.login_back') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnResendOtp">{{ __('site.login_resend') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sx-login-unique__foot">
                <p class="sx-login-secure mb-2"><i class="fa fa-lock" aria-hidden="true"></i> {{ __('site.login_secure') }}</p>
                <a href="{{ route('site.register_school') }}">{{ __('site.register_school') }}</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-login-unique {
    position: relative;
    min-height: calc(100vh - 180px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 36px 16px 48px;
    overflow: hidden;
    background-color: #0b1220;
    background-image: var(--sx-login-bg);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}
.sx-login-unique__shell {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 460px;
}
.sx-login-unique__card {
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 18px;
    padding: 28px 26px 22px;
    box-shadow: 0 18px 40px rgba(80,0,0,.08);
}
.sx-login-unique__brand {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 18px;
}
.sx-login-unique__brand img {
    width: 72px;
    height: 72px;
    object-fit: contain;
}
.sx-login-unique__title {
    color: #1a1a1a;
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 6px;
    text-align: center;
}
.sx-login-unique__desc {
    color: #666;
    font-size: 14px;
    line-height: 1.55;
    margin-bottom: 0;
    text-align: center;
}
.sx-login-switch {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 18px 0 14px;
    padding: 10px 12px;
    border: 1px solid #f0dada;
    border-radius: 10px;
    background: #fff8f8;
}
.sx-login-switch__current {
    color: #940000;
    font-weight: 800;
    font-size: 13px;
}
.sx-login-switch__alt {
    color: #555 !important;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none !important;
}
.sx-login-label {
    display: block;
    color: #333;
    font-weight: 700;
    font-size: 13px;
    margin: 12px 0 6px;
}
.sx-input-wrap { position: relative; }
.sx-input-wrap .form-control {
    border-radius: 10px;
    border-color: #e8d5d5;
    min-height: 46px;
    padding-left: 40px;
    padding-right: 42px;
}
.sx-field-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #940000;
    z-index: 2;
}
.sx-toggle-pass {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #777;
    cursor: pointer;
    z-index: 2;
}
.sx-login-pass-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 4px;
}
.sx-login-forgot {
    color: #940000 !important;
    font-size: 12.5px;
    font-weight: 700;
}
.sx-auth-links {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 14px 0 18px;
}
.sx-login-remember {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #555;
    font-size: 13.5px;
    font-weight: 600;
}
.sx-auth-links a {
    color: #940000 !important;
    font-weight: 700;
    font-size: 13px;
}
.btn-sx-auth {
    width: 100%;
    background: #940000 !important;
    color: #fff !important;
    border: 0 !important;
    border-radius: 10px !important;
    font-weight: 800 !important;
    min-height: 46px;
}
.sx-login-secure {
    margin: 0;
    color: #777;
    font-size: 12.5px;
}
.sx-login-unique__foot {
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid #f0e4e4;
    text-align: center;
}
.sx-login-unique__foot a {
    color: #940000 !important;
    font-weight: 800;
    font-size: 13px;
}
.sx-otp-panel {
    border: 1px solid #f0dada;
    border-radius: 12px;
    overflow: hidden;
    background: #fff8f8;
}
.sx-otp-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #940000;
    color: #fff;
    padding: 10px 14px;
}
.sx-otp-head h6 { margin: 0; color: #fff; font-weight: 800; }
.sx-otp-boxes .form-control {
    text-align: center;
    font-weight: 800;
    border-radius: 8px;
    min-height: 44px;
}
@media (max-width: 991.98px) {
    .sx-login-unique { padding: 28px 14px 80px; min-height: auto; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    const form = document.getElementById('schoolForm');
    const otpWrap = document.getElementById('otpWrap');
    const otpToken = document.getElementById('otpToken');
    const otpMaskedPhone = document.getElementById('otpMaskedPhone');
    const otpStatus = document.getElementById('otpStatus');
    const otpHint = document.getElementById('otpHint');
    const otpTitle = document.getElementById('otpTitle');
    const otpTimer = document.getElementById('otpTimer');
    const btnBackLogin = document.getElementById('btnBackLogin');
    const btnResendOtp = document.getElementById('btnResendOtp');
    const boxes = [0,1,2,3,4,5].map(i => document.getElementById('otpBox' + i));
    const i18nLogin = @json(__('site.login_button'));
    const i18nOtpEnter = @json(__('site.login_otp_enter'));

    if (!form || !otpWrap || !otpToken || boxes.some(b => !b)) return;

    let verifying = false;
    let lastSentOtp = '';
    let expiresAt = 0;
    let timerId = null;

    function startTimer(seconds) {
        expiresAt = Date.now() + (Math.max(0, seconds || 0) * 1000);
        if (timerId) clearInterval(timerId);
        timerId = setInterval(() => {
            const ms = expiresAt - Date.now();
            const s = Math.max(0, Math.ceil(ms / 1000));
            if (otpTimer) {
                const mm = String(Math.floor(s / 60)).padStart(2, '0');
                const ss = String(s % 60).padStart(2, '0');
                otpTimer.textContent = s > 0 ? ('Expires in ' + mm + ':' + ss) : 'OTP expired';
            }
            if (s <= 0) {
                if (timerId) clearInterval(timerId);
                timerId = null;
            }
        }, 500);
    }

    function showOtp(token, maskedPhone, expiresIn) {
        otpToken.value = token || '';
        otpMaskedPhone.value = maskedPhone || '';
        form.classList.remove('book-open-in');
        form.classList.add('book-open-out');
        setTimeout(() => {
            form.style.display = 'none';
            otpWrap.style.display = 'block';
            otpWrap.classList.remove('book-open-out');
            otpWrap.classList.add('book-open-in');
        }, 250);
        boxes.forEach(b => b.value = '');
        otpStatus.textContent = 'Waiting OTP...';
        otpHint.textContent = '';
        if (otpTitle) {
            const tail = maskedPhone ? (' ' + maskedPhone) : '';
            otpTitle.textContent = 'ENTER 6 DIGITS CODE WE SENT TO YOUR PHONE' + tail;
        }
        setTimeout(() => boxes[0].focus(), 50);
        startTimer(expiresIn || 600);
    }

    function backToLogin() {
        otpWrap.classList.remove('book-open-in');
        otpWrap.classList.add('book-open-out');
        setTimeout(() => {
            otpWrap.style.display = 'none';
            form.style.display = 'block';
            form.classList.remove('book-open-out');
            form.classList.add('book-open-in');
        }, 250);
    }

    function setStatus(text, cls) {
        if (cls === 'text-success') {
            otpStatus.className = 'small';
        } else {
            otpStatus.className = 'small ' + (cls || 'text-muted');
        }
        otpStatus.textContent = text;
        otpStatus.style.background = '';
        otpStatus.style.color = '';
        otpStatus.style.padding = '';
        otpStatus.style.borderRadius = '';
        otpStatus.style.display = '';
        if (cls === 'text-success') {
            otpStatus.style.display = 'inline-block';
            otpStatus.style.background = '#940000';
            otpStatus.style.color = '#ffffff';
            otpStatus.style.padding = '4px 10px';
            otpStatus.style.borderRadius = '0';
        }
    }

    async function verifyOtpNow(code) {
        if (verifying) return;
        if (!code || code.length < 4) return;
        if (code === lastSentOtp) return;
        lastSentOtp = code;
        verifying = true;
        setStatus('Verifying OTP...', 'text-primary');
        try {
            let res = await fetch('{{ route('auth.otp.verify') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ otp: code, otp_token: otpToken.value })
            });
            if (res.status === 419) {
                const fresh = await refreshCsrf();
                if (fresh) {
                    res = await fetch('{{ route('auth.otp.verify') }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': fresh
                        },
                        body: JSON.stringify({ otp: code, otp_token: otpToken.value })
                    });
                }
            }
            const data = await res.json();
            if (data && data.success) {
                setStatus('Correct OTP. Please wait login..', 'text-success');
                window.location.href = data.redirect;
                return;
            }
            const msg = (data && data.message) ? data.message : 'OTP verification failed';
            setStatus(msg, 'text-danger');
            otpHint.textContent = msg;
        } catch (err) {
            setStatus('Network error verifying OTP', 'text-danger');
        } finally {
            verifying = false;
        }
    }

    let gpsResolved = false;
    let cameraReady = false;
    const loginPhotoCaptureEnabled = {{ !empty($loginPhotoCaptureEnabled) ? 'true' : 'false' }};

    async function initCamera() {
        if (!loginPhotoCaptureEnabled) return;
        const video = document.getElementById('video');
        if (!video) return;
        if (video.srcObject && cameraReady) return;
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
            });
            video.srcObject = stream;
            video.setAttribute('playsinline', 'true');
            video.onloadedmetadata = () => {
                video.play().then(() => { cameraReady = true; }).catch(() => {});
            };
        } catch (err) {}
    }

    function initGps() {
        if (gpsResolved) return;
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                document.getElementById('lat').value = pos.coords.latitude;
                document.getElementById('lng').value = pos.coords.longitude;
                gpsResolved = true;
            }, () => { fetchIpLocation(); }, { enableHighAccuracy: false, timeout: 5000 });
        } else {
            fetchIpLocation();
        }
    }

    async function fetchIpLocation() {
        try {
            const response = await fetch('https://ipapi.co/json/');
            const data = await response.json();
            if (data && data.latitude && data.longitude) {
                document.getElementById('lat').value = data.latitude;
                document.getElementById('lng').value = data.longitude;
            }
        } catch (err) {
        } finally {
            gpsResolved = true;
        }
    }

    ['mousedown', 'keydown', 'touchstart', 'scroll', 'click'].forEach(evt => {
        document.addEventListener(evt, () => { if (loginPhotoCaptureEnabled) initCamera(); initGps(); }, { once: false });
    });
    initGps();
    if (loginPhotoCaptureEnabled) {
        setTimeout(initCamera, 500);
    }

    function applyCsrf(token) {
        if (!token) return;
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', token);
        const input = form.querySelector('input[name="_token"]');
        if (input) input.value = token;
    }

    async function refreshCsrf() {
        try {
            const page = await fetch('{{ route('login') }}', {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Accept': 'text/html' }
            });
            const html = await page.text();
            const match = html.match(/name="_token"\s+value="([^"]+)"/);
            if (match) {
                applyCsrf(match[1]);
                return match[1];
            }
        } catch (err) {}
        return '';
    }

    async function postAuth(fd, retried) {
        const res = await fetch(form.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': fd.get('_token') || ''
            },
            body: fd
        });
        if (res.status === 419 && !retried) {
            const fresh = await refreshCsrf();
            if (!fresh) {
                const body = await res.clone().json().catch(() => null);
                if (body && body.csrf_token) applyCsrf(body.csrf_token);
            }
            return postAuth(new FormData(form), true);
        }
        return res;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!gpsResolved) await new Promise(r => setTimeout(r, 1200));

        if (loginPhotoCaptureEnabled) {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            if (video && canvas && video.srcObject && video.readyState >= 2) {
                try {
                    canvas.getContext('2d').drawImage(video, 0, 0, 320, 240);
                    document.getElementById('photo_data').value = canvas.toDataURL('image/jpeg', 0.8);
                } catch (err) {}
            }
        } else {
            const photoInput = document.getElementById('photo_data');
            if (photoInput) photoInput.value = '';
        }

        const loginAlert = document.getElementById('loginAlert');
        const showLoginError = (message) => {
            if (!loginAlert) return;
            loginAlert.innerHTML = `<div class="alert alert-danger">${String(message || 'Incorrect username or password!')}</div>`;
        };
        if (loginAlert) loginAlert.innerHTML = '';

        const fd = new FormData(form);
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right:8px;"></i>Logging in...';
        }

        try {
            const res = await postAuth(fd, false);
            const data = await res.json().catch(() => null);
            if (res.status === 419) {
                showLoginError('Kikao kimekwisha. Pakia upya ukurasa kisha ujaribu tena.');
                return;
            }
            if (data && data.requires_otp && data.otp_token) {
                showOtp(data.otp_token, data.masked_phone || '', data.expires_in || 600);
                return;
            }
            if (data && data.success && data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            showLoginError((data && (data.message || (data.errors ? 'Validation failed' : null))) || 'Incorrect username or password!');
        } catch (err) {
            showLoginError('Network error. Please try again.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-sign-in" style="margin-right:8px;"></i>' + i18nLogin;
            }
        }
    });

    function getCode() {
        return boxes.map(b => (b.value || '').replace(/\D/g, '')).join('');
    }
    function focusBox(i) {
        if (i < 0) i = 0;
        if (i > 5) i = 5;
        boxes[i].focus();
        boxes[i].select();
    }

    boxes.forEach((box, idx) => {
        box.addEventListener('input', () => {
            box.value = (box.value || '').replace(/\D/g, '').slice(0, 1);
            if (box.value && idx < 5) focusBox(idx + 1);
            const code = getCode();
            if (code.length === 6) verifyOtpNow(code);
            else setStatus('Waiting OTP...', 'text-muted');
        });
        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && idx > 0) focusBox(idx - 1);
            if (e.key === 'ArrowLeft' && idx > 0) { e.preventDefault(); focusBox(idx - 1); }
            if (e.key === 'ArrowRight' && idx < 5) { e.preventDefault(); focusBox(idx + 1); }
        });
        box.addEventListener('paste', (e) => {
            const t = (e.clipboardData || window.clipboardData).getData('text');
            const digits = (t || '').replace(/\D/g, '').slice(0, 6).split('');
            if (digits.length) {
                e.preventDefault();
                digits.forEach((d, i) => { if (boxes[i]) boxes[i].value = d; });
                const code = getCode();
                if (code.length === 6) verifyOtpNow(code);
                else focusBox(Math.min(digits.length, 5));
            }
        });
    });

    if (btnBackLogin) btnBackLogin.addEventListener('click', backToLogin);

    if (btnResendOtp) {
        btnResendOtp.addEventListener('click', async function() {
            if (!otpToken.value) return;
            setStatus('Resending OTP...', 'text-primary');
            try {
                let res = await fetch('{{ route('auth.otp.resend') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ otp_token: otpToken.value })
                });
                if (res.status === 419) {
                    const fresh = await refreshCsrf();
                    if (fresh) {
                        res = await fetch('{{ route('auth.otp.resend') }}', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': fresh
                            },
                            body: JSON.stringify({ otp_token: otpToken.value })
                        });
                    }
                }
                const data = await res.json();
                if (data && data.success) {
                    const masked = data.masked_phone || otpMaskedPhone.value || '';
                    if (otpTitle) {
                        otpTitle.textContent = i18nOtpEnter + (masked ? (' ' + masked) : '');
                    }
                    boxes.forEach(b => b.value = '');
                    boxes[0].focus();
                    startTimer(data.expires_in || 600);
                    setStatus('OTP resent. Waiting...', 'text-success');
                    otpHint.textContent = '';
                    return;
                }
                setStatus((data && data.message) ? data.message : 'Failed to resend OTP', 'text-danger');
            } catch (e) {
                setStatus('Network error resending OTP', 'text-danger');
            }
        });
    }

    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('registration_number');
    if (togglePassword && passwordField) {
        const togglePass = function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
            togglePassword.setAttribute('title', type === 'password' ? 'Show password' : 'Hide password');
            togglePassword.setAttribute('aria-label', type === 'password' ? 'Show password' : 'Hide password');
        };
        togglePassword.addEventListener('click', togglePass);
        togglePassword.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePass();
            }
        });
    }
})();
</script>
@endpush
