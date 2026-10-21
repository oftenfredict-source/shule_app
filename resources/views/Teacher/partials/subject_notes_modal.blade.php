{{-- Teacher Notes modal (per subject card) --}}
<div class="modal fade" id="subjectNotesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-journal-text" id="snModalIcon"></i> <span id="snModalKindLabel">Notes</span>
                    <small class="d-block" style="font-size:13px;font-weight:500;" id="snSubjectLabel"></small>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="snClassSubjectId" value="">
                <input type="hidden" id="snEditId" value="">
                <input type="hidden" id="snKind" value="note">
                <input type="hidden" id="snApplyAll" value="0">
                <div id="snStreamsBox" class="mb-3" style="display:none;">
                    <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                        <strong>Streams:</strong>
                        <span id="snStreamBadges"></span>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="snApplyAllBtn">Apply to all streams</button>
                    </div>
                    <small class="text-muted d-block mt-1" id="snApplyAllHint"></small>
                </div>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="sn-upload-tab" data-toggle="tab" href="#snUploadPane" role="tab">Upload</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="sn-list-tab" data-toggle="tab" href="#snListPane" role="tab">My Notes</a>
                    </li>
                </ul>
                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="snUploadPane" role="tabpanel">
                        <div id="snForms"></div>
                        <button type="button" class="btn btn-outline-primary" id="snAddAnother">
                            <i class="bi bi-plus-circle"></i> Add another
                        </button>
                    </div>
                    <div class="tab-pane fade" id="snListPane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="snNotesTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Title</th>
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
                <button type="button" class="btn btn-primary-custom" id="snSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="subjectNoteViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="snViewTitle">View Note</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" style="height:75vh;">
                <iframe id="snViewFrame" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-outline-primary" id="snViewDownloadBtn" target="_blank">Download</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .sn-note-form {
        border: 1px solid #eadada;
        background: #fff;
        padding: 14px 14px 10px;
        margin-bottom: 12px;
    }
    .sn-dropzone {
        border: 2px dashed #940000;
        background: #fff8f8;
        padding: 18px 14px;
        text-align: center;
        cursor: pointer;
        color: #333;
    }
    .sn-dropzone.is-drag {
        background: #ffeaea;
        border-style: solid;
    }
    #snStreamsBox {
        border: 1px solid #eadada;
        background: #fff8f8;
        padding: 10px 12px;
    }
    #snStreamBadges .badge {
        border-radius: 0;
        font-weight: 600;
        font-size: 12px;
        padding: 5px 8px;
    }
    #snApplyAllBtn.is-on {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
</style>

<script>
(function() {
    function bootSn($) {
        if (!$) return;
        const SN_URLS = {
            index: @json(route('subject_notes.index')),
            store: @json(route('subject_notes.store')),
            csrf: $('meta[name="csrf-token"]').attr('content')
        };
        const SN_MAX = 50 * 1024 * 1024;
        const SN_OK = /\.(pdf|doc|docx)$/i;

        function currentKind() {
            return $('#snKind').val() === 'past_paper' ? 'past_paper' : 'note';
        }

        function kindCopy() {
            if (currentKind() === 'past_paper') {
                return {
                    title: 'Past Papers',
                    list: 'My Past Papers',
                    empty: 'No past papers for this subject yet',
                    add: 'Add past papers',
                    applyHint: 'You teach this subject in :label. Use Apply to all streams to save the same past papers on every stream.'
                };
            }
            return {
                title: 'Notes',
                list: 'My Notes',
                empty: 'No notes for this subject yet',
                add: 'Add notes',
                applyHint: 'You teach this subject in :label. Use Apply to all streams to save the same notes on every stream.'
            };
        }

        function applyKindLabels() {
            const copy = kindCopy();
            $('#snModalKindLabel').text(copy.title);
            $('#sn-list-tab').text(copy.list);
        }

        function currentClassSubjectId() {
            return $('#snClassSubjectId').val() || '';
        }

        function isEditMode() {
            return !!$('#snEditId').val();
        }

        function escapeHtml(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function setApplyAll(on) {
            const active = !!on;
            $('#snApplyAll').val(active ? '1' : '0');
            $('#snApplyAllBtn').toggleClass('is-on', active)
                .toggleClass('btn-outline-primary', !active)
                .text(active ? 'Applying to all streams' : 'Apply to all streams');
        }

        function renderStreams(res) {
            const streams = res.streams || [];
            const canApply = !!res.apply_to_all_available && streams.length > 1 && !isEditMode();
            if (!canApply) {
                $('#snStreamsBox').hide();
                setApplyAll(false);
                return;
            }
            let badges = '';
            streams.forEach(function(s) {
                const cls = s.is_current ? 'badge-danger' : 'badge-light border';
                badges += '<span class="badge ' + cls + '">' + escapeHtml(s.stream || '') + '</span> ';
            });
            $('#snStreamBadges').html(badges);
            $('#snApplyAllHint').text(kindCopy().applyHint.replace(':label', res.streams_label || 'these streams'));
            $('#snStreamsBox').show();
        }

        function loadNotes() {
            const $tb = $('#snNotesTable tbody');
            $tb.html('<tr><td colspan="3" class="text-center text-muted">Loading...</td></tr>');
            $.get(SN_URLS.index, { class_subjectID: currentClassSubjectId(), kind: currentKind() }).done(function(res) {
                renderStreams(res);
                const rows = res.notes || [];
                if (!rows.length) {
                    $tb.html('<tr><td colspan="3" class="text-center text-muted">' + kindCopy().empty + '<br><button type="button" class="btn btn-sm btn-primary-custom mt-2" id="snAddFirst">' + kindCopy().add + '</button></td></tr>');
                    return;
                }
                let html = '';
                rows.forEach(function(n) {
                    const titleSafe = String(n.title || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
                    const fileSafe = String(n.original_filename || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
                    html += '<tr>'
                        + '<td><strong>' + titleSafe + '</strong><br><small class="text-muted">' + (n.created_at || '') + '</small></td>'
                        + '<td>' + fileSafe + '</td>'
                        + '<td class="text-nowrap">'
                        + '<button type="button" class="btn btn-sm btn-info sn-view-btn" data-url="' + n.view_url + '" data-dl="' + n.download_url + '" data-title="' + titleSafe + '"><i class="bi bi-eye"></i></button> '
                        + '<a class="btn btn-sm btn-secondary" href="' + n.download_url + '"><i class="bi bi-download"></i></a> '
                        + '<button type="button" class="btn btn-sm btn-warning sn-edit-btn" data-id="' + n.noteID + '" data-title="' + titleSafe + '" data-filename="' + fileSafe + '"><i class="bi bi-pencil"></i></button> '
                        + '<button type="button" class="btn btn-sm btn-danger sn-del-btn" data-id="' + n.noteID + '"><i class="bi bi-trash"></i></button>'
                        + '</td></tr>';
                });
                $tb.html(html);
            }).fail(function(xhr) {
                $tb.html('<tr><td colspan="3" class="text-center text-danger">' + ((xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load') + '</td></tr>');
            });
        }

        function refreshFormLabels() {
            const total = $('#snForms .sn-note-form').length;
            $('#snForms .sn-note-form').each(function(i) {
                $(this).find('.sn-form-label').text('Note ' + (i + 1));
                $(this).find('.sn-remove-form').toggle(total > 1 && !isEditMode());
            });
            $('#snAddAnother').toggle(!isEditMode());
        }

        function addNoteForm(opts) {
            opts = opts || {};
            const required = opts.fileRequired !== false;
            const html = '<div class="sn-note-form">'
                + '<div class="d-flex justify-content-between align-items-center mb-2">'
                +   '<strong class="sn-form-label" style="color:#940000;">Note 1</strong>'
                +   '<button type="button" class="btn btn-sm btn-outline-danger sn-remove-form" title="Remove"><i class="bi bi-x"></i> Remove</button>'
                + '</div>'
                + '<div class="form-group">'
                +   '<label>Title / Topic range *</label>'
                +   '<input type="text" class="form-control sn-title" placeholder="e.g. EXPONENT AND RADICAL" value="' + String(opts.title || '').replace(/"/g, '&quot;') + '">'
                + '</div>'
                + '<div class="form-group mb-0">'
                +   '<label>File (PDF / DOC / DOCX, max 50 MB) ' + (required ? '*' : '(optional)') + '</label>'
                +   '<div class="sn-dropzone">'
                +     '<i class="bi bi-cloud-arrow-up" style="font-size:28px;color:#940000;"></i>'
                +     '<div class="mt-1"><strong>Drag and drop</strong> the file here, or click to select</div>'
                +     '<small class="text-muted">PDF, DOC, DOCX. Max 50 MB.</small>'
                +     '<input type="file" class="sn-file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" hidden>'
                +   '</div>'
                +   '<small class="text-info sn-file-name d-none d-block mt-1"></small>'
                + '</div>'
                + '</div>';
            const $form = $(html);
            if (opts.currentFile) {
                $form.find('.sn-file-name').removeClass('d-none').html('<strong>Current file:</strong> ' + $('<div>').text(opts.currentFile).html() + ' (leave empty to keep)');
            }
            $('#snForms').append($form);
            refreshFormLabels();
            $form.find('.sn-title').focus();
            return $form;
        }

        function resetUploadForm() {
            $('#snEditId').val('');
            $('#snForms').empty();
            $('#snSaveBtn').text('Save').prop('disabled', false);
            setApplyAll(false);
            if ($.trim($('#snStreamBadges').html() || '') !== '') {
                $('#snStreamsBox').show();
            }
            addNoteForm();
        }

        function assignFile($form, file) {
            if (!file) return false;
            if (!SN_OK.test(file.name || '')) {
                Swal.fire('Error', 'Please upload a PDF or Word file', 'error');
                return false;
            }
            if (file.size > SN_MAX) {
                Swal.fire('Error', 'File must be 50 MB or less', 'error');
                return false;
            }
            const input = $form.find('.sn-file')[0];
            $form.data('snFile', file);
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            } catch (err) {}
            $form.find('.sn-file-name').removeClass('d-none').text(file.name);
            return true;
        }

        window.openSubjectNotesModal = function(classSubjectID, subjectName, kind) {
            $('#snClassSubjectId').val(classSubjectID);
            $('#snKind').val(kind === 'past_paper' ? 'past_paper' : 'note');
            $('#snSubjectLabel').text(subjectName || 'Subject');
            applyKindLabels();
            setApplyAll(false);
            resetUploadForm();
            loadNotes();
            $('#sn-list-tab').tab('show');
            $('#subjectNotesModal').modal('show');
        };

        $('#snApplyAllBtn').off('click.snall').on('click.snall', function() {
            if (isEditMode()) return;
            setApplyAll($('#snApplyAll').val() !== '1');
        });

        $('#snAddAnother').off('click.sn').on('click.sn', function() {
            if (isEditMode()) return;
            addNoteForm();
        });

        $(document).off('click.snrm', '.sn-remove-form').on('click.snrm', '.sn-remove-form', function() {
            if (isEditMode()) return;
            if ($('#snForms .sn-note-form').length < 2) return;
            $(this).closest('.sn-note-form').remove();
            refreshFormLabels();
        });

        $(document).off('click.sndz', '.sn-dropzone').on('click.sndz', '.sn-dropzone', function(e) {
            if (e.target && ($(e.target).is('.sn-file') || $(e.target).closest('.sn-file').length)) {
                return;
            }
            e.preventDefault();
            $(this).find('.sn-file')[0].click();
        });
        $(document).off('click.snfile', '.sn-file').on('click.snfile', '.sn-file', function(e) {
            e.stopPropagation();
        });
        $(document).off('dragover.sndz dragenter.sndz', '.sn-dropzone').on('dragover.sndz dragenter.sndz', '.sn-dropzone', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('is-drag');
        });
        $(document).off('dragleave.sndz', '.sn-dropzone').on('dragleave.sndz', '.sn-dropzone', function() {
            $(this).removeClass('is-drag');
        });
        $(document).off('drop.sndz', '.sn-dropzone').on('drop.sndz', '.sn-dropzone', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('is-drag');
            const files = e.originalEvent.dataTransfer.files;
            if (files && files[0]) {
                assignFile($(this).closest('.sn-note-form'), files[0]);
            }
        });
        $(document).off('change.snfile', '.sn-file').on('change.snfile', '.sn-file', function() {
            const f = this.files && this.files[0];
            if (f) {
                assignFile($(this).closest('.sn-note-form'), f);
            }
        });

        $(document).off('click.snadd', '#snAddFirst').on('click.snadd', '#snAddFirst', function() {
            resetUploadForm();
            $('#sn-upload-tab').tab('show');
        });

        $('#sn-list-tab').off('shown.bs.tab.sn').on('shown.bs.tab.sn', loadNotes);

        $('#snSaveBtn').off('click.sn').on('click.sn', function() {
            const editId = $('#snEditId').val();
            const classSubjectID = currentClassSubjectId();
            if (!classSubjectID) {
                Swal.fire('Error', 'Subject is not selected', 'error');
                return;
            }

            const fd = new FormData();
            fd.append('_token', SN_URLS.csrf);
            fd.append('class_subjectID', classSubjectID);
            fd.append('kind', currentKind());
            if (!editId && $('#snApplyAll').val() === '1') {
                fd.append('apply_to_all', '1');
            }

            if (editId) {
                const $form = $('#snForms .sn-note-form').first();
                const title = $.trim($form.find('.sn-title').val() || '');
                const file = $form.data('snFile') || ($form.find('.sn-file')[0].files[0]);
                if (!title) {
                    Swal.fire('Error', 'Please enter a title / topic range', 'error');
                    return;
                }
                fd.append('title', title);
                if (file) {
                    fd.append('file', file);
                }
            } else {
                const forms = $('#snForms .sn-note-form');
                if (!forms.length) {
                    Swal.fire('Error', 'Please add at least one note', 'error');
                    return;
                }
                let ok = true;
                forms.each(function(i) {
                    const title = $.trim($(this).find('.sn-title').val() || '');
                    const file = $(this).data('snFile') || ($(this).find('.sn-file')[0].files[0]);
                    if (!title) {
                        Swal.fire('Error', 'Please enter a title / topic range on Note ' + (i + 1), 'error');
                        ok = false;
                        return false;
                    }
                    if (!file) {
                        Swal.fire('Error', 'Please upload a PDF or Word file on Note ' + (i + 1), 'error');
                        ok = false;
                        return false;
                    }
                    fd.append('titles[]', title);
                    fd.append('files[]', file);
                });
                if (!ok) return;
            }

            const url = editId ? (@json(url('subject-notes')) + '/' + editId) : SN_URLS.store;
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
                loadNotes();
                $('#sn-list-tab').tab('show');
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

        $(document).off('click.sn', '.sn-view-btn').on('click.sn', '.sn-view-btn', function() {
            $('#snViewTitle').text($(this).attr('data-title') || 'Note');
            $('#snViewFrame').attr('src', $(this).attr('data-url'));
            $('#snViewDownloadBtn').attr('href', $(this).attr('data-dl'));
            $('#subjectNoteViewModal').modal('show');
        });

        $(document).off('click.sn', '.sn-edit-btn').on('click.sn', '.sn-edit-btn', function() {
            const $btn = $(this);
            $('#snEditId').val($btn.attr('data-id'));
            setApplyAll(false);
            $('#snStreamsBox').hide();
            $('#snForms').empty();
            addNoteForm({
                title: $btn.attr('data-title') || '',
                currentFile: $btn.attr('data-filename') || '',
                fileRequired: false
            });
            $('#snSaveBtn').text('Update');
            $('#sn-upload-tab').tab('show');
        });

        $(document).off('click.sn', '.sn-del-btn').on('click.sn', '.sn-del-btn', function() {
            const id = $(this).attr('data-id');
            Swal.fire({
                title: 'Delete note?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: @json(url('subject-notes')) + '/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': SN_URLS.csrf }
                }).done(function(res) {
                    Swal.fire('Deleted', res.message || 'Deleted successfully', 'success');
                    loadNotes();
                }).fail(function(xhr) {
                    Swal.fire('Error', (xhr.responseJSON && xhr.responseJSON.error) || 'Failed', 'error');
                });
            });
        });

        $('#subjectNoteViewModal').off('hidden.bs.modal.sn').on('hidden.bs.modal.sn', function() {
            $('#snViewFrame').attr('src', '');
        });
    }

    window.openSubjectNotesModal = window.openSubjectNotesModal || function(classSubjectID, subjectName, kind) {
        const ready = window.jQuery;
        if (!ready) {
            alert('Please wait, page is still loading...');
            return;
        }
        bootSn(ready);
        if (typeof window.openSubjectNotesModal === 'function') {
            window.openSubjectNotesModal(classSubjectID, subjectName, kind);
        }
    };

    function tryBoot() {
        if (window.jQuery) {
            bootSn(window.jQuery);
            return;
        }
        setTimeout(tryBoot, 50);
    }
    tryBoot();
})();
</script>
