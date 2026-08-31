<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Online Application, {{ $meta['application_number'] ?? '' }}</title>
    <style>
        @page { margin: 12mm 10mm 16mm 10mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; color: #333; line-height: 1.45; }
        .header { text-align: center; border-bottom: 2.5px solid #940000; padding-bottom: 10px; margin-bottom: 14px; }
        .school-name { font-size: 16px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .school-meta { font-size: 9px; color: #666; margin-top: 4px; }
        .report-title { margin-top: 10px; font-size: 13px; font-weight: bold; background: #f8f9fa; border: 1.5px solid #ccc; padding: 8px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; table-layout: fixed; }
        .meta-table td { border: 1px solid #ccc; padding: 7px 8px; vertical-align: top; width: 25%; }
        .meta-table .label { background: #f8f9fa; font-weight: bold; color: #444; width: 18%; }
        .section-title { font-size: 12px; font-weight: bold; color: #940000; border-bottom: 1.5px solid #940000; padding-bottom: 4px; margin: 16px 0 8px; }
        .section-desc { color: #777; font-size: 9px; margin-bottom: 8px; }
        .fields-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; table-layout: fixed; }
        .fields-table th, .fields-table td { border: 1px solid #ccc; padding: 7px 8px; vertical-align: top; word-wrap: break-word; }
        .fields-table th { width: 34%; background: #f7f7f7; color: #444; text-align: left; font-weight: bold; }
        .file-name { color: #940000; font-weight: bold; }
        .muted { color: #888; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #666; border-top: 1px solid #ccc; padding-top: 5px; }
        .badge { display: inline-block; padding: 2px 8px; border: 1px solid #940000; color: #940000; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .photo { max-width: 72px; max-height: 72px; border: 1px solid #ccc; }
        .logo-wrap { margin-bottom: 6px; }
        .logo-img { max-height: 60px; max-width: 120px; }
        .logo-placeholder,
        .photo-placeholder {
            display: inline-block;
            border: 1.5px dashed #c9a0a0;
            background: #faf5f5;
            color: #940000;
            text-align: center;
            vertical-align: middle;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .logo-placeholder {
            width: 70px;
            height: 70px;
            line-height: 70px;
            margin: 0 auto 6px;
        }
        .photo-placeholder {
            width: 72px;
            height: 72px;
            line-height: 72px;
        }
    </style>
</head>
<body>
@php
    if (!function_exists('oaPdfSmartBase64')) {
        function oaPdfSmartBase64($path) {
            if (!$path) {
                return null;
            }

            $originalPath = trim((string) $path);
            if (str_starts_with($originalPath, 'data:image/')) {
                return $originalPath;
            }

            $urlPath = parse_url($originalPath, PHP_URL_PATH);
            $path = ltrim($urlPath ?: $originalPath, '/');
            $filename = basename($path);
            $base = base_path();
            $parent = dirname($base);

            $possibilities = [
                public_path($path),
                public_path('userImages/' . $filename),
                public_path('uploads/' . $path),
                public_path('uploads/' . $filename),
                $parent . '/public_html/' . $path,
                $parent . '/public_html/userImages/' . $filename,
                $parent . '/public_html/uploads/' . $path,
                $parent . '/public_html/uploads/' . $filename,
            ];

            if (!empty($_SERVER['SCRIPT_FILENAME'])) {
                $scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
                $possibilities[] = $scriptDir . '/' . $path;
                $possibilities[] = $scriptDir . '/userImages/' . $filename;
                $possibilities[] = $scriptDir . '/uploads/' . $path;
            }

            if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
                $possibilities[] = $docRoot . '/' . $path;
                $possibilities[] = $docRoot . '/userImages/' . $filename;
                $possibilities[] = $docRoot . '/uploads/' . $path;
                $possibilities[] = $docRoot . '/uploads/' . $filename;
            }

            $possibilities[] = base_path($path);
            $possibilities[] = base_path('public/' . $path);
            $possibilities[] = base_path('public/userImages/' . $filename);
            $possibilities[] = base_path('../public_html/' . $path);

            // Absolute existing path already
            if (@is_file($originalPath)) {
                array_unshift($possibilities, $originalPath);
            }

            foreach (array_unique(array_filter($possibilities)) as $fullPath) {
                if (@is_file($fullPath)) {
                    try {
                        $data = base64_encode(file_get_contents($fullPath));
                        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'png');
                        if ($ext === 'jpg') {
                            $ext = 'jpeg';
                        }
                        return 'data:image/' . $ext . ';base64,' . $data;
                    } catch (\Throwable $e) {
                        // continue
                    }
                }
            }

            return null;
        }
    }

    $schoolLogoBase64 = oaPdfSmartBase64($school->school_logo ?? '');
@endphp

    <div class="header">
        <div class="logo-wrap">
            @if($schoolLogoBase64)
                <img class="logo-img" src="{{ $schoolLogoBase64 }}" alt="School logo">
            @else
                <div class="logo-placeholder">Logo</div>
            @endif
        </div>
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div class="school-meta">
            @if(!empty($school->address)) {{ $school->address }} · @endif
            @if(!empty($school->phone)) Tel: {{ $school->phone }} · @endif
            Online Admission Application
        </div>
        <div class="report-title">APPLICATION DETAILS, {{ $meta['application_number'] ?? '' }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Reference</td>
            <td>{{ $meta['application_number'] ?? '-' }}</td>
            <td class="label">Status</td>
            <td><span class="badge">{{ $meta['status'] ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="label">Student</td>
            <td>{{ $meta['student_name'] ?? '-' }}</td>
            <td class="label">Class</td>
            <td>{{ $meta['class_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Parent</td>
            <td>{{ $meta['parent_name'] ?? '-' }}</td>
            <td class="label">Phone</td>
            <td>{{ $meta['parent_phone'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Academic Year</td>
            <td>{{ $meta['academic_year'] ?? '-' }}</td>
            <td class="label">Submitted</td>
            <td>{{ $meta['submitted_at'] ?? '-' }}</td>
        </tr>
    </table>

    @foreach($sections as $section)
        <div class="section-title">{{ $section['title'] }}</div>
        @if(!empty($section['description']))
            <div class="section-desc">{{ $section['description'] }}</div>
        @endif
        <table class="fields-table">
            @foreach($section['fields'] as $field)
                @php
                    $photoBase64 = null;
                    if (!empty($field['is_file']) && !empty($field['is_image']) && !empty($field['filename'])) {
                        $photoBase64 = oaPdfSmartBase64('userImages/' . ltrim($field['filename'], '/'));
                        if (!$photoBase64) {
                            $photoBase64 = oaPdfSmartBase64($field['filename']);
                        }
                    }
                @endphp
                <tr>
                    <th>{{ $field['label'] }}</th>
                    <td>
                        @if(!empty($field['is_file']))
                            @if(!empty($field['is_image']))
                                @if($photoBase64)
                                    <div><img class="photo" src="{{ $photoBase64 }}" alt=""></div>
                                @else
                                    <div class="photo-placeholder">No Photo</div>
                                @endif
                                @if(!empty($field['filename']))
                                    <div class="file-name">{{ $field['filename'] }}</div>
                                @else
                                    <div class="muted">No photo uploaded</div>
                                @endif
                            @elseif(!empty($field['filename']))
                                <span class="file-name">{{ $field['filename'] }}</span>
                                <div class="muted">Uploaded document, open from Online Admission to view/download.</div>
                            @else
                                <span class="muted">No file uploaded</span>
                            @endif
                        @else
                            {{ $field['value'] ?? '-' }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endforeach

    <div class="footer">
        Generated by ShuleXpert on {{ $generatedAt ?? now()->format('d M Y H:i') }} · Powered by EmCa Techonology
    </div>
</body>
</html>
