<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Subject Results - {{ $subject->subject_name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-logo { max-height: 80px; max-width: 120px; margin-bottom: 6px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }

        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; border: 1px solid #ddd; }
        .label { font-weight: bold; color: #555; background: #fafafa; width: 15%; }
        .value { width: 35%; }

        table.results { width: 100%; border-collapse: collapse; margin-bottom: 20px; text-align: center; }
        table.results th, table.results td { border: 1px solid #ddd; padding: 6px 4px; }
        table.results th { background-color: #940000; color: white; font-size: 10px; text-transform: uppercase; }
        .student-name { text-align: left; }
        .muted { color: #888; }
        .formula-note { font-size: 9px; color: #555; margin-bottom: 10px; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }

        table.results tr:nth-child(even) { background-color: #fffafa; }
    </style>
</head>
<body>
    @php
        $isDuo = !empty($is_duo) && !empty($duo_papers);
        $papers = $duo_papers ?? [];
        $paperMaxTotal = $paper_max_total ?? 0;
        $school = $school ?? null;

        if (!function_exists('getSmartBase64SubjectResultsPdf')) {
            function getSmartBase64SubjectResultsPdf($path) {
                if (!$path) {
                    return null;
                }
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
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
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
                            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'png');
                            $mime = $ext === 'jpg' || $ext === 'jpeg' ? 'jpeg' : $ext;
                            return 'data:image/' . $mime . ';base64,' . $data;
                        } catch (\Exception $e) {
                        }
                    }
                }
                return null;
            }
        }

        $schoolLogoBase64 = getSmartBase64SubjectResultsPdf($school->school_logo ?? ($school->logo ?? ''));
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" class="school-logo" alt="School Logo">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School Report' }}</div>
        @if(!empty($school->address))
            <div>{{ $school->address }}</div>
        @endif
        <div>
            @if(!empty($school->phone))Tel: {{ $school->phone }}@endif
            @if(!empty($school->phone) && !empty($school->email)) | @endif
            @if(!empty($school->email))Email: {{ $school->email }}@endif
        </div>
        <div class="report-title">SUBJECT PERFORMANCE REPORT{{ $isDuo ? ' (DUO)' : '' }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Class:</td>
            <td class="value">{{ $class_subject->class_name ?? 'N/A' }}</td>
            <td class="label">Subject:</td>
            <td class="value">{{ $class_subject->subject_name ?? 'N/A' }}{{ $isDuo ? ' [DUO]' : '' }}</td>
        </tr>
        <tr>
            <td class="label">Teacher:</td>
            <td class="value">{{ $teacher_name }}</td>
            <td class="label">Examination:</td>
            <td class="value">{{ $exam_name }}</td>
        </tr>
        <tr>
            <td class="label">Generated:</td>
            <td class="value" colspan="3">{{ date('d/m/Y H:i:s') }}</td>
        </tr>
    </table>

    @if($isDuo)
        <div class="formula-note">
            Duo total = student paper marks ÷ {{ $paperMaxTotal }} (all papers max) × 100 → grade
        </div>
    @endif

    <table class="results">
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Index No.</th>
                @if($isDuo)
                    @foreach($papers as $paper)
                        <th>
                            {{ $paper['component_name'] }}
                            @if(!empty($paper['max_marks']))
                                <br><span style="font-weight:normal;font-size:8px;">(/{{ $paper['max_marks'] }})</span>
                            @endif
                        </th>
                    @endforeach
                    <th>Total<br><span style="font-weight:normal;font-size:8px;">(/{{ $paperMaxTotal }} → %)</span></th>
                @else
                    <th>Examination</th>
                    @if($has_ca)
                        <th>Exam Marks</th>
                        <th>CA</th>
                        <th>Total</th>
                        <th>Avg</th>
                    @else
                        <th>Marks</th>
                    @endif
                @endif
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $index => $result)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="student-name">{{ trim(($result->student->first_name ?? '') . ' ' . ($result->student->middle_name ?? '') . ' ' . ($result->student->last_name ?? '')) }}</td>
                <td>{{ $result->examination_number ?? ($result->student->examination_number ?? 'N/A') }}</td>
                @if($isDuo)
                    @php $paperMarks = (array) ($result->paper_marks ?? []); @endphp
                    @foreach($papers as $paper)
                        @php
                            $cid = (int) $paper['componentID'];
                            $m = array_key_exists($cid, $paperMarks) ? $paperMarks[$cid] : null;
                        @endphp
                        <td>{{ $m !== null && $m !== '' ? $m : '—' }}</td>
                    @endforeach
                    <td>
                        @if($result->percentage !== null && $result->percentage !== '')
                            <strong>{{ $result->percentage }}</strong>
                            @if($result->student_raw_total !== null)
                                <br><span class="muted" style="font-size:8px;">{{ $result->student_raw_total }}/{{ $paperMaxTotal }}</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                @else
                    <td>{{ $exam_name }}</td>
                    @if($has_ca)
                        <td>{{ $result->exam_marks }}</td>
                        <td>{{ $result->ca_marks }}</td>
                        <td>{{ $result->total_marks }}</td>
                        <td>{{ $result->avg_marks }}</td>
                    @else
                        <td>{{ $result->exam_marks }}</td>
                    @endif
                @endif
                <td><strong>{{ $result->grade }}</strong></td>
                <td>{{ $result->remark }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $isDuo ? (5 + count($papers)) : ($has_ca ? 9 : 6) }}" style="text-align:center;">No results found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by ShuleXpert Support System | {{ date('d/m/Y H:i') }} | Teacher: {{ $teacher_name }}
    </div>
</body>
</html>
