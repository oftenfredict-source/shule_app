@extends('StudentPortal.layout')
@section('title', 'Quizzes')
@section('content')
<div class="sp-page-head">
    <h1>Quizzes</h1>
    <p>Assessments for {{ $classLabel }}</p>
</div>
<div class="sp-list">
@forelse($quizzes as $quiz)
    @php
        $attempt = $attempts[$quiz->id] ?? null;
        $status = $quiz->windowStatus();
        if ($attempt) {
            $meta = 'Completed: '.$attempt->score.'/'.$attempt->total;
        } elseif ($status === 'upcoming') {
            $meta = 'Opens '.$quiz->opens_at->format('d M Y H:i');
        } elseif ($status === 'closed') {
            $meta = 'Closed';
        } elseif ($quiz->isScheduled()) {
            $meta = 'Open now';
        } else {
            $meta = 'Not attempted yet';
        }
    @endphp
    <a class="sp-list-item" href="{{ route('student_portal.quiz', $quiz->id) }}">
        <div>
            <strong>{{ $quiz->title }}</strong>
            <div class="sp-list-meta">
                {{ $meta }}
                @if(!$attempt && $quiz->isScheduled() && $status !== 'closed')
                    <span class="sp-live-timer" data-opens="{{ $quiz->opens_at->timestamp }}" data-closes="{{ $quiz->closes_at->timestamp }}"></span>
                @endif
            </div>
        </div>
        <span class="sp-list-arrow"><i class="fa fa-arrow-right" aria-hidden="true"></i></span>
    </a>
@empty
    <div class="sp-empty">No quizzes for your class yet.</div>
@endforelse
</div>
@php
    $completed = $attempts->filter(fn ($attempt) => (int) $attempt->total > 0);
    $overallAverage = $completed->isNotEmpty()
        ? round($completed->avg(fn ($attempt) => ($attempt->score / $attempt->total) * 100), 1)
        : null;
@endphp
@if($overallAverage !== null)
    <div class="sp-score sp-score-bottom">
        Average of your quizzes: <strong>{{ $overallAverage }}%</strong>
    </div>
@endif
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
    var timers = document.querySelectorAll('.sp-live-timer');
    if (!timers.length) return;
    function tick() {
        var now = Math.floor(Date.now() / 1000);
        var reload = false;
        timers.forEach(function (el) {
            var opens = parseInt(el.dataset.opens, 10);
            var closes = parseInt(el.dataset.closes, 10);
            if (now < opens) {
                el.textContent = ' · opens in ' + formatLeft(opens - now);
            } else if (now <= closes) {
                el.textContent = ' · closes in ' + formatLeft(closes - now);
            } else {
                el.textContent = '';
                reload = true;
            }
        });
        if (reload) window.location.reload();
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endsection
