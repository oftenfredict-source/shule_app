@extends('layouts.teacher_corp')

@section('title', ((isset($filterType) && $filterType == 'direct') ? __('teacher.pages.direct_goals') : __('teacher.pages.assigned_tasks')).' - ShuleXpert')
@section('page_title', (isset($filterType) && $filterType == 'direct') ? __('teacher.pages.direct_goals') : __('teacher.pages.assigned_tasks'))
@section('page_breadcrumb', __('teacher.pages.goals_bc'))

@push('styles')
<style>
    *, *::before, *::after { box-sizing: border-box; }

    .text-maroon { color: #940000 !important; }
    .bg-maroon   { background-color: #940000 !important; }
    .btn-maroon  { background: linear-gradient(180deg, #a80000, #940000 55%, #7f0000) !important; color: #fff !important; border: 1px solid #7a0000; border-radius: 0 !important; font-weight: 700; }
    .btn-maroon:hover, .btn-maroon:active { background-color: #7a0000 !important; color: #fff !important; }
    .btn-send-hod {
        border: 2px solid #940000 !important;
        color: #940000 !important;
        font-weight: 700 !important;
        background: transparent !important;
        border-radius: 0 !important;
        transition: all 0.2s;
    }
    .btn-send-hod:hover, .btn-send-hod:active { background: #940000 !important; color: white !important; }

    /* === Page Wrapper === */
    .sx-my-tasks {
        --maroon: #940000;
        --line: #e8e0e0;
        --soft: #faf7f7;
    }
    .mt-page {
        background: #fff;
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow: 0 1px 2px rgba(80,0,0,.04), 0 10px 28px rgba(80,0,0,.06);
        min-height: 420px;
        padding: 0;
        overflow: hidden;
    }

    .task-header {
        background: linear-gradient(180deg, #fff, var(--soft));
        color: #940000;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--line);
    }
    .task-header h4 { color: #940000; font-size: 1.05rem; font-weight: 800; margin: 0; }
    .task-header .portal-pill {
        background: #fff;
        border: 1px solid #dfcaca;
        border-radius: 0;
        color: #940000;
        padding: .3rem .65rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    /* === Task Cards Grid === */
    .tasks-wrap { padding: 1.15rem; }
    .tasks-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }

    /* === Task Card === */
    .task-card {
        background: #fff;
        border: 1px solid #e8e0e0;
        border-radius: 0;
        border-left: 5px solid #940000;
        box-shadow: 0 3px 12px rgba(80,0,0,.06);
        overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .task-card:hover { transform: translateY(-1px); box-shadow: 0 7px 18px rgba(80,0,0,.1); }
    .task-card .badge, .modal .badge { border-radius: 0 !important; }
    
    .tc-body { padding: 14px 14px 10px; }
    .tc-title { font-size: 0.95rem; font-weight: 800; color: #222; margin: 0 0 4px; line-height: 1.3; }
    .tc-goal  { font-size: 0.75rem; color: #999; }
    .tc-weight-badge {
        background: #f7f7f7;
        border: 1px solid #eee;
        border-radius: 0;
        padding: 3px 10px;
        font-size: 0.76rem;
        font-weight: 700;
        color: #555;
        white-space: nowrap;
    }
    .tc-progress-num { font-size: 0.82rem; font-weight: 800; color: #940000; margin-top: 2px; }
    .tc-desc {
        margin: 8px 14px;
        background: #fafafa;
        border-left: 3px solid #e0e0e0;
        padding: 7px 10px;
        border-radius: 0;
        font-size: 0.78rem;
        color: #777;
        font-style: italic;
    }

    /* Progress bar strip */
    .tc-progress-strip { height: 5px; background: #f0f0f0; }
    .tc-progress-fill  { height: 100%; background: #940000; transition: width 0.5s ease; }

    /* Card Footer */
    .tc-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: #fafafa;
        border-top: 1px solid #f0f0f0;
    }
    .tc-count { font-size: 0.76rem; color: #aaa; }
    .tc-actions { display: flex; gap: 6px; }
    .tc-actions .btn {
        border-radius: 0;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 7px 14px;
        min-height: 38px;
        line-height: 1.2;
    }

    /* Empty State */
    .empty-state { text-align: center; padding: 4rem 1.25rem; color: #777; grid-column: 1 / -1; background: linear-gradient(180deg, #fff, #fffafa); border: 1px dashed #dfcaca; }
    .empty-state i { color: #940000; font-size: 2.5rem; display: block; margin-bottom: 14px; }
    .empty-state p { font-size: 0.88rem; margin-top: 6px; color: #777; }

    .modal-content { border: 1px solid #eadfdf !important; border-radius: 0 !important; box-shadow: 0 18px 40px rgba(80,0,0,.14) !important; }
    @media (max-width: 576px) {
        .modal-dialog { margin: 8px 8px !important; }
        .modal-dialog[style] { max-width: calc(100vw - 16px) !important; }
    }
    .modal-header { background: linear-gradient(180deg, #fff7f7, #fff) !important; border-bottom: 1px solid #eadfdf !important; border-radius: 0 !important; color: #940000 !important; padding: 14px 16px !important; }
    .modal-header h5, .modal-header .modal-title { color: #940000 !important; font-size: 0.95rem !important; font-weight: 800 !important; }
    .modal-body { padding: 14px 14px !important; font-size: 0.88rem; }
    .modal-footer { padding: 10px 14px !important; border-top: 1px solid #eadfdf !important; background: #faf7f7; }

    /* Add Subtasks modal */
    #addSubtasksModal .sx-add-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        width: 100%;
    }
    #addSubtasksModal .sx-add-head-main { min-width: 0; flex: 1; }
    #addSubtasksModal .sx-add-head-main .modal-title {
        margin: 0;
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    #addSubtasksModal .sx-add-task-name {
        display: block;
        margin-top: .28rem;
        font-size: .8rem;
        font-weight: 600;
        color: #666 !important;
        line-height: 1.35;
        word-break: break-word;
    }
    #addSubtasksModal .sx-weight-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border: 1px solid #eadfdf;
        background: #fff;
        color: #940000 !important;
        font-size: .75rem;
        font-weight: 800;
        padding: .35rem .55rem;
        white-space: nowrap;
        flex-shrink: 0;
    }
    #addSubtasksModal .sx-weight-badge.is-over { color: #a11919 !important; border-color: #f0c7c7; background: #fff5f5; }
    #addSubtasksModal .subtask-row {
        border: 1px solid #eadfdf;
        border-left: 3px solid #940000;
        background: #fff;
        padding: .9rem .95rem;
        margin-bottom: .75rem;
        box-shadow: 0 1px 4px rgba(80,0,0,.04);
    }
    #addSubtasksModal .subtask-row .sx-row-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        margin-bottom: .65rem;
    }
    #addSubtasksModal .subtask-row .sx-row-label {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #940000 !important;
        margin: 0;
    }
    #addSubtasksModal .subtask-row label {
        font-size: .7rem !important;
        font-weight: 800 !important;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: .3rem;
        display: block;
    }
    #addSubtasksModal .subtask-row .form-control {
        border-radius: 0 !important;
        min-height: 42px;
        border: 1px solid #d4caca;
        font-weight: 600;
    }
    #addSubtasksModal .subtask-row .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 .15rem rgba(148,0,0,.12) !important;
    }
    #addSubtasksModal .btn-remove-sub {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0 !important;
        border: 1px solid #f0c7c7;
        color: #a11919;
        background: #fff;
    }
    #addSubtasksModal .btn-remove-sub:hover { background: #fff5f5; }
    #addSubtasksModal #btn_add_subtask_row {
        border: 1px dashed #d8b4b4 !important;
        background: #fff !important;
        color: #940000 !important;
        font-weight: 700 !important;
        min-height: 42px;
        box-shadow: none !important;
    }
    #addSubtasksModal #btn_add_subtask_row:hover { background: #fff5f5 !important; border-style: solid !important; }
    #addSubtasksModal .modal-footer .btn { min-height: 40px; font-weight: 700; border-radius: 0 !important; }

    /* === Subtask Card in View Modal === */
    .sub-card {
        background: #fff;
        border-radius: 0;
        border-left: 4px solid #940000;
        padding: 12px 12px 10px;
        margin-bottom: 10px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.07);
    }
    .sub-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 6px; }
    .sub-card-name { font-size: 0.88rem; font-weight: 700; color: #222; margin: 0; flex:1; }
    .sub-card-meta { font-size: 0.75rem; color: #aaa; margin: 3px 0 6px; }
    .sub-card-desc { font-size: 0.78rem; color: #888; margin-bottom: 8px; font-style: italic; }
    .sub-actions { display: flex; flex-wrap: wrap; gap: 6px; }
    .sub-actions .btn {
        flex: 1; min-width: 72px; font-size: 0.76rem; font-weight: 700;
        padding: 6px 10px; border-radius: 0; min-height: 36px; text-align: center;
    }

    /* === Step Entry === */
    .step-entry {
        background: #f4fbfd;
        border-left: 3px solid #17a2b8;
        border-radius: 0;
        padding: 9px 12px;
        margin-bottom: 8px;
    }
    .step-entry-top { display: flex; justify-content: space-between; align-items: center; }
    .step-date { font-size: 0.72rem; color: #17a2b8; font-weight: 700; }
    .step-desc { font-size: 0.82rem; color: #333; margin-top: 4px; }
    .step-btns { display: flex; gap: 2px; }

    /* === Form Controls === */
    .sx-my-tasks .form-control, .modal .form-control, .modal .btn { border-radius: 0 !important; }
    .sx-my-tasks .form-control, .modal .form-control { font-size: 0.88rem !important; min-height: 42px; }
    .sx-my-tasks textarea.form-control, .modal textarea.form-control { min-height: 72px; }
    .sx-my-tasks .form-control:focus, .modal .form-control:focus { border-color: #940000 !important; box-shadow: 0 0 0 2px rgba(148,0,0,0.15) !important; }
    .sx-my-tasks label, .modal label { font-size: 0.8rem !important; font-weight: 700 !important; }

    /* === Step Input Row === */
    .step-row { background: #f8f8f8; border-radius: 0; padding: 10px 12px; margin-bottom: 8px; }

    /* === Desktop Breakpoints === */
    @media (min-width: 576px) {
        .tasks-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (min-width: 992px) {
        .tasks-grid { grid-template-columns: 1fr 1fr 1fr; }
        .modal-dialog { max-width: 65% !important; }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-my-tasks,
    html[data-bs-theme="dark"] .sx-my-tasks {
        --line: #3a2a2e;
        --soft: #2a1c20;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .mt-page,
    html[data-bs-theme="dark"] .sx-my-tasks .mt-page {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .task-header,
    html[data-bs-theme="dark"] .sx-my-tasks .task-header {
        background: #1c1417 !important;
        border-bottom-color: #3a2a2e !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .task-header h4,
    html[data-bs-theme="dark"] .sx-my-tasks .task-header h4 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .task-header .portal-pill,
    html[data-bs-theme="dark"] .sx-my-tasks .task-header .portal-pill {
        background: #24181c !important;
        border-color: #4a383c !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .task-card,
    html[data-bs-theme="dark"] .sx-my-tasks .task-card {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .tc-title,
    html[data-bs-theme="dark"] .sx-my-tasks .tc-title {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .tc-goal,
    html[data-bs-theme="dark"] .sx-my-tasks .tc-goal {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .tc-weight-badge,
    html[data-bs-theme="dark"] .sx-my-tasks .tc-weight-badge {
        background: #2a1c20 !important;
        border-color: #3a2a2e !important;
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .tc-progress-num,
    html[data-bs-theme="dark"] .sx-my-tasks .tc-progress-num {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .tc-desc,
    html[data-bs-theme="dark"] .sx-my-tasks .tc-desc {
        background: #24181c !important;
        border-left-color: #4a383c !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .tc-progress-strip,
    html[data-bs-theme="dark"] .sx-my-tasks .tc-progress-strip {
        background: #2a2226 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .tc-footer,
    html[data-bs-theme="dark"] .sx-my-tasks .tc-footer {
        background: #24181c !important;
        border-top-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .tc-count,
    html[data-bs-theme="dark"] .sx-my-tasks .tc-count {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .empty-state,
    html[data-bs-theme="dark"] .sx-my-tasks .empty-state {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .empty-state i,
    html[data-bs-theme="dark"] .sx-my-tasks .empty-state i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .empty-state p,
    html[data-bs-theme="dark"] .sx-my-tasks .empty-state p,
    html[data-sx-theme="dark"] .sx-my-tasks .empty-state h5,
    html[data-bs-theme="dark"] .sx-my-tasks .empty-state h5 {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .btn-send-hod,
    html[data-bs-theme="dark"] .sx-my-tasks .btn-send-hod {
        color: #ffb4b4 !important;
        border-color: #940000 !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks .form-control,
    html[data-bs-theme="dark"] .sx-my-tasks .form-control,
    html[data-sx-theme="dark"] .modal .form-control,
    html[data-bs-theme="dark"] .modal .form-control,
    html[data-sx-theme="dark"] .modal .form-select,
    html[data-bs-theme="dark"] .modal .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-my-tasks label,
    html[data-bs-theme="dark"] .sx-my-tasks label,
    html[data-sx-theme="dark"] .modal label,
    html[data-bs-theme="dark"] .modal label,
    html[data-sx-theme="dark"] .modal .form-label,
    html[data-bs-theme="dark"] .modal .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .modal .modal-content,
    html[data-bs-theme="dark"] .modal .modal-content {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .modal .modal-header,
    html[data-bs-theme="dark"] .modal .modal-header {
        background: #2a1c20 !important;
        border-bottom-color: #3a2a2e !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .modal .modal-header h5,
    html[data-bs-theme="dark"] .modal .modal-header h5,
    html[data-sx-theme="dark"] .modal .modal-title,
    html[data-bs-theme="dark"] .modal .modal-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .modal .modal-body,
    html[data-bs-theme="dark"] .modal .modal-body {
        background: #1c1417 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .modal .modal-footer,
    html[data-bs-theme="dark"] .modal .modal-footer {
        background: #24181c !important;
        border-top-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #addSubtasksModal .sx-add-task-name,
    html[data-bs-theme="dark"] #addSubtasksModal .sx-add-task-name {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #addSubtasksModal .sx-weight-badge,
    html[data-bs-theme="dark"] #addSubtasksModal .sx-weight-badge {
        background: #24181c !important;
        border-color: #4a383c !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #addSubtasksModal .subtask-row,
    html[data-bs-theme="dark"] #addSubtasksModal .subtask-row {
        background: #24181c !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #addSubtasksModal .subtask-row .sx-row-label,
    html[data-bs-theme="dark"] #addSubtasksModal .subtask-row .sx-row-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #addSubtasksModal .subtask-row label,
    html[data-bs-theme="dark"] #addSubtasksModal .subtask-row label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #addSubtasksModal .btn-remove-sub,
    html[data-bs-theme="dark"] #addSubtasksModal .btn-remove-sub {
        background: #2a1c20 !important;
        border-color: #5a3030 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #addSubtasksModal #btn_add_subtask_row,
    html[data-bs-theme="dark"] #addSubtasksModal #btn_add_subtask_row {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sub-card,
    html[data-bs-theme="dark"] .sub-card {
        background: #24181c !important;
        color: #f3e9eb !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sub-card-name,
    html[data-bs-theme="dark"] .sub-card-name {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sub-card-meta,
    html[data-bs-theme="dark"] .sub-card-meta,
    html[data-sx-theme="dark"] .sub-card-desc,
    html[data-bs-theme="dark"] .sub-card-desc {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .step-entry,
    html[data-bs-theme="dark"] .step-entry {
        background: #1a2a30 !important;
        color: #e8f4f8 !important;
    }
    html[data-sx-theme="dark"] .step-desc,
    html[data-bs-theme="dark"] .step-desc {
        color: #e8f4f8 !important;
    }
    html[data-sx-theme="dark"] .step-row,
    html[data-bs-theme="dark"] .step-row {
        background: #24181c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .text-maroon,
    html[data-bs-theme="dark"] .text-maroon {
        color: #ffb4b4 !important;
    }
</style>
@endpush

@section('content')
<div class="sx-my-tasks">
<div class="mt-page">

    <!-- Sticky Header -->
    <div class="task-header d-flex justify-content-between align-items-center">
        <h4><i class="fa fa-{{ (isset($filterType) && $filterType == 'direct') ? 'star' : 'thumb-tack' }} mr-2"></i>{{ (isset($filterType) && $filterType == 'direct') ? __('teacher.pages.direct_goals') : __('teacher.pages.assigned_tasks') }}</h4>
        <span class="portal-pill">{{ __('teacher.goals.member_portal') }}</span>
    </div>

    <!-- Tasks Grid -->
    <div class="tasks-wrap">
        <div class="tasks-grid">
            @forelse($myTasks as $task)
                @php
                    $pctDone = (float)$task->progress_val == (int)$task->progress_val ? number_format($task->progress_val, 0) : rtrim(rtrim(number_format($task->progress_val, 6, '.', ''), '0'), '.');
                @endphp
                <div class="task-card" style="border-left-color: {{ $task->is_direct ? '#2980b9' : '#940000' }};">
                    <div class="tc-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex:1; margin-right:8px;">
                                <div class="mb-1">
                                    @if($task->is_direct)
                                        <span class="badge badge-primary px-2 py-1" style="font-size: 0.65rem; border-radius: 0;"><i class="fa fa-shield"></i> {{ __('teacher.goals.direct_assignment') }}</span>
                                    @else
                                        <span class="badge badge-light border px-2 py-1" style="font-size: 0.65rem; border-radius: 0; color: #940000;"><i class="fa fa-users"></i> {{ __('teacher.goals.via_department') }}</span>
                                    @endif
                                </div>
                                <p class="tc-title">{{ $task->display_name }}</p>
                                <div class="tc-goal"><i class="fa fa-bullseye mr-1"></i>{{ $task->parent_goal_name }}</div>
                            </div>
                            <div class="text-right">
                                <span class="tc-weight-badge">{{ $task->assigned_weight }}%</span>
                                <div class="tc-progress-num">{{ __('teacher.goals.done_pct', ['pct' => $pctDone]) }}</div>
                            </div>
                        </div>
                    </div>

                    @if($task->description)
                        <div class="tc-desc">"{{ $task->description }}"</div>
                    @endif

                    <div class="tc-progress-strip">
                        <div class="tc-progress-fill" style="width: {{ $task->progress_val }}%;"></div>
                    </div>

                    <div class="tc-footer">
                        <div class="tc-count"><i class="fa fa-list mr-1"></i>{{ __('teacher.goals.subtasks_count', ['count' => $task->subtasks->count()]) }}</div>
                        <div class="tc-actions">
                            <button class="btn btn-maroon btn-add-subtasks"
                                data-id="{{ $task->id }}"
                                data-is-direct="{{ $task->is_direct ? '1' : '0' }}"
                                data-name="{{ $task->display_name }}"
                                data-weight="{{ $task->assigned_weight }}"
                                data-used="{{ $task->weight_sum }}">
                                <i class="fa fa-plus-circle"></i> {{ __('teacher.goals.subtasks') }}
                            </button>
                            <button class="btn btn-maroon btn-view-subtasks"
                                data-id="{{ $task->id }}"
                                data-is-direct="{{ $task->is_direct ? '1' : '0' }}"
                                data-name="{{ $task->display_name }}">
                                <i class="fa fa-eye"></i> {{ __('teacher.goals.view') }}
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa fa-tasks"></i>
                    <h5 style="color:#bbb;">{{ __('teacher.goals.no_tasks') }}</h5>
                    <p>{{ __('teacher.goals.no_tasks') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
</div>

<!-- ===== MODALS ===== -->

<!-- Modal: Add Subtasks -->
<div class="modal fade" id="addSubtasksModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="sx-add-head">
                    <div class="sx-add-head-main">
                        <h5 class="modal-title mb-0"><i class="fa fa-plus-square"></i> {{ __('teacher.goals.add_subtasks') }}</h5>
                        <span class="sx-add-task-name" id="modal_parent_task_name"></span>
                    </div>
                    <span class="sx-weight-badge" id="sx_weight_badge">
                        {{ __('teacher.goals.left') }} <span id="remaining_weight_display">100</span>%
                    </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.goals.close') }}"></button>
            </div>
            <form id="subtaskForm">
                @csrf
                <input type="hidden" name="member_task_id" id="modal_member_task_id">
                <input type="hidden" name="direct_task_id" id="modal_direct_task_id">
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    <div id="subtasks_container"></div>
                    <button type="button" id="btn_add_subtask_row" class="btn btn-sm w-100 mt-1">
                        <i class="fa fa-plus"></i> {{ __('teacher.goals.add_another') }}
                    </button>
                </div>
                <div class="modal-footer d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal" data-dismiss="modal">{{ __('teacher.goals.cancel') }}</button>
                    <button type="submit" id="btn_save_subtasks" class="btn btn-maroon flex-fill">{{ __('teacher.goals.save_subtasks') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: View & Conduct Subtasks -->
<div class="modal fade" id="viewSubtasksModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">{{ __('teacher.goals.my_subtasks') }} <span id="view_modal_task_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.goals.close') }}"></button>
            </div>
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto; background: #f5f5f8;">
                <div id="subtasks_list_container"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Subtask -->
<div class="modal fade" id="editSubtaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">{{ __('teacher.goals.edit_subtask') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.goals.close') }}"></button>
            </div>
            <form id="editSubtaskForm">
                @csrf
                <input type="hidden" id="edit_subtask_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('teacher.goals.subtask_name') }}</label>
                        <input type="text" id="edit_sub_name" name="subtask_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('teacher.goals.weight_pct') }}</label>
                        <input type="number" id="edit_sub_weight" name="weight" class="form-control" required min="0.1" step="0.1">
                    </div>
                    <div class="form-group">
                        <label>{{ __('teacher.goals.description') }}</label>
                        <textarea id="edit_sub_desc" name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm flex-fill" data-bs-dismiss="modal" data-dismiss="modal">{{ __('teacher.goals.cancel') }}</button>
                    <button type="submit" class="btn btn-maroon btn-sm flex-fill">{{ __('teacher.goals.update_subtask') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Conduct Steps -->
<div class="modal fade" id="conductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">{{ __('teacher.goals.conduct') }}</h5>
                    <div class="small" style="opacity:0.85;" id="conduct_subtask_name"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.goals.close') }}"></button>
            </div>
            <div class="modal-body" style="max-height: 82vh; overflow-y: auto;">
                <!-- Log Steps -->
                <div style="background:#f0fafc; border-radius:0; padding:12px; margin-bottom:14px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-weight-bold" style="font-size:0.85rem; color:#17a2b8;"><i class="fa fa-plus-circle"></i> {{ __('teacher.goals.log_steps') }}</span>
                        <button type="button" id="btn_add_step_row" class="btn btn-sm" style="background:#17a2b8; color:white; border-radius:0; font-size:0.76rem; padding:4px 10px;">{{ __('teacher.goals.more') }}</button>
                    </div>
                    <form id="stepForm">
                        @csrf
                        <input type="hidden" name="subtask_id" id="conduct_subtask_id">
                        <div id="steps_rows_container"></div>
                        <button type="submit" id="btn_save_steps" class="btn w-100 mt-2" style="background:#17a2b8; color:white; border-radius:0; font-weight:700; min-height:42px;">
                            {{ __('teacher.goals.save_action_steps') }}
                        </button>
                    </form>
                </div>

                <!-- History -->
                <div class="font-weight-bold mb-2" style="font-size:0.85rem; color:#555;">
                    <i class="fa fa-history mr-1"></i> {{ __('teacher.goals.implementation_log') }}
                </div>
                <div id="steps_history_list"></div>
            </div>
        </div>
    </div>
</div>
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-title,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-title {
    color: #f3e9eb !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-goal,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-goal,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-count,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-count,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-desc,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-desc {
    color: #d7c4c8 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .task-header h4,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .task-header h4,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-progress-num,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-my-tasks .tc-progress-num {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .modal label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .modal label,
html[data-sx-theme="dark"] .sx-sa-content-wrap .modal .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .modal .form-label,
html[data-sx-theme="dark"] #addSubtasksModal .subtask-row label,
html[data-bs-theme="dark"] #addSubtasksModal .subtask-row label {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sub-card-name,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sub-card-name {
    color: #f3e9eb !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sub-card-meta,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sub-card-meta,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sub-card-desc,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sub-card-desc {
    color: #d7c4c8 !important;
}
</style>
@endsection

@push('scripts')
<script>
const G = @json(__('teacher.goals'));
G.loading = @json(__('teacher.loading'));
G.date = @json(__('teacher.date'));

function gt(key, repl) {
    let s = G[key] || key;
    if (repl) Object.keys(repl).forEach(k => { s = s.split(':' + k).join(String(repl[k])); });
    return s;
}

window.sxShowModal = window.sxShowModal || function(el) {
    const node = typeof el === 'string' ? document.querySelector(el) : el;
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(node).show();
        return;
    }
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

$(document).ready(function() {
    let subtaskRowCount = 0;
    let stepRowCount = 0;
    let global_used_weight_percent = 0;
    let currentViewTaskId = null;

    // ======== SUBTASK CREATION ========
    $('.btn-add-subtasks').click(function() {
        global_used_weight_percent = parseFloat($(this).data('used')) || 0;

        if (global_used_weight_percent >= 100) {
            Swal.fire({
                title: gt('weight_label', {pct: 100}),
                text: G.not_broken_down,
                icon: 'warning',
                confirmButtonColor: '#940000',
                confirmButtonText: G.close
            });
            return;
        }

        const id   = $(this).data('id');
        const name = $(this).data('name');
        const isDirect = $(this).data('is-direct') == '1';

        $('#modal_member_task_id').val(isDirect ? '' : id);
        $('#modal_direct_task_id').val(isDirect ? id : '');
        
        $('#modal_parent_task_name').text(name);
        $('#subtasks_container').empty();
        subtaskRowCount = 0;
        addSubtaskRow();
        updateBudget();
        sxShowModal('#addSubtasksModal');
    });

    $('#btn_add_subtask_row').click(addSubtaskRow);

    function addSubtaskRow() {
        const n = subtaskRowCount + 1;
        const row = `
            <div class="subtask-row">
                <div class="sx-row-top">
                    <p class="sx-row-label">${gt('subtask_n', {n: n})}</p>
                    <button type="button" class="btn-remove-sub remove-row" title="${G.remove}"><i class="fa fa-trash"></i></button>
                </div>
                <div class="row g-2">
                    <div class="col-md-8">
                        <label>${G.subtask_name}</label>
                        <input type="text" name="subtasks[${subtaskRowCount}][subtask_name]" class="form-control" required placeholder="${G.example_subtask}">
                    </div>
                    <div class="col-md-4">
                        <label>${G.weight_pct}</label>
                        <input type="number" name="subtasks[${subtaskRowCount}][weight]" class="form-control sub-weight-input" required min="0.1" max="100" step="0.1" placeholder="e.g. 25">
                    </div>
                    <div class="col-12">
                        <label>${G.desc_optional}</label>
                        <textarea name="subtasks[${subtaskRowCount}][description]" class="form-control" rows="2" placeholder="${G.desc_ph}"></textarea>
                    </div>
                </div>
            </div>
        `;
        $('#subtasks_container').append(row);
        subtaskRowCount++;
        updateBudget();
    }

    $(document).on('click', '.remove-row', function() {
        const $rows = $('#subtasks_container .subtask-row');
        if ($rows.length <= 1) {
            Swal.fire({ icon: 'info', title: G.add_subtasks, text: G.add_another, confirmButtonColor: '#940000' });
            return;
        }
        $(this).closest('.subtask-row').remove();
        $('#subtasks_container .subtask-row').each(function(i) {
            $(this).find('.sx-row-label').text(gt('subtask_n', {n: i + 1}));
        });
        updateBudget();
    });

    $(document).on('input', '.sub-weight-input', updateBudget);

    function updateBudget() {
        let total = 0;
        $('.sub-weight-input').each(function() { total += parseFloat($(this).val()) || 0; });
        const remaining = 100 - (global_used_weight_percent + total);
        $('#remaining_weight_display').text(remaining.toFixed(1));
        $('#sx_weight_badge').toggleClass('is-over', remaining < 0);
    }

    $('#subtaskForm').submit(function(e) {
        e.preventDefault();
        let total = 0;
        $('.sub-weight-input').each(function() { total += parseFloat($(this).val()) || 0; });

        if ((global_used_weight_percent + total) > 100) {
            Swal.fire(G.weight_pct, gt('weight_label', {pct: 100}), 'error');
            return;
        }

        const $btn = $('#btn_save_subtasks');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + G.loading);

        $.ajax({
            url: '{{ route("member.goals.subtaskStore") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    Swal.fire(G.save_subtasks, res.message, 'success').then(() => location.reload());
                } else {
                    $btn.prop('disabled', false).text(G.save_subtasks);
                    Swal.fire(G.fail_load_subtasks, res.message, 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text(G.save_subtasks);
                Swal.fire(G.fail_load_subtasks, G.fail_load_subtasks, 'error');
            }
        });
    });

    // ======== VIEW SUBTASKS ========
    $('.btn-view-subtasks').click(function() {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        const isDirect = $(this).data('is-direct') == '1';
        currentViewTaskId = id;
        $('#view_modal_task_name').text(name);
        loadSubtasks(id, isDirect);
        sxShowModal('#viewSubtasksModal');
    });

    function loadSubtasks(taskId, isDirect = false) {
        const container = $('#subtasks_list_container');
        container.html('<div class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $.get('{{ url("goals/fetch-subtasks") }}/' + taskId + '?is_direct=' + (isDirect ? '1' : '0'), function(subtasks) {
            renderSubtasks(subtasks, isDirect);
        }).fail(function() {
            container.html('<div class="text-center py-4 text-danger">' + G.fail_load_subtasks + '</div>');
        });
    }

    function renderSubtasks(subtasks, isDirect = false) {
        const container = $('#subtasks_list_container');
        container.empty();

        if (!subtasks.length) {
            container.html(`
                <div style="text-align:center; padding:40px 20px; color:#ccc;">
                    <i class="fa fa-list fa-3x mb-3" style="display:block;"></i>
                    <p style="font-size:0.88rem;">${G.not_broken_down}</p>
                </div>
            `);
            return;
        }

        subtasks.forEach(s => {
            let statusColor = '#888', statusIcon = 'fa-clock-o', statusLabel = G.pending;
            if (s.status === 'Submitted') { statusColor = '#e67e22'; statusIcon = 'fa-send'; statusLabel = G.pending; }
            if (s.status === 'Approved')  { statusColor = '#27ae60'; statusIcon = 'fa-check-circle'; statusLabel = G.approved; }

            const who = isDirect ? G.admin : G.hod;
            const scorePercent = s.is_approved ? Math.round((s.marks / s.weight) * 100) : null;
            const scoreHtml = s.is_approved ? `
                <div style="display:flex; align-items:center; gap:10px; margin:8px 0; background:#f0fff4; border-radius:0; padding:8px 10px;">
                    <div style="flex:1;">
                        <div style="height:6px; background:#e0e0e0; border-radius:0;">
                            <div style="width:${scorePercent}%; height:100%; background:#27ae60; border-radius:0; transition:width 0.5s;"></div>
                        </div>
                    </div>
                    <span style="font-weight:800; color:#27ae60; font-size:0.85rem; white-space:nowrap;">
                        ${gt('pts', {marks: s.marks, weight: s.weight})} &bull; ${scorePercent}<sup>%</sup>
                    </span>
                </div>
            ` : (s.status === 'Submitted' ? `
                <div style="background:#fff8f0; border-radius:0; padding:7px 10px; margin:6px 0; font-size:0.78rem; color:#e67e22;">
                    <i class="fa fa-hourglass-half mr-1"></i> ${gt('awaiting_review', {who: who})}
                </div>
            ` : '');

            const actionsHtml = !s.is_sent_to_hod ? `
                <button class="btn btn-sm btn-outline-secondary btn-conduct" data-id="${s.id}" data-name="${s.subtask_name}">
                    <i class="fa fa-edit"></i> ${G.conduct}
                </button>
                <button class="btn btn-sm btn-outline-primary btn-edit-subtask" data-id="${s.id}" data-name="${s.subtask_name}" data-weight="${s.weight}" data-desc="${(s.description||'').replace(/"/g,'&quot;')}">
                    <i class="fa fa-pencil"></i>
                </button>
                <button class="btn btn-sm btn-send-hod btn-submit-hod" data-id="${s.id}" data-is-direct="${isDirect ? '1' : '0'}" title="${who}">
                    <i class="fa fa-send"></i> ${who}
                </button>
                <button class="btn btn-sm btn-outline-danger btn-delete-subtask" data-id="${s.id}">
                    <i class="fa fa-trash"></i>
                </button>
            ` : `
                <button class="btn btn-sm btn-outline-secondary btn-conduct" data-id="${s.id}" data-name="${s.subtask_name}">
                    <i class="fa fa-eye"></i> ${G.steps}
                </button>
                <button class="btn btn-sm" disabled style="background:#f5f5f5; color:#aaa; border-radius:0; font-size:0.76rem; cursor:default;">
                    <i class="fa fa-lock"></i> ${G.locked}
                </button>
            `;

            container.append(`
                <div class="sub-card">
                    <div class="sub-card-top">
                        <p class="sub-card-name">${s.subtask_name}</p>
                        <span style="background:${statusColor}; color:white; border-radius:0; padding:3px 10px; font-size:0.72rem; font-weight:700; white-space:nowrap;">
                            <i class="fa ${statusIcon}"></i> ${statusLabel}
                        </span>
                    </div>
                    <div class="sub-card-meta">
                        <i class="fa fa-balance-scale mr-1"></i>${gt('weight_label', {pct: s.weight})} &bull;
                        <i class="fa fa-list mr-1"></i>${gt('steps_count', {count: s.steps.length})}
                    </div>
                    ${s.description ? `<div class="sub-card-desc">"${s.description}"</div>` : ''}
                    ${scoreHtml}
                    <div class="sub-actions mt-2">${actionsHtml}</div>
                </div>
            `);
        });
    }

    // ======== EDIT SUBTASK ========
    $(document).on('click', '.btn-edit-subtask', function() {
        $('#edit_subtask_id').val($(this).data('id'));
        $('#edit_sub_name').val($(this).data('name'));
        $('#edit_sub_weight').val($(this).data('weight'));
        $('#edit_sub_desc').val($(this).data('desc'));
        sxHideModal('#viewSubtasksModal');
        setTimeout(() => sxShowModal('#editSubtaskModal'), 350);
    });

    $('#editSubtaskForm').submit(function(e) {
        e.preventDefault();
        const id = $('#edit_subtask_id').val();
        $.post('{{ url("goals/member/update-subtask") }}/' + id, $(this).serialize(), function(res) {
            if (res.success) {
                sxHideModal('#editSubtaskModal');
                Swal.fire({icon:'success', title:G.update_subtask, text: res.message, toast:true, position:'top-end', showConfirmButton:false, timer:2500});
                location.reload();
            } else {
                Swal.fire(G.fail_load_subtasks, res.message, 'error');
            }
        });
    });

    // ======== CONDUCT / STEPS ========
    $(document).on('click', '.btn-conduct', function() {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        $('#conduct_subtask_id').val(id);
        $('#conduct_subtask_name').text(name);

        $('#steps_rows_container').empty();
        stepRowCount = 0;
        addStepRow();

        loadSteps(id);
        sxHideModal('#viewSubtasksModal');
        setTimeout(() => sxShowModal('#conductModal'), 350);
    });

    $('#btn_add_step_row').click(addStepRow);

    function addStepRow() {
        const row = `
            <div class="step-row">
                <div class="d-flex" style="gap:8px; align-items:flex-end;">
                    <div class="form-group mb-0" style="width:130px; flex-shrink:0;">
                        <label>${G.date}</label>
                        <input type="date" name="steps[${stepRowCount}][date]" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group mb-0" style="flex:1;">
                        <label>${G.what_did_you_do}</label>
                        <input type="text" name="steps[${stepRowCount}][step_description]" class="form-control form-control-sm" required placeholder="${G.describe_action}">
                    </div>
                    ${stepRowCount > 0 ? `<button type="button" class="btn btn-sm btn-outline-danger remove-step-row mb-0" style="min-height:36px; border-radius:0;"><i class="fa fa-times"></i></button>` : ''}
                </div>
            </div>
        `;
        $('#steps_rows_container').append(row);
        stepRowCount++;
    }

    $(document).on('click', '.remove-step-row', function() {
        $(this).closest('.step-row').remove();
    });

    function loadSteps(subtaskId) {
        const list = $('#steps_history_list');
        list.html('<div class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin"></i></div>');
        $.get('{{ url("goals/fetch-subtask-details") }}/' + subtaskId, function(data) {
            list.empty();
            if (!data.steps.length) {
                list.html('<div style="text-align:center; padding:20px; color:#ccc; font-size:0.82rem; border:1px dashed #ddd; border-radius:0;">' + G.no_steps + '</div>');
            } else {
                data.steps.forEach(step => {
                    list.append(`
                        <div class="step-entry">
                            <div class="step-entry-top">
                                <span class="step-date"><i class="fa fa-calendar"></i> ${step.date}</span>
                                <div class="step-btns">
                                    <button class="btn btn-xs btn-link text-info btn-edit-step p-0 mr-1" data-id="${step.id}" data-date="${step.date}" data-desc="${step.step_description}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-xs btn-link text-danger btn-delete-step p-0" data-id="${step.id}"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="step-desc">${step.step_description}</div>
                        </div>
                    `);
                });
            }
        });
    }

    $('#stepForm').submit(function(e) {
        e.preventDefault();
        const subtaskId = $('#conduct_subtask_id').val();
        const $btn = $('#btn_save_steps');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + G.loading);

        $.post('{{ url("goals/member/save-step") }}', $(this).serialize(), function(res) {
            $btn.prop('disabled', false).text(G.save_action_steps);
            if (res.success) {
                Swal.fire({icon:'success', title:G.save_action_steps, toast:true, position:'top-end', showConfirmButton:false, timer:2000});
                $('#steps_rows_container').empty();
                stepRowCount = 0;
                addStepRow();
                loadSteps(subtaskId);
                if (currentViewTaskId) loadSubtasks(currentViewTaskId);
            } else {
                Swal.fire(G.fail_load_subtasks, res.message || G.fail_load_subtasks, 'error');
            }
        }).fail(function(xhr) {
            $btn.prop('disabled', false).text(G.save_action_steps);
            const msg = xhr.responseJSON?.message || G.fail_load_subtasks;
            Swal.fire(G.fail_load_subtasks, msg, 'error');
        });
    });

    // Edit individual step
    $(document).on('click', '.btn-edit-step', function() {
        const id   = $(this).data('id');
        const date = $(this).data('date');
        const desc = $(this).data('desc');
        Swal.fire({
            title: G.edit,
            html: `
                <label style="font-size:0.82rem; font-weight:700; display:block; text-align:left; margin-bottom:4px;">${G.date}</label>
                <input type="date" id="swal_step_date" class="swal2-input" value="${date}" style="margin:0 0 10px; width:100%;">
                <label style="font-size:0.82rem; font-weight:700; display:block; text-align:left; margin-bottom:4px;">${G.description}</label>
                <textarea id="swal_step_desc" class="swal2-textarea" style="margin:0; width:100%;">${desc}</textarea>
            `,
            showCancelButton: true,
            confirmButtonText: G.update_subtask,
            cancelButtonText: G.cancel,
            confirmButtonColor: '#940000',
            preConfirm: () => ({
                date: document.getElementById('swal_step_date').value,
                step_description: document.getElementById('swal_step_desc').value
            })
        }).then(result => {
            if (result.isConfirmed) {
                $.post('{{ url("goals/member/update-step") }}/' + id, { ...result.value, _token: '{{ csrf_token() }}' }, () => {
                    loadSteps($('#conduct_subtask_id').val());
                });
            }
        });
    });

    // Delete individual step
    $(document).on('click', '.btn-delete-step', function() {
        const id = $(this).data('id');
        Swal.fire({ title: G.remove, icon:'warning', showCancelButton:true, confirmButtonColor:'#d33', confirmButtonText: G.remove, cancelButtonText: G.cancel })
        .then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("goals/member/delete-step") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: () => { loadSteps($('#conduct_subtask_id').val()); }
                });
            }
        });
    });

    // ======== SEND TO HOD ========
    $(document).on('click', '.btn-submit-hod', function() {
        const id = $(this).data('id');
        const isDirect = $(this).data('is-direct') == '1';
        const reviewer = isDirect ? G.admin : G.hod;

        Swal.fire({
            title: reviewer,
            text: gt('awaiting_review', {who: reviewer}),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: G.submitted,
            cancelButtonText: G.cancel
        }).then(result => {
            if (result.isConfirmed) {
                $.post('{{ url("goals/member/submit-subtask") }}/' + id, { _token: '{{ csrf_token() }}' }, res => {
                    if (res.success) {
                        Swal.fire({icon:'success', title:G.submitted, text: res.message, toast:true, position:'top-end', showConfirmButton:false, timer:2500});
                        if (currentViewTaskId) loadSubtasks(currentViewTaskId);
                    }
                });
            }
        });
    });

    // ======== DELETE SUBTASK ========
    $(document).on('click', '.btn-delete-subtask', function() {
        const id = $(this).data('id');
        Swal.fire({ title: G.remove, text: G.edit_subtask, icon:'warning', showCancelButton:true, confirmButtonColor:'#d33', confirmButtonText: G.remove, cancelButtonText: G.cancel })
        .then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("goals/member/delete-subtask") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: res => {
                        if (res.success) {
                            Swal.fire({icon:'success', toast:true, title:G.remove, position:'top-end', showConfirmButton:false, timer:2000});
                            location.reload();
                        } else {
                            Swal.fire(G.fail_load_subtasks, res.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
