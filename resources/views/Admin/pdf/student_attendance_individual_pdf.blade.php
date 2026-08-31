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
        .student-name { font-size: 15px; font-weight: bold; color: #940000; margin-bottom: 8px; }
        .stats-grid { width: 100%; margin-bottom: 15px; }
        .stats-grid td { width: 25%; text-align: center; padding: 10px; border: 1px solid #eee; }
        .stat-val { font-size: 16px; font-weight: bold; color: #333; }
        .stat-label { font-size: 9px; color: #777; text-transform: uppercase; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 6px; text-align: left; }
        table.data-table th { background-color: #940000; color: #fff; font-weight: bold; }
        table.data-table td.center { text-align: center; }
        .status-present { color: #198754; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-sick { color: #fd7e14; font-weight: bold; }
        .status-excused { color: #0dcaf0; font-weight: bold; }
        .status-late { color: #6f42c1; font-weight: bold; }
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
        $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">INDIVIDUAL STUDENT ATTENDANCE REPORT</div>
    </div>

    <div class="student-name">{{ $studentName }}</div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Admission No:</td>
                <td>{{ $student->admission_number ?? 'N/A' }}</td>
                <td class="label">Gender:</td>
                <td>{{ $student->gender ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Period:</td>
                <td>{{ $period_label }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ $generated_at }}</td>
            </tr>
        </table>
    </div>

    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-val" style="color:#198754;">{{ $stats['present'] ?? 0 }}</div>
                <div class="stat-label">Present</div>
            </td>
            <td>
                <div class="stat-val" style="color:#dc3545;">{{ $stats['absent'] ?? 0 }}</div>
                <div class="stat-label">Absent</div>
            </td>
            <td>
                <div class="stat-val" style="color:#fd7e14;">{{ $stats['sick'] ?? 0 }}</div>
                <div class="stat-label">Sick</div>
            </td>
            <td>
                <div class="stat-val" style="color:#0dcaf0;">{{ $stats['excused'] ?? 0 }}</div>
                <div class="stat-label">Permission</div>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 8px; font-weight: bold; color: #940000;">Attendance History ({{ $stats['total_days'] ?? 0 }} records)</p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>Date</th>
                <th class="center">Status</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $idx => $att)
                @php
                    $statusClass = match($att->status ?? '') {
                        'Present' => 'status-present',
                        'Absent' => 'status-absent',
                        'Sick' => 'status-sick',
                        'Excused' => 'status-excused',
                        'Late' => 'status-late',
                        default => '',
                    };
                    $dateStr = $att->attendance_date
                        ? \Carbon\Carbon::parse($att->attendance_date)->format('d M Y')
                        : '-';
                @endphp
                <tr>
                    <td class="center">{{ $idx + 1 }}</td>
                    <td>{{ $dateStr }}</td>
                    <td class="center {{ $statusClass }}">{{ $att->status ?? '-' }}</td>
                    <td>{{ $att->remark ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#777;">No attendance records for this period.</td>
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
