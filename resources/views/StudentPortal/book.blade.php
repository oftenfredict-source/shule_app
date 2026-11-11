@extends('StudentPortal.layout')
@section('title', $book->title)
@section('content')
@php
    $embedUrl = null;
    $external = $book->external_url ?? null;
    if (!empty($external)) {
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $external, $m)) {
            $embedUrl = 'https://www.youtube.com/embed/' . $m[1] . '?rel=0';
        } elseif (preg_match('~youtube\.com/shorts/([A-Za-z0-9_-]{6,})~', $external, $m)) {
            $embedUrl = 'https://www.youtube.com/embed/' . $m[1] . '?rel=0';
        }
    }
@endphp
<div class="sp-panel">
    <a href="{{ route('student_portal.books') }}" class="sp-back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to books</a>
    <h1>{{ $book->title }}</h1>
    @if($book->author)<p style="color:#6b5c5c;margin-top:-4px;">{{ $book->author }}</p>@endif
    @if($book->description)
        <div style="white-space: pre-wrap; color: #3a2e2e; line-height: 1.7;">{{ $book->description }}</div>
    @endif

    @if($embedUrl)
        <div class="mt-3" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;background:#111;">
            <iframe
                src="{{ $embedUrl }}"
                title="{{ $book->title }}"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe>
        </div>
    @elseif(!empty($external))
        <div class="mt-3" style="border:1px solid rgba(148,0,0,0.12);background:#faf8f8;">
            <iframe
                src="{{ $external }}"
                title="{{ $book->title }}"
                style="width:100%;min-height:70vh;border:0;display:block;"
                sandbox="allow-scripts allow-same-origin allow-popups allow-forms"></iframe>
        </div>
    @endif

    @if($book->file_path)
        <p class="mt-4 mb-0">
            <a class="sp-btn" href="{{ route('student_portal.book.download', $book->id) }}" rel="noopener">
                <i class="fa fa-download" aria-hidden="true"></i> Download file{{ $book->original_name ? ': '.$book->original_name : '' }}
            </a>
        </p>
    @endif
</div>
@endsection
