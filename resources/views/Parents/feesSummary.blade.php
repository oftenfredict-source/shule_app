@extends('layouts.parent_corp')

@section('title', __('parent.fees_summary') . ' - ShuleXpert')
@section('page_title', __('parent.fees_summary'))
@section('page_breadcrumb', __('parent.nav_fees_summary'))

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees span:not(.badge):not(.status-pill),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees .text-muted {
        color: #000 !important;
    }
    .sx-parent-fees .btn-primary,
    .sx-parent-fees a.btn-primary,
    .sx-parent-fees .badge {
        color: #fff !important;
    }
    .sx-parent-fees .total-banner,
    .sx-parent-fees .total-banner *,
    .sx-parent-fees .total-banner .total-banner-lbl,
    .sx-parent-fees .total-banner .total-banner-val,
    .sx-parent-fees .total-banner .total-banner-item,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees .total-banner,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees .total-banner *,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees .total-banner div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees .total-banner .total-banner-lbl,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees .total-banner .total-banner-val,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-fees .total-banner .total-banner-item,
    html[data-sx-theme="dark"] .sx-parent-fees .total-banner,
    html[data-bs-theme="dark"] .sx-parent-fees .total-banner,
    html[data-sx-theme="dark"] .sx-parent-fees .total-banner *,
    html[data-bs-theme="dark"] .sx-parent-fees .total-banner *,
    html[data-sx-theme="dark"] .sx-parent-fees .total-banner div,
    html[data-bs-theme="dark"] .sx-parent-fees .total-banner div,
    html[data-sx-theme="dark"] .sx-parent-fees .total-banner .total-banner-lbl,
    html[data-bs-theme="dark"] .sx-parent-fees .total-banner .total-banner-lbl,
    html[data-sx-theme="dark"] .sx-parent-fees .total-banner .total-banner-val,
    html[data-bs-theme="dark"] .sx-parent-fees .total-banner .total-banner-val {
        color: #fff !important;
    }
    .sx-parent-fees .card,
    .sx-parent-fees .panel-card,
    .sx-parent-fees .student-fee-card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-fees .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-fees .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-parent-fees .hint-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        padding: 0.85rem 1rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-fees .section-label {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #940000 !important;
        margin-bottom: 0.85rem;
    }
    .sx-parent-fees .year-select {
        border: 1px solid rgba(148, 0, 0, 0.25);
        font-weight: 700;
        color: #940000;
        min-width: 120px;
    }
    .sx-parent-fees .student-fee-card {
        overflow: hidden;
        margin-bottom: 0;
    }
    .sx-parent-fees .student-fee-header {
        background: #fffaf9;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f0e6e6;
        border-top: 4px solid #940000;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }
    .sx-parent-fees .student-name {
        font-weight: 800;
        font-size: 1rem;
        color: #222 !important;
        line-height: 1.3;
    }
    .sx-parent-fees .student-meta {
        font-size: 0.75rem;
        color: #777 !important;
        font-weight: 600;
        margin-top: 0.15rem;
    }
    .sx-parent-fees .status-pill {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.4rem 0.7rem;
        border-radius: 0;
        display: inline-block;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .sx-parent-fees .status-pill.pending {
        background: #fff3cd;
        color: #664d03 !important;
        border-color: #ffe69c;
    }
    .sx-parent-fees .status-pill.clear {
        background: #d1e7dd;
        color: #0f5132 !important;
        border-color: #a3cfbb;
    }
    .sx-parent-fees .fee-body {
        padding: 1.15rem 1.25rem;
    }
    .sx-parent-fees .fee-type-box {
        padding: 1.1rem 1.15rem;
        border: 1px solid #e6e6e6;
        border-top: 3px solid #940000;
        background: #fff;
        height: 100%;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }
    .sx-parent-fees .fee-type-box.is-tuition { border-top-color: #28a745; }
    .sx-parent-fees .fee-type-box.is-other { border-top-color: #0dcaf0; }
    .sx-parent-fees .fee-title {
        font-size: 0.72rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.04em;
        margin-bottom: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: #940000 !important;
    }
    .sx-parent-fees .fee-type-box.is-tuition .fee-title { color: #198754 !important; }
    .sx-parent-fees .fee-type-box.is-other .fee-title { color: #0aa2c0 !important; }
    .sx-parent-fees .fee-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.55rem;
        font-size: 0.85rem;
    }
    .sx-parent-fees .fee-row.is-balance {
        border-top: 1px solid #f0e6e6;
        padding-top: 0.65rem;
        margin-top: 0.35rem;
        margin-bottom: 0;
    }
    .sx-parent-fees .fee-lbl {
        color: #888 !important;
        font-weight: 600;
    }
    .sx-parent-fees .fee-val {
        font-weight: 800;
        color: #222 !important;
        white-space: nowrap;
    }
    .sx-parent-fees .fee-val.is-paid { color: #28a745 !important; }
    .sx-parent-fees .fee-val.is-balance { color: #dc3545 !important; }
    .sx-parent-fees .control-number-banner {
        background: #fff5f5;
        border: 1px dashed #940000;
        padding: 0.7rem 0.75rem;
        margin-top: 0.95rem;
        text-align: center;
    }
    .sx-parent-fees .control-number-banner .c-lbl {
        font-size: 0.62rem;
        color: #940000 !important;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.25rem;
    }
    .sx-parent-fees .control-number-banner .c-num {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 1.05rem;
        font-weight: 800;
        color: #940000 !important;
        word-break: break-all;
    }
    .sx-parent-fees .fee-action-btn {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-radius: 0;
        margin-top: 0.95rem;
        padding: 0.45rem 0.75rem;
    }
    .sx-parent-fees .total-banner {
        background: #940000;
        padding: 1rem 1.15rem;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        text-align: center;
        gap: 0.75rem;
    }
    .sx-parent-fees .total-banner-item {
        flex: 1 1 120px;
        min-width: 100px;
    }
    .sx-parent-fees .total-banner-val {
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .sx-parent-fees .total-banner-lbl {
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        opacity: 0.85;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .sx-parent-fees .empty-state {
        text-align: center;
        padding: 2.75rem 1.25rem;
        background: #fff;
        border: 1px solid #e6e6e6;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-fees .empty-state i {
        font-size: 2.4rem;
        color: #940000;
        opacity: 0.5;
        display: block;
        margin-bottom: 0.75rem;
    }
    .sx-parent-fees .loading-state {
        text-align: center;
        padding: 2.5rem 1rem;
        background: #fff;
        border: 1px solid #e6e6e6;
    }

    /* Modal */
    .sx-fees-modal .modal-content {
        border-radius: 0.35rem;
        border: 1px solid #e6e6e6;
        overflow: hidden;
    }
    .sx-fees-modal .modal-header {
        background: #940000;
        color: #fff;
        border-bottom: none;
        padding: 1rem 1.25rem;
    }
    .sx-fees-modal .modal-title {
        font-weight: 800;
        font-size: 1rem;
        color: #fff !important;
    }
    .sx-fees-modal .modal-body {
        padding: 0;
        background: #fff;
    }
    .sx-fees-modal .modal-footer {
        border-top: 1px solid #eee;
        background: #fff;
        padding: 0.85rem 1.15rem;
    }
    .sx-fees-modal .modal-subtitle {
        text-align: center;
        font-weight: 800;
        padding: 1rem 1.15rem 0.5rem;
        color: #940000 !important;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sx-fees-modal .inst-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }
    .sx-fees-modal .inst-table thead th {
        background: #fcf7f7;
        color: #940000 !important;
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 0.05em;
        font-weight: 800;
        border-bottom: 2px solid rgba(148, 0, 0, 0.18);
        padding: 0.85rem 1rem;
    }
    .sx-fees-modal .inst-table tbody td {
        border-bottom: 1px solid #f0ecec;
        padding: 0.9rem 1rem;
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .sx-fees-modal .inst-table tbody tr:last-child td {
        border-bottom: none;
    }
    .sx-fees-modal .inst-name {
        font-weight: 800;
        color: #222 !important;
    }
    .sx-fees-modal .inst-type {
        font-size: 0.72rem;
        color: #888 !important;
        font-weight: 600;
        margin-top: 0.1rem;
    }
    .sx-fees-modal .inst-amt {
        font-weight: 800;
        color: #940000 !important;
        white-space: nowrap;
    }
    .sx-fees-modal .inst-empty {
        text-align: center;
        padding: 2rem 1rem;
        color: #888 !important;
    }

    @media (max-width: 576px) {
        .sx-parent-fees .total-banner-val {
            font-size: 0.95rem;
        }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-parent-fees .card,
    html[data-bs-theme="dark"] .sx-parent-fees .card,
    html[data-sx-theme="dark"] .sx-parent-fees .panel-card,
    html[data-bs-theme="dark"] .sx-parent-fees .panel-card,
    html[data-sx-theme="dark"] .sx-parent-fees .hint-strip,
    html[data-bs-theme="dark"] .sx-parent-fees .hint-strip,
    html[data-sx-theme="dark"] .sx-parent-fees .student-fee-card,
    html[data-bs-theme="dark"] .sx-parent-fees .student-fee-card,
    html[data-sx-theme="dark"] .sx-parent-fees .student-fee-header,
    html[data-bs-theme="dark"] .sx-parent-fees .student-fee-header,
    html[data-sx-theme="dark"] .sx-parent-fees .fee-type-box,
    html[data-bs-theme="dark"] .sx-parent-fees .fee-type-box,
    html[data-sx-theme="dark"] .sx-parent-fees .empty-state,
    html[data-bs-theme="dark"] .sx-parent-fees .empty-state,
    html[data-sx-theme="dark"] .sx-parent-fees .loading-state,
    html[data-bs-theme="dark"] .sx-parent-fees .loading-state {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-fees .student-fee-header,
    html[data-bs-theme="dark"] .sx-parent-fees .student-fee-header {
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-fees,
    html[data-bs-theme="dark"] .sx-parent-fees,
    html[data-sx-theme="dark"] .sx-parent-fees p,
    html[data-bs-theme="dark"] .sx-parent-fees p,
    html[data-sx-theme="dark"] .sx-parent-fees span:not(.badge):not(.status-pill),
    html[data-bs-theme="dark"] .sx-parent-fees span:not(.badge):not(.status-pill),
    html[data-sx-theme="dark"] .sx-parent-fees small,
    html[data-bs-theme="dark"] .sx-parent-fees small,
    html[data-sx-theme="dark"] .sx-parent-fees strong,
    html[data-bs-theme="dark"] .sx-parent-fees strong,
    html[data-sx-theme="dark"] .sx-parent-fees div,
    html[data-bs-theme="dark"] .sx-parent-fees div,
    html[data-sx-theme="dark"] .sx-parent-fees .text-muted,
    html[data-bs-theme="dark"] .sx-parent-fees .text-muted,
    html[data-sx-theme="dark"] .sx-parent-fees .student-name,
    html[data-bs-theme="dark"] .sx-parent-fees .student-name,
    html[data-sx-theme="dark"] .sx-parent-fees .student-meta,
    html[data-bs-theme="dark"] .sx-parent-fees .student-meta,
    html[data-sx-theme="dark"] .sx-parent-fees .fee-lbl,
    html[data-bs-theme="dark"] .sx-parent-fees .fee-lbl,
    html[data-sx-theme="dark"] .sx-parent-fees .fee-val,
    html[data-bs-theme="dark"] .sx-parent-fees .fee-val {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-fees .section-label,
    html[data-bs-theme="dark"] .sx-parent-fees .section-label,
    html[data-sx-theme="dark"] .sx-parent-fees .fee-title,
    html[data-bs-theme="dark"] .sx-parent-fees .fee-title,
    html[data-sx-theme="dark"] .sx-parent-fees .control-number-banner .c-lbl,
    html[data-bs-theme="dark"] .sx-parent-fees .control-number-banner .c-lbl,
    html[data-sx-theme="dark"] .sx-parent-fees .control-number-banner .c-num,
    html[data-bs-theme="dark"] .sx-parent-fees .control-number-banner .c-num {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-fees .fee-val.is-paid,
    html[data-bs-theme="dark"] .sx-parent-fees .fee-val.is-paid {
        color: #75b798 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-fees .fee-val.is-balance,
    html[data-bs-theme="dark"] .sx-parent-fees .fee-val.is-balance {
        color: #ea868f !important;
    }
    html[data-sx-theme="dark"] .sx-parent-fees .control-number-banner,
    html[data-bs-theme="dark"] .sx-parent-fees .control-number-banner {
        background: rgba(148, 0, 0, 0.2) !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-fees .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-fees .btn-outline-light,
    html[data-sx-theme="dark"] .sx-parent-fees .fee-action-btn,
    html[data-bs-theme="dark"] .sx-parent-fees .fee-action-btn {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-fees .year-select,
    html[data-bs-theme="dark"] .sx-parent-fees .year-select {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-fees-modal .modal-content,
    html[data-bs-theme="dark"] .sx-fees-modal .modal-content,
    html[data-sx-theme="dark"] .sx-fees-modal .modal-body,
    html[data-bs-theme="dark"] .sx-fees-modal .modal-body,
    html[data-sx-theme="dark"] .sx-fees-modal .modal-footer,
    html[data-bs-theme="dark"] .sx-fees-modal .modal-footer {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-fees-modal .inst-table thead th,
    html[data-bs-theme="dark"] .sx-fees-modal .inst-table thead th {
        background: rgba(148, 0, 0, 0.22) !important;
        color: #ffb4b4 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-fees-modal .inst-table tbody td,
    html[data-bs-theme="dark"] .sx-fees-modal .inst-table tbody td {
        border-color: rgba(255, 180, 180, 0.1) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-fees-modal .inst-name,
    html[data-bs-theme="dark"] .sx-fees-modal .inst-name,
    html[data-sx-theme="dark"] .sx-fees-modal .inst-type,
    html[data-bs-theme="dark"] .sx-fees-modal .inst-type {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-fees-modal .inst-amt,
    html[data-bs-theme="dark"] .sx-fees-modal .inst-amt,
    html[data-sx-theme="dark"] .sx-fees-modal .modal-subtitle,
    html[data-bs-theme="dark"] .sx-fees-modal .modal-subtitle {
        color: #ffb4b4 !important;
    }
</style>

<div class="sx-parent-fees">
    <div class="row g-3">
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div class="pe-2">
                            <h5 class="card-title mb-1">
                                <i class="fi fi-rr-file-invoice me-1" style="color:#940000;"></i>
                                {{ __('parent.fees_summary') }}
                            </h5>
                            <p class="text-muted small mb-0">{{ __('parent.financial_overview') }}</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-end gap-2">
                            <div>
                                <label class="small text-uppercase fw-bold text-muted d-block mb-1">{{ __('parent.academic_year') }}</label>
                                <select id="summaryYear" class="form-select form-select-sm year-select">
                                    @foreach($academicYears ?? [] as $ay)
                                        <option value="{{ $ay['academic_yearID'] }}" {{ (int) $ay['academic_yearID'] === (int) $selectedAcademicYearID ? 'selected' : '' }}>{{ $ay['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="requestControlNumberBtn">
                                <i class="fi fi-rr-key me-1"></i> {{ __('parent.request_control_numbers') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="hint-strip">
                <i class="fi fi-rr-info me-1" style="color:#940000;"></i>
                {{ __('parent.financial_overview') }}
            </div>
        </div>

        <div class="col-12">
            <div class="section-label">
                <i class="fi fi-rr-users me-1"></i>
                {{ __('parent.fees_summary') }}
            </div>
            <div class="row g-3" id="feesSummaryContent">
                <div class="col-12">
                    <div class="loading-state">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade sx-fees-modal" id="viewFeeDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('parent.fee_installment_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('parent.close') }}"></button>
            </div>
            <div class="modal-body" id="viewFeeDetailsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light btn-shadow waves-effect" data-bs-dismiss="modal">{{ __('parent.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const P = @json(trans('parent'));

        function esc(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function money(n) {
            return 'TZS ' + (parseFloat(n) || 0).toLocaleString();
        }

        function feeLabel(t) {
            if (t === 'Tuition Fees') return P.tuition_fees;
            if (t === 'Other Fees') return P.other_fees;
            return t || '';
        }

        function feeBox(opts) {
            var html = '<div class="fee-type-box ' + opts.boxClass + '">';
            html += '<div class="fee-title"><i class="fi ' + opts.icon + '"></i> ' + esc(opts.title) + '</div>';
            html += '<div class="fee-row"><span class="fee-lbl">' + esc(P.required_amount) + ':</span><span class="fee-val">' + esc(money(opts.required)) + '</span></div>';
            html += '<div class="fee-row"><span class="fee-lbl">' + esc(P.paid) + ':</span><span class="fee-val is-paid">' + esc(money(opts.paid)) + '</span></div>';
            html += '<div class="fee-row is-balance"><span class="fee-lbl">' + esc(P.balance) + ':</span><span class="fee-val is-balance">' + esc(money(opts.balance)) + '</span></div>';
            if (opts.control_number) {
                html += '<div class="control-number-banner">';
                html += '<div class="c-lbl">' + esc(opts.controlLabel) + '</div>';
                html += '<div class="c-num">' + esc(opts.control_number) + '</div>';
                html += '</div>';
            }
            html += '<button type="button" class="btn btn-outline-light fee-action-btn w-100 view-more-fee-btn"'
                + ' data-student-name="' + esc(opts.studentName) + '"'
                + ' data-fee-type="' + esc(opts.feeType) + '"'
                + ' data-installments="' + esc(encodeURIComponent(JSON.stringify(opts.installments || []))) + '">'
                + esc(opts.actionLabel)
                + '</button>';
            html += '</div>';
            return html;
        }

        function loadSummary() {
            $('#feesSummaryContent').html(
                '<div class="col-12"><div class="loading-state"><div class="spinner-border text-primary" role="status"></div></div></div>'
            );

            $.ajax({
                url: '{{ route("get_fees_summary_ajax") }}',
                data: { academic_yearID: $('#summaryYear').val() },
                success: function(res) {
                    if (!res.success) {
                        $('#feesSummaryContent').html(
                            '<div class="col-12"><div class="empty-state"><i class="fi fi-rr-file-invoice"></i><p class="text-muted small mb-0">' + esc(P.load_failed || P.error) + '</p></div></div>'
                        );
                        return;
                    }

                    var rows = res.data || [];
                    if (!rows.length) {
                        $('#feesSummaryContent').html(
                            '<div class="col-12"><div class="empty-state"><i class="fi fi-rr-file-invoice"></i><p class="text-muted small mb-0">' + esc(P.no_data) + '</p></div></div>'
                        );
                        return;
                    }

                    var html = '';
                    rows.forEach(function(item) {
                        var pending = parseFloat(item.total.balance) > 0;
                        html += '<div class="col-12"><div class="student-fee-card">';
                        html += '<div class="student-fee-header">';
                        html += '<div><div class="student-name">' + esc(item.student_name) + '</div>';
                        html += '<div class="student-meta">' + esc(item.admission_number || '') + (item.class ? ' · ' + esc(item.class) : '') + '</div></div>';
                        html += '<span class="status-pill ' + (pending ? 'pending' : 'clear') + '">' + esc(pending ? P.payment_pending : P.account_clear) + '</span>';
                        html += '</div>';

                        html += '<div class="fee-body"><div class="row g-3">';
                        html += '<div class="col-md-6">' + feeBox({
                            boxClass: 'is-tuition',
                            icon: 'fi-rr-book',
                            title: P.tuition_fees,
                            required: item.tuition_fees.required,
                            paid: item.tuition_fees.paid,
                            balance: item.tuition_fees.balance,
                            control_number: item.tuition_fees.control_number,
                            controlLabel: P.tuition_control,
                            studentName: item.student_name,
                            feeType: 'Tuition Fees',
                            installments: item.tuition_fees.installments || [],
                            actionLabel: P.view_installments
                        }) + '</div>';
                        html += '<div class="col-md-6">' + feeBox({
                            boxClass: 'is-other',
                            icon: 'fi-rr-wallet',
                            title: P.other_fees,
                            required: item.other_fees.required,
                            paid: item.other_fees.paid,
                            balance: item.other_fees.balance,
                            control_number: item.other_fees.control_number,
                            controlLabel: P.other_control,
                            studentName: item.student_name,
                            feeType: 'Other Fees',
                            installments: item.other_fees.installments || [],
                            actionLabel: P.view_breakdown
                        }) + '</div>';
                        html += '</div></div>';

                        html += '<div class="total-banner">';
                        html += '<div class="total-banner-item"><div class="total-banner-lbl">' + esc(P.total_required) + '</div><div class="total-banner-val">' + esc(money(item.total.required)) + '</div></div>';
                        html += '<div class="total-banner-item"><div class="total-banner-lbl">' + esc(P.total_paid) + '</div><div class="total-banner-val">' + esc(money(item.total.paid)) + '</div></div>';
                        html += '<div class="total-banner-item"><div class="total-banner-lbl">' + esc(P.current_balance) + '</div><div class="total-banner-val">' + esc(money(item.total.balance)) + '</div></div>';
                        html += '</div>';

                        html += '</div></div>';
                    });

                    $('#feesSummaryContent').html(html);
                },
                error: function() {
                    $('#feesSummaryContent').html(
                        '<div class="col-12"><div class="empty-state"><i class="fi fi-rr-file-invoice"></i><p class="text-muted small mb-0">' + esc(P.load_failed || P.error) + '</p></div></div>'
                    );
                }
            });
        }

        $('#summaryYear').on('change', loadSummary);
        loadSummary();

        $(document).on('click', '.view-more-fee-btn', function() {
            var raw = $(this).attr('data-installments') || '';
            var installments = [];
            try {
                installments = JSON.parse(decodeURIComponent(raw));
            } catch (e) {
                installments = [];
            }

            var student = $(this).data('student-name');
            var type = $(this).data('fee-type');

            var html = '<div class="modal-subtitle">' + esc(feeLabel(type)) + ' — ' + esc(student) + '</div>';
            html += '<div class="table-responsive"><table class="inst-table"><thead><tr>';
            html += '<th>' + esc(P.installment) + '</th>';
            html += '<th class="text-end">' + esc(P.amount_tzs) + '</th>';
            html += '</tr></thead><tbody>';

            if (installments.length) {
                installments.forEach(function(ins) {
                    html += '<tr><td><div class="inst-name">' + esc(ins.installment_name) + '</div>';
                    html += '<div class="inst-type">' + esc(ins.installment_type || '') + '</div></td>';
                    html += '<td class="text-end"><span class="inst-amt">' + esc(money(ins.amount)) + '</span></td></tr>';
                });
            } else {
                html += '<tr><td colspan="2" class="inst-empty">' + esc(P.full_payment_only) + '</td></tr>';
            }

            html += '</tbody></table></div>';
            $('#viewFeeDetailsContent').html(html);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('viewFeeDetailsModal')).show();
        });

        $('#requestControlNumberBtn').on('click', function() {
            Swal.fire({
                title: P.request_cn_title,
                text: P.request_cn_text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000'
            }).then(function(res) {
                if (res.isConfirmed) {
                    Swal.fire({ title: P.processing, didOpen: function() { Swal.showLoading(); } });
                    location.reload();
                }
            });
        });
    });
</script>
@endpush
