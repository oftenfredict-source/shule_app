@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.my_permission').' - ShuleXpert')
@section('page_title', __('teacher.pages.my_permission'))
@section('page_breadcrumb', __('teacher.pages.my_permission_bc'))

@php
    $permissionListRoute = 'teacher.permissions';
    $permissionStoreRoute = 'teacher.permissions.store';
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-perm,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-perm p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-perm span,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-perm label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-perm td,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-perm th,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-perm small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-perm strong {
        color: #111 !important;
    }

    .sx-teacher-perm {
        --maroon: #940000;
        --line: #e8e0e0;
        --soft: #faf7f7;
        --muted: #666;
    }

    .sx-teacher-perm .sx-panel {
        background: #fff;
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow: 0 1px 2px rgba(80,0,0,.04), 0 10px 28px rgba(80,0,0,.06);
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .sx-teacher-perm .sx-perm-body { padding: 1.05rem 1.15rem 1.25rem; }

    .sx-teacher-perm #permissionTabs {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .65rem;
        margin: 0;
        padding: 1rem 1.1rem;
        border-bottom: 1px solid var(--line);
        background: linear-gradient(135deg, rgba(148,0,0,.035), transparent 42%), linear-gradient(180deg, #fff, var(--soft));
    }
    .sx-teacher-perm #permissionTabs .nav-item { width: 100%; margin: 0; }
    .sx-teacher-perm #permissionTabs .nav-link {
        width: 100%;
        height: 72px;
        min-height: 72px;
        max-height: 72px;
        border: 1px solid #eadfdf !important;
        border-top: 3px solid #d2c4c4 !important;
        border-radius: 0 !important;
        color: #444 !important;
        font-weight: 700 !important;
        font-size: .84rem;
        padding: .7rem .45rem;
        background: linear-gradient(180deg, #fff, #fcfbfb) !important;
        box-shadow: 0 2px 8px rgba(80,0,0,.05);
        display: flex !important;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .22rem;
        position: relative;
        text-decoration: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .sx-teacher-perm #permissionTabs .nav-link i { color: #940000 !important; font-size: 1.05rem; }
    .sx-teacher-perm #permissionTabs .nav-link:hover {
        background: linear-gradient(180deg, #fff, #fff5f5) !important;
        border-color: #d8b4b4 !important;
        border-top-color: #940000 !important;
        color: var(--maroon) !important;
        transform: translateY(-1px);
    }
    .sx-teacher-perm #permissionTabs .nav-link.active {
        color: var(--maroon) !important;
        border-color: #940000 !important;
        border-top-color: #940000 !important;
        background: linear-gradient(180deg, #fff8f8, #ffeaea) !important;
        box-shadow: 0 0 0 1px rgba(148,0,0,.08), 0 8px 18px rgba(148,0,0,.12);
    }
    .sx-teacher-perm #permissionTabs .badge {
        position: absolute !important;
        top: 6px;
        right: 6px;
        margin: 0 !important;
        border-radius: 0 !important;
        font-weight: 700 !important;
        font-size: .65rem;
        padding: .18rem .35rem;
        line-height: 1;
    }
    @media (max-width: 991.98px) {
        .sx-teacher-perm #permissionTabs { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    .sx-teacher-perm .form-label {
        font-size: .7rem;
        font-weight: 800 !important;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: .3rem;
    }
    .sx-teacher-perm .form-control,
    .sx-teacher-perm .form-select,
    .sx-teacher-perm .btn,
    .modal .form-control,
    .modal .btn,
    .modal .modal-content { border-radius: 0 !important; }
    .sx-teacher-perm .form-control,
    .sx-teacher-perm .form-select {
        min-height: 42px;
        border: 1px solid #d4caca;
        font-size: .92rem;
        font-weight: 600 !important;
        color: #111 !important;
        background: #fff;
    }
    .sx-teacher-perm .form-control:focus,
    .sx-teacher-perm .form-select:focus {
        border-color: var(--maroon);
        box-shadow: 0 0 0 .18rem rgba(148,0,0,.12);
    }

    .sx-teacher-perm .sx-filter,
    .sx-teacher-perm .sx-form-surface {
        border: 1px solid var(--line);
        background: linear-gradient(180deg, #fff, var(--soft));
        padding: .95rem 1.05rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 4px rgba(80,0,0,.04);
    }

    .sx-teacher-perm .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .sx-teacher-perm .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        overflow: hidden;
    }
    .sx-teacher-perm .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: progressSlide 1.1s linear infinite;
    }
    @keyframes progressSlide {
        0% { left: -40%; }
        100% { left: 100%; }
    }

    .btn-primary-custom,
    .btn-primary-custom:hover,
    .btn-primary-custom:focus {
        background: linear-gradient(180deg, #a80000 0%, #940000 55%, #7f0000 100%) !important;
        border-color: #7a0000 !important;
        color: #fff !important;
        font-weight: 700 !important;
        border-radius: 0 !important;
        min-height: 42px;
        box-shadow: 0 4px 12px rgba(148,0,0,.22);
    }
    .btn-primary-custom * { color: #fff !important; -webkit-text-fill-color: #fff !important; }
    .btn-exam-action {
        background: #fff !important;
        color: #940000 !important;
        border: 1px solid #940000;
        padding: .35rem .7rem;
        font-size: .78rem;
        font-weight: 700 !important;
        border-radius: 0 !important;
    }
    .btn-exam-action:hover { background: #fff5f5 !important; }

    .sx-ep-table-wrap {
        border: 1px solid var(--line);
        overflow: auto;
        background: #fff;
        box-shadow: 0 2px 10px rgba(80,0,0,.04);
    }
    table.sx-ep-table { width: 100%; margin: 0; border-collapse: collapse; }
    table.sx-ep-table th {
        background: linear-gradient(180deg, #f7f2f2, #f1eaea);
        color: #333 !important;
        font-size: .7rem;
        font-weight: 800 !important;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .72rem .8rem;
        border-bottom: 1px solid var(--line);
        white-space: nowrap;
    }
    table.sx-ep-table td {
        font-size: .9rem;
        font-weight: 600 !important;
        color: #111 !important;
        padding: .8rem;
        border-bottom: 1px solid #f0e8e8;
        vertical-align: middle;
        line-height: 1.4;
        background: #fff;
    }
    table.sx-ep-table tbody tr:hover td { background: #fffafa; }
    .sx-ep-title { color: var(--maroon) !important; font-weight: 800 !important; font-size: .94rem; }
    .sx-ep-meta { display: block; margin-top: .14rem; font-size: .8rem; color: var(--muted) !important; font-weight: 600 !important; }
    .sx-ep-actions { display: flex; flex-wrap: wrap; gap: .35rem; justify-content: flex-end; align-items: center; }
    .sx-dots-wrap { position: relative; }
    .sx-dots-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #eadfdf;
        background: #fff;
        color: #940000 !important;
        padding: 0;
        line-height: 1;
    }
    .sx-dots-btn:hover,
    .sx-dots-wrap.is-open .sx-dots-btn {
        background: #fff5f5;
        border-color: #940000;
    }
    .sx-dots-btn i { font-size: 1.05rem; color: #940000 !important; }
    .sx-dots-menu {
        display: none;
        position: fixed;
        min-width: 180px;
        z-index: 2000;
        background: #fff;
        border: 1px solid #eadfdf;
        box-shadow: 0 10px 24px rgba(80,0,0,.12);
        padding: .35rem 0;
    }
    .sx-dots-wrap.is-open .sx-dots-menu { display: block; }
    .sx-dots-link {
        display: flex;
        align-items: center;
        gap: .45rem;
        width: 100%;
        border: 0;
        background: transparent;
        text-align: left;
        padding: .55rem .85rem;
        font-size: .86rem;
        font-weight: 700 !important;
        color: #333 !important;
        text-decoration: none !important;
        white-space: nowrap;
        cursor: pointer;
    }
    .sx-dots-link i { color: #940000 !important; }
    .sx-dots-link:hover { background: #fff5f5; color: #940000 !important; }
    .sx-ep-empty {
        padding: 2.4rem 1rem;
        text-align: center;
        color: var(--muted) !important;
        font-weight: 600 !important;
        background: radial-gradient(circle at top, rgba(148,0,0,.05), transparent 55%);
    }
    .sx-ep-empty i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        margin: 0 auto .65rem;
        border: 1px solid #eadfdf;
        background: #fff;
        color: var(--maroon);
        font-size: 1.25rem;
    }

    .sx-perm-card {
        border: 1px solid var(--line);
        border-left: 4px solid #ffc107;
        background: #fff;
        padding: .95rem 1rem;
        height: 100%;
        box-shadow: 0 2px 8px rgba(80,0,0,.04);
    }

    .tag {
        display: inline-block;
        padding: .2rem .45rem;
        font-size: .7rem;
        font-weight: 700 !important;
        border-radius: 0;
        line-height: 1.3;
    }
    .tag-approved { background: #e8f6ec !important; color: #146c2e !important; border: 1px solid #c6e6cf; }
    .tag-rejected { background: #fdecec !important; color: #a11919 !important; border: 1px solid #f0c7c7; }
    .tag-pending { background: #fff3cd !important; color: #7a5a00 !important; border: 1px solid #edd89a; }

    .modal .modal-content {
        border: 1px solid #eadfdf !important;
        box-shadow: 0 18px 40px rgba(80,0,0,.14) !important;
        border-radius: 0 !important;
    }
    #permissionDetailsModal .modal-header {
        background: linear-gradient(180deg, #fff8f8, #fff) !important;
        border-bottom: 1px solid #eadfdf !important;
        color: #940000 !important;
    }
    #permissionDetailsModal .modal-title {
        font-weight: 800 !important;
        color: #940000 !important;
    }
    #permissionDetailsModal .btn-close-white {
        filter: none;
        opacity: .7;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-teacher-perm,
    html[data-bs-theme="dark"] .sx-teacher-perm {
        --line: #3a2a2e;
        --soft: #2a1c20;
        --muted: #d7c4c8;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm .sx-panel,
    html[data-bs-theme="dark"] .sx-teacher-perm .sx-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm #permissionTabs,
    html[data-bs-theme="dark"] .sx-teacher-perm #permissionTabs {
        background: #1c1417 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm #permissionTabs .nav-link,
    html[data-bs-theme="dark"] .sx-teacher-perm #permissionTabs .nav-link {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        border-top-color: #4a383c !important;
        color: #e8c9cd !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm #permissionTabs .nav-link i,
    html[data-bs-theme="dark"] .sx-teacher-perm #permissionTabs .nav-link i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm #permissionTabs .nav-link:hover,
    html[data-bs-theme="dark"] .sx-teacher-perm #permissionTabs .nav-link:hover,
    html[data-sx-theme="dark"] .sx-teacher-perm #permissionTabs .nav-link.active,
    html[data-bs-theme="dark"] .sx-teacher-perm #permissionTabs .nav-link.active {
        background: #2a1c20 !important;
        border-color: #940000 !important;
        border-top-color: #940000 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm .form-label,
    html[data-bs-theme="dark"] .sx-teacher-perm .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm .form-control,
    html[data-bs-theme="dark"] .sx-teacher-perm .form-control,
    html[data-sx-theme="dark"] .sx-teacher-perm .form-select,
    html[data-bs-theme="dark"] .sx-teacher-perm .form-select,
    html[data-sx-theme="dark"] .modal .form-control,
    html[data-bs-theme="dark"] .modal .form-control,
    html[data-sx-theme="dark"] .modal .form-select,
    html[data-bs-theme="dark"] .modal .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-perm .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm .sx-filter,
    html[data-bs-theme="dark"] .sx-teacher-perm .sx-filter,
    html[data-sx-theme="dark"] .sx-teacher-perm .sx-form-surface,
    html[data-bs-theme="dark"] .sx-teacher-perm .sx-form-surface {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm .form-loading,
    html[data-bs-theme="dark"] .sx-teacher-perm .form-loading {
        background: rgba(148, 0, 0, 0.18) !important;
        border-color: #4a383c !important;
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm .form-progress,
    html[data-bs-theme="dark"] .sx-teacher-perm .form-progress {
        background: #2a2226 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-table-wrap,
    html[data-bs-theme="dark"] .sx-ep-table-wrap {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] table.sx-ep-table th,
    html[data-bs-theme="dark"] table.sx-ep-table th {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        border-bottom-color: #6e0000 !important;
    }
    html[data-sx-theme="dark"] table.sx-ep-table td,
    html[data-bs-theme="dark"] table.sx-ep-table td {
        background: #24181c !important;
        color: #f3e9eb !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] table.sx-ep-table tbody tr:hover td,
    html[data-bs-theme="dark"] table.sx-ep-table tbody tr:hover td {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-title,
    html[data-bs-theme="dark"] .sx-ep-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-meta,
    html[data-bs-theme="dark"] .sx-ep-meta,
    html[data-sx-theme="dark"] .sx-teacher-perm .text-muted,
    html[data-bs-theme="dark"] .sx-teacher-perm .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-empty,
    html[data-bs-theme="dark"] .sx-ep-empty {
        color: #d7c4c8 !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-ep-empty i,
    html[data-bs-theme="dark"] .sx-ep-empty i {
        background: #2a1c20 !important;
        border-color: #3a2a2e !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-perm-card,
    html[data-bs-theme="dark"] .sx-perm-card {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .btn-exam-action,
    html[data-bs-theme="dark"] .btn-exam-action {
        background: transparent !important;
        color: #ffb4b4 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .btn-exam-action:hover,
    html[data-bs-theme="dark"] .btn-exam-action:hover {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-btn,
    html[data-bs-theme="dark"] .sx-dots-btn {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-btn:hover,
    html[data-bs-theme="dark"] .sx-dots-btn:hover,
    html[data-sx-theme="dark"] .sx-dots-wrap.is-open .sx-dots-btn,
    html[data-bs-theme="dark"] .sx-dots-wrap.is-open .sx-dots-btn {
        background: #2a1c20 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-btn i,
    html[data-bs-theme="dark"] .sx-dots-btn i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-menu,
    html[data-bs-theme="dark"] .sx-dots-menu {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: 0 10px 24px rgba(0,0,0,.45) !important;
    }
    html[data-sx-theme="dark"] .sx-dots-link,
    html[data-bs-theme="dark"] .sx-dots-link {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-dots-link i,
    html[data-bs-theme="dark"] .sx-dots-link i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-link:hover,
    html[data-bs-theme="dark"] .sx-dots-link:hover {
        background: #2a1c20 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-perm .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-teacher-perm .btn-outline-secondary {
        color: #e8c9cd !important;
        border-color: #4a383c !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .modal .modal-content,
    html[data-bs-theme="dark"] .modal .modal-content {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .modal-header,
    html[data-bs-theme="dark"] #permissionDetailsModal .modal-header {
        background: #2a1c20 !important;
        border-bottom-color: #3a2a2e !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .modal-title,
    html[data-bs-theme="dark"] #permissionDetailsModal .modal-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .modal-body,
    html[data-bs-theme="dark"] #permissionDetailsModal .modal-body,
    html[data-sx-theme="dark"] #permissionDetailsModal .modal-footer,
    html[data-bs-theme="dark"] #permissionDetailsModal .modal-footer {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
</style>
@endpush

@section('content')
<div class="sx-teacher-perm">
    <div class="sx-panel card">
        <ul class="nav nav-tabs permission-tabs" id="permissionTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'request' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'request']) }}">
                    <i class="bi bi-send"></i>
                    <span>{{ __('teacher.hr.tab_request') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'pending']) }}">
                    <i class="bi bi-clock-history"></i>
                    <span>{{ __('teacher.hr.tab_pending') }}</span>
                    @if($unreadPendingCount > 0)
                        <span class="badge bg-danger">{{ $unreadPendingCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'approved']) }}">
                    <i class="bi bi-check-circle"></i>
                    <span>{{ __('teacher.hr.tab_completed') }}</span>
                    @if($unreadApprovedCount > 0)
                        <span class="badge bg-success">{{ $unreadApprovedCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'rejected' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'rejected']) }}">
                    <i class="bi bi-x-circle"></i>
                    <span>{{ __('teacher.hr.tab_rejected') }}</span>
                    @if($unreadRejectedCount > 0)
                        <span class="badge bg-danger">{{ $unreadRejectedCount }}</span>
                    @endif
                </a>
            </li>
        </ul>

        <div class="sx-perm-body card-body">
            <div class="form-loading" id="teacherTabLoading">
                <span><i class="bi bi-arrow-repeat text-primary"></i> {{ __('teacher.loading') }}</span>
                <div class="form-progress"></div>
            </div>

            @if($activeTab !== 'request')
            <div class="sx-filter">
                <form id="permissionFilterForm" method="GET" action="{{ route($permissionListRoute) }}">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3 col-6">
                            <label class="form-label">{{ __('teacher.hr.filter_by') }}</label>
                            <select name="filter_type" id="filter_type" class="form-control" onchange="toggleFilterInputs()">
                                <option value="all" {{ ($filterType ?? '') == 'all' ? 'selected' : '' }}>{{ __('teacher.hr.all_records') }}</option>
                                <option value="range" {{ ($filterType ?? '') == 'range' ? 'selected' : '' }}>{{ __('teacher.hr.date_range') }}</option>
                                <option value="month" {{ ($filterType ?? '') == 'month' ? 'selected' : '' }}>{{ __('teacher.hr.specific_month') }}</option>
                                <option value="year" {{ ($filterType ?? '') == 'year' ? 'selected' : '' }}>{{ __('teacher.hr.specific_year') }}</option>
                            </select>
                        </div>

                        <div id="range_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'range' ? 'flex' : 'none' }}; gap: 10px;">
                            <div class="flex-grow-1">
                                <label class="form-label">{{ __('teacher.from') }}</label>
                                <input type="date" name="date_from" id="filterDateFrom" class="form-control" value="{{ $dateFrom ?? '' }}">
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label">{{ __('teacher.to') }}</label>
                                <input type="date" name="date_to" id="filterDateTo" class="form-control" value="{{ $dateTo ?? '' }}">
                            </div>
                        </div>

                        <div id="month_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'month' ? 'flex' : 'none' }}; gap: 10px;">
                            <div class="flex-grow-1">
                                <label class="form-label">{{ __('teacher.hr.month') }}</label>
                                <select name="filter_month" class="form-control">
                                    @php $hrMonths = __('teacher.hr.months'); @endphp
                                    @foreach($hrMonths as $index => $monthName)
                                        <option value="{{ $index + 1 }}" {{ ($filterMonth ?? date('n')) == ($index + 1) ? 'selected' : '' }}>{{ $monthName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label">{{ __('teacher.year') }}</label>
                                <select name="filter_month_year" class="form-control">
                                    @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                        <option value="{{ $y }}" {{ ($filterMonthYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div id="year_inputs" class="col-md-3 filter-input-group" style="display: {{ ($filterType ?? '') == 'year' ? 'block' : 'none' }};">
                            <label class="form-label">{{ __('teacher.year') }}</label>
                            <select name="filter_year" class="form-control">
                                @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                    <option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3 col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary-custom flex-grow-1">
                                <i class="bi bi-funnel"></i> {{ __('teacher.hr.apply') }}
                            </button>
                            <button type="button" onclick="resetTeacherFilters()" class="btn btn-outline-secondary flex-grow-1" style="min-height:42px;font-weight:700;">
                                <i class="bi bi-arrow-counterclockwise"></i> {{ __('teacher.hr.reset') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            @if($activeTab === 'request')
                @isset($editPermission)
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-3 rounded-0" style="border-left: 4px solid #ff9800;">
                    <i class="bi bi-pencil-square"></i>
                    <div class="flex-grow-1"><strong>{{ __('teacher.hr.editing_rejected') }}</strong> — {{ __('teacher.hr.editing_rejected_hint') }}</div>
                    <a href="{{ route($permissionListRoute, ['tab' => 'request']) }}" class="btn btn-sm btn-outline-secondary rounded-0"><i class="bi bi-x"></i> {{ __('teacher.cancel') }}</a>
                </div>
                @endisset

                <div class="sx-form-surface">
                    <form method="POST" action="{{ route($permissionStoreRoute) }}" id="teacherPermissionForm">
                        @csrf
                        @isset($editPermission)
                        <input type="hidden" name="permission_id" value="{{ $editPermission->permissionID }}">
                        @endisset

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label" for="timeMode">{{ __('teacher.hr.time_mode') }}</label>
                                <select class="form-control" name="time_mode" id="timeMode" required>
                                    <option value="">{{ __('teacher.hr.select') }}</option>
                                    <option value="days" {{ isset($editPermission) && $editPermission->time_mode === 'days' ? 'selected' : '' }}>{{ __('teacher.hr.days_max7') }}</option>
                                    <option value="hours" {{ isset($editPermission) && $editPermission->time_mode === 'hours' ? 'selected' : '' }}>{{ __('teacher.hr.hours') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('teacher.hr.reason_type') }}</label>
                                <select class="form-control" name="reason_type" required>
                                    <option value="medical" {{ isset($editPermission) && $editPermission->reason_type === 'medical' ? 'selected' : '' }}>{{ __('teacher.hr.medical') }}</option>
                                    <option value="official" {{ isset($editPermission) && $editPermission->reason_type === 'official' ? 'selected' : '' }}>{{ __('teacher.hr.official') }}</option>
                                    <option value="professional" {{ isset($editPermission) && $editPermission->reason_type === 'professional' ? 'selected' : '' }}>{{ __('teacher.hr.professional') }}</option>
                                    <option value="emergency" {{ isset($editPermission) && $editPermission->reason_type === 'emergency' ? 'selected' : '' }}>{{ __('teacher.hr.emergency') }}</option>
                                    <option value="other" {{ isset($editPermission) && $editPermission->reason_type === 'other' ? 'selected' : '' }}>{{ __('teacher.hr.other') }}</option>
                                </select>
                            </div>
                        </div>

                        <div id="daysFields" style="display:none;">
                            <input type="hidden" name="days_count" id="teacherDaysCount" value="">
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher.hr.from_date') }}</label>
                                    <input type="date" class="form-control" name="start_date" id="teacherStartDate"
                                        value="{{ isset($editPermission) && $editPermission->start_date ? \Carbon\Carbon::parse($editPermission->start_date)->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher.hr.to_date') }}</label>
                                    <input type="date" class="form-control" name="end_date" id="teacherEndDate"
                                        value="{{ isset($editPermission) && $editPermission->end_date ? \Carbon\Carbon::parse($editPermission->end_date)->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher.hr.total_days') }}</label>
                                    <input type="text" class="form-control" id="teacherDaysDisplay" readonly>
                                    <small class="text-danger d-none" id="teacherDaysError">{{ __('teacher.hr.days_exceed') }}</small>
                                </div>
                            </div>
                        </div>

                        <div id="hoursFields" style="display:none;">
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher.hr.start_time') }}</label>
                                    <input type="time" class="form-control" name="start_time" value="{{ isset($editPermission) ? $editPermission->start_time : '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher.hr.end_time') }}</label>
                                    <input type="time" class="form-control" name="end_time" value="{{ isset($editPermission) ? $editPermission->end_time : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('teacher.hr.reason_desc') }}</label>
                            <textarea class="form-control" name="reason_description" rows="3" required>{{ isset($editPermission) ? $editPermission->reason_description : '' }}</textarea>
                        </div>

                        <button class="btn btn-primary-custom" type="submit" id="teacherPermissionSubmit">
                            @isset($editPermission)
                                <i class="bi bi-arrow-repeat"></i> {{ __('teacher.hr.update_resubmit') }}
                            @else
                                <i class="bi bi-send"></i> {{ __('teacher.hr.send_request') }}
                            @endisset
                        </button>
                    </form>
                </div>
            @endif

            @if($activeTab === 'pending')
                @include('Teacher.partials.permission_table_corp', ['permissions' => $pendingPermissions])
            @endif

            @if($activeTab === 'approved')
                @include('Teacher.partials.permission_table_corp', ['permissions' => $approvedPermissions])
            @endif

            @if($activeTab === 'rejected')
                @include('Teacher.partials.permission_table_corp', ['permissions' => $rejectedPermissions])
            @endif
        </div>
    </div>
</div>

@include('partials.permission_progress_modal')
@include('partials.permission_details_modal')
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm .form-label {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm #permissionTabs .nav-link,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm #permissionTabs .nav-link {
    color: #e8c9cd !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm #permissionTabs .nav-link.active,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm #permissionTabs .nav-link.active,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm #permissionTabs .nav-link.active span,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm #permissionTabs .nav-link.active span {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th,
html[data-bs-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th {
    background: #940000 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm .sx-ep-meta,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm .sx-ep-meta,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm .text-muted,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-perm .text-muted {
    color: #d7c4c8 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-approved,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-approved {
    background: #e8f6ec !important; color: #146c2e !important; -webkit-text-fill-color: #146c2e !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-rejected,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-rejected {
    background: #fdecec !important; color: #a11919 !important; -webkit-text-fill-color: #a11919 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-pending,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-pending {
    background: #fff3cd !important; color: #7a5a00 !important; -webkit-text-fill-color: #7a5a00 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-dots-menu .sx-dots-link,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-dots-menu .sx-dots-link {
    color: #f3e9eb !important;
}
</style>
@endsection

@push('scripts')
<script>
const i18n = @json(__('teacher.hr'));
window.togglePermDots = function(btn) {
    const wrap = btn.closest('.sx-dots-wrap');
    const menu = wrap.querySelector('.sx-dots-menu');
    const wasOpen = wrap.classList.contains('is-open');
    document.querySelectorAll('.sx-dots-wrap.is-open').forEach(function(el) { el.classList.remove('is-open'); });
    if (wasOpen) return;
    wrap.classList.add('is-open');
    const rect = btn.getBoundingClientRect();
    const menuWidth = 180;
    let left = rect.right - menuWidth;
    if (left < 8) left = 8;
    let top = rect.bottom + 4;
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
};
window.closePermDots = function() {
    document.querySelectorAll('.sx-dots-wrap.is-open').forEach(function(el) { el.classList.remove('is-open'); });
};
document.addEventListener('click', function(e) {
    if (!e.target.closest('.sx-dots-wrap')) closePermDots();
});
window.addEventListener('scroll', closePermDots, true);

window.sxShowModal = function(el) {
    const node = typeof el === 'string' ? document.querySelector(el) : el;
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('show');
};
window.sxHideModal = function(el) {
    const node = typeof el === 'string' ? document.querySelector(el) : el;
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        const inst = bootstrap.Modal.getInstance(node);
        if (inst) inst.hide();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('hide');
};

function initPermViewToggle() {
    var isMobile = window.innerWidth <= 991;
    var storageKey = 'teacher_perm_view_pref';
    var savedView = sessionStorage.getItem(storageKey);
    var activeView = savedView || (isMobile ? 'grid' : 'list');

    function applyView(view) {
        var listEl = document.getElementById('perm-list-view');
        var gridEl = document.getElementById('perm-grid-view');
        var btns = document.querySelectorAll('.perm-view-btn');
        if (!listEl || !gridEl) return;
        listEl.style.display = view === 'list' ? 'block' : 'none';
        gridEl.style.display = view === 'grid' ? 'block' : 'none';
        btns.forEach(function(btn) {
            var isActive = btn.getAttribute('data-view') === view;
            btn.classList.toggle('active', isActive);
            btn.classList.toggle('btn-secondary', isActive);
            btn.classList.toggle('btn-outline-secondary', !isActive);
        });
        sessionStorage.setItem(storageKey, view);
    }

    applyView(activeView);
    document.querySelectorAll('.perm-view-btn').forEach(function(btn) {
        btn.onclick = function() { applyView(this.getAttribute('data-view')); };
    });
}

function initTeacherPermissionForm() {
    const timeMode = document.getElementById('timeMode');
    const daysFields = document.getElementById('daysFields');
    const hoursFields = document.getElementById('hoursFields');
    const startDateInput = document.getElementById('teacherStartDate');
    const endDateInput = document.getElementById('teacherEndDate');
    const daysDisplay = document.getElementById('teacherDaysDisplay');
    const daysCountInput = document.getElementById('teacherDaysCount');
    const daysError = document.getElementById('teacherDaysError');

    function calculateDays() {
        if (!startDateInput || !endDateInput || !daysDisplay || !daysCountInput || !daysError) return;
        const start = startDateInput.value ? new Date(startDateInput.value) : null;
        const end = endDateInput.value ? new Date(endDateInput.value) : null;
        if (!start || !end) {
            daysDisplay.value = '';
            daysCountInput.value = '';
            daysError.classList.add('d-none');
            return;
        }

        let count = 0;
        let current = new Date(start);
        current.setHours(0, 0, 0, 0);
        let endCopy = new Date(end);
        endCopy.setHours(0, 0, 0, 0);

        while (current <= endCopy) {
            const dayOfWeek = current.getDay();
            if (dayOfWeek !== 0 && dayOfWeek !== 6) count++;
            current.setDate(current.getDate() + 1);
        }

        daysDisplay.value = count > 0 ? count : '';
        daysCountInput.value = count > 0 ? count : '';
        daysError.classList.toggle('d-none', count <= 7);
    }

    function toggleTimeMode() {
        if (!timeMode) return;
        if (timeMode.value === 'days') {
            daysFields.style.display = 'block';
            hoursFields.style.display = 'none';
            calculateDays();
        } else if (timeMode.value === 'hours') {
            hoursFields.style.display = 'block';
            daysFields.style.display = 'none';
        } else {
            daysFields.style.display = 'none';
            hoursFields.style.display = 'none';
        }
    }

    if (timeMode) {
        timeMode.addEventListener('change', toggleTimeMode);
        toggleTimeMode();
    }
    if (startDateInput) startDateInput.addEventListener('change', calculateDays);
    if (endDateInput) endDateInput.addEventListener('change', calculateDays);
    if (startDateInput && startDateInput.value && endDateInput && endDateInput.value) calculateDays();

    const permissionForm = document.getElementById('teacherPermissionForm');
    if (permissionForm) {
        permissionForm.addEventListener('submit', function(e) {
            if (timeMode && timeMode.value === 'days') {
                calculateDays();
                const days = parseInt(daysCountInput.value || '0', 10);
                if (days > 7) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: i18n.failed, text: i18n.days_exceed, confirmButtonColor: '#940000' });
                    return;
                }
            }
            e.preventDefault();
            const submitBtn = document.getElementById('teacherPermissionSubmit');
            if (submitBtn) submitBtn.disabled = true;
            Swal.fire({ icon: 'info', title: i18n.sending, text: i18n.please_wait, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const formData = new FormData(permissionForm);
            fetch(permissionForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: i18n.success, text: data.message, confirmButtonColor: '#940000' });
                    permissionForm.reset();
                    if (daysFields) daysFields.style.display = 'none';
                    if (hoursFields) hoursFields.style.display = 'none';
                } else {
                    Swal.fire({ icon: 'error', title: i18n.failed, text: data.message || i18n.fail_send_request, confirmButtonColor: '#940000' });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: i18n.failed, text: i18n.fail_send_request, confirmButtonColor: '#940000' });
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    }
}

function loadTeacherTab(url) {
    const cardBody = document.querySelector('.sx-perm-body');
    if (!cardBody) return;
    const loadingBar = cardBody.querySelector('#teacherTabLoading');
    if (loadingBar) loadingBar.style.display = 'flex';
    cardBody.style.opacity = '0.6';
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newBody = doc.querySelector('.sx-perm-body');
            const newTabs = doc.querySelector('#permissionTabs');
            if (newTabs) {
                const currentTabs = document.querySelector('#permissionTabs');
                if (currentTabs) currentTabs.innerHTML = newTabs.innerHTML;
            }
            if (newBody) {
                cardBody.innerHTML = newBody.innerHTML;
                bindTeacherTabLinks();
                initTeacherPermissionForm();
                initPermViewToggle();
            }
        })
        .finally(() => {
            cardBody.style.opacity = '1';
            const currentLoadingBar = cardBody.querySelector('#teacherTabLoading');
            if (currentLoadingBar) currentLoadingBar.style.display = 'none';
        });
}

function toggleFilterInputs() {
    const type = document.getElementById('filter_type') ? document.getElementById('filter_type').value : 'all';
    document.querySelectorAll('.filter-input-group').forEach(function(el) { el.style.display = 'none'; });
    if (type === 'range') document.getElementById('range_inputs').style.display = 'flex';
    else if (type === 'month') document.getElementById('month_inputs').style.display = 'flex';
    else if (type === 'year') document.getElementById('year_inputs').style.display = 'block';
}

function resetTeacherFilters() {
    window.location.href = "{{ route($permissionListRoute) }}?tab={{ $activeTab }}";
}

function bindTeacherTabLinks() {
    document.querySelectorAll('.permission-tabs .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            loadTeacherTab(this.href);
            history.pushState({}, '', this.href);
        });
    });

    const filterForm = document.getElementById('permissionFilterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> ' + (i18n.filtering || 'Filtering...');
            btn.disabled = true;

            const url = new URL(this.action);
            url.searchParams.set('tab', this.querySelector('input[name="tab"]').value);
            const formData = new FormData(this);
            for (let [key, value] of formData.entries()) {
                if (value) url.searchParams.set(key, value);
            }
            loadTeacherTab(url.toString());
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }, 800);
        });
    }
}

bindTeacherTabLinks();
initTeacherPermissionForm();
initPermViewToggle();

@if(session('success'))
    Swal.fire({ icon: 'success', title: i18n.success, text: @json(session('success')), confirmButtonColor: '#940000' });
@endif
@if(session('error'))
    Swal.fire({ icon: 'error', title: i18n.failed, text: @json(session('error')), confirmButtonColor: '#940000' });
@endif
</script>
@endpush
