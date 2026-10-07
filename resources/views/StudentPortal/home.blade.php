@extends('StudentPortal.layout')
@section('title', 'Home')
@section('main_class', 'sp-main sp-main--home')
@section('content')
<section class="sp-home-hero">
    <div class="sp-home-hero-inner">
        <span class="sp-kicker">Student Portal</span>
        <h1>{{ $school->school_name }}</h1>
        <p class="sp-lead">Welcome back, {{ $student->first_name }}. Your learning materials are ready.</p>
        <div class="sp-meta-row">
            <span><strong>Admission</strong> {{ $student->admission_number }}</span>
            <span><strong>Class</strong> {{ $classLabel }}</span>
        </div>
    </div>
</section>

<div class="sp-destinations">
    <a class="sp-dest" href="{{ route('student_portal.notes') }}">
        <span class="sp-dest-icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
        <h2>Notes</h2>
        <p>Lesson notes and attachments shared by your teachers.</p>
        <div class="sp-dest-foot">
            <span class="sp-count">{{ $noteCount }}</span>
            <span>Open <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
        </div>
    </a>

    <a class="sp-dest" href="{{ route('student_portal.books') }}">
        <span class="sp-dest-icon"><i class="fa fa-book" aria-hidden="true"></i></span>
        <h2>Books &amp; Files</h2>
        <p>Reading materials, handouts, and media for your class.</p>
        <div class="sp-dest-foot">
            <span class="sp-count">{{ $bookCount }}</span>
            <span>Browse <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
        </div>
    </a>

    <a class="sp-dest" href="{{ route('student_portal.quizzes') }}">
        <span class="sp-dest-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span>
        <h2>Quizzes</h2>
        <p>Practice assessments with instant scoring after you submit.</p>
        <div class="sp-dest-foot">
            <span class="sp-count">{{ $quizCount }}</span>
            <span>Start <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
        </div>
    </a>
</div>
@endsection
