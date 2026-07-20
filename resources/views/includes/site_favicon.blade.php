{{-- Favicon — supports dynamic school logo via $seo['favicon'] --}}
@php
    $v = function (string $relative) {
        $full = public_path(ltrim($relative, '/'));
        $url = url($relative);
        return is_file($full) ? ($url . '?v=' . filemtime($full)) : $url;
    };

    $customFavicon = $seo['favicon'] ?? null;
    if ($customFavicon) {
        // Absolute URL or site-relative path (e.g. school logo).
        if (!preg_match('#^https?://#i', $customFavicon)) {
            $rel = ltrim(parse_url($customFavicon, PHP_URL_PATH) ?: $customFavicon, '/');
            $full = public_path($rel);
            $customFavicon = is_file($full) ? (url($rel) . '?v=' . filemtime($full)) : url($rel);
        }
        $ico = $customFavicon;
        $png16 = $customFavicon;
        $png32 = $customFavicon;
        $apple = $customFavicon;
        $chrome192 = $customFavicon;
    } else {
        $ico = $v('/favicon.ico');
        $png16 = $v('/images/favicon-16x16.png');
        $png32 = $v('/images/favicon-32x32.png');
        $apple = $v('/images/apple-touch-icon.png');
        $chrome192 = $v('/images/android-chrome-192x192.png');
    }
    $manifest = $v('/site.webmanifest');
@endphp
<link rel="icon" href="{{ $ico }}" sizes="any">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $png16 }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ $png32 }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ $chrome192 }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $apple }}">
<link rel="shortcut icon" href="{{ $ico }}">
@unless(!empty($seo['favicon']))
<link rel="manifest" href="{{ $manifest }}">
@endunless
<meta name="msapplication-TileColor" content="#940000">
<meta name="msapplication-TileImage" content="{{ $chrome192 }}">
<meta name="theme-color" content="#940000">
