<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Character Assessment - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }

        .info-section { margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 20%; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 8px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; text-transform: uppercase; font-size: 10px; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }

        .sig-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 50%; text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 120px; max-height: 120px; opacity: 0.85; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .sig-line { border-top: 1.5px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label { font-size: 8.5px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase;}

        .watermark {
            position: fixed;
            top: 45%;
            left: 5%;
            width: 100%;
            text-align: center;
            opacity: 0.05;
            transform: rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: #000;
            z-index: -1;
            text-transform: uppercase;
        }
        .badge { padding: 4px 8px; font-size: 9px; border-radius: 3px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
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
        <div class="report-title">STUDENT CHARACTER ASSESSMENT REPORT</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Student Name:</td>
                <td><strong>{{ strtoupper($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name) }}</strong></td>
                <td class="label">Admission No:</td>
                <td>{{ $student->admission_number }}</td>
            </tr>
            <tr>
                <td class="label">Class:</td>
                <td>{{ $student->subclass->class->class_name ?? 'N/A' }} {{ $student->subclass->subclass_name ?? '' }}</td>
                <td class="label">Term:</td>
                <td>{{ ucfirst(str_replace('_', ' ', $timetable->term)) }} ({{ $timetable->academicYear->year ?? 'N/A' }})</td>
            </tr>
            <tr>
                <td class="label">Assessment Period:</td>
                <td>{{ date('d M Y', strtotime($timetable->start_date)) }} - {{ date('d M Y', strtotime($timetable->end_date)) }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Behavior Category / Sector</th>
                <th style="width: 20%;">Rating / Choice</th>
                <th style="width: 40%;">Detailed Observations / Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assessments as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item->category->name }}</strong></td>
                    <td>
                        @if($item->choice)
                            <span style="color: #940000; font-weight: bold;">{{ $item->choice->choice_name }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>{{ $item->description ?? 'No additional comments provided.' }}</td>
                </tr>
            @endforeach
            @if($assessments->isEmpty())
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: #888;">No assessment records found for this period.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="sig-table">
        <tr>
            <td class="stamp-cell">
                @if($schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}">
                    <div style="font-size: 8px; color: #777; margin-top: 5px;">OFFICIAL SCHOOL STAMP</div>
                @endif
            </td>
            <td class="sign-cell">
                <div style="height: 40px;">
                    @if($schoolSignBase64)
                        <img src="{{ $schoolSignBase64 }}" style="max-height: 40px;">
                    @endif
                </div>
                <span class="sig-line"></span>
                <span class="sig-label">HEADMASTER'S SIGNATURE</span>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by ShuleXpert School Management System &copy; {{ date('Y') }}
    </div>
</body>
</html>
