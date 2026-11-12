@php
    $skipSplash = true;
    $seo = [
        'title' => 'Create Account to Apply Online | ShuleXpert Online Admission',
        'description' => 'Create your ShuleXpert online application account to apply to schools in Tanzania. Verify your phone with OTP and start submitting student admission applications.',
        'keywords' => 'create online application account, school admission account Tanzania, ShuleXpert apply online, student application registration',
        'canonical' => app(\App\Services\OnlineApplicationApplyTokenService::class)->publicAbsoluteUrl('/online-application/account/register'),
        'type' => 'website',
        'image' => url('/images/login-school-night.jpg'),
        'robots' => 'noindex, follow',
    ];
    $loginHref = route('online_application.auth.login', array_filter(['redirect' => $redirect ?? null]));
    $schoolsHref = auth('online_application')->check()
        ? route('online_application.portal.apply')
        : route('online_application');
@endphp
@extends('layouts.site')

@section('content')
<section class="sx-login-unique sx-oa-login" style="--sx-login-bg: url('{{ asset('images/login-school-night.jpg') }}')">
    <div class="sx-login-unique__shell sx-oa-register-shell">
        <div class="sx-login-unique__card">
            <div class="sx-login-unique__brand">
                <img src="{{ asset('shule.png') }}" alt="ShuleXpert">
            </div>

            <h1 class="sx-login-unique__title">Create Account to Apply</h1>
            <p class="sx-login-unique__desc">Fill in your details, then verify your phone with a code to activate your online application account.</p>

            <div class="sx-login-switch">
                <span class="sx-login-switch__current"><i class="fa fa-user-plus" aria-hidden="true"></i> New account</span>
                <a href="{{ $loginHref }}" class="sx-login-switch__alt">Login <i class="fa fa-angle-right" aria-hidden="true"></i></a>
            </div>

            <div id="oaRegisterAlert" class="alert d-none" role="alert"></div>

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

            <form method="POST" action="{{ route('online_application.auth.register') }}" id="oaRegisterForm">
                @csrf
                @if(!empty($redirect))
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                @endif

                @include('online_application.partials.tz-phone-field', [
                    'phoneAutocomplete' => 'tel',
                ])

                <label class="sx-login-label required" for="first_name">First name</label>
                <div class="sx-input-wrap">
                    <i class="fa fa-user sx-field-icon"></i>
                    <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First name" value="{{ old('first_name') }}" required>
                </div>

                <label class="sx-login-label" for="middle_name">Middle name</label>
                <div class="sx-input-wrap">
                    <i class="fa fa-user sx-field-icon"></i>
                    <input type="text" id="middle_name" name="middle_name" class="form-control" placeholder="Middle name (optional)" value="{{ old('middle_name') }}">
                </div>

                <label class="sx-login-label required" for="last_name">Last name</label>
                <div class="sx-input-wrap">
                    <i class="fa fa-user sx-field-icon"></i>
                    <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last name" value="{{ old('last_name') }}" required>
                </div>

                <label class="sx-login-label required" for="password">Create password</label>
                <div class="sx-input-wrap">
                    <i class="fa fa-key sx-field-icon"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" required autocomplete="new-password" minlength="6">
                </div>

                <label class="sx-login-label required" for="password_confirmation">Confirm password</label>
                <div class="sx-input-wrap">
                    <i class="fa fa-key sx-field-icon"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password" required autocomplete="new-password" minlength="6">
                </div>

                <button type="submit" class="btn btn-sx-auth mt-3" id="oaCreateAccountBtn">
                    <span class="oa-btn-label"><i class="fa fa-user-plus" style="margin-right:8px;"></i>Create Account</span>
                    <span class="oa-btn-loading" style="display:none;"><i class="fa fa-spinner fa-spin" style="margin-right:8px;"></i>Creating account...</span>
                </button>
                <p id="oaPhoneExistsMsg" class="text-danger small mt-2 mb-0" style="display:none;"></p>
            </form>

            <div class="sx-login-unique__foot">
                <p class="sx-login-secure mb-2"><i class="fa fa-lock" aria-hidden="true"></i> Online applications only — not school staff or parent portals.</p>
                <a href="{{ $loginHref }}">Already have an account? Login</a>
                <div class="mt-2">
                    <a href="{{ $schoolsHref }}" class="sx-oa-muted-link">Back to schools</a>
                </div>
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
.sx-oa-register-shell { max-width: 480px; }
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
.sx-field-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #940000;
    z-index: 2;
}
.sx-tz-phone-hint {
    font-size: 12px;
    color: #777;
    margin: 6px 0 0 !important;
}
.sx-tz-phone-hint strong { color: #940000; }
.btn-sx-auth {
    width: 100%;
    background: #940000 !important;
    color: #fff !important;
    border: 0 !important;
    border-radius: 10px !important;
    font-weight: 800 !important;
    min-height: 46px;
}
#oaCreateAccountBtn:disabled { opacity: 0.75; cursor: wait; }
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
.sx-oa-muted-link {
    color: #777 !important;
    font-weight: 600 !important;
    font-size: 12.5px !important;
}
@media (max-width: 991.98px) {
    .sx-login-unique { padding: 28px 14px 80px; min-height: auto; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('online_application.partials.tz-phone-script')
<script>
(function () {
    var phoneInput = document.getElementById('phone');
    var msg = document.getElementById('oaPhoneExistsMsg');
    var btn = document.getElementById('oaCreateAccountBtn');
    var form = document.getElementById('oaRegisterForm');
    var alertBox = document.getElementById('oaRegisterAlert');
    if (!phoneInput || !form || !btn) return;

    var checkUrl = @json(route('online_application.auth.check_phone'));
    var csrf = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : '';
    var timer = null;
    var lastChecked = '';
    var submitting = false;

    function setExists(exists, text) {
        if (!msg) return;
        if (exists) {
            msg.style.display = 'block';
            msg.textContent = text || 'This phone number already has an account. Please login instead.';
            btn.disabled = true;
        } else {
            msg.style.display = 'none';
            msg.textContent = '';
            if (!submitting) btn.disabled = false;
        }
    }

    function showAlert(type, text) {
        if (!alertBox) return;
        alertBox.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
        alertBox.textContent = text;
        alertBox.classList.remove('d-none');
    }

    function setLoading(on) {
        submitting = on;
        btn.disabled = on || (msg && msg.style.display === 'block' && msg.textContent);
        var label = btn.querySelector('.oa-btn-label');
        var loading = btn.querySelector('.oa-btn-loading');
        if (label) label.style.display = on ? 'none' : 'inline';
        if (loading) loading.style.display = on ? 'inline' : 'none';
    }

    function checkPhone() {
        var phone = String(phoneInput.value || '').replace(/\D+/g, '');
        if (!/^255[67]\d{8}$/.test(phone)) {
            setExists(false);
            lastChecked = '';
            return;
        }
        if (phone === lastChecked) return;
        lastChecked = phone;

        fetch(checkUrl + '?phone=' + encodeURIComponent(phone), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.exists) {
                setExists(true, data.message);
            } else {
                setExists(false);
            }
        })
        .catch(function () {});
    }

    phoneInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(checkPhone, 350);
    });
    phoneInput.addEventListener('blur', checkPhone);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (submitting) return;
        if (msg && msg.style.display === 'block' && msg.textContent) {
            showAlert('danger', msg.textContent);
            return;
        }

        setLoading(true);
        if (alertBox) alertBox.classList.add('d-none');

        var fd = new FormData(form);
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
                return { ok: res.ok, status: res.status, data: data };
            });
        })
        .then(function (result) {
            if (result.ok && result.data && result.data.success && result.data.redirect) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Code sent',
                        text: result.data.message || 'Enter the verification code to save your account.',
                        timer: 1600,
                        showConfirmButton: false
                    }).then(function () {
                        window.location.href = result.data.redirect;
                    });
                } else {
                    window.location.href = result.data.redirect;
                }
                return;
            }

            setLoading(false);
            var message = (result.data && result.data.message) || 'Could not create account. Please try again.';
            showAlert('danger', message);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Unable to continue', text: message });
            }
        })
        .catch(function () {
            setLoading(false);
            showAlert('danger', 'Network error. Please try again.');
        });
    });
})();
</script>
@endpush

@push('schema')
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => 'Create Online Application Account | ShuleXpert',
        'description' => 'Register for a ShuleXpert online school application account with phone verification.',
        'url' => route('online_application.auth.register'),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
