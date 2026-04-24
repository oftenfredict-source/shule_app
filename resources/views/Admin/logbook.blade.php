@include('includes.Admin_nav')

<div class="content-wrapper" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif; background-color: #FFFFFF;">
    <div class="content mt-3">
        <div class="animated fadeIn">
            
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 0; border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                        <div class="card-body py-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">ADMINISTRATIVE LOGBOOKS</h4>
                                    <p class="text-muted mb-0 small" style="font-weight: 600;">Monitor and provide Headmaster comments on all teaching records across the school</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="row mb-4 pt-2 pb-3" style="background: rgba(255,255,255,0.98); border-bottom: 2px solid #f0f0f0;">
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted" style="letter-spacing: 1px;">SELECT SUBJECT</label>
                    <select id="adminSubjectSelect" class="form-control border-0 bg-light" style="border-radius: 0; font-weight: 700; color: #940000; height: 50px;">
                        <option value="">-- Choose Subject --</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->subjectID }}">{{ $subj->subject_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted" style="letter-spacing: 1px;">SELECT CLASS</label>
                    <select id="adminClassSelect" class="form-control border-0 bg-light" style="border-radius: 0; font-weight: 700; color: #940000; height: 50px;">
                        <option value="">-- Choose Class --</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls->classID }}">{{ $cls->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted" style="letter-spacing: 1px;">SELECT STREAM</label>
                    <select id="adminSubclassSelect" class="form-control border-0 bg-light" style="border-radius: 0; font-weight: 700; color: #940000; height: 50px;">
                        <option value="">-- Choose Stream --</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="adminLoadLogbookBtn" class="btn btn-block text-white" style="border-radius: 0; background: #940000; font-weight: 700; border: none; height: 50px;">
                        <i class="fa fa-search"></i> VIEW LOGS
                    </button>
                </div>
            </div>

            <!-- Data Display Area -->
            <div id="adminLogbookContainer" style="display: none;">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 0; border-top: 3px solid #940000;">
                            <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1 font-weight-bold" style="color: #2f2f2f;">Teaching Records</h5>
                                    <div class="small text-muted" style="font-weight: 600;">
                                        <span id="h_Subject" style="color: #940000;"></span> | 
                                        <span id="h_Class"></span> | 
                                        <span id="h_Teacher"></span>
                                    </div>
                                </div>
                                <button id="adminDownloadPdfBtn" class="btn btn-outline-danger btn-sm font-weight-bold" style="border-radius: 0; display: none;">
                                    <i class="fa fa-file-pdf-o mr-1"></i> DOWNLOAD PDF
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="adminLogbookTable">
                                        <thead style="background: #FFF5F5;">
                                            <tr class="text-center small text-uppercase font-weight-bold" style="color: #940000;">
                                                <th class="border-0">#</th>
                                                <th class="border-0">Topic</th>
                                                <th class="border-0">Subtopic</th>
                                                <th class="border-0">Content</th>
                                                <th class="border-0">Teacher Sign</th>
                                                <th class="border-0">HOD Comment</th>
                                                <th class="border-0">HM Comment</th>
                                                <th class="border-0">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="adminLogbookBody">
                                            <!-- Data injected via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Initial Placeholder -->
            <div id="adminLogbookPlaceholder" class="text-center p-5 mt-5">
                <div class="py-5">
                    <i class="fa fa-book fa-4x mb-4" style="color: #FFF5F5;"></i>
                    <h4 class="text-muted font-weight-bold">Select parameters above to review school-wide records</h4>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- HM Comment Modal -->
<div class="modal fade" id="hmCommentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 0; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.2);">
            <div class="modal-header p-4 border-0" style="background: #940000; border-radius: 0;">
                <h5 class="modal-title font-weight-bold text-white"><i class="fa fa-commenting mr-2"></i> HEADMASTER FEEDBACK</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="hmCommentForm">
                @csrf
                <input type="hidden" name="logbookID" id="hmLogbookID">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase" style="color:#666;">HM Comment</label>
                        <textarea name="hm_comment" id="hmCommentText" class="form-control bg-light border-0" rows="4" required style="border-radius: 0;" placeholder="Enter headmaster feedback..."></textarea>
                    </div>
                    <div class="alert alert-info small border-0" style="border-radius: 0; background-color: #FFF5F5; color: #940000;">
                        <i class="fa fa-info-circle mr-1"></i> The teacher will be notified via SMS.
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn px-4 font-weight-bold btn-light" data-dismiss="modal" style="border-radius: 0;">CANCEL</button>
                    <button type="submit" class="btn px-4 font-weight-bold text-white" style="border-radius: 0; background: #940000; border: none;">SAVE COMMENT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<input type="hidden" id="admin_current_classSubjectID">

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function($) {
        $(document).ready(function() {
            // Subclass loading
            $('#adminClassSelect').on('change', function() {
                const classID = $(this).val();
                const subclassSelect = $('#adminSubclassSelect');
                subclassSelect.html('<option value="">-- Loading... --</option>');
                if (!classID) {
                    subclassSelect.html('<option value="">-- Choose Stream --</option>');
                    return;
                }
                $.get("{{ url('get_class_subclasses') }}/" + classID, function(response) {
                    let options = '<option value="">-- Choose Stream --</option>';
                    let data = [];
                    if (Array.isArray(response)) data = response;
                    else if (response.subclasses) data = response.subclasses;
                    if (data.length > 0) {
                        data.forEach(sub => {
                            options += `<option value="${sub.subclassID}">${sub.subclass_name || sub.display_name}</option>`;
                        });
                    }
                    subclassSelect.html(options);
                });
            });

            // Load Data
            $('#adminLoadLogbookBtn').on('click', function() {
                const subjectID = $('#adminSubjectSelect').val();
                const classID = $('#adminClassSelect').val();
                const subclassID = $('#adminSubclassSelect').val();
                
                if (!subjectID || !classID || !subclassID) {
                    Swal.fire({ icon: 'warning', title: 'Selection Required', text: 'Please select Subject, Class, and Stream.', confirmButtonColor: '#940000' });
                    return;
                }

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.getLogbookData') }}",
                    type: 'GET',
                    data: { subjectID: subjectID, classID: classID, subclassID: subclassID },
                    success: function(response) {
                        $btn.html(originalHtml).prop('disabled', false);
                        if (response.success) {
                            $('#adminLogbookPlaceholder').hide();
                            $('#adminLogbookContainer').show();
                            $('#adminDownloadPdfBtn').show();
                            $('#h_Subject').text(response.subjectName);
                            $('#h_Class').text(response.className + ' (' + (response.subclassName || 'N/A') + ')');
                            $('#h_Teacher').text('Teacher: ' + (response.teacherName || 'Not Assigned'));
                            $('#admin_current_classSubjectID').val(response.classSubjectID);

                            let rows = '';
                            if (response.logbookEntries && response.logbookEntries.length > 0) {
                                response.logbookEntries.forEach((entry, index) => {
                                    let teacherSign = entry.status === 'Active' 
                                        ? `<span class="badge badge-info px-2 py-1" style="border-radius:0;">SIGNED</span>` 
                                        : `<span class="badge badge-warning px-2 py-1" style="border-radius:0;">PENDING</span>`;
                                    
                                    let hodStatus = entry.hod_comment 
                                        ? `<span class="badge badge-success px-2 py-1" style="border-radius:0; background:#28a745;">COMMENTED</span><div class="small mt-1 text-dark">${entry.hod_comment}</div>` 
                                        : `<span class="badge badge-secondary px-2 py-1" style="border-radius:0;">PENDING</span>`;
                                    
                                    let hmStatus = entry.hm_comment 
                                        ? `<span class="badge badge-success px-2 py-1" style="border-radius:0; background:#28a745;">COMMENTED</span><div class="small mt-1 text-dark">${entry.hm_comment}</div>` 
                                        : `<span class="badge badge-secondary px-2 py-1" style="border-radius:0;">PENDING</span>`;

                                    rows += `
                                        <tr class="text-center align-middle small">
                                            <td class="font-weight-bold">${index + 1}</td>
                                            <td>${entry.main_topic || ''}</td>
                                            <td>${entry.sub_topic || ''}</td>
                                            <td style="max-width: 250px; white-space: normal;">${entry.content_covered || ''}</td>
                                            <td>${teacherSign}</td>
                                            <td style="max-width: 150px;">${hodStatus}</td>
                                            <td style="max-width: 150px;">${hmStatus}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger font-weight-bold" onclick="openHmCommentModal(${entry.logbookID}, '${escapeJs(entry.hm_comment || '')}')" style="border-radius:0;">
                                                    <i class="fa fa-comment"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                            } else {
                                rows = '<tr><td colspan="8" class="p-5 text-center text-muted font-weight-bold">No records found.</td></tr>';
                            }
                            $('#adminLogbookBody').html(rows);
                        } else {
                            Swal.fire('Notice', response.message || 'No data found', 'info');
                        }
                    },
                    error: function(xhr) {
                        $btn.html(originalHtml).prop('disabled', false);
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to load records.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

            // HM Comment Submission
            $('#hmCommentForm').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> SAVING...');
                $.ajax({
                    url: "{{ route('admin.adminSignLogbook') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#hmCommentModal').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Saved', text: response.message, timer: 1500, showConfirmButton: false });
                            $('#adminLoadLogbookBtn').click();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('SAVE COMMENT');
                    }
                });
            });

            $('#adminDownloadPdfBtn').on('click', function() {
                const classSubjectID = $('#admin_current_classSubjectID').val();
                if (!classSubjectID) return;
                window.open("{{ url('teacher/download-logbook-pdf') }}/" + classSubjectID, '_blank');
            });

            function escapeJs(text) {
                if (!text) return '';
                return text.replace(/'/g, "\\'").replace(/"/g, '\\"');
            }

            window.openHmCommentModal = function(id, comment) {
                $('#hmLogbookID').val(id);
                $('#hmCommentText').val(comment);
                $('#hmCommentModal').modal('show');
            };
        });
    })(jQuery);
</script>
