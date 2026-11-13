@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.fingerprint_attendance_setup'))
@section('page_breadcrumb', __('superadmin.pages.fingerprint_attendance_setup'))

@section('page_actions')
    @if(!$setup)
        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#uploadSetupModal">
            <i class="fi fi-rr-upload me-1"></i> {{ __('superadmin.fingerprint_setup.upload_setup') }}
        </button>
    @endif
    <a href="{{ route('superadmin.attendance_sms_schedule') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-calendar me-1"></i> {{ __('superadmin.fingerprint_setup.sms_schedule') }}
    </a>
@endsection

@section('content')
<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp span:not(.sx-badge):not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-fp a:not(.btn) {
        color: #000 !important;
    }
    .sx-fp .btn-primary,
    .sx-fp a.btn-primary,
    .sx-fp .btn-primary i,
    .sx-fp .btn-primary i:before,
    .sx-fp .text-white,
    .sx-fp .sx-badge.is-active {
        color: #fff !important;
    }
    .sx-fp .sx-fp-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-fp .sx-fp-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-fp .sx-fp-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-fp .sx-fp-stat .stat-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.2;
        color: #000 !important;
        word-break: break-word;
    }
    .sx-fp .sx-fp-stat .stat-value.is-num { font-size: 1.85rem; }
    .sx-fp .sx-fp-stat .stat-ico {
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
    .sx-fp .sx-fp-stat .stat-ico i,
    .sx-fp .sx-fp-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-fp .sx-fp-stat.is-status .stat-ico { background: #e8f7ec; color: #145c2a !important; }
    .sx-fp .sx-fp-stat.is-status.is-empty .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-fp .sx-fp-stat.is-version .stat-ico { background: #fff0e8; color: #a34a00 !important; }
    .sx-fp .sx-fp-stat.is-size .stat-ico { background: #eef2ff; color: #3b4cc0 !important; }
    .sx-fp .sx-fp-stat.is-limit .stat-ico { background: #f5f5f5; color: #555 !important; }
    .sx-fp .sx-fp-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-fp .sx-fp-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-fp .sx-fp-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-fp .sx-fp-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-fp .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-fp .form-control,
    .sx-fp .form-select,
    #uploadSetupModal .form-control,
    #editSetupModal .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
        color: #000;
    }
    .sx-fp .form-control:focus,
    #uploadSetupModal .form-control:focus,
    #editSetupModal .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-fp .btn-primary,
    #uploadSetupModal .btn-primary,
    #editSetupModal .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-fp .btn-primary:hover,
    #uploadSetupModal .btn-primary:hover,
    #editSetupModal .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-fp .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000 !important;
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-fp .btn-outline-primary:hover {
        background: #940000 !important;
        color: #fff !important;
    }
    .sx-fp .btn-outline-success,
    .sx-fp .btn-outline-danger,
    .sx-fp .btn-outline-secondary {
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-fp .sx-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    .sx-fp .sx-badge.is-active {
        background: #145c2a;
        color: #fff !important;
    }
    .sx-fp .sx-badge.is-empty {
        background: #f0f0f0;
        color: #666 !important;
    }
    .sx-fp .sx-setup-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.25rem;
    }
    .sx-fp .sx-setup-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 0.65rem;
        background: #fde8e8;
        color: #940000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        flex-shrink: 0;
    }
    .sx-fp .sx-setup-icon i:before { color: inherit !important; }
    .sx-fp .sx-setup-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #111 !important;
        margin: 0 0 0.35rem;
        line-height: 1.3;
    }
    .sx-fp .sx-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
        margin-top: 0.85rem;
    }
    .sx-fp .sx-meta-item {
        font-size: 0.86rem;
        color: #555 !important;
    }
    .sx-fp .sx-meta-item i {
        color: #940000 !important;
        margin-right: 0.3rem;
    }
    .sx-fp .sx-meta-item i:before { color: inherit !important; }
    .sx-fp .sx-empty {
        text-align: center;
        padding: 3rem 1.5rem;
    }
    .sx-fp .sx-empty-ico {
        width: 4.5rem;
        height: 4.5rem;
        border-radius: 50%;
        background: #fff5f5;
        color: #940000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-bottom: 1rem;
    }
    .sx-fp .sx-empty-ico i:before { color: inherit !important; }
    .sx-fp .fp-upload-progress .progress-bar,
    .sx-fp .fp-setup-download-progress .progress-bar,
    #uploadSetupModal .progress-bar,
    #editSetupModal .progress-bar {
        background-color: #940000;
    }
    .sx-fp .alert {
        border-radius: 0.45rem;
        border: 1px solid transparent;
    }
    #uploadSetupModal .modal-content,
    #editSetupModal .modal-content {
        border: 0;
        border-radius: 0.65rem;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(20, 0, 0, 0.18);
        border-top: 4px solid #940000;
    }
    #uploadSetupModal .modal-header,
    #editSetupModal .modal-header {
        background: #fafafa;
        border-bottom: 1px solid #eee;
    }
    #uploadSetupModal .modal-title,
    #editSetupModal .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #940000;
    }
    #uploadSetupModal .modal-footer,
    #editSetupModal .modal-footer {
        border-top: 1px solid #eee;
        background: #fff;
    }
    #uploadSetupModal .btn-outline-secondary,
    #editSetupModal .btn-outline-secondary {
        border-radius: 0.45rem;
        font-weight: 600;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-fp,
    html[data-bs-theme="dark"] .sx-fp,
    html[data-sx-theme="dark"] .sx-fp p,
    html[data-bs-theme="dark"] .sx-fp p,
    html[data-sx-theme="dark"] .sx-fp span:not(.sx-badge):not(.badge):not(.stat-ico):not(.sx-setup-icon),
    html[data-bs-theme="dark"] .sx-fp span:not(.sx-badge):not(.badge):not(.stat-ico):not(.sx-setup-icon),
    html[data-sx-theme="dark"] .sx-fp small,
    html[data-bs-theme="dark"] .sx-fp small,
    html[data-sx-theme="dark"] .sx-fp strong,
    html[data-bs-theme="dark"] .sx-fp strong,
    html[data-sx-theme="dark"] .sx-fp h1,
    html[data-bs-theme="dark"] .sx-fp h1,
    html[data-sx-theme="dark"] .sx-fp h2,
    html[data-bs-theme="dark"] .sx-fp h2,
    html[data-sx-theme="dark"] .sx-fp h3,
    html[data-bs-theme="dark"] .sx-fp h3,
    html[data-sx-theme="dark"] .sx-fp h4,
    html[data-bs-theme="dark"] .sx-fp h4,
    html[data-sx-theme="dark"] .sx-fp h5,
    html[data-bs-theme="dark"] .sx-fp h5,
    html[data-sx-theme="dark"] .sx-fp h6,
    html[data-bs-theme="dark"] .sx-fp h6,
    html[data-sx-theme="dark"] .sx-fp label,
    html[data-bs-theme="dark"] .sx-fp label,
    html[data-sx-theme="dark"] .sx-fp a:not(.btn),
    html[data-bs-theme="dark"] .sx-fp a:not(.btn),
    html[data-sx-theme="dark"] .sx-fp .sx-fp-stat .stat-label,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-stat .stat-label,
    html[data-sx-theme="dark"] .sx-fp .sx-fp-stat .stat-value,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-stat .stat-value,
    html[data-sx-theme="dark"] .sx-fp .sx-setup-title,
    html[data-bs-theme="dark"] .sx-fp .sx-setup-title,
    html[data-sx-theme="dark"] .sx-fp .sx-fp-card-title,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-card-title {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-fp .text-muted,
    html[data-bs-theme="dark"] .sx-fp .text-muted,
    html[data-sx-theme="dark"] .sx-fp .text-body,
    html[data-bs-theme="dark"] .sx-fp .text-body,
    html[data-sx-theme="dark"] .sx-fp span.text-muted:not(.sx-badge):not(.badge):not(.stat-ico):not(.sx-setup-icon),
    html[data-bs-theme="dark"] .sx-fp span.text-muted:not(.sx-badge):not(.badge):not(.stat-ico):not(.sx-setup-icon),
    html[data-sx-theme="dark"] .sx-fp .form-label,
    html[data-bs-theme="dark"] .sx-fp .form-label,
    html[data-sx-theme="dark"] .sx-fp label.form-label,
    html[data-bs-theme="dark"] .sx-fp label.form-label,
    html[data-sx-theme="dark"] .sx-fp .sx-meta-item,
    html[data-bs-theme="dark"] .sx-fp .sx-meta-item,
    html[data-sx-theme="dark"] .sx-fp span.sx-meta-item:not(.sx-badge):not(.badge):not(.stat-ico):not(.sx-setup-icon),
    html[data-bs-theme="dark"] .sx-fp span.sx-meta-item:not(.sx-badge):not(.badge):not(.stat-ico):not(.sx-setup-icon),
    html[data-sx-theme="dark"] .sx-fp .sx-empty p,
    html[data-bs-theme="dark"] .sx-fp .sx-empty p,
    html[data-sx-theme="dark"] #uploadSetupModal .form-label,
    html[data-bs-theme="dark"] #uploadSetupModal .form-label,
    html[data-sx-theme="dark"] #editSetupModal .form-label,
    html[data-bs-theme="dark"] #editSetupModal .form-label,
    html[data-sx-theme="dark"] #uploadSetupModal .text-muted,
    html[data-bs-theme="dark"] #uploadSetupModal .text-muted,
    html[data-sx-theme="dark"] #editSetupModal .text-muted,
    html[data-bs-theme="dark"] #editSetupModal .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-fp .sx-fp-stat,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-stat,
    html[data-sx-theme="dark"] .sx-fp .sx-fp-card,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-fp .sx-fp-card-head,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    /* Stat icons */
    html[data-sx-theme="dark"] .sx-fp .sx-fp-stat.is-status .stat-ico,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-stat.is-status .stat-ico {
        background: rgba(20, 92, 42, 0.35) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-fp .sx-fp-stat.is-status.is-empty .stat-ico,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-stat.is-status.is-empty .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-fp .sx-fp-stat.is-version .stat-ico,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-stat.is-version .stat-ico {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-fp .sx-fp-stat.is-size .stat-ico,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-stat.is-size .stat-ico {
        background: rgba(59, 76, 192, 0.28) !important;
        color: #b8c4ff !important;
    }
    html[data-sx-theme="dark"] .sx-fp .sx-fp-stat.is-limit .stat-ico,
    html[data-bs-theme="dark"] .sx-fp .sx-fp-stat.is-limit .stat-ico {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #d7c4c8 !important;
    }
    /* Badges, hero, empty state */
    html[data-sx-theme="dark"] .sx-fp .sx-badge.is-active,
    html[data-bs-theme="dark"] .sx-fp .sx-badge.is-active {
        background: rgba(20, 92, 42, 0.45) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-fp .sx-badge.is-empty,
    html[data-bs-theme="dark"] .sx-fp .sx-badge.is-empty {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-fp .sx-setup-icon,
    html[data-bs-theme="dark"] .sx-fp .sx-setup-icon {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-fp .sx-empty-ico,
    html[data-bs-theme="dark"] .sx-fp .sx-empty-ico {
        background: rgba(148, 0, 0, 0.22) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-fp .sx-meta-item i,
    html[data-bs-theme="dark"] .sx-fp .sx-meta-item i {
        color: #ffb4b4 !important;
    }
    /* Progress bars */
    html[data-sx-theme="dark"] .sx-fp .progress,
    html[data-bs-theme="dark"] .sx-fp .progress,
    html[data-sx-theme="dark"] #uploadSetupModal .progress,
    html[data-bs-theme="dark"] #uploadSetupModal .progress,
    html[data-sx-theme="dark"] #editSetupModal .progress,
    html[data-bs-theme="dark"] #editSetupModal .progress {
        background-color: #1c1417 !important;
    }
    html[data-sx-theme="dark"] .sx-fp .fp-upload-progress .progress-bar,
    html[data-bs-theme="dark"] .sx-fp .fp-upload-progress .progress-bar,
    html[data-sx-theme="dark"] .sx-fp .fp-setup-download-progress .progress-bar,
    html[data-bs-theme="dark"] .sx-fp .fp-setup-download-progress .progress-bar,
    html[data-sx-theme="dark"] #uploadSetupModal .progress-bar,
    html[data-bs-theme="dark"] #uploadSetupModal .progress-bar,
    html[data-sx-theme="dark"] #editSetupModal .progress-bar,
    html[data-bs-theme="dark"] #editSetupModal .progress-bar {
        background-color: #c43a3a !important;
        color: #fff !important;
    }
    /* Buttons */
    html[data-sx-theme="dark"] .sx-fp .btn-primary,
    html[data-bs-theme="dark"] .sx-fp .btn-primary,
    html[data-sx-theme="dark"] .sx-fp .btn-primary i,
    html[data-bs-theme="dark"] .sx-fp .btn-primary i,
    html[data-sx-theme="dark"] #uploadSetupModal .btn-primary,
    html[data-bs-theme="dark"] #uploadSetupModal .btn-primary,
    html[data-sx-theme="dark"] #editSetupModal .btn-primary,
    html[data-bs-theme="dark"] #editSetupModal .btn-primary {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-fp .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-fp .btn-outline-primary,
    html[data-sx-theme="dark"] .sx-fp .btn-outline-danger,
    html[data-bs-theme="dark"] .sx-fp .btn-outline-danger {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
    }
    html[data-sx-theme="dark"] .sx-fp .btn-outline-success,
    html[data-bs-theme="dark"] .sx-fp .btn-outline-success {
        color: #8ce99a !important;
        border-color: #2f9e57 !important;
    }
    html[data-sx-theme="dark"] .sx-fp .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-fp .btn-outline-secondary,
    html[data-sx-theme="dark"] #uploadSetupModal .btn-outline-secondary,
    html[data-bs-theme="dark"] #uploadSetupModal .btn-outline-secondary,
    html[data-sx-theme="dark"] #editSetupModal .btn-outline-secondary,
    html[data-bs-theme="dark"] #editSetupModal .btn-outline-secondary {
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
    }
    html[data-sx-theme="dark"] .sx-fp .btn-outline-primary:hover,
    html[data-bs-theme="dark"] .sx-fp .btn-outline-primary:hover,
    html[data-sx-theme="dark"] .sx-fp .btn-outline-danger:hover,
    html[data-bs-theme="dark"] .sx-fp .btn-outline-danger:hover {
        background: rgba(148, 0, 0, 0.3) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-fp .btn-outline-success:hover,
    html[data-bs-theme="dark"] .sx-fp .btn-outline-success:hover {
        background: rgba(20, 92, 42, 0.45) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-fp .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] .sx-fp .btn-outline-secondary:hover,
    html[data-sx-theme="dark"] #uploadSetupModal .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] #uploadSetupModal .btn-outline-secondary:hover,
    html[data-sx-theme="dark"] #editSetupModal .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] #editSetupModal .btn-outline-secondary:hover {
        background: rgba(255, 180, 180, 0.1) !important;
        color: #f3e9eb !important;
    }
    /* Alerts */
    html[data-sx-theme="dark"] .sx-fp .alert-success,
    html[data-bs-theme="dark"] .sx-fp .alert-success {
        background: rgba(20, 92, 42, 0.3) !important;
        border-color: rgba(140, 233, 154, 0.3) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-fp .alert-danger,
    html[data-bs-theme="dark"] .sx-fp .alert-danger {
        background: rgba(148, 0, 0, 0.28) !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
        color: #ffb4b4 !important;
    }
    /* Forms */
    html[data-sx-theme="dark"] .sx-fp .form-control,
    html[data-bs-theme="dark"] .sx-fp .form-control,
    html[data-sx-theme="dark"] .sx-fp .form-select,
    html[data-bs-theme="dark"] .sx-fp .form-select,
    html[data-sx-theme="dark"] #uploadSetupModal .form-control,
    html[data-bs-theme="dark"] #uploadSetupModal .form-control,
    html[data-sx-theme="dark"] #editSetupModal .form-control,
    html[data-bs-theme="dark"] #editSetupModal .form-control {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    /* Modals (outside .sx-fp) */
    html[data-sx-theme="dark"] #uploadSetupModal .modal-content,
    html[data-bs-theme="dark"] #uploadSetupModal .modal-content,
    html[data-sx-theme="dark"] #editSetupModal .modal-content,
    html[data-bs-theme="dark"] #editSetupModal .modal-content {
        background: #24181c !important;
        color: #f3e9eb !important;
        border-top-color: #940000 !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
    }
    html[data-sx-theme="dark"] #uploadSetupModal .modal-header,
    html[data-bs-theme="dark"] #uploadSetupModal .modal-header,
    html[data-sx-theme="dark"] #editSetupModal .modal-header,
    html[data-bs-theme="dark"] #editSetupModal .modal-header {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] #uploadSetupModal .modal-title,
    html[data-bs-theme="dark"] #uploadSetupModal .modal-title,
    html[data-sx-theme="dark"] #editSetupModal .modal-title,
    html[data-bs-theme="dark"] #editSetupModal .modal-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #uploadSetupModal .modal-body,
    html[data-bs-theme="dark"] #uploadSetupModal .modal-body,
    html[data-sx-theme="dark"] #editSetupModal .modal-body,
    html[data-bs-theme="dark"] #editSetupModal .modal-body {
        background: #24181c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #uploadSetupModal .modal-footer,
    html[data-bs-theme="dark"] #uploadSetupModal .modal-footer,
    html[data-sx-theme="dark"] #editSetupModal .modal-footer,
    html[data-bs-theme="dark"] #editSetupModal .modal-footer {
        background: #24181c !important;
        border-top-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] #uploadSetupModal .btn-close,
    html[data-bs-theme="dark"] #uploadSetupModal .btn-close,
    html[data-sx-theme="dark"] #editSetupModal .btn-close,
    html[data-bs-theme="dark"] #editSetupModal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
</style>

<div class="sx-fp">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-fp-stat is-status {{ $setup ? '' : 'is-empty' }} h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.fingerprint_setup.status') }}</span>
                        <span class="stat-value">{{ $setup ? __('superadmin.fingerprint_setup.status_ready') : __('superadmin.fingerprint_setup.status_not_set') }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-fingerprint"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-fp-stat is-version h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.fingerprint_setup.version') }}</span>
                        <span class="stat-value">{{ $setup?->version ?: '—' }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-settings"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-fp-stat is-size h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.fingerprint_setup.file_size') }}</span>
                        <span class="stat-value">{{ $setup?->file_size_human ?: '—' }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-file"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-fp-stat is-limit h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.fingerprint_setup.upload_limit') }}</span>
                        <span class="stat-value">{{ $phpUploadLimits['upload_max_filesize'] ?? '?' }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-upload"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-fp-card">
        <div class="card-body">
            <div class="sx-fp-card-head">
                <h6 class="sx-fp-card-title"><i class="fi fi-rr-fingerprint me-1"></i> {{ __('superadmin.fingerprint_setup.desktop_agent') }}</h6>
                <span class="small text-muted">{{ __('superadmin.fingerprint_setup.one_setup') }}</span>
            </div>

            @if($setup)
                <div class="sx-setup-hero">
                    <div class="d-flex gap-3 align-items-start flex-grow-1">
                        <span class="sx-setup-icon" aria-hidden="true"><i class="fi fi-rr-fingerprint"></i></span>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h5 class="sx-setup-title">{{ $setup->title }}</h5>
                                <span class="sx-badge is-active">{{ __('superadmin.fingerprint_setup.active') }}</span>
                            </div>
                            <div class="sx-meta-row">
                                <span class="sx-meta-item"><i class="fi fi-rr-file"></i>{{ $setup->original_filename }}</span>
                                <span class="sx-meta-item"><i class="fi fi-rr-hdd"></i>{{ $setup->file_size_human }}</span>
                                @if($setup->version)
                                    <span class="sx-meta-item"><i class="fi fi-rr-settings"></i>{{ __('superadmin.fingerprint_setup.version_prefix', ['version' => $setup->version]) }}</span>
                                @endif
                                <span class="sx-meta-item"><i class="fi fi-rr-clock"></i>{{ __('superadmin.fingerprint_setup.uploaded_at', ['date' => $setup->created_at?->format('d M Y H:i')]) }}</span>
                            </div>
                            <div class="fp-setup-download-progress d-none mt-3" style="max-width: 360px;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fp-setup-download-label">{{ __('superadmin.fingerprint_setup.downloading') }}</span>
                                    <strong class="fp-setup-download-percent">0%</strong>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 0.35rem; overflow: hidden;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated fp-setup-download-bar" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button"
                            class="btn btn-outline-success fp-setup-download-btn"
                            data-download-url="{{ route('fingerprint_attendance_setup.download', $setup->setupID) }}"
                            data-filename="{{ $setup->original_filename ?: 'fingerprint_setup.zip' }}">
                            <i class="fi fi-rr-download me-1"></i> {{ __('superadmin.fingerprint_setup.download') }}
                        </button>
                        <button type="button" class="btn btn-outline-primary edit-setup-btn"
                            data-setup='@json($setup)'>
                            <i class="fi fi-rr-edit me-1"></i> {{ __('superadmin.fingerprint_setup.edit') }}
                        </button>
                        <form action="{{ route('superadmin.fingerprint_attendance_setup.destroy', $setup->setupID) }}" method="POST" class="d-inline" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('superadmin.fingerprint_setup.delete_title') . "\n\n" . __('superadmin.fingerprint_setup.delete_text')) }});">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger"><i class="fi fi-rr-trash me-1"></i> {{ __('superadmin.fingerprint_setup.delete') }}</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="sx-empty">
                    <div class="sx-empty-ico" aria-hidden="true"><i class="fi fi-rr-upload"></i></div>
                    <h6 class="fw-bold mb-2">{{ __('superadmin.fingerprint_setup.no_setup') }}</h6>
                    <p class="text-muted mb-3">{{ __('superadmin.fingerprint_setup.click') }} <strong>{{ __('superadmin.fingerprint_setup.upload_setup') }}</strong> {{ __('superadmin.fingerprint_setup.to_add') }}</p>
                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#uploadSetupModal">
                        <i class="fi fi-rr-upload me-1"></i> {{ __('superadmin.fingerprint_setup.upload_setup') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@if(!$setup)
<div class="modal fade" id="uploadSetupModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="uploadSetupForm" action="{{ route('superadmin.fingerprint_attendance_setup.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fi fi-rr-upload me-1"></i> {{ __('superadmin.fingerprint_setup.upload_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="uploadModalClose"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('superadmin.fingerprint_setup.app_name') }} *</label>
                        <input type="text" name="title" class="form-control" required placeholder="{{ __('superadmin.fingerprint_setup.app_name_placeholder') }}" value="{{ old('title') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('superadmin.fingerprint_setup.version_label') }}</label>
                        <input type="text" name="version" class="form-control" placeholder="{{ __('superadmin.fingerprint_setup.version_placeholder') }}" value="{{ old('version') }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">{{ __('superadmin.fingerprint_setup.setup_file') }} *</label>
                        <input type="file" name="setup_file" class="form-control" required accept=".zip,.exe,.msi,.rar,.7z">
                        <small class="text-muted">{{ __('superadmin.fingerprint_setup.file_hint') }}</small>
                    </div>
                    <div id="uploadProgressWrap" class="fp-upload-progress d-none mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="uploadProgressLabel">{{ __('superadmin.fingerprint_setup.uploading') }}</span>
                            <strong id="uploadPercentText">0%</strong>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 0.35rem; overflow: hidden;">
                            <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="uploadCancelBtn">{{ __('superadmin.fingerprint_setup.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="uploadSubmitBtn">
                        <i class="fi fi-rr-upload me-1"></i> {{ __('superadmin.fingerprint_setup.upload') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="editSetupModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editSetupForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fi fi-rr-edit me-1"></i> {{ __('superadmin.fingerprint_setup.edit_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="editModalClose"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('superadmin.fingerprint_setup.app_name') }} *</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('superadmin.fingerprint_setup.version_label') }}</label>
                        <input type="text" name="version" id="edit_version" class="form-control">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">{{ __('superadmin.fingerprint_setup.replace_file') }}</label>
                        <input type="file" name="setup_file" id="edit_setup_file" class="form-control" accept=".zip,.exe,.msi,.rar,.7z">
                        <small class="text-muted" id="edit_current_file"></small>
                    </div>
                    <div id="editProgressWrap" class="fp-upload-progress d-none mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="editProgressLabel">{{ __('superadmin.fingerprint_setup.uploading') }}</span>
                            <strong id="editPercentText">0%</strong>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 0.35rem; overflow: hidden;">
                            <div id="editProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="editCancelBtn">{{ __('superadmin.fingerprint_setup.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="editSubmitBtn">
                        <i class="fi fi-rr-disk me-1"></i> {{ __('superadmin.fingerprint_setup.save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const SX = @json(__('superadmin.fingerprint_setup'));
const SXC = @json(__('superadmin.common'));
function sxFmt(str, map) {
    return Object.keys(map || {}).sort(function (a, b) { return b.length - a.length; }).reduce(function (s, k) { return s.split(':' + k).join(map[k]); }, String(str));
}
(function () {
    function resetProgress(wrapId, barId, textId, labelId) {
        var wrap = document.getElementById(wrapId);
        var bar = document.getElementById(barId);
        var text = document.getElementById(textId);
        var label = document.getElementById(labelId);
        if (wrap) wrap.classList.add('d-none');
        if (bar) {
            bar.style.width = '0%';
            bar.setAttribute('aria-valuenow', '0');
        }
        if (text) text.textContent = '0%';
        if (label) label.textContent = SX.js.uploading;
    }

    function showProgress(wrapId, barId, textId, labelId, percent, label) {
        var wrap = document.getElementById(wrapId);
        var bar = document.getElementById(barId);
        var text = document.getElementById(textId);
        var labelEl = document.getElementById(labelId);
        if (wrap) wrap.classList.remove('d-none');
        if (bar) {
            bar.style.width = percent + '%';
            bar.setAttribute('aria-valuenow', String(percent));
        }
        if (text) text.textContent = percent + '%';
        if (labelEl && label) labelEl.textContent = label;
    }

    function ajaxErrorMessage(xhr) {
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) return xhr.responseJSON.message;
            if (xhr.responseJSON.error) return xhr.responseJSON.error;
            if (xhr.responseJSON.errors) {
                var errs = xhr.responseJSON.errors;
                return Object.values(errs).flat().join('\n');
            }
        }
        if (xhr.status === 413) return SX.js.file_too_large;
        if (xhr.status === 419) return SX.js.session_expired;
        if (xhr.status === 0) return SX.js.network_error;
        if (xhr.status >= 500) return sxFmt(SX.js.server_error, { status: xhr.status });
        if (xhr.status === 422) return SX.js.upload_failed;
        return sxFmt(SX.js.request_failed, { status: xhr.status });
    }

    function submitWithProgress(form, options) {
        var $form = $(form);
        var $btn = $(options.submitBtn);
        var $cancel = $(options.cancelBtns);
        var $close = $(options.closeBtns);
        var hasFile = options.hasFile !== false;

        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        $btn.prop('disabled', true);
        $cancel.prop('disabled', true);
        $close.prop('disabled', true);

        if (hasFile) {
            resetProgress(options.wrapId, options.barId, options.textId, options.labelId);
            showProgress(options.wrapId, options.barId, options.textId, options.labelId, 0, SX.js.uploading);
        }

        $.ajax({
            url: options.url,
            type: options.method || 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': @json(csrf_token()),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                if (!hasFile) return xhr;

                xhr.upload.addEventListener('progress', function (evt) {
                    if (!evt.lengthComputable) return;
                    var percent = Math.min(100, Math.round((evt.loaded / evt.total) * 100));
                    var label = percent >= 100 ? SX.js.processing : SX.js.uploading;
                    showProgress(options.wrapId, options.barId, options.textId, options.labelId, percent, label);
                }, false);

                return xhr;
            },
            success: function (res) {
                if (hasFile) {
                    showProgress(options.wrapId, options.barId, options.textId, options.labelId, 100, SX.js.complete);
                }
                Swal.fire(SX.js.success, res.message || SX.js.saved, 'success').then(function () {
                    location.reload();
                });
            },
            error: function (xhr) {
                Swal.fire(SX.js.error, ajaxErrorMessage(xhr), 'error');
                $btn.prop('disabled', false);
                $cancel.prop('disabled', false);
                $close.prop('disabled', false);
                if (hasFile) {
                    resetProgress(options.wrapId, options.barId, options.textId, options.labelId);
                }
            }
        });
    }

    document.querySelectorAll('.edit-setup-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var s = JSON.parse(this.getAttribute('data-setup'));
            document.getElementById('editSetupForm').action = @json(url('super-admin/fingerprint-attendance-setup')) + '/' + s.setupID;
            document.getElementById('edit_title').value = s.title || '';
            document.getElementById('edit_version').value = s.version || '';
            document.getElementById('edit_current_file').textContent = sxFmt(SX.js.current, { name: s.original_filename || '', size: s.file_size_human || '' });
            var fileInput = document.getElementById('edit_setup_file');
            if (fileInput) fileInput.value = '';
            resetProgress('editProgressWrap', 'editProgressBar', 'editPercentText', 'editProgressLabel');
            new bootstrap.Modal(document.getElementById('editSetupModal')).show();
        });
    });

    var uploadForm = document.getElementById('uploadSetupForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitWithProgress(uploadForm, {
                url: uploadForm.action,
                method: 'POST',
                submitBtn: '#uploadSubmitBtn',
                cancelBtns: '#uploadCancelBtn',
                closeBtns: '#uploadModalClose',
                wrapId: 'uploadProgressWrap',
                barId: 'uploadProgressBar',
                textId: 'uploadPercentText',
                labelId: 'uploadProgressLabel',
                hasFile: true
            });
        });
    }

    var editForm = document.getElementById('editSetupForm');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fileInput = document.getElementById('edit_setup_file');
            var hasNewFile = fileInput && fileInput.files && fileInput.files.length > 0;

            submitWithProgress(editForm, {
                url: editForm.action,
                method: 'POST',
                submitBtn: '#editSubmitBtn',
                cancelBtns: '#editCancelBtn',
                closeBtns: '#editModalClose',
                wrapId: 'editProgressWrap',
                barId: 'editProgressBar',
                textId: 'editPercentText',
                labelId: 'editProgressLabel',
                hasFile: hasNewFile
            });
        });
    }
})();
</script>
@endpush
@include('partials.fingerprint_setup_download_ajax')
@endsection
