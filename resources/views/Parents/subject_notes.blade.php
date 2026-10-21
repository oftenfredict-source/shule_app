@extends('layouts.parent_corp')

@section('title', (($kind ?? 'note') === 'past_paper' ? __('parent.past_papers') : __('parent.notes')) . ' - ShuleXpert')

@section('content')
@php
    $kind = $kind ?? 'note';
    $isPaper = $kind === 'past_paper';
    $t = [
        'page' => $isPaper ? __('parent.past_papers') : __('parent.notes'),
        'pick_child' => __('parent.pick_child'),
        'no_child' => __('parent.no_child'),
        'notes' => $isPaper ? __('parent.past_papers_count_label') : __('parent.notes_count_label'),
        'back' => __('parent.back'),
        'subjects' => __('parent.subjects'),
        'loading' => __('parent.loading'),
        'fail' => __('parent.load_failed'),
        'no_notes' => $isPaper ? __('parent.no_past_papers') : __('parent.no_notes'),
        'view' => __('parent.view'),
        'title' => __('parent.title'),
        'file' => __('parent.file'),
        'teacher' => __('parent.teacher'),
        'close' => __('parent.close'),
        'download' => __('parent.download'),
        'icon' => $isPaper ? 'bx-file' : 'bx-note',
    ];
@endphp
<style>
    .pn-page,
    .pn-list,
    .pn-widget {
        width: 100%;
        max-width: none;
    }
    .pn-section-title {
        font-size: 14px;
        font-weight: 800;
        color: #940000;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 16px;
        border-bottom: 2px solid rgba(148,0,0,0.1);
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .pn-widget {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        padding: 16px 20px;
        margin-bottom: 12px;
        cursor: pointer;
    }
    .pn-widget:hover { background: #fff8f8; }
    .pn-icon {
        width: 44px;
        height: 44px;
        background: #fff5f5;
        color: #940000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        overflow: hidden;
        border: 1px solid #eadada;
    }
    .pn-icon img { width: 100%; height: 100%; object-fit: cover; }
    .pn-body { min-width: 0; flex: 1; }
    .pn-name { font-weight: 800; color: #222; font-size: 16px; }
    .pn-meta { font-size: 12px; color: #777; }
    .pn-count-unread {
        background: #ff0000;
        color: #fff;
        min-width: 24px;
        height: 24px;
        padding: 0 7px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        flex-shrink: 0;
    }
    .pn-chevron { color: #940000; font-size: 22px; flex-shrink: 0; }
    .pn-back { color: #940000; font-weight: 700; text-decoration: none; }
    .pn-back:hover { color: #b30000; }
    .pn-settings {
        background: #fff;
        border: 1px solid #e6e6e6;
        width: 100%;
    }
    .pn-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-bottom: 1px solid #eee;
        background: #fff;
    }
    .pn-row:last-child { border-bottom: 0; }
    @media (min-width: 992px) {
        .pn-page,
        .pn-list,
        .pn-widget {
            width: 100% !important;
            max-width: none !important;
        }
    }
</style>

<div class="pn-page">
    <div class="pn-section-title">
        <i class="bx {{ $t['icon'] }}"></i> {{ $t['page'] }}
    </div>
    <p class="text-muted mb-3" id="pnStepHint">{{ $t['pick_child'] }}</p>
    <div id="pnBackWrap" class="mb-2" style="display:none;">
        <a href="#" class="pn-back" id="pnBackBtn"><i class="bx bx-chevron-left"></i> {{ $t['back'] }}</a>
    </div>
    <div class="pn-list" id="pnList"></div>
</div>

<div class="modal fade" id="parentNotesListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="parentNotesListTitle">{{ $t['page'] }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="parentNotesListBody">
                <div class="text-center text-muted py-4">{{ $t['loading'] }}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $t['close'] }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function($) {
    const T = @json($t);
    const children = @json($children);
    const subjectsUrl = @json($isPaper ? url('parentPastPapers') : url('parentNotes'));
    let step = 'children';
    let currentStudent = null;

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function unreadBadge(n) {
        const v = parseInt(n, 10) || 0;
        if (v < 1) return '';
        return '<span class="pn-count-unread" title="Unread">' + v + '</span>';
    }

    function notesMeta(count) {
        const v = parseInt(count, 10) || 0;
        return v + ' ' + T.notes;
    }

    function renderChildren() {
        step = 'children';
        currentStudent = null;
        $('#pnStepHint').text(T.pick_child);
        $('#pnBackWrap').hide();
        if (!children.length) {
            $('#pnList').html('<div class="p-4 text-muted">' + escapeHtml(T.no_child) + '</div>');
            return;
        }
        let html = '';
        children.forEach(function(c) {
            const photo = c.photo
                ? '<img src="' + escapeHtml(c.photo) + '" alt="">'
                : '<i class="bx bx-user"></i>';
            html += '<div class="pn-widget pn-child" data-id="' + c.studentID + '">'
                + '<div class="pn-icon">' + photo + '</div>'
                + '<div class="pn-body"><div class="pn-name">' + escapeHtml(c.full_name) + '</div>'
                + '<div class="pn-meta">' + escapeHtml(c.class_label || '')
                + (c.admission_number ? ' · ' + escapeHtml(c.admission_number) : '')
                + ' · ' + notesMeta(c.notes_count)
                + '</div></div>'
                + unreadBadge(c.unread_count)
                + '<i class="bx bx-chevron-right pn-chevron"></i>'
                + '</div>';
        });
        $('#pnList').html(html);
    }

    function renderSubjects(student, subjects) {
        step = 'subjects';
        currentStudent = student;
        currentStudent.subjects = subjects;
        $('#pnStepHint').text((student.full_name || '') + ' · ' + T.subjects);
        $('#pnBackWrap').show();
        if (!subjects.length) {
            $('#pnList').html('<div class="p-4 text-muted">' + escapeHtml(T.no_notes) + '</div>');
            return;
        }
        let html = '';
        subjects.forEach(function(s) {
            html += '<div class="pn-widget pn-subject" data-id="' + s.class_subjectID + '" data-name="' + escapeHtml(s.subject_name) + '" data-unread="' + (parseInt(s.unread_count, 10) || 0) + '">'
                + '<div class="pn-icon"><i class="bx bx-book"></i></div>'
                + '<div class="pn-body"><div class="pn-name">' + escapeHtml(s.subject_name) + '</div>'
                + '<div class="pn-meta">' + escapeHtml(s.teacher_name || '')
                + (s.subject_code ? ' · ' + escapeHtml(s.subject_code) : '')
                + ' · ' + notesMeta(s.notes_count)
                + '</div></div>'
                + unreadBadge(s.unread_count)
                + '<i class="bx bx-chevron-right pn-chevron"></i>'
                + '</div>';
        });
        $('#pnList').html(html);
    }

    $(document).on('click', '.pn-child', function() {
        const id = $(this).data('id');
        $('#pnList').html('<div class="p-4 text-muted">' + escapeHtml(T.loading) + '</div>');
        $.get(subjectsUrl + '/' + id + '/subjects')
            .done(function(res) {
                renderSubjects(res.student || {}, res.subjects || []);
            })
            .fail(function() {
                $('#pnList').html('<div class="p-4 text-danger">' + escapeHtml(T.fail) + '</div>');
            });
    });

    $('#pnBackBtn').on('click', function(e) {
        e.preventDefault();
        renderChildren();
    });

    $(document).on('click', '.pn-subject', function() {
        if (!currentStudent) return;
        const csId = $(this).data('id');
        const name = $(this).data('name') || 'Notes';
        $('#parentNotesListTitle').text(name);
        $('#parentNotesListBody').html('<div class="text-center text-muted py-4">' + escapeHtml(T.loading) + '</div>');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('parentNotesListModal')).show();
        const unreadOpened = parseInt($(this).attr('data-unread'), 10) || 0;
        $.get(subjectsUrl + '/' + currentStudent.studentID + '/subjects/' + csId + '/notes')
            .done(function(res) {
                if (unreadOpened > 0) {
                    const $row = $('.pn-subject[data-id="' + csId + '"]');
                    $row.attr('data-unread', 0).find('.pn-count-unread').remove();
                    if (currentStudent.subjects) {
                        currentStudent.subjects.forEach(function(s) {
                            if (String(s.class_subjectID) === String(csId)) s.unread_count = 0;
                        });
                    }
                    const child = children.find(function(c) { return String(c.studentID) === String(currentStudent.studentID); });
                    if (child) {
                        child.unread_count = Math.max(0, (parseInt(child.unread_count, 10) || 0) - unreadOpened);
                    }
                }
                const rows = res.notes || [];
                if (!rows.length) {
                    $('#parentNotesListBody').html('<div class="alert alert-light border mb-0">' + escapeHtml(T.no_notes) + '</div>');
                    return;
                }
                let html = '<div class="pn-settings">';
                rows.forEach(function(n) {
                    html += '<div class="pn-row" style="cursor:default;">'
                        + '<div class="pn-icon"><i class="bx bx-file"></i></div>'
                        + '<div class="pn-body"><div class="pn-name">' + escapeHtml(n.title) + '</div>'
                        + '<div class="pn-meta">' + escapeHtml(n.original_filename || '')
                        + (n.teacher_name ? ' · ' + escapeHtml(n.teacher_name) : '')
                        + '</div></div>'
                        + '<button type="button" class="btn btn-sm btn-outline-primary parent-note-view" data-url="' + escapeHtml(n.view_url) + '" data-dl="' + escapeHtml(n.download_url) + '" data-title="' + escapeHtml(n.title) + '"><i class="bx bx-show"></i> ' + escapeHtml(T.view) + '</button>'
                        + '<a class="btn btn-sm btn-primary" href="' + escapeHtml(n.download_url) + '" title="' + escapeHtml(T.download) + '"><i class="bx bx-download"></i></a>'
                        + '</div>';
                });
                html += '</div>';
                $('#parentNotesListBody').html(html);
            })
            .fail(function() {
                $('#parentNotesListBody').html('<div class="alert alert-danger mb-0">' + escapeHtml(T.fail) + '</div>');
            });
    });

    $(document).on('click', '.parent-note-view', function() {
        const url = $(this).data('url');
        const dl = $(this).data('dl');
        const title = $(this).data('title') || 'Notes';
        if (window.openParentHomePackageView) {
            window.openParentHomePackageView(url, dl, title);
        }
    });

    renderChildren();
})(jQuery);
</script>
@endpush
