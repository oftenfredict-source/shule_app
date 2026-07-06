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
        .class-block { margin-bottom: 22px; page-break-inside: avoid; }
        .class-title { font-size: 13px; font-weight: bold; color: #940000; border-bottom: 2px solid #940000; padding-bottom: 5px; margin-bottom: 10px; }
        .stats-grid { width: 100%; margin-bottom: 12px; }
        .stats-grid td { width: 25%; text-align: center; padding: 8px; border: 1px solid #eee; }
        .stat-val { font-size: 15px; font-weight: bold; color: #333; }
        .stat-label { font-size: 9px; color: #777; text-transform: uppercase; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 5px 6px; text-align: left; font-size: 10px; }
        table.data-table th { background-color: #940000; color: #fff; font-weight: bold; }
        table.data-table td.center { text-align: center; }
        .badge-p { color: #198754; font-weight: bold; }
        .badge-a { color: #dc3545; font-weight: bold; }
        .badge-s { color: #fd7e14; font-weight: bold; }
        .badge-e { color: #0dcaf0; font-weight: bold; }
        .no-data { background: #fff3cd; border: 1px solid #ffc107; padding: 8px; margin-bottom: 15px; font-size: 10px; }
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
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">STUDENT ATTENDANCE REPORT</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Period:</td>
                <td>{{ $period_label }}</td>
                <td class="label">Scope:</td>
                <td>{{ $scope_label ?? 'All Classes' }}</td>
            </tr>
            <tr>
                <td class="label">Search Type:</td>
                <td>{{ ucfirst($search_type) }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ $generated_at }}</td>
            </tr>
        </table>
    </div>

    @forelse($attendanceByClass as $classData)
        <div class="class-block">
            <div class="class-title">{{ $classData['display_name'] ?? ($classData['class_name'] . ' ' . $classData['subclass_name']) }}</div>

            @if(empty($classData['has_attendance']))
                <div class="no-data">No attendance collected {{ $period_label }} for this class.</div>
                @continue
            @endif

            <table class="stats-grid">
                <tr>
                    <td>
                        <div class="stat-val" style="color:#198754;">{{ $classData['present'] ?? 0 }}</div>
                        <div class="stat-label">Present @if(in_array($search_type, ['month','year'])) ({{ $classData['present_percentage'] ?? 0 }}%) @endif</div>
                    </td>
                    <td>
                        <div class="stat-val" style="color:#dc3545;">{{ $classData['absent'] ?? 0 }}</div>
                        <div class="stat-label">Absent @if(in_array($search_type, ['month','year'])) ({{ $classData['absent_percentage'] ?? 0 }}%) @endif</div>
                    </td>
                    <td>
                        <div class="stat-val" style="color:#fd7e14;">{{ $classData['sick'] ?? 0 }}</div>
                        <div class="stat-label">Sick @if(in_array($search_type, ['month','year'])) ({{ $classData['sick_percentage'] ?? 0 }}%) @endif</div>
                    </td>
                    <td>
                        <div class="stat-val" style="color:#0dcaf0;">{{ $classData['excused'] ?? 0 }}</div>
                        <div class="stat-label">Permission @if(in_array($search_type, ['month','year'])) ({{ $classData['excused_percentage'] ?? 0 }}%) @endif</div>
                    </td>
                </tr>
            </table>

            @if(!empty($classData['students']) && count($classData['students']) > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th>Student Name</th>
                            <th>Adm No.</th>
                            <th class="center">Gender</th>
                            <th class="center">Present</th>
                            <th class="center">Absent</th>
                            <th class="center">Sick</th>
                            <th class="center">Permission</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classData['students'] as $idx => $student)
                            @php
                                $s = is_array($student) ? (object) $student : $student;
                                $name = trim(($s->first_name ?? '') . ' ' . ($s->middle_name ?? '') . ' ' . ($s->last_name ?? ''));
                            @endphp
                            <tr>
                                <td class="center">{{ $idx + 1 }}</td>
                                <td>{{ $name }}</td>
                                <td>{{ $s->admission_number ?? 'N/A' }}</td>
                                <td class="center">{{ $s->gender ?? 'N/A' }}</td>
                                <td class="center badge-p">{{ $s->days_present ?? 0 }}</td>
                                <td class="center badge-a">{{ $s->days_absent ?? 0 }}</td>
                                <td class="center badge-s">{{ $s->days_sick ?? 0 }}</td>
                                <td class="center badge-e">{{ $s->days_excused ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @empty
        <div class="no-data">No attendance data found for the selected period.</div>
    @endforelse

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
