@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">Register Your School</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.register_school') }}">Register School</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-gap" style="padding-top:40px;padding-bottom:60px;">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" style="border-radius:0;border-left:4px solid #1a7f37;">
                {{ session('success') }}
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-4" style="border:1px solid #e5e5e5;padding:16px 18px;background:#fff;">
                    <h4 style="color:#940000;font-weight:700;margin-bottom:8px;">School Registration — ShuleXpert</h4>
                    <p class="mb-2" style="color:#555;">
                        Fill in this form to apply to join ShuleXpert. After submission, your application will remain
                        <strong>Pending</strong> until our team verifies it. <strong>You will not receive login credentials</strong>
                        until after verification.
                    </p>
                    <p class="mb-0 small text-muted">Steps: 1) Fill in your details → 2) Submit Registration → 3) Verify phone with OTP → 4) Application is saved</p>
                </div>

                <form id="schoolRegForm" method="POST" action="{{ route('site.register_school.submit') }}" style="border:1px solid #e5e5e5;padding:24px;background:#fff;">
                    @csrf
                    <input type="hidden" name="otp_token" id="otp_token" value="">

                    <h6 style="color:#940000;font-weight:700;border-bottom:1px solid #eee;padding-bottom:8px;margin-bottom:18px;">Basic Information</h6>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">School Name <span class="text-danger">*</span></label>
                            <input type="text" name="school_name" class="form-control" value="{{ old('school_name') }}" required>
                            @error('school_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="font-weight-bold">Registration No.</label>
                            <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number') }}" placeholder="e.g. S927/123">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="font-weight-bold">Exam Center No.</label>
                            <input type="text" name="exam_center_number" class="form-control" value="{{ old('exam_center_number') }}">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Education Level <span class="text-danger">*</span></label>
                            <select name="school_type" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="Primary" {{ old('school_type') === 'Primary' ? 'selected' : '' }}>Primary</option>
                                <option value="Secondary" {{ old('school_type') === 'Secondary' ? 'selected' : '' }}>Secondary</option>
                            </select>
                            @error('school_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Ownership <span class="text-danger">*</span></label>
                            <select name="ownership" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="Public" {{ old('ownership') === 'Public' ? 'selected' : '' }}>Public (Government)</option>
                                <option value="Private" {{ old('ownership') === 'Private' ? 'selected' : '' }}>Private / NGO</option>
                            </select>
                            @error('ownership')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Established Year</label>
                            <input type="text" name="established_year" class="form-control" value="{{ old('established_year') }}" placeholder="e.g. 1998">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Number of Students <span class="text-danger">*</span></label>
                            <input type="number" name="number_of_students" class="form-control" value="{{ old('number_of_students') }}" min="1" required>
                            @error('number_of_students')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}" placeholder="Headmaster / Admin name">
                        </div>
                    </div>

                    <h6 style="color:#940000;font-weight:700;border-bottom:1px solid #eee;padding-bottom:8px;margin:24px 0 18px;">Location & Contact</h6>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Region <span class="text-danger">*</span></label>
                            <input type="text" name="region" class="form-control" value="{{ old('region') }}" required>
                            @error('region')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">District <span class="text-danger">*</span></label>
                            <input type="text" name="district" class="form-control" value="{{ old('district') }}" required>
                            @error('district')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Ward</label>
                            <input type="text" name="ward" class="form-control" value="{{ old('ward') }}">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', '255') }}" required placeholder="2557XXXXXXXX" maxlength="12">
                            @error('phone')<small class="text-danger d-block">{{ $message }}</small>@enderror
                            <small class="text-muted">An OTP will be sent after you click Submit Registration.</small>
                        </div>
                        <div class="col-12 form-group mb-3">
                            <label class="font-weight-bold">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-12 form-group mb-3">
                            <label class="font-weight-bold">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional — message to ShuleXpert team">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- OTP step: shown after Submit, before save --}}
                    <div id="otpStep" style="display:none;margin:18px 0;padding:16px;border:1px solid #e0cdcd;background:#fffaf9;">
                        <h6 style="color:#940000;font-weight:700;margin-bottom:8px;">Verify phone with OTP</h6>
                        <p class="mb-2 small" id="otpIntro" style="color:#555;">We have sent an OTP to your phone. Enter the code to complete registration.</p>
                        <div class="d-flex flex-wrap" style="gap:8px;max-width:420px;">
                            <input type="text" id="otpInput" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
                            <button type="button" class="btn btn-dark" id="btnVerifyOtp" style="border-radius:0;white-space:nowrap;">Verify & Save</button>
                            <button type="button" class="btn btn-outline-secondary" id="btnResendOtp" style="border-radius:0;white-space:nowrap;">Resend OTP</button>
                        </div>
                        <small id="otpStatus" class="d-block mt-2" style="color:#666;"></small>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3" style="gap:12px;">
                        <small class="text-muted" id="verifyHint">Fill in the form, then click Submit Registration — an OTP will be sent to your phone.</small>
                        <button type="submit" class="btn btn-lg" id="btnSubmit" style="background:#940000;color:#fff;border-radius:0;font-weight:700;">
                            Submit Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var form = document.getElementById('schoolRegForm');
    var phoneInput = document.getElementById('phone');
    var otpToken = document.getElementById('otp_token');
    var otpStep = document.getElementById('otpStep');
    var otpInput = document.getElementById('otpInput');
    var otpStatus = document.getElementById('otpStatus');
    var otpIntro = document.getElementById('otpIntro');
    var btnVerify = document.getElementById('btnVerifyOtp');
    var btnResend = document.getElementById('btnResendOtp');
    var btnSubmit = document.getElementById('btnSubmit');
    var verifyHint = document.getElementById('verifyHint');
    var verified = false;
    var verifiedPhone = '';
    var otpSent = false;
    var sendingOtp = false;
    var allowingSubmit = false;

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        if (m) return m.getAttribute('content');
        var i = document.querySelector('input[name="_token"]');
        return i ? i.value : '';
    }
    function digitsOnly(v) { return String(v || '').replace(/\D+/g, ''); }
    function normalizePhone(v) {
        var d = digitsOnly(v);
        if (d.startsWith('255')) d = d.slice(3);
        if (d.startsWith('0')) d = d.slice(1);
        return ('255' + d).slice(0, 12);
    }
    function isValidTzMobile(phone) {
        return /^255[67]\d{8}$/.test(phone);
    }
    function setStatus(msg, ok) {
        otpStatus.style.color = ok ? '#1a7f37' : '#940000';
        otpStatus.textContent = msg || '';
    }
    function resetVerification() {
        verified = false;
        verifiedPhone = '';
        otpSent = false;
        otpToken.value = '';
        otpInput.value = '';
        otpStep.style.display = 'none';
        btnSubmit.style.display = '';
        verifyHint.textContent = 'Fill in the form, then click Submit Registration — an OTP will be sent to your phone.';
        verifyHint.style.color = '#666';
        setStatus('', true);
    }

    phoneInput.addEventListener('input', function () {
        var d = digitsOnly(phoneInput.value);
        if (!d.startsWith('255')) {
            d = '255' + d.replace(/^255/, '').replace(/^0+/, '');
        }
        phoneInput.value = d.slice(0, 12);
        if ((otpSent || verified) && verifiedPhone && phoneInput.value !== verifiedPhone) {
            resetVerification();
        }
    });

    if (!digitsOnly(phoneInput.value).startsWith('255')) {
        phoneInput.value = '255';
    }

    function sendOtp() {
        var phone = normalizePhone(phoneInput.value);
        phoneInput.value = phone;
        if (!isValidTzMobile(phone)) {
            otpStep.style.display = 'block';
            setStatus('Enter a valid number: 2557XXXXXXXX or 2556XXXXXXXX', false);
            return Promise.resolve(false);
        }

        sendingOtp = true;
        btnSubmit.disabled = true;
        btnResend.disabled = true;
        setStatus('Sending OTP...', true);
        otpStep.style.display = 'block';
        otpStep.scrollIntoView({ behavior: 'smooth', block: 'center' });

        return fetch('{{ route('site.register_school.send_otp') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ phone: phone })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
            if (!res.body || !res.body.success) {
                setStatus((res.body && res.body.message) ? res.body.message : 'Failed to send OTP.', false);
                return false;
            }
            otpToken.value = res.body.otp_token || '';
            otpSent = true;
            verifiedPhone = phone;
            verified = false;
            otpIntro.textContent = 'We sent an OTP to ' + (res.body.masked_phone || 'your phone') + '. Enter the code to complete registration.';
            setStatus(res.body.message || 'OTP sent.', true);
            verifyHint.textContent = 'Enter the OTP you received, then click Verify & Save.';
            verifyHint.style.color = '#940000';
            btnSubmit.style.display = 'none';
            otpInput.focus();
            return true;
        })
        .catch(function () {
            setStatus('Network error while sending OTP.', false);
            return false;
        })
        .finally(function () {
            sendingOtp = false;
            btnSubmit.disabled = false;
            btnResend.disabled = false;
        });
    }

    form.addEventListener('submit', function (e) {
        if (allowingSubmit && verified) {
            return; // proceed to server save
        }
        e.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var phone = normalizePhone(phoneInput.value);
        phoneInput.value = phone;
        if (!isValidTzMobile(phone)) {
            otpStep.style.display = 'block';
            setStatus('Enter a valid number: 2557XXXXXXXX or 2556XXXXXXXX', false);
            return;
        }

        // Already verified for this phone → save
        if (verified && verifiedPhone === phone && otpToken.value) {
            allowingSubmit = true;
            form.submit();
            return;
        }

        // First submit → send OTP, do not save yet
        if (sendingOtp) return;
        sendOtp();
    });

    btnResend.addEventListener('click', function () {
        if (sendingOtp) return;
        verified = false;
        sendOtp();
    });

    btnVerify.addEventListener('click', function () {
        var phone = normalizePhone(phoneInput.value);
        var otp = digitsOnly(otpInput.value);
        phoneInput.value = phone;

        if (!otpToken.value) {
            setStatus('Click Submit Registration first to receive an OTP.', false);
            return;
        }
        if (!otp || otp.length < 4) {
            setStatus('Enter a valid OTP (6 digits).', false);
            return;
        }

        btnVerify.disabled = true;
        btnResend.disabled = true;
        btnVerify.textContent = 'Verifying...';

        fetch('{{ route('site.register_school.verify_otp') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ phone: phone, otp: otp, otp_token: otpToken.value })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
            if (!res.body || !res.body.success) {
                setStatus((res.body && res.body.message) ? res.body.message : 'Incorrect OTP.', false);
                return;
            }
            verified = true;
            verifiedPhone = phone;
            setStatus(res.body.message || 'Phone verified. Saving your registration...', true);
            verifyHint.textContent = 'Phone verified — saving your application...';
            verifyHint.style.color = '#1a7f37';
            allowingSubmit = true;
            form.submit();
        })
        .catch(function () {
            setStatus('Network error while verifying OTP.', false);
        })
        .finally(function () {
            btnVerify.disabled = false;
            btnResend.disabled = false;
            btnVerify.textContent = 'Verify & Save';
        });
    });
})();
</script>
@endsection
