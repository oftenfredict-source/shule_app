@php
    $skipSplash = true;
    $seo = [
        'title' => 'Login to Apply Online — ShuleXpert Online Admission',
        'description' => 'Login to your ShuleXpert online application account with your phone number and password to apply to schools and track admission status.',
        'keywords' => 'login online application, ShuleXpert admission login, apply school online Tanzania',
        'canonical' => route('online_application.auth.login'),
        'type' => 'website',
        'image' => url('/images/shuleXpert.jpg'),
    ];
@endphp
@extends('layouts.online_application_portal')

@section('content')
<section class="sx-auth-stage">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="sx-auth-panel">
                    <aside class="sx-auth-art sx-auth-art-bg" style="--sx-auth-art-bg: url('{{ asset('img/carousel-1.jpg') }}')">
                        <div>
                            <h2>Your application portal</h2>
                            <p>Login with your phone number and password to apply to schools, track application status, and manage your online admission account.</p>
                        </div>
                        <div class="sx-auth-powered">Powered by EmCa Techonology</div>
                    </aside>

                    <div class="sx-auth-form-wrap">
                        <h3><i class="fa fa-sign-in" style="margin-right:8px;"></i>Login to Apply Online</h3>
                        <p class="sx-auth-sub">This login is only for online school applications — not for school staff or parent accounts.</p>

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

                            <label class="required" for="password">Password</label>
                            <div class="sx-input-wrap">
                                <i class="fa fa-key sx-field-icon"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Your password" required autocomplete="current-password">
                            </div>

                            <div class="sx-auth-links mb-3">
                                <label class="mb-0" style="font-weight:600;">
                                    <input type="checkbox" name="remember" value="1" style="margin-right:6px;"> Remember me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-sx-auth" id="oaLoginBtn">
                                <span class="oa-btn-label"><i class="fa fa-unlock" style="margin-right:8px;"></i>Login</span>
                                <span class="oa-btn-loading" style="display:none;"><i class="fa fa-spinner fa-spin" style="margin-right:8px;"></i>Logging in...</span>
                            </button>
                        </form>

                        <div class="sx-auth-links mt-4 mb-0">
                            <span>Don't have an account?</span>
                            <a href="{{ route('online_application.auth.register', array_filter(['redirect' => $redirect ?? null])) }}">Create account to apply</a>
                            <a href="{{ auth('online_application')->check() ? route('online_application.portal.apply') : route('online_application') }}">Back to schools</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-tz-phone-hint {
    font-size: 12px;
    color: #777;
    margin-bottom: 12px !important;
}
.sx-tz-phone-hint strong { color: #940000; }
#oaLoginBtn:disabled { opacity: 0.75; cursor: wait; }
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
    if (!form || !btn) return;

    var csrf = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : '';
    var submitting = false;

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
        'name' => 'Login to Apply Online — ShuleXpert',
        'description' => 'Login to your ShuleXpert online school application account.',
        'url' => route('online_application.auth.login'),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
