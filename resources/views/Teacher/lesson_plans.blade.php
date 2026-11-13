@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.lesson_plans').' - ShuleXpert')
@section('page_title', __('teacher.pages.lesson_plans'))
@section('page_breadcrumb', __('teacher.pages.lesson_plans_bc'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lp,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lp p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lp small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lp strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lp label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lp td,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lp th {
        color: #000 !important;
    }
    .sx-teacher-lp .sx-lp-toolbar,
    .sx-teacher-lp .sx-lp-panel {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
        border-top: 4px solid #940000 !important;
        padding: 0.9rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    .sx-teacher-lp .form-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.3rem;
    }
    .sx-teacher-lp .form-select,
    .sx-teacher-lp .form-control,
    .sx-teacher-lp .btn,
    #lessonPlanModal .form-select,
    #lessonPlanModal .form-control,
    #lessonPlanModal .btn,
    #lessonPlanModal .modal-content {
        border-radius: 0 !important;
    }
    .sx-teacher-lp .form-select {
        min-height: 46px;
        font-weight: 600;
        border: 1px solid #cfcfcf;
    }
    .sx-teacher-lp .form-select:focus,
    #lessonPlanModal .form-control:focus,
    #lessonPlanModal .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12);
    }
    .bg-primary-custom {
        background: #fff5f5 !important;
        color: #940000 !important;
        border-bottom: 2px solid #940000 !important;
    }
    .text-primary-custom { color: #940000 !important; }
    .btn-session-action,
    .btn-primary-custom,
    #lessonPlanModal .btn-session-action,
    #lessonPlanModal .btn-primary-custom {
        background: #940000 !important;
        color: #fff !important;
        border: none !important;
        font-weight: 800;
    }
    .btn-session-action:hover,
    .btn-primary-custom:hover,
    .btn-session-action *,
    .btn-primary-custom * {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    .btn-session-action:hover,
    .btn-primary-custom:hover {
        background: #7b0000 !important;
    }
    .btn-outline-corporate {
        background: transparent;
        color: #940000 !important;
        border: 1px solid #940000 !important;
        font-weight: 800;
    }
    .btn-outline-corporate:hover {
        background: #940000;
        color: #fff !important;
    }
    .lesson-plan-table {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0;
        border: 2px solid #940000;
        font-size: 0.9rem;
        background: #fff;
    }
    .lesson-plan-table td,
    .lesson-plan-table th {
        border: 1px solid #940000 !important;
        padding: 5px 8px;
        text-align: left;
    }
    .lesson-plan-table th {
        background: #fff5f5;
        color: #940000 !important;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .lesson-plan-table tbody tr:nth-child(even) { background-color: #fff5f5; }
    .lesson-plan-table input,
    .lesson-plan-table textarea {
        width: 100%;
        border: 1px solid rgba(148, 0, 0, 0.2);
        padding: 6px 10px;
        background-color: #fff;
        font-size: 0.9rem;
        border-radius: 0 !important;
    }
    .lesson-plan-header {
        text-align: center;
        font-weight: 800;
        font-size: 22px;
        margin: 20px 0;
        color: #940000;
    }
    #lessonPlanTabs .nav-link {
        color: #666;
        border: none;
        border-radius: 0 !important;
        padding: 12px 20px;
        font-weight: 700;
    }
    #lessonPlanTabs .nav-link.active {
        background: #fff5f5 !important;
        color: #940000 !important;
        border-bottom: 3px solid #940000;
    }
    .dotted-line {
        border-bottom: 2px dotted #940000;
        min-height: 35px;
        padding: 8px 0;
        margin: 8px 0;
    }
    .signature-container {
        margin: 24px 0;
        background: #fff5f5;
        padding: 16px;
        border-radius: 0;
    }
    .signature-label {
        color: #940000;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .signature-canvas,
    .signature-preview {
        border: 2px solid #940000;
        border-radius: 0;
        background: #fff;
        max-width: 100%;
    }
    .signature-canvas {
        cursor: crosshair;
        width: 100%;
        max-width: 400px;
    }
    .signature-actions button { margin-right: 8px; }
    #lessonPlanModal .modal-header .btn-close { box-shadow: none; }
    .sx-teacher-lp .sx-lp-meta {
        font-size: 0.86rem;
        font-weight: 600;
        color: #444 !important;
    }
    .sx-teacher-lp .sx-lp-meta strong { color: #940000 !important; }
    .sx-teacher-lp .form-hint {
        font-size: 0.78rem;
        color: #666 !important;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-teacher-lp .sx-lp-toolbar,
    html[data-bs-theme="dark"] .sx-teacher-lp .sx-lp-toolbar,
    html[data-sx-theme="dark"] .sx-teacher-lp .sx-lp-panel,
    html[data-bs-theme="dark"] .sx-teacher-lp .sx-lp-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lp .form-label,
    html[data-bs-theme="dark"] .sx-teacher-lp .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lp .form-hint,
    html[data-bs-theme="dark"] .sx-teacher-lp .form-hint {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lp .form-select,
    html[data-bs-theme="dark"] .sx-teacher-lp .form-select,
    html[data-sx-theme="dark"] .sx-teacher-lp .form-control,
    html[data-bs-theme="dark"] .sx-teacher-lp .form-control {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lp .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-lp .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-lp .text-muted,
    html[data-bs-theme="dark"] .sx-teacher-lp .text-muted,
    html[data-sx-theme="dark"] .sx-teacher-lp .text-muted p,
    html[data-bs-theme="dark"] .sx-teacher-lp .text-muted p {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lp .sx-lp-meta,
    html[data-bs-theme="dark"] .sx-teacher-lp .sx-lp-meta {
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lp .sx-lp-meta strong,
    html[data-bs-theme="dark"] .sx-teacher-lp .sx-lp-meta strong {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lp .alert-warning,
    html[data-bs-theme="dark"] .sx-teacher-lp .alert-warning {
        background: #3a2a1a !important;
        border-color: #5a4030 !important;
        color: #f5e6c8 !important;
    }
    html[data-sx-theme="dark"] #lessonPlanModal .modal-content,
    html[data-bs-theme="dark"] #lessonPlanModal .modal-content {
        background: #24181c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #lessonPlanModal .modal-header.bg-primary-custom,
    html[data-bs-theme="dark"] #lessonPlanModal .modal-header.bg-primary-custom {
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%) !important;
        color: #fff !important;
        border-bottom: none !important;
    }
    html[data-sx-theme="dark"] #lessonPlanModal .modal-header.bg-primary-custom .modal-title,
    html[data-bs-theme="dark"] #lessonPlanModal .modal-header.bg-primary-custom .modal-title,
    html[data-sx-theme="dark"] #lessonPlanModal .modal-header.bg-primary-custom i,
    html[data-bs-theme="dark"] #lessonPlanModal .modal-header.bg-primary-custom i {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] #lessonPlanModal .modal-header .btn-close,
    html[data-bs-theme="dark"] #lessonPlanModal .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    html[data-sx-theme="dark"] #lessonPlanModal .modal-body,
    html[data-bs-theme="dark"] #lessonPlanModal .modal-body {
        background: #1c1417 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #lessonPlanTabs .nav-link,
    html[data-bs-theme="dark"] #lessonPlanTabs .nav-link {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #lessonPlanTabs .nav-link.active,
    html[data-bs-theme="dark"] #lessonPlanTabs .nav-link.active {
        background: #2a1c20 !important;
        color: #ffb4b4 !important;
        border-bottom-color: #940000 !important;
    }
    html[data-sx-theme="dark"] #lessonPlanModal .text-muted,
    html[data-bs-theme="dark"] #lessonPlanModal .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #lessonPlanModal .form-control,
    html[data-bs-theme="dark"] #lessonPlanModal .form-control,
    html[data-sx-theme="dark"] #lessonPlanModal .form-select,
    html[data-bs-theme="dark"] #lessonPlanModal .form-select {
        background: #24181c !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #lessonPlanModal .form-label,
    html[data-bs-theme="dark"] #lessonPlanModal .form-label,
    html[data-sx-theme="dark"] #lessonPlanModal label,
    html[data-bs-theme="dark"] #lessonPlanModal label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .lesson-plan-table,
    html[data-bs-theme="dark"] .lesson-plan-table {
        background: #24181c !important;
        color: #f3e9eb !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .lesson-plan-table th,
    html[data-bs-theme="dark"] .lesson-plan-table th {
        background: #2a1c20 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .lesson-plan-table tbody tr:nth-child(even),
    html[data-bs-theme="dark"] .lesson-plan-table tbody tr:nth-child(even) {
        background-color: #1c1417 !important;
    }
    html[data-sx-theme="dark"] .lesson-plan-table tbody tr:nth-child(odd),
    html[data-bs-theme="dark"] .lesson-plan-table tbody tr:nth-child(odd) {
        background-color: #24181c !important;
    }
    html[data-sx-theme="dark"] .lesson-plan-table td,
    html[data-bs-theme="dark"] .lesson-plan-table td {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .lesson-plan-table input,
    html[data-bs-theme="dark"] .lesson-plan-table input,
    html[data-sx-theme="dark"] .lesson-plan-table textarea,
    html[data-bs-theme="dark"] .lesson-plan-table textarea {
        background-color: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .lesson-plan-table input[readonly],
    html[data-bs-theme="dark"] .lesson-plan-table input[readonly],
    html[data-sx-theme="dark"] .lesson-plan-table input[style*="background-color: #f5f5f5"],
    html[data-bs-theme="dark"] .lesson-plan-table input[style*="background-color: #f5f5f5"] {
        background-color: #2a2226 !important;
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .lesson-plan-header,
    html[data-bs-theme="dark"] .lesson-plan-header,
    html[data-sx-theme="dark"] .text-primary-custom,
    html[data-bs-theme="dark"] .text-primary-custom {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .signature-container,
    html[data-bs-theme="dark"] .signature-container {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .signature-label,
    html[data-bs-theme="dark"] .signature-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .signature-canvas,
    html[data-bs-theme="dark"] .signature-canvas,
    html[data-sx-theme="dark"] .signature-preview,
    html[data-bs-theme="dark"] .signature-preview {
        background: #fff !important;
    }
    html[data-sx-theme="dark"] .btn-outline-corporate,
    html[data-bs-theme="dark"] .btn-outline-corporate {
        color: #ffb4b4 !important;
        border-color: #940000 !important;
    }
</style>
@endpush

@section('content')
<div class="sx-teacher-lp">
    @if(isset($message))
        <div class="alert alert-warning rounded-0">
            <i class="bi bi-exclamation-triangle"></i> {{ $message }}
        </div>
    @else
        <div class="sx-lp-toolbar">
            <label class="form-label" for="subjectSelector">{{ __('teacher.subject') }}</label>
            <select class="form-select" id="subjectSelector" onchange="loadSessionsBySubject()">
                <option value="">{{ __('teacher.plans.select_subject') }}</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->subjectID ?? $subject['subjectID'] ?? '' }}">{{ $subject->subject_name ?? $subject['subject_name'] ?? '' }}</option>
                @endforeach
            </select>
            <span class="form-hint d-block mt-2">{{ __('teacher.plans.hint') }}</span>
        </div>

        <div class="sx-lp-panel" id="sessionsContainer">
            <div id="sessionsList">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-info-circle" style="font-size: 2rem; color:#940000;"></i>
                    <p class="mt-2 mb-0">{{ __('teacher.plans.select_subject_sessions') }}</p>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Lesson Plan Modal -->
<div class="modal fade" id="lessonPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-primary-custom rounded-0">
                <h5 class="modal-title">
                    <i class="bi bi-journal-text"></i> {{ __('teacher.plans.management') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 120px); overflow-y: auto;">
                    <div id="modalAlertContainer"></div>
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist" id="lessonPlanTabs">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active" id="create-tab" data-bs-toggle="tab" data-bs-target="#create-lesson-plan" role="tab" aria-controls="create-lesson-plan" aria-selected="true">
                            <i class="bi bi-plus-circle"></i> {{ __('teacher.plans.create_tab') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage-lesson-plan" role="tab" aria-controls="manage-lesson-plan" aria-selected="false">
                            <i class="bi bi-pencil-square"></i> {{ __('teacher.plans.manage_tab') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" id="view-tab" data-bs-toggle="tab" data-bs-target="#view-lesson-plan" role="tab" aria-controls="view-lesson-plan" aria-selected="false">
                            <i class="bi bi-eye"></i> {{ __('teacher.plans.view_tab') }}
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="lessonPlanTabContent">
                    <!-- Create Tab -->
                    <div class="tab-pane fade show active" id="create-lesson-plan" role="tabpanel">
                        <div id="createTabContent">
                            <div class="text-center mb-3 d-flex flex-wrap gap-2 justify-content-center">
                                <button type="button" class="btn btn-lg btn-session-action text-white" onclick="showCreateNewForm()">
                                    <i class="bi bi-file-plus"></i> <span class="text-white">{{ __('teacher.plans.create_new') }}</span>
                                </button>
                                <button type="button" class="btn btn-lg btn-outline-corporate" onclick="showUseExistingForm()">
                                    <i class="bi bi-folder"></i> {{ __('teacher.plans.use_existing') }}
                                </button>
                            </div>
                            <div id="createNewForm" style="display: none;">
                                <!-- Lesson Plan Form will be loaded here -->
                            </div>
                            <div id="useExistingForm" style="display: none;">
                                <div id="existingLessonPlansList">
                                    <div class="text-center py-3">
                                        <div class="spinner-border" role="status" style="color: #940000;">
                                            <span class="visually-hidden">{{ __('teacher.loading') }}</span>
                                        </div>
                                        <p class="mt-2">{{ __('teacher.plans.loading_existing') }}</p>
                                    </div>
                                </div>
                                <div id="existingLessonPlanContent" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Manage Tab -->
                    <div class="tab-pane fade" id="manage-lesson-plan" role="tabpanel">
                        <div id="manageTabContent">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">{{ __('teacher.plans.filter_manage') }}</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('teacher.plans.filter_type') }}</label>
                                                <select class="form-select" id="manageFilterType"
                                                    onchange="toggleManageFilterOptions()">
                                                    <option value="single_date">{{ __('teacher.plans.single_date') }}</option>
                                                    <option value="date_range">{{ __('teacher.plans.date_range') }}</option>
                                                    <option value="year">{{ __('teacher.plans.by_year') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="manageSingleDateFilter">
                                            <div class="form-group">
                                                <label>{{ __('teacher.plans.select_date') }}</label>
                                                <input type="date" class="form-control" id="manageLessonDate"
                                                    onchange="loadLessonPlanForManage()">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="manageDateRangeFilter1" style="display: none;">
                                            <div class="form-group">
                                                <label>{{ __('teacher.plans.from_date') }}</label>
                                                <input type="date" class="form-control" id="manageFromDate">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="manageDateRangeFilter2" style="display: none;">
                                            <div class="form-group">
                                                <label>{{ __('teacher.plans.to_date') }}</label>
                                                <input type="date" class="form-control" id="manageToDate">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="manageYearFilter" style="display: none;">
                                            <div class="form-group">
                                                <label>{{ __('teacher.year') }}</label>
                                                <input type="number" class="form-control" id="manageYear" min="2020"
                                                    max="2100" value="{{ date('Y') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn w-100 btn-session-action text-white" onclick="loadLessonPlansForManage()">
                                                    <i class="bi bi-search"></i> <span class="text-white">{{ __('teacher.plans.load_plans') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="manageLessonPlanContent">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                                    <p class="mt-3">{{ __('teacher.plans.filter_hint') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- View Tab -->
                    <div class="tab-pane fade" id="view-lesson-plan" role="tabpanel">
                        <div id="viewTabContent">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">{{ __('teacher.plans.filter_plans') }}</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('teacher.plans.filter_type') }}</label>
                                                <select class="form-select" id="filterType"
                                                    onchange="toggleFilterOptions()">
                                                    <option value="date_range">{{ __('teacher.plans.date_range') }}</option>
                                                    <option value="year">{{ __('teacher.plans.by_year') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="dateRangeFilter">
                                            <div class="form-group">
                                                <label>{{ __('teacher.plans.from_date') }}</label>
                                                <input type="date" class="form-control" id="viewFromDate">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="dateRangeFilter2">
                                            <div class="form-group">
                                                <label>{{ __('teacher.plans.to_date') }}</label>
                                                <input type="date" class="form-control" id="viewToDate">
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="yearFilter" style="display: none;">
                                            <div class="form-group">
                                                <label>{{ __('teacher.year') }}</label>
                                                <input type="number" class="form-control" id="viewYear" min="2020"
                                                    max="2100" value="{{ date('Y') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn w-100 btn-session-action text-white" onclick="loadLessonPlansByFilter()">
                                                    <i class="bi bi-search"></i> <span class="text-white">{{ __('teacher.plans.load_plans') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="viewLessonPlanContent">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                                    <p class="mt-3">{{ __('teacher.plans.filter_hint') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- PDF Download Progress Modal -->
<div class="modal fade" id="downloadProgressModal" tabindex="-1" aria-labelledby="downloadProgressModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-primary-custom rounded-0">
                <h5 class="modal-title" id="downloadProgressModalLabel">
                    <i class="bi bi-file-pdf"></i> {{ __('teacher.plans.generating_pdf') }}
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-3">{{ __('teacher.plans.wait_pdf') }}</p>
                <div class="progress mb-2 rounded-0" style="height: 25px;">
                    <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" style="width: 0%; background-color: #940000;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div id="pdfProgressPercent" class="fw-bold" style="color: #940000;">0%</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
window.sxShowModal = function(el) {
    var node = typeof el === 'string' ? document.querySelector(el) : (el && el.jquery ? el[0] : el);
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('show');
};
window.sxHideModal = function(el) {
    var node = typeof el === 'string' ? document.querySelector(el) : (el && el.jquery ? el[0] : el);
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        var inst = bootstrap.Modal.getInstance(node) || bootstrap.Modal.getOrCreateInstance(node);
        inst.hide();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('hide');
};
window.sxShowTab = function(selector) {
    var node = document.querySelector(selector);
    if (!node) return;
    if (window.bootstrap && bootstrap.Tab) {
        bootstrap.Tab.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.tab) jQuery(node).tab('show');
};
</script>
<script>
    // Ensure jQuery is available globally as $ to prevent "TypeError: $ is not a function"
    if (typeof jQuery !== 'undefined') {
        window.$ = jQuery;
    }

    var i18n = @json(__('teacher.plans'));
    i18n.loading = @json(__('teacher.loading'));
    i18n.cancel = @json(__('teacher.cancel'));
    i18n.subject = @json(__('teacher.subject'));
    i18n.class = @json(__('teacher.class'));
    i18n.year = @json(__('teacher.year'));
    i18n.date = @json(__('teacher.date'));
    i18n.time = @json(__('teacher.time'));
    i18n.status = @json(__('teacher.status'));
    i18n.actions = @json(__('teacher.actions'));

    // Make sure functions are globally accessible
    let currentSubjectSessions = [];
    var currentSessionData = null;
    // teacherSignaturePad is declared later near signature helpers.
    // Draft helpers reference it safely.

    // ===== Draft cache (same idea as Add Results marks cache) =====
    const LESSON_PLAN_DRAFT_PREFIX = 'shulexpert_lesson_plan_draft_v1_';
    let lessonPlanDraftTimer = null;
    let restoringLessonPlanDraft = false;

    function getLessonPlanDraftKey(sessionId, date) {
        const sid = sessionId || (typeof currentSessionData !== 'undefined' && currentSessionData && currentSessionData.sessionTimetableID) || '0';
        const d = date || $('#lesson_date_hidden').val() || 'new';
        return LESSON_PLAN_DRAFT_PREFIX + sid + '_' + d;
    }

    function collectLessonPlanDraftFields() {
        const stages = [];
        $('#lessonStagesTable tr').each(function () {
            stages.push({
                stage: $(this).find('td:first').text().trim(),
                time: $(this).find('.stage-time').val() || '',
                teaching_activities: $(this).find('.stage-teaching').val() || '',
                learning_activities: $(this).find('.stage-learning').val() || '',
                assessment_criteria: $(this).find('.stage-assessment').val() || ''
            });
        });
        const sigPad = (typeof teacherSignaturePad !== 'undefined') ? teacherSignaturePad : null;
        return {
            main_competence: $('#main_competence').val() || '',
            specific_competence: $('#specific_competence').val() || '',
            main_activity: $('#main_activity').val() || '',
            specific_activity: $('#specific_activity').val() || '',
            teaching_learning_resources: $('#teaching_learning_resources').val() || '',
            references: $('#references').val() || '',
            remarks: $('#remarks').val() || '',
            reflection: $('#reflection').val() || '',
            evaluation: $('#evaluation').val() || '',
            stages: stages,
            teacher_signature: (sigPad && !sigPad.isEmpty())
                ? sigPad.toDataURL()
                : ($('#teacher_signature').val() || '')
        };
    }

    function saveLessonPlanDraft(showToast) {
        if (!$('#main_competence').length) return;
        try {
            const key = getLessonPlanDraftKey();
            const draft = {
                saved_at: Date.now(),
                session_timetableID: (typeof currentSessionData !== 'undefined' && currentSessionData)
                    ? currentSessionData.sessionTimetableID
                    : null,
                lesson_date: $('#lesson_date_hidden').val(),
                fields: collectLessonPlanDraftFields()
            };
            localStorage.setItem(key, JSON.stringify(draft));
            if (showToast && typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: i18n.draft_saved,
                    showConfirmButton: false,
                    timer: 2500
                });
            }
        } catch (e) {
            console.warn('Failed to save lesson plan draft', e);
        }
    }

    function getLessonPlanDraft(sessionId, date) {
        try {
            const raw = localStorage.getItem(getLessonPlanDraftKey(sessionId, date));
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function clearLessonPlanDraft(sessionId, date) {
        try {
            localStorage.removeItem(getLessonPlanDraftKey(sessionId, date));
        } catch (e) {}
    }

    function applyLessonPlanFields(fields) {
        if (!fields) return;
        if (fields.main_competence != null) $('#main_competence').val(fields.main_competence);
        if (fields.specific_competence != null) $('#specific_competence').val(fields.specific_competence);
        if (fields.main_activity != null) $('#main_activity').val(fields.main_activity);
        if (fields.specific_activity != null) $('#specific_activity').val(fields.specific_activity);
        if (fields.teaching_learning_resources != null || fields.teaching_aids != null) {
            $('#teaching_learning_resources').val(fields.teaching_learning_resources || fields.teaching_aids || '');
        }
        if (fields.references != null) $('#references').val(fields.references);
        if (fields.remarks != null) $('#remarks').val(fields.remarks);
        if (fields.reflection != null) $('#reflection').val(fields.reflection);
        if (fields.evaluation != null) $('#evaluation').val(fields.evaluation);

        const stages = fields.stages || fields.lesson_stages || [];
        if (Array.isArray(stages) && stages.length) {
            stages.forEach(function (stage, index) {
                const rows = $('#lessonStagesTable tr');
                if (rows.length > index) {
                    const row = $(rows[index]);
                    if (stage.time) row.find('.stage-time').val(stage.time);
                    if (stage.teaching_activities || stage.teaching) {
                        row.find('.stage-teaching').val(stage.teaching_activities || stage.teaching);
                    }
                    if (stage.learning_activities || stage.learning) {
                        row.find('.stage-learning').val(stage.learning_activities || stage.learning);
                    }
                    if (stage.assessment_criteria || stage.assessment) {
                        row.find('.stage-assessment').val(stage.assessment_criteria || stage.assessment);
                    }
                }
            });
        }
    }

    function scheduleLessonPlanDraftSave() {
        if (restoringLessonPlanDraft) return;
        clearTimeout(lessonPlanDraftTimer);
        lessonPlanDraftTimer = setTimeout(function () {
            saveLessonPlanDraft(false);
        }, 400);
    }

    $(document).on('input change', '#createNewForm textarea, #createNewForm input', function () {
        scheduleLessonPlanDraftSave();
    });

    $('#lessonPlanModal').on('hidden.bs.modal', function () {
        if ($('#main_competence').length) {
            saveLessonPlanDraft(true);
        }
    });

    function loadSessionsBySubject() {
        const subjectID = $('#subjectSelector').val();
        console.log('Loading sessions for subjectID:', subjectID);

        if (!subjectID) {
            currentSubjectSessions = [];
            $('#sessionsList').html(`
            <div class="text-center text-muted py-5">
                <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                <p class="mt-3">${i18n.select_subject_sessions}</p>
            </div>
        `);
            return;
        }

        // Show loading
        $('#sessionsList').html(`
        <div class="text-center py-5">
            <div class="spinner-border" role="status" style="color: #940000;">
                <span class="visually-hidden">${i18n.loading}</span>
            </div>
            <p class="mt-3 text-muted">${i18n.loading_sessions}</p>
        </div>
    `);

        $.ajax({
            url: '{{ route("teacher.get_sessions_by_subject") }}',
            method: 'GET',
            data: {
                subjectID: subjectID
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function (response) {
                console.log('Sessions response:', response);

                if (!response) {
                    $('#sessionsList').html(`
                    <div class="text-center text-danger py-5">
                        <i class="bi bi-x-circle" style="font-size: 3rem;"></i>
                        <p class="mt-3">${i18n.invalid_response}</p>
                    </div>
                `);
                    return;
                }

                if (response.success && response.sessions && Array.isArray(response.sessions) && response.sessions.length > 0) {
                    currentSubjectSessions = response.sessions.filter(s => s && s.session_timetableID);

                    const firstSession = currentSubjectSessions[0];
                    if (!firstSession) {
                        $('#sessionsList').html(`
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                            <p class="mt-3">${i18n.no_valid_session}</p>
                        </div>
                    `);
                        return;
                    }

                    const startTime = formatTime(firstSession.start_time);
                    const endTime = formatTime(firstSession.end_time);
                    const subjectName = firstSession.subject_name || i18n.na;
                    const className = firstSession.class_name || i18n.na;
                    const subclassName = firstSession.subclass_name || '';
                    const day = firstSession.day || i18n.na;

                    const sid = firstSession.session_timetableID;
                    const esc = (v) => String(v ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                    $('#sessionsList').html(`
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="sx-lp-meta">
                            <div><strong>${i18n.session_ready}</strong></div>
                            <div>${subjectName} · ${className}${subclassName ? ' - ' + subclassName : ''} · ${day} · ${startTime} - ${endTime}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-session-action text-white"
                            onclick="openLessonPlanModal(${sid}, '${esc(day)}', '${esc(firstSession.start_time)}', '${esc(firstSession.end_time)}', '${esc(subjectName)}', '${esc(className)}')">
                            <i class="bi bi-journal-text"></i> <span class="text-white">${i18n.open_plan}</span>
                        </button>
                    </div>
                `);

                    openLessonPlanModal(
                        sid,
                        day,
                        firstSession.start_time,
                        firstSession.end_time,
                        subjectName,
                        className
                    );
                } else {
                    const errorMsg = response.error || response.message || i18n.no_session;
                    $('#sessionsList').html(`
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                        <p class="mt-3">${errorMsg}</p>
                    </div>
                `);
                }
            },
            error: function (xhr) {
                console.error('Error loading sessions:', xhr);
                console.error('Response:', xhr.responseJSON);
                console.error('Status:', xhr.status);
                const error = xhr.responseJSON?.error || xhr.responseJSON?.message || i18n.fail_load_sessions;
                $('#sessionsList').html(`
                <div class="text-center text-danger py-5">
                    <i class="bi bi-x-circle" style="font-size: 3rem;"></i>
                    <p class="mt-3">${error}</p>
                    <p class="mt-2 small text-muted">${(i18n.status_label || 'Status: :status').replace(':status', xhr.status)}</p>
                </div>
            `);
            }
        });
    }

    function formatTime(timeStr) {
        if (!timeStr) return 'N/A';
        const parts = timeStr.split(':');
        const hours = parseInt(parts[0]);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        return displayHours + ':' + minutes + ' ' + ampm;
    }

    function loadSessionDates(sessionTimetableID) {
        const currentYear = {{ $currentYear }};

        $.ajax({
            url: '{{ route("teacher.get_all_sessions_for_year") }}',
            method: 'GET',
            data: {
                session_timetableID: sessionTimetableID,
                year: currentYear
            },
            success: function (response) {
                if (response.success && response.data.dates.length > 0) {
                    const dates = response.data.dates;
                    const totalSessions = dates.length;
                    const sessionsWithPlans = dates.filter(d => d.has_lesson_plan).length;

                    // Show first few dates and total count
                    let datesText = '';
                    if (dates.length <= 5) {
                        datesText = dates.map(d => d.formatted_date).join(', ');
                    } else {
                        datesText = dates.slice(0, 3).map(d => d.formatted_date).join(', ') + ' ... (' + totalSessions + ' sessions)';
                    }

                    $('#sessionDates_' + sessionTimetableID).html(
                        '<i class="bi bi-calendar3"></i> <span class="text-info">' + (i18n.sessions_year || ':count sessions/year').replace(':count', totalSessions) + '</span> | ' +
                        '<span class="text-success">' + (i18n.with_plans || ':count with plans').replace(':count', sessionsWithPlans) + '</span><br>' +
                        '<small class="text-muted">' + datesText + '</small>'
                    );
                } else {
                    $('#sessionDates_' + sessionTimetableID).html(
                        '<i class="bi bi-calendar3"></i> <span class="text-muted">' + i18n.no_sessions + '</span>'
                    );
                }
            },
            error: function () {
                $('#sessionDates_' + sessionTimetableID).html(
                    '<i class="bi bi-calendar3"></i> <span class="text-danger">' + i18n.error_loading_dates + '</span>'
                );
            }
        });
    }

    function openLessonPlanModal(sessionTimetableID, day, startTime, endTime, subjectName, className, date, autoOpenCreate) {
        currentSessionData = {
            sessionTimetableID: sessionTimetableID,
            day: day,
            startTime: startTime,
            endTime: endTime,
            subjectName: subjectName,
            className: className,
            date: date || null
        };

        sxShowModal('#lessonPlanModal');
        sxShowTab('#create-tab');

        if (autoOpenCreate && date) {
            // Auto-open create form with session details
            $('#createNewForm').show();
            $('#useExistingForm').hide();
            // Load form directly with date
            loadCreateNewFormDirectly(date);
        } else {
            $('#createNewForm').hide();
            $('#useExistingForm').hide();
        }
    }

    function showCreateNewForm() {
        $('#useExistingForm').hide();
        $('#createNewForm').show();
        loadCreateNewForm();
    }

    function showUseExistingForm() {
        $('#createNewForm').hide();
        $('#useExistingForm').show();
        $('#existingLessonPlanContent').hide();
        $('#existingLessonPlansList').show();

        // Load all existing lesson plans for this session
        loadAllExistingLessonPlans();
    }

    function loadAllExistingLessonPlans() {
        if (!currentSessionData || !currentSessionData.sessionTimetableID) {
            $('#existingLessonPlansList').html('<div class="alert alert-warning">' + i18n.no_session_selected + '</div>');
            return;
        }

        // Get lesson plans for the last 2 years to show recent ones
        const currentYear = new Date().getFullYear();
        const lastYear = currentYear - 1;

        $.ajax({
            url: '{{ route("teacher.get_lesson_plans_by_filter") }}',
            method: 'GET',
            data: {
                session_timetableID: currentSessionData.sessionTimetableID,
                filter_type: 'year',
                year: currentYear
            },
            success: function (response) {
                if (response.success && response.lesson_plans && response.lesson_plans.length > 0) {
                    displayExistingLessonPlansList(response.lesson_plans);
                } else {
                    // Try last year if current year has no plans
                    $.ajax({
                        url: '{{ route("teacher.get_lesson_plans_by_filter") }}',
                        method: 'GET',
                        data: {
                            session_timetableID: currentSessionData.sessionTimetableID,
                            filter_type: 'year',
                            year: lastYear
                        },
                        success: function (response2) {
                            if (response2.success && response2.lesson_plans && response2.lesson_plans.length > 0) {
                                displayExistingLessonPlansList(response2.lesson_plans);
                            } else {
                                $('#existingLessonPlansList').html(`
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
                                    <p class="mt-2">${i18n.no_existing}</p>
                                    <p>${i18n.please_create_new}</p>
                                </div>
                            `);
                            }
                        },
                        error: function () {
                            $('#existingLessonPlansList').html('<div class="alert alert-danger">' + i18n.fail_load_plans + '</div>');
                        }
                    });
                }
            },
            error: function () {
                $('#existingLessonPlansList').html('<div class="alert alert-danger">' + i18n.fail_load_plans + '</div>');
            }
        });
    }

    function displayExistingLessonPlansList(lessonPlans) {
        let html = '<div class="mb-3"><h6>' + i18n.select_plan_use + '</h6></div>';
        html += '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
        html += '<table class="table table-hover table-bordered">';
        html += '<thead class="thead-light" style="position: sticky; top: 0; background: white; z-index: 10;">';
        html += '<tr>';
        html += '<th>' + i18n.th_date + '</th>';
        html += '<th>' + i18n.th_subject + '</th>';
        html += '<th>' + i18n.th_class + '</th>';
        html += '<th>' + i18n.th_time + '</th>';
        html += '<th>' + i18n.th_action + '</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        lessonPlans.forEach(function (plan) {
            const planDate = new Date(plan.lesson_date);
            const formattedDate = planDate.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            const formatTime = (timeStr) => {
                if (!timeStr) return i18n.na;
                try {
                    const parts = timeStr.split(':');
                    const hours = parseInt(parts[0]);
                    const minutes = parts[1] || '00';
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    const displayHours = hours % 12 || 12;
                    return displayHours + ':' + minutes + ' ' + ampm;
                } catch (e) {
                    return timeStr;
                }
            };

            const startTime = formatTime(plan.lesson_time_start);
            const endTime = formatTime(plan.lesson_time_end);

            html += '<tr>';
            html += '<td>' + formattedDate + '</td>';
            html += '<td>' + (plan.subject || i18n.na) + '</td>';
            html += '<td>' + (plan.class_name || i18n.na) + '</td>';
            html += '<td>' + startTime + ' - ' + endTime + '</td>';
            html += '<td>';
            html += '<button class="btn btn-sm btn-primary-custom" onclick="useExistingLessonPlan(' + plan.lesson_planID + ')">';
            html += '<i class="bi bi-check-circle"></i> ' + i18n.use_this;
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';
        html += '</div>';

        $('#existingLessonPlansList').html(html);
    }

    function useExistingLessonPlan(lessonPlanID) {
        // Load the lesson plan data
        $.ajax({
            url: '{{ route("teacher.get_lesson_plan_by_id") }}',
            method: 'GET',
            data: {
                lesson_planID: lessonPlanID
            },
            success: function (response) {
                if (response.success && response.data) {
                    // Show form to select new date and time
                    showDateTimePickerForExistingPlan(response.data);
                } else {
                    Swal.fire({
                        title: i18n.error,
                        text: response.error || i18n.fail_load_plan,
                        icon: 'error',
                        confirmButtonColor: '#f5f5f5'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.fail_load_plan,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
            }
        });
    }

    function showDateTimePickerForExistingPlan(lessonPlan) {
        const formatTime = (timeStr) => {
            if (!timeStr) return '';
            try {
                const parts = timeStr.split(':');
                const hours = parts[0].padStart(2, '0');
                const minutes = parts[1] || '00';
                return hours + ':' + minutes;
            } catch (e) {
                return '';
            }
        };

        const currentDate = new Date().toISOString().split('T')[0];
        const currentStartTime = currentSessionData.startTime || formatTime(lessonPlan.lesson_time_start);
        const currentEndTime = currentSessionData.endTime || formatTime(lessonPlan.lesson_time_end);

        Swal.fire({
            title: i18n.select_new_datetime,
            html: `
            <div class="form-group text-left">
                <label>${i18n.date_label}</label>
                <input type="date" id="swal-new-date" class="form-control" value="${currentDate}" required>
            </div>
            <div class="form-group text-left">
                <label>${i18n.start_time}</label>
                <input type="time" id="swal-new-start-time" class="form-control" value="${currentStartTime}" required>
            </div>
            <div class="form-group text-left">
                <label>${i18n.end_time}</label>
                <input type="time" id="swal-new-end-time" class="form-control" value="${currentEndTime}" required>
            </div>
        `,
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: i18n.continue,
            cancelButtonText: i18n.cancel,
            width: '500px',
            preConfirm: () => {
                const date = document.getElementById('swal-new-date').value;
                const startTime = document.getElementById('swal-new-start-time').value;
                const endTime = document.getElementById('swal-new-end-time').value;

                if (!date || !startTime || !endTime) {
                    Swal.showValidationMessage(i18n.fill_all);
                    return false;
                }

                return {
                    date: date,
                    startTime: startTime,
                    endTime: endTime
                };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                // Load attendance stats for the new date
                loadExistingLessonPlanWithNewDateTime(lessonPlan, result.value.date, result.value.startTime, result.value.endTime);
            }
        });
    }

    function loadExistingLessonPlanWithNewDateTime(lessonPlan, newDate, newStartTime, newEndTime) {
        // First get attendance stats for the new date
        $.ajax({
            url: '{{ route("teacher.get_session_attendance_stats") }}',
            method: 'GET',
            data: {
                session_timetableID: currentSessionData.sessionTimetableID,
                date: newDate
            },
            success: function (response) {
                if (response.success) {
                    // Convert time format from HH:MM to HH:MM:SS if needed
                    const formatTimeForDisplay = (timeStr) => {
                        if (!timeStr) return '';
                        const parts = timeStr.split(':');
                        if (parts.length === 2) {
                            return timeStr + ':00';
                        }
                        return timeStr;
                    };

                    // Merge lesson plan data with attendance stats and new date/time
                    const mergedData = {
                        ...response.data,
                        // Override time with new time
                        start_time: formatTimeForDisplay(newStartTime),
                        end_time: formatTimeForDisplay(newEndTime),
                        // Use lesson plan content if available
                        main_competence: lessonPlan.main_competence || response.data.main_competence || '',
                        specific_competence: lessonPlan.specific_competence || response.data.specific_competence || '',
                        main_activity: lessonPlan.main_activity || response.data.main_activity || '',
                        specific_activity: lessonPlan.specific_activity || response.data.specific_activity || '',
                        teaching_learning_resources: lessonPlan.teaching_learning_resources || response.data.teaching_learning_resources || '',
                        references: lessonPlan.references || response.data.references || '',
                        remarks: lessonPlan.remarks || response.data.remarks || '',
                        reflection: lessonPlan.reflection || response.data.reflection || '',
                        evaluation: lessonPlan.evaluation || response.data.evaluation || '',
                        // Handle lesson stages (JSON array)
                        stages: lessonPlan.lesson_stages || response.data.lesson_stages || []
                    };

                    // Render form with merged data
                    renderLessonPlanForm(mergedData, newDate);

                    // Hide existing plans list and show form
                    $('#existingLessonPlansList').hide();
                    $('#existingLessonPlanContent').hide();
                    $('#createNewForm').show();
                } else {
                    Swal.fire({
                        title: i18n.error,
                        text: response.error || i18n.fail_attendance,
                        icon: 'error',
                        confirmButtonColor: '#f5f5f5'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.fail_attendance,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
            }
        });
    }

    function loadExistingLessonPlanByDate(date) {
        $('#existingLessonDate').val(date);
        loadExistingLessonPlan();
    }

    function loadCreateNewForm() {
        // Show date picker first
        Swal.fire({
            title: i18n.select_date_plan,
            html: '<input type="date" id="swal-date" class="swal2-input" value="' + new Date().toISOString().split('T')[0] + '">',
            showCancelButton: true,
            confirmButtonColor: '#f5f5f5',
            cancelButtonColor: '#6c757d',
            confirmButtonText: i18n.continue,
            cancelButtonText: i18n.cancel,
            preConfirm: () => {
                return document.getElementById('swal-date').value;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const sessionDate = result.value;

                // First check if lesson plan already exists
                $.ajax({
                    url: '{{ route("teacher.check_lesson_plan_exists") }}',
                    method: 'GET',
                    data: {
                        session_timetableID: currentSessionData.sessionTimetableID,
                        date: sessionDate
                    },
                    success: function (checkResponse) {
                        if (checkResponse.success && checkResponse.exists) {
                            Swal.fire({
                                title: i18n.already_exists,
                                text: i18n.already_exists_text,
                                icon: 'warning',
                                confirmButtonColor: '#f5f5f5',
                                confirmButtonText: 'OK'
                            });
                            return;
                        }

                        // If doesn't exist, proceed to get attendance stats
                        $.ajax({
                            url: '{{ route("teacher.get_session_attendance_stats") }}',
                            method: 'GET',
                            data: {
                                session_timetableID: currentSessionData.sessionTimetableID,
                                date: sessionDate
                            },
                            success: function (response) {
                                if (response.success) {
                                    renderLessonPlanForm(response.data, sessionDate);
                                } else {
                                    let errorMessage = response.error || i18n.fail_attendance;
                                    let icon = 'error';

                                    // Check date status
                                    if (response.date_status === 'weekend') {
                                        icon = 'info';
                                    } else if (response.date_status === 'holiday') {
                                        icon = 'warning';
                                    } else if (response.date_status === 'no_session') {
                                        icon = 'info';
                                    }

                                    Swal.fire({
                                        title: icon === 'error' ? i18n.error : i18n.notice,
                                        text: errorMessage,
                                        icon: icon,
                                        confirmButtonColor: '#f5f5f5',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    title: i18n.error,
                                    text: i18n.fail_attendance,
                                    icon: 'error',
                                    confirmButtonColor: '#f5f5f5'
                                });
                            }
                        });
                    },
                    error: function () {
                        // If check fails, proceed anyway
                        $.ajax({
                            url: '{{ route("teacher.get_session_attendance_stats") }}',
                            method: 'GET',
                            data: {
                                session_timetableID: currentSessionData.sessionTimetableID,
                                date: sessionDate
                            },
                            success: function (response) {
                                if (response.success) {
                                    renderLessonPlanForm(response.data, sessionDate);
                                } else {
                                    let errorMessage = response.error || i18n.fail_attendance;
                                    let icon = 'error';

                                    if (response.date_status === 'weekend') {
                                        icon = 'info';
                                    } else if (response.date_status === 'holiday') {
                                        icon = 'warning';
                                    } else if (response.date_status === 'no_session') {
                                        icon = 'info';
                                    }

                                    Swal.fire({
                                        title: icon === 'error' ? i18n.error : i18n.notice,
                                        text: errorMessage,
                                        icon: icon,
                                        confirmButtonColor: '#f5f5f5',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    title: i18n.error,
                                    text: i18n.fail_attendance,
                                    icon: 'error',
                                    confirmButtonColor: '#f5f5f5'
                                });
                            }
                        });
                    }
                });
            }
        });
    }

    function loadCreateNewFormDirectly(date) {
        // Load create form directly with the provided date, bypassing date picker
        if (!date) {
            console.error('Date is required for loadCreateNewFormDirectly');
            return;
        }

        // First check if lesson plan already exists
        $.ajax({
            url: '{{ route("teacher.check_lesson_plan_exists") }}',
            method: 'GET',
            data: {
                session_timetableID: currentSessionData.sessionTimetableID,
                date: date
            },
            success: function (checkResponse) {
                if (checkResponse.success && checkResponse.exists) {
                    Swal.fire({
                        title: i18n.already_exists,
                        text: i18n.already_exists_text,
                        icon: 'warning',
                        confirmButtonColor: '#f5f5f5',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // If doesn't exist, proceed to get attendance stats
                $.ajax({
                    url: '{{ route("teacher.get_session_attendance_stats") }}',
                    method: 'GET',
                    data: {
                        session_timetableID: currentSessionData.sessionTimetableID,
                        date: date
                    },
                    success: function (response) {
                        if (response.success) {
                            renderLessonPlanForm(response.data, date);
                        } else {
                            let errorMessage = response.error || i18n.fail_attendance;
                            let icon = 'error';

                            // Check date status
                            if (response.date_status === 'weekend') {
                                icon = 'info';
                            } else if (response.date_status === 'holiday') {
                                icon = 'warning';
                            } else if (response.date_status === 'no_session') {
                                icon = 'info';
                            }

                            Swal.fire({
                                title: icon === 'error' ? i18n.error : i18n.notice,
                                text: errorMessage,
                                icon: icon,
                                confirmButtonColor: '#f5f5f5',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: i18n.error,
                            text: i18n.fail_attendance,
                            icon: 'error',
                            confirmButtonColor: '#f5f5f5'
                        });
                    }
                });
            },
            error: function () {
                // If check fails, proceed anyway
                $.ajax({
                    url: '{{ route("teacher.get_session_attendance_stats") }}',
                    method: 'GET',
                    data: {
                        session_timetableID: currentSessionData.sessionTimetableID,
                        date: date
                    },
                    success: function (response) {
                        if (response.success) {
                            renderLessonPlanForm(response.data, date);
                        } else {
                            let errorMessage = response.error || i18n.fail_attendance;
                            let icon = 'error';

                            if (response.date_status === 'weekend') {
                                icon = 'info';
                            } else if (response.date_status === 'holiday') {
                                icon = 'warning';
                            } else if (response.date_status === 'no_session') {
                                icon = 'info';
                            }

                            Swal.fire({
                                title: icon === 'error' ? i18n.error : i18n.notice,
                                text: errorMessage,
                                icon: icon,
                                confirmButtonColor: '#f5f5f5',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: i18n.error,
                            text: i18n.fail_attendance,
                            icon: 'error',
                            confirmButtonColor: '#f5f5f5'
                        });
                    }
                });
            }
        });
    }

    function renderLessonPlanForm(attendanceData, date) {
        const schoolType = '{{ $schoolType }}';
        const currentYear = {{ $currentYear }};

        // Format time
        const formatTime = (timeStr) => {
            if (!timeStr) return 'N/A';
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return displayHours + ':' + minutes + ' ' + ampm;
        };

        const startTime = formatTime(attendanceData.start_time);
        const endTime = formatTime(attendanceData.end_time);
        const dateObj = new Date(date);
        const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });

        let html = `
        <div class="lesson-plan-header">LESSON PLAN</div>
        <div class="lesson-plan-header">${schoolType}</div>
        
        <table class="lesson-plan-table">
            <tr>
                <th>SUBJECT:</th>
                <td><input type="text" id="lesson_subject" value="${attendanceData.subject || ''}" readonly style="background-color: #f5f5f5;"></td>
                <th>CLASS:</th>
                <td><input type="text" id="lesson_class" value="${attendanceData.class_name || ''}" readonly style="background-color: #f5f5f5;"></td>
                <th>YEAR:</th>
                <td><input type="text" id="lesson_year" value="${currentYear}" readonly style="background-color: #f5f5f5;"></td>
            </tr>
            <tr>
                <th>TEACHER'S NAME</th>
                <td colspan="2"><input type="text" id="lesson_teacher_name" value="${attendanceData.teacher_name || ''}" readonly style="background-color: #f5f5f5; border: 1px solid #212529; padding: 2px 4px; width: 100%;"></td>
                <td colspan="3">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #212529;">
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">REGISTERED</th>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.registered_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.registered_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.registered_total || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.present_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.present_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${attendanceData.present_total || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>TIME</th>
                <td><input type="text" value="${startTime} - ${endTime}" readonly style="background-color: #f5f5f5;"></td>
                <th>DATE</th>
                <td colspan="3"><input type="text" id="lesson_date" value="${formattedDate}" readonly style="background-color: #f5f5f5;"></td>
            </tr>
        </table>
        
        <table class="lesson-plan-table">
            <tr>
                <th>MAIN COMPETENCE</th>
                <td><textarea id="main_competence" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>SPECIFIC COMPETENCE</th>
                <td><textarea id="specific_competence" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>MAIN ACTIVITY</th>
                <td><textarea id="main_activity" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>SPECIFIC ACTIVITY</th>
                <td><textarea id="specific_activity" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>TEACHING & LEARNING RESOURCES</th>
                <td><textarea id="teaching_learning_resources" rows="2"></textarea></td>
            </tr>
            <tr>
                <th>REFERENCES</th>
                <td><textarea id="references" rows="2"></textarea></td>
            </tr>
        </table>
        
        <h5 class="mt-4 mb-3">LESSON DEVELOPMENT</h5>
        <table class="lesson-plan-table">
            <thead>
                <tr>
                    <th>STAGE</th>
                    <th>TIME</th>
                    <th>TEACHING ACTIVITIES</th>
                    <th>LEARNING ACTIVITIES</th>
                    <th>ASSESSMENT CRITERIA</th>
                </tr>
            </thead>
            <tbody id="lessonStagesTable">
                <tr>
                    <td>Introduction</td>
                    <td><input type="text" class="stage-time" placeholder="e.g., 5 minutes"></td>
                    <td><textarea class="stage-teaching" rows="2"></textarea></td>
                    <td><textarea class="stage-learning" rows="2"></textarea></td>
                    <td><textarea class="stage-assessment" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td>Competence development</td>
                    <td><input type="text" class="stage-time" placeholder="e.g., 15 minutes"></td>
                    <td><textarea class="stage-teaching" rows="2"></textarea></td>
                    <td><textarea class="stage-learning" rows="2"></textarea></td>
                    <td><textarea class="stage-assessment" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td>Design</td>
                    <td><input type="text" class="stage-time" placeholder="e.g., 15 minutes"></td>
                    <td><textarea class="stage-teaching" rows="2"></textarea></td>
                    <td><textarea class="stage-learning" rows="2"></textarea></td>
                    <td><textarea class="stage-assessment" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td>Realization</td>
                    <td><input type="text" class="stage-time" placeholder="e.g., 5 minutes"></td>
                    <td><textarea class="stage-teaching" rows="2"></textarea></td>
                    <td><textarea class="stage-learning" rows="2"></textarea></td>
                    <td><textarea class="stage-assessment" rows="2"></textarea></td>
                </tr>
            </tbody>
        </table>
        
        <div class="form-group mt-3">
            <label><strong>Remarks:</strong></label>
            <div class="dotted-line">
                <textarea id="remarks" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Reflection:</strong></label>
            <div class="dotted-line">
                <textarea id="reflection" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
            <div class="dotted-line">
                <textarea class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Evaluation:</strong></label>
            <div class="dotted-line">
                <textarea id="evaluation" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
            <div class="dotted-line">
                <textarea class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="signature-container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="signature-label">Subject Teacher's Signature:</div>
                    <canvas id="teacherSignatureCanvas" class="signature-canvas" width="400" height="150"></canvas>
                    <div class="signature-actions">
                        <button type="button" class="btn btn-sm" onclick="clearTeacherSignature()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                    <input type="hidden" id="teacher_signature" name="teacher_signature">
                </div>
                <div class="col-md-6">
                    <div class="signature-label">Academic/Supervisor's Signature:</div>
                    <div style="border: 2px solid #212529; border-radius: 4px; min-height: 150px; padding: 10px; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center;">
                        <p class="text-muted mb-0">To be signed by supervisor</p>
                    </div>
                    <input type="hidden" id="supervisor_signature" name="supervisor_signature">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="lesson_date_hidden" value="${date}">
        
        <button class="btn btn-block mt-3" onclick="saveLessonPlan()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
            <i class="bi bi-save"></i> Save Changes
        </button>
    `;

        $('#createNewForm').html(html);

        // Priority:
        // 1) Local draft (if user closed modal without saving)
        // 2) Previous saved lesson plan for same subject (autofill next period)
        // 3) Values already on attendanceData (e.g. "Use existing")
        restoringLessonPlanDraft = true;
        const sessionId = currentSessionData && currentSessionData.sessionTimetableID;
        const draft = getLessonPlanDraft(sessionId, date);

        function finishRestore(sourceLabel) {
            restoringLessonPlanDraft = false;
            if (sourceLabel && typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: sourceLabel,
                    showConfirmButton: false,
                    timer: 3500
                });
            }
            // Initialize signature pad after a short delay to ensure canvas is rendered
            setTimeout(function () {
                initializeSignaturePad();
            }, 100);
        }

        if (draft && draft.fields) {
            applyLessonPlanFields(draft.fields);
            finishRestore(i18n.draft_restored);
            return;
        }

        // Populate fields from attendanceData if available (for using existing lesson plan)
        if (attendanceData.main_competence) {
            $('#main_competence').val(attendanceData.main_competence);
        }
        if (attendanceData.specific_competence) {
            $('#specific_competence').val(attendanceData.specific_competence);
        }
        if (attendanceData.main_activity) {
            $('#main_activity').val(attendanceData.main_activity);
        }
        if (attendanceData.specific_activity) {
            $('#specific_activity').val(attendanceData.specific_activity);
        }
        if (attendanceData.teaching_learning_resources || attendanceData.teaching_aids) {
            $('#teaching_learning_resources').val(attendanceData.teaching_learning_resources || attendanceData.teaching_aids);
        }
        if (attendanceData.references) {
            $('#references').val(attendanceData.references);
        }
        if (attendanceData.remarks) {
            $('#remarks').val(attendanceData.remarks);
        }
        if (attendanceData.reflection) {
            $('#reflection').val(attendanceData.reflection);
        }
        if (attendanceData.evaluation) {
            $('#evaluation').val(attendanceData.evaluation);
        }

        // Populate lesson stages if available
        if (attendanceData.stages && Array.isArray(attendanceData.stages)) {
            attendanceData.stages.forEach(function (stage, index) {
                const rows = $('#lessonStagesTable tr');
                if (rows.length > index) {
                    const row = $(rows[index]);
                    // Handle different stage field names
                    if (stage.time) row.find('.stage-time').val(stage.time);
                    if (stage.teaching_activities || stage.teaching) {
                        row.find('.stage-teaching').val(stage.teaching_activities || stage.teaching);
                    }
                    if (stage.learning_activities || stage.learning) {
                        row.find('.stage-learning').val(stage.learning_activities || stage.learning);
                    }
                    if (stage.assessment_criteria || stage.assessment) {
                        row.find('.stage-assessment').val(stage.assessment_criteria || stage.assessment);
                    }
                }
            });
        }

        // If form is still empty, autofill from previous period of same subject
        const hasContent = !!($('#main_competence').val() || $('#specific_competence').val() || $('#main_activity').val());
        if (!hasContent && sessionId) {
            $.ajax({
                url: '{{ route("teacher.get_previous_lesson_plan") }}',
                method: 'GET',
                data: {
                    session_timetableID: sessionId,
                    before_date: date
                },
                success: function (resp) {
                    if (resp && resp.success && resp.found && resp.data) {
                        applyLessonPlanFields(resp.data);
                        finishRestore(i18n.prev_loaded);
                    } else {
                        finishRestore(null);
                    }
                },
                error: function () {
                    finishRestore(null);
                }
            });
            return;
        }

        finishRestore(null);
    }

    function saveLessonPlan() {
        // Collect lesson stages data
        const stages = [];
        $('#lessonStagesTable tr').each(function () {
            const stageName = $(this).find('td:first').text().trim();
            const time = $(this).find('.stage-time').val();
            const teaching = $(this).find('.stage-teaching').val();
            const learning = $(this).find('.stage-learning').val();
            const assessment = $(this).find('.stage-assessment').val();

            if (stageName) {
                stages.push({
                    stage: stageName,
                    time: time,
                    teaching_activities: teaching,
                    learning_activities: learning,
                    assessment_criteria: assessment
                });
            }
        });

        const formData = {
            session_timetableID: currentSessionData.sessionTimetableID,
            lesson_date: $('#lesson_date_hidden').val(),
            main_competence: $('#main_competence').val(),
            specific_competence: $('#specific_competence').val(),
            main_activity: $('#main_activity').val(),
            specific_activity: $('#specific_activity').val(),
            teaching_learning_resources: $('#teaching_learning_resources').val(),
            references: $('#references').val(),
            lesson_stages: stages,
            remarks: $('#remarks').val(),
            reflection: $('#reflection').val(),
            evaluation: $('#evaluation').val(),
            teacher_signature: teacherSignaturePad && !teacherSignaturePad.isEmpty() ? teacherSignaturePad.toDataURL() : '',
            supervisor_signature: $('#supervisor_signature').val() || '',
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: '{{ route("teacher.store_lesson_plan") }}',
            method: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    clearLessonPlanDraft(
                        formData.session_timetableID,
                        formData.lesson_date
                    );
                    Swal.fire({
                        title: i18n.success,
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#940000'
                    }).then(() => {
                        sxHideModal('#lessonPlanModal');
                    });
                } else {
                    Swal.fire({
                        title: i18n.error,
                        text: response.error || i18n.fail_save,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function (xhr) {
                const error = xhr.responseJSON?.error || i18n.fail_save;
                Swal.fire({
                    title: i18n.error,
                    text: error,
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    }

    function loadExistingLessonPlan() {
        const date = $('#existingLessonDate').val();
        if (!date) return;

        $.ajax({
            url: '{{ route("teacher.get_lesson_plan") }}',
            method: 'GET',
            data: {
                session_timetableID: currentSessionData.sessionTimetableID,
                date: date
            },
            success: function (response) {
                if (response.success) {
                    $('#existingLessonPlanContent').html('<div class="alert alert-success">Lesson plan found! You can use this as a template.</div>');
                } else {
                    $('#existingLessonPlanContent').html('<div class="alert alert-info">' + response.error + '</div>');
                }
            }
        });
    }


    function toggleManageFilterOptions() {
        const filterType = $('#manageFilterType').val();
        if (filterType === 'single_date') {
            $('#manageSingleDateFilter').show();
            $('#manageDateRangeFilter1').hide();
            $('#manageDateRangeFilter2').hide();
            $('#manageYearFilter').hide();
        } else if (filterType === 'date_range') {
            $('#manageSingleDateFilter').hide();
            $('#manageDateRangeFilter1').show();
            $('#manageDateRangeFilter2').show();
            $('#manageYearFilter').hide();
        } else {
            $('#manageSingleDateFilter').hide();
            $('#manageDateRangeFilter1').hide();
            $('#manageDateRangeFilter2').hide();
            $('#manageYearFilter').show();
        }
    }

    function loadLessonPlansForManage() {
        const filterType = $('#manageFilterType').val();
        let url = '{{ route("teacher.get_lesson_plans_by_filter") }}';
        let data = {
            session_timetableID: currentSessionData?.sessionTimetableID,
            filter_type: filterType
        };

        if (filterType === 'single_date') {
            const date = $('#manageLessonDate').val();
            if (!date) {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.please_select_date,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
                return;
            }
            data.from_date = date;
            data.to_date = date;
            data.filter_type = 'date_range';
        } else if (filterType === 'date_range') {
            const fromDate = $('#manageFromDate').val();
            const toDate = $('#manageToDate').val();

            if (!fromDate || !toDate) {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.please_select_range,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
                return;
            }

            if (new Date(fromDate) > new Date(toDate)) {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.from_gt_to,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
                return;
            }

            data.from_date = fromDate;
            data.to_date = toDate;
        } else {
            const year = $('#manageYear').val();
            if (!year) {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.please_enter_year,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
                return;
            }
            data.year = year;
        }

        $('#manageLessonPlanContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">${i18n.loading}</span>
            </div>
            <p class="mt-3 text-muted">${i18n.loading_plans}</p>
        </div>
    `);

        const sessionsForManage = Array.isArray(currentSubjectSessions) && currentSubjectSessions.length > 0
            ? currentSubjectSessions
            : (currentSessionData?.sessionTimetableID ? [currentSessionData] : []);

        if (sessionsForManage.length === 0) {
            $('#manageLessonPlanContent').html(`
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> ${i18n.select_subject_first}
            </div>
        `);
            return;
        }

        const requests = sessionsForManage.map(session => {
            return $.ajax({
                url: url,
                method: 'GET',
                data: {
                    ...data,
                    session_timetableID: session.session_timetableID || session.sessionTimetableID
                }
            });
        });

        $.when.apply($, requests)
            .done(function () {
                const responses = requests.length === 1 ? [arguments[0]] : Array.from(arguments).map(arg => arg[0]);
                const allPlans = [];
                responses.forEach(response => {
                    if (response && response.success && Array.isArray(response.lesson_plans)) {
                        allPlans.push(...response.lesson_plans);
                    }
                });
                const uniquePlans = [];
                const seen = new Set();
                allPlans.forEach(plan => {
                    if (!plan || seen.has(plan.lesson_planID)) return;
                    seen.add(plan.lesson_planID);
                    uniquePlans.push(plan);
                });

                if (uniquePlans.length > 0) {
                    renderManageLessonPlansList(uniquePlans);
                } else {
                    $('#manageLessonPlanContent').html(`
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> ${i18n.none_for_filter}
                    </div>
                `);
                }
            })
            .fail(function (xhr) {
                const error = xhr.responseJSON?.error || i18n.fail_load_plans;
                $('#manageLessonPlanContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> ${error}
                </div>
            `);
            });
    }

    function renderManageLessonPlansList(lessonPlans) {
        let html = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">${(i18n.found_manage || '').replace(':count', lessonPlans.length)}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>${i18n.th_date}</th>
                                <th>${i18n.th_subject}</th>
                                <th>${i18n.th_class}</th>
                                <th>${i18n.th_time}</th>
                                <th>${i18n.th_status}</th>
                                <th>${i18n.th_actions}</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

        lessonPlans.forEach(function (plan) {
            const dateObj = new Date(plan.lesson_date);
            const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const formatTime = (timeStr) => {
                if (!timeStr) return 'N/A';
                const parts = timeStr.split(':');
                const hours = parseInt(parts[0]);
                const minutes = parts[1];
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const displayHours = hours % 12 || 12;
                return displayHours + ':' + minutes + ' ' + ampm;
            };

            html += `
            <tr>
                <td>${formattedDate}</td>
                <td>${plan.subject || 'N/A'}</td>
                <td>${plan.class_name || 'N/A'}</td>
                <td>${formatTime(plan.lesson_time_start)} - ${formatTime(plan.lesson_time_end)}</td>
                <td>
                    ${plan.sent_to_admin ?
                    '<span class="badge badge-success">' + i18n.sent_admin + '</span>' :
                    '<span class="badge badge-secondary">' + i18n.not_sent + '</span>'
                }
                </td>
                <td>
                    <button class="btn btn-sm" onclick="loadLessonPlanForManageByID(${plan.lesson_planID})" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                        <i class="bi bi-pencil-square"></i> ${i18n.edit}
                    </button>
                </td>
            </tr>
        `;
        });

        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

        $('#manageLessonPlanContent').html(html);
    }

    function loadLessonPlanForManageByID(lessonPlanID) {
        $.ajax({
            url: '{{ route("teacher.get_lesson_plan_by_id") }}',
            method: 'GET',
            data: { lesson_planID: lessonPlanID },
            success: function (response) {
                if (response.success) {
                    // Render form for editing
                    renderLessonPlanFormForEdit(response.data);
                } else {
                    Swal.fire({
                        title: i18n.error,
                        text: response.error || i18n.fail_load_plan,
                        icon: 'error',
                        confirmButtonColor: '#f5f5f5'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.fail_load_plan,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
            }
        });
    }

    function loadLessonPlanForManage() {
        // For backward compatibility - single date selection
        const date = $('#manageLessonDate').val();
        if (!date) return;

        $.ajax({
            url: '{{ route("teacher.get_lesson_plan") }}',
            method: 'GET',
            data: {
                session_timetableID: currentSessionData.sessionTimetableID,
                date: date
            },
            success: function (response) {
                if (response.success) {
                    // Render form for editing
                    renderLessonPlanFormForEdit(response.data);
                } else {
                    $('#manageLessonPlanContent').html('<div class="alert alert-info">' + response.error + '</div>');
                }
            }
        });
    }

    function renderLessonPlanFormForEdit(data) {
        const schoolType = '{{ $schoolType }}';
        const currentYear = {{ $currentYear }};

        const formatTime = (timeStr) => {
            if (!timeStr) return 'N/A';
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return displayHours + ':' + minutes + ' ' + ampm;
        };

        const startTime = formatTime(data.lesson_time_start);
        const endTime = formatTime(data.lesson_time_end);
        const dateObj = new Date(data.lesson_date);
        const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });

        let html = `
        <div class="lesson-plan-header">LESSON PLAN</div>
        <div class="lesson-plan-header">${schoolType}</div>
        
        <table class="lesson-plan-table">
            <tr>
                <th>SUBJECT:</th>
                <td><input type="text" value="${data.subject || ''}" readonly style="background-color: #f5f5f5;"></td>
                <th>CLASS:</th>
                <td><input type="text" value="${data.class_name || ''}" readonly style="background-color: #f5f5f5;"></td>
                <th>YEAR:</th>
                <td><input type="text" value="${data.year}" readonly style="background-color: #f5f5f5;"></td>
            </tr>
            <tr>
                <th>TEACHER'S NAME</th>
                <td colspan="2"><input type="text" value="${data.teacher_name || ''}" readonly style="background-color: #f5f5f5; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                <td colspan="3">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #212529;">
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">REGISTERED</th>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.registered_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.registered_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.registered_total || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.present_girls || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.present_boys || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                            <td style="border: 1px solid #212529; padding: 3px 5px;"><input type="text" value="${data.present_total || 0}" readonly style="background-color: #f5f5f5; text-align: center; border: 1px solid #212529; padding: 2px 4px; width: 100%; font-size: 0.9rem;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>TIME</th>
                <td><input type="text" value="${startTime} - ${endTime}" readonly style="background-color: #f5f5f5;"></td>
                <th>DATE</th>
                <td colspan="3"><input type="text" value="${formattedDate}" readonly style="background-color: #f5f5f5;"></td>
            </tr>
        </table>
        
        <table class="lesson-plan-table">
            <tr>
                <th>MAIN COMPETENCE</th>
                <td><textarea id="edit_main_competence" rows="2">${data.main_competence || ''}</textarea></td>
            </tr>
            <tr>
                <th>SPECIFIC COMPETENCE</th>
                <td><textarea id="edit_specific_competence" rows="2">${data.specific_competence || ''}</textarea></td>
            </tr>
            <tr>
                <th>MAIN ACTIVITY</th>
                <td><textarea id="edit_main_activity" rows="2">${data.main_activity || ''}</textarea></td>
            </tr>
            <tr>
                <th>SPECIFIC ACTIVITY</th>
                <td><textarea id="edit_specific_activity" rows="2">${data.specific_activity || ''}</textarea></td>
            </tr>
            <tr>
                <th>TEACHING & LEARNING RESOURCES</th>
                <td><textarea id="edit_teaching_learning_resources" rows="2">${data.teaching_learning_resources || ''}</textarea></td>
            </tr>
            <tr>
                <th>REFERENCES</th>
                <td><textarea id="edit_references" rows="2">${data.references || ''}</textarea></td>
            </tr>
        </table>
        
        <h5 class="mt-4 mb-3">LESSON DEVELOPMENT</h5>
        <table class="lesson-plan-table">
            <thead>
                <tr>
                    <th>STAGE</th>
                    <th>TIME</th>
                    <th>TEACHING ACTIVITIES</th>
                    <th>LEARNING ACTIVITIES</th>
                    <th>ASSESSMENT CRITERIA</th>
                </tr>
            </thead>
            <tbody id="editLessonStagesTable">
    `;

        const stages = data.lesson_stages || [];
        const stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];

        stageNames.forEach((stageName, index) => {
            const stage = stages.find(s => s.stage === stageName) || {};
            html += `
            <tr>
                <td>${stageName}</td>
                <td><input type="text" class="edit-stage-time" value="${stage.time || ''}" placeholder="e.g., 5 minutes"></td>
                <td><textarea class="edit-stage-teaching" rows="2">${stage.teaching_activities || ''}</textarea></td>
                <td><textarea class="edit-stage-learning" rows="2">${stage.learning_activities || ''}</textarea></td>
                <td><textarea class="edit-stage-assessment" rows="2">${stage.assessment_criteria || ''}</textarea></td>
            </tr>
        `;
        });

        html += `
            </tbody>
        </table>
        
        <div class="form-group mt-3">
            <label><strong>Remarks:</strong></label>
            <div class="dotted-line">
                <textarea id="edit_remarks" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;">${data.remarks || ''}</textarea>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Reflection:</strong></label>
            <div class="dotted-line">
                <textarea id="edit_reflection" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;">${data.reflection || ''}</textarea>
            </div>
            <div class="dotted-line">
                <textarea class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Evaluation:</strong></label>
            <div class="dotted-line">
                <textarea id="edit_evaluation" class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;">${data.evaluation || ''}</textarea>
            </div>
            <div class="dotted-line">
                <textarea class="form-control" rows="2" style="border: none; background: transparent; resize: none; min-height: 30px;"></textarea>
            </div>
        </div>
        
        <div class="signature-container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="signature-label">Subject Teacher's Signature:</div>
                    <canvas id="editTeacherSignatureCanvas" class="signature-canvas" width="400" height="150"></canvas>
                    <div class="signature-actions">
                        <button type="button" class="btn btn-sm" onclick="clearEditTeacherSignature()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                    <input type="hidden" id="edit_teacher_signature" name="edit_teacher_signature" value="${data.teacher_signature || ''}">
                    ${data.teacher_signature ? `<img src="${data.teacher_signature}" class="signature-preview mt-2" style="max-width: 400px;">` : ''}
                </div>
                <div class="col-md-6">
                    <div class="signature-label">Academic/Supervisor's Signature:</div>
                    ${data.supervisor_signature ?
                `<img src="${data.supervisor_signature}" class="signature-preview mt-2" style="max-width: 400px;">` :
                `<div style="border: 2px solid #212529; border-radius: 4px; min-height: 150px; padding: 10px; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center;">
                            <p class="text-muted mb-0">To be signed by supervisor</p>
                        </div>`
            }
                    <input type="hidden" id="edit_supervisor_signature" name="edit_supervisor_signature" value="${data.supervisor_signature || ''}">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="edit_lesson_planID" value="${data.lesson_planID}">
        <input type="hidden" id="edit_sent_to_admin" value="${data.sent_to_admin || false}">
        
        <div class="row mt-3">
            <div class="col-md-6">
                <button class="btn btn-block" onclick="updateLessonPlan()" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
            <i class="bi bi-save"></i> Update Changes
        </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-block" onclick="sendLessonPlanToAdmin(${data.lesson_planID})" style="background-color: #940000; color: #ffffff; border: 1px solid #940000;" ${data.sent_to_admin ? 'disabled' : ''}>
                    <i class="bi bi-send"></i> ${data.sent_to_admin ? 'Sent to Admin' : 'Send to Admin'}
                </button>
            </div>
        </div>
    `;

        $('#manageLessonPlanContent').html(html);

        // Initialize signature pad for edit form
        setTimeout(function () {
            initializeEditSignaturePad(data.teacher_signature);
        }, 100);
    }

    let teacherSignaturePad = null;

    function initializeSignaturePad() {
        const canvas = document.getElementById('teacherSignatureCanvas');
        if (canvas) {
            teacherSignaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 1,
                maxWidth: 3,
            });

            // Handle resize
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const width = canvas.offsetWidth;
                const height = canvas.offsetHeight;
                canvas.width = width * ratio;
                canvas.height = height * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                if (teacherSignaturePad) {
                    teacherSignaturePad.clear();
                }
            }

            // Initial resize
            resizeCanvas();

            // Handle window resize
            window.addEventListener('resize', resizeCanvas);
        }
    }

    function clearTeacherSignature() {
        if (teacherSignaturePad) {
            teacherSignaturePad.clear();
            $('#teacher_signature').val('');
        }
    }

    let editTeacherSignaturePad = null;

    function initializeEditSignaturePad(existingSignature) {
        const canvas = document.getElementById('editTeacherSignatureCanvas');
        if (canvas) {
            editTeacherSignaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 1,
                maxWidth: 3,
            });

            // Load existing signature if available
            if (existingSignature) {
                const img = new Image();
                img.onload = function () {
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    editTeacherSignaturePad.fromDataURL(existingSignature);
                };
                img.src = existingSignature;
            }

            // Handle resize
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const width = canvas.offsetWidth;
                const height = canvas.offsetHeight;
                canvas.width = width * ratio;
                canvas.height = height * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                if (editTeacherSignaturePad && existingSignature) {
                    const img = new Image();
                    img.onload = function () {
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    };
                    img.src = existingSignature;
                }
            }

            // Initial resize
            resizeCanvas();

            // Handle window resize
            window.addEventListener('resize', resizeCanvas);
        }
    }

    function clearEditTeacherSignature() {
        if (editTeacherSignaturePad) {
            editTeacherSignaturePad.clear();
            $('#edit_teacher_signature').val('');
        }
    }

    function updateLessonPlan() {
        const lessonPlanID = $('#edit_lesson_planID').val();

        const stages = [];
        $('#editLessonStagesTable tr').each(function () {
            const stageName = $(this).find('td:first').text().trim();
            const time = $(this).find('.edit-stage-time').val();
            const teaching = $(this).find('.edit-stage-teaching').val();
            const learning = $(this).find('.edit-stage-learning').val();
            const assessment = $(this).find('.edit-stage-assessment').val();

            if (stageName) {
                stages.push({
                    stage: stageName,
                    time: time,
                    teaching_activities: teaching,
                    learning_activities: learning,
                    assessment_criteria: assessment
                });
            }
        });

        const formData = {
            main_competence: $('#edit_main_competence').val(),
            specific_competence: $('#edit_specific_competence').val(),
            main_activity: $('#edit_main_activity').val(),
            specific_activity: $('#edit_specific_activity').val(),
            teaching_learning_resources: $('#edit_teaching_learning_resources').val(),
            references: $('#edit_references').val(),
            lesson_stages: stages,
            remarks: $('#edit_remarks').val(),
            reflection: $('#edit_reflection').val(),
            evaluation: $('#edit_evaluation').val(),
            teacher_signature: editTeacherSignaturePad && !editTeacherSignaturePad.isEmpty() ? editTeacherSignaturePad.toDataURL() : $('#edit_teacher_signature').val(),
            supervisor_signature: $('#edit_supervisor_signature').val() || '',
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: '{{ route("teacher.update_lesson_plan", ":id") }}'.replace(':id', lessonPlanID),
            method: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: i18n.success,
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#940000'
                    }).then(() => {
                        loadLessonPlanForManage();
                    });
                } else {
                    Swal.fire({
                        title: i18n.error,
                        text: response.error || i18n.fail_update,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function (xhr) {
                const error = xhr.responseJSON?.error || i18n.fail_update;
                Swal.fire({
                    title: i18n.error,
                    text: error,
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    }

    function loadLessonPlanForView() {
        const date = $('#viewLessonDate').val();
        if (!date) return;

        $.ajax({
            url: '{{ route("teacher.get_lesson_plan") }}',
            method: 'GET',
            data: {
                session_timetableID: currentSessionData.sessionTimetableID,
                date: date
            },
            success: function (response) {
                if (response.success) {
                    // Render read-only view
                    renderLessonPlanView(response.data);
                } else {
                    $('#viewLessonPlanContent').html('<div class="alert alert-info">' + response.error + '</div>');
                }
            }
        });
    }

    function renderLessonPlanView(data) {
        const schoolType = '{{ $schoolType }}';
        const currentYear = {{ $currentYear }};

        const formatTime = (timeStr) => {
            if (!timeStr) return 'N/A';
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return displayHours + ':' + minutes + ' ' + ampm;
        };

        const startTime = formatTime(data.lesson_time_start);
        const endTime = formatTime(data.lesson_time_end);
        const dateObj = new Date(data.lesson_date);
        const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });

        let html = `
        <div class="lesson-plan-header">LESSON PLAN</div>
        <div class="lesson-plan-header">${schoolType}</div>
        
        <table class="lesson-plan-table">
            <tr>
                <th>SUBJECT:</th>
                <td>${data.subject || 'N/A'}</td>
                <th>CLASS:</th>
                <td>${data.class_name || 'N/A'}</td>
                <th>YEAR:</th>
                <td>${data.year}</td>
            </tr>
            <tr>
                <th>TEACHER'S NAME</th>
                <td colspan="2">${data.teacher_name || 'N/A'}</td>
                <td colspan="3">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #212529;">
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">REGISTERED</th>
                            <th style="border: 1px solid #212529; text-align: center; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;" colspan="3">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">GIRLS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">BOYS</th>
                            <th style="border: 1px solid #212529; padding: 3px 5px; background-color: #f5f5f5; font-size: 0.9rem;">TOTAL</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.registered_girls || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.registered_boys || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.registered_total || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.present_girls || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.present_boys || 0}</td>
                            <td style="border: 1px solid #212529; text-align: center; padding: 3px 5px; font-size: 0.9rem;">${data.present_total || 0}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>TIME</th>
                <td>${startTime} - ${endTime}</td>
                <th>DATE</th>
                <td colspan="3">${formattedDate}</td>
            </tr>
        </table>
        
        <table class="lesson-plan-table">
            <tr>
                <th>MAIN COMPETENCE</th>
                <td>${data.main_competence || ''}</td>
            </tr>
            <tr>
                <th>SPECIFIC COMPETENCE</th>
                <td>${data.specific_competence || ''}</td>
            </tr>
            <tr>
                <th>MAIN ACTIVITY</th>
                <td>${data.main_activity || ''}</td>
            </tr>
            <tr>
                <th>SPECIFIC ACTIVITY</th>
                <td>${data.specific_activity || ''}</td>
            </tr>
            <tr>
                <th>TEACHING & LEARNING RESOURCES</th>
                <td>${data.teaching_learning_resources || ''}</td>
            </tr>
            <tr>
                <th>REFERENCES</th>
                <td>${data.references || ''}</td>
            </tr>
        </table>
        
        <h5 class="mt-4 mb-3">LESSON DEVELOPMENT</h5>
        <table class="lesson-plan-table">
            <thead>
                <tr>
                    <th>STAGE</th>
                    <th>TIME</th>
                    <th>TEACHING ACTIVITIES</th>
                    <th>LEARNING ACTIVITIES</th>
                    <th>ASSESSMENT CRITERIA</th>
                </tr>
            </thead>
            <tbody>
    `;

        const stages = data.lesson_stages || [];
        const stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];

        stageNames.forEach((stageName) => {
            const stage = stages.find(s => s.stage === stageName) || {};
            html += `
            <tr>
                <td>${stageName}</td>
                <td>${stage.time || ''}</td>
                <td>${stage.teaching_activities || ''}</td>
                <td>${stage.learning_activities || ''}</td>
                <td>${stage.assessment_criteria || ''}</td>
            </tr>
        `;
        });

        html += `
            </tbody>
        </table>
        
        <div class="form-group mt-3">
            <label><strong>Remarks:</strong></label>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;">${data.remarks || ''}</p>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Reflection:</strong></label>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;">${data.reflection || ''}</p>
            </div>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;"></p>
            </div>
        </div>
        
        <div class="form-group mt-3">
            <label><strong>Evaluation:</strong></label>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;">${data.evaluation || ''}</p>
            </div>
            <div class="dotted-line">
                <p style="margin: 0; padding: 5px 0;"></p>
            </div>
        </div>
        
        <div class="signature-container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="signature-label">Subject Teacher's Signature:</div>
                    ${data.teacher_signature ?
                `<img src="${data.teacher_signature}" class="signature-preview" style="max-width: 100%; border: 2px solid #212529; border-radius: 4px;">` :
                `<div style="border: 2px solid #212529; border-radius: 4px; min-height: 150px; padding: 10px; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center;">
                            <p class="text-muted mb-0">No signature</p>
                        </div>`
            }
                </div>
                <div class="col-md-6">
                    <div class="signature-label">Academic/Supervisor's Signature:</div>
                    ${data.supervisor_signature ?
                `<img src="${data.supervisor_signature}" class="signature-preview" style="max-width: 100%; border: 2px solid #212529; border-radius: 4px;">` :
                `<div style="border: 2px solid #212529; border-radius: 4px; min-height: 150px; padding: 10px; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center;">
                            <p class="text-muted mb-0">To be signed by supervisor</p>
                        </div>`
            }
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 mb-3">
            <button class="btn btn-lg btn-session-action" onclick="downloadLessonPlanPDF('${data.lesson_plan_id || data.lesson_planID || ''}', '${data.lesson_date || ''}')">
                <i class="bi bi-download"></i> ${i18n.download_pdf}
            </button>
        </div>
    `;

    $('#viewLessonPlanContent').html(html);
}

    function downloadLessonPlanPDF(lessonPlanID, date) {
        if (!lessonPlanID) {
            Swal.fire({
                title: i18n.error,
                text: 'Lesson plan ID is missing',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
            return;
        }

        // Show progress modal
        sxShowModal('#downloadProgressModal');
        $('#pdfProgressBar').css('width', '0%');
        $('#pdfProgressPercent').text('0%');

        let progress = 0;
        const interval = setInterval(function() {
            if (progress < 95) {
                let inc = 2 + Math.random() * 5;
                if (progress > 50) inc = 1 + Math.random() * 2;
                if (progress > 80) inc = 0.5 + Math.random();
                
                progress += inc;
                if (progress > 95) progress = 95;
                
                $('#pdfProgressBar').css('width', progress + '%');
                $('#pdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 300);

        // Construct URL
        const url = `{{ url('teacher/lesson-plan/download-pdf') }}/${lessonPlanID}`;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Server error: ' + response.statusText);
                return response.blob();
            })
            .then(blob => {
                clearInterval(interval);
                $('#pdfProgressBar').css('width', '100%');
                $('#pdfProgressPercent').text('100%');
                
                setTimeout(() => {
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    
                    const filename = `Lesson_Plan_${date || 'Report'}.pdf`;
                    a.download = filename;
                    
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    document.body.removeChild(a);
                    
                    setTimeout(() => {
                        sxHideModal('#downloadProgressModal');
                        setTimeout(() => {
                            $('#pdfProgressBar').css('width', '0%');
                            $('#pdfProgressPercent').text('0%');
                        }, 500);
                    }, 800);
                }, 400);
            })
            .catch(error => {
                clearInterval(interval);
                sxHideModal('#downloadProgressModal');
                Swal.fire({
                    title: i18n.error,
                    text: 'Failed to generate PDF: ' + error.message,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
                console.error('PDF Export Error:', error);
            });
    }
    function toggleFilterOptions() {
        const filterType = $('#filterType').val();
        if (filterType === 'year') {
            $('#dateRangeFilter').hide();
            $('#dateRangeFilter2').hide();
            $('#yearFilter').show();
        } else {
            $('#dateRangeFilter').show();
            $('#dateRangeFilter2').show();
            $('#yearFilter').hide();
        }
    }

    function loadLessonPlansByFilter() {
        const filterType = $('#filterType').val();
        let url = '{{ route("teacher.get_lesson_plans_by_filter") }}';
        let data = {
            session_timetableID: currentSessionData?.sessionTimetableID,
            filter_type: filterType
        };

        if (filterType === 'date_range') {
            const fromDate = $('#viewFromDate').val();
            const toDate = $('#viewToDate').val();

            if (!fromDate || !toDate) {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.please_select_range,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
                return;
            }

            if (new Date(fromDate) > new Date(toDate)) {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.from_gt_to,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
                return;
            }

            data.from_date = fromDate;
            data.to_date = toDate;
        } else {
            const year = $('#viewYear').val();
            if (!year) {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.please_enter_year,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
                return;
            }
            data.year = year;
        }

        $('#viewLessonPlanContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">${i18n.loading}</span>
            </div>
            <p class="mt-3 text-muted">${i18n.loading_plans}</p>
        </div>
    `);

        const sessionsForView = Array.isArray(currentSubjectSessions) && currentSubjectSessions.length > 0
            ? currentSubjectSessions
            : (currentSessionData?.sessionTimetableID ? [currentSessionData] : []);

        if (sessionsForView.length === 0) {
            $('#viewLessonPlanContent').html(`
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> ${i18n.select_subject_first}
            </div>
        `);
            return;
        }

        const requests = sessionsForView.map(session => {
            return $.ajax({
                url: url,
                method: 'GET',
                data: {
                    ...data,
                    session_timetableID: session.session_timetableID || session.sessionTimetableID
                }
            });
        });

        $.when.apply($, requests)
            .done(function () {
                const responses = requests.length === 1 ? [arguments[0]] : Array.from(arguments).map(arg => arg[0]);
                const allPlans = [];
                responses.forEach(response => {
                    if (response && response.success && Array.isArray(response.lesson_plans)) {
                        allPlans.push(...response.lesson_plans);
                    }
                });
                const uniquePlans = [];
                const seen = new Set();
                allPlans.forEach(plan => {
                    if (!plan || seen.has(plan.lesson_planID)) return;
                    seen.add(plan.lesson_planID);
                    uniquePlans.push(plan);
                });

                if (uniquePlans.length > 0) {
                    renderLessonPlansList(uniquePlans);
                } else {
                    $('#viewLessonPlanContent').html(`
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> ${i18n.none_for_filter}
                    </div>
                `);
                }
            })
            .fail(function (xhr) {
                const error = xhr.responseJSON?.error || i18n.fail_load_plans;
                $('#viewLessonPlanContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> ${error}
                </div>
            `);
            });
    }

    function renderLessonPlansList(lessonPlans) {
        let html = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">${(i18n.found_view || '').replace(':count', lessonPlans.length)}</h6>
                <button class="btn btn-sm" onclick="downloadAllLessonPlansPDF()" style="background-color: #940000; color: #ffffff; border: 1px solid #940000;">
                    <i class="bi bi-download"></i> ${i18n.download_pdf}
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>${i18n.th_date}</th>
                                <th>${i18n.th_subject}</th>
                                <th>${i18n.th_class}</th>
                                <th>${i18n.th_time}</th>
                                <th>${i18n.th_status}</th>
                                <th>${i18n.th_actions}</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

        lessonPlans.forEach(function (plan) {
            const dateObj = new Date(plan.lesson_date);
            const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const formatTime = (timeStr) => {
                if (!timeStr) return 'N/A';
                const parts = timeStr.split(':');
                const hours = parseInt(parts[0]);
                const minutes = parts[1];
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const displayHours = hours % 12 || 12;
                return displayHours + ':' + minutes + ' ' + ampm;
            };

            html += `
            <tr>
                <td>${formattedDate}</td>
                <td>${plan.subject || 'N/A'}</td>
                <td>${plan.class_name || 'N/A'}</td>
                <td>${formatTime(plan.lesson_time_start)} - ${formatTime(plan.lesson_time_end)}</td>
                <td>
                    ${plan.sent_to_admin ?
                    '<span class="badge badge-success">' + i18n.sent_admin + '</span>' :
                    '<span class="badge badge-secondary">' + i18n.not_sent + '</span>'
                }
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-corporate" onclick="viewSingleLessonPlan(${plan.lesson_planID})">
                        <i class="bi bi-eye"></i> ${i18n.view}
                    </button>
                    <button class="btn btn-sm btn-outline-corporate" onclick="downloadLessonPlanPDF(${plan.lesson_planID}, '${plan.lesson_date}')">
                        <i class="bi bi-download"></i> ${i18n.download_pdf}
                    </button>
                </td>
            </tr>
        `;
        });

        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

        // Store lesson plans for bulk download
        window.filteredLessonPlans = lessonPlans;

        $('#viewLessonPlanContent').html(html);
    }

    function viewSingleLessonPlan(lessonPlanID) {
        $.ajax({
            url: '{{ route("teacher.get_lesson_plan_by_id") }}',
            method: 'GET',
            data: { lesson_planID: lessonPlanID },
            success: function (response) {
                if (response.success) {
                    renderLessonPlanView(response.data);
                    sxShowTab('#view-tab');
                } else {
                    Swal.fire({
                        title: i18n.error,
                        text: response.error || i18n.fail_load_plan,
                        icon: 'error',
                        confirmButtonColor: '#f5f5f5'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: i18n.error,
                    text: i18n.fail_load_plan,
                    icon: 'error',
                    confirmButtonColor: '#f5f5f5'
                });
            }
        });
    }


    function downloadAllLessonPlansPDF() {
        if (!window.filteredLessonPlans || window.filteredLessonPlans.length === 0) {
            Swal.fire({ title: i18n.error, text: i18n.no_plans_download, icon: 'error', confirmButtonColor: '#940000' });
            return;
        }

        const lessonPlanIDs = window.filteredLessonPlans.map(p => p.lesson_planID);

        // Show progress modal
        sxShowModal('#downloadProgressModal');
        $('#pdfProgressBar').css('width', '0%');
        $('#pdfProgressPercent').text('0%');

        let progress = 0;
        const interval = setInterval(function() {
            if (progress < 95) {
                let inc = 2 + Math.random() * 5;
                if (progress > 50) inc = 1 + Math.random() * 2;
                if (progress > 80) inc = 0.5 + Math.random();
                progress += inc;
                if (progress > 95) progress = 95;
                $('#pdfProgressBar').css('width', progress + '%');
                $('#pdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 300);

        fetch('{{ route("teacher.download_bulk_lesson_plans_pdf") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({ lesson_plan_ids: lessonPlanIDs })
        })
        .then(response => {
            if (!response.ok) throw new Error('Server error: ' + response.statusText);
            return response.blob();
        })
        .then(blob => {
            clearInterval(interval);
            $('#pdfProgressBar').css('width', '100%');
            $('#pdfProgressPercent').text('100%');
            setTimeout(() => {
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = downloadUrl;
                a.download = `Lesson_Plans_Bulk_${new Date().toISOString().split('T')[0]}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(downloadUrl);
                document.body.removeChild(a);
                setTimeout(() => {
                    sxHideModal('#downloadProgressModal');
                    setTimeout(() => { $('#pdfProgressBar').css('width', '0%'); $('#pdfProgressPercent').text('0%'); }, 500);
                }, 800);
            }, 400);
        })
        .catch(error => {
            clearInterval(interval);
            sxHideModal('#downloadProgressModal');
            Swal.fire({ title: i18n.error, text: 'Failed to generate PDF: ' + error.message, icon: 'error', confirmButtonColor: '#940000' });
            console.error('Bulk PDF Error:', error);
        });
    }

    function sendLessonPlanToAdmin(lessonPlanID) {
        Swal.fire({
            title: 'Send to Admin?',
            text: 'Are you sure you want to send this lesson plan to admin for review?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Send',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("teacher.send_lesson_plan_to_admin") }}',
                    method: 'POST',
                    data: {
                        lesson_planID: lessonPlanID,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: i18n.success,
                                text: response.message || 'Lesson plan sent to admin successfully',
                                icon: 'success',
                                confirmButtonColor: '#940000'
                            }).then(() => {
                                loadLessonPlanForManage();
                            });
                        } else {
                            Swal.fire({
                                title: i18n.error,
                                text: response.error || 'Failed to send lesson plan',
                                icon: 'error',
                                confirmButtonColor: '#940000'
                            });
                        }
                    },
                    error: function (xhr) {
                        const error = xhr.responseJSON?.error || 'Failed to send lesson plan';
                        Swal.fire({
                            title: i18n.error,
                            text: error,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    }

    // Auto-open lesson plan modal with session details from URL parameters
    jQuery(document).ready(function ($) {
        const urlParams = new URLSearchParams(window.location.search);
        const autoOpen = urlParams.get('auto_open');

        if (autoOpen === 'true') {
            const sessionTimetableID = urlParams.get('session_timetableID');
            const day = urlParams.get('day');
            const startTime = urlParams.get('start_time');
            const endTime = urlParams.get('end_time');
            const subjectName = urlParams.get('subject_name');
            const className = urlParams.get('class_name');
            const date = urlParams.get('date');

            if (sessionTimetableID && day && startTime && endTime && subjectName && className && date) {
                // Wait a bit for modal to be ready
                setTimeout(function () {
                    openLessonPlanModal(
                        sessionTimetableID,
                        day,
                        startTime,
                        endTime,
                        subjectName,
                        className,
                        date,
                        true // autoOpenCreate
                    );

                    // Clean up URL parameters
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }, 500);
            }
        }
    });
</script>
@endpush
