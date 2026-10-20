@php
    $first = $items->first();
    $cfg = is_array($first->config ?? null) ? $first->config : [];
@endphp

@if(!empty($cfg['section_instructions']))
    <p style="color:#6b5c5c;font-size:0.9rem;">{{ $cfg['section_instructions'] }}</p>
@endif

@if($type === 'matching')
    @php
        $left = $cfg['left'] ?? [];
        $right = $cfg['right'] ?? [];
        $qid = $first->id;
    @endphp
    <p style="color:#6b5c5c;font-size:0.9rem;">{{ $first->question_text }}</p>
    <p style="font-size:0.85rem;color:#8a6a6a;">Drag items from column B into the matching slots in column A.</p>
    <div class="sp-match" data-qid="{{ $qid }}">
        <div class="sp-match-col">
            <h4>A</h4>
            @foreach($left as $row)
                <div class="sp-match-slot" data-left="{{ $row['key'] }}">
                    <span class="label">{{ $row['key'] }}.</span>
                    <span>{{ $row['text'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="sp-match-col">
            <h4>B — drag from here</h4>
            <div class="sp-match-bank">
                @foreach($right as $row)
                    <span class="sp-match-chip" data-right="{{ $row['key'] }}">
                        <strong>{{ strtoupper($row['key']) }}.</strong> {{ $row['text'] }}
                    </span>
                @endforeach
            </div>
        </div>
        <div class="sp-match-hidden" style="display:none;">
            @foreach($left as $row)
                <input type="hidden" name="answers[{{ $qid }}][{{ $row['key'] }}]" data-left="{{ $row['key'] }}" value="">
            @endforeach
        </div>
    </div>
@else
    @foreach($items as $question)
        <fieldset class="sp-q">
            <legend>
                @if($question->item_label){{ $question->item_label }}. @endif
                {{ $question->question_text }}
                @if((float)$question->points !== 1.0)
                    <small style="color:#8a6a6a;">({{ rtrim(rtrim(number_format((float)$question->points, 2), '0'), '.') }} pts)</small>
                @endif
            </legend>

            @if($type === 'multiple_choice')
                @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field)
                    @if($question->$field)
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="q{{ $question->id }}{{ $letter }}" value="{{ $letter }}">
                            <label class="form-check-label" for="q{{ $question->id }}{{ $letter }}">{{ $letter }}. {{ $question->$field }}</label>
                        </div>
                    @endif
                @endforeach
            @elseif($type === 'true_false')
                <div class="sp-tf">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="q{{ $question->id }}t" value="true">
                        <label class="form-check-label" for="q{{ $question->id }}t">True</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="q{{ $question->id }}f" value="false">
                        <label class="form-check-label" for="q{{ $question->id }}f">False</label>
                    </div>
                </div>
            @elseif($type === 'fill_blank')
                <input class="form-control" type="text" name="answers[{{ $question->id }}]" placeholder="Type your answer" maxlength="500">
            @else
                <textarea class="form-control" name="answers[{{ $question->id }}]" rows="{{ $type === 'essay' ? 6 : 3 }}" placeholder="{{ $type === 'essay' ? 'Write your essay answer…' : 'Write a short answer…' }}" maxlength="10000"></textarea>
                <small style="color:#8a6a6a;">This answer will be reviewed by the teacher (not auto-marked).</small>
            @endif
        </fieldset>
    @endforeach
@endif
