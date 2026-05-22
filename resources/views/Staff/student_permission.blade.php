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

                        <div class="col-12 mb-3" id="daysCountContainer" style="display: none;">
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
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Permission Details</th>
                                        <th>Reason</th>
                                        <th>Progress Chain</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingTableBody">
                                    <tr><td colspan="5" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Tab -->
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Permission Details</th>
                                        <th>Reason</th>
                                        <th>Progress Chain</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="approvedTableBody">
                                    <tr><td colspan="5" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rejected Tab -->
            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Permission Details</th>
                                        <th>Reason</th>
                                        <th>Progress Chain</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="rejectedTableBody">
                                    <tr><td colspan="5" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
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

<script>
    let searchTimeout;

    // Load permission requests on page load
    $(document).ready(function() {
        loadPermissionRequests();
    });

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
                    $('#pendingTableBody').html('<tr><td colspan="5" class="text-center text-danger">' + errorMsg + '</td></tr>');
                    $('#approvedTableBody').html('<tr><td colspan="5" class="text-center text-danger">' + errorMsg + '</td></tr>');
                    $('#rejectedTableBody').html('<tr><td colspan="5" class="text-center text-danger">' + errorMsg + '</td></tr>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load requests';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMsg = 'Server error: ' + xhr.status;
                }
                $('#pendingTableBody').html('<tr><td colspan="5" class="text-center text-danger">' + errorMsg + '</td></tr>');
                $('#approvedTableBody').html('<tr><td colspan="5" class="text-center text-danger">' + errorMsg + '</td></tr>');
                $('#rejectedTableBody').html('<tr><td colspan="5" class="text-center text-danger">' + errorMsg + '</td></tr>');
            }
        });
    }

    function populateTable(status, requests) {
        const tbody = $(`#${status}TableBody`);
        tbody.empty();

        if (requests.length === 0) {
            tbody.append('<tr><td colspan="5" class="text-center">No requests found</td></tr>');
            return;
        }

        requests.forEach(request => {
            const progressChain = generateProgressChain(request);
            tbody.append(`
                <tr>
                    <td>
                        <div class="font-weight-bold">${request.student_name}</div>
                        <div class="small text-muted">${request.class_name || 'N/A'}</div>
                    </td>
                    <td>
                        <div><strong>Mode:</strong> ${request.time_mode || 'N/A'}</div>
                        <div><strong>Duration:</strong> ${request.duration || 'N/A'}</div>
                        <div><strong>Date:</strong> ${request.date_range || 'N/A'}</div>
                    </td>
                    <td>
                        <div><strong>Type:</strong> ${request.reason_type || 'N/A'}</div>
                        <div><strong>Details:</strong> ${request.reason_details || 'N/A'}</div>
                    </td>
                    <td>
                        ${progressChain}
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewRequestDetails(${request.id})">
                            <i class="bx bx-show"></i> View
                        </button>
                    </td>
                </tr>
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
        // Implement view details modal
        alert('View details for request ID: ' + requestId);
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
                    data.students.forEach(student => {
                        let studentPhoto = student.photo;
                        if (!studentPhoto || studentPhoto === 'undefined' || studentPhoto === null || studentPhoto === '') {
                            studentPhoto = student.gender === 'Female' ? '{{ asset("images/female.png") }}' : '{{ asset("images/male.png") }}';
                        }

                        resultsDiv.append(`
                            <div class="student-result-item" onclick="selectStudent(${student.studentID}, '${student.first_name} ${student.last_name}')">
                                <div class="d-flex align-items-center">
                                    <img src="${studentPhoto}" alt="${student.first_name}" class="student-photo-thumbnail me-3" onerror="this.src='${student.gender === 'Female' ? '{{ asset("images/female.png") }}' : '{{ asset("images/male.png") }}'}">
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
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred',
                    confirmButtonColor: '#940000'
                });
            }
        });
    });
</script>
