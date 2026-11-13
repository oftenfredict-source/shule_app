@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.character').' - ShuleXpert')
@section('page_title', __('teacher.pages.character'))
@section('page_breadcrumb', __('teacher.pages.character_bc'))

@section('page_actions')
    @php $periodStatus = $periodStatus ?? 'active'; @endphp
    @if($periodStatus === 'active')
        <span class="badge bg-primary rounded-pill px-3 py-2">
            <i class="fi fi-rr-calendar-check me-1"></i> {{ __('teacher.char.active_period') }}
        </span>
    @elseif($periodStatus === 'upcoming')
        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
            <i class="fi fi-rr-clock me-1"></i> {{ __('teacher.char.upcoming_period') }}
        </span>
    @elseif($periodStatus === 'ended')
        <span class="badge bg-secondary rounded-pill px-3 py-2">
            <i class="fi fi-rr-calendar me-1"></i> {{ __('teacher.char.period_ended') }}
        </span>
    @else
        <span class="badge bg-primary rounded-pill px-3 py-2">
            <i class="fi fi-rr-info me-1"></i> {{ __('teacher.char.not_assigned') }}
        </span>
    @endif
@endsection

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca span:not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-ca label {
        color: #000 !important;
    }
    .sx-teacher-ca .card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
    }
    .sx-teacher-ca .panel-card .card-body { padding: 1.25rem 1.35rem; }
    .sx-teacher-ca .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-teacher-ca .students-panel {
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        overflow: hidden;
        background: #fff;
    }
    .sx-teacher-ca .students-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.95rem 1.25rem;
        background: linear-gradient(135deg, #fcf7f7 0%, #fff 70%);
        border-bottom: 1px solid rgba(148, 0, 0, 0.1);
    }
    .sx-teacher-ca .students-panel-head h6 {
        margin: 0;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-size: 0.78rem;
        color: #940000 !important;
    }
    .sx-teacher-ca .students-count {
        font-size: 0.75rem;
        font-weight: 700;
        color: #666 !important;
        background: #fff;
        border: 1px solid #e8e0e0;
        padding: 0.25rem 0.7rem;
        border-radius: 0;
        white-space: nowrap;
    }
    .sx-teacher-ca .ca-table {
        margin: 0;
        width: 100%;
    }
    .sx-teacher-ca .ca-table thead th {
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
    .sx-teacher-ca .ca-table tbody td {
        vertical-align: middle;
        font-size: 0.9rem;
        padding: 0.85rem 1rem;
        border-color: rgba(148, 0, 0, 0.07);
        background: #fff;
    }
    .sx-teacher-ca .ca-table tbody tr.student-row {
        transition: background .15s ease;
    }
    .sx-teacher-ca .ca-table tbody tr.student-row:hover td {
        background: #fff8f8;
    }
    .sx-teacher-ca .ca-table tbody tr.student-row.is-locked td {
        opacity: 0.62;
    }
    .sx-teacher-ca .ca-table tbody tr.student-row.is-open td {
        background: #fcf7f7;
    }
    .sx-teacher-ca .ca-table .student-cell {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-width: 0;
    }
    .sx-teacher-ca .student-avatar {
        width: 44px;
        height: 44px;
        object-fit: cover;
        border: 2px solid #940000;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .sx-teacher-ca .student-name {
        font-weight: 800;
        font-size: 0.95rem;
        line-height: 1.25;
        margin: 0;
        color: #111 !important;
    }
    .sx-teacher-ca .student-meta {
        font-size: 0.75rem;
        color: #666 !important;
        margin-top: 0.15rem;
    }
    .sx-teacher-ca .adm-chip {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        padding: 0.28rem 0.65rem;
        background: #f5f5f5;
        border: 1px solid #e6e6e6;
        color: #333 !important;
    }
    .sx-teacher-ca .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.28rem 0.75rem;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .sx-teacher-ca .status-pill.is-ready {
        background: #e8f5e9;
        color: #1b7a2a !important;
    }
    .sx-teacher-ca .status-pill.is-pending {
        background: #fff3e8;
        color: #9a4d00 !important;
    }
    .sx-teacher-ca .status-pill.is-locked {
        background: #f0f0f0;
        color: #666 !important;
    }
    .sx-teacher-ca .ca-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        justify-content: flex-end;
    }
    .sx-teacher-ca .ca-actions .btn {
        font-weight: 700;
        font-size: 0.78rem;
        min-width: 88px;
        border-radius: 0;
    }
    .sx-teacher-ca .assessment-row td {
        padding: 0 !important;
        border-top: none !important;
        background: #fcf7f7 !important;
    }
    .sx-teacher-ca .assessment-dropdown {
        display: none;
        border-top: 1px solid rgba(148, 0, 0, 0.12);
        background: #fcf7f7;
        padding: 1.15rem 1.25rem 1.25rem !important;
    }
    .sx-teacher-ca .assessment-form {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
    }
    .sx-teacher-ca .cat-label {
        font-size: 0.72rem;
        font-weight: 800;
        color: #940000 !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .sx-teacher-ca .student-cat-input,
    .sx-teacher-ca select.student-cat-input,
    .sx-teacher-ca textarea.student-cat-input {
        background: #fff !important;
        border: 1px solid #cfcfcf !important;
        min-height: 44px;
        border-radius: 0;
        color: #111 !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        -webkit-text-fill-color: #111 !important;
    }
    .sx-teacher-ca select.student-cat-input option {
        color: #111 !important;
        background: #fff !important;
        font-weight: 600;
    }
    .sx-teacher-ca select.student-cat-input option[value=""] {
        color: #666 !important;
        font-weight: 500;
    }
    .sx-teacher-ca textarea.student-cat-input {
        min-height: 90px;
    }
    .sx-teacher-ca textarea.student-cat-input::placeholder {
        color: #888 !important;
        font-weight: 500 !important;
        opacity: 1;
        -webkit-text-fill-color: #888 !important;
    }
    .sx-teacher-ca .form-control:focus,
    .sx-teacher-ca .form-select:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12) !important;
    }
    /* Student record modal */
    #historyModal .modal-content {
        border: none;
        border-radius: 0;
        overflow: hidden;
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.18);
    }
    #historyModal .modal-header {
        border: none;
        padding: 1.15rem 1.35rem;
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%);
    }
    #historyModal .modal-title {
        font-size: 0.82rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 800;
    }
    #historyModal .modal-body {
        padding: 0 !important;
        background: #f7f4f4;
    }
    #historyModal .hr-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.35rem 1.4rem;
        background: linear-gradient(135deg, #fff 0%, #fcf7f7 100%);
        border-bottom: 1px solid rgba(148, 0, 0, 0.1);
    }
    #historyModal .hr-avatar {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #940000;
        box-shadow: 0 6px 16px rgba(148, 0, 0, 0.18);
        flex-shrink: 0;
        background: #fff;
    }
    #historyModal .hr-name {
        margin: 0 0 0.35rem;
        font-size: 1.2rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #111 !important;
        line-height: 1.2;
    }
    #historyModal .hr-adm {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        padding: 0.3rem 0.7rem;
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.18);
        color: #940000 !important;
    }
    #historyModal .hr-subtitle {
        margin: 0.35rem 0 0;
        font-size: 0.75rem;
        font-weight: 600;
        color: #777 !important;
    }
    #historyModal .hr-body {
        padding: 1.15rem 1.25rem 1.35rem;
    }
    #historyModal .hr-section-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }
    #historyModal .hr-section-label span {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #940000 !important;
    }
    #historyModal .hr-count {
        font-size: 0.7rem;
        font-weight: 700;
        color: #666 !important;
        background: #fff;
        border: 1px solid #e8e0e0;
        padding: 0.2rem 0.55rem;
    }
    #historyModal .hr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 0.75rem;
    }
    #historyModal .history-item {
        position: relative;
        padding: 1rem 1rem 1.05rem;
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.1);
        border-top: 3px solid #940000;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        min-height: 110px;
    }
    #historyModal .history-item .hi-cat {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.7rem;
    }
    #historyModal .history-item .hi-num {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #940000;
        color: #fff !important;
        font-size: 0.68rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    #historyModal .history-item .hi-title {
        margin: 0;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #940000 !important;
        line-height: 1.25;
    }
    #historyModal .history-item .hi-result {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.92rem;
        font-weight: 800;
        color: #111 !important;
        background: #fcf7f7;
        border: 1px solid rgba(148, 0, 0, 0.12);
        padding: 0.45rem 0.75rem;
        line-height: 1.2;
    }
    #historyModal .history-item .hi-weight {
        font-size: 0.7rem;
        font-weight: 800;
        color: #940000 !important;
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.15);
        padding: 0.1rem 0.4rem;
    }
    #historyModal .history-item .hi-text {
        margin: 0.55rem 0 0;
        font-size: 0.86rem;
        font-weight: 600;
        color: #333 !important;
        line-height: 1.45;
    }
    #historyModal .hr-empty {
        text-align: center;
        padding: 2.5rem 1.25rem;
        background: #fff;
        border: 1px dashed rgba(148, 0, 0, 0.2);
    }
    #historyModal .hr-empty i {
        display: block;
        font-size: 2rem;
        color: #940000;
        margin-bottom: 0.75rem;
        opacity: 0.75;
    }
    #historyModal .hr-empty h6 {
        font-weight: 800;
        color: #333 !important;
        margin-bottom: 0.35rem;
    }
    #historyModal .hr-empty p {
        margin: 0;
        font-size: 0.82rem;
        color: #777 !important;
    }
    #historyModal .modal-footer {
        border-top: 1px solid rgba(148, 0, 0, 0.1);
        background: #fff;
        padding: 0.9rem 1.25rem;
    }
    #historyModal .modal-footer .btn {
        border-radius: 0;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-size: 0.78rem;
    }
    .sx-teacher-ca .filter-sticky {
        position: sticky;
        top: 0;
        z-index: 20;
        background: rgba(255,255,255,0.98);
        padding-top: 0.35rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid rgba(148, 0, 0, 0.08);
        margin-bottom: 1rem;
    }
    .sx-teacher-ca .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #666;
    }
    #mobileSaveFab {
        position: fixed;
        bottom: 28px;
        right: 28px;
        z-index: 9999;
        display: none;
    }
    #mobileSaveFab .btn {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #940000;
        border: none;
        box-shadow: 0 8px 20px rgba(148, 0, 0, 0.28);
    }

    @media (max-width: 767.98px) {
        .sx-teacher-ca .ca-table thead { display: none; }
        .sx-teacher-ca .ca-table,
        .sx-teacher-ca .ca-table tbody,
        .sx-teacher-ca .ca-table tr.student-row,
        .sx-teacher-ca .ca-table tr.student-row td {
            display: block;
            width: 100%;
        }
        .sx-teacher-ca .ca-table tbody tr.student-row {
            border-bottom: 1px solid rgba(148, 0, 0, 0.1);
            padding: 0.35rem 0;
        }
        .sx-teacher-ca .ca-table tbody tr.student-row td {
            border: none;
            padding: 0.35rem 1rem;
        }
        .sx-teacher-ca .ca-actions {
            justify-content: stretch;
        }
        .sx-teacher-ca .ca-actions .btn {
            flex: 1;
        }
        .sx-teacher-ca .assessment-form {
            grid-template-columns: 1fr;
        }
    }

    html[data-sx-theme="dark"] .sx-teacher-ca .card,
    html[data-bs-theme="dark"] .sx-teacher-ca .card,
    html[data-sx-theme="dark"] .sx-teacher-ca .students-panel,
    html[data-bs-theme="dark"] .sx-teacher-ca .students-panel,
    html[data-sx-theme="dark"] .sx-teacher-ca .filter-sticky,
    html[data-bs-theme="dark"] .sx-teacher-ca .filter-sticky,
    html[data-sx-theme="dark"] .sx-teacher-ca .assessment-dropdown,
    html[data-bs-theme="dark"] .sx-teacher-ca .assessment-dropdown,
    html[data-sx-theme="dark"] .sx-teacher-ca .ca-table tbody td,
    html[data-bs-theme="dark"] .sx-teacher-ca .ca-table tbody td {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #historyModal .modal-content,
    html[data-bs-theme="dark"] #historyModal .modal-content,
    html[data-sx-theme="dark"] #historyModal .modal-body,
    html[data-bs-theme="dark"] #historyModal .modal-body,
    html[data-sx-theme="dark"] #historyModal .modal-footer,
    html[data-bs-theme="dark"] #historyModal .modal-footer,
    html[data-sx-theme="dark"] #historyModal .hr-profile,
    html[data-bs-theme="dark"] #historyModal .hr-profile,
    html[data-sx-theme="dark"] #historyModal .history-item,
    html[data-bs-theme="dark"] #historyModal .history-item,
    html[data-sx-theme="dark"] #historyModal .hr-empty,
    html[data-bs-theme="dark"] #historyModal .hr-empty {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #historyModal .hr-name,
    html[data-bs-theme="dark"] #historyModal .hr-name,
    html[data-sx-theme="dark"] #historyModal .history-item .hi-result,
    html[data-bs-theme="dark"] #historyModal .history-item .hi-result,
    html[data-sx-theme="dark"] #historyModal .history-item .hi-text,
    html[data-bs-theme="dark"] #historyModal .history-item .hi-text,
    html[data-sx-theme="dark"] #historyModal .hr-empty h6,
    html[data-bs-theme="dark"] #historyModal .hr-empty h6 {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #historyModal .history-item .hi-result,
    html[data-bs-theme="dark"] #historyModal .history-item .hi-result {
        background: rgba(148, 0, 0, 0.18) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-ca .students-panel,
    html[data-bs-theme="dark"] .sx-teacher-ca .students-panel {
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-ca .students-panel-head,
    html[data-bs-theme="dark"] .sx-teacher-ca .students-panel-head,
    html[data-sx-theme="dark"] .sx-teacher-ca .ca-table thead th,
    html[data-bs-theme="dark"] .sx-teacher-ca .ca-table thead th,
    html[data-sx-theme="dark"] .sx-teacher-ca .ca-table tbody tr.student-row:hover td,
    html[data-bs-theme="dark"] .sx-teacher-ca .ca-table tbody tr.student-row:hover td,
    html[data-sx-theme="dark"] .sx-teacher-ca .ca-table tbody tr.student-row.is-open td,
    html[data-bs-theme="dark"] .sx-teacher-ca .ca-table tbody tr.student-row.is-open td,
    html[data-sx-theme="dark"] .sx-teacher-ca .assessment-row td,
    html[data-bs-theme="dark"] .sx-teacher-ca .assessment-row td {
        background: rgba(148, 0, 0, 0.16) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-ca,
    html[data-bs-theme="dark"] .sx-teacher-ca,
    html[data-sx-theme="dark"] .sx-teacher-ca .text-muted,
    html[data-bs-theme="dark"] .sx-teacher-ca .text-muted,
    html[data-sx-theme="dark"] .sx-teacher-ca label,
    html[data-bs-theme="dark"] .sx-teacher-ca label,
    html[data-sx-theme="dark"] .sx-teacher-ca .student-name,
    html[data-bs-theme="dark"] .sx-teacher-ca .student-name,
    html[data-sx-theme="dark"] .sx-teacher-ca .student-meta,
    html[data-bs-theme="dark"] .sx-teacher-ca .student-meta {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-ca .students-count,
    html[data-bs-theme="dark"] .sx-teacher-ca .students-count {
        background: rgba(148, 0, 0, 0.35) !important;
        border: 1px solid rgba(255, 180, 180, 0.35) !important;
        color: #ffe0e0 !important;
        -webkit-text-fill-color: #ffe0e0 !important;
    }
    html[data-sx-theme="dark"] .app-page-head .badge.bg-primary,
    html[data-bs-theme="dark"] .app-page-head .badge.bg-primary {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    html[data-sx-theme="dark"] .app-page-head .badge.bg-warning,
    html[data-bs-theme="dark"] .app-page-head .badge.bg-warning {
        background: #ffc107 !important;
        color: #212529 !important;
        -webkit-text-fill-color: #212529 !important;
    }
    html[data-sx-theme="dark"] .app-page-head .badge.bg-secondary,
    html[data-bs-theme="dark"] .app-page-head .badge.bg-secondary {
        background: #6c757d !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-ca .btn-outline-light,
    html[data-bs-theme="dark"] .sx-teacher-ca .btn-outline-light,
    html[data-sx-theme="dark"] .sx-teacher-ca .adm-chip,
    html[data-bs-theme="dark"] .sx-teacher-ca .adm-chip {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-ca .student-cat-input,
    html[data-bs-theme="dark"] .sx-teacher-ca .student-cat-input,
    html[data-sx-theme="dark"] .sx-teacher-ca select.student-cat-input,
    html[data-bs-theme="dark"] .sx-teacher-ca select.student-cat-input,
    html[data-sx-theme="dark"] .sx-teacher-ca textarea.student-cat-input,
    html[data-bs-theme="dark"] .sx-teacher-ca textarea.student-cat-input {
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.25) !important;
        color: #f3e9eb !important;
        -webkit-text-fill-color: #f3e9eb !important;
    }
</style>

<div class="sx-teacher-ca">
    @if($assignments->isEmpty())
        <div class="alert alert-primary mb-3" role="alert">
            <strong><i class="fi fi-rr-info me-1"></i> {{ __('teacher.char.empty_title') }}</strong>
            {{ __('teacher.char.empty_body') }}
        </div>
    @endif

    <div class="filter-sticky {{ $assignments->isEmpty() ? 'opacity-50 pe-none' : '' }}">
        <div class="card panel-card mb-0">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-uppercase mb-1" style="letter-spacing:.04em;">{{ __('teacher.char.main_class') }}</label>
                        <select id="mainClassSelector" class="form-select fw-semibold" style="color:#940000;">
                            @php $uniqueClasses = $assignments->unique('classID'); @endphp
                            @forelse($uniqueClasses as $asgn)
                                <option value="{{ $asgn->classID }}">{{ optional($asgn->class)->class_name ?? ('Class #'.$asgn->classID) }}</option>
                            @empty
                                <option value="">{{ __('teacher.char.no_assigned_class') }}</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-uppercase mb-1" style="letter-spacing:.04em;">{{ __('teacher.char.subclass_group') }}</label>
                        <select id="subclassSelector" class="form-select fw-semibold" style="color:#940000;">
                            {{-- Populated via JS --}}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-uppercase mb-1" style="letter-spacing:.04em;">{{ __('teacher.char.find_student') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fi fi-rr-search text-muted"></i></span>
                            <input type="text" id="studentSearch" class="form-control" placeholder="{{ __('teacher.char.search_placeholder') }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-primary waves-effect waves-light w-100" id="saveAllBtn" style="display:none;">
                            <i class="fi fi-rr-disk me-1"></i> {{ __('teacher.save') }}
                        </button>
                        <div id="filterLoading" class="ms-1" style="display:none;">
                            <span class="spinner-border spinner-border-sm text-danger" role="status"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="mobileSaveFab">
        <button type="button" class="btn text-white" onclick="saveAssessments()" title="{{ __('teacher.char.save_all_title') }}">
            <i class="fi fi-rr-disk" style="font-size:1.25rem;"></i>
        </button>
    </div>

    <div class="students-panel {{ $assignments->isEmpty() ? 'opacity-50' : '' }}">
        <div class="students-panel-head">
            <h6><i class="fi fi-rr-users-alt me-1"></i> {{ __('teacher.char.student_roster') }}</h6>
            <span class="students-count" id="studentsCountLabel">{{ __('teacher.char.students_count', ['count' => 0]) }}</span>
        </div>
        <div id="studentContainer">
            {{-- Students load here --}}
        </div>
    </div>
</div>

{{-- History Modal (BS5) --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="historyTitle"><i class="fi fi-rr-document me-1"></i> {{ __('teacher.char.student_record') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="hr-profile">
                    <img id="historyStudentImg" src="" alt="" class="hr-avatar">
                    <div class="min-w-0">
                        <h5 id="historyStudentName" class="hr-name text-truncate"></h5>
                        <span id="historyStudentAdm" class="hr-adm"></span>
                        <p class="hr-subtitle">{{ __('teacher.char.history_subtitle') }}</p>
                    </div>
                </div>
                <div class="hr-body" id="historyContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light btn-shadow w-100" data-bs-dismiss="modal">{{ __('teacher.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStudents = [];
    let pendingChanges = {};
    const GLOBAL_CHOICES = @json($globalChoices);
    const GLOBAL_CATEGORIES = @json($categories);
    const ALL_ASSIGNMENTS = @json($assignments);
    const ALL_SUBCLASSES = @json($subclasses);
    var i18n = @json(__('teacher.char'));
    i18n.save = @json(__('teacher.save'));
    i18n.close = @json(__('teacher.close'));
    i18n.student = @json(__('teacher.student'));
    i18n.status = @json(__('teacher.status'));
    i18n.actions = @json(__('teacher.actions'));

    function studentsLabel(count) {
        var key = count === 1 ? i18n.student_count : i18n.students_count;
        return String(key).replace(':count', count);
    }

    function showHistoryModal() {
        var el = document.getElementById('historyModal');
        if (window.bootstrap && bootstrap.Modal && el) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (window.jQuery && jQuery.fn.modal) {
            jQuery('#historyModal').modal('show');
        }
    }

    $(document).ready(function () {
        if (!ALL_ASSIGNMENTS || !ALL_ASSIGNMENTS.length) {
            $('#studentsCountLabel').text(studentsLabel(0));
            $('#studentContainer').html('<div class="empty-state"><p class="fw-bold mb-1">' + i18n.no_assigned_classes + '</p></div>');
            return;
        }

        updateSubclassOptions();
        loadStudents();

        $('#mainClassSelector').on('change', function () {
            updateSubclassOptions();
            loadStudents();
            pendingChanges = {};
            $('#saveAllBtn').fadeOut();
            $('#mobileSaveFab').fadeOut();
        });

        $('#subclassSelector').on('change', function () {
            loadStudents();
            pendingChanges = {};
            $('#saveAllBtn').fadeOut();
            $('#mobileSaveFab').fadeOut();
        });

        $('#studentSearch').on('keyup', function () {
            const query = ($(this).val() || '').toLowerCase().trim();
            let visible = 0;
            $('tr.student-row.student-item-col').each(function () {
                const name = String($(this).data('name') || '').toLowerCase();
                const adm = String($(this).data('adm') || '').toLowerCase();
                const match = !query || name.includes(query) || adm.includes(query);
                const sid = $(this).data('student-id');
                $(this).toggle(match);
                $(this).next('tr.assessment-row').toggle(match);
                if (match) {
                    visible += 1;
                } else {
                    $(`#assess_div_${sid}`).hide();
                    $(this).removeClass('is-open');
                }
            });
            $('#studentsCountLabel').text(studentsLabel(visible));
        });

        $('#saveAllBtn').on('click', function () {
            saveAssessments();
        });
    });

    function updateSubclassOptions() {
        const classID = $('#mainClassSelector').val();
        if (!classID) {
            $('#subclassSelector').html('<option value="">' + i18n.no_groups + '</option>');
            return;
        }
        const assignmentsForClass = ALL_ASSIGNMENTS.filter(a => a.classID == classID);

        if (assignmentsForClass.length === 0) {
            $('#subclassSelector').html('<option value="">' + i18n.no_groups + '</option>');
            return;
        }

        const baseAssignment = assignmentsForClass[0];
        const hasAllAssignment = assignmentsForClass.find(a => a.subclassID === null);

        let html = `<option value="all"
                            data-timetable-id="${baseAssignment.timetableID}"
                            data-categories='${JSON.stringify(baseAssignment.selected_categories || [])}'>
                        ${i18n.all_groups}
                    </option>`;

        if (hasAllAssignment) {
            const relatedSubclasses = ALL_SUBCLASSES.filter(s => s.classID == classID);
            relatedSubclasses.forEach(s => {
                html += `<option value="${s.subclassID}" data-timetable-id="${hasAllAssignment.timetableID}" data-categories='${JSON.stringify(hasAllAssignment.selected_categories || [])}'>${s.subclass_name}</option>`;
            });
        } else {
            assignmentsForClass.forEach(a => {
                const subName = a.subclass ? a.subclass.subclass_name : 'Group-X';
                html += `<option value="${a.subclassID}"
                                 data-timetable-id="${a.timetableID}"
                                 data-categories='${JSON.stringify(a.selected_categories || [])}'>
                            ${subName}
                         </option>`;
            });
        }
        $('#subclassSelector').html(html);
    }

    function loadStudents() {
        const classID = $('#mainClassSelector').val();
        const subOption = $('#subclassSelector option:selected');
        const subclassID = subOption.val();
        const timetableID = subOption.attr('data-timetable-id');

        if (!classID) return;

        $('#filterLoading').show();
        $('#studentContainer').css('opacity', '0.5');

        $.ajax({
            url: "{{ route('teacher.get_assessment_students') }}",
            type: 'GET',
            data: { subclassID: subclassID, classID: classID, timetableID: timetableID },
            success: function (response) {
                $('#filterLoading').hide();
                $('#studentContainer').css('opacity', '1');
                if (response.success) {
                    currentStudents = response.students;
                    renderStudents(response.students);
                }
            },
            error: function (xhr) {
                $('#filterLoading').hide();
                $('#studentContainer').css('opacity', '1');
                currentStudents = [];
                renderStudents([]);
                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : i18n.fail_load_students;
                Swal.fire(xhr.status === 422 ? i18n.no_active_enrollment : i18n.error, msg, xhr.status === 422 ? 'info' : 'error');
            }
        });
    }

    function renderStudents(students) {
        $('#studentsCountLabel').text(studentsLabel(students.length));

        if (students.length === 0) {
            $('#studentContainer').html(`
                <div class="empty-state">
                    <p class="fw-bold mb-1">${i18n.no_students_found}</p>
                    <p class="small mb-0 text-muted">${i18n.roster_hint}</p>
                </div>
            `);
            return;
        }

        let rows = '';
        students.forEach((student, index) => {
            const fullName = `${student.first_name} ${student.middle_name ? student.middle_name + ' ' : ''}${student.last_name}`;
            const photo = student.photo ? `{{ asset('userImages/') }}/${student.photo}` : (student.gender === 'Female' ? `{{ asset('images/female.png') }}` : `{{ asset('images/male.png') }}`);
            const safeName = fullName.replace(/'/g, "\\'");

            const assessments = student.character_assessments || student.characterAssessments || [];
            const hasAssessments = assessments.length > 0;
            const canAssess = student.can_assess !== false && (student.enrollment_status || 'Active') === 'Active';
            const enrStatus = student.enrollment_status || 'Active';
            const btnLabel = hasAssessments ? i18n.edit : i18n.assess;
            const assessBtnAttrs = canAssess
                ? `onclick="toggleAssessDiv(${student.studentID})"`
                : `disabled title="${i18n.not_editable_title}"`;

            let statusPill = '';
            if (!canAssess) {
                statusPill = `<span class="status-pill is-locked">${enrStatus}</span>`;
            } else if (hasAssessments) {
                statusPill = `<span class="status-pill is-ready"><i class="fi fi-rr-check"></i> ${i18n.ready}</span>`;
            } else {
                statusPill = `<span class="status-pill is-pending">${i18n.pending}</span>`;
            }

            rows += `
                <tr class="student-item-col student-row ${canAssess ? '' : 'is-locked'}"
                    id="student_row_${student.studentID}"
                    data-student-id="${student.studentID}"
                    data-name="${fullName}"
                    data-adm="${student.admission_number}">
                    <td class="text-muted fw-bold" style="width:48px;">${index + 1}</td>
                    <td>
                        <div class="student-cell">
                            <img src="${photo}" class="student-avatar" alt="">
                            <div class="min-w-0">
                                <p class="student-name text-truncate">${fullName}</p>
                                <div class="student-meta">${student.gender || '—'} · ${canAssess ? i18n.active_enrollment : i18n.assessment_locked}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="adm-chip">${student.admission_number || '—'}</span></td>
                    <td>${statusPill}</td>
                    <td>
                        <div class="ca-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary" ${assessBtnAttrs}>
                                <i class="fi fi-rr-${hasAssessments ? 'edit' : 'pencil'} me-1"></i> ${btnLabel}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light btn-shadow"
                                onclick="viewHistory(${student.studentID}, '${safeName}', '${student.admission_number}', '${photo}')">
                                <i class="fi fi-rr-time-past me-1"></i> ${i18n.history}
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="assessment-row" data-student-id="${student.studentID}">
                    <td colspan="5">
                        <div id="assess_div_${student.studentID}" class="assessment-dropdown">
                            ${canAssess
                                ? renderAssessmentForm(student.studentID)
                                : `<p class="text-muted mb-0">${i18n.cannot_assess}</p>`}
                            ${canAssess ? `
                                <div class="text-end mt-3 pt-3 border-top">
                                    <button type="button" class="btn btn-primary fw-bold px-4" onclick="saveSingleStudent(${student.studentID})">
                                        <i class="fi fi-rr-disk me-1"></i> ${i18n.save_this_student}
                                    </button>
                                </div>` : ''}
                        </div>
                    </td>
                </tr>
            `;
        });

        $('#studentContainer').html(`
            <div class="table-responsive">
                <table class="table ca-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>${i18n.student}</th>
                            <th>${i18n.admission}</th>
                            <th>${i18n.status}</th>
                            <th class="text-end">${i18n.actions}</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `);
    }

    function renderAssessmentForm(studentID) {
        const student = currentStudents.find(s => s.studentID == studentID);
        const existing = student.character_assessments || student.characterAssessments || [];

        const selectedOption = $('#subclassSelector option:selected');
        const selectedCategoriesStr = selectedOption.data('categories');
        let selectedCategories = [];
        try {
            selectedCategories = typeof selectedCategoriesStr === 'string' ? JSON.parse(selectedCategoriesStr) : selectedCategoriesStr;
        } catch (e) { selectedCategories = []; }

        if (!selectedCategories || selectedCategories.length === 0) {
            return `<p class="small text-danger fw-bold mb-0">${i18n.no_categories}</p>`;
        }

        selectedCategories.sort((a, b) => (a.number || 0) - (b.number || 0));

        let html = '<div class="assessment-form">';
        selectedCategories.forEach(config => {
            const targetID = typeof config === 'object' ? config.categoryID : config;
            const seqNum = typeof config === 'object' ? config.number : '';
            const cat = GLOBAL_CATEGORIES.find(c => c.categoryID == targetID);
            if (!cat) return;

            const record = existing.find(r => r.categoryID == targetID);
            const oldChoice = record ? record.choiceID : '';
            const oldText = record ? record.description : '';

            html += `<div class="mb-3">
                        <label class="cat-label d-block mb-2">
                           ${seqNum ? `<span class="badge bg-primary rounded-circle me-2" style="width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:10px;">${seqNum}</span>` : ''}
                           ${cat.name}
                        </label>`;

            if (cat.type === 'choice') {
                html += `<select class="form-select student-cat-input"
                                 data-student="${studentID}" data-cat="${cat.categoryID}" data-type="choice"
                                 onchange="trackChange(${studentID}, ${cat.categoryID}, 'choiceID', this.value)">
                            <option value="">${i18n.rate_student}</option>
                            ${GLOBAL_CHOICES.map(c => `<option value="${c.choiceID}" ${c.choiceID == oldChoice ? 'selected' : ''}>${c.choice_name} (${c.weight})</option>`).join('')}
                         </select>`;
            } else {
                html += `<textarea class="form-control student-cat-input"
                                   data-student="${studentID}" data-cat="${cat.categoryID}" data-type="text"
                                   placeholder="${i18n.type_description}"
                                   onkeyup="trackChange(${studentID}, ${cat.categoryID}, 'description', this.value)">${oldText || ''}</textarea>`;
            }
            html += `</div>`;
        });
        html += '</div>';
        return html;
    }

    function toggleAssessDiv(studentID) {
        const $panel = $(`#assess_div_${studentID}`);
        const $row = $(`#student_row_${studentID}`);
        const opening = !$panel.is(':visible');

        $('.assessment-dropdown:visible').not($panel).slideUp(200);
        $('.student-row.is-open').not($row).removeClass('is-open');

        $panel.slideToggle(280);
        $row.toggleClass('is-open', opening);
    }

    function trackChange(studentID, categoryID, field, value) {
        if (!pendingChanges[studentID]) pendingChanges[studentID] = {};
        if (!pendingChanges[studentID][categoryID]) pendingChanges[studentID][categoryID] = {};
        pendingChanges[studentID][categoryID][field] = value;
        $('#saveAllBtn').fadeIn();
        if ($(window).width() <= 768) {
            $('#mobileSaveFab').fadeIn();
        }
    }

    function saveSingleStudent(studentID) {
        saveAssessments(studentID);
    }

    function saveAssessments(targetStudentID = null) {
        const selectedOption = $('#subclassSelector option:selected');
        const timetableID = selectedOption.data('timetable-id');
        const assessmentData = [];

        if (targetStudentID) {
            const targetStudent = currentStudents.find(s => s.studentID == targetStudentID);
            if (targetStudent && targetStudent.can_assess === false) {
                Swal.fire(i18n.locked, i18n.only_active, 'info');
                return;
            }
            if (!pendingChanges[targetStudentID]) {
                Swal.fire(i18n.no_changes, i18n.make_ratings, 'info');
                return;
            }
            const categories = [];
            for (const categoryID in pendingChanges[targetStudentID]) {
                categories.push({ categoryID: categoryID, ...pendingChanges[targetStudentID][categoryID] });
            }
            assessmentData.push({ studentID: targetStudentID, timetableID: timetableID, categories: categories });
        } else {
            if (Object.keys(pendingChanges).length === 0) return;
            for (const studentID in pendingChanges) {
                const categories = [];
                for (const categoryID in pendingChanges[studentID]) {
                    categories.push({ categoryID: categoryID, ...pendingChanges[studentID][categoryID] });
                }
                assessmentData.push({ studentID: studentID, timetableID: timetableID, categories: categories });
            }
        }

        const saveBtn = targetStudentID ? $(`#assess_div_${targetStudentID} button`) : $('#saveAllBtn');
        const originalHtml = saveBtn.html();

        saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + i18n.saving);
        $('#mobileSaveFab button').prop('disabled', true).html('<span class="spinner-border spinner-border-sm text-white"></span>');

        $.ajax({
            url: "{{ route('teacher.store_student_assessment') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}", assessments: assessmentData },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success', title: i18n.recorded, text: i18n.saved_success,
                        confirmButtonColor: '#940000', timer: 1500
                    });

                    if (targetStudentID) {
                        delete pendingChanges[targetStudentID];
                    } else {
                        pendingChanges = {};
                    }

                    if (Object.keys(pendingChanges).length === 0) {
                        $('#saveAllBtn').hide();
                        $('#mobileSaveFab').hide();
                    }

                    saveBtn.prop('disabled', false).html(originalHtml);
                    $('#mobileSaveFab button').prop('disabled', false).html('<i class="fi fi-rr-disk" style="font-size:1.25rem;"></i>');

                    loadStudents();

                    if (!targetStudentID && $(window).width() <= 768) {
                        $('html, body').animate({ scrollTop: 0 }, 500);
                    }
                }
            },
            error: function (xhr) {
                saveBtn.prop('disabled', false).html(originalHtml);
                $('#mobileSaveFab button').prop('disabled', false).html('<i class="fi fi-rr-disk" style="font-size:1.25rem;"></i>');
                const msg = xhr.responseJSON ? xhr.responseJSON.message : i18n.fail_save;
                Swal.fire(i18n.error, msg, 'error');
            }
        });
    }

    function viewHistory(studentID, name, adm, photo) {
        const selectedOption = $('#subclassSelector option:selected');
        const timetableID = selectedOption.data('timetable-id');

        $('#historyStudentImg').attr('src', photo);
        $('#historyStudentName').text(name);
        $('#historyStudentAdm').text(adm ? `ADM · ${adm}` : 'ADM · —');
        $('#historyContent').html('<div class="text-center py-5"><span class="spinner-border text-danger"></span></div>');
        showHistoryModal();

        $.ajax({
            url: "{{ route('teacher.get_student_assessment_history') }}",
            type: 'GET',
            data: { studentID: studentID, timetableID: timetableID },
            success: function (response) {
                if (response.success) {
                    let html = '';
                    if (!response.history || response.history.length === 0) {
                        html = `
                            <div class="hr-empty">
                                <i class="fi fi-rr-folder-open"></i>
                                <h6>${i18n.no_assessment_yet}</h6>
                                <p>${i18n.use_assess_hint}</p>
                            </div>
                        `;
                    } else {
                        const cards = response.history.map((item, idx) => {
                            const catName = (item.category && item.category.name) ? item.category.name : i18n.category;
                            const choiceName = item.choice ? item.choice.choice_name : '';
                            const weight = item.choice && item.choice.weight != null ? item.choice.weight : '';
                            let body = '';
                            if (choiceName) {
                                body += `<div class="hi-result">${choiceName}${weight !== '' ? `<span class="hi-weight">${weight}</span>` : ''}</div>`;
                            }
                            if (item.description) {
                                body += `<p class="hi-text">${item.description}</p>`;
                            }
                            if (!body) {
                                body = `<div class="hi-result">${i18n.not_rated}</div>`;
                            }
                            return `
                                <div class="history-item">
                                    <div class="hi-cat">
                                        <span class="hi-num">${idx + 1}</span>
                                        <h6 class="hi-title">${catName}</h6>
                                    </div>
                                    ${body}
                                </div>
                            `;
                        }).join('');

                        html = `
                            <div class="hr-section-label">
                                <span>${i18n.assessment_sectors}</span>
                                <span class="hr-count">${String(i18n.recorded_count).replace(':count', response.history.length)}</span>
                            </div>
                            <div class="hr-grid">${cards}</div>
                        `;
                    }
                    $('#historyContent').html(html);
                }
            },
            error: function () {
                $('#historyContent').html(`<div class="hr-empty"><h6 class="text-danger">${i18n.fail_load_history}</h6><p>${i18n.please_try_again}</p></div>`);
            }
        });
    }
</script>
@endpush
