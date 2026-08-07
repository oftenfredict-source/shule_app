<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Subject Performance Summary</title>
    @php
        if (!function_exists('getSmartBase64PdfSubjectStats')) {
            function getSmartBase64PdfSubjectStats($path) {
                if (!$path) return null;
                
                $path = ltrim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
                $filename = basename($path);
                $base = base_path();
                $parent = dirname($base);
                
                $possibilities = [
                    public_path($path),                             // Standard Laravel Public
                    public_path('uploads/' . $path),                // Standard Laravel Public Uploads
                    public_path('images/' . $filename),
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
        $schoolLogoBase64  = getSmartBase64PdfSubjectStats($school->school_logo ?? '');
        $schoolStampBase64 = getSmartBase64PdfSubjectStats($school->school_stamp ?? '');
        $schoolSignBase64  = getSmartBase64PdfSubjectStats($school->school_signature ?? '');
        $underOrg = !empty($school->is_under_organization);
        $orgLogoBase64 = ($underOrg && !empty($school->organization_logo))
            ? getSmartBase64PdfSubjectStats($school->organization_logo)
            : null;
        $orgName = ($underOrg && !empty(trim((string) ($school->organization_name ?? ''))))
            ? strtoupper(trim((string) $school->organization_name))
            : null;
    @endphp
    <style>
        @page { margin: 10mm 8mm 12mm 8mm; }
        body { font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif; font-size: 14px; color: #333; line-height: 1.4; }
        
        /* ── WATERMARK ─────────────────── */
        .watermark {
            position: fixed;
            top: 40%;
            left: 5%;
            width: 100%;
            text-align: center;
            opacity: 0.05;
            transform: rotate(-30deg);
            font-size: 100px;
            font-weight: bold;
            color: #000;
            z-index: -1000;
            text-transform: uppercase;
        }

        /* ── HEADER ─────────────────── */
        .header { margin-bottom: 12px; border-bottom: 2px solid #940000; padding-bottom: 10px; text-align: center; }
        .header-table { width: auto; max-width: 78%; margin: 0 auto; border-collapse: collapse; }
        .header-table td { border: none; padding: 0; vertical-align: middle; }
        .logo-side { width: 64px; }
        .logo-side img { display: block; max-height: 56px; max-width: 62px; border: none; outline: none; margin: 0; }
        .logo-side.left { text-align: left; padding-right: 10px; }
        .logo-side.right { text-align: right; padding-left: 10px; }
        .logo-side.right img { margin-left: auto; }
        .header-center { text-align: center; padding: 0 4px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .org-name { font-size: 13px; font-weight: bold; color: #555; text-transform: uppercase; margin-bottom: 3px; }
        .school-sub { font-size: 11px; color: #555; margin: 1px 0; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; color: #940000; text-transform: uppercase; }
        .report-subtitle { font-size: 11px; font-weight: bold; margin-top: 4px; color: #555; text-transform: uppercase; }

        /* ── INFO SECTION ─────────────────── */
        .info-section { margin-bottom: 15px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 14px; }
        .label { font-weight: bold; color: #555; width: 20%; }

        /* ── LEGEND ─────────────────── */
        .legend-title { text-align: center; font-weight: bold; color: #6c757d; margin: 10px 0 5px 0; font-size: 14px; text-transform: uppercase; }
        .legend { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .legend td { border: 1px solid #eee; text-align: center; padding: 5px; font-weight: bold; font-size: 14px; }
        
        .remark-excellent { background: #28a745; color: #ffffff; }
        .remark-pass { background: #d4edda; color: #155724; }
        .remark-good { background: #fff3cd; color: #856404; }
        .remark-avg { background: #ffe8d6; color: #a04000; }
        .remark-fail { background: #f8d7da; color: #721c24; }
        .remark-inc { background: #d1ecf1; color: #0c5460; }
        table.report td.remark-cell,
        .legend td {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── TABLE ─────────────────── */
        table.report { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 20px; }
        table.report th, table.report td {
            border: 1px solid #eee; padding: 4px 2px; text-align: center; vertical-align: middle; font-size: 13px;
        }
        table.report thead th { background: #f8f9fa; font-weight: bold; color: #940000; }
        table.report td.subject { text-align: left; font-weight: bold; color: #333; padding-left: 5px; }
        table.report td.remark-cell { font-weight: bold; }
        
        /* ── FOOTER ─────────────────── */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        /* ── SIGNATURE AND STAMP ─────────────────── */
        .sig-table { width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 50%; text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 250px; max-height: 250px; opacity: 0.85; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 90px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 280px; max-height: 90px; }
        .sig-line   { border-top: 1.5px solid #940000; width: 280px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 13px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase;}
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

    @php
        $displayClass = $classLabel ?: 'ALL CLASSES';
        if (preg_match('/all\s+subclasses/i', $displayClass)) {
            $displayClass = preg_replace('/[-\s]*all\s+subclasses/i', '', $displayClass);
        }
        $displayClass = trim($displayClass);
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-side left">
                    @if($orgLogoBase64)
                        <img src="{{ $orgLogoBase64 }}" alt="Organization Logo">
                    @elseif($schoolLogoBase64)
                        <img src="{{ $schoolLogoBase64 }}" alt="School Logo">
                    @endif
                </td>
                <td class="header-center">
                    @if($orgName)
                        <div class="org-name">{{ $orgName }}</div>
                    @endif
                    <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
                    <div class="school-sub">{{ $school->address ?? '' }}</div>
                    <div class="school-sub">Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
                </td>
                <td class="logo-side right">
                    @if($orgLogoBase64 && $schoolLogoBase64)
                        <img src="{{ $schoolLogoBase64 }}" alt="School Logo">
                    @endif
                </td>
            </tr>
        </table>
        <div class="report-title">SUBJECTS PERFORMANCE SUMMARY</div>
        <div class="report-subtitle">{{ $displayClass }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Academic Year:</td>
                <td>{{ $yearLabel }}</td>
                <td class="label">Term:</td>
                <td>{{ $termLabel }}</td>
            </tr>
            <tr>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
                <td class="label">Class/Grade:</td>
                <td>{{ $displayClass }}</td>
            </tr>
        </table>
    </div>

    <div class="legend-title">Remark Color Definition</div>
    <table class="legend">
        <tr>
            @if(strtolower($schoolType) === 'secondary')
                <td class="remark-excellent">A - Excellent</td>
                <td class="remark-pass">B - Very Good</td>
                <td class="remark-good">C - Good</td>
                <td class="remark-avg">D - Satisfactory</td>
                <td class="remark-fail">F - Fail</td>
                <td class="remark-inc">Incomplete</td>
            @else
                <td class="remark-pass">Pass</td>
                <td class="remark-fail">Failed</td>
                <td class="remark-inc">Incomplete</td>
            @endif
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th rowspan="2" style="width:18%;">SUBJECTS</th>
                @foreach($gradeHeaders as $grade)
                    <th colspan="3">{{ $grade }}</th>
                @endforeach
                <th rowspan="2" style="width:4%;">GPA</th>
                <th rowspan="2" style="width:5%;">RANKS</th>
                <th rowspan="2" style="width:10%;">COMPETENCY LEVEL</th>
                <th rowspan="2" style="width:4%;">GRADE</th>
                <th rowspan="2" style="width:8%;">REMARK</th>
            </tr>
            <tr>
                @foreach($gradeHeaders as $grade)
                    <th style="width:2.8%;">F</th>
                    <th style="width:2.8%;">M</th>
                    <th style="width:2.8%;">T</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td class="subject">{{ strtoupper($row['subject']) }}</td>
                    @foreach($gradeHeaders as $grade)
                        <td>{{ $row['grade_breakdown'][$grade]['female'] ?? 0 }}</td>
                        <td>{{ $row['grade_breakdown'][$grade]['male'] ?? 0 }}</td>
                        <td style="font-weight: bold;">{{ $row['grade_breakdown'][$grade]['total'] ?? 0 }}</td>
                    @endforeach
                    <td>{{ $row['gpa_text'] }}</td>
                    <td>{{ $row['rank'] }}</td>
                    <td>{{ $row['competency_level'] }}</td>
                    <td>{{ (strtoupper($row['grade']) === 'INCOMPLETE') ? '-' : $row['grade'] }}</td>
                    <td class="remark-cell {{ $row['remark_class'] }}">{{ $row['remark_text'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sig-table">
        <tr>
            <td class="stamp-cell">
                @if($schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}">
                    <div style="font-size: 8px; color: #777; margin-top: 5px;">SCHOOL OFFICIAL STAMP</div>
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

    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Technology &copy; {{ date('Y') }}
    </div>
</body>
</html>
