@include('includes.Admin_nav')

<link rel="stylesheet" href="{{ asset('css/learning-portal-hub.css') }}?v=3">

@php
    $studentName = $student
        ? trim(($student->first_name ?? '').' '.($student->middle_name ?? '').' '.($student->last_name ?? ''))
        : ('Student #'.$attempt->studentID);
    $pct = ($totals['total'] ?? 0) > 0 ? round((($totals['score'] ?? 0) / $totals['total']) * 100) : 0;
    $saved = is_array($attempt->answers) ? $attempt->answers : [];
@endphp

<div class="content mt-3 lph">
    <section class="lph-hero">
        <div class="lph-hero-inner">
            <div>
                <span class="lph-kicker">Marking</span>
                <h1>Review attempt</h1>
                <p>{{ $quiz->title }} — {{ $studentName }}
                    @if($student && $student->admission_number)
                        ({{ $student->admission_number }})
                    @endif
                </p>
            </div>
            <div class="lph-hero-actions">
                <a href="{{ route('student_portal.materials', ['tab' => 'results']) }}" class="lph-btn lph-btn-soft">
                    <i class="fa fa-arrow-left"></i> Back to results
                </a>
            </div>
        </div>
    </section>

    @if(session('success'))<div class="lph-flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="lph-flash err">{{ session('error') }}</div>@endif

    <div class="lph-panel" style="margin-bottom:16px;">
        <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:0.8rem;color:#6b5c5c;text-transform:uppercase;letter-spacing:.04em;">Current score</div>
                <div style="font-size:1.6rem;font-weight:700;">
                    {{ $attempt->score }} / {{ $attempt->total }}
                    <span class="lph-pct {{ $pct >= 50 ? 'pass' : 'fail' }}" style="font-size:0.95rem;">{{ $pct }}%</span>
                </div>
                <div style="font-size:0.85rem;color:#6b5c5c;margin-top:4px;">
                    Auto: {{ $totals['auto_score'] ?? 0 }}
                    · Manual: {{ $totals['manual_awarded'] ?? 0 }}
                    @if(($totals['pending'] ?? 0) > 0)
                        · <strong style="color:#856404;">{{ $totals['pending'] }} awaiting marks</strong>
                    @endif
                    @if($attempt->graded_at)
                        · Graded {{ $attempt->graded_at->format('d M Y H:i') }}
                    @endif
                </div>
            </div>
            <div style="font-size:0.85rem;color:#6b5c5c;">
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
            <div class="lph-panel" style="margin-bottom:14px;">
                <div class="lph-panel-head" style="margin-bottom:10px;">
                    <h2 style="font-size:1.05rem;margin:0;">
                        Q{{ $first->section_number ?: '' }} · {{ \App\Support\StudentPortalQuizScoring::typeLabel($type) }}
                        @if($first->section_title)
                            <span style="font-weight:500;color:#6b5c5c;">— {{ $first->section_title }}</span>
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
                    <p style="color:#6b5c5c;">{{ $first->question_text }}</p>
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
                                    <td>{!! $ok ? '<span style="color:#155724;">✓</span>' : '<span style="color:#721c24;">✗</span>' !!}</td>
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
                        <fieldset style="border:1px solid #eadfdf;border-radius:10px;padding:12px;margin-bottom:10px;background:#fff;">
                            <legend style="font-weight:700;padding:0 6px;">
                                @if($question->item_label){{ $question->item_label }}. @endif
                                {{ $question->question_text }}
                                <small style="font-weight:500;color:#6b5c5c;">({{ $question->points }} pts)</small>
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
                                    {!! ($picked !== '' && $picked === $correct) ? '✓' : '✗' !!}
                                </div>
                            @elseif($type === 'fill_blank')
                                @php $ok = \App\Support\StudentPortalQuizScoring::fillBlankMatches($question, (string) $picked); @endphp
                                <div>Student: <strong>{{ $picked !== '' ? $picked : '—' }}</strong></div>
                                <div style="font-size:0.85rem;color:#6b5c5c;">Expected: {{ $question->correct_option }} {!! $ok ? '✓' : '✗' !!}</div>
                            @else
                                <div style="white-space:pre-wrap;background:#faf7f4;border:1px solid #eadfdf;border-radius:8px;padding:10px;margin-bottom:10px;">{{ is_string($picked) && $picked !== '' ? $picked : '—' }}</div>
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label style="font-size:0.8rem;font-weight:700;">Marks (max {{ $question->points }})</label>
                                        <input class="form-control" type="number" min="0" max="{{ $question->points }}" step="0.5"
                                               name="marks[{{ $qid }}]"
                                               value="{{ $manualScores[$qid] ?? '' }}"
                                               placeholder="0 – {{ $question->points }}">
                                    </div>
                                    <div class="col-md-9 mb-2">
                                        <label style="font-size:0.8rem;font-weight:700;">Feedback (optional)</label>
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
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:40px;">
                <button type="submit" class="lph-btn lph-btn-brand" style="width:auto;">
                    <i class="fa fa-check"></i> Save marks
                </button>
                <a href="{{ route('student_portal.materials', ['tab' => 'results']) }}" class="lph-btn lph-btn-soft">Cancel</a>
            </div>
        @else
            <p style="color:#6b5c5c;margin-bottom:40px;">Quiz hii ina maswali ya auto-mark tu — hakuna short answer/essay ya kuweka alama.</p>
        @endif
    </form>
</div>
