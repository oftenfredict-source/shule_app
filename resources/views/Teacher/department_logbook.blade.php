@include('includes.teacher_nav')

<div class="content-wrapper" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif; background-color: #FFFFFF;">
    <div class="content mt-3">
        <div class="animated fadeIn">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 0; border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">DEPARTMENT LOGBOOKS</h4>
                                    <p class="text-muted mb-0 small" style="font-weight: 600;">Monitor and sign teaching records for your department</p>
                                </div>
                                <div>
                                    <button class="btn shadow-sm text-white" id="downloadPdfBtn" style="border-radius: 0; background: #940000; border: none; font-weight: 700; padding: 10px 20px; display: none;">
                                        <i class="fa fa-file-pdf-o"></i> DOWNLOAD PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 0; background-color: #FFFFFF;">
                        <div class="card-header border-0 p-4" style="background: #fafafa;">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 1px;">Select Subject</label>
                                    <select id="hocSubjectSelect" class="form-control" style="border-radius:0; border:1px solid #ddd; font-weight:600;">
                                        <option value="">-- Choose Subject --</option>
                                        @foreach($subjects as $subj)
                                            <option value="{{ $subj->subjectID }}">{{ $subj->subject_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 1px;">Select Class</label>
                                    <select id="hocClassSelect" class="form-control" style="border-radius:0; border:1px solid #ddd; font-weight:600;">
                                        <option value="">-- Choose Class --</option>
                                        @foreach($classes as $cls)
                                            <option value="{{ $cls->classID }}">{{ $cls->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-block text-white" id="loadDeptLogbookBtn" style="background:#2f2f2f; border-radius:0; height:calc(1.5em + .75rem + 2px); font-weight: 700;">
                                        <i class="fa fa-search"></i> FILTER
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logbook Content -->
            <div id="deptLogbookContainer" style="display: none;">
                <!-- Header Info -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="p-3 shadow-sm border" style="background-color: #FFF5F5; border-radius: 0;">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <small class="text-muted d-block font-weight-bold">SUBJECT</small>
                                    <span id="h_Subject" class="font-weight-bold" style="color: #940000;"></span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block font-weight-bold">CLASS</small>
                                    <span id="h_Class" class="font-weight-bold" style="color: #940000;"></span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block font-weight-bold">YEAR</small>
                                    <span id="h_Year" class="font-weight-bold" style="color: #940000;"></span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block font-weight-bold">TEACHER</small>
                                    <span id="h_Teacher" class="font-weight-bold" style="color: #940000;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 0;">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0" style="border-color: #eee;">
                                        <thead style="background-color: #940000; color: white;">
                                            <tr class="text-center small">
                                                <th style="width: 50px;">SN</th>
                                                <th>MAIN TOPIC</th>
                                                <th>SUB TOPIC</th>
                                                <th>CONTENT COVERED</th>
                                                <th>DATE STARTED</th>
                                                <th>DATE ENDED</th>
                                                <th>TEACHER'S COMMENT</th>
                                                <th>H.O.D COMMENT</th>
                                                <th>H/M COMMENT</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody id="deptLogbookBody">
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Placeholder -->
            <div class="py-5 text-center" id="deptWelcomePlaceholder">
                <div class="p-4 mb-4 rounded-circle mx-auto" style="background-color: #FFF5F5; width: fit-content;">
                    <i class="fa fa-users fa-4x text-muted" style="color: #940000 !important;"></i>
                </div>
                <h3 class="font-weight-bold mb-3" style="color: #2f2f2f;">Departmental Oversight</h3>
                <p class="text-muted mb-4 px-3" style="max-width: 600px; margin: 0 auto;">
                    Select a subject and class to review the logbooks of teachers in your department. 
                    You can provide feedback and sign off on content coverage.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Sign Modal -->
<div class="modal fade" id="signModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="border-radius: 0;">
            <div class="modal-header border-0 p-4 d-block" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #940000; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title font-weight-bold mb-1" style="color: #940000; letter-spacing: 0.5px;">SIGN LOGBOOK ENTRY</h5>
                <p class="text-muted small mb-0 font-weight-bold">Provide your comment as Head of Department.</p>
            </div>
            <form id="signForm">
                @csrf
                <input type="hidden" name="logbookID" id="signLogbookID">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="small font-weight-bold text-uppercase pb-1" style="color:#666;">H.O.D Comment</label>
                            <textarea name="hod_comment" id="signCommentText" class="form-control" rows="4" required style="border-radius: 0;" placeholder="e.g. Content verified, keep it up."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 0; background: #f0f0f0; color: #2f2f2f;">CANCEL</button>
                    <button type="submit" class="btn px-4 font-weight-bold text-white shadow-sm" style="border-radius: 0; background: #940000; border: none;">SUBMIT SIGNATURE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<input type="hidden" id="current_classSubjectID">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#loadDeptLogbookBtn').on('click', function() {
            const subjectID = $('#hocSubjectSelect').val();
            const classID = $('#hocClassSelect').val();
            
            if (!subjectID || !classID) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select both subject and class.',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            $.ajax({
                url: "{{ route('teacher.getDepartmentLogbookData') }}",
                type: 'GET',
                data: { subjectID: subjectID, classID: classID },
                beforeSend: function() {
                    $('#deptWelcomePlaceholder').hide();
                    $('#deptLogbookContainer').hide();
                    $('#downloadPdfBtn').hide();
                },
                success: function(response) {
                    if (response.success) {
                        $('#h_Subject').text(response.subjectName);
                        $('#h_Class').text(response.className);
                        $('#h_Year').text(response.year);
                        $('#h_Teacher').text(response.teacherName);
                        $('#current_classSubjectID').val(response.classSubjectID);

                        let rows = '';
                        if (response.logbookEntries.length > 0) {
                            response.logbookEntries.forEach((entry, index) => {
                                rows += `
                                    <tr class="text-center small">
                                        <td class="font-weight-bold">${index + 1}</td>
                                        <td>${entry.main_topic || ''}</td>
                                        <td>${entry.sub_topic || ''}</td>
                                        <td>${entry.content_covered || ''}</td>
                                        <td>${entry.date_started || ''}</td>
                                        <td>${entry.date_ended || ''}</td>
                                        <td>${entry.teacher_comment || ''}</td>
                                        <td class="italic">${entry.hod_comment || '<span class="text-muted">Not Signed</span>'}</td>
                                        <td class="text-muted italic">${entry.hm_comment || 'Pending...'}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger" onclick="openSignModal(${entry.logbookID}, '${escapeHtml(entry.hod_comment || '')}')" style="border-radius: 0; font-size: 10px;">
                                                <i class="fa fa-pencil"></i> SIGN
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            rows = '<tr><td colspan="10" class="text-center py-5 text-muted font-weight-bold">No logbook entries recorded for this subject and class.</td></tr>';
                        }

                        $('#deptLogbookBody').html(rows);
                        $('#deptLogbookContainer').fadeIn();
                        $('#downloadPdfBtn').show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                        $('#deptWelcomePlaceholder').show();
                    }
                },
                error: function(err) {
                    const msg = err.responseJSON ? err.responseJSON.message : 'Something went wrong';
                    Swal.fire('Error', msg, 'error');
                    $('#deptWelcomePlaceholder').show();
                }
            });
        });

        $('#signForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: "{{ route('teacher.signLogbook') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#signModal').modal('hide');
                        $('#loadDeptLogbookBtn').click(); // Refresh data
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                }
            });
        });

        // AJAX Download PDF
        $('#downloadPdfBtn').on('click', function() {
            const classSubjectID = $('#current_classSubjectID').val();
            if (!classSubjectID) return;

            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> GENERATING...');

            fetch("{{ url('teacher/download-logbook-pdf') }}/" + classSubjectID)
                .then(response => {
                    if(!response.ok) throw new Error('Failed to generate PDF');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'Logbook_' + classSubjectID + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    Swal.fire({ title: 'Success', text: 'Logbook PDF downloaded.', icon: 'success', timer: 1500, showConfirmButton: false });
                })
                .catch(error => {
                    Swal.fire('Error', error.message, 'error');
                })
                .finally(() => {
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });
    });

    function openSignModal(id, comment) {
        $('#signLogbookID').val(id);
        $('#signCommentText').val(comment);
        $('#signModal').modal('show');
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
