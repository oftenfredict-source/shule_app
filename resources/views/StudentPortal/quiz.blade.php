@extends('StudentPortal.layout')
@section('title', $quiz->title)
@section('content')
<div class="sp-panel">
    <a href="{{ route('student_portal.quizzes') }}" class="sp-back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to quizzes</a>
    <h1>{{ $quiz->title }}</h1>
    @if($quiz->instructions)<p style="color:#6b5c5c;">{{ $quiz->instructions }}</p>@endif

    @if($attempt)
        @php
            $savedAnswers = $attempt->answers ?? [];
            $average = $attempt->total > 0 ? round(($attempt->score / $attempt->total) * 100, 1) : 0;
        @endphp
        <p class="sp-review-note">You already submitted this quiz. You can read it, but you cannot take it again.</p>
        @foreach($quiz->questions as $question)
            @php
                $picked = strtoupper((string) ($savedAnswers[$question->id] ?? $savedAnswers[(string) $question->id] ?? ''));
                $correct = strtoupper((string) $question->correct_option);
            @endphp
            <fieldset class="sp-q sp-q-locked">
                <legend>{{ $loop->iteration }}. {{ $question->question_text }}</legend>
                @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field)
                    @if($question->$field)
                        <div class="form-check mb-1 {{ $letter === $picked ? ($picked === $correct ? 'sp-pick-right' : 'sp-pick-wrong') : '' }}">
                            <input class="form-check-input" type="radio" {{ $letter === $picked ? 'checked' : '' }} disabled>
                            <label class="form-check-label">{{ $letter }}. {{ $question->$field }}</label>
                        </div>
                    @endif
                @endforeach
            </fieldset>
        @endforeach
        <div class="sp-score sp-score-bottom">
            <div>Marks: <strong>{{ $attempt->score }} / {{ $attempt->total }}</strong></div>
            <div>Average: <strong>{{ $average }}%</strong></div>
        </div>
    @elseif($quiz->windowStatus() === 'upcoming')
        <div class="sp-review-note" id="quizWait" data-opens="{{ $quiz->opens_at->timestamp }}">
            This quiz opens at {{ $quiz->opens_at->format('d M Y H:i') }}.
            <div>Opens in <strong id="quizCountdown">--:--</strong></div>
        </div>
    @elseif($quiz->windowStatus() === 'closed')
        <div class="sp-review-note">This quiz closed at {{ $quiz->closes_at->format('d M Y H:i') }}. You can no longer take it.</div>
    @else
        @if($quiz->closes_at)
            <div class="sp-timer" id="quizClock" data-closes="{{ $quiz->closes_at->timestamp }}">
                Time left: <strong id="quizCountdown">--:--</strong>
            </div>
        @endif
        <form method="POST" action="{{ route('student_portal.quiz.submit', $quiz->id) }}" id="quizTakeForm">
            @csrf
            @foreach($quiz->questions as $question)
                <fieldset class="sp-q">
                    <legend>{{ $loop->iteration }}. {{ $question->question_text }}</legend>
                    @foreach(['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field)
                        @if($question->$field)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="q{{ $question->id }}{{ $letter }}" value="{{ $letter }}" required>
                                <label class="form-check-label" for="q{{ $question->id }}{{ $letter }}">{{ $letter }}. {{ $question->$field }}</label>
                            </div>
                        @endif
                    @endforeach
                </fieldset>
            @endforeach
            <button class="sp-btn" type="submit" id="quizSubmitBtn">Submit answers</button>
        </form>
    @endif
</div>
@endsection

@section('scripts')
<script>
(function () {
    function pad(n) { return String(n).padStart(2, '0'); }
    function formatLeft(seconds) {
        seconds = Math.max(0, seconds);
        var h = Math.floor(seconds / 3600);
        var m = Math.floor((seconds % 3600) / 60);
        var s = seconds % 60;
        return (h > 0 ? h + ':' : '') + pad(m) + ':' + pad(s);
    }
    var label = document.getElementById('quizCountdown');
    if (!label) return;
    var wait = document.getElementById('quizWait');
    var clock = document.getElementById('quizClock');
    var target = wait ? parseInt(wait.dataset.opens, 10) : parseInt(clock.dataset.closes, 10);
    var sent = false;
    function tick() {
        var left = target - Math.floor(Date.now() / 1000);
        label.textContent = formatLeft(left);
        if (left > 0) return;
        if (wait) {
            window.location.reload();
            return;
        }
        if (sent) return;
        sent = true;
        label.textContent = '00:00';
        var form = document.getElementById('quizTakeForm');
        if (!form) return;
        form.querySelectorAll('[required]').forEach(function (input) { input.required = false; });
        var button = document.getElementById('quizSubmitBtn');
        if (button) {
            button.disabled = true;
            button.textContent = 'Time is up';
        }
        form.submit();
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endsection
