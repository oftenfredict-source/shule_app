@extends('includes.superadmin_nav')

@section('page_title', 'Register School')
@section('page_breadcrumb', 'Register School')

@section('page_actions')
    <a href="{{ route('superadmin.schools.index') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-building me-1"></i> Registered Schools
    </a>
@endsection

@section('content')
@php
    $oldPhone = (string) old('phone', '');
    $oldPhoneDigits = preg_replace('/\D+/', '', $oldPhone);
@endphp
<style>
    .sx-reg {
        color: #000;
    }
    .sx-reg .sx-reg-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-reg .sx-reg-card .card-body {
        padding: 1.35rem 1.4rem 1.5rem;
    }
    .sx-reg .sx-steps {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.15rem;
        border-bottom: 1px solid #eee;
        overflow-x: auto;
    }
    .sx-reg .sx-step {
        flex: 1;
        min-width: 140px;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.55rem 0.7rem;
        border: 1px solid #e8e8e8;
        background: #fafafa;
        border-radius: 0.45rem;
        color: #555;
    }
    .sx-reg .sx-step-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 800;
        background: #e9e9e9;
        color: #444;
        flex-shrink: 0;
    }
    .sx-reg .sx-step-label {
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        line-height: 1.2;
        text-transform: uppercase;
    }
    .sx-reg .sx-step.is-active {
        border-color: #940000;
        background: #fff5f5;
        color: #000;
    }
    .sx-reg .sx-step.is-active .sx-step-num {
        background: #940000;
        color: #fff;
    }
    .sx-reg .sx-step.is-done {
        border-color: #1a7f37;
        background: #effaf2;
        color: #145c2a;
    }
    .sx-reg .sx-step.is-done .sx-step-num {
        background: #1a7f37;
        color: #fff;
        font-size: 0;
    }
    .sx-reg .sx-step.is-done .sx-step-num::before {
        content: "✓";
        font-size: 0.85rem;
        font-weight: 800;
        line-height: 1;
    }
    .sx-reg .sx-reg-pane {
        display: none;
    }
    .sx-reg .sx-reg-pane.is-active {
        display: block;
    }
    .sx-reg .sx-reg-section-title {
        margin: 0 0 1rem;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #000;
    }
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
    .sx-reg .ssm-wrap .small.text-muted {
        display: none;
    }
    .sx-reg .ssm-wrap .form-check.form-switch {
        margin-bottom: 0.5rem;
    }
    .sx-reg .sx-ssm-panel {
        border: 1px solid #ececec;
        background: #fafafa;
        border-radius: 0.5rem;
        padding: 1rem 1.05rem;
    }
    .sx-reg .sx-ssm-panel .ssm-wrap {
        margin: 0;
    }
    .sx-reg .sx-ssm-panel .ssm-fields {
        display: block !important;
    }
    .sx-reg .sx-ssm-panel .ssm-row {
        border: 1px solid #e6e6e6;
        background: #fff;
        border-radius: 0.5rem;
        padding: 0.9rem 1rem;
        margin-bottom: 0.75rem;
    }
    .sx-reg .sx-ssm-panel .ssm-row:last-child {
        margin-bottom: 0.85rem;
    }
    .sx-reg .sx-ssm-panel .ssm-row label,
    .sx-reg .sx-ssm-panel .ssm-row .small.fw-bold {
        display: block;
        font-weight: 700;
        font-size: 0.78rem;
        color: #222;
        margin-bottom: 0.35rem;
    }
    .sx-reg .sx-ssm-panel .ssm-picker-btn,
    .sx-reg .sx-ssm-panel .ssm-value.form-control,
    .sx-reg .sx-ssm-panel input.ssm-value {
        height: 42px !important;
        min-height: 42px !important;
        max-height: none !important;
        border-radius: 0.45rem !important;
        border-color: #d9d9d9;
        font-size: 0.92rem;
        padding: 0 0.75rem;
    }
    .sx-reg .sx-ssm-panel .ssm-picker-btn:focus,
    .sx-reg .sx-ssm-panel .ssm-value.form-control:focus,
    .sx-reg .sx-ssm-panel input.ssm-value:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-reg .sx-ssm-panel .ssm-picker-menu {
        border-radius: 0.45rem;
        border-color: #e0e0e0;
        overflow: hidden;
    }
    .sx-reg .sx-ssm-panel .ssm-picker-option:hover,
    .sx-reg .sx-ssm-panel .ssm-picker-option.is-selected {
        background: #fff5f5;
    }
    .sx-reg .sx-ssm-panel .ssm-remove {
        min-height: 42px;
        width: 100%;
        border-radius: 0.45rem;
        border: 1px solid #940000 !important;
        background: #fff !important;
        color: #940000 !important;
        font-weight: 700;
    }
    .sx-reg .sx-ssm-panel .ssm-remove i {
        color: #940000 !important;
    }
    .sx-reg .sx-ssm-panel .ssm-remove:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-reg .sx-ssm-panel .ssm-picker-icon-logo {
        background: #fff !important;
        border: 1px solid #e6e6e6;
    }
    .sx-reg .sx-ssm-panel .ssm-add-btn {
        border-radius: 0.45rem;
        border-color: #940000;
        color: #940000;
        font-weight: 700;
        padding: 0.4rem 0.85rem;
    }
    .sx-reg .sx-ssm-panel .ssm-add-btn:hover {
        background: #940000;
        border-color: #940000;
        color: #fff;
    }
    html[data-sx-theme="dark"] .sx-reg .sx-ssm-panel,
    html[data-bs-theme="dark"] .sx-reg .sx-ssm-panel {
        background: #1c1417;
        border-color: rgba(255, 180, 180, 0.16);
    }
    html[data-sx-theme="dark"] .sx-reg .sx-ssm-panel .ssm-row,
    html[data-bs-theme="dark"] .sx-reg .sx-ssm-panel .ssm-row {
        background: #24181c;
        border-color: rgba(255, 180, 180, 0.16);
    }
    html[data-sx-theme="dark"] .sx-reg .sx-ssm-panel .ssm-picker-btn,
    html[data-bs-theme="dark"] .sx-reg .sx-ssm-panel .ssm-picker-btn,
    html[data-sx-theme="dark"] .sx-reg .sx-ssm-panel input.ssm-value,
    html[data-bs-theme="dark"] .sx-reg .sx-ssm-panel input.ssm-value {
        background: #1a1215;
        border-color: rgba(255, 180, 180, 0.22);
        color: #fff;
    }
    html[data-sx-theme="dark"] .sx-reg .sx-ssm-panel .ssm-row label,
    html[data-bs-theme="dark"] .sx-reg .sx-ssm-panel .ssm-row label,
    html[data-sx-theme="dark"] .sx-reg .sx-ssm-panel .ssm-picker-label,
    html[data-bs-theme="dark"] .sx-reg .sx-ssm-panel .ssm-picker-label {
        color: #fff !important;
    }
    .sx-reg .sx-reg-panel {
        border: 1px solid #ececec;
        background: #fafafa;
        border-radius: 0.5rem;
        padding: 1rem;
    }
    .sx-reg .form-check-input:checked {
        background-color: #940000;
        border-color: #940000;
    }
    .sx-reg .form-check-label {
        color: #000;
        font-weight: 700;
        font-size: 0.82rem;
    }
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
    .sx-reg .sx-phone-dial {
        white-space: nowrap;
    }
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
    .sx-reg .sx-phone-menu.is-open {
        display: block;
    }
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
    .sx-reg .sx-phone-option.is-active {
        background: #fff5f5;
    }
    .sx-reg .sx-phone-option-name {
        flex: 1;
        font-weight: 600;
    }
    .sx-reg .sx-phone-option-dial {
        color: #555;
        font-weight: 700;
    }
    .sx-reg .sx-phone-local {
        flex: 1;
        min-width: 0;
    }
    .sx-reg .sx-reg-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
        padding-top: 1.15rem;
        margin-top: 1.25rem;
        border-top: 1px solid #eee;
    }
    .sx-reg .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 700;
        border-radius: 0.45rem;
        padding: 0.55rem 1.25rem;
    }
    .sx-reg .btn-outline-secondary {
        border-radius: 0.45rem;
        font-weight: 700;
        color: #000;
    }
    .sx-reg .sx-review-grid {
        display: grid;
        gap: 1rem;
    }
    .sx-reg .sx-review-block {
        border: 1px solid #ececec;
        background: #fafafa;
        border-radius: 0.5rem;
        padding: 1rem 1.05rem;
    }
    .sx-reg .sx-review-block h3 {
        margin: 0 0 0.75rem;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-reg .sx-review-rows {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem 1rem;
    }
    .sx-reg .sx-review-item dt {
        margin: 0;
        font-size: 0.72rem;
        font-weight: 700;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sx-reg .sx-review-item dd {
        margin: 0.15rem 0 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: #000;
        word-break: break-word;
    }
    .sx-reg .sx-review-item.is-full {
        grid-column: 1 / -1;
    }
    .sx-reg .is-invalid-step {
        border-color: #940000 !important;
    }
    @media (max-width: 767.98px) {
        .sx-reg .sx-review-rows {
            grid-template-columns: 1fr;
        }
        .sx-reg .sx-phone {
            flex-direction: column;
        }
        .sx-reg .sx-phone-cc {
            min-width: 0;
            width: 100%;
        }
        .sx-reg .sx-phone-menu {
            width: 100%;
        }
    }
    html[data-sx-theme="dark"] .sx-reg,
    html[data-bs-theme="dark"] .sx-reg,
    html[data-sx-theme="dark"] .sx-reg .form-label,
    html[data-bs-theme="dark"] .sx-reg .form-label,
    html[data-sx-theme="dark"] .sx-reg .sx-reg-section-title,
    html[data-bs-theme="dark"] .sx-reg .sx-reg-section-title,
    html[data-sx-theme="dark"] .sx-reg .form-check-label,
    html[data-bs-theme="dark"] .sx-reg .form-check-label,
    html[data-sx-theme="dark"] .sx-reg .sx-review-block h3,
    html[data-bs-theme="dark"] .sx-reg .sx-review-block h3,
    html[data-sx-theme="dark"] .sx-reg .sx-review-item dd,
    html[data-bs-theme="dark"] .sx-reg .sx-review-item dd,
    html[data-sx-theme="dark"] .sx-reg .sx-step.is-active,
    html[data-bs-theme="dark"] .sx-reg .sx-step.is-active,
    html[data-sx-theme="dark"] .sx-reg .sx-phone-cc-btn,
    html[data-bs-theme="dark"] .sx-reg .sx-phone-cc-btn,
    html[data-sx-theme="dark"] .sx-reg .sx-phone-option,
    html[data-bs-theme="dark"] .sx-reg .sx-phone-option {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-reg .sx-step.is-done,
    html[data-bs-theme="dark"] .sx-reg .sx-step.is-done {
        color: #9be7b0 !important;
        background: rgba(26, 127, 55, 0.18);
        border-color: #1a7f37;
    }
    html[data-sx-theme="dark"] .sx-reg .sx-reg-card,
    html[data-bs-theme="dark"] .sx-reg .sx-reg-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18);
        border-top-color: #940000;
    }
    html[data-sx-theme="dark"] .sx-reg .sx-reg-panel,
    html[data-bs-theme="dark"] .sx-reg .sx-reg-panel,
    html[data-sx-theme="dark"] .sx-reg .sx-review-block,
    html[data-bs-theme="dark"] .sx-reg .sx-review-block,
    html[data-sx-theme="dark"] .sx-reg .sx-step,
    html[data-bs-theme="dark"] .sx-reg .sx-step,
    html[data-sx-theme="dark"] .sx-reg .sx-phone-menu,
    html[data-bs-theme="dark"] .sx-reg .sx-phone-menu,
    html[data-sx-theme="dark"] .sx-reg .sx-phone-cc-btn,
    html[data-bs-theme="dark"] .sx-reg .sx-phone-cc-btn {
        background: #1c1417;
        border-color: rgba(255, 180, 180, 0.16);
    }
    html[data-sx-theme="dark"] .sx-reg .sx-step.is-active,
    html[data-bs-theme="dark"] .sx-reg .sx-step.is-active {
        background: rgba(148, 0, 0, 0.22);
        border-color: #940000;
    }
    html[data-sx-theme="dark"] .sx-reg .form-control,
    html[data-bs-theme="dark"] .sx-reg .form-control,
    html[data-sx-theme="dark"] .sx-reg .form-select,
    html[data-bs-theme="dark"] .sx-reg .form-select {
        background: #1a1215;
        border-color: rgba(255, 180, 180, 0.22);
        color: #fff;
    }
    html[data-sx-theme="dark"] .sx-reg .help,
    html[data-bs-theme="dark"] .sx-reg .help,
    html[data-sx-theme="dark"] .sx-reg .sx-review-item dt,
    html[data-bs-theme="dark"] .sx-reg .sx-review-item dt,
    html[data-sx-theme="dark"] .sx-reg .sx-phone-option-dial,
    html[data-bs-theme="dark"] .sx-reg .sx-phone-option-dial {
        color: #d7c4c8;
    }
    html[data-sx-theme="dark"] .sx-reg .sx-phone-option:hover,
    html[data-bs-theme="dark"] .sx-reg .sx-phone-option:hover,
    html[data-sx-theme="dark"] .sx-reg .sx-phone-option.is-active,
    html[data-bs-theme="dark"] .sx-reg .sx-phone-option.is-active {
        background: rgba(148, 0, 0, 0.22);
    }
</style>

<div class="sx-reg">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fi fi-rr-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="sx-reg-card">
        <div class="card-body">
            <div class="sx-steps" role="tablist" aria-label="Registration steps">
                <div class="sx-step is-active" data-step-indicator="1">
                    <span class="sx-step-num">1</span>
                    <span class="sx-step-label">Basic Info</span>
                </div>
                <div class="sx-step" data-step-indicator="2">
                    <span class="sx-step-num">2</span>
                    <span class="sx-step-label">Location</span>
                </div>
                <div class="sx-step" data-step-indicator="3">
                    <span class="sx-step-num">3</span>
                    <span class="sx-step-label">System</span>
                </div>
                <div class="sx-step" data-step-indicator="4">
                    <span class="sx-step-num">4</span>
                    <span class="sx-step-label">Review</span>
                </div>
            </div>

            <form id="schoolForm" method="POST" action="{{ route('save_school') }}" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="sx-reg-pane is-active" data-step="1">
                    <h2 class="sx-reg-section-title">Basic Information</h2>
                    <div class="row g-3">
                        <div class="col-md-6 col-xl-5">
                            <label class="form-label required" for="school_name">Official School Name</label>
                            <input type="text" id="school_name" name="school_name" class="form-control @error('school_name') is-invalid @enderror" value="{{ old('school_name') }}" placeholder="Full institutional name" required>
                            @error('school_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <label class="form-label" for="registration_number">Registration Number</label>
                            <input type="text" id="registration_number" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror" value="{{ old('registration_number') }}" placeholder="e.g. S927/123">
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <label class="form-label" for="exam_center_number">Exam Center No.</label>
                            <input type="text" id="exam_center_number" name="exam_center_number" class="form-control @error('exam_center_number') is-invalid @enderror" value="{{ old('exam_center_number') }}" placeholder="e.g. S0101">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="school_type">Education Level</label>
                            <select id="school_type" name="school_type" class="form-select @error('school_type') is-invalid @enderror" required>
                                <option value="" disabled {{ old('school_type') ? '' : 'selected' }}>Select level</option>
                                <option value="Primary" {{ old('school_type') == 'Primary' ? 'selected' : '' }}>Primary Level</option>
                                <option value="Secondary" {{ old('school_type') == 'Secondary' ? 'selected' : '' }}>Secondary Level</option>
                                <option value="Advanced" {{ old('school_type') == 'Advanced' ? 'selected' : '' }}>Advanced Level</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="ownership">Ownership Type</label>
                            <select id="ownership" name="ownership" class="form-select @error('ownership') is-invalid @enderror" required>
                                <option value="" disabled {{ old('ownership') ? '' : 'selected' }}>Select ownership</option>
                                <option value="Public" {{ old('ownership') == 'Public' ? 'selected' : '' }}>Public (Government)</option>
                                <option value="Private" {{ old('ownership') == 'Private' ? 'selected' : '' }}>Private / NGO</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="established_year">Established Year</label>
                            <select id="established_year" name="established_year" class="form-select">
                                <option value="">Select year</option>
                                @for ($y = (int) date('Y'); $y >= 1900; $y--)
                                    <option value="{{ $y }}" {{ (string) old('established_year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="sx-reg-pane" data-step="2">
                    <h2 class="sx-reg-section-title">Location &amp; Contact</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required" for="region">Region</label>
                            <select id="region" name="region" class="form-select" required data-old="{{ old('region') }}">
                                <option value="">Select region</option>
                            </select>
                            <div class="help" id="regionLoadHint">Loading regions...</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="district">District</label>
                            <select id="district" name="district" class="form-select" required disabled data-old="{{ old('district') }}">
                                <option value="">Select region first</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="ward">Ward</label>
                            <select id="ward" name="ward" class="form-select" disabled data-old="{{ old('ward') }}">
                                <option value="">Select district first</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Official Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="contact@school.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone_local">Primary Phone</label>
                            <div class="sx-phone" id="phonePicker" data-old-phone="{{ $oldPhoneDigits }}">
                                <div class="sx-phone-cc">
                                    <button type="button" class="sx-phone-cc-btn" id="phoneCcBtn" aria-haspopup="listbox" aria-expanded="false">
                                        <img class="sx-phone-flag" id="phoneFlag" src="https://flagcdn.com/w40/tz.png" width="22" height="15" alt="">
                                        <span class="sx-phone-dial" id="phoneDialLabel">+255</span>
                                        <span class="sx-phone-cc-caret">▾</span>
                                    </button>
                                    <div class="sx-phone-menu" id="phoneCcMenu" role="listbox"></div>
                                </div>
                                <input type="tel" id="phone_local" class="form-control sx-phone-local" inputmode="numeric" autocomplete="tel-national" placeholder="7XXXXXXXX" maxlength="15">
                                <input type="hidden" id="phone" name="phone" value="{{ $oldPhoneDigits }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="address">Postal Address</label>
                            <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}" placeholder="P.O. Box 1234, ...">
                        </div>
                    </div>
                </div>

                <div class="sx-reg-pane" data-step="3">
                    <h2 class="sx-reg-section-title">System Settings</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="environment">System Environment</label>
                            <select id="environment" name="environment" class="form-select">
                                <option value="Demo" {{ old('environment', 'Demo') == 'Demo' ? 'selected' : '' }}>Demo / Testing</option>
                                <option value="Live" {{ old('environment') == 'Live' ? 'selected' : '' }}>Live / Production</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="senderID">SMS Sender ID</label>
                            <input type="text" id="senderID" name="senderID" class="form-control" value="{{ old('senderID') }}" placeholder="e.g. KILIMANJARO" maxlength="11">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="status">Account Status</label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="Active">Active (Unlocked)</option>
                                <option value="Inactive">Inactive (Locked)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="school_logo">School Brand Logo</label>
                            <input type="file" id="school_logo" name="school_logo" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-4 d-flex align-items-start">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="is_under_organization" name="is_under_organization" value="1" {{ old('is_under_organization') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_under_organization">Is under organization?</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-start">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="reg_ssm_ui_toggle" {{ old('enable_social_media') ? 'checked' : '' }}>
                                <label class="form-check-label" for="reg_ssm_ui_toggle">School Social Media A/C</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-start">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="student_portal_enabled" name="student_portal_enabled" value="1" {{ old('student_portal_enabled') ? 'checked' : '' }}>
                                <label class="form-check-label" for="student_portal_enabled">Enable student portal</label>
                            </div>
                        </div>

                        <div class="col-12" id="organizationFieldsWrap" style="{{ old('is_under_organization') ? '' : 'display:none;' }}">
                            <div class="sx-reg-panel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="organization_name">Organization Name</label>
                                        <input type="text" id="organization_name" name="organization_name" class="form-control" value="{{ old('organization_name') }}" placeholder="e.g. Roman Catholic Diocese">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="organization_logo">Organization Logo</label>
                                        <input type="file" id="organization_logo" name="organization_logo" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12" id="socialMediaFieldsWrap" style="{{ old('enable_social_media') ? '' : 'display:none;' }}">
                            <div class="sx-ssm-panel">
                                @include('SuperAdmin.partials.school_social_media_fields', [
                                    'ssmId' => 'reg_ssm',
                                    'ssmEnabled' => (bool) old('enable_social_media'),
                                    'ssmItems' => old('social_media', []),
                                    'ssmHideToggle' => true,
                                ])
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required" for="login_status">Login Access</label>
                            <select id="login_status" name="login_status" class="form-select" required>
                                <option value="1" selected>Grant Access</option>
                                <option value="0">Deny Access</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="suspension_reason_container" style="display: none;">
                            <label class="form-label required" for="suspension_reason">Suspension Reason</label>
                            <textarea id="suspension_reason" name="suspension_reason" class="form-control" rows="2" placeholder="Shown to users when they try to login."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="two_factor_enabled" name="two_factor_enabled" value="1" {{ old('two_factor_enabled') ? 'checked' : '' }}>
                                <label class="form-check-label" for="two_factor_enabled">Enforce two-factor authentication (2FA)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sx-reg-pane" data-step="4">
                    <h2 class="sx-reg-section-title">Review &amp; Register</h2>

                    <div class="sx-review-grid" id="reviewSummary">
                        <div class="sx-review-block">
                            <h3>Basic Information</h3>
                            <dl class="sx-review-rows">
                                <div class="sx-review-item"><dt>School Name</dt><dd data-review="school_name">—</dd></div>
                                <div class="sx-review-item"><dt>Registration No.</dt><dd data-review="registration_number">—</dd></div>
                                <div class="sx-review-item"><dt>Exam Center</dt><dd data-review="exam_center_number">—</dd></div>
                                <div class="sx-review-item"><dt>Education Level</dt><dd data-review="school_type">—</dd></div>
                                <div class="sx-review-item"><dt>Ownership</dt><dd data-review="ownership">—</dd></div>
                                <div class="sx-review-item"><dt>Established</dt><dd data-review="established_year">—</dd></div>
                            </dl>
                        </div>
                        <div class="sx-review-block">
                            <h3>Location &amp; Contact</h3>
                            <dl class="sx-review-rows">
                                <div class="sx-review-item"><dt>Region</dt><dd data-review="region">—</dd></div>
                                <div class="sx-review-item"><dt>District</dt><dd data-review="district">—</dd></div>
                                <div class="sx-review-item"><dt>Ward</dt><dd data-review="ward">—</dd></div>
                                <div class="sx-review-item"><dt>Email</dt><dd data-review="email">—</dd></div>
                                <div class="sx-review-item"><dt>Phone</dt><dd data-review="phone">—</dd></div>
                                <div class="sx-review-item is-full"><dt>Postal Address</dt><dd data-review="address">—</dd></div>
                            </dl>
                        </div>
                        <div class="sx-review-block">
                            <h3>System Settings</h3>
                            <dl class="sx-review-rows">
                                <div class="sx-review-item"><dt>Environment</dt><dd data-review="environment">—</dd></div>
                                <div class="sx-review-item"><dt>SMS Sender ID</dt><dd data-review="senderID">—</dd></div>
                                <div class="sx-review-item"><dt>Account Status</dt><dd data-review="status">—</dd></div>
                                <div class="sx-review-item"><dt>Login Access</dt><dd data-review="login_status">—</dd></div>
                                <div class="sx-review-item"><dt>School Logo</dt><dd data-review="school_logo">—</dd></div>
                                <div class="sx-review-item"><dt>Under Organization</dt><dd data-review="is_under_organization">—</dd></div>
                                <div class="sx-review-item"><dt>Organization</dt><dd data-review="organization_name">—</dd></div>
                                <div class="sx-review-item"><dt>Student Portal</dt><dd data-review="student_portal_enabled">—</dd></div>
                                <div class="sx-review-item"><dt>2FA</dt><dd data-review="two_factor_enabled">—</dd></div>
                                <div class="sx-review-item is-full"><dt>Suspension Reason</dt><dd data-review="suspension_reason">—</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="sx-reg-actions">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" id="btnPrev" style="display:none;">
                            Back
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="form-check me-2" id="agreeWrap" style="display:none;">
                            <input class="form-check-input" type="checkbox" id="agree" required>
                            <label class="form-check-label" for="agree">I confirm all details are accurate</label>
                        </div>
                        <button type="button" class="btn btn-primary" id="btnNext">
                            Continue
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit" style="display:none;">
                            <i class="fi fi-rr-disk me-1"></i> Register School
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('schoolForm');
        const panes = Array.from(document.querySelectorAll('.sx-reg-pane'));
        const indicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');
        const btnSubmit = document.getElementById('btnSubmit');
        const agreeWrap = document.getElementById('agreeWrap');
        const agree = document.getElementById('agree');
        const totalSteps = panes.length;
        let currentStep = 1;

        const loginStatus = document.getElementById('login_status');
        const reasonContainer = document.getElementById('suspension_reason_container');
        const reasonInput = document.getElementById('suspension_reason');

        function toggleReason() {
            if (!loginStatus || !reasonContainer || !reasonInput) return;
            if (loginStatus.value === '0') {
                reasonContainer.style.display = 'block';
                reasonInput.setAttribute('required', 'required');
            } else {
                reasonContainer.style.display = 'none';
                reasonInput.removeAttribute('required');
                reasonInput.value = '';
            }
        }

        if (loginStatus) {
            loginStatus.addEventListener('change', toggleReason);
            toggleReason();
        }

        const orgToggle = document.getElementById('is_under_organization');
        const orgWrap = document.getElementById('organizationFieldsWrap');
        if (orgToggle && orgWrap) {
            orgToggle.addEventListener('change', function () {
                orgWrap.style.display = this.checked ? '' : 'none';
            });
        }

        const ssmUiToggle = document.getElementById('reg_ssm_ui_toggle');
        const ssmRealToggle = document.getElementById('reg_ssm_enable');
        const ssmPanelWrap = document.getElementById('socialMediaFieldsWrap');
        function syncSocialMediaPanel(checked) {
            if (ssmRealToggle) {
                ssmRealToggle.checked = !!checked;
                ssmRealToggle.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (ssmPanelWrap) {
                ssmPanelWrap.style.display = checked ? '' : 'none';
            }
        }
        if (ssmUiToggle) {
            ssmUiToggle.addEventListener('change', function () {
                syncSocialMediaPanel(this.checked);
            });
            syncSocialMediaPanel(ssmUiToggle.checked);
        }

        /* -------- TZ location cascading selects -------- */
        const regionSelect = document.getElementById('region');
        const districtSelect = document.getElementById('district');
        const wardSelect = document.getElementById('ward');
        const regionLoadHint = document.getElementById('regionLoadHint');
        const TZ_LOCATIONS_API = 'https://tz-locations-api.mwemanoor.workers.dev/v1';
        let oldRegion = regionSelect ? (regionSelect.getAttribute('data-old') || '') : '';
        let oldDistrict = districtSelect ? (districtSelect.getAttribute('data-old') || '') : '';
        let oldWard = wardSelect ? (wardSelect.getAttribute('data-old') || '') : '';

        function fillSelect(selectEl, items, placeholder, selectedName) {
            selectEl.innerHTML = '';
            const first = document.createElement('option');
            first.value = '';
            first.textContent = placeholder;
            selectEl.appendChild(first);
            (items || []).forEach(function (item) {
                const opt = document.createElement('option');
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
            const opt = selectEl.options[selectEl.selectedIndex];
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
            regionLoadHint.style.color = '';
            fetch(TZ_LOCATIONS_API + '/regions', { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    const rows = (json && json.data) ? json.data : [];
                    fillSelect(regionSelect, rows, 'Select region', oldRegion);
                    regionLoadHint.textContent = '';
                    if (oldRegion) {
                        const slug = selectedSlug(regionSelect);
                        if (slug) loadDistricts(slug, oldDistrict, oldWard);
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
                    const rows = (json && json.data) ? json.data : [];
                    fillSelect(districtSelect, rows, 'Select district', preferDistrict || '');
                    districtSelect.disabled = false;
                    if (preferDistrict) {
                        const dSlug = selectedSlug(districtSelect);
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
                    const rows = (json && json.data) ? json.data : [];
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

        /* -------- Country code phone picker -------- */
        const countries = [
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

        const phoneCcBtn = document.getElementById('phoneCcBtn');
        const phoneCcMenu = document.getElementById('phoneCcMenu');
        const phoneFlag = document.getElementById('phoneFlag');
        const phoneDialLabel = document.getElementById('phoneDialLabel');
        const phoneLocal = document.getElementById('phone_local');
        const phoneHidden = document.getElementById('phone');
        const phonePicker = document.getElementById('phonePicker');
        let selectedCountry = countries[0];

        function digitsOnly(v) {
            return String(v || '').replace(/\D+/g, '');
        }

        function syncPhoneHidden() {
            const local = digitsOnly(phoneLocal.value).replace(/^0+/, '');
            const full = local ? (selectedCountry.dial + local) : '';
            phoneHidden.value = full.slice(0, 20);
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

        countries.forEach(function (c) {
            const btn = document.createElement('button');
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
            });
            phoneCcMenu.appendChild(btn);
        });

        phoneCcBtn.addEventListener('click', function () {
            const open = !phoneCcMenu.classList.contains('is-open');
            phoneCcMenu.classList.toggle('is-open', open);
            phoneCcBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!phonePicker.contains(e.target)) closePhoneMenu();
        });

        phoneLocal.addEventListener('input', function () {
            phoneLocal.value = digitsOnly(phoneLocal.value).slice(0, 15);
            syncPhoneHidden();
        });

        (function initPhoneFromOld() {
            const old = digitsOnly(phonePicker.getAttribute('data-old-phone') || '');
            if (!old) {
                setCountry(countries[0]);
                return;
            }
            const matched = countries
                .slice()
                .sort(function (a, b) { return b.dial.length - a.dial.length; })
                .find(function (c) { return old.indexOf(c.dial) === 0; });
            const country = matched || countries[0];
            setCountry(country);
            phoneLocal.value = old.slice(country.dial.length);
            syncPhoneHidden();
        })();

        /* -------- Wizard helpers -------- */
        function val(id) {
            const el = document.getElementById(id);
            if (!el) return '';
            if (el.type === 'checkbox') return el.checked ? 'Yes' : 'No';
            if (el.type === 'file') {
                return el.files && el.files[0] ? el.files[0].name : 'Not uploaded';
            }
            if (el.tagName === 'SELECT') {
                const opt = el.options[el.selectedIndex];
                return opt && opt.value ? opt.text.trim() : '';
            }
            return (el.value || '').trim();
        }

        function setReview(key, value) {
            const target = document.querySelector('[data-review="' + key + '"]');
            if (target) target.textContent = value || '—';
        }

        function buildReview() {
            setReview('school_name', val('school_name'));
            setReview('registration_number', val('registration_number'));
            setReview('exam_center_number', val('exam_center_number'));
            setReview('school_type', val('school_type'));
            setReview('ownership', val('ownership'));
            setReview('established_year', val('established_year'));
            setReview('region', val('region'));
            setReview('district', val('district'));
            setReview('ward', val('ward'));
            setReview('email', val('email'));
            const phoneVal = phoneHidden.value ? ('+' + phoneHidden.value) : '';
            setReview('phone', phoneVal);
            setReview('address', val('address'));
            setReview('environment', val('environment'));
            setReview('senderID', val('senderID'));
            setReview('status', val('status'));
            setReview('login_status', val('login_status'));
            setReview('school_logo', val('school_logo'));
            setReview('is_under_organization', val('is_under_organization'));
            setReview('organization_name', orgToggle && orgToggle.checked ? val('organization_name') : '—');
            setReview('student_portal_enabled', val('student_portal_enabled'));
            setReview('two_factor_enabled', val('two_factor_enabled'));
            setReview('suspension_reason', loginStatus && loginStatus.value === '0' ? val('suspension_reason') : '—');
        }

        function showStep(step) {
            currentStep = step;
            panes.forEach(function (pane) {
                pane.classList.toggle('is-active', Number(pane.getAttribute('data-step')) === step);
            });
            indicators.forEach(function (item) {
                const n = Number(item.getAttribute('data-step-indicator'));
                item.classList.toggle('is-active', n === step);
                item.classList.toggle('is-done', n < step);
            });

            btnPrev.style.display = step > 1 ? '' : 'none';
            btnNext.style.display = step < totalSteps ? '' : 'none';
            btnSubmit.style.display = step === totalSteps ? '' : 'none';
            agreeWrap.style.display = step === totalSteps ? '' : 'none';

            if (step === totalSteps) {
                syncPhoneHidden();
                buildReview();
            }
        }

        function isFieldVisible(el) {
            if (!el || el.disabled || el.type === 'hidden') return false;
            let node = el;
            while (node && node !== form) {
                const style = window.getComputedStyle(node);
                if (style.display === 'none' || style.visibility === 'hidden') return false;
                node = node.parentElement;
            }
            return true;
        }

        function validateStep(step) {
            const pane = panes.find(function (p) {
                return Number(p.getAttribute('data-step')) === step;
            });
            if (!pane) return true;

            const fields = Array.from(pane.querySelectorAll('input, select, textarea')).filter(function (el) {
                if (el.id === 'phone_local' || el.id === 'phone') return false;
                if (!isFieldVisible(el)) return false;
                return el.hasAttribute('required') || el.type === 'email';
            });

            let ok = true;
            let firstInvalid = null;

            fields.forEach(function (el) {
                el.classList.remove('is-invalid-step');
                let valid = true;

                if (el.hasAttribute('required')) {
                    if (el.type === 'checkbox') {
                        valid = el.checked;
                    } else {
                        valid = String(el.value || '').trim() !== '';
                    }
                }

                if (valid && el.type === 'email' && el.value.trim() !== '') {
                    valid = el.checkValidity();
                }

                if (!valid) {
                    ok = false;
                    el.classList.add('is-invalid-step');
                    if (!firstInvalid) firstInvalid = el;
                }
            });

            if (!ok && firstInvalid) {
                firstInvalid.focus();
            }
            return ok;
        }

        btnNext.addEventListener('click', function () {
            if (!validateStep(currentStep)) return;
            if (currentStep < totalSteps) {
                showStep(currentStep + 1);
            }
        });

        btnPrev.addEventListener('click', function () {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });

        form.addEventListener('submit', function (e) {
            syncPhoneHidden();
            if (currentStep !== totalSteps) {
                e.preventDefault();
                return;
            }
            if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
                e.preventDefault();
                if (!validateStep(1)) showStep(1);
                else if (!validateStep(2)) showStep(2);
                else showStep(3);
                return;
            }
            if (!agree.checked) {
                e.preventDefault();
                agree.focus();
                agree.classList.add('is-invalid-step');
                return;
            }
        });

        showStep(1);
    });
</script>
@endpush
