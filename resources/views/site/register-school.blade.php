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
                    <h4 style="color:#940000;font-weight:700;margin-bottom:8px;">School Registration | ShuleXpert</h4>
                    <p class="mb-2" style="color:#555;">
                        Fill in this form to apply to join ShuleXpert. After submission, your application will remain
                        <strong>Pending</strong> until our team verifies it. <strong>You will not receive login credentials</strong>
                        until after verification.
                    </p>
                    <p class="mb-0 small text-muted">Steps: 1) Fill in your details, 2) Submit Registration, 3) Verify phone with OTP, 4) Application is saved</p>
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
                            <input type="text" name="registration_number" id="registration_number" class="form-control" value="{{ old('registration_number') }}" placeholder="e.g. S927/123" autocomplete="off">
                            <small id="registration_numberFeedback" class="d-block mt-1" style="min-height:18px;"></small>
                            @error('registration_number')<small class="text-danger">{{ $message }}</small>@enderror
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
                            <select name="region" id="region" class="form-control" required data-old="{{ old('region') }}">
                                <option value="">Select region</option>
                            </select>
                            <small id="regionLoadHint" class="text-muted d-block mt-1">Loading regions...</small>
                            @error('region')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">District <span class="text-danger">*</span></label>
                            <select name="district" id="district" class="form-control" required disabled data-old="{{ old('district') }}">
                                <option value="">Select region first</option>
                            </select>
                            @error('district')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Ward</label>
                            <select name="ward" id="ward" class="form-control" disabled data-old="{{ old('ward') }}">
                                <option value="">Select district first</option>
                            </select>
                            @error('ward')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" autocomplete="email">
                            <small id="emailFeedback" class="d-block mt-1" style="min-height:18px;"></small>
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', '255') }}" required placeholder="2557XXXXXXXX" maxlength="12" autocomplete="tel">
                            <small id="phoneFeedback" class="d-block mt-1" style="min-height:18px;"></small>
                            @error('phone')<small class="text-danger d-block">{{ $message }}</small>@enderror
                            <small class="text-muted">An OTP will be sent after you click Submit Registration.</small>
                        </div>
                        <div class="col-12 form-group mb-3">
                            <label class="font-weight-bold">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-12 form-group mb-3">
                            <label class="font-weight-bold">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional message to the ShuleXpert team">{{ old('notes') }}</textarea>
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
                        <small class="text-muted" id="verifyHint">Fill in the form, then click Submit Registration. An OTP will be sent to your phone.</small>
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
    var regionSelect = document.getElementById('region');
    var districtSelect = document.getElementById('district');
    var wardSelect = document.getElementById('ward');
    var regionLoadHint = document.getElementById('regionLoadHint');
    var TZ_LOCATIONS_API = 'https://tz-locations-api.mwemanoor.workers.dev/v1';
    var oldRegion = regionSelect ? (regionSelect.getAttribute('data-old') || '') : '';
    var oldDistrict = districtSelect ? (districtSelect.getAttribute('data-old') || '') : '';
    var oldWard = wardSelect ? (wardSelect.getAttribute('data-old') || '') : '';

    function fillSelect(selectEl, items, placeholder, selectedName) {
        selectEl.innerHTML = '';
        var first = document.createElement('option');
        first.value = '';
        first.textContent = placeholder;
        selectEl.appendChild(first);
        (items || []).forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.name;
            opt.textContent = item.name;
            opt.setAttribute('data-slug', item.slug || '');
            if (selectedName && selectedName === item.name) {
                opt.selected = true;
            }
            selectEl.appendChild(opt);
        });
    }

    function selectedSlug(selectEl) {
        var opt = selectEl.options[selectEl.selectedIndex];
        return opt ? (opt.getAttribute('data-slug') || '') : '';
    }

    function resetDistrictWard(districtPlaceholder, wardPlaceholder) {
        fillSelect(districtSelect, [], districtPlaceholder || 'Select region first', '');
        districtSelect.disabled = true;
        fillSelect(wardSelect, [], wardPlaceholder || 'Select district first', '');
        wardSelect.disabled = true;
    }

    function loadRegions() {
        if (!regionSelect) return;
        regionLoadHint.textContent = 'Loading regions...';
        fetch(TZ_LOCATIONS_API + '/regions', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                var rows = (json && json.data) ? json.data : [];
                fillSelect(regionSelect, rows, 'Select region', oldRegion);
                regionLoadHint.textContent = 'Source: Tanzania locations API';
                if (oldRegion) {
                    var slug = selectedSlug(regionSelect);
                    if (slug) {
                        loadDistricts(slug, oldDistrict, oldWard);
                    }
                }
            })
            .catch(function () {
                regionLoadHint.textContent = 'Could not load regions. Refresh and try again.';
                regionLoadHint.style.color = '#940000';
            });
    }

    function loadDistricts(regionSlug, preferDistrict, preferWard) {
        if (!regionSlug) {
            resetDistrictWard();
            return;
        }
        districtSelect.disabled = true;
        fillSelect(districtSelect, [], 'Loading districts...', '');
        fillSelect(wardSelect, [], 'Select district first', '');
        wardSelect.disabled = true;

        fetch(TZ_LOCATIONS_API + '/regions/' + encodeURIComponent(regionSlug) + '/districts', {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                var rows = (json && json.data) ? json.data : [];
                fillSelect(districtSelect, rows, 'Select district', preferDistrict || '');
                districtSelect.disabled = false;
                if (preferDistrict) {
                    var dSlug = selectedSlug(districtSelect);
                    if (dSlug) loadWards(dSlug, preferWard || '');
                }
            })
            .catch(function () {
                fillSelect(districtSelect, [], 'Failed to load districts', '');
                districtSelect.disabled = false;
            });
    }

    function loadWards(districtSlug, preferWard) {
        if (!districtSlug) {
            fillSelect(wardSelect, [], 'Select district first', '');
            wardSelect.disabled = true;
            return;
        }
        wardSelect.disabled = true;
        fillSelect(wardSelect, [], 'Loading wards...', '');

        fetch(TZ_LOCATIONS_API + '/districts/' + encodeURIComponent(districtSlug) + '/wards', {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                var rows = (json && json.data) ? json.data : [];
                fillSelect(wardSelect, rows, 'Select ward (optional)', preferWard || '');
                wardSelect.disabled = false;
            })
            .catch(function () {
                fillSelect(wardSelect, [], 'Failed to load wards', '');
                wardSelect.disabled = false;
            });
    }

    if (regionSelect) {
        regionSelect.addEventListener('change', function () {
            oldDistrict = '';
            oldWard = '';
            loadDistricts(selectedSlug(regionSelect), '', '');
        });
    }
    if (districtSelect) {
        districtSelect.addEventListener('change', function () {
            oldWard = '';
            loadWards(selectedSlug(districtSelect), '');
        });
    }
    loadRegions();

    var verified = false;
    var verifiedPhone = '';
    var otpSent = false;
    var sendingOtp = false;
    var allowingSubmit = false;
    var fieldAvailability = {
        phone: true,
        email: true,
        registration_number: true
    };
    var checkTimers = {};
    var checkControllers = {};

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
    function setFieldFeedback(field, message, state) {
        var el = document.getElementById(field + 'Feedback');
        var input = document.getElementById(field === 'registration_number' ? 'registration_number' : field);
        if (!el) return;
        el.textContent = message || '';
        if (state === 'error') {
            el.style.color = '#940000';
            if (input) input.style.borderColor = '#940000';
        } else if (state === 'ok') {
            el.style.color = '#1a7f37';
            if (input) input.style.borderColor = '#1a7f37';
        } else {
            el.style.color = '#666';
            if (input) input.style.borderColor = '';
        }
    }
    function anyFieldTaken() {
        return !fieldAvailability.phone || !fieldAvailability.email || !fieldAvailability.registration_number;
    }
    function checkFieldAvailability(field, value) {
        if (checkTimers[field]) clearTimeout(checkTimers[field]);
        checkTimers[field] = setTimeout(function () {
            var trimmed = String(value || '').trim();
            if (trimmed === '' || (field === 'phone' && (trimmed === '255' || trimmed.length < 12))) {
                fieldAvailability[field] = true;
                setFieldFeedback(field, '', 'neutral');
                return;
            }
            if (field === 'email' && trimmed.indexOf('@') === -1) {
                fieldAvailability[field] = true;
                setFieldFeedback(field, '', 'neutral');
                return;
            }
            if (field === 'registration_number' && trimmed.length < 2) {
                fieldAvailability[field] = true;
                setFieldFeedback(field, '', 'neutral');
                return;
            }

            setFieldFeedback(field, 'Checking...', 'neutral');
            if (checkControllers[field]) {
                try { checkControllers[field].abort(); } catch (e) {}
            }
            checkControllers[field] = typeof AbortController !== 'undefined' ? new AbortController() : null;

            var payload = { field: field, value: field === 'phone' ? normalizePhone(trimmed) : trimmed };
            fetch('{{ route('site.register_school.check_field') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                signal: checkControllers[field] ? checkControllers[field].signal : undefined
            })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                if (!res.body || !res.body.success) {
                    fieldAvailability[field] = true;
                    setFieldFeedback(field, '', 'neutral');
                    return;
                }
                if (res.body.incomplete) {
                    fieldAvailability[field] = true;
                    setFieldFeedback(field, '', 'neutral');
                    return;
                }
                if (res.body.available === false) {
                    fieldAvailability[field] = false;
                    setFieldFeedback(field, res.body.message || 'Already taken.', 'error');
                    return;
                }
                fieldAvailability[field] = true;
                setFieldFeedback(field, res.body.message || 'Available.', 'ok');
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') return;
                fieldAvailability[field] = true;
                setFieldFeedback(field, '', 'neutral');
            });
        }, 450);
    }

    var regInput = document.getElementById('registration_number');
    var emailInput = document.getElementById('email');
    if (regInput) {
        regInput.addEventListener('input', function () {
            checkFieldAvailability('registration_number', regInput.value);
        });
        if (regInput.value) checkFieldAvailability('registration_number', regInput.value);
    }
    if (emailInput) {
        emailInput.addEventListener('input', function () {
            checkFieldAvailability('email', emailInput.value);
        });
        if (emailInput.value) checkFieldAvailability('email', emailInput.value);
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
        verifyHint.textContent = 'Fill in the form, then click Submit Registration. An OTP will be sent to your phone.';
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
        checkFieldAvailability('phone', phoneInput.value);
    });
    if (phoneInput.value && phoneInput.value !== '255') {
        checkFieldAvailability('phone', phoneInput.value);
    }

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
        if (anyFieldTaken()) {
            setStatus('Fix already-taken fields (phone, email, or registration number) before continuing.', false);
            otpStep.style.display = 'block';
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
                var msg = (res.body && res.body.message) ? res.body.message : 'Failed to send OTP.';
                setStatus(msg, false);
                if (res.body && res.body.available === false) {
                    fieldAvailability.phone = false;
                    setFieldFeedback('phone', msg, 'error');
                }
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
        if (anyFieldTaken()) {
            verifyHint.textContent = 'Phone, email, or registration number is already taken. Please change it.';
            verifyHint.style.color = '#940000';
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

        if (anyFieldTaken()) {
            setStatus('Fix already-taken fields before saving.', false);
            return;
        }
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
            verifyHint.textContent = 'Phone verified. Saving your application...';
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
