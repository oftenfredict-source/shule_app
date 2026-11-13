@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.change_password'))
@section('page_breadcrumb', __('superadmin.pages.change_password'))

@section('page_actions')
    <a href="{{ route('superAdminDashboard') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-apps me-1"></i> {{ __('superadmin.change_password.dashboard') }}
    </a>
@endsection

@section('content')
<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp span:not(.sx-badge):not(.is-ok):not(.is-bad),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cp a:not(.btn) {
        color: #000 !important;
    }
    .sx-cp .btn-primary,
    .sx-cp a.btn-primary,
    .sx-cp .btn-primary i,
    .sx-cp .btn-primary i:before,
    .sx-cp .text-white {
        color: #fff !important;
    }
    .sx-cp .sx-cp-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-cp .sx-cp-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-cp .sx-cp-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-cp .sx-cp-stat .stat-value {
        display: block;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.25;
        color: #000 !important;
    }
    .sx-cp .sx-cp-stat .stat-ico {
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
    .sx-cp .sx-cp-stat .stat-ico i,
    .sx-cp .sx-cp-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-cp .sx-cp-stat.is-lock .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-cp .sx-cp-stat.is-rules .stat-ico { background: #fff0e8; color: #a34a00 !important; }
    .sx-cp .sx-cp-stat.is-match .stat-ico { background: #e8f7ec; color: #145c2a !important; }
    .sx-cp .sx-cp-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-cp .sx-cp-card .card-body { padding: 1.25rem 1.35rem 1.4rem; }
    .sx-cp .sx-cp-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-cp .sx-cp-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-cp .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-cp .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 42px;
        color: #000;
    }
    .sx-cp .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-cp .input-group-text {
        border-radius: 0.45rem 0 0 0.45rem;
        border-color: #d9d9d9;
        background: #fafafa;
        color: #940000;
    }
    .sx-cp .input-group .form-control {
        border-radius: 0 0.45rem 0.45rem 0;
    }
    .sx-cp .input-group .form-control.border-start-0 {
        border-left: 0;
    }
    .sx-cp .input-group-text.border-end-0 {
        border-right: 0;
    }
    .sx-cp .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-cp .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-cp .btn-primary:disabled {
        opacity: 0.55;
    }
    .sx-cp .sx-req-box {
        border: 1px solid #f0dada;
        background: #fffafa;
        border-radius: 0.45rem;
        padding: 0.85rem 1rem;
        margin-top: 0.65rem;
    }
    .sx-cp .sx-req-title {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #940000 !important;
        margin-bottom: 0.45rem;
    }
    .sx-cp .requirement-item.is-ok { color: #145c2a !important; }
    .sx-cp .requirement-item.is-bad { color: #940000 !important; }
    .sx-cp .requirement-item i:before { color: inherit !important; }
    .sx-cp .alert {
        border-radius: 0.45rem;
    }
    .sx-cp .sx-confirm-ok { color: #145c2a !important; }
    .sx-cp .sx-confirm-bad { color: #940000 !important; }
    .sx-cp .sx-strength {
        margin-top: 0.7rem;
    }
    .sx-cp .sx-strength-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.4rem;
    }
    .sx-cp .sx-strength-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #666 !important;
    }
    .sx-cp .sx-strength-text {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .sx-cp .sx-strength-bar {
        height: 8px;
        border-radius: 999px;
        background: #ececec;
        overflow: hidden;
    }
    .sx-cp .sx-strength-fill {
        height: 100%;
        width: 0%;
        border-radius: 999px;
        transition: width 0.25s ease, background-color 0.25s ease;
        background: #c9c9c9;
    }
    .sx-cp .sx-strength.is-empty .sx-strength-text { color: #888 !important; }
    .sx-cp .sx-strength.is-very-weak .sx-strength-text { color: #940000 !important; }
    .sx-cp .sx-strength.is-very-weak .sx-strength-fill { background: #940000; }
    .sx-cp .sx-strength.is-weak .sx-strength-text { color: #c2410c !important; }
    .sx-cp .sx-strength.is-weak .sx-strength-fill { background: #e8590c; }
    .sx-cp .sx-strength.is-fair .sx-strength-text { color: #a34a00 !important; }
    .sx-cp .sx-strength.is-fair .sx-strength-fill { background: #f59f00; }
    .sx-cp .sx-strength.is-strong .sx-strength-text { color: #2b8a3e !important; }
    .sx-cp .sx-strength.is-strong .sx-strength-fill { background: #37b24d; }
    .sx-cp .sx-strength.is-very-strong .sx-strength-text { color: #145c2a !important; }
    .sx-cp .sx-strength.is-very-strong .sx-strength-fill { background: #087f5b; }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-cp,
    html[data-bs-theme="dark"] .sx-cp,
    html[data-sx-theme="dark"] .sx-cp p,
    html[data-bs-theme="dark"] .sx-cp p,
    html[data-sx-theme="dark"] .sx-cp span:not(.sx-badge):not(.is-ok):not(.is-bad),
    html[data-bs-theme="dark"] .sx-cp span:not(.sx-badge):not(.is-ok):not(.is-bad),
    html[data-sx-theme="dark"] .sx-cp small,
    html[data-bs-theme="dark"] .sx-cp small,
    html[data-sx-theme="dark"] .sx-cp strong,
    html[data-bs-theme="dark"] .sx-cp strong,
    html[data-sx-theme="dark"] .sx-cp h1,
    html[data-bs-theme="dark"] .sx-cp h1,
    html[data-sx-theme="dark"] .sx-cp h2,
    html[data-bs-theme="dark"] .sx-cp h2,
    html[data-sx-theme="dark"] .sx-cp h3,
    html[data-bs-theme="dark"] .sx-cp h3,
    html[data-sx-theme="dark"] .sx-cp h4,
    html[data-bs-theme="dark"] .sx-cp h4,
    html[data-sx-theme="dark"] .sx-cp h5,
    html[data-bs-theme="dark"] .sx-cp h5,
    html[data-sx-theme="dark"] .sx-cp h6,
    html[data-bs-theme="dark"] .sx-cp h6,
    html[data-sx-theme="dark"] .sx-cp .sx-cp-card-title,
    html[data-bs-theme="dark"] .sx-cp .sx-cp-card-title,
    html[data-sx-theme="dark"] .sx-cp .stat-label,
    html[data-bs-theme="dark"] .sx-cp .stat-label,
    html[data-sx-theme="dark"] .sx-cp .stat-value,
    html[data-bs-theme="dark"] .sx-cp .stat-value,
    html[data-sx-theme="dark"] .sx-cp a:not(.btn),
    html[data-bs-theme="dark"] .sx-cp a:not(.btn) {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-cp .text-muted,
    html[data-bs-theme="dark"] .sx-cp .text-muted,
    html[data-sx-theme="dark"] .sx-cp .form-label,
    html[data-bs-theme="dark"] .sx-cp .form-label,
    html[data-sx-theme="dark"] .sx-cp .sx-strength-label,
    html[data-bs-theme="dark"] .sx-cp .sx-strength-label {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-cp .sx-cp-stat,
    html[data-bs-theme="dark"] .sx-cp .sx-cp-stat,
    html[data-sx-theme="dark"] .sx-cp .sx-cp-card,
    html[data-bs-theme="dark"] .sx-cp .sx-cp-card,
    html[data-sx-theme="dark"] .sx-cp .card,
    html[data-bs-theme="dark"] .sx-cp .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-cp .sx-cp-card-head,
    html[data-bs-theme="dark"] .sx-cp .sx-cp-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-cp .form-control,
    html[data-bs-theme="dark"] .sx-cp .form-control {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-cp .form-control:focus,
    html[data-bs-theme="dark"] .sx-cp .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.22) !important;
    }
    html[data-sx-theme="dark"] .sx-cp .input-group-text,
    html[data-bs-theme="dark"] .sx-cp .input-group-text {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cp .sx-req-box,
    html[data-bs-theme="dark"] .sx-cp .sx-req-box {
        background: rgba(148, 0, 0, 0.16) !important;
        border-color: rgba(255, 180, 180, 0.22) !important;
    }
    html[data-sx-theme="dark"] .sx-cp .sx-req-title,
    html[data-bs-theme="dark"] .sx-cp .sx-req-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cp .requirement-item.is-ok,
    html[data-bs-theme="dark"] .sx-cp .requirement-item.is-ok,
    html[data-sx-theme="dark"] .sx-cp .sx-confirm-ok,
    html[data-bs-theme="dark"] .sx-cp .sx-confirm-ok {
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-cp .requirement-item.is-bad,
    html[data-bs-theme="dark"] .sx-cp .requirement-item.is-bad,
    html[data-sx-theme="dark"] .sx-cp .sx-confirm-bad,
    html[data-bs-theme="dark"] .sx-cp .sx-confirm-bad {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cp .sx-strength-bar,
    html[data-bs-theme="dark"] .sx-cp .sx-strength-bar {
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] .sx-cp .sx-strength.is-empty .sx-strength-text,
    html[data-bs-theme="dark"] .sx-cp .sx-strength.is-empty .sx-strength-text {
        color: #8a7579 !important;
    }
    html[data-sx-theme="dark"] .sx-cp .sx-cp-stat.is-lock .stat-ico,
    html[data-bs-theme="dark"] .sx-cp .sx-cp-stat.is-lock .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cp .sx-cp-stat.is-rules .stat-ico,
    html[data-bs-theme="dark"] .sx-cp .sx-cp-stat.is-rules .stat-ico {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-cp .sx-cp-stat.is-match .stat-ico,
    html[data-bs-theme="dark"] .sx-cp .sx-cp-stat.is-match .stat-ico {
        background: rgba(20, 92, 42, 0.28) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-cp .btn-primary,
    html[data-bs-theme="dark"] .sx-cp .btn-primary {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-cp .alert-success,
    html[data-bs-theme="dark"] .sx-cp .alert-success {
        background: rgba(20, 92, 42, 0.28) !important;
        border-color: rgba(140, 233, 154, 0.28) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-cp .alert-danger,
    html[data-bs-theme="dark"] .sx-cp .alert-danger {
        background: rgba(148, 0, 0, 0.28) !important;
        border-color: rgba(255, 180, 180, 0.22) !important;
        color: #ffb4b4 !important;
    }
</style>

<div class="sx-cp">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card sx-cp-stat is-lock h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.change_password.account') }}</span>
                        <span class="stat-value">{{ __('superadmin.change_password.super_admin') }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card sx-cp-stat is-rules h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.change_password.min_length') }}</span>
                        <span class="stat-value">{{ __('superadmin.change_password.min_length_value') }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-key"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-xl-4">
            <div class="card sx-cp-stat is-match h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.change_password.status') }}</span>
                        <span class="stat-value" id="cpLiveStatus">{{ __('superadmin.change_password.status_waiting') }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-shield-check"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fi fi-rr-check me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fi fi-rr-exclamation me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card sx-cp-card">
                <div class="card-body">
                    <div class="sx-cp-card-head">
                        <h6 class="sx-cp-card-title"><i class="fi fi-rr-lock me-1"></i> {{ __('superadmin.change_password.update_password') }}</h6>
                        <span class="small text-muted">{{ __('superadmin.change_password.update_password_desc') }}</span>
                    </div>

                    <form id="superAdminChangePasswordForm" method="POST" action="{{ route('superadmin.change_password.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="current_password">{{ __('superadmin.change_password.current_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="fi fi-rr-key"></i></span>
                                <input type="password" name="current_password" id="current_password" class="form-control border-start-0" placeholder="{{ __('superadmin.change_password.current_placeholder') }}" required autocomplete="current-password">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="super_new_password">{{ __('superadmin.change_password.new_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="fi fi-rr-unlock"></i></span>
                                <input type="password" name="new_password" id="super_new_password" class="form-control border-start-0" placeholder="{{ __('superadmin.change_password.new_placeholder') }}" required autocomplete="new-password">
                            </div>
                            <div class="sx-strength is-empty" id="superStrengthMeter" aria-live="polite">
                                <div class="sx-strength-meta">
                                    <span class="sx-strength-label">{{ __('superadmin.change_password.strength') }}</span>
                                    <span class="sx-strength-text" id="superStrengthText">—</span>
                                </div>
                                <div class="sx-strength-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="superStrengthBar">
                                    <div class="sx-strength-fill" id="superStrengthFill"></div>
                                </div>
                            </div>
                            <div class="sx-req-box" id="super_requirements_card">
                                <div class="sx-req-title">{{ __('superadmin.change_password.checklist') }}</div>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="requirement-item is-bad" id="super_req_len">
                                        <i class="fi fi-rr-cross-small me-1 align-middle" id="super_icon_len"></i> {{ __('superadmin.change_password.rule_length') }}
                                    </li>
                                    <li class="requirement-item is-bad" id="super_req_lower">
                                        <i class="fi fi-rr-cross-small me-1 align-middle" id="super_icon_lower"></i> {{ __('superadmin.change_password.rule_lower') }}
                                    </li>
                                    <li class="requirement-item is-bad" id="super_req_upper">
                                        <i class="fi fi-rr-cross-small me-1 align-middle" id="super_icon_upper"></i> {{ __('superadmin.change_password.rule_upper') }}
                                    </li>
                                    <li class="requirement-item is-bad" id="super_req_num">
                                        <i class="fi fi-rr-cross-small me-1 align-middle" id="super_icon_num"></i> {{ __('superadmin.change_password.rule_number') }}
                                    </li>
                                    <li class="requirement-item is-bad" id="super_req_special">
                                        <i class="fi fi-rr-cross-small me-1 align-middle" id="super_icon_special"></i> {{ __('superadmin.change_password.rule_special') }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="super_confirm_password">{{ __('superadmin.change_password.confirm_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="fi fi-rr-lock"></i></span>
                                <input type="password" name="new_password_confirmation" id="super_confirm_password" class="form-control border-start-0" placeholder="{{ __('superadmin.change_password.confirm_placeholder') }}" required autocomplete="new-password">
                            </div>
                            <div class="mt-2 small" id="super_confirm_feedback" style="display: none;">
                                <span id="super_confirm_status"></span>
                            </div>
                        </div>

                        <button type="submit" id="superAdminSubmitBtn" class="btn btn-primary waves-effect waves-light w-100 py-2" disabled>
                            <i class="fi fi-rr-disk me-1"></i> {{ __('superadmin.change_password.update_password') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const SX = @json(__('superadmin.change_password'));
    const SXC = @json(__('superadmin.common'));
    const newPasswordInput = $('#super_new_password');
    const confirmPasswordInput = $('#super_confirm_password');
    const submitBtn = $('#superAdminSubmitBtn');

    let state = {
        length: false,
        match: false,
        lower: false,
        upper: false,
        number: false,
        special: false
    };

    function updateLiveStatus() {
        if (state.length && state.match) {
            $('#cpLiveStatus').text(SX.js.ready);
        } else if (newPasswordInput.val() || confirmPasswordInput.val()) {
            $('#cpLiveStatus').text(SX.js.incomplete);
        } else {
            $('#cpLiveStatus').text(SX.js.waiting);
        }
    }

    function updateRequirementUI(elementId, iconId, isMet) {
        const element = $(elementId);
        const icon = $(iconId);
        if (isMet) {
            element.removeClass('is-bad').addClass('is-ok');
            icon.removeClass('fi-rr-cross-small').addClass('fi-rr-check');
        } else {
            element.removeClass('is-ok').addClass('is-bad');
            icon.removeClass('fi-rr-check').addClass('fi-rr-cross-small');
        }
    }

    function scorePassword(pw) {
        let score = 0;
        if (!pw) return 0;
        if (pw.length >= 6) score += 1;
        if (pw.length >= 10) score += 1;
        if (/[a-z]/.test(pw)) score += 1;
        if (/[A-Z]/.test(pw)) score += 1;
        if (/[0-9]/.test(pw)) score += 1;
        if (/[^A-Za-z0-9]/.test(pw)) score += 1;
        return Math.min(score, 5);
    }

    function updateStrengthMeter(pw) {
        const meter = $('#superStrengthMeter');
        const fill = $('#superStrengthFill');
        const text = $('#superStrengthText');
        const bar = $('#superStrengthBar');
        const score = scorePassword(pw);
        const levels = [
            { cls: 'is-empty', label: '—', pct: 0 },
            { cls: 'is-very-weak', label: SX.js.strength_very_weak, pct: 20 },
            { cls: 'is-weak', label: SX.js.strength_weak, pct: 40 },
            { cls: 'is-fair', label: SX.js.strength_fair, pct: 60 },
            { cls: 'is-strong', label: SX.js.strength_strong, pct: 80 },
            { cls: 'is-very-strong', label: SX.js.strength_very_strong, pct: 100 }
        ];
        const level = pw ? levels[score] || levels[1] : levels[0];

        meter.removeClass('is-empty is-very-weak is-weak is-fair is-strong is-very-strong').addClass(level.cls);
        fill.css('width', level.pct + '%');
        text.text(level.label);
        bar.attr('aria-valuenow', level.pct);
    }

    function checkPassword() {
        const pw = newPasswordInput.val();
        state.length = pw.length >= 6;
        state.lower = /[a-z]/.test(pw);
        state.upper = /[A-Z]/.test(pw);
        state.number = /[0-9]/.test(pw);
        state.special = /[^A-Za-z0-9]/.test(pw);

        updateRequirementUI('#super_req_len', '#super_icon_len', state.length);
        updateRequirementUI('#super_req_lower', '#super_icon_lower', state.lower);
        updateRequirementUI('#super_req_upper', '#super_icon_upper', state.upper);
        updateRequirementUI('#super_req_num', '#super_icon_num', state.number);
        updateRequirementUI('#super_req_special', '#super_icon_special', state.special);
        updateStrengthMeter(pw);
        checkMatch();
    }

    function checkMatch() {
        const pw = newPasswordInput.val();
        const confirm = confirmPasswordInput.val();
        const feedback = $('#super_confirm_feedback');
        const statusSpan = $('#super_confirm_status');

        if (!confirm) {
            feedback.hide();
            state.match = false;
            toggleSubmit();
            return;
        }

        feedback.show();
        if (pw === confirm) {
            state.match = true;
            statusSpan.html('<i class="fi fi-rr-check me-1"></i> ' + SX.js.match).removeClass('sx-confirm-bad').addClass('sx-confirm-ok');
        } else {
            state.match = false;
            statusSpan.html('<i class="fi fi-rr-cross-small me-1"></i> ' + SX.js.mismatch).removeClass('sx-confirm-ok').addClass('sx-confirm-bad');
        }

        toggleSubmit();
    }

    function toggleSubmit() {
        const allPassed = state.length && state.match;
        submitBtn.prop('disabled', !allPassed);
        updateLiveStatus();
    }

    newPasswordInput.on('input', checkPassword);
    confirmPasswordInput.on('input', checkMatch);

    $('#superAdminChangePasswordForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const btn = $('#superAdminSubmitBtn');
        const originalBtnHtml = btn.html();

        $('.alert-dynamic').remove();
        btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + SX.js.updating).attr('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                btn.html(originalBtnHtml).attr('disabled', false);

                if (response.success) {
                    Swal.fire({
                        title: SX.js.success,
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#940000',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        form.find('input[type="password"]').val('');
                        $('#super_confirm_feedback').hide();
                        ['#super_req_len', '#super_req_lower', '#super_req_upper', '#super_req_num', '#super_req_special'].forEach(function (id) {
                            $(id).removeClass('is-ok').addClass('is-bad');
                        });
                        ['#super_icon_len', '#super_icon_lower', '#super_icon_upper', '#super_icon_num', '#super_icon_special'].forEach(function (id) {
                            $(id).removeClass('fi-rr-check').addClass('fi-rr-cross-small');
                        });
                        updateStrengthMeter('');
                        state = { length: false, match: false, lower: false, upper: false, number: false, special: false };
                        toggleSubmit();
                    });
                } else {
                    Swal.fire({
                        title: SX.js.error,
                        text: response.message || SX.js.generic_error,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    toggleSubmit();
                }
            },
            error: function(xhr) {
                btn.html(originalBtnHtml).attr('disabled', false);

                let errorMsg = SX.js.failed;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show alert-dynamic mb-3" role="alert">
                        <strong>${SX.js.error_label}</strong> ${errorMsg}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                form.before(alertHtml);

                Swal.fire({
                    title: SX.js.error,
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
                toggleSubmit();
            }
        });
    });
});
</script>
@endpush
