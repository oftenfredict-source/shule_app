@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .bg-primary-custom { background-color: #940000 !important; }
    .text-primary-custom { color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #ffffff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #ffffff; }
    
    .approval-wrapper {
        font-family: "Century Gothic", "Segoe UI", Tahoma, sans-serif;
        padding: 20px;
    }
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: none;
    }
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        color: #940000;
        font-weight: 600;
    }
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .badge-pending { background-color: #fff3cd; color: #856404; }
    
    /* Timeline styles for approval chain */
    .timeline {
        position: relative;
        padding: 20px 0;
        list-style: none;
    }
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 40px;
        width: 2px;
        background: #dee2e6;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-badge {
        position: absolute;
        top: 0;
        left: 30px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #dee2e6;
        z-index: 100;
    }
    .timeline-badge.approved { border-color: #28a745; background: #28a745; }
    .timeline-badge.pending { border-color: #ffc107; background: #ffc107; }
    .timeline-badge.rejected { border-color: #dc3545; background: #dc3545; }
    .timeline-badge.current { border-color: #940000; background: #fff; box-shadow: 0 0 0 4px rgba(148, 0, 0, 0.1); }
    
    .timeline-panel {
        margin-left: 70px;
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
    }
</style>

<div class="approval-wrapper">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-primary-custom mb-0"><i class="fa fa-check-circle"></i> Exam Paper Approval</h3>
                <p class="text-muted mt-1">Review and approve exam papers in the approval chain.</p>
            </div>
            @if($user_type == 'Admin')
            <div>
                <span class="badge badge-info p-2"><i class="fa fa-shield"></i> Initial Admin Approval Required for Printing Unit</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body bg-light">
            <form action="{{ route('admin.exam_paper_approval') }}" method="GET" class="row align-items-end" id="filterForm">
                <div class="col-md-2">
                    <label class="small font-weight-bold">Year</label>
                    <select name="year" class="form-control">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $yearFilter == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold">Term</label>
                    <select name="term" class="form-control">
                        <option value="">All Terms</option>
                        <option value="first_term" {{ $termFilter == 'first_term' ? 'selected' : '' }}>First Term</option>
                        <option value="second_term" {{ $termFilter == 'second_term' ? 'selected' : '' }}>Second Term</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold">Exam</label>
                    <select name="examID" class="form-control">
                        <option value="">All Examinations</option>
                        @foreach($filter_examinations as $exam)
                            <option value="{{ $exam->examID }}" {{ $examID == $exam->examID ? 'selected' : '' }}>
                                {{ $exam->exam_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold">Class</label>
                    <select name="classID" class="form-control">
                        <option value="">All Classes</option>
                        @foreach($filter_classes as $class)
                            <option value="{{ $class->classID }}" {{ $classID == $class->classID ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold">Subclass</label>
                    <select name="subclassID" class="form-control">
                        <option value="">All Subclasses</option>
                        @foreach($filter_subclasses as $sub)
                            <option value="{{ $sub->subclassID }}" {{ $subclassID == $sub->subclassID ? 'selected' : '' }}>
                                {{ $sub->subclass_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2" id="week_filter_container" style="{{ (isset($selected_exam) && in_array(strtolower(trim($selected_exam->exam_name)), ['weekly test', 'monthly test'])) ? '' : 'display: none;' }}">
                    <label class="small font-weight-bold">Week</label>
                    <input type="hidden" name="week" id="filter_week_value" value="{{ $weekFilter ?? '' }}">
                    <div class="d-flex justify-content-between align-items-center bg-white rounded border p-1" style="height: 38px;">
                        <button type="button" class="btn btn-sm btn-light text-primary-custom font-weight-bold" id="btn_prev_week">
                            <i class="fa fa-chevron-left"></i>
                        </button>
                        <span class="font-weight-bold small text-center flex-grow-1 text-truncate px-1" id="display_week_label">
                            {{ $weekFilter ?: 'Select Week' }}
                        </span>
                        <button type="button" class="btn btn-sm btn-light text-primary-custom font-weight-bold" id="btn_next_week">
                            <i class="fa fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold">Subject</label>
                    <select name="subjectID" class="form-control">
                        <option value="">All Subjects</option>
                        @foreach($filter_subjects as $sub)
                            <option value="{{ $sub->subjectID }}" {{ $subjectID == $sub->subjectID ? 'selected' : '' }}>
                                {{ $sub->subject_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mt-3 text-right">
                    <button type="submit" class="btn btn-primary-custom px-4" id="btnFilterApply">
                        <i class="fa fa-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.exam_paper_approval') }}" class="btn btn-secondary px-4">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3 border-bottom-0" id="approvalTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold border-top-0 border-left-0 border-right-0" id="pending-tab" data-toggle="tab" href="#pending" role="tab" aria-selected="true" style="border-bottom: 3px solid transparent;">
                <i class="fa fa-clock-o"></i> Pending Approvals
                <span class="badge badge-danger ml-1 rounded-circle">{{ count($pendingLogs) }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold border-top-0 border-left-0 border-right-0" id="history-tab" data-toggle="tab" href="#history" role="tab" aria-selected="false" style="border-bottom: 3px solid transparent;">
                <i class="fa fa-history"></i> Action History
                <span class="badge badge-secondary ml-1 rounded-circle">{{ count($historyLogs) }}</span>
            </a>
        </li>
    </ul>

    <style>
        .nav-tabs .nav-link.active {
            border-bottom-color: #940000 !important;
            color: #940000 !important;
            background: transparent;
        }
        .nav-tabs .nav-link {
            color: #6c757d;
        }
        .nav-tabs .nav-link:hover {
            border-bottom-color: #ccc;
        }
    </style>

    <div class="tab-content" id="approvalTabsContent">
        <!-- PENDING TAB -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
            <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="pendingApprovalsTable">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th>Exam Details</th>
                            <th>Subject & Class</th>
                            <th>Teacher</th>
                            <th>Approval Step</th>
                            <th>Date Uploaded</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLogs as $index => $log)
                            <tr id="row-{{ $log->exam_paperID }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $log->examPaper->examination->exam_name ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $log->examPaper->examination->year }} - {{ $log->examPaper->examination->term }}</div>
                                </td>
                                <td>
                                    <div class="text-primary-custom">{{ $log->examPaper->classSubject->subject->subject_name ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ ($log->examPaper->classSubject->class->class_name ?? '') }} {{ ($log->examPaper->classSubject->subclass->subclass_name ?? 'N/A') }}</div>
                                </td>
                                <td>
                                    {{ $log->examPaper->teacher->first_name ?? '' }} {{ $log->examPaper->teacher->last_name ?? '' }}
                                </td>
                                <td>
                                    <span class="badge {{ $log->special_role_type == 'admin' ? 'badge-dark' : 'bg-primary-custom' }} text-white p-2">
                                        Step {{ $log->approval_order }}: 
                                        @if($log->special_role_type)
                                            {{ ucwords(str_replace('_', ' ', $log->special_role_type)) }}
                                        @else
                                            {{ $log->role->name ?? 'Unknown Role' }}
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $log->examPaper->created_at->format('d M Y H:i') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-info btn-preview-paper mr-1" 
                                                data-paper-id="{{ $log->exam_paperID }}"
                                                title="Preview Paper Content">
                                            <i class="fa fa-eye"></i> View
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                More
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="{{ route('download_exam_paper', $log->exam_paperID) }}">
                                                    <i class="fa fa-download"></i> Download Paper
                                                </a>
                                                <a class="dropdown-item btn-view-chain" href="javascript:void(0)" 
                                                   data-paper-id="{{ $log->exam_paperID }}"
                                                   data-exam-id="{{ $log->examPaper->examID }}">
                                                    <i class="fa fa-list-ol"></i> View Chain
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-success btn-approve-reject" href="javascript:void(0)" 
                                                   data-action="approve" 
                                                   data-paper-id="{{ $log->exam_paperID }}"
                                                   data-log-id="{{ $log->paper_approval_logID }}">
                                                    <i class="fa fa-check"></i> Approve
                                                </a>
                                                <a class="dropdown-item text-danger btn-approve-reject" href="javascript:void(0)" 
                                                   data-action="reject" 
                                                   data-paper-id="{{ $log->exam_paperID }}"
                                                   data-log-id="{{ $log->paper_approval_logID }}">
                                                    <i class="fa fa-times"></i> Reject
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- Expandable Preview Row -->
                            <tr id="preview-row-{{ $log->exam_paperID }}" class="bg-light preview-row" style="display: none;">
                                <td colspan="7">
                                    <div class="p-3 border rounded bg-white shadow-sm mx-3 my-2">
                                        <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                                            <h5 class="text-primary-custom mb-0"><i class="fa fa-file-text-o"></i> Exam Paper Preview</h5>
                                            <button type="button" class="close btn-close-preview" data-id="{{ $log->exam_paperID }}">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div id="preview-content-{{ $log->exam_paperID }}">
                                            <div class="text-center py-4">
                                                <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                                                <p>Loading paper content...</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fa fa-info-circle fa-3x text-muted mb-3"></i>
                                    <h5>No pending approvals found.</h5>
                                    <p class="text-muted">You have caught up with all your approval tasks!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
        
    <!-- HISTORY TAB -->
    <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="historyApprovalsTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Exam Details</th>
                                <th>Subject & Class</th>
                                <th>Teacher</th>
                                <th>Approval Status</th>
                                <th>Comment / Reason</th>
                                <th>Date Acted</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historyLogs as $index => $log)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="font-weight-bold">{{ $log->examPaper->examination->exam_name ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $log->examPaper->examination->year }} - {{ $log->examPaper->examination->term }}</div>
                                    </td>
                                    <td>
                                        <div class="text-primary-custom">{{ $log->examPaper->classSubject->subject->subject_name ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ ($log->examPaper->classSubject->class->class_name ?? '') }} {{ ($log->examPaper->classSubject->subclass->subclass_name ?? 'N/A') }}</div>
                                    </td>
                                    <td>
                                        {{ $log->examPaper->teacher->first_name ?? '' }} {{ $log->examPaper->teacher->last_name ?? '' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $log->status == 'approved' ? 'badge-success' : 'badge-danger' }} p-2">
                                            {{ ucfirst($log->status) }} at Step {{ $log->approval_order }}
                                        </span>
                                        <div class="small text-muted mt-1">
                                            @if($log->special_role_type)
                                                ({{ ucwords(str_replace('_', ' ', $log->special_role_type)) }})
                                            @else
                                                ({{ $log->role->name ?? 'Unknown Role' }})
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($log->comment)
                                            <span class="small font-italic">"{{ $log->comment }}"</span>
                                        @else
                                            <span class="text-muted small">No comment</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->updated_at->format('d M Y H:i') }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-sm btn-info btn-preview-paper mr-1" 
                                                    data-paper-id="{{ $log->exam_paperID }}"
                                                    data-is-history="true"
                                                    title="Preview Paper Content">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                            <a class="btn btn-sm btn-outline-secondary btn-view-chain" href="javascript:void(0)" 
                                               data-paper-id="{{ $log->exam_paperID }}"
                                               data-exam-id="{{ $log->examPaper->examID }}"
                                               title="View Chain">
                                                <i class="fa fa-list-ol"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Expandable Preview Row for History -->
                                <tr id="history-preview-row-{{ $log->exam_paperID }}" class="bg-light preview-row" style="display: none;">
                                    <td colspan="8">
                                        <div class="p-3 border rounded bg-white shadow-sm mx-3 my-2">
                                            <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                                                <h5 class="text-primary-custom mb-0"><i class="fa fa-file-text-o"></i> Exam Paper Preview</h5>
                                                <button type="button" class="close btn-close-preview" data-id="history-{{ $log->exam_paperID }}">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div id="history-preview-content-{{ $log->exam_paperID }}">
                                                <div class="text-center py-4">
                                                    <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                                                    <p>Loading paper content...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fa fa-history fa-3x text-muted mb-3"></i>
                                        <h5>No history found.</h5>
                                        <p class="text-muted">You haven't approved or rejected any papers matching these filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- END TAB CONTENT -->
    </div>
</div>

<!-- Modal for Approve/Reject -->
<div class="modal fade" id="approvalActionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="approvalModalTitle">Approve Exam Paper</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="approvalActionForm">
                @csrf
                <input type="hidden" id="action_paper_id" name="exam_paper_id">
                <input type="hidden" id="action_log_id" name="paper_approval_log_id">
                <input type="hidden" id="action_type" name="action">
                
                <div class="modal-body">
                    <div id="rejection_reason_group" style="display: none;">
                        <label class="font-weight-bold">Rejection Reason (Optional)</label>
                        <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="4" placeholder="Please explain why the paper is rejected..."></textarea>
                    </div>
                    <div id="approval_comment_group">
                        <label class="font-weight-bold">Comments (Optional)</label>
                        <textarea class="form-control" name="approval_comment" id="approval_comment" rows="4" placeholder="Any feedback for the teacher?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="submitApprovalBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for View Chain -->
<div class="modal fade" id="viewChainModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Approval Progress Chain</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="chainTimelineContent">
                    <div class="text-center py-4">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                        <p>Loading chain details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Handle Approve/Reject button clicks using event delegation
        $(document).on('click', '.btn-approve-reject', function() {
            const paperId = $(this).data('paper-id');
            const logId = $(this).data('log-id');
            const action = $(this).data('action');
            
            $('#action_paper_id').val(paperId);
            $('#action_log_id').val(logId);
            $('#action_type').val(action);
            
            if (action === 'approve') {
                $('#approvalModalTitle').text('Approve Exam Paper');
                $('#rejection_reason_group').hide();
                $('#approval_comment_group').show();
                $('#submitApprovalBtn').text('Confirm Approval').removeClass('btn-danger').addClass('btn-primary-custom');
            } else {
                $('#approvalModalTitle').text('Reject Exam Paper');
                $('#rejection_reason_group').show();
                $('#approval_comment_group').hide();
                $('#submitApprovalBtn').text('Confirm Rejection').removeClass('btn-primary-custom').addClass('btn-danger');
            }
            
            $('#approvalActionModal').modal('show');
        });
        
        // Handle Form Submission
        $('#approvalActionForm').on('submit', function(e) {
            e.preventDefault();
            const paperId = $('#action_paper_id').val();
            const action = $('#action_type').val();
            
            $('#submitApprovalBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            
            $.ajax({
                url: "{{ url('approve_reject_exam_paper') }}/" + paperId,
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#approvalActionModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    $('#submitApprovalBtn').prop('disabled', false).text('Confirm');
                    let errorMsg = 'An error occurred while processing your request.';
                    if (xhr.status === 422) {
                        errorMsg = 'Please fill out all required fields.';
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: errorMsg
                    });
                }
            });
        });
        
        // Handle Preview Paper content using event delegation
        $(document).on('click', '.btn-preview-paper', function() {
            const paperId = $(this).data('paper-id');
            const isHistory = $(this).data('is-history');
            const prefix = isHistory ? 'history-' : '';
            
            const previewRow = $('#' + prefix + 'preview-row-' + paperId);
            const contentDiv = $('#' + prefix + 'preview-content-' + paperId);
            
            if (previewRow.is(':visible')) {
                previewRow.hide();
                return;
            }
            
            $('.preview-row').hide(); // Hide any other open previews
            previewRow.show();
            
            // Fetch content if not already loaded or reload
            contentDiv.html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i><p>Loading paper content...</p></div>');
            
            $.ajax({
                url: "{{ url('get_admin_exam_paper_review') }}/" + paperId,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const paper = response.paper;
                        let html = `
                            <div class="row">
                                <div class="col-md-4 border-right">
                                    <h6 class="font-weight-bold text-primary-custom">General Info</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><td width="40%" class="text-muted">Teacher:</td><td>${paper.teacher_name}</td></tr>
                                        <tr><td class="text-muted">Subject:</td><td>${paper.subject}</td></tr>
                                        <tr><td class="text-muted">Class:</td><td>${paper.class}</td></tr>
                                    </table>
                                    <div class="mt-3">
                                        <h6 class="font-weight-bold text-primary-custom">Description</h6>
                                        <p class="small">${paper.description || 'No description provided'}</p>
                                    </div>
                                    ${paper.file_path ? `
                                        <div class="mt-3">
                                            <a href="${paper.file_path}" target="_blank" class="btn btn-sm btn-outline-primary btn-block">
                                                <i class="fa fa-file-pdf-o"></i> View Uploaded File
                                            </a>
                                        </div>
                                    ` : '<div class="alert alert-secondary py-1 small">No file uploaded.</div>'}
                                </div>
                                <div class="col-md-8">
                                    <h6 class="font-weight-bold text-primary-custom">Questions List</h6>
                                    ${paper.questions.length > 0 ? `
                                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th width="10%">No.</th>
                                                        <th>Question Description</th>
                                                        <th width="15%">Marks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${paper.questions.map(q => `
                                                        <tr>
                                                            <td>${q.number}</td>
                                                            <td class="small">${q.description}</td>
                                                            <td>${q.marks}</td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    ` : '<div class="alert alert-warning">No questions listed for this paper.</div>'}
                                </div>
                            </div>
                            <div class="mt-3 d-flex justify-content-end border-top pt-2">
                                <button class="btn btn-sm btn-success mr-2 btn-approve-direct" data-paper-id="${paper.exam_paperID}">
                                    <i class="fa fa-check"></i> Approve This Paper
                                </button>
                                <button class="btn btn-sm btn-danger btn-reject-direct" data-paper-id="${paper.exam_paperID}">
                                    <i class="fa fa-times"></i> Reject
                                </button>
                            </div>
                        `;
                        contentDiv.html(html);
                        
                        // Re-bind actions inside the preview
                        $('.btn-approve-direct').on('click', function() {
                            const pId = $(this).data('paper-id');
                            $(`#row-${pId} .btn-approve-reject[data-action="approve"]`).click();
                        });
                        $('.btn-reject-direct').on('click', function() {
                            const pId = $(this).data('paper-id');
                            $(`#row-${pId} .btn-approve-reject[data-action="reject"]`).click();
                        });
                    } else {
                        contentDiv.html('<div class="alert alert-danger">Failed to load paper details.</div>');
                    }
                },
                error: function() {
                    contentDiv.html('<div class="alert alert-danger">An error occurred while fetching paper details.</div>');
                }
            });
        });
        
        // Handle close preview
        $(document).on('click', '.btn-close-preview', function() {
            const id = $(this).data('id');
            const isStr = typeof id === 'string' && id.startsWith('history-');
            if (isStr) {
                $('#' + id.replace('history-', 'history-preview-row-')).hide();
            } else {
                $('#preview-row-' + id).hide();
            }
        });
        
        // Initial week data if available
        let availableWeeks = @json($available_weeks ?? []);
        let currentWeekValue = "{{ $weekFilter ?? '' }}";
        
        // Handle Exam Change
        $('select[name="examID"]').on('change', function() {
            const examID = $(this).val();
            const year = $('select[name="year"]').val();
            const term = $('select[name="term"]').val();
            
            if (!examID) {
                $('#week_filter_container').hide();
                $('#filter_week_value').val('');
                return;
            }

            $.ajax({
                url: "{{ url('get_exam_available_weeks') }}/" + examID,
                method: 'GET',
                success: function(response) {
                    console.log("Week Data Response:", response);
                    if (response.success) {
                        availableWeeks = response.available_weeks;
                        const category = (response.category || "").toLowerCase().trim();
                        if (category === 'weekly test' || category === 'monthly test') {
                            $('#week_filter_container').show();
                            // If no week selected, default to current week in label
                            if (!$('#filter_week_value').val()) {
                                currentWeekValue = "Week " + getISOWeek(new Date());
                                updateWeekDisplay(currentWeekValue);
                            }
                        } else {
                            $('#week_filter_container').hide();
                            $('#filter_week_value').val('');
                            availableWeeks = [];
                        }
                    }
                },
                error: function(err) {
                    console.error("Failed to fetch weeks:", err);
                }
            });
            
            // For now, let's also check if we can get more info about the exam
            // Or just always show it if the category matches (handled by backend on page load)
        });

        // Week Navigation Logic
        function getISOWeek(date) {
            const tempDate = new Date(date.getTime());
            tempDate.setHours(0, 0, 0, 0);
            tempDate.setDate(tempDate.getDate() + 3 - (tempDate.getDay() + 6) % 7);
            const week1 = new Date(tempDate.getFullYear(), 0, 4);
            return 1 + Math.round(((tempDate.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
        }

        function updateWeekDisplay(val) {
            $('#filter_week_value').val(val);
            $('#display_week_label').text(val || 'Select Week');
        }

        $('#btn_prev_week').on('click', function() {
            if (availableWeeks.length === 0) return;
            let currentIndex = availableWeeks.indexOf(currentWeekValue);
            if (currentIndex > 0) {
                currentWeekValue = availableWeeks[currentIndex - 1];
                updateWeekDisplay(currentWeekValue);
            }
        });

        $('#btn_next_week').on('click', function() {
            if (availableWeeks.length === 0) return;
            let currentIndex = availableWeeks.indexOf(currentWeekValue);
            if (currentIndex < availableWeeks.length - 1) {
                currentWeekValue = availableWeeks[currentIndex + 1];
                updateWeekDisplay(currentWeekValue);
            }
            else if (currentIndex === -1) {
                // Not in list? Default to first
                currentWeekValue = availableWeeks[0];
                updateWeekDisplay(currentWeekValue);
            }
        });

        // Dynamic Filter Logic
        $('select[name="year"], select[name="term"]').on('change', function() {
            const year = $('select[name="year"]').val();
            const term = $('select[name="term"]').val();
            
            $.ajax({
                url: "{{ route('get_examinations_for_filter') }}",
                method: 'GET',
                data: { year, term },
                success: function(response) {
                    if (response.success) {
                        let html = '<option value="">All Examinations</option>';
                        response.examinations.forEach(function(exam) {
                            html += `<option value="${exam.examID}">${exam.exam_name}</option>`;
                        });
                        $('select[name="examID"]').html(html).trigger('change');
                    }
                }
            });
        });

        $('select[name="classID"]').on('change', function() {
            const classID = $(this).val();
            
            $.ajax({
                url: "{{ route('get_subclasses_for_exam') }}",
                method: 'GET',
                data: { classID },
                success: function(response) {
                    if (response.success) {
                        let html = '<option value="">All Subclasses</option>';
                        response.subclasses.forEach(function(sub) {
                            html += `<option value="${sub.subclassID}">${sub.subclass_name}</option>`;
                        });
                        $('select[name="subclassID"]').html(html);
                        // Reset subject as well
                        $('select[name="subjectID"]').html('<option value="">All Subjects</option>');
                    }
                }
            });
        });

        $('select[name="subclassID"]').on('change', function() {
            const subclassID = $(this).val();
            if (!subclassID) {
                // Should we reset subjects here?
                // Actually, if subclass is cleared, we might want to show all school subjects or keep it empty.
                // The backend handles all subjects if subclassID is null.
                return;
            }
            
            $.ajax({
                url: "{{ route('get_class_subjects_by_subclass_post') }}",
                method: 'POST',
                data: { 
                    subclass_ids: [subclassID],
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        let html = '<option value="">All Subjects</option>';
                        const addedSubjects = new Set();
                        response.class_subjects.forEach(function(cs) {
                            if (cs.subjectID && !addedSubjects.has(cs.subjectID)) {
                                html += `<option value="${cs.subjectID}">${cs.subject_name}</option>`;
                                addedSubjects.add(cs.subjectID);
                            }
                        });
                        $('select[name="subjectID"]').html(html);
                    }
                }
            });
        });

        // Handle View Chain using event delegation
        $(document).on('click', '.btn-view-chain', function() {
            const paperId = $(this).data('paper-id');
            const examId = $(this).data('exam-id');
            
            $('#viewChainModal').modal('show');
            $('#chainTimelineContent').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i><p>Loading chain details...</p></div>');
            
            $.ajax({
                url: "{{ url('view_paper_approval_chain') }}/" + examId,
                method: 'GET',
                data: { paper_id: paperId },
                success: function(response) {
                    let html = '<ul class="timeline">';
                    
                    // Approval Chain Config from Examination
                    const chain = response.chain;
                    const logs = response.logs || [];
                    const currentOrder = response.current_order;
                    
                    chain.forEach(function(step) {
                        let statusClass = 'pending';
                        let statusText = 'Pending';
                        let comment = '';
                        let approver = '';
                        let date = '';
                        
                        // Check if we have a log entry for this step
                        const log = logs.find(l => l.approval_order === step.approval_order);
                        
                        if (log) {
                            if (log.status === 'approved') {
                                statusClass = 'approved';
                                statusText = 'Approved';
                                approver = log.approver ? log.approver.first_name + ' ' + log.approver.last_name : 'N/A';
                                date = new Date(log.updated_at).toLocaleString();
                                comment = log.comment ? '<div class="small mt-2 p-2 bg-light border-left border-success">" ' + log.comment + ' "</div>' : '';
                            } else if (log.status === 'rejected') {
                                statusClass = 'rejected';
                                statusText = 'Rejected';
                                approver = log.approver ? log.approver.first_name + ' ' + log.approver.last_name : 'N/A';
                                date = new Date(log.updated_at).toLocaleString();
                                comment = log.comment ? '<div class="small mt-2 p-2 bg-light border-left border-danger">" ' + log.comment + ' "</div>' : '';
                            } else if (log.status === 'pending') {
                                statusClass = 'current';
                                statusText = 'Awaiting Approval';
                            }
                        }
                        
                        let roleName = step.special_role_type ? 
                            step.special_role_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 
                            (step.role ? step.role.name : 'Unknown Role');

                        let contentHtml = '';
                        if (approver || date || comment) {
                            contentHtml = `
                                <div class="small text-muted">
                                    ${approver ? 'By: ' + approver + (date ? ' | ' + date : '') : 'Progressing...'}
                                </div>
                                ${comment}
                            `;
                        } else {
                            contentHtml = `<div class="small text-muted">Progressing...</div>`;
                        }

                        html += `
                            <li class="timeline-item">
                                <div class="timeline-badge ${statusClass}"></div>
                                <div class="timeline-panel">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1 text-primary-custom font-weight-bold">Step ${step.approval_order}: ${roleName}</h6>
                                        <span class="badge ${statusClass === 'approved' ? 'badge-success' : (statusClass === 'rejected' ? 'badge-danger' : 'badge-warning')} text-capitalize">${statusText}</span>
                                    </div>
                                    ${contentHtml}
                                </div>
                            </li>
                        `;
                    });
                    
                    html += '</ul>';
                    $('#chainTimelineContent').html(html);
                },
                error: function() {
                    $('#chainTimelineContent').html('<div class="alert alert-danger">Failed to load chain details.</div>');
                }
            });
        });

        // Handle Form Submission via AJAX (Filtering)
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = $('#btnFilterApply');
            const origHtml = btn.html();
            
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Filtering...');
            
            const tbodyPending = $('#pendingApprovalsTable tbody');
            const tbodyHistory = $('#historyApprovalsTable tbody');
            const pendingBadge = $('#pending-tab .badge');
            const historyBadge = $('#history-tab .badge');

            tbodyPending.html('<tr><td colspan="7" class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i></td></tr>');
            tbodyHistory.html('<tr><td colspan="8" class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i></td></tr>');
            
            $.ajax({
                url: form.attr('action'),
                method: 'GET',
                data: form.serialize(),
                success: function(response) {
                    const parsedResponse = $(response);
                    // Extract just the tbody content from the returned HTML
                    const newTbodyPending = parsedResponse.find('#pendingApprovalsTable tbody').html();
                    const newTbodyHistory = parsedResponse.find('#historyApprovalsTable tbody').html();
                    
                    const newPendingCount = parsedResponse.find('#pending-tab .badge').text();
                    const newHistoryCount = parsedResponse.find('#history-tab .badge').text();

                    tbodyPending.html(newTbodyPending);
                    tbodyHistory.html(newTbodyHistory);
                    
                    pendingBadge.text(newPendingCount);
                    historyBadge.text(newHistoryCount);

                    btn.prop('disabled', false).html(origHtml);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(origHtml);
                    tbodyPending.html('<tr><td colspan="7" class="text-center text-danger py-4">Error loading filtered data.</td></tr>');
                    tbodyHistory.html('<tr><td colspan="8" class="text-center text-danger py-4">Error loading filtered data.</td></tr>');

                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'An error occurred while filtering. Please try again.'
                    });
                }
            });
        });

    });
</script>
