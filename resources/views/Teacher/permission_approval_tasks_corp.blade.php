@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.permission_approvals').' - ShuleXpert')
@section('page_title', __('teacher.pages.permission_approvals'))
@section('page_breadcrumb', __('teacher.pages.permission_approvals_bc'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    .sx-approval-page { --maroon:#940000; --line:#e8e0e0; --soft:#faf7f7; --muted:#666; }
    .sx-approval-page .sx-panel {
        background:#fff; border:1px solid var(--line); border-top:4px solid var(--maroon);
        box-shadow:0 1px 2px rgba(80,0,0,.04),0 10px 28px rgba(80,0,0,.06); overflow:hidden;
    }
    .sx-approval-page .sx-panel-body { padding:1.05rem 1.15rem 1.25rem; }
    .sx-approval-page .sx-filter {
        border:1px solid var(--line); background:linear-gradient(180deg,#fff,var(--soft));
        padding:.95rem 1.05rem; margin-bottom:1rem; box-shadow:0 1px 4px rgba(80,0,0,.04);
    }
    .sx-approval-page .form-label {
        font-size:.7rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase;
        color:#666; margin-bottom:.3rem;
    }
    .sx-approval-page .form-control,.sx-approval-page .form-select,.sx-approval-page .btn,
    #approveModal .form-control,#rejectModal .form-control,#approveModal .btn,#rejectModal .btn,
    #approveModal .modal-content,#rejectModal .modal-content { border-radius:0!important; }
    .sx-approval-page .form-control,.sx-approval-page .form-select {
        min-height:42px; border:1px solid #d4caca; font-size:.9rem; font-weight:600; color:#111; background:#fff;
    }
    .sx-approval-page .form-control:focus,.sx-approval-page .form-select:focus {
        border-color:var(--maroon); box-shadow:0 0 0 .18rem rgba(148,0,0,.12);
    }
    .btn-primary-custom,.btn-primary-custom:hover,.btn-primary-custom:focus {
        background:linear-gradient(180deg,#a80000 0%,#940000 55%,#7f0000 100%)!important;
        border-color:#7a0000!important; color:#fff!important; font-weight:700!important;
        border-radius:0!important; box-shadow:0 4px 12px rgba(148,0,0,.18);
    }
    .form-loading {
        display:none; align-items:center; gap:12px; padding:10px 12px; border:1px solid rgba(148,0,0,.25);
        background:rgba(148,0,0,.05); margin-bottom:12px; font-weight:700;
    }
    .form-progress { position:relative; flex:1; height:8px; background:#f0f0f0; overflow:hidden; }
    .form-progress::after {
        content:""; position:absolute; left:-40%; width:40%; height:100%; background:#940000;
        animation:sxApprovalProgress 1.1s linear infinite;
    }
    @keyframes sxApprovalProgress { from{left:-40%} to{left:100%} }
    .sx-ep-table-wrap { border:1px solid var(--line); overflow:auto; background:#fff; box-shadow:0 2px 10px rgba(80,0,0,.04); }
    table.sx-ep-table { width:100%; margin:0; border-collapse:collapse; }
    table.sx-ep-table th {
        background:linear-gradient(180deg,#f7f2f2,#f1eaea); color:#333; font-size:.7rem; font-weight:800;
        text-transform:uppercase; letter-spacing:.04em; padding:.72rem .8rem; border-bottom:1px solid var(--line); white-space:nowrap;
    }
    table.sx-ep-table td {
        font-size:.88rem; font-weight:600; color:#111; padding:.8rem; border-bottom:1px solid #f0e8e8;
        vertical-align:middle; line-height:1.4; background:#fff;
    }
    table.sx-ep-table tbody tr:hover td { background:#fffafa; }
    .sx-ep-title { color:var(--maroon)!important; font-weight:800!important; font-size:.94rem; }
    .sx-ep-meta { display:block; margin-top:.14rem; font-size:.78rem; color:var(--muted)!important; font-weight:600!important; }
    .sx-approval-reason { max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:.35rem; }
    .sx-ep-actions { display:flex; justify-content:flex-end; align-items:center; }
    .tag { display:inline-block; padding:.2rem .45rem; font-size:.68rem; font-weight:700; border-radius:0; line-height:1.3; }
    .tag-pending { background:#fff3cd; color:#7a5a00; border:1px solid #edd89a; }
    .tag-waiting { background:#e8f3fa; color:#175d82; border:1px solid #bfdceb; }
    .tag-view { background:#f0f0f0; color:#555; border:1px solid #d7d7d7; }
    .tag-neutral { background:#f7f2f2; color:#5b3a3a; border:1px solid #e5dada; }
    .sx-dots-wrap { position:relative; }
    .sx-dots-btn {
        width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;
        border:1px solid #eadfdf; background:#fff; color:#940000; padding:0; line-height:1;
    }
    .sx-dots-btn:hover,.sx-dots-wrap.is-open .sx-dots-btn { background:#fff5f5; border-color:#940000; }
    .sx-dots-btn i { font-size:1.05rem; color:#940000; }
    .sx-dots-menu {
        display:none; position:fixed; min-width:190px; z-index:2050; background:#fff; border:1px solid #eadfdf;
        box-shadow:0 10px 24px rgba(80,0,0,.16); padding:.35rem 0;
    }
    .sx-dots-wrap.is-open .sx-dots-menu { display:block; }
    .sx-dots-link {
        display:flex; align-items:center; gap:.45rem; width:100%; border:0; background:transparent; text-align:left;
        padding:.55rem .85rem; font-size:.84rem; font-weight:700; color:#333!important; text-decoration:none!important; white-space:nowrap;
    }
    button.sx-dots-link { cursor:pointer; }
    .sx-dots-link i { color:#940000; }
    .sx-dots-link:hover { background:#fff5f5; color:#940000!important; }
    .sx-dots-link.is-success i { color:#198754; } .sx-dots-link.is-danger i { color:#dc3545; }
    .sx-dots-link.is-disabled { color:#999!important; cursor:not-allowed; }
    .sx-dots-link.is-disabled i { color:#999; }
    .sx-dots-divider { display:block; border-top:1px solid #eee5e5; margin:.3rem 0; }
    .sx-ep-empty { padding:2.4rem 1rem; text-align:center; color:var(--muted); font-weight:600; }
    .sx-ep-empty i { display:block; color:#940000; font-size:1.5rem; margin-bottom:.45rem; }
    .sx-perm-card {
        border:1px solid var(--line); border-left:4px solid #940000; background:#fff; padding:.95rem 1rem;
        height:100%; box-shadow:0 2px 8px rgba(80,0,0,.05);
    }
    .view-toggle-btn.active { background:#940000!important; border-color:#940000!important; color:#fff!important; }
    .sx-action-modal { border:1px solid #eadfdf!important; box-shadow:0 18px 40px rgba(80,0,0,.14)!important; }
    .sx-action-modal .modal-header {
        background:linear-gradient(180deg,#fff8f8,#fff)!important; border-bottom:1px solid #eadfdf!important; color:#940000!important;
    }
    .sx-action-modal .modal-title { color:#940000!important; font-weight:800!important; }
    .modal-loading {
        display:none; position:absolute; inset:0; background:rgba(255,255,255,.92); z-index:20;
        align-items:center; justify-content:center; flex-direction:column;
    }
    .modal-content.is-ajax-loading { position:relative; }
    @media(max-width:991px){ #perm-list-view{display:none!important} #perm-grid-view{display:block!important} }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-approval-page,
    html[data-bs-theme="dark"] .sx-approval-page {
        --line: #3a2a2e;
        --soft: #2a1c20;
        --muted: #d7c4c8;
    }
    html[data-sx-theme="dark"] .sx-approval-page .sx-panel,
    html[data-bs-theme="dark"] .sx-approval-page .sx-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-approval-page .sx-filter,
    html[data-bs-theme="dark"] .sx-approval-page .sx-filter {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-approval-page .form-label,
    html[data-bs-theme="dark"] .sx-approval-page .form-label,
    html[data-sx-theme="dark"] #approveModal .form-label,
    html[data-bs-theme="dark"] #approveModal .form-label,
    html[data-sx-theme="dark"] #rejectModal .form-label,
    html[data-bs-theme="dark"] #rejectModal .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-approval-page .form-control,
    html[data-bs-theme="dark"] .sx-approval-page .form-control,
    html[data-sx-theme="dark"] .sx-approval-page .form-select,
    html[data-bs-theme="dark"] .sx-approval-page .form-select,
    html[data-sx-theme="dark"] #approveModal .form-control,
    html[data-bs-theme="dark"] #approveModal .form-control,
    html[data-sx-theme="dark"] #rejectModal .form-control,
    html[data-bs-theme="dark"] #rejectModal .form-control {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-approval-page .form-select option,
    html[data-bs-theme="dark"] .sx-approval-page .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-approval-page .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-approval-page .btn-outline-secondary {
        color: #e8c9cd !important;
        border-color: #4a383c !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-approval-page .text-muted,
    html[data-bs-theme="dark"] .sx-approval-page .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .form-loading,
    html[data-bs-theme="dark"] .form-loading {
        background: rgba(148, 0, 0, 0.18) !important;
        border-color: #4a383c !important;
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .form-progress,
    html[data-bs-theme="dark"] .form-progress {
        background: #2a2226 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-table-wrap,
    html[data-bs-theme="dark"] .sx-ep-table-wrap {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] table.sx-ep-table th,
    html[data-bs-theme="dark"] table.sx-ep-table th {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        border-bottom-color: #6e0000 !important;
    }
    html[data-sx-theme="dark"] table.sx-ep-table td,
    html[data-bs-theme="dark"] table.sx-ep-table td {
        background: #24181c !important;
        color: #f3e9eb !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] table.sx-ep-table tbody tr:hover td,
    html[data-bs-theme="dark"] table.sx-ep-table tbody tr:hover td {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-title,
    html[data-bs-theme="dark"] .sx-ep-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-meta,
    html[data-bs-theme="dark"] .sx-ep-meta {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-empty,
    html[data-bs-theme="dark"] .sx-ep-empty {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-empty i,
    html[data-bs-theme="dark"] .sx-ep-empty i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-perm-card,
    html[data-bs-theme="dark"] .sx-perm-card {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-dots-btn,
    html[data-bs-theme="dark"] .sx-dots-btn {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-btn:hover,
    html[data-bs-theme="dark"] .sx-dots-btn:hover,
    html[data-sx-theme="dark"] .sx-dots-wrap.is-open .sx-dots-btn,
    html[data-bs-theme="dark"] .sx-dots-wrap.is-open .sx-dots-btn {
        background: #2a1c20 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-btn i,
    html[data-bs-theme="dark"] .sx-dots-btn i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-menu,
    html[data-bs-theme="dark"] .sx-dots-menu {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: 0 10px 24px rgba(0,0,0,.45) !important;
    }
    html[data-sx-theme="dark"] .sx-dots-link,
    html[data-bs-theme="dark"] .sx-dots-link {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-dots-link i,
    html[data-bs-theme="dark"] .sx-dots-link i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-link:hover,
    html[data-bs-theme="dark"] .sx-dots-link:hover {
        background: #2a1c20 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-dots-link.is-disabled,
    html[data-bs-theme="dark"] .sx-dots-link.is-disabled,
    html[data-sx-theme="dark"] .sx-dots-link.is-disabled i,
    html[data-bs-theme="dark"] .sx-dots-link.is-disabled i {
        color: #a8989c !important;
    }
    html[data-sx-theme="dark"] .sx-dots-divider,
    html[data-bs-theme="dark"] .sx-dots-divider {
        border-top-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-action-modal,
    html[data-bs-theme="dark"] .sx-action-modal {
        background: #24181c !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-action-modal .modal-header,
    html[data-bs-theme="dark"] .sx-action-modal .modal-header {
        background: #2a1c20 !important;
        border-bottom-color: #3a2a2e !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-action-modal .modal-title,
    html[data-bs-theme="dark"] .sx-action-modal .modal-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-action-modal .modal-body,
    html[data-bs-theme="dark"] .sx-action-modal .modal-body,
    html[data-sx-theme="dark"] .sx-action-modal .modal-footer,
    html[data-bs-theme="dark"] .sx-action-modal .modal-footer {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .modal-loading,
    html[data-bs-theme="dark"] .modal-loading {
        background: rgba(28, 20, 23, .92) !important;
        color: #e8c9cd !important;
    }
</style>
@endpush

@section('content')
<div class="sx-approval-page">
    <div class="sx-panel">
        <div class="sx-panel-body">
            <div class="sx-filter">
                <form id="filterForm" method="GET" action="{{ route('admin.hr.permissions.pending_approvals') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3 col-6">
                            <label class="form-label">{{ __('teacher.hr.filter_by') }}</label>
                            <select name="filter_type" id="filter_type" class="form-select" onchange="toggleFilterInputs()">
                                <option value="all" {{ ($filterType ?? '') === 'all' ? 'selected' : '' }}>{{ __('teacher.hr.all_pending') }}</option>
                                <option value="range" {{ ($filterType ?? '') === 'range' ? 'selected' : '' }}>{{ __('teacher.hr.date_range') }}</option>
                                <option value="month" {{ ($filterType ?? '') === 'month' ? 'selected' : '' }}>{{ __('teacher.hr.specific_month') }}</option>
                                <option value="year" {{ ($filterType ?? '') === 'year' ? 'selected' : '' }}>{{ __('teacher.hr.specific_year') }}</option>
                            </select>
                        </div>
                        <div id="range_inputs" class="col-md-6 filter-input-group" style="display:{{ ($filterType ?? '') === 'range' ? 'flex' : 'none' }};gap:10px">
                            <div class="flex-grow-1"><label class="form-label">{{ __('teacher.from') }}</label><input type="date" name="date_from" class="form-control" value="{{ $dateFrom ?? '' }}"></div>
                            <div class="flex-grow-1"><label class="form-label">{{ __('teacher.to') }}</label><input type="date" name="date_to" class="form-control" value="{{ $dateTo ?? '' }}"></div>
                        </div>
                        <div id="month_inputs" class="col-md-6 filter-input-group" style="display:{{ ($filterType ?? '') === 'month' ? 'flex' : 'none' }};gap:10px">
                            <div class="flex-grow-1">
                                <label class="form-label">{{ __('teacher.hr.month') }}</label>
                                <select name="filter_month" class="form-select">
                                    @php $hrMonths = __('teacher.hr.months'); @endphp
                                    @foreach($hrMonths as $index => $monthName)
                                        <option value="{{ $index + 1 }}" {{ ($filterMonth ?? date('n')) == $index + 1 ? 'selected' : '' }}>{{ $monthName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label">{{ __('teacher.year') }}</label>
                                <select name="filter_month_year" class="form-select">
                                    @for($y=date('Y');$y>=date('Y')-5;$y--)<option value="{{ $y }}" {{ ($filterMonthYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor
                                </select>
                            </div>
                        </div>
                        <div id="year_inputs" class="col-md-3 filter-input-group" style="display:{{ ($filterType ?? '') === 'year' ? 'block' : 'none' }}">
                            <label class="form-label">{{ __('teacher.year') }}</label>
                            <select name="filter_year" class="form-select">
                                @for($y=date('Y');$y>=date('Y')-5;$y--)<option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor
                            </select>
                        </div>
                        <div class="col-md-3 col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary-custom flex-grow-1"><i class="bi bi-funnel"></i> {{ __('teacher.hr.apply') }}</button>
                            <button type="button" class="btn btn-outline-secondary flex-grow-1" onclick="resetFilters()"><i class="bi bi-arrow-counterclockwise"></i> {{ __('teacher.hr.reset') }}</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="form-loading" id="approvalLoading"><span><i class="bi bi-arrow-repeat"></i> {{ __('teacher.hr.filtering') }}</span><div class="form-progress"></div></div>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <small class="text-muted fw-semibold">{{ __('teacher.hr.pending_count', ['count' => $permissions->count()]) }}</small>
                <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('teacher.hr.view_toggle') }}">
                    <button type="button" class="btn btn-outline-secondary view-toggle-btn" data-view="list" title="{{ __('teacher.hr.list_view') }}"><i class="bi bi-list-ul"></i></button>
                    <button type="button" class="btn btn-outline-secondary view-toggle-btn" data-view="grid" title="{{ __('teacher.hr.grid_view') }}"><i class="bi bi-grid-3x3-gap"></i></button>
                </div>
            </div>

            <div id="perm-list-view">
                <div class="sx-ep-table-wrap">
                    <table class="sx-ep-table">
                        <thead><tr><th>{{ __('teacher.hr.requester') }}</th><th>{{ __('teacher.hr.type') }}</th><th>{{ __('teacher.hr.date_time') }}</th><th>{{ __('teacher.hr.reason') }}</th><th class="text-end">{{ __('teacher.actions') }}</th></tr></thead>
                        <tbody>
                            @include('Teacher.partials.permission_approval_list_corp', compact('permissions', 'userType', 'myRoleIDs', 'myProfessionID'))
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="perm-grid-view" style="display:none">
                <div class="row g-3">
                @forelse($permissions as $permission)
                    @php
                        $isStudentPerm = $permission->is_student_permission ?? false;
                        $requesterName = __('teacher.hr.na');
                        if ($isStudentPerm) {
                            $requesterName = $permission->student_name ?? __('teacher.hr.na');
                            $requesterInfo = __('teacher.student') . ' | ' . ($permission->class_name ?? __('teacher.hr.na'));
                        } elseif ($permission->requester_type === 'student' && $permission->student) {
                            $requesterName = trim($permission->student->first_name . ' ' . $permission->student->last_name);
                            $requesterInfo = ucfirst($permission->requester_type);
                            if ($permission->student->subclass) $requesterInfo .= ' | ' . ($permission->student->subclass->class->class_name ?? '') . ' ' . ($permission->student->subclass->subclass_name ?? '');
                        } elseif ($permission->requester_type === 'staff' && $permission->staff) {
                            $requesterName = trim($permission->staff->first_name . ' ' . $permission->staff->last_name);
                            $requesterInfo = ucfirst($permission->requester_type);
                        } elseif ($permission->teacher) {
                            $requesterName = trim($permission->teacher->first_name . ' ' . $permission->teacher->last_name);
                            $requesterInfo = ucfirst($permission->requester_type);
                        } else {
                            $requesterInfo = ucfirst($permission->requester_type ?? __('teacher.hr.unknown'));
                        }
                        $permissionType = $isStudentPerm ? 'student' : $permission->requester_type;
                        $myChainOrders = \Illuminate\Support\Facades\Schema::hasTable('permission_approval_chains')
                            ? DB::table('permission_approval_chains')->where('schoolID', $permission->schoolID)->where('permission_type', $permissionType)
                                ->where(function($q) use ($myRoleIDs, $myProfessionID) {
                                    if (!empty($myRoleIDs)) $q->orWhereIn('role_id', $myRoleIDs);
                                    if ($myProfessionID) $q->orWhere('profession_id', $myProfessionID);
                                })->pluck('approval_order')->toArray()
                            : [];
                        $isMyTurn = in_array($permission->current_approval_order, $myChainOrders);
                        $viewOnlyClassTeacher = $isStudentPerm && ($permission->requester_type ?? '') === 'parent' && session('teacherID')
                            && (int)session('teacherID') === (int)($permission->class_teacher_id ?? 0);
                        if ($viewOnlyClassTeacher) $isMyTurn = false;
                        $cardStart = $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j M Y') : '-';
                        $cardEnd = $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j M Y') : '-';
                        $reasonLabel = match($permission->reason_type) {
                            'medical' => __('teacher.hr.medical'),
                            'official' => __('teacher.hr.official'),
                            'professional' => __('teacher.hr.professional'),
                            'emergency' => __('teacher.hr.emergency'),
                            'other' => __('teacher.hr.other'),
                            default => ucfirst($permission->reason_type),
                        };
                        $cardPayload = [
                            'id'=>$permission->permissionID, 'requester_name'=>$requesterName, 'requester_info'=>$requesterInfo,
                            'time_mode'=>ucfirst($permission->time_mode), 'days_count'=>$permission->days_count,
                            'start_date'=>$permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
                            'end_date'=>$permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
                            'start_time'=>$permission->start_time, 'end_time'=>$permission->end_time,
                            'reason_type'=>$reasonLabel, 'reason_description'=>$permission->reason_description,
                            'status'=>ucfirst($permission->status),
                            'requested_at'=>$permission->created_at ? \Carbon\Carbon::parse($permission->created_at)->format('j F Y H:i') : __('teacher.hr.na'),
                            'is_student_permission'=>$isStudentPerm,
                        ];
                    @endphp
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="sx-perm-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div><div class="sx-ep-title">{{ $requesterName }}</div><span class="sx-ep-meta">{{ $requesterInfo }}</span></div>
                                <div class="sx-ep-actions sx-dots-wrap">
                                    <button type="button" class="sx-dots-btn" aria-label="{{ __('teacher.actions') }}" onclick="togglePermDots(this)"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="sx-dots-menu">
                                        @if($isMyTurn)
                                            <button type="button" class="sx-dots-link is-success" onclick="openApprovalModal({{ $permission->permissionID }}, {{ $isStudentPerm ? 'true' : 'false' }});closePermDots()"><i class="bi bi-check-circle"></i> {{ __('teacher.hr.approve') }}</button>
                                            <button type="button" class="sx-dots-link is-danger" onclick="openRejectionModal({{ $permission->permissionID }}, {{ $isStudentPerm ? 'true' : 'false' }});closePermDots()"><i class="bi bi-x-circle"></i> {{ __('teacher.hr.reject') }}</button>
                                        @else
                                            <span class="sx-dots-link is-disabled"><i class="bi bi-{{ $viewOnlyClassTeacher ? 'eye' : 'lock' }}"></i> {{ $viewOnlyClassTeacher ? __('teacher.hr.view_only') : __('teacher.hr.approval_locked') }}</span>
                                        @endif
                                        <span class="sx-dots-divider"></span>
                                        <button type="button" class="sx-dots-link" onclick="viewPermissionProgress({{ $permission->permissionID }}, {{ $isStudentPerm ? 'true' : 'false' }});closePermDots()"><i class="bi bi-diagram-3"></i> {{ __('teacher.hr.view_progress') }}</button>
                                        <button type="button" class="sx-dots-link" onclick='viewPermissionDetails(@json($cardPayload));closePermDots()'><i class="bi bi-card-list"></i> {{ __('teacher.hr.view_details') }}</button>
                                        <a class="sx-dots-link" href="{{ $isStudentPerm ? route('admin.student_permissions.download', $permission->permissionID) : route('admin.permissions.download', $permission->permissionID) }}"><i class="bi bi-file-earmark-pdf"></i> {{ __('teacher.hr.download_pdf') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2"><span class="tag {{ $isMyTurn ? 'tag-pending' : ($viewOnlyClassTeacher ? 'tag-view' : 'tag-waiting') }}">{{ $isMyTurn ? __('teacher.hr.your_turn') : ($viewOnlyClassTeacher ? __('teacher.hr.view_only') : __('teacher.hr.awaiting_previous')) }}</span></div>
                            <div class="sx-ep-meta mb-2"><i class="bi bi-calendar3"></i>
                                @if($permission->time_mode === 'days') {{ $cardStart }} → {{ $cardEnd }} · {{ $permission->days_count }} {{ __('teacher.hr.days') }}
                                @else {{ $cardStart }} · {{ $permission->start_time ?? '-' }} → {{ $permission->end_time ?? '-' }} @endif
                            </div>
                            <div class="sx-ep-title">{{ $reasonLabel }}</div>
                            <div class="sx-ep-meta">{{ \Illuminate\Support\Str::limit($permission->reason_description, 100) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><div class="sx-ep-empty"><i class="bi bi-inbox"></i>{{ __('teacher.hr.no_pending') }}</div></div>
                @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content sx-action-modal">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-check-circle"></i> {{ __('teacher.hr.approve_permission') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.close') }}" onclick="sxHideModal('#approveModal')"></button></div>
        <form id="approveForm">@csrf<input type="hidden" name="permissionID" id="approvePermissionID"><input type="hidden" name="is_student_permission" id="isStudentPermission" value="0">
            <div class="modal-body position-relative"><div class="modal-loading"><i class="bi bi-arrow-repeat fs-2"></i><p class="mt-2">{{ __('teacher.hr.processing') }}</p></div><label class="form-label">{{ __('teacher.hr.remarks') }}</label><textarea class="form-control" name="admin_response" rows="3" required placeholder="{{ __('teacher.hr.approval_remarks_ph') }}"></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="sxHideModal('#approveModal')">{{ __('teacher.cancel') }}</button><button type="submit" class="btn btn-primary-custom">{{ __('teacher.hr.confirm_approval') }}</button></div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content sx-action-modal">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-x-circle"></i> {{ __('teacher.hr.reject_permission') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.close') }}" onclick="sxHideModal('#rejectModal')"></button></div>
        <form id="rejectForm">@csrf<input type="hidden" name="permissionID" id="rejectPermissionID"><input type="hidden" name="is_student_permission" id="isStudentPermissionReject" value="0">
            <div class="modal-body position-relative"><div class="modal-loading"><i class="bi bi-arrow-repeat fs-2"></i><p class="mt-2">{{ __('teacher.hr.processing') }}</p></div><label class="form-label">{{ __('teacher.hr.rejection_reason') }}</label><textarea class="form-control" name="admin_response" rows="3" required placeholder="{{ __('teacher.hr.rejection_reason_ph') }}"></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="sxHideModal('#rejectModal')">{{ __('teacher.cancel') }}</button><button type="submit" class="btn btn-danger rounded-0">{{ __('teacher.hr.confirm_rejection') }}</button></div>
        </form>
    </div></div>
</div>

@include('partials.permission_details_modal')
@include('partials.permission_progress_modal')
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-approval-page .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-approval-page .form-label {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th,
html[data-bs-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th {
    background: #940000 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-approval-page .sx-ep-title,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-approval-page .sx-ep-title {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-approval-page .sx-ep-meta,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-approval-page .sx-ep-meta,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-approval-page .text-muted,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-approval-page .text-muted {
    color: #d7c4c8 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-pending,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-pending {
    background: #fff3cd !important; color: #7a5a00 !important; -webkit-text-fill-color: #7a5a00 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-waiting,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-waiting {
    background: #e8f3fa !important; color: #175d82 !important; -webkit-text-fill-color: #175d82 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-view,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-view {
    background: #eee !important; color: #555 !important; -webkit-text-fill-color: #555 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-neutral,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-neutral {
    background: #f7f2f2 !important; color: #5b3a3a !important; -webkit-text-fill-color: #5b3a3a !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-dots-menu .sx-dots-link,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-dots-menu .sx-dots-link {
    color: #f3e9eb !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-action-modal .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-action-modal .form-label,
html[data-sx-theme="dark"] #approveModal .form-label,
html[data-bs-theme="dark"] #approveModal .form-label,
html[data-sx-theme="dark"] #rejectModal .form-label,
html[data-bs-theme="dark"] #rejectModal .form-label {
    color: #ffb4b4 !important;
}
</style>
@endsection

@push('scripts')
<script>
const i18n = @json(__('teacher.hr'));
window.sxShowModal=function(el){const node=typeof el==='string'?document.querySelector(el):el;if(!node)return;if(window.bootstrap&&bootstrap.Modal){bootstrap.Modal.getOrCreateInstance(node).show();return}if(window.jQuery&&jQuery.fn.modal)jQuery(node).modal('show')};
window.sxHideModal=function(el){const node=typeof el==='string'?document.querySelector(el):el;if(!node)return;if(window.bootstrap&&bootstrap.Modal){const inst=bootstrap.Modal.getInstance(node);if(inst)inst.hide();return}if(window.jQuery&&jQuery.fn.modal)jQuery(node).modal('hide')};
window.togglePermDots=function(btn){
    const wrap=btn.closest('.sx-dots-wrap'),menu=wrap.querySelector('.sx-dots-menu'),wasOpen=wrap.classList.contains('is-open');
    closePermDots();if(wasOpen)return;wrap.classList.add('is-open');
    const rect=btn.getBoundingClientRect(),menuWidth=190,menuHeight=menu.offsetHeight||250;
    let left=Math.max(8,Math.min(rect.right-menuWidth,window.innerWidth-menuWidth-8));
    let top=rect.bottom+4;if(top+menuHeight>window.innerHeight-8)top=Math.max(8,rect.top-menuHeight-4);
    menu.style.left=left+'px';menu.style.top=top+'px';
};
window.closePermDots=function(){document.querySelectorAll('.sx-dots-wrap.is-open').forEach(el=>el.classList.remove('is-open'))};
document.addEventListener('click',e=>{if(!e.target.closest('.sx-dots-wrap'))closePermDots()});
window.addEventListener('scroll',closePermDots,true);window.addEventListener('resize',closePermDots);

function toggleFilterInputs(){
    const type=document.getElementById('filter_type').value;
    document.querySelectorAll('.filter-input-group').forEach(el=>el.style.display='none');
    if(type==='range')document.getElementById('range_inputs').style.display='flex';
    else if(type==='month')document.getElementById('month_inputs').style.display='flex';
    else if(type==='year')document.getElementById('year_inputs').style.display='block';
}
function resetFilters(){window.location.href="{{ route('admin.hr.permissions.pending_approvals') }}"}
function initApprovalViewToggle(){
    const key='approval_tasks_view_pref',mobile=window.innerWidth<=991;
    function apply(view){
        document.getElementById('perm-list-view').style.display=view==='list'?'block':'none';
        document.getElementById('perm-grid-view').style.display=view==='grid'?'block':'none';
        document.querySelectorAll('.view-toggle-btn').forEach(btn=>btn.classList.toggle('active',btn.dataset.view===view));
        sessionStorage.setItem(key,view);
    }
    apply(sessionStorage.getItem(key)||(mobile?'grid':'list'));
    document.querySelectorAll('.view-toggle-btn').forEach(btn=>btn.addEventListener('click',()=>apply(btn.dataset.view)));
}
function openApprovalModal(id,isStudentPermission=false){
    document.getElementById('approvePermissionID').value=id;
    document.getElementById('isStudentPermission').value=isStudentPermission?'1':'0';
    sxShowModal('#approveModal');
}
function openRejectionModal(id,isStudentPermission=false){
    document.getElementById('rejectPermissionID').value=id;
    document.getElementById('isStudentPermissionReject').value=isStudentPermission?'1':'0';
    sxShowModal('#rejectModal');
}
function submitApprovalAction(url,form){
    const $=window.jQuery;if(typeof $!=='function')return;
    const $form=$(form),modalContent=$form.closest('.modal-content')[0],loading=$form.find('.modal-loading')[0],bar=document.getElementById('approvalLoading'),$button=$form.find('button[type="submit"]');
    modalContent.classList.add('is-ajax-loading');loading.style.display='flex';$button.prop('disabled',true);const label=$button.html();$button.html('<i class="bi bi-arrow-repeat"></i> '+(i18n.processing||'Processing...'));
    $.ajax({url:url,method:'POST',data:$form.serialize(),headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}})
    .done(response=>{if(response.success){Swal.fire({icon:'success',title:i18n.success,text:response.message,confirmButtonColor:'#940000'}).then(()=>location.reload())}else Swal.fire({icon:'error',title:i18n.failed,text:response.message||i18n.operation_failed,confirmButtonColor:'#940000'})})
    .fail(xhr=>Swal.fire({icon:'error',title:i18n.error,text:xhr.responseJSON?.message||i18n.unexpected_error,confirmButtonColor:'#940000'}))
    .always(()=>{loading.style.display='none';modalContent.classList.remove('is-ajax-loading');if(bar)bar.style.display='none';$button.prop('disabled',false).html(label);sxHideModal(form.closest('.modal'))});
}
document.getElementById('filterForm').addEventListener('submit',()=>document.getElementById('approvalLoading').style.display='flex');
document.getElementById('approveForm').addEventListener('submit',function(e){e.preventDefault();submitApprovalAction(@json(route('admin.permissions.approve')),this)});
document.getElementById('rejectForm').addEventListener('submit',function(e){e.preventDefault();submitApprovalAction(@json(route('admin.permissions.reject')),this)});
initApprovalViewToggle();
</script>
@endpush
