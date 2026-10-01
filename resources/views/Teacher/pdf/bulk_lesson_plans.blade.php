<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulk Lesson Plans</title>
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
        .dotted-line { border-bottom: 1.5px dotted #940000; min-height: 22px; padding: 4px 0; margin: 4px 0; background: #fffafb; }

        /* ── SIGNATURE AND STAMP ─────────────────── */
        .sig-table { width: 100%; margin-top: 20px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 8px; vertical-align: bottom; text-align: center; }
        .stamp-cell img { max-width: 90px; max-height: 90px; }
        .signature-img { height: 35px; margin-bottom: 4px; }
        .signature-img img { max-width: 110px; max-height: 35px; }
        .sig-line { border-top: 1.5px solid #940000; width: 130px; margin: 0 auto; display: block; }
        .sig-label { font-size: 8px; color: #940000; font-weight: bold; padding-top: 3px; display: block; text-transform: uppercase; }

        .page-break { page-break-before: always; }
        .plan-divider { border-top: 3px solid #940000; margin-top: 25px; margin-bottom: 20px; }

        /* ── WATERMARK ─────────────────── */
        .watermark {
            position: fixed; top: 45%; left: 5%; width: 100%; text-align: center; opacity: 0.04;
            transform: rotate(-45deg); font-size: 100px; font-weight: bold; color: #000; z-index: -1; text-transform: uppercase;
        }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #940000; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

    @php
        $schoolLogoBase64 = null;
        if ($school && $school->school_logo) {
            $logoPath = public_path($school->school_logo);
            $parent = dirname(base_path());
            $possibilities = [
                $logoPath,
                $parent . '/public_html/' . ltrim($school->school_logo, '/'),
            ];
            foreach ($possibilities as $p) {
                if (@file_exists($p) && is_file($p)) {
                    try {
                        $ext = pathinfo($p, PATHINFO_EXTENSION);
                        $schoolLogoBase64 = 'data:image/' . ($ext ?: 'png') . ';base64,' . base64_encode(file_get_contents($p));
                    } catch (\Exception $e) {}
                    break;
                }
            }
        }

        // School stamp & signature (for approved plans)
        $schoolStampBase64 = null;
        $headmasterSignBase64 = null;
        if ($school) {
            foreach (['school_stamp' => 'schoolStampBase64', 'school_signature' => 'headmasterSignBase64'] as $field => $var) {
                if ($school->$field) {
                    $p = public_path($school->$field);
                    if (@file_exists($p) && is_file($p)) {
                        try {
                            $ext = pathinfo($p, PATHINFO_EXTENSION);
                            $$var = 'data:image/' . ($ext ?: 'png') . ';base64,' . base64_encode(file_get_contents($p));
                        } catch (\Exception $e) {}
                    }
                }
            }
        }
    @endphp

    {{-- ── SHARED HEADER ─────────────────── --}}
    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 70px; margin-bottom: 8px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
        <div class="school-info">{{ $school->address ?? '' }}</div>
        <div class="school-info">Phone: {{ $school?->formattedPhones() }} | Email: {{ $school?->email ?? '' }}</div>
        <div class="report-title">LESSON PLANS REPORT - {{ $schoolType }} ({{ $lessonPlans->count() }} Plan(s))</div>
    </div>

    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Technology &copy; {{ date('Y') }}
    </div>

    {{-- ── LESSON PLAN LOOP ─────────────────── --}}
    @foreach($lessonPlans as $index => $lessonPlan)
        @php
            $teacherName = 'N/A';
            if ($lessonPlan->teacher) {
                $teacherName = trim(($lessonPlan->teacher->first_name ?? '') . ' ' . ($lessonPlan->teacher->last_name ?? ''));
            }

            $formattedDate = \Carbon\Carbon::parse($lessonPlan->lesson_date)->format('d/m/Y');

            $formatTime = function($timeStr) {
                if (!$timeStr) return 'N/A';
                $parts = explode(':', $timeStr);
                $hours = (int)$parts[0];
                $minutes = $parts[1] ?? '00';
                $ampm = $hours >= 12 ? 'PM' : 'AM';
                $displayHours = $hours % 12 ?: 12;
                return $displayHours . ':' . $minutes . ' ' . $ampm;
            };

            $startTime = $formatTime($lessonPlan->lesson_time_start);
            $endTime   = $formatTime($lessonPlan->lesson_time_end);

            $stages = is_array($lessonPlan->lesson_stages)
                ? $lessonPlan->lesson_stages
                : (json_decode($lessonPlan->lesson_stages, true) ?? []);
            $stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];

            // Convert teacher signature to base64 if it's a data URL already or a path
            $teacherSigB64 = null;
            if ($lessonPlan->teacher_signature) {
                if (str_starts_with($lessonPlan->teacher_signature, 'data:image')) {
                    $teacherSigB64 = $lessonPlan->teacher_signature;
                }
            }
            $supervisorSigB64 = null;
            if ($lessonPlan->supervisor_signature) {
                if (str_starts_with($lessonPlan->supervisor_signature, 'data:image')) {
                    $supervisorSigB64 = $lessonPlan->supervisor_signature;
                }
            }
            $isApproved = !empty($lessonPlan->supervisor_signature);
        @endphp

        <div class="{{ $index > 0 ? 'page-break' : '' }}">

            {{-- ── INFO TABLE ─────────────────── --}}
            <table style="table-layout: auto;">
                <tr>
                    <th style="width: 12%;">SUBJECT:</th>
                    <td style="width: 20%;">{{ $lessonPlan->subject ?? 'N/A' }}</td>
                    <th style="width: 10%;">CLASS:</th>
                    <td style="width: 20%;">{{ $lessonPlan->class_name ?? 'N/A' }}</td>
                    <th style="width: 8%;">YEAR:</th>
                    <td>{{ $lessonPlan->year ?? date('Y') }}</td>
                </tr>
                <tr>
                    <th style="width: 15%;">TEACHER'S NAME</th>
                    <td colspan="2" style="width: 35%;">{{ $teacherName }}</td>
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
                    <th>TIME</th>
                    <td colspan="2">{{ $startTime }} - {{ $endTime }}</td>
                    <th>DATE</th>
                    <td colspan="2">{{ $formattedDate }}</td>
                </tr>
            </table>

            {{-- ── COMPETENCE TABLE ─────────────────── --}}
            <table>
                <tr>
                    <th style="width: 28%;">MAIN COMPETENCE</th>
                    <td>{{ $lessonPlan->main_competence ?? '' }}</td>
                </tr>
                <tr>
                    <th>SPECIFIC COMPETENCE</th>
                    <td>{{ $lessonPlan->specific_competence ?? '' }}</td>
                </tr>
                <tr>
                    <th>MAIN ACTIVITY</th>
                    <td>{{ $lessonPlan->main_activity ?? '' }}</td>
                </tr>
                <tr>
                    <th>SPECIFIC ACTIVITY</th>
                    <td>{{ $lessonPlan->specific_activity ?? '' }}</td>
                </tr>
                <tr>
                    <th>TEACHING &amp; LEARNING RESOURCES</th>
                    <td>{{ $lessonPlan->teaching_learning_resources ?? '' }}</td>
                </tr>
                <tr>
                    <th>REFERENCES</th>
                    <td>{{ $lessonPlan->references ?? '' }}</td>
                </tr>
            </table>

            {{-- ── LESSON DEVELOPMENT ─────────────────── --}}
            <div class="section-title">LESSON DEVELOPMENT</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 18%;">STAGE</th>
                        <th style="width: 12%;">TIME</th>
                        <th style="width: 23%;">TEACHING ACTIVITIES</th>
                        <th style="width: 23%;">LEARNING ACTIVITIES</th>
                        <th>ASSESSMENT CRITERIA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stageNames as $stageName)
                        @php $stage = collect($stages)->firstWhere('stage', $stageName) ?? []; @endphp
                        <tr>
                            <td>{{ $stageName }}</td>
                            <td>{{ $stage['time'] ?? '' }}</td>
                            <td>{{ $stage['teaching_activities'] ?? '' }}</td>
                            <td>{{ $stage['learning_activities'] ?? '' }}</td>
                            <td>{{ $stage['assessment_criteria'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- ── REMARKS / REFLECTION / EVALUATION ─────────────────── --}}
            <div class="section-title">Remarks</div>
            <div class="dotted-line"><p style="margin:0; padding: 2px 0;">{{ $lessonPlan->remarks ?? '' }}</p></div>

            <div class="section-title">Reflection</div>
            <div class="dotted-line"><p style="margin:0; padding: 2px 0;">{{ $lessonPlan->reflection ?? '' }}</p></div>
            <div class="dotted-line"></div>

            <div class="section-title">Evaluation</div>
            <div class="dotted-line"><p style="margin:0; padding: 2px 0;">{{ $lessonPlan->evaluation ?? '' }}</p></div>
            <div class="dotted-line"></div>

            {{-- ── SIGNATURES ─────────────────── --}}
            <table class="sig-table">
                <tr>
                    <td style="width: 33%;">
                        <div class="signature-img">
                            @if($teacherSigB64)
                                <img src="{{ $teacherSigB64 }}">
                            @endif
                        </div>
                        <span class="sig-line"></span>
                        <span class="sig-label">SUBJECT TEACHER'S SIGNATURE</span>
                        <div style="font-size: 7px; margin-top: 2px;">{{ strtoupper($teacherName) }}</div>
                    </td>
                    <td style="width: 34%; text-align: center;">
                        @if($isApproved && $schoolStampBase64)
                            <img src="{{ $schoolStampBase64 }}" style="max-height: 75px;">
                            <div style="font-size: 7px; color: #940000; font-weight: bold; margin-top: 4px;">SCHOOL OFFICIAL STAMP</div>
                        @endif
                    </td>
                    <td style="width: 33%;">
                        <div class="signature-img">
                            @if($supervisorSigB64)
                                <img src="{{ $supervisorSigB64 }}">
                            @elseif($isApproved && $headmasterSignBase64)
                                <img src="{{ $headmasterSignBase64 }}">
                            @endif
                        </div>
                        <span class="sig-line"></span>
                        <span class="sig-label">HEADMASTER'S SIGNATURE</span>
                        <div style="font-size: 7px; margin-top: 2px;">ACADEMIC DEPARTMENT</div>
                    </td>
                </tr>
            </table>

        </div>
    @endforeach
</body>
</html>
