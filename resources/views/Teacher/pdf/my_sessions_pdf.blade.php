<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'My Teaching Sessions' }}</title>
    <style>
        @page { margin: 12mm 10mm 16mm 10mm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10.5px; color: #333; line-height: 1.45; background: #fff; margin: 0; padding: 0 0 28px 0; }
        .header { text-align: center; margin-bottom: 14px; border-bottom: 2.5px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 17px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 13px; font-weight: bold; margin-top: 8px; background: #f8f9fa; padding: 7px 8px; border: 1.5px solid #ccc; }

        .info-section { margin-bottom: 12px; width: 100%; }
        .info-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .info-table td { padding: 7px 8px; border: 1px solid #ccc; width: 25%; vertical-align: middle; }
        .label { font-weight: bold; color: #444; background: #f8f9fa; }

        .section-title { font-size: 11.5px; font-weight: bold; color: #940000; border-bottom: 1.5px solid #940000; padding-bottom: 4px; margin-top: 14px; margin-bottom: 8px; }

        .stats-grid { width: 100%; margin-bottom: 14px; border-collapse: collapse; table-layout: fixed; }
        .stats-grid td {
            width: 25%;
            text-align: center;
            padding: 12px 6px;
            border: 1px solid #ccc;
            vertical-align: middle;
            background: #fff;
        }
        .stat-val { font-size: 16px; font-weight: bold; color: #222; line-height: 1.2; }
        .stat-label { font-size: 8.5px; color: #666; text-transform: uppercase; margin-top: 4px; letter-spacing: 0.3px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; table-layout: fixed; border: 1.5px solid #ccc; }
        table.data-table th, table.data-table td {
            border: 1px solid #ccc;
            padding: 7px 6px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #940000;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        table.data-table td.center { text-align: center; }
        table.data-table td.day-cell { text-align: center; background: #f7f7f7; font-weight: bold; vertical-align: middle; }
        table.data-table tr.prepo-row td { background: #fcfcfc; }
        .muted { color: #666; font-size: 8.5px; }
        .badge-prepo { color: #0c5460; font-weight: bold; font-size: 8px; text-transform: uppercase; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; text-align: center; font-size: 8.5px; color: #666; border-top: 1px solid #ccc; padding-top: 5px; background: #fff; }

        .sig-table { width: 100%; margin-top: 28px; border-collapse: collapse; page-break-inside: avoid; table-layout: fixed; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; width: 50%; }
        .stamp-cell { text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 110px; max-height: 110px; opacity: 0.85; }
        .sign-cell { text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 150px; max-height: 40px; }
        .sig-line   { border-top: 2px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 8.5px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase; }

        .watermark {
            position: fixed;
            top: 45%;
            left: 5%;
            width: 100%;
            text-align: center;
            opacity: 0.05;
            transform: rotate(-45deg);
            font-size: 100px;
            font-weight: bold;
            color: #000;
            z-index: 1000;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

    @php
        if (!function_exists('getSmartBase64TeacherSessions')) {
            function getSmartBase64TeacherSessions($path) {
                if (!$path) return null;
                $path = ltrim($path, '/');
                $base = base_path();
                $parent = dirname($base);
                $possibilities = [
                    public_path($path),
                    public_path('uploads/' . $path),
                    $parent . '/public_html/' . $path,
                    $parent . '/public_html/uploads/' . $path,
                ];
                if (isset($_SERVER['DOCUMENT_ROOT'])) {
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                    $possibilities[] = $docRoot . '/' . $path;
                    $possibilities[] = $docRoot . '/uploads/' . $path;
                }
                $possibilities[] = base_path($path);
                $possibilities[] = base_path('public/' . $path);
                $possibilities[] = base_path('../public_html/' . $path);
                foreach ($possibilities as $fullPath) {
                    if (@file_exists($fullPath) && is_file($fullPath)) {
                        try {
                            $data = base64_encode(file_get_contents($fullPath));
                            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                            return 'data:image/' . ($ext ?: 'png') . ';base64,' . $data;
                        } catch (\Exception $e) {}
                    }
                }
                return null;
            }
        }

        $schoolLogoBase64  = getSmartBase64TeacherSessions($school->school_logo ?? '');
        $schoolStampBase64 = getSmartBase64TeacherSessions($school->school_stamp ?? '');
        $schoolSignBase64  = getSmartBase64TeacherSessions($school->school_signature ?? '');

        $rows = $rows ?? [];
        $totalSessions = count($rows);
        $prepoCount = collect($rows)->where('is_prepo', true)->count();
        $regularCount = $totalSessions - $prepoCount;
        $uniqueClasses = collect($rows)->pluck('class_label')->filter()->unique()->count();
        $uniqueSubjects = collect($rows)->pluck('subject_name')->filter()->unique()->count();
        $weekLabel = $weekLabel ?? '';
        $teacherName = $teacherName ?? 'Teacher';
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }}@if(!empty($school->email)) | Email: {{ $school->email }}@endif</div>
        <div class="report-title">{{ $reportTitle ?? ('TEACHER SESSIONS — ' . strtoupper($teacherName)) }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Teacher:</td>
                <td>{{ $teacherName }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Week:</td>
                <td>{{ $weekLabel }}</td>
                <td class="label">Report:</td>
                <td>My Teaching Sessions</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Sessions Overview</div>
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-val">{{ $totalSessions }}</div>
                <div class="stat-label">Total Sessions</div>
            </td>
            <td>
                <div class="stat-val">{{ $regularCount }}</div>
                <div class="stat-label">Regular</div>
            </td>
            <td>
                <div class="stat-val">{{ $prepoCount }}</div>
                <div class="stat-label">Prepo</div>
            </td>
            <td>
                <div class="stat-val">{{ $uniqueClasses }} / {{ $uniqueSubjects }}</div>
                <div class="stat-label">Classes / Subjects</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Weekly Sessions Details</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Day</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 16%;">Time</th>
                <th style="width: 18%;">Class</th>
                <th style="width: 22%;">Subject</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 8%;">#</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr class="{{ !empty($row['is_prepo']) ? 'prepo-row' : '' }}">
                    @if(!empty($row['is_first_of_day']))
                        <td class="day-cell" rowspan="{{ (int) ($row['day_rowspan'] ?? 1) }}">
                            {{ $row['day'] ?? '' }}
                        </td>
                    @endif
                    <td class="center">{{ $row['date_label'] ?? '' }}</td>
                    <td class="center">{{ $row['time_label'] ?? '' }}</td>
                    <td class="center">{{ $row['class_label'] ?? '—' }}</td>
                    <td>
                        {{ $row['subject_name'] ?? 'N/A' }}
                        @if(!empty($row['is_prepo']))
                            <div class="badge-prepo">Prepo session</div>
                        @endif
                    </td>
                    <td class="center">{{ !empty($row['is_prepo']) ? 'Prepo' : 'Regular' }}</td>
                    <td class="center">{{ $i + 1 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted" style="text-align:center;">No sessions scheduled for this week.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="sig-table">
        <tr>
            <td class="stamp-cell">
                @if($schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}" alt="Stamp">
                @endif
            </td>
            <td class="sign-cell">
                @if($schoolSignBase64)
                    <div class="school-signature-img"><img src="{{ $schoolSignBase64 }}" alt="Signature"></div>
                @endif
                <span class="sig-line"></span>
                <span class="sig-label">Teacher Signature</span>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by ShuleXpert · Powered by EmCa Techonology · {{ date('d M Y H:i') }}
    </div>
</body>
</html>
