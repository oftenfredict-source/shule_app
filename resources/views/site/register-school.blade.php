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
                    <h4 style="color:#940000;font-weight:700;margin-bottom:8px;">Usajili wa Shule — ShuleXpert</h4>
                    <p class="mb-2" style="color:#555;">
                        Jaza fomu hii ili kuomba kujiunga na ShuleXpert. Baada ya kuwasilisha, maombi yatakuwa
                        <strong>Pending</strong> hadi timu yetu iyaverify. <strong>Hautapokea login credentials</strong>
                        mpaka baada ya verification.
                    </p>
                    <p class="mb-0 small text-muted">Hatua: 1) Jaza taarifa → 2) Thibitisha simu kwa OTP → 3) Submit</p>
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
                            <label class="font-weight-bold">Phone (OTP) <span class="text-danger">*</span></label>
                            <div class="d-flex" style="gap:8px;">
                                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', '255') }}" required placeholder="2557XXXXXXXX" maxlength="12">
                                <button type="button" class="btn" id="btnSendOtp" style="background:#940000;color:#fff;white-space:nowrap;border-radius:0;">Send OTP</button>
                            </div>
                            @error('phone')<small class="text-danger d-block">{{ $message }}</small>@enderror
                            <div id="otpBox" style="display:none;margin-top:10px;">
                                <div class="d-flex" style="gap:8px;max-width:360px;">
                                    <input type="text" id="otpInput" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6" inputmode="numeric">
                                    <button type="button" class="btn btn-dark" id="btnVerifyOtp" style="border-radius:0;white-space:nowrap;">Verify</button>
                                </div>
                                <small id="otpStatus" class="d-block mt-2" style="color:#666;"></small>
                            </div>
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

                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3" style="gap:12px;">
                        <small class="text-muted" id="verifyHint">Thibitisha simu kwa OTP kabla ya kuwasilisha.</small>
                        <button type="submit" class="btn btn-lg" id="btnSubmit" disabled style="background:#940000;color:#fff;border-radius:0;font-weight:700;">
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
    var phoneInput = document.getElementById('phone');
    var otpToken = document.getElementById('otp_token');
    var otpBox = document.getElementById('otpBox');
    var otpInput = document.getElementById('otpInput');
    var otpStatus = document.getElementById('otpStatus');
    var btnSend = document.getElementById('btnSendOtp');
    var btnVerify = document.getElementById('btnVerifyOtp');
    var btnSubmit = document.getElementById('btnSubmit');
    var verifyHint = document.getElementById('verifyHint');
    var verified = false;
    var verifiedPhone = '';

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
    function resetVerification() {
        verified = false;
        verifiedPhone = '';
        otpToken.value = '';
        btnSubmit.disabled = true;
        verifyHint.textContent = 'Thibitisha simu kwa OTP kabla ya kuwasilisha.';
        verifyHint.style.color = '#666';
    }

    phoneInput.addEventListener('input', function () {
        var d = digitsOnly(phoneInput.value);
        if (!d.startsWith('255')) {
            d = '255' + d.replace(/^255/, '').replace(/^0+/, '');
        }
        phoneInput.value = d.slice(0, 12);
        if (verified && phoneInput.value !== verifiedPhone) {
            resetVerification();
            otpBox.style.display = 'none';
        }
    });

    if (!digitsOnly(phoneInput.value).startsWith('255')) {
        phoneInput.value = '255';
    }

    btnSend.addEventListener('click', function () {
        var phone = normalizePhone(phoneInput.value);
        phoneInput.value = phone;
        if (!isValidTzMobile(phone)) {
            otpBox.style.display = 'block';
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Enter a valid number: 2557XXXXXXXX or 2556XXXXXXXX';
            return;
        }
        resetVerification();
        btnSend.disabled = true;
        btnSend.textContent = 'Sending...';
        otpBox.style.display = 'block';
        otpStatus.style.color = '#666';
        otpStatus.textContent = 'Sending OTP...';

        fetch('{{ route('site.register_school.send_otp') }}', {
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
                otpStatus.style.color = '#940000';
                otpStatus.textContent = (res.body && res.body.message) ? res.body.message : 'Failed to send OTP.';
                return;
            }
            otpToken.value = res.body.otp_token || '';
            otpStatus.style.color = '#1a7f37';
            otpStatus.textContent = res.body.message || 'OTP sent.';
            otpInput.focus();
        })
        .catch(function () {
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Network error while sending OTP.';
        })
        .finally(function () {
            btnSend.disabled = false;
            btnSend.textContent = 'Send OTP';
        });
    });

    btnVerify.addEventListener('click', function () {
        var phone = normalizePhone(phoneInput.value);
        var otp = digitsOnly(otpInput.value);
        phoneInput.value = phone;
        if (!otpToken.value) {
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Request OTP first.';
            return;
        }
        btnVerify.disabled = true;
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
                otpStatus.style.color = '#940000';
                otpStatus.textContent = (res.body && res.body.message) ? res.body.message : 'OTP verification failed.';
                return;
            }
            verified = true;
            verifiedPhone = phone;
            btnSubmit.disabled = false;
            otpStatus.style.color = '#1a7f37';
            otpStatus.textContent = res.body.message || 'Phone verified.';
            verifyHint.textContent = 'Simu imethibitishwa — unaweza kuwasilisha.';
            verifyHint.style.color = '#1a7f37';
        })
        .catch(function () {
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Network error while verifying OTP.';
        })
        .finally(function () {
            btnVerify.disabled = false;
            btnVerify.textContent = 'Verify';
        });
    });

    document.getElementById('schoolRegForm').addEventListener('submit', function (e) {
        if (!verified) {
            e.preventDefault();
            otpBox.style.display = 'block';
            otpStatus.style.color = '#940000';
            otpStatus.textContent = 'Thibitisha namba ya simu kwa OTP kabla ya kuwasilisha.';
        }
    });
})();
</script>
@endsection
