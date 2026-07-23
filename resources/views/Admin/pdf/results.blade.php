<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Results - {{ $school->school_name ?? 'School' }}</title>
    @php
        /**
         * Smart Image Resolver for DomPDF
         * Checks multiple possible server locations (cPanel vs Local)
         */
        if (!function_exists('getSmartBase64')) {
            function getSmartBase64($path) {
                if (!$path) return null;
                
                // Accept absolute URL, /path, or plain filename (e.g. "photo.jpg")
                $originalPath = trim((string) $path);
                $urlPath = parse_url($originalPath, PHP_URL_PATH);
                $path = ltrim($urlPath ?: $originalPath, '/');
                $base = base_path();
                $parent = dirname($base);
                $filename = basename($path);
                
                $possibilities = [
                    public_path($path),                             // Standard Laravel Public
                    public_path('userImages/' . $filename),        // Student photos folder
                    public_path('uploads/' . $path),                // Standard Laravel Public Uploads
                    $parent . '/public_html/' . $path,              // cPanel / Shared Hosting
                    $parent . '/public_html/userImages/' . $filename, // cPanel student photos
                    $parent . '/public_html/uploads/' . $path,      // cPanel / Shared Hosting Uploads
                ];
                
                if (isset($_SERVER['DOCUMENT_ROOT'])) {
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                    $possibilities[] = $docRoot . '/' . $path;
                    $possibilities[] = $docRoot . '/userImages/' . $filename;
                    $possibilities[] = $docRoot . '/uploads/' . $path;
                }
                
                $possibilities[] = base_path($path);
                $possibilities[] = base_path('public/userImages/' . $filename);
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

        if (!function_exists('pdfIsALevelClassName')) {
            function pdfIsALevelClassName(?string $className): bool {
                if (!$className) {
                    return false;
                }
                $normalized = strtolower(preg_replace('/[\s\-]+/', '_', trim($className)));
                return in_array($normalized, ['form_five', 'form_six', 'form_5', 'form_6'], true)
                    || str_contains($normalized, 'form_five')
                    || str_contains($normalized, 'form_six');
            }
        }

        if (!function_exists('pdfSecondaryGradeKeys')) {
            function pdfSecondaryGradeKeys(bool $isALevel): array {
                return $isALevel
                    ? ['A', 'B', 'C', 'D', 'E', 'S', 'F']
                    : ['A', 'B', 'C', 'D', 'F'];
            }
        }

        if (!function_exists('pdfSecondaryPointMap')) {
            function pdfSecondaryPointMap(bool $isALevel): array {
                return $isALevel
                    ? ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'S' => 6, 'F' => 7]
                    : ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'F' => 5];
            }
        }

        if (!function_exists('getSubjectRemarkInfo')) {
            function getSubjectRemarkInfo($grade, $isSecondary, $isALevel = false) {
                $normalizedGrade = strtoupper(trim((string) ($grade ?? '')));
                if ($grade === null || $grade === '' || $normalizedGrade === 'N/A' || $normalizedGrade === 'INCOMPLETE' || $normalizedGrade === '-') {
                    return ['text' => 'Incomplete', 'class' => 'remark-inc'];
                }

                if ($isSecondary) {
                    if ($isALevel) {
                        switch ($normalizedGrade) {
                            case 'A':
                                return ['text' => 'Excellent', 'class' => 'remark-excellent'];
                            case 'B':
                                return ['text' => 'Very Good', 'class' => 'remark-pass'];
                            case 'C':
                                return ['text' => 'Good', 'class' => 'remark-good'];
                            case 'D':
                                return ['text' => 'Average', 'class' => 'remark-avg'];
                            case 'E':
                                return ['text' => 'Satisfactory', 'class' => 'remark-avg'];
                            case 'S':
                                return ['text' => 'Subsidiary', 'class' => 'remark-avg'];
                            case 'F':
                                return ['text' => 'Fail', 'class' => 'remark-fail'];
                        }
                    } else {
                        switch ($normalizedGrade) {
                            case 'A':
                                return ['text' => 'Excellent', 'class' => 'remark-excellent'];
                            case 'B':
                                return ['text' => 'Very Good', 'class' => 'remark-pass'];
                            case 'C':
                                return ['text' => 'Good', 'class' => 'remark-good'];
                            case 'D':
                                return ['text' => 'Satisfactory', 'class' => 'remark-avg'];
                            case 'F':
                                return ['text' => 'Fail', 'class' => 'remark-fail'];
                        }
                    }
                    if (preg_match('/^(I|II|III|IV)\./', $normalizedGrade)) {
                        return ['text' => 'Pass', 'class' => 'remark-pass'];
                    }
                    if (preg_match('/^(0|FAIL)/', $normalizedGrade)) {
                        return ['text' => 'Fail', 'class' => 'remark-fail'];
                    }
                    return ['text' => 'Fail', 'class' => 'remark-fail'];
                }

                if (in_array($normalizedGrade, ['A', 'B', 'C', 'D'])) {
                    return ['text' => 'Pass', 'class' => 'remark-pass'];
                }
                if (in_array($normalizedGrade, ['E', 'F'])) {
                    return ['text' => 'Fail', 'class' => 'remark-fail'];
                }

                return ['text' => 'Fail', 'class' => 'remark-fail'];
            }
        }

        if (!function_exists('getAutomaticComment')) {
            function getAutomaticComment($gradeOrDivision) {
                $gradeOrDivision = strtoupper(trim($gradeOrDivision));
                
                // Secondary School (Division I, II, III, IV, 0)
                if (in_array($gradeOrDivision, ['I', 'DIV I', 'DIVISION I'])) return 'Excellent performance, continue with the same efforts.';
                if (in_array($gradeOrDivision, ['II', 'DIV II', 'DIVISION II'])) return 'Very good performance, keep working hard to reach division one.';
                if (in_array($gradeOrDivision, ['III', 'DIV III', 'DIVISION III'])) return 'Good performance, but more effort is needed to improve.';
                if (in_array($gradeOrDivision, ['IV', 'DIV IV', 'DIVISION IV'])) return 'Average performance, you need to increase your efforts significantly.';
                if (in_array($gradeOrDivision, ['0', 'DIV 0', 'DIVISION 0', 'FAIL'])) return 'Poor performance, you must seek academic advice and work much harder.';
                
                // Primary School (Grade A, B, C, D, E)
                if ($gradeOrDivision === 'A') return 'Excellent performance, keep up the spirit.';
                if ($gradeOrDivision === 'B') return 'Very good performance, work harder for better results.';
                if ($gradeOrDivision === 'C') return 'Good performance, more effort is required.';
                if ($gradeOrDivision === 'D') return 'Average ufaulu, you need to put more focus on your studies.';
                if ($gradeOrDivision === 'E' || $gradeOrDivision === 'F') return 'Poor performance, urgent intervention and more study time needed.';
                
                return 'Good effort, keep working hard to improve your results.';
            }
        }
    @endphp
    <style>
        @page { margin: 12mm 10mm 16mm 10mm; }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10.5px;
            color: #333;
            line-height: 1.45;
            background: #fff;
            margin: 0;
            padding: 0 0 28px 0;
        }
        table, td, th, p, div, span, li {
            font-family: 'Helvetica', sans-serif;
            color: #333;
        }

        /* Fee-structure style header (logo centred on top — not left/right) */
        .header { text-align: center; margin-bottom: 12px; border-bottom: 2.5px solid #940000; padding-bottom: 10px; }
        .header img.school-logo-top { display: block; max-height: 60px; margin: 0 auto 5px auto; border: none; outline: none; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .header-table td { border: none; padding: 0; vertical-align: middle; }
        .logo-cell { width: 72px; padding-right: 8px; text-align: left; }
        .logo-cell img { display: block; max-height: 60px; max-width: 68px; border: none; outline: none; }
        .student-photo-cell { width: 72px; text-align: right; padding-left: 8px; }
        .student-photo-cell img { display: block; width: 68px; height: 68px; margin-left: auto; border: none; outline: none; object-fit: cover; }
        .school-name-cell { text-align: center; }
        .school-name { font-size: 17px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .school-sub  { font-size: 10px; color: #555; margin: 1px 0; }

        .report-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
            background: #f8f9fa;
            padding: 7px 8px;
            border: 1.5px solid #ccc;
            text-align: center;
            text-transform: uppercase;
            color: #333;
        }

        .info-section { margin-bottom: 10px; width: 100%; }
        .info-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .info-table td { padding: 6px 8px; border: 1px solid #ccc; width: 25%; vertical-align: middle; font-size: 10px; }
        .info-table .label { font-weight: bold; color: #444; background: #f8f9fa; }

        .section-title {
            font-size: 11.5px;
            font-weight: bold;
            color: #940000;
            border-bottom: 1.5px solid #940000;
            padding-bottom: 4px;
            margin-top: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        /* Keep old class names as aliases (fee-structure look) */
        .title-band { /* replaced visually by .report-title in markup */ }
        .meta-bar, .student-info, .result-bar {
            background: transparent;
            border: none;
            padding: 0;
            margin: 0 0 8px 0;
            font-size: 10.5px;
            color: #333;
        }
        .s-label { color: #444; font-weight: bold; }

        .group-head {
            background: #f0f0f0;
            color: #940000;
            font-size: 11px;
            font-weight: bold;
            padding: 6px 8px;
            margin: 12px 0 0 0;
            border: 1px solid #ccc;
            text-transform: uppercase;
        }
        .sub-head {
            background: #fcfcfc;
            color: #444;
            font-size: 10.5px;
            font-weight: bold;
            border-left: 2.5px solid #940000;
            padding: 4px 7px;
            margin: 6px 0 3px 0;
        }

        table.data, table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
            border: 1.5px solid #ccc;
            font-size: 10.5px;
        }
        table.data th, table.data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #940000;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 9.5px;
            text-transform: uppercase;
            padding: 6px 5px;
        }
        table.data thead tr { background-color: #f0f0f0; color: #940000; }
        table.data thead th { color: #940000 !important; }
        table.data td, table.data-table td {
            border: 1px solid #ccc;
            padding: 5px 6px;
            text-align: left;
            vertical-align: middle;
            font-size: 10.5px;
        }
        table.data tbody tr.even { background-color: #fcfcfc; }
        .tc  { text-align: center; }
        .tr  { text-align: right; }
        .bold { font-weight: bold; }
        table.data th.subj-group,
        table.data td.subj-name { font-size: 9px; }
        table.data td.subj-marks,
        table.data td.subj-grade { text-align: center; font-size: 10px; font-weight: bold; }
        table.data td.student-span { vertical-align: middle; }

        /* Excel-style subject board (horizontal Marks|Grade per subject) */
        table.data-board { width: 100%; border-collapse: collapse; table-layout: fixed; border: 1.5px solid #ccc; font-size: 7px; margin-bottom: 10px; }
        table.data-board th, table.data-board td {
            border: 1px solid #ccc;
            padding: 2px 1px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.data-board th {
            background: #f0f0f0;
            color: #940000;
            font-weight: bold;
            font-size: 6.5px;
            text-transform: uppercase;
        }
        table.data-board th.subj-head { font-size: 6px; line-height: 1.1; }
        table.data-board td.name-col { text-align: left; padding-left: 3px; font-size: 7px; font-weight: bold; }
        table.data-board tr.even { background: #fcfcfc; }
        table.data-board td.inc-cell { color: #888; }

        .badge-red, .badge-pale, .badge-grn {
            background: #f8f9fa;
            color: #333;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #ccc;
        }
        .badge-red { color: #940000; border-color: #940000; }
        .badge-grn { color: #155724; border-color: #c3e6cb; background: #d4edda; }

        .teacher-comment-box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 14px;
            margin-bottom: 10px;
            background: #fff;
        }
        .comment-title {
            font-weight: bold;
            color: #940000;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 10.5px;
        }
        .comment-text { font-style: italic; font-size: 10.5px; min-height: 15px; border-bottom: 1px dotted #ccc; padding-bottom: 3px; }
        .teacher-sig-area { margin-top: 5px; }
        .teacher-sig-img { height: 35px; margin-bottom: 3px; }
        .teacher-sig-img img { max-width: 140px; max-height: 35px; }
        .teacher-name { font-weight: bold; font-size: 10px; border-top: 1px solid #000; width: 150px; padding-top: 2px; }

        .sig-table { width: 100%; margin-top: 28px; border-collapse: collapse; page-break-inside: avoid; table-layout: fixed; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; width: 50%; }
        .stamp-cell { text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 110px; max-height: 110px; opacity: 0.85; }
        .sign-cell { text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 150px; max-height: 40px; }
        .sig-line   { border-top: 2px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 8.5px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0; right: 0;
            width: 100%;
            text-align: center;
            font-size: 8.5px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            background: #fff;
        }
        .footer .brand { color: #940000; font-weight: bold; }
        .placeholder-img {
            border: 1px dashed #ccc;
            background: #fdfdfd;
            color: #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 9px;
            overflow: hidden;
        }

        .remark-color-legend {
            text-align: center;
            margin: 8px 0 12px 0;
            width: 100%;
        }
        .remark-color-legend-title {
            font-weight: bold;
            color: #666;
            font-size: 9.5px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .remark-color-blocks {
            display: table;
            width: 100%;
            table-layout: fixed;
            border: 1px solid #ccc;
            overflow: hidden;
        }
        .remark-color-block {
            display: table-cell;
            width: 33.3333%;
            box-sizing: border-box;
            padding: 5px 6px;
            font-size: 9.5px;
            font-weight: bold;
            text-align: center;
        }
        .remark-color-blocks.secondary-legend .remark-color-block {
            width: 16.6666%;
            font-size: 8.5px;
            padding: 4px 2px;
        }
        /* Soft remark chips — readable, not loud layout fills */
        .remark-excellent { background-color: #e8f5e9; color: #1b5e20; }
        .remark-pass { background-color: #f1f8e9; color: #33691e; }
        .remark-good { background-color: #fff8e1; color: #6d4c00; }
        .remark-avg { background-color: #fff3e0; color: #e65100; }
        .remark-fail { background-color: #fce4ec; color: #880e4f; }
        .remark-inc { background-color: #e3f2fd; color: #0d47a1; }
        table.data td.remark-cell { text-align: center; font-weight: bold; font-size: 9.5px; }

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
            pointer-events: none;
        }
        .header-line { display: none; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

{{-- ====================================================
     SINGLE STUDENT OR BULK SINGLE STUDENT
==================================================== --}}
@if(($option === 'single' || $option === 'bulk_single') && $students->count() > 0)
    @php
        $studentsToIterate = ($option === 'single') ? $students->take(1) : $students;
        $idxCount = 0;
    @endphp
    @foreach($studentsToIterate as $student)
        @php 
            $result = $resultsData[$student->studentID] ?? null; 
            if ($option === 'bulk_single' && isset($detailedBulkData[$student->studentID])) {
                $detailedSingleData = $detailedBulkData[$student->studentID];
            }
            $studentPhotoBase64 = getSmartBase64($student->photo ?? '');
            if (!$studentPhotoBase64) {
                $studentPhotoBase64 = getSmartBase64(($student->gender ?? '') === 'Female' ? 'images/female.png' : 'images/male.png');
            }
        @endphp

        {{-- HEADER (Printed for each student) — fee-structure style --}}
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @if($schoolLogoBase64)
                            <img src="{{ $schoolLogoBase64 }}" alt="Logo">
                        @endif
                    </td>
                    <td class="school-name-cell">
                        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
                        @if($school)
                            <div class="school-sub">{{ $school->address ?? '' }}</div>
                            <div class="school-sub">Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
                        @endif
                    </td>
                    <td class="student-photo-cell">
                        @if($studentPhotoBase64)
                            <img src="{{ $studentPhotoBase64 }}" alt="Student Photo">
                        @endif
                    </td>
                </tr>
            </table>
            <div class="report-title">
                {{ strtoupper(trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name)) }}
                — {{ $title ?? ($filters['type'] === 'report' ? 'Term Report' : 'Exam Results') }}
            </div>
        </div>

        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td class="label">Term:</td>
                    <td>{{ $filters['term'] ? ucwords(str_replace('_', ' ', $filters['term'])) : 'All Terms' }}</td>
                    <td class="label">Year:</td>
                    <td>{{ $filters['year'] }}</td>
                </tr>
                <tr>
                    <td class="label">Type:</td>
                    <td>
                        {{ $filters['type'] === 'exam' ? 'Exam Results' : 'Term Report' }}
                        @if($filters['examID'])
                            @php $exam = \App\Models\Examination::find($filters['examID']); @endphp
                            @if($exam) — {{ $exam->exam_name }} @endif
                        @endif
                    </td>
                    <td class="label">Generated:</td>
                    <td>{{ date('d M Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Admission No:</td>
                    <td>{{ $student->admission_number ?? 'N/A' }}</td>
                    <td class="label">Class:</td>
                    <td>
                        @php
                            $pdfClassLabel = 'N/A';
                            if ($student->subclass && $student->subclass->class) {
                                $pdfCn = $student->subclass->class->class_name ?? '';
                                $pdfSn = trim($student->subclass->subclass_name ?? '');
                                $pdfClassLabel = pdfIsALevelClassName($pdfCn)
                                    ? ($pdfSn !== '' ? $pdfSn : $pdfCn)
                                    : ($pdfSn !== '' ? $pdfCn . ' / ' . $pdfSn : $pdfCn);
                            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                                $pdfCn = $student->oldSubclass->class->class_name ?? '';
                                $pdfSn = trim($student->oldSubclass->subclass_name ?? '');
                                $pdfClassLabel = pdfIsALevelClassName($pdfCn)
                                    ? ($pdfSn !== '' ? $pdfSn : $pdfCn)
                                    : ($pdfSn !== '' ? $pdfCn . ' / ' . $pdfSn : $pdfCn);
                            }
                        @endphp
                        {{ $pdfClassLabel }}
                        &nbsp;|&nbsp; {{ $student->gender ?? '' }}
                    </td>
                </tr>
            </table>
        </div>

    @if($result)
        @if(isset($detailedSingleData) && isset($detailedSingleData['subjects']) && count($detailedSingleData['subjects']) > 0)
            @php
                $isSecondaryPdf = strtolower($schoolType ?? 'Secondary') === 'secondary';
                $pdfStudentClassName = $student->subclass->class->class_name
                    ?? ($student->oldSubclass->class->class_name ?? '');
                $isALevelPdf = $isSecondaryPdf && pdfIsALevelClassName($pdfStudentClassName);
            @endphp
            <div class="remark-color-legend">
                <div class="remark-color-legend-title">Remark Color Definition</div>
                @if($isSecondaryPdf)
                <div class="remark-color-blocks secondary-legend">
                    <span class="remark-color-block remark-excellent">A - Excellent</span>
                    <span class="remark-color-block remark-pass">B - Very Good</span>
                    <span class="remark-color-block remark-good">C - Good</span>
                    <span class="remark-color-block remark-avg">D - {{ $isALevelPdf ? 'Average' : 'Satisfactory' }}</span>
                    @if($isALevelPdf)
                        <span class="remark-color-block remark-avg">E - Satisfactory</span>
                        <span class="remark-color-block remark-avg">S - Subsidiary</span>
                    @endif
                    <span class="remark-color-block remark-fail">F - Fail</span>
                    <span class="remark-color-block remark-inc">Incomplete</span>
                </div>
                @else
                <div class="remark-color-blocks">
                    <span class="remark-color-block remark-pass">Pass</span>
                    <span class="remark-color-block remark-fail">Fail</span>
                    <span class="remark-color-block remark-inc">Incomplete</span>
                </div>
                @endif
            </div>
            <div class="section-title">Detailed Performance Breakdown</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>Subject</th>
                        @php 
                            // Determine extra columns globally across all subjects
                            $hasBreakdown = false;
                            $examHeaders = [];
                            foreach ($detailedSingleData['subjects'] as $s) {
                                if (isset($s['exams']) && is_array($s['exams']) && count($s['exams']) > 0) {
                                    $hasBreakdown = true;
                                    foreach ($s['exams'] as $ex) {
                                        $eName = $ex['exam_name'] ?? 'Exam';
                                        if (!in_array($eName, $examHeaders)) {
                                            $examHeaders[] = $eName;
                                        }
                                    }
                                }
                            }
                            // Sort headers to maintain consistency if needed (usually already sorted by start_date)
                        @endphp
                        
                        @if($hasBreakdown)
                            @foreach($examHeaders as $h)
                                <th class="tc">{{ $h }}</th>
                            @endforeach
                        @endif
                        <th class="tc">Average</th>
                        <th class="tc">{{ $isSecondaryPdf ? 'Division' : 'Grade' }}</th>
                        <th class="tc">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailedSingleData['subjects'] as $i => $subj)
                        @php
                            $isIncompleteSubj = !empty($subj['is_incomplete']) || strtoupper(trim((string)($subj['grade'] ?? ''))) === 'INCOMPLETE';
                            $subjGrade = $isIncompleteSubj ? 'Incomplete' : ($subj['grade'] ?? 'N/A');
                            $remarkInfo = getSubjectRemarkInfo($subjGrade, $isSecondaryPdf, $isALevelPdf);
                        @endphp
                        <tr class="{{ $i % 2 == 0 ? '' : 'even' }}">
                            <td>
                                {{ $subj['subject_name'] }}
                                @if(!empty($subj['is_duo']) && !empty($subj['papers']))
                                    <div style="font-size:11px; color:#555; margin-top:2px;">
                                        @foreach($subj['papers'] as $paper)
                                            @php
                                                $pm = isset($paper['marks']) && $paper['marks'] !== null && $paper['marks'] !== ''
                                                    ? number_format((float)$paper['marks'], 0) . '/' . ($paper['max_marks'] ?? '')
                                                    : '—';
                                            @endphp
                                            <div>{{ $paper['component_name'] ?? 'Paper' }}: {{ $pm }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            @if($hasBreakdown)
                                @foreach($examHeaders as $hName)
                                    @php
                                        // Find the corresponding exam result dynamically by name
                                        $exResult = null;
                                        if (isset($subj['exams']) && is_array($subj['exams'])) {
                                            foreach($subj['exams'] as $ex) {
                                                if (($ex['exam_name'] ?? 'Exam') === $hName) {
                                                    $exResult = $ex;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <td class="tc">
                                        @if($exResult && isset($exResult['marks']))
                                            {{ number_format((float)$exResult['marks'], 0) }} 
                                            @if(isset($exResult['grade']))
                                                <small class="badge-pale" style="padding:0 2px; border:none; background:none;">({{ $exResult['grade'] }})</small>
                                            @endif
                                            @if(!empty($exResult['papers']))
                                                <div style="font-size:10px; color:#555; margin-top:2px;">
                                                    @foreach($exResult['papers'] as $paper)
                                                        @php
                                                            $epm = isset($paper['marks']) && $paper['marks'] !== null && $paper['marks'] !== ''
                                                                ? number_format((float)$paper['marks'], 0) . '/' . ($paper['max_marks'] ?? '')
                                                                : '—';
                                                        @endphp
                                                        <div>{{ $paper['component_name'] ?? 'Paper' }}: {{ $epm }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endforeach
                            @endif
                            <td class="tc bold">
                                @if($isIncompleteSubj)
                                    —
                                @elseif(isset($subj['average']) && $subj['average'] !== null && $subj['average'] !== '')
                                    {{ number_format((float)$subj['average'], 0) }}
                                @elseif(isset($subj['marks']) && $subj['marks'] !== null && $subj['marks'] !== '')
                                    {{ number_format((float)$subj['marks'], 0) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="tc">{{ $subjGrade }}</td>
                            <td class="tc remark-cell {{ $remarkInfo['class'] }}">{{ $remarkInfo['text'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="result-bar">
                <strong>Total:</strong> {{ number_format($detailedSingleData['totalMarks'] ?? 0, 0) }} &nbsp;|&nbsp;
                <strong>Average:</strong> {{ number_format($detailedSingleData['averageMarks'] ?? 0, 0) }} &nbsp;|&nbsp;
                <strong>Grade:</strong> {{ $detailedSingleData['grade'] ?? 'N/A' }}
                @if(isset($detailedSingleData['division']))
                    &nbsp;|&nbsp; <strong>Division:</strong> {{ $detailedSingleData['division'] }}
                @endif
                @if(isset($detailedSingleData['position']))
                    &nbsp;|&nbsp; <strong>Position:</strong> {{ $detailedSingleData['position'] }} / {{ $detailedSingleData['totalStudentsCount'] ?? 0 }}
                @endif
            </div>

            @if(isset($detailedSingleData['remarks']))
            <div style="margin-top: 10px; border: 1px dashed #e0cdcd; padding: 6px; font-size: 14px;">
                <strong>Remarks:</strong> {{ $detailedSingleData['remarks'] }}
            </div>
            @endif

        @elseif($filters['type'] === 'report')
            <div class="section-title">Term Performance Summary</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>Total Marks</th>
                        <th class="tc">Average (%)</th>
                        <th class="tc">Grade</th>
                        <th class="tc">Division</th>
                        <th class="tc">Position</th>
                        <th class="tc">Exams</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="bold">{{ number_format($result['total_marks'], 0) }}</td>
                        <td class="tc bold">{{ number_format($result['average_marks'], 0) }}</td>
                        <td class="tc"><span class="badge-pale">{{ $result['grade'] ?? 'N/A' }}</span></td>
                        <td class="tc"><span class="badge-red">{{ $result['division'] ?? 'N/A' }}</span></td>
                        <td class="tc"><span class="badge-grn">{{ $result['position'] ?? 'N/A' }}</span></td>
                        <td class="tc">{{ $result['exam_count'] ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            @php
                $isSecondaryExamPdf = strtolower($schoolType ?? 'Secondary') === 'secondary';
                $pdfExamClassName = $student->subclass->class->class_name
                    ?? ($student->oldSubclass->class->class_name ?? '');
                $isALevelExamPdf = $isSecondaryExamPdf && pdfIsALevelClassName($pdfExamClassName);
            @endphp
            <div class="remark-color-legend">
                <div class="remark-color-legend-title">Remark Color Definition</div>
                @if($isSecondaryExamPdf)
                <div class="remark-color-blocks secondary-legend">
                    <span class="remark-color-block remark-excellent">A - Excellent</span>
                    <span class="remark-color-block remark-pass">B - Very Good</span>
                    <span class="remark-color-block remark-good">C - Good</span>
                    <span class="remark-color-block remark-avg">D - {{ $isALevelExamPdf ? 'Average' : 'Satisfactory' }}</span>
                    @if($isALevelExamPdf)
                        <span class="remark-color-block remark-avg">E - Satisfactory</span>
                        <span class="remark-color-block remark-avg">S - Subsidiary</span>
                    @endif
                    <span class="remark-color-block remark-fail">F - Fail</span>
                    <span class="remark-color-block remark-inc">Incomplete</span>
                </div>
                @else
                <div class="remark-color-blocks">
                    <span class="remark-color-block remark-pass">Pass</span>
                    <span class="remark-color-block remark-fail">Fail</span>
                    <span class="remark-color-block remark-inc">Incomplete</span>
                </div>
                @endif
            </div>
            @foreach($result as $examResult)
                <div class="section-title">{{ $examResult['exam']->exam_name ?? 'N/A' }} - {{ $examResult['exam']->start_date ?? '' }}</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th class="tc">Marks</th>
                            <th class="tc">{{ $isSecondaryExamPdf ? 'Division' : 'Grade' }}</th>
                            <th class="tc">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($examResult['subjects'] as $i => $subject)
                            @php
                                $examSubjGrade = $subject['grade'] ?? 'N/A';
                                $examRemarkInfo = getSubjectRemarkInfo($examSubjGrade, $isSecondaryExamPdf, $isALevelExamPdf);
                            @endphp
                            <tr class="{{ $i % 2 == 0 ? '' : 'even' }}">
                                <td>
                                    {{ $subject['subject_name'] }}
                                    @if(!empty($subject['is_duo']) && !empty($subject['papers']))
                                        <div style="font-size:11px; color:#555; margin-top:2px;">
                                            @foreach($subject['papers'] as $paper)
                                                @php
                                                    $pm = isset($paper['marks']) && $paper['marks'] !== null && $paper['marks'] !== ''
                                                        ? number_format((float)$paper['marks'], 0) . '/' . ($paper['max_marks'] ?? '')
                                                        : '—';
                                                @endphp
                                                <div>{{ $paper['component_name'] ?? 'Paper' }}: {{ $pm }}</div>
                                            @endforeach
                                            <div><strong>Total: {{ isset($subject['marks']) ? number_format((float)$subject['marks'], 0) : 'N/A' }}%</strong></div>
                                        </div>
                                    @endif
                                </td>
                                <td class="tc bold">{{ isset($subject['marks']) ? number_format((float)$subject['marks'], 0) : 'N/A' }}</td>
                                <td class="tc">{{ $examSubjGrade }}</td>
                                <td class="tc remark-cell {{ $examRemarkInfo['class'] }}">{{ $examRemarkInfo['text'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="result-bar">
                    <strong>Total:</strong> {{ number_format($examResult['total_marks'], 0) }} &nbsp;|&nbsp;
                    <strong>Average:</strong> {{ number_format($examResult['average_marks'], 0) }} &nbsp;|&nbsp;
                    <strong>Grade:</strong> {{ $examResult['grade'] ?? 'N/A' }} &nbsp;|&nbsp;
                    <strong>Division:</strong> {{ $examResult['division'] ?? 'N/A' }}
                </div>
            @endforeach
        @endif
    @else
        <p style="color:#940000; font-style:italic; margin-top:10px;">No result data found for this student.</p>
    @endif

    {{-- Character & Behavioral Assessment --}}
    @if(isset($characterAssessments) && isset($characterAssessments[$student->studentID]) && count($characterAssessments[$student->studentID]) > 0)
        <div class="section-title">Character & Behavioral Assessment</div>
        <table class="data character-assessment-table" style="margin-bottom: 45px;">
            <thead>
                <tr>
                    <th style="width: 40%;">Assessment Category</th>
                    <th style="width: 60%;" class="tc">Rating</th>
                </tr>
            </thead>
            <tbody>
                @foreach($characterAssessments[$student->studentID] as $idx => $ca)
                    <tr class="{{ $idx % 2 == 0 ? '' : 'even' }}">
                        <td>{{ $ca->category->name }}</td>
                        <td class="tc">
                            <span class="bold" style="color:#940000; font-size:14px;">{{ $ca->choice->choice_name ?? 'N/A' }}</span>
                            @if($ca->description)
                                <br><span style="font-size:14px; font-style:italic; color:#555;">{{ $ca->description }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Teacher's Comment & Signature --}}
    @php
        $teacher = $student->subclass->teacher ?? $student->oldSubclass->teacher ?? null;
        $teacherName = $teacher ? $teacher->first_name . ' ' . $teacher->last_name : 'N/A';
        $teacherSignBase64 = $teacher ? getSmartBase64($teacher->signature) : null;
        
        $commentVal = null;
        if(isset($detailedSingleData)) {
            $commentVal = $detailedSingleData['division'] ?? $detailedSingleData['grade'] ?? null;
        } elseif($result) {
            $reportRes = ($filters['type'] === 'report') ? $result : (is_array($result) && !empty($result) ? $result[0] : null);
            $commentVal = $reportRes['division'] ?? $reportRes['grade'] ?? null;
        }
        $teacherComment = getAutomaticComment($commentVal);
    @endphp
    <div class="teacher-comment-box">
        <div class="comment-title">Maoni ya Mwalimu wa Darasa (Class Teacher's Comment):</div>
        <div class="comment-text">{{ $teacherComment }}</div>
        
        <div class="teacher-sig-area">
            <div class="teacher-sig-img">
                @if($teacherSignBase64)
                    <img src="{{ $teacherSignBase64 }}" alt="Teacher Sign">
                @endif
            </div>
            <div class="teacher-name">Class Teacher: {{ $teacherName }}</div>
        </div>
    </div>

    <table class="sig-table">
        <tr>
            <td class="stamp-cell">
                @if($schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}" alt="Stamp">
                @else
                    <div class="placeholder-img" style="width:100px; height:100px; margin: 0 auto;">STAMP</div>
                @endif
            </td>
            <td class="sign-cell">
                @if($schoolSignBase64)
                    <div class="school-signature-img">
                        <img src="{{ $schoolSignBase64 }}" alt="Signature">
                    </div>
                @else
                    <div class="placeholder-img" style="width:160px; height:40px; margin: 0 auto 4px auto;">SIGNATURE</div>
                @endif
                <div class="sig-line"></div>
                <span class="sig-label">Headmaster's Signature</span>
            </td>
        </tr>
    </table>

    @php $idxCount++; @endphp
    @if($idxCount < $studentsToIterate->count())
        <div style="page-break-after: always;"></div>
    @endif
@endforeach

{{-- ====================================================
     CLASS / SUBCLASS / ALL STUDENTS
==================================================== --}}
@else
    {{-- HEADER (Printed once for class/subclass bulk mode) — fee-structure style --}}
    <div class="header">
        @if($schoolLogoBase64)
            <img class="school-logo-top" src="{{ $schoolLogoBase64 }}" alt="Logo">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        @if($school)
            <div class="school-sub">{{ $school->address ?? '' }}</div>
            <div class="school-sub">Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        @endif
        <div class="report-title">
            {{ $title ?? ($filters['type'] === 'report' ? 'Term Report' : 'Exam Results') }}
        </div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Term:</td>
                <td>{{ $filters['term'] ? ucwords(str_replace('_', ' ', $filters['term'])) : 'All Terms' }}</td>
                <td class="label">Year:</td>
                <td>{{ $filters['year'] }}</td>
            </tr>
            <tr>
                <td class="label">Type:</td>
                <td>
                    {{ $filters['type'] === 'exam' ? 'Exam Results' : 'Term Report' }}
                    @if($filters['examID'])
                        @php $exam = \App\Models\Examination::find($filters['examID']); @endphp
                        @if($exam) — {{ $exam->exam_name }} @endif
                    @endif
                </td>
                <td class="label">Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Scope:</td>
                <td>
                    @php
                        $scopeDisplay = $filterScopeLabel ?? '';
                        if ($scopeDisplay === '') {
                            if (!empty($filters['class'])) {
                                $cls = \App\Models\ClassModel::find($filters['class']);
                                $scopeDisplay = $cls->class_name ?? 'Selected Class';
                                if (!empty($filters['subclass'])) {
                                    $sub = \App\Models\Subclass::find($filters['subclass']);
                                    $scopeSubName = trim($sub->subclass_name ?? '');
                                    if ($scopeSubName !== '') {
                                        $scopeDisplay .= ' / ' . $scopeSubName;
                                    }
                                }
                            } else {
                                $scopeDisplay = 'All Classes';
                            }
                        }
                    @endphp
                    {{ $scopeDisplay }}
                </td>
                <td class="label">Students:</td>
                <td>{{ $students->count() }}</td>
            </tr>
        </table>
    </div>

    @if($option === 'class' || $option === 'subclass')
        @php
            $groupedStudents = $students->groupBy(function($s) {
                if ($s->subclass && $s->subclass->class) return $s->subclass->class->class_name;
                if ($s->oldSubclass && $s->oldSubclass->class) return $s->oldSubclass->class->class_name;
                return 'Unknown';
            });
        @endphp

        @foreach($groupedStudents as $className => $classStudents)
            @php
                // Overview / titles: class + subclass from filter (e.g. FORM FIVE / A)
                $overviewLabel = !empty($filterScopeLabel)
                    ? $filterScopeLabel
                    : $className;
            @endphp
            <div class="group-head" style="font-size: 15px;">{{ $overviewLabel }} - PERFORMANCE OVERVIEW</div>
            
            @php
                $isALevelClass = ($schoolType === 'Secondary') && pdfIsALevelClassName($className);
                $gradeKeysForMap = ($schoolType === 'Primary')
                    ? ['A','B','C','D','E','F']
                    : pdfSecondaryGradeKeys($isALevelClass);
                $examNumbers = $examNumbers ?? [];
                // CALCULATE STATISTICS FOR THIS GROUP
                $sTotal = 0; $sMale = 0; $sFemale = 0; $sPass = 0;
                $sMarks = 0; $mMarks = 0; $fMarks = 0;
                $divs = ['I'=>['M'=>0,'F'=>0,'T'=>0], 'II'=>['M'=>0,'F'=>0,'T'=>0], 'III'=>['M'=>0,'F'=>0,'T'=>0], 'IV'=>['M'=>0,'F'=>0,'T'=>0], '0'=>['M'=>0,'F'=>0,'T'=>0]];
                $top = [];
                $subjMap = [];
                
                foreach($classStudents as $s) {
                    if(!isset($resultsData[$s->studentID])) continue;
                    $r = $resultsData[$s->studentID];
                    $res = ($filters['type'] === 'report') ? $r : (is_array($r) && !empty($r) ? $r[0] : null);
                    if(!$res) continue;
                    
                    $sTotal++;
                    $marks = (float)($res['average_marks'] ?? 0);
                    $sMarks += $marks;
                    
                    $isMale = strtolower($s->gender ?? '') === 'male';
                    if($isMale) { $sMale++; $mMarks += $marks; } else { $sFemale++; $fMarks += $marks; }
                    
                    $dv = $res['division'] ?? '0';
                    $dvSplit = explode('.', $dv);
                    $dvCode = preg_replace('/[^IV0]/', '', $dvSplit[0]);
                    if($dvCode == '') $dvCode = '0';
                    if(!isset($divs[$dvCode])) $dvCode = '0';
                    
                    $divs[$dvCode][$isMale ? 'M' : 'F']++;
                    $divs[$dvCode]['T']++;
                    
                    if(!in_array($dvCode, ['IV', '0']) && $dvCode !== '') $sPass++;

                    $subjsList = $res['subjects'] ?? [];
                    if(is_array($subjsList)) {
                        foreach($subjsList as $subItem) {
                            $sn = $subItem['subject_name'] ?? 'Unknown';
                            if(!isset($subjMap[$sn])) {
                                $subjMap[$sn] = array_fill_keys($gradeKeysForMap, ['M'=>0,'F'=>0,'T'=>0]);
                            }
                            $gr = strtoupper($subItem['grade'] ?? 'F');
                            if(!isset($subjMap[$sn][$gr])) $gr = 'F';
                            $subjMap[$sn][$gr][$isMale ? 'M' : 'F']++;
                            $subjMap[$sn][$gr]['T']++;
                        }
                    }
                    
                    // Calculate academic rank weight for robust sorting
                    $divRank = 99; $ptsRank = 99;
                    if($schoolType === 'Secondary') {
                        $dvCodeChar = preg_replace('/[^IV0]/', '', explode('.', $dv)[0] ?: '0');
                        $divMap = ['I'=>1, 'II'=>2, 'III'=>3, 'IV'=>4, '0'=>5];
                        $divRank = $divMap[$dvCodeChar] ?? 5;
                        $ptsPart = explode('.', $dv)[1] ?? 35;
                        $ptsRank = is_numeric($ptsPart) ? (int)$ptsPart : 35;
                    } else {
                        $grChar = strtoupper($res['grade'] ?? 'F');
                        $grMap = ['A'=>1, 'B'=>2, 'C'=>3, 'D'=>4, 'E'=>5, 'F'=>6];
                        $divRank = $grMap[$grChar] ?? 6;
                    }

                    // Weight = (Div * 1000) + Points - (Average / 100)
                    $rankWeight = ($divRank * 1000) + ($ptsRank) - ($marks / 1000);

                    $top[] = [
                        'id' => ($examNumbers[$s->studentID] ?? null) ?: ($s->admission_number ?? 'N/A'),
                        'name' => trim($s->first_name . ' ' . ($s->middle_name ?? '') . ' ' . $s->last_name),
                        'div' => $dv,
                        'mark' => $marks,
                        'rank_weight' => $rankWeight
                    ];
                }
                // Sort by academic rank weight (ascending)
                usort($top, function($a, $b) { 
                    return $a['rank_weight'] <=> $b['rank_weight'];
                });
                $top5 = array_slice($top, 0, 5);
                $pRate = $sTotal > 0 ? ($sPass / $sTotal) * 100 : 0;
                $avgMarks = $sTotal > 0 ? $sMarks / $sTotal : 0;
                $mAvg = $sMale > 0 ? $mMarks / $sMale : 0;
                $fAvg = $sFemale > 0 ? $fMarks / $sFemale : 0;
            @endphp

            {{-- ONLY RENDER STATS IF WE HAVE STUDENTS WITH RESULTS --}}
            @if($sTotal > 0)
                <div class="sub-head">Overview Statistics</div>
                <table class="data" style="margin-bottom: 12px;">
                    <tbody>
                        <tr><td style="width: 50%;">Total Students</td><td class="tc bold">{{ $sTotal }}</td></tr>
                        <tr class="even"><td>Male</td><td class="tc">{{ $sMale }}</td></tr>
                        <tr><td>Female</td><td class="tc">{{ $sFemale }}</td></tr>
                        <tr class="even"><td>Pass Rate</td><td class="tc bold" style="color:#155724;">{{ number_format($pRate, 0) }}%</td></tr>
                        @php
                            // Calculate Class GPA from subjMap
                            $gpaPoints = 0; $gpaTotal = 0;
                            $ptMapGPA = ($schoolType === 'Primary')
                                ? ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'F'=>0]
                                : pdfSecondaryPointMap($isALevelClass);
                            foreach($subjMap as $sn => $sg) {
                                foreach($ptMapGPA as $g => $pts) {
                                    $cnt = $sg[$g]['T'] ?? 0;
                                    $gpaPoints += $cnt * $pts;
                                    $gpaTotal  += $cnt;
                                }
                            }
                            $classGPAVal = $gpaTotal > 0 ? number_format($gpaPoints / $gpaTotal, 2) : '0.00';
                        @endphp
                        <tr><td>Class GPA</td><td class="tc bold">{{ $classGPAVal }}</td></tr>
                        <tr class="even"><td>Pass Rate</td><td class="tc bold" style="color:#155724;">{{ number_format($pRate, 0) }}%</td></tr>
                        <tr><td>Male Average</td><td class="tc">{{ number_format($mAvg, 0) }}</td></tr>
                        <tr class="even"><td>Female Average</td><td class="tc">{{ number_format($fAvg, 0) }}</td></tr>
                        <tr><td>Fail Rate</td><td class="tc bold" style="color:#721c24;">{{ number_format((100 - $pRate), 0) }}%</td></tr>
                        @php
                            $rmk = ''; $cmt = '';
                            if ($avgMarks >= 75) { $rmk = 'Excellent'; $cmt = 'The class has performed excellently with an outstanding average score. Keep up the great work!'; }
                            elseif ($avgMarks >= 65) { $rmk = 'Very Good'; $cmt = 'Good performance, push a little harder to reach excellent.'; }
                            elseif ($avgMarks >= 45) { $rmk = 'Good'; $cmt = 'Average performance, more effort is needed to improve scores.'; }
                            elseif ($avgMarks >= 30) { $rmk = 'Satisfactory'; $cmt = 'Below average performance, students need serious effort.'; }
                            else { $rmk = 'Fail'; $cmt = 'Poor performance, urgent academic intervention is required.'; }
                        @endphp
                        <tr><td colspan="2">
                            <strong>Performance Remark:</strong> {{ $rmk }}<br>
                            <strong>Performance Comment:</strong><br><span style="font-size: 14px;">{{ $cmt }}</span>
                        </td></tr>
                    </tbody>
                </table>

                <div class="sub-head">Division Distribution</div>
                <table class="data" style="margin-bottom: 12px;">
                    <thead>
                        <tr><th style="width: 40%;">Div</th><th class="tc" style="width: 20%;">M</th><th class="tc" style="width: 20%;">F</th><th class="tc" style="width: 20%;">Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach(['I','II','III','IV','0'] as $i => $dCode)
                            <tr class="{{ $i % 2 == 0 ? '' : 'even' }}">
                                <td>Division {{ $dCode }}</td>
                                <td class="tc">{{ $divs[$dCode]['M'] }}</td>
                                <td class="tc">{{ $divs[$dCode]['F'] }}</td>
                                <td class="tc bold">{{ $divs[$dCode]['T'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="sub-head">Top 5 Students</div>
                <table class="data" style="margin-bottom: 12px;">
                    <thead>
                        <tr>
                            <th style="width:10%;" class="tc">#</th>
                            <th style="width:25%;">Index No</th>
                            <th style="width:40%;">Student Name</th>
                            <th class="tc" style="width:25%;">Division</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($top5 as $i => $t)
                            <tr class="{{ $i % 2 == 0 ? '' : 'even' }}">
                                <td class="tc">{{ $i + 1 }}</td>
                                <td>{{ $t['id'] }}</td>
                                <td>{{ $t['name'] }}</td>
                                <td class="tc bold">{{ $t['div'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="sub-head" style="margin-top:2px;">Subject Performance Statistics</div>
                <table class="data" style="margin-bottom:15px;">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            @php $gradeHeaders = ($schoolType === 'Primary') ? ['A','B','C','D','E','F'] : pdfSecondaryGradeKeys($isALevelClass); @endphp
                            @foreach($gradeHeaders as $gr)
                            <th class="tc">{{ $gr }}</th>
                            @endforeach
                            <th class="tc">GPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $si = 0;
                            $ptMapSubj = ($schoolType === 'Primary')
                                ? ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'F'=>0]
                                : pdfSecondaryPointMap($isALevelClass);
                        @endphp
                        @foreach($subjMap as $sName => $sGrades)
                            @php
                                $subjPts = 0; $subjTot = 0;
                                foreach($ptMapSubj as $g => $pts) {
                                    $c = $sGrades[$g]['T'] ?? 0;
                                    $subjPts += $c * $pts;
                                    $subjTot += $c;
                                }
                                $subjGPA = $subjTot > 0 ? number_format($subjPts / $subjTot, 2) : '0.00';
                            @endphp
                            <tr class="{{ $si % 2 == 0 ? '' : 'even' }}">
                                <td class="bold">{{ $sName }}</td>
                                @foreach($gradeHeaders as $gr)
                                <td class="tc" style="font-size:14px;">
                                    <strong>{{ $sGrades[$gr]['T'] ?? 0 }}</strong><br>
                                    <span style="color:#666;">M:{{ $sGrades[$gr]['M'] ?? 0 }}, F:{{ $sGrades[$gr]['F'] ?? 0 }}</span>
                                </td>
                                @endforeach
                                <td class="tc bold">{{ $subjGPA }}</td>
                            </tr>
                            @php $si++; @endphp
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="group-head" style="font-size:14px;">STUDENT RESULTS DETAILED LIST</div>

            @php
                // Sort students by academic rank weight (best first)
                $classStudentsSorted = $classStudents->map(function($s) use ($resultsData, $filters, $schoolType) {
                    if(!isset($resultsData[$s->studentID])) return ['s' => $s, 'weight' => 999999, 'found' => false];
                    $r = $resultsData[$s->studentID];
                    $res = ($filters['type'] === 'report') ? $r : (is_array($r) && !empty($r) ? $r[0] : null);
                    if(!$res) return ['s' => $s, 'weight' => 999999, 'found' => false];
                    
                    $dv = $res['division'] ?? '0';
                    $marks = floatval($res['average_marks'] ?? 0);
                    
                    $divRank = 99; $ptsRank = 99;
                    if($schoolType === 'Secondary') {
                        $dvCodeChar = preg_replace('/[^IV0]/', '', explode('.', $dv)[0] ?: '0');
                        $divMap = ['I'=>1, 'II'=>2, 'III'=>3, 'IV'=>4, '0'=>5];
                        $divRank = $divMap[$dvCodeChar] ?? 5;
                        $ptsPart = explode('.', $dv)[1] ?? 35;
                        $ptsRank = is_numeric($ptsPart) ? (int)$ptsPart : 35;
                    } else {
                        $grChar = strtoupper($res['grade'] ?? 'F');
                        $grMap = ['A'=>1, 'B'=>2, 'C'=>3, 'D'=>4, 'E'=>5, 'F'=>6];
                        $divRank = $grMap[$grChar] ?? 6;
                    }
                    $weight = ($divRank * 1000) + ($ptsRank) - ($marks / 1000);
                    return ['s' => $s, 'weight' => $weight, 'found' => true];
                })->sortBy('weight')->pluck('s');

                $examNumbers = $examNumbers ?? [];

                // Original list style: subjects as "PHY-68-C, CHEM-95-A, ..."
                $renderTableItems = function($studentsList) use ($resultsData, $filters, $examNumbers) {
                    $html = '';
                    $pos = 1;
                    foreach($studentsList as $student) {
                        if(!isset($resultsData[$student->studentID])) continue;
                        $r = $resultsData[$student->studentID];
                        $res = ($filters['type'] === 'report') ? $r : (is_array($r) && !empty($r) ? $r[0] : null);
                        if(!$res) continue;

                        $cn = '';
                        if ($student->subclass && $student->subclass->class) {
                            $mainCn = $student->subclass->class->class_name ?? '';
                            $subCn = trim($student->subclass->subclass_name ?? '');
                            $cn = pdfIsALevelClassName($mainCn)
                                ? ($subCn !== '' ? $subCn : $mainCn)
                                : ($subCn !== '' ? $mainCn . ' / ' . $subCn : $mainCn);
                        } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                            $mainCn = $student->oldSubclass->class->class_name ?? '';
                            $subCn = trim($student->oldSubclass->subclass_name ?? '');
                            $cn = pdfIsALevelClassName($mainCn)
                                ? ($subCn !== '' ? $subCn : $mainCn)
                                : ($subCn !== '' ? $mainCn . ' / ' . $subCn : $mainCn);
                        }

                        $divstr = $res['division'] ?? ($res['grade'] ?? 'N/A');
                        $subjStrs = [];
                        if (isset($res['subjects']) && is_array($res['subjects'])) {
                            foreach ($res['subjects'] as $subj) {
                                $sn = $subj['subject_name'] ?? 'U';
                                $gr = $subj['grade'] ?? 'F';
                                if (strtolower((string) $gr) === 'incomplete') {
                                    $subjStrs[] = "{$sn}-Incomplete";
                                } else {
                                    $mk = number_format(floatval($subj['marks'] ?? 0), 0);
                                    $subjStrs[] = "{$sn}-{$mk}-{$gr}";
                                }
                            }
                        }
                        $subText = htmlspecialchars(implode(', ', $subjStrs), ENT_QUOTES, 'UTF-8');
                        $cls = ($pos % 2 == 0) ? 'even' : '';
                        $name = htmlspecialchars(
                            trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        $indexNo = htmlspecialchars(
                            (string) ($examNumbers[$student->studentID] ?? 'N/A'),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        $cnEsc = htmlspecialchars($cn, ENT_QUOTES, 'UTF-8');
                        $divEsc = htmlspecialchars((string) $divstr, ENT_QUOTES, 'UTF-8');

                        $html .= '<tr class="'.$cls.'">
                            <td class="tc">'.$pos.'</td>
                            <td class="bold" style="font-size:11px;">'.$name.'</td>
                            <td style="font-size:10px;">'.$indexNo.'</td>
                            <td style="font-size:10px;">'.$cnEsc.'</td>
                            <td style="font-size:10px; line-height:1.25;">'.$subText.'</td>
                            <td class="tc bold">'.$divEsc.'</td>
                        </tr>';
                        $pos++;
                    }
                    return $html;
                };
            @endphp

            @if($option === 'subclass')
                @php
                    $subclassGroups = $classStudentsSorted->groupBy(function($s) {
                        if ($s->subclass) return $s->subclass->subclass_name;
                        if ($s->oldSubclass) return $s->oldSubclass->subclass_name;
                        return 'Unknown';
                    });
                @endphp
                @foreach($subclassGroups as $subclassName => $subclassStudents)
                    @php
                        $streamHead = !empty($filterScopeLabel)
                            ? $filterScopeLabel
                            : (($className ?? '') . ($subclassName !== '' ? ' / ' . $subclassName : ''));
                    @endphp
                    <div class="sub-head">{{ $streamHead }}</div>
                    <table class="data">
                        <thead>
                            <tr>
                                <th style="width:5%" class="tc">#</th>
                                <th style="width:18%">Student Name</th>
                                <th style="width:12%">Index No</th>
                                <th style="width:10%">Class</th>
                                <th style="width:45%">Subject Performances</th>
                                <th style="width:10%" class="tc">Div/Gr</th>
                            </tr>
                        </thead>
                        <tbody>
                            {!! $renderTableItems($subclassStudents) !!}
                        </tbody>
                    </table>
                @endforeach
            @else
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width:5%" class="tc">#</th>
                            <th style="width:18%">Student Name</th>
                            <th style="width:12%">Index No</th>
                            <th style="width:10%">Class</th>
                            <th style="width:45%">Subject Performances</th>
                            <th style="width:10%" class="tc">Div/Gr</th>
                        </tr>
                    </thead>
                    <tbody>
                        {!! $renderTableItems($classStudentsSorted) !!}
                    </tbody>
                </table>
            @endif


            @php
                $bulkTeacher = null;
                if(isset($subclassID)) {
                    $bulkTeacher = \App\Models\Subclass::find($subclassID)->teacher ?? null;
                } elseif(isset($filters['subclass'])) {
                    $bulkTeacher = \App\Models\Subclass::find($filters['subclass'])->teacher ?? null;
                }
                $bulkTeacherName = $bulkTeacher ? $bulkTeacher->first_name . ' ' . $bulkTeacher->last_name : null;
                $bulkTeacherSignBase64 = $bulkTeacher ? getSmartBase64($bulkTeacher->signature) : null;
            @endphp
            <table class="sig-table">
                <tr>
                    <td style="width: 33%; text-align: center;">
                        @if($bulkTeacherSignBase64)
                            <div class="teacher-sig-img" style="margin: 0 auto;">
                                <img src="{{ $bulkTeacherSignBase64 }}" alt="Teacher Sign">
                            </div>
                        @else
                            <div class="placeholder-img" style="width:120px; height:35px; margin: 0 auto 4px auto;">TEACHER SIGN</div>
                        @endif
                        <div class="sig-line" style="width: 140px;"></div>
                        <span class="sig-label">Class Teacher: {{ $bulkTeacherName ?? 'N/A' }}</span>
                    </td>
                    <td class="stamp-cell" style="width: 33%;">
                        @if($schoolStampBase64)
                            <img src="{{ $schoolStampBase64 }}" alt="Stamp" style="max-height: 120px;">
                        @else
                            <div class="placeholder-img" style="width:80px; height:80px; margin: 0 auto;">STAMP</div>
                        @endif
                    </td>
                    <td class="sign-cell" style="width: 33%;">
                        @if($schoolSignBase64)
                            <div class="school-signature-img">
                                <img src="{{ $schoolSignBase64 }}" alt="Signature">
                            </div>
                        @else
                            <div class="placeholder-img" style="width:140px; height:35px; margin: 0 auto 4px auto;">SIGNATURE</div>
                        @endif
                        <div class="sig-line" style="width: 140px;"></div>
                        <span class="sig-label">Headmaster's Signature</span>
                    </td>
                </tr>
            </table>
        @endforeach
    @else
        {{-- ALL STUDENTS — GROUPED BY CLASS, SORTED BY MARKS DESCENDING --}}
        @php
            // Group students by class, sorted by marks descending within each class
            $allGrouped = $students->groupBy(function($s) {
                if ($s->subclass && $s->subclass->class) return $s->subclass->class->class_name;
                if ($s->oldSubclass && $s->oldSubclass->class) return $s->oldSubclass->class->class_name;
                return 'Unknown';
            });
        @endphp

        @foreach($allGrouped as $className => $classStudents)
            @php
                $overviewLabelAll = !empty($filterScopeLabel)
                    ? $filterScopeLabel
                    : $className;
            @endphp
            <div class="group-head" style="font-size:15px;">{{ $overviewLabelAll }} — RESULTS</div>
            @php
                $isALevelClass = ($schoolType === 'Secondary') && pdfIsALevelClassName($className);
                $gradeKeysForMap = ($schoolType === 'Primary')
                    ? ['A','B','C','D','E','F']
                    : pdfSecondaryGradeKeys($isALevelClass);
                // Build stats for this class group
                $sTotal = 0; $sMale = 0; $sFemale = 0; $sPass = 0;
                $sMarks = 0; $mMarks = 0; $fMarks = 0;
                $subjMapAll = [];
                $topAll = [];

                foreach($classStudents as $s) {
                    if(!isset($resultsData[$s->studentID])) continue;
                    $r = $resultsData[$s->studentID];
                    $res = ($filters['type'] === 'report') ? $r : (is_array($r) && !empty($r) ? $r[0] : null);
                    if(!$res) continue;

                    $sTotal++;
                    $marks = (float)($res['average_marks'] ?? 0);
                    $sMarks += $marks;
                    $isMale = strtolower($s->gender ?? '') === 'male';
                    if($isMale) { $sMale++; $mMarks += $marks; } else { $sFemale++; $fMarks += $marks; }

                    $dv = $res['division'] ?? '0';
                    $dvSplit = explode('.', $dv);
                    $dvCode = preg_replace('/[^IV0]/', '', $dvSplit[0]);
                    if($dvCode == '') $dvCode = '0';
                    if(!in_array($dvCode, ['IV', '0']) && $dvCode !== '') $sPass++;

                    if(isset($res['subjects']) && is_array($res['subjects'])) {
                        foreach($res['subjects'] as $subItem) {
                            $sn = $subItem['subject_name'] ?? 'Unknown';
                            if(!isset($subjMapAll[$sn])) {
                                $subjMapAll[$sn] = array_fill_keys($gradeKeysForMap, ['M'=>0,'F'=>0,'T'=>0]);
                            }
                            $gr = strtoupper($subItem['grade'] ?? 'F');
                            if(!isset($subjMapAll[$sn][$gr])) $gr = 'F';
                            $subjMapAll[$sn][$gr][$isMale ? 'M' : 'F']++;
                            $subjMapAll[$sn][$gr]['T']++;
                        }
                    }

                    // Academic rank weight
                    $divRank = 99; $ptsRank = 99;
                    if($schoolType === 'Secondary') {
                        $dvCodeChar = preg_replace('/[^IV0]/', '', explode('.', $dv)[0] ?: '0');
                        $divMap = ['I'=>1, 'II'=>2, 'III'=>3, 'IV'=>4, '0'=>5];
                        $divRank = $divMap[$dvCodeChar] ?? 5;
                        $ptsPart = explode('.', $dv)[1] ?? 35;
                        $ptsRank = is_numeric($ptsPart) ? (int)$ptsPart : 35;
                    } else {
                        $grChar = strtoupper($res['grade'] ?? 'F');
                        $grMap = ['A'=>1, 'B'=>2, 'C'=>3, 'D'=>4, 'E'=>5, 'F'=>6];
                        $divRank = $grMap[$grChar] ?? 6;
                    }
                    $rankWeight = ($divRank * 1000) + ($ptsRank) - ($marks / 1000);

                    $topAll[] = ['student' => $s, 'marks' => $marks, 'div' => $dv, 'rank_weight' => $rankWeight];
                }

                // Sort by academic rank weight (ascending)
                usort($topAll, function($a, $b) { 
                    return $a['rank_weight'] <=> $b['rank_weight'];
                });
                $pRate = $sTotal > 0 ? ($sPass / $sTotal) * 100 : 0;
                $avgMarks = $sTotal > 0 ? $sMarks / $sTotal : 0;
                $mAvg = $sMale > 0 ? $mMarks / $sMale : 0;
                $fAvg = $sFemale > 0 ? $fMarks / $sFemale : 0;

                // Class GPA
                $gpaPoints = 0; $gpaTotal = 0;
                $ptMapGPA = ($schoolType === 'Primary')
                    ? ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'F'=>0]
                    : pdfSecondaryPointMap($isALevelClass);
                foreach($subjMapAll as $sn => $sg) {
                    foreach($ptMapGPA as $g => $pts) {
                        $cnt = $sg[$g]['T'] ?? 0;
                        $gpaPoints += $cnt * $pts;
                        $gpaTotal  += $cnt;
                    }
                }
                $classGPAVal = $gpaTotal > 0 ? number_format($gpaPoints / $gpaTotal, 2) : '0.00';
            @endphp

            @if($sTotal > 0)
                {{-- Overview --}}
                <div class="sub-head">Overview Statistics</div>
                <table class="data" style="margin-bottom:12px;">
                    <tbody>
                        <tr><td style="width:50%;">Total Students</td><td class="tc bold">{{ $sTotal }}</td></tr>
                        <tr class="even"><td>Male</td><td class="tc">{{ $sMale }}</td></tr>
                        <tr><td>Female</td><td class="tc">{{ $sFemale }}</td></tr>
                        <tr class="even"><td>Class GPA</td><td class="tc bold">{{ $classGPAVal }}</td></tr>
                        <tr><td>Pass Rate</td><td class="tc bold" style="color:#155724;">{{ number_format($pRate, 0) }}%</td></tr>
                        <tr class="even"><td>Fail Rate</td><td class="tc bold" style="color:#721c24;">{{ number_format((100 - $pRate), 0) }}%</td></tr>
                    </tbody>
                </table>

                {{-- Subject Performance Statistics --}}
                @if(!empty($subjMapAll))
                    @php
                        $gradeHdrs = ($schoolType === 'Primary') ? ['A','B','C','D','E','F'] : pdfSecondaryGradeKeys($isALevelClass);
                        $ptMapSubj = ($schoolType === 'Primary')
                            ? ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'F'=>0]
                            : pdfSecondaryPointMap($isALevelClass);
                    @endphp
                    <div class="sub-head" style="margin-top:2px;">Subject Performance Statistics</div>
                    <table class="data" style="margin-bottom:15px;">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                @foreach($gradeHdrs as $gr)<th class="tc">{{ $gr }}</th>@endforeach
                                <th class="tc">GPA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $si2 = 0; @endphp
                            @foreach($subjMapAll as $sName => $sGrades)
                                @php
                                    $subjPts = 0; $subjTot = 0;
                                    foreach($ptMapSubj as $g => $pts) {
                                        $c = $sGrades[$g]['T'] ?? 0;
                                        $subjPts += $c * $pts;
                                        $subjTot += $c;
                                    }
                                    $subjGPA = $subjTot > 0 ? number_format($subjPts / $subjTot, 2) : '0.00';
                                @endphp
                                <tr class="{{ $si2 % 2 == 0 ? '' : 'even' }}">
                                    <td class="bold">{{ $sName }}</td>
                                    @foreach($gradeHdrs as $gr)
                                    <td class="tc" style="font-size:14px;">
                                        <strong>{{ $sGrades[$gr]['T'] ?? 0 }}</strong><br>
                                        <span style="color:#666;">M:{{ $sGrades[$gr]['M'] ?? 0 }}, F:{{ $sGrades[$gr]['F'] ?? 0 }}</span>
                                    </td>
                                    @endforeach
                                    <td class="tc bold">{{ $subjGPA }}</td>
                                </tr>
                                @php $si2++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- Students list — subjects listed like original (PHY-68-C, CHEM-95-A, ...) --}}
                <div class="group-head" style="font-size:14px;">STUDENT RESULTS DETAILED LIST</div>
                @php $examNumbers = $examNumbers ?? []; @endphp
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width:5%" class="tc">#</th>
                            <th style="width:18%">Student Name</th>
                            <th style="width:12%">Index No</th>
                            <th style="width:50%">Subject Performances</th>
                            <th style="width:7%" class="tc">Avg</th>
                            <th style="width:8%" class="tc">Div/Gr</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topAll as $pIdx => $item)
                            @php
                                $s2 = $item['student'];
                                $r2 = $resultsData[$s2->studentID];
                                $res2 = ($filters['type'] === 'report') ? $r2 : (is_array($r2) && !empty($r2) ? $r2[0] : null);
                                $name2 = trim($s2->first_name . ' ' . ($s2->middle_name ?? '') . ' ' . $s2->last_name);
                                $index2 = $examNumbers[$s2->studentID] ?? 'N/A';
                                $subjStrs2 = [];
                                if (isset($res2['subjects']) && is_array($res2['subjects'])) {
                                    foreach ($res2['subjects'] as $subj2) {
                                        $gr2 = $subj2['grade'] ?? 'F';
                                        if (strtolower((string) $gr2) === 'incomplete') {
                                            $subjStrs2[] = ($subj2['subject_name'] ?? 'U') . '-Incomplete';
                                        } else {
                                            $subjStrs2[] = ($subj2['subject_name'] ?? 'U') . '-' . number_format(floatval($subj2['marks'] ?? 0), 0) . '-' . $gr2;
                                        }
                                    }
                                }
                                $cls2 = ($pIdx % 2 == 0) ? '' : 'even';
                            @endphp
                            <tr class="{{ $cls2 }}">
                                <td class="tc">{{ $pIdx + 1 }}</td>
                                <td class="bold" style="font-size:11px;">{{ $name2 }}</td>
                                <td style="font-size:10px;">{{ $index2 }}</td>
                                <td style="font-size:10px; line-height:1.25;">{{ implode(', ', $subjStrs2) }}</td>
                                <td class="tc bold">{{ number_format($item['marks'], 0) }}</td>
                                <td class="tc bold">{{ $item['div'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach

        @php
            $bulkTeacherAll = null;
            if(isset($subclassID)) {
                $bulkTeacherAll = \App\Models\Subclass::find($subclassID)->teacher ?? null;
            } elseif(isset($filters['subclass'])) {
                $bulkTeacherAll = \App\Models\Subclass::find($filters['subclass'])->teacher ?? null;
            }
            $bulkTeacherNameAll = $bulkTeacherAll ? $bulkTeacherAll->first_name . ' ' . $bulkTeacherAll->last_name : null;
            $bulkTeacherSignBase64All = $bulkTeacherAll ? getSmartBase64($bulkTeacherAll->signature) : null;
        @endphp
        <table class="sig-table">
            <tr>
                <td style="width: 33%; text-align: center;">
                    @if($bulkTeacherSignBase64All)
                        <div class="teacher-sig-img" style="margin: 0 auto;">
                            <img src="{{ $bulkTeacherSignBase64All }}" alt="Teacher Sign">
                        </div>
                    @else
                        <div class="placeholder-img" style="width:120px; height:35px; margin: 0 auto 4px auto;">TEACHER SIGN</div>
                    @endif
                    <div class="sig-line" style="width: 140px;"></div>
                    <span class="sig-label">Class Teacher: {{ $bulkTeacherNameAll ?? 'N/A' }}</span>
                </td>
                <td class="stamp-cell" style="width: 33%;">
                    @if($schoolStampBase64)
                        <img src="{{ $schoolStampBase64 }}" alt="Stamp" style="max-height: 120px;">
                    @else
                        <div class="placeholder-img" style="width:80px; height:80px; margin: 0 auto;">STAMP</div>
                    @endif
                </td>
                <td class="sign-cell" style="width: 33%;">
                    @if($schoolSignBase64)
                        <div class="school-signature-img">
                            <img src="{{ $schoolSignBase64 }}" alt="Signature">
                        </div>
                    @else
                        <div class="placeholder-img" style="width:140px; height:35px; margin: 0 auto 4px auto;">SIGNATURE</div>
                    @endif
                    <div class="sig-line" style="width: 140px;"></div>
                    <span class="sig-label">Headmaster's Signature</span>
                </td>
            </tr>
        </table>
    @endif
@endif
<div class="footer">
    {{ $school->school_name ?? 'School' }} &nbsp;|&nbsp; Generated: {{ date('d/m/Y H:i:s') }}
    &nbsp; &mdash; &nbsp; <span class="brand">Powered by EmCa Technologies LTD</span>
</div>

</body>
</html>
