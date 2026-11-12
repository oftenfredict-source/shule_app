@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-reg-page-banner" id="home" style="background-image:url('{{ asset('images/modules-school-hero.jpg') }}')">
    <div class="sx-reg-page-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ __('site.register_school') }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('site.register_school') }}">{{ __('site.register_school') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-reg-page-wrap">
    <div class="container sx-reg-block">
        <div class="sx-reg-intro-lead text-center">
            <p class="mb-0">{{ __('site.register_intro') }}</p>
        </div>
    </div>

    <div class="container sx-reg-block sx-reg-block--last">
        @if(session('success'))
            <div class="sx-reg-alert">{{ session('success') }}</div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="sx-reg-form-card">
                    <div class="sx-reg-form-card__intro">
                        <p class="sx-reg-kicker">{{ __('site.register_form_kicker') }}</p>
                        <h2 class="sx-reg-heading">{{ __('site.register_form_title') }}</h2>
                        <p class="sx-reg-text">{{ __('site.register_form_desc') }}</p>
                        <p class="sx-reg-steps mb-0">{{ __('site.register_steps') }}</p>
                    </div>

                <form id="schoolRegForm" class="sx-reg-form" method="POST" action="{{ route('site.register_school.submit') }}">
                    @csrf
                    <input type="hidden" name="otp_token" id="otp_token" value="">

                    <h6 class="sx-reg-section-title">{{ __('site.register_basic') }}</h6>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_school_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="school_name" class="form-control" value="{{ old('school_name') }}" required>
                            @error('school_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_reg_no') }}</label>
                            <input type="text" name="registration_number" id="registration_number" class="form-control" value="{{ old('registration_number') }}" placeholder="e.g. S927/123" autocomplete="off">
                            <small id="registration_numberFeedback" class="d-block mt-1 sx-reg-feedback"></small>
                            @error('registration_number')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_exam_center') }}</label>
                            <input type="text" name="exam_center_number" class="form-control" value="{{ old('exam_center_number') }}">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_level') }} <span class="text-danger">*</span></label>
                            <select name="school_type" class="form-control" required>
                                <option value="">{{ __('site.register_select') }}</option>
                                <option value="Primary" {{ old('school_type') === 'Primary' ? 'selected' : '' }}>Primary</option>
                                <option value="Secondary" {{ old('school_type') === 'Secondary' ? 'selected' : '' }}>Secondary</option>
                            </select>
                            @error('school_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_ownership') }} <span class="text-danger">*</span></label>
                            <select name="ownership" class="form-control" required>
                                <option value="">{{ __('site.register_select') }}</option>
                                <option value="Public" {{ old('ownership') === 'Public' ? 'selected' : '' }}>{{ __('site.register_public') }}</option>
                                <option value="Private" {{ old('ownership') === 'Private' ? 'selected' : '' }}>{{ __('site.register_private') }}</option>
                            </select>
                            @error('ownership')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_established') }}</label>
                            <input type="text" name="established_year" class="form-control" value="{{ old('established_year') }}" placeholder="e.g. 1998">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_students') }} <span class="text-danger">*</span></label>
                            <input type="number" name="number_of_students" class="form-control" value="{{ old('number_of_students') }}" min="1" required>
                            @error('number_of_students')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_contact_person') }}</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}" placeholder="Headmaster / Admin name">
                        </div>
                    </div>

                    <h6 class="sx-reg-section-title sx-reg-section-title--spaced">{{ __('site.register_location') }}</h6>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_region') }} <span class="text-danger">*</span></label>
                            <select name="region" id="region" class="form-control" required data-old="{{ old('region') }}">
                                <option value="">Select region</option>
                            </select>
                            <small id="regionLoadHint" class="sx-reg-muted d-block mt-1">Loading regions...</small>
                            @error('region')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_district') }} <span class="text-danger">*</span></label>
                            <select name="district" id="district" class="form-control" required disabled data-old="{{ old('district') }}">
                                <option value="">Select region first</option>
                            </select>
                            @error('district')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_ward') }}</label>
                            <select name="ward" id="ward" class="form-control" disabled data-old="{{ old('ward') }}">
                                <option value="">Select district first</option>
                            </select>
                            @error('ward')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_email') }}</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" autocomplete="email">
                            <small id="emailFeedback" class="d-block mt-1 sx-reg-feedback"></small>
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_phone') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', '255') }}" required placeholder="2557XXXXXXXX" maxlength="12" autocomplete="tel">
                            <small id="phoneFeedback" class="d-block mt-1 sx-reg-feedback"></small>
                            @error('phone')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_address') }}</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-12 form-group mb-3">
                            <label class="sx-reg-label">{{ __('site.register_notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional message to the ShuleXpert team">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- OTP step: shown after Submit, before save --}}
                    <div id="otpStep" class="sx-reg-otp" style="display:none;">
                        <h6 class="sx-reg-otp__title">{{ __('site.register_otp_title') }}</h6>
                        <p class="mb-2 small" id="otpIntro">{{ __('site.register_otp_intro') }}</p>
                        <div class="d-flex flex-wrap sx-reg-otp__row">
                            <input type="text" id="otpInput" class="form-control" placeholder="{{ __('site.register_otp_ph') }}" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
                            <button type="button" class="btn btn-dark sx-reg-btn-secondary" id="btnVerifyOtp">{{ __('site.register_verify_save') }}</button>
                            <button type="button" class="btn btn-outline-secondary sx-reg-btn-ghost" id="btnResendOtp">{{ __('site.register_resend') }}</button>
                        </div>
                        <small id="otpStatus" class="d-block mt-2 sx-reg-muted"></small>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 sx-reg-actions">
                        <small class="sx-reg-muted" id="verifyHint">{{ __('site.register_hint') }}</small>
                        <button type="submit" class="btn btn-lg sx-reg-submit" id="btnSubmit">
                            {{ __('site.register_submit') }}
                        </button>
                    </div>
                </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sx-reg-side-cta">
                    <h3>{{ __('site.register_aside_title') }}</h3>
                    <p>{{ __('site.register_aside_text') }}</p>
                    <ul>
                        <li><i class="fa fa-check"></i> {{ __('site.register_aside_point1') }}</li>
                        <li><i class="fa fa-check"></i> {{ __('site.register_aside_point2') }}</li>
                        <li><i class="fa fa-check"></i> {{ __('site.register_aside_point3') }}</li>
                    </ul>
                    <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-reg-side-cta__link">
                        <i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}
                    </a>
                    <a href="mailto:emca@emcatech.co.tz" class="sx-reg-side-cta__link">
                        <i class="fa fa-envelope"></i> emca@emcatech.co.tz
                    </a>
                    <a href="{{ route('pricing') }}" class="btn btn-light btn-block mt-3">{{ __('about.view_pricing') }}</a>
                </div>
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
                regionLoadHint.textContent = '';
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
        verifyHint.textContent = @json(__('site.register_hint'));
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

@push('styles')
<style>
.sx-public-site .banner-area.sx-reg-page-banner.about-banner,
.sx-reg-page-banner.about-banner {
    min-height: 300px !important;
    height: 300px !important;
    padding: 0 !important;
    position: relative;
    overflow: hidden;
    background-color: #5c0000 !important;
    background-size: cover !important;
    background-position: center 35% !important;
    background-repeat: no-repeat !important;
    display: flex;
    align-items: center;
}
.sx-reg-page-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(92,0,0,.62) 0%, rgba(148,0,0,.46) 55%, rgba(26,10,10,.38) 100%);
    z-index: 1;
}
.sx-reg-page-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-reg-page-banner .about-content h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-reg-page-banner .link-nav a { color: #fff !important; }

.sx-reg-page-wrap { padding-bottom: 20px; }
.sx-reg-page-wrap > .container.sx-reg-block {
    padding-top: 40px !important;
    padding-bottom: 0 !important;
}
.sx-reg-page-wrap > .container.sx-reg-block.sx-reg-block--last {
    padding-bottom: 30px !important;
}
@media (max-width: 991.98px) {
    .sx-reg-page-wrap > .container.sx-reg-block.sx-reg-block--last {
        padding-bottom: 70px !important;
    }
    .sx-reg-page-banner .about-content h1 { font-size: 26px; }
}

.sx-reg-intro-lead {
    max-width: 760px;
    margin: 0 auto;
    background: #fff8f8;
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    padding: 20px 26px;
    color: #444;
    font-size: 15.5px;
    line-height: 1.7;
}
.sx-reg-alert {
    background: #edf7f0;
    border: 1px solid #b7e0c2;
    border-left: 4px solid #1a7f37;
    border-radius: 10px;
    padding: 12px 14px;
    color: #1a5c2e;
    margin-bottom: 18px;
}
.sx-reg-form-card {
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    background: #fff;
    padding: 24px 22px;
    box-shadow: 0 8px 22px rgba(148,0,0,.05);
}
.sx-reg-form-card__intro { margin-bottom: 20px; }
.sx-reg-kicker {
    color: #940000;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.sx-reg-heading {
    color: #1a1a1a;
    font-weight: 800;
    font-size: 24px;
    margin: 0 0 10px;
}
.sx-reg-text {
    color: #555;
    font-size: 14.5px;
    line-height: 1.65;
    margin-bottom: 8px;
}
.sx-reg-steps {
    color: #777;
    font-size: 13px;
}
.sx-reg-section-title {
    color: #940000;
    font-weight: 800;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: .04em;
    border-bottom: 1px solid #f0e4e4;
    padding-bottom: 8px;
    margin-bottom: 16px;
}
.sx-reg-section-title--spaced { margin-top: 22px; }
.sx-reg-label {
    display: block;
    color: #333;
    font-weight: 700;
    font-size: 13px;
    margin-bottom: 6px;
}
.sx-reg-form .form-control {
    border-radius: 10px;
    border-color: #e8d5d5;
    min-height: 42px;
}
.sx-reg-form textarea.form-control { min-height: 72px; }
.sx-reg-muted { color: #777 !important; }
.sx-reg-feedback { min-height: 18px; }
.sx-reg-otp {
    margin: 16px 0;
    padding: 16px;
    border: 1px solid #f0dada;
    background: #fff8f8;
    border-radius: 12px;
}
.sx-reg-otp__title {
    color: #940000;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-reg-otp p { color: #555; }
.sx-reg-otp__row { gap: 8px; max-width: 480px; }
.sx-reg-btn-secondary,
.sx-reg-btn-ghost {
    border-radius: 10px !important;
    white-space: nowrap;
}
.sx-reg-actions { gap: 12px; }
.sx-reg-submit {
    background: #940000 !important;
    color: #fff !important;
    border-radius: 10px !important;
    font-weight: 800 !important;
    border: none !important;
    padding: 10px 18px !important;
}

.sx-reg-side-cta {
    background: #940000;
    color: #fff;
    border-radius: 14px;
    padding: 22px 18px;
    position: sticky;
    top: 90px;
}
.sx-reg-side-cta h3 {
    color: #fff;
    font-size: 18px;
    font-weight: 800;
    margin: 0 0 10px;
}
.sx-reg-side-cta > p {
    color: #ffe3e3;
    font-size: 13.5px;
    line-height: 1.55;
    margin-bottom: 14px;
}
.sx-reg-side-cta ul {
    list-style: none;
    margin: 0 0 16px;
    padding: 0;
}
.sx-reg-side-cta li {
    color: #fff;
    font-size: 13.5px;
    font-weight: 600;
    margin-bottom: 8px;
}
.sx-reg-side-cta li .fa {
    margin-right: 6px;
    color: #ffd6d6;
}
.sx-reg-side-cta__link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffe3e3 !important;
    text-decoration: none !important;
    font-weight: 600;
    font-size: 13.5px;
    margin-bottom: 8px;
}
.sx-reg-side-cta .btn-light {
    background: #fff !important;
    color: #940000 !important;
    border: 0 !important;
    font-weight: 800;
}

html[data-sx-theme="dark"] .sx-reg-intro-lead,
html[data-sx-theme="dark"] .sx-reg-form-card {
    background: #1f1418 !important;
    border-color: rgba(255,180,180,0.2) !important;
    color: #f3e9eb !important;
}
html[data-sx-theme="dark"] .sx-reg-heading,
html[data-sx-theme="dark"] .sx-reg-section-title,
html[data-sx-theme="dark"] .sx-reg-otp__title,
html[data-sx-theme="dark"] .sx-reg-kicker {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-reg-text,
html[data-sx-theme="dark"] .sx-reg-steps,
html[data-sx-theme="dark"] .sx-reg-label,
html[data-sx-theme="dark"] .sx-reg-muted,
html[data-sx-theme="dark"] #verifyHint,
html[data-sx-theme="dark"] #otpStatus,
html[data-sx-theme="dark"] #regionLoadHint {
    color: #f3e9eb !important;
}
html[data-sx-theme="dark"] .sx-reg-form .form-control,
html[data-sx-theme="dark"] .sx-reg-otp .form-control {
    background: #2a1a1f !important;
    border-color: rgba(255,180,180,0.22) !important;
    color: #fff !important;
}
html[data-sx-theme="dark"] .sx-reg-otp {
    background: #2a171c !important;
    border-color: rgba(255,180,180,0.28) !important;
}
</style>
@endpush
