@php
    $skipSplash = true;
    $seo = [
        'title' => 'Login to Apply Online | ShuleXpert Online Admission',
        'description' => 'Login to your ShuleXpert online application account with your phone number and password to apply to schools and track admission status.',
        'keywords' => 'login online application, ShuleXpert admission login, apply school online Tanzania',
        'canonical' => app(\App\Services\OnlineApplicationApplyTokenService::class)->publicAbsoluteUrl('/online-application/account/login'),
        'type' => 'website',
        'image' => url('/images/login-school-night.jpg'),
        'robots' => 'noindex, follow',
    ];
    $schoolsHref = auth('online_application')->check()
        ? route('online_application.portal.apply')
        : route('online_application');
    $registerHref = route('online_application.auth.register', array_filter(['redirect' => $redirect ?? null]));
@endphp
@extends('layouts.site')

@section('content')
<section class="sx-login-unique sx-oa-login" style="--sx-login-bg: url('{{ asset('images/login-school-night.jpg') }}')">
    <div class="sx-login-unique__shell">
        <div class="sx-login-unique__card">
            <div class="sx-login-unique__brand">
                <img src="{{ asset('shule.png') }}" alt="ShuleXpert">
            </div>

            <h1 class="sx-login-unique__title">Apply Online Login</h1>
            <p class="sx-login-unique__desc">Sign in with your phone number and password to apply to schools and track admission status.</p>

            <div class="sx-login-switch">
                <span class="sx-login-switch__current"><i class="fa fa-file-text-o" aria-hidden="true"></i> Online Application</span>
                <a href="{{ $registerHref }}" class="sx-login-switch__alt">Create account <i class="fa fa-angle-right" aria-hidden="true"></i></a>
            </div>

            <div id="oaLoginAlert" class="alert d-none" role="alert"></div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
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

            <form method="POST" action="{{ route('online_application.auth.login') }}" id="oaLoginForm">
                @csrf
                @if(!empty($redirect))
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                @endif

                @include('online_application.partials.tz-phone-field', [
                    'phoneAutocomplete' => 'username',
                ])

                <label class="sx-login-label required" for="password">Password</label>
                <div class="sx-input-wrap">
                    <i class="fa fa-key sx-field-icon"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Your password" required autocomplete="current-password">
                    <i class="fa fa-eye-slash sx-toggle-pass" id="oaTogglePassword" title="Show password" role="button" tabindex="0" aria-label="Show password"></i>
                </div>

                <div class="sx-auth-links">
                    <label class="sx-login-remember mb-0" for="oa_remember">
                        <input type="checkbox" name="remember" id="oa_remember" value="1">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ $schoolsHref }}">Back to schools</a>
                </div>

                <button type="submit" class="btn btn-sx-auth" id="oaLoginBtn">
                    <span class="oa-btn-label"><i class="fa fa-sign-in" style="margin-right:8px;"></i>Login</span>
                    <span class="oa-btn-loading" style="display:none;"><i class="fa fa-spinner fa-spin" style="margin-right:8px;"></i>Logging in...</span>
                </button>
            </form>

            <div class="sx-login-unique__foot">
                <p class="sx-login-secure mb-2"><i class="fa fa-lock" aria-hidden="true"></i> For online applications only — not school staff or parent portals.</p>
                <a href="{{ $registerHref }}">Create account to apply</a>
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
.sx-login-unique label.required,
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
.sx-tz-phone-hint {
    font-size: 12px;
    color: #777;
    margin: 6px 0 0 !important;
}
.sx-tz-phone-hint strong { color: #940000; }
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
#oaLoginBtn:disabled { opacity: 0.75; cursor: wait; }
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
    var form = document.getElementById('oaLoginForm');
    var btn = document.getElementById('oaLoginBtn');
    var alertBox = document.getElementById('oaLoginAlert');
    var toggle = document.getElementById('oaTogglePassword');
    var password = document.getElementById('password');
    if (!form || !btn) return;

    var csrf = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : '';
    var submitting = false;

    if (toggle && password) {
        toggle.addEventListener('click', function () {
            var show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            toggle.classList.toggle('fa-eye', show);
            toggle.classList.toggle('fa-eye-slash', !show);
            toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            toggle.setAttribute('title', show ? 'Hide password' : 'Show password');
        });
    }

    function showAlert(type, text) {
        if (!alertBox) return;
        alertBox.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
        alertBox.textContent = text;
        alertBox.classList.remove('d-none');
    }

    function setLoading(on) {
        submitting = on;
        btn.disabled = on;
        var label = btn.querySelector('.oa-btn-label');
        var loading = btn.querySelector('.oa-btn-loading');
        if (label) label.style.display = on ? 'none' : 'inline';
        if (loading) loading.style.display = on ? 'inline' : 'none';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (submitting) return;

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
                return { ok: res.ok, data: data };
            });
        })
        .then(function (result) {
            if (result.ok && result.data && result.data.success && result.data.redirect) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome back',
                        text: result.data.message || 'Login successful.',
                        timer: 1200,
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
            var message = (result.data && result.data.message) || 'Login failed. Please try again.';
            showAlert('danger', message);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Login failed', text: message });
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
        'name' => 'Login to Apply Online | ShuleXpert',
        'description' => 'Login to your ShuleXpert online school application account.',
        'url' => route('online_application.auth.login'),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
