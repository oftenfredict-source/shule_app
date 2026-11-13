@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.bank_account_verification'))
@section('page_breadcrumb', __('superadmin.pages.bank_account_verification'))

@section('page_actions')
    <a href="{{ route('superadmin.banks.index') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-bank me-1"></i> {{ __('superadmin.bank_verification.banks') }}
    </a>
    <a href="{{ route('superadmin.nbc_integration_docs') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-document me-1"></i> {{ __('superadmin.bank_verification.nbc_docs') }}
    </a>
@endsection

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav span:not(.sx-badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav a:not(.btn),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav .stat-label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-bav .stat-value {
        color: #000 !important;
    }
    .sx-bav .btn-primary,
    .sx-bav a.btn-primary,
    .sx-bav .btn-primary i,
    .sx-bav .btn-primary i:before,
    .sx-bav .btn-success,
    .sx-bav .btn-success i,
    .sx-bav .btn-success i:before,
    .sx-bav .text-white {
        color: #fff !important;
    }
    .sx-bav .sx-bav-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-bav .sx-bav-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-bav .sx-bav-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .sx-bav .sx-bav-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .sx-bav .sx-bav-stat .stat-ico {
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
    .sx-bav .sx-bav-stat .stat-ico i,
    .sx-bav .sx-bav-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-bav .sx-bav-stat.is-total .stat-ico { background: #fff0e8; color: #a34a00 !important; }
    .sx-bav .sx-bav-stat.is-pending .stat-ico { background: #fff6e5; color: #9a6b00 !important; }
    .sx-bav .sx-bav-stat.is-approved .stat-ico { background: #e8f7ec; color: #145c2a !important; }
    .sx-bav .sx-bav-stat.is-rejected .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-bav .sx-bav-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-bav .sx-bav-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-bav .sx-bav-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-bav .sx-bav-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .sx-bav .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-bav .form-select,
    .sx-bav .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
    }
    .sx-bav .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-bav .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-bav .btn-success {
        background: #145c2a !important;
        border-color: #145c2a !important;
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-bav .btn-outline-danger {
        border-radius: 0.45rem;
        border-color: #940000;
        color: #940000 !important;
        font-weight: 600;
    }
    .sx-bav .btn-outline-danger:hover {
        background: #fde8e8 !important;
        color: #940000 !important;
        border-color: #940000 !important;
    }
    .sx-bav .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: visible;
        padding: 0.75rem;
    }
    .sx-bav .table { margin: 0; vertical-align: middle; }
    .sx-bav .table thead th {
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
    .sx-bav .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
    }
    .sx-bav .table tbody tr:hover { background: #fffafa; }
    .sx-bav .sx-acc-no {
        font-weight: 700;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        color: #940000 !important;
    }
    .sx-bav .sx-bank-meta {
        font-size: 0.78rem;
        color: #666 !important;
    }
    .sx-bav .sx-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        border-radius: 0.3rem;
        line-height: 1;
    }
    .sx-bav .sx-badge-pending { background: #fff6e5; color: #9a6b00 !important; }
    .sx-bav .sx-badge-approved { background: #e8f7ec; color: #145c2a !important; }
    .sx-bav .sx-badge-rejected { background: #fde8e8; color: #940000 !important; }
    .sx-bav .sx-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #666 !important;
    }
    .sx-bav .sx-empty i {
        display: block;
        font-size: 2rem;
        color: #940000 !important;
        margin-bottom: 0.65rem;
    }
    .sx-bav .sx-empty i:before { color: inherit !important; }
    .sx-bav .select2-container--default .select2-selection--single {
        height: 40px;
        border-radius: 0.45rem !important;
        border: 1px solid #d9d9d9;
        display: flex;
        align-items: center;
    }
    .sx-bav .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .sx-bav .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
    .sx-bav .select2-container--default.select2-container--focus .select2-selection--single,
    .sx-bav .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-bav .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #940000;
        color: #fff;
    }
    .sx-bav .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 36px;
        padding: 0.25rem 0.65rem;
    }
    .sx-bav .dataTables_wrapper .dataTables_length select {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
        margin: 0 0.35rem;
    }
    .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-bav,
    html[data-bs-theme="dark"] .sx-bav,
    html[data-sx-theme="dark"] .sx-bav p,
    html[data-bs-theme="dark"] .sx-bav p,
    html[data-sx-theme="dark"] .sx-bav span:not(.sx-badge):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-bav span:not(.sx-badge):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-bav small,
    html[data-bs-theme="dark"] .sx-bav small,
    html[data-sx-theme="dark"] .sx-bav strong,
    html[data-bs-theme="dark"] .sx-bav strong,
    html[data-sx-theme="dark"] .sx-bav div,
    html[data-bs-theme="dark"] .sx-bav div,
    html[data-sx-theme="dark"] .sx-bav label,
    html[data-bs-theme="dark"] .sx-bav label,
    html[data-sx-theme="dark"] .sx-bav h1,
    html[data-sx-theme="dark"] .sx-bav h2,
    html[data-sx-theme="dark"] .sx-bav h3,
    html[data-sx-theme="dark"] .sx-bav h4,
    html[data-sx-theme="dark"] .sx-bav h5,
    html[data-sx-theme="dark"] .sx-bav h6,
    html[data-bs-theme="dark"] .sx-bav h1,
    html[data-bs-theme="dark"] .sx-bav h2,
    html[data-bs-theme="dark"] .sx-bav h3,
    html[data-bs-theme="dark"] .sx-bav h4,
    html[data-bs-theme="dark"] .sx-bav h5,
    html[data-bs-theme="dark"] .sx-bav h6,
    html[data-sx-theme="dark"] .sx-bav .sx-bav-card-title,
    html[data-bs-theme="dark"] .sx-bav .sx-bav-card-title,
    html[data-sx-theme="dark"] .sx-bav .stat-label,
    html[data-bs-theme="dark"] .sx-bav .stat-label,
    html[data-sx-theme="dark"] .sx-bav .stat-value,
    html[data-bs-theme="dark"] .sx-bav .stat-value,
    html[data-sx-theme="dark"] .sx-bav .table,
    html[data-bs-theme="dark"] .sx-bav .table,
    html[data-sx-theme="dark"] .sx-bav .table tbody td,
    html[data-bs-theme="dark"] .sx-bav .table tbody td,
    html[data-sx-theme="dark"] .sx-bav a:not(.btn),
    html[data-bs-theme="dark"] .sx-bav a:not(.btn) {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-bav .text-muted,
    html[data-bs-theme="dark"] .sx-bav .text-muted,
    html[data-sx-theme="dark"] .sx-bav .sx-bank-meta,
    html[data-bs-theme="dark"] .sx-bav .sx-bank-meta,
    html[data-sx-theme="dark"] .sx-bav .form-label,
    html[data-bs-theme="dark"] .sx-bav .form-label,
    html[data-sx-theme="dark"] .sx-bav .table thead th,
    html[data-bs-theme="dark"] .sx-bav .table thead th,
    html[data-sx-theme="dark"] .sx-bav .sx-empty,
    html[data-bs-theme="dark"] .sx-bav .sx-empty,
    html[data-sx-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_filter {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-bav-stat,
    html[data-bs-theme="dark"] .sx-bav .sx-bav-stat,
    html[data-sx-theme="dark"] .sx-bav .sx-bav-card,
    html[data-bs-theme="dark"] .sx-bav .sx-bav-card,
    html[data-sx-theme="dark"] .sx-bav .card,
    html[data-bs-theme="dark"] .sx-bav .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-bav .sx-bav-card-head,
    html[data-bs-theme="dark"] .sx-bav .sx-bav-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-bav .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-bav .table thead th,
    html[data-bs-theme="dark"] .sx-bav .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-bav .table tbody td,
    html[data-bs-theme="dark"] .sx-bav .table tbody td {
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-bav .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-bav .table tbody tr:hover {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    html[data-sx-theme="dark"] .sx-bav .form-control,
    html[data-bs-theme="dark"] .sx-bav .form-control,
    html[data-sx-theme="dark"] .sx-bav .form-select,
    html[data-bs-theme="dark"] .sx-bav .form-select,
    html[data-sx-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-bav .select2-container--default .select2-selection--single,
    html[data-bs-theme="dark"] .sx-bav .select2-container--default .select2-selection--single {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-bav .select2-container--default .select2-selection--single .select2-selection__rendered,
    html[data-bs-theme="dark"] .sx-bav .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-acc-no,
    html[data-bs-theme="dark"] .sx-bav .sx-acc-no {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-empty i,
    html[data-bs-theme="dark"] .sx-bav .sx-empty i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-bav .btn-outline-danger,
    html[data-bs-theme="dark"] .sx-bav .btn-outline-danger {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
    }
    html[data-sx-theme="dark"] .sx-bav .btn-outline-danger:hover,
    html[data-bs-theme="dark"] .sx-bav .btn-outline-danger:hover {
        background: rgba(148, 0, 0, 0.22) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-bav .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-badge-pending,
    html[data-bs-theme="dark"] .sx-bav .sx-badge-pending {
        background: rgba(154, 107, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-badge-approved,
    html[data-bs-theme="dark"] .sx-bav .sx-badge-approved {
        background: rgba(20, 92, 42, 0.35) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-badge-rejected,
    html[data-bs-theme="dark"] .sx-bav .sx-badge-rejected {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-bav-stat.is-total .stat-ico,
    html[data-bs-theme="dark"] .sx-bav .sx-bav-stat.is-total .stat-ico {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-bav-stat.is-pending .stat-ico,
    html[data-bs-theme="dark"] .sx-bav .sx-bav-stat.is-pending .stat-ico {
        background: rgba(154, 107, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-bav-stat.is-approved .stat-ico,
    html[data-bs-theme="dark"] .sx-bav .sx-bav-stat.is-approved .stat-ico {
        background: rgba(20, 92, 42, 0.28) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-bav .sx-bav-stat.is-rejected .stat-ico,
    html[data-bs-theme="dark"] .sx-bav .sx-bav-stat.is-rejected .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-bav .btn-primary,
    html[data-bs-theme="dark"] .sx-bav .btn-primary,
    html[data-sx-theme="dark"] .sx-bav .btn-success,
    html[data-bs-theme="dark"] .sx-bav .btn-success {
        color: #fff !important;
    }
</style>

<div class="sx-bav">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-bav-stat is-total h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.bank_verification.loaded') }}</span>
                        <span class="stat-value" id="statTotal">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-bank"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-bav-stat is-pending h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.bank_verification.pending') }}</span>
                        <span class="stat-value" id="statPending">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-clock"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-bav-stat is-approved h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.bank_verification.approved') }}</span>
                        <span class="stat-value" id="statApproved">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-check-circle"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-bav-stat is-rejected h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.bank_verification.rejected') }}</span>
                        <span class="stat-value" id="statRejected">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-cross-circle"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-bav-card mb-3">
        <div class="card-body">
            <div class="sx-bav-card-head">
                <h6 class="sx-bav-card-title">{{ __('superadmin.bank_verification.select_school') }}</h6>
                <span class="small text-muted">{{ __('superadmin.bank_verification.select_school_desc') }}</span>
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label" for="filterSchool">{{ __('superadmin.bank_verification.school') }}</label>
                    <select class="form-select" id="filterSchool">
                        <option value="">{{ __('superadmin.bank_verification.select_school_option') }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}" data-phone="{{ $school->phone }}">
                                {{ $school->school_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-primary waves-effect waves-light w-100" id="btnLoadAccounts">
                        <i class="fi fi-rr-search me-1"></i> {{ __('superadmin.bank_verification.load_accounts') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-bav-card">
        <div class="card-body">
            <div class="sx-bav-card-head">
                <h6 class="sx-bav-card-title">{{ __('superadmin.bank_verification.school_accounts') }}</h6>
            </div>
            <div class="sx-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="accountsTable">
                        <thead>
                            <tr>
                                <th>{{ __('superadmin.bank_verification.th_account_number') }}</th>
                                <th>{{ __('superadmin.bank_verification.th_account_name') }}</th>
                                <th>{{ __('superadmin.bank_verification.th_bank') }}</th>
                                <th>{{ __('superadmin.bank_verification.th_status') }}</th>
                                <th>{{ __('superadmin.bank_verification.th_otp_verified') }}</th>
                                <th>{{ __('superadmin.bank_verification.th_notes') }}</th>
                                <th class="text-end">{{ __('superadmin.bank_verification.th_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7">
                                    <div class="sx-empty">
                                        <i class="fi fi-rr-info"></i>
                                        {{ __('superadmin.bank_verification.select_to_load') }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    const SX = @json(__('superadmin.bank_verification'));
    const SXC = @json(__('superadmin.common'));
    const csrf = $('meta[name="csrf-token"]').attr('content');
    let accountsDataTable = null;

    $('#filterSchool').select2({
        width: '100%',
        placeholder: SX.js.search_select_school,
        allowClear: true,
        dropdownParent: $('.sx-bav')
    });

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function destroyAccountsDataTable() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#accountsTable')) {
            $('#accountsTable').DataTable().destroy();
        }
        accountsDataTable = null;
    }

    function setAccountsBody(html) {
        destroyAccountsDataTable();
        $('#accountsTable tbody').html(html);
    }

    function emptyRow(message, icon) {
        return `
            <tr>
                <td colspan="7">
                    <div class="sx-empty">
                        <i class="fi ${icon || 'fi-rr-info'}"></i>
                        ${escapeHtml(message)}
                    </div>
                </td>
            </tr>
        `;
    }

    function resetStats() {
        $('#statTotal').text('0');
        $('#statPending').text('0');
        $('#statApproved').text('0');
        $('#statRejected').text('0');
    }

    function updateStats(accounts) {
        const list = accounts || [];
        $('#statTotal').text(list.length);
        $('#statPending').text(list.filter(a => a.status === 'pending').length);
        $('#statApproved').text(list.filter(a => a.status === 'approved').length);
        $('#statRejected').text(list.filter(a => a.status === 'rejected').length);
    }

    function statusBadge(status) {
        if (status === 'approved') return '<span class="sx-badge sx-badge-approved">' + escapeHtml(SX.js.approved) + '</span>';
        if (status === 'rejected') return '<span class="sx-badge sx-badge-rejected">' + escapeHtml(SX.js.rejected) + '</span>';
        return '<span class="sx-badge sx-badge-pending">' + escapeHtml(SX.js.pending) + '</span>';
    }

    function initAccountsDataTable() {
        destroyAccountsDataTable();
        if (!$.fn.DataTable) return;

        accountsDataTable = $('#accountsTable').DataTable({
            paging: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, SXC.all]],
            ordering: true,
            info: true,
            searching: true,
            autoWidth: false,
            columnDefs: [{ orderable: false, targets: [6] }],
            language: {
                search: '_INPUT_',
                searchPlaceholder: SX.js.search_placeholder,
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                zeroRecords: SX.js.no_matching,
                paginate: { previous: SXC.previous, next: SXC.next }
            },
            dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>t<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });
    }

    function loadAccounts() {
        const schoolID = $('#filterSchool').val();
        if (!schoolID) {
            resetStats();
            setAccountsBody(emptyRow(SX.js.select_to_load, 'fi-rr-info'));
            Swal.fire(SX.js.select_school, SX.js.select_school_first, 'info');
            return;
        }

        setAccountsBody(emptyRow(SX.js.loading_accounts, 'fi-rr-spinner'));

        $.get("{{ route('superadmin.bank_accounts.list') }}", { schoolID: schoolID })
            .done(function (res) {
                if (!res.success) {
                    resetStats();
                    setAccountsBody(emptyRow(res.error || SX.js.load_failed, 'fi-rr-exclamation'));
                    return;
                }

                const accounts = res.accounts || [];
                updateStats(accounts);

                if (!accounts.length) {
                    setAccountsBody(emptyRow(SX.js.no_accounts, 'fi-rr-bank'));
                    return;
                }

                let html = '';
                accounts.forEach(function (a) {
                    let actions = '—';
                    if (a.status === 'pending' || a.status === 'rejected') {
                        actions =
                            '<button type="button" class="btn btn-sm btn-success me-1 btn-approve" data-id="' + a.accountID + '">' +
                                '<i class="fi fi-rr-check me-1"></i>' + escapeHtml(SX.js.approve) +
                            '</button>' +
                            '<button type="button" class="btn btn-sm btn-outline-danger btn-reject" data-id="' + a.accountID + '">' +
                                '<i class="fi fi-rr-cross me-1"></i>' + escapeHtml(SX.js.reject) +
                            '</button>';
                    } else if (a.status === 'approved') {
                        actions =
                            '<button type="button" class="btn btn-sm btn-outline-danger btn-reject" data-id="' + a.accountID + '">' +
                                '<i class="fi fi-rr-cross me-1"></i>' + escapeHtml(SX.js.reject) +
                            '</button>';
                    }

                    const notes = (a.status === 'rejected' && a.rejection_reason)
                        ? '<span class="text-danger">' + escapeHtml(a.rejection_reason) + '</span>'
                        : escapeHtml(a.reviewed_at || '—');

                    html +=
                        '<tr>' +
                            '<td><div class="sx-acc-no">' + escapeHtml(a.account_number) + '</div></td>' +
                            '<td>' + escapeHtml(a.account_name || '—') + '</td>' +
                            '<td>' +
                                '<div>' + escapeHtml(a.bank_name) + '</div>' +
                                (a.bank_code ? '<div class="sx-bank-meta">' + escapeHtml(a.bank_code) + '</div>' : '') +
                            '</td>' +
                            '<td>' + statusBadge(a.status) + '</td>' +
                            '<td>' + escapeHtml(a.otp_verified_at || '—') + '</td>' +
                            '<td class="small">' + notes + '</td>' +
                            '<td class="text-end text-nowrap">' + actions + '</td>' +
                        '</tr>';
                });

                setAccountsBody(html);
                initAccountsDataTable();
            })
            .fail(function () {
                resetStats();
                setAccountsBody(emptyRow(SX.js.load_failed, 'fi-rr-exclamation'));
            });
    }

    $('#btnLoadAccounts').on('click', loadAccounts);
    $('#filterSchool').on('change', function () {
        if ($(this).val()) loadAccounts();
        else {
            resetStats();
            setAccountsBody(emptyRow(SX.js.select_to_load, 'fi-rr-info'));
        }
    });

    $(document).on('click', '.btn-approve', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: SX.js.approve_title,
            text: SX.js.approve_text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#145c2a',
            confirmButtonText: SX.js.approve,
            cancelButtonText: SX.js.cancel
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post("{{ url('super-admin/bank-accounts') }}/" + id + "/approve", { _token: csrf }, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: SX.js.approved_title, text: res.message, timer: 1600, showConfirmButton: false });
                    loadAccounts();
                } else {
                    Swal.fire(SX.js.error, res.error || SX.js.failed, 'error');
                }
            }).fail(function (xhr) {
                Swal.fire(SX.js.error, xhr.responseJSON?.error || SX.js.request_failed, 'error');
            });
        });
    });

    $(document).on('click', '.btn-reject', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: SX.js.reject_title,
            input: 'textarea',
            inputLabel: SX.js.reject_label,
            inputPlaceholder: SX.js.reject_placeholder,
            inputAttributes: { 'aria-label': SX.js.reject_reason },
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: SX.js.reject,
            cancelButtonText: SX.js.cancel,
            inputValidator: (value) => {
                if (!value || !value.trim()) return SX.js.reject_required;
            }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post("{{ url('super-admin/bank-accounts') }}/" + id + "/reject", {
                _token: csrf,
                rejection_reason: result.value
            }, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: SX.js.rejected_title, text: res.message, timer: 1600, showConfirmButton: false });
                    loadAccounts();
                } else {
                    Swal.fire(SX.js.error, res.error || SX.js.failed, 'error');
                }
            }).fail(function (xhr) {
                Swal.fire(SX.js.error, xhr.responseJSON?.error || SX.js.request_failed, 'error');
            });
        });
    });
});
</script>
@endpush
