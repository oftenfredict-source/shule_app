@extends('StudentPortal.layout')
@section('title', 'Notes')
@section('content')
<div class="sp-page-head">
    <h1>Notes</h1>
    <p>Materials shared with {{ $classLabel }}</p>
</div>
<div class="sp-list">
@forelse($notes as $note)
    <a class="sp-list-item" href="{{ route('student_portal.note', $note->id) }}">
        <div>
            <strong>{{ $note->title }}</strong>
            <div class="sp-list-meta">
                {{ $note->created_at ? $note->created_at->format('d M Y') : '' }}{{ $note->original_name ? ' · File attached' : '' }}{{ !empty($note->external_url) ? ' · Link available' : '' }}
            </div>
        </div>
        <span class="sp-list-arrow"><i class="fa fa-arrow-right" aria-hidden="true"></i></span>
    </a>
@empty
    <div class="sp-empty">No notes for your class yet.</div>
@endforelse
</div>
@endsection
