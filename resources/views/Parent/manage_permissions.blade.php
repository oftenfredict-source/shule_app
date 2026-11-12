@extends('layouts.parent_corp')

@section('title', __('parent.perm_title') . ' - ShuleXpert')
@section('page_title', __('parent.perm_title'))
@section('page_breadcrumb', __('parent.nav_permissions'))

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm span:not(.badge):not(.parent-unread-badge):not(.parent-unread-count):not(.status-badge):not(.days-chip),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-perm .text-muted {
        color: #000 !important;
    }
    .sx-parent-perm .btn-primary,
    .sx-parent-perm a.btn-primary,
    .sx-parent-perm .badge,
    .sx-parent-perm .parent-unread-badge,
    .sx-parent-perm .parent-unread-badge * {
        color: #fff !important;
    }
    .sx-parent-perm .card,
    .sx-parent-perm .panel-card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-perm .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-perm .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-parent-perm .hint-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        padding: 0.85rem 1rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-perm .section-label {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #940000 !important;
        margin-bottom: 0.85rem;
    }
    .sx-parent-perm .form-label {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #777 !important;
        margin-bottom: 0.4rem;
    }
    .sx-parent-perm .form-control,
    .sx-parent-perm .form-select {
        border: 1px solid #e6e6e6;
        border-radius: 0.25rem;
        padding: 0.55rem 0.75rem;
        font-size: 0.9rem;
        background: #fff;
    }
    .sx-parent-perm .form-control:focus,
    .sx-parent-perm .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 3px rgba(148, 0, 0, 0.08);
    }

    /* Tabs */
    .sx-parent-perm .permission-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0;
        border-bottom: 1px solid #eee;
        background: #fff;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .sx-parent-perm .permission-tabs .nav-link {
        border: 0 !important;
        border-bottom: 3px solid transparent !important;
        border-radius: 0 !important;
        font-weight: 800;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #777 !important;
        padding: 0.95rem 1.1rem;
        background: transparent;
        cursor: pointer;
    }
    .sx-parent-perm .permission-tabs .nav-link.active {
        background: #fff8f8 !important;
        color: #940000 !important;
        border-bottom-color: #940000 !important;
    }
    .sx-parent-perm .parent-unread-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        margin-left: 6px;
        background: #940000;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.15rem 0.4rem;
        line-height: 1;
        vertical-align: middle;
    }
    .sx-parent-perm .perm-pane { display: none; }
    .sx-parent-perm .perm-pane.is-active { display: block; }
    .sx-parent-perm .tab-content-area {
        background: #fff;
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-perm .form-heading {
        font-weight: 800;
        color: #940000 !important;
        border-left: 4px solid #940000;
        padding-left: 0.85rem;
        margin-bottom: 1.15rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sx-parent-perm #parentDaysDisplay {
        background: #fff8f8 !important;
        font-weight: 800;
        color: #940000 !important;
    }
    .sx-parent-perm .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(148, 0, 0, 0.2);
        border-left: 4px solid #940000;
        background: #fff8f8;
        margin-bottom: 1rem;
    }
    .sx-parent-perm .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        overflow: hidden;
    }
    .sx-parent-perm .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: sxPermProgress 1.1s linear infinite;
    }
    @keyframes sxPermProgress {
        0% { left: -40%; }
        100% { left: 100%; }
    }
    .sx-parent-perm .filter-panel {
        border: 1px solid #e6e6e6;
        background: #fffaf9;
        padding: 1rem 1.15rem;
        margin-bottom: 1rem;
    }

    /* Table / cards from partial */
    .sx-parent-perm .perm-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        background: #fff;
    }
    .sx-parent-perm .perm-table thead th {
        background: #fcf7f7;
        color: #940000 !important;
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 0.05em;
        font-weight: 800;
        border-bottom: 2px solid rgba(148, 0, 0, 0.18);
        border-right: 1px solid #f0e8e8;
        white-space: nowrap;
        padding: 0.85rem 0.9rem;
        vertical-align: middle;
    }
    .sx-parent-perm .perm-table thead th:last-child { border-right: none; }
    .sx-parent-perm .perm-table tbody td {
        vertical-align: middle;
        border-bottom: 1px solid #f0ecec;
        border-right: 1px solid #f6f1f1;
        padding: 0.9rem;
        font-size: 0.88rem;
        background: #fff;
    }
    .sx-parent-perm .perm-table tbody td:last-child { border-right: none; }
    .sx-parent-perm .perm-table tbody tr:nth-child(even) td { background: #fffcfc; }
    .sx-parent-perm .perm-table tbody tr:hover td { background: #fff5f5 !important; }
    .sx-parent-perm .perm-table tbody tr:last-child td { border-bottom: none; }
    .sx-parent-perm .child-name {
        font-weight: 800;
        color: #1a1a1a !important;
    }
    .sx-parent-perm .days-chip {
        display: inline-block;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.25rem 0.45rem;
        background: #fff5f5;
        border: 1px solid #f0d0d0;
        color: #940000 !important;
        margin-top: 0.25rem;
    }
    .sx-parent-perm .status-badge {
        font-size: 0.65rem;
        padding: 0.3rem 0.55rem;
        text-transform: uppercase;
        font-weight: 800;
        border-radius: 0 !important;
        letter-spacing: 0.03em;
        display: inline-block;
        border: 1px solid transparent;
    }
    .sx-parent-perm .status-badge.approved {
        background: #d1e7dd;
        color: #0f5132 !important;
        border-color: #a3cfbb;
    }
    .sx-parent-perm .status-badge.rejected {
        background: #f8d7da;
        color: #842029 !important;
        border-color: #f1aeb5;
    }
    .sx-parent-perm .status-badge.pending {
        background: #fff3cd;
        color: #664d03 !important;
        border-color: #ffe69c;
    }
    .sx-parent-perm .perm-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 3px solid #940000;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        padding: 1rem 1.05rem;
        height: 100%;
    }
    .sx-parent-perm .perm-card.is-approved { border-top-color: #28a745; }
    .sx-parent-perm .perm-card.is-rejected { border-top-color: #dc3545; }
    .sx-parent-perm .perm-card.is-pending { border-top-color: #ffc107; }
    .sx-parent-perm .perm-view-btn.active {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-parent-perm .reject-note {
        margin-top: 0.65rem;
        padding: 0.55rem 0.65rem;
        background: #fff5f5;
        border-left: 3px solid #dc3545;
        font-size: 0.75rem;
        color: #842029 !important;
    }
    .sx-parent-perm .empty-state {
        text-align: center;
        padding: 2.25rem 1rem;
        border: 1px solid #e6e6e6;
        background: #fff;
        color: #888 !important;
    }

    @media (max-width: 991px) {
        .sx-parent-perm .perm-list-view { display: none !important; }
        .sx-parent-perm .perm-grid-view { display: block !important; }
        .sx-parent-perm .permission-tabs {
            flex-wrap: nowrap !important;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .sx-parent-perm .permission-tabs .nav-item { flex: 0 0 auto; }
        .sx-parent-perm .permission-tabs .nav-link { white-space: nowrap; }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-parent-perm .card,
    html[data-bs-theme="dark"] .sx-parent-perm .card,
    html[data-sx-theme="dark"] .sx-parent-perm .panel-card,
    html[data-bs-theme="dark"] .sx-parent-perm .panel-card,
    html[data-sx-theme="dark"] .sx-parent-perm .hint-strip,
    html[data-bs-theme="dark"] .sx-parent-perm .hint-strip,
    html[data-sx-theme="dark"] .sx-parent-perm .tab-content-area,
    html[data-bs-theme="dark"] .sx-parent-perm .tab-content-area,
    html[data-sx-theme="dark"] .sx-parent-perm .filter-panel,
    html[data-bs-theme="dark"] .sx-parent-perm .filter-panel,
    html[data-sx-theme="dark"] .sx-parent-perm .perm-card,
    html[data-bs-theme="dark"] .sx-parent-perm .perm-card,
    html[data-sx-theme="dark"] .sx-parent-perm .empty-state,
    html[data-bs-theme="dark"] .sx-parent-perm .empty-state,
    html[data-sx-theme="dark"] .sx-parent-perm .perm-table,
    html[data-bs-theme="dark"] .sx-parent-perm .perm-table {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-perm,
    html[data-bs-theme="dark"] .sx-parent-perm,
    html[data-sx-theme="dark"] .sx-parent-perm p,
    html[data-bs-theme="dark"] .sx-parent-perm p,
    html[data-sx-theme="dark"] .sx-parent-perm span:not(.badge):not(.parent-unread-badge):not(.parent-unread-count):not(.status-badge):not(.days-chip),
    html[data-bs-theme="dark"] .sx-parent-perm span:not(.badge):not(.parent-unread-badge):not(.parent-unread-count):not(.status-badge):not(.days-chip),
    html[data-sx-theme="dark"] .sx-parent-perm small,
    html[data-bs-theme="dark"] .sx-parent-perm small,
    html[data-sx-theme="dark"] .sx-parent-perm strong,
    html[data-bs-theme="dark"] .sx-parent-perm strong,
    html[data-sx-theme="dark"] .sx-parent-perm div,
    html[data-bs-theme="dark"] .sx-parent-perm div,
    html[data-sx-theme="dark"] .sx-parent-perm label,
    html[data-bs-theme="dark"] .sx-parent-perm label,
    html[data-sx-theme="dark"] .sx-parent-perm .text-muted,
    html[data-bs-theme="dark"] .sx-parent-perm .text-muted,
    html[data-sx-theme="dark"] .sx-parent-perm .child-name,
    html[data-bs-theme="dark"] .sx-parent-perm .child-name,
    html[data-sx-theme="dark"] .sx-parent-perm .permission-tabs .nav-link,
    html[data-bs-theme="dark"] .sx-parent-perm .permission-tabs .nav-link {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-perm .section-label,
    html[data-bs-theme="dark"] .sx-parent-perm .section-label,
    html[data-sx-theme="dark"] .sx-parent-perm .form-heading,
    html[data-bs-theme="dark"] .sx-parent-perm .form-heading,
    html[data-sx-theme="dark"] .sx-parent-perm .permission-tabs .nav-link.active,
    html[data-bs-theme="dark"] .sx-parent-perm .permission-tabs .nav-link.active,
    html[data-sx-theme="dark"] .sx-parent-perm .perm-table thead th,
    html[data-bs-theme="dark"] .sx-parent-perm .perm-table thead th,
    html[data-sx-theme="dark"] .sx-parent-perm .days-chip,
    html[data-bs-theme="dark"] .sx-parent-perm .days-chip {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-perm .permission-tabs .nav-link.active,
    html[data-bs-theme="dark"] .sx-parent-perm .permission-tabs .nav-link.active {
        background: rgba(148, 0, 0, 0.18) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-perm .perm-table thead th,
    html[data-bs-theme="dark"] .sx-parent-perm .perm-table thead th {
        background: rgba(148, 0, 0, 0.22) !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-perm .perm-table tbody td,
    html[data-bs-theme="dark"] .sx-parent-perm .perm-table tbody td {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-perm .perm-table tbody tr:nth-child(even) td,
    html[data-bs-theme="dark"] .sx-parent-perm .perm-table tbody tr:nth-child(even) td {
        background: #2a1c21 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-perm .form-control,
    html[data-bs-theme="dark"] .sx-parent-perm .form-control,
    html[data-sx-theme="dark"] .sx-parent-perm .form-select,
    html[data-bs-theme="dark"] .sx-parent-perm .form-select,
    html[data-sx-theme="dark"] .sx-parent-perm #parentDaysDisplay,
    html[data-bs-theme="dark"] .sx-parent-perm #parentDaysDisplay {
        background: #1c1216 !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-perm .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-perm .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
</style>

<div class="sx-parent-perm">
    <div class="row g-3">
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div class="pe-2">
                            <h5 class="card-title mb-1">
                                <i class="fi fi-rr-shield-check me-1" style="color:#940000;"></i>
                                {{ __('parent.perm_title') }}
                            </h5>
                            <p class="text-muted small mb-0">{{ __('parent.request_permission') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="hint-strip">
                <i class="fi fi-rr-info me-1" style="color:#940000;"></i>
                {{ __('parent.request_permission') }}
            </div>
        </div>

        <div class="col-12">
            <div class="card panel-card overflow-hidden">
                <ul class="nav permission-tabs">
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-perm-tab {{ $activeTab === 'request' ? 'active' : '' }}" data-tab="request">{{ __('parent.request_permission') }}</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-perm-tab {{ $activeTab === 'pending' ? 'active' : '' }}" data-tab="pending">{{ __('parent.pending_tab') }}
                            <span class="parent-unread-badge" data-unread-for="pending" @if($unreadPendingCount < 1) style="display:none;" @endif><i class="fi fi-rr-bell"></i> <span class="parent-unread-count">{{ $unreadPendingCount }}</span></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-perm-tab {{ $activeTab === 'approved' ? 'active' : '' }}" data-tab="approved">{{ __('parent.approved_tab') }}
                            <span class="parent-unread-badge" data-unread-for="approved" @if($unreadApprovedCount < 1) style="display:none;" @endif><i class="fi fi-rr-bell"></i> <span class="parent-unread-count">{{ $unreadApprovedCount }}</span></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-perm-tab {{ $activeTab === 'rejected' ? 'active' : '' }}" data-tab="rejected">{{ __('parent.rejected_tab') }}
                            <span class="parent-unread-badge" data-unread-for="rejected" @if($unreadRejectedCount < 1) style="display:none;" @endif><i class="fi fi-rr-bell"></i> <span class="parent-unread-count">{{ $unreadRejectedCount }}</span></span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content-area">
                    <div class="perm-pane {{ $activeTab === 'request' ? 'is-active' : '' }}" data-pane="request">
                        @isset($editPermission)
                        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius:0; border-left: 4px solid #ff9800;">
                            <i class="fi fi-rr-edit" style="color:#ff9800;"></i>
                            <div class="flex-grow-1">{{ __('parent.editing_rejected') }}</div>
                            <a href="{{ route('parent.permissions') }}" class="btn btn-sm btn-outline-light">
                                <i class="fi fi-rr-cross me-1"></i> {{ __('parent.cancel') }}
                            </a>
                        </div>
                        @endisset
                        @php
                            $editStudentId = isset($editPermission) ? ($editPermission->student_id ?? $editPermission->studentID ?? '') : '';
                            $editReasonRaw = strtolower((string) data_get($editPermission, 'reason_type', ''));
                            $editReason = in_array($editReasonRaw, ['sick', 'medical'], true) ? 'sick' : 'permission';
                        @endphp
                        <div class="form-heading">{{ isset($editPermission) ? __('parent.edit_resubmit') : __('parent.new_permission') }}</div>
                        <form method="POST" action="{{ route('parent.permissions.store') }}" enctype="multipart/form-data" id="parentPermissionForm">
                            @csrf
                            @isset($editPermission)
                            <input type="hidden" name="permission_id" value="{{ $editPermission->permissionID ?? $editPermission->id }}">
                            @endisset
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">{{ __('parent.student') }}</label>
                                    <select class="form-select" name="student_id" required>
                                        <option value="">{{ __('parent.choose_child') }}</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->studentID }}" {{ (string)$editStudentId === (string)$student->studentID ? 'selected' : '' }}>{{ $student->first_name }} {{ $student->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('parent.leave_date') }}</label>
                                    <input type="date" class="form-control" name="start_date" id="parentStartDate"
                                        value="{{ isset($editPermission) && $editPermission->start_date ? \Carbon\Carbon::parse($editPermission->start_date)->format('Y-m-d') : '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('parent.return_date') }}</label>
                                    <input type="date" class="form-control" name="end_date" id="parentEndDate"
                                        value="{{ isset($editPermission) && $editPermission->end_date ? \Carbon\Carbon::parse($editPermission->end_date)->format('Y-m-d') : '' }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('parent.total_permission_days') }}</label>
                                    <input type="text" class="form-control" id="parentDaysDisplay" readonly>
                                    <small class="text-danger d-none" id="parentDaysError" style="font-weight:700;">{{ __('parent.days_exceeded') }}</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('parent.permission_type') }}</label>
                                    <select class="form-select" name="reason_type" id="parentReasonType" required>
                                        <option value="">{{ __('parent.choose_type_opt') }}</option>
                                        <option value="permission" {{ isset($editPermission) && $editReason === 'permission' ? 'selected' : '' }}>{{ __('parent.permission') }}</option>
                                        <option value="sick" {{ isset($editPermission) && $editReason === 'sick' ? 'selected' : '' }}>{{ __('parent.sick') }}</option>
                                    </select>
                                </div>
                                <div class="col-12" id="medicalAttachmentField">
                                    <label class="form-label">{{ __('parent.attachment_optional') }}</label>
                                    <input type="file" class="form-control" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">{{ __('parent.attachment_hint') }}</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('parent.details') }}</label>
                                    <textarea class="form-control" name="reason_description" rows="4" placeholder="{{ __('parent.details_placeholder') }}" required>{{ isset($editPermission) ? $editPermission->reason_description : '' }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary waves-effect waves-light px-4" type="submit" id="parentPermissionSubmit">
                                        @isset($editPermission)
                                            <i class="fi fi-rr-refresh me-1"></i> {{ __('parent.update_resubmit') }}
                                        @else
                                            <i class="fi fi-rr-paper-plane me-1"></i> {{ __('parent.submit_request') }}
                                        @endisset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="parentListShell" style="display: {{ $activeTab === 'request' ? 'none' : 'block' }};">
                        <div class="form-loading" id="parentFilterLoading">
                            <span><i class="fi fi-rr-refresh"></i> {{ __('parent.filtering') }}</span>
                            <div class="form-progress"></div>
                        </div>

                        <div class="filter-panel">
                            <div class="section-label mb-3">
                                <i class="fi fi-rr-filter me-1"></i>
                                {{ __('parent.filter_by') }}
                            </div>
                            <form id="parentFilterForm" method="GET" action="{{ route('parent.permissions') }}">
                                <input type="hidden" name="tab" value="{{ $activeTab === 'request' ? 'pending' : $activeTab }}">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('parent.filter_by') }}</label>
                                        <select name="filter_type" id="filter_type" class="form-select" onchange="toggleFilterInputs()">
                                            <option value="all" {{ ($filterType ?? '') == 'all' ? 'selected' : '' }}>{{ __('parent.all_records') }}</option>
                                            <option value="range" {{ ($filterType ?? '') == 'range' ? 'selected' : '' }}>{{ __('parent.date_range') }}</option>
                                            <option value="month" {{ ($filterType ?? '') == 'month' ? 'selected' : '' }}>{{ __('parent.specific_month') }}</option>
                                            <option value="year" {{ ($filterType ?? '') == 'year' ? 'selected' : '' }}>{{ __('parent.specific_year') }}</option>
                                        </select>
                                    </div>

                                    <div id="range_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'range' ? 'flex' : 'none' }}; gap: 0.5rem;">
                                        <div class="flex-grow-1">
                                            <label class="form-label">{{ __('parent.from') }}</label>
                                            <input type="date" name="date_from" id="filterDateFrom" class="form-control" value="{{ $dateFrom ?? '' }}">
                                        </div>
                                        <div class="flex-grow-1">
                                            <label class="form-label">{{ __('parent.to') }}</label>
                                            <input type="date" name="date_to" id="filterDateTo" class="form-control" value="{{ $dateTo ?? '' }}">
                                        </div>
                                    </div>

                                    <div id="month_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'month' ? 'flex' : 'none' }}; gap: 0.5rem;">
                                        <div class="flex-grow-1">
                                            <label class="form-label">{{ __('parent.month') }}</label>
                                            <select name="filter_month" class="form-select">
                                                @foreach(__('parent.months') as $index => $monthName)
                                                    <option value="{{ $index + 1 }}" {{ ($filterMonth ?? date('n')) == ($index + 1) ? 'selected' : '' }}>{{ $monthName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex-grow-1">
                                            <label class="form-label">{{ __('parent.year') }}</label>
                                            <select name="filter_month_year" class="form-select">
                                                @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                                    <option value="{{ $y }}" {{ ($filterMonthYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div id="year_inputs" class="col-md-3 filter-input-group" style="display: {{ ($filterType ?? '') == 'year' ? 'block' : 'none' }};">
                                        <label class="form-label">{{ __('parent.year') }}</label>
                                        <select name="filter_year" class="form-select">
                                            @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                                <option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-md-3 d-flex gap-2">
                                        <button class="btn btn-primary flex-fill" type="submit">
                                            <i class="fi fi-rr-filter me-1"></i> {{ __('parent.filter') }}
                                        </button>
                                        <button class="btn btn-outline-light flex-fill" type="button" onclick="resetParentFilters()">
                                            <i class="fi fi-rr-undo me-1"></i> {{ __('parent.clear') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="perm-pane {{ $activeTab === 'pending' ? 'is-active' : '' }}" data-pane="pending">
                            @include('Parent.partials.permission_table', ['permissions' => $pendingPermissions, 'studentNames' => $studentNames])
                        </div>
                        <div class="perm-pane {{ $activeTab === 'approved' ? 'is-active' : '' }}" data-pane="approved">
                            @include('Parent.partials.permission_table', ['permissions' => $approvedPermissions, 'studentNames' => $studentNames])
                        </div>
                        <div class="perm-pane {{ $activeTab === 'rejected' ? 'is-active' : '' }}" data-pane="rejected">
                            @include('Parent.partials.permission_table', ['permissions' => $rejectedPermissions, 'studentNames' => $studentNames])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.permission_progress_modal')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const P = @json(trans('parent'));
        function initPermViewToggle(root) {
            var scope = root || document.querySelector('.perm-pane.is-active') || document;
            var isMobile = window.innerWidth <= 991;
            var storageKey = 'parent_perm_view_pref';
            var savedView = sessionStorage.getItem(storageKey);
            var activeView = savedView || (isMobile ? 'grid' : 'list');

            function applyView(view) {
                var listEl = scope.querySelector('.perm-list-view');
                var gridEl = scope.querySelector('.perm-grid-view');
                var btns = scope.querySelectorAll('.perm-view-btn');
                if (!listEl || !gridEl) return;
                if (window.innerWidth > 991) {
                    listEl.style.display = view === 'list' ? 'block' : 'none';
                    gridEl.style.display = view === 'grid' ? 'block' : 'none';
                }
                btns.forEach(function(btn) {
                    var isActive = btn.getAttribute('data-view') === view;
                    btn.classList.toggle('active', isActive);
                    btn.classList.toggle('btn-primary', isActive);
                    btn.classList.toggle('btn-outline-light', !isActive);
                });
                sessionStorage.setItem(storageKey, view);
            }

            applyView(activeView);

            scope.querySelectorAll('.perm-view-btn').forEach(function(btn) {
                btn.removeEventListener('click', btn._permViewHandler);
                btn._permViewHandler = function() { applyView(this.getAttribute('data-view')); };
                btn.addEventListener('click', btn._permViewHandler);
            });
        }

        window.showParentPermissionTab = function(tab) {
            document.querySelectorAll('.parent-perm-tab').forEach(function(btn) {
                btn.classList.toggle('active', btn.getAttribute('data-tab') === tab);
            });
            document.querySelectorAll('.perm-pane').forEach(function(pane) {
                pane.classList.toggle('is-active', pane.getAttribute('data-pane') === tab);
            });
            var shell = document.getElementById('parentListShell');
            if (shell) shell.style.display = tab === 'request' ? 'none' : 'block';
            var tabInput = document.querySelector('#parentFilterForm input[name="tab"]');
            if (tabInput && tab !== 'request') tabInput.value = tab;
            if (window.history && window.history.replaceState) {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url.pathname + (url.search || ''));
            }
            var activePane = document.querySelector('.perm-pane.is-active');
            if (activePane) initPermViewToggle(activePane);
            if (tab !== 'request') {
                var badge = document.querySelector('.parent-unread-badge[data-unread-for="' + tab + '"]');
                if (badge) badge.style.display = 'none';
                $.post("{{ route('parent.permissions.read') }}", {
                    _token: "{{ csrf_token() }}",
                    status: tab
                });
            }
        };

        document.querySelectorAll('.parent-perm-tab').forEach(function(btn) {
            btn.addEventListener('click', function() {
                showParentPermissionTab(this.getAttribute('data-tab'));
            });
        });

        initPermViewToggle(document.querySelector('.perm-pane.is-active'));

        const startDateInput = $('#parentStartDate');
        const endDateInput = $('#parentEndDate');
        const daysDisplay = $('#parentDaysDisplay');
        const daysError = $('#parentDaysError');

        function calculateParentDays() {
            const startVal = startDateInput.val();
            const endVal = endDateInput.val();
            if (!startVal || !endVal) {
                daysDisplay.val('');
                daysError.addClass('d-none');
                return;
            }

            const start = new Date(startVal);
            const end = new Date(endVal);

            let count = 0;
            let current = new Date(start);
            current.setHours(0, 0, 0, 0);
            let endCopy = new Date(end);
            endCopy.setHours(0, 0, 0, 0);

            while (current <= endCopy) {
                const dayOfWeek = current.getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }

            daysDisplay.val(count > 0 ? count : '');
            if (count > 7) {
                daysError.removeClass('d-none');
            } else {
                daysError.addClass('d-none');
            }
        }

        startDateInput.on('change', calculateParentDays);
        endDateInput.on('change', calculateParentDays);
        if (startDateInput.val() && endDateInput.val()) {
            calculateParentDays();
        }

        $('#parentPermissionForm').on('submit', function(e) {
            const daysCount = parseInt(daysDisplay.val() || '0', 10);
            if (daysCount > 7) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: P.days_exceeded_title, text: P.days_exceeded, confirmButtonColor: '#940000' });
                return;
            }

            e.preventDefault();
            const btn = $('#parentPermissionSubmit');
            const defaultHtml = btn.data('default-html') || btn.html();
            btn.data('default-html', defaultHtml);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>' + P.sending);

            let formData = new FormData(this);
            formData.append('days_count', daysCount);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        Swal.fire(P.sent, res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire(P.error, res.message, 'error');
                        btn.prop('disabled', false).html(defaultHtml);
                    }
                },
                error: function(xhr) {
                    let msg = P.submit_failed;
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire(P.error, msg, 'error');
                    btn.prop('disabled', false).html(defaultHtml);
                }
            });
        });

        const parentFilterForm = $('#parentFilterForm');
        if (parentFilterForm.length) {
            parentFilterForm.on('submit', function() {
                const loadingBar = document.getElementById('parentFilterLoading');
                if (loadingBar) loadingBar.style.display = 'flex';
                const btn = $(this).find('button[type="submit"]');
                btn.html('<i class="fi fi-rr-refresh"></i> ' + P.filtering);
                btn.prop('disabled', true);
            });
        }

        window.toggleFilterInputs = function() {
            const type = $('#filter_type').val();
            $('.filter-input-group').hide();
            if (type === 'range') {
                $('#range_inputs').css('display', 'flex');
            } else if (type === 'month') {
                $('#month_inputs').css('display', 'flex');
            } else if (type === 'year') {
                $('#year_inputs').show();
            }
        };

        window.resetParentFilters = function() {
            var tabInput = document.querySelector('#parentFilterForm input[name="tab"]');
            var tab = tabInput && tabInput.value ? tabInput.value : 'pending';
            window.location.href = "{{ route('parent.permissions') }}?tab=" + encodeURIComponent(tab);
        };

        $(document).on('show.bs.dropdown', '.table-responsive', function () {
            $(this).css('overflow', 'inherit');
        });
        $(document).on('hide.bs.dropdown', '.table-responsive', function () {
            $(this).css('overflow', 'auto');
        });
    });
</script>
@endpush

@include('partials.permission_details_modal')
