@extends('layouts.parent_corp')

@section('title', 'Registered Subjects - ShuleXpert')

@section('content')
<style>
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
    .table th {
        background: #fcfcfc;
        color: #940000;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        font-weight: 800;
    }
    .subject-row:hover {
        background: #fffafa !important;
    }
    .status-badge {
        font-size: 9px;
        padding: 4px 8px;
        text-transform: uppercase;
        font-weight: 700;
        border-radius: 0 !important;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="section-title"><i class="bx bx-book"></i> Curriculum & Subjects</div>
    </div>

    <!-- Student Selection -->
    <div class="col-12">
        <div class="selection-card">
            <div class="row align-items-end g-3">
                <div class="col-md-6">
                    <label class="form-label text-uppercase small font-weight-bold">Select Student</label>
                    <select id="studentSelect" class="form-select">
                        <option value="">Choose a student...</option>
                        @foreach($students as $student)
                            <option value="{{ $student->studentID }}">
                                {{ $student->first_name }} {{ $student->last_name }} 
                                ({{ $student->subclass->class->class_name ?? '' }} {{ $student->subclass->subclass_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <div class="text-muted small pb-2"><i class="bx bx-info-circle"></i> Selecting a student will load their current subject list and teacher assignments.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects Container -->
    <div class="col-12 mt-4" id="subjectsContainer">
        <div class="bg-white p-5 text-center shadow-sm">
            <i class="bx bx-select-multiple text-muted mb-3" style="font-size: 50px;"></i>
            <h5 class="text-muted">Please select a student above to view their subjects.</h5>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#studentSelect').on('change', function() {
            var studentID = $(this).val();
            if (!studentID) return;

            $('#subjectsContainer').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

            $.ajax({
                url: "{{ route('get_student_subjects', ':id') }}".replace(':id', studentID),
                type: "GET",
                success: function(response) {
                    if (response.success && response.subjects) {
                        var html = '<div class="card border-0 shadow-sm">';
                        html += '<div class="card-header bg-white py-3 border-bottom"><h6 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing:1px; color:#940000;"><i class="bx bx-list-ul"></i> Subject List for ' + response.student.first_name + '</h6></div>';
                        html += '<div class="table-responsive">';
                        html += '<table class="table table-hover align-middle mb-0" id="subjectsTable">';
                        html += '<thead><tr><th>#</th><th>Subject</th><th>Teacher</th><th>Requirement</th><th>Action</th></tr></thead><tbody>';

                        response.subjects.forEach(function(s, idx) {
                            var reqBadge = s.student_status === 'Required' ? 'bg-warning text-dark' : 'bg-info text-white';
                            html += '<tr class="subject-row">';
                            html += '<td>' + (idx+1) + '</td>';
                            html += '<td><div style="font-weight:800; color:#333;">' + s.subject_name + '</div><div class="small text-muted">' + (s.subject_code || '---') + '</div></td>';
                            html += '<td class="small">' + (s.teacher_name || '<i class="text-muted">No teacher assigned</i>') + '</td>';
                            html += '<td><span class="status-badge ' + reqBadge + '">' + s.student_status + '</span></td>';
                            html += '<td>';
                            if (s.student_status === 'Optional' && s.status === 'Active') {
                                if (s.is_elected) {
                                    html += '<button class="btn btn-sm btn-danger deselect-subject-btn py-1" style="font-size:10px; border-radius:0;" data-sid="'+response.student.studentID+'" data-csid="'+s.class_subjectID+'" data-name="'+s.subject_name+'">DESELECT</button>';
                                } else {
                                    html += '<button class="btn btn-sm btn-primary elect-subject-btn py-1" style="font-size:10px; border-radius:0;" data-sid="'+response.student.studentID+'" data-csid="'+s.class_subjectID+'" data-name="'+s.subject_name+'">ELECT SUBJECT</button>';
                                }
                            } else {
                                html += '<span class="text-muted small">---</span>';
                            }
                            html += '</td></tr>';
                        });

                        html += '</tbody></table></div></div>';
                        $('#subjectsContainer').html(html);
                        $('#subjectsTable').DataTable({ "pageLength": 50, "bLengthChange": false });
                    }
                }
            });
        });

        $(document).on('click', '.elect-subject-btn, .deselect-subject-btn', function() {
            var isElect = $(this).hasClass('elect-subject-btn');
            var url = isElect ? "{{ route('elect_subject') }}" : "{{ route('deselect_subject') }}";
            var sid = $(this).data('sid');
            var csid = $(this).data('csid');
            var name = $(this).data('name');

            Swal.fire({
                title: isElect ? 'Elect Subject?' : 'Deselect Subject?',
                text: 'Confirm action for ' + name,
                icon: isElect ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Confirm'
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post(url, { studentID: sid, classSubjectID: csid, _token: '{{ csrf_token() }}' }, function(r) {
                        if (r.success) {
                            Swal.fire('Success', r.success, 'success').then(() => $('#studentSelect').trigger('change'));
                        } else {
                            Swal.fire('Error', r.error, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
