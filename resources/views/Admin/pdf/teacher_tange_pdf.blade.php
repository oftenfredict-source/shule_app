<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'TANGE, Teachers Particular' }}</title>
    <style>
        @page { margin: 8mm 10mm 14mm 10mm; }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10.5px;
            color: #333;
            line-height: 1.35;
            background: #fff;
            margin: 0;
            padding: 0 0 24px 0;
        }
        .header {
            margin: 0 0 8px 0;
            border-bottom: 2px solid #940000;
            padding: 0 0 4px 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }
        .header-table td {
            border: none;
            vertical-align: top;
            padding: 0;
            margin: 0;
            line-height: 1.25;
        }
        .header-logo {
            width: 72px;
            text-align: left;
        }
        .header-logo img {
            width: 68px;
            height: 68px;
            display: block;
        }
        .header-center {
            text-align: center;
            padding: 2px 6px 0 6px;
            vertical-align: middle;
        }
        .header-photo {
            width: 72px;
            text-align: right;
        }
        .header-photo img {
            width: 68px;
            height: 68px;
            display: block;
            margin-left: auto;
            border: 1px solid #ccc;
        }
        .photo-placeholder {
            width: 68px;
            height: 68px;
            border: 1px solid #ccc;
            background: #f0f0f0;
            text-align: center;
            margin-left: auto;
        }
        .photo-placeholder-inner {
            padding-top: 14px;
            line-height: 1.15;
        }
        .photo-placeholder-initials {
            font-size: 18px;
            font-weight: bold;
            color: #940000;
        }
        .photo-placeholder-label {
            font-size: 6.5px;
            color: #777;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .school-name {
            font-size: 14px;
            font-weight: bold;
            color: #940000;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }
        .header-meta {
            font-size: 9px;
            color: #555;
            margin: 1px 0 0 0;
            padding: 0;
            line-height: 1.25;
        }
        .report-title {
            font-size: 11.5px;
            font-weight: bold;
            margin: 4px 0 0 0;
            background: #f8f9fa;
            padding: 5px 6px;
            border: 1px solid #ccc;
            text-align: center;
            line-height: 1.2;
        }

        .info-section { margin-bottom: 12px; width: 100%; }
        .info-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .info-table td {
            padding: 7px 8px;
            border: 1px solid #ccc;
            width: 25%;
            vertical-align: middle;
        }
        .label { font-weight: bold; color: #444; background: #f8f9fa; }

        .section-title {
            font-size: 11.5px;
            font-weight: bold;
            color: #940000;
            border-bottom: 1.5px solid #940000;
            padding-bottom: 4px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .section-desc {
            font-size: 8.5px;
            color: #666;
            margin: -4px 0 8px 0;
        }

        .stats-grid { width: 100%; margin-bottom: 12px; border-collapse: collapse; table-layout: fixed; }
        .stats-grid td {
            width: 20%;
            text-align: center;
            padding: 10px 6px;
            border: 1px solid #ccc;
            vertical-align: middle;
            background: #fff;
        }
        .stat-val { font-size: 15px; font-weight: bold; color: #222; line-height: 1.2; }
        .stat-label { font-size: 8px; color: #666; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.3px; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
            border: 1.5px solid #ccc;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #ccc;
            padding: 7px 8px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #940000;
            width: 22%;
            font-size: 9px;
            text-transform: uppercase;
        }
        table.data-table td { width: 28%; background: #fff; }
        table.data-table td.full { width: 78%; }

        .teacher-name-bar {
            font-size: 12.5px;
            font-weight: bold;
            color: #940000;
            background: #f8f9fa;
            border: 1.5px solid #ccc;
            padding: 8px 10px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            font-size: 8.5px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            background: #fff;
        }

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

        .teacher-page { page-break-inside: auto; }
        .page-break { page-break-after: always; break-after: page; }

        .sig-table {
            width: 100%;
            margin-top: 28px;
            border-collapse: collapse;
            page-break-inside: avoid;
            table-layout: fixed;
        }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; width: 50%; }
        .stamp-cell { text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 110px; max-height: 110px; opacity: 0.85; }
        .sign-cell { text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 150px; max-height: 40px; }
        .sig-line { border-top: 2px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label {
            font-size: 8.5px;
            color: #940000;
            font-weight: bold;
            padding-top: 4px;
            display: block;
            text-transform: uppercase;
        }
        .empty-note {
            text-align: center;
            color: #940000;
            margin-top: 40px;
            font-weight: bold;
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
                    public_path('userImages/' . $path),
                    $parent . '/public_html/' . $path,
                    $parent . '/public_html/uploads/' . $path,
                    $parent . '/public_html/schoolLogos/' . $path,
                    $parent . '/public_html/userImages/' . $path,
                ];
                if (isset($_SERVER['DOCUMENT_ROOT'])) {
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
                    $possibilities[] = $docRoot . '/' . $path;
                    $possibilities[] = $docRoot . '/uploads/' . $path;
                    $possibilities[] = $docRoot . '/schoolLogos/' . $path;
                    $possibilities[] = $docRoot . '/userImages/' . $path;
                }
                $possibilities[] = base_path($path);
                $possibilities[] = base_path('public/' . $path);
                $possibilities[] = base_path('public/userImages/' . $path);

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
        $reportTitle = $title ?? 'TANGE, TEACHERS PARTICULAR';
        $totalTeachers = $list->count();
        $isBulk = $totalTeachers > 1;
        $generatedAt = now()->format('d M Y H:i');
    @endphp

    @if($list->isEmpty())
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @if($schoolLogoBase64)
                            <img src="{{ $schoolLogoBase64 }}" alt="School Logo">
                        @endif
                    </td>
                    <td class="header-center">
                        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
                    </td>
                    <td class="header-photo">
                        <div class="photo-placeholder">
                            <div class="photo-placeholder-inner">
                                <div class="photo-placeholder-initials">-</div>
                                <div class="photo-placeholder-label">No Photo</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <div class="report-title">{{ strtoupper($reportTitle) }}</div>
        </div>
        <p class="empty-note">No teacher data found for this report.</p>
    @else
        @foreach($list as $row)
            @php
                $fullName = trim(implode(' ', array_filter([
                    $row->first_name ?? null,
                    $row->middle_name ?? null,
                    $row->last_name ?? null,
                ])));
                $ageYears = $row->age ?? null;
                $expYears = $row->years_of_experience ?? null;
                $completion = isset($row->tange_completion_percent) ? (int) $row->tange_completion_percent : null;
                $pageTitle = $isBulk
                    ? 'TANGE, TEACHERS PARTICULAR | ALL TEACHERS'
                    : strtoupper($reportTitle);

                $teacherPhotoBase64 = null;
                $imgName = $row->image ?? null;
                if ($imgName) {
                    $teacherPhotoBase64 = tangeGetSmartBase64('userImages/' . ltrim($imgName, '/'));
                    if (!$teacherPhotoBase64) {
                        $teacherPhotoBase64 = tangeGetSmartBase64($imgName);
                    }
                }

                $initials = '';
                if (!empty($row->first_name)) {
                    $initials .= strtoupper(mb_substr(trim($row->first_name), 0, 1));
                }
                if (!empty($row->last_name)) {
                    $initials .= strtoupper(mb_substr(trim($row->last_name), 0, 1));
                }
                if ($initials === '') {
                    $initials = 'T';
                }
            @endphp

            <div class="teacher-page {{ !$loop->last ? 'page-break' : '' }}">
                <div class="header">
                    <table class="header-table">
                        <tr>
                            <td class="header-logo">
                                @if($schoolLogoBase64)
                                    <img src="{{ $schoolLogoBase64 }}" alt="School Logo">
                                @else
                                    <div class="photo-placeholder">
                                        <div class="photo-placeholder-inner">
                                            <div class="photo-placeholder-initials" style="font-size:14px;">LOGO</div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="header-center">
                                <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
                                @if(!empty($school->address))
                                    <div class="header-meta">{{ $school->address }}</div>
                                @endif
                                <div class="header-meta">
                                    Phone: {{ $school->formattedPhones() ?: 'N/A' }}
                                    @if(!empty($school->email))
                                        | Email: {{ $school->email }}
                                    @endif
                                </div>
                            </td>
                            <td class="header-photo">
                                @if($teacherPhotoBase64)
                                    <img src="{{ $teacherPhotoBase64 }}" alt="Teacher Photo">
                                @else
                                    <div class="photo-placeholder">
                                        <div class="photo-placeholder-inner">
                                            <div class="photo-placeholder-initials">{{ $initials }}</div>
                                            <div class="photo-placeholder-label">No Photo</div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                    <div class="report-title">{{ $pageTitle }}</div>
                </div>

                <div class="info-section">
                    <table class="info-table">
                        <tr>
                            <td class="label">Report</td>
                            <td>Teachers Particular (TANGE)</td>
                            <td class="label">Date Generated</td>
                            <td>{{ $generatedAt }}</td>
                        </tr>
                        <tr>
                            <td class="label">Teacher</td>
                            <td>{{ $val($fullName !== '' ? $fullName : null) }}</td>
                            <td class="label">{{ $isBulk ? 'Page' : 'Records' }}</td>
                            <td>
                                @if($isBulk)
                                    {{ $loop->iteration }} of {{ $totalTeachers }}
                                @else
                                    1 Teacher
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Check Number (C/N)</td>
                            <td>{{ $val($row->employee_number ?? null) }}</td>
                            <td class="label">School</td>
                            <td>{{ $school->school_name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="section-title">Profile Overview</div>
                <p class="section-desc">Quick summary of identity, service duration, and TANGE completeness for this teacher.</p>
                <table class="stats-grid">
                    <tr>
                        <td>
                            <div class="stat-val">{{ $ageYears !== null ? $ageYears : '-' }}</div>
                            <div class="stat-label">Age (Years)</div>
                        </td>
                        <td>
                            <div class="stat-val">{{ $expYears !== null ? $expYears : '-' }}</div>
                            <div class="stat-label">Experience</div>
                        </td>
                        <td>
                            <div class="stat-val" style="font-size:11px;">{{ $val($row->gender ?? null) }}</div>
                            <div class="stat-label">Sex</div>
                        </td>
                        <td>
                            <div class="stat-val" style="font-size:10px;">{{ $val($row->establishment_rank ?? null) }}</div>
                            <div class="stat-label">Rank</div>
                        </td>
                        <td>
                            <div class="stat-val">{{ $completion !== null ? $completion . '%' : '-' }}</div>
                            <div class="stat-label">TANGE Fill</div>
                        </td>
                    </tr>
                </table>

                <div class="teacher-name-bar">
                    {{ $val($fullName !== '' ? $fullName : null) }}
                    @if(!empty($row->employee_number))
                       , C/N: {{ $row->employee_number }}
                    @endif
                </div>

                <div class="section-title">1. Identity &amp; Employment</div>
                <p class="section-desc">Official identity numbers, establishment details, and current employment status.</p>
                <table class="data-table">
                    <tr>
                        <th>Teacher / Officer Name</th>
                        <td colspan="3" class="full">{{ $val($fullName !== '' ? $fullName : null) }}</td>
                    </tr>
                    <tr>
                        <th>Sex</th>
                        <td>{{ $val($row->gender ?? null) }}</td>
                        <th>Check Number (C/N)</th>
                        <td>{{ $val($row->employee_number ?? null) }}</td>
                    </tr>
                    <tr>
                        <th>TSC Number (TSC/N)</th>
                        <td>{{ $val($row->tsc_number ?? null) }}</td>
                        <th>Establishment Rank</th>
                        <td>{{ $val($row->establishment_rank ?? null) }}</td>
                    </tr>
                    <tr>
                        <th>Salary Scale</th>
                        <td>{{ $val($row->salary_scale ?? null) }}</td>
                        <th>Position of Authority</th>
                        <td>{{ $val($row->position ?? null) }}</td>
                    </tr>
                    <tr>
                        <th>Work Station</th>
                        <td colspan="3" class="full">{{ $val($row->work_station ?? null) }}</td>
                    </tr>
                </table>

                <div class="section-title">2. Service Dates &amp; Tenure</div>
                <p class="section-desc">Key career milestones, birth, employment, confirmation, rank, and retirement dates.</p>
                <table class="data-table">
                    <tr>
                        <th>Date of Birth</th>
                        <td>{{ $fmtDate($row->date_of_birth ?? null) }}</td>
                        <th>Age</th>
                        <td>{{ $ageYears !== null ? $ageYears . ' years' : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Date of Employment</th>
                        <td>{{ $fmtDate($row->date_hired ?? null) }}</td>
                        <th>Work Experience</th>
                        <td>{{ $expYears !== null ? $expYears . ' years' : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Date of Confirmation</th>
                        <td>{{ $fmtDate($row->date_confirmed ?? null) }}</td>
                        <th>Date of Current Rank</th>
                        <td>{{ $fmtDate($row->date_current_rank ?? null) }}</td>
                    </tr>
                    <tr>
                        <th>Date of Retirement</th>
                        <td colspan="3" class="full">{{ $fmtDate($row->date_of_retirement ?? null) }}</td>
                    </tr>
                </table>

                <div class="section-title">3. Academic &amp; Teaching</div>
                <p class="section-desc">Highest qualification, subjects studied, and subjects currently taught.</p>
                <table class="data-table">
                    <tr>
                        <th>Highest Education Level</th>
                        <td colspan="3" class="full">{{ $val($row->qualification ?? null) }}</td>
                    </tr>
                    <tr>
                        <th>Subjects Studied</th>
                        <td colspan="3" class="full">{{ $val($row->specialization ?? null) }}</td>
                    </tr>
                    <tr>
                        <th>Subjects Teaching</th>
                        <td colspan="3" class="full">{{ $val($row->subjects_teaching ?? null) }}</td>
                    </tr>
                </table>

                <div class="section-title">4. Contact &amp; Residence</div>
                <p class="section-desc">Residential address and phone number for official contact.</p>
                <table class="data-table">
                    <tr>
                        <th>Place of Residence</th>
                        <td colspan="3" class="full">{{ $val($row->address ?? null) }}</td>
                    </tr>
                    <tr>
                        <th>Phone Number</th>
                        <td colspan="3" class="full">{{ $val($row->phone_number ?? null) }}</td>
                    </tr>
                </table>

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
        Generated by ShuleXpert (School Management System), Powered By EmCa Techonology &copy; {{ date('Y') }}
    </div>
</body>
</html>
