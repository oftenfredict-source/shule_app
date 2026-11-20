@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.logbook').' - ShuleXpert')
@section('page_title', __('teacher.pages.logbook'))
@section('page_breadcrumb', __('teacher.pages.logbook_bc'))

@section('page_actions')
    <button type="button" class="btn btn-primary btn-sm waves-effect text-white" id="addEntryBtn" style="display:none;" onclick="openAddEntryModal()">
        <i class="fa fa-plus me-1 text-white"></i> <span class="text-white">{{ __('teacher.logbook.add_entry') }}</span>
    </button>
@endsection

@push('styles')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-logbook,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-logbook p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-logbook small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-logbook strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-logbook label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-logbook td {
        color: #000 !important;
    }
    .sx-teacher-logbook .sx-lb-toolbar,
    .sx-teacher-logbook .sx-lb-meta,
    .sx-teacher-logbook .sx-lb-table-wrap,
    .sx-teacher-logbook .sx-lb-empty {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
        border-top: 4px solid #940000 !important;
    }
    .sx-teacher-logbook .sx-lb-toolbar {
        padding: 0.9rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    .sx-teacher-logbook .form-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.3rem;
    }
    .sx-teacher-logbook .form-select,
    .sx-teacher-logbook .form-control,
    .sx-teacher-logbook .btn,
    #entryModal .form-control,
    #entryModal .btn,
    #entryModal .modal-content {
        border-radius: 0 !important;
    }
    .sx-teacher-logbook .form-select {
        min-height: 46px;
        font-weight: 600;
        border: 1px solid #cfcfcf;
    }
    .sx-teacher-logbook .form-select:focus,
    #entryModal .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12);
    }
    .sx-teacher-logbook .btn-dark-custom {
        background: #2f2f2f !important;
        border: none !important;
        color: #fff !important;
        font-weight: 800;
        min-height: 46px;
    }
    .sx-teacher-logbook .btn-dark-custom:hover {
        background: #1a1a1a !important;
        color: #fff !important;
    }
    .sx-teacher-logbook .btn-primary-custom,
    #entryModal .btn-primary-custom,
    .btn.btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 800;
    }
    .sx-teacher-logbook .btn-primary-custom:hover,
    #entryModal .btn-primary-custom:hover,
    .btn.btn-primary:hover,
    .btn.btn-primary *,
    .btn-primary-custom *,
    #entryModal .btn-primary-custom * {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    .sx-teacher-logbook .btn-primary-custom:hover,
    #entryModal .btn-primary-custom:hover,
    .btn.btn-primary:hover {
        background: #7b0000 !important;
        border-color: #7b0000 !important;
    }
    .sx-teacher-logbook .sx-lb-meta {
        padding: 0.85rem 1rem;
        margin-bottom: 0.85rem;
        background: #fff5f5 !important;
    }
    .sx-teacher-logbook .sx-lb-meta small {
        display: block;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.2rem;
    }
    .sx-teacher-logbook .sx-lb-meta span {
        font-weight: 800;
        color: #940000 !important;
    }
    .sx-teacher-logbook .sx-lb-table-wrap {
        overflow: hidden;
        margin-bottom: 0.85rem;
    }
    .sx-teacher-logbook #logbookTable thead th {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        border-color: #7b0000 !important;
        white-space: nowrap;
        vertical-align: middle;
    }
    .sx-teacher-logbook #logbookTable td {
        font-size: 0.82rem;
        vertical-align: middle;
        border-color: #eee !important;
    }
    .sx-teacher-logbook .sx-lb-empty {
        padding: 2.5rem 1.25rem;
        text-align: center;
    }
    .sx-teacher-logbook .sx-lb-empty i {
        color: #940000;
        font-size: 2.4rem;
        margin-bottom: 0.75rem;
    }
    .sx-teacher-logbook .sx-lb-empty h3 {
        font-size: 1.05rem;
        font-weight: 800;
        color: #940000 !important;
        margin-bottom: 0.4rem;
    }
    .sx-teacher-logbook .sx-lb-empty p {
        max-width: 520px;
        margin: 0 auto;
        color: #666 !important;
        font-weight: 600;
        font-size: 0.88rem;
    }
    #entryModal .modal-header {
        background: #fff5f5;
        border-bottom: 2px solid #940000;
        border-radius: 0 !important;
    }
    #entryModal .modal-title {
        color: #940000 !important;
        font-weight: 800;
        letter-spacing: 0.04em;
    }
    #entryModal .form-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
    }
    .badge-success {
        background: #198754 !important;
        color: #fff !important;
    }
    .badge-secondary {
        background: #6c757d !important;
        color: #fff !important;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-teacher-logbook .sx-lb-toolbar,
    html[data-bs-theme="dark"] .sx-teacher-logbook .sx-lb-toolbar,
    html[data-sx-theme="dark"] .sx-teacher-logbook .sx-lb-table-wrap,
    html[data-bs-theme="dark"] .sx-teacher-logbook .sx-lb-table-wrap,
    html[data-sx-theme="dark"] .sx-teacher-logbook .sx-lb-empty,
    html[data-bs-theme="dark"] .sx-teacher-logbook .sx-lb-empty {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook .sx-lb-meta,
    html[data-bs-theme="dark"] .sx-teacher-logbook .sx-lb-meta {
        background: #2a1c20 !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook .form-label,
    html[data-bs-theme="dark"] .sx-teacher-logbook .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook .form-select,
    html[data-bs-theme="dark"] .sx-teacher-logbook .form-select,
    html[data-sx-theme="dark"] .sx-teacher-logbook .form-control,
    html[data-bs-theme="dark"] .sx-teacher-logbook .form-control {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-logbook .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook .sx-lb-meta small,
    html[data-bs-theme="dark"] .sx-teacher-logbook .sx-lb-meta small {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook .sx-lb-meta span,
    html[data-bs-theme="dark"] .sx-teacher-logbook .sx-lb-meta span {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook #logbookTable,
    html[data-bs-theme="dark"] .sx-teacher-logbook #logbookTable {
        color: #f3e9eb !important;
        --bs-table-bg: transparent;
        --bs-table-color: #f3e9eb;
        --bs-table-striped-bg: #1c1417;
        --bs-table-hover-bg: #2a1c20;
        --bs-table-border-color: #3a2a2e;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook #logbookTable td,
    html[data-bs-theme="dark"] .sx-teacher-logbook #logbookTable td {
        color: #f3e9eb !important;
        border-color: #3a2a2e !important;
        background: #24181c !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook #logbookTable thead th,
    html[data-bs-theme="dark"] .sx-teacher-logbook #logbookTable thead th {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook .sx-lb-empty i,
    html[data-bs-theme="dark"] .sx-teacher-logbook .sx-lb-empty i,
    html[data-sx-theme="dark"] .sx-teacher-logbook .sx-lb-empty h3,
    html[data-bs-theme="dark"] .sx-teacher-logbook .sx-lb-empty h3 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook .sx-lb-empty p,
    html[data-bs-theme="dark"] .sx-teacher-logbook .sx-lb-empty p {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #entryModal .modal-content,
    html[data-bs-theme="dark"] #entryModal .modal-content {
        background: #24181c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #entryModal .modal-header,
    html[data-bs-theme="dark"] #entryModal .modal-header {
        background: #2a1c20 !important;
        border-bottom-color: #940000 !important;
    }
    html[data-sx-theme="dark"] #entryModal .modal-title,
    html[data-bs-theme="dark"] #entryModal .modal-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #entryModal .modal-body,
    html[data-bs-theme="dark"] #entryModal .modal-body,
    html[data-sx-theme="dark"] #entryModal .modal-footer,
    html[data-bs-theme="dark"] #entryModal .modal-footer {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #entryModal .form-label,
    html[data-bs-theme="dark"] #entryModal .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #entryModal .form-control,
    html[data-bs-theme="dark"] #entryModal .form-control,
    html[data-sx-theme="dark"] #entryModal .form-select,
    html[data-bs-theme="dark"] #entryModal .form-select {
        background: #24181c !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-logbook .btn-dark-custom,
    html[data-bs-theme="dark"] .sx-teacher-logbook .btn-dark-custom,
    html[data-sx-theme="dark"] .sx-teacher-logbook .btn-dark-custom *,
    html[data-bs-theme="dark"] .sx-teacher-logbook .btn-dark-custom * {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
</style>
@endpush

@section('content')
<div class="sx-teacher-logbook">
    <div class="sx-lb-toolbar">
        <div class="row g-2 align-items-end">
            <div class="col-md-8 col-lg-9">
                <label class="form-label" for="subjectSelect">{{ __('teacher.subject') }}</label>
                <select id="subjectSelect" class="form-select">
                    <option value="">{{ __('teacher.logbook.choose_subject') }}</option>
                    @foreach($teachingSubjects as $subject)
                        @php
                            $subjectName = $subject->subject->subject_name ?? 'N/A';
                            $className = $subject->subclass?->class?->class_name
                                ?? $subject->class?->class_name
                                ?? '';
                            $subclassName = $subject->subclass?->subclass_name ?? '';
                            $classLabel = trim($className . ' ' . $subclassName);
                        @endphp
                        <option value="{{ $subject->class_subjectID }}">
                            {{ $subjectName }}@if($classLabel !== '') - {{ $classLabel }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3">
                <button type="button" class="btn btn-dark-custom w-100 text-white" id="loadLogbookBtn">
                    <i class="fa fa-search me-1"></i> {{ __('teacher.logbook.view_logbook') }}
                </button>
            </div>
        </div>
    </div>

    <div id="logbookTableContainer" style="display: none;">
        <div class="sx-lb-meta">
            <div class="row text-center g-2">
                <div class="col-6 col-md-3">
                    <small>{{ __('teacher.subject') }}</small>
                    <span id="headerSubject"></span>
                </div>
                <div class="col-6 col-md-3">
                    <small>{{ __('teacher.class') }}</small>
                    <span id="headerClass"></span>
                </div>
                <div class="col-6 col-md-3">
                    <small>{{ __('teacher.year') }}</small>
                    <span id="headerYear"></span>
                </div>
                <div class="col-6 col-md-3">
                    <small>{{ __('teacher.logbook.teacher') }}</small>
                    <span id="headerTeacher"></span>
                </div>
            </div>
        </div>

        <div class="sx-lb-table-wrap">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="logbookTable">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 50px;">{{ __('teacher.logbook.sn') }}</th>
                            <th>{{ __('teacher.logbook.main_topic') }}</th>
                            <th>{{ __('teacher.logbook.sub_topic') }}</th>
                            <th>{{ __('teacher.logbook.content_covered') }}</th>
                            <th>{{ __('teacher.logbook.date_started') }}</th>
                            <th>{{ __('teacher.logbook.date_ended') }}</th>
                            <th>{{ __('teacher.logbook.teacher_comment') }}</th>
                            <th>{{ __('teacher.logbook.hod_comment') }}</th>
                            <th>{{ __('teacher.logbook.hm_comment') }}</th>
                        </tr>
                    </thead>
                    <tbody id="logbookBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="sx-lb-empty" id="welcomePlaceholder">
        <i class="fa fa-book"></i>
        <h3>{{ __('teacher.logbook.empty_title') }}</h3>
        <p>{{ __('teacher.logbook.empty_body') }}</p>
    </div>
</div>

{{-- Add Entry Modal --}}
<div class="modal fade" id="entryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-0">
            <div class="modal-header p-3">
                <div>
                    <h5 class="modal-title mb-1">{{ __('teacher.logbook.modal_title') }}</h5>
                    <p class="text-muted small mb-0 fw-semibold">{{ __('teacher.logbook.modal_sub') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <form id="entryForm">
                @csrf
                <input type="hidden" name="class_subjectID" id="formClassSubjectID">
                <div class="modal-body p-3" style="max-height: 450px; overflow-y: auto;">
                    <div class="row g-3 mb-1">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher.logbook.main_topic') }}</label>
                            <input type="text" name="main_topic" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher.logbook.sub_topic') }}</label>
                            <input type="text" name="sub_topic" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('teacher.logbook.content_covered') }}</label>
                            <textarea name="content_covered" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher.logbook.date_started') }}</label>
                            <input type="date" name="date_started" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher.logbook.date_ended') }}</label>
                            <input type="date" name="date_ended" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('teacher.logbook.teacher_comment') }}</label>
                            <textarea name="teacher_comment" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn px-4 fw-bold" data-bs-dismiss="modal" style="background:#f0f0f0;color:#2f2f2f;">{{ __('teacher.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-custom px-4 text-white">
                        <span class="text-white">{{ __('teacher.logbook.save_entry') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.sxShowModal = function(el) {
    var node = typeof el === 'string' ? document.querySelector(el) : (el && el.jquery ? el[0] : el);
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('show');
};
window.sxHideModal = function(el) {
    var node = typeof el === 'string' ? document.querySelector(el) : (el && el.jquery ? el[0] : el);
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        var inst = bootstrap.Modal.getInstance(node) || bootstrap.Modal.getOrCreateInstance(node);
        inst.hide();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('hide');
};

(function ($) {
    var i18n = @json(__('teacher.logbook'));
    i18n.pending = @json(__('teacher.pending'));

    $(document).ready(function () {
        $('#loadLogbookBtn').on('click', function () {
            const classSubjectID = $('#subjectSelect').val();
            if (!classSubjectID) {
                Swal.fire({
                    icon: 'warning',
                    title: i18n.selection_required,
                    text: i18n.select_subject_first,
                    confirmButtonColor: '#940000'
                });
                return;
            }
            loadLogbook(classSubjectID);
        });

        $('#subjectSelect').on('change', function () {
            if ($(this).val()) {
                loadLogbook($(this).val());
            } else {
                $('#logbookTableContainer').hide();
                $('#welcomePlaceholder').show();
                $('#addEntryBtn').hide();
            }
        });

        $('#entryForm').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: "{{ route('teacher.save_logbook_entry') }}",
                type: 'POST',
                data: formData,
                beforeSend: function () {
                    Swal.fire({
                        title: i18n.saving,
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                },
                success: function (response) {
                    Swal.close();
                    if (response.success) {
                        sxHideModal('#entryModal');
                        $('#entryForm')[0].reset();
                        loadLogbook($('#subjectSelect').val());
                        Swal.fire({
                            icon: 'success',
                            title: i18n.saved,
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire(i18n.error, response.message, 'error');
                    }
                },
                error: function () {
                    Swal.close();
                    Swal.fire(i18n.error, i18n.something_wrong, 'error');
                }
            });
        });
    });

    window.loadLogbook = function (classSubjectID) {
        $.ajax({
            url: "{{ url('teacher/get-logbook-data') }}/" + classSubjectID,
            type: 'GET',
            beforeSend: function () {
                $('#welcomePlaceholder').hide();
                $('#logbookTableContainer').hide();
            },
            success: function (response) {
                if (response.success) {
                    $('#headerSubject').text(response.subjectName);
                    $('#headerClass').text(response.className + ' ' + response.subclassName);
                    $('#headerYear').text(response.year);
                    $('#headerTeacher').text(response.teacherName);
                    $('#formClassSubjectID').val(classSubjectID);

                    let rows = '';
                    if (response.logbookEntries.length > 0) {
                        response.logbookEntries.forEach((entry, index) => {
                            let hodStatus = entry.hod_comment
                                ? `<span class="badge badge-success" style="font-size: 10px;"><i class="fa fa-check"></i> ${i18n.commented}</span><div class="mt-1 small text-dark">${entry.hod_comment}</div>`
                                : `<span class="badge badge-secondary" style="font-size: 10px;">${i18n.pending}</span>`;

                            let hmStatus = entry.hm_comment
                                ? `<span class="badge badge-success" style="font-size: 10px;"><i class="fa fa-check"></i> ${i18n.commented}</span><div class="mt-1 small text-dark">${entry.hm_comment}</div>`
                                : `<span class="badge badge-secondary" style="font-size: 10px;">${i18n.pending}</span>`;

                            rows += `
                                <tr class="text-center">
                                    <td class="fw-bold">${index + 1}</td>
                                    <td>${entry.main_topic || ''}</td>
                                    <td>${entry.sub_topic || ''}</td>
                                    <td>${entry.content_covered || ''}</td>
                                    <td>${entry.date_started || ''}</td>
                                    <td>${entry.date_ended || ''}</td>
                                    <td>
                                        <span class="badge badge-success" style="font-size: 10px;"><i class="fa fa-check"></i> ${i18n.signed}</span>
                                        <div class="mt-1 small text-dark">${entry.teacher_comment || ''}</div>
                                    </td>
                                    <td>${hodStatus}</td>
                                    <td>${hmStatus}</td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = '<tr><td colspan="9" class="text-center py-5 text-muted fw-bold">' + i18n.no_entries + '</td></tr>';
                    }

                    $('#logbookBody').html(rows);
                    $('#logbookTableContainer').fadeIn();
                    $('#addEntryBtn').show();
                } else {
                    Swal.fire(i18n.error, response.message, 'error');
                }
            }
        });
    };

    window.openAddEntryModal = function () {
        sxShowModal('#entryModal');
    };
})(jQuery);
</script>
@endpush
