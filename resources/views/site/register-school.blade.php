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

                @php
                    $oldPhone = (string) old('phone', '');
                    $oldPhoneDigits = preg_replace('/\D+/', '', $oldPhone);
                @endphp
                <form id="schoolRegForm" class="sx-reg sx-reg-form" method="POST" action="{{ route('site.register_school.submit') }}">
                    @csrf
                    <input type="hidden" name="otp_token" id="otp_token" value="">

                    <h2 class="sx-reg-section-title">{{ __('site.register_basic') }}</h2>
                    <div class="row g-3">
                        <div class="col-md-6 col-xl-5">
                            <label class="form-label required" for="school_name">{{ __('site.register_school_name') }}</label>
                            <input type="text" id="school_name" name="school_name" class="form-control @error('school_name') is-invalid @enderror" value="{{ old('school_name') }}" required>
                            @error('school_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <label class="form-label" for="registration_number">{{ __('site.register_reg_no') }}</label>
                            <input type="text" id="registration_number" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror" value="{{ old('registration_number') }}" placeholder="e.g. S927/123" autocomplete="off">
                            <small id="registration_numberFeedback" class="d-block mt-1 sx-reg-feedback"></small>
                            @error('registration_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <label class="form-label" for="exam_center_number">{{ __('site.register_exam_center') }}</label>
                            <input type="text" id="exam_center_number" name="exam_center_number" class="form-control" value="{{ old('exam_center_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="school_type">{{ __('site.register_level') }}</label>
                            <select id="school_type" name="school_type" class="form-select @error('school_type') is-invalid @enderror" required>
                                <option value="" disabled {{ old('school_type') ? '' : 'selected' }}>{{ __('site.register_select') }}</option>
                                <option value="Primary" {{ old('school_type') === 'Primary' ? 'selected' : '' }}>Primary</option>
                                <option value="Secondary" {{ old('school_type') === 'Secondary' ? 'selected' : '' }}>Secondary</option>
                            </select>
                            @error('school_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="ownership">{{ __('site.register_ownership') }}</label>
                            <select id="ownership" name="ownership" class="form-select @error('ownership') is-invalid @enderror" required>
                                <option value="" disabled {{ old('ownership') ? '' : 'selected' }}>{{ __('site.register_select') }}</option>
                                <option value="Public" {{ old('ownership') === 'Public' ? 'selected' : '' }}>{{ __('site.register_public') }}</option>
                                <option value="Private" {{ old('ownership') === 'Private' ? 'selected' : '' }}>{{ __('site.register_private') }}</option>
                            </select>
                            @error('ownership')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="established_year">{{ __('site.register_established') }}</label>
                            <select id="established_year" name="established_year" class="form-select">
                                <option value="">{{ __('site.register_select') }}</option>
                                @for ($y = (int) date('Y'); $y >= 1900; $y--)
                                    <option value="{{ $y }}" {{ (string) old('established_year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="number_of_students">{{ __('site.register_students') }}</label>
                            <input type="number" id="number_of_students" name="number_of_students" class="form-control @error('number_of_students') is-invalid @enderror" value="{{ old('number_of_students') }}" min="1" required>
                            @error('number_of_students')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="contact_person">{{ __('site.register_contact_person') }}</label>
                            <input type="text" id="contact_person" name="contact_person" class="form-control" value="{{ old('contact_person') }}" placeholder="Headmaster / Admin name">
                        </div>
                    </div>

                    <h2 class="sx-reg-section-title sx-reg-section-title--spaced">{{ __('site.register_location') }}</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required" for="region">{{ __('site.register_region') }}</label>
                            <select name="region" id="region" class="form-select @error('region') is-invalid @enderror" required data-old="{{ old('region') }}">
                                <option value="">Select region</option>
                            </select>
                            <div class="help" id="regionLoadHint">Loading regions...</div>
                            @error('region')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="district">{{ __('site.register_district') }}</label>
                            <select name="district" id="district" class="form-select @error('district') is-invalid @enderror" required disabled data-old="{{ old('district') }}">
                                <option value="">Select region first</option>
                            </select>
                            @error('district')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="ward">{{ __('site.register_ward') }}</label>
                            <select name="ward" id="ward" class="form-select" disabled data-old="{{ old('ward') }}">
                                <option value="">Select district first</option>
                            </select>
                            @error('ward')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">{{ __('site.register_email') }}</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email">
                            <small id="emailFeedback" class="d-block mt-1 sx-reg-feedback"></small>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="phone_local">{{ __('site.register_phone') }}</label>
                            <div class="sx-phone" id="phonePicker" data-old-phone="{{ $oldPhoneDigits }}">
                                <div class="sx-phone-cc">
                                    <button type="button" class="sx-phone-cc-btn" id="phoneCcBtn" aria-haspopup="listbox" aria-expanded="false">
                                        <img class="sx-phone-flag" id="phoneFlag" src="https://flagcdn.com/w40/tz.png" width="22" height="15" alt="">
                                        <span class="sx-phone-dial" id="phoneDialLabel">+255</span>
                                        <span class="sx-phone-cc-caret">▾</span>
                                    </button>
                                    <div class="sx-phone-menu" id="phoneCcMenu" role="listbox"></div>
                                </div>
                                <input type="tel" id="phone_local" class="form-control sx-phone-local" inputmode="numeric" autocomplete="tel-national" placeholder="7XXXXXXXX" maxlength="15" required>
                                <input type="hidden" id="phone" name="phone" value="{{ $oldPhoneDigits }}">
                            </div>
                            <small id="phoneFeedback" class="d-block mt-1 sx-reg-feedback"></small>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="address">{{ __('site.register_address') }}</label>
                            <textarea id="address" name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="notes">{{ __('site.register_notes') }}</label>
                            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Optional message to the ShuleXpert team">{{ old('notes') }}</textarea>
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
    var phoneLocal = document.getElementById('phone_local');
    var phoneCcBtn = document.getElementById('phoneCcBtn');
    var phoneCcMenu = document.getElementById('phoneCcMenu');
    var phoneFlag = document.getElementById('phoneFlag');
    var phoneDialLabel = document.getElementById('phoneDialLabel');
    var phonePicker = document.getElementById('phonePicker');
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

    var countries = [
        { iso: 'tz', name: 'Tanzania', dial: '255' },
        { iso: 'ke', name: 'Kenya', dial: '254' },
        { iso: 'ug', name: 'Uganda', dial: '256' },
        { iso: 'rw', name: 'Rwanda', dial: '250' },
        { iso: 'bi', name: 'Burundi', dial: '257' },
        { iso: 'zm', name: 'Zambia', dial: '260' },
        { iso: 'mw', name: 'Malawi', dial: '265' },
        { iso: 'mz', name: 'Mozambique', dial: '258' },
        { iso: 'cd', name: 'DR Congo', dial: '243' },
        { iso: 'ss', name: 'South Sudan', dial: '211' },
        { iso: 'et', name: 'Ethiopia', dial: '251' },
        { iso: 'so', name: 'Somalia', dial: '252' },
        { iso: 'za', name: 'South Africa', dial: '27' },
        { iso: 'ae', name: 'United Arab Emirates', dial: '971' },
        { iso: 'sa', name: 'Saudi Arabia', dial: '966' },
        { iso: 'in', name: 'India', dial: '91' },
        { iso: 'gb', name: 'United Kingdom', dial: '44' },
        { iso: 'us', name: 'United States', dial: '1' },
        { iso: 'cn', name: 'China', dial: '86' }
    ];
    var selectedCountry = countries[0];

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
    function syncPhoneHidden() {
        var local = digitsOnly(phoneLocal.value).replace(/^0+/, '');
        var full = local ? (selectedCountry.dial + local) : '';
        phoneInput.value = full.slice(0, 20);
        return phoneInput.value;
    }
    function normalizePhone(v) {
        var d = digitsOnly(v || phoneInput.value);
        if (!d) return '';
        if (selectedCountry.dial === '255' || d.indexOf('255') === 0) {
            if (d.startsWith('255')) d = d.slice(3);
            if (d.startsWith('0')) d = d.slice(1);
            return ('255' + d).slice(0, 12);
        }
        return d.slice(0, 20);
    }
    function isValidTzMobile(phone) {
        return /^255[67]\d{8}$/.test(phone);
    }
    function setCountry(country) {
        selectedCountry = country;
        phoneFlag.src = 'https://flagcdn.com/w40/' + country.iso + '.png';
        phoneFlag.alt = country.name;
        phoneDialLabel.textContent = '+' + country.dial;
        Array.from(phoneCcMenu.querySelectorAll('.sx-phone-option')).forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-iso') === country.iso);
        });
        syncPhoneHidden();
    }
    function closePhoneMenu() {
        phoneCcMenu.classList.remove('is-open');
        phoneCcBtn.setAttribute('aria-expanded', 'false');
    }
    function setFieldFeedback(field, message, state) {
        var el = document.getElementById(field + 'Feedback');
        var input = field === 'phone'
            ? phoneLocal
            : document.getElementById(field === 'registration_number' ? 'registration_number' : field);
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
            if (trimmed === '' || (field === 'phone' && (!isValidTzMobile(normalizePhone(trimmed)) || trimmed.length < 12))) {
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

    countries.forEach(function (c) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'sx-phone-option';
        btn.setAttribute('role', 'option');
        btn.setAttribute('data-iso', c.iso);
        btn.innerHTML =
            '<img class="sx-phone-flag" src="https://flagcdn.com/w40/' + c.iso + '.png" width="22" height="15" alt="">' +
            '<span class="sx-phone-option-name">' + c.name + '</span>' +
            '<span class="sx-phone-option-dial">+' + c.dial + '</span>';
        btn.addEventListener('click', function () {
            setCountry(c);
            closePhoneMenu();
            phoneLocal.focus();
            if ((otpSent || verified) && verifiedPhone && phoneInput.value !== verifiedPhone) {
                resetVerification();
            }
            checkFieldAvailability('phone', phoneInput.value);
        });
        phoneCcMenu.appendChild(btn);
    });

    phoneCcBtn.addEventListener('click', function () {
        var open = !phoneCcMenu.classList.contains('is-open');
        phoneCcMenu.classList.toggle('is-open', open);
        phoneCcBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', function (e) {
        if (phonePicker && !phonePicker.contains(e.target)) closePhoneMenu();
    });

    phoneLocal.addEventListener('input', function () {
        phoneLocal.value = digitsOnly(phoneLocal.value).slice(0, 15);
        syncPhoneHidden();
        if ((otpSent || verified) && verifiedPhone && phoneInput.value !== verifiedPhone) {
            resetVerification();
        }
        checkFieldAvailability('phone', phoneInput.value);
    });

    (function initPhoneFromOld() {
        var old = digitsOnly(phonePicker.getAttribute('data-old-phone') || '');
        if (!old) {
            setCountry(countries[0]);
            return;
        }
        var matched = countries
            .slice()
            .sort(function (a, b) { return b.dial.length - a.dial.length; })
            .find(function (c) { return old.indexOf(c.dial) === 0; });
        var country = matched || countries[0];
        setCountry(country);
        phoneLocal.value = old.slice(country.dial.length);
        syncPhoneHidden();
        if (phoneInput.value) checkFieldAvailability('phone', phoneInput.value);
    })();

    function sendOtp() {
        syncPhoneHidden();
        var phone = normalizePhone(phoneInput.value);
        phoneInput.value = phone;
        if (selectedCountry.dial !== '255' || !isValidTzMobile(phone)) {
            otpStep.style.display = 'block';
            setStatus('Enter a valid Tanzania mobile: +255 7XXXXXXXX or +255 6XXXXXXXX', false);
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

        syncPhoneHidden();
        var phone = normalizePhone(phoneInput.value);
        phoneInput.value = phone;
        if (selectedCountry.dial !== '255' || !isValidTzMobile(phone)) {
            otpStep.style.display = 'block';
            setStatus('Enter a valid Tanzania mobile: +255 7XXXXXXXX or +255 6XXXXXXXX', false);
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
        syncPhoneHidden();
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
    margin: 0 0 1rem;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #000;
    border-bottom: 1px solid #eee;
    padding-bottom: 0.65rem;
}
.sx-reg-section-title--spaced { margin-top: 1.5rem; }
.sx-reg .form-label {
    font-weight: 700;
    font-size: 0.78rem;
    color: #222;
    margin-bottom: 0.35rem;
}
.sx-reg .form-label.required::after {
    content: " *";
    color: #940000;
    font-weight: 800;
}
.sx-reg .form-control,
.sx-reg .form-select {
    border-radius: 0.45rem;
    border-color: #d9d9d9;
    min-height: 42px;
    font-size: 0.92rem;
    color: #000;
}
.sx-reg .form-control:focus,
.sx-reg .form-select:focus {
    border-color: #940000;
    box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
}
.sx-reg .help {
    font-size: 0.75rem;
    color: #555;
    margin-top: 0.3rem;
}
.sx-reg-form textarea.form-control { min-height: 72px; }
.sx-reg .sx-phone {
    display: flex;
    gap: 0.5rem;
    align-items: stretch;
}
.sx-reg .sx-phone-cc {
    position: relative;
    flex: 0 0 auto;
    min-width: 118px;
}
.sx-reg .sx-phone-cc-btn {
    width: 100%;
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0 0.65rem;
    border: 1px solid #d9d9d9;
    border-radius: 0.45rem;
    background: #fff;
    color: #000;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
}
.sx-reg .sx-phone-cc-btn:focus {
    border-color: #940000;
    box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    outline: 0;
}
.sx-reg .sx-phone-flag {
    width: 22px;
    height: 15px;
    object-fit: cover;
    border-radius: 2px;
    flex-shrink: 0;
    box-shadow: 0 0 0 1px rgba(0,0,0,0.08);
}
.sx-reg .sx-phone-dial { white-space: nowrap; }
.sx-reg .sx-phone-cc-caret {
    margin-left: auto;
    color: #666;
    font-size: 0.7rem;
}
.sx-reg .sx-phone-menu {
    position: absolute;
    z-index: 30;
    top: calc(100% + 4px);
    left: 0;
    width: 280px;
    max-height: 260px;
    overflow: auto;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 0.45rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    display: none;
    padding: 0.35rem;
}
.sx-reg .sx-phone-menu.is-open { display: block; }
.sx-reg .sx-phone-option {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    border: 0;
    background: transparent;
    padding: 0.45rem 0.55rem;
    border-radius: 0.35rem;
    text-align: left;
    cursor: pointer;
    color: #000;
    font-size: 0.86rem;
}
.sx-reg .sx-phone-option:hover,
.sx-reg .sx-phone-option.is-active { background: #fff5f5; }
.sx-reg .sx-phone-option-name { flex: 1; font-weight: 600; }
.sx-reg .sx-phone-option-dial { color: #555; font-weight: 700; }
.sx-reg .sx-phone-local { flex: 1; min-width: 0; }
@media (max-width: 767.98px) {
    .sx-reg .sx-phone { flex-direction: column; }
    .sx-reg .sx-phone-cc { min-width: 0; width: 100%; }
    .sx-reg .sx-phone-menu { width: 100%; }
}
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
html[data-sx-theme="dark"] .sx-reg-otp__title,
html[data-sx-theme="dark"] .sx-reg-kicker {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-reg .sx-reg-section-title,
html[data-sx-theme="dark"] .sx-reg .form-label,
html[data-sx-theme="dark"] .sx-reg .sx-phone-cc-btn,
html[data-sx-theme="dark"] .sx-reg .sx-phone-option {
    color: #fff !important;
}
html[data-sx-theme="dark"] .sx-reg-text,
html[data-sx-theme="dark"] .sx-reg-steps,
html[data-sx-theme="dark"] .sx-reg-muted,
html[data-sx-theme="dark"] .sx-reg .help,
html[data-sx-theme="dark"] #verifyHint,
html[data-sx-theme="dark"] #otpStatus,
html[data-sx-theme="dark"] #regionLoadHint {
    color: #f3e9eb !important;
}
html[data-sx-theme="dark"] .sx-reg .form-control,
html[data-sx-theme="dark"] .sx-reg .form-select,
html[data-sx-theme="dark"] .sx-reg-otp .form-control {
    background: #1a1215 !important;
    border-color: rgba(255,180,180,0.22) !important;
    color: #fff !important;
}
html[data-sx-theme="dark"] .sx-reg .sx-phone-cc-btn,
html[data-sx-theme="dark"] .sx-reg .sx-phone-menu {
    background: #1c1417 !important;
    border-color: rgba(255,180,180,0.16) !important;
}
html[data-sx-theme="dark"] .sx-reg .sx-phone-option-dial {
    color: #d7c4c8 !important;
}
html[data-sx-theme="dark"] .sx-reg .sx-phone-option:hover,
html[data-sx-theme="dark"] .sx-reg .sx-phone-option.is-active {
    background: rgba(148, 0, 0, 0.22) !important;
}
html[data-sx-theme="dark"] .sx-reg-otp {
    background: #2a171c !important;
    border-color: rgba(255,180,180,0.28) !important;
}
</style>
@endpush
