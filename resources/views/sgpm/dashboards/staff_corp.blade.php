@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.scorecard').' - ShuleXpert')
@section('page_title', __('teacher.pages.scorecard'))
@section('page_breadcrumb', __('teacher.pages.scorecard_bc'))

@push('styles')
<style>
    .sx-scorecard {
        --maroon: #940000;
        --line: #e8e0e0;
        --soft: #faf7f7;
    }
    .sx-scorecard .sx-panel {
        background: #fff;
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow: 0 1px 2px rgba(80,0,0,.04), 0 10px 28px rgba(80,0,0,.06);
        overflow: hidden;
        height: 100%;
    }
    .sx-scorecard .sx-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        background: linear-gradient(180deg, #fff, var(--soft));
        border-bottom: 1px solid var(--line);
    }
    .sx-scorecard .sx-panel-head h4 { margin: 0; color: var(--maroon); font-size: 1.05rem; font-weight: 800; }
    .sx-scorecard .sx-panel-head p { margin: .2rem 0 0; color: #666; font-size: .8rem; }
    .sx-scorecard .sx-panel-body { padding: 1.1rem; }
    .sx-scorecard .btn, .sx-scorecard .badge, .sx-scorecard .progress { border-radius: 0 !important; }
    .sx-btn-maroon {
        background: linear-gradient(180deg, #a80000, #940000 55%, #7f0000) !important;
        border: 1px solid #7a0000 !important;
        color: #fff !important;
        font-weight: 700 !important;
        border-radius: 0 !important;
    }
    .sx-btn-maroon:hover { color: #fff !important; filter: brightness(.95); }
    .sx-btn-outline {
        background: #fff;
        border: 1px solid #940000;
        color: #940000;
        font-weight: 700;
        border-radius: 0;
    }
    .sx-btn-outline:hover { background: #fff5f5; color: #7a0000; }

    .sx-score-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.25rem;
        background: #fff;
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow: 0 1px 2px rgba(80,0,0,.04), 0 10px 28px rgba(80,0,0,.06);
        margin-bottom: 1rem;
    }
    .sx-score-hero h3 {
        margin: 0;
        color: var(--maroon);
        font-size: 1.15rem;
        font-weight: 800;
    }
    .sx-score-hero p { margin: .25rem 0 0; color: #666; font-size: .85rem; }
    .sx-score-hero .sx-avg {
        text-align: right;
        white-space: nowrap;
    }
    .sx-score-hero .sx-avg strong {
        display: block;
        font-size: 1.75rem;
        line-height: 1.1;
        color: var(--maroon);
        font-weight: 800;
    }
    .sx-score-hero .sx-avg span { color: #777; font-size: .75rem; font-weight: 600; }

    .sx-score-ring-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1.5rem 1rem;
        min-height: 280px;
    }
    .sx-score-ring {
        width: 128px;
        height: 128px;
        border: 8px solid #f0e4e4;
        border-top-color: #940000;
        border-right-color: #c45a5a;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    .sx-score-ring strong {
        font-size: 1.65rem;
        color: #940000;
        font-weight: 800;
    }
    .sx-score-ring-wrap h5 {
        margin: 0 0 .4rem;
        color: #333;
        font-weight: 800;
        font-size: .95rem;
    }
    .sx-score-ring-wrap p {
        margin: 0 0 1rem;
        color: #777;
        font-size: .8rem;
        max-width: 240px;
    }

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
        padding: .8rem;
        border-bottom: 1px solid #f0eaea;
        vertical-align: middle;
        font-size: .88rem;
        color: #333;
    }
    table.sx-ep-table tbody tr:hover { background: #fffafa; }
    .sx-task-title { display: block; font-weight: 700; color: #2a2a2a; }
    .sx-task-meta { display: block; color: #888; font-size: .78rem; margin-top: .15rem; }
    .sx-tag {
        display: inline-block;
        padding: .22rem .55rem;
        font-size: .72rem;
        font-weight: 700;
        border: 1px solid transparent;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .sx-tag-approved, .sx-tag-completed { background: #eef8f0; color: #1b6b2e; border-color: #c8e6cf; }
    .sx-tag-rejected { background: #fdeeee; color: #940000; border-color: #f0c8c8; }
    .sx-tag-pending, .sx-tag-in-progress, .sx-tag-submitted { background: #f3f6fb; color: #3a5a8c; border-color: #d4dff0; }
    .sx-score-good { color: #1b6b2e; font-weight: 800; }
    .sx-score-warn { color: #9a6b00; font-weight: 800; }
    .sx-score-pending { color: #999; font-weight: 600; }
</style>
@endpush

@section('content')
@php
    $standing = $avgScore >= 80 ? __('teacher.sgpm.standing_excellent') : ($avgScore >= 60 ? __('teacher.sgpm.standing_good') : ($avgScore > 0 ? __('teacher.sgpm.standing_attention') : __('teacher.sgpm.standing_none')));
@endphp
<div class="sx-scorecard">
    <div class="sx-score-hero">
        <div>
            <h3><i class="fa fa-briefcase me-1"></i> {{ __('teacher.sgpm.scorecard_head') }}</h3>
            <p>{{ __('teacher.sgpm.scorecard_sub') }}</p>
        </div>
        <div class="sx-avg">
            <strong>{{ number_format($avgScore, 1) }}%</strong>
            <span>{{ __('teacher.sgpm.personal_growth') }}</span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="sx-panel">
                <div class="sx-score-ring-wrap">
                    <div class="sx-score-ring">
                        <strong>{{ number_format($avgScore, 0) }}%</strong>
                    </div>
                    <h5>{{ $standing }}</h5>
                    <p>{{ __('teacher.sgpm.avg_hint') }}</p>
                    <a href="{{ route('sgpm.tasks.index') }}" class="btn sx-btn-maroon btn-sm px-4">
                        <i class="fa fa-tasks me-1"></i> {{ __('teacher.sgpm.view_my_tasks') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="sx-panel">
                <div class="sx-panel-head">
                    <div>
                        <h4>{{ __('teacher.sgpm.recent_reviews') }}</h4>
                        <p>{{ __('teacher.sgpm.recent_reviews_sub') }}</p>
                    </div>
                </div>
                <div class="sx-panel-body p-0">
                    <div class="sx-ep-table-wrap" style="border:0;">
                        <table class="sx-ep-table">
                            <thead>
                                <tr>
                                    <th>{{ __('teacher.sgpm.kpi_task') }}</th>
                                    <th>{{ __('teacher.sgpm.due_date') }}</th>
                                    <th>{{ __('teacher.sgpm.score') }}</th>
                                    <th>{{ __('teacher.sgpm.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks->take(10) as $t)
                                    @php
                                        $statusKey = strtolower(str_replace(' ', '-', (string) $t->status));
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="sx-task-title">{{ $t->kpi }}</span>
                                            <span class="sx-task-meta">{{ $t->actionPlan->title ?? __('teacher.sgpm.general_task') }}</span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($t->due_date)->format('M d, Y') }}</td>
                                        <td>
                                            @if($t->total_score)
                                                <span class="{{ $t->total_score >= 80 ? 'sx-score-good' : 'sx-score-warn' }}">{{ number_format($t->total_score, 1) }}%</span>
                                            @else
                                                <span class="sx-score-pending">{{ __('teacher.sgpm.pending') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="sx-tag sx-tag-{{ $statusKey }}">{{ $t->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">{{ __('teacher.sgpm.no_tasks') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
