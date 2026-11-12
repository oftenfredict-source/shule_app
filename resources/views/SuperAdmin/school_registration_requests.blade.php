@extends('includes.superadmin_nav')

@section('page_title', 'Registration Requests')
@section('page_breadcrumb', 'Registration Requests')

@section('page_actions')
    <a href="{{ route('superadmin.schools.register') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-add me-1"></i> Register School
    </a>
@endsection

@section('content')
@php
    $requestPayload = ($requests ?? collect())->getCollection()->map(function ($row) {
        return [
            'id' => $row->id,
            'school_name' => $row->school_name,
            'registration_number' => $row->registration_number,
            'exam_center_number' => $row->exam_center_number,
            'school_type' => $row->school_type,
            'ownership' => $row->ownership,
            'established_year' => $row->established_year,
            'region' => $row->region,
            'district' => $row->district,
            'ward' => $row->ward,
            'email' => $row->email,
            'phone' => $row->phone,
            'address' => $row->address,
            'number_of_students' => $row->number_of_students,
            'contact_person' => $row->contact_person,
            'notes' => $row->notes,
            'status' => $row->status,
            'submitted' => optional($row->created_at)->format('d M Y H:i'),
            'phone_verified_at' => optional($row->phone_verified_at)->format('d M Y H:i'),
            'verified_at' => optional($row->verified_at)->format('d M Y H:i'),
            'admin_notes' => $row->admin_notes,
            'verify_url' => route('superadmin.schools.registration_requests.verify', $row->id),
            'reject_url' => route('superadmin.schools.registration_requests.reject', $row->id),
        ];
    })->values();
@endphp
<style>
    .sx-req { color: #000; }
    .sx-req .sx-req-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.25rem;
    }
    .sx-req .sx-req-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-req .sx-req-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-req .sx-req-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-req .sx-req-url {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.55rem 0.85rem;
        padding: 0.75rem 0.9rem;
        border: 1px solid #ececec;
        background: #fafafa;
        border-radius: 0.45rem;
        margin-bottom: 1rem;
    }
    .sx-req .sx-req-url-label {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555;
    }
    .sx-req .sx-req-url code {
        color: #940000;
        font-weight: 700;
        font-size: 0.86rem;
        word-break: break-all;
        background: transparent;
    }
    .sx-req .sx-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .sx-req .sx-req-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .sx-req .sx-req-filter {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.8rem;
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #000;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 0.45rem;
    }
    .sx-req .sx-req-filter:hover {
        border-color: #940000;
        color: #000;
        background: #fff5f5;
    }
    .sx-req .sx-req-filter.is-active {
        background: #940000;
        border-color: #940000;
        color: #fff !important;
    }
    .sx-req .sx-req-filter .count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.35rem;
        height: 1.35rem;
        padding: 0 0.35rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        background: rgba(0, 0, 0, 0.08);
    }
    .sx-req .sx-req-filter.is-active .count {
        background: rgba(255, 255, 255, 0.22);
        color: #fff;
    }
    .sx-req .sx-view-toggle {
        display: inline-flex;
        border: 1px solid #e0e0e0;
        border-radius: 0.45rem;
        overflow: hidden;
    }
    .sx-req .sx-view-btn {
        border: 0;
        background: #fff;
        color: #444;
        padding: 0.45rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        cursor: pointer;
    }
    .sx-req .sx-view-btn + .sx-view-btn {
        border-left: 1px solid #e0e0e0;
    }
    .sx-req .sx-view-btn.is-active {
        background: #940000;
        color: #fff !important;
    }
    .sx-req .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: hidden;
    }
    .sx-req .table {
        margin: 0;
        color: #000;
        vertical-align: middle;
    }
    .sx-req .table thead th {
        background: #f7f7f7;
        border-bottom: 1px solid #e6e6e6;
        border-top: 0;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #333;
        white-space: nowrap;
        padding: 0.75rem 0.9rem;
    }
    .sx-req .table tbody td {
        border-color: #f0f0f0;
        padding: 0.9rem;
        font-size: 0.9rem;
        color: #000;
    }
    .sx-req .table tbody tr:hover { background: #fffafa; }
    .sx-req .sx-school-name {
        font-weight: 800;
        color: #000;
        line-height: 1.25;
    }
    .sx-req .sx-meta {
        font-size: 0.78rem;
        color: #555;
        margin-top: 0.15rem;
    }
    .sx-req .sx-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border-radius: 0.3rem;
        line-height: 1;
    }
    .sx-req .sx-badge-pending { background: #fff4d6; color: #8a5b00 !important; }
    .sx-req .sx-badge-ready { background: #e8f7ec; color: #145c2a !important; }
    .sx-req .sx-badge-rejected { background: #fde8e8; color: #940000 !important; }
    .sx-req .btn-view-more {
        border: 1px solid #940000;
        background: #fff;
        color: #940000 !important;
        font-weight: 700;
        border-radius: 0.45rem;
        font-size: 0.78rem;
        padding: 0.35rem 0.7rem;
    }
    .sx-req .btn-view-more:hover {
        background: #fff5f5;
        color: #940000 !important;
    }
    .sx-req .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 700;
        border-radius: 0.45rem;
    }
    .sx-req .btn-success-soft {
        background: #145c2a;
        border-color: #145c2a;
        color: #fff !important;
        font-weight: 700;
        border-radius: 0.45rem;
        font-size: 0.78rem;
        padding: 0.4rem 0.75rem;
    }
    .sx-req .btn-reject {
        border: 1px solid #940000;
        background: #fff;
        color: #940000 !important;
        font-weight: 700;
        border-radius: 0.45rem;
        font-size: 0.78rem;
        padding: 0.4rem 0.75rem;
    }
    .sx-req .sx-empty {
        text-align: center;
        padding: 2.25rem 1rem;
        color: #666;
        font-weight: 600;
    }
    .sx-req .sx-grid {
        display: none;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.9rem;
    }
    .sx-req.is-grid .sx-list-view { display: none; }
    .sx-req.is-grid .sx-grid { display: grid; }
    .sx-req.is-list .sx-list-view { display: block; }
    .sx-req.is-list .sx-grid { display: none; }
    .sx-req .sx-grid-card {
        border: 1px solid #ececec;
        background: #fff;
        border-top: 3px solid #940000;
        border-radius: 0.45rem;
        padding: 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }
    .sx-req .sx-grid-card-top {
        display: flex;
        justify-content: space-between;
        gap: 0.5rem;
        align-items: flex-start;
    }
    .sx-req .sx-grid-rows {
        display: grid;
        gap: 0.45rem;
        flex: 1;
    }
    .sx-req .sx-grid-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.86rem;
    }
    .sx-req .sx-grid-row span:first-child {
        color: #666;
        font-weight: 700;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sx-req .sx-grid-row span:last-child {
        font-weight: 700;
        text-align: right;
        color: #000;
    }
    .sx-req .modal-content {
        border-radius: 0;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
    }
    .sx-req .modal-header {
        border-bottom: 1px solid #eee;
        padding: 1rem 1.15rem;
    }
    .sx-req .modal-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #000;
    }
    .sx-req .modal-body { padding: 1.15rem; }
    .sx-req .modal-footer {
        border-top: 1px solid #eee;
        padding: 0.85rem 1.15rem;
        gap: 0.5rem;
    }
    .sx-req .sx-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem 1rem;
    }
    .sx-req .sx-detail-item dt {
        margin: 0;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #666;
    }
    .sx-req .sx-detail-item dd {
        margin: 0.2rem 0 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: #000;
        word-break: break-word;
    }
    .sx-req .sx-detail-item.is-full { grid-column: 1 / -1; }
    @media (max-width: 991.98px) {
        .sx-req .sx-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .sx-req .sx-grid { grid-template-columns: 1fr; }
        .sx-req .sx-detail-grid { grid-template-columns: 1fr; }
    }
    html[data-sx-theme="dark"] .sx-req,
    html[data-bs-theme="dark"] .sx-req,
    html[data-sx-theme="dark"] .sx-req .sx-req-card-title,
    html[data-bs-theme="dark"] .sx-req .sx-req-card-title,
    html[data-sx-theme="dark"] .sx-req .sx-school-name,
    html[data-bs-theme="dark"] .sx-req .sx-school-name,
    html[data-sx-theme="dark"] .sx-req .table,
    html[data-bs-theme="dark"] .sx-req .table,
    html[data-sx-theme="dark"] .sx-req .modal-title,
    html[data-bs-theme="dark"] .sx-req .modal-title,
    html[data-sx-theme="dark"] .sx-req .sx-detail-item dd,
    html[data-bs-theme="dark"] .sx-req .sx-detail-item dd,
    html[data-sx-theme="dark"] .sx-req .sx-grid-row span:last-child,
    html[data-bs-theme="dark"] .sx-req .sx-grid-row span:last-child {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-req .sx-req-card,
    html[data-bs-theme="dark"] .sx-req .sx-req-card,
    html[data-sx-theme="dark"] .sx-req .sx-grid-card,
    html[data-bs-theme="dark"] .sx-req .sx-grid-card,
    html[data-sx-theme="dark"] .sx-req .modal-content,
    html[data-bs-theme="dark"] .sx-req .modal-content {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18);
        border-top-color: #940000;
    }
    html[data-sx-theme="dark"] .sx-req .sx-req-url,
    html[data-bs-theme="dark"] .sx-req .sx-req-url,
    html[data-sx-theme="dark"] .sx-req .sx-req-filter,
    html[data-bs-theme="dark"] .sx-req .sx-req-filter,
    html[data-sx-theme="dark"] .sx-req .sx-view-btn,
    html[data-bs-theme="dark"] .sx-req .sx-view-btn {
        background: #1c1417;
        border-color: rgba(255, 180, 180, 0.16);
        color: #fff;
    }
    html[data-sx-theme="dark"] .sx-req .table thead th,
    html[data-bs-theme="dark"] .sx-req .table thead th {
        background: #1c1417;
        border-color: rgba(255, 180, 180, 0.16);
        color: #f0dede;
    }
    html[data-sx-theme="dark"] .sx-req .sx-meta,
    html[data-bs-theme="dark"] .sx-req .sx-meta,
    html[data-sx-theme="dark"] .sx-req .sx-detail-item dt,
    html[data-bs-theme="dark"] .sx-req .sx-detail-item dt,
    html[data-sx-theme="dark"] .sx-req .sx-grid-row span:first-child,
    html[data-bs-theme="dark"] .sx-req .sx-grid-row span:first-child {
        color: #d7c4c8;
    }
</style>

<div class="sx-req is-list" id="sxReqRoot">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fi fi-rr-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="sx-req-card">
        <div class="card-body">
            <div class="sx-req-card-head">
                <h2 class="sx-req-card-title">Public Registration Queue</h2>
            </div>

            <div class="sx-toolbar">
                <div class="sx-req-filters" role="tablist" aria-label="Request status filters">
                    <a href="{{ route('superadmin.schools.registration_requests', ['status' => 'pending']) }}"
                       class="sx-req-filter {{ ($status ?? '') === 'pending' ? 'is-active' : '' }}">
                        Pending <span class="count">{{ $counts['pending'] }}</span>
                    </a>
                    <a href="{{ route('superadmin.schools.registration_requests', ['status' => 'ready']) }}"
                       class="sx-req-filter {{ ($status ?? '') === 'ready' ? 'is-active' : '' }}">
                        Ready <span class="count">{{ $counts['ready'] }}</span>
                    </a>
                    <a href="{{ route('superadmin.schools.registration_requests', ['status' => 'rejected']) }}"
                       class="sx-req-filter {{ ($status ?? '') === 'rejected' ? 'is-active' : '' }}">
                        Rejected <span class="count">{{ $counts['rejected'] }}</span>
                    </a>
                    <a href="{{ route('superadmin.schools.registration_requests', ['status' => 'all']) }}"
                       class="sx-req-filter {{ ($status ?? '') === 'all' ? 'is-active' : '' }}">
                        All <span class="count">{{ $counts['all'] }}</span>
                    </a>
                </div>

                <div class="sx-view-toggle" role="group" aria-label="View mode">
                    <button type="button" class="sx-view-btn is-active" data-view="list" id="btnViewList">
                        <i class="fi fi-rr-list"></i> List
                    </button>
                    <button type="button" class="sx-view-btn" data-view="grid" id="btnViewGrid">
                        <i class="fi fi-rr-apps"></i> Grid
                    </button>
                </div>
            </div>

            <div class="sx-list-view">
                <div class="sx-table-wrap">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>School</th>
                                    <th>Location</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $row)
                                    <tr>
                                        <td>
                                            <div class="sx-school-name">{{ $row->school_name }}</div>
                                            <div class="sx-meta">{{ $row->school_type }} · {{ $row->ownership }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $row->region }}</div>
                                            <div class="sx-meta">{{ $row->district }}</div>
                                        </td>
                                        <td>{{ $row->phone }}</td>
                                        <td>
                                            @if($row->status === 'pending')
                                                <span class="sx-badge sx-badge-pending">Pending</span>
                                            @elseif($row->status === 'ready')
                                                <span class="sx-badge sx-badge-ready">Ready</span>
                                            @else
                                                <span class="sx-badge sx-badge-rejected">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-view-more btn-open-request"
                                                    data-id="{{ $row->id }}">
                                                View more
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="sx-empty">No registration requests in this filter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="sx-grid">
                @forelse($requests as $row)
                    <div class="sx-grid-card">
                        <div class="sx-grid-card-top">
                            <div>
                                <div class="sx-school-name">{{ $row->school_name }}</div>
                                <div class="sx-meta">{{ $row->school_type }} · {{ $row->ownership }}</div>
                            </div>
                            @if($row->status === 'pending')
                                <span class="sx-badge sx-badge-pending">Pending</span>
                            @elseif($row->status === 'ready')
                                <span class="sx-badge sx-badge-ready">Ready</span>
                            @else
                                <span class="sx-badge sx-badge-rejected">Rejected</span>
                            @endif
                        </div>
                        <div class="sx-grid-rows">
                            <div class="sx-grid-row">
                                <span>Location</span>
                                <span>{{ $row->region }}, {{ $row->district }}</span>
                            </div>
                            <div class="sx-grid-row">
                                <span>Phone</span>
                                <span>{{ $row->phone }}</span>
                            </div>
                            <div class="sx-grid-row">
                                <span>Submitted</span>
                                <span>{{ $row->created_at?->format('d M Y') }}</span>
                            </div>
                        </div>
                        <button type="button"
                                class="btn btn-view-more btn-open-request align-self-start"
                                data-id="{{ $row->id }}">
                            View more
                        </button>
                    </div>
                @empty
                    <div class="sx-empty" style="grid-column: 1 / -1; border: 1px solid #ececec; border-radius: 0.45rem;">
                        No registration requests in this filter.
                    </div>
                @endforelse
            </div>

            @if($requests->hasPages())
                <div class="mt-3">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="sx-req-card">
        <div class="card-body">
            <div class="sx-req-card-head">
                <div>
                    <h2 class="sx-req-card-title">Blocked OTP Phones</h2>
                    <div class="sx-meta mt-1">
                        Blocked after {{ \App\Models\OtpPhoneBlock::MAX_OTP_SENDS }} OTP requests until released.
                    </div>
                </div>
            </div>

            <div class="sx-table-wrap">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Phone</th>
                                <th>OTP Sends</th>
                                <th>Blocked At</th>
                                <th>Reason</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($blockedOtpPhones ?? collect()) as $block)
                                <tr>
                                    <td><div class="sx-school-name">{{ $block->phone }}</div></td>
                                    <td>{{ (int) $block->otp_send_count }}</td>
                                    <td>
                                        <div>{{ $block->blocked_at?->format('d M Y') ?: '—' }}</div>
                                        <div class="sx-meta">{{ $block->blocked_at?->format('H:i') }}</div>
                                    </td>
                                    <td>{{ $block->block_reason ?: 'OTP abuse limit' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('superadmin.schools.registration_otp_blocks.release', $block->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm"
                                                    onclick="return confirm('Release this phone so it can receive OTPs again?')">
                                                Release
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="sx-empty">No blocked phones.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="requestDetailModal" tabindex="-1" aria-labelledby="requestDetailTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestDetailTitle">Request details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="sx-detail-grid" id="requestDetailBody"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div id="requestDetailStatus"></div>
                    <div class="d-flex flex-wrap gap-2" id="requestDetailActions"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('sxReqRoot');
    var requests = @json($requestPayload);
    var byId = {};
    requests.forEach(function (row) { byId[String(row.id)] = row; });
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

    function setView(mode) {
        var isGrid = mode === 'grid';
        root.classList.toggle('is-grid', isGrid);
        root.classList.toggle('is-list', !isGrid);
        document.getElementById('btnViewList')?.classList.toggle('is-active', !isGrid);
        document.getElementById('btnViewGrid')?.classList.toggle('is-active', isGrid);
        try { localStorage.setItem('sx_reg_req_view', isGrid ? 'grid' : 'list'); } catch (e) {}
    }

    document.getElementById('btnViewList')?.addEventListener('click', function () { setView('list'); });
    document.getElementById('btnViewGrid')?.addEventListener('click', function () { setView('grid'); });
    try {
        var saved = localStorage.getItem('sx_reg_req_view');
        if (saved === 'grid' || saved === 'list') setView(saved);
    } catch (e) {}

    function dash(v) {
        v = (v === null || v === undefined) ? '' : String(v).trim();
        return v === '' ? '—' : v;
    }

    function statusBadge(status) {
        if (status === 'pending') return '<span class="sx-badge sx-badge-pending">Pending</span>';
        if (status === 'ready') return '<span class="sx-badge sx-badge-ready">Ready</span>';
        return '<span class="sx-badge sx-badge-rejected">Rejected</span>';
    }

    function detailItem(label, value, full) {
        return '<div class="sx-detail-item' + (full ? ' is-full' : '') + '">'
            + '<dt>' + label + '</dt>'
            + '<dd>' + dash(value) + '</dd>'
            + '</div>';
    }

    function openDetails(id) {
        var row = byId[String(id)];
        if (!row) return;

        document.getElementById('requestDetailTitle').textContent = row.school_name || 'Request details';
        document.getElementById('requestDetailStatus').innerHTML = statusBadge(row.status);

        document.getElementById('requestDetailBody').innerHTML =
            detailItem('School name', row.school_name)
            + detailItem('Registration no.', row.registration_number)
            + detailItem('Exam center', row.exam_center_number)
            + detailItem('Education level', row.school_type)
            + detailItem('Ownership', row.ownership)
            + detailItem('Established year', row.established_year)
            + detailItem('Students', row.number_of_students)
            + detailItem('Contact person', row.contact_person)
            + detailItem('Region', row.region)
            + detailItem('District', row.district)
            + detailItem('Ward', row.ward)
            + detailItem('Phone', row.phone)
            + detailItem('Email', row.email)
            + detailItem('Submitted', row.submitted)
            + detailItem('Phone verified', row.phone_verified_at)
            + detailItem('Verified at', row.verified_at)
            + detailItem('Postal address', row.address, true)
            + detailItem('Notes', row.notes, true)
            + detailItem('Admin notes', row.admin_notes, true);

        var actions = document.getElementById('requestDetailActions');
        actions.innerHTML = '';
        if (row.status === 'pending') {
            actions.innerHTML =
                '<form method="POST" action="' + row.verify_url + '" class="d-inline">'
                + '<input type="hidden" name="_token" value="' + csrf + '">'
                + '<button type="submit" class="btn btn-success-soft" onclick="return confirm(\'Mark as Ready? No login credentials will be created.\')">Verify</button>'
                + '</form>'
                + '<form method="POST" action="' + row.reject_url + '" class="d-inline">'
                + '<input type="hidden" name="_token" value="' + csrf + '">'
                + '<button type="submit" class="btn btn-reject" onclick="return confirm(\'Reject this registration request?\')">Reject</button>'
                + '</form>';
        }

        var modalEl = document.getElementById('requestDetailModal');
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (window.jQuery) {
            window.jQuery(modalEl).modal('show');
        }
    }

    document.querySelectorAll('.btn-open-request').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openDetails(this.getAttribute('data-id'));
        });
    });

});
</script>
@endpush
