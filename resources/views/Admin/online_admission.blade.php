@if(($user_type ?? Session::get('user_type')) == 'Admin')
@include('includes.Admin_nav')
@elseif(($user_type ?? Session::get('user_type')) == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

<style>
    .bg-primary-custom { background-color: #940000 !important; }
    .text-primary-custom { color: #940000 !important; }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #fff;
    }
    .btn-outline-primary-custom {
        color: #940000;
        border-color: #940000;
    }
    .btn-outline-primary-custom:hover {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    .oa-status-pending { color: #856404; font-weight: 700; }
    .oa-status-confirmed { color: #155724; font-weight: 700; }
    .oa-status-rejected { color: #721c24; font-weight: 700; }
    .oa-app-responses { display: flex; flex-direction: column; gap: 12px; }
    .oa-app-response-row {
        border: 1px solid #eee;
        background: #fafafa;
        padding: 12px 14px;
    }
    .oa-app-response-label {
        font-weight: 700;
        color: #444;
        margin-bottom: 6px;
        font-size: 13px;
    }
    .oa-app-response-value {
        color: #222;
        word-break: break-word;
    }
    .oa-app-response-file {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .oa-app-file-thumb {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border: 1px solid #ddd;
        background: #fff;
    }
    .oa-app-file-icon {
        font-size: 28px;
        color: #940000;
        width: 40px;
        text-align: center;
    }
    .oa-app-file-name {
        font-size: 13px;
        color: #333;
        word-break: break-all;
        margin-bottom: 6px;
    }
    .oa-app-file-actions .btn { margin-right: 4px; margin-bottom: 4px; }
    .oa-app-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }
    .oa-app-summary-item {
        border: 1px solid #eee;
        background: #fff;
        padding: 10px 12px;
    }
    .oa-app-summary-item span {
        display: block;
        font-size: 11px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 2px;
    }
    .oa-app-summary-item strong {
        color: #222;
        font-size: 14px;
        word-break: break-word;
    }
    .oa-app-section {
        border: 1px solid #f0dada;
        margin-bottom: 14px;
        background: #fff;
    }
    .oa-app-section__head {
        background: linear-gradient(90deg, rgba(148,0,0,0.08), #fff);
        border-bottom: 1px solid #f0dada;
        padding: 10px 14px;
    }
    .oa-app-section__head h6 {
        margin: 0;
        color: #940000;
        font-weight: 800;
        font-size: 14px;
    }
    .oa-app-section__head p {
        margin: 4px 0 0;
        color: #777;
        font-size: 12px;
    }
    .oa-app-section__body { padding: 8px 0; }
    @media (max-width: 767.98px) {
        .oa-app-summary { grid-template-columns: 1fr; }
    }
    #admissionsTable thead th {
        background: #fff5f5;
        color: #940000;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .oa-apps-view-toggle {
        display: inline-flex;
        border: 1px solid #f0dada;
        background: #fff;
        vertical-align: middle;
    }
    .oa-apps-view-toggle__btn {
        width: 38px;
        height: 34px;
        border: 0;
        background: transparent;
        color: #777;
        cursor: pointer;
    }
    .oa-apps-view-toggle__btn + .oa-apps-view-toggle__btn {
        border-left: 1px solid #f0dada;
    }
    .oa-apps-view-toggle__btn.is-active,
    .oa-apps-view-toggle__btn[aria-pressed="true"] {
        background: #940000;
        color: #fff;
    }
    .oa-apps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 14px;
    }
    .oa-app-card {
        border: 1px solid #f0dada;
        background: #fff;
        box-shadow: 0 6px 18px rgba(148, 0, 0, 0.05);
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 100%;
    }
    .oa-app-card__ref {
        font-size: 12px;
        font-weight: 800;
        color: #940000;
        letter-spacing: 0.02em;
    }
    .oa-app-card__name {
        font-size: 16px;
        font-weight: 700;
        color: #222;
        margin: 0;
        line-height: 1.25;
    }
    .oa-app-card__meta {
        font-size: 13px;
        color: #666;
        margin: 0;
    }
    .oa-app-card__footer {
        margin-top: auto;
        padding-top: 10px;
        border-top: 1px solid #f3e4e4;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .oa-apps-empty {
        text-align: center;
        padding: 36px 16px;
        color: #777;
    }
    .oa-apps-empty i {
        font-size: 36px;
        color: #940000;
        margin-bottom: 10px;
        display: block;
    }
    .oa-apps-empty h5 {
        color: #940000;
        font-weight: 800;
    }

    /* Defaults: desktop = list, mobile/tablet = grid (JS can override via data-view) */
    @media (max-width: 991.98px) {
        .oa-apps-view-root:not([data-view="list"]) .oa-apps-list-wrap {
            display: none !important;
        }
        .oa-apps-view-root:not([data-view="list"]) .oa-apps-grid {
            display: grid !important;
        }
        .oa-apps-view-root[data-view="list"] .oa-apps-list-wrap {
            display: block !important;
        }
        .oa-apps-view-root[data-view="list"] .oa-apps-grid {
            display: none !important;
        }
        .oa-apps-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (min-width: 992px) {
        .oa-apps-view-root:not([data-view="grid"]) .oa-apps-grid {
            display: none !important;
        }
        .oa-apps-view-root:not([data-view="grid"]) .oa-apps-list-wrap {
            display: block !important;
        }
        .oa-apps-view-root[data-view="grid"] .oa-apps-list-wrap {
            display: none !important;
        }
        .oa-apps-view-root[data-view="grid"] .oa-apps-grid {
            display: grid !important;
        }
    }
    .oa-apps-view-root.is-empty .oa-apps-list-wrap,
    .oa-apps-view-root.is-empty .oa-apps-grid {
        display: none !important;
    }
    .oa-apps-view-root:not(.is-empty) .oa-apps-empty {
        display: none !important;
    }
    .card, .form-control, .btn, .table, .modal-content {
        border-radius: 0 !important;
    }
    .oa-actions-dropdown {
        position: relative;
        flex-shrink: 0;
    }
    .oa-actions-toggle {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dee2e6;
        color: #940000;
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0;
    }
    .oa-actions-toggle:hover,
    .oa-actions-dropdown.show .oa-actions-toggle {
        background: #f5f5f5;
        border-color: #940000;
    }
    .oa-actions-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        min-width: 260px;
        background: #fff;
        border: 1px solid #dee2e6;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        z-index: 1060;
        padding: 4px 0;
    }
    .oa-actions-dropdown.show .oa-actions-menu {
        display: block;
    }
    .oa-actions-menu .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 10px 16px;
        border: 0;
        background: transparent;
        color: #333;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
    }
    .oa-actions-menu .dropdown-item:hover {
        background: #f5f5f5;
        color: #940000;
    }
    .oa-actions-menu .dropdown-item i {
        width: 18px;
        text-align: center;
        color: #940000;
    }
    .oa-field-type-card {
        border: 2px solid #eee;
        padding: 12px;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
        height: 100%;
    }
    .oa-field-type-card:hover {
        border-color: #940000;
        background: #fffafa;
    }
    .oa-field-type-card.is-selected {
        border-color: #940000;
        background: #fff5f5;
        box-shadow: 0 0 0 2px rgba(148,0,0,0.12);
    }
    .oa-field-type-card strong {
        display: block;
        color: #333;
        font-size: 14px;
    }
    .oa-field-type-card small {
        color: #777;
        line-height: 1.4;
    }
    .oa-field-list-row {
        border: 1px solid #eee;
        padding: 14px;
        margin-bottom: 10px;
        background: #fafafa;
    }
    .oa-field-list-row.is-system {
        background: #f0f4ff;
        border-color: #d0daf0;
    }
    .oa-type-badge {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: #940000;
        color: #fff;
        padding: 2px 8px;
        display: inline-block;
    }
    .oa-spaces-table th {
        background: #fff5f5;
        color: #940000;
    }
    .oa-form-builder-panel {
        background: #fffafa;
        border: 1px dashed #e8c4c4;
        padding: 16px;
    }
    .oa-builder-list .oa-field-row {
        border-color: #eee !important;
        box-shadow: none;
    }
    .oa-builder-list .oa-field-row:hover {
        border-color: #e8c4c4 !important;
        background: #fffcfc;
    }
    .oa-field-index {
        color: #940000;
        font-weight: 700;
    }
    .oa-step-badge {
        display: inline-block;
        background: #940000 !important;
        color: #fff !important;
        padding: 0.3rem 0.65rem;
        font-weight: 700;
        font-size: 0.75rem;
        border-radius: 0;
        border: none;
        letter-spacing: 0.02em;
    }
    .oa-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.88);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 20000;
        backdrop-filter: blur(2px);
    }
    .oa-loading-overlay.show {
        display: flex;
    }
    .oa-loading-box {
        text-align: center;
        padding: 1.5rem 2rem;
        background: #fff;
        border: 2px solid #940000;
        min-width: 220px;
    }
    .oa-loading-box .spinner-border {
        color: #940000;
        width: 2.5rem;
        height: 2.5rem;
    }
    .oa-loading-box .oa-loading-text {
        margin-top: 0.75rem;
        font-weight: 700;
        color: #940000;
    }
    .oa-copy-toast-fallback {
        position: fixed;
        top: 18px;
        left: 50%;
        transform: translateX(-50%) translateY(-12px);
        background: #1f1f1f;
        color: #fff;
        padding: 10px 18px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 600;
        z-index: 30000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    }
    .oa-copy-toast-fallback.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
    .swal2-popup.oa-copy-toast {
        border-radius: 999px !important;
        padding: 0.65rem 1.1rem !important;
        font-size: 14px !important;
    }
</style>

<div class="container-fluid mt-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1 text-primary-custom">
                        <i class="bi bi-wpforms"></i> Online Admission
                    </h4>
                    <p class="text-muted mb-0 small">
                        Review applications, configure the admission form, and manage class spaces for the active academic year.
                    </p>
                    @if($activeYear)
                        <p class="mb-0 mt-2"><strong>Active year:</strong> {{ $activeYear->year_name ?? $activeYear->year }}</p>
                    @else
                        <p class="text-danger mb-0 mt-2">No active academic year, set one before configuring spaces.</p>
                    @endif
                    @if(!empty($publicApplicationUrl))
                        <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                            <input type="text" readonly class="form-control form-control-sm" id="oaPublicAppLink"
                                value="{{ $publicApplicationUrl }}" style="max-width: 420px; font-size: 12px;">
                            <button type="button" class="btn btn-sm btn-outline-primary-custom" id="oaCopyPublicLink">
                                <i class="bi bi-clipboard"></i> Copy application link
                            </button>
                        </div>
                        <p class="text-muted small mb-0 mt-1">Forward this link to parents. It opens your school’s online application page.</p>
                    @endif
                </div>
                <div class="oa-actions-dropdown" id="oaActionsDropdown">
                    <button type="button" class="oa-actions-toggle" id="oaActionsToggle" title="Admission actions" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="oa-actions-menu" role="menu">
                        <button type="button" class="dropdown-item" id="oaMenuCopyLink" data-url="{{ $publicApplicationUrl ?? '' }}">
                            <i class="bi bi-link-45deg"></i> Copy public application link
                        </button>
                        <button type="button" class="dropdown-item" data-action="configure-form">
                            <i class="bi bi-ui-checks-grid"></i> Configure form
                        </button>
                        <button type="button" class="dropdown-item" data-action="configure-sections">
                            <i class="bi bi-diagram-3"></i> Admission form sections
                        </button>
                        <button type="button" class="dropdown-item" data-action="view-form">
                            <i class="bi bi-eye"></i> View configured form
                        </button>
                        <button type="button" class="dropdown-item" data-action="configure-spaces">
                            <i class="bi bi-sliders"></i> Configure application spaces
                        </button>
                        <button type="button" class="dropdown-item" data-action="view-spaces">
                            <i class="bi bi-table"></i> View application spaces
                        </button>
                        <button type="button" class="dropdown-item" data-action="view-fees">
                            <i class="bi bi-cash-coin"></i> View fee structure
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold">Status</label>
                    <select id="filterStatus" class="form-control form-control-sm">
                        <option value="all">All</option>
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold">Class</label>
                    <select id="filterClass" class="form-control form-control-sm">
                        <option value="">All classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->classID }}">{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-primary-custom" id="btnReloadApps">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                    <div class="oa-apps-view-toggle" role="group" aria-label="Applications layout">
                        <button type="button" class="oa-apps-view-toggle__btn" data-oa-apps-view="list" title="List view" aria-pressed="false">
                            <i class="fa fa-list"></i>
                        </button>
                        <button type="button" class="oa-apps-view-toggle__btn" data-oa-apps-view="grid" title="Grid view" aria-pressed="false">
                            <i class="fa fa-th-large"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div id="oaAppsViewRoot" class="oa-apps-view-root is-empty">
                <div id="oaAppsListWrap" class="table-responsive oa-apps-list-wrap">
                    <table class="table table-bordered table-hover w-100 mb-0" id="admissionsTable">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Student</th>
                                <th>Parent Phone</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="oaAppsGrid" class="oa-apps-grid" aria-live="polite"></div>
                <div id="oaAppsEmpty" class="oa-apps-empty">
                    <i class="fa fa-inbox"></i>
                    <h5>No applications found</h5>
                    <p>Try another status or class filter.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Configure form (PangishaPro-style form builder) --}}
<div class="modal fade" id="configureFormModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-ui-checks-grid"></i> Configure Admission Form</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="font-weight-bold mb-1">Class (main class) <span class="text-danger">*</span></label>
                    <select id="oaConfigureFormClass" class="form-control form-control-sm oa-form-class-select">
                        <option value="">-- Select class first --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->classID }}">{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Each main class has its own admission form. New classes start with the default form.</small>
                </div>
                <p class="text-muted small mb-3">
                    Configure the questions applicants must answer - like a custom Google Form.
                    System fields (student &amp; parent details) stay by default; you can add your own questions and save once.
                </p>

                <div id="oaConfigureFormBody" class="d-none">
                <ul class="nav nav-tabs mb-3" id="configureFormTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tabBuilder">Questions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tabBuilderPreview">Applicant preview</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tabBuilder">
                        <h6 class="mb-3 font-weight-bold text-primary-custom">Questions</h6>
                        <div id="oaBuilderFields" class="oa-builder-list"></div>
                        <div class="text-center mt-3 mb-2 pt-3 border-top">
                            <button type="button" class="btn btn-primary-custom px-4" id="btnAddQuestion">
                                <i class="bi bi-plus-lg"></i> Add question
                            </button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabBuilderPreview">
                        <div id="builderPreviewContainer" class="p-3 border bg-white oa-form-builder-panel">
                            <p class="text-muted mb-0">Save or open this tab to preview the applicant form.</p>
                        </div>
                    </div>
                </div>
                </div>
                <div id="oaConfigureFormHint" class="alert alert-light border mb-0">
                    Select a class above to load and configure its admission form.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="btnSaveAdmissionForm" disabled>
                    <i class="bi bi-check-lg"></i> Save form
                </button>
            </div>
        </div>
    </div>
</div>

<template id="oaFieldRowTemplate">
    <div class="oa-field-row card border mb-3">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="badge badge-light border oa-field-index">#1</span>
                    <span class="badge badge-secondary oa-system-badge d-none ml-1">System</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger oa-remove-field" title="Remove question">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <input type="hidden" class="oa-f-id" value="">
            <input type="hidden" class="oa-f-system" value="0">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Label</label>
                    <input type="text" class="form-control form-control-sm oa-f-label" required placeholder="e.g. Birth certificate">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Type</label>
                    <select class="form-control form-control-sm oa-f-type">
                        <option value="text">text</option>
                        <option value="textarea">textarea</option>
                        <option value="select">select</option>
                        <option value="radio">radio</option>
                        <option value="checkbox">checkbox</option>
                        <option value="file">file</option>
                        <option value="phone">phone</option>
                        <option value="gender">gender</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Key</label>
                    <input type="text" class="form-control form-control-sm oa-f-key" placeholder="auto">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <div class="form-check mr-3">
                        <input class="form-check-input oa-f-required" type="checkbox" value="1">
                        <label class="form-check-label small">Required</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input oa-f-active" type="checkbox" value="1" checked>
                        <label class="form-check-label small">Active</label>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Under section (step)</label>
                    <select class="form-control form-control-sm oa-f-section"></select>
                </div>
                <div class="col-md-6 mb-0 oa-options-wrap d-none">
                    <label class="small font-weight-bold text-muted mb-1">Options (one per line)</label>
                    <textarea class="form-control form-control-sm oa-f-options" rows="2" placeholder="Option A&#10;Option B"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- View configured form (applicant preview only) --}}
<div class="modal fade" id="viewFormModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-eye"></i> View Configured Form</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="font-weight-bold mb-1">Class (main class) <span class="text-danger">*</span></label>
                    <select id="oaViewFormClass" class="form-control form-control-sm oa-form-class-select">
                        <option value="">-- Select class first --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->classID }}">{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="previewContainer" class="p-3 border bg-white oa-form-builder-panel">
                    <p class="text-muted mb-0">Select a class to view its configured admission form.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-custom" id="btnOpenBuilderFromPreview">
                    <i class="bi bi-pencil-square"></i> Edit form
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


{{-- Admission form sections --}}
<div class="modal fade" id="configureSectionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-diagram-3"></i> Admission Form Sections</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="font-weight-bold mb-1">Class (main class) <span class="text-danger">*</span></label>
                    <select id="oaSectionsFormClass" class="form-control form-control-sm oa-form-class-select">
                        <option value="">-- Select class first --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->classID }}">{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Sections / steps are configured per main class.</small>
                </div>
                <div id="oaSectionsFormBody" class="d-none">
                <p class="text-muted small mb-3">
                    Sections are the <strong>steps</strong> on the application form (e.g. Student Details, Parent Details, Education).
                    Default sections cannot be deleted.
                </p>
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tabSectionConfigure">Configure section</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabSectionView">View sections</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tabSectionConfigure">
                        <input type="hidden" id="sectionEditId" value="">
                        <div class="form-group">
                            <label class="font-weight-bold">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sectionTitle" maxlength="200" placeholder="e.g. Education Qualification">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Description</label>
                            <textarea class="form-control" id="sectionDescription" rows="3" maxlength="1000" placeholder="Short help text shown on this step"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="sectionActive" checked>
                            <label class="form-check-label" for="sectionActive">Active (show as a step)</label>
                        </div>
                        <button type="button" class="btn btn-primary-custom" id="btnSaveSection">
                            <i class="bi bi-check-lg"></i> Save section
                        </button>
                        <button type="button" class="btn btn-secondary d-none" id="btnCancelSectionEdit">Cancel edit</button>
                    </div>
                    <div class="tab-pane fade" id="tabSectionView">
                        <div id="sectionsListContainer"><p class="text-muted">Loading...</p></div>
                    </div>
                </div>
                </div>
                <div id="oaSectionsFormHint" class="alert alert-light border mb-0">
                    Select a class above to configure its form sections.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
{{-- Configure application spaces --}}
<div class="modal fade" id="configureSpacesModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-sliders"></i> Configure Application Spaces</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="configureSpacesBody">
                <p class="text-muted mb-0">Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="btnSaveSpaces">
                    <i class="bi bi-check-lg"></i> Save spaces
                </button>
            </div>
        </div>
    </div>
</div>

{{-- View application spaces (read-only) --}}
<div class="modal fade" id="viewSpacesModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-table"></i> Application Spaces</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="viewSpacesBody">
                <p class="text-muted mb-0">Loading...</p>
            </div>
        </div>
    </div>
</div>

{{-- View configured fees --}}
<div class="modal fade" id="viewFeesModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-cash-coin"></i> Configured Fee Structure</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="small font-weight-bold">Class</label>
                        <select id="feesClassSelect" class="form-control">
                            <option value="">All classes</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->classID }}">{{ $c->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-outline-primary-custom" id="btnLoadFees">
                            <i class="fa fa-eye"></i> View fee
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="oaFeesStructureTable" class="table table-bordered table-hover mb-0"
                           style="border-collapse: separate; border-spacing: 0;">
                        <thead style="background-color:#940000 !important;">
                            <tr>
                                <th class="text-center text-white" style="width: 20%;">Class</th>
                                <th class="text-white" style="width: 30%;">Fee name</th>
                                <th class="text-end text-white" style="width: 15%;">Amount (TZS)</th>
                                <th class="text-white" style="width: 20%;">Deadline</th>
                                <th class="text-white" style="width: 15%;">Breakdown</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div id="oaFeesEmpty" class="text-center py-4 text-muted" style="display:none;">
                    <i class="fa fa-info-circle" style="font-size:42px;color:#940000;"></i>
                    <div class="mt-2"><strong>No fees found</strong></div>
                    <div>Configure fees first in <em>Manage Fees</em>.</div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-custom" id="btnDownloadFeesExcel">
                    <i class="fa fa-file-excel-o"></i> Download Excel
                </button>
                <button type="button" class="btn btn-primary-custom" id="btnDownloadFeesPdf">
                    <i class="fa fa-file-pdf-o"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Application details --}}
<div class="modal fade" id="viewAppModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Application Details</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="viewAppBody"></div>
            <div class="modal-footer" id="viewAppActions"></div>
        </div>
    </div>
</div>

{{-- Application PDF progress --}}
<div class="modal fade" id="oaAppPdfProgressModal" data-backdrop="static" tabindex="-1" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="fa fa-file-pdf-o text-danger" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-4" style="color: #940000; font-weight: bold;">
                    Generating Application PDF
                </h5>
                <div class="progress mb-2" style="height: 12px; border-radius: 0 !important;">
                    <div id="oaAppPdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                        role="progressbar" style="width: 0%; border-radius: 0 !important;"></div>
                </div>
                <div id="oaAppPdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small mb-0">Tafadhali subiri kidogo, tunaandaa PDF ya maelezo ya application.</p>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<div class="oa-loading-overlay" id="oaLoadingOverlay" aria-live="polite" aria-busy="true">
    <div class="oa-loading-box">
        <div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>
        <div class="oa-loading-text" id="oaLoadingText">Please wait...</div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
(function () {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;

    var routes = {
        fields: @json(route('school.online_admission.form_fields')),
        syncFields: @json(route('school.online_admission.form_fields.sync')),
        sections: @json(route('school.online_admission.form_sections')),
        storeSection: @json(route('school.online_admission.form_sections.store')),
        updateSectionBase: @json(url('online-admission/form-sections')),
        deleteSectionBase: @json(url('online-admission/form-sections')),
        spaces: @json(route('school.online_admission.spaces')),
        saveSpaces: @json(route('school.online_admission.spaces.save')),
        apps: @json(route('school.online_admission.applications')),
        showApp: @json(url('online-admission/applications')),
        appPdfBase: @json(url('online-admission/applications')),
        confirm: @json(url('online-admission/applications')),
        reject: @json(url('online-admission/applications')),
        cancel: @json(url('online-admission/applications')),
        preview: @json(route('school.online_admission.preview')),
        getClassFees: @json(url('get_class_fees')),
        exportFeesStructurePdf: @json(route('admin.export_fees_structure_pdf')),
        csrf: @json(csrf_token()),
    };

    var cachedFields = [];
    var cachedSections = [];
    var cachedSpaces = null;
    var currentFormClassId = '';

    function getFormClassId(selector) {
        var $el = $(selector || '#oaConfigureFormClass');
        return String($el.val() || '').trim();
    }

    function requireFormClassId(selector) {
        var classID = getFormClassId(selector);
        if (!classID) {
            swalErr('Please select a class (main class) first.');
            return null;
        }
        return classID;
    }

    function syncFormClassSelects(classID) {
        currentFormClassId = String(classID || '');
        $('#oaConfigureFormClass, #oaViewFormClass, #oaSectionsFormClass').val(currentFormClassId);
    }

    function swalOk(title, text, icon) {
        if (typeof Swal === 'undefined') return;
        return Swal.fire({
            icon: icon || 'success',
            title: title || 'Done',
            text: text || '',
            confirmButtonColor: '#940000',
        });
    }

    function swalErr(text) {
        if (typeof Swal === 'undefined') return;
        return Swal.fire({
            icon: 'error',
            title: 'Error',
            text: text || 'Something went wrong.',
            confirmButtonColor: '#940000',
        });
    }

    function swalConfirm(title, text) {
        if (typeof Swal === 'undefined') return Promise.resolve({ isConfirmed: false });
        return Swal.fire({
            icon: 'warning',
            title: title || 'Are you sure?',
            text: text || '',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
        });
    }

    function swalPrompt(title, inputLabel, inputPlaceholder) {
        if (typeof Swal === 'undefined') return Promise.resolve({ isConfirmed: false });
        return Swal.fire({
            title: title || 'Enter value',
            input: 'textarea',
            inputLabel: inputLabel || '',
            inputPlaceholder: inputPlaceholder || '',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
        });
    }

    function showOverlay(text) {
        $('#oaLoadingText').text(text || 'Please wait...');
        $('#oaLoadingOverlay').addClass('show');
    }

    function hideOverlay() {
        $('#oaLoadingOverlay').removeClass('show');
    }

    function setBtnLoading($btn, loading, idleHtml) {
        if (!$btn || !$btn.length) return;
        if (loading) {
            if (!$btn.data('idle-html')) {
                $btn.data('idle-html', $btn.html());
            }
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Please wait...');
        } else {
            $btn.prop('disabled', false).html(idleHtml || $btn.data('idle-html') || $btn.html());
            $btn.removeData('idle-html');
        }
    }

    function closeActionsMenu() {
        $('#oaActionsDropdown').removeClass('show');
        $('#oaActionsToggle').attr('aria-expanded', 'false');
    }

    function statusClass(s) {
        if (s === 'Confirmed') return 'oa-status-confirmed';
        if (s === 'Rejected') return 'oa-status-rejected';
        return 'oa-status-pending';
    }

    var table = $('#admissionsTable').DataTable({ order: [[5, 'desc']], pageLength: 15 });
    var appsCache = [];
    var OA_APPS_VIEW_KEY_M = 'sx_oa_admin_apps_view_m_v2';
    var OA_APPS_VIEW_KEY_D = 'sx_oa_admin_apps_view_d_v2';
    var OA_APPS_MOBILE_MAX = 991;

    function isAppsMobileView() {
        var mq = false;
        try {
            mq = window.matchMedia('(max-width: ' + OA_APPS_MOBILE_MAX + 'px)').matches;
        } catch (e) {}
        return mq || window.innerWidth <= OA_APPS_MOBILE_MAX;
    }

    function appsViewStorageKey() {
        return isAppsMobileView() ? OA_APPS_VIEW_KEY_M : OA_APPS_VIEW_KEY_D;
    }

    function defaultAppsView() {
        return isAppsMobileView() ? 'grid' : 'list';
    }

    function resolveAppsView() {
        try {
            // Drop legacy keys that could force list on mobile
            localStorage.removeItem('sx_oa_admin_apps_view_m');
            localStorage.removeItem('sx_oa_admin_apps_view_d');
            var saved = localStorage.getItem(appsViewStorageKey());
            if (saved === 'grid' || saved === 'list') return saved;
        } catch (e) {}
        return defaultAppsView();
    }

    function syncAppsViewButtons(view) {
        $('[data-oa-apps-view]').each(function () {
            var active = $(this).attr('data-oa-apps-view') === view;
            $(this).toggleClass('is-active', active);
            $(this).attr('aria-pressed', active ? 'true' : 'false');
        });
    }

    function applyAppsView(mode) {
        var view = mode === 'grid' ? 'grid' : 'list';
        var $root = $('#oaAppsViewRoot');
        var hasRows = appsCache.length > 0;

        $root.toggleClass('is-empty', !hasRows);
        $root.attr('data-view', view);

        if (hasRows && view === 'grid') {
            renderAppsGrid(appsCache);
        }

        syncAppsViewButtons(view);
    }

    function renderAppsGrid(rows) {
        var $grid = $('#oaAppsGrid').empty();
        (rows || []).forEach(function (row) {
            $grid.append(
                '<article class="oa-app-card">' +
                    '<div class="oa-app-card__ref">' + escapeHtml(row.application_number || '') + '</div>' +
                    '<h3 class="oa-app-card__name">' + escapeHtml(row.student_name || '-') + '</h3>' +
                    '<p class="oa-app-card__meta"><i class="fa fa-phone"></i> ' + escapeHtml(row.parent_phone || '-') + '</p>' +
                    '<p class="oa-app-card__meta"><i class="fa fa-graduation-cap"></i> ' + escapeHtml(row.class_name || '-') + '</p>' +
                    '<p class="oa-app-card__meta"><i class="fa fa-clock-o"></i> ' + escapeHtml(row.created_at || '-') + '</p>' +
                    '<div class="oa-app-card__footer">' +
                        '<span class="' + statusClass(row.status) + '">' + escapeHtml(row.status || '') + '</span>' +
                        '<button class="btn btn-sm btn-outline-primary-custom btn-view-app" data-id="' + row.id + '">View more</button>' +
                    '</div>' +
                '</article>'
            );
        });
    }

    function loadApplications() {
        showOverlay('Loading applications...');
        $.get(routes.apps, {
            status: $('#filterStatus').val(),
            classID: $('#filterClass').val(),
        }).done(function (res) {
            appsCache = res.data || [];
            table.clear();
            appsCache.forEach(function (row) {
                table.row.add([
                    row.application_number,
                    row.student_name,
                    row.parent_phone,
                    row.class_name || '-',
                    '<span class="' + statusClass(row.status) + '">' + row.status + '</span>',
                    row.created_at,
                    '<button class="btn btn-sm btn-outline-primary-custom btn-view-app" data-id="' + row.id + '">View more</button>',
                ]);
            });
            table.draw();
            applyAppsView(resolveAppsView());
        }).fail(function () {
            swalErr('Could not load applications.');
        }).always(hideOverlay);
    }

    // Set correct default immediately (mobile = grid, desktop = list)
    applyAppsView(resolveAppsView());

    $(document).on('click', '[data-oa-apps-view]', function () {
        var view = $(this).attr('data-oa-apps-view') === 'grid' ? 'grid' : 'list';
        applyAppsView(view);
        try { localStorage.setItem(appsViewStorageKey(), view); } catch (e) {}
    });

    var appsResizeTimer = null;
    var appsLastMobile = isAppsMobileView();
    $(window).on('resize orientationchange', function () {
        clearTimeout(appsResizeTimer);
        appsResizeTimer = setTimeout(function () {
            var nowMobile = isAppsMobileView();
            if (nowMobile !== appsLastMobile) {
                appsLastMobile = nowMobile;
                applyAppsView(resolveAppsView());
            }
        }, 120);
    });

    function fetchFields(classID) {
        classID = classID || getFormClassId('#oaConfigureFormClass');
        if (!classID) return $.Deferred().reject().promise();
        return $.get(routes.fields, { classID: classID }).then(function (res) {
            cachedFields = res.fields || [];
            cachedSections = res.sections || [];
            return cachedFields;
        });
    }

    function fetchSections(classID) {
        classID = classID || getFormClassId('#oaSectionsFormClass') || getFormClassId('#oaConfigureFormClass');
        if (!classID) return $.Deferred().reject().promise();
        return $.get(routes.sections, { classID: classID }).then(function (res) {
            cachedSections = res.sections || [];
            return cachedSections;
        });
    }

    function sectionOptionsHtml(selectedId) {
        var html = '';
        (cachedSections || []).forEach(function (s) {
            if (s.is_active === false || s.is_active === 0) return;
            html += '<option value="' + s.id + '"' +
                (String(selectedId) === String(s.id) ? ' selected' : '') + '>' +
                escapeHtml(s.title) + '</option>';
        });
        if (!html) {
            html = '<option value="">No sections yet</option>';
        }
        return html;
    }

    function renderSectionsList() {
        var $c = $('#sectionsListContainer').empty();
        if (!cachedSections.length) {
            $c.html('<p class="text-muted mb-0">No sections yet.</p>');
            return;
        }
        cachedSections.forEach(function (s, i) {
            var actions = '<button type="button" class="btn btn-sm btn-outline-primary-custom btn-edit-section mr-1" data-id="' + s.id + '"><i class="bi bi-pencil"></i> Edit</button>';
            if (!s.is_system) {
                actions += '<button type="button" class="btn btn-sm btn-outline-danger btn-del-section" data-id="' + s.id + '"><i class="bi bi-trash"></i> Delete</button>';
            }
            $c.append(
                '<div class="oa-field-list-row' + (s.is_system ? ' is-system' : '') + '">' +
                    '<div class="d-flex justify-content-between align-items-start flex-wrap">' +
                        '<div>' +
                            '<span class="oa-step-badge mr-1">Step ' + (i + 1) + '</span>' +
                            '<strong>' + escapeHtml(s.title) + '</strong>' +
                            (s.is_system ? ' <span class="badge badge-secondary">Default</span>' : '') +
                            (s.is_active ? '' : ' <span class="badge badge-warning">Hidden</span>') +
                            (s.description ? '<div class="small text-muted mt-1">' + escapeHtml(s.description) + '</div>' : '') +
                        '</div>' +
                        '<div class="mt-1">' + actions + '</div>' +
                    '</div>' +
                '</div>'
            );
        });
    }

    function resetSectionForm() {
        $('#sectionEditId').val('');
        $('#sectionTitle').val('');
        $('#sectionDescription').val('');
        $('#sectionActive').prop('checked', true);
        $('#btnCancelSectionEdit').addClass('d-none');
        $('#btnSaveSection').html('<i class="bi bi-check-lg"></i> Save section');
    }

    function openSectionsModal() {
        resetSectionForm();
        $('#oaSectionsFormBody').addClass('d-none');
        $('#oaSectionsFormHint').removeClass('d-none');
        if (currentFormClassId) {
            $('#oaSectionsFormClass').val(currentFormClassId);
        }
        $('#configureSectionsModal').modal('show');
        if (getFormClassId('#oaSectionsFormClass')) {
            loadSectionsForSelectedClass();
        }
    }

    function loadSectionsForSelectedClass() {
        var classID = requireFormClassId('#oaSectionsFormClass');
        if (!classID) {
            $('#oaSectionsFormBody').addClass('d-none');
            $('#oaSectionsFormHint').removeClass('d-none');
            return;
        }
        syncFormClassSelects(classID);
        showOverlay('Loading sections...');
        fetchSections(classID).then(function () {
            $('#oaSectionsFormHint').addClass('d-none');
            $('#oaSectionsFormBody').removeClass('d-none');
            renderSectionsList();
            $('a[href="#tabSectionConfigure"]').tab('show');
        }).fail(function (xhr) {
            swalErr((xhr && xhr.responseJSON && xhr.responseJSON.message) || 'Could not load sections.');
            $('#oaSectionsFormBody').addClass('d-none');
            $('#oaSectionsFormHint').removeClass('d-none');
        }).always(hideOverlay);
    }

    function fetchSpaces() {
        return $.get(routes.spaces).then(function (res) {
            cachedSpaces = res;
            return res;
        });
    }

    function loadPreview($target, classID) {
        var $el = $target && $target.length ? $target : $('#previewContainer');
        classID = classID || getFormClassId('#oaConfigureFormClass') || getFormClassId('#oaViewFormClass');
        if (!classID) {
            $el.html('<p class="text-muted mb-0">Select a class to preview its admission form.</p>');
            return;
        }
        $el.html('<p class="text-muted">Loading preview...</p>');
        $el.load(routes.preview + '?classID=' + encodeURIComponent(classID), function (_response, status) {
            if (status !== 'success') {
                $el.html('<p class="text-danger">Could not load preview. Select a class and try again.</p>');
            }
        });
    }

    function renderConfigureSpacesForm(res) {
        if (!res.success) {
            $('#configureSpacesBody').html('<p class="text-danger mb-0">' + escapeHtml(res.message || 'Error loading spaces.') + '</p>');
            return;
        }
        var html = '<p class="text-muted small">Set how many online admission spaces each class has for the active academic year.</p><form id="spacesForm"><div class="row">';
        (res.classes || []).forEach(function (c) {
            html += '<div class="col-md-6 mb-3">' +
                '<label class="font-weight-bold">' + escapeHtml(c.class_name) +
                ' <small class="text-muted">(used: ' + c.used + ', available: ' + c.available + ')</small></label>' +
                '<input type="number" min="0" class="form-control" name="spaces[' + c.classID + ']" value="' + c.spaces + '">' +
                '</div>';
        });
        html += '</div></form>';
        $('#configureSpacesBody').html(html);
    }

    function renderViewSpacesTable(res) {
        if (!res.success) {
            $('#viewSpacesBody').html('<p class="text-danger mb-0">' + escapeHtml(res.message || 'Error loading spaces.') + '</p>');
            return;
        }
        var year = res.academic_year ? (res.academic_year.year_name || res.academic_year.year) : '-';
        var html = '<p class="mb-3"><strong>Academic year:</strong> ' + escapeHtml(year) + '</p>';
        html += '<div class="table-responsive"><table class="table table-bordered oa-spaces-table mb-0"><thead><tr>' +
            '<th>Class</th><th>Allocated</th><th>Used</th><th>Available</th></tr></thead><tbody>';
        (res.classes || []).forEach(function (c) {
            html += '<tr><td>' + escapeHtml(c.class_name) + '</td><td>' + c.spaces + '</td><td>' + c.used + '</td><td><strong>' + c.available + '</strong></td></tr>';
        });
        html += '</tbody></table></div>';
        $('#viewSpacesBody').html(html);
    }

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // â”€â”€ Form builder (PangishaPro guest-forms style) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function toggleBuilderOptions($row) {
        var type = $row.find('.oa-f-type').val();
        $row.find('.oa-options-wrap').toggleClass('d-none', !['select', 'radio'].includes(type));
    }

    function reindexBuilderRows() {
        $('#oaBuilderFields .oa-field-row').each(function (i) {
            $(this).find('.oa-field-index').text('#' + (i + 1));
        });
    }

    function addBuilderRow(data) {
        var tpl = document.getElementById('oaFieldRowTemplate');
        if (!tpl) return;
        var node = tpl.content.cloneNode(true);
        var $row = $(node).find('.oa-field-row');
        var isSystem = !!(data && data.is_system);
        var sectionId = data ? (data.section_id || (data.section && data.section.id) || '') : '';

        $row.find('.oa-f-section').html(sectionOptionsHtml(sectionId));

        if (data) {
            $row.find('.oa-f-id').val(data.id || '');
            $row.find('.oa-f-system').val(isSystem ? '1' : '0');
            $row.find('.oa-f-label').val(data.label || '');
            $row.find('.oa-f-type').val(data.field_type || data.type || 'text');
            $row.find('.oa-f-key').val(data.field_key || '');
            $row.find('.oa-f-required').prop('checked', !!data.is_required);
            $row.find('.oa-f-active').prop('checked', data.is_active !== false && data.is_active !== 0);
            if (Array.isArray(data.options)) {
                $row.find('.oa-f-options').val(data.options.join('\n'));
            }
        } else if (cachedSections.length) {
            // Prefer student_details for new custom questions when available
            var student = cachedSections.find(function (s) { return s.section_key === 'student_details'; });
            if (student) {
                $row.find('.oa-f-section').val(student.id);
            }
        }

        if (isSystem) {
            $row.find('.oa-system-badge').removeClass('d-none');
            $row.find('.oa-f-type, .oa-f-key').prop('disabled', true);
            $row.find('.oa-remove-field').prop('disabled', true).attr('title', 'System fields cannot be removed');
            $row.addClass('is-system');
        }

        $row.find('.oa-f-type').on('change', function () {
            toggleBuilderOptions($row);
        });

        $row.find('.oa-remove-field').on('click', function () {
            if ($row.find('.oa-f-system').val() === '1') {
                swalErr('System fields cannot be removed.');
                return;
            }
            if ($('#oaBuilderFields .oa-field-row').length <= 1) {
                swalErr('Keep at least one question.');
                return;
            }
            $row.remove();
            reindexBuilderRows();
        });

        $('#oaBuilderFields').append($row);
        toggleBuilderOptions($row);
        reindexBuilderRows();
    }

    function openFormBuilder() {
        $('#oaBuilderFields').empty();
        $('#configureFormTabs a[href="#tabBuilder"]').tab('show');
        $('#oaConfigureFormBody').addClass('d-none');
        $('#oaConfigureFormHint').removeClass('d-none');
        $('#btnSaveAdmissionForm').prop('disabled', true);
        if (currentFormClassId) {
            $('#oaConfigureFormClass').val(currentFormClassId);
        }
        $('#configureFormModal').modal('show');
        if (getFormClassId('#oaConfigureFormClass')) {
            loadFormBuilderForSelectedClass();
        }
    }

    function loadFormBuilderForSelectedClass() {
        var classID = requireFormClassId('#oaConfigureFormClass');
        if (!classID) {
            $('#oaConfigureFormBody').addClass('d-none');
            $('#oaConfigureFormHint').removeClass('d-none');
            $('#btnSaveAdmissionForm').prop('disabled', true);
            return;
        }
        syncFormClassSelects(classID);
        $('#oaBuilderFields').empty();
        showOverlay('Loading form builder...');
        fetchFields(classID).then(function (fields) {
            $('#oaConfigureFormHint').addClass('d-none');
            $('#oaConfigureFormBody').removeClass('d-none');
            $('#btnSaveAdmissionForm').prop('disabled', false);
            if (fields && fields.length) {
                fields.forEach(function (f) { addBuilderRow(f); });
            } else {
                addBuilderRow(null);
            }
        }).fail(function (xhr) {
            swalErr((xhr && xhr.responseJSON && xhr.responseJSON.message) || 'Could not load form fields.');
            $('#oaConfigureFormBody').addClass('d-none');
            $('#oaConfigureFormHint').removeClass('d-none');
            $('#btnSaveAdmissionForm').prop('disabled', true);
        }).always(hideOverlay);
    }

    function openViewFormModal() {
        if (currentFormClassId) {
            $('#oaViewFormClass').val(currentFormClassId);
        }
        $('#previewContainer').html('<p class="text-muted mb-0">Select a class to view its configured admission form.</p>');
        $('#viewFormModal').modal('show');
        if (getFormClassId('#oaViewFormClass')) {
            loadPreview($('#previewContainer'), getFormClassId('#oaViewFormClass'));
        }
    }

    function collectBuilderPayload() {
        var fields = [];
        var ok = true;
        $('#oaBuilderFields .oa-field-row').each(function (i) {
            var $row = $(this);
            var label = $row.find('.oa-f-label').val().trim();
            var type = $row.find('.oa-f-type').val();
            if (!label) {
                ok = false;
                swalErr('Question #' + (i + 1) + ' needs a label.');
                return false;
            }
            var options = $row.find('.oa-f-options').val();
            if (['select', 'radio'].includes(type)) {
                var opts = String(options || '').split(/\r?\n/).map(function (s) { return s.trim(); }).filter(Boolean);
                if (!opts.length) {
                    ok = false;
                    swalErr('Question "' + label + '" needs at least one option.');
                    return false;
                }
            }
            fields.push({
                id: $row.find('.oa-f-id').val() || null,
                label: label,
                field_type: type,
                field_key: $row.find('.oa-f-key').val().trim(),
                section_id: $row.find('.oa-f-section').val() || null,
                is_required: $row.find('.oa-f-required').is(':checked') ? 1 : 0,
                is_active: $row.find('.oa-f-active').is(':checked') ? 1 : 0,
                options: options,
                sort_order: (i + 1) * 10,
            });
        });
        return ok ? fields : null;
    }

    function fmtMoneyTZS(amount) {
        const n = parseFloat(amount || 0);
        return 'TZS ' + n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + '/=';
    }

    function deadlineText(fee) {
        const amount = fee.payment_deadline_amount ?? null;
        const date = fee.payment_deadline_date ?? null;
        let parts = [];
        if (amount) parts.push('Amount: ' + fmtMoneyTZS(amount));
        if (date) {
            const d = String(date);
            parts.push('Date: ' + d.slice(0, 10));
        }
        if (!parts.length && fee.deadlines && fee.deadlines.length) {
            parts.push('Deadline: ' + (fee.deadlines[0].start_date ? String(fee.deadlines[0].start_date).slice(0, 10) : '-'));
        }
        return parts.length ? parts.join('<br>') : '<span class="text-muted">-</span>';
    }

    function breakdownHtml(fee) {
        if (!fee.breakdowns || !fee.breakdowns.length) {
            return '<span class="text-muted">-</span>';
        }
        let items = fee.breakdowns.map(function (b) {
            return '<div>' + escapeHtml(b.item_name) + ': <strong>' + fmtMoneyTZS(b.amount) + '</strong></div>';
        }).join('');
        return '<div style="line-height:1.35;">' + items + '</div>';
    }

    function getSelectedFeeClassIds() {
        const selected = $('#feesClassSelect').val();
        if (!selected) {
            const ids = [];
            $('#feesClassSelect option').each(function () {
                const v = $(this).val();
                if (v) ids.push(v);
            });
            return ids;
        }
        return [selected];
    }

    function getClassNameFromSelect(classID) {
        return $('#feesClassSelect option[value="' + classID + '"]').text() || ('Class ' + classID);
    }

    async function loadFeesTableForSelection() {
        const classIDs = getSelectedFeeClassIds();
        const $tbody = $('#oaFeesStructureTable tbody');
        $tbody.empty();
        $('#oaFeesEmpty').hide();

        if (!classIDs.length) {
            $('#oaFeesEmpty').show();
            return;
        }

        showOverlay('Loading fee structure...');
        try {
            const promises = classIDs.map(function (classID) {
                return $.get(routes.getClassFees + '/' + classID).then(function (res) {
                    return { classID: classID, res: res };
                });
            });

            const results = await Promise.all(promises);
            let hasAnyFees = false;

            results.forEach(function (r) {
                const fees = (r.res && r.res.fees) ? r.res.fees : [];
                if (!fees.length) {
                    $tbody.append(
                        '<tr>' +
                        '<td class="text-center">' + escapeHtml(getClassNameFromSelect(r.classID)) + '</td>' +
                        '<td colspan="4" class="text-center text-muted py-3"><em>No fees assigned</em></td>' +
                        '</tr>'
                    );
                    return;
                }

                fees.forEach(function (fee, idx) {
                    hasAnyFees = true;
                    const classCell = (idx === 0)
                        ? '<td class="text-center border-end align-middle bg-light"><strong>' + escapeHtml(getClassNameFromSelect(r.classID)) + '</strong></td>'
                        : '<td class="text-center border-end align-middle" style="color: transparent;">' + escapeHtml(getClassNameFromSelect(r.classID)) + '</td>';

                    $tbody.append(
                        '<tr class="align-middle">' +
                        classCell +
                        '<td>' + escapeHtml(fee.fee_name || 'Fee') + '</td>' +
                        '<td class="text-end"><strong>' + fmtMoneyTZS(fee.amount) + '</strong></td>' +
                        '<td>' + deadlineText(fee) + '</td>' +
                        '<td>' + breakdownHtml(fee) + '</td>' +
                        '</tr>'
                    );
                });
            });

            if (!hasAnyFees) {
                $('#oaFeesEmpty').show();
            }
        } catch (e) {
            swalErr('Failed to load fee structure.');
        } finally {
            hideOverlay();
        }
    }

    $(document).on('click', '#oaActionsToggle', function (e) {
        e.stopPropagation();
        var $dd = $('#oaActionsDropdown');
        var open = $dd.hasClass('show');
        $('.oa-actions-dropdown').removeClass('show');
        if (!open) {
            $dd.addClass('show');
            $(this).attr('aria-expanded', 'true');
        } else {
            $(this).attr('aria-expanded', 'false');
        }
    });

    function oaShowCopiedToast(message) {
        var text = message || 'Link copied';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'success',
                title: text,
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true,
                background: '#1f1f1f',
                color: '#fff',
                iconColor: '#7dcea0',
                customClass: {
                    popup: 'oa-copy-toast'
                }
            });
            return;
        }
        var $toast = $('<div class="oa-copy-toast-fallback"></div>').text(text);
        $('body').append($toast);
        setTimeout(function () { $toast.addClass('show'); }, 10);
        setTimeout(function () {
            $toast.removeClass('show');
            setTimeout(function () { $toast.remove(); }, 250);
        }, 1800);
    }

    function oaCopyTextFallback(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (e) {
            ok = false;
        }
        document.body.removeChild(ta);
        return ok;
    }

    function oaCopyPublicApplicationLink(url) {
        if (!url) {
            swalErr('Application link is not available.');
            return;
        }
        var done = function () {
            oaShowCopiedToast('Link copied');
        };
        var fail = function () {
            if (oaCopyTextFallback(url)) {
                done();
                return;
            }
            swalErr('Could not copy the link. Please copy it manually from the box.');
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(done).catch(fail);
        } else {
            fail();
        }
    }

    $(document).on('click', '#oaCopyPublicLink, #oaMenuCopyLink', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var url = $(this).data('url') || $('#oaPublicAppLink').val() || '';
        oaCopyPublicApplicationLink(url);
        closeActionsMenu();
    });

    $(document).on('click', function () {
        closeActionsMenu();
    });

    $(document).on('click', '.oa-actions-menu', function (e) {
        e.stopPropagation();
    });

    $(document).on('click', '.oa-actions-menu .dropdown-item', function () {
        var action = $(this).data('action');
        closeActionsMenu();

        if (action === 'configure-form') {
            openFormBuilder();
        } else if (action === 'configure-sections') {
            openSectionsModal();
        } else if (action === 'view-form') {
            openViewFormModal();
        } else if (action === 'configure-spaces') {
            showOverlay('Loading spaces...');
            fetchSpaces().then(function (res) {
                renderConfigureSpacesForm(res);
                $('#configureSpacesModal').modal('show');
            }).fail(function () {
                swalErr('Could not load spaces.');
            }).always(hideOverlay);
        } else if (action === 'view-spaces') {
            showOverlay('Loading spaces...');
            fetchSpaces().then(function (res) {
                renderViewSpacesTable(res);
                $('#viewSpacesModal').modal('show');
            }).fail(function () {
                swalErr('Could not load spaces.');
            }).always(hideOverlay);
        } else if (action === 'view-fees') {
            $('#feesClassSelect').val('');
            $('#viewFeesModal').modal('show');
            loadFeesTableForSelection();
        }
    });

    $('#btnAddQuestion').on('click', function () {
        addBuilderRow(null);
        var $last = $('#oaBuilderFields .oa-field-row').last();
        if ($last.length) {
            $last.find('.oa-f-label').focus();
            var el = $last.get(0);
            if (el && el.scrollIntoView) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    $('#btnSaveSection').on('click', function () {
        var classID = requireFormClassId('#oaSectionsFormClass');
        if (!classID) return;
        var title = $('#sectionTitle').val().trim();
        if (!title) {
            swalErr('Section title is required.');
            return;
        }
        var id = $('#sectionEditId').val();
        var payload = {
            _token: routes.csrf,
            classID: classID,
            title: title,
            description: $('#sectionDescription').val(),
            is_active: $('#sectionActive').is(':checked') ? 1 : 0,
        };
        var $btn = $(this);
        setBtnLoading($btn, true);
        showOverlay(id ? 'Updating section...' : 'Saving section...');
        var req;
        if (id) {
            payload._method = 'PUT';
            req = $.post(routes.updateSectionBase + '/' + id, payload);
        } else {
            req = $.post(routes.storeSection, payload);
        }
        req.done(function (res) {
            cachedSections = res.sections || [];
            swalOk('Saved', res.message || 'Section saved.');
            resetSectionForm();
            renderSectionsList();
            $('a[href="#tabSectionView"]').tab('show');
        }).fail(function (xhr) {
            swalErr((xhr.responseJSON && xhr.responseJSON.message) || 'Could not save section.');
        }).always(function () {
            hideOverlay();
            setBtnLoading($btn, false, id ? '<i class="bi bi-check-lg"></i> Update section' : '<i class="bi bi-check-lg"></i> Save section');
        });
    });

    $('#btnCancelSectionEdit').on('click', resetSectionForm);

    $(document).on('click', '.btn-edit-section', function () {
        var id = $(this).data('id');
        var s = cachedSections.find(function (x) { return String(x.id) === String(id); });
        if (!s) return;
        $('#sectionEditId').val(s.id);
        $('#sectionTitle').val(s.title || '');
        $('#sectionDescription').val(s.description || '');
        $('#sectionActive').prop('checked', s.is_active !== false && s.is_active !== 0);
        $('#btnCancelSectionEdit').removeClass('d-none');
        $('#btnSaveSection').html('<i class="bi bi-check-lg"></i> Update section');
        $('a[href="#tabSectionConfigure"]').tab('show');
    });

    $(document).on('click', '.btn-del-section', function () {
        var id = $(this).data('id');
        var classID = requireFormClassId('#oaSectionsFormClass');
        if (!classID) return;
        swalConfirm('Delete section?', 'Questions in this section will move to Student Details.').then(function (result) {
            if (!result.isConfirmed) return;
            showOverlay('Deleting section...');
            $.ajax({
                url: routes.deleteSectionBase + '/' + id,
                type: 'DELETE',
                data: { _token: routes.csrf, classID: classID },
            }).done(function (res) {
                cachedSections = res.sections || [];
                swalOk('Deleted', res.message || 'Section removed.');
                renderSectionsList();
            }).fail(function (xhr) {
                swalErr((xhr.responseJSON && xhr.responseJSON.message) || 'Could not delete section.');
            }).always(hideOverlay);
        });
    });

    $('a[data-toggle="tab"][href="#tabSectionView"]').on('shown.bs.tab', function () {
        renderSectionsList();
    });

    $('#btnSaveAdmissionForm').on('click', function () {
        var classID = requireFormClassId('#oaConfigureFormClass');
        if (!classID) return;
        var fields = collectBuilderPayload();
        if (!fields) return;

        var $btn = $(this);
        setBtnLoading($btn, true);
        showOverlay('Saving admission form...');

        $.ajax({
            url: routes.syncFields,
            method: 'POST',
            data: {
                _token: routes.csrf,
                classID: classID,
                fields: fields,
            },
        }).done(function (res) {
            cachedFields = res.fields || [];
            if (res.sections) cachedSections = res.sections;
            swalOk('Form saved', res.message || 'Admission form updated.');
            $('#oaBuilderFields').empty();
            (cachedFields || []).forEach(function (f) { addBuilderRow(f); });
            loadPreview($('#builderPreviewContainer'), classID);
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && (xhr.responseJSON.message || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors).flat().join(' ')))) || 'Could not save form.';
            swalErr(msg);
        }).always(function () {
            hideOverlay();
            setBtnLoading($btn, false, '<i class="bi bi-check-lg"></i> Save form');
        });
    });

    $('a[data-toggle="tab"][href="#tabBuilderPreview"]').on('shown.bs.tab', function () {
        loadPreview($('#builderPreviewContainer'), getFormClassId('#oaConfigureFormClass'));
    });

    $('#btnOpenBuilderFromPreview').on('click', function () {
        var classID = getFormClassId('#oaViewFormClass');
        if (classID) syncFormClassSelects(classID);
        $('#viewFormModal').modal('hide');
        setTimeout(openFormBuilder, 300);
    });

    $('#oaConfigureFormClass').on('change', function () {
        loadFormBuilderForSelectedClass();
    });

    $('#oaViewFormClass').on('change', function () {
        var classID = getFormClassId('#oaViewFormClass');
        if (!classID) {
            $('#previewContainer').html('<p class="text-muted mb-0">Select a class to view its configured admission form.</p>');
            return;
        }
        syncFormClassSelects(classID);
        loadPreview($('#previewContainer'), classID);
    });

    $('#oaSectionsFormClass').on('change', function () {
        resetSectionForm();
        loadSectionsForSelectedClass();
    });

    $('#btnSaveSpaces').on('click', function () {
        var $form = $('#spacesForm');
        if (!$form.length) {
            swalErr('No spaces form loaded.');
            return;
        }
        var $btn = $(this);
        setBtnLoading($btn, true);
        showOverlay('Saving application spaces...');
        $.post(routes.saveSpaces, $form.serialize() + '&_token=' + routes.csrf)
            .done(function (res) {
                swalOk('Saved', res.message || 'Admission spaces updated.');
                $('#configureSpacesModal').modal('hide');
                fetchSpaces();
            })
            .fail(function (xhr) {
                swalErr((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to save spaces.');
            })
            .always(function () {
                hideOverlay();
                setBtnLoading($btn, false, '<i class="bi bi-check-lg"></i> Save spaces');
            });
    });

    $('#btnReloadApps').on('click', loadApplications);
    $('#filterStatus, #filterClass').on('change', loadApplications);

    $('#btnLoadFees').on('click', function () {
        loadFeesTableForSelection();
    });

    $('#btnDownloadFeesPdf').on('click', function () {
        const classID = $('#feesClassSelect').val();
        let url = routes.exportFeesStructurePdf;
        if (classID) {
            url += '?classIDs[]=' + encodeURIComponent(classID);
        }
        swalConfirm('Download fees structure?', 'PDF will be generated for the selected class scope.').then(function (result) {
            if (!result.isConfirmed) return;
            window.location.href = url;
        });
    });

    $('#btnDownloadFeesExcel').on('click', function () {
        const tableEl = document.getElementById('oaFeesStructureTable');
        if (!tableEl) {
            swalErr('Fees table not found.');
            return;
        }
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tableEl);
        XLSX.utils.book_append_sheet(wb, ws, 'FeesStructure');
        const selectedClassName = $('#feesClassSelect option:selected').text() || 'All_Classes';
        const safeName = String(selectedClassName).replace(/\s+/g, '_').replace(/[^\w\-]+/g, '');
        const timestamp = new Date().toISOString().slice(0, 10);
        try {
            XLSX.writeFile(wb, 'Fees_Structure_' + safeName + '_' + timestamp + '.xlsx');
            swalOk('Downloaded', 'Excel file saved successfully.');
        } catch (e) {
            swalErr('Excel export failed.');
        }
    });

    function renderAppFieldRow(field) {
        var html = '<div class="oa-app-response-row">';
        html += '<div class="oa-app-response-label">' + escapeHtml(field.label || field.key) + '</div>';
        if (field.is_file && field.url) {
            html += '<div class="oa-app-response-file">';
            if (field.is_image) {
                html += '<a href="' + escapeHtml(field.url) + '" target="_blank" rel="noopener"><img src="' + escapeHtml(field.url) + '" alt="" class="oa-app-file-thumb"></a>';
            } else {
                html += '<i class="fa fa-file-pdf-o oa-app-file-icon"></i>';
            }
            html += '<div class="oa-app-file-meta">';
            html += '<div class="oa-app-file-name">' + escapeHtml(field.filename || 'Uploaded file') + '</div>';
            html += '<div class="oa-app-file-actions">';
            html += '<a class="btn btn-sm btn-outline-primary" href="' + escapeHtml(field.url) + '" target="_blank" rel="noopener"><i class="fa fa-eye"></i> View</a> ';
            html += '<a class="btn btn-sm btn-outline-secondary" href="' + escapeHtml(field.download_url || field.url) + '" download="' + escapeHtml(field.filename || 'file') + '"><i class="fa fa-download"></i> Download</a>';
            html += '</div></div></div>';
        } else if (field.is_file) {
            html += '<div class="oa-app-response-value text-muted">No file uploaded</div>';
        } else {
            html += '<div class="oa-app-response-value">' + escapeHtml(field.value || '-') + '</div>';
        }
        html += '</div>';
        return html;
    }

    function downloadApplicationPdf(id, ref) {
        var url = routes.appPdfBase + '/' + id + '/pdf';
        var safeName = String(ref || ('application_' + id)).replace(/\s+/g, '_').replace(/[^\w\-]+/g, '');

        $('#oaAppPdfProgressModal').modal('show');
        $('#oaAppPdfProgressBar').css('width', '0%');
        $('#oaAppPdfProgressPercent').text('0%');

        var progress = 0;
        var interval = setInterval(function () {
            if (progress < 95) {
                progress += 2 + Math.random() * 5;
                if (progress > 95) progress = 95;
                $('#oaAppPdfProgressBar').css('width', progress + '%');
                $('#oaAppPdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 300);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) {
                if (!res.ok) throw new Error('PDF generation failed');
                return res.blob();
            })
            .then(function (blob) {
                clearInterval(interval);
                $('#oaAppPdfProgressBar').css('width', '100%');
                $('#oaAppPdfProgressPercent').text('100%');
                var objectUrl = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = objectUrl;
                a.download = 'Online_Application_' + safeName + '.pdf';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(objectUrl);
                setTimeout(function () {
                    $('#oaAppPdfProgressModal').modal('hide');
                }, 400);
            })
            .catch(function () {
                clearInterval(interval);
                $('#oaAppPdfProgressModal').modal('hide');
                swalErr('Could not generate application PDF.');
            });
    }

    $(document).on('click', '.btn-view-app', function () {
        var id = $(this).data('id');
        showOverlay('Loading application...');
        $.get(routes.showApp + '/' + id).done(function (res) {
            var d = res.data || {};
            var meta = res.meta || {};
            var sections = res.sections || [];

            var html = '<div class="oa-app-summary">';
            html += '<div class="oa-app-summary-item"><span>Reference</span><strong>' + escapeHtml(meta.application_number || d.application_number || '-') + '</strong></div>';
            html += '<div class="oa-app-summary-item"><span>Status</span><strong>' + escapeHtml(meta.status || d.status || '-') + '</strong></div>';
            html += '<div class="oa-app-summary-item"><span>Student</span><strong>' + escapeHtml(meta.student_name || '-') + '</strong></div>';
            html += '<div class="oa-app-summary-item"><span>Class</span><strong>' + escapeHtml(meta.class_name || (d.class ? d.class.class_name : '-')) + '</strong></div>';
            html += '<div class="oa-app-summary-item"><span>Parent</span><strong>' + escapeHtml((meta.parent_name || '-') + (meta.parent_phone ? ' · ' + meta.parent_phone : '')) + '</strong></div>';
            html += '<div class="oa-app-summary-item"><span>Submitted</span><strong>' + escapeHtml(meta.submitted_at || '-') + '</strong></div>';
            html += '</div>';

            if (!sections.length) {
                html += '<div class="alert alert-warning mb-0">No form responses found for this application.</div>';
            } else {
                sections.forEach(function (section) {
                    html += '<div class="oa-app-section">';
                    html += '<div class="oa-app-section__head"><h6>' + escapeHtml(section.title || 'Section') + '</h6>';
                    if (section.description) {
                        html += '<p>' + escapeHtml(section.description) + '</p>';
                    }
                    html += '</div><div class="oa-app-section__body oa-app-responses">';
                    (section.fields || []).forEach(function (field) {
                        html += renderAppFieldRow(field);
                    });
                    html += '</div></div>';
                });
            }

            $('#viewAppBody').html(html);

            var classID = d.classID || (d.class ? d.class.classID : null);
            var viewFeeBtn = classID
                ? '<button type="button" class="btn btn-outline-primary-custom btn-view-fee" data-class-id="' + classID + '">View fee</button>'
                : '';
            var pdfBtn = '<button type="button" class="btn btn-outline-danger btn-download-app-pdf" data-id="' + id + '" data-ref="' + escapeHtml(meta.application_number || d.application_number || '') + '"><i class="fa fa-file-pdf-o"></i> Download PDF</button>';
            var actions = pdfBtn + viewFeeBtn + '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
            if (d.status === 'Pending') {
                actions = '<button type="button" class="btn btn-success btn-confirm-app" data-id="' + id + '">Confirm</button>' +
                    '<button type="button" class="btn btn-warning btn-reject-app" data-id="' + id + '">Reject</button>' +
                    '<button type="button" class="btn btn-danger btn-cancel-app" data-id="' + id + '">Cancel (delete)</button>' +
                    actions;
            }
            $('#viewAppActions').html(actions);
            $('#viewAppModal').modal('show');
        }).fail(function () {
            swalErr('Could not load application details.');
        }).always(hideOverlay);
    });

    $('#viewAppActions').on('click', '.btn-download-app-pdf', function () {
        var id = $(this).data('id');
        var ref = $(this).data('ref') || '';
        downloadApplicationPdf(id, ref);
    });

    $('#viewAppActions').on('click', '.btn-view-fee', function () {
        var classID = $(this).data('class-id');
        if (!classID) {
            swalErr('Class not found for this application.');
            return;
        }
        $('#viewAppModal').modal('hide');
        $('#feesClassSelect').val(classID);
        $('#viewFeesModal').modal('show');
        loadFeesTableForSelection();
    });

    $('#viewAppActions').on('click', '.btn-confirm-app', function () {
        var id = $(this).data('id');
        swalConfirm('Confirm admission?', 'Student will be registered (not enrolled). Parent will receive SMS.').then(function (result) {
            if (!result.isConfirmed) return;
            showOverlay('Confirming admission...');
            $.post(routes.confirm + '/' + id + '/confirm', { _token: routes.csrf })
                .done(function (res) {
                    swalOk('Confirmed', res.message);
                    $('#viewAppModal').modal('hide');
                    loadApplications();
                })
                .fail(function (xhr) {
                    hideOverlay();
                    swalErr((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to confirm.');
                });
            // loadApplications shows its own overlay; hide here only on fail before reload
        });
    });

    $('#viewAppActions').on('click', '.btn-reject-app', function () {
        var id = $(this).data('id');
        swalPrompt('Reject application', 'Reason (optional)', 'Enter rejection reason...').then(function (result) {
            if (!result.isConfirmed) return;
            showOverlay('Rejecting application...');
            $.post(routes.reject + '/' + id + '/reject', { _token: routes.csrf, reason: result.value || '' })
                .done(function (res) {
                    swalOk('Rejected', res.message);
                    $('#viewAppModal').modal('hide');
                    loadApplications();
                })
                .fail(function () {
                    hideOverlay();
                    swalErr('Failed to reject application.');
                });
        });
    });

    $('#viewAppActions').on('click', '.btn-cancel-app', function () {
        var id = $(this).data('id');
        swalConfirm('Delete application?', 'This will permanently remove the application record.').then(function (result) {
            if (!result.isConfirmed) return;
            showOverlay('Deleting application...');
            $.ajax({
                url: routes.cancel + '/' + id,
                type: 'DELETE',
                data: { _token: routes.csrf },
            }).done(function (res) {
                swalOk('Deleted', res.message);
                $('#viewAppModal').modal('hide');
                loadApplications();
            }).fail(function () {
                hideOverlay();
                swalErr('Failed to delete application.');
            });
        });
    });

    loadApplications();
})();
</script>

