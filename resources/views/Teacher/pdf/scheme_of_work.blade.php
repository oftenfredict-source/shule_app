<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scheme of Work - {{ $scheme->classSubject->subject->subject_name ?? 'Subject' }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        @page { margin: 0.5cm; }
        
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .school-info { font-size: 10px; color: #666; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 8px; background: #f8f9fa; padding: 5px; color: #333; border: 1px solid #eee; text-transform: uppercase; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 6px 4px; text-align: left; word-wrap: break-word; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; color: #000; font-size: 8px; text-transform: uppercase; text-align: center; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; border: none; }
        .info-table td { border: none; padding: 4px 5px; }
        .info-label { font-weight: bold; color: #555; width: 15%; }

        .objectives-section { margin-bottom: 15px; border: 1px solid #000; padding: 10px; background: #fffcfc; }
        .section-title { font-size: 11px; font-weight: bold; color: #000; margin-bottom: 5px; border-bottom: 1px solid #000; padding-bottom: 2px; text-transform: uppercase;}

        .month-header { background-color: #e9ecef; color: #000; font-weight: bold; padding: 6px; text-align: center; font-size: 10px; text-transform: uppercase; border: 1px solid #000; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }

        .watermark {
            position: fixed; top: 40%; left: 0; width: 100%; text-align: center; opacity: 0.12;
            transform: rotate(-45deg); font-size: 110px; font-weight: bold; color: #ccc; z-index: 9999; text-transform: uppercase;
            pointer-events: none;
        }

        .sig-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; border: none; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 50%; text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 120px; max-height: 120px; opacity: 0.85; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 150px; max-height: 40px; }
        .sig-line   { border-top: 1.5px solid #000; width: 160px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 8.5px; color: #000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase;}

        /* Column Widths */
        .col-num    { width: 3.5%; }
        .col-comp   { width: 10.5%; }
        .col-act    { width: 12%; }
        .col-time   { width: 5.5%; }
        .col-period { width: 3.5%; }
        .col-method { width: 10%; }
        .col-resources { width: 10%; }
        .col-tools  { width: 9%; }
        .col-ref    { width: 10%; }
        .col-rem    { width: 6%; }
    </style>
</head>
<body>
    @php
        if (!function_exists('getSmartBase64SOW')) {
            function getSmartBase64SOW($path) {
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
        $schoolLogoBase64  = getSmartBase64SOW($school->school_logo ?? '');
        $schoolStampBase64 = getSmartBase64SOW($school->school_stamp ?? '');
        $schoolSignBase64  = getSmartBase64SOW($school->school_signature ?? '');
    @endphp

    {{-- ── HEADER ─────────────────── --}}
    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 70px; margin-bottom: 8px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
        <div class="school-info">{{ $school->address ?? '' }}</div>
        <div class="school-info">Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">SCHEME OF WORK REPORT - {{ strtoupper($school->school_type ?? 'Secondary') }}</div>
    </div>

    {{-- ── INFO TABLE ─────────────────── --}}
    <table class="info-table">
        <tr>
            <td class="info-label">Subject:</td>
            <td style="width: 35%;">{{ strtoupper($scheme->classSubject->subject->subject_name ?? 'N/A') }}</td>
            <td class="info-label">Academic Year:</td>
            <td>{{ $scheme->year }}</td>
        </tr>
        <tr>
            <td class="info-label">Class:</td>
            <td>
                {{ strtoupper($scheme->classSubject->subclass && $scheme->classSubject->subclass->class ? $scheme->classSubject->subclass->class->class_name . ' ' . $scheme->classSubject->subclass->subclass_name : ($scheme->classSubject->class ? $scheme->classSubject->class->class_name : 'N/A')) }}
            </td>
            <td class="info-label">Teacher:</td>
            <td>{{ strtoupper(($scheme->createdBy->first_name ?? '') . ' ' . ($scheme->createdBy->last_name ?? '')) }}</td>
        </tr>
    </table>

    {{-- ── LEARNING OBJECTIVES ─────────────────── --}}
    @if($scheme->learningObjectives && $scheme->learningObjectives->count() > 0)
        @php
            $schoolType = $school->school_type ?? 'Primary';
            $mainClassName = '';
            if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class) {
                $mainClassName = $scheme->classSubject->subclass->class->class_name;
            } elseif($scheme->classSubject->class) {
                $mainClassName = $scheme->classSubject->class->class_name;
            }
            
            $objectivesIntro = "";
            if($schoolType === 'Primary') {
                $mainClassNameLower = strtolower(trim($mainClassName));
                if(preg_match('/^standard\s*(3|iii|iv|v|vi|4|5|6|7)/i', $mainClassNameLower)) {
                    $objectivesIntro = "The objectives of Primary Education Standard III – VI are to:";
                } else {
                    $objectivesIntro = "The objectives of Primary Education Standard Baby class – standard2 are to:";
                }
            }
        @endphp
        <div class="objectives-section" style="page-break-inside: avoid;">
            <div class="section-title">General Learning Objectives</div>
            @if(!empty($objectivesIntro))
                <div style="font-weight: bold; margin-bottom: 5px; font-size: 10px;">{{ $objectivesIntro }}</div>
            @endif
            <ol style="margin: 0; padding-left: 25px;">
                @foreach($scheme->learningObjectives as $objective)
                    <li style="margin-bottom: 4px; padding-left: 5px; text-align: justify; line-height: 1.5;">
                        {{ $objective->objective_text }}
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    {{-- ── MAIN SCHEME TABLE ─────────────────── --}}
    <table>
        <thead>
            <tr>
                <th class="col-num">S/N</th>
                <th class="col-comp">Main Competence</th>
                <th class="col-comp">Specific Competence</th>
                <th class="col-act">Learning Activities</th>
                <th class="col-act">Specific Activities</th>
                <th class="col-time">Time (Wk)</th>
                <th class="col-period">Prd</th>
                <th class="col-method">T & L Methods</th>
                <th class="col-resources">T & L Resources</th>
                <th class="col-tools">Assessment Tools</th>
                <th class="col-ref">References</th>
                <th class="col-rem">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php $prevMonth = null; $snCount = 1; @endphp
            @foreach($scheme->items as $item)
                @if($prevMonth !== $item->month)
                    <tr>
                        <td colspan="12" class="month-header">{{ strtoupper($item->month) }}</td>
                    </tr>
                    @php $prevMonth = $item->month; @endphp
                @endif
                <tr>
                    <td style="text-align: center;">{{ $snCount++ }}</td>
                    <td>{{ $item->main_competence }}</td>
                    <td>{{ $item->specific_competences }}</td>
                    <td>{{ $item->learning_activities }}</td>
                    <td>{{ $item->specific_activities }}</td>
                    <td style="text-align: center;">Wk {{ $item->week }}</td>
                    <td style="text-align: center;">{{ $item->number_of_periods }}</td>
                    <td>{{ $item->teaching_methods }}</td>
                    <td>{{ $item->teaching_resources }}</td>
                    <td>{{ $item->assessment_tools }}</td>
                    <td>{{ $item->references }}</td>
                    <td>{{ $item->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Technology &copy; {{ date('Y') }}
    </div>

    {{-- ── SIGNATURE AND STAMP ── --}}
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

    <div class="watermark">ShuleXpert</div>
</body>
</html>
