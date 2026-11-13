@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.calendar').' - ShuleXpert')
@section('page_title', __('teacher.pages.calendar'))
@section('page_breadcrumb', __('teacher.pages.calendar_bc'))

@section('page_actions')
    <span class="badge rounded-0 fw-bold px-3 py-2" style="background:#6c757d;color:#fff;">
        <i class="fa fa-eye me-1"></i> {{ __('teacher.cal.view_only') }}
    </span>
@endsection

@push('styles')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal span,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal td,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal th,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal li,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-cal h3 {
        color: #111 !important;
    }

    .sx-teacher-cal {
        --cal-maroon: #940000;
        --cal-ink: #111;
        --cal-muted: #555;
        --cal-line: #e5e5e5;
        --cal-soft: #faf7f7;
        max-width: 1180px;
    }

    .sx-teacher-cal .sx-panel {
        background: #fff;
        border: 1px solid var(--cal-line);
        border-top: 4px solid var(--cal-maroon);
        border-radius: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
        margin-bottom: 1rem;
    }

    /* Toolbar */
    .sx-teacher-cal .sx-cal-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem 1.25rem;
        padding: 1rem 1.15rem;
    }
    .sx-teacher-cal .sx-cal-toolbar .sx-title-block h2 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--cal-maroon) !important;
        line-height: 1.3;
    }
    .sx-teacher-cal .sx-cal-toolbar .sx-title-block p {
        margin: 0.2rem 0 0;
        font-size: 0.92rem;
        color: var(--cal-muted) !important;
        font-weight: 600;
        line-height: 1.4;
    }
    .sx-teacher-cal .sx-year-wrap {
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }
    .sx-teacher-cal .sx-year-wrap label {
        margin: 0;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--cal-ink) !important;
    }
    .sx-teacher-cal .form-select {
        border-radius: 0 !important;
        min-height: 42px;
        min-width: 120px;
        font-weight: 700;
        font-size: 0.95rem;
        border: 1px solid #cfcfcf;
        color: #111 !important;
    }
    .sx-teacher-cal .form-select:focus {
        border-color: var(--cal-maroon);
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12);
    }

    /* Stats strip — one row, readable */
    .sx-teacher-cal .sx-stats {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0;
        border-top: 1px solid var(--cal-line);
        background: var(--cal-soft);
    }
    .sx-teacher-cal .sx-stat {
        padding: 0.85rem 0.7rem;
        text-align: center;
        border-right: 1px solid var(--cal-line);
    }
    .sx-teacher-cal .sx-stat:last-child { border-right: 0; }
    .sx-teacher-cal .sx-stat .n {
        display: block;
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--cal-maroon) !important;
        line-height: 1.2;
    }
    .sx-teacher-cal .sx-stat .l {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--cal-muted) !important;
        line-height: 1.25;
    }
    @media (max-width: 991.98px) {
        .sx-teacher-cal .sx-stats {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .sx-teacher-cal .sx-stat:nth-child(4) { border-right: 0; }
        .sx-teacher-cal .sx-stat:nth-child(n+5) {
            border-top: 1px solid var(--cal-line);
        }
    }
    @media (max-width: 575.98px) {
        .sx-teacher-cal .sx-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .sx-teacher-cal .sx-stat { border-right: 1px solid var(--cal-line); }
        .sx-teacher-cal .sx-stat:nth-child(even) { border-right: 0; }
        .sx-teacher-cal .sx-stat:nth-child(n+3) { border-top: 1px solid var(--cal-line); }
    }

    /* Section heads */
    .sx-teacher-cal .sx-sec-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid var(--cal-line);
        background: #fff;
    }
    .sx-teacher-cal .sx-sec-head h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: var(--cal-maroon) !important;
        line-height: 1.35;
    }
    .sx-teacher-cal .sx-sec-head .hint {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--cal-muted) !important;
    }

    /* Holidays two columns */
    .sx-teacher-cal .sx-holidays-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    @media (max-width: 767.98px) {
        .sx-teacher-cal .sx-holidays-body { grid-template-columns: 1fr; }
    }
    .sx-teacher-cal .sx-hol-col {
        padding: 1rem 1.15rem 1.15rem;
        min-width: 0;
    }
    .sx-teacher-cal .sx-hol-col + .sx-hol-col {
        border-left: 1px solid var(--cal-line);
    }
    @media (max-width: 767.98px) {
        .sx-teacher-cal .sx-hol-col + .sx-hol-col {
            border-left: 0;
            border-top: 1px solid var(--cal-line);
        }
    }
    .sx-teacher-cal .sx-hol-col .col-title {
        margin: 0 0 0.75rem;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--cal-ink) !important;
        line-height: 1.35;
    }
    .sx-teacher-cal .sx-hol-col .col-title i {
        color: var(--cal-maroon);
        margin-right: 0.35rem;
    }
    .sx-teacher-cal .sx-scroll {
        max-height: 280px;
        overflow: auto;
        border: 1px solid var(--cal-line);
        background: #fff;
    }
    .sx-teacher-cal table.sx-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }
    .sx-teacher-cal table.sx-table th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f3f3f3 !important;
        color: #222 !important;
        font-size: 0.8rem;
        font-weight: 800;
        text-align: left;
        padding: 0.55rem 0.7rem;
        border-bottom: 1px solid var(--cal-line);
        white-space: nowrap;
    }
    .sx-teacher-cal table.sx-table td {
        font-size: 0.9rem;
        font-weight: 600;
        color: #111 !important;
        padding: 0.55rem 0.7rem;
        border-bottom: 1px solid #efefef;
        vertical-align: top;
        line-height: 1.4;
    }
    .sx-teacher-cal table.sx-table tr:last-child td { border-bottom: 0; }
    .sx-teacher-cal .sx-empty {
        padding: 1.25rem 0.75rem;
        text-align: center;
        color: var(--cal-muted) !important;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .sx-teacher-cal .tag {
        display: inline-block;
        padding: 0.15rem 0.45rem;
        font-size: 0.75rem;
        font-weight: 800;
        line-height: 1.3;
        border-radius: 0;
        white-space: nowrap;
    }
    .sx-teacher-cal .tag-green { background: #e8f6ec; color: #146c2e !important; }
    .sx-teacher-cal .tag-amber { background: #fff3cd; color: #7a5a00 !important; }
    .sx-teacher-cal .tag-blue { background: #e7f5f8; color: #0b6e7c !important; }
    .sx-teacher-cal .tag-maroon { background: #f8eaea; color: #940000 !important; }

    /* Months grid */
    .sx-teacher-cal .sx-months {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.85rem;
        padding: 1rem 1.15rem 1.15rem;
    }
    @media (max-width: 991.98px) {
        .sx-teacher-cal .sx-months { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) {
        .sx-teacher-cal .sx-months { grid-template-columns: 1fr; }
    }
    .sx-teacher-cal .month-card {
        border: 1px solid var(--cal-line);
        border-top: 3px solid var(--cal-maroon);
        background: #fff;
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }
    .sx-teacher-cal .month-card .m-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.7rem 0.85rem;
        background: var(--cal-soft);
        border-bottom: 1px solid var(--cal-line);
    }
    .sx-teacher-cal .month-card .m-name {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 800;
        color: var(--cal-maroon) !important;
        line-height: 1.3;
    }
    .sx-teacher-cal .month-card .m-days {
        font-size: 0.8rem;
        font-weight: 800;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        background: #2f2f2f;
        padding: 0.28rem 0.55rem;
        white-space: nowrap;
    }
    .sx-teacher-cal .month-card .m-days b {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        font-size: 0.95rem;
    }
    .sx-teacher-cal .month-card .m-body {
        padding: 0.75rem 0.85rem 0.9rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex: 1;
    }
    .sx-teacher-cal .m-block .m-label {
        margin: 0 0 0.4rem;
        font-size: 0.8rem;
        font-weight: 800;
        color: #333 !important;
        line-height: 1.3;
    }
    .sx-teacher-cal .m-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        max-height: 140px;
        overflow: auto;
    }
    .sx-teacher-cal .m-list li {
        padding: 0.45rem 0.55rem;
        background: #f7f7f7;
        border-left: 3px solid var(--cal-maroon);
        font-size: 0.88rem;
        font-weight: 600;
        color: #111 !important;
        line-height: 1.4;
    }
    .sx-teacher-cal .m-list li .meta {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--cal-muted) !important;
    }
    .sx-teacher-cal .m-list li .desc {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.8rem;
        font-weight: 500;
        color: #444 !important;
        line-height: 1.35;
    }
    .sx-teacher-cal .m-none {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 600;
        color: #777 !important;
        font-style: italic;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-teacher-cal,
    html[data-bs-theme="dark"] .sx-teacher-cal {
        --cal-ink: #f3e9eb;
        --cal-muted: #d7c4c8;
        --cal-line: #3a2a2e;
        --cal-soft: #2a1c20;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-panel,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-cal-toolbar .sx-title-block h2,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-cal-toolbar .sx-title-block h2 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-cal-toolbar .sx-title-block p,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-cal-toolbar .sx-title-block p {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-year-wrap label,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-year-wrap label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .form-select,
    html[data-bs-theme="dark"] .sx-teacher-cal .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-cal .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-stats,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-stats {
        background: #1c1417 !important;
        border-top-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-stat,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-stat {
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-stat .n,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-stat .n {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-stat .l,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-stat .l {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-sec-head,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-sec-head {
        background: #24181c !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-sec-head h3,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-sec-head h3 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-sec-head .hint,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-sec-head .hint {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-hol-col .col-title,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-hol-col .col-title {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-hol-col .col-title i,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-hol-col .col-title i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-scroll,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-scroll {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal table.sx-table th,
    html[data-bs-theme="dark"] .sx-teacher-cal table.sx-table th {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        border-bottom-color: #6e0000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal table.sx-table td,
    html[data-bs-theme="dark"] .sx-teacher-cal table.sx-table td {
        color: #f3e9eb !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .sx-empty,
    html[data-bs-theme="dark"] .sx-teacher-cal .sx-empty {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .month-card,
    html[data-bs-theme="dark"] .sx-teacher-cal .month-card {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .month-card .m-head,
    html[data-bs-theme="dark"] .sx-teacher-cal .month-card .m-head {
        background: #2a1c20 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .month-card .m-name,
    html[data-bs-theme="dark"] .sx-teacher-cal .month-card .m-name {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .m-block .m-label,
    html[data-bs-theme="dark"] .sx-teacher-cal .m-block .m-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .m-list li,
    html[data-bs-theme="dark"] .sx-teacher-cal .m-list li {
        background: #24181c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .m-list li .meta,
    html[data-bs-theme="dark"] .sx-teacher-cal .m-list li .meta {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .m-list li .desc,
    html[data-bs-theme="dark"] .sx-teacher-cal .m-list li .desc {
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .m-none,
    html[data-bs-theme="dark"] .sx-teacher-cal .m-none {
        color: #a8989c !important;
    }
    /* Keep status tags readable (dark text on light tint) */
    html[data-sx-theme="dark"] .sx-teacher-cal .tag-green,
    html[data-bs-theme="dark"] .sx-teacher-cal .tag-green {
        background: #e8f6ec !important; color: #146c2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .tag-amber,
    html[data-bs-theme="dark"] .sx-teacher-cal .tag-amber {
        background: #fff3cd !important; color: #7a5a00 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .tag-blue,
    html[data-bs-theme="dark"] .sx-teacher-cal .tag-blue {
        background: #e7f5f8 !important; color: #0b6e7c !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-cal .tag-maroon,
    html[data-bs-theme="dark"] .sx-teacher-cal .tag-maroon {
        background: #f8eaea !important; color: #940000 !important;
    }
</style>
@endpush

@section('content')
@php
    $months = [
        __('teacher.cal.january'),
        __('teacher.cal.february'),
        __('teacher.cal.march'),
        __('teacher.cal.april'),
        __('teacher.cal.may'),
        __('teacher.cal.june'),
        __('teacher.cal.july'),
        __('teacher.cal.august'),
        __('teacher.cal.september'),
        __('teacher.cal.october'),
        __('teacher.cal.november'),
        __('teacher.cal.december'),
    ];
@endphp
<div class="sx-teacher-cal">
    {{-- Top: year + summary --}}
    <div class="sx-panel">
        <div class="sx-cal-toolbar">
            <div class="sx-title-block">
                <h2>{{ __('teacher.cal.overview') }}</h2>
                <p>{{ __('teacher.cal.overview_sub', ['year' => $currentYear]) }}</p>
            </div>
            <div class="sx-year-wrap">
                <label for="yearSelect">{{ __('teacher.year') }}</label>
                <select class="form-select" id="yearSelect" aria-label="{{ __('teacher.cal.select_year') }}">
                    @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" @selected((int)$y === (int)$currentYear)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="sx-stats" role="list">
            <div class="sx-stat" role="listitem">
                <span class="n">{{ $stats->total_days ?? 0 }}</span>
                <span class="l">{{ __('teacher.cal.total_days') }}</span>
            </div>
            <div class="sx-stat" role="listitem">
                <span class="n">{{ $stats->working_days ?? 0 }}</span>
                <span class="l">{{ __('teacher.cal.working_days') }}</span>
            </div>
            <div class="sx-stat" role="listitem">
                <span class="n">{{ $stats->weekend_days ?? 0 }}</span>
                <span class="l">{{ __('teacher.cal.weekends') }}</span>
            </div>
            <div class="sx-stat" role="listitem">
                <span class="n">{{ $stats->holiday_days ?? 0 }}</span>
                <span class="l">{{ __('teacher.cal.holidays') }}</span>
            </div>
            <div class="sx-stat" role="listitem">
                <span class="n">{{ $stats->event_days ?? 0 }}</span>
                <span class="l">{{ __('teacher.cal.events') }}</span>
            </div>
            <div class="sx-stat" role="listitem">
                <span class="n">{{ $stats->non_working_event_days ?? 0 }}</span>
                <span class="l">{{ __('teacher.cal.non_working_events') }}</span>
            </div>
            <div class="sx-stat" role="listitem">
                <span class="n">{{ $stats->total_sessions ?? 0 }}</span>
                <span class="l">{{ __('teacher.cal.sessions_year') }}</span>
            </div>
        </div>
    </div>

    {{-- Holidays --}}
    <div class="sx-panel">
        <div class="sx-sec-head">
            <h3>{{ __('teacher.cal.holidays_year', ['year' => $currentYear]) }}</h3>
            <span class="hint">{{ __('teacher.cal.public_school_hint') }}</span>
        </div>
        <div class="sx-holidays-body">
            <div class="sx-hol-col">
                <p class="col-title"><i class="fa fa-flag"></i> {{ __('teacher.cal.public_holidays') }}</p>
                <div class="sx-scroll">
                    <table class="sx-table">
                        <thead>
                            <tr>
                                <th>{{ __('teacher.cal.th_date') }}</th>
                                <th>{{ __('teacher.cal.th_name') }}</th>
                                <th>{{ __('teacher.cal.th_type') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($autoHolidays as $autoHoliday)
                                <tr>
                                    <td>{{ $autoHoliday['date']->format('d M Y') }}</td>
                                    <td>{{ $autoHoliday['name'] }}</td>
                                    <td><span class="tag tag-green">{{ $autoHoliday['type'] }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3"><div class="sx-empty">{{ __('teacher.cal.no_public') }}</div></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="sx-hol-col">
                <p class="col-title"><i class="fa fa-school"></i> {{ __('teacher.cal.school_holidays') }}</p>
                <div class="sx-scroll">
                    <table class="sx-table">
                        <thead>
                            <tr>
                                <th>{{ __('teacher.cal.th_dates') }}</th>
                                <th>{{ __('teacher.cal.th_name') }}</th>
                                <th>{{ __('teacher.cal.th_type') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                <tr>
                                    <td>
                                        {{ \Carbon\Carbon::parse($holiday->start_date)->format('d M Y') }}
                                        @if($holiday->start_date != $holiday->end_date)
                                            – {{ \Carbon\Carbon::parse($holiday->end_date)->format('d M Y') }}
                                        @endif
                                    </td>
                                    <td>{{ $holiday->holiday_name }}</td>
                                    <td><span class="tag tag-amber">{{ $holiday->type }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3"><div class="sx-empty">{{ __('teacher.cal.no_school') }}</div></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Months --}}
    <div class="sx-panel">
        <div class="sx-sec-head">
            <h3>{{ __('teacher.cal.month_by_month') }}</h3>
            <span class="hint">{{ __('teacher.cal.month_hint') }}</span>
        </div>
        <div class="sx-months">
            @foreach($months as $index => $month)
                @php
                    $monthNum = $index + 1;
                    $monthHolidays = $holidays->filter(function ($h) use ($currentYear, $monthNum) {
                        $start = \Carbon\Carbon::parse($h->start_date);
                        $end = \Carbon\Carbon::parse($h->end_date);
                        return ($start->year == $currentYear && $start->month == $monthNum) ||
                               ($end->year == $currentYear && $end->month == $monthNum) ||
                               ($start->year <= $currentYear && $end->year >= $currentYear &&
                                $start->month <= $monthNum && $end->month >= $monthNum);
                    });
                    $monthAutoHolidays = collect($autoHolidays)->filter(function ($h) use ($currentYear, $monthNum) {
                        return $h['date']->year == $currentYear && $h['date']->month == $monthNum;
                    });
                    $monthEvents = $events->filter(function ($e) use ($currentYear, $monthNum) {
                        $d = \Carbon\Carbon::parse($e->event_date);
                        return $d->year == $currentYear && $d->month == $monthNum;
                    });
                    $monthWorkingDays = $monthlyStats[$monthNum]['working_days'] ?? 0;
                @endphp
                <article class="month-card">
                    <div class="m-head">
                        <h4 class="m-name">{{ $month }}</h4>
                        <span class="m-days"><b>{{ $monthWorkingDays }}</b> {{ __('teacher.cal.working') }}</span>
                    </div>
                    <div class="m-body">
                        <div class="m-block">
                            <p class="m-label">{{ __('teacher.cal.school_holidays') }}</p>
                            @if($monthHolidays->count() > 0)
                                <ul class="m-list">
                                    @foreach($monthHolidays as $holiday)
                                        <li>
                                            <span class="tag tag-amber">{{ $holiday->holiday_name }}</span>
                                            <span class="meta">
                                                {{ \Carbon\Carbon::parse($holiday->start_date)->format('d M') }}
                                                – {{ \Carbon\Carbon::parse($holiday->end_date)->format('d M') }}
                                            </span>
                                            @if($holiday->description)
                                                <span class="desc">{{ $holiday->description }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="m-none">{{ __('teacher.cal.none') }}</p>
                            @endif
                        </div>

                        <div class="m-block">
                            <p class="m-label">{{ __('teacher.cal.public_holidays') }}</p>
                            @if($monthAutoHolidays->count() > 0)
                                <ul class="m-list">
                                    @foreach($monthAutoHolidays as $autoHoliday)
                                        <li>
                                            <span class="tag tag-green">{{ $autoHoliday['name'] }}</span>
                                            <span class="meta">{{ $autoHoliday['date']->format('d M') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="m-none">{{ __('teacher.cal.none') }}</p>
                            @endif
                        </div>

                        <div class="m-block">
                            <p class="m-label">{{ __('teacher.cal.events') }}</p>
                            @if($monthEvents->count() > 0)
                                <ul class="m-list">
                                    @foreach($monthEvents as $event)
                                        <li>
                                            <span class="tag tag-blue">{{ $event->event_name }}</span>
                                            <span class="meta">
                                                {{ \Carbon\Carbon::parse($event->event_date)->format('d M') }}
                                                @if($event->start_time)
                                                    · {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                                                    @if($event->end_time)
                                                        – {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                                                    @endif
                                                @endif
                                            </span>
                                            @if($event->description)
                                                <span class="desc">{{ $event->description }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="m-none">{{ __('teacher.cal.none') }}</p>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</div>
{{-- After layout #sx-teacher-dark-fix so headers/tags stay readable --}}
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal table.sx-table thead th,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal table.sx-table thead th {
    background: #940000 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .tag.tag-green,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .tag.tag-green {
    background: #e8f6ec !important;
    color: #146c2e !important;
    -webkit-text-fill-color: #146c2e !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .tag.tag-amber,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .tag.tag-amber {
    background: #fff3cd !important;
    color: #7a5a00 !important;
    -webkit-text-fill-color: #7a5a00 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .tag.tag-blue,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .tag.tag-blue {
    background: #e7f5f8 !important;
    color: #0b6e7c !important;
    -webkit-text-fill-color: #0b6e7c !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .tag.tag-maroon,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .tag.tag-maroon {
    background: #f8eaea !important;
    color: #940000 !important;
    -webkit-text-fill-color: #940000 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .sx-hol-col .col-title,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-cal .sx-hol-col .col-title {
    color: #ffb4b4 !important;
}
</style>
@endsection

@push('scripts')
<script>
jQuery(function ($) {
    $('#yearSelect').on('change', function () {
        window.location.href = '{{ route("teacher.calendar") }}?year=' + $(this).val();
    });
});
</script>
@endpush
