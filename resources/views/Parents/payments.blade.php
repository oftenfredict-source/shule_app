@extends('layouts.parent_corp')

@section('title', __('parent.payments_title') . ' - ShuleXpert')
@section('page_title', __('parent.payments_title'))
@section('page_breadcrumb', __('parent.nav_make_payment'))

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay span:not(.badge):not(.status-badge):not(.fee-badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-pay .text-muted {
        color: #000 !important;
    }
    .sx-parent-pay .btn-primary,
    .sx-parent-pay a.btn-primary,
    .sx-parent-pay .badge {
        color: #fff !important;
    }
    .sx-parent-pay .card,
    .sx-parent-pay .panel-card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-pay .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-pay .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-parent-pay .hint-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        padding: 0.85rem 1rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-pay .section-label {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #940000 !important;
        margin-bottom: 0.85rem;
    }

    /* Stat pills */
    .sx-parent-pay .stat-pill {
        text-align: center;
        padding: 0.95rem 0.55rem;
        border: 1px solid #e6e6e6;
        border-top: 3px solid #940000;
        background: #fff;
        height: 100%;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }
    .sx-parent-pay .stat-pill.is-required { border-top-color: #dc3545; }
    .sx-parent-pay .stat-pill.is-paid { border-top-color: #28a745; }
    .sx-parent-pay .stat-pill.is-balance {
        border-top-color: #940000;
        background: #fff8f8;
    }
    .sx-parent-pay .stat-pill .num {
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.15;
        word-break: break-word;
    }
    .sx-parent-pay .stat-pill.is-required .num { color: #dc3545 !important; }
    .sx-parent-pay .stat-pill.is-paid .num { color: #28a745 !important; }
    .sx-parent-pay .stat-pill.is-balance .num { color: #940000 !important; }
    .sx-parent-pay .stat-pill .txt {
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #777 !important;
        font-weight: 700;
        margin-top: 0.4rem;
    }

    /* Table */
    .sx-parent-pay .pay-panel {
        overflow: hidden;
    }
    .sx-parent-pay .pay-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #f0e6e6;
        background: #fffaf9;
    }
    .sx-parent-pay .pay-count {
        font-size: 0.78rem;
        font-weight: 700;
        color: #666 !important;
    }
    .sx-parent-pay .pay-count strong {
        color: #940000 !important;
    }
    .sx-parent-pay .pay-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        background: #fff;
    }
    .sx-parent-pay .pay-table thead th {
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
    .sx-parent-pay .pay-table thead th:last-child {
        border-right: none;
    }
    .sx-parent-pay .pay-table tbody td {
        vertical-align: middle;
        border-bottom: 1px solid #f0ecec;
        border-right: 1px solid #f6f1f1;
        padding: 0.9rem;
        font-size: 0.88rem;
        background: #fff;
    }
    .sx-parent-pay .pay-table tbody td:last-child {
        border-right: none;
    }
    .sx-parent-pay .pay-table tbody tr:nth-child(even) td {
        background: #fffcfc;
    }
    .sx-parent-pay .pay-table tbody tr:hover td {
        background: #fff5f5 !important;
    }
    .sx-parent-pay .pay-table tbody tr:last-child td {
        border-bottom: none;
    }
    .sx-parent-pay .col-num {
        width: 48px;
        text-align: center;
    }
    .sx-parent-pay .row-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #fff0f0;
        border: 1px solid #f0d0d0;
        color: #940000 !important;
        font-weight: 800;
        font-size: 0.72rem;
    }
    .sx-parent-pay .child-name {
        font-weight: 800;
        color: #1a1a1a !important;
        line-height: 1.25;
    }
    .sx-parent-pay .child-meta {
        font-size: 0.72rem;
        color: #888 !important;
        font-weight: 600;
        margin-top: 0.1rem;
    }
    .sx-parent-pay .control-number {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 800;
        color: #940000 !important;
        background: #fff5f5;
        padding: 0.2rem 0.45rem;
        border: 1px dashed #940000;
        font-size: 0.78rem;
        display: inline-block;
    }
    .sx-parent-pay .amt {
        font-weight: 800;
        white-space: nowrap;
    }
    .sx-parent-pay .amt-required { color: #333 !important; }
    .sx-parent-pay .amt-paid { color: #28a745 !important; }
    .sx-parent-pay .amt-balance { color: #dc3545 !important; }
    .sx-parent-pay .fee-badge,
    .sx-parent-pay .status-badge {
        font-size: 0.65rem;
        padding: 0.3rem 0.55rem;
        text-transform: uppercase;
        font-weight: 800;
        border-radius: 0 !important;
        letter-spacing: 0.03em;
        display: inline-block;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .sx-parent-pay .fee-badge.tuition {
        background: #d1e7dd;
        color: #0f5132 !important;
        border-color: #a3cfbb;
    }
    .sx-parent-pay .fee-badge.other {
        background: #cff4fc;
        color: #055160 !important;
        border-color: #b6effb;
    }
    .sx-parent-pay .status-badge.paid {
        background: #d1e7dd;
        color: #0f5132 !important;
        border-color: #a3cfbb;
    }
    .sx-parent-pay .status-badge.pending {
        background: #fff3cd;
        color: #664d03 !important;
        border-color: #ffe69c;
    }
    .sx-parent-pay .empty-row {
        text-align: center;
        padding: 2.5rem 1rem !important;
        color: #888 !important;
    }
    .sx-parent-pay label.form-label {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #777 !important;
        margin-bottom: 0.35rem;
    }

    @media (max-width: 768px) {
        .sx-parent-pay .stat-pill .num {
            font-size: 0.9rem;
        }
        .sx-parent-pay .pay-table thead th,
        .sx-parent-pay .pay-table tbody td {
            padding: 0.7rem 0.55rem;
            font-size: 0.8rem;
        }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-parent-pay .card,
    html[data-bs-theme="dark"] .sx-parent-pay .card,
    html[data-sx-theme="dark"] .sx-parent-pay .panel-card,
    html[data-bs-theme="dark"] .sx-parent-pay .panel-card,
    html[data-sx-theme="dark"] .sx-parent-pay .hint-strip,
    html[data-bs-theme="dark"] .sx-parent-pay .hint-strip,
    html[data-sx-theme="dark"] .sx-parent-pay .stat-pill,
    html[data-bs-theme="dark"] .sx-parent-pay .stat-pill,
    html[data-sx-theme="dark"] .sx-parent-pay .pay-toolbar,
    html[data-bs-theme="dark"] .sx-parent-pay .pay-toolbar,
    html[data-sx-theme="dark"] .sx-parent-pay .pay-table,
    html[data-bs-theme="dark"] .sx-parent-pay .pay-table {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .stat-pill.is-balance,
    html[data-bs-theme="dark"] .sx-parent-pay .stat-pill.is-balance {
        background: rgba(148, 0, 0, 0.18) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay,
    html[data-bs-theme="dark"] .sx-parent-pay,
    html[data-sx-theme="dark"] .sx-parent-pay p,
    html[data-bs-theme="dark"] .sx-parent-pay p,
    html[data-sx-theme="dark"] .sx-parent-pay span:not(.badge):not(.status-badge):not(.fee-badge),
    html[data-bs-theme="dark"] .sx-parent-pay span:not(.badge):not(.status-badge):not(.fee-badge),
    html[data-sx-theme="dark"] .sx-parent-pay small,
    html[data-bs-theme="dark"] .sx-parent-pay small,
    html[data-sx-theme="dark"] .sx-parent-pay strong,
    html[data-bs-theme="dark"] .sx-parent-pay strong,
    html[data-sx-theme="dark"] .sx-parent-pay div,
    html[data-bs-theme="dark"] .sx-parent-pay div,
    html[data-sx-theme="dark"] .sx-parent-pay .text-muted,
    html[data-bs-theme="dark"] .sx-parent-pay .text-muted,
    html[data-sx-theme="dark"] .sx-parent-pay .child-name,
    html[data-bs-theme="dark"] .sx-parent-pay .child-name,
    html[data-sx-theme="dark"] .sx-parent-pay .child-meta,
    html[data-bs-theme="dark"] .sx-parent-pay .child-meta,
    html[data-sx-theme="dark"] .sx-parent-pay .pay-count,
    html[data-bs-theme="dark"] .sx-parent-pay .pay-count,
    html[data-sx-theme="dark"] .sx-parent-pay .stat-pill .txt,
    html[data-bs-theme="dark"] .sx-parent-pay .stat-pill .txt,
    html[data-sx-theme="dark"] .sx-parent-pay .amt-required,
    html[data-bs-theme="dark"] .sx-parent-pay .amt-required {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .section-label,
    html[data-bs-theme="dark"] .sx-parent-pay .section-label,
    html[data-sx-theme="dark"] .sx-parent-pay .pay-count strong,
    html[data-bs-theme="dark"] .sx-parent-pay .pay-count strong,
    html[data-sx-theme="dark"] .sx-parent-pay .pay-table thead th,
    html[data-bs-theme="dark"] .sx-parent-pay .pay-table thead th,
    html[data-sx-theme="dark"] .sx-parent-pay .row-num,
    html[data-bs-theme="dark"] .sx-parent-pay .row-num,
    html[data-sx-theme="dark"] .sx-parent-pay .control-number,
    html[data-bs-theme="dark"] .sx-parent-pay .control-number {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .pay-table thead th,
    html[data-bs-theme="dark"] .sx-parent-pay .pay-table thead th {
        background: rgba(148, 0, 0, 0.22) !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .pay-table tbody td,
    html[data-bs-theme="dark"] .sx-parent-pay .pay-table tbody td {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .pay-table tbody tr:nth-child(even) td,
    html[data-bs-theme="dark"] .sx-parent-pay .pay-table tbody tr:nth-child(even) td {
        background: #2a1c21 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .pay-table tbody tr:hover td,
    html[data-bs-theme="dark"] .sx-parent-pay .pay-table tbody tr:hover td {
        background: rgba(255, 180, 180, 0.08) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .row-num,
    html[data-bs-theme="dark"] .sx-parent-pay .row-num,
    html[data-sx-theme="dark"] .sx-parent-pay .control-number,
    html[data-bs-theme="dark"] .sx-parent-pay .control-number {
        background: rgba(148, 0, 0, 0.25) !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-pay .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .stat-pill.is-paid .num,
    html[data-bs-theme="dark"] .sx-parent-pay .stat-pill.is-paid .num,
    html[data-sx-theme="dark"] .sx-parent-pay .amt-paid,
    html[data-bs-theme="dark"] .sx-parent-pay .amt-paid {
        color: #75b798 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .stat-pill.is-required .num,
    html[data-bs-theme="dark"] .sx-parent-pay .stat-pill.is-required .num,
    html[data-sx-theme="dark"] .sx-parent-pay .amt-balance,
    html[data-bs-theme="dark"] .sx-parent-pay .amt-balance {
        color: #ea868f !important;
    }
    html[data-sx-theme="dark"] .sx-parent-pay .stat-pill.is-balance .num,
    html[data-bs-theme="dark"] .sx-parent-pay .stat-pill.is-balance .num {
        color: #ffb4b4 !important;
    }
</style>

<div class="sx-parent-pay">
    <div class="row g-3">
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div class="pe-2">
                            <h5 class="card-title mb-1">
                                <i class="fi fi-rr-credit-card me-1" style="color:#940000;"></i>
                                {{ __('parent.payments_title') }}
                            </h5>
                            <p class="text-muted small mb-0">{{ __('parent.financial_overview') }}</p>
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
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <div class="stat-pill is-required">
                        <div class="num" id="statTotalRequired">TZS 0</div>
                        <div class="txt">{{ __('parent.amount_required') }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="stat-pill is-paid">
                        <div class="num" id="statTotalPaid">TZS 0</div>
                        <div class="txt">{{ __('parent.total_paid') }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="stat-pill is-balance">
                        <div class="num" id="statOutstandingBalance">TZS 0</div>
                        <div class="txt">{{ __('parent.outstanding_balance') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="section-label">
                        <i class="fi fi-rr-filter me-1"></i>
                        {{ __('parent.search_name') }}
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('parent.search_name') }}</label>
                            <input type="text" class="form-control" id="searchStudentInput" placeholder="{{ __('parent.child_name_placeholder') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('parent.academic_year') }}</label>
                            <select class="form-select" id="filterYear">
                                @foreach($availableYears ?? [date('Y')] as $y)
                                    <option value="{{ $y }}" {{ (string)$y === (string)date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('parent.fee_type') }}</label>
                            <select class="form-select" id="filterFeeType">
                                <option value="">{{ __('parent.all_fees') }}</option>
                                <option value="Tuition Fees">{{ __('parent.tuition_fees') }}</option>
                                <option value="Other Fees">{{ __('parent.other_fees') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-light btn-shadow waves-effect w-100" id="clearFiltersBtn">
                                <i class="fi fi-rr-refresh me-1"></i> {{ __('parent.reset_filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card panel-card pay-panel">
                <div class="pay-toolbar">
                    <div class="pay-count">
                        <strong id="payVisibleCount">0</strong> {{ __('parent.payments_title') }}
                        · <span id="payYearLabel"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="pay-table" id="paymentsTable">
                        <thead>
                            <tr>
                                <th class="col-num">#</th>
                                <th>{{ __('parent.child') }}</th>
                                <th>{{ __('parent.fee_category') }}</th>
                                <th>{{ __('parent.control_number') }}</th>
                                <th class="text-end">{{ __('parent.required_amount') }}</th>
                                <th class="text-end">{{ __('parent.paid') }}</th>
                                <th class="text-end">{{ __('parent.balance') }}</th>
                                <th class="text-center">{{ __('parent.status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="paymentsTableBody">
                            <tr>
                                <td colspan="8" class="empty-row">
                                    <div class="spinner-border text-primary" role="status"></div>
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

        function payStatus(s) {
            return String(s || '').toLowerCase() === 'paid' ? P.paid_status : P.pending_status;
        }

        function loadData() {
            $('#paymentsTableBody').html(
                '<tr><td colspan="8" class="empty-row"><div class="spinner-border text-primary" role="status"></div></td></tr>'
            );
            $('#payYearLabel').text($('#filterYear').val() || '');

            $.ajax({
                url: '{{ route("get_parent_payments_ajax") }}',
                data: {
                    search: $('#searchStudentInput').val(),
                    year: $('#filterYear').val(),
                    fee_type: $('#filterFeeType').val()
                },
                success: function(res) {
                    if (!res.success) {
                        $('#paymentsTableBody').html(
                            '<tr><td colspan="8" class="empty-row">' + esc(P.load_failed || P.error) + '</td></tr>'
                        );
                        return;
                    }

                    var rows = res.data || [];
                    $('#payVisibleCount').text(rows.length);

                    if (!rows.length) {
                        $('#paymentsTableBody').html(
                            '<tr><td colspan="8" class="empty-row">' + esc(P.no_records) + '</td></tr>'
                        );
                    } else {
                        var html = '';
                        rows.forEach(function(item, i) {
                            var p = item.payment || {};
                            var s = item.student || {};
                            var isPaid = String(p.payment_status || '').toLowerCase() === 'paid';
                            var isTuition = p.fee_type === 'Tuition Fees';
                            var ctrl = p.control_number || P.pending_status;

                            html += '<tr>';
                            html += '<td class="col-num"><span class="row-num">' + (i + 1) + '</span></td>';
                            html += '<td><div class="child-name">' + esc(s.first_name || '') + ' ' + esc(s.last_name || '') + '</div>';
                            html += '<div class="child-meta">' + esc(s.admission_number || 'N/A') + '</div></td>';
                            html += '<td><span class="fee-badge ' + (isTuition ? 'tuition' : 'other') + '">' + esc(feeLabel(p.fee_type)) + '</span></td>';
                            html += '<td><span class="control-number">' + esc(ctrl) + '</span></td>';
                            html += '<td class="text-end"><span class="amt amt-required">' + esc(money(p.amount_required)) + '</span></td>';
                            html += '<td class="text-end"><span class="amt amt-paid">' + esc(money(p.amount_paid)) + '</span></td>';
                            html += '<td class="text-end"><span class="amt amt-balance">' + esc(money(p.balance)) + '</span></td>';
                            html += '<td class="text-center"><span class="status-badge ' + (isPaid ? 'paid' : 'pending') + '">' + esc(payStatus(p.payment_status)) + '</span></td>';
                            html += '</tr>';
                        });
                        $('#paymentsTableBody').html(html);
                    }

                    var st = res.statistics || {};
                    $('#statTotalRequired').text(money(st.total_required));
                    $('#statTotalPaid').text(money(st.total_paid));
                    $('#statOutstandingBalance').text(money(st.total_balance));
                },
                error: function() {
                    $('#paymentsTableBody').html(
                        '<tr><td colspan="8" class="empty-row">' + esc(P.load_failed || P.error) + '</td></tr>'
                    );
                }
            });
        }

        $('#searchStudentInput').on('input', function() {
            clearTimeout(window.searchTimer);
            window.searchTimer = setTimeout(loadData, 500);
        });

        $('#filterYear, #filterFeeType').on('change', loadData);
        $('#clearFiltersBtn').on('click', function() {
            $('#searchStudentInput').val('');
            $('#filterFeeType').val('');
            loadData();
        });

        loadData();
    });
</script>
@endpush
