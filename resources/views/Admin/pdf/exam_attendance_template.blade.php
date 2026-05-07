<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Exam Attendance - {{ $subject->subject_name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; color: #940000; }

        .info-section { margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 20%; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 8px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; text-transform: uppercase; font-size: 10px; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }

        /* ── SIGNATURE AND STAMP ─────────────────── */
        .sig-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 10px; vertical-align: top; }
        .stamp-cell { width: 40%; text-align: center; }
        .stamp-cell img { max-width: 100px; opacity: 0.8; }
        .sign-cell { width: 30%; text-align: center; }
        .sig-line { border-top: 1.5px solid #940000; width: 140px; margin: 10px auto 0; display: block; }
        .sig-label { font-size: 8.5px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase; }

        /* ── WATERMARK ─────────────────── */
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

        .status-present { color: #28a745; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .empty-box { border: 1px solid #ccc; width: 20px; height: 15px; display: inline-block; }
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
                    public_path($path),                             // Standard Laravel Public
                    public_path('uploads/' . $path),                // Standard Laravel Public Uploads
                    $parent . '/public_html/' . $path,              // cPanel / Shared Hosting
                    $parent . '/public_html/uploads/' . $path,      // cPanel / Shared Hosting Uploads
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
        <div style="font-size: 10px;">{{ $school->address ?? '' }}</div>
        <div style="font-size: 10px;">Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">
            {{ isset($with_data) && $with_data ? 'EXAM ATTENDANCE RECORD' : 'EXAM ATTENDANCE COLLECTION SHEET' }}
        </div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Examination:</td>
                <td>{{ $exam->exam_name }}</td>
                <td class="label">Academic Year:</td>
                <td>{{ $year }}</td>
            </tr>
            <tr>
                <td class="label">Term:</td>
                <td>{{ ucfirst(str_replace('_', ' ', $term)) }}</td>
                <td class="label">Subject:</td>
                <td style="font-weight: bold; color: #940000;">{{ $subject->subject_name }}</td>
            </tr>
            <tr>
                <td class="label">Class:</td>
                <td>{{ $class->class_name }} {{ $subclass ? $subclass->subclass_name : '' }}</td>
                <td class="label">Date:</td>
                <td>{{ isset($with_data) && $with_data ? date('d M Y') : '____________________' }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">S/N</th>
                <th>STUDENT NAME</th>
                <th style="width: 40px; text-align: center;">SEX</th>
                <th style="width: 110px; text-align: center;">EXAM NUMBER</th>
                @if(isset($with_data) && $with_data)
                    <th style="width: 80px; text-align: center;">STATUS</th>
                @endif
                @if(!(isset($with_data) && $with_data))
                    <th style="width: 140px; text-align: center;">SIGNATURE</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                <tr @if($loop->even) style="background-color: #fcfcfc;" @endif>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="font-weight: bold;">{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</td>
                    <td style="text-align: center;">{{ substr($student->gender, 0, 1) }}</td>
                    <td style="text-align: center; font-family: monospace; font-size: 10px;">{{ $student->examination_number }}</td>
                    @if(isset($with_data) && $with_data)
                        <td style="text-align: center;">
                            @if(isset($records[$student->studentID]))
                                <span class="{{ $records[$student->studentID]->status === 'Present' ? 'status-present' : 'status-absent' }}">
                                    {{ $records[$student->studentID]->status }}
                                </span>
                            @else
                                <span style="color: #777;">-</span>
                            @endif
                        </td>
                    @endif
                    @if(!(isset($with_data) && $with_data))
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!(isset($with_data) && $with_data))
    <table class="sig-table">
        <tr>
            <td class="stamp-cell" style="width: 50%; text-align: left; padding-left: 50px;">
                @if($schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}" style="max-width: 180px;">
                    <div style="font-size: 8px; color: #777; margin-top: 5px; padding-left: 40px;">OFFICIAL SCHOOL STAMP</div>
                @endif
            </td>
            <td class="sign-cell" style="width: 50%; text-align: right; padding-right: 50px; vertical-align: bottom;">
                <div style="height: 60px; text-align: center; width: 200px; margin-left: auto;">
                    @if($schoolSignBase64)
                        <img src="{{ $schoolSignBase64 }}" style="max-height: 50px;">
                    @endif
                </div>
                <div style="width: 200px; margin-left: auto; text-align: center;">
                    <span class="sig-line" style="width: 180px;"></span>
                    <span class="sig-label">HEADMASTER'S SIGNATURE</span>
                </div>
            </td>
        </tr>
    </table>
    @endif

    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Techonology &copy; {{ date('Y') }}
    </div>
</body>
</html>
