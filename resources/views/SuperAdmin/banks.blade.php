@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.bank_registration'))
@section('page_breadcrumb', __('superadmin.pages.bank_registration'))

@section('page_actions')
    <a href="{{ route('superadmin.bank_accounts.verification') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-shield-check me-1"></i> {{ __('superadmin.banks.account_verification') }}
    </a>
    <button type="button" class="btn btn-primary waves-effect waves-light" id="btnOpenCreateBank">
        <i class="fi fi-rr-add me-1"></i> {{ __('superadmin.banks.register_bank') }}
    </button>
@endsection

@section('content')
@php
    $totalBanks = $banks->count();
    $activeBanks = $banks->where('status', 'Active')->count();
    $inactiveBanks = $banks->where('status', 'Inactive')->count();
@endphp

<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks span:not(.sx-badge):not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks a:not(.btn),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks .stat-label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks .stat-value,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-banks .sx-bank-name {
        color: #000 !important;
    }
    .sx-banks .btn-primary,
    .sx-banks a.btn-primary,
    .sx-banks .btn-primary i,
    .sx-banks .btn-primary i:before,
    .sx-banks .text-white {
        color: #fff !important;
    }
    .sx-banks .sx-banks-stat {
        display: block;
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-banks .sx-banks-stat .card-body {
        padding: 1rem 1.15rem 1.1rem;
    }
    .sx-banks .sx-banks-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .sx-banks .sx-banks-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }
    .sx-banks .sx-banks-stat .stat-ico {
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
    .sx-banks .sx-banks-stat .stat-ico i,
    .sx-banks .sx-banks-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-banks .sx-banks-stat.is-total .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-banks .sx-banks-stat.is-active .stat-ico { background: #e8f7ec; color: #145c2a !important; }
    .sx-banks .sx-banks-stat.is-inactive .stat-ico { background: #f1f1f1; color: #5c5c5c !important; }
    .sx-banks .sx-banks-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-banks .sx-banks-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-banks .sx-banks-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-banks .sx-banks-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .sx-banks .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-banks .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-banks .btn-outline-action {
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #444 !important;
        border-radius: 0.45rem;
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .sx-banks .btn-outline-action:hover {
        border-color: #940000;
        color: #940000 !important;
        background: #fff5f5;
    }
    .sx-banks .btn-outline-action.text-danger:hover {
        background: #fde8e8;
        color: #940000 !important;
    }
    .sx-banks .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: visible;
        padding: 0.75rem;
    }
    .sx-banks .table { margin: 0; vertical-align: middle; }
    .sx-banks .table thead th {
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
    .sx-banks .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
    }
    .sx-banks .table tbody tr:hover { background: #fffafa; }
    .sx-banks .sx-bank-name {
        font-weight: 700;
    }
    .sx-banks .sx-bank-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 0.86rem;
        color: #555 !important;
    }
    .sx-banks .sx-badge {
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
    .sx-banks .sx-badge-on { background: #e8f7ec; color: #145c2a !important; }
    .sx-banks .sx-badge-off { background: #f1f1f1; color: #555 !important; }
    .sx-banks .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-banks .form-control,
    .sx-banks .form-select {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
    }
    .sx-banks .form-control:focus,
    .sx-banks .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-banks .modal-content {
        border-radius: 0;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
    }
    .sx-banks .modal-header {
        background: #fafafa;
        border-bottom: 1px solid #eee;
    }
    .sx-banks .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #940000 !important;
    }
    .sx-banks .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 36px;
        padding: 0.25rem 0.65rem;
    }
    .sx-banks .dataTables_wrapper .dataTables_length select {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
        margin: 0 0.35rem;
    }
    .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-banks,
    html[data-bs-theme="dark"] .sx-banks,
    html[data-sx-theme="dark"] .sx-banks p,
    html[data-bs-theme="dark"] .sx-banks p,
    html[data-sx-theme="dark"] .sx-banks span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-banks span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-banks small,
    html[data-bs-theme="dark"] .sx-banks small,
    html[data-sx-theme="dark"] .sx-banks strong,
    html[data-bs-theme="dark"] .sx-banks strong,
    html[data-sx-theme="dark"] .sx-banks div,
    html[data-bs-theme="dark"] .sx-banks div,
    html[data-sx-theme="dark"] .sx-banks label,
    html[data-bs-theme="dark"] .sx-banks label,
    html[data-sx-theme="dark"] .sx-banks h1,
    html[data-sx-theme="dark"] .sx-banks h2,
    html[data-sx-theme="dark"] .sx-banks h3,
    html[data-sx-theme="dark"] .sx-banks h4,
    html[data-sx-theme="dark"] .sx-banks h5,
    html[data-sx-theme="dark"] .sx-banks h6,
    html[data-bs-theme="dark"] .sx-banks h1,
    html[data-bs-theme="dark"] .sx-banks h2,
    html[data-bs-theme="dark"] .sx-banks h3,
    html[data-bs-theme="dark"] .sx-banks h4,
    html[data-bs-theme="dark"] .sx-banks h5,
    html[data-bs-theme="dark"] .sx-banks h6,
    html[data-sx-theme="dark"] .sx-banks .sx-banks-card-title,
    html[data-bs-theme="dark"] .sx-banks .sx-banks-card-title,
    html[data-sx-theme="dark"] .sx-banks .stat-label,
    html[data-bs-theme="dark"] .sx-banks .stat-label,
    html[data-sx-theme="dark"] .sx-banks .stat-value,
    html[data-bs-theme="dark"] .sx-banks .stat-value,
    html[data-sx-theme="dark"] .sx-banks .sx-bank-name,
    html[data-bs-theme="dark"] .sx-banks .sx-bank-name,
    html[data-sx-theme="dark"] .sx-banks .table,
    html[data-bs-theme="dark"] .sx-banks .table,
    html[data-sx-theme="dark"] .sx-banks .table tbody td,
    html[data-bs-theme="dark"] .sx-banks .table tbody td,
    html[data-sx-theme="dark"] .sx-banks a:not(.btn),
    html[data-bs-theme="dark"] .sx-banks a:not(.btn) {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-banks .text-muted,
    html[data-bs-theme="dark"] .sx-banks .text-muted,
    html[data-sx-theme="dark"] .sx-banks .sx-bank-code,
    html[data-bs-theme="dark"] .sx-banks .sx-bank-code,
    html[data-sx-theme="dark"] .sx-banks .form-label,
    html[data-bs-theme="dark"] .sx-banks .form-label,
    html[data-sx-theme="dark"] .sx-banks .table thead th,
    html[data-bs-theme="dark"] .sx-banks .table thead th,
    html[data-sx-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_filter {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-banks .sx-banks-stat,
    html[data-bs-theme="dark"] .sx-banks .sx-banks-stat,
    html[data-sx-theme="dark"] .sx-banks .sx-banks-card,
    html[data-bs-theme="dark"] .sx-banks .sx-banks-card,
    html[data-sx-theme="dark"] .sx-banks .card,
    html[data-bs-theme="dark"] .sx-banks .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-banks .sx-banks-card-head,
    html[data-bs-theme="dark"] .sx-banks .sx-banks-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-banks .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-banks .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-banks .table thead th,
    html[data-bs-theme="dark"] .sx-banks .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-banks .table tbody td,
    html[data-bs-theme="dark"] .sx-banks .table tbody td {
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-banks .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-banks .table tbody tr:hover {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    html[data-sx-theme="dark"] .sx-banks .form-control,
    html[data-bs-theme="dark"] .sx-banks .form-control,
    html[data-sx-theme="dark"] .sx-banks .form-select,
    html[data-bs-theme="dark"] .sx-banks .form-select,
    html[data-sx-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-banks .btn-outline-action,
    html[data-bs-theme="dark"] .sx-banks .btn-outline-action {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-banks .btn-outline-action:hover,
    html[data-bs-theme="dark"] .sx-banks .btn-outline-action:hover {
        background: rgba(148, 0, 0, 0.22) !important;
        border-color: #940000 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-banks .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-banks .sx-badge-on,
    html[data-bs-theme="dark"] .sx-banks .sx-badge-on {
        background: rgba(20, 92, 42, 0.35) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-banks .sx-badge-off,
    html[data-bs-theme="dark"] .sx-banks .sx-badge-off {
        background: #3a2a2e !important;
        color: #c9b4b8 !important;
    }
    html[data-sx-theme="dark"] .sx-banks .sx-banks-stat.is-total .stat-ico,
    html[data-bs-theme="dark"] .sx-banks .sx-banks-stat.is-total .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-banks .sx-banks-stat.is-active .stat-ico,
    html[data-bs-theme="dark"] .sx-banks .sx-banks-stat.is-active .stat-ico {
        background: rgba(20, 92, 42, 0.28) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-banks .sx-banks-stat.is-inactive .stat-ico,
    html[data-bs-theme="dark"] .sx-banks .sx-banks-stat.is-inactive .stat-ico {
        background: #3a2a2e !important;
        color: #c9b4b8 !important;
    }
    html[data-sx-theme="dark"] .sx-banks .modal-content,
    html[data-bs-theme="dark"] .sx-banks .modal-content {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-banks .modal-header,
    html[data-bs-theme="dark"] .sx-banks .modal-header,
    html[data-sx-theme="dark"] .sx-banks .modal-footer,
    html[data-bs-theme="dark"] .sx-banks .modal-footer {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-banks .modal-title,
    html[data-bs-theme="dark"] .sx-banks .modal-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-banks .btn-primary,
    html[data-bs-theme="dark"] .sx-banks .btn-primary {
        color: #fff !important;
    }
</style>

<div class="sx-banks">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card sx-banks-stat is-total h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.banks.total_banks') }}</span>
                        <span class="stat-value">{{ $totalBanks }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-bank"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card sx-banks-stat is-active h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.banks.active') }}</span>
                        <span class="stat-value">{{ $activeBanks }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-check-circle"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card sx-banks-stat is-inactive h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.banks.inactive') }}</span>
                        <span class="stat-value">{{ $inactiveBanks }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-ban"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-banks-card">
        <div class="card-body">
            <div class="sx-banks-card-head">
                <h6 class="sx-banks-card-title">{{ __('superadmin.banks.registered_banks') }}</h6>
                <span class="small text-muted">{{ __('superadmin.banks.registered_banks_desc') }}</span>
            </div>
            <div class="sx-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="banksTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('superadmin.banks.th_bank_name') }}</th>
                                <th>{{ __('superadmin.banks.th_bank_code') }}</th>
                                <th>{{ __('superadmin.banks.th_status') }}</th>
                                <th class="text-end">{{ __('superadmin.banks.th_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banks as $i => $bank)
                                <tr data-bank-id="{{ $bank->bankID }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td><div class="sx-bank-name">{{ $bank->bank_name }}</div></td>
                                    <td><span class="sx-bank-code">{{ $bank->bank_code ?: '—' }}</span></td>
                                    <td>
                                        @if($bank->status === 'Active')
                                            <span class="sx-badge sx-badge-on">{{ __('superadmin.banks.active') }}</span>
                                        @else
                                            <span class="sx-badge sx-badge-off">{{ __('superadmin.banks.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-outline-action btn-edit-bank"
                                                title="{{ __('superadmin.banks.edit') }}"
                                                data-id="{{ $bank->bankID }}"
                                                data-name="{{ $bank->bank_name }}"
                                                data-code="{{ $bank->bank_code }}"
                                                data-status="{{ $bank->status }}">
                                                <i class="fi fi-rr-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-action text-danger btn-delete-bank"
                                                title="{{ __('superadmin.banks.delete') }}"
                                                data-id="{{ $bank->bankID }}"
                                                data-name="{{ $bank->bank_name }}">
                                                <i class="fi fi-rr-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('superadmin.banks.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bankModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bankModalTitle">{{ __('superadmin.banks.register_bank') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('superadmin.common.close') }}"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="bankID">
                    <div class="mb-3">
                        <label class="form-label" for="bank_name">{{ __('superadmin.banks.bank_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bank_name" placeholder="{{ __('superadmin.banks.bank_name_placeholder') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="bank_code">{{ __('superadmin.banks.bank_code') }}</label>
                        <input type="text" class="form-control" id="bank_code" placeholder="{{ __('superadmin.banks.bank_name_placeholder') }}">
                        <small class="text-muted d-block mt-1">{{ __('superadmin.banks.bank_code_hint') }}</small>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="bank_status">{{ __('superadmin.banks.status') }}</label>
                        <select class="form-select" id="bank_status">
                            <option value="Active">{{ __('superadmin.banks.active') }}</option>
                            <option value="Inactive">{{ __('superadmin.banks.inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('superadmin.banks.cancel') }}</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="btnSaveBank">{{ __('superadmin.banks.save') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const SX = @json(__('superadmin.banks'));
    const SXC = @json(__('superadmin.common'));
    const csrf = $('meta[name="csrf-token"]').attr('content');
    let bankModal = null;
    let banksDataTable = null;

    function getModal() {
        if (!bankModal) bankModal = new bootstrap.Modal(document.getElementById('bankModal'));
        return bankModal;
    }

    if ($.fn.DataTable && $('#banksTable tbody tr td[colspan]').length === 0) {
        banksDataTable = $('#banksTable').DataTable({
            paging: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, SXC.all]],
            ordering: true,
            info: true,
            searching: true,
            autoWidth: false,
            columnDefs: [{ orderable: false, targets: [4] }],
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

    $('#btnOpenCreateBank').on('click', function () {
        $('#bankModalTitle').text(SX.js.register_bank);
        $('#bankID').val('');
        $('#bank_name').val('');
        $('#bank_code').val('');
        $('#bank_status').val('Active');
        getModal().show();
    });

    $(document).on('click', '.btn-edit-bank', function () {
        $('#bankModalTitle').text(SX.js.edit_bank);
        $('#bankID').val($(this).data('id'));
        $('#bank_name').val($(this).data('name'));
        $('#bank_code').val($(this).data('code') || '');
        $('#bank_status').val($(this).data('status'));
        getModal().show();
    });

    $('#btnSaveBank').on('click', function () {
        const id = $('#bankID').val();
        const payload = {
            bank_name: $('#bank_name').val(),
            bank_code: $('#bank_code').val(),
            status: $('#bank_status').val(),
            _token: csrf
        };
        const url = id
            ? "{{ url('super-admin/banks') }}/" + id
            : "{{ route('superadmin.banks.store') }}";
        if (id) payload._method = 'PUT';

        const $btn = $(this).prop('disabled', true);
        $.ajax({
            url: url,
            method: 'POST',
            data: payload,
            success: function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: SX.js.done, text: res.message, timer: 1600, showConfirmButton: false });
                    setTimeout(() => location.reload(), 900);
                } else {
                    Swal.fire(SX.js.error, res.error || SX.js.failed, 'error');
                }
            },
            error: function (xhr) {
                Swal.fire(SX.js.error, xhr.responseJSON?.error || SX.js.request_failed, 'error');
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    $(document).on('click', '.btn-delete-bank', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        Swal.fire({
            title: SX.js.delete_title,
            text: SX.js.delete_text.replace(':name', name),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: SX.js.yes_delete,
            cancelButtonText: SX.js.cancel
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: "{{ url('super-admin/banks') }}/" + id,
                method: 'POST',
                data: { _token: csrf, _method: 'DELETE' },
                success: function (res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: SX.js.deleted, timer: 1200, showConfirmButton: false });
                        setTimeout(() => location.reload(), 800);
                    } else {
                        Swal.fire(SX.js.error, res.error || SX.js.failed, 'error');
                    }
                },
                error: function (xhr) {
                    Swal.fire(SX.js.error, xhr.responseJSON?.error || SX.js.request_failed, 'error');
                }
            });
        });
    });
});
</script>
@endpush
