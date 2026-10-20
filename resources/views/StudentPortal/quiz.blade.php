@extends('StudentPortal.layout')
@section('title', $quiz->title)
@section('content')
@php
    use App\Support\StudentPortalQuizScoring;
    $questions = $quiz->questions->sortBy(['section_number', 'sort_order'])->values();
    $sections = $questions->groupBy('section_number');
@endphp
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
        @foreach($sections as $sectionNumber => $items)
            @php
                $first = $items->first();
                $type = $first->question_type ?: 'multiple_choice';
            @endphp
            <div class="sp-section">
                <h2 class="sp-section-title">
                    Question {{ $sectionNumber }}
                    @if($first->section_title) — {{ $first->section_title }} @endif
                    <small>({{ StudentPortalQuizScoring::typeLabel($type) }})</small>
                </h2>
                @include('StudentPortal.partials.quiz_section_review', ['items' => $items, 'type' => $type, 'savedAnswers' => $savedAnswers, 'attempt' => $attempt])
            </div>
        @endforeach
        <div class="sp-score sp-score-bottom">
            <div>Auto-marked: <strong>{{ $attempt->score }} / {{ $attempt->total }}</strong></div>
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
            @foreach($sections as $sectionNumber => $items)
                @php
                    $first = $items->first();
                    $type = $first->question_type ?: 'multiple_choice';
                @endphp
                <div class="sp-section">
                    <h2 class="sp-section-title">
                        Question {{ $sectionNumber }}
                        @if($first->section_title) — {{ $first->section_title }} @endif
                        <small>({{ StudentPortalQuizScoring::typeLabel($type) }})</small>
                    </h2>
                    @include('StudentPortal.partials.quiz_section_take', ['items' => $items, 'type' => $type])
                </div>
            @endforeach
            <button class="sp-btn" type="submit" id="quizSubmitBtn">Submit answers</button>
        </form>
    @endif
</div>
@endsection

@section('scripts')
<style>
    .sp-section { margin: 1.25rem 0 1.75rem; padding-top: 0.5rem; border-top: 1px solid #eadfdf; }
    .sp-section-title { font-size: 1.05rem; margin: 0 0 0.85rem; }
    .sp-section-title small { font-weight: 600; color: #8a6a6a; }
    .sp-match { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .sp-match-col h4 { font-size: 0.9rem; margin: 0 0 0.5rem; }
    .sp-match-slot {
        min-height: 44px; border: 1px dashed #c9b4b4; background: #fff;
        padding: 0.45rem 0.55rem; margin-bottom: 0.45rem; display: flex; gap: 0.5rem; align-items: center;
    }
    .sp-match-slot .label { font-weight: 700; min-width: 1.2rem; }
    .sp-match-bank { min-height: 80px; border: 1px solid #eadfdf; background: #faf7f7; padding: 0.5rem; }
    .sp-match-chip {
        display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #c9b4b4;
        padding: 0.35rem 0.55rem; margin: 0.2rem; cursor: grab; user-select: none;
    }
    .sp-match-chip.dragging { opacity: 0.5; }
    .sp-match-slot.over { background: #fff0f0; border-color: #940000; }
    .sp-tf label, .sp-q legend { font-weight: 600; }
    @media (max-width: 700px) { .sp-match { grid-template-columns: 1fr; } }
</style>
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
    if (label) {
        var wait = document.getElementById('quizWait');
        var clock = document.getElementById('quizClock');
        var target = wait ? parseInt(wait.dataset.opens, 10) : parseInt(clock.dataset.closes, 10);
        var sent = false;
        function tick() {
            var left = target - Math.floor(Date.now() / 1000);
            label.textContent = formatLeft(left);
            if (left > 0) return;
            if (wait) { window.location.reload(); return; }
            if (sent) return;
            sent = true;
            label.textContent = '00:00';
            var form = document.getElementById('quizTakeForm');
            if (!form) return;
            form.querySelectorAll('[required]').forEach(function (input) { input.required = false; });
            var button = document.getElementById('quizSubmitBtn');
            if (button) { button.disabled = true; button.textContent = 'Time is up'; }
            form.submit();
        }
        tick();
        setInterval(tick, 1000);
    }

    // Drag-drop matching: bank chips -> A slots
    document.querySelectorAll('.sp-match').forEach(function (board) {
        var bank = board.querySelector('.sp-match-bank');
        var hiddenWrap = board.querySelector('.sp-match-hidden');
        function syncHidden() {
            if (!hiddenWrap) return;
            hiddenWrap.querySelectorAll('input[data-left]').forEach(function (inp) {
                var left = inp.getAttribute('data-left');
                var slot = board.querySelector('.sp-match-slot[data-left="'+left+'"] .sp-match-chip');
                inp.value = slot ? (slot.getAttribute('data-right') || '') : '';
            });
        }
        board.querySelectorAll('.sp-match-chip').forEach(function (chip) {
            chip.setAttribute('draggable', 'true');
            chip.addEventListener('dragstart', function (e) {
                chip.classList.add('dragging');
                e.dataTransfer.setData('text/plain', chip.getAttribute('data-right') || '');
                e.dataTransfer.effectAllowed = 'move';
            });
            chip.addEventListener('dragend', function () {
                chip.classList.remove('dragging');
                syncHidden();
            });
        });
        board.querySelectorAll('.sp-match-slot, .sp-match-bank').forEach(function (zone) {
            zone.addEventListener('dragover', function (e) {
                e.preventDefault();
                zone.classList.add('over');
            });
            zone.addEventListener('dragleave', function () { zone.classList.remove('over'); });
            zone.addEventListener('drop', function (e) {
                e.preventDefault();
                zone.classList.remove('over');
                var rightKey = e.dataTransfer.getData('text/plain');
                var chip = board.querySelector('.sp-match-chip[data-right="'+rightKey+'"]');
                if (!chip) return;
                // If dropping into a slot that already has a chip, return old chip to bank
                if (zone.classList.contains('sp-match-slot')) {
                    var existing = zone.querySelector('.sp-match-chip');
                    if (existing && existing !== chip && bank) bank.appendChild(existing);
                    zone.appendChild(chip);
                } else if (bank) {
                    bank.appendChild(chip);
                }
                syncHidden();
            });
        });
        syncHidden();
    });
})();
</script>
@endsection
