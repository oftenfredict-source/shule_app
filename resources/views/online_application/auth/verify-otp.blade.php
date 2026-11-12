@php
    $skipSplash = true;
    $seo = [
        'title' => 'Verify Phone | ShuleXpert Online Admission',
        'description' => 'Verify your phone number with OTP to activate your ShuleXpert online application account.',
        'canonical' => route('online_application.auth.verify_otp', ['token' => $token]),
        'image' => url('/images/login-school-night.jpg'),
        'robots' => 'noindex, follow',
    ];
    $expiresAt = $expiresAt ?? null;
    $otpTtlMinutes = $otpTtlMinutes ?? 10;
@endphp
@extends('layouts.site')

@section('content')
<section class="sx-login-unique sx-oa-login" style="--sx-login-bg: url('{{ asset('images/login-school-night.jpg') }}')">
    <div class="sx-login-unique__shell">
        <div class="sx-login-unique__card">
            <div class="sx-login-unique__brand">
                <img src="{{ asset('shule.png') }}" alt="ShuleXpert">
            </div>

            <h1 class="sx-login-unique__title">Verify Your Phone</h1>
            <p class="sx-login-unique__desc">
                We sent a 6-digit code to <strong>{{ $maskedPhone }}</strong>.
                Enter it below — verification runs automatically when complete.
            </p>

            <div class="sx-login-switch">
                <span class="sx-login-switch__current"><i class="fa fa-shield" aria-hidden="true"></i> Phone verification</span>
                <span class="sx-login-switch__alt">Expires in <strong id="oaOtpCountdown">{{ $otpTtlMinutes }}:00</strong></span>
            </div>

            <div id="oaOtpAlert" class="alert d-none" role="alert"></div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
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

            <form method="POST" action="{{ route('online_application.auth.verify_otp', ['token' => $token]) }}" id="oaOtpForm">
                @csrf
                <label class="sx-login-label required" for="otp">Verification code</label>
                <div class="sx-input-wrap">
                    <i class="fa fa-shield sx-field-icon"></i>
                    <input type="text" id="otp" name="otp" class="form-control text-center sx-otp-input" placeholder="000000" maxlength="6" required autocomplete="one-time-code" inputmode="numeric">
                </div>
                <p id="oaOtpStatus" class="small text-muted mt-2 mb-0" style="display:none;"></p>
            </form>

            <form method="POST" action="{{ route('online_application.auth.resend_otp', ['token' => $token]) }}" class="mt-3" id="oaResendOtpForm">
                @csrf
                <button type="submit" class="btn btn-sx-auth" id="oaResendOtpBtn" style="background:#fff !important;color:#940000 !important;border:1px solid #f0dada !important;">
                    Resend verification code
                </button>
            </form>

            <div class="sx-login-unique__foot">
                <a href="{{ route('online_application.auth.register') }}">Start over</a>
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
    padding-right: 14px;
}
.sx-otp-input {
    letter-spacing: 0.3em;
    font-size: 20px !important;
    font-weight: 700;
}
.sx-field-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #940000;
    z-index: 2;
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
@media (max-width: 991.98px) {
    .sx-login-unique { padding: 28px 14px 80px; min-height: auto; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    var form = document.getElementById('oaOtpForm');
    var input = document.getElementById('otp');
    var statusEl = document.getElementById('oaOtpStatus');
    var alertBox = document.getElementById('oaOtpAlert');
    var countdownEl = document.getElementById('oaOtpCountdown');
    var resendForm = document.getElementById('oaResendOtpForm');
    if (!form || !input) return;

    var csrf = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : '';
    var expiresAt = {{ (int) ($expiresAt ?? 0) }};
    var registerUrl = @json(route('online_application.auth.register'));
    var verifying = false;
    var lastTried = '';

    function showAlert(type, text) {
        if (!alertBox) return;
        alertBox.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
        alertBox.textContent = text;
        alertBox.classList.remove('d-none');
    }

    function setStatus(text, show) {
        if (!statusEl) return;
        statusEl.style.display = show ? 'block' : 'none';
        statusEl.textContent = text || '';
    }

    function tickCountdown() {
        if (!countdownEl || !expiresAt) return;
        var left = expiresAt - Math.floor(Date.now() / 1000);
        if (left <= 0) {
            countdownEl.textContent = '0:00';
            input.disabled = true;
            showAlert('danger', 'Verification code expired after {{ $otpTtlMinutes }} minutes. Please create your account again.');
            setTimeout(function () { window.location.href = registerUrl; }, 2200);
            return;
        }
        var m = Math.floor(left / 60);
        var s = left % 60;
        countdownEl.textContent = m + ':' + String(s).padStart(2, '0');
        setTimeout(tickCountdown, 1000);
    }
    tickCountdown();

    function verifyCode(code) {
        if (verifying) return;
        verifying = true;
        lastTried = code;
        input.readOnly = true;
        setStatus('Verifying...', true);

        var fd = new FormData(form);
        fd.set('otp', code);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            body: fd
        })
        .then(function (res) {
            return res.json().then(function (data) {
                return { ok: res.ok, data: data };
            });
        })
        .then(function (result) {
            if (result.ok && result.data && result.data.success && result.data.redirect) {
                setStatus('Verified. Saving account...', true);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account saved',
                        text: result.data.message || 'Your account is ready.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function () {
                        window.location.href = result.data.redirect;
                    });
                } else {
                    window.location.href = result.data.redirect;
                }
                return;
            }

            verifying = false;
            input.readOnly = false;
            lastTried = '';
            var message = (result.data && result.data.message) || 'Invalid verification code.';
            setStatus('', false);
            showAlert('danger', message);
            input.value = '';
            input.focus();
        })
        .catch(function () {
            verifying = false;
            input.readOnly = false;
            lastTried = '';
            setStatus('', false);
            showAlert('danger', 'Network error. Please try again.');
        });
    }

    input.addEventListener('input', function () {
        var code = String(input.value || '').replace(/\D+/g, '').slice(0, 6);
        if (input.value !== code) input.value = code;
        if (alertBox) alertBox.classList.add('d-none');

        if (code.length === 6 && code !== lastTried) {
            verifyCode(code);
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var code = String(input.value || '').replace(/\D+/g, '');
        if (code.length === 6) verifyCode(code);
    });

    if (resendForm) {
        resendForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(resendForm);
            fetch(resendForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: fd
            })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (result.ok && result.data && result.data.success) {
                    expiresAt = Math.floor(Date.now() / 1000) + ({{ (int) $otpTtlMinutes }} * 60);
                    input.disabled = false;
                    input.value = '';
                    lastTried = '';
                    showAlert('success', result.data.message || 'A new verification code has been sent.');
                    input.focus();
                    return;
                }
                showAlert('danger', (result.data && result.data.message) || 'Could not resend code.');
            })
            .catch(function () {
                showAlert('danger', 'Network error. Please try again.');
            });
        });
    }

    input.focus();
})();
</script>
@endpush
