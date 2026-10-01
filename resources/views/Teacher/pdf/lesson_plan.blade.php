<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lesson Plan - {{ $lessonPlan->subject }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; padding: 0; margin: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 15px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; margin-bottom: 5px; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; color: #333; border: 1px solid #eee; }
        .school-info { font-size: 10px; color: #666; margin-bottom: 3px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; word-wrap: break-word; }
        th { background-color: #f8f9fa; font-weight: bold; color: #940000; font-size: 9px; text-transform: uppercase; }
        
        .nested-table { width: 100%; border-collapse: collapse; }
        .nested-table th, .nested-table td { font-size: 8px; padding: 3px; text-align: center; border: 1px solid #ddd; }
        .nested-table th { background-color: #f8f9fa; color: #333; }

        .section-title { font-size: 11px; font-weight: bold; color: #940000; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 2px; }
        .dotted-line { border-bottom: 1.5px dotted #940000; min-height: 25px; padding: 5px 0; margin: 5px 0; background: #fffafb; }

        /* ── SIGNATURE AND STAMP ─────────────────── */
        .sig-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 10px; vertical-align: bottom; text-align: center; }
        .stamp-cell img { max-width: 100px; max-height: 100px; }
        .sign-cell { width: 30%; }
        .signature-img { height: 40px; margin-bottom: 4px; }
        .signature-img img { max-width: 120px; max-height: 40px; }
        .sig-line { border-top: 1.5px solid #940000; width: 140px; margin: 0 auto; display: block; }
        .sig-label { font-size: 8px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #940000; border-top: 1px solid #eee; padding-top: 5px; }
        
        /* ── WATERMARK ─────────────────── */
        .watermark {
            position: fixed; top: 45%; left: 5%; width: 100%; text-align: center; opacity: 0.04;
            transform: rotate(-45deg); font-size: 100px; font-weight: bold; color: #000; z-index: -1; text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

    <div class="header">
        @if(isset($schoolLogoBase64) && $schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 70px; margin-bottom: 8px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
        <div class="school-info">{{ $school->address ?? '' }}</div>
        <div class="school-info">Phone: {{ $school->formattedPhones() }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">LESSON PLAN REPORT - {{ $schoolType }}</div>
    </div>
    
    <table style="table-layout: auto;">
        <tr>
            <th style="width: 12%;">SUBJECT:</th>
            <td class="swahili-text" style="width: 20%;">{{ $lessonPlan->subject ?? 'N/A' }}</td>
            <th style="width: 10%;">CLASS:</th>
            <td class="swahili-text" style="width: 15%;">{{ $lessonPlan->class_name ?? 'N/A' }}</td>
            <th style="width: 8%;">YEAR:</th>
            <td style="width: 35%;">{{ $lessonPlan->year ?? date('Y') }}</td>
        </tr>
        <tr>
            <th style="width: 15%;">TEACHER'S NAME</th>
            <td colspan="2" class="swahili-text" style="width: 35%;">{{ $teacherName }}</td>
            <td colspan="3" style="width: 50%; padding: 0;">
                <table class="nested-table" style="margin: 0; width: 100%;">
                    <tr>
                        <th colspan="6" style="text-align: center;">NUMBER OF PUPILS</th>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: center; width: 50%;">REGISTERED</th>
                        <th colspan="3" style="text-align: center; width: 50%;">PRESENT</th>
                    </tr>
                    <tr>
                        <th style="font-size: 7px;">GIRLS</th>
                        <th style="font-size: 7px;">BOYS</th>
                        <th style="font-size: 7px;">TOTAL</th>
                        <th style="font-size: 7px;">GIRLS</th>
                        <th style="font-size: 7px;">BOYS</th>
                        <th style="font-size: 7px;">TOTAL</th>
                    </tr>
                    <tr>
                        <td>{{ $lessonPlan->registered_girls ?? 0 }}</td>
                        <td>{{ $lessonPlan->registered_boys ?? 0 }}</td>
                        <td>{{ $lessonPlan->registered_total ?? 0 }}</td>
                        <td>{{ $lessonPlan->present_girls ?? 0 }}</td>
                        <td>{{ $lessonPlan->present_boys ?? 0 }}</td>
                        <td>{{ $lessonPlan->present_total ?? 0 }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th style="width: 8%;">TIME</th>
            <td style="width: 20%;">
                @php
                    $startTime = $lessonPlan->lesson_time_start;
                    $endTime = $lessonPlan->lesson_time_end;
                    if (is_string($startTime)) {
                        $parts = explode(':', $startTime);
                        $hours = (int)$parts[0];
                        $minutes = $parts[1] ?? '00';
                        $ampm = $hours >= 12 ? 'PM' : 'AM';
                        $displayHours = $hours % 12 ?: 12;
                        $startTimeFormatted = $displayHours . ':' . $minutes . ' ' . $ampm;
                    } else {
                        $startTimeFormatted = $startTime;
                    }
                    if (is_string($endTime)) {
                        $parts = explode(':', $endTime);
                        $hours = (int)$parts[0];
                        $minutes = $parts[1] ?? '00';
                        $ampm = $hours >= 12 ? 'PM' : 'AM';
                        $displayHours = $hours % 12 ?: 12;
                        $endTimeFormatted = $displayHours . ':' . $minutes . ' ' . $ampm;
                    } else {
                        $endTimeFormatted = $endTime;
                    }
                @endphp
                {{ $startTimeFormatted }} - {{ $endTimeFormatted }}
            </td>
            <th style="width: 8%;">DATE</th>
            <td colspan="3" style="width: 64%;">
                @php
                    $date = $lessonPlan->lesson_date;
                    if ($date instanceof \Carbon\Carbon) {
                        $dateFormatted = $date->format('d/m/Y');
                    } else {
                        $dateFormatted = \Carbon\Carbon::parse($date)->format('d/m/Y');
                    }
                @endphp
                {{ $dateFormatted }}
            </td>
        </tr>
    </table>
    
    <table>
        <tr>
            <th style="width: 28%;">MAIN COMPETENCE</th>
            <td class="swahili-text" style="width: 72%;">{{ $lessonPlan->main_competence ?? '' }}</td>
        </tr>
        <tr>
            <th style="width: 28%;">SPECIFIC COMPETENCE</th>
            <td class="swahili-text" style="width: 72%;">{{ $lessonPlan->specific_competence ?? '' }}</td>
        </tr>
        <tr>
            <th style="width: 28%;">MAIN ACTIVITY</th>
            <td class="swahili-text" style="width: 72%;">{{ $lessonPlan->main_activity ?? '' }}</td>
        </tr>
        <tr>
            <th style="width: 28%;">SPECIFIC ACTIVITY</th>
            <td class="swahili-text" style="width: 72%;">{{ $lessonPlan->specific_activity ?? '' }}</td>
        </tr>
        <tr>
            <th style="width: 28%;">TEACHING & LEARNING RESOURCES</th>
            <td class="swahili-text" style="width: 72%;">{{ $lessonPlan->teaching_learning_resources ?? '' }}</td>
        </tr>
        <tr>
            <th style="width: 28%;">REFERENCES</th>
            <td class="swahili-text" style="width: 72%;">{{ $lessonPlan->references ?? '' }}</td>
        </tr>
    </table>
    
    <div class="section-title">LESSON DEVELOPMENT</div>
    <table>
        <thead>
            <tr>
                <th>STAGE</th>
                <th>TIME</th>
                <th>TEACHING ACTIVITIES</th>
                <th>LEARNING ACTIVITIES</th>
                <th>ASSESSMENT CRITERIA</th>
            </tr>
        </thead>
        <tbody>
            @php
                $stages = is_array($lessonPlan->lesson_stages) ? $lessonPlan->lesson_stages : json_decode($lessonPlan->lesson_stages, true) ?? [];
                $stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];
            @endphp
            @foreach($stageNames as $stageName)
                @php
                    $stage = collect($stages)->firstWhere('stage', $stageName) ?? [];
                @endphp
                <tr>
                    <td>{{ $stageName }}</td>
                    <td>{{ $stage['time'] ?? '' }}</td>
                    <td class="swahili-text">{{ $stage['teaching_activities'] ?? '' }}</td>
                    <td class="swahili-text">{{ $stage['learning_activities'] ?? '' }}</td>
                    <td class="swahili-text">{{ $stage['assessment_criteria'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="section-title">Remarks</div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;" class="swahili-text">{{ $lessonPlan->remarks ?? '' }}</p>
    </div>
    
    <div class="section-title">Reflection</div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;" class="swahili-text">{{ $lessonPlan->reflection ?? '' }}</p>
    </div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;"></p>
    </div>
    
    <div class="section-title">Evaluation</div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;" class="swahili-text">{{ $lessonPlan->evaluation ?? '' }}</p>
    </div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;"></p>
    </div>
    
    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Technology &copy; {{ date('Y') }}
    </div>

    <table class="sig-table">
        <tr>
            <td class="sign-cell" style="width: 33%;">
                <div class="signature-img">
                    @if(isset($teacherSignatureBase64) && $teacherSignatureBase64)
                        <img src="{{ $teacherSignatureBase64 }}">
                    @endif
                </div>
                <span class="sig-line"></span>
                <span class="sig-label">SUBJECT TEACHER'S SIGNATURE</span>
                <div style="font-size: 7px; margin-top: 2px;">{{ strtoupper($teacherName) }}</div>
            </td>
            <td class="stamp-cell" style="width: 34%;">
                @if(isset($schoolStampBase64) && $schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}" style="max-height: 80px;">
                    <div style="font-size: 7px; color: #940000; font-weight: bold; margin-top: 5px;">SCHOOL OFFICIAL STAMP</div>
                @endif
            </td>
            <td class="sign-cell" style="width: 33%;">
                <div class="signature-img">
                    @if(isset($supervisorSignatureBase64) && $supervisorSignatureBase64)
                        <img src="{{ $supervisorSignatureBase64 }}">
                        @if(isset($headmasterSignatureBase64) && $headmasterSignatureBase64)
                            <div style="margin-top: -10px;">
                                <img src="{{ $headmasterSignatureBase64 }}" style="max-width: 80px; max-height: 30px; opacity: 0.8;">
                            </div>
                        @endif
                    @endif
                </div>
                <span class="sig-line"></span>
                <span class="sig-label">HEADMASTER'S SIGNATURE</span>
                <div style="font-size: 7px; margin-top: 2px;">ACADEMIC DEPARTMENT</div>
            </td>
        </tr>
    </table>
</body>
</html>

