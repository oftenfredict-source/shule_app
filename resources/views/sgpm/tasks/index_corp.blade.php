@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.perf_tasks').' - ShuleXpert')
@section('page_title', __('teacher.pages.perf_tasks'))
@section('page_breadcrumb', __('teacher.pages.perf_tasks_bc'))

@push('styles')
<style>
    .sx-sgpm-tasks {
        --maroon: #940000;
        --line: #e8e0e0;
        --soft: #faf7f7;
    }
    .sx-sgpm-tasks .sx-panel {
        background: #fff;
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow: 0 1px 2px rgba(80,0,0,.04), 0 10px 28px rgba(80,0,0,.06);
        overflow: hidden;
    }
    .sx-sgpm-tasks .sx-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        background: linear-gradient(180deg, #fff, var(--soft));
        border-bottom: 1px solid var(--line);
    }
    .sx-sgpm-tasks .sx-panel-head h4 { margin: 0; color: var(--maroon); font-size: 1.05rem; font-weight: 800; }
    .sx-sgpm-tasks .sx-panel-head p { margin: .2rem 0 0; color: #666; font-size: .8rem; }
    .sx-sgpm-tasks .sx-panel-body { padding: 1.1rem; }
    .sx-sgpm-tasks .btn, .sx-sgpm-tasks .form-control, .sx-sgpm-tasks .form-select,
    .sx-sgpm-tasks .alert, .sx-sgpm-tasks .progress, .sx-sgpm-tasks .card,
    .sx-sgpm-task-modal .btn, .sx-sgpm-task-modal .form-control, .sx-sgpm-task-modal .form-select,
    .sx-sgpm-task-modal .alert, .sx-sgpm-task-modal .progress, .sx-sgpm-task-modal .card,
    .sx-sgpm-task-modal .modal-content, .sx-sgpm-task-modal .badge { border-radius: 0 !important; }
    .sx-btn-maroon {
        background: linear-gradient(180deg, #a80000, #940000 55%, #7f0000) !important;
        border: 1px solid #7a0000 !important;
        color: #fff !important;
        font-weight: 700 !important;
        border-radius: 0 !important;
    }
    .sx-btn-outline { background: #fff; border: 1px solid #940000; color: #940000; font-weight: 700; border-radius: 0; }
    .sx-btn-outline:hover { background: #fff5f5; color: #7a0000; }
    .sx-ep-table-wrap { border: 1px solid var(--line); overflow-x: auto; background: #fff; }
    table.sx-ep-table { width: 100%; margin: 0; border-collapse: collapse; }
    table.sx-ep-table th {
        background: linear-gradient(180deg, #f7f2f2, #f1eaea);
        color: #333;
        font-size: .7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .72rem .8rem;
        border-bottom: 1px solid var(--line);
        white-space: nowrap;
    }
    table.sx-ep-table td {
        padding: .78rem .8rem;
        border-bottom: 1px solid #f0e8e8;
        vertical-align: middle;
        color: #222;
        font-size: .86rem;
        font-weight: 600;
    }
    table.sx-ep-table tbody tr:hover td { background: #fffafa; }
    .sx-task-title { color: #940000; font-weight: 800; }
    .sx-task-meta { display: block; margin-top: .14rem; color: #777; font-size: .76rem; }
    .sx-actions { display: flex; flex-wrap: wrap; gap: .35rem; }
    .sx-actions .btn { font-size: .75rem; padding: .35rem .55rem; white-space: nowrap; }
    .sx-tag { display: inline-block; padding: .2rem .45rem; border: 1px solid; border-radius: 0; font-size: .7rem; font-weight: 800; text-transform: uppercase; }
    .sx-tag-pending, .sx-tag-in-progress { background: #fff3cd; color: #755700; border-color: #ead38b; }
    .sx-tag-completed { background: #e8f4fb; color: #14647d; border-color: #bdddea; }
    .sx-tag-approved { background: #e8f6ec; color: #146c2e; border-color: #c6e6cf; }
    .sx-tag-rejected { background: #fdecec; color: #a11919; border-color: #f0c7c7; }
    .sx-task-progress { width: 125px; height: 5px; margin-top: .4rem; background: #eee; overflow: hidden; }
    .sx-task-progress span { display: block; height: 100%; background: #940000; }
    .sx-progress-label { display: block; margin-top: .2rem; color: #777; font-size: .68rem; }
    .sx-sgpm-task-modal .modal-content { border: 1px solid #eadfdf !important; box-shadow: 0 18px 40px rgba(80,0,0,.14); }
    .sx-sgpm-task-modal .modal-header {
        background: linear-gradient(180deg, #fff7f7, #fff) !important;
        border-bottom: 1px solid #eadfdf !important;
        color: #940000 !important;
    }
    .sx-sgpm-task-modal .modal-title { color: #940000 !important; font-weight: 800; }
    .sx-sgpm-task-modal .btn-close span { display: none; }
    .sx-sgpm-task-modal .btn-close { opacity: .7; }
    .sx-sgpm-task-modal .modal-footer { background: #faf7f7; border-top: 1px solid #eadfdf; }
    .sx-sgpm-task-modal .form-label { color: #555; font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; }
    .sx-sgpm-task-modal .form-control, .sx-sgpm-task-modal .form-select { min-height: 42px; border-color: #d4caca; }
    .sx-sgpm-task-modal .form-control:focus, .sx-sgpm-task-modal .form-select:focus { border-color: #940000; box-shadow: 0 0 0 .15rem rgba(148,0,0,.12); }
    .sx-sgpm-task-modal .card-header { background: #f8f3f3 !important; color: #940000 !important; }
    .sx-sgpm-task-modal table { border-radius: 0 !important; }
    @media (max-width: 767.98px) {
        .sx-sgpm-tasks .sx-panel-head { align-items: flex-start; flex-direction: column; }
        .sx-sgpm-tasks .sx-panel-head .btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="sx-sgpm-tasks">
    <div class="sx-panel">
        <div class="sx-panel-head">
            <div>
                <h4><i class="fa fa-tasks me-1"></i> {{ __('teacher.sgpm.tasks_title') }}</h4>
                <p>{{ __('teacher.sgpm.tasks_sub') }}</p>
            </div>
            @if($userType == 'Admin' || $isHod)
                <button type="button" class="btn sx-btn-maroon" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="fa fa-plus"></i> {{ __('teacher.sgpm.assign_task') }}
                </button>
            @endif
        </div>
        <div class="sx-panel-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="sx-ep-table-wrap">
                <table class="sx-ep-table">
                    <thead>
                        <tr>
                            <th>{{ __('teacher.sgpm.task_plan') }}</th>
                            <th>{{ __('teacher.sgpm.assigned_to') }}</th>
                            <th>{{ __('teacher.sgpm.kpi_weight') }}</th>
                            <th>{{ __('teacher.sgpm.due_date') }}</th>
                            <th>{{ __('teacher.sgpm.status') }}</th>
                            <th>{{ __('teacher.sgpm.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            @php
                                $statusKey = strtolower(str_replace(' ', '-', $task->status));
                                $progressPercent = round(($task->progress / max($task->weight, 1)) * 100);
                                $submittedCount = $task->subtasks->where('status', 'Submitted')->count();
                            @endphp
                            <tr>
                                <td>
                                    <span class="sx-task-title">{{ $task->kpi }}</span>
                                    <span class="sx-task-meta">{{ __('teacher.sgpm.plan', ['title' => $task->actionPlan->title]) }}</span>
                                </td>
                                <td>{{ $task->assignee->name ?? 'N/A' }}</td>
                                <td><span class="sx-tag sx-tag-completed">{{ __('teacher.sgpm.weight_badge', ['pct' => $task->weight]) }}</span></td>
                                <td class="{{ \Carbon\Carbon::parse($task->due_date)->isPast() ? 'text-danger fw-bold' : '' }}">{{ $task->due_date }}</td>
                                <td>
                                    <span class="sx-tag sx-tag-{{ $statusKey }}">{{ $task->status }}</span>
                                    <div class="sx-task-progress"><span style="width: {{ min($progressPercent, 100) }}%"></span></div>
                                    <small class="sx-progress-label">{{ __('teacher.sgpm.complete_pct', ['pct' => $progressPercent]) }}</small>
                                </td>
                                <td>
                                    <div class="sx-actions">
                                        @if($task->assigned_to == Session::get('userID') && in_array($task->status, ['Pending', 'In Progress']))
                                            <button type="button" class="btn sx-btn-outline" data-bs-toggle="modal" data-bs-target="#subtasksModal{{ $task->taskID }}">
                                                <i class="fa fa-list"></i> {{ __('teacher.sgpm.manage_subtasks') }}
                                            </button>
                                        @endif
                                        @if(($userType == 'Admin' || $isHod) && $task->status == 'Completed')
                                            <button type="button" class="btn btn-outline-warning rounded-0" data-bs-toggle="modal" data-bs-target="#evaluateModal{{ $task->taskID }}">{{ __('teacher.sgpm.evaluate') }}</button>
                                        @endif
                                        @if(($userType == 'Admin' || $isHod) && $submittedCount > 0)
                                            <button type="button" class="btn btn-outline-info rounded-0" data-bs-toggle="modal" data-bs-target="#reviewSubtasksModal{{ $task->taskID }}">
                                                <i class="fa fa-check-circle"></i> {{ __('teacher.sgpm.review_subtasks', ['count' => $submittedCount]) }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">{{ __('teacher.sgpm.no_tasks') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($tasks as $task)
    <div class="sx-sgpm-task-modal">
        <div class="modal fade" id="submitProgressModal{{ $task->taskID }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('sgpm.tasks.submit', $task->taskID) }}" method="POST" enctype="multipart/form-data" class="submit-progress-form">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('teacher.sgpm.submit_evidence') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.sgpm.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <div class="sending-overlay mb-3" style="display:none;">
                                <div class="card border-0" style="background:#fff5f5;border-left:4px solid #940000 !important;">
                                    <div class="card-body py-2">
                                        <strong style="color:#940000;"><i class="fa fa-cloud-upload fa-spin"></i> {{ __('teacher.sgpm.sending_review') }}</strong>
                                        <div class="progress mt-2" style="height:8px;"><div class="progress-bar progress-bar-striped progress-bar-animated w-100" style="background:#940000;"></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3"><label class="form-label">{{ __('teacher.sgpm.work_remarks') }}</label><textarea name="remarks" class="form-control" rows="3" required></textarea></div>
                            <div class="mb-3"><label class="form-label">{{ __('teacher.sgpm.upload_evidence') }}</label><input type="file" name="evidence_file" class="form-control"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('teacher.sgpm.cancel') }}</button>
                            <button type="submit" class="btn sx-btn-maroon"><i class="fa fa-send"></i> {{ __('teacher.sgpm.submit_admin') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="evaluateModal{{ $task->taskID }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('sgpm.tasks.evaluate', $task->taskID) }}" method="POST" class="evaluate-task-form">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('teacher.sgpm.eval_title') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.sgpm.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label">{{ __('teacher.sgpm.score_completion') }}</label><input type="number" name="score_completion" class="form-control" min="0" max="100" required></div>
                            <div class="mb-3"><label class="form-label">{{ __('teacher.sgpm.score_kpi') }}</label><input type="number" name="score_kpi" class="form-control" min="0" max="100" required></div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('teacher.sgpm.status') }}</label>
                                <select name="status" class="form-select" required>
                                    <option value="Approved">{{ __('teacher.sgpm.approve_score') }}</option>
                                    <option value="Rejected">{{ __('teacher.sgpm.reject_resubmit') }}</option>
                                </select>
                            </div>
                            <div class="mb-3"><label class="form-label">{{ __('teacher.sgpm.comments') }}</label><textarea name="hod_comments" class="form-control" rows="2"></textarea></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('teacher.sgpm.cancel') }}</button>
                            <button type="submit" class="btn sx-btn-maroon eval-submit-btn">{{ __('teacher.sgpm.save_score') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($task->assigned_to == Session::get('userID'))
            @include('sgpm.tasks._subtask_modal', ['task' => $task])
        @endif
        @if($userType == 'Admin' || $isHod)
            @include('sgpm.tasks._hod_review_subtasks', ['task' => $task])
        @endif
    </div>
@endforeach

@if($userType == 'Admin' || $isHod)
<div class="sx-sgpm-task-modal">
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('sgpm.tasks.store') }}" method="POST" class="ajax-form">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('teacher.sgpm.assign_staff') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.sgpm.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('teacher.sgpm.action_plan') }}</label>
                            <select name="action_planID" class="form-select" required>
                                <option value="">{{ __('teacher.sgpm.select_plan') }}</option>
                                @foreach($actionPlans as $plan)<option value="{{ $plan->action_planID }}">{{ $plan->title }}</option>@endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('teacher.sgpm.assign_to') }}</label>
                            <select name="assigned_to" class="form-select" required>
                                <option value="">{{ __('teacher.sgpm.select_staff') }}</option>
                                @foreach($users as $u)
                                    @php
                                        $profile = $u->teacher ?? $u->staff;
                                        $fullName = $profile ? ($profile->first_name . ' ' . ($profile->middle_name ? $profile->middle_name . ' ' : '') . $profile->last_name) : $u->name;
                                    @endphp
                                    <option value="{{ $u->id }}">{{ $fullName }} ({{ $u->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">{{ __('teacher.sgpm.task_desc') }}</label><textarea name="kpi" class="form-control" rows="2" required></textarea></div>
                        <div class="mb-3"><label class="form-label">{{ __('teacher.sgpm.weight_0_100') }}</label><input type="number" name="weight" class="form-control" min="1" max="100" value="20" required></div>
                        <div class="mb-3"><label class="form-label">{{ __('teacher.sgpm.due_date') }}</label><input type="date" name="due_date" class="form-control" required></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('teacher.sgpm.cancel') }}</button>
                        <button type="submit" class="btn sx-btn-maroon">{{ __('teacher.sgpm.assign_task') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const S = @json(__('teacher.sgpm'));
const G = @json(__('teacher.goals'));
S.loading = @json(__('teacher.loading'));
S.save = @json(__('teacher.save'));

window.sxShowModal = window.sxShowModal || function(el) {
    const node = typeof el === 'string' ? document.querySelector(el) : el;
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) return bootstrap.Modal.getOrCreateInstance(node).show();
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('show');
};
window.sxHideModal = window.sxHideModal || function(el) {
    const node = typeof el === 'string' ? document.querySelector(el) : el;
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        const instance = bootstrap.Modal.getInstance(node);
        if (instance) instance.hide();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('hide');
};

$(document).on('hidden.bs.modal', '.modal', function () {
    if ($('.modal.show').length) return;
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
});

$(document).on('submit', 'form.ajax-form', function(e) {
    e.preventDefault();
    const $form = $(this), $btn = $form.find('button[type="submit"]'), originalHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> ' + S.loading).prop('disabled', true);
    $.ajax({
        url:$form.attr('action'), method:$form.attr('method') || 'POST', data:new FormData(this), processData:false, contentType:false,
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
        success: response => {
            if (response.success) Swal.fire({icon:'success',title:S.assign_task,text:response.message,timer:2000,showConfirmButton:false}).then(() => response.redirect ? window.location.href = response.redirect : location.reload());
            else Swal.fire(G.fail_load_subtasks, response.message || G.fail_load_subtasks, 'error');
        },
        error: xhr => {
            let msg = xhr.responseJSON?.message || G.fail_load_subtasks;
            if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            Swal.fire({icon:'error',title:G.fail_load_subtasks,html:msg});
        },
        complete: () => $btn.html(originalHtml).prop('disabled', false)
    });
});

$(document).on('submit', '.subtask-create-form', function(e) {
    e.preventDefault();
    const $form = $(this), $btn = $form.find('.subtask-save-btn'), originalHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> ' + S.loading).prop('disabled', true);
    $.ajax({
        url: $form.attr('action'), method: 'POST', data: new FormData(this), processData: false, contentType: false,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(response) {
            if (response.success) Swal.fire({icon:'success',title:G.save_subtasks,text:response.message,timer:1800,showConfirmButton:false}).then(() => location.reload());
            else { Swal.fire(G.fail_load_subtasks, response.message || G.fail_load_subtasks, 'error'); $btn.html(originalHtml).prop('disabled', false); }
        },
        error: function(xhr) {
            let msg = G.fail_load_subtasks;
            if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
            Swal.fire(G.fail_load_subtasks, msg, 'error'); $btn.html(originalHtml).prop('disabled', false);
        }
    });
});

$(document).on('click', '.send-subtask-btn', function(e) {
    e.preventDefault();
    const $btn = $(this), id = $btn.data('id'), taskId = $btn.data('task'), originalHtml = $btn.html();
    const $progress = $('#sendingProgress' + taskId);
    $('#sendingLabel' + taskId).text($btn.data('label') || S.sending_review);
    $progress.slideDown(200); $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
    $.ajax({
        url: '/sgpm/subtasks/' + id + '/submit', method: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(response) {
            if (response.success) {
                $('#sendingLabel' + taskId).text(G.submitted);
                setTimeout(() => Swal.fire({icon:'success',title:G.submitted,text:response.message,timer:2000,showConfirmButton:false}).then(() => location.reload()), 400);
            } else { Swal.fire(G.fail_load_subtasks, response.message || G.fail_load_subtasks, 'error'); $progress.slideUp(200); $btn.html(originalHtml).prop('disabled', false); }
        },
        error: function(xhr) { Swal.fire(G.fail_load_subtasks, xhr.responseJSON?.message || G.fail_load_subtasks, 'error'); $progress.slideUp(200); $btn.html(originalHtml).prop('disabled', false); }
    });
});

$(document).on('click', '.approve-subtask', function(e) {
    e.preventDefault();
    const $btn = $(this), id = $btn.data('id'), action = $btn.data('action'), originalHtml = $btn.html();
    Swal.fire({
        title: G.reject, text: G.member_not_started, icon:'warning',
        input:'text', inputPlaceholder: S.comments, showCancelButton:true,
        confirmButtonColor:'#dc3545', confirmButtonText: G.reject, cancelButtonText: S.cancel
    }).then(result => {
        if (!result.isConfirmed) return;
        $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
        $.ajax({
            url: `/sgpm/subtasks/${id}/${action}`, method:'POST', data:{hod_comments:result.value || ''},
            headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
            success: response => { if (response.success) Swal.fire({icon:'success',title:G.reject,text:response.message,timer:2000,showConfirmButton:false}).then(() => location.reload()); },
            error: xhr => { Swal.fire(G.fail_load_subtasks, xhr.responseJSON?.message || G.fail_load_subtasks, 'error'); $btn.html(originalHtml).prop('disabled', false); }
        });
    });
});

$(document).on('submit', '.approve-subtask-form', function(e) {
    e.preventDefault();
    const $form = $(this), id = $form.data('id'), $btn = $form.find('button[type="submit"]'), originalHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> ' + S.loading).prop('disabled', true);
    $.ajax({
        url:`/sgpm/subtasks/${id}/approve`, method:'POST', data:$form.serialize(),
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
        success: response => {
            if (response.success) Swal.fire({icon:'success',title:G.approved,text:response.message,timer:2000,showConfirmButton:false}).then(() => location.reload());
            else { Swal.fire(G.fail_load_subtasks, response.message || G.fail_load_subtasks, 'error'); $btn.html(originalHtml).prop('disabled', false); }
        },
        error: xhr => { Swal.fire(G.fail_load_subtasks, xhr.responseJSON?.message || G.fail_load_subtasks, 'error'); $btn.html(originalHtml).prop('disabled', false); }
    });
});

$(document).on('change', '.filter-member, .filter-status', function() {
    const $body = $(this).closest('.modal-body'), $table = $body.find('.subtasks-table');
    const member = $body.find('.filter-member').val(), status = $body.find('.filter-status').val();
    $table.find('.subtask-row').each(function() {
        const $row = $(this);
        $row.toggle((!member || $row.data('member').toString() === member) && (!status || $row.data('status') === status));
    });
    $table.find('.no-results-row').remove();
    if (!$table.find('.subtask-row:visible').length) $table.find('tbody').append('<tr class="no-results-row"><td colspan="7" class="text-center text-muted py-3">' + S.no_subtasks_filter + '</td></tr>');
});

$(document).on('submit', '.submit-progress-form', function(e) {
    e.preventDefault();
    const $form = $(this), $btn = $form.find('button[type="submit"]'), originalHtml = $btn.html(), $overlay = $form.find('.sending-overlay');
    $overlay.slideDown(200); $btn.html('<i class="fa fa-spinner fa-spin"></i> ' + S.loading).prop('disabled', true);
    $.ajax({
        url:$form.attr('action'), method:'POST', data:new FormData(this), processData:false, contentType:false,
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
        success: response => {
            if (response.success) Swal.fire({icon:'success',title:G.submitted,text:response.message || S.submit_admin,timer:2000,showConfirmButton:false}).then(() => location.reload());
            else { Swal.fire(G.fail_load_subtasks, response.message || G.fail_load_subtasks, 'error'); $overlay.slideUp(200); $btn.html(originalHtml).prop('disabled', false); }
        },
        error: xhr => { Swal.fire(G.fail_load_subtasks, xhr.responseJSON?.message || G.fail_load_subtasks, 'error'); $overlay.slideUp(200); $btn.html(originalHtml).prop('disabled', false); }
    });
});

$(document).on('submit', '.evaluate-task-form', function(e) {
    e.preventDefault();
    const $form = $(this), $btn = $form.find('.eval-submit-btn'), originalHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> ' + S.loading).prop('disabled', true);
    $.ajax({
        url:$form.attr('action'), method:'POST', data:$form.serialize(),
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
        success: response => {
            if (response.success) Swal.fire({icon:'success',title:S.save_score,text:response.message || S.save_score,timer:2000,showConfirmButton:false}).then(() => location.reload());
            else { Swal.fire(G.fail_load_subtasks, response.message || G.fail_load_subtasks, 'error'); $btn.html(originalHtml).prop('disabled', false); }
        },
        error: xhr => { Swal.fire(G.fail_load_subtasks, xhr.responseJSON?.message || G.fail_load_subtasks, 'error'); $btn.html(originalHtml).prop('disabled', false); }
    });
});
</script>
@endpush
