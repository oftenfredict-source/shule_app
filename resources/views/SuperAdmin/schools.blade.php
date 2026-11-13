@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.registered_schools'))
@section('page_breadcrumb', __('superadmin.pages.registered_schools'))

@section('page_actions')
    <button type="button" class="btn btn-primary waves-effect waves-light" id="btnDisableSmsAll" title="{{ __('superadmin.schools.disable_sms_title') }}">
        <i class="fi fi-rr-comment-xmark me-1"></i> {{ __('superadmin.schools.disable_sms') }}
    </button>
    <button type="button" class="btn btn-primary waves-effect waves-light" id="btnEnableSmsAll" title="{{ __('superadmin.schools.enable_sms_title') }}">
        <i class="fi fi-rr-comment-check me-1"></i> {{ __('superadmin.schools.enable_sms') }}
    </button>
    <a href="{{ route('superadmin.schools.registration_requests') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-document me-1"></i> {{ __('superadmin.schools.requests') }}
    </a>
    <a href="{{ route('superadmin.schools.register') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-add me-1"></i> {{ __('superadmin.schools.register_school') }}
    </a>
@endsection

@section('content')
<style>
    .sx-sch { color: #000; }
    .sx-sch .sx-sch-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-sch .sx-sch-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-sch .sx-sch-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-sch .sx-sch-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-sch .sx-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .sx-sch .sx-search {
        min-width: 220px;
        max-width: 320px;
        flex: 1;
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
    }
    .sx-sch .sx-search:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-sch .sx-view-toggle {
        display: inline-flex;
        border: 1px solid #e0e0e0;
        border-radius: 0.45rem;
        overflow: hidden;
    }
    .sx-sch .sx-view-btn {
        border: 0;
        background: #fff;
        color: #444;
        padding: 0.45rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        cursor: pointer;
    }
    .sx-sch .sx-view-btn + .sx-view-btn { border-left: 1px solid #e0e0e0; }
    .sx-sch .sx-view-btn.is-active {
        background: #940000;
        color: #fff !important;
    }
    .sx-sch .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: visible;
    }
    .sx-sch .sx-table-wrap .table-responsive {
        overflow-x: auto;
        overflow-y: visible;
    }
    .sx-sch .table { margin: 0; color: #000; vertical-align: middle; }
    .sx-sch .table thead th {
        background: #f7f7f7;
        border-bottom: 1px solid #e6e6e6;
        border-top: 0;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555;
        white-space: nowrap;
        padding: 0.75rem 0.9rem;
    }
    .sx-sch .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
    }
    .sx-sch .table tbody tr:hover { background: #fffafa; }
    .sx-sch .school-logo-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #e0e0e0;
        background: #fff;
    }
    .sx-sch .sx-school-name {
        font-weight: 600;
        color: #000;
        line-height: 1.25;
    }
    .sx-sch .sx-meta {
        font-size: 0.78rem;
        color: #666;
        margin-top: 0.15rem;
        font-weight: 400;
    }
    .sx-sch .sx-badge {
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
    .sx-sch .sx-badge-students {
        background: #fde8e8;
        color: #940000 !important;
        font-size: 0.86rem;
        font-weight: 600;
        min-width: 2.4rem;
        justify-content: center;
    }
    .sx-sch .sx-badge-on { background: #e8f7ec; color: #145c2a !important; }
    .sx-sch .sx-badge-off { background: #fde8e8; color: #940000 !important; }
    .sx-sch .sx-dots-btn {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #444;
        border-radius: 0.45rem;
    }
    .sx-sch .sx-dots-btn:hover,
    .sx-sch .sx-dots-btn:focus,
    .sx-sch .sx-dots-btn.show {
        border-color: #940000;
        color: #940000;
        background: #fff5f5;
    }
    .sx-sch .sx-dots-btn i {
        font-size: 1.05rem;
        line-height: 1;
    }
    .sx-sch .sx-action-menu .dropdown-menu {
        min-width: 180px;
        border-radius: 0.45rem;
        padding: 0.35rem;
        z-index: 1080;
    }
    .sx-sch .sx-action-menu .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.15rem;
        border-radius: 0.35rem;
        font-size: 0.86rem;
        font-weight: 500;
        color: #222;
        padding: 0.5rem 0.7rem;
    }
    .sx-sch .sx-action-menu .dropdown-item:hover {
        background: #fff5f5;
        color: #940000;
    }
    .sx-sch .sx-action-menu .dropdown-item.text-danger {
        color: #940000 !important;
    }
    .sx-sch .sx-action-menu .dropdown-item.text-danger:hover {
        background: #fde8e8;
    }
    .sx-sch .sx-action-menu .dropdown-item i {
        width: 1.1rem;
        text-align: center;
    }
    .sx-sch .btn-primary,
    .sx-sch .btn-primary-custom {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-sch .btn-outline-secondary {
        border-radius: 0.45rem;
        font-weight: 500;
    }
    .sx-sch .sx-grid {
        display: none;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.9rem;
    }
    .sx-sch.is-grid .sx-list-view { display: none; }
    .sx-sch.is-grid .sx-grid { display: grid; }
    .sx-sch.is-list .sx-list-view { display: block; }
    .sx-sch.is-list .sx-grid { display: none; }
    .sx-sch .sx-grid-card {
        border: 1px solid #ececec;
        background: #fff;
        border-top: 3px solid #940000;
        border-radius: 0.45rem;
        padding: 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }
    .sx-sch .sx-grid-card-top {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    .sx-sch .sx-grid-rows {
        display: grid;
        gap: 0.4rem;
        flex: 1;
    }
    .sx-sch .sx-grid-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.86rem;
    }
    .sx-sch .sx-grid-row span:first-child {
        color: #777;
        font-weight: 500;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sx-sch .sx-grid-row span:last-child {
        font-weight: 500;
        text-align: right;
        color: #000;
    }
    .sx-sch .sx-empty {
        text-align: center;
        padding: 2.25rem 1rem;
        color: #666;
        font-weight: 400;
    }
    .sx-sch .is-hidden-item { display: none !important; }
    .sx-sch .modal-content {
        border-radius: 0;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        overflow: hidden;
    }
    .sx-sch .modal-header {
        background: #fff;
        border-bottom: 1px solid #eee;
    }
    .sx-sch .modal-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #000;
    }
    .sx-sch #viewMoreModal .modal-dialog {
        max-width: 920px;
    }
    .sx-sch #viewMoreModal .modal-body {
        background: #f7f7f7;
        padding: 1.15rem;
    }
    .sx-sch .sx-detail-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
        background: #fff;
        border: 1px solid #ececec;
        border-left: 4px solid #940000;
        border-radius: 0.45rem;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }
    .sx-sch .sx-detail-logo {
        width: 72px;
        height: 72px;
        border-radius: 0.5rem;
        object-fit: cover;
        border: 1px solid #e6e6e6;
        background: #fff;
        flex-shrink: 0;
    }
    .sx-sch .sx-detail-hero-main {
        flex: 1;
        min-width: 180px;
    }
    .sx-sch .sx-detail-hero-main h6 {
        margin: 0 0 0.25rem;
        font-size: 1.15rem;
        font-weight: 600;
        color: #111;
        line-height: 1.3;
    }
    .sx-sch .sx-detail-hero-main .sx-detail-reg {
        display: inline-block;
        font-size: 0.78rem;
        color: #666;
        margin-bottom: 0.45rem;
    }
    .sx-sch .sx-detail-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .sx-sch .sx-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.22rem 0.5rem;
        border-radius: 0.3rem;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        background: #f3f3f3;
        color: #444;
    }
    .sx-sch .sx-chip-live { background: #e8f7ec; color: #145c2a; }
    .sx-sch .sx-chip-demo { background: #fff4d6; color: #8a5b00; }
    .sx-sch .sx-chip-active { background: #e8f7ec; color: #145c2a; }
    .sx-sch .sx-chip-inactive { background: #fde8e8; color: #940000; }
    .sx-sch .sx-detail-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
        margin-bottom: 1rem;
    }
    .sx-sch .sx-stat {
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        padding: 0.75rem 0.85rem;
        text-align: center;
    }
    .sx-sch .sx-stat-num {
        display: block;
        font-size: 1.2rem;
        font-weight: 600;
        color: #940000;
        line-height: 1.2;
    }
    .sx-sch .sx-stat-label {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.7rem;
        font-weight: 500;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #777;
    }
    .sx-sch .sx-detail-sections {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }
    .sx-sch .sx-detail-panel {
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        padding: 0.9rem 1rem;
    }
    .sx-sch .sx-detail-panel.is-wide {
        grid-column: 1 / -1;
    }
    .sx-sch .sx-detail-panel h6 {
        margin: 0 0 0.75rem;
        padding-bottom: 0.55rem;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #555;
    }
    .sx-sch .sx-kv {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 0.35rem 0.75rem;
    }
    .sx-sch .sx-kv-item {
        display: contents;
    }
    .sx-sch .sx-kv-item .detail-label {
        font-size: 0.78rem;
        font-weight: 500;
        color: #777;
        padding: 0.35rem 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .sx-sch .sx-kv-item .detail-value {
        font-size: 0.86rem;
        font-weight: 500;
        color: #111;
        text-align: right;
        padding: 0.35rem 0;
        border-bottom: 1px solid #f5f5f5;
        word-break: break-word;
    }
    .sx-sch .sx-assets-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }
    .sx-sch .sx-asset-card {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        background: #fafafa;
        padding: 0.75rem;
        text-align: center;
    }
    .sx-sch .sx-asset-card .asset-preview-sm {
        width: 100%;
        height: 88px;
        margin: 0 auto 0.5rem;
        border: 1px solid #e6e6e6;
        border-radius: 0.4rem;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sx-sch .sx-asset-card .asset-preview-sm img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .sx-sch .sx-asset-card small {
        font-size: 0.72rem;
        color: #666;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sx-sch .sx-expand-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        border: 1px solid #ececec;
        background: #fff;
        border-radius: 0.4rem;
        padding: 0.7rem 0.85rem;
        font-size: 0.86rem;
        font-weight: 500;
        color: #333;
        text-align: left;
    }
    .sx-sch .sx-expand-btn:hover {
        border-color: #940000;
        background: #fff5f5;
        color: #940000;
    }
    .sx-sch .sx-expand-btn .count {
        color: #940000;
        font-weight: 600;
    }
    .sx-sch .expand-list {
        background: #fff;
        padding: 0.65rem 0.85rem;
        border: 1px solid #ececec;
        border-top: 0;
        margin-top: 0;
        display: none;
        border-radius: 0 0 0.4rem 0.4rem;
    }
    .sx-sch .expand-list .sx-expand-item {
        padding: 0.4rem 0;
        border-bottom: 1px solid #f3f3f3;
        font-size: 0.84rem;
        color: #333;
    }
    .sx-sch .expand-list .sx-expand-item:last-child {
        border-bottom: 0;
    }
    .sx-sch #viewMoreModal .modal-footer {
        background: #fff;
        border-top: 1px solid #eee;
    }
    .sx-sch #editModal .modal-dialog {
        max-width: 920px;
    }
    .sx-sch #editModal .modal-body {
        padding: 1.15rem 1.25rem;
    }
    .sx-sch .sx-edit-steps {
        display: flex;
        gap: 0.45rem;
        margin-bottom: 1.15rem;
        padding-bottom: 0.95rem;
        border-bottom: 1px solid #eee;
        overflow-x: auto;
    }
    .sx-sch .sx-edit-step {
        flex: 1;
        min-width: 120px;
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.5rem 0.65rem;
        border: 1px solid #e8e8e8;
        background: #fafafa;
        border-radius: 0.45rem;
        color: #555;
        font-size: 0.74rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .sx-sch .sx-edit-step-num {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e9e9e9;
        color: #444;
        font-size: 0.72rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .sx-sch .sx-edit-step.is-active {
        border-color: #940000;
        background: #fff5f5;
        color: #000;
    }
    .sx-sch .sx-edit-step.is-active .sx-edit-step-num {
        background: #940000;
        color: #fff;
    }
    .sx-sch .sx-edit-step.is-done {
        border-color: #1a7f37;
        background: #effaf2;
        color: #145c2a;
    }
    .sx-sch .sx-edit-step.is-done .sx-edit-step-num {
        background: #1a7f37;
        color: #fff;
        font-size: 0;
    }
    .sx-sch .sx-edit-step.is-done .sx-edit-step-num::before {
        content: "✓";
        font-size: 0.8rem;
        font-weight: 700;
    }
    .sx-sch .sx-edit-pane { display: none; }
    .sx-sch .sx-edit-pane.is-active { display: block; }
    .sx-sch .sx-edit-pane-title {
        margin: 0 0 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #333;
    }
    .sx-sch .sx-edit-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        align-items: center;
        justify-content: space-between;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }
    .sx-sch .sx-edit-review {
        display: grid;
        gap: 0.75rem;
    }
    .sx-sch .sx-edit-review-block {
        border: 1px solid #ececec;
        background: #fafafa;
        border-radius: 0.45rem;
        padding: 0.85rem 1rem;
    }
    .sx-sch .sx-edit-review-block h6 {
        margin: 0 0 0.65rem;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555;
    }
    .sx-sch .sx-edit-review-rows {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem 1rem;
    }
    .sx-sch .sx-edit-review-rows dt {
        margin: 0;
        font-size: 0.7rem;
        color: #777;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sx-sch .sx-edit-review-rows dd {
        margin: 0.1rem 0 0;
        font-size: 0.88rem;
        font-weight: 500;
        color: #111;
        word-break: break-word;
    }
    .sx-sch .is-invalid-step {
        border-color: #940000 !important;
    }
    @media (max-width: 767.98px) {
        .sx-sch .sx-edit-review-rows { grid-template-columns: 1fr; }
    }
    @media (max-width: 767.98px) {
        .sx-sch .sx-detail-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sx-sch .sx-detail-sections { grid-template-columns: 1fr; }
        .sx-sch .sx-assets-row { grid-template-columns: 1fr; }
        .sx-sch .sx-kv { grid-template-columns: 1fr; }
        .sx-sch .sx-kv-item .detail-value { text-align: left; border-bottom: 1px solid #ececec; margin-bottom: 0.35rem; }
        .sx-sch .sx-kv-item .detail-label { border-bottom: 0; padding-bottom: 0; }
    }
    .sx-sch .form-label,
    .sx-sch label.fw-bold.small,
    .sx-sch .form-check-label.fw-bold {
        font-weight: 500 !important;
        font-size: 0.78rem !important;
        color: #333;
    }
    .sx-sch .form-control,
    .sx-sch .form-select {
        border-radius: 0.45rem;
        min-height: 40px;
    }
    .sx-sch .form-control:focus,
    .sx-sch .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-sch .form-check-input:checked {
        background-color: #940000;
        border-color: #940000;
    }
    .sx-sch .pagination {
        margin-bottom: 0;
    }
    .sx-sch .page-link {
        color: #940000;
        border-color: #e0e0e0;
        font-weight: 500;
    }
    .sx-sch .page-item.active .page-link {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .sx-sch .page-link:hover {
        color: #940000;
        background-color: #fff5f5;
        border-color: #940000;
    }
    .sx-sch .page-item.disabled .page-link {
        color: #999;
    }
    @media (max-width: 991.98px) {
        .sx-sch .sx-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .sx-sch .sx-grid { grid-template-columns: 1fr; }
    }
    /* Dark mode — full coverage */
    html[data-sx-theme="dark"] .sx-sch,
    html[data-bs-theme="dark"] .sx-sch,
    html[data-sx-theme="dark"] .sx-sch p,
    html[data-bs-theme="dark"] .sx-sch p,
    html[data-sx-theme="dark"] .sx-sch span:not(.sx-badge):not(.badge),
    html[data-bs-theme="dark"] .sx-sch span:not(.sx-badge):not(.badge),
    html[data-sx-theme="dark"] .sx-sch small,
    html[data-bs-theme="dark"] .sx-sch small,
    html[data-sx-theme="dark"] .sx-sch strong,
    html[data-bs-theme="dark"] .sx-sch strong,
    html[data-sx-theme="dark"] .sx-sch div:not(.sx-badge),
    html[data-bs-theme="dark"] .sx-sch div:not(.sx-badge),
    html[data-sx-theme="dark"] .sx-sch label,
    html[data-bs-theme="dark"] .sx-sch label,
    html[data-sx-theme="dark"] .sx-sch .sx-sch-card-title,
    html[data-bs-theme="dark"] .sx-sch .sx-sch-card-title,
    html[data-sx-theme="dark"] .sx-sch .sx-school-name,
    html[data-bs-theme="dark"] .sx-sch .sx-school-name,
    html[data-sx-theme="dark"] .sx-sch .detail-value,
    html[data-bs-theme="dark"] .sx-sch .detail-value,
    html[data-sx-theme="dark"] .sx-sch .modal-title,
    html[data-bs-theme="dark"] .sx-sch .modal-title,
    html[data-sx-theme="dark"] .sx-sch .fw-semibold,
    html[data-bs-theme="dark"] .sx-sch .fw-semibold,
    html[data-sx-theme="dark"] .sx-sch .fw-bold,
    html[data-bs-theme="dark"] .sx-sch .fw-bold,
    html[data-sx-theme="dark"] .sx-sch .table,
    html[data-bs-theme="dark"] .sx-sch .table,
    html[data-sx-theme="dark"] .sx-sch .table tbody td,
    html[data-bs-theme="dark"] .sx-sch .table tbody td,
    html[data-sx-theme="dark"] .sx-sch .sx-grid-row span:last-child,
    html[data-bs-theme="dark"] .sx-sch .sx-grid-row span:last-child,
    html[data-sx-theme="dark"] .sx-sch .form-label,
    html[data-bs-theme="dark"] .sx-sch .form-label,
    html[data-sx-theme="dark"] .sx-sch label.fw-bold.small,
    html[data-bs-theme="dark"] .sx-sch label.fw-bold.small,
    html[data-sx-theme="dark"] .sx-sch .form-check-label,
    html[data-bs-theme="dark"] .sx-sch .form-check-label,
    html[data-sx-theme="dark"] .sx-sch .sx-action-menu .dropdown-item,
    html[data-bs-theme="dark"] .sx-sch .sx-action-menu .dropdown-item {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-meta,
    html[data-bs-theme="dark"] .sx-sch .sx-meta,
    html[data-sx-theme="dark"] .sx-sch .detail-label,
    html[data-bs-theme="dark"] .sx-sch .detail-label,
    html[data-sx-theme="dark"] .sx-sch .text-muted,
    html[data-bs-theme="dark"] .sx-sch .text-muted,
    html[data-sx-theme="dark"] .sx-sch .sx-empty,
    html[data-bs-theme="dark"] .sx-sch .sx-empty,
    html[data-sx-theme="dark"] .sx-sch .sx-grid-row span:first-child,
    html[data-bs-theme="dark"] .sx-sch .sx-grid-row span:first-child,
    html[data-sx-theme="dark"] .sx-sch .table thead th,
    html[data-bs-theme="dark"] .sx-sch .table thead th {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-sch-card,
    html[data-bs-theme="dark"] .sx-sch .sx-sch-card,
    html[data-sx-theme="dark"] .sx-sch .sx-grid-card,
    html[data-bs-theme="dark"] .sx-sch .sx-grid-card,
    html[data-sx-theme="dark"] .sx-sch .modal-content,
    html[data-bs-theme="dark"] .sx-sch .modal-content {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-sch-card-head,
    html[data-bs-theme="dark"] .sx-sch .sx-sch-card-head,
    html[data-sx-theme="dark"] .sx-sch .modal-header,
    html[data-bs-theme="dark"] .sx-sch .modal-header,
    html[data-sx-theme="dark"] .sx-sch .modal-footer,
    html[data-bs-theme="dark"] .sx-sch .modal-footer {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-sch .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-sch .table thead th,
    html[data-bs-theme="dark"] .sx-sch .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-sch .table tbody td,
    html[data-bs-theme="dark"] .sx-sch .table tbody td {
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-sch .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-sch .table tbody tr:hover {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-search,
    html[data-bs-theme="dark"] .sx-sch .sx-search,
    html[data-sx-theme="dark"] .sx-sch .form-control,
    html[data-bs-theme="dark"] .sx-sch .form-control,
    html[data-sx-theme="dark"] .sx-sch .form-select,
    html[data-bs-theme="dark"] .sx-sch .form-select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-view-toggle,
    html[data-bs-theme="dark"] .sx-sch .sx-view-toggle {
        border-color: rgba(255, 180, 180, 0.18) !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-view-btn,
    html[data-bs-theme="dark"] .sx-sch .sx-view-btn {
        background: #1c1417 !important;
        color: #f3e9eb !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-view-btn.is-active,
    html[data-bs-theme="dark"] .sx-sch .sx-view-btn.is-active {
        background: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-dots-btn,
    html[data-bs-theme="dark"] .sx-sch .sx-dots-btn {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-dots-btn:hover,
    html[data-bs-theme="dark"] .sx-sch .sx-dots-btn:hover,
    html[data-sx-theme="dark"] .sx-sch .sx-dots-btn.show,
    html[data-bs-theme="dark"] .sx-sch .sx-dots-btn.show {
        background: rgba(148, 0, 0, 0.22) !important;
        border-color: #940000 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-action-menu .dropdown-menu,
    html[data-bs-theme="dark"] .sx-sch .sx-action-menu .dropdown-menu {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-action-menu .dropdown-item:hover,
    html[data-bs-theme="dark"] .sx-sch .sx-action-menu .dropdown-item:hover {
        background: rgba(148, 0, 0, 0.2) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sch .school-logo-circle,
    html[data-bs-theme="dark"] .sx-sch .school-logo-circle {
        border-color: rgba(255, 180, 180, 0.25) !important;
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] .sx-sch .page-link,
    html[data-bs-theme="dark"] .sx-sch .page-link {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sch .page-item.active .page-link,
    html[data-bs-theme="dark"] .sx-sch .page-item.active .page-link {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sch .page-item.disabled .page-link,
    html[data-bs-theme="dark"] .sx-sch .page-item.disabled .page-link {
        color: #8a7579 !important;
        background: #1a1215 !important;
    }
    html[data-sx-theme="dark"] .sx-sch .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-sch .btn-outline-secondary {
        color: #f3e9eb !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
    }
    html[data-sx-theme="dark"] .sx-sch .btn-primary,
    html[data-bs-theme="dark"] .sx-sch .btn-primary,
    html[data-sx-theme="dark"] .sx-sch .sx-badge-on,
    html[data-bs-theme="dark"] .sx-sch .sx-badge-on,
    html[data-sx-theme="dark"] .sx-sch .sx-view-btn.is-active,
    html[data-bs-theme="dark"] .sx-sch .sx-view-btn.is-active {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-badge-students,
    html[data-bs-theme="dark"] .sx-sch .sx-badge-students,
    html[data-sx-theme="dark"] .sx-sch .sx-badge-off,
    html[data-bs-theme="dark"] .sx-sch .sx-badge-off {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-badge-on,
    html[data-bs-theme="dark"] .sx-sch .sx-badge-on {
        background: rgba(20, 92, 42, 0.35) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-sch .sx-kv-item .detail-value,
    html[data-bs-theme="dark"] .sx-sch .sx-kv-item .detail-value {
        border-color: rgba(255, 180, 180, 0.14) !important;
    }
</style>

@php
    $placeholderLogo = asset('images/school-placeholder.svg');
@endphp

<div class="sx-sch is-list" id="sxSchRoot">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fi fi-rr-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('superadmin.common.close') }}"></button>
        </div>
    @endif

    <div class="sx-sch-card">
        <div class="card-body">
            <div class="sx-sch-card-head">
                <h2 class="sx-sch-card-title">{{ __('superadmin.schools.directory') }}</h2>
                <div class="sx-meta" id="schoolTotalCount">{{ __('superadmin.schools.registered_count', ['count' => $schools->total()]) }}</div>
            </div>

            <div class="sx-toolbar">
                <input type="search"
                       id="schoolSearch"
                       class="form-control sx-search"
                       value="{{ request('q') }}"
                       placeholder="{{ __('superadmin.schools.search_placeholder') }}"
                       autocomplete="off">
                <div class="sx-view-toggle" role="group" aria-label="{{ __('superadmin.schools.view_mode') }}">
                    <button type="button" class="sx-view-btn is-active" data-view="list" id="btnViewList">
                        <i class="fi fi-rr-list"></i> {{ __('superadmin.schools.list') }}
                    </button>
                    <button type="button" class="sx-view-btn" data-view="grid" id="btnViewGrid">
                        <i class="fi fi-rr-apps"></i> {{ __('superadmin.schools.grid') }}
                    </button>
                </div>
            </div>

            <div id="schoolsResults">
            <div class="sx-list-view">
                <div class="sx-table-wrap">
                    <div class="table-responsive">
                        <table class="table mb-0" id="schoolsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('superadmin.schools.th_school') }}</th>
                                    <th>{{ __('superadmin.schools.th_students') }}</th>
                                    <th>{{ __('superadmin.schools.th_sms') }}</th>
                                    <th>{{ __('superadmin.schools.th_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schools as $school)
                                    @php
                                        $sid = $school->schoolID;
                                        $sCount = $studentsBySchool[$sid] ?? 0;
                                        $smsOn = $school->isSmsEnabled();
                                        $searchKey = strtolower(trim(($school->school_name ?? '') . ' ' . ($school->registration_number ?? '')));
                                    @endphp
                                    <tr class="sx-school-item" data-search="{{ $searchKey }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $school->logo_url }}" class="school-logo-circle" alt=""
                                                     onerror="this.onerror=null;this.src='{{ $placeholderLogo }}'">
                                                <div>
                                                    <div class="sx-school-name">{{ $school->school_name }}</div>
                                                    <div class="sx-meta">{{ $school->registration_number ?: __('superadmin.schools.no_reg_no') }} · {{ $school->school_type }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="sx-badge sx-badge-students">{{ number_format($sCount) }}</span>
                                        </td>
                                        <td>
                                            @if($smsOn)
                                                <span class="sx-badge sx-badge-on">{{ __('superadmin.schools.sms_on') }}</span>
                                            @else
                                                <span class="sx-badge sx-badge-off">{{ __('superadmin.schools.sms_off') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('SuperAdmin.partials.school_actions_menu', ['school' => $school])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="sx-empty">{{ __('superadmin.schools.empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="sx-grid">
                @forelse($schools as $school)
                    @php
                        $sid = $school->schoolID;
                        $sCount = $studentsBySchool[$sid] ?? 0;
                        $smsOn = $school->isSmsEnabled();
                        $searchKey = strtolower(trim(($school->school_name ?? '') . ' ' . ($school->registration_number ?? '')));
                    @endphp
                    <div class="sx-grid-card sx-school-item" data-search="{{ $searchKey }}">
                        <div class="sx-grid-card-top">
                            <img src="{{ $school->logo_url }}" class="school-logo-circle" alt=""
                                 onerror="this.onerror=null;this.src='{{ $placeholderLogo }}'">
                            <div class="flex-grow-1">
                                <div class="sx-school-name">{{ $school->school_name }}</div>
                                <div class="sx-meta">{{ $school->registration_number ?: __('superadmin.schools.no_reg_no') }}</div>
                            </div>
                            @include('SuperAdmin.partials.school_actions_menu', ['school' => $school])
                        </div>
                        <div class="sx-grid-rows">
                            <div class="sx-grid-row">
                                <span>{{ __('superadmin.schools.th_students') }}</span>
                                <span class="sx-badge sx-badge-students">{{ number_format($sCount) }}</span>
                            </div>
                            <div class="sx-grid-row">
                                <span>{{ __('superadmin.schools.level') }}</span>
                                <span>{{ $school->school_type }}</span>
                            </div>
                            <div class="sx-grid-row">
                                <span>{{ __('superadmin.schools.th_sms') }}</span>
                                <span>
                                    @if($smsOn)
                                        <span class="sx-badge sx-badge-on">{{ __('superadmin.schools.sms_on') }}</span>
                                    @else
                                        <span class="sx-badge sx-badge-off">{{ __('superadmin.schools.sms_off') }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="sx-empty" style="grid-column: 1 / -1; border: 1px solid #ececec; border-radius: 0.45rem;">
                        {{ __('superadmin.schools.empty') }}
                    </div>
                @endforelse
            </div>

            <div id="schoolsPagination">
            @if($schools->hasPages())
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="sx-meta mb-0">
                        {{ __('superadmin.schools.showing', ['from' => $schools->firstItem(), 'to' => $schools->lastItem(), 'total' => $schools->total()]) }}
                    </div>
                    <div>
                        {{ $schools->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
            </div>
            </div>{{-- /#schoolsResults --}}
        </div>
    </div>

    {{-- View More Modal --}}
    <div class="modal fade" id="viewMoreModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('superadmin.schools.profile_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="sx-detail-hero">
                        <img id="v_logo" src="" class="sx-detail-logo" alt="">
                        <div class="sx-detail-hero-main">
                            <h6 id="v_name_header"></h6>
                            <div class="sx-detail-reg" id="v_reg_badge"></div>
                            <div class="sx-detail-chips">
                                <span class="sx-chip" id="v_type_chip"></span>
                                <span class="sx-chip" id="v_ownership_chip"></span>
                                <span class="sx-chip" id="v_env_chip"></span>
                                <span class="sx-chip" id="v_status_chip"></span>
                                <span class="sx-chip" id="v_login_chip"></span>
                            </div>
                        </div>
                    </div>

                    <div class="sx-detail-stats">
                        <div class="sx-stat">
                            <span class="sx-stat-num" id="v_students">0</span>
                            <span class="sx-stat-label">{{ __('superadmin.schools.students') }}</span>
                        </div>
                        <div class="sx-stat">
                            <span class="sx-stat-num" id="v_teachers">0</span>
                            <span class="sx-stat-label">{{ __('superadmin.schools.teachers') }}</span>
                        </div>
                        <div class="sx-stat">
                            <span class="sx-stat-num" id="v_staff">0</span>
                            <span class="sx-stat-label">{{ __('superadmin.schools.staff') }}</span>
                        </div>
                        <div class="sx-stat">
                            <span class="sx-stat-num" id="v_parents">0</span>
                            <span class="sx-stat-label">{{ __('superadmin.schools.parents') }}</span>
                        </div>
                    </div>

                    <div class="sx-detail-sections">
                        <div class="sx-detail-panel">
                            <h6>{{ __('superadmin.schools.institution') }}</h6>
                            <div class="sx-kv">
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.ownership') }}</span><span class="detail-value" id="v_ownership"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.education_level') }}</span><span class="detail-value" id="v_type"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.environment') }}</span><span class="detail-value" id="v_env"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.status') }}</span><span class="detail-value" id="v_status"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.established') }}</span><span class="detail-value" id="v_est"></span></div>
                            </div>
                        </div>

                        <div class="sx-detail-panel">
                            <h6>{{ __('superadmin.schools.location') }}</h6>
                            <div class="sx-kv">
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.region') }}</span><span class="detail-value" id="v_region"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.district') }}</span><span class="detail-value" id="v_district"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.ward') }}</span><span class="detail-value" id="v_ward"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.village') }}</span><span class="detail-value" id="v_village"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.address') }}</span><span class="detail-value" id="v_address"></span></div>
                            </div>
                        </div>

                        <div class="sx-detail-panel">
                            <h6>{{ __('superadmin.schools.contact_access') }}</h6>
                            <div class="sx-kv">
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.phone') }}</span><span class="detail-value" id="v_phone"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.email') }}</span><span class="detail-value" id="v_email"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.sender_id') }}</span><span class="detail-value" id="v_sender"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.sms_sending') }}</span><span class="detail-value" id="v_sms_enabled"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.student_portal') }}</span><span class="detail-value" id="v_student_portal"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.two_factor') }}</span><span class="detail-value" id="v_2fa"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.must_change_password') }}</span><span class="detail-value" id="v_must_change_password"></span></div>
                                <div class="sx-kv-item"><span class="detail-label">{{ __('superadmin.schools.login_access') }}</span><span class="detail-value" id="v_login_status"></span></div>
                            </div>
                            <div class="mt-2" id="v_suspension_reason_row" style="display:none;">
                                <div class="sx-meta mb-1">{{ __('superadmin.schools.suspension_reason') }}</div>
                                <div class="detail-value text-start" id="v_suspension_reason" style="color:#940000;"></div>
                            </div>
                        </div>

                        <div class="sx-detail-panel">
                            <h6>{{ __('superadmin.schools.brand_assets') }}</h6>
                            <div class="sx-assets-row">
                                <div class="sx-asset-card">
                                    <div class="asset-preview-sm">
                                        <img id="v_stamp" src="" title="{{ __('superadmin.schools.stamp_alt') }}" alt="">
                                    </div>
                                    <small>{{ __('superadmin.schools.stamp') }}</small>
                                </div>
                                <div class="sx-asset-card">
                                    <div class="asset-preview-sm">
                                        <img id="v_sign" src="" title="{{ __('superadmin.schools.signature_alt') }}" alt="">
                                    </div>
                                    <small>{{ __('superadmin.schools.signature') }}</small>
                                </div>
                                <div class="sx-asset-card">
                                    <div class="asset-preview-sm">
                                        <img id="v_logo_asset" src="" title="{{ __('superadmin.schools.logo_alt') }}" alt="">
                                    </div>
                                    <small>{{ __('superadmin.schools.logo') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="sx-detail-panel is-wide">
                            <h6>{{ __('superadmin.schools.classes') }}</h6>
                            <button type="button" class="sx-expand-btn" id="btnToggleClasses">
                                <span>{{ __('superadmin.schools.classes') }}</span>
                                <span class="count" id="v_classes_count">0</span>
                            </button>
                            <div id="v_classes_list" class="expand-list"></div>

                            <button type="button" class="sx-expand-btn mt-2" id="btnToggleSubclasses">
                                <span>{{ __('superadmin.schools.subclasses') }}</span>
                                <span class="count" id="v_subclasses_count">0</span>
                            </button>
                            <div id="v_subclasses_list" class="expand-list"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('superadmin.schools.close') }}</button>
                    <button type="button" class="btn btn-primary-custom" id="btnOpenEditFromView">{{ __('superadmin.schools.edit_title') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal (step wizard like register) --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('superadmin.schools.edit_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="sx-edit-steps" aria-label="{{ __('superadmin.schools.edit_steps') }}">
                        <div class="sx-edit-step is-active" data-edit-step-indicator="1">
                            <span class="sx-edit-step-num">1</span>
                            <span>{{ __('superadmin.schools.step_basic') }}</span>
                        </div>
                        <div class="sx-edit-step" data-edit-step-indicator="2">
                            <span class="sx-edit-step-num">2</span>
                            <span>{{ __('superadmin.schools.step_location') }}</span>
                        </div>
                        <div class="sx-edit-step" data-edit-step-indicator="3">
                            <span class="sx-edit-step-num">3</span>
                            <span>{{ __('superadmin.schools.step_system') }}</span>
                        </div>
                        <div class="sx-edit-step" data-edit-step-indicator="4">
                            <span class="sx-edit-step-num">4</span>
                            <span>{{ __('superadmin.schools.step_review') }}</span>
                        </div>
                    </div>

                    <form id="editForm" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="schoolID" id="e_id">

                        <div class="sx-edit-pane is-active" data-edit-step="1">
                            <h3 class="sx-edit-pane-title">{{ __('superadmin.schools.basic_information') }}</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="fw-bold small" for="e_name">{{ __('superadmin.schools.school_name') }} *</label>
                                    <input type="text" class="form-control" name="school_name" id="e_name" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-bold small" for="e_reg_no">{{ __('superadmin.schools.registration_number') }}</label>
                                    <input type="text" class="form-control" name="registration_number" id="e_reg_no">
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-bold small" for="e_exam_center">{{ __('superadmin.schools.exam_center') }}</label>
                                    <input type="text" class="form-control" name="exam_center_number" id="e_exam_center">
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_type">{{ __('superadmin.schools.education_level_req') }} *</label>
                                    <select class="form-select" name="school_type" id="e_type" required>
                                        <option value="">{{ __('superadmin.schools.select_level') }}</option>
                                        <option value="Primary">{{ __('superadmin.schools.level_primary') }}</option>
                                        <option value="Secondary">{{ __('superadmin.schools.level_secondary') }}</option>
                                        <option value="Advanced">{{ __('superadmin.schools.level_advanced') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_ownership">{{ __('superadmin.schools.ownership_type') }} *</label>
                                    <select class="form-select" name="ownership" id="e_ownership" required>
                                        <option value="">{{ __('superadmin.schools.select_ownership') }}</option>
                                        <option value="Public">{{ __('superadmin.schools.ownership_public') }}</option>
                                        <option value="Private">{{ __('superadmin.schools.ownership_private') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_established">{{ __('superadmin.schools.established_year') }}</label>
                                    <select class="form-select" name="established_year" id="e_established">
                                        <option value="">{{ __('superadmin.schools.select_year') }}</option>
                                        @for ($y = (int) date('Y'); $y >= 1900; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="sx-edit-pane" data-edit-step="2">
                            <h3 class="sx-edit-pane-title">{{ __('superadmin.schools.location_contact') }}</h3>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_region">{{ __('superadmin.schools.region') }} *</label>
                                    <select class="form-select" name="region" id="e_region" required>
                                        <option value="">{{ __('superadmin.schools.select_region') }}</option>
                                    </select>
                                    <div class="sx-meta" id="e_region_hint"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_district">{{ __('superadmin.schools.district') }} *</label>
                                    <select class="form-select" name="district" id="e_district" required disabled>
                                        <option value="">{{ __('superadmin.schools.select_region_first') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_ward">{{ __('superadmin.schools.ward') }}</label>
                                    <select class="form-select" name="ward" id="e_ward" disabled>
                                        <option value="">{{ __('superadmin.schools.select_district_first') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold small" for="e_email">{{ __('superadmin.schools.official_email') }}</label>
                                    <input type="email" class="form-control" name="email" id="e_email">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold small" for="e_phone">{{ __('superadmin.schools.primary_phone') }}</label>
                                    <input type="tel" class="form-control" name="phone" id="e_phone" placeholder="{{ __('superadmin.schools.phone_placeholder') }}">
                                </div>
                                <div class="col-12">
                                    <label class="fw-bold small" for="e_address">{{ __('superadmin.schools.postal_address') }}</label>
                                    <input type="text" class="form-control" name="address" id="e_address">
                                </div>
                            </div>
                        </div>

                        <div class="sx-edit-pane" data-edit-step="3">
                            <h3 class="sx-edit-pane-title">{{ __('superadmin.schools.system_settings') }}</h3>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_env">{{ __('superadmin.schools.system_environment') }}</label>
                                    <select class="form-select" name="environment" id="e_env">
                                        <option value="Demo">{{ __('superadmin.schools.env_demo') }}</option>
                                        <option value="Live">{{ __('superadmin.schools.env_live') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_sender">{{ __('superadmin.schools.sms_sender_id') }}</label>
                                    <input type="text" class="form-control" name="senderID" id="e_sender" maxlength="11">
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_status">{{ __('superadmin.schools.account_status') }} *</label>
                                    <select class="form-select" name="status" id="e_status" required>
                                        <option value="Active">{{ __('superadmin.schools.status_active') }}</option>
                                        <option value="Inactive">{{ __('superadmin.schools.status_inactive') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_sms_enabled">{{ __('superadmin.schools.sms_sending') }}</label>
                                    <select class="form-select" name="sms_enabled" id="e_sms_enabled">
                                        <option value="1">{{ __('superadmin.schools.sms_enabled') }}</option>
                                        <option value="0">{{ __('superadmin.schools.sms_disabled') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold small" for="e_login_status">{{ __('superadmin.schools.login_access') }} *</label>
                                    <select class="form-select" name="login_status" id="e_login_status" required>
                                        <option value="1">{{ __('superadmin.schools.grant_access') }}</option>
                                        <option value="0">{{ __('superadmin.schools.deny_access') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="e_suspension_reason_row" style="display:none;">
                                    <label class="fw-bold small text-danger" for="e_suspension_reason">{{ __('superadmin.schools.suspension_reason') }} *</label>
                                    <textarea class="form-control border-danger" name="suspension_reason" id="e_suspension_reason" rows="2"></textarea>
                                </div>

                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="form-check form-switch mb-0">
                                        <input type="checkbox" class="form-check-input" id="e_under_org" name="is_under_organization" value="1">
                                        <label class="form-check-label" for="e_under_org">{{ __('superadmin.schools.under_organization') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @include('SuperAdmin.partials.school_social_media_fields', [
                                        'ssmId' => 'edit_ssm',
                                        'ssmEnabled' => false,
                                        'ssmItems' => [],
                                        'ssmHideToggle' => false,
                                    ])
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="form-check form-switch mb-0">
                                        <input type="checkbox" class="form-check-input" id="e_student_portal_switch" value="1">
                                        <label class="form-check-label" for="e_student_portal_switch">{{ __('superadmin.schools.enable_student_portal') }}</label>
                                    </div>
                                    <input type="hidden" name="student_portal_enabled" id="e_student_portal" value="0">
                                </div>

                                <div class="col-12" id="e_org_fields" style="display:none;">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="fw-bold small" for="e_org_name">{{ __('superadmin.schools.organization_name') }}</label>
                                            <input type="text" class="form-control" name="organization_name" id="e_org_name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold small">{{ __('superadmin.schools.organization_logo') }}</label>
                                            <div class="asset-preview-sm mb-2 border">
                                                <img id="p_org_logo" src="" alt="">
                                            </div>
                                            <input type="file" class="form-control form-control-sm" name="organization_logo" onchange="readURL(this, 'p_org_logo')">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="e_2fa" name="two_factor_enabled" value="1">
                                        <label class="form-check-label" for="e_2fa">{{ __('superadmin.schools.enforce_2fa') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="e_must_change_password" name="must_change_default_password" value="1">
                                        <label class="form-check-label" for="e_must_change_password">{{ __('superadmin.schools.must_change_default_password') }}</label>
                                    </div>
                                </div>

                                <div class="col-md-4 text-center">
                                    <label class="fw-bold small">{{ __('superadmin.schools.school_logo') }}</label>
                                    <div class="asset-preview-sm mx-auto mb-2 border">
                                        <img id="p_logo" src="" alt="">
                                    </div>
                                    <input type="file" class="form-control form-control-sm" name="school_logo" onchange="readURL(this, 'p_logo')">
                                </div>
                                <div class="col-md-4 text-center">
                                    <label class="fw-bold small">{{ __('superadmin.schools.stamp') }}</label>
                                    <div class="asset-preview-sm mx-auto mb-2 border">
                                        <img id="p_stamp" src="" alt="">
                                    </div>
                                    <input type="file" class="form-control form-control-sm" name="school_stamp" onchange="readURL(this, 'p_stamp')">
                                </div>
                                <div class="col-md-4 text-center">
                                    <label class="fw-bold small">{{ __('superadmin.schools.signature') }}</label>
                                    <div class="asset-preview-sm mx-auto mb-2 border">
                                        <img id="p_sign" src="" alt="">
                                    </div>
                                    <input type="file" class="form-control form-control-sm" name="school_signature" onchange="readURL(this, 'p_sign')">
                                </div>
                            </div>
                        </div>

                        <div class="sx-edit-pane" data-edit-step="4">
                            <h3 class="sx-edit-pane-title">{{ __('superadmin.schools.review_save') }}</h3>
                            <div class="sx-edit-review" id="editReviewSummary">
                                <div class="sx-edit-review-block">
                                    <h6>{{ __('superadmin.schools.basic_information') }}</h6>
                                    <dl class="sx-edit-review-rows">
                                        <div><dt>{{ __('superadmin.schools.review_school_name') }}</dt><dd data-edit-review="school_name">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.review_registration_no') }}</dt><dd data-edit-review="registration_number">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.review_exam_center') }}</dt><dd data-edit-review="exam_center_number">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.review_education_level') }}</dt><dd data-edit-review="school_type">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.ownership') }}</dt><dd data-edit-review="ownership">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.established') }}</dt><dd data-edit-review="established_year">—</dd></div>
                                    </dl>
                                </div>
                                <div class="sx-edit-review-block">
                                    <h6>{{ __('superadmin.schools.location_contact') }}</h6>
                                    <dl class="sx-edit-review-rows">
                                        <div><dt>{{ __('superadmin.schools.region') }}</dt><dd data-edit-review="region">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.district') }}</dt><dd data-edit-review="district">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.ward') }}</dt><dd data-edit-review="ward">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.email') }}</dt><dd data-edit-review="email">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.phone') }}</dt><dd data-edit-review="phone">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.address') }}</dt><dd data-edit-review="address">—</dd></div>
                                    </dl>
                                </div>
                                <div class="sx-edit-review-block">
                                    <h6>{{ __('superadmin.schools.system_settings') }}</h6>
                                    <dl class="sx-edit-review-rows">
                                        <div><dt>{{ __('superadmin.schools.environment') }}</dt><dd data-edit-review="environment">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.status') }}</dt><dd data-edit-review="status">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.th_sms') }}</dt><dd data-edit-review="sms_enabled">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.review_login_access') }}</dt><dd data-edit-review="login_status">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.review_student_portal') }}</dt><dd data-edit-review="student_portal">—</dd></div>
                                        <div><dt>{{ __('superadmin.schools.two_factor') }}</dt><dd data-edit-review="two_factor">—</dd></div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="sx-edit-actions">
                            <button type="button" class="btn btn-outline-secondary" id="btnEditPrev" style="display:none;">{{ __('superadmin.schools.back') }}</button>
                            <div class="d-flex gap-2 ms-auto">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('superadmin.schools.cancel') }}</button>
                                <button type="button" class="btn btn-primary-custom" id="btnEditNext">{{ __('superadmin.schools.continue') }}</button>
                                <button type="submit" class="btn btn-primary-custom" id="btn_update" style="display:none;">{{ __('superadmin.schools.save_changes') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const SX = @json(__('superadmin.schools'));
    const SXC = @json(__('superadmin.common'));
    function readURL(input, target) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { $('#' + target).attr('src', e.target.result); }
            reader.readAsDataURL(input.files[0]);
        }
    }

    const PLACEHOLDER_LOGO = @json($placeholderLogo);
    let lastViewSchoolBtn = null;

    function bulkSmsToggle(enabled) {
        var title = enabled ? SX.js.enable_all_title : SX.js.disable_all_title;
        var text = enabled
            ? SX.js.enable_all_text
            : SX.js.disable_all_text;
        var confirmText = enabled ? SX.js.confirm_enable : SX.js.confirm_disable;

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmText,
            cancelButtonText: SX.js.cancel
        }).then(function (result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: SX.js.please_wait,
                text: enabled ? SX.js.enabling_sms : SX.js.disabling_sms,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function () { Swal.showLoading(); }
            });

            fetch('{{ route("superadmin.schools.bulk_sms_enabled") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ sms_enabled: enabled })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: SX.js.done,
                        text: data.message || (enabled ? SX.js.sms_enabled_all : SX.js.sms_disabled_all),
                        confirmButtonColor: '#940000'
                    }).then(function () { location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: SX.js.failed,
                        text: data.message || SX.js.sms_update_failed,
                        confirmButtonColor: '#940000'
                    });
                }
            })
            .catch(function () {
                Swal.fire({
                    icon: 'error',
                    title: SX.js.request_failed,
                    text: SX.js.try_again,
                    confirmButtonColor: '#940000'
                });
            });
        });
    }

    $(document).ready(function() {
        const root = document.getElementById('sxSchRoot');

        function setView(mode) {
            const isGrid = mode === 'grid';
            root.classList.toggle('is-grid', isGrid);
            root.classList.toggle('is-list', !isGrid);
            $('#btnViewList').toggleClass('is-active', !isGrid);
            $('#btnViewGrid').toggleClass('is-active', isGrid);
            try { localStorage.setItem('sx_schools_view', isGrid ? 'grid' : 'list'); } catch (e) {}
        }

        $('#btnViewList').on('click', function () { setView('list'); });
        $('#btnViewGrid').on('click', function () { setView('grid'); });
        try {
            const saved = localStorage.getItem('sx_schools_view');
            if (saved === 'grid' || saved === 'list') setView(saved);
        } catch (e) {}

        let searchTimer = null;
        let searchRequest = null;
        let activeQuery = String($('#schoolSearch').val() || '').trim();

        function buildSchoolsUrl(q, page) {
            const url = new URL(@json(route('superadmin.schools.index')), window.location.origin);
            if (q) url.searchParams.set('q', q);
            if (page && String(page) !== '1') url.searchParams.set('page', page);
            return url;
        }

        function loadSchoolsResults(q, page, pushState) {
            const url = buildSchoolsUrl(q, page);
            if (searchRequest && searchRequest.abort) {
                try { searchRequest.abort(); } catch (e) {}
            }
            const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
            searchRequest = controller;

            $('#schoolsResults').css('opacity', '0.55');

            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                signal: controller ? controller.signal : undefined
            })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newResults = doc.querySelector('#schoolsResults');
                const newCount = doc.querySelector('#schoolTotalCount');
                if (newResults) {
                    document.querySelector('#schoolsResults').innerHTML = newResults.innerHTML;
                }
                if (newCount) {
                    const countEl = document.querySelector('#schoolTotalCount');
                    if (countEl) countEl.textContent = newCount.textContent;
                }
                activeQuery = q;
                if (pushState !== false) {
                    window.history.replaceState({}, '', url.toString());
                }
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') return;
            })
            .finally(function () {
                $('#schoolsResults').css('opacity', '1');
            });
        }

        $('#schoolSearch').on('input', function () {
            const q = String($(this).val() || '').trim();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                if (q === activeQuery) return;
                loadSchoolsResults(q, 1, true);
            }, 350);
        });

        $(document).on('click', '#schoolsPagination a.page-link', function (e) {
            const href = $(this).attr('href');
            if (!href || href === '#') return;
            e.preventDefault();
            try {
                const url = new URL(href, window.location.origin);
                const page = url.searchParams.get('page') || '1';
                const q = String($('#schoolSearch').val() || '').trim();
                loadSchoolsResults(q, page, true);
            } catch (err) {}
        });

        $('#btnDisableSmsAll').on('click', function () { bulkSmsToggle(false); });
        $('#btnEnableSmsAll').on('click', function () { bulkSmsToggle(true); });

        function setChip($el, text, extraClass) {
            $el.removeClass('sx-chip-live sx-chip-demo sx-chip-active sx-chip-inactive');
            $el.text(text || '—');
            if (extraClass) $el.addClass(extraClass);
        }

        $(document).on('click', '.view-more-btn', function(e) {
            e.preventDefault();
            lastViewSchoolBtn = $(this);
            const b = $(this);
            const s = b.data('school');
            const logo = s.logo_url || PLACEHOLDER_LOGO;

            $('#v_logo').attr('src', logo);
            $('#v_logo_asset').attr('src', logo);
            $('#v_stamp').attr('src', s.stamp_url || '');
            $('#v_sign').attr('src', s.sign_url || '');

            $('#v_name_header').text(s.school_name || '—');
            $('#v_reg_badge').text(s.registration_number || SX.js.no_reg_number);

            $('#v_ownership').text(s.ownership || '—');
            $('#v_type').text(s.school_type || '—');
            $('#v_env').text(s.environment || '—');
            $('#v_status').text(s.status || '—');
            $('#v_est').text(s.established_year || '—');

            setChip($('#v_type_chip'), s.school_type || SX.level);
            setChip($('#v_ownership_chip'), s.ownership || SX.ownership);
            setChip($('#v_env_chip'), s.environment || SX.environment, String(s.environment).toLowerCase() === 'live' ? 'sx-chip-live' : 'sx-chip-demo');
            setChip($('#v_status_chip'), s.status || SX.status, String(s.status).toLowerCase() === 'active' ? 'sx-chip-active' : 'sx-chip-inactive');
            setChip($('#v_login_chip'), s.login_status != 0 ? SX.js.access_granted : SX.js.blocked_title, s.login_status != 0 ? 'sx-chip-active' : 'sx-chip-inactive');

            $('#v_region').text(s.region || '—');
            $('#v_district').text(s.district || '—');
            $('#v_ward').text(s.ward || '—');
            $('#v_village').text(s.village || '—');
            $('#v_address').text(s.address || '—');

            $('#v_phone').text(s.phone || '—');
            $('#v_email').text(s.email || '—');
            $('#v_sender').text(s.senderID || '—');
            const smsOn = (s.sms_enabled === undefined || s.sms_enabled === null) ? true : !!(s.sms_enabled == 1 || s.sms_enabled === true);
            $('#v_sms_enabled').html(smsOn ? '<span class="sx-badge sx-badge-on">' + SX.js.on + '</span>' : '<span class="sx-badge sx-badge-off">' + SX.js.off + '</span>');
            const portalOn = !!(s.student_portal_enabled == 1 || s.student_portal_enabled === true);
            $('#v_student_portal').html(portalOn ? '<span class="sx-badge sx-badge-on">' + SX.js.yes + '</span>' : '<span class="sx-badge sx-badge-off">' + SX.js.no + '</span>');
            $('#v_2fa').text(s.two_factor_enabled ? SX.js.yes_plain : SX.js.no_plain);
            $('#v_must_change_password').text(s.must_change_default_password ? SX.js.yes_plain : SX.js.no_plain);

            $('#v_login_status').html(s.login_status != 0 ? '<span class="sx-badge sx-badge-on">' + SX.js.active + '</span>' : '<span class="sx-badge sx-badge-off">' + SX.js.blocked + '</span>');
            if (s.login_status == 0) {
                $('#v_suspension_reason').text(s.suspension_reason || SX.js.no_reason);
                $('#v_suspension_reason_row').show();
            } else {
                $('#v_suspension_reason_row').hide();
            }

            $('#v_students').text(b.data('students'));
            $('#v_teachers').text(b.data('teachers'));
            $('#v_staff').text(b.data('staff'));
            $('#v_parents').text(b.data('parents'));

            $('#v_classes_count').text(b.data('classes-count'));
            $('#v_subclasses_count').text(b.data('subclasses-count'));

            const cList = JSON.parse(b.attr('data-classes-list') || '[]');
            $('#v_classes_list').html(cList.length ? cList.map(c => `<div class="sx-expand-item">${c}</div>`).join('') : '<div class="sx-expand-item">' + SX.js.none + '</div>');

            const scList = JSON.parse(b.attr('data-subclasses-list') || '[]');
            $('#v_subclasses_list').html(scList.length ? scList.map(sc => `<div class="sx-expand-item">${sc}</div>`).join('') : '<div class="sx-expand-item">' + SX.js.none + '</div>');

            $('.expand-list').hide();
            $('#btnToggleClasses, #btnToggleSubclasses').css('border-radius', '0.4rem');
        });

        $('#btnToggleClasses').on('click', function () {
            const $list = $('#v_classes_list');
            const opening = !$list.is(':visible');
            $list.slideToggle(150);
            $(this).css('border-radius', opening ? '0.4rem 0.4rem 0 0' : '0.4rem');
        });
        $('#btnToggleSubclasses').on('click', function () {
            const $list = $('#v_subclasses_list');
            const opening = !$list.is(':visible');
            $list.slideToggle(150);
            $(this).css('border-radius', opening ? '0.4rem 0.4rem 0 0' : '0.4rem');
        });

        const TZ_LOCATIONS_API = 'https://tz-locations-api.mwemanoor.workers.dev/v1';
        let editStep = 1;
        const editTotalSteps = 4;

        function fillSelect($el, items, placeholder, selectedName) {
            $el.empty();
            $el.append($('<option>', { value: '', text: placeholder }));
            (items || []).forEach(function (item) {
                const opt = $('<option>', {
                    value: item.name,
                    text: item.name,
                    selected: selectedName && selectedName === item.name
                });
                opt.attr('data-slug', item.slug || '');
                $el.append(opt);
            });
        }

        function selectedSlug($el) {
            const opt = $el.find('option:selected');
            return opt.length ? (opt.attr('data-slug') || '') : '';
        }

        function loadEditDistricts(regionSlug, preferDistrict, preferWard) {
            const $district = $('#e_district');
            const $ward = $('#e_ward');
            if (!regionSlug) {
                fillSelect($district, [], SX.js.select_region_first, '');
                $district.prop('disabled', true);
                fillSelect($ward, [], SX.js.select_district_first, '');
                $ward.prop('disabled', true);
                return;
            }
            $district.prop('disabled', true);
            fillSelect($district, [], SX.js.loading_districts, '');
            fillSelect($ward, [], SX.js.select_district_first, '');
            $ward.prop('disabled', true);
            fetch(TZ_LOCATIONS_API + '/regions/' + encodeURIComponent(regionSlug) + '/districts', {
                headers: { 'Accept': 'application/json' }
            })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    fillSelect($district, (json && json.data) ? json.data : [], SX.js.select_district, preferDistrict || '');
                    $district.prop('disabled', false);
                    if (preferDistrict) {
                        const dSlug = selectedSlug($district);
                        if (dSlug) loadEditWards(dSlug, preferWard || '');
                    }
                })
                .catch(function () {
                    fillSelect($district, [], SX.js.districts_failed, '');
                    $district.prop('disabled', false);
                });
        }

        function loadEditWards(districtSlug, preferWard) {
            const $ward = $('#e_ward');
            if (!districtSlug) {
                fillSelect($ward, [], SX.js.select_district_first, '');
                $ward.prop('disabled', true);
                return;
            }
            $ward.prop('disabled', true);
            fillSelect($ward, [], SX.js.loading_wards, '');
            fetch(TZ_LOCATIONS_API + '/districts/' + encodeURIComponent(districtSlug) + '/wards', {
                headers: { 'Accept': 'application/json' }
            })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    fillSelect($ward, (json && json.data) ? json.data : [], SX.js.select_ward, preferWard || '');
                    $ward.prop('disabled', false);
                })
                .catch(function () {
                    fillSelect($ward, [], SX.js.wards_failed, '');
                    $ward.prop('disabled', false);
                });
        }

        function loadEditRegions(preferRegion, preferDistrict, preferWard) {
            const $region = $('#e_region');
            const $hint = $('#e_region_hint');
            $hint.text(SX.js.loading_regions);
            fetch(TZ_LOCATIONS_API + '/regions', { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    fillSelect($region, (json && json.data) ? json.data : [], SX.js.select_region, preferRegion || '');
                    $hint.text('');
                    if (preferRegion) {
                        const slug = selectedSlug($region);
                        if (slug) loadEditDistricts(slug, preferDistrict || '', preferWard || '');
                    }
                })
                .catch(function () {
                    $hint.text(SX.js.regions_failed).css('color', '#940000');
                });
        }

        $('#e_region').on('change', function () {
            loadEditDistricts(selectedSlug($(this)), '', '');
        });
        $('#e_district').on('change', function () {
            loadEditWards(selectedSlug($(this)), '');
        });

        function syncStudentPortalField() {
            $('#e_student_portal').val($('#e_student_portal_switch').is(':checked') ? '1' : '0');
        }
        $('#e_student_portal_switch').on('change', syncStudentPortalField);

        function showEditStep(step) {
            editStep = step;
            $('.sx-edit-pane').removeClass('is-active');
            $('.sx-edit-pane[data-edit-step="' + step + '"]').addClass('is-active');
            $('[data-edit-step-indicator]').each(function () {
                const n = Number($(this).attr('data-edit-step-indicator'));
                $(this).toggleClass('is-active', n === step);
                $(this).toggleClass('is-done', n < step);
            });
            if (step > 1) {
                $('#btnEditPrev').show();
            } else {
                $('#btnEditPrev').hide();
            }
            if (step < editTotalSteps) {
                $('#btnEditNext').show();
                $('#btn_update').hide();
            } else {
                $('#btnEditNext').hide();
                $('#btn_update').show();
                buildEditReview();
            }
        }

        function editFieldText(id) {
            const $el = $('#' + id);
            if (!$el.length) return '';
            if ($el.is('select')) {
                const v = $el.val();
                return v ? String($el.find('option:selected').text() || '').trim() : '';
            }
            return String($el.val() || '').trim();
        }

        function setEditReview(key, value) {
            $('[data-edit-review="' + key + '"]').text(value || '—');
        }

        function buildEditReview() {
            setEditReview('school_name', editFieldText('e_name'));
            setEditReview('registration_number', editFieldText('e_reg_no'));
            setEditReview('exam_center_number', editFieldText('e_exam_center'));
            setEditReview('school_type', editFieldText('e_type'));
            setEditReview('ownership', editFieldText('e_ownership'));
            setEditReview('established_year', editFieldText('e_established'));
            setEditReview('region', editFieldText('e_region'));
            setEditReview('district', editFieldText('e_district'));
            setEditReview('ward', editFieldText('e_ward'));
            setEditReview('email', editFieldText('e_email'));
            setEditReview('phone', editFieldText('e_phone'));
            setEditReview('address', editFieldText('e_address'));
            setEditReview('environment', editFieldText('e_env'));
            setEditReview('status', editFieldText('e_status'));
            setEditReview('sms_enabled', editFieldText('e_sms_enabled'));
            setEditReview('login_status', editFieldText('e_login_status'));
            setEditReview('student_portal', $('#e_student_portal_switch').is(':checked') ? SX.js.yes_plain : SX.js.no_plain);
            setEditReview('two_factor', $('#e_2fa').is(':checked') ? SX.js.yes_plain : SX.js.no_plain);
        }

        $(document).on('input change', '#editForm input, #editForm select, #editForm textarea', function () {
            $(this).removeClass('is-invalid-step');
        });

        function markInvalid($el, okState) {
            $el.toggleClass('is-invalid-step', !okState);
            return okState;
        }

        function validateEditStep(step) {
            const $pane = $('.sx-edit-pane[data-edit-step="' + step + '"]');
            let ok = true;
            let firstInvalid = null;

            $pane.find('input, select, textarea').each(function () {
                const $el = $(this);
                if ($el.attr('type') === 'hidden' || $el.attr('type') === 'file') return;
                if ($el.is(':disabled')) return;
                if (!$el.is(':visible') && !$el.prop('required')) return;

                let valid = true;
                if ($el.prop('required')) {
                    valid = String($el.val() || '').trim() !== '';
                }
                if (valid && $el.attr('type') === 'email' && String($el.val() || '').trim() !== '') {
                    valid = this.checkValidity();
                }
                if (!markInvalid($el, valid)) {
                    ok = false;
                    if (!firstInvalid) firstInvalid = this;
                }
            });

            if (step === 2) {
                const regionOk = markInvalid($('#e_region'), String($('#e_region').val() || '').trim() !== '');
                const districtOk = markInvalid($('#e_district'), String($('#e_district').val() || '').trim() !== '');
                if (!regionOk) {
                    ok = false;
                    if (!firstInvalid) firstInvalid = document.getElementById('e_region');
                }
                if (!districtOk) {
                    ok = false;
                    if (!firstInvalid) firstInvalid = document.getElementById('e_district');
                }
            }

            if (!ok && firstInvalid) firstInvalid.focus();
            return ok;
        }

        $('#btnEditNext').on('click', function () {
            if (!validateEditStep(editStep)) return;
            if (editStep < editTotalSteps) showEditStep(editStep + 1);
        });
        $('#btnEditPrev').on('click', function () {
            if (editStep > 1) showEditStep(editStep - 1);
        });

        function fillEditForm(b) {
            const s = b.data('school');
            $('#e_id').val(s.schoolID);
            $('#e_name').val(s.school_name || '');
            $('#e_reg_no').val(s.registration_number || '');
            $('#e_exam_center').val(s.exam_center_number || '');

            const rawType = String(s.school_type || '').toLowerCase();
            let mappedType = s.school_type || '';
            if (rawType.indexOf('primary') !== -1) mappedType = 'Primary';
            else if (rawType.indexOf('advanced') !== -1 || rawType.indexOf('a-level') !== -1) mappedType = 'Advanced';
            else if (rawType.indexOf('secondary') !== -1 || rawType.indexOf('o-level') !== -1) mappedType = 'Secondary';
            $('#e_type').val(mappedType);

            const rawOwn = String(s.ownership || '').toLowerCase();
            let mappedOwn = s.ownership || '';
            if (rawOwn.indexOf('public') !== -1 || rawOwn.indexOf('government') !== -1) mappedOwn = 'Public';
            else if (rawOwn.indexOf('private') !== -1 || rawOwn.indexOf('ngo') !== -1) mappedOwn = 'Private';
            $('#e_ownership').val(mappedOwn);

            $('#e_established').val(s.established_year || '');
            $('#e_email').val(s.email || '');
            $('#e_phone').val(s.phone || '');
            $('#e_address').val(s.address || '');
            $('#e_env').val(s.environment || 'Demo');
            $('#e_status').val(s.status || 'Active');
            $('#e_sender').val(s.senderID || '');
            const smsEnabled = (s.sms_enabled === undefined || s.sms_enabled === null) ? 1 : ((s.sms_enabled == 1 || s.sms_enabled === true) ? 1 : 0);
            $('#e_sms_enabled').val(String(smsEnabled));
            const portalEnabled = (s.student_portal_enabled == 1 || s.student_portal_enabled === true);
            $('#e_student_portal_switch').prop('checked', portalEnabled);
            syncStudentPortalField();
            $('#e_2fa').prop('checked', s.two_factor_enabled == 1);
            $('#e_must_change_password').prop('checked', s.must_change_default_password == 1);
            $('#e_under_org').prop('checked', s.is_under_organization == 1 || s.is_under_organization === true);
            $('#e_org_name').val(s.organization_name || '');
            $('#p_org_logo').attr('src', s.org_logo_url || '');
            if (s.is_under_organization == 1 || s.is_under_organization === true) {
                $('#e_org_fields').show();
            } else {
                $('#e_org_fields').hide();
            }
            if (window.SchoolSocialMediaUI) {
                window.SchoolSocialMediaUI.loadItems(
                    $('#edit_ssm_wrap'),
                    !!(s.enable_social_media == 1 || s.enable_social_media === true),
                    s.social_media || []
                );
            }
            $('#e_login_status').val(s.login_status == 0 ? '0' : '1');
            $('#e_suspension_reason').val(s.suspension_reason || '');

            if (s.login_status == 0) {
                $('#e_suspension_reason_row').show();
                $('#e_suspension_reason').attr('required', true);
            } else {
                $('#e_suspension_reason_row').hide();
                $('#e_suspension_reason').removeAttr('required');
            }

            $('#p_logo').attr('src', b.data('logo-url') || PLACEHOLDER_LOGO);
            $('#p_stamp').attr('src', b.data('stamp-url') || '');
            $('#p_sign').attr('src', b.data('sign-url') || '');

            loadEditRegions(s.region || '', s.district || '', s.ward || '');
            showEditStep(1);
        }

        $('#btnOpenEditFromView').on('click', function () {
            if (!lastViewSchoolBtn || !lastViewSchoolBtn.length) return;
            fillEditForm(lastViewSchoolBtn);
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewMoreModal'));
            if (viewModal) viewModal.hide();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
        });

        $(document).on('click', '.edit-school-btn', function (e) {
            e.preventDefault();
            fillEditForm($(this));
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
        });

        function schoolActionPost(url, payload) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            }).then(function (r) { return r.json(); });
        }

        $(document).on('click', '.block-school-btn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const name = $(this).data('name') || 'this school';
            Swal.fire({
                title: SX.js.block_title,
                html: SX.js.block_text.replace(':name', name),
                input: 'textarea',
                inputLabel: SX.js.suspension_reason,
                inputPlaceholder: SX.js.suspension_placeholder,
                inputAttributes: { 'aria-label': SX.js.suspension_reason },
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: SX.js.block,
                cancelButtonText: SX.js.cancel,
                inputValidator: function (value) {
                    if (!value || !String(value).trim()) {
                        return SX.js.suspension_required;
                    }
                }
            }).then(function (result) {
                if (!result.isConfirmed) return;
                Swal.fire({ title: SX.js.please_wait, allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
                schoolActionPost('{{ route("superadmin.schools.block") }}', {
                    schoolID: id,
                    suspension_reason: String(result.value || '').trim()
                }).then(function (data) {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: SX.js.blocked_title, text: data.message || SX.js.school_blocked, confirmButtonColor: '#940000' })
                            .then(function () { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: SX.js.failed, text: data.message || SX.js.block_failed, confirmButtonColor: '#940000' });
                    }
                }).catch(function () {
                    Swal.fire({ icon: 'error', title: SX.js.request_failed, text: SX.js.try_again, confirmButtonColor: '#940000' });
                });
            });
        });

        $(document).on('click', '.unblock-school-btn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const name = $(this).data('name') || 'this school';
            Swal.fire({
                title: SX.js.unblock_title,
                html: SX.js.unblock_text.replace(':name', name),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: SX.js.unblock,
                cancelButtonText: SX.js.cancel
            }).then(function (result) {
                if (!result.isConfirmed) return;
                Swal.fire({ title: SX.js.please_wait, allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
                schoolActionPost('{{ route("superadmin.schools.unblock") }}', { schoolID: id }).then(function (data) {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: SX.js.unblocked_title, text: data.message || SX.js.school_unblocked, confirmButtonColor: '#940000' })
                            .then(function () { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: SX.js.failed, text: data.message || SX.js.unblock_failed, confirmButtonColor: '#940000' });
                    }
                }).catch(function () {
                    Swal.fire({ icon: 'error', title: SX.js.request_failed, text: SX.js.try_again, confirmButtonColor: '#940000' });
                });
            });
        });

        $(document).on('click', '.delete-school-btn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const name = $(this).data('name') || 'this school';
            Swal.fire({
                title: SX.js.delete_title,
                html: SX.js.delete_text.replace(':name', name),
                input: 'text',
                inputPlaceholder: name,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: SX.js.delete,
                cancelButtonText: SX.js.cancel,
                inputValidator: function (value) {
                    if (String(value || '').trim() !== String(name).trim()) {
                        return SX.js.name_mismatch;
                    }
                }
            }).then(function (result) {
                if (!result.isConfirmed) return;
                Swal.fire({ title: SX.js.please_wait, allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
                schoolActionPost('{{ route("superadmin.schools.delete") }}', { schoolID: id }).then(function (data) {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: SX.js.deleted_title, text: data.message || SX.js.school_deleted, confirmButtonColor: '#940000' })
                            .then(function () { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: SX.js.failed, text: data.message || SX.js.delete_failed, confirmButtonColor: '#940000' });
                    }
                }).catch(function () {
                    Swal.fire({ icon: 'error', title: SX.js.request_failed, text: SX.js.try_again, confirmButtonColor: '#940000' });
                });
            });
        });

        $(document).on('change', '#e_under_org', function() {
            if ($(this).is(':checked')) {
                $('#e_org_fields').slideDown();
            } else {
                $('#e_org_fields').slideUp();
            }
        });

        $(document).on('change', '#e_login_status', function() {
            if ($(this).val() === '0') {
                $('#e_suspension_reason_row').slideDown();
                $('#e_suspension_reason').attr('required', true).focus();
            } else {
                $('#e_suspension_reason_row').slideUp();
                $('#e_suspension_reason').removeAttr('required');
            }
        });

        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            syncStudentPortalField();
            const $btn = $('#btn_update');
            $btn.prop('disabled', true).text(SX.js.updating);

            $.ajax({
                url: '{{ route("superadmin.schools.update_comprehensive") }}',
                type: 'POST',
                data: (function() {
                    const fd = new FormData(document.getElementById('editForm'));
                    if (!$('#e_under_org').is(':checked')) {
                        fd.set('is_under_organization', '0');
                    }
                    if (!$('#edit_ssm_enable').is(':checked')) {
                        fd.set('enable_social_media', '0');
                    }
                    fd.set('student_portal_enabled', $('#e_student_portal_switch').is(':checked') ? '1' : '0');
                    return fd;
                })(),
                processData: false, contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    if(res.success) {
                        Swal.fire(SX.js.updated, res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire(SX.js.error, res.message, 'error');
                        $btn.prop('disabled', false).text(SX.js.save_changes);
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).text(SX.js.save_changes);
                    let msg = SX.js.update_failed;
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errs = xhr.responseJSON.errors;
                            msg = typeof errs === 'object'
                                ? Object.values(errs).flat().join('\n')
                                : String(errs);
                        } else if (xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        }
                    }
                    Swal.fire(SX.js.error, msg, 'error');
                }
            });
        });
    });
</script>
@endpush
