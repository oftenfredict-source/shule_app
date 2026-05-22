@extends('layouts.teacher_corp')

@section('title', 'Student Permission Requests - ShuleXpert')

@section('content')
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
        padding: 12px 15px !important;
        font-size: 14px !important;
        background-color: #fff !important;
    }
    .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 4px rgba(148, 0, 0, 0.05) !important;
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
</style>

<div class="row">
    <div class="col-12">
        <div class="section-title"><i class="bx bx-user-plus"></i> Request Student Permission</div>
    </div>

    <div class="col-12">
        <div class="card border-0">
            <div class="card-body">
                <form id="studentPermissionForm" method="POST" action="{{ route('teacher.student_permission.store') }}">
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
                            <input type="text" class="form-control" value="Teacher" readonly>
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
                            <a href="{{ route('teachersDashboard') }}" class="btn btn-secondary">
                                <i class="bx bx-x"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let searchTimeout;
    
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
        $.ajax({
            url: '{{ route("teacher.student_permission.search") }}',
            method: 'GET',
            data: { query: query },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                const resultsDiv = $('#studentSearchResults');
                resultsDiv.empty();
                
                if (data.students && data.students.length > 0) {
                    data.students.forEach(student => {
                        resultsDiv.append(`
                            <div class="student-result-item" onclick="selectStudent(${student.studentID}, '${student.first_name} ${student.last_name}')">
                                <div class="student-name">${student.first_name} ${student.last_name}</div>
                                <div class="student-details">${student.class_name || 'No Class'} | ${student.gender || 'N/A'}</div>
                            </div>
                        `);
                    });
                    resultsDiv.show();
                } else {
                    resultsDiv.append('<div class="student-result-item">No students found</div>');
                    resultsDiv.show();
                }
            },
            error: function() {
                $('#studentSearchResults').hide();
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
                        window.location.href = "{{ route('teachersDashboard') }}";
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
@endsection
