{{-- Teacher Home Package modal (per subject card) --}}
<div class="modal fade" id="homePackageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-house-door"></i> Home Package
                    <small class="d-block" style="font-size:13px;font-weight:500;" id="hpSubjectLabel"></small>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="hpClassSubjectId" value="">
                <input type="hidden" id="hpLevel" value="">
                <ul class="nav nav-tabs" id="homePackageTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="hp-upload-tab" data-toggle="tab" href="#hpUploadPane" role="tab">Upload</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="hp-list-tab" data-toggle="tab" href="#hpListPane" role="tab">My Packages</a>
                    </li>
                </ul>
                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="hpUploadPane" role="tabpanel">
                        <form id="homePackageForm" enctype="multipart/form-data">
                            <input type="hidden" id="hpEditId" value="">
                            <div class="form-group">
                                <label>Holiday *</label>
                                <select class="form-control" id="hpHoliday" required>
                                    <option value="">-- Select holiday --</option>
                                </select>
                                <small class="text-muted">Holidays from the header academic year (O/A based on the subject class)</small>
                            </div>
                            <div class="form-group">
                                <label>Title (optional)</label>
                                <input type="text" class="form-control" id="hpTitle" placeholder="e.g. Holiday exercises">
                            </div>
                            <div class="form-group">
                                <label>File (PDF / DOC / DOCX) <span id="hpFileRequired">*</span></label>
                                <input type="file" class="form-control-file" id="hpFile" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                <small class="text-muted d-block" id="hpFileHint">You cannot upload twice for the same holiday on this subject — edit or delete first.</small>
                                <small class="text-info d-none d-block mt-1" id="hpCurrentFile"></small>
                            </div>
                            <div class="alert alert-warning d-none" id="hpHolidayTakenAlert">
                                A home package already exists for this holiday on this subject. Use <strong>Edit</strong> or <strong>Delete</strong>.
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="hpListPane" role="tabpanel">
                        <div class="form-group mb-3">
                            <label>Filter Holiday</label>
                            <select class="form-control form-control-sm" id="hpFilterHoliday">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="hpPackagesTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Holiday</th>
                                        <th>File</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="3" class="text-center text-muted">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="hpSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="homePackageViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="hpViewTitle">View Home Package</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" style="height:75vh;">
                <iframe id="hpViewFrame" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-outline-primary" id="hpViewDownloadBtn" target="_blank">Download</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function bootHp($) {
        if (!$) return;

        const HP_URLS = {
            holidays: @json(route('home_packages.holidays')),
            index: @json(route('home_packages.index')),
            store: @json(route('home_packages.store')),
            csrf: $('meta[name="csrf-token"]').attr('content')
        };

        function currentClassSubjectId() {
            return $('#hpClassSubjectId').val() || '';
        }

        function loadHolidaysInto(selectSelector, forFilter, selectedHolidayID) {
            const $sel = $(selectSelector);
            const csId = currentClassSubjectId();
            const prefer = selectedHolidayID != null && selectedHolidayID !== ''
                ? String(selectedHolidayID)
                : '';
            $sel.prop('disabled', true);
            return $.get(HP_URLS.holidays, { class_subjectID: csId })
                .done(function(res) {
                    if (res.level) $('#hpLevel').val(res.level);
                    const placeholder = forFilter ? '-- All --' : '-- Select holiday --';
                    let html = '<option value="">' + placeholder + '</option>';
                    (res.holidays || []).forEach(function(h) {
                        const id = String(h.holidayID);
                        // When editing, the current holiday is not "taken" for this package
                        const isCurrent = prefer && id === prefer;
                        const taken = h.has_package && !isCurrent;
                        const takenLabel = taken ? ' (already uploaded)' : '';
                        const selAttr = (prefer && id === prefer) ? ' selected' : '';
                        html += '<option value="' + id + '" data-taken="' + (taken ? '1' : '0') + '"' + selAttr + '>'
                            + h.holiday_name + ' (' + (h.start_date || '') + ' - ' + (h.end_date || '') + ')' + takenLabel
                            + '</option>';
                    });
                    $sel.html(html);
                    if (prefer) {
                        $sel.val(prefer);
                    }
                    $sel.trigger('change.hp');
                })
                .always(function() { $sel.prop('disabled', false); });
        }

        function loadPackages() {
            const params = {
                class_subjectID: currentClassSubjectId(),
                holidayID: $('#hpFilterHoliday').val() || ''
            };
            const $tb = $('#hpPackagesTable tbody');
            $tb.html('<tr><td colspan="3" class="text-center text-muted">Loading...</td></tr>');
            $.get(HP_URLS.index, params).done(function(res) {
                const rows = res.packages || [];
                if (!rows.length) {
                    $tb.html('<tr><td colspan="3" class="text-center text-muted">No home package for this subject</td></tr>');
                    return;
                }
                let html = '';
                rows.forEach(function(p) {
                    const titleSafe = String(p.title || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
                    const fileSafe = String(p.original_filename || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
                    html += '<tr>'
                        + '<td><strong>' + (p.holiday_name || '') + '</strong><br><small>' + (p.holiday_dates || '') + '</small></td>'
                        + '<td>' + (p.original_filename || p.title || '') + '</td>'
                        + '<td class="text-nowrap">'
                        + '<button type="button" class="btn btn-sm btn-info hp-view-btn" data-url="' + p.view_url + '" data-dl="' + p.download_url + '" data-title="' + titleSafe + '"><i class="bi bi-eye"></i></button> '
                        + '<a class="btn btn-sm btn-secondary" href="' + p.download_url + '"><i class="bi bi-download"></i></a> '
                        + '<button type="button" class="btn btn-sm btn-warning hp-edit-btn"'
                        + ' data-id="' + p.home_packageID + '"'
                        + ' data-holiday="' + p.holidayID + '"'
                        + ' data-title="' + titleSafe + '"'
                        + ' data-filename="' + fileSafe + '">'
                        + '<i class="bi bi-pencil"></i></button> '
                        + '<button type="button" class="btn btn-sm btn-danger hp-del-btn" data-id="' + p.home_packageID + '"><i class="bi bi-trash"></i></button>'
                        + '</td></tr>';
                });
                $tb.html(html);
            });
        }

        function resetUploadForm() {
            $('#hpEditId').val('');
            $('#hpTitle').val('');
            $('#hpFile').val('');
            $('#hpCurrentFile').addClass('d-none').text('');
            $('#hpFileRequired').show();
            $('#hpFileHint').text('You cannot upload twice for the same holiday on this subject — edit or delete first.');
            $('#hpHolidayTakenAlert').addClass('d-none');
            $('#hpSaveBtn').text('Save').prop('disabled', false);
            loadHolidaysInto('#hpHoliday', false);
        }

        window.openHomePackageModal = function(classSubjectID, subjectName) {
            $('#hpClassSubjectId').val(classSubjectID);
            $('#hpSubjectLabel').text(subjectName || 'Subject');
            resetUploadForm();
            loadHolidaysInto('#hpFilterHoliday', true);
            loadPackages();
            $('#hp-upload-tab').tab('show');
            $('#homePackageModal').modal('show');
        };

        $('#hpHoliday').off('change.hp').on('change.hp', function() {
            const taken = $('#hpHoliday option:selected').attr('data-taken') == '1';
            const editing = !!$('#hpEditId').val();
            if (taken && !editing) {
                $('#hpHolidayTakenAlert').removeClass('d-none');
                $('#hpSaveBtn').prop('disabled', true);
            } else {
                $('#hpHolidayTakenAlert').addClass('d-none');
                $('#hpSaveBtn').prop('disabled', false);
            }
        });

        $('#hpFilterHoliday').off('change.hp').on('change.hp', loadPackages);
        $('#hp-list-tab').off('shown.bs.tab.hp').on('shown.bs.tab.hp', loadPackages);

        $('#hpSaveBtn').off('click.hp').on('click.hp', function() {
            const editId = $('#hpEditId').val();
            const holidayID = $('#hpHoliday').val();
            const classSubjectID = currentClassSubjectId();
            const fileInput = document.getElementById('hpFile');

            if (!classSubjectID) {
                Swal.fire('Error', 'Subject is not selected', 'error');
                return;
            }
            if (!holidayID) {
                Swal.fire('Error', 'Please select a holiday', 'error');
                return;
            }
            if (!editId && (!fileInput.files || !fileInput.files.length)) {
                Swal.fire('Error', 'Please upload a file (PDF or DOC)', 'error');
                return;
            }

            const fd = new FormData();
            fd.append('_token', HP_URLS.csrf);
            fd.append('holidayID', holidayID);
            fd.append('class_subjectID', classSubjectID);
            fd.append('title', $('#hpTitle').val() || '');
            if (fileInput.files && fileInput.files[0]) {
                fd.append('file', fileInput.files[0]);
            }

            const url = editId
                ? (@json(url('home-packages')) + '/' + editId)
                : HP_URLS.store;
            const $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: url,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false
            }).done(function(res) {
                Swal.fire('Success', res.message || 'Saved successfully', 'success');
                resetUploadForm();
                loadPackages();
                $('#hp-list-tab').tab('show');
            }).fail(function(xhr) {
                let msg = 'Failed to save';
                if (xhr.responseJSON) {
                    msg = xhr.responseJSON.error || (xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).flat().join('\n') : msg);
                }
                Swal.fire('Error', msg, 'error');
            }).always(function() {
                $btn.prop('disabled', false).text(editId ? 'Update' : 'Save');
            });
        });

        $(document).off('click.hp', '.hp-view-btn').on('click.hp', '.hp-view-btn', function() {
            const url = $(this).attr('data-url');
            const dl = $(this).attr('data-dl');
            const title = $(this).attr('data-title') || 'Home Package';
            $('#hpViewTitle').text(title);
            $('#hpViewFrame').attr('src', url);
            $('#hpViewDownloadBtn').attr('href', dl);
            $('#homePackageViewModal').modal('show');
        });

        $(document).off('click.hp', '.hp-edit-btn').on('click.hp', '.hp-edit-btn', function() {
            const $btn = $(this);
            const id = $btn.attr('data-id');
            const holiday = $btn.attr('data-holiday');
            const title = $btn.attr('data-title') || '';
            const filename = $btn.attr('data-filename') || '';

            $('#hpEditId').val(id);
            $('#hpTitle').val(title);
            $('#hpFile').val('');
            if (filename) {
                $('#hpCurrentFile').removeClass('d-none').html('<strong>Current file:</strong> ' + $('<div>').text(filename).html());
            } else {
                $('#hpCurrentFile').addClass('d-none').text('');
            }
            $('#hpFileRequired').hide();
            $('#hpFileHint').text('Leave empty if you do not want to replace the file.');
            $('#hpSaveBtn').text('Update').prop('disabled', false);
            $('#hpHolidayTakenAlert').addClass('d-none');

            // Load holidays then select the existing holiday (old value)
            loadHolidaysInto('#hpHoliday', false, holiday);
            $('#hp-upload-tab').tab('show');
        });

        $(document).off('click.hp', '.hp-del-btn').on('click.hp', '.hp-del-btn', function() {
            const id = $(this).attr('data-id');
            Swal.fire({
                title: 'Delete Home Package?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: @json(url('home-packages')) + '/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': HP_URLS.csrf }
                }).done(function(res) {
                    Swal.fire('Deleted', res.message || 'Deleted successfully', 'success');
                    loadPackages();
                    loadHolidaysInto('#hpHoliday', false);
                }).fail(function(xhr) {
                    Swal.fire('Error', (xhr.responseJSON && xhr.responseJSON.error) || 'Failed', 'error');
                });
            });
        });

        $('#homePackageViewModal').off('hidden.bs.modal.hp').on('hidden.bs.modal.hp', function() {
            $('#hpViewFrame').attr('src', '');
        });
    }

    // Stub so early clicks never throw "openHomePackageModal is not defined"
    window.openHomePackageModal = window.openHomePackageModal || function(classSubjectID, subjectName) {
        const ready = window.jQuery;
        if (!ready) {
            alert('Please wait, page is still loading…');
            return;
        }
        bootHp(ready);
        if (typeof window.openHomePackageModal === 'function') {
            window.openHomePackageModal(classSubjectID, subjectName);
        }
    };

    function tryBoot() {
        if (window.jQuery) {
            bootHp(window.jQuery);
            return;
        }
        setTimeout(tryBoot, 50);
    }
    tryBoot();
})();
</script>
