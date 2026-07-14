<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'TANGE — Teachers Particular' }}</title>
    <style>
        @page {
            margin: 24px 28px 42px 28px;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding-bottom: 28px;
        }
        .header {
            text-align: center;
            margin-bottom: 18px;
            border-bottom: 2px solid #940000;
            padding-bottom: 10px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #940000;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            background: #f8f9fa;
            padding: 5px;
        }
        .info-section { margin-bottom: 16px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 25%; }

        .section-title {
            background: #940000;
            color: #fff;
            padding: 6px 10px;
            margin: 12px 0 8px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table th,
        table.data-table td {
            border: 1px solid #eee;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
            background: transparent;
        }
        table.data-table th {
            background-color: rgba(248, 249, 250, 0.55);
            font-weight: bold;
            color: #940000;
            width: 42%;
        }
        .teacher-block {
            margin-bottom: 10px;
            border: 1px solid #ddd;
            padding: 14px;
            border-radius: 5px;
            background: transparent;
        }
        .teacher-name {
            font-size: 13px;
            font-weight: bold;
            color: #940000;
            margin-bottom: 6px;
            border-bottom: 2px solid #940000;
            padding-bottom: 5px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 6px;
            padding-bottom: 4px;
            background: #fff;
        }

        .watermark {
            position: fixed;
            top: 42%;
            left: 0;
            width: 100%;
            text-align: center;
            opacity: 0.05;
            transform: rotate(-45deg);
            font-size: 90px;
            font-weight: bold;
            color: #000;
            z-index: 9999;
            text-transform: uppercase;
            pointer-events: none;
        }

        .teacher-page {
            page-break-inside: auto;
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }
        .sig-table { width: 100%; margin-top: 24px; border-collapse: collapse; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 50%; text-align: center; }
        .stamp-cell img { max-width: 110px; max-height: 110px; opacity: 0.85; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 150px; max-height: 40px; }
        .sig-line { border-top: 1.5px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label {
            font-size: 8.5px;
            color: #940000;
            font-weight: bold;
            padding-top: 4px;
            display: block;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

    @php
        $list = collect(isset($teachers) ? $teachers : [])->filter()->values();
        if ($list->isEmpty() && isset($teacher) && $teacher) {
            $list = collect([$teacher]);
        }

        $val = function ($value) {
            if ($value === null) {
                return 'N/A';
            }
            if (is_string($value) && trim($value) === '') {
                return 'N/A';
            }
            return $value;
        };

        $fmtDate = function ($value) {
            if ($value === null || $value === '') {
                return 'N/A';
            }
            try {
                return \Carbon\Carbon::parse($value)->format('d M Y');
            } catch (\Throwable $e) {
                return 'N/A';
            }
        };

        if (!function_exists('tangeGetSmartBase64')) {
            function tangeGetSmartBase64($path) {
                if (!$path) {
                    return null;
                }
                $path = ltrim($path, '/');
                $base = base_path();
                $parent = dirname($base);
                $possibilities = [
                    public_path($path),
                    public_path('uploads/' . $path),
                    public_path('schoolLogos/' . $path),
                    $parent . '/public_html/' . $path,
                    $parent . '/public_html/uploads/' . $path,
                    $parent . '/public_html/schoolLogos/' . $path,
                ];
                if (isset($_SERVER['DOCUMENT_ROOT'])) {
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
                    $possibilities[] = $docRoot . '/' . $path;
                    $possibilities[] = $docRoot . '/uploads/' . $path;
                    $possibilities[] = $docRoot . '/schoolLogos/' . $path;
                }
                $possibilities[] = base_path($path);
                $possibilities[] = base_path('public/' . $path);

                foreach ($possibilities as $fullPath) {
                    if (@file_exists($fullPath) && is_file($fullPath)) {
                        try {
                            $data = base64_encode(file_get_contents($fullPath));
                            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'png');
                            $mime = in_array($ext, ['jpg', 'jpeg'], true) ? 'jpeg' : $ext;
                            return 'data:image/' . $mime . ';base64,' . $data;
                        } catch (\Exception $e) {
                        }
                    }
                }
                return null;
            }
        }

        $schoolLogoBase64 = tangeGetSmartBase64($school->school_logo ?? ($school->logo ?? ''));
        $schoolStampBase64 = tangeGetSmartBase64($school->school_stamp ?? '');
        $schoolSignBase64 = tangeGetSmartBase64($school->school_signature ?? '');
        $reportTitle = $title ?? 'TANGE — Teachers Particular';
        $totalTeachers = $list->count();
        $isBulk = $totalTeachers > 1;
    @endphp

    @if($list->isEmpty())
        <div class="header">
            @if($schoolLogoBase64)
                <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
            @endif
            <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
            <div class="report-title">{{ strtoupper($reportTitle) }}</div>
        </div>
        <p style="text-align:center;color:#940000;margin-top:40px;"><strong>No teacher data found for this report.</strong></p>
    @else
        @foreach($list as $row)
            @php
                $fullName = trim(implode(' ', array_filter([
                    $row->first_name ?? null,
                    $row->middle_name ?? null,
                    $row->last_name ?? null,
                ])));
                $ageYears = $row->age;
                $expYears = $row->years_of_experience;
            @endphp

            {{-- Each teacher gets a full page identical to single-teacher TANGE PDF --}}
            <div class="teacher-page {{ !$loop->last ? 'page-break' : '' }}">
                <div class="header">
                    @if($schoolLogoBase64)
                        <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
                    @endif
                    <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
                    <div>{{ $school->address ?? '' }}</div>
                    <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
                    <div class="report-title">
                        {{ strtoupper($isBulk ? 'TANGE — Teachers Particular' : $reportTitle) }}
                    </div>
                </div>

                <div class="info-section">
                    <table class="info-table">
                        <tr>
                            <td class="label">Report:</td>
                            <td>Teachers Particular (TANGE)</td>
                            <td class="label">{{ $isBulk ? 'Page' : 'Teachers:' }}</td>
                            <td>
                                @if($isBulk)
                                    {{ $loop->iteration }} of {{ $totalTeachers }}
                                @else
                                    1
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Teacher:</td>
                            <td>{{ $val($fullName !== '' ? $fullName : null) }}</td>
                            <td class="label">Date Generated:</td>
                            <td>{{ now()->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Check Number (C/N):</td>
                            <td>{{ $val($row->employee_number ?? null) }}</td>
                            <td class="label">School:</td>
                            <td>{{ $school->school_name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="teacher-block">
                    <div class="teacher-name">{{ $val($fullName !== '' ? $fullName : null) }}</div>

                    <div class="section-title">Teachers Particular (TANGE)</div>
                    <table class="data-table">
                        <tr>
                            <th>Teacher / Officer Name</th>
                            <td>{{ $val($fullName !== '' ? $fullName : null) }}</td>
                        </tr>
                        <tr>
                            <th>Sex</th>
                            <td>{{ $val($row->gender ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Check Number (C/N)</th>
                            <td>{{ $val($row->employee_number ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>TSC Number (TSC/N)</th>
                            <td>{{ $val($row->tsc_number ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Establishment Rank</th>
                            <td>{{ $val($row->establishment_rank ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Salary Scale</th>
                            <td>{{ $val($row->salary_scale ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Date of Birth</th>
                            <td>{{ $fmtDate($row->date_of_birth ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Date of Employment</th>
                            <td>{{ $fmtDate($row->date_hired ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Date of Confirmation</th>
                            <td>{{ $fmtDate($row->date_confirmed ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Date of Current Rank</th>
                            <td>{{ $fmtDate($row->date_current_rank ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Date of Retirement</th>
                            <td>{{ $fmtDate($row->date_of_retirement ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Highest Education Level</th>
                            <td>{{ $val($row->qualification ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Subjects Studied</th>
                            <td>{{ $val($row->specialization ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Subjects Teaching</th>
                            <td>{{ $val($row->subjects_teaching ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Age</th>
                            <td>{{ $ageYears !== null ? $ageYears . ' years' : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Position of Authority</th>
                            <td>{{ $val($row->position ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Work Station</th>
                            <td>{{ $val($row->work_station ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Work Experience (Years)</th>
                            <td>{{ $expYears !== null ? $expYears . ' years' : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Place of Residence</th>
                            <td>{{ $val($row->address ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td>{{ $val($row->phone_number ?? null) }}</td>
                        </tr>
                    </table>
                </div>

                @if($loop->last)
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
                @endif
            </div>
        @endforeach
    @endif

    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Techonologies &copy; {{ date('Y') }}
    </div>
</body>
</html>
