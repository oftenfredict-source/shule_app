<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'School Session Timetable' }}</title>
    <style>
        @page { margin: 8mm 6mm 12mm 6mm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 8px; color: #222; margin: 0; padding: 0 0 20px 0; }
        .header { text-align: center; margin-bottom: 8px; border-bottom: 2.5px solid #940000; padding-bottom: 6px; }
        .school-name { font-size: 15px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 11px; font-weight: bold; margin-top: 5px; background: #f8f9fa; padding: 5px 8px; border: 1px solid #ccc; text-transform: uppercase; }
        table.master { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.master th, table.master td {
            border: 1px solid #555;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 7px;
            word-wrap: break-word;
        }
        table.master th {
            background: #940000;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
        }
        table.master td.day-col { background: #f3e6e6; font-weight: bold; font-size: 8px; width: 18mm; }
        table.master td.class-col { font-weight: bold; font-size: 8px; width: 22mm; }
        table.master td.break-col { background: #fff3cd; font-weight: bold; color: #856404; writing-mode: vertical-rl; }
        .cell-line { display: block; line-height: 1.25; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 7.5px; color: #666; border-top: 1px solid #ccc; padding-top: 3px; }
        .watermark {
            position: fixed; top: 45%; left: 5%; width: 100%; text-align: center; opacity: 0.05;
            transform: rotate(-45deg); font-size: 90px; font-weight: bold; color: #000; z-index: 0; text-transform: uppercase;
        }
        .sig-table { width: 100%; margin-top: 14px; border-collapse: collapse; }
        .sig-table td { border: none; text-align: center; vertical-align: bottom; }
        .sig-line { border-top: 2px solid #940000; width: 140px; margin: 0 auto; display: block; }
        .sig-label { font-size: 7.5px; color: #940000; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>
    @php
        if (!function_exists('getSmartBase64SchoolTt')) {
            function getSmartBase64SchoolTt($path) {
                if (!$path) return null;
                $path = ltrim($path, '/');
                $parent = dirname(base_path());
                foreach ([public_path($path), $parent . '/public_html/' . $path, base_path('public/' . $path)] as $fullPath) {
                    if (@file_exists($fullPath) && is_file($fullPath)) {
                        try {
                            $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'png';
                            return 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($fullPath));
                        } catch (\Exception $e) {}
                    }
                }
                return null;
            }
        }
        $schoolLogoBase64 = getSmartBase64SchoolTt($school->school_logo ?? '');
        $schoolStampBase64 = getSmartBase64SchoolTt($school->school_stamp ?? '');
        $schoolSignBase64 = getSmartBase64SchoolTt($school->school_signature ?? '');
        $days = $days ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $periods = $periods ?? [];
        $classRows = $class_rows ?? [];
        $grid = $grid ?? [];
        $rowCount = max(1, count($classRows));
        $fmt = function ($t) { return substr((string) $t, 0, 5); };
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 50px; margin-bottom: 3px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div class="report-title">{{ $reportTitle ?? 'School Session Timetable' }}</div>
    </div>

    <table class="master">
        <thead>
            <tr>
                <th rowspan="2">Day</th>
                <th rowspan="2">Class</th>
                @foreach($periods as $p)
                    @if(!empty($p['is_break']))
                        <th rowspan="2">{{ $p['break_label'] ?? 'Break' }}</th>
                    @elseif(!empty($p['is_prepo']))
                        <th rowspan="2">{{ $p['break_label'] ?? 'PREPARATION' }}<br>{{ $fmt($p['start']) }}-{{ $fmt($p['end']) }}</th>
                    @else
                        <th>{{ $fmt($p['start']) }} - {{ $fmt($p['end']) }}</th>
                    @endif
                @endforeach
            </tr>
            <tr>
                @foreach($periods as $p)
                    @if(empty($p['is_break']) && empty($p['is_prepo']))
                        <th>{{ $p['number'] ?? '' }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($days as $day)
                @foreach($classRows as $i => $row)
                    <tr>
                        @if($i === 0)
                            <td class="day-col" rowspan="{{ $rowCount }}">{{ strtoupper($day) }}</td>
                        @endif
                        <td class="class-col" style="background: {{ $row['color'] ?? '#f8f8f8' }};">{{ $row['label'] }}</td>
                        @foreach($periods as $p)
                            @if(!empty($p['is_break']))
                                @if($i === 0)
                                    <td class="break-col" rowspan="{{ $rowCount }}">{{ $p['break_label'] ?? 'BREAK' }}</td>
                                @endif
                            @else
                                @php
                                    $key = $day . '|' . $row['key'] . '|' . $p['start'] . '-' . $p['end'];
                                    $items = $grid[$key] ?? [];
                                @endphp
                                <td>
                                    @forelse($items as $item)
                                        <span class="cell-line">{{ $item['text'] ?? '' }}</span>
                                    @empty
                                        &nbsp;
                                    @endforelse
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <table class="sig-table">
        <tr>
            <td>
                @if($schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}" style="max-height: 70px;">
                @endif
            </td>
            <td>
                @if($schoolSignBase64)
                    <img src="{{ $schoolSignBase64 }}" style="max-height: 32px;"><br>
                @endif
                <span class="sig-line"></span>
                <span class="sig-label">Head of School / Academic Master</span>
            </td>
        </tr>
    </table>
    <div class="footer">Generated by ShuleXpert · Powered by EmCa Techonology · {{ date('d M Y H:i') }}</div>
</body>
</html>
