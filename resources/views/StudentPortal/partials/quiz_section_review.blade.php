@php
    $first = $items->first();
    $cfg = is_array($first->config ?? null) ? $first->config : [];
@endphp

@if($type === 'matching')
    @php
        $left = $cfg['left'] ?? [];
        $right = collect($cfg['right'] ?? [])->keyBy('key');
        $correct = $cfg['answers'] ?? [];
        $picked = $savedAnswers[$first->id] ?? $savedAnswers[(string) $first->id] ?? [];
        if (!is_array($picked)) $picked = [];
    @endphp
    <p style="color:#6b5c5c;">{{ $first->question_text }}</p>
    <div class="table-responsive">
        <table class="table table-sm" style="background:#fff;">
            <thead><tr><th>A</th><th>Your match</th><th>Correct</th></tr></thead>
            <tbody>
            @foreach($left as $row)
                @php
                    $yk = (string) ($picked[$row['key']] ?? '');
                    $ck = (string) ($correct[$row['key']] ?? '');
                    $ok = $yk !== '' && $yk === $ck;
                @endphp
                <tr class="{{ $ok ? 'sp-pick-right' : 'sp-pick-wrong' }}">
                    <td>{{ $row['key'] }}. {{ $row['text'] }}</td>
                    <td>{{ $yk !== '' ? strtoupper($yk).'. '.($right[$yk]['text'] ?? $yk) : '—' }}</td>
                    <td>{{ $ck !== '' ? strtoupper($ck).'. '.($right[$ck]['text'] ?? $ck) : '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    @foreach($items as $question)
        @php
            $picked = $savedAnswers[$question->id] ?? $savedAnswers[(string) $question->id] ?? '';
        @endphp
        <fieldset class="sp-q sp-q-locked">
            <legend>
                @if($question->item_label){{ $question->item_label }}. @endif
                {{ $question->question_text }}
            </legend>

            @if($type === 'multiple_choice')
                @php $picked = strtoupper((string) $picked); $correct = strtoupper((string) $question->correct_option); @endphp
                @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field)
                    @if($question->$field)
                        <div class="form-check mb-1 {{ $letter === $picked ? ($picked === $correct ? 'sp-pick-right' : 'sp-pick-wrong') : '' }}">
                            <input class="form-check-input" type="radio" {{ $letter === $picked ? 'checked' : '' }} disabled>
                            <label class="form-check-label">{{ $letter }}. {{ $question->$field }}</label>
                        </div>
                    @endif
                @endforeach
            @elseif($type === 'true_false')
                @php
                    $picked = strtolower((string) $picked);
                    $correct = strtolower((string) $question->correct_option);
                @endphp
                @foreach(['true' => 'True', 'false' => 'False'] as $val => $lab)
                    <div class="form-check mb-1 {{ $val === $picked ? ($picked === $correct ? 'sp-pick-right' : 'sp-pick-wrong') : '' }}">
                        <input class="form-check-input" type="radio" {{ $val === $picked ? 'checked' : '' }} disabled>
                        <label class="form-check-label">{{ $lab }}</label>
                    </div>
                @endforeach
            @elseif($type === 'fill_blank')
                @php
                    $ok = \App\Support\StudentPortalQuizScoring::fillBlankMatches($question, (string) $picked);
                @endphp
                <div class="{{ $ok ? 'sp-pick-right' : 'sp-pick-wrong' }}" style="padding:0.4rem 0.55rem;">
                    Your answer: <strong>{{ $picked !== '' ? $picked : '—' }}</strong>
                    @if(!$ok)
                        <div style="font-size:0.85rem;margin-top:4px;">Expected: {{ $question->correct_option }}</div>
                    @endif
                </div>
            @else
                <div style="white-space:pre-wrap;background:#fff;border:1px solid #eadfdf;padding:0.65rem;">{{ $picked !== '' ? $picked : '—' }}</div>
                @php
                    $manualScores = is_array($attempt->manual_scores ?? null) ? $attempt->manual_scores : [];
                    $feedbackMap = is_array($attempt->teacher_feedback ?? null) ? $attempt->teacher_feedback : [];
                    $qid = (string) $question->id;
                    $awarded = $manualScores[$qid] ?? null;
                    $note = $feedbackMap[$qid] ?? null;
                @endphp
                @if($awarded !== null)
                    <small style="color:#155724;">Teacher mark: <strong>{{ $awarded }}</strong> / {{ $question->points }}
                        @if($note) — {{ $note }} @endif
                    </small>
                @else
                    <small style="color:#8a6a6a;">Awaiting teacher review / marking.</small>
                @endif
            @endif
        </fieldset>
    @endforeach
@endif
