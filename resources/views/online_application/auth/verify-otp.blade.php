@php
    $skipSplash = true;
    $seo = [
        'title' => 'Verify Phone — ShuleXpert Online Admission',
        'description' => 'Verify your phone number with OTP to activate your ShuleXpert online application account.',
        'canonical' => route('online_application.auth.verify_otp', ['token' => $token]),
        'robots' => 'noindex, follow',
    ];
    $expiresAt = $expiresAt ?? null;
    $otpTtlMinutes = $otpTtlMinutes ?? 10;
@endphp
@extends('layouts.online_application_portal')

@section('content')
<section class="sx-auth-stage">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="sx-auth-panel">
                    <div class="sx-auth-form-wrap w-100">
                        <h3><i class="fa fa-mobile" style="margin-right:8px;"></i>Verify Your Phone</h3>
                        <p class="sx-auth-sub">
                            We sent a 6-digit code to <strong>{{ $maskedPhone }}</strong>.
                            Enter it below — verification runs automatically when the code is complete.
                        </p>
                        <p class="sx-auth-sub mb-3">
                            Code expires in <strong id="oaOtpCountdown">{{ $otpTtlMinutes }}:00</strong>
                            <span class="text-muted">({{ $otpTtlMinutes }} minutes)</span>
                        </p>

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
                            <label class="required" for="otp">Verification code</label>
                            <div class="sx-input-wrap">
                                <i class="fa fa-shield sx-field-icon"></i>
                                <input type="text" id="otp" name="otp" class="form-control text-center" placeholder="000000" maxlength="6" required autocomplete="one-time-code" inputmode="numeric" style="letter-spacing:0.3em;font-size:20px;">
                            </div>
                            <p id="oaOtpStatus" class="small text-muted mt-2 mb-0" style="display:none;"></p>
                        </form>

                        <form method="POST" action="{{ route('online_application.auth.resend_otp', ['token' => $token]) }}" class="mt-3" id="oaResendOtpForm">
                            @csrf
                            <button type="submit" class="btn btn-link p-0" id="oaResendOtpBtn" style="color:#940000;font-weight:600;">
                                Resend verification code
                            </button>
                        </form>

                        <div class="sx-auth-links mt-4 mb-0">
                            <a href="{{ route('online_application.auth.register') }}">Start over</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

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

    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
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
