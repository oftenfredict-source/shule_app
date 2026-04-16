<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Character Assessment Class Report - {{ $class->class_name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }

        .info-section { margin-bottom: 15px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 4px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 20%; }

        .summary-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-grid td { width: 25%; text-align: center; padding: 10px; border: 1px solid #eee; }
        .stat-val { font-size: 16px; font-weight: bold; color: #333; }
        .stat-label { font-size: 8px; color: #777; text-transform: uppercase; }

        .section-header { background: #940000; color: white; padding: 6px 10px; font-weight: bold; text-transform: uppercase; margin-top: 20px; margin-bottom: 10px; border-radius: 2px; font-size: 10px; }

        /* Distribution Table (Mock Graph) */
        table.dist-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.dist-table th, table.dist-table td { border: 1px solid #eee; padding: 8px; text-align: left; }
        table.dist-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; }
        
        .progress-container { background: #eee; height: 12px; width: 100px; display: inline-block; vertical-align: middle; margin-right: 5px; }
        .progress-bar { background: #940000; height: 100%; }

        /* Detailed Records Table */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto; }
        table.data-table thead { display: table-header-group; }
        table.data-table tr { page-break-inside: avoid; page-break-after: auto; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 6px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; text-transform: uppercase; font-size: 9px; }

        .student-header { background: #fdfdfd; font-weight: bold; color: #2f2f2f; }
        .assessment-row { font-size: 9px; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }
        .watermark { position: fixed; top: 45%; left: 5%; width: 100%; text-align: center; opacity: 0.05; transform: rotate(-45deg); font-size: 80px; font-weight: bold; color: #000; z-index: -1; text-transform: uppercase; }
        
        .badge { padding: 2px 4px; border-radius: 2px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

    @php
        if (!function_exists('getSmartBase64')) {
            function getSmartBase64($path) {
                if (!$path) return null;
                $path = ltrim($path, '/');
                $fullPath = public_path($path);
                if (@file_exists($fullPath) && is_file($fullPath)) {
                    $data = base64_encode(file_get_contents($fullPath));
                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                    return 'data:image/' . ($ext ?: 'png') . ';base64,' . $data;
                }
                return null;
            }
        }
        $schoolLogoBase64  = getSmartBase64($school->school_logo ?? '');
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div class="report-title">CHARACTER ASSESSMENT CONSOLIDATED REPORT</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Class:</td>
                <td>{{ strtoupper($class->class_name) }}</td>
                <td class="label">Academic Year:</td>
                <td><strong>{{ $timetable->academicYear->year ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Term:</td>
                <td>{{ strtoupper(str_replace('_', ' ', $timetable->term)) }}</td>
                <td class="label">Assessment Period:</td>
                <td>{{ date('d M Y', strtotime($timetable->start_date)) }} - {{ date('d M Y', strtotime($timetable->end_date)) }}</td>
            </tr>
        </table>
    </div>

    @php
        $total = $students->count();
        $assessedCount = $students->filter(fn($s) => $s->characterAssessments->count() > 0)->count();
        $pendingCount = $total - $assessedCount;
    @endphp

    <table class="summary-grid">
        <tr>
            <td>
                <div class="stat-val">{{ $total }}</div>
                <div class="stat-label">Total Enrollment</div>
            </td>
            <td>
                <div class="stat-val" style="color: #28a745;">{{ $assessedCount }}</div>
                <div class="stat-label">Assessed</div>
            </td>
            <td>
                <div class="stat-val" style="color: #dc3545;">{{ $pendingCount }}</div>
                <div class="stat-label">Pending</div>
            </td>
            <td>
                <div class="stat-val">{{ $total > 0 ? round(($assessedCount / $total) * 100, 1) : 0 }}%</div>
                <div class="stat-label">Participation Rate</div>
            </td>
        </tr>
    </table>

    <div class="section-header">Performance Distribution (Analytical Graphs)</div>
    <table class="dist-table">
        <thead>
            <tr>
                <th style="width: 30%;">Behavior Category</th>
                <th>Choice Distribution (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $row)
                <tr>
                    <td><strong>{{ $row['name'] }}</strong></td>
                    <td>
                        @foreach($row['choices'] as $ch)
                            <div style="margin-bottom: 5px; font-size: 8px;">
                                <span style="display:inline-block; width: 70px;">{{ $ch['name'] }}:</span>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: {{ $ch['percent'] }}%;"></div>
                                </div>
                                <span style="font-weight:bold;">{{ $ch['percent'] }}%</span>
                            </div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-header">Detailed Student-by-Student Character Profiles</div>
    
    @foreach($students->chunk(15) as $chunk)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Student Information</th>
                    <th style="width: 25%;">Category</th>
                    <th style="width: 20%;">Rating</th>
                    <th style="width: 30%;">Detailed Observations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $student)
                    @php
                        $ass = $student->characterAssessments;
                        $first = true;
                    @endphp
                    @if($ass->isEmpty())
                        <tr class="student-header">
                            <td colspan="4">
                                <span style="color: #dc3545;">[PENDING]</span> 
                                {{ strtoupper($student->first_name . ' ' . $student->last_name) }} ({{ $student->admission_number }})
                            </td>
                        </tr>
                    @else
                        @foreach($ass as $item)
                            <tr class="assessment-row">
                                @if($first)
                                    <td rowspan="{{ $ass->count() }}" class="student-header" style="vertical-align: top;">
                                        <strong>{{ strtoupper($student->first_name . ' ' . $student->last_name) }}</strong><br>
                                        <small>{{ $student->admission_number }} | {{ $student->gender }}</small>
                                    </td>
                                    @php $first = false; @endphp
                                @endif
                                <td>{{ $item->category->name }}</td>
                                <td style="font-weight: bold; color: #940000;">{{ $item->choice->choice_name ?? 'N/A' }}</td>
                                <td style="color: #666; font-style: italic;">{{ $item->description ?? '-' }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        Generated by ShuleXpert School Management System &copy; {{ date('Y') }} | Page 1 of 1
    </div>
</body>
</html>
