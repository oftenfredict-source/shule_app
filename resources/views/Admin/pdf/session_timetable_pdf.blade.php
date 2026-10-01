<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Session Timetable' }}</title>
    <style>
        @page { margin: 10mm 8mm 14mm 8mm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 9.5px; color: #333; line-height: 1.4; background: #fff; margin: 0; padding: 0 0 24px 0; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 2.5px solid #940000; padding-bottom: 8px; }
        .school-name { font-size: 16px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 12px; font-weight: bold; margin-top: 6px; background: #f8f9fa; padding: 6px 8px; border: 1.5px solid #ccc; }

        .info-section { margin-bottom: 10px; width: 100%; }
        .info-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .info-table td { padding: 6px 7px; border: 1px solid #ccc; width: 25%; vertical-align: middle; }
        .label { font-weight: bold; color: #444; background: #f8f9fa; }

        .section-title { font-size: 11px; font-weight: bold; color: #940000; border-bottom: 1.5px solid #940000; padding-bottom: 3px; margin-top: 10px; margin-bottom: 6px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; border: 1.5px solid #ccc; }
        table.data-table th, table.data-table td {
            border: 1px solid #ccc;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            font-size: 8.5px;
        }
        table.data-table th {
            background-color: #940000;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
            font-size: 9px;
        }
        table.data-table td.time-col {
            background: #f7f7f7;
            font-weight: bold;
            width: 12%;
            white-space: nowrap;
        }
        table.data-table td.break-cell {
            background: #fff3cd;
            font-weight: bold;
            color: #856404;
            text-transform: uppercase;
        }
        table.data-table td.prepo-label {
            background: #e8f4fd;
            font-weight: bold;
            color: #0c5460;
            text-transform: uppercase;
        }
        .cell-subject { font-weight: bold; color: #222; display: block; }
        .cell-teacher { color: #555; font-size: 8px; display: block; }
        .cell-multi { margin-top: 3px; padding-top: 3px; border-top: 1px dashed #ddd; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #ccc; padding-top: 4px; background: #fff; }

        .sig-table { width: 100%; margin-top: 22px; border-collapse: collapse; page-break-inside: avoid; table-layout: fixed; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; width: 50%; }
        .stamp-cell { text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 100px; max-height: 100px; opacity: 0.85; }
        .sign-cell { text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 36px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 140px; max-height: 36px; }
        .sig-line   { border-top: 2px solid #940000; width: 150px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 8px; color: #940000; font-weight: bold; padding-top: 3px; display: block; text-transform: uppercase; }

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
        if (!function_exists('getSmartBase64Timetable')) {
            function getSmartBase64Timetable($path) {
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

        $schoolLogoBase64  = getSmartBase64Timetable($school->school_logo ?? '');
        $schoolStampBase64 = getSmartBase64Timetable($school->school_stamp ?? '');
        $schoolSignBase64  = getSmartBase64Timetable($school->school_signature ?? '');

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $classLabel = trim(($subclass->class_name ?? '') . ' ' . ($subclass->subclass_name ?? ''));
        $definition = $definition ?? null;
        $breakTimes = collect($breakTimes ?? []);
        $sessions = collect($sessions ?? []);

        $regularByDay = [];
        $prepoByDay = [];
        foreach ($days as $day) {
            $regularByDay[$day] = [];
            $prepoByDay[$day] = [];
        }

        foreach ($sessions as $session) {
            $day = $session['day'] ?? '';
            if (!isset($regularByDay[$day])) {
                continue;
            }
            $start = $session['start_time'] ?? '';
            $end = $session['end_time'] ?? '';
            $timeKey = $start . '-' . $end;
            $isPrepo = !empty($session['is_prepo']);
            if ($isPrepo) {
                if (!isset($prepoByDay[$day][$timeKey])) {
                    $prepoByDay[$day][$timeKey] = [];
                }
                $prepoByDay[$day][$timeKey][] = $session;
            } else {
                if (!isset($regularByDay[$day][$timeKey])) {
                    $regularByDay[$day][$timeKey] = [];
                }
                $regularByDay[$day][$timeKey][] = $session;
            }
        }

        $regularSlots = [];
        foreach ($days as $day) {
            foreach (array_keys($regularByDay[$day]) as $tk) {
                $regularSlots[$tk] = true;
            }
        }
        foreach ($breakTimes as $bt) {
            $btStart = is_object($bt) ? ($bt->start_time ?? '') : ($bt['start_time'] ?? '');
            $btEnd = is_object($bt) ? ($bt->end_time ?? '') : ($bt['end_time'] ?? '');
            if ($btStart && $btEnd) {
                $regularSlots[$btStart . '-' . $btEnd . '-BREAK'] = true;
            }
        }
        $sortedRegularSlots = array_keys($regularSlots);
        usort($sortedRegularSlots, function ($a, $b) {
            $aStart = str_replace('-BREAK', '', explode('-', $a)[0] ?? '');
            $bStart = str_replace('-BREAK', '', explode('-', $b)[0] ?? '');
            return strcmp($aStart, $bStart);
        });

        $prepoSlots = [];
        foreach ($days as $day) {
            foreach (array_keys($prepoByDay[$day]) as $tk) {
                $prepoSlots[$tk] = true;
            }
        }
        $sortedPrepoSlots = array_keys($prepoSlots);
        usort($sortedPrepoSlots, function ($a, $b) {
            return strcmp(explode('-', $a)[0] ?? '', explode('-', $b)[0] ?? '');
        });

        $formatCell = function (array $list) {
            if (empty($list)) {
                return '<span style="color:#bbb;">-</span>';
            }
            $seen = [];
            $unique = [];
            foreach ($list as $s) {
                $key = ($s['subject_name'] ?? '') . '|' . ($s['teacher_name'] ?? '');
                if (isset($seen[$key])) continue;
                $seen[$key] = true;
                $unique[] = $s;
            }
            $html = '';
            foreach ($unique as $i => $s) {
                if ($i > 0) {
                    $html .= '<div class="cell-multi"></div>';
                }
                $html .= '<span class="cell-subject">' . e($s['subject_name'] ?? 'N/A') . '</span>';
                $html .= '<span class="cell-teacher">' . e($s['teacher_name'] ?? '') . '</span>';
            }
            return $html;
        };
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 58px; margin-bottom: 4px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->formattedPhones() }}@if(!empty($school->email)) | Email: {{ $school->email }}@endif</div>
        <div class="report-title">{{ $reportTitle ?? ('SESSION TIMETABLE, ' . strtoupper($classLabel)) }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Class / Stream:</td>
                <td>{{ $classLabel ?: '-' }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Session Hours:</td>
                <td>
                    @if($definition)
                        {{ $definition->session_start_time ?? '' }} - {{ $definition->session_end_time ?? '' }}
                    @else,
                    @endif
                </td>
                <td class="label">Prepo:</td>
                <td>
                    @if($definition && !empty($definition->has_prepo))
                        {{ $definition->prepo_start_time ?? '' }} - {{ $definition->prepo_end_time ?? '' }}
                    @else
                        Not set
                    @endif
                </td>
            </tr>
            @if($breakTimes->count())
            <tr>
                <td class="label">Break Times:</td>
                <td colspan="3">
                    @foreach($breakTimes as $i => $bt)
                        @php
                            $btStart = is_object($bt) ? ($bt->start_time ?? '') : ($bt['start_time'] ?? '');
                            $btEnd = is_object($bt) ? ($bt->end_time ?? '') : ($bt['end_time'] ?? '');
                        @endphp
                        Break {{ $i + 1 }}: {{ $btStart }} - {{ $btEnd }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section-title">Weekly Session Timetable</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:12%;">Time</th>
                @foreach($days as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($sortedRegularSlots as $timeSlot)
                @if(str_contains($timeSlot, '-BREAK'))
                    @php
                        $clean = str_replace('-BREAK', '', $timeSlot);
                        $parts = explode('-', $clean);
                        $label = ($parts[0] ?? '') . ' - ' . ($parts[1] ?? '');
                    @endphp
                    <tr>
                        <td class="time-col">{{ $label }}</td>
                        <td colspan="5" class="break-cell">Break Time</td>
                    </tr>
                @else
                    @php
                        $parts = explode('-', $timeSlot);
                        $label = ($parts[0] ?? '') . ' - ' . ($parts[1] ?? '');
                    @endphp
                    <tr>
                        <td class="time-col">{{ $label }}</td>
                        @foreach($days as $day)
                            <td>{!! $formatCell($regularByDay[$day][$timeSlot] ?? []) !!}</td>
                        @endforeach
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" style="color:#888;">No sessions scheduled for this class.</td>
                </tr>
            @endforelse

            @if(count($sortedPrepoSlots) > 0)
                <tr>
                    <td colspan="6" class="prepo-label">Prepo Session</td>
                </tr>
                @foreach($sortedPrepoSlots as $timeSlot)
                    @php
                        $parts = explode('-', $timeSlot);
                        $label = ($parts[0] ?? '') . ' - ' . ($parts[1] ?? '');
                    @endphp
                    <tr>
                        <td class="time-col">{{ $label }}</td>
                        @foreach($days as $day)
                            <td>{!! $formatCell($prepoByDay[$day][$timeSlot] ?? []) !!}</td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
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
                <span class="sig-label">Head of School / Academic Master</span>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by ShuleXpert · Powered by EmCa Techonology · {{ date('d M Y H:i') }}
    </div>
</body>
</html>
