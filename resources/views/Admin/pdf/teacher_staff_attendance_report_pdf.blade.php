<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }
        .info-section { margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 25%; }
        .summary-box { background: #fdf2f2; border: 1px solid #fbd5d5; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .summary-title { font-weight: bold; color: #940000; margin-bottom: 5px; border-bottom: 1px solid #fbd5d5; }
        .stats-grid { width: 100%; margin-bottom: 15px; }
        .stats-grid td { width: 25%; text-align: center; padding: 10px; border: 1px solid #eee; }
        .stat-val { font-size: 16px; font-weight: bold; color: #333; }
        .stat-label { font-size: 9px; color: #777; text-transform: uppercase; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 6px; text-align: left; }
        table.data-table th { background-color: #940000; color: #fff; font-weight: bold; }
        table.data-table td.center { text-align: center; }
        .badge-teacher { color: #0d6efd; font-size: 9px; }
        .badge-staff { color: #0dcaf0; font-size: 9px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }
        .sig-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 50%; text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 120px; max-height: 120px; opacity: 0.85; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 150px; max-height: 40px; }
        .sig-line { border-top: 1.5px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label { font-size: 8.5px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase; }
        .watermark { position: fixed; top: 45%; left: 5%; width: 100%; text-align: center; opacity: 0.05; transform: rotate(-45deg); font-size: 100px; font-weight: bold; color: #000; z-index: 1000; text-transform: uppercase; }
        .day-stat-danger { color: #dc3545; }
        .day-stat-info { color: #0dcaf0; }
        .day-stat-success { color: #198754; }
        .day-stat-purple { color: #6f42c1; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>
    @php
        if (!function_exists('getSmartBase64')) {
            function getSmartBase64($path) {
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
        $schoolLogoBase64  = getSmartBase64($school->school_logo ?? '');
        $schoolStampBase64 = getSmartBase64($school->school_stamp ?? '');
        $schoolSignBase64  = getSmartBase64($school->school_signature ?? '');
        $isDayReport = ($filter_type ?? '') === 'day';
        $daySummary = $day_summary ?? null;
        if (!function_exists('formatTsPdfTimeAmPm')) {
            function formatTsPdfTimeAmPm($time) {
                if (!$time) return '';
                $time = (string) $time;
                if (strlen($time) > 8) {
                    $time = substr($time, 11, 8);
                }
                if (strlen($time) === 5) {
                    $time .= ':00';
                }
                try {
                    return \Carbon\Carbon::createFromFormat('H:i:s', $time)->format('h:i A');
                } catch (\Exception $e) {
                    return $time;
                }
            }
        }
        $dayStatusLabels = [
            'Present' => 'Alikuwepo',
            'Late' => 'Amechelewa',
            'Early' => 'Amewahi',
            'Absent' => 'Hayupo',
            'Permitted' => 'Ruhusa',
            'Off Day' => 'Sio Siku ya Kazi',
        ];
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">{{ $title }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Period:</td>
                <td>{{ $period_label }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ $generated_at }}</td>
            </tr>
            <tr>
                <td class="label">Official Time:</td>
                <td>{{ $work_start_time }} - {{ $work_end_time }}</td>
                <td class="label">Status:</td>
                <td>{{ $period_incomplete ? 'In progress' : 'Complete' }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <div class="summary-title">Report Summary</div>
        @if($isDayReport && $daySummary)
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-val">{{ $daySummary['date_label'] ?? ($daySummary['date'] ?? '') }}</div>
                    <div class="stat-label">Tarehe</div>
                </td>
                <td>
                    <div class="stat-val day-stat-success">{{ $daySummary['present_count'] ?? 0 }} / {{ $daySummary['total_people'] ?? 0 }}</div>
                    <div class="stat-label">Wamehudhuria</div>
                </td>
                <td>
                    <div class="stat-val day-stat-danger">{{ $daySummary['late_count'] ?? 0 }}</div>
                    <div class="stat-label">Walio Chelewa</div>
                </td>
                <td>
                    <div class="stat-val day-stat-info">{{ $daySummary['early_count'] ?? 0 }}</div>
                    <div class="stat-label">Walio Wahi</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="stat-val day-stat-danger">{{ $daySummary['absent_count'] ?? 0 }}</div>
                    <div class="stat-label">Walikosa</div>
                </td>
                <td>
                    <div class="stat-val day-stat-purple">{{ $daySummary['permitted_count'] ?? 0 }}</div>
                    <div class="stat-label">Walio na Ruhusa</div>
                </td>
                <td>
                    <div class="stat-val day-stat-success">{{ $daySummary['on_time_count'] ?? 0 }}</div>
                    <div class="stat-label">Wakati Sahihi</div>
                </td>
                <td>
                    <div class="stat-val">{{ !empty($daySummary['is_working_day']) ? 'Ndiyo' : 'Hapana' }}</div>
                    <div class="stat-label">Siku ya Kazi</div>
                </td>
            </tr>
        </table>
        <p style="font-size:9px; color:#666; margin-top:8px; margin-bottom:0;">
            Present = ameweka kidole (check-in tu inatosha). Walio Chelewa = wameingia baada ya muda rasmi. Walio Wahi = wameingia kabla ya muda rasmi.
        </p>
        @else
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-val">{{ $total_working_days }}</div>
                    <div class="stat-label">Working Days</div>
                </td>
                @if($period_incomplete)
                <td>
                    <div class="stat-val">{{ $complete_working_days ?? $elapsed_working_days }}</div>
                    <div class="stat-label">Complete Working Days</div>
                </td>
                @endif
                <td>
                    <div class="stat-val">{{ count($rows) }}</div>
                    <div class="stat-label">Total People</div>
                </td>
                <td>
                    <div class="stat-val">{{ collect($rows)->sum('present_days') }}</div>
                    <div class="stat-label">Total Present Days</div>
                </td>
            </tr>
        </table>
        @if($period_incomplete)
        <p style="font-size:9px; color:#666; margin-top:8px; margin-bottom:0;">
            Absent = complete working days without fingerprint record in database (no scan = absent).
        </p>
        @endif
        @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Name</th>
                @if($isDayReport)
                <th class="center">Hali</th>
                <th class="center">Ingia</th>
                <th class="center">Toka</th>
                <th class="center">Walio Chelewa</th>
                <th class="center">Walio Wahi</th>
                @else
                <th class="center">Present</th>
                <th class="center">Permitted</th>
                <th class="center">Absent</th>
                <th class="center">Working Days</th>
                @if($period_incomplete)
                <th class="center">Complete Working Days</th>
                @endif
                <th class="center">Late</th>
                <th class="center">Early</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>
                    {{ $row['name'] }}
                    <span class="{{ $row['person_type'] === 'staff' ? 'badge-staff' : 'badge-teacher' }}">
                        ({{ $row['person_type'] === 'staff' ? 'Staff' : 'Teacher' }})
                    </span>
                </td>
                @if($isDayReport)
                @php
                    $dayStatus = $row['day_status'] ?? 'Absent';
                    $statusLabel = $dayStatusLabels[$dayStatus] ?? $dayStatus;
                    $isLate = $dayStatus === 'Late';
                    $isEarly = $dayStatus === 'Early';
                @endphp
                <td class="center">{{ $statusLabel }}</td>
                <td class="center">{{ !empty($row['check_in_time']) ? formatTsPdfTimeAmPm($row['check_in_time']) : '-' }}</td>
                <td class="center">{{ !empty($row['check_out_time']) ? formatTsPdfTimeAmPm($row['check_out_time']) : '-' }}</td>
                <td class="center">{{ $isLate ? 'Ndiyo' : '-' }}</td>
                <td class="center">{{ $isEarly ? 'Ndiyo' : '-' }}</td>
                @else
                <td class="center">{{ $row['present_days'] }}</td>
                <td class="center">{{ $row['permitted_days'] ?? 0 }}</td>
                <td class="center">{{ $row['absent_days'] }}</td>
                <td class="center">{{ $row['total_working_days'] }}</td>
                @if($period_incomplete)
                <td class="center">{{ $row['complete_working_days'] ?? $row['elapsed_working_days'] ?? 0 }}</td>
                @endif
                <td class="center">{{ $row['late_days'] }}</td>
                <td class="center">{{ $row['early_days'] }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sig-table">
        <tr>
            <td class="stamp-cell">
                @if($schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}">
                    <div style="font-size: 9px; color: #777; margin-top: 5px;">SCHOOL OFFICIAL STAMP</div>
                @endif
            </td>
            <td class="sign-cell">
                <div class="school-signature-img">
                    @if($schoolSignBase64)
                        <img src="{{ $schoolSignBase64 }}">
                    @endif
                </div>
                <span class="sig-line"></span>
                <span class="sig-label">HEADMASTER'S SIGNATURE</span>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Techonology &copy; {{ date('Y') }}
    </div>
</body>
</html>
