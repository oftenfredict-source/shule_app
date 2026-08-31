@php
    $skipSplash = true;
    $seo = [
        'title' => 'Create Account to Apply Online | ShuleXpert Online Admission',
        'description' => 'Create your ShuleXpert online application account to apply to schools in Tanzania. Verify your phone with OTP and start submitting student admission applications.',
        'keywords' => 'create online application account, school admission account Tanzania, ShuleXpert apply online, student application registration',
        'canonical' => app(\App\Services\OnlineApplicationApplyTokenService::class)->publicAbsoluteUrl('/online-application/account/register'),
        'type' => 'website',
        'image' => url('/images/shuleXpert.jpg'),
        'robots' => 'noindex, follow',
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
                            <h2>Apply to schools online</h2>
                            <p>Create a dedicated online application account, separate from school staff, teacher, or parent accounts. Verify your phone number and start applying in minutes.</p>
                        </div>
                        <div class="sx-auth-powered">Powered by EmCa Techonology</div>
                    </aside>

                    <div class="sx-auth-form-wrap">
                        <h3><i class="fa fa-user-plus" style="margin-right:8px;"></i>Create Account to Apply</h3>
                        <p class="sx-auth-sub">Fill in your details below, then click <strong>Create Account</strong>. We will send a verification code to your phone, enter it to save and activate your account.</p>

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

                            <label class="required" for="first_name">First name</label>
                            <div class="sx-input-wrap">
                                <i class="fa fa-user sx-field-icon"></i>
                                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First name" value="{{ old('first_name') }}" required>
                            </div>

                            <label for="middle_name">Middle name</label>
                            <div class="sx-input-wrap">
                                <i class="fa fa-user sx-field-icon"></i>
                                <input type="text" id="middle_name" name="middle_name" class="form-control" placeholder="Middle name (optional)" value="{{ old('middle_name') }}">
                            </div>

                            <label class="required" for="last_name">Last name</label>
                            <div class="sx-input-wrap">
                                <i class="fa fa-user sx-field-icon"></i>
                                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last name" value="{{ old('last_name') }}" required>
                            </div>

                            <label class="required" for="password">Create password</label>
                            <div class="sx-input-wrap">
                                <i class="fa fa-key sx-field-icon"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" required autocomplete="new-password" minlength="6">
                            </div>

                            <label class="required" for="password_confirmation">Confirm password</label>
                            <div class="sx-input-wrap">
                                <i class="fa fa-key sx-field-icon"></i>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password" required autocomplete="new-password" minlength="6">
                            </div>

                            <button type="submit" class="btn btn-sx-auth" id="oaCreateAccountBtn">
                                <span class="oa-btn-label"><i class="fa fa-user-plus" style="margin-right:8px;"></i>Create Account</span>
                                <span class="oa-btn-loading" style="display:none;"><i class="fa fa-spinner fa-spin" style="margin-right:8px;"></i>Creating account...</span>
                            </button>
                            <p id="oaPhoneExistsMsg" class="text-danger small mt-2 mb-0" style="display:none;"></p>
                        </form>

                        <div class="sx-auth-links mt-4 mb-0">
                            <span>Already have an account?</span>
                            <a href="{{ route('online_application.auth.login', array_filter(['redirect' => $redirect ?? null])) }}">Login to apply online</a>
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
#oaCreateAccountBtn:disabled { opacity: 0.75; cursor: wait; }
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
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
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
