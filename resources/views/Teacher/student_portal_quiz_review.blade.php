@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.quiz_review').' - ShuleXpert')
@section('page_title', __('teacher.pages.quiz_review'))
@section('page_breadcrumb', __('teacher.pages.quiz_review_bc'))

@section('page_actions')
    <a href="{{ route('student_portal.materials', ['tab' => 'results']) }}" class="btn btn-outline-primary btn-sm waves-effect">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to results
    </a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/learning-portal-hub.css') }}?v=6">
<style>
    .sx-teacher-lph.lph {
        --lph-display: inherit;
        --lph-ui: inherit;
        --lph-surface: #fff;
        --lph-elevated: #fcf7f7;
        --lph-chip: #fcf7f7;
        --lph-line: #e6e6e6;
        font-family: inherit;
        padding-bottom: 1.25rem;
    }

    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph span:not(.lph-badge):not(.lph-pct),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph legend,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-table td {
        color: #000 !important;
    }
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-table th {
        color: #940000 !important;
    }

    .sx-teacher-lph .lph-intro,
    .sx-teacher-lph .lph-panel {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
        border-top: 4px solid #940000 !important;
    }

    .sx-teacher-lph .lph-intro {
        padding: 1rem 1.15rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .sx-teacher-lph .lph-panel {
        padding: 1.15rem 1.25rem !important;
        margin-bottom: 1rem;
    }
    .sx-teacher-lph .lph-panel-head {
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(148, 0, 0, 0.1) !important;
    }
    .sx-teacher-lph .lph-panel-head h2 {
        font-size: 0.78rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: #940000 !important;
        margin: 0 !important;
    }
    .sx-teacher-lph .lph-panel-head h2 span {
        font-weight: 600 !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        color: #666 !important;
        font-size: 0.85rem !important;
    }

    .sx-teacher-lph .lph-score-n {
        font-size: 1.55rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .sx-teacher-lph .lph-score-meta {
        font-size: 0.85rem;
        color: #666 !important;
        margin-top: 0.35rem;
    }
    .sx-teacher-lph .lph-score-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.25rem;
    }

    .sx-teacher-lph .lph-q-block {
        border: 1px solid #e6e6e6;
        border-top: 3px solid #940000;
        border-radius: 0;
        padding: 0.9rem 1rem;
        margin-bottom: 0.75rem;
        background: #fff;
    }
    .sx-teacher-lph .lph-q-block legend {
        font-weight: 700;
        padding: 0 0.35rem;
        float: none;
        width: auto;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .sx-teacher-lph .lph-answer-box {
        white-space: pre-wrap;
        background: #fcf7f7;
        border: 1px solid #e6e6e6;
        border-radius: 0;
        padding: 0.75rem 0.9rem;
        margin-bottom: 0.75rem;
    }

    .sx-teacher-lph .lph-btn,
    .sx-teacher-lph .lph-btn-brand,
    .sx-teacher-lph .lph-btn-soft,
    .sx-teacher-lph .lph-btn-light {
        border-radius: 0 !important;
        font-weight: 800 !important;
    }
    .sx-teacher-lph .lph-btn-brand {
        background: #940000 !important;
        color: #fff !important;
        width: auto;
    }
    .sx-teacher-lph .lph-btn-brand:hover { background: #7b0000 !important; color: #fff !important; }
    .sx-teacher-lph .lph-btn-light {
        background: #fff !important;
        color: #940000 !important;
        border: 1px solid #940000 !important;
        width: auto;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    .sx-teacher-lph .lph-btn-light:hover {
        background: #fcf7f7 !important;
        color: #6e0000 !important;
        border-color: #6e0000 !important;
    }

    .sx-teacher-lph .form-control {
        border-radius: 0 !important;
        min-height: 42px;
        font-weight: 600;
        box-shadow: none !important;
    }
    .sx-teacher-lph .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12) !important;
    }
    .sx-teacher-lph .form-label,
    .sx-teacher-lph label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .sx-teacher-lph .lph-table th {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #940000 !important;
        background: #fcf7f7 !important;
        border-bottom: 1px solid rgba(148, 0, 0, 0.12) !important;
        white-space: nowrap;
    }

    .sx-teacher-lph .lph-ok { color: #155724 !important; }
    .sx-teacher-lph .lph-bad { color: #721c24 !important; }
    .sx-teacher-lph .lph-warn { color: #856404 !important; }

    html[data-sx-theme="dark"] .sx-teacher-lph .lph-intro,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-intro,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-panel,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-panel,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-q-block,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-q-block {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-intro,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-intro,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-panel,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-panel {
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-answer-box,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-answer-box {
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
</style>
@endpush

@section('content')
@php
    $studentName = $student
        ? trim(($student->first_name ?? '').' '.($student->middle_name ?? '').' '.($student->last_name ?? ''))
        : ('Student #'.$attempt->studentID);
    $pct = ($totals['total'] ?? 0) > 0 ? round((($totals['score'] ?? 0) / $totals['total']) * 100) : 0;
    $saved = is_array($attempt->answers) ? $attempt->answers : [];
@endphp

<div class="sx-teacher-lph lph">
    <div class="lph-intro">
        <p class="mb-0">
            <strong>{{ $quiz->title }}</strong> — {{ $studentName }}
            @if($student && $student->admission_number)
                ({{ $student->admission_number }})
            @endif
        </p>
    </div>

    @if(session('success'))<div class="lph-flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="lph-flash err">{{ session('error') }}</div>@endif

    <div class="lph-panel">
        <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between;">
            <div>
                <div class="lph-score-label">Current score</div>
                <div class="lph-score-n">
                    {{ $attempt->score }} / {{ $attempt->total }}
                    <span class="lph-pct {{ $pct >= 50 ? 'pass' : 'fail' }}" style="font-size:0.95rem;">{{ $pct }}%</span>
                </div>
                <div class="lph-score-meta">
                    Auto: {{ $totals['auto_score'] ?? 0 }}
                    · Manual: {{ $totals['manual_awarded'] ?? 0 }}
                    @if(($totals['pending'] ?? 0) > 0)
                        · <strong class="lph-warn">{{ $totals['pending'] }} awaiting marks</strong>
                    @endif
                    @if($attempt->graded_at)
                        · Graded {{ $attempt->graded_at->format('d M Y H:i') }}
                    @endif
                </div>
            </div>
            <div class="lph-score-meta">
                Submitted {{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y H:i') : '—' }}
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('student_portal.materials.attempts.mark', ['quizId' => $quiz->id, 'attemptId' => $attempt->id]) }}">
        @csrf

        @foreach($questions->groupBy(fn ($q) => ($q->section_number ?: 0).'|'.($q->question_type ?: 'multiple_choice')) as $groupKey => $items)
            @php
                $first = $items->first();
                $type = $first->question_type ?: 'multiple_choice';
                $cfg = is_array($first->config) ? $first->config : [];
            @endphp
            <div class="lph-panel">
                <div class="lph-panel-head">
                    <h2>
                        Q{{ $first->section_number ?: '' }} · {{ \App\Support\StudentPortalQuizScoring::typeLabel($type) }}
                        @if($first->section_title)
                            <span>— {{ $first->section_title }}</span>
                        @endif
                    </h2>
                </div>

                @if($type === 'matching')
                    @php
                        $left = $cfg['left'] ?? [];
                        $right = collect($cfg['right'] ?? [])->keyBy('key');
                        $correct = $cfg['answers'] ?? [];
                        $picked = $saved[$first->id] ?? $saved[(string) $first->id] ?? [];
                        if (!is_array($picked)) $picked = [];
                        $matchFrac = \App\Support\StudentPortalQuizScoring::scoreMatching($first, $picked);
                        $matchPts = round($matchFrac * (float) ($first->points ?: 1), 2);
                    @endphp
                    <p>{{ $first->question_text }}</p>
                    <p style="font-size:0.85rem;"><strong>Auto score:</strong> {{ $matchPts }} / {{ $first->points }}</p>
                    <div class="table-responsive">
                        <table class="lph-table">
                            <thead><tr><th>A (prompt)</th><th>Student match</th><th>Correct B</th><th></th></tr></thead>
                            <tbody>
                            @foreach($left as $row)
                                @php
                                    $yk = (string) ($picked[$row['key']] ?? '');
                                    $ck = (string) ($correct[$row['key']] ?? '');
                                    $ok = $yk !== '' && $yk === $ck;
                                @endphp
                                <tr>
                                    <td>{{ $row['key'] }}. {{ $row['text'] }}</td>
                                    <td>{{ $yk !== '' ? strtoupper($yk).'. '.($right[$yk]['text'] ?? $yk) : '—' }}</td>
                                    <td>{{ $ck !== '' ? strtoupper($ck).'. '.($right[$ck]['text'] ?? $ck) : '—' }}</td>
                                    <td>{!! $ok ? '<span class="lph-ok">✓</span>' : '<span class="lph-bad">✗</span>' !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    @foreach($items as $question)
                        @php
                            $picked = $saved[$question->id] ?? $saved[(string) $question->id] ?? '';
                            $qid = (string) $question->id;
                            $isManual = \App\Support\StudentPortalQuizScoring::isManualType($type);
                        @endphp
                        <fieldset class="lph-q-block">
                            <legend>
                                @if($question->item_label){{ $question->item_label }}. @endif
                                {{ $question->question_text }}
                                <small style="font-weight:500;color:#666;">({{ $question->points }} pts)</small>
                            </legend>

                            @if($type === 'multiple_choice')
                                @php $picked = strtoupper((string) $picked); $correct = strtoupper((string) $question->correct_option); @endphp
                                @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field)
                                    @if($question->$field)
                                        <div style="padding:2px 0;{{ $letter === $picked ? ($picked === $correct ? 'color:#155724;font-weight:700;' : 'color:#721c24;font-weight:700;') : '' }}">
                                            {{ $letter }}. {{ $question->$field }}
                                            @if($letter === $correct) <small>(correct)</small> @endif
                                        </div>
                                    @endif
                                @endforeach
                            @elseif($type === 'true_false')
                                @php $picked = strtolower((string) $picked); $correct = strtolower((string) $question->correct_option); @endphp
                                <div>Student: <strong>{{ $picked !== '' ? ucfirst($picked) : '—' }}</strong>
                                    · Correct: <strong>{{ ucfirst($correct) }}</strong>
                                    {!! ($picked !== '' && $picked === $correct) ? '<span class="lph-ok">✓</span>' : '<span class="lph-bad">✗</span>' !!}
                                </div>
                            @elseif($type === 'fill_blank')
                                @php $ok = \App\Support\StudentPortalQuizScoring::fillBlankMatches($question, (string) $picked); @endphp
                                <div>Student: <strong>{{ $picked !== '' ? $picked : '—' }}</strong></div>
                                <div style="font-size:0.85rem;color:#666;">Expected: {{ $question->correct_option }} {!! $ok ? '<span class="lph-ok">✓</span>' : '<span class="lph-bad">✗</span>' !!}</div>
                            @else
                                <div class="lph-answer-box">{{ is_string($picked) && $picked !== '' ? $picked : '—' }}</div>
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Marks (max {{ $question->points }})</label>
                                        <input class="form-control" type="number" min="0" max="{{ $question->points }}" step="0.5"
                                               name="marks[{{ $qid }}]"
                                               value="{{ $manualScores[$qid] ?? '' }}"
                                               placeholder="0 – {{ $question->points }}">
                                    </div>
                                    <div class="col-md-9 mb-2">
                                        <label class="form-label">Feedback (optional)</label>
                                        <input class="form-control" type="text" name="feedback[{{ $qid }}]"
                                               value="{{ $feedback[$qid] ?? '' }}"
                                               maxlength="2000" placeholder="Short comment to student">
                                    </div>
                                </div>
                            @endif
                        </fieldset>
                    @endforeach
                @endif
            </div>
        @endforeach

        @php
            $hasManual = $questions->contains(fn ($q) => \App\Support\StudentPortalQuizScoring::isManualType($q->question_type ?: ''));
        @endphp
        @if($hasManual)
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:1.5rem;">
                <button type="submit" class="lph-btn lph-btn-brand">
                    <i class="fa fa-check"></i> Save marks
                </button>
                <a href="{{ route('student_portal.materials', ['tab' => 'results']) }}" class="lph-btn lph-btn-light">Cancel</a>
            </div>
        @else
            <p style="color:#666;margin-bottom:1.5rem;">Quiz hii ina maswali ya auto-mark tu — hakuna short answer/essay ya kuweka alama.</p>
        @endif
    </form>
</div>
@endsection
