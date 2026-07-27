@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, div, .form-control, .form-select { border-radius: 0 !important; }
    .bg-primary-custom { background-color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .table thead th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 700;
        border-bottom: 2px solid #dee2e6 !important;
    }
    .permission-tabs .nav-link { cursor: pointer; color: #940000; border-radius: 0; }
    .permission-tabs .nav-link.active { background: #fff5f5; color: #940000; font-weight: 600; border-color: #940000; }
    .badge-count { background: #940000; color: #fff; border-radius: 10px; padding: 2px 6px; font-size: 0.75rem; margin-left: 6px; }
    .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: progressSlide 1.1s linear infinite;
    }
    @keyframes progressSlide {
        0% { left: -40%; }
        100% { left: 100%; }
    }
    .modal-loading {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 10;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    /* Action Circle Style */
    .action-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: 1px solid #ddd;
        cursor: pointer;
    }
    .action-circle:hover {
        background: #940000;
        color: white;
        border-color: #940000;
    }
    .action-circle i { font-size: 14px; }

    /* Layout Toggle */
    .view-toggle-btn.active {
        background-color: #940000 !important;
        color: white !important;
        border-color: #940000 !important;
    }

    @media (max-width: 991px) {
        #perm-list-view { display: none !important; }
        #perm-grid-view { display: block !important; }
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Permissions</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">
            <strong>Permission Requests</strong>
            <div class="btn-group">
                @if($adminInChain ?? false)
                <button type="button" class="btn btn-sm btn-light border-0" style="border-radius: 0;" onclick="openApprovalChainModal()">
                    <i class="fa fa-link"></i> Approval Chain
                </button>
                @endif
                <button type="button" class="btn btn-sm btn-light border-0" style="border-radius: 0;" onclick="openStudentRequesterModal()">
                    <i class="fa fa-user-plus"></i> Student Permission Requesters
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="form-loading" id="permissionLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Filtering...</span>
                <div class="form-progress"></div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                <small class="text-muted" id="recordCount">{{ $permissions->count() }} record(s)</small>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary view-toggle-btn active" data-view="list">
                        <i class="fa fa-list"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary view-toggle-btn" data-view="grid">
                        <i class="fa fa-th-large"></i>
                    </button>
                </div>
            </div>

            <ul class="nav nav-tabs permission-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'teacher' ? 'active' : '' }}" href="{{ route('admin.hr.permission', ['tab' => 'teacher']) }}">
                        Teacher Permission
                        @if(!empty($tabCounts['teacher']))
                            <span class="badge-count">{{ $tabCounts['teacher'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'staff' ? 'active' : '' }}" href="{{ route('admin.hr.permission', ['tab' => 'staff']) }}">
                        Staff Permission
                        @if(!empty($tabCounts['staff']))
                            <span class="badge-count">{{ $tabCounts['staff'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'student' ? 'active' : '' }}" href="{{ route('admin.hr.permission', ['tab' => 'student']) }}">
                        Student Permission
                        @if(!empty($tabCounts['student']))
                            <span class="badge-count">{{ $tabCounts['student'] }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <div class="row mb-3">
                <div class="col-md-12">
                    <form id="permissionFilterForm" method="GET" action="{{ route('admin.hr.permission') }}" class="p-3 bg-light border shadow-sm">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label class="font-weight-bold small">Filter By:</label>
                                <select name="filter_type" id="filter_type" class="form-control form-control-sm" onchange="toggleFilterInputs()">
                                    <option value="all" {{ ($filterType ?? '') == 'all' ? 'selected' : '' }}>All Records</option>
                                    <option value="range" {{ ($filterType ?? '') == 'range' ? 'selected' : '' }}>Date Range</option>
                                    <option value="month" {{ ($filterType ?? '') == 'month' ? 'selected' : '' }}>Specific Month</option>
                                    <option value="year" {{ ($filterType ?? '') == 'year' ? 'selected' : '' }}>Specific Year</option>
                                </select>
                            </div>

                            <div id="range_inputs" class="col-md-5 filter-input-group" style="display: {{ ($filterType ?? '') == 'range' ? 'flex' : 'none' }}; gap: 10px;">
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">From:</label>
                                    <input type="date" name="date_from" id="filterDateFrom" class="form-control form-control-sm" value="{{ $dateFrom ?? '' }}">
                                </div>
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">To:</label>
                                    <input type="date" name="date_to" id="filterDateTo" class="form-control form-control-sm" value="{{ $dateTo ?? '' }}">
                                </div>
                            </div>

                            <div id="month_inputs" class="col-md-5 filter-input-group" style="display: {{ ($filterType ?? '') == 'month' ? 'flex' : 'none' }}; gap: 10px;">
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">Month:</label>
                                    <select name="filter_month" class="form-control form-control-sm">
                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $monthName)
                                            <option value="{{ $index + 1 }}" {{ ($filterMonth ?? date('n')) == ($index + 1) ? 'selected' : '' }}>{{ $monthName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">Year:</label>
                                    <select name="filter_month_year" class="form-control form-control-sm">
                                        @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                            <option value="{{ $y }}" {{ ($filterMonthYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div id="year_inputs" class="col-md-2 filter-input-group" style="display: {{ ($filterType ?? '') == 'year' ? 'block' : 'none' }};">
                                <label class="font-weight-bold small">Year:</label>
                                <select name="filter_year" class="form-control form-control-sm">
                                    @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                        <option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="font-weight-bold small">Search Name/ID:</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="search" placeholder="Search..." value="{{ $search ?? '' }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary-custom" type="submit"><i class="fa fa-search"></i> Apply</button>
                                        <button class="btn btn-outline-secondary" type="button" onclick="resetAdminFilters()"><i class="fa fa-undo"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mb-3">
                <span class="badge badge-warning">Pending: {{ $pendingCount }}</span>
                <span class="badge badge-success ml-2">Approved: {{ $approvedCount }}</span>
                <span class="badge badge-danger ml-2">Rejected: {{ $rejectedCount }}</span>
            </div>

            <div id="perm-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="bg-primary-custom text-white">
                            <tr>
                                <th>Requester</th>
                                <th>Mode</th>
                                <th>Period</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="permissionTableBody">
                            @forelse($permissions as $permission)
                                <tr data-permission-id="{{ $permission->permissionID }}">
                                    <td>
                                        @php
                                            $requesterName = 'N/A';
                                            if($activeTab === 'student' && $permission->student) {
                                                $requesterName = trim($permission->student->first_name . ' ' . $permission->student->last_name);
                                            } elseif($activeTab === 'staff' && $permission->staff) {
                                                $requesterName = trim($permission->staff->first_name . ' ' . $permission->staff->last_name);
                                            } elseif($permission->teacher) {
                                                $requesterName = trim($permission->teacher->first_name . ' ' . $permission->teacher->last_name);
                                            }
                                        @endphp
                                        <strong>{{ $requesterName }}</strong>
                                        <br><small class="text-muted">{{ ucfirst($activeTab) }}</small>
                                    </td>
                                    <td>{{ ucfirst($permission->time_mode) }}</td>
                                    <td>
                                        @if($permission->time_mode === 'days')
                                            {{ \Carbon\Carbon::parse($permission->start_date)->format('j F Y') }} - {{ \Carbon\Carbon::parse($permission->end_date)->format('j F Y') }}
                                            <br><small class="text-muted">({{ $permission->days_count }} days)</small>
                                        @else
                                            {{ $permission->start_time }} - {{ $permission->end_time }}
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($permission->reason_type) }}</td>
                                    <td class="permission-status">
                                        @if($permission->status === 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($permission->status === 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="bx bx-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow border">
                                                <a class="dropdown-item" href="#" onclick="viewPermissionProgress({{ $permission->permissionID }}); return false;">
                                                    <i class="fa fa-project-diagram text-info"></i> View Progress
                                                </a>
                                                <a class="dropdown-item" href="#" onclick='viewPermissionDetails({{ json_encode([
                                                    'id' => $permission->permissionID,
                                                    'time_mode' => ucfirst($permission->time_mode),
                                                    'days_count' => $permission->days_count,
                                                    'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
                                                    'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
                                                    'return_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->addDay()->format('j F Y') : null,
                                                    'start_time' => $permission->start_time,
                                                    'end_time' => $permission->end_time,
                                                    'reason_type' => ucfirst($permission->reason_type),
                                                    'reason_description' => $permission->reason_description,
                                                    'status' => ucfirst($permission->status),
                                                    'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : 'N/A'
                                                ]) }}); return false;'>
                                                    <i class="fa fa-list-alt text-secondary"></i> View Details
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.permissions.download', $permission->permissionID) }}">
                                                    <i class="fa fa-file-pdf text-danger"></i> Download PDF
                                                </a>
                                                @if($permission->attachment_path)
                                                    <a class="dropdown-item" href="{{ route('admin.permissions.attachment', $permission->permissionID) }}" target="_blank">
                                                        <i class="fa fa-paperclip text-warning"></i> View Attachment
                                                    </a>
                                                @endif
                                                @if($permission->status === 'pending')
                                                    @php
                                                        $canApprove = !($adminInChain ?? false) || ($permission->current_approval_order == $adminApprovalOrder);
                                                    @endphp
                                                    @if($canApprove)
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-success" href="#" data-toggle="modal" data-target="#approveModal{{ $permission->permissionID }}">
                                                        <i class="fa fa-check"></i> Approve
                                                    </a>
                                                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal{{ $permission->permissionID }}">
                                                        <i class="fa fa-times"></i> Reject
                                                    </a>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No permission requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>{{-- end list view --}}

            <div id="perm-grid-view" style="display:none;">
                <div class="row" id="permGridContainer">
                    @foreach($permissions as $permission)
                        @php
                            $borderColor = match($permission->status) {
                                'approved' => '#28a745',
                                'rejected' => '#dc3545',
                                default => '#ffc107'
                            };
                            $requesterName = 'N/A';
                            if($activeTab === 'student' && $permission->student) {
                                $requesterName = trim($permission->student->first_name . ' ' . $permission->student->last_name);
                            } elseif($activeTab === 'staff' && $permission->staff) {
                                $requesterName = trim($permission->staff->first_name . ' ' . $permission->staff->last_name);
                            } elseif($permission->teacher) {
                                $requesterName = trim($permission->teacher->first_name . ' ' . $permission->teacher->last_name);
                            }
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 shadow-sm border-0" style="border-left: 5px solid {{ $borderColor }} !important; background: #fff;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-0"><strong>{{ $requesterName }}</strong></h6>
                                            <small class="text-muted">{{ ucfirst($activeTab) }}</small>
                                        </div>
                                        <div class="dropdown">
                                            <div class="action-circle" data-toggle="dropdown">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </div>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" onclick="viewPermissionProgress({{ $permission->permissionID }}); return false;">
                                                    <i class="fa fa-project-diagram text-info"></i> View Progress
                                                </a>
                                                <a class="dropdown-item" href="#" onclick='viewPermissionDetails({{ json_encode([
                                                    'id' => $permission->permissionID,
                                                    'time_mode' => ucfirst($permission->time_mode),
                                                    'days_count' => $permission->days_count,
                                                    'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
                                                    'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
                                                    'return_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->addDay()->format('j F Y') : null,
                                                    'start_time' => $permission->start_time,
                                                    'end_time' => $permission->end_time,
                                                    'reason_type' => ucfirst($permission->reason_type),
                                                    'reason_description' => $permission->reason_description,
                                                    'status' => ucfirst($permission->status),
                                                    'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : 'N/A'
                                                ]) }}); return false;'>
                                                    <i class="fa fa-list-alt text-secondary"></i> View Details
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.permissions.download', $permission->permissionID) }}">
                                                    <i class="fa fa-file-pdf text-danger"></i> Download PDF
                                                </a>
                                                @if($permission->attachment_path)
                                                    <a class="dropdown-item" href="{{ route('admin.permissions.attachment', $permission->permissionID) }}" target="_blank">
                                                        <i class="fa fa-paperclip text-warning"></i> View Attachment
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Period:</small>
                                        @if($permission->time_mode === 'days')
                                            <i class="fa fa-calendar-alt text-primary-custom"></i> {{ \Carbon\Carbon::parse($permission->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($permission->end_date)->format('d M Y') }}
                                            <span class="badge badge-light border ml-1">{{ $permission->days_count }} days</span>
                                        @else
                                            <i class="fa fa-clock text-primary-custom"></i> {{ $permission->start_time }} - {{ $permission->end_time }}
                                        @endif
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Reason ({{ $permission->reason_type }}):</small>
                                        <p class="mb-0 text-muted" style="font-size: 0.85rem;">{{ Str::limit($permission->reason_description, 80) }}</p>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge badge-{{ $permission->status === 'approved' ? 'success' : ($permission->status === 'rejected' ? 'danger' : 'warning') }} text-uppercase" style="font-size: 10px;">
                                            {{ $permission->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>{{-- end grid view --}}

            <div id="permissionModals">
                @foreach($permissions as $permission)
                    @if($permission->status === 'pending')
                        @php
                            $canApprove = !($adminInChain ?? false) || ($permission->current_approval_order == $adminApprovalOrder);
                        @endphp
                        @if($canApprove)
                        <div class="modal fade" id="approveModal{{ $permission->permissionID }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary-custom text-white">
                                        <h5 class="modal-title">Approve Permission</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.permissions.approve') }}" enctype="multipart/form-data" class="permission-approve-form" data-permission-id="{{ $permission->permissionID }}">
                                        @csrf
                                        <input type="hidden" name="permissionID" value="{{ $permission->permissionID }}">
                                        <div class="modal-body position-relative">
                                            <div class="modal-loading">
                                                <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                                                <p class="mt-2">Processing...</p>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Response</label>
                                                <textarea class="form-control" name="admin_response" rows="3" required></textarea>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Upload Attachment (optional)</label>
                                                <input type="file" class="form-control" name="admin_attachment" accept=".pdf,.jpg,.jpeg,.png">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary-custom">Approve</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="rejectModal{{ $permission->permissionID }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary-custom text-white">
                                        <h5 class="modal-title">Reject Permission</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.permissions.reject') }}" class="permission-reject-form" data-permission-id="{{ $permission->permissionID }}">
                                        @csrf
                                        <input type="hidden" name="permissionID" value="{{ $permission->permissionID }}">
                                        <div class="modal-body position-relative">
                                            <div class="modal-loading">
                                                <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                                                <p class="mt-2">Processing...</p>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Response</label>
                                                <textarea class="form-control" name="admin_response" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approvalChainModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Permission Approval Chain</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="approvalChainModalBody">
                <div class="text-center p-5">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary-custom"></i>
                    <p class="mt-2">Loading approval chain settings...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.permission_details_modal')
@include('partials.permission_progress_modal')

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", confirmButtonColor: '#940000' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Failed', text: "{{ session('error') }}", confirmButtonColor: '#940000' });
    @endif

    const filterForm = document.getElementById('permissionFilterForm');
    const loadingBar = document.getElementById('permissionLoading');
    const tableBody = document.getElementById('permissionTableBody');
    const gridContainer = document.getElementById('permGridContainer');
    const activeTab = "{{ $activeTab }}";

    function renderPermissionRows(items) {
        const modalContainer = document.getElementById('permissionModals');
        if (!items || items.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No permission requests found.</td></tr>';
            gridContainer.innerHTML = '<div class="col-12 text-center py-4">No permission requests found.</div>';
            if (modalContainer) modalContainer.innerHTML = '';
            return;
        }

        let modalsHtml = '';

        // Render List View
        tableBody.innerHTML = items.map(item => {
            const period = item.timeMode === 'days'
                ? `${item.startDate || ''} - ${item.endDate || ''} (${item.daysCount || 0} days)`
                : `${item.startTime || ''} - ${item.endTime || ''}`;

            const statusBadge = item.status === 'approved'
                ? '<span class="badge badge-success">Approved</span>'
                : item.status === 'rejected'
                    ? '<span class="badge badge-danger">Rejected</span>'
                    : '<span class="badge badge-warning">Pending</span>';

            const attachmentLink = item.attachment
                ? `<a class="dropdown-item" href="${item.attachment}" target="_blank"><i class="fa fa-paperclip text-warning"></i> View Attachment</a>`
                : '';

            let actionButtons = '';
            if (item.status === 'pending') {
                actionButtons = `
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-success" href="#" data-toggle="modal" data-target="#approveModal${item.permissionID}">
                        <i class="fa fa-check"></i> Approve
                    </a>
                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal${item.permissionID}">
                        <i class="fa fa-times"></i> Reject
                    </a>
                `;
                modalsHtml += `
                    <div class="modal fade" id="approveModal${item.permissionID}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary-custom text-white">
                                    <h5 class="modal-title">Approve Permission</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                </div>
                                <form method="POST" action="{{ route('admin.permissions.approve') }}" enctype="multipart/form-data" class="permission-approve-form" data-permission-id="${item.permissionID}">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="permissionID" value="${item.permissionID}">
                                    <div class="modal-body position-relative">
                                        <div class="modal-loading">
                                            <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                                            <p class="mt-2">Processing...</p>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Response</label>
                                            <textarea class="form-control" name="admin_response" rows="3" required></textarea>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Upload Attachment (optional)</label>
                                            <input type="file" class="form-control" name="admin_attachment" accept=".pdf,.jpg,.jpeg,.png">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary-custom">Approve</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="rejectModal${item.permissionID}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary-custom text-white">
                                    <h5 class="modal-title">Reject Permission</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                </div>
                                <form method="POST" action="{{ route('admin.permissions.reject') }}" class="permission-reject-form" data-permission-id="${item.permissionID}">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="permissionID" value="${item.permissionID}">
                                    <div class="modal-body position-relative">
                                        <div class="modal-loading">
                                            <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                                            <p class="mt-2">Processing...</p>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Response</label>
                                            <textarea class="form-control" name="admin_response" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;
            }

            const detailsData = JSON.stringify({
                id: item.permissionID,
                time_mode: item.timeMode ? item.timeMode.charAt(0).toUpperCase() + item.timeMode.slice(1) : '',
                days_count: item.daysCount,
                start_date: item.startDate,
                end_date: item.endDate,
                start_time: item.startTime,
                end_time: item.endTime,
                reason_type: item.reasonType ? item.reasonType.charAt(0).toUpperCase() + item.reasonType.slice(1) : '',
                status: item.status ? item.status.charAt(0).toUpperCase() + item.status.slice(1) : '',
                requested_at: item.requested_at || 'N/A'
            });

            const downloadUrl = `{{ url('admin/hr/permissions/export-pdf') }}/${item.permissionID}`;

            return `
                <tr data-permission-id="${item.permissionID}">
                    <td><strong>${item.requesterName || 'N/A'}</strong></td>
                    <td>${item.timeMode ? item.timeMode.charAt(0).toUpperCase() + item.timeMode.slice(1) : ''}</td>
                    <td>${period}</td>
                    <td>${item.reasonType ? item.reasonType.charAt(0).toUpperCase() + item.reasonType.slice(1) : ''}</td>
                    <td class="permission-status">${statusBadge}</td>
                    <td class="text-center">
                        <div class="dropdown">
                            <div class="action-circle" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-v"></i>
                            </div>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" onclick="viewPermissionProgress(${item.permissionID}); return false;">
                                    <i class="fa fa-project-diagram text-info"></i> View Progress
                                </a>
                                <a class="dropdown-item" href="#" onclick='viewPermissionDetails(${detailsData}); return false;'>
                                    <i class="fa fa-list-alt text-secondary"></i> View Details
                                </a>
                                <a class="dropdown-item" href="${downloadUrl}">
                                    <i class="fa fa-file-pdf text-danger"></i> Download PDF
                                </a>
                                ${attachmentLink}
                                ${actionButtons}
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Render Grid View
        gridContainer.innerHTML = items.map(item => {
            const borderColor = item.status === 'approved' ? '#28a745' : (item.status === 'rejected' ? '#dc3545' : '#ffc107');
            const statusClass = item.status === 'approved' ? 'success' : (item.status === 'rejected' ? 'danger' : 'warning');

            const period = item.timeMode === 'days'
                ? `<i class="fa fa-calendar-alt text-primary-custom"></i> ${item.startDate} - ${item.endDate} <span class="badge badge-light border ml-1">${item.daysCount} days</span>`
                : `<i class="fa fa-clock text-primary-custom"></i> ${item.startTime} - ${item.endTime}`;

            const attachmentLink = item.attachment
                ? `<a class="dropdown-item" href="${item.attachment}" target="_blank"><i class="fa fa-paperclip text-warning"></i> View Attachment</a>`
                : '';

            let actionButtons = '';
            if (item.status === 'pending') {
                actionButtons = `
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-success" href="#" data-toggle="modal" data-target="#approveModal${item.permissionID}">
                        <i class="fa fa-check"></i> Approve
                    </a>
                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal${item.permissionID}">
                        <i class="fa fa-times"></i> Reject
                    </a>
                `;
            }

            const detailsData = JSON.stringify({
                id: item.permissionID,
                time_mode: item.timeMode ? item.timeMode.charAt(0).toUpperCase() + item.timeMode.slice(1) : '',
                days_count: item.daysCount,
                start_date: item.startDate,
                end_date: item.endDate,
                start_time: item.startTime,
                end_time: item.endTime,
                reason_type: item.reasonType ? item.reasonType.charAt(0).toUpperCase() + item.reasonType.slice(1) : '',
                status: item.status ? item.status.charAt(0).toUpperCase() + item.status.slice(1) : '',
                requested_at: item.requested_at || 'N/A'
            });

            const downloadUrl = `{{ url('admin/hr/permissions/export-pdf') }}/${item.permissionID}`;

            return `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm border-0" style="border-left: 5px solid ${borderColor} !important; background: #fff;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0"><strong>${item.requesterName || 'N/A'}</strong></h6>
                                    <small class="text-muted">${activeTab.charAt(0).toUpperCase() + activeTab.slice(1)}</small>
                                </div>
                                <div class="dropdown">
                                    <div class="action-circle" data-toggle="dropdown">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="#" onclick="viewPermissionProgress(${item.permissionID}); return false;">
                                            <i class="fa fa-project-diagram text-info"></i> View Progress
                                        </a>
                                        <a class="dropdown-item" href="#" onclick='viewPermissionDetails(${detailsData}); return false;'>
                                            <i class="fa fa-list-alt text-secondary"></i> View Details
                                        </a>
                                        <a class="dropdown-item" href="${downloadUrl}">
                                            <i class="fa fa-file-pdf text-danger"></i> Download PDF
                                        </a>
                                        ${attachmentLink}
                                        ${actionButtons}
                                    </div>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="mb-2">
                                <small class="text-muted d-block">Period:</small>
                                ${period}
                            </div>
                            <div>
                                <small class="text-muted d-block">Reason (${item.reasonType}):</small>
                                <p class="mb-0 text-muted" style="font-size: 0.85rem;">${item.reasonDescription ? (item.reasonDescription.substring(0, 80) + '...') : ''}</p>
                            </div>
                            <div class="mt-2">
                                <span class="badge badge-${statusClass} text-uppercase" style="font-size: 10px;">
                                    ${item.status}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        if (modalContainer) modalContainer.innerHTML = modalsHtml;
    }

    function submitAdminFilters() {
        if (!filterForm) return;
        if (loadingBar) loadingBar.style.display = 'flex';
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        fetch(`${filterForm.action}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderPermissionRows(data.data);
                const countBadge = document.querySelector(`.permission-tabs .nav-link.active .badge-count`);
                if (countBadge) countBadge.textContent = data.data.length;
                const recordCountEl = document.getElementById('recordCount');
                if (recordCountEl) recordCountEl.textContent = `${data.data.length} record(s)`;
            } else {
                renderPermissionRows([]);
            }
        })
        .catch(() => renderPermissionRows([]))
        .finally(() => {
            if (loadingBar) loadingBar.style.display = 'none';
        });
    }

    function toggleFilterInputs() {
        const type = $('#filter_type').val();
        $('.filter-input-group').hide();
        if (type === 'range') {
            $('#range_inputs').css('display', 'flex');
        } else if (type === 'month') {
            $('#month_inputs').css('display', 'flex');
        } else if (type === 'year') {
            $('#year_inputs').show();
        }
    }

    function resetAdminFilters() {
        window.location.href = "{{ route('admin.hr.permission') }}?tab={{ $activeTab }}";
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAdminFilters();
        });
    }

    function initViewToggle() {
        var isMobile = window.innerWidth <= 991;
        var storageKey = 'admin_perm_request_view_pref';
        var savedView = sessionStorage.getItem(storageKey);
        var activeView = savedView || (isMobile ? 'grid' : 'list');

        function applyView(view) {
            var listEl = document.getElementById('perm-list-view');
            var gridEl = document.getElementById('perm-grid-view');
            var btns = document.querySelectorAll('.view-toggle-btn');
            if (!listEl || !gridEl) return;
            listEl.style.display = view === 'list' ? 'block' : 'none';
            gridEl.style.display = view === 'grid' ? 'block' : 'none';
            btns.forEach(function(btn) {
                var isActive = btn.getAttribute('data-view') === view;
                btn.classList.toggle('active', isActive);
            });
            sessionStorage.setItem(storageKey, view);
        }

        applyView(activeView);

        document.querySelectorAll('.view-toggle-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                applyView(this.getAttribute('data-view'));
            });
        });
    }

    initViewToggle();

    function setActiveTab(link) {
        document.querySelectorAll('.permission-tabs .nav-link').forEach(tab => {
            tab.classList.remove('active');
        });
        link.classList.add('active');
    }

    function bindAdminTabLinks() {
        document.querySelectorAll('.permission-tabs .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabInput = document.querySelector('#permissionFilterForm input[name="tab"]');
                if (tabInput) {
                    const url = new URL(this.href, window.location.origin);
                    const tabValue = url.searchParams.get('tab') || 'teacher';
                    tabInput.value = tabValue;
                }
                setActiveTab(this);
                submitAdminFilters();
            });
        });
    }

    bindAdminTabLinks();

    function openApprovalChainModal() {
        $('#approvalChainModal').modal('show');
        fetch("{{ route('admin.permissions.chain.view') }}", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            document.getElementById('approvalChainModalBody').innerHTML = html;
            // Initialize the chain after loading
            initApprovalChain();
        });
    }

    // Approval Chain Functions
    let availableRoles = [];
    let availablePositions = [];

    function addChainStep(existingData = null) {
        const container = document.getElementById('chainStepsContainer');
        const stepCount = container.querySelectorAll('.chain-step').length + 1;

        const stepDiv = document.createElement('div');
        stepDiv.className = 'chain-step p-3 mb-2 border rounded bg-light position-relative';

        let roleOptions = '<optgroup label="Special">';
        roleOptions += `<option value="admin">Admin (School Administrator)</option>`;
        roleOptions += '</optgroup><optgroup label="Roles">';
        availableRoles.forEach(r => {
            roleOptions += `<option value="role_${r.id}">${r.name}</option>`;
        });
        roleOptions += '</optgroup><optgroup label="Positions">';
        availablePositions.forEach(p => {
            roleOptions += `<option value="prof_${p.id}">${p.name}</option>`;
        });
        roleOptions += '</optgroup>';

        stepDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="mr-3 font-weight-bold step-number">Step ${stepCount}</div>
                <div class="flex-grow-1 mr-3">
                    <select class="form-control approver-select">
                        <option value="">-- Select Approver --</option>
                        ${roleOptions}
                    </select>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.chain-step').remove(); updateStepNumbers();">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(stepDiv);

        if (existingData) {
            const select = stepDiv.querySelector('.approver-select');
            if (existingData.is_admin) {
                select.value = 'admin';
            } else if (existingData.role_id) {
                select.value = 'role_' + existingData.role_id;
            } else if (existingData.profession_id) {
                select.value = 'prof_' + existingData.profession_id;
            }
        }
    }

    function updateStepNumbers() {
        const steps = document.querySelectorAll('.chain-step');
        steps.forEach((step, index) => {
            step.querySelector('.step-number').innerText = 'Step ' + (index + 1);
        });
    }

    function loadChainForType(type) {
        document.querySelectorAll('.chain-step').forEach(e => e.remove());
        document.getElementById('chainLoading').style.display = 'block';

        fetch("{{ route('admin.permissions.chain.get') }}?type=" + type, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('chainLoading').style.display = 'none';
            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach(chain => addChainStep(chain));
            } else {
                addChainStep();
            }
        });
    }

    function saveChainSetup() {
        const type = document.getElementById('chainTypeSelect').value;
        const steps = document.querySelectorAll('.chain-step');

        let approvers = [];
        let valid = true;

        steps.forEach((step, index) => {
            const val = step.querySelector('.approver-select').value;
            const select = step.querySelector('.approver-select');
            const text = select.options[select.selectedIndex].text;
            if (!val) { valid = false; return; }

            if (val === 'admin') {
                approvers.push({ is_admin: true, role_name: text });
            } else if (val.startsWith('role_')) {
                approvers.push({ role_id: val.split('_')[1], role_name: text });
            } else if (val.startsWith('prof_')) {
                approvers.push({ profession_id: val.split('_')[1], role_name: text });
            }
        });

        if (!valid || approvers.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please define at least one valid approver.' });
            return;
        }

        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        fetch("{{ route('admin.permissions.chain.save') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                permission_type: type,
                approvers: approvers
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Saved', text: data.message, confirmButtonColor: '#940000' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Operation failed' });
            }
        });
    }

    function deleteChainSetup() {
        const type = document.getElementById('chainTypeSelect').value;
        Swal.fire({
            title: 'Are you sure?',
            text: "This will remove all approval steps for " + type + " permissions.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('admin.permissions.chain.delete') }}?type=" + type, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, confirmButtonColor: '#940000' });
                        document.querySelectorAll('.chain-step').forEach(e => e.remove());
                        addChainStep();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Operation failed' });
                    }
                });
            }
        });
    }

    function initApprovalChain() {
        // Fetch roles and positions
        fetch("{{ route('admin.hr.roles_positions') }}", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                availableRoles = data.roles || [];
                availablePositions = data.positions || [];

                // Load chain for current type
                const tabInput = document.querySelector('#permissionFilterForm input[name="tab"]');
                if (tabInput && tabInput.value) {
                    document.getElementById('chainTypeSelect').value = tabInput.value;
                }
                loadChainForType(document.getElementById('chainTypeSelect').value);
            }
        });
    }

    document.addEventListener('submit', function(e) {
        const approveForm = e.target.closest('.permission-approve-form');
        const rejectForm = e.target.closest('.permission-reject-form');
        if (!approveForm && !rejectForm) {
            return;
        }
        e.preventDefault();
        const form = approveForm || rejectForm;
        const formData = new FormData(form);
        const modalBody = form.querySelector('.modal-body');
        const internalLoading = modalBody ? modalBody.querySelector('.modal-loading') : null;

        if (internalLoading) {
            internalLoading.style.display = 'flex';
        } else {
            Swal.fire({ icon: 'info', title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        }

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(async (res) => {
            let data = null;
            try {
                data = await res.json();
            } catch (e) {
                throw new Error(res.status === 419
                    ? 'Session expired. Please refresh the page and try again.'
                    : 'Server returned an invalid response (' + res.status + ').');
            }
            if (!res.ok || !data.success) {
                const msg = (data && (data.message || (data.errors && Object.values(data.errors).flat()[0])))
                    || 'Operation failed.';
                throw new Error(msg);
            }
            return data;
        })
        .then(data => {
            Swal.fire({ icon: 'success', title: 'Success', text: data.message, confirmButtonColor: '#940000' });
            $(form.closest('.modal')).modal('hide');
            submitAdminFilters();
        })
        .catch((err) => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: (err && err.message) ? err.message : 'An unexpected error occurred.',
                confirmButtonColor: '#940000'
            });
        })
        .finally(() => {
            if (internalLoading) internalLoading.style.display = 'none';
        });
    });

    // Student Permission Requesters Modal Functions
    function openStudentRequesterModal() {
        $('#studentRequesterModal').modal('show');
        loadStudentRequesters();
        loadAvailableUsers();
    }

    function loadAvailableUsers() {
        $.ajax({
            url: '{{ route("admin.student_permission_requesters.get_users") }}',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                const optionsDiv = $('#requesterDropdownOptions');
                optionsDiv.empty();

                if (data.success) {
                    if (data.teachers && data.teachers.length > 0) {
                        optionsDiv.append('<div class="dropdown-header font-weight-bold small text-muted">Teachers</div>');
                        data.teachers.forEach(teacher => {
                            optionsDiv.append(`<a class="dropdown-item requester-option" href="#" data-value="teacher_${teacher.teacherID}" data-name="${teacher.first_name} ${teacher.last_name} (Teacher)">${teacher.first_name} ${teacher.last_name} (Teacher)</a>`);
                        });
                    }

                    if (data.staff && data.staff.length > 0) {
                        optionsDiv.append('<div class="dropdown-header font-weight-bold small text-muted mt-2">Staff</div>');
                        data.staff.forEach(staff => {
                            optionsDiv.append(`<a class="dropdown-item requester-option" href="#" data-value="staff_${staff.staffID}" data-name="${staff.first_name} ${staff.last_name} (Staff)">${staff.first_name} ${staff.last_name} (Staff)</a>`);
                        });
                    }

                    // Add click handlers to options
                    $('.requester-option').on('click', function(e) {
                        e.preventDefault();
                        const value = $(this).data('value');
                        const name = $(this).data('name');
                        addRequesterFromDropdown(value, name);
                        $('#requesterDropdownBtn').dropdown('toggle');
                    });
                } else {
                    optionsDiv.append('<div class="dropdown-item text-muted">Failed to load users</div>');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to load users';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#requesterDropdownOptions').html('<div class="dropdown-item text-muted">' + errorMessage + '</div>');
            }
        });
    }

    function filterDropdownOptions() {
        const searchQuery = $('#requesterDropdownSearch').val().toLowerCase();
        $('.requester-option').each(function() {
            const optionText = $(this).text().toLowerCase();
            if (optionText.includes(searchQuery)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function addRequesterFromDropdown(value, name) {
        // Check if already added
        if ($(`#selectedRequesters .badge[data-id="${value}"]`).length) return;

        const badge = `
            <span class="badge px-3 py-2 text-white d-flex align-items-center gap-2" data-id="${value}" style="background: #940000; border-radius: 0; font-size: 11px;">
                ${name}
                <i class="fa fa-times cursor-pointer" onclick="$(this).parent().remove();"></i>
            </span>`;
        $('#selectedRequesters').append(badge);
    }

    function filterRequesterOptions() {
        // This function is no longer needed, using filterDropdownOptions instead
    }

    function loadStudentRequesters() {
        $.ajax({
            url: '{{ route("admin.student_permission_requesters.index") }}',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                const tbody = $('#assignedRequestersTable tbody');
                tbody.empty();

                if (data.requesters && data.requesters.length > 0) {
                    data.requesters.forEach(requester => {
                        tbody.append(`
                            <tr>
                                <td>${requester.name}</td>
                                <td>${requester.type}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeStudentRequester(${requester.id})">
                                        <i class="fa fa-trash"></i> Remove
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="3" class="text-center">No assigned requesters</td></tr>');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load requesters.', confirmButtonColor: '#940000' });
            }
        });
    }

    function saveStudentRequesters() {
        const selectedUsers = [];
        $('#selectedRequesters .badge').each(function() {
            const value = $(this).data('id');
            const parts = value.split('_');
            const userId = parseInt(parts[1]);
            const userType = parts[0];

            console.log('Badge value:', value, 'Parts:', parts, 'User ID:', userId, 'User Type:', userType);

            selectedUsers.push({ type: userType, user_id: userId });
        });

        console.log('Final selected users array:', selectedUsers);

        if (selectedUsers.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Warning', text: 'Please select at least one user.', confirmButtonColor: '#940000' });
            return;
        }

        $.ajax({
            url: '{{ route("admin.student_permission_requesters.store") }}',
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                users: selectedUsers
            },
            success: function(data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Success', text: data.message, confirmButtonColor: '#940000' });
                    loadStudentRequesters();
                    $('#selectedRequesters').empty();
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Operation failed.', confirmButtonColor: '#940000' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save requesters.', confirmButtonColor: '#940000' });
            }
        });
    }

    function removeStudentRequester(id) {
        Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: 'This will remove the requester. Do you want to continue?',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.student_permission_requesters.destroy", ':id') }}'.replace(':id', id),
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Success', text: data.message, confirmButtonColor: '#940000' });
                            loadStudentRequesters();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Operation failed.', confirmButtonColor: '#940000' });
                        }
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to remove requester.', confirmButtonColor: '#940000' });
                    }
                });
            }
        });
    }
</script>

<!-- Student Permission Requesters Modal -->
<div class="modal fade" id="studentRequesterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Manage Student Permission Requesters</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="font-weight-bold small">Assign Teachers/Staff to Request Student Permissions</label>
                        <div class="teacher-list d-flex flex-wrap gap-1 mb-2 p-2 border bg-light" id="selectedRequesters" style="min-height: 40px; border-style: dashed !important; background-color: #fcfcfc;">
                            <!-- Selected requesters badges go here -->
                        </div>
                        <div class="dropdown" style="position: relative;">
                            <button class="btn btn-outline-secondary form-control text-left d-flex justify-content-between align-items-center" type="button" id="requesterDropdownBtn" data-toggle="dropdown" style="border-radius: 0;">
                                <span id="requesterDropdownText">Choose Teacher/Staff...</span>
                                <i class="fa fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu w-100 p-2" style="max-height: 300px; overflow-y: auto; border-radius: 0;">
                                <input type="text" class="form-control mb-2" id="requesterDropdownSearch" placeholder="Search teachers/staff..." onkeyup="filterDropdownOptions()">
                                <div id="requesterDropdownOptions">
                                    <!-- Options will be loaded via AJAX -->
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">Click to select, search to filter teachers/staff</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="font-weight-bold small">Current Assigned Requesters</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="assignedRequestersTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Assigned requesters will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveStudentRequesters()">Save Assignments</button>
            </div>
        </div>
    </div>
</div>
