@php
    $portalStoreRoute = $portalRole === 'teacher'
        ? route('teacher.store_requisitions.store')
        : route('staff.store_requisitions.store');
    $portalPdfRoute = $portalRole === 'teacher'
        ? route('teacher.store_requisitions.export_pdf')
        : route('staff.store_requisitions.export_pdf');
    $navInclude = $portalRole === 'teacher' ? 'includes.teacher_nav' : 'includes.staff_nav';
    $dash = $portalDashboard ?? [];
@endphp
@include($navInclude)

<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, div, .form-control, .form-select, .select2-container { border-radius: 0 !important; }
    .bg-primary-custom { background-color: #940000 !important; }
    .text-primary-custom { color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .btn-danger-custom { background-color: #dc3545; border-color: #dc3545; color: #fff; }
    .btn-danger-custom:hover { background-color: #c82333; border-color: #bd2130; color: #fff; }
    .store-menu-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 1rem;
    }
    .store-menu.list-group,
    .list-group.store-menu {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap;
        min-width: min-content;
        width: 100%;
        padding-left: 0;
        margin-bottom: 0;
    }
    .store-menu > .list-group-item {
        flex: 1 1 0;
        width: auto;
        margin-bottom: 0 !important;
        white-space: nowrap;
        min-width: 88px;
        font-size: 0.82rem;
        padding: 10px 8px;
        cursor: pointer;
        border-left: 4px solid transparent;
        color: #333;
        background: #fff;
        border-radius: 0 !important;
        text-align: center;
    }
    .store-menu > .list-group-item + .list-group-item {
        border-top-width: 1px;
        border-left-width: 0;
    }
    .store-menu > .list-group-item.active {
        margin-top: 0;
    }
    .store-menu > .list-group-item:hover {
        background: #fafafa;
        color: #940000;
    }
    .store-menu > .list-group-item.active {
        border-left-color: #940000 !important;
        background: #fff5f5 !important;
        color: #940000 !important;
        font-weight: 600;
        z-index: 1;
    }
    .store-section { display: none; }
    .store-section.active { display: block; }
    .section-title { font-weight: 600; margin-bottom: 12px; color: #940000; font-size: 1.05rem; }
    .store-section-toolbar { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; }
    .store-section-toolbar .section-title { margin-bottom: 0; }
    .store-view-toggle .btn {
        min-width: 72px;
    }
    .store-view-toggle .btn.active {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .portal-subsection-title {
        font-weight: 600;
        color: #940000;
        font-size: 0.95rem;
        margin-bottom: 10px;
    }
    .portal-table-empty {
        color: #888;
        font-size: 0.85rem;
        text-align: center;
        padding: 16px 8px;
        border: 1px dashed #dee2e6;
        background: #fafafa;
    }
    .store-data-wrapper .store-grid-view { display: none; }
    .store-data-wrapper[data-active-view="list"] .store-list-view { display: block !important; }
    .store-data-wrapper[data-active-view="list"] .store-grid-view { display: none !important; }
    .store-data-wrapper[data-active-view="grid"] .store-list-view { display: none !important; }
    .store-data-wrapper[data-active-view="grid"] .store-grid-view { display: flex !important; flex-wrap: wrap; margin-left: -8px; margin-right: -8px; }
    .store-grid-card { border: 1px solid #dee2e6; background: #fff; height: 100%; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .store-grid-card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .store-grid-card-title { font-weight: 700; margin-bottom: 8px; }
    .store-grid-meta-row { display: flex; justify-content: space-between; gap: 8px; font-size: 0.82rem; padding: 3px 0; border-bottom: 1px dashed #eee; }
    .store-grid-meta-label { color: #777; }
    .store-grid-empty { text-align: center; color: #888; padding: 24px; border: 1px dashed #ddd; width: 100%; }
    .store-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 16px; }
    .store-stat-card { border: 1px solid #eee; padding: 14px; text-align: center; background: #fafafa; }
    .store-stat-val { font-size: 1.4rem; font-weight: 700; color: #333; }
    .store-stat-label { font-size: 0.72rem; color: #777; text-transform: uppercase; letter-spacing: 0.04em; }
    .store-badge-pill { display: inline-block; padding: 2px 8px; font-size: 0.72rem; border-radius: 10px; font-weight: 600; }
    .store-badge-return-yes { background: #d4edda; color: #155724; }
    .store-badge-return-no { background: #f8d7da; color: #721c24; }
    .store-badge-overdue { background: #f8d7da; color: #721c24; }
    .store-badge-pending { background: #fff3cd; color: #856404; }
    .store-badge-defective { background: #ffe5d0; color: #8a4b08; }
    .req-return-overdue { color: #dc3545; font-weight: 700; }
    .issue-take-target-list, .issue-hier-unit { border: 1px solid #dee2e6; background: #fafafa; }
    .issue-hier-unit { margin-bottom: 8px; padding: 10px; }
    .issue-hier-unit.is-checked { border-color: #940000; background: #fff; }
    .issue-hier-unit-body { display: none; margin-top: 8px; padding-left: 8px; border-left: 2px solid #eee; }
    .issue-hier-qty-error { color: #dc3545; font-size: 0.78rem; margin-top: 4px; }
    .store-checkbox-toolbar { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
    .store-checkbox-toolbar .btn { font-size: 0.75rem; padding: 3px 10px; line-height: 1.3; }
    .store-ajax-bar { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 20000; pointer-events: none; opacity: 0; transition: opacity 0.2s; background: rgba(148,0,0,0.08); }
    .store-ajax-bar.is-active { opacity: 1; }
    .store-ajax-bar-fill { height: 100%; width: 70%; background: linear-gradient(90deg, #940000, #ff4d4d, #940000); animation: storeBarRun 1.1s ease-in-out infinite; }
    @keyframes storeBarRun { 0% { margin-left: -40%; width: 30%; } 50% { margin-left: 20%; width: 55%; } 100% { margin-left: 100%; width: 30%; } }
    .store-ajax-overlay { position: fixed; inset: 0; z-index: 19999; display: none; align-items: center; justify-content: center; background: rgba(20,0,0,0.28); }
    .store-ajax-overlay.is-active { display: flex; }
    .store-ajax-card { min-width: 220px; background: #fff; border: 2px solid #940000; padding: 22px 24px; text-align: center; }
    .portal-requester-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 12px 14px; margin-bottom: 16px; }
    #requestItemModal .issue-form-select { width: 100% !important; }
    #requestItemModal .select2-container { width: 100% !important; z-index: 1060; }
    #requestItemModal .select2-container--open { z-index: 1061; }
    #requestItemModal .select2-dropdown { z-index: 1062; }
    .store-table-compact td,
    .store-table-compact th {
        vertical-align: middle !important;
    }
    @media (max-width: 767px) {
        .store-menu .list-group-item {
            min-width: 76px;
            font-size: 0.7rem;
            padding: 8px 6px;
        }
        .store-menu .list-group-item i {
            display: block;
            font-size: 1rem;
            margin-bottom: 3px;
        }
        .store-section-toolbar .btn-sm {
            width: 100%;
        }
        .store-view-toggle {
            width: 100%;
        }
        .store-view-toggle .btn {
            flex: 1;
        }
    }
    @media (min-width: 768px) {
        .store-menu.list-group {
            flex-wrap: wrap;
        }
        .store-menu > .list-group-item {
            min-width: 0;
        }
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-8">
        <div class="page-header float-left">
            <div class="page-title"><h1>Item Requisition</h1></div>
        </div>
    </div>
</div>

<div id="storeAjaxBar" class="store-ajax-bar" aria-hidden="true"><div class="store-ajax-bar-fill"></div></div>
<div id="storeAjaxOverlay" class="store-ajax-overlay" aria-hidden="true">
    <div class="store-ajax-card">
        <div class="store-ajax-text" id="storeAjaxText">Working...</div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
            <strong><i class="fa fa-clipboard"></i> My Store Portal</strong>
            <div class="d-flex flex-wrap" style="gap:8px;">
                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#requestItemModal">
                    <i class="fa fa-plus"></i> Request Item
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="store-menu-wrap">
                <div class="list-group list-group-horizontal store-menu">
                    <a class="list-group-item active flex-fill text-center" data-section="section-dashboard"><i class="fa fa-dashboard"></i> Dashboard</a>
                    <a class="list-group-item flex-fill text-center" data-section="section-my-requests"><i class="fa fa-clock-o"></i> My Requests</a>
                    <a class="list-group-item flex-fill text-center" data-section="section-my-items"><i class="fa fa-cubes"></i> My Items</a>
                    <a class="list-group-item flex-fill text-center" data-section="section-incidents"><i class="fa fa-warning"></i> Incidents</a>
                </div>
            </div>

            @php
                $storeViewToggle = function ($tableId) {
                    return '<div class="btn-group btn-group-sm store-view-toggle" data-table="' . $tableId . '">
                        <button type="button" class="btn btn-outline-secondary" data-view="list"><i class="fa fa-list"></i> List</button>
                        <button type="button" class="btn btn-outline-secondary" data-view="grid"><i class="fa fa-th"></i> Grid</button>
                    </div>';
                };
                $storePdfBtn = function ($section) {
                    return '<button type="button" class="btn btn-sm btn-danger-custom portal-download-pdf-btn" data-section="' . $section . '"><i class="fa fa-file-pdf-o"></i> PDF</button>';
                };
            @endphp

            {{-- Dashboard --}}
            <div id="section-dashboard" class="store-section active">
                <div class="store-section-toolbar">
                    <div>
                        <div class="section-title">Dashboard</div>
                        <p class="text-muted small mb-0">Summary of your requisitions and requests. Updated: {{ $dash['generated_at'] ?? '-' }}</p>
                    </div>
                    <div>{!! $storePdfBtn('dashboard') !!}</div>
                </div>
                <div class="store-stat-grid">
                    <div class="store-stat-card"><div class="store-stat-val" id="statTotalRequests">{{ $dash['total_requests'] ?? 0 }}</div><div class="store-stat-label">Total Requests</div></div>
                    <div class="store-stat-card"><div class="store-stat-val text-warning" id="statPendingRequests">{{ $dash['pending_requests'] ?? 0 }}</div><div class="store-stat-label">Pending</div></div>
                    <div class="store-stat-card"><div class="store-stat-val" id="statActiveItems">{{ $dash['active_items'] ?? 0 }}</div><div class="store-stat-label">Active Items</div></div>
                    <div class="store-stat-card"><div class="store-stat-val text-danger" id="statOverdueItems">{{ $dash['overdue_items'] ?? 0 }}</div><div class="store-stat-label">Overdue Returns</div></div>
                    <div class="store-stat-card"><div class="store-stat-val" id="statReturnedItems">{{ $dash['returned_items'] ?? 0 }}</div><div class="store-stat-label">Returned</div></div>
                    <div class="store-stat-card"><div class="store-stat-val text-danger" id="statOpenIncidents">{{ $dash['open_incidents'] ?? 0 }}</div><div class="store-stat-label">Open Incidents</div></div>
                </div>
            </div>

            {{-- My Requests --}}
            <div id="section-my-requests" class="store-section">
                <div class="store-section-toolbar">
                    <div><div class="section-title">My Requests</div></div>
                    <div class="d-flex" style="gap:8px;">{!! $storePdfBtn('requests') !!}{!! $storeViewToggle('portalPendingTable') !!}</div>
                </div>
                <div class="portal-subsection-title"><i class="fa fa-clock-o"></i> Pending Approval (Requested)</div>
                <div class="store-data-wrapper mb-4" data-table-id="portalPendingTable">
                    <div class="store-list-view">
                        @if(($portalPendingRequests ?? collect())->isEmpty())
                            <div class="portal-table-empty" id="portalPendingEmpty">No pending requests.</div>
                        @endif
                        <div class="table-responsive" @if(($portalPendingRequests ?? collect())->isEmpty()) style="display:none;" @endif id="portalPendingTableWrap">
                            <table class="table table-bordered table-hover table-striped store-table-compact" id="portalPendingTable">
                                <thead class="bg-light"><tr><th width="50">#</th><th>Status</th><th>Items</th><th>Expected Return</th><th>Requested</th><th>Notes</th></tr></thead>
                                <tbody>
                                    @foreach($portalPendingRequests as $i => $req)
                                    <tr data-request-id="{{ $req['requestID'] }}">
                                        <td>{{ $i + 1 }}</td>
                                        <td><span class="store-badge-pill store-badge-pending">{{ $req['status_label'] ?? 'Requested' }}</span></td>
                                        <td class="small">{{ $req['items_summary'] }}</td>
                                        <td>{{ $req['expected_return_date'] ?? '-' }}</td>
                                        <td>{{ $req['requested_at'] }}</td>
                                        <td class="small">{{ $req['notes'] ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="store-grid-view row"></div>
                </div>
                <div class="store-section-toolbar mt-3">
                    <div><div class="portal-subsection-title mb-0"><i class="fa fa-history"></i> Request History</div></div>
                    <div>{!! $storeViewToggle('portalHistoryTable') !!}</div>
                </div>
                <div class="store-data-wrapper" data-table-id="portalHistoryTable">
                    <div class="store-list-view">
                        @if(($portalRequestHistory ?? collect())->isEmpty())
                            <div class="portal-table-empty">No request history yet.</div>
                        @endif
                        <div class="table-responsive" @if(($portalRequestHistory ?? collect())->isEmpty()) style="display:none;" @endif>
                            <table class="table table-bordered table-hover table-striped store-table-compact" id="portalHistoryTable">
                                <thead class="bg-light"><tr><th width="50">#</th><th>Status</th><th>Items</th><th>Requested</th><th>Reviewed</th><th>Review Notes</th></tr></thead>
                                <tbody>
                                    @foreach($portalRequestHistory as $i => $req)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><span class="store-badge-pill store-badge-{{ $req['status'] === 'approved' ? 'return-yes' : ($req['status'] === 'rejected' ? 'return-no' : 'pending') }}">{{ ucfirst($req['status']) }}</span></td>
                                        <td class="small">{{ $req['items_summary'] }}</td>
                                        <td>{{ $req['requested_at'] }}</td>
                                        <td>{{ $req['reviewed_at'] ?? '-' }}</td>
                                        <td class="small">{{ $req['review_notes'] ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- My Items --}}
            <div id="section-my-items" class="store-section">
                <div class="store-section-toolbar">
                    <div><div class="section-title">My Issued Items</div></div>
                    <div class="d-flex" style="gap:8px;">{!! $storePdfBtn('items') !!}{!! $storeViewToggle('portalItemsTable') !!}</div>
                </div>
                <div class="store-data-wrapper" data-table-id="portalItemsTable">
                    <div class="store-list-view">
                        @if(($portalRequisitions ?? collect())->isEmpty())
                            <div class="portal-table-empty">No items issued to you yet.</div>
                        @endif
                        <div class="table-responsive" @if(($portalRequisitions ?? collect())->isEmpty()) style="display:none;" @endif>
                            <table class="table table-bordered table-hover table-striped store-table-compact" id="portalItemsTable">
                                <thead class="bg-light"><tr><th width="50">#</th><th>Item</th><th>Label</th><th>Taken</th><th>Return Status</th><th>Expected Return</th><th>Issued</th></tr></thead>
                                <tbody>
                                    @foreach($portalRequisitions as $i => $req)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="font-weight-bold">{{ $req['item_name'] }}</td>
                                        <td>{{ $req['label'] }}</td>
                                        <td class="small">{{ $req['take_display'] }}</td>
                                        <td>{!! $req['return_status_html'] !!}</td>
                                        <td>{!! $req['expected_return_html'] !!}</td>
                                        <td class="small">{{ $req['issued_at'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- Incidents --}}
            <div id="section-incidents" class="store-section">
                <div class="store-section-toolbar">
                    <div><div class="section-title">Damaged / Lost Items Assigned to Me</div></div>
                    <div class="d-flex" style="gap:8px;">{!! $storePdfBtn('incidents') !!}{!! $storeViewToggle('portalIncidentsTable') !!}</div>
                </div>
                <div class="store-data-wrapper" data-table-id="portalIncidentsTable">
                    <div class="store-list-view">
                        @if(($portalIncidents ?? collect())->isEmpty())
                            <div class="portal-table-empty">No incidents recorded for you.</div>
                        @endif
                        <div class="table-responsive" @if(($portalIncidents ?? collect())->isEmpty()) style="display:none;" @endif>
                            <table class="table table-bordered table-hover table-striped store-table-compact" id="portalIncidentsTable">
                                <thead class="bg-light"><tr><th width="50">#</th><th>Type</th><th>Item</th><th>Label</th><th>Reason</th><th>Status</th><th>Recorded</th></tr></thead>
                                <tbody>
                                    @foreach($portalIncidents as $i => $inc)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ ucfirst($inc['incident_type']) }}</td>
                                        <td>{{ $inc['item_name'] }}</td>
                                        <td>{{ $inc['label'] }}</td>
                                        <td class="small">{{ $inc['reason'] ?? '-' }}</td>
                                        <td>{!! $inc['resolve_status_html'] !!}</td>
                                        <td class="small">{{ $inc['recorded_at'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="store-grid-view row"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Request Item Modal --}}
<div class="modal fade" id="requestItemModal" tabindex="-1" role="dialog" aria-labelledby="requestItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="requestItemModalLabel"><i class="fa fa-plus"></i> Request Item</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="issueItemForm">
                <div class="modal-body">
                    <p class="text-muted small mb-3">Submit a request for store items. An administrator will review and approve before items are issued.</p>
                    <div class="portal-requester-box">
                        <strong>Requester:</strong> {{ $requester['name'] ?? '-' }}
                        @if(!empty($requester['employee_number']))
                            <span class="text-muted">({{ $requester['employee_number'] }})</span>
                        @endif
                        <span class="badge badge-secondary ml-2">{{ ucfirst($portalRole) }}</span>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Item Category</label>
                        <select class="form-control" id="issueCategoryFilter">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->categoryID }}">{{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Select Item <span class="text-danger">*</span></label>
                        <select class="form-control issue-form-select" id="issueItemSelect" required>
                            <option value="">Search or select item in stock...</option>
                        </select>
                    </div>
                    <div id="issueStockInfo" class="alert alert-light border small py-2" style="display:none;">
                        <strong>Package / Unit:</strong> <span id="issuePackageUnit">-</span>
                        &nbsp;|&nbsp;
                        <strong>Available:</strong> <span id="issueAvailableCount">0</span> unit(s)
                    </div>
                    <div id="issueLabelsSection" style="display:none;">
                        <label class="font-weight-bold">Select Label(s) <span class="text-danger">*</span></label>
                        <div class="store-checkbox-toolbar">
                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-all-btn" data-target="#issueLabelsList">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-none-btn" data-target="#issueLabelsList">Deselect All</button>
                        </div>
                        <div id="issueLabelsList" class="border p-3" style="max-height:220px;overflow-y:auto;background:#fafafa;"></div>
                    </div>
                    <div id="issueTakeSection" style="display:none;">
                        <label class="font-weight-bold">Request Method & Quantity</label>
                        <div id="issueTakeChainInfo" class="alert alert-light border small py-2 mb-2" style="display:none;"></div>
                        <div class="form-group mb-2" id="issueTakeMethodWrap">
                            <label class="font-weight-bold">Request Method <span class="text-danger">*</span></label>
                            <select class="form-control" id="issueTakeMethod"><option value="">Choose request method...</option></select>
                        </div>
                        <div class="form-group mb-2" id="issueTakeTargetWrap" style="display:none;">
                            <label class="font-weight-bold" id="issueTakeTargetLabel">Package / Unit <span class="text-danger">*</span></label>
                            <select class="form-control" id="issueTakeTarget" style="display:none;"></select>
                            <div class="store-checkbox-toolbar" id="issueTakeTargetToolbar" style="display:none;">
                                <button type="button" class="btn btn-sm btn-outline-secondary store-check-all-btn" data-target="#issueTakeTargetList">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary store-check-none-btn" data-target="#issueTakeTargetList">Deselect All</button>
                            </div>
                            <div id="issueTakeTargetList" class="issue-take-target-list border p-3" style="display:none;"></div>
                            <small class="text-muted d-block mt-1" id="issueTakeTargetDetail"></small>
                        </div>
                        <div class="form-group mb-0" id="issueTakeQtyWrap">
                            <label class="font-weight-bold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="issueTakeQty" min="1" step="1" value="1">
                            <small class="text-muted" id="issueTakeQtyHint"></small>
                        </div>
                        <div id="issueTakeLivePreview" class="alert alert-info border small py-2 mt-3 mb-0" style="display:none;"></div>
                    </div>
                    <div id="issueExpectedReturnSection" class="form-group" style="display:none;">
                        <label class="font-weight-bold">Expected Return Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="issueExpectedReturnDate" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Notes</label>
                        <textarea class="form-control" id="issueNotes" rows="2" placeholder="Optional notes about this request..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="btnIssueItem" disabled>
                        <i class="fa fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('portal.partials.store_request_scripts', ['portalStoreRoute' => $portalStoreRoute])

<script>
(function () {
    const portalPdfRoute = @json($portalPdfRoute);

    $('.store-menu .list-group-item').on('click', function (e) {
        e.preventDefault();
        const section = $(this).data('section');
        $('.store-menu .list-group-item').removeClass('active');
        $(this).addClass('active');
        $('.store-section').removeClass('active');
        $('#' + section).addClass('active');
        refreshPortalGridsInSection(section);
    });

    $('.portal-download-pdf-btn').on('click', function () {
        const section = $(this).data('section') || 'dashboard';
        window.location.href = portalPdfRoute + '?section=' + encodeURIComponent(section);
    });

    window.onPortalRequestSubmitted = function (json) {
        if (json.request) {
            const req = json.request;
            $('#portalPendingEmpty').remove();
            $('#portalPendingTableWrap').show();
            const statusBadge = '<span class="store-badge-pill store-badge-pending">' + escapeHtml(req.status_label || 'Requested') + '</span>';
            const row = '<tr data-request-id="' + req.requestID + '">' +
                '<td></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td class="small">' + escapeHtml(req.items_summary || '') + '</td>' +
                '<td>' + escapeHtml(req.expected_return_date || '-') + '</td>' +
                '<td>' + escapeHtml(req.requested_at || '') + '</td>' +
                '<td class="small">' + escapeHtml(req.notes || '-') + '</td></tr>';
            const $table = $('#portalPendingTable');
            const $tbody = $table.find('tbody');
            $tbody.prepend(row);
            $tbody.find('tr').each(function (i) { $(this).find('td:first').text(i + 1); });
            const pending = parseInt($('#statPendingRequests').text(), 10) || 0;
            const total = parseInt($('#statTotalRequests').text(), 10) || 0;
            $('#statPendingRequests').text(pending + 1);
            $('#statTotalRequests').text(total + 1);
            if ($.fn.DataTable.isDataTable($table)) {
                $table.DataTable().destroy();
            }
            initPortalTables();
            buildStoreGrid('portalPendingTable');
        }
        $('.store-menu .list-group-item[data-section="section-my-requests"]').trigger('click');
    };

    const storeViewConfigs = {
        portalPendingTable: { titleCol: 2, titleHtml: false, fields: [{ col: 1, label: 'Status', html: true }, { col: 3, label: 'Expected Return' }, { col: 4, label: 'Requested' }] },
        portalHistoryTable: { titleCol: 2, titleHtml: false, fields: [{ col: 1, label: 'Status', html: true }, { col: 3, label: 'Requested' }, { col: 4, label: 'Reviewed' }] },
        portalItemsTable: { titleCol: 1, titleHtml: false, fields: [{ col: 2, label: 'Label' }, { col: 3, label: 'Taken' }, { col: 4, label: 'Return Status', html: true }] },
        portalIncidentsTable: { titleCol: 2, titleHtml: false, fields: [{ col: 1, label: 'Type' }, { col: 3, label: 'Label' }, { col: 5, label: 'Status', html: true }] },
    };

    function storeIsMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }
    function storeDeviceKey() {
        return storeIsMobile() ? 'mobile' : 'desktop';
    }
    function storeDefaultView() {
        return storeIsMobile() ? 'grid' : 'list';
    }
    function storePrefKey(tableId) {
        return 'portal_view_' + storeDeviceKey() + '_' + tableId;
    }
    function storeUserPrefKey(tableId) {
        return 'portal_view_user_' + storeDeviceKey() + '_' + tableId;
    }
    function storeHasUserViewPref(tableId) {
        try { return localStorage.getItem(storeUserPrefKey(tableId)) === '1'; } catch (e) { return false; }
    }
    function storeGetView(tableId) {
        if (storeHasUserViewPref(tableId)) {
            const saved = localStorage.getItem(storePrefKey(tableId));
            if (saved === 'list' || saved === 'grid') return saved;
        }
        return storeDefaultView();
    }
    function storeSetUserViewPref(tableId, view) {
        try {
            localStorage.setItem(storePrefKey(tableId), view);
            localStorage.setItem(storeUserPrefKey(tableId), '1');
        } catch (e) {}
    }
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }
    function getStoreTableRows($table) {
        const rows = [];
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().rows({ search: 'applied' }).every(function () { rows.push($(this.node())); });
        } else {
            $table.find('tbody tr').each(function () { rows.push($(this)); });
        }
        return rows;
    }
    function buildStoreGrid(tableId) {
        const config = storeViewConfigs[tableId];
        if (!config) return;
        const $wrapper = $('.store-data-wrapper[data-table-id="' + tableId + '"]');
        const $grid = $wrapper.find('.store-grid-view').empty();
        const $table = $('#' + tableId);
        let cardCount = 0;
        getStoreTableRows($table).forEach(function ($tr) {
            const $cells = $tr.find('td');
            if (!$cells.length) return;
            const colCount = $table.find('thead th').length;
            if ($cells.length !== colCount) return;
            const titleContent = config.titleHtml ? $cells.eq(config.titleCol).html() : escapeHtml($cells.eq(config.titleCol).text().trim());
            let metaHtml = '';
            (config.fields || []).forEach(f => {
                const val = f.html ? $cells.eq(f.col).html() : escapeHtml($cells.eq(f.col).text().trim());
                metaHtml += '<div class="store-grid-meta-row"><span class="store-grid-meta-label">' + escapeHtml(f.label) + '</span><span class="store-grid-meta-value">' + val + '</span></div>';
            });
            $grid.append($('<div class="col-12 col-sm-6 col-xl-4 mb-3"></div>').append(
                '<div class="store-grid-card"><div class="store-grid-card-head"><span>#' + escapeHtml($cells.eq(0).text().trim()) + '</span></div><div class="store-grid-card-title">' + titleContent + '</div><div>' + metaHtml + '</div></div>'
            ));
            cardCount++;
        });
        if (cardCount === 0) {
            $grid.append('<div class="col-12"><div class="store-grid-empty">No data available.</div></div>');
        }
    }
    function setStoreView(tableId, view, userChoice) {
        const $wrapper = $('.store-data-wrapper[data-table-id="' + tableId + '"]');
        if (!$wrapper.length) return;
        if (view === 'grid') buildStoreGrid(tableId);
        $wrapper.attr('data-active-view', view);
        $('.store-view-toggle[data-table="' + tableId + '"] button').removeClass('active');
        $('.store-view-toggle[data-table="' + tableId + '"] button[data-view="' + view + '"]').addClass('active');
        if (userChoice) storeSetUserViewPref(tableId, view);
    }
    function initPortalDataViews() {
        Object.keys(storeViewConfigs).forEach(function (tableId) {
            setStoreView(tableId, storeGetView(tableId), false);
        });
    }
    function refreshPortalGridsInSection(sectionId) {
        $('#' + sectionId).find('.store-data-wrapper').each(function () {
            const tableId = $(this).attr('data-table-id');
            if (tableId && $(this).attr('data-active-view') === 'grid') {
                buildStoreGrid(tableId);
            }
        });
    }
    function initPortalTables() {
        ['portalPendingTable', 'portalHistoryTable', 'portalItemsTable', 'portalIncidentsTable'].forEach(function (tableId) {
            const $table = $('#' + tableId);
            if (!$table.length || $.fn.DataTable.isDataTable($table)) return;
            if ($table.find('tbody tr').length === 0) return;
            $table.DataTable({ pageLength: 10, order: [], language: { emptyTable: 'No data available.' } });
        });
    }
    $(document).on('click', '.store-view-toggle button', function () {
        const tableId = $(this).closest('.store-view-toggle').data('table');
        setStoreView(tableId, $(this).data('view'), true);
    });
    $(window).on('resize', function () {
        clearTimeout(window._portalResizeTimer);
        window._portalResizeTimer = setTimeout(function () {
            initPortalDataViews();
            const $activeSection = $('.store-section.active');
            if ($activeSection.length) {
                refreshPortalGridsInSection($activeSection.attr('id'));
            }
        }, 200);
    });
    $(function () {
        initPortalTables();
        initPortalDataViews();
    });
})();
</script>
