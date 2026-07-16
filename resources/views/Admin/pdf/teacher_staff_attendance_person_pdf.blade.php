<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} - {{ $person['name'] ?? '' }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }
        .info-section { margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 25%; }
        .person-card { border: 1px solid #ddd; padding: 12px; margin-bottom: 15px; border-radius: 5px; }
        .person-name { font-size: 14px; font-weight: bold; color: #940000; }
        .person-meta { font-size: 10px; color: #666; margin-top: 4px; }
        .stats-grid { width: 100%; margin-bottom: 15px; }
        .stats-grid td { width: 20%; text-align: center; padding: 10px; border: 1px solid #eee; }
        .stat-val { font-size: 16px; font-weight: bold; color: #333; }
        .stat-label { font-size: 9px; color: #777; text-transform: uppercase; }
        .stat-late .stat-val { color: #dc3545; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 6px; text-align: left; }
        table.data-table th { background-color: #940000; color: #fff; font-weight: bold; }
        table.data-table td.center { text-align: center; }
        .row-late { background-color: #fff5f5; }
        .row-weekend { background-color: #f3e8ff; }
        .row-nonwork { background-color: #fff8e1; }
        .status-present { color: #28a745; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-late { color: #dc3545; font-weight: bold; }
        .status-early { color: #0dcaf0; font-weight: bold; }
        .status-off { color: #6c757d; font-weight: bold; }
        .status-permitted { color: #6f42c1; font-weight: bold; }
        .status-weekend { color: #6f42c1; font-weight: bold; }
        .status-nonwork { color: #856404; font-weight: bold; }
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
        $personImg = null;
        if (!empty($person['image_file'])) {
            $personImg = getSmartBase64('userImages/' . $person['image_file']);
        }
        $s = $statistics ?? [];
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
                <td>{{ $work_start_time }} – {{ $work_end_time }}</td>
                <td class="label">Employee No:</td>
                <td>{{ $person['employee_number'] ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="person-card">
        <table style="width:100%; border:none;">
            <tr>
                @if($personImg)
                <td style="width:70px; border:none; vertical-align:top;">
                    <img src="{{ $personImg }}" style="width:60px; height:60px; border-radius:50%; object-fit:cover;">
                </td>
                @endif
                <td style="border:none; vertical-align:top;">
                    <div class="person-name">{{ $person['name'] ?? 'N/A' }}</div>
                    <div class="person-meta">
                        {{ ucfirst($person['person_type'] ?? '') }} &bull; {{ $person['role_label'] ?? '' }}
                        @if(!empty($person['phone_number']))
                            &bull; Phone: {{ $person['phone_number'] }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-val">{{ $s['present_days'] ?? 0 }}</div>
                <div class="stat-label">Present</div>
            </td>
            <td>
                <div class="stat-val">{{ $s['permitted_days'] ?? 0 }}</div>
                <div class="stat-label">Permitted</div>
            </td>
            <td>
                <div class="stat-val">{{ $s['absent_days'] ?? 0 }}</div>
                <div class="stat-label">Absent</div>
            </td>
            <td>
                <div class="stat-val">{{ $s['total_working_days'] ?? 0 }}</div>
                <div class="stat-label">Working Days</div>
            </td>
            @if(!empty($period_incomplete))
            <td>
                <div class="stat-val">{{ $s['complete_working_days'] ?? $s['elapsed_working_days'] ?? 0 }}</div>
                <div class="stat-label">Complete Working Days</div>
            </td>
            @endif
            <td class="stat-late">
                <div class="stat-val">{{ $s['late_days'] ?? 0 }}</div>
                <div class="stat-label">Late</div>
            </td>
            <td>
                <div class="stat-val">{{ $s['early_days'] ?? 0 }}</div>
                <div class="stat-label">Early</div>
            </td>
        </tr>
    </table>

    @if(!empty($period_incomplete))
    <p style="font-size:9px; color:#666; margin-bottom:12px;">
        Absent = complete working days without fingerprint record in database (no scan = absent).
    </p>
    @endif

    <h4 style="color:#940000; border-bottom:1px solid #940000; padding-bottom:5px;">Daily Attendance</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th class="center">Check In</th>
                <th class="center">Check Out</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daily_records as $day)
            @php
                $status = $day['status'] ?? '';
                $statusClass = match($status) {
                    'Permitted' => 'status-permitted',
                    'Absent' => 'status-absent',
                    'Late' => 'status-late',
                    'Early' => 'status-early',
                    'Present (Off Day)' => 'status-off',
                    'Weekend' => 'status-weekend',
                    'Not a working day' => 'status-nonwork',
                    default => 'status-present',
                };
                $rowClass = !empty($day['is_late']) ? 'row-late' : (
                    ($status === 'Weekend' || ($day['day_kind'] ?? '') === 'weekend') ? 'row-weekend' : (
                        ($status === 'Not a working day' || ($day['day_kind'] ?? '') === 'non_working') ? 'row-nonwork' : ''
                    )
                );
                $statusNote = $day['status_note'] ?? (
                    $status === 'Weekend' ? 'Ni weekend' : (
                        $status === 'Not a working day' ? 'Sio siku ya kazi' : null
                    )
                );
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $day['date_label'] ?? $day['date'] ?? '' }}</td>
                <td class="center">{{ $day['check_in_time'] ?? '-' }}</td>
                <td class="center">{{ $day['check_out_time'] ?? '-' }}</td>
                <td class="center {{ $statusClass }}">
                    {{ $status ?: '-' }}
                    @if($statusNote)
                        <br><span style="font-size:8px;font-weight:normal;">{{ $statusNote }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="center">No attendance records for this period.</td>
            </tr>
            @endforelse
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
