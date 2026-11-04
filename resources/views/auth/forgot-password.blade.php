@php
    $skipSplash = true;
    $seo = [
        'title' => 'Forgot Password | ShuleXpert',
        'description' => 'Reset your ShuleXpert school account password. A new password is sent to your registered phone. Powered by EmCa Techonology.',
        'keywords' => 'ShuleXpert forgot password, reset password, EmCa Techonology',
        'canonical' => url('/forgot-password'),
        'robots' => 'noindex, follow',
    ];
@endphp
@extends('layouts.site')

@section('content')
<section class="sx-auth-stage sx-login-stage" style="--sx-auth-art-bg: url('{{ asset('img/shulexpert-login-bg.jpg') }}')">
    <div class="sx-login-stage__bg" aria-hidden="true"></div>
    <div class="sx-login-stage__glow sx-login-stage__glow--a" aria-hidden="true"></div>
    <div class="sx-login-stage__glow sx-login-stage__glow--b" aria-hidden="true"></div>

    <div class="container sx-login-container">
        <div class="sx-auth-panel sx-login-panel">
            <aside class="sx-auth-art sx-login-art">
                <div class="sx-login-art__top">
                    <div class="sx-login-brand">
                        <img class="sx-auth-logo" src="{{ asset('images/shuleXpert.jpg') }}" alt="ShuleXpert">
                        <div class="sx-login-brand__text">
                            <span class="sx-login-brand__name">ShuleXpert</span>
                            <span class="sx-login-brand__sub">School Management Platform</span>
                        </div>
                    </div>
                    <h2 class="sx-login-art__title">Reset access</h2>
                    <p class="sx-login-art__lead">Weka username yako. Password mpya itatumwa kwenye namba ya simu uliyosajiliwa nayo kwenye mfumo.</p>
                </div>
                <div class="sx-auth-powered">Powered by EmCa Techonology</div>
            </aside>

            <div class="sx-auth-form-wrap sx-login-form">
                <div class="sx-login-form__head">
                    <p class="sx-login-kicker">Account recovery</p>
                    <h3>Forgot Password</h3>
                    <p class="sx-auth-sub">We will SMS a new password to your registered phone number.</p>
                </div>

                <form id="forgotPasswordForm" method="POST" action="{{ route('auth.forgot_password') }}">
                    @csrf
                    <label class="required" for="fp_username">Username</label>
                    <div class="sx-input-wrap">
                        <i class="fa fa-user sx-field-icon"></i>
                        <input type="text" id="fp_username" name="username" class="form-control" placeholder="Weka username yako" required autocomplete="username">
                    </div>

                    <div id="fpAlert" class="mb-3"></div>

                    <button type="submit" class="btn btn-sx-auth" id="fpSubmitBtn">
                        <i class="fa fa-paper-plane" style="margin-right:8px;"></i>Get Password
                    </button>
                </form>

                <div class="sx-auth-links mt-4 mb-0">
                    <a href="{{ route('login') }}"><i class="fa fa-arrow-left" style="margin-right:6px;"></i>Back to Login</a>
                    <a href="{{ route('site.contact') }}">Contact support</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fpForm = document.getElementById('forgotPasswordForm');
    if (!fpForm) return;

    fpForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('fpSubmitBtn');
        const alertBox = document.getElementById('fpAlert');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right:8px;"></i>Inatuma...';
        alertBox.innerHTML = '';

        try {
            const fd = new FormData(this);
            const res = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            });
            const data = await res.json();
            if (data && data.success) {
                alertBox.innerHTML = `<div class="alert alert-success small mb-0">${data.message}</div>`;
                this.reset();
            } else {
                alertBox.innerHTML = `<div class="alert alert-danger small mb-0">${data.message || 'Tatizo limetokea. Tafadhali jaribu tena.'}</div>`;
            }
        } catch (err) {
            alertBox.innerHTML = `<div class="alert alert-danger small mb-0">Tatizo la mtandao. Tafadhali jaribu tena.</div>`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane" style="margin-right:8px;"></i>Get Password';
        }
    });
});
</script>
@endpush
