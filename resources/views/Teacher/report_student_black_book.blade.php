@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.report_student').' - ShuleXpert')
@section('page_title', __('teacher.pages.report_student'))
@section('page_breadcrumb', __('teacher.pages.report_student_bc'))

@php
    $bbUserType = Session::get('user_type');
    $bbUserHasManage = app(\App\Services\BlackBookAccessService::class)->currentUserHasManageAccess((int) Session::get('schoolID'));
@endphp

@section('page_actions')
    @if($bbUserHasManage || strtolower((string) $bbUserType) === 'admin')
        <a href="{{ route('manage_black_book') }}" class="btn btn-outline-light btn-shadow btn-sm waves-effect me-1">
            <i class="fi fi-rr-book-alt me-1"></i> {{ __('teacher.bb.black_book') }}
        </a>
    @endif
    <button type="button" class="btn btn-primary btn-sm waves-effect" data-bs-toggle="modal" data-bs-target="#reportStudentModal">
        <i class="fi fi-rr-plus me-1"></i> {{ __('teacher.bb.report_student') }}
    </button>
@endsection

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb span:not(.badge):not(.status-pill),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-bb label {
        color: #000 !important;
    }
    .sx-teacher-bb .card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
    }
    .sx-teacher-bb .panel-card {
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
    }
    .sx-teacher-bb .panel-card .card-body { padding: 1.25rem 1.35rem; }
    .sx-teacher-bb .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }

    .sx-teacher-bb .bb-sms-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.85rem;
        padding: 1rem 1.15rem;
        margin-bottom: 1.15rem;
        background: linear-gradient(135deg, #fcf7f7 0%, #fff 70%);
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
    }
    .sx-teacher-bb .bb-sms-bar h6 {
        margin: 0 0 0.2rem;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #940000 !important;
    }
    .sx-teacher-bb .bb-sms-bar p {
        margin: 0;
        font-size: 0.82rem;
        color: #666 !important;
    }
    .sx-teacher-bb .bb-sms-label {
        font-weight: 800;
        font-size: 0.85rem;
        min-width: 2.5rem;
        display: inline-block;
    }

    .sx-teacher-bb .bb-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .sx-teacher-bb .bb-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .sx-teacher-bb .bb-tabs .nav-link {
        border: 1px solid #e6e6e6;
        background: #fff;
        color: #333 !important;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        padding: 0.45rem 0.85rem;
        border-radius: 0;
        text-decoration: none;
    }
    .sx-teacher-bb .bb-tabs .nav-link.active,
    .sx-teacher-bb .bb-tabs .nav-link:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-teacher-bb .bb-view-toggle .btn {
        border-radius: 0;
        font-weight: 700;
        border-color: #d9d9d9;
        color: #333 !important;
        background: #fff;
    }
    .sx-teacher-bb .bb-view-toggle .btn.active {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }

    .sx-teacher-bb .bb-reports-wrapper .bb-reports-list-view { display: none; }
    .sx-teacher-bb .bb-reports-wrapper .bb-reports-grid-view { display: flex; flex-wrap: wrap; }
    @media (min-width: 768px) {
        .sx-teacher-bb .bb-reports-wrapper:not([data-active-view="grid"]) .bb-reports-list-view { display: block; }
        .sx-teacher-bb .bb-reports-wrapper:not([data-active-view="grid"]) .bb-reports-grid-view { display: none; }
    }
    .sx-teacher-bb .bb-reports-wrapper[data-active-view="list"] .bb-reports-list-view { display: block !important; }
    .sx-teacher-bb .bb-reports-wrapper[data-active-view="list"] .bb-reports-grid-view { display: none !important; }
    .sx-teacher-bb .bb-reports-wrapper[data-active-view="grid"] .bb-reports-list-view { display: none !important; }
    .sx-teacher-bb .bb-reports-wrapper[data-active-view="grid"] .bb-reports-grid-view { display: flex !important; flex-wrap: wrap; }

    .sx-teacher-bb .bb-table {
        margin: 0;
        width: 100%;
    }
    .sx-teacher-bb .bb-table thead th {
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        font-weight: 800;
        color: #940000 !important;
        background: #fcf7f7;
        border-bottom: 1px solid rgba(148, 0, 0, 0.12);
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.85rem 1rem;
    }
    .sx-teacher-bb .bb-table tbody td {
        vertical-align: middle;
        font-size: 0.85rem;
        padding: 0.75rem 0.7rem;
        border-color: rgba(148, 0, 0, 0.07);
        background: #fff;
        white-space: nowrap;
    }
    .sx-teacher-bb .bb-table tbody tr:hover td {
        background: #fff8f8;
    }
    .sx-teacher-bb .bb-table .td-desc {
        white-space: normal;
        min-width: 150px;
        max-width: 220px;
    }
    .sx-teacher-bb .bb-table .td-resolution {
        white-space: normal;
        min-width: 120px;
        max-width: 180px;
        font-size: 0.8rem;
    }
    .sx-teacher-bb .bb-desc-short {
        display: block;
        color: #333 !important;
        font-weight: 600;
        line-height: 1.35;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 200px;
    }
    .sx-teacher-bb .bb-view-more {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        margin-top: 0.3rem;
        padding: 0.15rem 0.45rem;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: #fff;
        color: #940000 !important;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border-radius: 0;
        cursor: pointer;
        line-height: 1.2;
    }
    .sx-teacher-bb .bb-view-more:hover {
        background: #940000;
        border-color: #940000;
        color: #fff !important;
    }
    #reportDetailModal .modal-content {
        border: none;
        border-radius: 0;
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.18);
    }
    #reportDetailModal .modal-header {
        border: none;
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%);
        color: #fff !important;
        padding: 1rem 1.25rem;
    }
    #reportDetailModal .modal-title {
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #fff !important;
        margin: 0;
    }
    #reportDetailModal .modal-body {
        background: #faf7f7;
        padding: 1.15rem 1.25rem;
    }
    #reportDetailModal .rd-card {
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.1);
        border-top: 3px solid #940000;
        padding: 0.95rem 1rem;
        margin-bottom: 0.75rem;
    }
    #reportDetailModal .rd-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #940000 !important;
        margin-bottom: 0.3rem;
    }
    #reportDetailModal .rd-value {
        font-size: 0.92rem;
        font-weight: 700;
        color: #111 !important;
        line-height: 1.45;
        white-space: pre-wrap;
        word-break: break-word;
    }
    #reportDetailModal .modal-footer {
        border-top: 1px solid rgba(148, 0, 0, 0.1);
        background: #fff;
    }
    #reportDetailModal .modal-footer .btn {
        border-radius: 0;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.78rem;
    }

    .sx-teacher-bb .bb-report-card {
        border: 1px solid rgba(148, 0, 0, 0.1);
        border-top: 3px solid #940000;
        padding: 1rem 1.05rem;
        margin-bottom: 0.85rem;
        background: #fff;
        height: 100%;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-teacher-bb .bb-report-card .bb-report-meta {
        font-size: 0.72rem;
        font-weight: 700;
        color: #888 !important;
        margin-bottom: 0.45rem;
        letter-spacing: 0.02em;
    }
    .sx-teacher-bb .bb-report-card .bb-report-student {
        font-weight: 800;
        color: #111 !important;
        font-size: 1rem;
        line-height: 1.25;
    }
    .sx-teacher-bb .bb-report-card .bb-report-class {
        font-size: 0.8rem;
        font-weight: 600;
        color: #666 !important;
        margin-top: 0.15rem;
    }
    .sx-teacher-bb .bb-report-card .bb-report-desc {
        font-size: 0.86rem;
        font-weight: 600;
        color: #333 !important;
        margin: 0.65rem 0 0;
        border-top: 1px dashed rgba(148, 0, 0, 0.12);
        padding-top: 0.65rem;
        line-height: 1.45;
    }
    .sx-teacher-bb .bb-report-card .bb-report-resolution {
        font-size: 0.8rem;
        font-weight: 600;
        color: #444 !important;
        background: #fcf7f7;
        border: 1px solid rgba(148, 0, 0, 0.08);
        padding: 0.55rem 0.7rem;
        margin-top: 0.65rem;
    }
    .sx-teacher-bb .bb-cat-chip {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.28rem 0.6rem;
        background: #fcf7f7;
        border: 1px solid rgba(148, 0, 0, 0.15);
        color: #940000 !important;
    }

    .sx-teacher-bb .status-pill,
    #reportStudentModal .status-pill,
    #reportDetailModal .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.28rem 0.7rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .sx-teacher-bb .status-pill.is-pending,
    #reportStudentModal .status-pill.is-pending,
    #reportDetailModal .status-pill.is-pending { background: #fff3e8 !important; color: #9a4d00 !important; }
    .sx-teacher-bb .status-pill.is-viewed,
    #reportStudentModal .status-pill.is-viewed,
    #reportDetailModal .status-pill.is-viewed { background: #e8f1ff !important; color: #1d5bbf !important; }
    .sx-teacher-bb .status-pill.is-monitored,
    #reportStudentModal .status-pill.is-monitored,
    #reportDetailModal .status-pill.is-monitored { background: #f4eef8 !important; color: #6f42c1 !important; }
    .sx-teacher-bb .status-pill.is-resolved,
    #reportStudentModal .status-pill.is-resolved,
    #reportDetailModal .status-pill.is-resolved { background: #e8f5e9 !important; color: #1b7a2a !important; }

    .bb-ajax-bar {
        position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 20000;
        pointer-events: none; opacity: 0; transition: opacity 0.2s ease;
        background: rgba(148, 0, 0, 0.08);
    }
    .bb-ajax-bar.is-active { opacity: 1; }
    .bb-ajax-bar-fill {
        height: 100%; width: 0;
        background: linear-gradient(90deg, #940000, #ff4d4d, #940000);
        background-size: 200% 100%;
        box-shadow: 0 0 10px rgba(148, 0, 0, 0.55);
    }
    .bb-ajax-bar.is-active .bb-ajax-bar-fill {
        animation: bbBarRun 1.1s ease-in-out infinite, bbBarShine 1.2s linear infinite;
        width: 70%;
    }
    .bb-ajax-bar.is-finishing .bb-ajax-bar-fill { animation: none; width: 100% !important; }
    @keyframes bbBarRun {
        0% { margin-left: -40%; width: 30%; }
        50% { margin-left: 20%; width: 55%; }
        100% { margin-left: 100%; width: 30%; }
    }
    @keyframes bbBarShine {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .bb-ajax-overlay {
        position: fixed; inset: 0; z-index: 19999; display: none;
        align-items: center; justify-content: center;
        background: rgba(20, 0, 0, 0.28); backdrop-filter: blur(2px);
    }
    .bb-ajax-overlay.is-active { display: flex; }
    .bb-ajax-card {
        min-width: 220px; max-width: 320px; background: #fff;
        border: 2px solid #940000; box-shadow: 0 12px 40px rgba(148, 0, 0, 0.25);
        padding: 22px 24px 18px; text-align: center;
    }
    .bb-ajax-orbit { width: 52px; height: 52px; margin: 0 auto 12px; position: relative; }
    .bb-ajax-orbit span {
        position: absolute; inset: 0; border: 2px solid transparent;
        border-top-color: #940000; border-radius: 50%;
        animation: bbOrbit 0.9s linear infinite;
    }
    .bb-ajax-orbit span:nth-child(2) { inset: 8px; border-top-color: #c44; animation-duration: 0.7s; animation-direction: reverse; }
    .bb-ajax-orbit span:nth-child(3) { inset: 16px; border-top-color: #f0a0a0; animation-duration: 0.5s; }
    @keyframes bbOrbit { to { transform: rotate(360deg); } }
    .bb-ajax-text { font-weight: 700; color: #940000; font-size: 0.95rem; }
    .bb-ajax-sub { font-size: 0.75rem; color: #888; margin-top: 4px; text-transform: uppercase; }
    body.bb-ajax-busy { cursor: progress; }
    body.bb-ajax-busy #submitReportBtn { pointer-events: none; opacity: 0.75; }

    #reportStudentModal,
    #reportDetailModal {
        z-index: 1085 !important;
    }
    body.modal-open > .modal-backdrop {
        z-index: 1080 !important;
        background: rgba(20, 0, 0, 0.55);
    }
    #reportStudentModal .modal-dialog {
        max-width: 720px;
        margin: 1.25rem auto;
    }
    #reportStudentModal .modal-content {
        border: none;
        border-radius: 0;
        overflow: visible;
        background: #fff;
        box-shadow: 0 22px 56px rgba(15, 23, 42, 0.22);
    }
    #reportStudentModal .modal-header {
        border: none;
        padding: 1.15rem 1.4rem;
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%);
        color: #fff !important;
    }
    #reportStudentModal .modal-title {
        margin: 0;
        font-size: 0.9rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-weight: 800;
        color: #fff !important;
    }
    #reportStudentModal .modal-body {
        padding: 1.25rem 1.4rem 0.5rem;
        background: #faf7f7;
        max-height: min(70vh, 620px);
        overflow-y: auto;
    }
    #reportStudentModal .bb-modal-section {
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.1);
        border-top: 3px solid #940000;
        padding: 1rem 1.05rem 1.1rem;
        margin-bottom: 0.9rem;
    }
    #reportStudentModal .bb-modal-section-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin: 0 0 0.85rem;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #940000 !important;
    }
    #reportStudentModal .bb-step {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #940000;
        color: #fff !important;
        font-size: 0.7rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    #reportStudentModal .form-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: none;
        color: #222 !important;
        margin-bottom: 0.4rem;
    }
    #reportStudentModal .form-hint {
        display: block;
        margin-top: 0.4rem;
        font-size: 0.78rem;
        color: #666 !important;
        line-height: 1.4;
    }
    #reportStudentModal .form-control,
    #reportStudentModal .form-select:not(.select2-hidden-accessible) {
        border-radius: 0 !important;
        min-height: 46px !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        color: #111 !important;
        background: #fff !important;
        border: 1px solid #cfcfcf !important;
        -webkit-text-fill-color: #111 !important;
    }
    #reportStudentModal textarea.form-control {
        min-height: 110px !important;
        line-height: 1.45;
        padding-top: 0.7rem;
    }
    #reportStudentModal .form-control:focus,
    #reportStudentModal .form-select:not(.select2-hidden-accessible):focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12) !important;
    }
    /* Keep native select fully hidden once Select2 is active */
    #reportStudentModal select.select2-hidden-accessible {
        border: 0 !important;
        clip: rect(0 0 0 0) !important;
        height: 1px !important;
        margin: -1px !important;
        overflow: hidden !important;
        padding: 0 !important;
        position: absolute !important;
        width: 1px !important;
        white-space: nowrap !important;
        -webkit-text-fill-color: transparent !important;
    }
    #reportStudentModal .select2-container {
        width: 100% !important;
        display: block !important;
        margin-bottom: 0.35rem;
    }
    #reportStudentModal .select2-container--bootstrap-5 .select2-selection--single {
        border-radius: 0 !important;
        height: 46px !important;
        min-height: 46px !important;
        padding: 0 2.25rem 0 0.75rem !important;
        display: flex !important;
        align-items: center !important;
        overflow: hidden !important;
        background: #fff !important;
        border: 1px solid #cfcfcf !important;
        position: relative !important;
    }
    #reportStudentModal .select2-container--bootstrap-5.select2-container--focus .select2-selection--single,
    #reportStudentModal .select2-container--bootstrap-5.select2-container--open .select2-selection--single {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12) !important;
    }
    #reportStudentModal .select2-container--bootstrap-5 .select2-selection__rendered {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        line-height: 1.3 !important;
        color: #111 !important;
        font-weight: 700 !important;
        font-size: 0.92rem !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
        -webkit-text-fill-color: #111 !important;
    }
    #reportStudentModal .select2-container--bootstrap-5 .select2-selection__placeholder {
        color: #777 !important;
        font-weight: 500 !important;
        -webkit-text-fill-color: #777 !important;
    }
    #reportStudentModal .select2-container--bootstrap-5 .select2-selection__clear {
        position: absolute !important;
        right: 1.75rem !important;
        top: 50% !important;
        transform: translateY(-50%);
        margin: 0 !important;
        height: auto !important;
        float: none !important;
        z-index: 2;
    }
    #reportStudentModal .select2-container--bootstrap-5 .select2-selection__arrow {
        height: 44px !important;
        top: 1px !important;
        right: 4px !important;
    }
    #reportStudentModal #categoryEmptyHint {
        margin-top: 0.35rem;
    }
    #reportStudentModal #customProblemWrap {
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px dashed rgba(148, 0, 0, 0.15);
    }
    #reportStudentModal #studentSearchResults {
        border: 1px solid rgba(148, 0, 0, 0.12);
        background: #fff;
        max-height: 180px;
        overflow: auto;
    }
    #reportStudentModal #studentSearchResults:empty {
        display: none;
    }
    #reportStudentModal .student-result-item {
        cursor: pointer;
        border-radius: 0;
        border: none;
        border-bottom: 1px solid #f0eaea;
        font-weight: 600;
        color: #222 !important;
        padding: 0.7rem 0.85rem;
    }
    #reportStudentModal .student-result-item:last-child { border-bottom: none; }
    #reportStudentModal .student-result-item:hover {
        background: #fff5f5;
        color: #940000 !important;
    }
    #reportStudentModal #selectedStudentDisplay {
        display: none;
        align-items: center;
        gap: 0.65rem;
        background: #fcf7f7;
        border: 1px solid rgba(148, 0, 0, 0.18);
        border-left: 4px solid #940000;
        font-weight: 700;
        color: #111 !important;
        padding: 0.8rem 0.95rem;
        margin-top: 0.65rem;
    }
    #reportStudentModal #selectedStudentDisplay.is-visible {
        display: flex !important;
    }
    #reportStudentModal #selectedStudentDisplay .sel-label {
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #940000 !important;
        display: block;
        margin-bottom: 0.15rem;
    }
    #reportStudentModal #selectedStudentDisplay .sel-name {
        font-size: 0.95rem;
        font-weight: 800;
        color: #111 !important;
    }
    #reportStudentModal .bb-sms-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.12);
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
    }
    #reportStudentModal .bb-sms-box .form-check-label {
        font-weight: 700;
        color: #222 !important;
        font-size: 0.88rem;
    }
    #reportStudentModal .form-check-input:checked {
        background-color: #940000;
        border-color: #940000;
    }
    #reportStudentModal .modal-footer {
        border-top: 1px solid rgba(148, 0, 0, 0.1);
        background: #fff;
        padding: 0.95rem 1.4rem;
        display: flex;
        gap: 0.65rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    #reportStudentModal .modal-footer .btn {
        border-radius: 0;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        font-size: 0.78rem;
        min-width: 120px;
        min-height: 42px;
    }
    #reportStudentModal .modal-footer .btn-outline-light {
        border: 1px solid #d0d0d0;
        background: #fff;
        color: #222 !important;
    }
    .select2-container--open { z-index: 2000 !important; }
    .select2-dropdown {
        z-index: 2001 !important;
        border-radius: 0 !important;
        border-color: #940000 !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background: #940000 !important;
    }
    @media (max-width: 575.98px) {
        #reportStudentModal .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }
        #reportStudentModal .modal-footer {
            flex-direction: column-reverse;
        }
        #reportStudentModal .modal-footer .btn {
            width: 100%;
        }
    }

    html[data-sx-theme="dark"] .sx-teacher-bb .card,
    html[data-bs-theme="dark"] .sx-teacher-bb .card,
    html[data-sx-theme="dark"] .sx-teacher-bb .panel-card,
    html[data-bs-theme="dark"] .sx-teacher-bb .panel-card,
    html[data-sx-theme="dark"] .sx-teacher-bb .bb-sms-bar,
    html[data-bs-theme="dark"] .sx-teacher-bb .bb-sms-bar,
    html[data-sx-theme="dark"] .sx-teacher-bb .bb-report-card,
    html[data-bs-theme="dark"] .sx-teacher-bb .bb-report-card,
    html[data-sx-theme="dark"] .sx-teacher-bb .bb-table tbody td,
    html[data-bs-theme="dark"] .sx-teacher-bb .bb-table tbody td {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-bb .panel-card,
    html[data-bs-theme="dark"] .sx-teacher-bb .panel-card {
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-bb,
    html[data-bs-theme="dark"] .sx-teacher-bb,
    html[data-sx-theme="dark"] .sx-teacher-bb .text-muted,
    html[data-bs-theme="dark"] .sx-teacher-bb .text-muted,
    html[data-sx-theme="dark"] .sx-teacher-bb label,
    html[data-bs-theme="dark"] .sx-teacher-bb label {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-bb .bb-table thead th,
    html[data-bs-theme="dark"] .sx-teacher-bb .bb-table thead th {
        background: rgba(148, 0, 0, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-bb .bb-tabs .nav-link,
    html[data-bs-theme="dark"] .sx-teacher-bb .bb-tabs .nav-link,
    html[data-sx-theme="dark"] .sx-teacher-bb .bb-view-toggle .btn,
    html[data-bs-theme="dark"] .sx-teacher-bb .bb-view-toggle .btn,
    html[data-sx-theme="dark"] .sx-teacher-bb .btn-outline-light,
    html[data-bs-theme="dark"] .sx-teacher-bb .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }

    /* Detail modal: white cards need dark text (global dark rules wash them out) */
    html[data-sx-theme="dark"] #reportDetailModal .modal-body,
    html[data-bs-theme="dark"] #reportDetailModal .modal-body {
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] #reportDetailModal .rd-card,
    html[data-bs-theme="dark"] #reportDetailModal .rd-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] #reportDetailModal .rd-label,
    html[data-bs-theme="dark"] #reportDetailModal .rd-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #reportDetailModal .rd-value,
    html[data-bs-theme="dark"] #reportDetailModal .rd-value {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #reportDetailModal .rd-value .text-muted,
    html[data-bs-theme="dark"] #reportDetailModal .rd-value .text-muted,
    html[data-sx-theme="dark"] #reportDetailModal .text-muted,
    html[data-bs-theme="dark"] #reportDetailModal .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #reportDetailModal .modal-footer,
    html[data-bs-theme="dark"] #reportDetailModal .modal-footer {
        background: #1c1417 !important;
        border-top-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] #reportDetailModal .status-pill.is-pending,
    html[data-bs-theme="dark"] #reportDetailModal .status-pill.is-pending {
        background: #fff3e8 !important;
        color: #9a4d00 !important;
        -webkit-text-fill-color: #9a4d00 !important;
    }
    html[data-sx-theme="dark"] #reportDetailModal .status-pill.is-viewed,
    html[data-bs-theme="dark"] #reportDetailModal .status-pill.is-viewed {
        background: #e8f1ff !important;
        color: #1d5bbf !important;
        -webkit-text-fill-color: #1d5bbf !important;
    }
    html[data-sx-theme="dark"] #reportDetailModal .status-pill.is-monitored,
    html[data-bs-theme="dark"] #reportDetailModal .status-pill.is-monitored {
        background: #f4eef8 !important;
        color: #6f42c1 !important;
        -webkit-text-fill-color: #6f42c1 !important;
    }
    html[data-sx-theme="dark"] #reportDetailModal .status-pill.is-resolved,
    html[data-bs-theme="dark"] #reportDetailModal .status-pill.is-resolved {
        background: #e8f5e9 !important;
        color: #1b7a2a !important;
        -webkit-text-fill-color: #1b7a2a !important;
    }

    /* List/grid status pills also stay readable in dark mode */
    html[data-sx-theme="dark"] .sx-teacher-bb .status-pill.is-pending,
    html[data-bs-theme="dark"] .sx-teacher-bb .status-pill.is-pending {
        background: #fff3e8 !important; color: #9a4d00 !important; -webkit-text-fill-color: #9a4d00 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-bb .status-pill.is-viewed,
    html[data-bs-theme="dark"] .sx-teacher-bb .status-pill.is-viewed {
        background: #e8f1ff !important; color: #1d5bbf !important; -webkit-text-fill-color: #1d5bbf !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-bb .status-pill.is-monitored,
    html[data-bs-theme="dark"] .sx-teacher-bb .status-pill.is-monitored {
        background: #f4eef8 !important; color: #6f42c1 !important; -webkit-text-fill-color: #6f42c1 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-bb .status-pill.is-resolved,
    html[data-bs-theme="dark"] .sx-teacher-bb .status-pill.is-resolved {
        background: #e8f5e9 !important; color: #1b7a2a !important; -webkit-text-fill-color: #1b7a2a !important;
    }
</style>

<div id="bbAjaxBar" class="bb-ajax-bar" aria-hidden="true"><div class="bb-ajax-bar-fill"></div></div>
<div id="bbAjaxOverlay" class="bb-ajax-overlay" aria-hidden="true">
    <div class="bb-ajax-card">
        <div class="bb-ajax-orbit"><span></span><span></span><span></span></div>
        <div class="bb-ajax-text" id="bbAjaxText">{{ __('teacher.bb.working') }}</div>
        <div class="bb-ajax-sub">{{ __('teacher.bb.please_wait') }}</div>
    </div>
</div>

<div class="sx-teacher-bb">
    <div class="card panel-card mb-0">
        <div class="card-body">
            <div class="bb-sms-bar">
                <div>
                    <h6><i class="fi fi-rr-envelope me-1"></i> {{ __('teacher.bb.notify_sms') }}</h6>
                    <p>{{ __('teacher.bb.notify_sms_hint') }}</p>
                </div>
                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="notifyParentSmsToggle" style="cursor:pointer;">
                    <label class="form-check-label bb-sms-label mb-0" for="notifyParentSmsToggle" id="notifyParentSmsLabel">{{ __('teacher.bb.off') }}</label>
                </div>
            </div>

            <div class="bb-toolbar">
                <ul class="bb-tabs" id="myReportsTabs">
                    <li><a class="nav-link active" data-status="" href="#">{{ __('teacher.bb.all') }}</a></li>
                    <li><a class="nav-link" data-status="pending" href="#">{{ __('teacher.pending') }}</a></li>
                    <li><a class="nav-link" data-status="viewed" href="#">{{ __('teacher.bb.viewed') }}</a></li>
                    <li><a class="nav-link" data-status="monitored" href="#">{{ __('teacher.bb.monitored') }}</a></li>
                    <li><a class="nav-link" data-status="resolved" href="#">{{ __('teacher.bb.resolved') }}</a></li>
                </ul>
                <div class="btn-group btn-group-sm bb-view-toggle" id="reportsViewToggle">
                    <button type="button" class="btn" data-view="grid" title="{{ __('teacher.bb.grid_view') }}"><i class="fi fi-rr-apps"></i></button>
                    <button type="button" class="btn" data-view="list" title="{{ __('teacher.bb.list_view') }}"><i class="fi fi-rr-list"></i></button>
                </div>
            </div>

            <div class="bb-reports-wrapper" id="reportsDataWrapper">
                <div class="bb-reports-list-view">
                    <div class="table-responsive">
                        <table class="table bb-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('teacher.date') }}</th>
                                    <th>{{ __('teacher.student') }}</th>
                                    <th>{{ __('teacher.class') }}</th>
                                    <th style="min-width:160px;">{{ __('teacher.bb.description') }}</th>
                                    <th>{{ __('teacher.status') }}</th>
                                    <th>{{ __('teacher.bb.resolution') }}</th>
                                </tr>
                            </thead>
                            <tbody id="myReportsBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="bb-reports-grid-view row g-3" id="myReportsGrid"></div>
            </div>
        </div>
    </div>
</div>

{{-- Report detail modal --}}
<div class="modal fade" id="reportDetailModal" tabindex="-1" aria-labelledby="reportDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportDetailTitle"><i class="fi fi-rr-document me-1"></i> {{ __('teacher.bb.report_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body" id="reportDetailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light border" data-bs-dismiss="modal">{{ __('teacher.close') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Report Modal (BS5) --}}
<div class="modal fade" id="reportStudentModal" tabindex="-1" aria-labelledby="reportStudentTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportStudentTitle"><i class="fi fi-rr-exclamation me-1"></i> {{ __('teacher.bb.report_student') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="bb-modal-section">
                    <h6 class="bb-modal-section-title"><span class="bb-step">1</span> {{ __('teacher.student') }}</h6>
                    <label class="form-label" for="studentSearchInput">{{ __('teacher.bb.search_student') }}</label>
                    <input type="text" id="studentSearchInput" class="form-control" placeholder="{{ __('teacher.bb.search_ph') }}" autocomplete="off">
                    <span class="form-hint">{{ __('teacher.bb.active_only_hint') }}</span>
                    <input type="hidden" id="selectedStudentID">
                    <div id="studentSearchResults" class="list-group mt-2"></div>
                    <div id="selectedStudentDisplay">
                        <div>
                            <span class="sel-label">{{ __('teacher.bb.selected_student') }}</span>
                            <span class="sel-name" id="selectedStudentName"></span>
                        </div>
                    </div>
                </div>

                <div class="bb-modal-section">
                    <h6 class="bb-modal-section-title"><span class="bb-step">2</span> {{ __('teacher.bb.problem_category') }}</h6>
                    <label class="form-label" for="problemCategorySelect">{{ __('teacher.bb.choose_category') }}</label>
                    <select id="problemCategorySelect" class="form-select bb-search-select">
                        <option value="">{{ __('teacher.bb.search_category') }}</option>
                        @foreach($categories->filter(fn ($c) => strcasecmp((string) $c->name, 'Other') !== 0) as $cat)
                            <option value="{{ $cat->categoryID }}">{{ $cat->name }} ({{ __('teacher.bb.weight') }}: {{ $cat->weight }}%)</option>
                        @endforeach
                        <option value="other">{{ __('teacher.bb.other') }}</option>
                    </select>
                    @if($categories->isEmpty())
                        <span class="form-hint" id="categoryEmptyHint">{{ __('teacher.bb.no_categories', ['other' => __('teacher.bb.other')]) }}</span>
                    @endif

                    <div class="d-none" id="customProblemWrap">
                        <label class="form-label" for="customProblemName">{{ __('teacher.bb.write_problem') }}</label>
                        <input type="text" id="customProblemName" class="form-control" maxlength="150" placeholder="{{ __('teacher.bb.write_problem_ph') }}">
                        <span class="form-hint">{{ __('teacher.bb.write_problem_hint') }}</span>
                    </div>
                </div>

                <div class="bb-modal-section">
                    <h6 class="bb-modal-section-title"><span class="bb-step">3</span> {{ __('teacher.bb.description') }}</h6>
                    <label class="form-label" for="reportDescription">{{ __('teacher.bb.what_happened') }}</label>
                    <textarea id="reportDescription" class="form-control" rows="4" placeholder="{{ __('teacher.bb.describe_ph') }}"></textarea>
                </div>

                <div class="bb-sms-box">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="notifyParentSmsModal">
                        <label class="form-check-label" for="notifyParentSmsModal">{{ __('teacher.bb.send_sms_parent') }}</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">{{ __('teacher.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="submitReportBtn">
                    <i class="fi fi-rr-disk me-1"></i> {{ __('teacher.bb.submit_report') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function() {
    var i18n = @json(__('teacher.bb'));
    i18n.date = @json(__('teacher.date'));
    i18n.student = @json(__('teacher.student'));
    i18n.class = @json(__('teacher.class'));
    i18n.status = @json(__('teacher.status'));
    i18n.close = @json(__('teacher.close'));
    i18n.cancel = @json(__('teacher.cancel'));
    i18n.pending = i18n.pending || @json(__('teacher.pending'));
    i18n.loading = @json(__('teacher.loading'));

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const searchUrl = @json(route('black_book.students.search'));
    const storeUrl = @json(route('black_book.reports.store'));
    const listUrl = @json(route('black_book.reports.index'));

    function hideReportModal() {
        const el = document.getElementById('reportStudentModal');
        if (window.bootstrap && bootstrap.Modal && el) {
            bootstrap.Modal.getOrCreateInstance(el).hide();
        } else if (window.jQuery && jQuery.fn.modal) {
            jQuery('#reportStudentModal').modal('hide');
        }
    }

    function initProblemCategorySelect() {
        const $ = window.jQuery;
        if (!$ || !$.fn.select2) return;
        const $sel = $('#problemCategorySelect');
        if (!$sel.length) return;
        if ($sel.hasClass('select2-hidden-accessible')) {
            try { $sel.select2('destroy'); } catch (e) {}
        }
        $sel.off('change.bbOther').on('change.bbOther', toggleCustomProblemField);
        $sel.select2({
            theme: 'bootstrap-5',
            placeholder: i18n.search_category,
            allowClear: true,
            width: '100%',
            dropdownParent: $('#reportStudentModal'),
            minimumResultsForSearch: 0,
            language: {
                noResults: function() { return i18n.no_category_found; },
                searching: function() { return i18n.searching; }
            }
        });
        toggleCustomProblemField();
    }

    function toggleCustomProblemField() {
        const val = window.jQuery
            ? String(window.jQuery('#problemCategorySelect').val() || '')
            : String(document.getElementById('problemCategorySelect').value || '');
        const wrap = document.getElementById('customProblemWrap');
        const input = document.getElementById('customProblemName');
        const emptyHint = document.getElementById('categoryEmptyHint');
        const isOther = val === 'other';
        if (wrap) wrap.classList.toggle('d-none', !isOther);
        if (emptyHint) emptyHint.classList.toggle('d-none', isOther);
        if (!isOther && input) input.value = '';
        if (isOther && input) {
            setTimeout(function() { input.focus(); }, 50);
        }
    }

    const PARENT_SMS_KEY = 'black_book_notify_parent_sms';

    function isParentSmsEnabled() {
        return localStorage.getItem(PARENT_SMS_KEY) === '1';
    }

    function setParentSmsEnabled(on) {
        localStorage.setItem(PARENT_SMS_KEY, on ? '1' : '0');
        syncParentSmsToggles();
    }

    function syncParentSmsToggles() {
        const on = isParentSmsEnabled();
        const pageToggle = document.getElementById('notifyParentSmsToggle');
        const modalToggle = document.getElementById('notifyParentSmsModal');
        const label = document.getElementById('notifyParentSmsLabel');
        if (pageToggle) pageToggle.checked = on;
        if (modalToggle) modalToggle.checked = on;
        if (label) {
            label.textContent = on ? i18n.on : i18n.off;
            label.style.color = on ? '#1b7a2a' : '#888';
        }
    }

    document.getElementById('notifyParentSmsToggle').addEventListener('change', function() {
        setParentSmsEnabled(this.checked);
    });
    document.getElementById('notifyParentSmsModal').addEventListener('change', function() {
        setParentSmsEnabled(this.checked);
    });

    const reportModalEl = document.getElementById('reportStudentModal');
    if (reportModalEl && reportModalEl.parentElement !== document.body) {
        document.body.appendChild(reportModalEl);
    }
    if (reportModalEl) {
        reportModalEl.addEventListener('show.bs.modal', syncParentSmsToggles);
        reportModalEl.addEventListener('shown.bs.modal', initProblemCategorySelect);
    }
    if (window.jQuery) {
        jQuery('#reportStudentModal').on('show.bs.modal', syncParentSmsToggles);
        jQuery('#reportStudentModal').on('shown.bs.modal', initProblemCategorySelect);
    }
    syncParentSmsToggles();

    function clearSelectedStudent() {
        document.getElementById('selectedStudentID').value = '';
        const nameEl = document.getElementById('selectedStudentName');
        if (nameEl) nameEl.textContent = '';
        document.getElementById('selectedStudentDisplay').classList.remove('is-visible');
    }

    function setSelectedStudent(id, display) {
        document.getElementById('selectedStudentID').value = id;
        const nameEl = document.getElementById('selectedStudentName');
        if (nameEl) nameEl.textContent = display || '';
        document.getElementById('selectedStudentDisplay').classList.add('is-visible');
        document.getElementById('studentSearchResults').innerHTML = '';
        document.getElementById('studentSearchInput').value = '';
    }

    let bbAjaxActive = 0;
    let bbAjaxFinishTimer = null;

    function bbLoadingStart(message) {
        bbAjaxActive += 1;
        clearTimeout(bbAjaxFinishTimer);
        document.getElementById('bbAjaxBar').classList.add('is-active');
        document.getElementById('bbAjaxText').textContent = message || i18n.working;
        document.getElementById('bbAjaxOverlay').classList.add('is-active');
        document.body.classList.add('bb-ajax-busy');
    }

    function bbLoadingStop() {
        bbAjaxActive = Math.max(0, bbAjaxActive - 1);
        if (bbAjaxActive > 0) return;
        const bar = document.getElementById('bbAjaxBar');
        bar.classList.add('is-finishing');
        document.getElementById('bbAjaxOverlay').classList.remove('is-active');
        document.body.classList.remove('bb-ajax-busy');
        bbAjaxFinishTimer = setTimeout(function() {
            bar.classList.remove('is-active', 'is-finishing');
        }, 220);
    }

    async function bbApi(url, method, body, loadingOption) {
        const skipLoad = loadingOption === false;
        if (!skipLoad) bbLoadingStart(typeof loadingOption === 'string' ? loadingOption : (method === 'POST' ? i18n.reporting : i18n.loading));
        try {
            const opts = { method: method || 'GET', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } };
            if (body) {
                opts.method = method || 'POST';
                opts.headers['Content-Type'] = 'application/json';
                opts.body = JSON.stringify(body);
            }
            const res = await fetch(url, opts);
            return await res.json();
        } finally {
            if (!skipLoad) bbLoadingStop();
        }
    }

    let searchTimer;
    let allReports = @json($myReports);
    let filterStatus = '';

    function isMobile() { return window.matchMedia('(max-width: 767.98px)').matches; }

    function getReportsEffectiveView() {
        const wrapper = document.getElementById('reportsDataWrapper');
        const forced = wrapper.getAttribute('data-active-view');
        if (forced === 'grid' || forced === 'list') return forced;
        return isMobile() ? 'grid' : 'list';
    }

    function syncReportsViewToggle() {
        const view = getReportsEffectiveView();
        document.getElementById('reportsViewToggle').querySelectorAll('button').forEach(b => {
            b.classList.toggle('active', b.getAttribute('data-view') === view);
        });
    }

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function getResolutionText(r) {
        if (r.status !== 'resolved' && r.status !== 'monitored') return '-';
        let resolution = r.resolve_method === 'parent_call'
            ? i18n.parent_call + ', ' + (r.resolved_by_name || '')
            : ((r.punishment_category || i18n.punishment) + ', ' + (r.punishment_description || '').substring(0, 80));
        if (r.status === 'monitored') {
            resolution += ' [' + String(i18n.monitoring_until || '').replace(':date', r.monitoring_deadline || '-') + ']';
        }
        return resolution;
    }

    document.getElementById('reportsViewToggle').querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            const wrapper = document.getElementById('reportsDataWrapper');
            wrapper.setAttribute('data-active-view', view);
            wrapper.setAttribute('data-user-view', '1');
            syncReportsViewToggle();
        });
    });

    window.addEventListener('resize', function() {
        const wrapper = document.getElementById('reportsDataWrapper');
        if (!wrapper.hasAttribute('data-user-view')) {
            wrapper.removeAttribute('data-active-view');
        }
        syncReportsViewToggle();
    });

    function statusBadge(s) {
        const map = {
            resolved: ['is-resolved', i18n.resolved],
            monitored: ['is-monitored', i18n.monitored],
            viewed: ['is-viewed', i18n.viewed],
            pending: ['is-pending', i18n.pending],
        };
        const item = map[s] || map.pending;
        return '<span class="status-pill ' + item[0] + '">' + item[1] + '</span>';
    }

    function truncateText(str, maxLen) {
        const text = String(str || '').trim();
        if (text.length <= maxLen) return text;
        return text.substring(0, maxLen).trim() + '…';
    }

    function showReportDetail(reportID) {
        const r = allReports.find(item => String(item.reportID) === String(reportID));
        if (!r) return;
        const resolution = getResolutionText(r);
        const body = document.getElementById('reportDetailBody');
        const el = document.getElementById('reportDetailModal');
        if (!body || !el) return;

        body.innerHTML =
            '<div class="rd-card"><span class="rd-label">' + i18n.student + '</span><div class="rd-value">' + escapeHtml(r.student_name) + '</div></div>' +
            '<div class="rd-card"><span class="rd-label">' + i18n.class + '</span><div class="rd-value">' + escapeHtml(r.class_name) + '</div></div>' +
            '<div class="rd-card"><span class="rd-label">' + i18n.category + '</span><div class="rd-value">' + escapeHtml(r.problem_category) + ' (' + escapeHtml(r.problem_weight) + '%)</div></div>' +
            '<div class="rd-card"><span class="rd-label">' + i18n.status + '</span><div class="rd-value">' + statusBadge(r.status) + ' <span class="ms-2 small text-muted">' + escapeHtml(r.created_at) + '</span></div></div>' +
            '<div class="rd-card"><span class="rd-label">' + i18n.full_description + '</span><div class="rd-value">' + escapeHtml(r.description || '—') + '</div></div>' +
            (resolution !== '-' ? '<div class="rd-card"><span class="rd-label">' + i18n.resolution + '</span><div class="rd-value">' + escapeHtml(resolution) + '</div></div>' : '');

        if (el.parentElement !== document.body) {
            document.body.appendChild(el);
        }

        // Prefer Bootstrap 5 only (avoid double init with jQuery plugin)
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el, { backdrop: true, keyboard: true, focus: true }).show();
        } else if (window.jQuery && jQuery.fn.modal) {
            jQuery(el).modal('show');
        }
    }
    window.showReportDetail = showReportDetail;

    document.getElementById('reportDetailModal')?.addEventListener('hidden.bs.modal', function() {
        // Clear any stuck backdrop / body lock if Bootstrap left remnants
        if (!document.querySelector('.modal.show')) {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
        }
    });

    function renderDescriptionCell(r) {
        const full = String(r.description || '').trim();
        const short = truncateText(full, 42);
        const needsMore = full.length > 42;
        return '<div class="bb-desc-short" title="' + escapeHtml(full) + '">' + escapeHtml(short || '—') + '</div>' +
            (needsMore
                ? '<button type="button" class="bb-view-more" onclick="showReportDetail(' + r.reportID + ')"><i class="fi fi-rr-eye"></i> ' + i18n.view_more + '</button>'
                : (full
                    ? '<button type="button" class="bb-view-more" onclick="showReportDetail(' + r.reportID + ')"><i class="fi fi-rr-eye"></i> ' + i18n.view + '</button>'
                    : ''));
    }

    function renderReports() {
        const rows = filterStatus ? allReports.filter(r => r.status === filterStatus) : allReports;
        const tbody = document.getElementById('myReportsBody');
        const grid = document.getElementById('myReportsGrid');
        const emptyMsg = i18n.empty_reports;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">' + (filterStatus ? i18n.no_reports_found : emptyMsg) + '</td></tr>';
            grid.innerHTML = '<div class="col-12 text-center text-muted py-4">' + (filterStatus ? i18n.no_reports_found : emptyMsg) + '</div>';
            return;
        }

        tbody.innerHTML = rows.map(r => {
            const resolution = getResolutionText(r);
            const shortResolution = truncateText(resolution, 36);
            return '<tr>' +
                '<td>' + escapeHtml(r.created_at) + '</td>' +
                '<td class="fw-bold">' + escapeHtml(r.student_name) + '</td>' +
                '<td>' + escapeHtml(r.class_name) + '</td>' +
                '<td class="td-desc">' + renderDescriptionCell(r) + '</td>' +
                '<td>' + statusBadge(r.status) + '</td>' +
                '<td class="td-resolution" title="' + escapeHtml(resolution) + '">' + escapeHtml(shortResolution) + '</td>' +
                '</tr>';
        }).join('');

        grid.innerHTML = rows.map(r => {
            const resolution = getResolutionText(r);
            const fullDesc = String(r.description || '').trim();
            const shortDesc = truncateText(fullDesc, 110);
            return '<div class="col-md-6 col-lg-4 col-sm-12">' +
                '<div class="bb-report-card">' +
                '<div class="bb-report-meta"><i class="fi fi-rr-clock"></i> ' + escapeHtml(r.created_at) + '</div>' +
                '<div class="bb-report-student">' + escapeHtml(r.student_name) + '</div>' +
                '<div class="bb-report-class"><i class="fi fi-rr-graduation-cap"></i> ' + escapeHtml(r.class_name) + '</div>' +
                '<div class="mt-2">' + statusBadge(r.status) + '</div>' +
                '<div class="bb-report-desc">' + escapeHtml(shortDesc || '—') +
                (fullDesc.length > 110 ? ' <button type="button" class="bb-view-more ms-1" onclick="showReportDetail(' + r.reportID + ')">' + i18n.view_more + '</button>' : '') +
                '</div>' +
                (resolution !== '-' ? '<div class="bb-report-resolution"><strong>' + i18n.resolution_label + '</strong> ' + escapeHtml(resolution) + '</div>' : '') +
                '</div></div>';
        }).join('');
    }

    syncReportsViewToggle();
    renderReports();

    document.querySelectorAll('#myReportsTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('#myReportsTabs .nav-link').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterStatus = this.getAttribute('data-status');
            renderReports();
        });
    });

    document.getElementById('studentSearchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        clearSelectedStudent();
        if (q.length < 2) {
            document.getElementById('studentSearchResults').innerHTML = '';
            return;
        }
        searchTimer = setTimeout(async () => {
            const res = await bbApi(searchUrl + '?search=' + encodeURIComponent(q) + '&for_report=1', 'GET', null, false);
            const box = document.getElementById('studentSearchResults');
            if (res.success === false && (res.error_code === 'no_academic_year' || res.error_code === 'no_active_enrollments')) {
                box.innerHTML = '<div class="list-group-item text-muted small">' + (res.message || i18n.no_active_enrollments) + '</div>';
                return;
            }
            const students = (res.students || []).filter(s => s.can_report !== false);
            if (!students.length) {
                box.innerHTML = '<div class="list-group-item text-muted small">' + i18n.no_students_matched + '</div>';
                return;
            }
            box.innerHTML = students.map(s =>
                '<button type="button" class="list-group-item list-group-item-action student-result-item" data-id="' + s.studentID + '" data-display="' + String(s.display || '').replace(/"/g, '&quot;') + '">' + escapeHtml(s.display) + '</button>'
            ).join('');
            box.querySelectorAll('.student-result-item').forEach(btn => {
                btn.onclick = () => {
                    setSelectedStudent(btn.getAttribute('data-id'), btn.getAttribute('data-display'));
                };
            });
        }, 300);
    });

    document.getElementById('problemCategorySelect').addEventListener('change', toggleCustomProblemField);

    document.getElementById('submitReportBtn').onclick = async () => {
        const studentID = document.getElementById('selectedStudentID').value;
        const problem_categoryID = window.jQuery ? window.jQuery('#problemCategorySelect').val() : document.getElementById('problemCategorySelect').value;
        const custom_problem_name = (document.getElementById('customProblemName').value || '').trim();
        const description = document.getElementById('reportDescription').value.trim();
        if (!studentID) return Swal.fire(i18n.error, i18n.err_select_student, 'error');
        if (!problem_categoryID) return Swal.fire(i18n.error, i18n.err_select_category, 'error');
        if (problem_categoryID === 'other' && custom_problem_name.length < 2) {
            return Swal.fire(i18n.error, i18n.err_write_problem, 'error');
        }
        if (description.length < 5) return Swal.fire(i18n.error, i18n.err_description, 'error');

        const btn = document.getElementById('submitReportBtn');
        btn.disabled = true;
        const payload = {
            studentID,
            problem_categoryID,
            description,
            send_parent_sms: isParentSmsEnabled(),
        };
        if (problem_categoryID === 'other') {
            payload.custom_problem_name = custom_problem_name;
        }
        const res = await bbApi(storeUrl, 'POST', payload, i18n.reporting);
        btn.disabled = false;

        if (res.success) {
            Swal.fire({ icon: 'success', title: i18n.success, text: res.message, confirmButtonColor: '#940000' });
            hideReportModal();
            clearSelectedStudent();
            document.getElementById('reportDescription').value = '';
            document.getElementById('customProblemName').value = '';
            if (window.jQuery) {
                try { window.jQuery('#problemCategorySelect').val(null).trigger('change'); } catch (e) {}
            } else {
                document.getElementById('problemCategorySelect').value = '';
            }
            toggleCustomProblemField();
            const listRes = await bbApi(listUrl + '?mine=1', 'GET', null, i18n.refreshing);
            if (listRes.success) { allReports = listRes.reports; renderReports(); }
        } else {
            Swal.fire(i18n.error, res.message, 'error');
        }
    };
})();
</script>
@endpush
