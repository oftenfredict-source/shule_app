@php
    $kind = $kind ?? 'note';
    $labels = $labels ?? [
        'page' => 'NOTES',
        'hint' => 'Teacher notes (PDF / DOC) for the selected academic year',
        'filter' => 'Filter notes',
        'table' => 'Subjects',
        'count_col' => 'Notes (PDF / DOC)',
        'empty' => 'Select a main class to see subjects.',
        'empty_filter' => 'No subjects found for this filter.',
        'loading' => 'Loading subjects...',
        'no_items' => 'No notes uploaded for this subject.',
        'count_word' => 'note',
        'count_word_plural' => 'notes',
        'icon' => 'fa-sticky-note',
    ];
    $subjectsRoute = $kind === 'past_paper' ? 'admin.past_papers.subjects' : 'admin.notes.subjects';
    $notesListBase = $kind === 'past_paper' ? url('adminPastPapers/subjects') : url('adminNotes/subjects');
@endphp
@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .table, .form-control, .modal-content {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
    }
    .sn-admin-page {
        width: 100%;
        float: left;
    }
    .bg-primary-custom {
        background: #940000 !important;
        color: #fff !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
        border-radius: 0;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #fff;
    }
    .sn-admin-page .card,
    .sn-admin-page .form-control,
    .sn-admin-page .btn,
    .modal-content {
        border-radius: 0 !important;
    }
    .sn-admin-page .form-control {
        width: 100%;
        display: block;
    }
    .sn-teacher {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .sn-teacher img {
        width: 42px;
        height: 42px;
        object-fit: cover;
        border: 1px solid #eadada;
        background: #fff5f5;
        flex-shrink: 0;
    }
    .sn-count {
        background: #fff5f5;
        color: #940000;
        border: 1px solid #eadada;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 13px;
        display: inline-block;
    }
    .sn-note-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f0e4e4;
    }
    .sn-note-row:last-child { border-bottom: 0; }
    .sn-note-body { flex: 1; min-width: 0; }
    .sn-note-title { font-weight: 700; color: #222; }
    .sn-note-meta { font-size: 12px; color: #777; }
</style>

<div class="content mt-3 sn-admin-page">
    <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
        <div class="card-body">
            <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">{{ $labels['page'] }}</h4>
            <p class="text-muted mb-0 small" style="font-weight: 600;">{{ $labels['hint'] }}: {{ $yearLabel }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary-custom text-white">
            <strong><i class="fa fa-filter"></i> {{ $labels['filter'] }}</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Main class</label>
                        <select id="snClass" class="form-control">
                            <option value="">Select class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Subclass</label>
                        <select id="snSubclass" class="form-control" disabled>
                            <option value="">All subclasses</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Status</label>
                        <select id="snStatus" class="form-control">
                            <option value="">All</option>
                            <option value="Required">Required</option>
                            <option value="Optional">Optional</option>
                        </select>
                    </div>
                </div>
            </div>
            <p class="text-muted mb-0 mt-3 small">Select a class to load subjects, the teacher, and how many {{ $labels['count_word_plural'] }} they uploaded.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <strong><i class="fa {{ $labels['icon'] }}"></i> {{ $labels['table'] }}</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="snSubjectsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Subject</th>
                            <th>Subclass</th>
                            <th>Status</th>
                            <th>Teacher</th>
                            <th>{{ $labels['count_col'] }}</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ $labels['empty'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="snAdminNotesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="snAdminNotesTitle">Notes</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="snAdminNotesBody">
                <div class="text-center text-muted py-4">Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="snAdminViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="snAdminViewTitle">View Note</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" style="height:75vh;">
                <iframe id="snAdminViewFrame" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-outline-primary" id="snAdminViewDownload" target="_blank">Download</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function($) {
    const subjectsUrl = @json(route($subjectsRoute));
    const notesUrlBase = @json($notesListBase);
    const L = @json($labels);
    let lastSubclasses = [];

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function emptyRow(text) {
        return '<tr><td colspan="6" class="text-center text-muted py-4">' + escapeHtml(text) + '</td></tr>';
    }

    function fillSubclasses(items, keepValue) {
        const current = keepValue ? String($('#snSubclass').val() || '') : '';
        let html = '<option value="">All subclasses</option>';
        (items || []).forEach(function(s) {
            html += '<option value="' + escapeHtml(s.subclassID) + '">' + escapeHtml(s.subclass_name) + '</option>';
        });
        $('#snSubclass').html(html);
        if (current && $('#snSubclass option[value="' + current + '"]').length) {
            $('#snSubclass').val(current);
        }
        $('#snSubclass').prop('disabled', false);
    }

    function loadSubjects() {
        const classID = $('#snClass').val();
        if (!classID) {
            $('#snSubclass').html('<option value="">All subclasses</option>').prop('disabled', true);
            $('#snSubjectsTable tbody').html(emptyRow(L.empty));
            return;
        }
        $('#snSubjectsTable tbody').html(emptyRow(L.loading));
        $.get(subjectsUrl, {
            classID: classID,
            subclassID: $('#snSubclass').val() || '',
            student_status: $('#snStatus').val() || ''
        }).done(function(res) {
            lastSubclasses = res.subclasses || [];
            fillSubclasses(lastSubclasses, true);
            const rows = res.subjects || [];
            if (!rows.length) {
                $('#snSubjectsTable tbody').html(emptyRow(L.empty_filter));
                return;
            }
            let html = '';
            rows.forEach(function(row) {
                const count = parseInt(row.notes_count, 10) || 0;
                const ids = (row.class_subjectIDs && row.class_subjectIDs.length)
                    ? row.class_subjectIDs.join(',')
                    : String(row.class_subjectID || '');
                const title = (row.subject_name || 'Subject')
                    + (row.subclass_name ? ' · ' + row.subclass_name : '');
                html += '<tr>'
                    + '<td><strong>' + escapeHtml(row.subject_name) + '</strong>'
                    + (row.subject_code ? '<br><small class="text-muted">' + escapeHtml(row.subject_code) + '</small>' : '')
                    + '</td>'
                    + '<td>' + escapeHtml(row.subclass_name) + '</td>'
                    + '<td><span class="badge ' + (row.student_status === 'Optional' ? 'badge-info' : 'badge-secondary') + '">' + escapeHtml(row.student_status) + '</span></td>'
                    + '<td><div class="sn-teacher"><img src="' + escapeHtml(row.teacher_photo) + '" alt="">'
                    + '<div>' + escapeHtml(row.teacher_name) + '</div></div></td>'
                    + '<td><span class="sn-count">' + count + ' ' + (count === 1 ? L.count_word : L.count_word_plural) + '</span></td>'
                    + '<td><button type="button" class="btn btn-sm btn-primary-custom sn-view-btn" data-id="' + escapeHtml(row.class_subjectID) + '" data-ids="' + escapeHtml(ids) + '" data-name="' + escapeHtml(title) + '"><i class="fa fa-eye"></i> View</button></td>'
                    + '</tr>';
            });
            $('#snSubjectsTable tbody').html(html);
        }).fail(function(xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Failed to load subjects.';
            $('#snSubjectsTable tbody').html(emptyRow(msg));
        });
    }

    $('#snClass').on('change', function() {
        $('#snSubclass').val('');
        loadSubjects();
    });
    $('#snSubclass, #snStatus').on('change', function() {
        if ($('#snClass').val()) {
            loadSubjects();
        }
    });

    $(document).on('click', '.sn-view-btn', function() {
        const id = $(this).attr('data-id');
        const ids = $(this).attr('data-ids') || id;
        const name = $(this).attr('data-name') || 'Notes';
        $('#snAdminNotesTitle').text(name);
        $('#snAdminNotesBody').html('<div class="text-center text-muted py-4">Loading notes...</div>');
        $('#snAdminNotesModal').modal('show');
        $.get(notesUrlBase + '/' + id + '/notes', { ids: ids }).done(function(res) {
            const notes = res.notes || [];
            if (res.streams_label) {
                $('#snAdminNotesTitle').text((res.subject_name || name) + ' · ' + res.streams_label);
            }
            if (!notes.length) {
                $('#snAdminNotesBody').html('<div class="alert alert-light border mb-0">' + escapeHtml(L.no_items) + '</div>');
                return;
            }
            let html = '';
            notes.forEach(function(n) {
                const streamText = (n.streams && n.streams.length) ? n.streams.join(', ') : '';
                html += '<div class="sn-note-row">'
                    + '<div class="sn-note-body">'
                    + '<div class="sn-note-title">' + escapeHtml(n.title) + '</div>'
                    + '<div class="sn-note-meta">' + escapeHtml(n.file_kind || 'FILE')
                    + (n.original_filename ? ' · ' + escapeHtml(n.original_filename) : '')
                    + (streamText ? ' · ' + escapeHtml(streamText) : '')
                    + (n.created_at ? ' · ' + escapeHtml(n.created_at) : '')
                    + '</div></div>'
                    + '<button type="button" class="btn btn-sm btn-outline-primary sn-open-note" data-url="' + escapeHtml(n.view_url) + '" data-dl="' + escapeHtml(n.download_url) + '" data-title="' + escapeHtml(n.title) + '"><i class="fa fa-eye"></i> View</button>'
                    + '<a class="btn btn-sm btn-primary-custom" href="' + escapeHtml(n.download_url) + '"><i class="fa fa-download"></i> Download</a>'
                    + '</div>';
            });
            $('#snAdminNotesBody').html(html);
        }).fail(function() {
            $('#snAdminNotesBody').html('<div class="alert alert-danger mb-0">Failed to load notes.</div>');
        });
    });

    $(document).on('click', '.sn-open-note', function() {
        const url = $(this).data('url');
        const dl = $(this).data('dl');
        const title = $(this).data('title') || 'View Note';
        $('#snAdminViewTitle').text(title);
        $('#snAdminViewFrame').attr('src', url);
        $('#snAdminViewDownload').attr('href', dl);
        $('#snAdminViewModal').modal('show');
    });

    $('#snAdminViewModal').on('hidden.bs.modal', function() {
        $('#snAdminViewFrame').attr('src', '');
    });
})(jQuery);
</script>

@include('includes.footer')
