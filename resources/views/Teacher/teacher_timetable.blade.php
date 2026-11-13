@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.timetable').' - ShuleXpert')
@section('page_title', __('teacher.pages.timetable'))
@section('page_breadcrumb', __('teacher.pages.timetable_bc'))

@section('page_actions')
    <a href="{{ route('teacher.mySessions') }}" class="btn btn-sm btn-outline-primary rounded-0 fw-bold" style="border-color:#940000;color:#940000;">
        <i class="bi bi-calendar-week me-1"></i> {{ __('teacher.tt.open_sessions') }}
    </a>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    .sx-tt {
        --maroon: #940000;
        --line: #e8e0e0;
        --soft: #faf7f7;
    }
    .sx-tt .sx-panel {
        background: linear-gradient(180deg, #fff 0%, #fff 72%, #fbf8f8 100%);
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow: 0 1px 2px rgba(80,0,0,.04), 0 10px 28px rgba(80,0,0,.06);
        overflow: hidden;
    }
    .sx-tt .sx-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        background:
            linear-gradient(135deg, rgba(148,0,0,.04), transparent 42%),
            linear-gradient(180deg, #fff, var(--soft));
        border-bottom: 1px solid var(--line);
    }
    .sx-tt .sx-panel-head h4 {
        margin: 0;
        color: var(--maroon);
        font-size: 1.05rem;
        font-weight: 800;
    }
    .sx-tt .sx-panel-head p { margin: .2rem 0 0; color: #666; font-size: .8rem; }
    .sx-tt .sx-panel-body { padding: 1.1rem; }
    .sx-tt .btn, .sx-tt .badge, .sx-tt .alert { border-radius: 0 !important; }

    .sx-tt-grid-wrap { overflow-x: auto; border: 1px solid var(--line); }
    table.sx-tt-grid {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
        margin: 0;
        background: #fff;
    }
    table.sx-tt-grid th {
        background: linear-gradient(180deg, #f7f2f2, #f1eaea);
        color: #940000;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .75rem .6rem;
        border-bottom: 1px solid var(--line);
        border-right: 1px solid #f0eaea;
        text-align: center;
        white-space: nowrap;
    }
    table.sx-tt-grid th:first-child,
    table.sx-tt-grid td:first-child {
        width: 110px;
        text-align: left;
        background: linear-gradient(180deg, #faf7f7, #f5f0f0);
        color: #555;
        font-weight: 800;
        font-size: .75rem;
        position: sticky;
        left: 0;
        z-index: 1;
    }
    table.sx-tt-grid td {
        border-bottom: 1px solid #f0eaea;
        border-right: 1px solid #f0eaea;
        padding: .45rem;
        vertical-align: top;
        min-width: 120px;
        height: 76px;
    }
    .sx-tt-cell {
        border: 1px solid #eadfdf;
        border-left: 3px solid #940000;
        background: linear-gradient(180deg, #fff, #fff8f8);
        padding: .45rem .5rem;
        min-height: 62px;
        height: 100%;
    }
    .sx-tt-cell.empty {
        border: 1px dashed #e6e0e0;
        border-left: 1px dashed #e6e0e0;
        background: #fcfcfc;
        color: #bbb;
        font-size: .72rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .sx-tt-subj {
        display: block;
        font-size: .8rem;
        font-weight: 800;
        color: #111;
        line-height: 1.25;
        margin-bottom: .15rem;
    }
    .sx-tt-class {
        display: block;
        font-size: .7rem;
        color: #666;
        font-weight: 600;
    }
    .sx-tt-time {
        display: block;
        font-size: .65rem;
        color: #940000;
        font-weight: 800;
        margin-top: .2rem;
    }

    .sx-tt-day-list { display: none; }
    @media (max-width: 767.98px) {
        .sx-tt-grid-wrap { display: none; }
        .sx-tt-day-list { display: block; }
        .sx-tt-day {
            border: 1px solid var(--line);
            margin-bottom: .75rem;
            background: #fff;
        }
        .sx-tt-day-h {
            padding: .65rem .85rem;
            background: linear-gradient(180deg, #fff, var(--soft));
            border-bottom: 1px solid var(--line);
            color: #940000;
            font-weight: 800;
            font-size: .85rem;
        }
        .sx-tt-day-item {
            padding: .7rem .85rem;
            border-bottom: 1px solid #f0eaea;
        }
        .sx-tt-day-item:last-child { border-bottom: 0; }
    }
</style>
@endpush

@section('content')
@php
    $hasSessions = collect($sessionsByDay ?? [])->flatten(1)->count() > 0;
@endphp
<div class="sx-tt">
    <div class="sx-panel">
        <div class="sx-panel-head">
            <div>
                <h4><i class="bi bi-calendar3 me-1"></i> {{ __('teacher.tt.title') }}</h4>
                <p>{{ __('teacher.tt.subtitle') }}</p>
            </div>
        </div>
        <div class="sx-panel-body">
            @if(!empty($message))
                <div class="alert alert-warning mb-0">{{ $message }}</div>
            @elseif(!$hasSessions)
                <div class="alert alert-info mb-0">{{ __('teacher.tt.no_sessions') }}</div>
            @else
                <div class="sx-tt-grid-wrap">
                    <table class="sx-tt-grid">
                        <thead>
                            <tr>
                                <th>{{ __('teacher.time') }}</th>
                                @foreach($days as $day)
                                    <th>{{ $day }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($timeSlots as $slot)
                                @php
                                    $slotKey = $slot['start'] . '-' . $slot['end'];
                                @endphp
                                <tr>
                                    <td>
                                        {{ \Carbon\Carbon::createFromFormat('H:i', $slot['start'])->format('g:i A') }}
                                        <br>
                                        <span style="font-weight:600;color:#888;">{{ \Carbon\Carbon::createFromFormat('H:i', $slot['end'])->format('g:i A') }}</span>
                                    </td>
                                    @foreach($days as $day)
                                        @php
                                            $cellSessions = collect($sessionsByDay[$day] ?? [])->filter(function ($s) use ($slot) {
                                                return substr((string) $s->start_time, 0, 5) === $slot['start']
                                                    && substr((string) $s->end_time, 0, 5) === $slot['end'];
                                            });
                                        @endphp
                                        <td>
                                            @forelse($cellSessions as $s)
                                                @php
                                                    $subjectName = $s->subject->subject_name
                                                        ?? ($s->classSubject->subject->subject_name ?? __('teacher.subject'));
                                                    $className = trim(($s->subclass->class->class_name ?? '') . ' ' . ($s->subclass->subclass_name ?? ''));
                                                @endphp
                                                <div class="sx-tt-cell {{ !$loop->first ? 'mt-1' : '' }}">
                                                    <span class="sx-tt-subj">{{ $subjectName }}</span>
                                                    <span class="sx-tt-class">{{ $className !== '' ? $className : __('teacher.class') }}</span>
                                                </div>
                                            @empty
                                                <div class="sx-tt-cell empty">—</div>
                                            @endforelse
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($days) + 1 }}" class="text-center text-muted py-4">{{ __('teacher.tt.no_slots') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="sx-tt-day-list">
                    @foreach($days as $day)
                        <div class="sx-tt-day">
                            <div class="sx-tt-day-h">{{ $day }}</div>
                            @forelse($sessionsByDay[$day] ?? [] as $s)
                                @php
                                    $subjectName = $s->subject->subject_name
                                        ?? ($s->classSubject->subject->subject_name ?? __('teacher.subject'));
                                    $className = trim(($s->subclass->class->class_name ?? '') . ' ' . ($s->subclass->subclass_name ?? ''));
                                @endphp
                                <div class="sx-tt-day-item">
                                    <span class="sx-tt-subj">{{ $subjectName }}</span>
                                    <span class="sx-tt-class">{{ $className !== '' ? $className : __('teacher.class') }}</span>
                                    <span class="sx-tt-time">
                                        {{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }}
                                        –
                                        {{ \Carbon\Carbon::parse($s->end_time)->format('g:i A') }}
                                    </span>
                                </div>
                            @empty
                                <div class="sx-tt-day-item text-muted">{{ __('teacher.tt.no_day_sessions') }}</div>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
