@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.ai_training'))
@section('page_breadcrumb', __('superadmin.pages.ai_training'))

@section('page_actions')
    <a href="{{ route('superadmin.customer_care') }}" class="btn btn-primary waves-effect waves-light me-2">
        <i class="fi fi-rr-headset me-1"></i> {{ __('superadmin.ai_training.customer_care') }}
    </a>
    <button type="button" class="btn btn-primary waves-effect waves-light" id="btnOpenCreate">
        <i class="fi fi-rr-add me-1"></i> {{ __('superadmin.ai_training.add_qa') }}
    </button>
@endsection

@section('content')
@php
    $qaItems = collect($items ?? []);
    $qaTotal = $qaItems->count();
    $qaActive = $qaItems->filter(function ($it) { return !empty($it->is_active); })->count();
    $qaInactive = max(0, $qaTotal - $qaActive);
@endphp

<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait span:not(.sx-badge):not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ait a:not(.btn) {
        color: #000 !important;
    }
    .sx-ait .btn-primary,
    .sx-ait a.btn-primary,
    .sx-ait .btn-primary i,
    .sx-ait .btn-primary i:before,
    .sx-ait .text-white,
    .sx-ait .sx-badge.is-active {
        color: #fff !important;
    }
    .sx-ait .sx-ait-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-ait .sx-ait-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-ait .sx-ait-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-ait .sx-ait-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #000 !important;
    }
    .sx-ait .sx-ait-stat .stat-ico {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.2rem;
        line-height: 1;
    }
    .sx-ait .sx-ait-stat .stat-ico i,
    .sx-ait .sx-ait-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-ait .sx-ait-stat.is-total .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-ait .sx-ait-stat.is-active .stat-ico { background: #e8f7ec; color: #145c2a !important; }
    .sx-ait .sx-ait-stat.is-inactive .stat-ico { background: #f5f5f5; color: #555 !important; }
    .sx-ait .sx-ait-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-ait .sx-ait-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-ait .sx-ait-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-ait .sx-ait-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-ait .form-label,
    #qaModal .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
    }
    .sx-ait .form-label { color: #555 !important; }
    .sx-ait .form-select,
    .sx-ait .form-control,
    #qaModal .form-select,
    #qaModal .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
    }
    .sx-ait .form-select:focus,
    .sx-ait .form-control:focus,
    #qaModal .form-select:focus,
    #qaModal .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
        outline: none;
    }
    .sx-ait .btn-primary,
    #qaModal .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-ait .btn-primary:hover,
    #qaModal .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-ait .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000 !important;
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-ait .btn-outline-primary:hover {
        background: #940000 !important;
        color: #fff !important;
    }
    .sx-ait .btn-outline-secondary,
    .sx-ait .btn-outline-danger,
    #qaModal .btn-outline-secondary {
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-ait .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: hidden;
        padding: 0.75rem;
    }
    .sx-ait .table { margin: 0; color: #000; vertical-align: middle; }
    .sx-ait .table thead th {
        background: #f7f7f7;
        border-bottom: 1px solid #e6e6e6;
        border-top: 0;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        white-space: nowrap;
        padding: 0.75rem 0.9rem;
    }
    .sx-ait .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
        color: #000;
    }
    .sx-ait .table tbody tr:hover { background: #fffafa; }
    .sx-ait .sx-ait-q { font-weight: 700; max-width: 260px; }
    .sx-ait .sx-ait-answer {
        max-width: 420px;
        white-space: pre-wrap;
        word-break: break-word;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }
    .sx-ait .sx-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    .sx-ait .sx-badge.is-active { background: #145c2a; color: #fff !important; }
    .sx-ait .sx-badge.is-inactive { background: #f0f0f0; color: #666 !important; }
    .sx-ait .sx-badge.is-locale { background: #fff0e8; color: #a34a00 !important; }
    .sx-ait .sx-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        font-size: 0.92rem;
    }
    .sx-ait .sx-empty i { display: block; font-size: 1.8rem; margin-bottom: 0.5rem; color: #940000 !important; }
    .sx-ait .dataTables_wrapper .dataTables_length select,
    .sx-ait .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
    }
    .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }

    /* Modal (Bootstrap may move #qaModal outside .sx-ait) */
    #qaModal .modal-content {
        border: 0;
        border-top: 4px solid #940000;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(20, 0, 0, 0.18);
    }
    #qaModal .modal-header {
        background: #f7f7f7;
        border-bottom: 1px solid #e6e6e6;
    }
    #qaModal .modal-title {
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #940000;
    }
    #qaModal .modal-footer { border-top: 1px solid #eee; }
    /* Dark mode */
    html[data-sx-theme="dark"] .sx-ait,
    html[data-bs-theme="dark"] .sx-ait,
    html[data-sx-theme="dark"] .sx-ait p,
    html[data-bs-theme="dark"] .sx-ait p,
    html[data-sx-theme="dark"] .sx-ait span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-ait span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-ait small,
    html[data-bs-theme="dark"] .sx-ait small,
    html[data-sx-theme="dark"] .sx-ait strong,
    html[data-bs-theme="dark"] .sx-ait strong,
    html[data-sx-theme="dark"] .sx-ait div,
    html[data-bs-theme="dark"] .sx-ait div,
    html[data-sx-theme="dark"] .sx-ait label,
    html[data-bs-theme="dark"] .sx-ait label,
    html[data-sx-theme="dark"] .sx-ait h1,
    html[data-bs-theme="dark"] .sx-ait h1,
    html[data-sx-theme="dark"] .sx-ait h2,
    html[data-bs-theme="dark"] .sx-ait h2,
    html[data-sx-theme="dark"] .sx-ait h3,
    html[data-bs-theme="dark"] .sx-ait h3,
    html[data-sx-theme="dark"] .sx-ait h4,
    html[data-bs-theme="dark"] .sx-ait h4,
    html[data-sx-theme="dark"] .sx-ait h5,
    html[data-bs-theme="dark"] .sx-ait h5,
    html[data-sx-theme="dark"] .sx-ait h6,
    html[data-bs-theme="dark"] .sx-ait h6,
    html[data-sx-theme="dark"] .sx-ait .sx-ait-card-title,
    html[data-bs-theme="dark"] .sx-ait .sx-ait-card-title,
    html[data-sx-theme="dark"] .sx-ait .stat-label,
    html[data-bs-theme="dark"] .sx-ait .stat-label,
    html[data-sx-theme="dark"] .sx-ait .stat-value,
    html[data-bs-theme="dark"] .sx-ait .stat-value,
    html[data-sx-theme="dark"] .sx-ait .table,
    html[data-bs-theme="dark"] .sx-ait .table,
    html[data-sx-theme="dark"] .sx-ait .table tbody td,
    html[data-bs-theme="dark"] .sx-ait .table tbody td,
    html[data-sx-theme="dark"] .sx-ait .text-body,
    html[data-bs-theme="dark"] .sx-ait .text-body,
    html[data-sx-theme="dark"] .sx-ait a:not(.btn),
    html[data-bs-theme="dark"] .sx-ait a:not(.btn),
    html[data-sx-theme="dark"] .sx-ait .form-check-label,
    html[data-bs-theme="dark"] .sx-ait .form-check-label,
    html[data-sx-theme="dark"] .sx-ait .sx-ait-answer,
    html[data-bs-theme="dark"] .sx-ait .sx-ait-answer,
    html[data-sx-theme="dark"] .sx-ait .sx-ait-q,
    html[data-bs-theme="dark"] .sx-ait .sx-ait-q {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-ait .text-muted,
    html[data-bs-theme="dark"] .sx-ait .text-muted,
    html[data-sx-theme="dark"] .sx-ait .form-label,
    html[data-bs-theme="dark"] .sx-ait .form-label,
    html[data-sx-theme="dark"] .sx-ait .table thead th,
    html[data-bs-theme="dark"] .sx-ait .table thead th,
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_filter,
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_processing,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_processing {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ait .sx-ait-stat,
    html[data-bs-theme="dark"] .sx-ait .sx-ait-stat,
    html[data-sx-theme="dark"] .sx-ait .sx-ait-card,
    html[data-bs-theme="dark"] .sx-ait .sx-ait-card,
    html[data-sx-theme="dark"] .sx-ait .card,
    html[data-bs-theme="dark"] .sx-ait .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-ait .sx-ait-card-head,
    html[data-bs-theme="dark"] .sx-ait .sx-ait-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-ait .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-ait .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-ait .table,
    html[data-bs-theme="dark"] .sx-ait .table {
        --bs-table-bg: transparent;
        --bs-table-color: #f3e9eb;
        --bs-table-hover-bg: rgba(255, 180, 180, 0.06);
        --bs-table-hover-color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-ait .table thead th,
    html[data-bs-theme="dark"] .sx-ait .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-ait .table tbody td,
    html[data-bs-theme="dark"] .sx-ait .table tbody td,
    html[data-sx-theme="dark"] .sx-ait .table tbody tr,
    html[data-bs-theme="dark"] .sx-ait .table tbody tr {
        background-color: transparent !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-ait .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-ait .table tbody tr:hover,
    html[data-sx-theme="dark"] .sx-ait .table tbody tr:hover td,
    html[data-bs-theme="dark"] .sx-ait .table tbody tr:hover td {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    html[data-sx-theme="dark"] .sx-ait .form-control,
    html[data-bs-theme="dark"] .sx-ait .form-control,
    html[data-sx-theme="dark"] .sx-ait .form-select,
    html[data-bs-theme="dark"] .sx-ait .form-select,
    html[data-sx-theme="dark"] .sx-ait .kbd-textarea,
    html[data-bs-theme="dark"] .sx-ait .kbd-textarea,
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-ait .form-control::placeholder,
    html[data-bs-theme="dark"] .sx-ait .form-control::placeholder,
    html[data-sx-theme="dark"] .sx-ait .kbd-textarea::placeholder,
    html[data-bs-theme="dark"] .sx-ait .kbd-textarea::placeholder {
        color: #a8949a !important;
    }
    html[data-sx-theme="dark"] .sx-ait .form-control:focus,
    html[data-bs-theme="dark"] .sx-ait .form-control:focus,
    html[data-sx-theme="dark"] .sx-ait .form-select:focus,
    html[data-bs-theme="dark"] .sx-ait .form-select:focus,
    html[data-sx-theme="dark"] .sx-ait .kbd-textarea:focus,
    html[data-bs-theme="dark"] .sx-ait .kbd-textarea:focus {
        border-color: #c43a3a !important;
        box-shadow: 0 0 0 0.2rem rgba(196, 58, 58, 0.25) !important;
    }
    html[data-sx-theme="dark"] .sx-ait .form-select,
    html[data-bs-theme="dark"] .sx-ait .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23d7c4c8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    }
    html[data-sx-theme="dark"] .sx-ait .select2-container--default .select2-selection--single,
    html[data-bs-theme="dark"] .sx-ait .select2-container--default .select2-selection--single,
    html[data-sx-theme="dark"] .sx-ait .select2-container--default .select2-selection--multiple,
    html[data-bs-theme="dark"] .sx-ait .select2-container--default .select2-selection--multiple {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-ait .select2-container--default .select2-selection--single .select2-selection__rendered,
    html[data-bs-theme="dark"] .sx-ait .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-ait .select2-container--default .select2-selection--single .select2-selection__placeholder,
    html[data-bs-theme="dark"] .sx-ait .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #a8949a !important;
    }
    html[data-sx-theme="dark"] .sx-ait .select2-container--default .select2-selection--single .select2-selection__arrow b,
    html[data-bs-theme="dark"] .sx-ait .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #d7c4c8 transparent transparent transparent !important;
    }
    html[data-sx-theme="dark"] .sx-ait .select2-container--default .select2-selection__clear,
    html[data-bs-theme="dark"] .sx-ait .select2-container--default .select2-selection__clear {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ait .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-ait .btn-outline-primary {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-ait .btn-outline-primary:hover,
    html[data-bs-theme="dark"] .sx-ait .btn-outline-primary:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ait .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-ait .btn-outline-secondary {
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-ait .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] .sx-ait .btn-outline-secondary:hover {
        background: rgba(255, 180, 180, 0.12) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ait .btn-outline-danger,
    html[data-bs-theme="dark"] .sx-ait .btn-outline-danger {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-ait .btn-outline-danger:hover,
    html[data-bs-theme="dark"] .sx-ait .btn-outline-danger:hover {
        background: rgba(148, 0, 0, 0.45) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ait .btn-primary,
    html[data-bs-theme="dark"] .sx-ait .btn-primary,
    html[data-sx-theme="dark"] .sx-ait .btn-success,
    html[data-bs-theme="dark"] .sx-ait .btn-success,
    html[data-sx-theme="dark"] .sx-ait .btn-danger,
    html[data-bs-theme="dark"] .sx-ait .btn-danger,
    html[data-sx-theme="dark"] .sx-ait .btn-primary i,
    html[data-bs-theme="dark"] .sx-ait .btn-primary i,
    html[data-sx-theme="dark"] .sx-ait .btn-primary i:before,
    html[data-bs-theme="dark"] .sx-ait .btn-primary i:before {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(148, 0, 0, 0.3) !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-sx-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    html[data-bs-theme="dark"] .sx-ait .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-ait .form-check-input,
    html[data-bs-theme="dark"] .sx-ait .form-check-input {
        background-color: #1c1417;
        border-color: rgba(255, 180, 180, 0.35);
    }
    html[data-sx-theme="dark"] .sx-ait .form-check-input:checked,
    html[data-bs-theme="dark"] .sx-ait .form-check-input:checked {
        background-color: #940000;
        border-color: #940000;
    }
    html[data-sx-theme="dark"] .sx-ait .spinner-border,
    html[data-bs-theme="dark"] .sx-ait .spinner-border {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ait .sx-ait-stat.is-total .stat-ico,
    html[data-bs-theme="dark"] .sx-ait .sx-ait-stat.is-total .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ait .sx-ait-stat.is-active .stat-ico,
    html[data-bs-theme="dark"] .sx-ait .sx-ait-stat.is-active .stat-ico {
        background: rgba(20, 92, 42, 0.28) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-ait .sx-ait-stat.is-inactive .stat-ico,
    html[data-bs-theme="dark"] .sx-ait .sx-ait-stat.is-inactive .stat-ico {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ait .sx-badge.is-active,
    html[data-bs-theme="dark"] .sx-ait .sx-badge.is-active {
        background: rgba(20, 92, 42, 0.45) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-ait .sx-badge.is-inactive,
    html[data-bs-theme="dark"] .sx-ait .sx-badge.is-inactive {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ait .sx-badge.is-locale,
    html[data-bs-theme="dark"] .sx-ait .sx-badge.is-locale {
        background: rgba(163, 74, 0, 0.3) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-ait .sx-empty,
    html[data-bs-theme="dark"] .sx-ait .sx-empty,
    html[data-sx-theme="dark"] .sx-ait .sx-empty i,
    html[data-bs-theme="dark"] .sx-ait .sx-empty i {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #qaModal .modal-content,
    html[data-bs-theme="dark"] #qaModal .modal-content {
        background: #24181c !important;
        color: #f3e9eb !important;
        border-top: 4px solid #940000 !important;
        border-color: rgba(255, 180, 180, 0.16);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
    }
    html[data-sx-theme="dark"] #qaModal .modal-header,
    html[data-bs-theme="dark"] #qaModal .modal-header,
    html[data-sx-theme="dark"] #qaModal .modal-footer,
    html[data-bs-theme="dark"] #qaModal .modal-footer {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] #qaModal .modal-title,
    html[data-bs-theme="dark"] #qaModal .modal-title,
    html[data-sx-theme="dark"] #qaModal label,
    html[data-bs-theme="dark"] #qaModal label,
    html[data-sx-theme="dark"] #qaModal .form-label,
    html[data-bs-theme="dark"] #qaModal .form-label,
    html[data-sx-theme="dark"] #qaModal .modal-body,
    html[data-bs-theme="dark"] #qaModal .modal-body {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #qaModal .text-muted,
    html[data-bs-theme="dark"] #qaModal .text-muted,
    html[data-sx-theme="dark"] #qaModal small,
    html[data-bs-theme="dark"] #qaModal small {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #qaModal .btn-close,
    html[data-bs-theme="dark"] #qaModal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    html[data-sx-theme="dark"] #qaModal .form-control,
    html[data-bs-theme="dark"] #qaModal .form-control,
    html[data-sx-theme="dark"] #qaModal .form-select,
    html[data-bs-theme="dark"] #qaModal .form-select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #qaModal .form-control::placeholder,
    html[data-bs-theme="dark"] #qaModal .form-control::placeholder {
        color: #a8949a !important;
    }
    html[data-sx-theme="dark"] #qaModal .form-control:focus,
    html[data-bs-theme="dark"] #qaModal .form-control:focus,
    html[data-sx-theme="dark"] #qaModal .form-select:focus,
    html[data-bs-theme="dark"] #qaModal .form-select:focus {
        border-color: #c43a3a !important;
        box-shadow: 0 0 0 0.2rem rgba(196, 58, 58, 0.25) !important;
    }
    html[data-sx-theme="dark"] #qaModal .form-select,
    html[data-bs-theme="dark"] #qaModal .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23d7c4c8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    }
    html[data-sx-theme="dark"] #qaModal .btn-primary,
    html[data-bs-theme="dark"] #qaModal .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] #qaModal .btn-outline-secondary,
    html[data-bs-theme="dark"] #qaModal .btn-outline-secondary {
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] #qaModal .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] #qaModal .btn-outline-secondary:hover {
        background: rgba(255, 180, 180, 0.12) !important;
        color: #fff !important;
    }
</style>

<div class="sx-ait">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card sx-ait-stat is-total h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.ai_training.total_qa') }}</span>
                        <span class="stat-value" id="statTotal">{{ $qaTotal }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-comment-alt"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card sx-ait-stat is-active h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.ai_training.active') }}</span>
                        <span class="stat-value" id="statActive">{{ $qaActive }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-check-circle"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-xl-4">
            <div class="card sx-ait-stat is-inactive h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.ai_training.inactive') }}</span>
                        <span class="stat-value" id="statInactive">{{ $qaInactive }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-pause-circle"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-ait-card">
        <div class="card-body">
            <div class="sx-ait-card-head">
                <h6 class="sx-ait-card-title"><i class="fi fi-rr-robot me-1"></i> {{ __('superadmin.ai_training.common_questions') }}</h6>
                <span class="small text-muted">{{ __('superadmin.ai_training.common_questions_desc') }}</span>
            </div>

            @if($qaTotal === 0)
                <div class="sx-table-wrap">
                    <div class="sx-empty text-muted">
                        <i class="fi fi-rr-info"></i>
                        {{ __('superadmin.ai_training.empty') }} <strong>{{ __('superadmin.ai_training.add_qa') }}</strong> {{ __('superadmin.ai_training.empty_suffix') }}
                    </div>
                </div>
            @else
                <div class="sx-table-wrap">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="qaTable">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>{{ __('superadmin.ai_training.th_question') }}</th>
                                    <th>{{ __('superadmin.ai_training.th_answer') }}</th>
                                    <th width="90">{{ __('superadmin.ai_training.th_locale') }}</th>
                                    <th width="80">{{ __('superadmin.ai_training.th_priority') }}</th>
                                    <th width="100">{{ __('superadmin.ai_training.th_status') }}</th>
                                    <th class="text-end" width="110">{{ __('superadmin.ai_training.th_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($qaItems as $i => $item)
                                    <tr data-id="{{ $item->id }}">
                                        <td>{{ $i + 1 }}</td>
                                        <td class="sx-ait-q">{{ $item->question }}</td>
                                        <td><div class="sx-ait-answer small text-muted">{{ \Illuminate\Support\Str::limit($item->answer, 140) }}</div></td>
                                        <td><span class="sx-badge is-locale">{{ strtoupper($item->locale) }}</span></td>
                                        <td>{{ $item->priority }}</td>
                                        <td>
                                            @if($item->is_active)
                                                <span class="sx-badge is-active">{{ __('superadmin.ai_training.active') }}</span>
                                            @else
                                                <span class="sx-badge is-inactive">{{ __('superadmin.ai_training.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit"
                                                title="{{ __('superadmin.ai_training.edit') }}"
                                                data-id="{{ $item->id }}"
                                                data-question="{{ e($item->question) }}"
                                                data-answer="{{ e($item->answer) }}"
                                                data-locale="{{ $item->locale }}"
                                                data-keywords="{{ e($item->keywords) }}"
                                                data-priority="{{ $item->priority }}"
                                                data-active="{{ $item->is_active ? 1 : 0 }}">
                                                <i class="fi fi-rr-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                title="{{ __('superadmin.ai_training.delete') }}"
                                                data-id="{{ $item->id }}"
                                                data-question="{{ e($item->question) }}">
                                                <i class="fi fi-rr-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="qaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qaModalTitle">{{ __('superadmin.ai_training.add_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('superadmin.ai_training.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="qaId">
                <div class="mb-3">
                    <label class="form-label">{{ __('superadmin.ai_training.question') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="qa_question" placeholder="{{ __('superadmin.ai_training.question_placeholder') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('superadmin.ai_training.answer') }} <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="qa_answer" rows="5" placeholder="{{ __('superadmin.ai_training.answer_placeholder') }}"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('superadmin.ai_training.language') }}</label>
                        <select class="form-select" id="qa_locale">
                            <option value="both">{{ __('superadmin.ai_training.lang_both') }}</option>
                            <option value="en">{{ __('superadmin.ai_training.lang_en') }}</option>
                            <option value="sw">{{ __('superadmin.ai_training.lang_sw') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('superadmin.ai_training.priority') }}</label>
                        <input type="number" class="form-control" id="qa_priority" min="0" max="100" value="10">
                        <small class="text-muted">{{ __('superadmin.ai_training.priority_hint') }}</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('superadmin.ai_training.status') }}</label>
                        <select class="form-select" id="qa_active">
                            <option value="1">{{ __('superadmin.ai_training.active') }}</option>
                            <option value="0">{{ __('superadmin.ai_training.inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 mb-0">
                    <label class="form-label">{{ __('superadmin.ai_training.keywords') }}</label>
                    <input type="text" class="form-control" id="qa_keywords" placeholder="{{ __('superadmin.ai_training.keywords_placeholder') }}">
                    <small class="text-muted">{{ __('superadmin.ai_training.keywords_hint') }}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('superadmin.ai_training.cancel') }}</button>
                <button type="button" class="btn btn-primary waves-effect waves-light" id="btnSaveQa">
                    <i class="fi fi-rr-disk me-1"></i> {{ __('superadmin.ai_training.save_training') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const SX = @json(__('superadmin.ai_training'));
const SXC = @json(__('superadmin.common'));
function sxFmt(str, map) {
    return Object.keys(map || {}).sort(function (a, b) { return b.length - a.length; }).reduce(function (s, k) { return s.split(':' + k).join(map[k]); }, String(str));
}
$(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');
    let qaModal;

    if ($.fn.DataTable && $('#qaTable').length) {
        $('#qaTable').DataTable({
            pageLength: 10,
            ordering: true,
            autoWidth: false,
            order: [[4, 'desc']],
            columnDefs: [{ orderable: false, targets: [2, 6] }],
            language: {
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                zeroRecords: SXC.datatables_zero,
                search: SXC.search + ':',
                paginate: { previous: SXC.previous, next: SXC.next }
            },
            dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>t<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });
    }

    function getModal() {
        if (!qaModal) qaModal = new bootstrap.Modal(document.getElementById('qaModal'));
        return qaModal;
    }

    function decodeAttr(v) {
        const t = document.createElement('textarea');
        t.innerHTML = v || '';
        return t.value;
    }

    $('#btnOpenCreate').on('click', function () {
        $('#qaModalTitle').text(SX.js.add_title);
        $('#qaId').val('');
        $('#qa_question').val('');
        $('#qa_answer').val('');
        $('#qa_locale').val('both');
        $('#qa_keywords').val('');
        $('#qa_priority').val(10);
        $('#qa_active').val('1');
        getModal().show();
    });

    $(document).on('click', '.btn-edit', function () {
        const $b = $(this);
        $('#qaModalTitle').text(SX.js.edit_title);
        $('#qaId').val($b.data('id'));
        $('#qa_question').val(decodeAttr($b.attr('data-question')));
        $('#qa_answer').val(decodeAttr($b.attr('data-answer')));
        $('#qa_locale').val($b.data('locale') || 'both');
        $('#qa_keywords').val(decodeAttr($b.attr('data-keywords')));
        $('#qa_priority').val($b.data('priority') || 10);
        $('#qa_active').val(String($b.data('active')));
        getModal().show();
    });

    $('#btnSaveQa').on('click', function () {
        const id = $('#qaId').val();
        const payload = {
            question: $('#qa_question').val(),
            answer: $('#qa_answer').val(),
            locale: $('#qa_locale').val(),
            keywords: $('#qa_keywords').val(),
            priority: parseInt($('#qa_priority').val(), 10) || 10,
            is_active: $('#qa_active').val() === '1',
            _token: csrf
        };

        const url = id
            ? @json(url('super-admin/ai-training')) + '/' + id
            : @json(route('superadmin.ai_training.store'));
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: payload,
            success: function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.error || SX.js.save_failed);
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : SX.js.save_failed;
                alert(msg);
            }
        });
    });

    $(document).on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        const q = decodeAttr($(this).attr('data-question'));
        if (!confirm(sxFmt(SX.js.delete_confirm, { question: q }))) return;

        $.ajax({
            url: @json(url('super-admin/ai-training')) + '/' + id,
            method: 'DELETE',
            data: { _token: csrf },
            success: function (res) {
                if (res.success) location.reload();
                else alert(res.error || SX.js.delete_failed);
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : SX.js.delete_failed;
                alert(msg);
            }
        });
    });
});
</script>
@endpush
