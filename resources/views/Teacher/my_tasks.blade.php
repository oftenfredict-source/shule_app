@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.my_tasks').' - ShuleXpert')
@section('page_title', __('teacher.pages.my_tasks'))
@section('page_breadcrumb', __('teacher.pages.my_tasks_bc'))

@section('page_actions')
    <a href="{{ route('teacher.mySessions') }}" class="btn btn-primary btn-sm waves-effect">
        <i class="fa-solid fa-plus me-1"></i> {{ __('teacher.tasks.assign_from_sessions') }}
    </a>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-tasks,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-tasks p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-tasks small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-tasks strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-tasks td,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-tasks label {
        color: #000 !important;
    }
    .sx-teacher-tasks .card,
    .sx-teacher-tasks .sx-tasks-toolbar {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
        border-top: 4px solid #940000 !important;
    }
    .sx-teacher-tasks .sx-tasks-toolbar {
        padding: 0.9rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    .sx-teacher-tasks .form-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.3rem;
    }
    .sx-teacher-tasks .form-control,
    .sx-teacher-tasks .form-select,
    .sx-teacher-tasks .btn {
        border-radius: 0 !important;
    }
    .sx-teacher-tasks .form-control,
    .sx-teacher-tasks .form-select {
        min-height: 42px;
        font-weight: 600;
        border: 1px solid #cfcfcf;
    }
    .sx-teacher-tasks .form-control:focus,
    .sx-teacher-tasks .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12);
    }
    .sx-teacher-tasks .btn-primary-custom,
    .btn-primary-custom {
        background: #940000;
        border-color: #940000;
        color: #fff;
        font-weight: 800;
    }
    .sx-teacher-tasks .btn-primary-custom:hover,
    .btn-primary-custom:hover {
        background: #7b0000;
        border-color: #7b0000;
        color: #fff;
    }
    .sx-teacher-tasks .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000;
        font-weight: 800;
    }
    .sx-teacher-tasks .btn-outline-primary:hover {
        background: #940000;
        color: #fff !important;
    }
    .sx-teacher-tasks .table.sx-tasks-table {
        margin-bottom: 0;
        background: #fff;
    }
    .sx-teacher-tasks .table.sx-tasks-table thead th {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #940000 !important;
        background: #fcf7f7;
        border-bottom: 1px solid rgba(148, 0, 0, 0.12);
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.65rem 0.75rem;
    }
    .sx-teacher-tasks .table.sx-tasks-table td {
        vertical-align: middle;
        font-size: 0.88rem;
        padding: 0.65rem 0.75rem;
    }
    .sx-teacher-tasks .sx-task-topic {
        font-weight: 800;
        margin: 0;
        line-height: 1.25;
    }
    .sx-teacher-tasks .sx-task-meta {
        font-size: 0.78rem;
        color: #666 !important;
        margin: 0.15rem 0 0;
    }
    .sx-teacher-tasks .status-badge {
        display: inline-block;
        padding: 0.28rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 0 !important;
    }
    .sx-teacher-tasks .status-pending { background: #fff3cd; color: #856404; }
    .sx-teacher-tasks .status-approved { background: #d4edda; color: #155724; }
    .sx-teacher-tasks .status-rejected { background: #f8d7da; color: #721c24; }
    .sx-teacher-tasks .sx-empty {
        border: 1px dashed #ddd;
        background: #fafafa;
        padding: 1.4rem 1rem;
        text-align: center;
        color: #777 !important;
        font-weight: 600;
    }
    .sx-teacher-tasks .sx-empty-sub {
        font-weight: 600;
        color: #888 !important;
    }
    .sx-teacher-tasks .sx-detail-row td {
        background: #faf7f7;
        border-top: none !important;
        padding: 0.85rem 1rem 1rem;
    }
    .sx-teacher-tasks .sx-detail-box {
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.1);
        border-left: 3px solid #940000;
        padding: 0.75rem 0.9rem;
        font-size: 0.88rem;
        white-space: pre-wrap;
    }
    .sx-teacher-tasks .sx-detail-box.comment {
        border-left-color: #ffc107;
        background: #fffdf5;
    }
    .sx-teacher-tasks .sx-detail-label {
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #940000 !important;
        margin-bottom: 0.35rem;
    }

    #editTaskModal .modal-dialog { max-width: 560px; }
    #editTaskModal .modal-content {
        border: none;
        border-radius: 0;
        box-shadow: 0 22px 56px rgba(15, 23, 42, 0.22);
    }
    #editTaskModal .modal-header {
        border: none;
        padding: 1.1rem 1.35rem;
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%);
        color: #fff !important;
    }
    #editTaskModal .modal-title {
        margin: 0;
        font-size: 0.9rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-weight: 800;
        color: #fff !important;
    }
    #editTaskModal .modal-body {
        background: #faf7f7;
        padding: 1.2rem 1.35rem;
    }
    #editTaskModal .form-group { margin-bottom: 0.95rem; }
    #editTaskModal label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #222;
        margin-bottom: 0.35rem;
        display: block;
    }
    #editTaskModal .form-control {
        border-radius: 0 !important;
        min-height: 44px;
        font-weight: 600;
        border: 1px solid #cfcfcf;
    }
    #editTaskModal textarea.form-control { min-height: 110px; }
    #editTaskModal .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12);
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-teacher-tasks .card,
    html[data-bs-theme="dark"] .sx-teacher-tasks .card,
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-tasks-toolbar,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-tasks-toolbar {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .form-label,
    html[data-bs-theme="dark"] .sx-teacher-tasks .form-label,
    html[data-sx-theme="dark"] .sx-teacher-tasks label.form-label,
    html[data-bs-theme="dark"] .sx-teacher-tasks label.form-label {
        color: #ffb4b4 !important;
        -webkit-text-fill-color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .form-control,
    html[data-bs-theme="dark"] .sx-teacher-tasks .form-control,
    html[data-sx-theme="dark"] .sx-teacher-tasks .form-select,
    html[data-bs-theme="dark"] .sx-teacher-tasks .form-select {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #f3e9eb !important;
        -webkit-text-fill-color: #f3e9eb !important;
        color-scheme: dark;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-tasks .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-empty,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-empty {
        background: #2a1c20 !important;
        border-color: rgba(255, 180, 180, 0.22) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-empty i,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-empty i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-empty div,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-empty div,
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-empty-sub,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-empty-sub {
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .table.sx-tasks-table,
    html[data-bs-theme="dark"] .sx-teacher-tasks .table.sx-tasks-table {
        background: #24181c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .table.sx-tasks-table thead th,
    html[data-bs-theme="dark"] .sx-teacher-tasks .table.sx-tasks-table thead th {
        background: #1c1417 !important;
        color: #ffb4b4 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .table.sx-tasks-table td,
    html[data-bs-theme="dark"] .sx-teacher-tasks .table.sx-tasks-table td {
        color: #f3e9eb !important;
        border-color: rgba(255, 180, 180, 0.12) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-task-meta,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-task-meta {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-detail-row td,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-detail-row td {
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-detail-box,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-detail-box {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-detail-box.comment,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-detail-box.comment {
        background: rgba(255, 193, 7, 0.12) !important;
        border-left-color: #ffc107 !important;
        color: #ffe8a3 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .sx-detail-label,
    html[data-bs-theme="dark"] .sx-teacher-tasks .sx-detail-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .status-pending,
    html[data-bs-theme="dark"] .sx-teacher-tasks .status-pending {
        background: #fff3cd !important; color: #6b5300 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .status-approved,
    html[data-bs-theme="dark"] .sx-teacher-tasks .status-approved {
        background: #d4edda !important; color: #155724 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .status-rejected,
    html[data-bs-theme="dark"] .sx-teacher-tasks .status-rejected {
        background: #f8d7da !important; color: #721c24 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-tasks .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-teacher-tasks .btn-outline-primary {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
    }
    html[data-sx-theme="dark"] #editTaskModal .modal-body,
    html[data-bs-theme="dark"] #editTaskModal .modal-body {
        background: #1c1417 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #editTaskModal label,
    html[data-bs-theme="dark"] #editTaskModal label {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #editTaskModal .form-control,
    html[data-bs-theme="dark"] #editTaskModal .form-control {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #editTaskModal .text-muted,
    html[data-bs-theme="dark"] #editTaskModal .text-muted {
        color: #d7c4c8 !important;
    }
</style>
@endpush

@section('content')
<div class="sx-teacher-tasks">
    <div class="sx-tasks-toolbar">
        <form id="filterTasksForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="statusFilter">{{ __('teacher.status') }}</label>
                    <select class="form-select" id="statusFilter" name="status">
                        <option value="">{{ __('teacher.tasks.all_statuses') }}</option>
                        <option value="pending" @selected(request('status') == 'pending')>{{ __('teacher.pending') }}</option>
                        <option value="approved" @selected(request('status') == 'approved')>{{ __('teacher.approved') }}</option>
                        <option value="rejected" @selected(request('status') == 'rejected')>{{ __('teacher.rejected') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="dateFilter">{{ __('teacher.date') }}</label>
                    <input type="date" class="form-control" id="dateFilter" name="date" value="{{ request('date') }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom flex-grow-1">
                        <i class="bi bi-funnel"></i> {{ __('teacher.filter') }}
                    </button>
                    @if(request()->filled('status') || request()->filled('date'))
                        <a href="{{ route('teacher.myTasks') }}" class="btn btn-outline-primary">{{ __('teacher.tasks.clear') }}</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="card border-0">
        <div class="card-body p-0">
            @if($tasks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover sx-tasks-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:130px;">{{ __('teacher.date') }}</th>
                                <th>{{ __('teacher.tasks.subject_class') }}</th>
                                <th>{{ __('teacher.tasks.topic') }}</th>
                                <th style="width:110px;">{{ __('teacher.status') }}</th>
                                <th style="width:160px;" class="text-end">{{ __('teacher.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                @php
                                    $subjectName = $task->sessionTimetable->subject->subject_name
                                        ?? ($task->sessionTimetable->classSubject->subject->subject_name ?? 'N/A');
                                    $className = trim(
                                        ($task->sessionTimetable->subclass->class->class_name ?? 'N/A')
                                        . ' - '
                                        . ($task->sessionTimetable->subclass->subclass_name ?? ''),
                                        ' -'
                                    );
                                    $rowId = 'task-detail-' . $task->session_taskID;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ \Carbon\Carbon::parse($task->task_date)->format('d M Y') }}</strong>
                                        <div class="sx-task-meta">
                                            {{ \Carbon\Carbon::parse($task->sessionTimetable->start_time)->format('h:i A') }}
                                            –
                                            {{ \Carbon\Carbon::parse($task->sessionTimetable->end_time)->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td>
                                        <p class="sx-task-topic">{{ $subjectName }}</p>
                                        <p class="sx-task-meta"><i class="bi bi-people"></i> {{ $className }}</p>
                                    </td>
                                    <td>
                                        <p class="sx-task-topic mb-0">{{ $task->topic ?? 'N/A' }}</p>
                                        @if($task->subtopic)
                                            <p class="sx-task-meta">{{ $task->subtopic }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $task->status }}">
                                            @if($task->status == 'pending')
                                                {{ __('teacher.pending') }}
                                            @elseif($task->status == 'approved')
                                                {{ __('teacher.approved') }}
                                            @else
                                                {{ __('teacher.rejected') }}
                                            @endif
                                        </span>
                                        <div class="sx-task-meta mt-1">
                                            {{ \Carbon\Carbon::parse($task->created_at)->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#{{ $rowId }}" aria-expanded="false">
                                            <i class="bi bi-eye"></i> {{ __('teacher.tasks.details') }}
                                        </button>
                                        @if($task->status == 'rejected')
                                            <button
                                                type="button"
                                                class="btn btn-primary-custom btn-sm mt-1 mt-md-0"
                                                onclick="editTask({{ $task->session_taskID }}, '{{ $task->sessionTimetable->session_timetableID }}', '{{ $task->task_date->format('Y-m-d') }}', '{{ \Carbon\Carbon::parse($task->sessionTimetable->start_time)->format('H:i') }}', '{{ \Carbon\Carbon::parse($task->sessionTimetable->end_time)->format('H:i') }}', '{{ addslashes($task->topic ?? '') }}', '{{ addslashes($task->subtopic ?? '') }}', '{{ addslashes($task->task_description) }}')"
                                            >
                                                <i class="bi bi-pencil"></i> {{ __('teacher.tasks.edit') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="sx-detail-row">
                                    <td colspan="5" class="p-0 border-0">
                                        <div class="collapse" id="{{ $rowId }}">
                                            <div class="p-3">
                                                <div class="sx-detail-label">{{ __('teacher.tasks.description') }}</div>
                                                <div class="sx-detail-box mb-2">{{ $task->task_description }}</div>
                                                @if($task->admin_comment)
                                                    <div class="sx-detail-label">{{ __('teacher.tasks.admin_comment') }}</div>
                                                    <div class="sx-detail-box comment mb-2">{{ $task->admin_comment }}</div>
                                                @endif
                                                @if($task->approved_by && $task->approved_at)
                                                    <div class="sx-task-meta">
                                                        {{ __('teacher.tasks.reviewed_by', [
                                                            'name' => $task->approver->name ?? __('teacher.tasks.admin'),
                                                            'date' => \Carbon\Carbon::parse($task->approved_at)->format('d M Y h:i A'),
                                                        ]) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="sx-empty m-3">
                    <div class="mb-2"><i class="bi bi-info-circle"></i> {{ __('teacher.tasks.no_tasks') }}</div>
                    <div class="mb-3 sx-empty-sub">{{ __('teacher.tasks.no_tasks_sub') }}</div>
                    <a href="{{ route('teacher.mySessions') }}" class="btn btn-primary-custom btn-sm">
                        <i class="bi bi-plus-lg"></i> {{ __('teacher.tasks.go_sessions') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Edit Task Modal --}}
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> {{ __('teacher.tasks.edit_resubmit') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="editTaskForm">
                    <input type="hidden" id="edit_task_id" name="task_id">
                    <input type="hidden" id="edit_session_timetableID" name="session_timetableID">
                    <input type="hidden" id="edit_task_date" name="task_date">

                    <div class="form-group">
                        <label for="edit_task_date_display">{{ __('teacher.date') }} <span class="text-muted">{{ __('teacher.tasks.auto') }}</span></label>
                        <input type="text" class="form-control" id="edit_task_date_display" readonly style="background:#f5f5f5;cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label for="edit_task_time_display">{{ __('teacher.time') }} <span class="text-muted">{{ __('teacher.tasks.auto') }}</span></label>
                        <input type="text" class="form-control" id="edit_task_time_display" readonly style="background:#f5f5f5;cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label for="edit_task_topic">{{ __('teacher.tasks.topic') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_task_topic" name="topic" required placeholder="{{ __('teacher.tasks.topic_ph') }}">
                    </div>
                    <div class="form-group">
                        <label for="edit_task_subtopic">{{ __('teacher.tasks.subtopic') }}</label>
                        <input type="text" class="form-control" id="edit_task_subtopic" name="subtopic" placeholder="{{ __('teacher.tasks.subtopic_ph') }}">
                    </div>
                    <div class="form-group">
                        <label for="edit_task_description">{{ __('teacher.tasks.description') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_task_description" name="task_description" rows="5" required placeholder="{{ __('teacher.tasks.description_ph') }}"></textarea>
                        <small class="text-muted">{{ __('teacher.tasks.min_chars') }}</small>
                    </div>
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="bi bi-check-circle"></i> {{ __('teacher.tasks.update_resubmit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var i18nTasks = @json(__('teacher.tasks'));
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

$(document).ready(function() {
    $('#filterTasksForm').on('submit', function(e) {
        e.preventDefault();
        const status = $('#statusFilter').val();
        const date = $('#dateFilter').val();
        let url = '{{ route("teacher.myTasks") }}';
        const params = [];
        if (status) params.push('status=' + encodeURIComponent(status));
        if (date) params.push('date=' + encodeURIComponent(date));
        if (params.length > 0) url += '?' + params.join('&');
        window.location.href = url;
    });

    // Auto-apply when status select changes
    $('#statusFilter').on('change', function() {
        $('#filterTasksForm').trigger('submit');
    });

    $('#editTaskForm').on('submit', function(e) {
        e.preventDefault();
        const taskID = $('#edit_task_id').val();
        $.ajax({
            url: '/teacher/update-session-task/' + taskID,
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: i18nTasks.success,
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#940000'
                    }).then(function() {
                        sxHideModal('#editTaskModal');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: i18nTasks.error,
                        text: response.error || i18nTasks.fail_update,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: i18nTasks.error,
                    text: (xhr.responseJSON && xhr.responseJSON.error) || i18nTasks.fail_update,
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    });
});

function editTask(taskID, sessionTimetableID, date, startTime, endTime, topic, subtopic, description) {
    function executeWhenReady() {
        if (typeof jQuery === 'undefined') {
            setTimeout(executeWhenReady, 50);
            return;
        }
        $('#edit_task_id').val(taskID);
        $('#edit_session_timetableID').val(sessionTimetableID);
        $('#edit_task_date').val(date);
        $('#edit_task_topic').val(topic || '');
        $('#edit_task_subtopic').val(subtopic || '');
        $('#edit_task_description').val(description || '');

        const dateObj = new Date(date + 'T00:00:00');
        if (!isNaN(dateObj.getTime())) {
            $('#edit_task_date_display').val(dateObj.toLocaleDateString('en-GB', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            }));
        } else {
            $('#edit_task_date_display').val(date);
        }

        const formatTime = function(timeStr) {
            if (!timeStr) return 'N/A';
            const parts = timeStr.split(':');
            if (parts.length >= 2) {
                const hours = parseInt(parts[0], 10);
                const minutes = parts[1];
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const displayHours = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);
                return displayHours + ':' + minutes + ' ' + ampm;
            }
            return timeStr;
        };
        $('#edit_task_time_display').val(formatTime(startTime) + ' - ' + formatTime(endTime));
        sxShowModal('#editTaskModal');
    }
    executeWhenReady();
}
</script>
@endpush
