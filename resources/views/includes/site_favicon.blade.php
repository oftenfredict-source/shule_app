{{-- Site favicon: ShuleXpert logo with baked-in border-radius --}}
@php
    $sxFaviconFile = public_path('images/shuleXpert-favicon.png');
    $sxFallbackJpg = public_path('images/shuleXpert.jpg');
    $sxLogoFile = public_path('images/logo.png');
    if (is_file($sxFaviconFile)) {
        $sxFaviconUrl = asset('images/shuleXpert-favicon.png') . '?v=' . filemtime($sxFaviconFile);
        $sxFaviconType = 'image/png';
    } elseif (is_file($sxFallbackJpg)) {
        $sxFaviconUrl = asset('images/shuleXpert.jpg') . '?v=' . filemtime($sxFallbackJpg);
        $sxFaviconType = 'image/jpeg';
    } elseif (is_file($sxLogoFile)) {
        $sxFaviconUrl = asset('images/logo.png') . '?v=' . filemtime($sxLogoFile);
        $sxFaviconType = 'image/png';
    } else {
        $sxFaviconUrl = asset('images/shuleXpert-favicon.png');
        $sxFaviconType = 'image/png';
    }
@endphp
<link rel="icon" type="image/png" href="{{ $sxFaviconUrl }}" sizes="32x32">
<link rel="icon" type="{{ $sxFaviconType }}" href="{{ $sxFaviconUrl }}" sizes="192x192">
<link rel="shortcut icon" type="{{ $sxFaviconType }}" href="{{ $sxFaviconUrl }}">
<link rel="apple-touch-icon" href="{{ $sxFaviconUrl }}">
<meta name="msapplication-TileImage" content="{{ $sxFaviconUrl }}">
