@extends('StudentPortal.layout')
@section('title', 'Books')
@section('content')
<div class="sp-page-head">
    <h1>Books &amp; Files</h1>
    <p>Reading and media for {{ $classLabel }}</p>
</div>
<div class="sp-list">
@forelse($books as $book)
    <a class="sp-list-item" href="{{ route('student_portal.book', $book->id) }}">
        <div>
            <strong>{{ $book->title }}</strong>
            <div class="sp-list-meta">
                {{ $book->author ?: 'Learning material' }}{{ $book->original_name ? ' · File available' : '' }}{{ !empty($book->external_url) ? ' · Link available' : '' }}
            </div>
        </div>
        <span class="sp-list-arrow"><i class="fa fa-arrow-right" aria-hidden="true"></i></span>
    </a>
@empty
    <div class="sp-empty">No books or files for your class yet.</div>
@endforelse
</div>
@endsection
