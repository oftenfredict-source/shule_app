@include('includes.staff_nav')

<style>
    body, .card, .btn, .form-control { border-radius: 0 !important; }
    .bg-primary-custom { background-color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }

    .selection-card {
        background: white;
        padding: 25px;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 800;
        color: #940000;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 20px;
        border-bottom: 2px solid rgba(148,0,0,0.1);
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-control {
        border: 1px solid #ced4da !important;
        padding: 15px !important;
        font-size: 14px !important;
        background-color: #fff !important;
        height: 50px !important;
    }
    .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 4px rgba(148, 0, 0, 0.05) !important;
    }
    textarea.form-control {
        height: auto !important;
        min-height: 100px !important;
    }
    select.form-control {
        height: 50px !important;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }
    .student-search-results {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-top: none;
        position: absolute;
        width: 100%;
        background: white;
        z-index: 1000;
    }
    .student-result-item {
        padding: 12px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .student-result-item:hover {
        background-color: #f8f9fa;
    }
    .student-result-item.selected {
        background-color: #fff5f5;
        border-left: 3px solid #940000;
    }
    .student-name {
        font-weight: 600;
        color: #333;
    }
    .student-details {
        font-size: 12px;
        color: #777;
    }
    .student-photo-thumbnail {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }
    .nav-tabs .nav-link {
        border-radius: 0 !important;
        border: none !important;
        color: #666;
        font-weight: 600;
        padding: 12px 20px;
    }
    .nav-tabs .nav-link.active {
        background-color: #940000 !important;
        color: white !important;
    }
    .nav-tabs .nav-link:hover {
        color: #940000;
    }
    .progress-chain {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 10px 0;
    }
    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
    }
    .progress-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 15px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #e0e0e0;
    }
    .progress-step.completed:not(:last-child)::after {
        background: #28a745;
    }
    .progress-step.current:not(:last-child)::after {
        background: #ffc107;
    }
    .progress-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        color: white;
        background: #e0e0e0;
        z-index: 1;
    }
    .progress-step.completed .progress-circle {
        background: #28a745;
    }
    .progress-step.current .progress-circle {
        background: #ffc107;
    }
    .progress-step.rejected .progress-circle {
        background: #dc3545;
    }
    .progress-label {
        font-size: 10px;
        margin-top: 5px;
        text-align: center;
        color: #666;
    }
    .status-badge {
        padding: 4px 8px;
        border-radius: 0;
        font-size: 11px;
        font-weight: 600;
    }
    .status-pending {
        background: #ffc107;
        color: #333;
    }
    .status-approved {
        background: #28a745;
        color: white;
    }
    .status-rejected {
        background: #dc3545;
        color: white;
    }
</style>

<div class="row" style="margin-left: 0; margin-right: 0;">
    <div class="col-12">
        <div class="section-title"><i class="bx bx-user-plus"></i> Student Permission Requests</div>
    </div>

    <div class="col-12">
        <ul class="nav nav-tabs mb-3" id="permissionTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="new-tab" data-toggle="tab" href="#new" role="tab">New Permission Request</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pending-tab" data-toggle="tab" href="#pending" role="tab">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab">Rejected</a>
            </li>
        </ul>

        <div class="tab-content" id="permissionTabsContent">
            <!-- New Permission Request Tab -->
            <div class="tab-pane fade show active" id="new" role="tabpanel">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="studentPermissionForm" method="POST" action="{{ route('staff.student_permission.store') }}">
                            @csrf

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="font-weight-bold small">Search Student *</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="studentSearch" placeholder="Type student name to search..." autocomplete="off">
                                <input type="hidden" name="student_id" id="selectedStudentId" required>
                                <div id="studentSearchResults" class="student-search-results" style="display: none;"></div>
                            </div>
                            <small class="text-muted">Start typing to search for active students</small>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="font-weight-bold small">Requester Type</label>
                            <input type="text" class="form-control" value="Staff" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="font-weight-bold small">Permission Mode *</label>
                            <select name="time_mode" id="time_mode" class="form-control" required onchange="togglePermissionMode()">
                                <option value="">Select Mode</option>
                                <option value="days">Days</option>
                                <option value="hours">Hours</option>
                            </select>
                        </div>
                    </div>

                    <div class="row" id="dateRangeContainer" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">End Date *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                        </div>
                    </div>

                    <div class="row" id="daysCountContainer" style="display: none;">
                        <div class="col-12 mb-3">
                            <label class="font-weight-bold small">Number of Days *</label>
                            <input type="number" name="days_count" id="days_count" class="form-control" min="1" placeholder="Auto-calculated" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>

                    <div class="row" id="timeRangeContainer" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Date *</label>
                            <input type="date" name="start_date_hours" id="start_date_hours" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold small">Start Time *</label>
                            <input type="time" name="start_time" id="start_time" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold small">End Time *</label>
                            <input type="time" name="end_time" id="end_time" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="font-weight-bold small">Reason Type *</label>
                            <select name="reason_type" class="form-control" required>
                                <option value="">Select Reason</option>
                                <option value="medical">Medical</option>
                                <option value="family">Family</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="font-weight-bold small">Reason Description *</label>
                            <textarea name="reason_description" class="form-control" rows="3" placeholder="Describe the reason for permission..." required></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="font-weight-bold small">Attachment (Optional)</label>
                            <input type="file" name="attachment" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Upload supporting documents (PDF, JPG, PNG)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="bx bx-send"></i> Submit Permission Request
                            </button>
                            <a href="{{ route('staffDashboard') }}" class="btn btn-secondary">
                                <i class="bx bx-x"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
                    </div>
                </div>
            </div>

            <!-- Pending Tab -->
            <div class="tab-pane fade" id="pending" role="tabpanel">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="perm-view-toggle">
                                <button class="btn btn-sm btn-outline-secondary perm-view-btn" data-view="list">
                                    <i class="bx bx-list-ul"></i> List
                                </button>
                                <button class="btn btn-sm btn-outline-secondary perm-view-btn" data-view="grid">
                                    <i class="bx bx-grid"></i> Grid
                                </button>
                            </div>
                        </div>
                        <div id="perm-list-view">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Requested At</th>
                                            <th>Mode</th>
                                            <th>Period</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pendingTableBody">
                                        <tr><td colspan="6" class="text-center">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="perm-grid-view" style="display: none;">
                            <div class="row" id="pendingGridBody">
                                <div class="col-12 text-center">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Tab -->
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="perm-view-toggle">
                                <button class="btn btn-sm btn-outline-secondary perm-view-btn" data-view="list">
                                    <i class="bx bx-list-ul"></i> List
                                </button>
                                <button class="btn btn-sm btn-outline-secondary perm-view-btn" data-view="grid">
                                    <i class="bx bx-grid"></i> Grid
                                </button>
                            </div>
                        </div>
                        <div id="perm-list-view">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Requested At</th>
                                            <th>Mode</th>
                                            <th>Period</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="approvedTableBody">
                                        <tr><td colspan="6" class="text-center">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="perm-grid-view" style="display: none;">
                            <div class="row" id="approvedGridBody">
                                <div class="col-12 text-center">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rejected Tab -->
            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="perm-view-toggle">
                                <button class="btn btn-sm btn-outline-secondary perm-view-btn" data-view="list">
                                    <i class="bx bx-list-ul"></i> List
                                </button>
                                <button class="btn btn-sm btn-outline-secondary perm-view-btn" data-view="grid">
                                    <i class="bx bx-grid"></i> Grid
                                </button>
                            </div>
                        </div>
                        <div id="perm-list-view">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Requested At</th>
                                            <th>Mode</th>
                                            <th>Period</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rejectedTableBody">
                                        <tr><td colspan="6" class="text-center">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="perm-grid-view" style="display: none;">
                            <div class="row" id="rejectedGridBody">
                                <div class="col-12 text-center">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Permission Request Tab -->
            <div class="tab-pane fade" id="new" role="tabpanel">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="studentPermissionForm" method="POST" action="{{ route('staff.student_permission.store') }}">
                            @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Search Student *</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="studentSearch" placeholder="Type student name to search..." autocomplete="off">
                                <input type="hidden" name="student_id" id="selectedStudentId" required>
                                <div id="studentSearchResults" class="student-search-results" style="display: none;"></div>
                            </div>
                            <small class="text-muted">Start typing to search for active students</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Requester Type</label>
                            <input type="text" class="form-control" value="Staff" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Permission Mode *</label>
                            <select name="time_mode" id="time_mode" class="form-control" required onchange="togglePermissionMode()">
                                <option value="">Select Mode</option>
                                <option value="days">Days</option>
                                <option value="hours">Hours</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3" id="daysCountContainer" style="display: none;">
                            <label class="font-weight-bold small">Number of Days *</label>
                            <input type="number" name="days_count" id="days_count" class="form-control" min="1" placeholder="Enter number of days">
                        </div>
                    </div>

                    <div class="row" id="dateRangeContainer" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">End Date *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                        </div>
                    </div>

                    <div class="row" id="timeRangeContainer" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Date *</label>
                            <input type="date" name="start_date_hours" id="start_date_hours" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold small">Start Time *</label>
                            <input type="time" name="start_time" id="start_time" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold small">End Time *</label>
                            <input type="time" name="end_time" id="end_time" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Reason Type *</label>
                            <select name="reason_type" class="form-control" required>
                                <option value="">Select Reason</option>
                                <option value="medical">Medical</option>
                                <option value="family">Family</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold small">Reason Description *</label>
                            <textarea name="reason_description" class="form-control" rows="4" required placeholder="Describe the reason for permission..."></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold small">Attachment (Optional)</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Upload supporting documents (PDF, JPG, PNG)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="bx bx-send"></i> Submit Permission Request
                            </button>
                            <a href="{{ route('staffDashboard') }}" class="btn btn-secondary">
                                <i class="bx bx-x"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.permission_details_modal')

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header bg-primary-custom text-white" style="border-radius: 0;">
                <h5 class="modal-title" id="progressModalLabel"><i class="bx bx-git-branch"></i> Approval Progress</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#progressModal').modal('hide')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="progressModalBody">
                <!-- Progress content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#progressModal').modal('hide')" style="border-radius: 0;">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 20px;
    }
    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #ddd;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px #ddd;
    }
    .timeline-item.completed .timeline-marker {
        background: #28a745;
        box-shadow: 0 0 0 3px #28a745;
    }
    .timeline-item.current .timeline-marker {
        background: #ffc107;
        box-shadow: 0 0 0 3px #ffc107;
    }
    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        border-left: 3px solid #ddd;
    }
    .timeline-item.completed .timeline-content {
        border-left-color: #28a745;
    }
    .timeline-item.current .timeline-content {
        border-left-color: #ffc107;
    }
    .timeline-title {
        margin: 0 0 5px 0;
        font-weight: bold;
    }
    .timeline-status {
        margin: 0;
        font-size: 14px;
    }
</style>

<script>
    let searchTimeout;

    // Load permission requests on page load
    $(document).ready(function() {
        loadPermissionRequests();
        initPermViewToggle();

        // Auto-calculate days when start and end dates change
        $('#start_date, #end_date').on('change', function() {
            calculateDaysExcludingWeekends();
        });
    });

    function calculateDaysExcludingWeekends() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);

            if (end < start) {
                $('#days_count').val('');
                return;
            }

            let count = 0;
            let current = new Date(start);

            while (current <= end) {
                const dayOfWeek = current.getDay();
                // 0 = Sunday, 6 = Saturday
                if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }

            $('#days_count').val(count);
        }
    }

    function loadPermissionRequests() {
        $.ajax({
            url: '{{ route("staff.student_permission.index") }}',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    populateTable('pending', data.pending || []);
                    populateTable('approved', data.approved || []);
                    populateTable('rejected', data.rejected || []);
                } else {
                    const errorMsg = data.message || 'Failed to load requests';
                    $('#pendingTableBody').html('<tr><td colspan="6" class="text-center text-danger">' + errorMsg + '</td></tr>');
                    $('#approvedTableBody').html('<tr><td colspan="6" class="text-center text-danger">' + errorMsg + '</td></tr>');
                    $('#rejectedTableBody').html('<tr><td colspan="6" class="text-center text-danger">' + errorMsg + '</td></tr>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load requests';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMsg = 'Server error: ' + xhr.status;
                }
                $('#pendingTableBody').html('<tr><td colspan="6" class="text-center text-danger">' + errorMsg + '</td></tr>');
                $('#approvedTableBody').html('<tr><td colspan="6" class="text-center text-danger">' + errorMsg + '</td></tr>');
                $('#rejectedTableBody').html('<tr><td colspan="6" class="text-center text-danger">' + errorMsg + '</td></tr>');
            }
        });
    }

    function populateTable(status, requests) {
        const tbody = $(`#${status}TableBody`);
        const gridBody = $(`#${status}GridBody`);
        tbody.empty();
        gridBody.empty();

        if (requests.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center">No requests found</td></tr>');
            gridBody.append('<div class="col-12 text-center">No requests found</div>');
            return;
        }

        requests.forEach(request => {
            const requestedAt = request.created_at ? new Date(request.created_at).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
            const mode = request.time_mode || 'N/A';
            let period = 'N/A';
            if (request.time_mode === 'days') {
                const startDate = request.start_date ? new Date(request.start_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
                const endDate = request.end_date ? new Date(request.end_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
                const days = request.days_count || 0;
                period = `${startDate} - ${endDate} (${days} days)`;
            } else {
                period = `${request.start_time || 'N/A'} - ${request.end_time || 'N/A'}`;
            }
            const reason = request.reason_type || 'N/A';
            const status = request.status || 'pending';
            const statusClass = status === 'approved' ? 'text-success' : (status === 'rejected' ? 'text-danger' : 'text-warning');
            const statusText = status.charAt(0).toUpperCase() + status.slice(1);

            // List view
            tbody.append(`
                <tr>
                    <td>${requestedAt}</td>
                    <td>${mode.charAt(0).toUpperCase() + mode.slice(1)}</td>
                    <td>${period}</td>
                    <td>${reason.charAt(0).toUpperCase() + reason.slice(1)}</td>
                    <td><span class="${statusClass} font-weight-bold">${statusText}</span></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i> Actions
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="viewProgress(${request.id}); return false;">
                                    <i class="bx bx-git-branch"></i> View Progress
                                </a>
                                <a class="dropdown-item" href="#" onclick="viewRequestDetails(${request.id}); return false;">
                                    <i class="bx bx-show"></i> View More
                                </a>
                                ${request.status === 'approved' ? `<a class="dropdown-item" href="/admin/student-permissions/export-pdf/${request.id}" target="_blank"><i class="bx bx-download"></i> Download PDF</a>` : ''}
                            </div>
                        </div>
                    </td>
                </tr>
            `);

            // Grid view
            gridBody.append(`
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title font-weight-bold">${request.student_name || 'N/A'}</h6>
                            <p class="card-text small text-muted mb-2">${request.class_name || 'N/A'}</p>
                            <div class="small mb-1"><strong>Requested:</strong> ${requestedAt}</div>
                            <div class="small mb-1"><strong>Mode:</strong> ${mode.charAt(0).toUpperCase() + mode.slice(1)}</div>
                            <div class="small mb-1"><strong>Period:</strong> ${period}</div>
                            <div class="small mb-1"><strong>Reason:</strong> ${reason.charAt(0).toUpperCase() + reason.slice(1)}</div>
                            <div class="small mb-2"><strong>Status:</strong> <span class="${statusClass} font-weight-bold">${statusText}</span></div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary dropdown-toggle w-100" type="button" data-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i> Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" onclick="viewProgress(${request.id}); return false;">
                                        <i class="bx bx-git-branch"></i> View Progress
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="viewRequestDetails(${request.id}); return false;">
                                        <i class="bx bx-show"></i> View More
                                    </a>
                                    ${request.status === 'approved' ? `<a class="dropdown-item" href="/admin/student-permissions/export-pdf/${request.id}" target="_blank"><i class="bx bx-download"></i> Download PDF</a>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
    }

    function generateProgressChain(request) {
        // This is a placeholder - adjust based on your actual approval chain structure
        const steps = [
            { name: 'Submitted', status: 'completed' },
            { name: 'HOD Review', status: request.hod_status || 'pending' },
            { name: 'Principal', status: request.principal_status || 'pending' },
            { name: 'Final', status: request.final_status || 'pending' }
        ];

        let chainHtml = '<div class="progress-chain">';
        steps.forEach((step, index) => {
            const stepClass = step.status === 'completed' ? 'completed' :
                              step.status === 'rejected' ? 'rejected' :
                              step.status === 'current' ? 'current' : '';
            chainHtml += `
                <div class="progress-step ${stepClass}">
                    <div class="progress-circle">${index + 1}</div>
                    <div class="progress-label">${step.name}</div>
                </div>
            `;
        });
        chainHtml += '</div>';
        return chainHtml;
    }

    function viewRequestDetails(requestId) {
        $.ajax({
            url: `/staff/student-permission/details/${requestId}`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    $('#pd_status').text(data.permission.status);

                    // Handle badge color
                    $('#pd_status').removeClass('badge-success badge-danger badge-warning badge-secondary');
                    if (data.permission.status.toLowerCase() === 'approved') {
                        $('#pd_status').addClass('badge-success').removeClass('badge-warning badge-danger');
                    } else if (data.permission.status.toLowerCase() === 'rejected') {
                        $('#pd_status').addClass('badge-danger').removeClass('badge-warning badge-success');
                    } else {
                        $('#pd_status').addClass('badge-warning').removeClass('badge-success badge-danger');
                    }

                    $('#pd_requested_at').text(data.permission.requested_at);
                    $('#pd_time_mode').text(data.permission.time_mode);
                    $('#pd_reason_type').text(data.permission.reason_type);
                    $('#pd_reason_description').text(data.permission.reason_description);

                    if(data.permission.time_mode.toLowerCase() === 'days') {
                        $('#pd_days_row, #pd_period_row, #pd_return_row').show();
                        $('#pd_time_row').hide();

                        $('#pd_days_count').text(data.permission.days_count);
                        $('#pd_start_date').text(data.permission.start_date);
                        $('#pd_end_date').text(data.permission.end_date);
                        $('#pd_return_date').text(data.permission.return_date);
                    } else {
                        $('#pd_days_row, #pd_period_row, #pd_return_row').hide();
                        $('#pd_time_row').show();

                        $('#pd_start_time').text(data.permission.start_time);
                        $('#pd_end_time').text(data.permission.end_time);
                    }

                    // Handle PDF Download
                    $('#pd_download_pdf_btn').off('click').on('click', function() {
                        if (data.permission.status !== 'approved') {
                            Swal.fire('Info', 'PDF download is only available for approved permissions.', 'info');
                            return;
                        }
                        window.open(`/admin/student-permissions/export-pdf/${requestId}`, '_blank');
                    });

                    $('#permissionDetailsModal').modal('show');
                } else {
                    Swal.fire('Error', data.message || 'Failed to load permission details', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to load permission details', 'error');
            }
        });
    }

    function viewProgress(requestId) {
        $.ajax({
            url: `/staff/student-permission/progress/${requestId}`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    let progressHtml = '<div class="timeline">';
                    data.chain.forEach((step, index) => {
                        const isCompleted = data.permission.current_approval_order > index + 1;
                        const isCurrent = data.permission.current_approval_order == index + 1;
                        const statusClass = isCompleted ? 'completed' : (isCurrent ? 'current' : 'pending');

                        progressHtml += `
                            <div class="timeline-item ${statusClass}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">${step.role_name}</h6>
                                    <p class="timeline-status">${isCompleted ? 'Approved' : (isCurrent ? 'Pending' : 'Waiting')}</p>
                                    ${isCompleted ? `<small class="text-muted">Completed</small>` : ''}
                                </div>
                            </div>
                        `;
                    });
                    progressHtml += '</div>';

                    $('#progressModalBody').html(progressHtml);
                    $('#progressModal').modal('show');
                } else {
                    Swal.fire('Error', data.message || 'Failed to load progress', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to load progress', 'error');
            }
        });
    }

    function initPermViewToggle() {
        var isMobile = window.innerWidth <= 991;
        var storageKey = 'student_perm_view_pref';
        var savedView = sessionStorage.getItem(storageKey);
        var activeView = savedView || (isMobile ? 'list' : 'list');

        function applyView(view) {
            var listEl = document.getElementById('perm-list-view');
            var gridEl = document.getElementById('perm-grid-view');
            var btns = document.querySelectorAll('.perm-view-btn');
            if (!listEl || !gridEl) return;
            listEl.style.display = view === 'list' ? 'block' : 'none';
            gridEl.style.display = view === 'grid' ? 'block' : 'none';
            btns.forEach(function(btn) {
                var isActive = btn.getAttribute('data-view') === view;
                btn.classList.toggle('active', isActive);
                btn.classList.toggle('btn-secondary', isActive);
                btn.classList.toggle('btn-outline-secondary', !isActive);
            });
            sessionStorage.setItem(storageKey, view);
        }

        applyView(activeView);

        document.querySelectorAll('.perm-view-btn').forEach(function(btn) {
            btn.removeEventListener('click', btn._permViewHandler);
            btn._permViewHandler = function() { applyView(this.getAttribute('data-view')); };
            btn.addEventListener('click', btn._permViewHandler);
        });
    }

    $('#studentSearch').on('input', function() {
        const query = $(this).val();
        clearTimeout(searchTimeout);

        if (query.length < 2) {
            $('#studentSearchResults').hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            searchStudents(query);
        }, 300);
    });

    function searchStudents(query) {
        const resultsDiv = $('#studentSearchResults');
        resultsDiv.empty();
        resultsDiv.append('<div class="student-result-item">Searching...</div>');
        resultsDiv.show();

        $.ajax({
            url: '{{ route("staff.student_permission.search") }}',
            method: 'GET',
            data: { query: query },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                resultsDiv.empty();

                if (data.students && data.students.length > 0) {
                    const malePlaceholder = '{{ asset("images/male.png") }}';
                    const femalePlaceholder = '{{ asset("images/female.png") }}';

                    data.students.forEach(student => {
                        let studentPhoto = student.photo;
                        if (!studentPhoto || studentPhoto === 'undefined' || studentPhoto === null || studentPhoto === '') {
                            studentPhoto = student.gender === 'Female' ? femalePlaceholder : malePlaceholder;
                        }

                        const fallbackPhoto = student.gender === 'Female' ? femalePlaceholder : malePlaceholder;

                        resultsDiv.append(`
                            <div class="student-result-item" onclick="selectStudent(${student.studentID}, '${student.first_name} ${student.last_name}')">
                                <div class="d-flex align-items-center">
                                    <img src="${studentPhoto}" alt="${student.first_name}" class="student-photo-thumbnail me-3" onerror="this.src='${fallbackPhoto}'">
                                    <div>
                                        <div class="student-name">${student.first_name} ${student.last_name}</div>
                                        <div class="student-details">${student.class_name || 'No Class'} | ${student.gender || 'N/A'}</div>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                    resultsDiv.show();
                } else {
                    resultsDiv.append('<div class="student-result-item">No students found</div>');
                    resultsDiv.show();
                }
            },
            error: function(xhr) {
                resultsDiv.empty();
                let errorMsg = 'Error searching students';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMsg = 'Server error: ' + xhr.status;
                }
                resultsDiv.append('<div class="student-result-item">' + errorMsg + '</div>');
                resultsDiv.show();
            }
        });
    }

    function selectStudent(studentId, studentName) {
        $('#selectedStudentId').val(studentId);
        $('#studentSearch').val(studentName);
        $('#studentSearchResults').hide();
    }

    function togglePermissionMode() {
        const mode = $('#time_mode').val();

        if (mode === 'days') {
            $('#daysCountContainer').show();
            $('#dateRangeContainer').show();
            $('#timeRangeContainer').hide();
        } else if (mode === 'hours') {
            $('#daysCountContainer').hide();
            $('#dateRangeContainer').hide();
            $('#timeRangeContainer').show();
        } else {
            $('#daysCountContainer').hide();
            $('#dateRangeContainer').hide();
            $('#timeRangeContainer').hide();
        }
    }

    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.position-relative').length) {
            $('#studentSearchResults').hide();
        }
    });

    $('#studentPermissionForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');

        // Show loading state
        Swal.fire({
            title: 'Submitting...',
            text: 'Please wait while we submit your request',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        submitBtn.prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                submitBtn.prop('disabled', false);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        confirmButtonColor: '#940000'
                    }).then(() => {
                        window.location.href = "{{ route('staffDashboard') }}";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to submit request',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false);
                let errorMsg = 'An unexpected error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMsg = 'Server error: ' + xhr.status;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg,
                    confirmButtonColor: '#940000'
                });
            }
        });
    });
</script>
