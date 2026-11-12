@extends('layouts.parent_corp')

@section('title', __('parent.exam_results_title') . ' - ShuleXpert')
@section('page_title', __('parent.student_results'))
@section('page_breadcrumb', __('parent.exam_results_title'))

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results span:not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results .step-label {
        color: #000 !important;
    }

    .sx-parent-results .card,
    .sx-parent-results .panel-card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-results .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-results .btn-primary,
    .sx-parent-results a.btn-primary,
    .sx-parent-results .badge,
    .sx-parent-results .step-item.completed .step-circle,
    .sx-parent-results .selection-card.selected,
    .sx-parent-results .selection-card.selected *,
    .sx-parent-results .selection-card.selected small,
    .sx-parent-results .selection-card.selected strong,
    .sx-parent-results .selection-card.selected i,
    .sx-parent-results .selection-card.selected .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results .selection-card.selected,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results .selection-card.selected *,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results .selection-card.selected small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results .selection-card.selected strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results .selection-card.selected i,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-results .selection-card.selected .text-muted {
        color: #fff !important;
    }
    .sx-parent-results .btn-link {
        font-weight: 600;
        text-decoration: none;
        color: #940000 !important;
    }
    .sx-parent-results .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }

    .sx-parent-results .hint-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        padding: 0.85rem 1rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    /* Step chain */
    .sx-parent-results .step-chain {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1.75rem;
        position: relative;
        padding: 0 12px;
    }
    .sx-parent-results .step-chain::before {
        content: '';
        position: absolute;
        top: 22px;
        left: 56px;
        right: 56px;
        height: 3px;
        background: rgba(148, 0, 0, 0.12);
        z-index: 1;
    }
    .sx-parent-results .step-chain-progress {
        position: absolute;
        top: 22px;
        left: 56px;
        height: 3px;
        background: #940000;
        z-index: 2;
        transition: width 0.35s ease;
        width: 0%;
        max-width: calc(100% - 112px);
    }
    .sx-parent-results .step-item {
        position: relative;
        z-index: 3;
        text-align: center;
        width: 96px;
    }
    .sx-parent-results .step-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e6e6e6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-weight: 800;
        color: #888;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }
    .sx-parent-results .step-item.active .step-circle {
        border-color: #940000;
        color: #940000 !important;
        box-shadow: 0 0 0 4px rgba(148, 0, 0, 0.12);
        transform: scale(1.06);
    }
    .sx-parent-results .step-item.completed .step-circle {
        background: #940000;
        border-color: #940000;
        color: #fff !important;
    }
    .sx-parent-results .step-label {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #777 !important;
    }
    .sx-parent-results .step-item.active .step-label {
        color: #940000 !important;
    }

    /* Selection cards */
    .sx-parent-results .selection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
        margin-top: 0.75rem;
    }
    .sx-parent-results .selection-card {
        border: 1px solid #e6e6e6;
        border-top: 3px solid transparent;
        border-radius: 0;
        padding: 1rem 0.85rem;
        text-align: center;
        cursor: pointer;
        transition: box-shadow 0.15s ease, transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-parent-results .selection-card:hover {
        border-color: #e0b0b0;
        border-top-color: #940000;
        background: #fff8f8;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.1);
    }
    .sx-parent-results .selection-card.selected {
        border-color: #940000;
        border-top-color: #940000;
        background: #940000;
        color: #fff !important;
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.18);
    }
    .sx-parent-results .selection-card.selected .text-muted {
        color: rgba(255, 255, 255, 0.85) !important;
    }
    .sx-parent-results .selection-card i {
        font-size: 1.35rem;
        margin-bottom: 0.45rem;
        display: block;
        color: #940000;
    }
    .sx-parent-results .selection-card.selected i {
        color: #fff !important;
    }
    .sx-parent-results .wizard-step-title {
        font-size: 0.95rem;
        font-weight: 800;
        margin-bottom: 0.35rem;
        letter-spacing: 0.02em;
    }
    .sx-parent-results .page-heading {
        color: #940000 !important;
        font-weight: 800;
        letter-spacing: 0.02em;
    }
    .sx-parent-results .nav-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    /* Results display */
    .sx-parent-results .result-section {
        display: none;
        animation: sxFadeIn 0.4s ease;
    }
    @keyframes sxFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .sx-parent-results .color-def-container {
        text-align: center;
        margin-bottom: 1rem;
    }
    .sx-parent-results .color-def-title {
        font-weight: 800;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.55rem;
        color: #777 !important;
    }
    .sx-parent-results .color-blocks {
        display: inline-flex;
        overflow: hidden;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-results .color-block {
        padding: 0.55rem 1.5rem;
        font-size: 0.85rem;
        font-weight: 700;
    }
    .sx-parent-results .bg-pass { background-color: #d4edda !important; color: #155724 !important; }
    .sx-parent-results .bg-fail { background-color: #f8d7da !important; color: #721c24 !important; }
    .sx-parent-results .bg-inc { background-color: #d1ecf1 !important; color: #0c5460 !important; }

    .sx-parent-results .univ-summary-table {
        width: 100%;
        border: 1px solid #e6e6e6;
        background: #fff;
        margin-bottom: 0;
    }
    .sx-parent-results .univ-summary-header {
        background: #fcf7f7;
        font-weight: 800;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #e6e6e6;
        color: #940000 !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.78rem;
    }
    .sx-parent-results .univ-summary-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #eee;
    }
    .sx-parent-results .univ-summary-table td.label {
        font-weight: 700;
        width: 220px;
        text-align: right;
        border-right: 1px solid #eee;
        color: #666 !important;
    }
    .sx-parent-results .univ-summary-table tr:last-child td {
        border-bottom: none;
    }
    .sx-parent-results .univ-dl-link {
        display: block;
        padding: 0.85rem 1rem;
        border: 1px solid #940000;
        background: #fff;
        color: #940000 !important;
        text-decoration: none;
        font-weight: 800;
        text-align: center;
        margin: 1rem 0 1.25rem;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .sx-parent-results .univ-dl-link:hover {
        background: #940000;
        color: #fff !important;
    }
    .sx-parent-results .univ-section-title {
        font-weight: 800;
        font-size: 1rem;
        margin: 0.5rem 0 0.85rem;
        color: #940000 !important;
    }
    .sx-parent-results .univ-marks-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e6e6e6;
        background: #fff;
    }
    .sx-parent-results .univ-marks-table th {
        background: #fcf7f7;
        color: #940000 !important;
        font-weight: 800;
        padding: 0.85rem 0.75rem;
        text-align: left;
        border-bottom: 1px solid #e6e6e6;
        border-right: 1px solid #eee;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }
    .sx-parent-results .univ-marks-table td {
        padding: 0.85rem 0.75rem;
        border-bottom: 1px solid #eee;
        border-right: 1px solid #eee;
        vertical-align: middle;
    }
    .sx-parent-results .univ-marks-table th.col-small,
    .sx-parent-results .univ-marks-table td.col-small {
        white-space: nowrap;
        text-align: center;
        width: 1%;
    }
    .sx-parent-results .univ-marks-table td.colored-remark {
        text-align: center;
        font-weight: 600;
        padding: 0.55rem 0.35rem !important;
    }
    .sx-parent-results .exam-sub-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .sx-parent-results .exam-sub-item {
        padding: 0.3rem 0;
        border-bottom: 1px solid #eee;
        font-size: 0.85rem;
    }
    .sx-parent-results .exam-sub-item:last-child {
        border-bottom: none;
    }

    @media (max-width: 768px) {
        .sx-parent-results .step-item { width: 70px; }
        .sx-parent-results .step-circle { width: 36px; height: 36px; font-size: 0.8rem; }
        .sx-parent-results .step-label { font-size: 0.62rem; }
        .sx-parent-results .step-chain::before,
        .sx-parent-results .step-chain-progress { top: 18px; left: 36px; right: 36px; }
        .sx-parent-results .step-chain-progress { max-width: calc(100% - 72px); left: 36px; }
        .sx-parent-results .selection-grid { grid-template-columns: 1fr; }
        .sx-parent-results .color-blocks { display: flex; width: 100%; }
        .sx-parent-results .color-block { flex: 1; padding: 0.5rem 0.35rem; font-size: 0.72rem; }
        .sx-parent-results .univ-summary-table td.label { width: 42%; font-size: 0.8rem; }
        .sx-parent-results .univ-marks-table th,
        .sx-parent-results .univ-marks-table td { font-size: 0.72rem; padding: 0.45rem 0.3rem; }
        .sx-parent-results .btn-back-responsive { width: 100%; }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-parent-results .card,
    html[data-bs-theme="dark"] .sx-parent-results .card,
    html[data-sx-theme="dark"] .sx-parent-results .panel-card,
    html[data-bs-theme="dark"] .sx-parent-results .panel-card,
    html[data-sx-theme="dark"] .sx-parent-results .hint-strip,
    html[data-bs-theme="dark"] .sx-parent-results .hint-strip,
    html[data-sx-theme="dark"] .sx-parent-results .selection-card,
    html[data-bs-theme="dark"] .sx-parent-results .selection-card,
    html[data-sx-theme="dark"] .sx-parent-results .univ-summary-table,
    html[data-bs-theme="dark"] .sx-parent-results .univ-summary-table,
    html[data-sx-theme="dark"] .sx-parent-results .univ-marks-table,
    html[data-bs-theme="dark"] .sx-parent-results .univ-marks-table,
    html[data-sx-theme="dark"] .sx-parent-results .step-circle,
    html[data-bs-theme="dark"] .sx-parent-results .step-circle {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-results,
    html[data-bs-theme="dark"] .sx-parent-results,
    html[data-sx-theme="dark"] .sx-parent-results p,
    html[data-bs-theme="dark"] .sx-parent-results p,
    html[data-sx-theme="dark"] .sx-parent-results span:not(.badge),
    html[data-bs-theme="dark"] .sx-parent-results span:not(.badge),
    html[data-sx-theme="dark"] .sx-parent-results small,
    html[data-bs-theme="dark"] .sx-parent-results small,
    html[data-sx-theme="dark"] .sx-parent-results strong,
    html[data-bs-theme="dark"] .sx-parent-results strong,
    html[data-sx-theme="dark"] .sx-parent-results div,
    html[data-bs-theme="dark"] .sx-parent-results div,
    html[data-sx-theme="dark"] .sx-parent-results h4,
    html[data-bs-theme="dark"] .sx-parent-results h4,
    html[data-sx-theme="dark"] .sx-parent-results h5,
    html[data-bs-theme="dark"] .sx-parent-results h5,
    html[data-sx-theme="dark"] .sx-parent-results .text-muted,
    html[data-bs-theme="dark"] .sx-parent-results .text-muted {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-results .page-heading,
    html[data-bs-theme="dark"] .sx-parent-results .page-heading,
    html[data-sx-theme="dark"] .sx-parent-results .univ-section-title,
    html[data-bs-theme="dark"] .sx-parent-results .univ-section-title,
    html[data-sx-theme="dark"] .sx-parent-results .univ-summary-header,
    html[data-bs-theme="dark"] .sx-parent-results .univ-summary-header,
    html[data-sx-theme="dark"] .sx-parent-results .step-item.active .step-label,
    html[data-bs-theme="dark"] .sx-parent-results .step-item.active .step-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-results .univ-summary-header,
    html[data-bs-theme="dark"] .sx-parent-results .univ-summary-header,
    html[data-sx-theme="dark"] .sx-parent-results .univ-marks-table th,
    html[data-bs-theme="dark"] .sx-parent-results .univ-marks-table th {
        background: rgba(148, 0, 0, 0.18) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-results .selection-card:hover,
    html[data-bs-theme="dark"] .sx-parent-results .selection-card:hover {
        background: rgba(255, 180, 180, 0.08) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-results .selection-card.selected,
    html[data-bs-theme="dark"] .sx-parent-results .selection-card.selected {
        background: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-parent-results .univ-dl-link,
    html[data-bs-theme="dark"] .sx-parent-results .univ-dl-link {
        background: transparent !important;
        color: #ffb4b4 !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-results .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-results .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
</style>

<div class="sx-parent-results">
    <div class="row g-3 justify-content-center">
        <div class="col-xl-10">
            <div class="card panel-card">
                <div class="card-body">
                    <div id="backBtnWrapper" class="mb-3" style="display:none;">
                        <button type="button" class="btn btn-outline-light btn-shadow waves-effect btn-back-responsive" id="backToWizardBtn">
                            <i class="fi fi-rr-arrow-left me-1"></i> {{ __('parent.back_wizard') }}
                        </button>
                    </div>

                    <h4 class="mb-3 text-center page-heading font-weight-bold">
                        <i class="fi fi-rr-trophy me-1"></i> {{ __('parent.student_results') }}
                    </h4>

                    <div class="hint-strip">
                        <i class="fi fi-rr-info me-1" style="color:#940000;"></i>
                        {{ __('parent.start_choose_student') }}
                    </div>

                    <div class="step-chain">
                        <div class="step-chain-progress" id="stepProgress"></div>
                        <div class="step-item active" id="step1-tab">
                            <div class="step-circle">1</div>
                            <div class="step-label">{{ __('parent.step_student') }}</div>
                        </div>
                        <div class="step-item" id="step2-tab">
                            <div class="step-circle">2</div>
                            <div class="step-label">{{ __('parent.step_year') }}</div>
                        </div>
                        <div class="step-item" id="step3-tab">
                            <div class="step-circle">3</div>
                            <div class="step-label">{{ __('parent.step_term') }}</div>
                        </div>
                        <div class="step-item" id="step4-tab">
                            <div class="step-circle">4</div>
                            <div class="step-label">{{ __('parent.step_exam') }}</div>
                        </div>
                    </div>

                    <div class="wizard-content">
                        <div class="wizard-step" id="step1">
                            <div class="wizard-step-title">{{ __('parent.step1_student') }}</div>
                            <div class="selection-grid" id="studentGrid">
                                @foreach($students as $student)
                                    <div class="selection-card"
                                         data-value="{{ $student->studentID }}"
                                         data-is-a-level="{{ !empty($student->is_a_level) ? '1' : '0' }}"
                                         data-level-label="{{ $student->level_label ?? '' }}">
                                        <i class="fi fi-rr-user"></i>
                                        <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                        <br><small class="text-muted">{{ $student->admission_number }}</small>
                                        @if(!empty($student->level_label))
                                            <br><small style="color:#940000;font-weight:700;">{{ $student->level_label }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="wizard-step d-none" id="step2">
                            <div class="wizard-step-title">{{ __('parent.step2_year') }}</div>
                            <div class="small text-muted mb-2" id="yearLevelHint"></div>
                            <div class="selection-grid" id="yearGrid">
                                <div class="col-12 text-muted small text-center py-3">{{ __('parent.choose_student_first') }}</div>
                            </div>
                            <div id="noYearsMessage" class="alert alert-warning d-none mt-3 mb-0">
                                {{ __('parent.no_year_for_level') }}
                            </div>
                        </div>

                        <div class="wizard-step d-none" id="step3">
                            <div class="wizard-step-title">{{ __('parent.step3_term') }}</div>
                            <div class="selection-grid" id="termGrid">
                                <div class="selection-card" data-value="first_term">
                                    <i class="fi fi-rr-clock"></i>
                                    <strong>{{ __('parent.first_term') }}</strong>
                                </div>
                                <div class="selection-card" data-value="second_term">
                                    <i class="fi fi-rr-time-past"></i>
                                    <strong>{{ __('parent.second_term') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="wizard-step d-none" id="step4">
                            <div class="wizard-step-title">{{ __('parent.step4_exam') }}</div>
                            <div class="selection-grid" id="examGrid"></div>
                            <div id="noExamsMessage" class="alert alert-warning d-none mt-3 mb-0">
                                {{ __('parent.no_exams_published') }}
                            </div>
                        </div>

                        <div class="nav-buttons">
                            <button type="button" class="btn btn-outline-light btn-shadow waves-effect px-4 d-none" id="prevBtn">
                                <i class="fi fi-rr-angle-left"></i> {{ __('parent.previous') }}
                            </button>
                            <div class="ms-auto d-flex gap-2">
                                <button type="button" class="btn btn-primary waves-effect waves-light px-4 d-none" id="nextBtn">
                                    {{ __('parent.continue') }} <i class="fi fi-rr-angle-right"></i>
                                </button>
                                <button type="button" class="btn btn-primary waves-effect waves-light px-4 d-none" id="viewBtn">
                                    <i class="fi fi-rr-eye"></i> {{ __('parent.view_results') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="resultDisplay" class="result-section mt-3">
                <div class="card panel-card mb-3">
                    <div class="card-body">
                        <div class="color-def-container">
                            <div class="color-def-title">{{ __('parent.color_meaning') }}</div>
                            <div class="color-blocks">
                                <div class="color-block bg-pass">{{ __('parent.pass') }}</div>
                                <div class="color-block bg-fail">{{ __('parent.fail') }}</div>
                                <div class="color-block bg-inc">{{ __('parent.incomplete') }}</div>
                            </div>
                        </div>

                        <div class="univ-summary-table">
                            <div class="univ-summary-header">{{ __('parent.results_summary') }}</div>
                            <table style="width:100%;">
                                <tbody id="overviewBody"></tbody>
                            </table>
                        </div>

                        <a href="#" id="univDownloadBtn" class="univ-dl-link">
                            <i class="fi fi-rr-download me-1"></i> {{ __('parent.download_results_pdf') }}
                        </a>

                        <div class="univ-section-title" id="semesterTitle">{{ __('parent.exam_results_heading') }}</div>
                        <div style="overflow-x:auto;width:100%;-webkit-overflow-scrolling:touch;">
                            <table class="univ-marks-table">
                                <thead id="marksHead"></thead>
                                <tbody id="marksBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="downloadForm" action="{{ route('download_student_result') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="studentID" id="dl_studentID">
    <input type="hidden" name="examID" id="dl_examID">
    <input type="hidden" name="year" id="dl_year">
    <input type="hidden" name="term" id="dl_term">
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const P = @json(trans('parent'));
    let currentStep = 1;
    let selections = {
        student: '',
        year: '',
        academic_yearID: '',
        term: '',
        exam: ''
    };
    const ayMode = @json($ayMode ?? 'none');
    const allAcademicYears = @json($academicYears ?? []);
    const fallbackYears = @json($years ?? []);

    const renderYearGrid = (isALevel, levelLabel) => {
        $('#noYearsMessage').addClass('d-none');
        let years = allAcademicYears.slice();
        if (ayMode === 'secondary_dual') {
            years = years.filter(function(y) { return !!y.is_a_level === !!isALevel; });
            $('#yearLevelHint').text(isALevel
                ? 'Mwanafunzi ni A Level, chagua academic year ya A Level.'
                : 'Mwanafunzi ni O Level, chagua academic year ya O Level.');
        } else {
            $('#yearLevelHint').text(levelLabel ? ('Kiwango: ' + levelLabel) : '');
        }

        if (!years.length && fallbackYears.length && ayMode !== 'secondary_dual') {
            years = fallbackYears.map(function(y) {
                return { academic_yearID: '', label: String(y), short_label: String(y), calendar_year: y, is_a_level: false };
            });
        }

        if (!years.length) {
            $('#yearGrid').html('');
            $('#noYearsMessage').removeClass('d-none');
            return;
        }

        let html = '';
        years.forEach(function(ay) {
            const label = (ayMode === 'secondary_dual' ? (ay.short_label || ay.label) : (ay.label || ay.short_label));
            html += '<div class="selection-card" data-value="' + ay.calendar_year + '" data-academic-year-id="' + (ay.academic_yearID || '') + '" data-label="' + String(label).replace(/"/g, '&quot;') + '">'
                + '<i class="fi fi-rr-calendar"></i>'
                + '<strong>' + label + '</strong>'
                + '</div>';
        });
        $('#yearGrid').html(html);
    };

    const savedSession = sessionStorage.getItem('parent_results_selections');
    if (savedSession) {
        try {
            selections = JSON.parse(savedSession);
            $('.step-chain, .wizard-content, h4.text-center').hide();
            $('.hint-strip').hide();
            $('#backBtnWrapper').show();
            $('#resultDisplay').show();
            $('#overviewBody').html('<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-danger"></div><br>' + P.loading_results + '</td></tr>');

            $.ajax({
                url: '{{ route("fetch_student_results_ajax") }}',
                data: selections,
                success: function(response) {
                    if (response.success) {
                        renderResults(response.data);
                    } else {
                        sessionStorage.removeItem('parent_results_selections');
                        $('.step-chain, .wizard-content, h4.text-center, .hint-strip').show();
                        $('#backBtnWrapper').hide();
                        $('#resultDisplay').hide();
                        Swal.fire(P.error, response.message || P.could_not_get_results, 'error');
                    }
                },
                error: function() {
                    sessionStorage.removeItem('parent_results_selections');
                    $('.step-chain, .wizard-content, h4.text-center, .hint-strip').show();
                    $('#backBtnWrapper').hide();
                    $('#resultDisplay').hide();
                    Swal.fire(P.error, P.network_error, 'error');
                }
            });
        } catch (e) {}
    }

    const updateWizard = () => {
        $('.step-item').removeClass('active completed');
        for (let i = 1; i <= 4; i++) {
            if (i < currentStep) $('#step' + i + '-tab').addClass('completed');
            if (i === currentStep) $('#step' + i + '-tab').addClass('active');
        }

        const progress = ((currentStep - 1) / 3) * 100;
        $('#stepProgress').css('width', progress + '%');

        $('.wizard-step').addClass('d-none');
        $('#step' + currentStep).removeClass('d-none');

        if (currentStep === 1) $('#prevBtn').addClass('d-none');
        else $('#prevBtn').removeClass('d-none');

        if (currentStep === 4) {
            $('#nextBtn').addClass('d-none');
            $('#viewBtn').removeClass('d-none');
        } else {
            $('#nextBtn').removeClass('d-none');
            $('#viewBtn').addClass('d-none');
        }

        const key = ['student', 'year', 'term', 'exam'][currentStep - 1];
        if (!selections[key]) $('#nextBtn, #viewBtn').prop('disabled', true);
        else $('#nextBtn, #viewBtn').prop('disabled', false);
    };

    $(document).on('click', '.selection-card', function() {
        const step = $(this).closest('.wizard-step').attr('id');
        const key = step.replace('step', '');
        const map = { '1': 'student', '2': 'year', '3': 'term', '4': 'exam' };
        const dataKey = map[key];

        $('#' + step + ' .selection-card').removeClass('selected');
        $(this).addClass('selected');
        selections[dataKey] = $(this).data('value');

        if (dataKey === 'student') {
            selections.year = '';
            selections.academic_yearID = '';
            selections.term = '';
            selections.exam = '';
            $('#termGrid .selection-card').removeClass('selected');
            $('#examGrid').html('');
            renderYearGrid($(this).data('is-a-level') == 1 || $(this).data('is-a-level') === true, $(this).data('level-label') || '');
        }

        if (dataKey === 'year') {
            selections.academic_yearID = $(this).data('academic-year-id') || '';
            selections.term = '';
            selections.exam = '';
            $('#termGrid .selection-card').removeClass('selected');
            $('#examGrid').html('');
        }

        if (dataKey === 'term') {
            selections.exam = '';
        }

        if (currentStep < 3) {
            currentStep++;
            updateWizard();
        } else if (currentStep === 3) {
            currentStep++;
            loadExams();
            updateWizard();
        } else {
            updateWizard();
        }
    });

    $('#nextBtn').click(function() {
        if (currentStep < 4) {
            currentStep++;
            if (currentStep === 4) loadExams();
            updateWizard();
        }
    });

    $('#prevBtn').click(function() {
        if (currentStep > 1) {
            currentStep--;
            updateWizard();
        }
    });

    const loadExams = () => {
        $('#examGrid').html('<div class="col-12 text-center py-4"><div class="spinner-border text-danger"></div></div>');
        $('#noExamsMessage').addClass('d-none');
        selections.exam = '';

        $.ajax({
            url: '{{ route("get_published_exams") }}',
            data: {
                year: selections.year,
                term: selections.term,
                academic_yearID: selections.academic_yearID || ''
            },
            success: function(response) {
                if (response.success && response.exams.length > 0) {
                    let html = '';
                    response.exams.forEach(exam => {
                        html += `
                            <div class="selection-card" data-value="${exam.examID}">
                                <i class="fi fi-rr-file"></i>
                                <strong>${exam.exam_name}</strong>
                                <br><small class="text-muted">${P.published_recently}</small>
                            </div>`;
                    });
                    $('#examGrid').html(html);
                } else {
                    $('#examGrid').html('');
                    $('#noExamsMessage').removeClass('d-none');
                }
                updateWizard();
            }
        });
    };

    $('#viewBtn').click(function() {
        $(this).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + P.searching);

        $.ajax({
            url: '{{ route("fetch_student_results_ajax") }}',
            data: selections,
            success: function(response) {
                $('#viewBtn').html('<i class="fi fi-rr-eye"></i> ' + P.view_results);
                if (response.success) {
                    sessionStorage.setItem('parent_results_selections', JSON.stringify(selections));
                    $('.step-chain, .wizard-content, h4.text-center, .hint-strip').hide();
                    $('#backBtnWrapper').show();
                    renderResults(response.data);
                    $('#resultDisplay').fadeIn();
                    window.scrollTo(0, 0);
                } else {
                    Swal.fire(P.error, response.message || P.could_not_get_results, 'error');
                }
            },
            error: function() {
                $('#viewBtn').html('<i class="fi fi-rr-eye"></i> ' + P.view_results);
                Swal.fire(P.error, P.network_error, 'error');
            }
        });
    });

    $('#backToWizardBtn').click(function(e) {
        e.preventDefault();
        sessionStorage.removeItem('parent_results_selections');
        $('#resultDisplay').hide();
        $('.step-chain, .wizard-content, h4.text-center, .hint-strip').show();
        $('#backBtnWrapper').hide();
        updateWizard();
    });

    const getParentSubjectRemark = (grade, isSecondary, isALevel) => {
        const g = String(grade || '').toUpperCase().trim();
        if (!grade || g === 'N/A' || g === 'INCOMPLETE' || g === '-') {
            return { text: P.incomplete, cssClass: 'bg-inc' };
        }
        if (isSecondary) {
            if (isALevel) {
                const map = {
                    A: P.excellent, B: P.very_good, C: P.good,
                    D: P.average_grade, E: P.satisfactory, S: P.subsidiary, F: P.fail
                };
                if (map[g]) {
                    return { text: map[g], cssClass: g === 'F' ? 'bg-fail' : 'bg-pass' };
                }
            } else {
                const map = {
                    A: P.excellent, B: P.very_good, C: P.good, D: P.pass, F: P.fail
                };
                if (map[g]) {
                    return { text: map[g], cssClass: g === 'F' ? 'bg-fail' : 'bg-pass' };
                }
            }
            if (/^(I|II|III|IV)\./.test(g)) {
                return { text: P.pass, cssClass: 'bg-pass' };
            }
            return { text: P.fail, cssClass: 'bg-fail' };
        }
        if (['A', 'B', 'C', 'D'].includes(g)) {
            return { text: P.pass, cssClass: 'bg-pass' };
        }
        return { text: P.fail, cssClass: 'bg-fail' };
    };

    const formatPaperLines = (papers) => {
        if (!papers || !papers.length) return '';
        return papers.map(p => {
            const pm = (p.marks !== null && p.marks !== undefined && p.marks !== '')
                ? `${Math.round(parseFloat(p.marks))}/${p.max_marks || ''}`
                : '-';
            return `<li class="exam-sub-item px-3" style="opacity:.9;">${p.component_name || P.paper}: <strong>${pm}</strong></li>`;
        }).join('');
    };

    const renderResults = (data) => {
        const position = (data.position && data.position !== 'N/A') ? data.position : P.incomplete;
        const grade = (data.overall_grade || data.overall_division || P.incomplete);
        const totalMarks = (data.total_marks !== null && data.total_marks !== undefined && data.total_marks !== '-') ? parseFloat(data.total_marks).toFixed(0) : P.incomplete;
        const average = (data.average !== null && data.average !== undefined && data.average !== 0 && data.average !== '-') ? parseFloat(data.average).toFixed(0) : P.incomplete;
        const isSecondary = String(data.school_type || 'Secondary').toLowerCase() === 'secondary';
        const isALevel = !!data.is_a_level;

        $('#overviewBody').html(`
            <tr><td class="label">${P.student_name}:</td><td>${data.student.first_name} ${data.student.last_name}</td></tr>
            <tr><td class="label">${P.total_marks}:</td><td>${totalMarks}</td></tr>
            <tr><td class="label">${P.average}:</td><td>${average}</td></tr>
            <tr><td class="label">${P.exam_division}:</td><td>${grade}</td></tr>
            <tr><td class="label">${P.position}:</td><td>${position}</td></tr>
        `);

        const hasCa = data.has_ca;
        let headHtml = '';
        if (hasCa) {
            headHtml = `
                <tr>
                    <th class="col-somo">${P.subject}</th>
                    <th class="col-mtihani">${P.exams}</th>
                    <th class="col-small text-center">${P.average}</th>
                    <th class="col-small text-center">${P.grade}</th>
                    <th class="col-small text-center">${P.remark}</th>
                </tr>`;
        } else {
            headHtml = `
                <tr>
                    <th class="col-somo">${P.subject}</th>
                    <th class="col-small text-center">${P.marks}</th>
                    <th class="col-small text-center">${P.grade}</th>
                    <th class="col-small text-center">${P.remark}</th>
                </tr>`;
        }
        $('#marksHead').html(headHtml);

        let bodyHtml = '';
        data.results.forEach(res => {
            const rawMarks = res.average ?? res.marks;
            const hasMarks = rawMarks !== null && rawMarks !== undefined && rawMarks !== '' && rawMarks !== '-';
            const marksVal = hasMarks ? parseFloat(rawMarks).toFixed(0) : P.incomplete;
            const gradeText = hasMarks ? (res.grade || 'F') : P.incomplete;
            const remarkInfo = getParentSubjectRemark(gradeText, isSecondary, isALevel);
            const remarkClass = remarkInfo.cssClass;
            const remarkText = res.remark || remarkInfo.text;

            let subjectCell = `<strong>${res.subject_name || P.na}</strong>`;
            if (res.is_duo && res.papers && res.papers.length) {
                subjectCell += `<ul class="exam-sub-list mt-1">${formatPaperLines(res.papers)}</ul>`;
            }

            if (hasCa) {
                bodyHtml += `
                    <tr>
                        <td class="align-middle">${subjectCell}</td>
                        <td class="col-mtihani p-0">
                            <ul class="exam-sub-list">
                                ${(res.exams || []).map(ex => {
                                    const exMarks = (ex.marks !== null && ex.marks !== undefined && ex.marks !== '')
                                        ? parseFloat(ex.marks).toFixed(0) : '-';
                                    return `<li class="exam-sub-item px-3">${ex.exam_name}: <strong>${exMarks}</strong></li>${formatPaperLines(ex.papers || [])}`;
                                }).join('')}
                            </ul>
                        </td>
                        <td class="col-small align-middle"><strong>${marksVal}</strong></td>
                        <td class="col-small align-middle">${gradeText}</td>
                        <td class="col-small colored-remark ${remarkClass} align-middle">${remarkText}</td>
                    </tr>
                `;
            } else {
                bodyHtml += `
                    <tr>
                        <td class="align-middle">${subjectCell}</td>
                        <td class="col-small align-middle"><strong>${marksVal}</strong></td>
                        <td class="col-small align-middle">${gradeText}</td>
                        <td class="col-small colored-remark ${remarkClass} align-middle">${remarkText}</td>
                    </tr>
                `;
            }
        });
        $('#marksBody').html(bodyHtml);
        $('#semesterTitle').text(P.exam_results_heading);
    };

    $('#univDownloadBtn').click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: P.preparing_pdf,
            html: P.preparing_results_wait + '<br><br><b></b>%',
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
                const b = Swal.getHtmlContainer().querySelector('b');
                let progress = 0;
                const timerInterval = setInterval(() => {
                    progress += 5;
                    if (progress > 100) progress = 100;
                    b.textContent = progress;
                }, 150);
            },
            willClose: () => {
                $('#dl_studentID').val(selections.student);
                $('#dl_examID').val(selections.exam);
                $('#dl_year').val(selections.year);
                $('#dl_term').val(selections.term);
                $('#downloadForm').submit();
            }
        });
    });

    updateWizard();
});
</script>
@endpush
