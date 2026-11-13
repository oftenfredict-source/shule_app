@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.supervise').' - ShuleXpert')
@section('page_title', __('teacher.pages.supervise'))
@section('page_breadcrumb', __('teacher.pages.supervise_bc'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-supervise,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-supervise p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-supervise span,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-supervise small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-supervise strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-supervise label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-supervise td,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-supervise th {
        color: #111 !important;
    }

    .sx-teacher-supervise .sx-sup-toolbar,
    .sx-teacher-supervise .sx-sup-panel {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
        border-top: 4px solid #940000 !important;
        margin-bottom: 0.85rem;
    }
    .sx-teacher-supervise .sx-sup-toolbar {
        padding: 0.9rem 1.1rem;
    }
    .sx-teacher-supervise .form-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.3rem;
    }
    .sx-teacher-supervise .form-select,
    .sx-teacher-supervise .btn {
        border-radius: 0 !important;
    }
    .sx-teacher-supervise .form-select {
        min-height: 42px;
        font-weight: 700;
        border: 1px solid #cfcfcf;
        color: #111 !important;
    }
    .sx-teacher-supervise .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12);
    }
    .sx-teacher-supervise .btn-outline-secondary {
        min-height: 42px;
        font-weight: 800;
        border-color: #cfcfcf;
        color: #333 !important;
    }
    .sx-teacher-supervise .btn-outline-secondary:hover {
        background: #f5f5f5;
        border-color: #940000;
        color: #940000 !important;
    }
    .sx-teacher-supervise .sx-sup-panel {
        overflow: hidden;
    }
    .sx-teacher-supervise .sx-sup-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem 1rem;
        padding: 0.75rem 1.1rem;
        border-bottom: 1px solid #eee;
        background: #fff5f5;
    }
    .sx-teacher-supervise .sx-sup-head h3 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        color: #940000 !important;
    }
    .sx-teacher-supervise .sx-sup-head .count {
        font-size: 0.82rem;
        font-weight: 700;
        color: #555 !important;
    }
    .sx-teacher-supervise .table {
        margin: 0;
    }
    .sx-teacher-supervise .table thead th {
        background: #f3f3f3;
        color: #222 !important;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border-color: #e6e6e6;
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.65rem 0.75rem;
    }
    .sx-teacher-supervise .table tbody td {
        font-size: 0.88rem;
        font-weight: 600;
        color: #111 !important;
        border-color: #eee;
        vertical-align: middle;
        padding: 0.7rem 0.75rem;
        line-height: 1.35;
    }
    .sx-teacher-supervise .sx-sub {
        font-weight: 800;
        color: #940000 !important;
        font-size: 0.92rem;
    }
    .sx-teacher-supervise .sx-meta {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #666 !important;
    }
    .sx-teacher-supervise .sx-time {
        font-weight: 800;
        white-space: nowrap;
    }
    .sx-teacher-supervise .tag {
        display: inline-block;
        padding: 0.18rem 0.45rem;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 0;
        line-height: 1.3;
        white-space: nowrap;
    }
    .sx-teacher-supervise .tag-active { background: #e8f6ec; color: #146c2e !important; }
    .sx-teacher-supervise .tag-upcoming { background: #e7f5f8; color: #0b6e7c !important; }
    .sx-teacher-supervise .tag-past { background: #eee; color: #555 !important; }
    .sx-teacher-supervise .tag-exam { background: #e7f0ff; color: #1a4d9b !important; }
    .sx-teacher-supervise .tag-test { background: #fff3cd; color: #7a5a00 !important; }
    .sx-teacher-supervise .sx-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        justify-content: flex-end;
    }
    .sx-teacher-supervise .btn-outline-maroon {
        border: 1px solid #940000;
        color: #940000 !important;
        background: #fff;
        font-weight: 800;
        font-size: 0.78rem;
        padding: 0.35rem 0.65rem;
    }
    .sx-teacher-supervise .btn-outline-maroon:hover {
        background: #fff5f5;
        color: #940000 !important;
    }
    .sx-teacher-supervise .btn-maroon {
        background: #940000 !important;
        border: 1px solid #940000;
        color: #fff !important;
        font-weight: 800;
        font-size: 0.78rem;
        padding: 0.35rem 0.65rem;
    }
    .sx-teacher-supervise .btn-maroon,
    .sx-teacher-supervise .btn-maroon * {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    .sx-teacher-supervise .btn-maroon:hover {
        background: #7b0000 !important;
        border-color: #7b0000;
    }
    .sx-teacher-supervise .sx-empty {
        padding: 2rem 1.25rem;
        text-align: center;
    }
    .sx-teacher-supervise .sx-empty i {
        font-size: 2rem;
        color: #940000;
        margin-bottom: 0.5rem;
    }
    .sx-teacher-supervise .sx-empty p {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #555 !important;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-sup-toolbar,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-sup-toolbar,
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-sup-panel,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-sup-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .form-label,
    html[data-bs-theme="dark"] .sx-teacher-supervise .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .form-select,
    html[data-bs-theme="dark"] .sx-teacher-supervise .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-supervise .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-teacher-supervise .btn-outline-secondary {
        color: #e8c9cd !important;
        border-color: #4a383c !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] .sx-teacher-supervise .btn-outline-secondary:hover {
        background: #2a1c20 !important;
        border-color: #940000 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-sup-head,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-sup-head {
        background: #2a1c20 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-sup-head h3,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-sup-head h3 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-sup-head .count,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-sup-head .count {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .table thead th,
    html[data-bs-theme="dark"] .sx-teacher-supervise .table thead th {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        border-color: #6e0000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .table tbody td,
    html[data-bs-theme="dark"] .sx-teacher-supervise .table tbody td {
        color: #f3e9eb !important;
        border-color: #3a2a2e !important;
        background: #24181c !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-sub,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-sub {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-meta,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-meta {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-time,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-time {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .btn-outline-maroon,
    html[data-bs-theme="dark"] .sx-teacher-supervise .btn-outline-maroon {
        background: transparent !important;
        color: #ffb4b4 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .btn-outline-maroon:hover,
    html[data-bs-theme="dark"] .sx-teacher-supervise .btn-outline-maroon:hover {
        background: #2a1c20 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-empty i,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-empty i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-supervise .sx-empty p,
    html[data-bs-theme="dark"] .sx-teacher-supervise .sx-empty p {
        color: #d7c4c8 !important;
    }
</style>
@endpush

@section('content')
<div class="sx-teacher-supervise">
    <div class="sx-sup-toolbar">
        <form action="{{ route('supervise_exams') }}" method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="yearFilter">{{ __('teacher.supervise.year') }}</label>
                    <select name="year" id="yearFilter" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('teacher.supervise.all_years') }}</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" @selected((string)$selectedYear === (string)$year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="categoryFilter">{{ __('teacher.supervise.exam_type') }}</label>
                    <select name="category" id="categoryFilter" class="form-select" onchange="this.form.submit()">
                        <option value="all" @selected($selectedCategory == 'all')>{{ __('teacher.supervise.all_types') }}</option>
                        <option value="school_exam" @selected($selectedCategory == 'school_exam')>{{ __('teacher.supervise.standard_exam') }}</option>
                        <option value="test" @selected($selectedCategory == 'test')>{{ __('teacher.supervise.weekly_monthly_test') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('supervise_exams') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> {{ __('teacher.supervise.reset_filters') }}
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="sx-sup-panel">
        <div class="sx-sup-head">
            <h3><i class="bi bi-calendar-event me-1"></i> {{ __('teacher.supervise.supervision_assignments') }}</h3>
            <span class="count">{{ $assignments->count() }} {{ $assignments->count() === 1 ? __('teacher.supervise.session') : __('teacher.supervise.sessions') }}</span>
        </div>

        @if($assignments->isEmpty())
            <div class="sx-empty">
                <i class="bi bi-info-circle"></i>
                <p>{{ __('teacher.supervise.no_assignments') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('teacher.supervise.date_time') }}</th>
                            <th>{{ __('teacher.supervise.subject_exam') }}</th>
                            <th>{{ __('teacher.supervise.hall_group') }}</th>
                            <th>{{ __('teacher.supervise.type') }}</th>
                            <th>{{ __('teacher.status') }}</th>
                            <th class="text-end">{{ __('teacher.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                            @php
                                $isActive = (bool) $assignment->is_active;
                                $isPast = isset($assignment->is_past) && $assignment->is_past;
                                $viewStudentsUrl = route('supervise_exam.view_students', [
                                    'hall_id' => $assignment->exam_hallID,
                                    'subject_id' => $assignment->subjectID,
                                    'timetable_id' => $assignment->exam_timetableID,
                                    'exam_category' => $assignment->exam_category,
                                    'examID' => $assignment->examID,
                                    'classID' => $assignment->classID,
                                    'scope' => $assignment->scope ?? null,
                                    'date' => $assignment->exam_date
                                ]);
                                $takeAttendanceUrl = route('supervise_exam.take_attendance', [
                                    'hall_id' => $assignment->exam_hallID,
                                    'subject_id' => $assignment->subjectID,
                                    'timetable_id' => $assignment->exam_timetableID,
                                    'examID' => $assignment->examID,
                                    'date' => $assignment->exam_date,
                                    'exam_category' => $assignment->exam_category,
                                    'classID' => $assignment->classID,
                                    'scope' => $assignment->scope ?? null
                                ]);
                                $hallLabel = $assignment->hall_name ?: ($assignment->exam_category === 'test' ? $assignment->class_name : __('teacher.supervise.na'));
                            @endphp
                            <tr>
                                <td>
                                    <div class="sx-time">
                                        {{ \Carbon\Carbon::parse($assignment->start_time)->format('h:i A') }}
                                        –
                                        {{ \Carbon\Carbon::parse($assignment->end_time)->format('h:i A') }}
                                    </div>
                                    <span class="sx-meta">
                                        {{ date('D, M d, Y', strtotime($assignment->exam_date)) }}
                                    </span>
                                    @if($assignment->exam_category === 'test' && !empty($assignment->week_range))
                                        <span class="sx-meta">{{ __('teacher.supervise.week_label', ['range' => $assignment->week_range]) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="sx-sub">{{ $assignment->subject_name }}</div>
                                    <span class="sx-meta">{{ $assignment->exam_name }}</span>
                                </td>
                                <td>
                                    <div>{{ $hallLabel }}</div>
                                    <span class="sx-meta">
                                        {{ $assignment->class_name }} · {{ __('teacher.supervise.students_count', ['count' => $assignment->students_count]) }}
                                    </span>
                                </td>
                                <td>
                                    @if($assignment->exam_category === 'test')
                                        <span class="tag tag-test">{{ __('teacher.supervise.test') }}</span>
                                    @else
                                        <span class="tag tag-exam">{{ __('teacher.supervise.exam') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isActive)
                                        <span class="tag tag-active">{{ __('teacher.supervise.active_today') }}</span>
                                    @elseif($isPast)
                                        <span class="tag tag-past">{{ __('teacher.supervise.past') }}</span>
                                    @else
                                        <span class="tag tag-upcoming">{{ __('teacher.supervise.upcoming') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="sx-actions">
                                        <a href="{{ $viewStudentsUrl }}" class="btn btn-outline-maroon btn-sm">
                                            <i class="bi bi-people"></i>
                                            {{ $assignment->exam_category === 'test' ? __('teacher.supervise.view_test_students') : __('teacher.supervise.view_students') }}
                                        </a>
                                        @if($isActive && $assignment->exam_date)
                                            <a href="{{ $takeAttendanceUrl }}" target="_blank" class="btn btn-maroon btn-sm text-white">
                                                <i class="bi bi-clipboard-check"></i>
                                                <span class="text-white">{{ $assignment->exam_category === 'test' ? __('teacher.supervise.test_attendance') : __('teacher.supervise.take_attendance') }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .table thead th,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .table thead th {
    background: #940000 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-active,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-active {
    background: #e8f6ec !important; color: #146c2e !important; -webkit-text-fill-color: #146c2e !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-upcoming,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-upcoming {
    background: #e7f5f8 !important; color: #0b6e7c !important; -webkit-text-fill-color: #0b6e7c !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-past,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-past {
    background: #eee !important; color: #555 !important; -webkit-text-fill-color: #555 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-exam,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-exam {
    background: #e7f0ff !important; color: #1a4d9b !important; -webkit-text-fill-color: #1a4d9b !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-test,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .tag.tag-test {
    background: #fff3cd !important; color: #7a5a00 !important; -webkit-text-fill-color: #7a5a00 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .form-label {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .sx-sub,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .sx-sub {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .sx-meta,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-supervise .sx-meta {
    color: #d7c4c8 !important;
}
</style>
@endsection
