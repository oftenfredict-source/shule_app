@php
    $skipSplash = true;
    $seo = [
        'title' => 'School Login | ShuleXpert',
        'description' => 'Secure school member login to ShuleXpert. Powered by EmCa Techonology.',
        'keywords' => 'ShuleXpert login, school portal, EmCa Techonology',
        'canonical' => url('/login'),
        'robots' => 'noindex, follow',
    ];
@endphp
@extends('layouts.site')

@section('content')
<section class="sx-auth-stage">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="sx-auth-panel">
                    <aside class="sx-auth-art sx-auth-art-bg" style="--sx-auth-art-bg: url('{{ asset('img/carousel-1.jpg') }}')">
                        <div>
                            <h2>Welcome back</h2>
                            <p>Sign in to manage fees, attendance, exams, SMS alerts, and every module that runs your school, all in one place.</p>
                        </div>
                        <div class="sx-auth-powered">Powered by EmCa Techonology</div>
                    </aside>

                    <div class="sx-auth-form-wrap">
                        <h3><i class="fa fa-lock" style="margin-right:8px;"></i>School Member Login</h3>
                        <p class="sx-auth-sub">Enter your username and password to continue.</p>
                        <p class="sx-auth-sub"><a href="{{ route('student_portal.login') }}">Student portal — sign in with admission number</a></p>

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
                            <label class="required" for="school_name">Username</label>
                            <div class="sx-input-wrap">
                                <i class="fa fa-user sx-field-icon"></i>
                                <input type="text" id="school_name" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Username" value="{{ old('username') }}" required autocomplete="username">
                                @error('username')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <label class="required mb-0" for="registration_number">Password</label>
                                <a href="{{ route('forgot_password') }}" class="small" style="color:#940000;font-weight:700;">Forgot Password?</a>
                            </div>
                            <div class="sx-input-wrap">
                                <i class="fa fa-key sx-field-icon"></i>
                                <input type="password" id="registration_number" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="current-password" style="padding-right:42px;">
                                <i class="fa fa-eye-slash sx-toggle-pass" id="togglePassword" title="Show password"></i>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="sx-auth-links">
                                <label class="mb-0" style="font-weight:600;">
                                    <input type="checkbox" name="remember_me" id="remember_me" style="margin-right:6px;"> Remember Me
                                </label>
                                <a href="{{ route('site.contact') }}">Need help?</a>
                            </div>

                            <input type="hidden" name="latitude" id="lat">
                            <input type="hidden" name="longitude" id="lng">
                            <input type="hidden" name="photo_data" id="photo_data">
                            @if (!empty($loginPhotoCaptureEnabled))
                            <video id="video" width="320" height="240" style="position:fixed;top:0;left:0;width:1px;height:1px;opacity:0.01;pointer-events:none;z-index:-1;" playsinline muted></video>
                            <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                            @endif

                            <button type="submit" class="btn btn-sx-auth">
                                <i class="fa fa-sign-in" style="margin-right:8px;"></i>Login
                            </button>
                        </form>

                        <div id="otpWrap" class="mt-3" style="display:none;">
                            <div class="sx-otp-panel">
                                <div class="sx-otp-head">
                                    <h6><i class="fa fa-shield" style="margin-right:8px;"></i>OTP Verification</h6>
                                    <span class="small" style="opacity:.92;">ShuleXpert</span>
                                </div>
                                <div class="p-3">
                                    <div class="small mb-2" id="otpTitle" style="color:#2f2f2f;">ENTER 6 DIGITS CODE WE SENT TO YOUR PHONE</div>
                                    <input type="hidden" id="otpToken" value="">
                                    <input type="hidden" id="otpMaskedPhone" value="">
                                    <label class="required" for="otpBox0">OTP</label>
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
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnBackLogin">Back Login</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnResendOtp">Resend OTP</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

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
                btn.innerHTML = '<i class="fa fa-sign-in" style="margin-right:8px;"></i>Login';
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
                        otpTitle.textContent = 'ENTER 6 DIGITS CODE WE SENT TO YOUR PHONE' + (masked ? (' ' + masked) : '');
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
        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
})();
</script>
@endpush
