@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .form-control, .table {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .btn, .form-control { border-radius: 0 !important; }
    .bg-primary-custom { background-color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .table thead th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 700;
        border-bottom: 2px solid #dee2e6 !important;
    }

    /* Timeline Styles */
    .progress-timeline { position: relative; padding-left: 30px; margin-top: 20px; }
    .progress-timeline::before { content: ''; position: absolute; left: 7px; top: 0; bottom: 0; width: 2px; background: #eee; }
    .timeline-item { position: relative; margin-bottom: 25px; }
    .timeline-dot { position: absolute; left: -30px; width: 16px; height: 16px; border-radius: 50%; background: #ddd; border: 3px solid #fff; z-index: 1; }
    .timeline-dot.completed { background: #28a745; }
    .timeline-dot.current { background: #ffc107; box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.2); }
    .timeline-content { padding: 5px 15px; background: #f8f9fa; border-radius: 4px; border-left: 4px solid #ddd; }
    .timeline-item.completed .timeline-content { border-left-color: #28a745; background: #f0fff4; }
    .timeline-item.current .timeline-content { border-left-color: #ffc107; background: #fffbeb; }
    .timeline-role { font-weight: 600; color: #333; display: block; }
    .timeline-status { font-size: 0.8rem; color: #666; }
    .timeline-time { font-size: 0.75rem; color: #999; display: block; margin-top: 2px; }

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
                <h1>Permission Approvals</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Pending Permissions for My Approval</strong>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <form id="filterForm" class="p-3 bg-light border shadow-sm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="font-weight-bold small">Filter By:</label>
                                <select name="filter_type" id="filter_type" class="form-control form-control-sm" onchange="toggleFilterInputs()">
                                    <option value="all" {{ ($filterType ?? '') == 'all' ? 'selected' : '' }}>All Pending</option>
                                    <option value="range" {{ ($filterType ?? '') == 'range' ? 'selected' : '' }}>Date Range</option>
                                    <option value="month" {{ ($filterType ?? '') == 'month' ? 'selected' : '' }}>Specific Month</option>
                                    <option value="year" {{ ($filterType ?? '') == 'year' ? 'selected' : '' }}>Specific Year</option>
                                </select>
                            </div>

                            <div id="range_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'range' ? 'flex' : 'none' }}; gap: 10px;">
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">From:</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ $dateFrom ?? '' }}">
                                </div>
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">To:</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ $dateTo ?? '' }}">
                                </div>
                            </div>

                            <div id="month_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'month' ? 'flex' : 'none' }}; gap: 10px;">
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

                            <div id="year_inputs" class="col-md-3 filter-input-group" style="display: {{ ($filterType ?? '') == 'year' ? 'block' : 'none' }};">
                                <label class="font-weight-bold small">Year:</label>
                                <select name="filter_year" class="form-control form-control-sm">
                                    @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                        <option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary-custom btn-sm px-4">
                                    <i class="fa fa-filter"></i> Apply
                                </button>
                                <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="form-loading" id="approvalLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Filtering...</span>
                <div class="form-progress"></div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 text-muted">{{ $permissions->count() }} Pending Request(s)</h6>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary view-toggle-btn" data-view="list">
                        <i class="fa fa-list"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary view-toggle-btn" data-view="grid">
                        <i class="fa fa-th-large"></i>
                    </button>
                </div>
            </div>

            <div id="perm-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="bg-primary-custom text-white">
                            <tr>
                                <th>Requester</th>
                                <th>Type</th>
                                <th>Date/Time</th>
                                <th>Reason</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('partials.permission_approval_list', [
                                'permissions' => $permissions,
                                'userType' => $userType,
                                'myRoleIDs' => $myRoleIDs ?? [],
                                'myProfessionID' => $myProfessionID ?? null
                            ])
                        </tbody>
                    </table>
                </div>
            </div>{{-- end list view --}}

            <div id="perm-grid-view" style="display:none;">
                <div class="row">
                    @forelse($permissions as $permission)
                        @php
                            $requesterName = 'N/A';
                            if($permission->requester_type === 'student' && $permission->student) {
                                $requesterName = trim($permission->student->first_name . ' ' . $permission->student->last_name);
                            } elseif($permission->requester_type === 'staff' && $permission->staff) {
                                $requesterName = trim($permission->staff->first_name . ' ' . $permission->staff->last_name);
                            } elseif($permission->teacher) {
                                $requesterName = trim($permission->teacher->first_name . ' ' . $permission->teacher->last_name);
                            }

                            $requesterInfo = ucfirst($permission->requester_type);
                            if($permission->requester_type === 'student' && $permission->student && $permission->student->class) {
                                $requesterInfo .= ' | ' . $permission->student->class->class_name;
                            }
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 shadow-sm border-0" style="border-left: 5px solid #940000 !important; background: #fff;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-0"><strong>{{ $requesterName }}</strong></h6>
                                            <small class="text-muted">{{ $requesterInfo }}</small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="bx bx-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow border">
                                                <a class="dropdown-item text-success" href="#" onclick="openApprovalModal({{ $permission->permissionID }}); return false;">
                                                    <i class="fa fa-check-circle"></i> Approve
                                                </a>
                                                <a class="dropdown-item text-danger" href="#" onclick="openRejectionModal({{ $permission->permissionID }}); return false;">
                                                    <i class="fa fa-times-circle"></i> Reject
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="viewPermissionProgress({{ $permission->permissionID }}); return false;">
                                                    <i class="fa fa-project-diagram"></i> View Progress
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="viewPermissionDetails({{ json_encode([
                                                    'id' => $permission->permissionID,
                                                    'requester_name' => $requesterName,
                                                    'requester_info' => $requesterInfo,
                                                    'time_mode' => ucfirst($permission->time_mode),
                                                    'days_count' => $permission->days_count,
                                                    'start_date' => $permission->start_date ? $permission->start_date->format('j F Y') : null,
                                                    'end_date' => $permission->end_date ? $permission->end_date->format('j F Y') : null,
                                                    'start_time' => $permission->start_time,
                                                    'end_time' => $permission->end_time,
                                                    'reason_type' => ucfirst($permission->reason_type),
                                                    'reason_description' => $permission->reason_description,
                                                    'status' => ucfirst($permission->status),
                                                    'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : 'N/A'
                                                ]) }}); return false;">
                                                    <i class="fa fa-list-alt"></i> View More
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.permissions.download', $permission->permissionID) }}">
                                                    <i class="fa fa-file-pdf"></i> Download PDF
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Period:</small>
                                        @if($permission->time_mode === 'days')
                                            <i class="fa fa-calendar-alt text-primary-custom"></i> {{ $permission->start_date->format('j F Y') }} - {{ $permission->end_date->format('j F Y') }}
                                            <span class="badge badge-light border ml-1">{{ $permission->days_count }} days</span>
                                        @else
                                            <i class="fa fa-clock text-primary-custom"></i> {{ $permission->start_date->format('j F Y') }} ({{ $permission->start_time }} - {{ $permission->end_time }})
                                        @endif
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Reason ({{ $permission->reason_type }}):</small>
                                        <p class="mb-0 text-muted" style="font-size: 0.85rem;">{{ Str::limit($permission->reason_description, 80) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4">No pending permissions.</div>
                    @endforelse
                </div>
            </div>{{-- end grid view --}}
        </div>
    </div>
</div>
    </div>
</div>

{{-- Approval Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Approve Permission</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="approveForm">
                @csrf
                <input type="hidden" name="permissionID" id="approvePermissionID">
                <div class="modal-body position-relative">
                    <div class="modal-loading">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                        <p class="mt-2">Processing...</p>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea class="form-control" name="admin_response" rows="3" required placeholder="Enter approval remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Rejection Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Reject Permission</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm">
                @csrf
                <input type="hidden" name="permissionID" id="rejectPermissionID">
                <div class="modal-body position-relative">
                    <div class="modal-loading">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                        <p class="mt-2">Processing...</p>
                    </div>
                    <div class="form-group">
                        <label>Reason for Rejection</label>
                        <textarea class="form-control" name="admin_response" rows="3" required placeholder="Enter reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function initViewToggle() {
        var isMobile = window.innerWidth <= 991;
        var storageKey = 'approval_tasks_view_pref';
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

    function resetFilters() {
        window.location.href = "{{ route('admin.hr.permissions.pending_approvals') }}";
    }

    $('#filterForm').on('submit', function(e) {
        const loadingBar = document.getElementById('approvalLoading');
        if (loadingBar) loadingBar.style.display = 'flex';
        // Let it submit naturally as it's a GET request
    });

    initViewToggle();
</script>

@include('partials.permission_details_modal')
@include('partials.permission_progress_modal')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openApprovalModal(id) {
        $('#approvePermissionID').val(id);
        $('#approveModal').modal('show');
    }

    function openRejectionModal(id) {
        $('#rejectPermissionID').val(id);
        $('#rejectModal').modal('show');
    }

    $('#approveForm').on('submit', function(e) {
        e.preventDefault();
        submitAction("{{ route('admin.permissions.approve') }}", $(this));
    });

    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        submitAction("{{ route('admin.permissions.reject') }}", $(this));
    });

    function submitAction(url, $form) {
        const modalBody = $form.find('.modal-body')[0];
        const internalLoading = modalBody ? modalBody.querySelector('.modal-loading') : null;

        if (internalLoading) {
            internalLoading.style.display = 'flex';
        } else {
            const loadingBar = document.getElementById('approvalLoading');
            if (loadingBar) loadingBar.style.display = 'flex';
        }

        const $submitBtn = $form.find('button[type="submit"]');
        $submitBtn.prop('disabled', true);

        $.ajax({
            url: url,
            method: "POST",
            data: $form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonColor: '#940000'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: response.message || 'Operation failed.',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr) {
                let message = 'An unexpected error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonColor: '#940000'
                });
            },
            complete: function() {
                const modalBody = $form.find('.modal-body')[0];
                const internalLoading = modalBody ? modalBody.querySelector('.modal-loading') : null;
                if (internalLoading) {
                    internalLoading.style.display = 'none';
                } else {
                    const loadingBar = document.getElementById('approvalLoading');
                    if (loadingBar) loadingBar.style.display = 'none';
                }
                $submitBtn.prop('disabled', false);
                $('.modal').modal('hide');
            }
        });
    }
</script>
