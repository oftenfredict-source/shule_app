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
                $dirHint = trim(str_replace('\\', '/', dirname($path)), './');
                
                $possibilities = [
                    public_path($path),                             // Standard Laravel Public
                    public_path('userImages/' . $filename),        // Student photos folder
                    public_path('uploads/' . $path),                // Standard Laravel Public Uploads
                    public_path('stamps/' . $filename),
                    public_path('signatures/' . $filename),
                    public_path('logos/' . $filename),
                    $parent . '/public_html/' . $path,              // cPanel / Shared Hosting
                    $parent . '/public_html/userImages/' . $filename, // cPanel student photos
                    $parent . '/public_html/uploads/' . $path,      // cPanel / Shared Hosting Uploads
                    $parent . '/public_html/stamps/' . $filename,
                    $parent . '/public_html/signatures/' . $filename,
                ];
                
                if (isset($_SERVER['DOCUMENT_ROOT'])) {
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
                    $possibilities[] = $docRoot . '/' . $path;
                    $possibilities[] = $docRoot . '/userImages/' . $filename;
                    $possibilities[] = $docRoot . '/uploads/' . $path;
                    $possibilities[] = $docRoot . '/stamps/' . $filename;
                    $possibilities[] = $docRoot . '/signatures/' . $filename;
                }
                
                $possibilities[] = base_path($path);
                $possibilities[] = base_path('public/userImages/' . $filename);
                $possibilities[] = base_path('public/' . $path);
                $possibilities[] = base_path('public/stamps/' . $filename);
                $possibilities[] = base_path('public/signatures/' . $filename);
                $possibilities[] = base_path('../public_html/' . $path);
                
                foreach ($possibilities as $fullPath) {
                    if (@file_exists($fullPath) && is_file($fullPath)) {
                        try {
                            $data = base64_encode(file_get_contents($fullPath));
                            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                            $mime = strtolower($ext ?: 'png');
                            if ($mime === 'jpg') {
                                $mime = 'jpeg';
                            }
                            return 'data:image/' . $mime . ';base64,' . $data;
                        } catch (\Exception $e) {}
                    }
                }

                // Fuzzy fallback: DB may reference an older timestamped file
                // e.g. stamps/1774551674_Stamp_Bishop.png → match *_Stamp_Bishop.png
                $suffix = preg_replace('/^\d+_/', '', $filename);
                if ($suffix !== '' && $suffix !== $filename) {
                    $searchDirs = [];
                    if ($dirHint !== '' && $dirHint !== '.') {
                        $searchDirs[] = public_path($dirHint);
                        $searchDirs[] = base_path('public/' . $dirHint);
                        if (isset($_SERVER['DOCUMENT_ROOT'])) {
                            $searchDirs[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/' . $dirHint;
                        }
                    }
                    $searchDirs[] = public_path('stamps');
                    $searchDirs[] = public_path('signatures');
                    $searchDirs = array_unique(array_filter($searchDirs, 'is_dir'));
                    $matches = [];
                    foreach ($searchDirs as $dir) {
                        foreach ((array) @glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '*' . $suffix) as $match) {
                            if (is_file($match)) {
                                $matches[] = $match;
                            }
                        }
                    }
                    if (!empty($matches)) {
                        usort($matches, function ($a, $b) {
                            $na = (int) preg_replace('/^(\d+).*/', '$1', basename($a));
                            $nb = (int) preg_replace('/^(\d+).*/', '$1', basename($b));
                            if ($na !== $nb) {
                                return $nb <=> $na;
                            }
                            return (@filemtime($b) ?: 0) <=> (@filemtime($a) ?: 0);
                        });
                        try {
                            $fullPath = $matches[0];
                            $data = base64_encode(file_get_contents($fullPath));
                            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                            $mime = strtolower($ext ?: 'png');
                            if ($mime === 'jpg') {
                                $mime = 'jpeg';
                            }
                            return 'data:image/' . $mime . ';base64,' . $data;
                        } catch (\Exception $e) {}
                    }
                }
                
                return null;
            }
        }
        
        $schoolLogoBase64  = getSmartBase64($school->school_logo ?? '');
        $schoolStampBase64 = getSmartBase64($school->school_stamp ?? '');
        $schoolSignBase64  = getSmartBase64($school->school_signature ?? '');
        $underOrg = !empty($school->is_under_organization);
        $orgLogoBase64 = ($underOrg && !empty($school->organization_logo))
            ? getSmartBase64($school->organization_logo)
            : null;
        $orgName = ($underOrg && !empty(trim((string) ($school->organization_name ?? ''))))
            ? strtoupper(trim((string) $school->organization_name))
            : null;

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
                    return ['text' => 'Haijakamilika', 'class' => 'remark-inc'];
                }

                if ($isSecondary) {
                    if ($isALevel) {
                        switch ($normalizedGrade) {
                            case 'A':
                                return ['text' => 'Bora sana', 'class' => 'remark-excellent'];
                            case 'B':
                                return ['text' => 'Nzuri sana', 'class' => 'remark-pass'];
                            case 'C':
                                return ['text' => 'Nzuri', 'class' => 'remark-good'];
                            case 'D':
                                return ['text' => 'Inaridhisha', 'class' => 'remark-avg'];
                            case 'E':
                                return ['text' => 'Tosheleza', 'class' => 'remark-avg'];
                            case 'S':
                                return ['text' => 'Subsidiary', 'class' => 'remark-avg'];
                            case 'F':
                                return ['text' => 'Mbaya', 'class' => 'remark-fail'];
                        }
                    } else {
                        switch ($normalizedGrade) {
                            case 'A':
                                return ['text' => 'Bora sana', 'class' => 'remark-excellent'];
                            case 'B':
                                return ['text' => 'Nzuri sana', 'class' => 'remark-pass'];
                            case 'C':
                                return ['text' => 'Nzuri', 'class' => 'remark-good'];
                            case 'D':
                                return ['text' => 'Inaridhisha', 'class' => 'remark-avg'];
                            case 'F':
                                return ['text' => 'Mbaya', 'class' => 'remark-fail'];
                        }
                    }
                    if (preg_match('/^(I|II|III|IV)\./', $normalizedGrade)) {
                        return ['text' => 'Amefaulu', 'class' => 'remark-pass'];
                    }
                    if (preg_match('/^(0|FAIL)/', $normalizedGrade)) {
                        return ['text' => 'Mbaya', 'class' => 'remark-fail'];
                    }
                    return ['text' => 'Mbaya', 'class' => 'remark-fail'];
                }

                if (in_array($normalizedGrade, ['A', 'B', 'C', 'D'])) {
                    return ['text' => 'Amefaulu', 'class' => 'remark-pass'];
                }
                if (in_array($normalizedGrade, ['E', 'F'])) {
                    return ['text' => 'Mbaya', 'class' => 'remark-fail'];
                }

                return ['text' => 'Mbaya', 'class' => 'remark-fail'];
            }
        }

        if (!function_exists('pdfDisplayGrade')) {
            /** Short grade for narrow Daraja cells (avoid "Incomplete" overflow). */
            function pdfDisplayGrade($grade) {
                $g = trim((string) ($grade ?? ''));
                if (
                    $g === '' ||
                    strcasecmp($g, 'Incomplete') === 0 ||
                    strcasecmp($g, 'INCOMPLETE') === 0 ||
                    strcasecmp($g, 'N/A') === 0 ||
                    $g === '-'
                ) {
                    return '—';
                }
                return $g;
            }
        }

        if (!function_exists('getAutomaticComment')) {
            function getAutomaticComment($gradeOrDivision) {
                $raw = strtoupper(trim((string) $gradeOrDivision));
                // Support "I.10", "DIVISION I", etc.
                $key = $raw;
                if (preg_match('/^(I|II|III|IV|0)\b/', $raw, $m)) {
                    $key = $m[1];
                } elseif (preg_match('/DIV(?:ISION)?\s*(I|II|III|IV|0)/', $raw, $m)) {
                    $key = $m[1];
                }

                if (in_array($key, ['I', 'DIV I', 'DIVISION I'], true) || $key === 'I') {
                    return 'Hongera! Umeonyesha ufaulu bora sana. Endelea na juhudi hizo hizo.';
                }
                if ($key === 'II') {
                    return 'Ufaulu mzuri sana. Endelea kufanya bidii ili kufikia daraja la kwanza.';
                }
                if ($key === 'III') {
                    return 'Ufaulu mzuri, lakini unahitaji juhudi zaidi ili kuboresha.';
                }
                if ($key === 'IV') {
                    return 'Ufaulu wa wastani. Unahitaji kuongeza bidii kwa kiasi kikubwa.';
                }
                if ($key === '0' || $key === 'FAIL') {
                    return 'Ufaulu duni. Tafuta ushauri wa kitaaluma na fanya bidii zaidi.';
                }

                if ($key === 'A') {
                    return 'Hongera! Umeonyesha ufaulu bora sana. Endelea na ari hiyo.';
                }
                if ($key === 'B') {
                    return 'Ufaulu mzuri sana. Fanya bidii zaidi kwa matokeo bora.';
                }
                if ($key === 'C') {
                    return 'Ufaulu mzuri. Unahitaji juhudi zaidi.';
                }
                if ($key === 'D') {
                    return 'Ufaulu wa wastani. Zingatia masomo yako zaidi.';
                }
                if ($key === 'E' || $key === 'F') {
                    return 'Ufaulu duni. Unahitaji msaada wa haraka na muda zaidi wa kusoma.';
                }

                return 'Juhudi nzuri. Endelea kufanya kazi kwa bidii ili kuboresha matokeo yako.';
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
        .header img.org-logo-top { display: block; max-height: 52px; margin: 0 auto 8px auto; border: none; outline: none; }
        .org-logo-center { text-align: center; margin: 0 0 8px 0; }
        .org-logo-center img { display: block; max-height: 56px; max-width: 120px; margin: 0 auto; border: none; outline: none; }
        .logo-stack { text-align: center; }
        .logo-stack img { display: block; margin: 0 auto 4px auto; border: none; outline: none; }
        .logo-stack img.school-logo { max-height: 55px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .header-table td { border: none; padding: 0; vertical-align: middle; }
        .logo-cell { width: 72px; padding-right: 8px; text-align: left; }
        .logo-cell img { display: block; max-height: 60px; max-width: 68px; border: none; outline: none; }
        .student-photo-cell { width: 72px; text-align: right; padding-left: 8px; }
        .student-photo-cell img { display: block; width: 68px; height: 68px; margin-left: auto; border: none; outline: none; object-fit: cover; }
        .school-name-cell { text-align: center; }
        .org-name { font-size: 12px; font-weight: bold; color: #555; text-transform: uppercase; margin-bottom: 2px; letter-spacing: 0.3px; }
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

        /* Term report card: Marks|Grade under each exam */
        table.data.term-report-card th.exam-group { font-size: 8.5px; padding: 4px 2px; }
        table.data.term-report-card th.exam-sub { font-size: 8px; padding: 3px 2px; background: #f8f8f8; color: #555; }
        table.data.term-report-card td.exam-marks,
        table.data.term-report-card td.exam-grade {
            text-align: center;
            font-size: 9.5px;
            padding: 4px 2px;
            overflow: hidden;
            word-wrap: break-word;
            word-break: break-word;
        }
        table.data td.daraja-cell {
            text-align: center;
            font-weight: bold;
            font-size: 9.5px;
            padding: 4px 2px;
            overflow: hidden;
            white-space: nowrap;
        }
        .summary-grid { width: 100%; border-collapse: collapse; margin: 8px 0 12px 0; border: 1.5px solid #ccc; table-layout: fixed; }
        .summary-grid td {
            border: 1px solid #ccc !important;
            padding: 5px 6px;
            font-size: 9px;
            vertical-align: middle;
            text-align: left;
        }
        .summary-grid .sg-k {
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
        }
        .summary-grid .sg-v {
            font-weight: bold;
            color: #940000;
        }
        .summary-grid .sg-label { font-weight: bold; color: #444; font-size: 8px; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .summary-grid .sg-value { font-weight: bold; color: #940000; font-size: 12px; display: block; }
        .grade-key-table { width: 100%; border-collapse: collapse; margin: 8px 0 10px 0; font-size: 8px; }
        .grade-key-table th, .grade-key-table td {
            border: 1px solid #ccc;
            padding: 3px 4px;
            text-align: center;
        }
        .grade-key-table th { background: #f0f0f0; color: #940000; font-size: 8px; text-transform: uppercase; }
        .grade-key-table .gk-title {
            background: #940000;
            color: #fff;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
            font-size: 9px;
            text-transform: uppercase;
        }

        .dotted-lines { margin-top: 4px; }
        .dot-line {
            border-bottom: 1px dotted #555;
            height: 16px;
            margin-bottom: 6px;
            width: 100%;
        }
        .sig-dot-line {
            border-bottom: 1px dotted #333;
            width: 100%;
            margin-top: 2px;
        }
        .official-remarks-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            border: 1.5px solid #ccc;
            font-size: 10.5px;
        }
        .official-remarks-table td {
            border: 1px solid #ccc !important;
            vertical-align: top;
            padding: 8px 10px;
        }
        .official-remarks-table .orm-title {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            color: #940000;
            margin-bottom: 6px;
        }
        .official-remarks-table .orm-title-dark {
            font-weight: bold;
            font-size: 10px;
            color: #222;
            margin-bottom: 4px;
        }
        .official-remarks-table .orm-body {
            font-size: 10px;
            line-height: 1.4;
            min-height: 34px;
        }
        .official-remarks-table .orm-meta {
            font-size: 9px;
            margin-top: 6px;
            color: #555;
        }
        .official-remarks-table .orm-sign-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #940000;
            margin-top: 6px;
        }

        /* ADA + TAARIFA (individual report) */
        .ada-taarifa-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 10px 0;
            border: 1.5px solid #ccc;
            font-size: 10px;
        }
        .ada-taarifa-table td {
            border: 1px solid #ccc !important;
            vertical-align: top;
            padding: 8px 10px;
            width: 50%;
        }
        .ada-taarifa-table .at-head {
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            color: #940000;
            margin-bottom: 8px;
        }
        .ada-taarifa-table .at-line {
            margin: 0 0 8px 0;
            line-height: 1.55;
            color: #333;
        }
        .ada-taarifa-table .at-dots {
            display: inline-block;
            border-bottom: 1px dotted #555;
            min-width: 55%;
            height: 12px;
            vertical-align: baseline;
            margin-left: 4px;
        }
        .ada-taarifa-table .at-uline {
            display: inline-block;
            border-bottom: 1px solid #555;
            min-width: 28%;
            height: 12px;
            vertical-align: baseline;
            margin: 0 2px;
        }

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

        .sig-table { width: 100%; margin-top: 18px; border-collapse: collapse; page-break-inside: avoid; table-layout: fixed; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; width: 50%; }
        .stamp-cell { text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 120px; max-height: 120px; opacity: 0.9; }
        .sign-cell { text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 48px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 160px; max-height: 48px; }
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

        {{-- HEADER (Printed for each student) — org logo top-center, then school row --}}
        <div class="header">
            @if($orgLogoBase64)
                <div class="org-logo-center">
                    <img src="{{ $orgLogoBase64 }}" alt="Organization Logo">
                </div>
            @endif
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @if($schoolLogoBase64)
                            <img class="school-logo" src="{{ $schoolLogoBase64 }}" alt="Logo">
                        @endif
                    </td>
                    <td class="school-name-cell">
                        @if($orgName)
                            <div class="org-name">{{ $orgName }}</div>
                        @endif
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
                <div class="remark-color-legend-title">Maelezo ya Maoni</div>
                @if($isSecondaryPdf)
                <div class="remark-color-blocks secondary-legend">
                    <span class="remark-color-block remark-excellent">A - Bora sana</span>
                    <span class="remark-color-block remark-pass">B - Nzuri sana</span>
                    <span class="remark-color-block remark-good">C - Nzuri</span>
                    <span class="remark-color-block remark-avg">D - Inaridhisha</span>
                    @if($isALevelPdf)
                        <span class="remark-color-block remark-avg">E - Tosheleza</span>
                        <span class="remark-color-block remark-avg">S - Subsidiary</span>
                    @endif
                    <span class="remark-color-block remark-fail">F - Mbaya</span>
                    <span class="remark-color-block remark-inc">Haijakamilika</span>
                </div>
                @else
                <div class="remark-color-blocks">
                    <span class="remark-color-block remark-pass">Amefaulu</span>
                    <span class="remark-color-block remark-fail">Mbaya</span>
                    <span class="remark-color-block remark-inc">Haijakamilika</span>
                </div>
                @endif
            </div>
            <div class="section-title">
                {{ ($filters['type'] ?? '') === 'report' ? 'Maendeleo Kitaaluma' : 'Maelezo ya Matokeo' }}
            </div>
            @php
                $isTermReportPdf = ($filters['type'] ?? '') === 'report';
                $termExamCols = [];
                if ($isTermReportPdf) {
                    // Prefer ordered exams list from API
                    if (!empty($detailedSingleData['exams']) && is_array($detailedSingleData['exams'])) {
                        foreach ($detailedSingleData['exams'] as $exMeta) {
                            $termExamCols[] = [
                                'examID' => $exMeta['examID'] ?? null,
                                'exam_name' => $exMeta['exam_name'] ?? 'Exam',
                            ];
                        }
                    }
                }
                // CA / multi-assessment on exam detail OR term report fallback
                if (empty($termExamCols)) {
                    $seenNames = [];
                    foreach ($detailedSingleData['subjects'] as $s) {
                        if (empty($s['exams']) || !is_array($s['exams'])) {
                            continue;
                        }
                        foreach ($s['exams'] as $exKey => $ex) {
                            $eName = $ex['exam_name'] ?? 'Exam';
                            if (isset($seenNames[$eName])) {
                                continue;
                            }
                            $seenNames[$eName] = true;
                            $termExamCols[] = [
                                'examID' => is_numeric($exKey) ? (int) $exKey : ($ex['examID'] ?? null),
                                'exam_name' => $eName,
                            ];
                        }
                    }
                }
                $useAssessmentCard = !empty($termExamCols);
            @endphp

            @if($useAssessmentCard)
                {{-- Term report card: Subject | each exam Marks+Grade | Total | Average | Grade | Remark --}}
                <table class="data term-report-card">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 18%;">Somo</th>
                            @foreach($termExamCols as $exCol)
                                <th class="exam-group" colspan="2">{{ $exCol['exam_name'] }}</th>
                            @endforeach
                            <th rowspan="2" class="tc" style="width: 7%;">Jumla</th>
                            <th rowspan="2" class="tc" style="width: 7%;">Wastani</th>
                            <th rowspan="2" class="tc" style="width: 7%;">Daraja</th>
                            <th rowspan="2" class="tc" style="width: 12%;">Maoni</th>
                        </tr>
                        <tr>
                            @foreach($termExamCols as $exCol)
                                <th class="exam-sub">Alama</th>
                                <th class="exam-sub">Daraja</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detailedSingleData['subjects'] as $i => $subj)
                            @php
                                $isIncompleteSubj = !empty($subj['is_incomplete']) || strtoupper(trim((string)($subj['grade'] ?? ''))) === 'INCOMPLETE';
                                $subjGradeRaw = $isIncompleteSubj ? 'Incomplete' : ($subj['grade'] ?? 'N/A');
                                $subjGrade = pdfDisplayGrade($subjGradeRaw);
                                $remarkInfo = getSubjectRemarkInfo($subjGradeRaw, $isSecondaryPdf, $isALevelPdf);
                                $subjExams = $subj['exams'] ?? [];
                                $rowTotal = 0;
                                $rowCount = 0;
                            @endphp
                            <tr class="{{ $i % 2 == 0 ? '' : 'even' }}">
                                <td class="subj-name">
                                    {{ $subj['subject_name'] }}
                                    @if(!empty($subj['is_duo']) && !empty($subj['papers']))
                                        <div style="font-size:9px; color:#555; margin-top:2px;">
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
                                @foreach($termExamCols as $exCol)
                                    @php
                                        $exResult = null;
                                        $wantId = $exCol['examID'] ?? null;
                                        $wantName = $exCol['exam_name'] ?? '';
                                        if (is_array($subjExams)) {
                                            if ($wantId !== null && isset($subjExams[$wantId])) {
                                                $exResult = $subjExams[$wantId];
                                            } elseif ($wantId !== null && isset($subjExams[(string) $wantId])) {
                                                $exResult = $subjExams[(string) $wantId];
                                            } else {
                                                foreach ($subjExams as $ex) {
                                                    if (($ex['exam_name'] ?? '') === $wantName) {
                                                        $exResult = $ex;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        $exMarks = null;
                                        $exGrade = '—';
                                        if ($exResult && isset($exResult['marks']) && $exResult['marks'] !== null && $exResult['marks'] !== '') {
                                            $exMarks = (float) $exResult['marks'];
                                            $rowTotal += $exMarks;
                                            $rowCount++;
                                            $exGrade = pdfDisplayGrade($exResult['grade'] ?? '—');
                                        } elseif ($exResult && !empty($exResult['grade'])) {
                                            $exGrade = pdfDisplayGrade($exResult['grade']);
                                        }
                                    @endphp
                                    <td class="exam-marks">{{ $exMarks !== null ? number_format($exMarks, 0) : '—' }}</td>
                                    <td class="exam-grade">{{ $exGrade }}</td>
                                @endforeach
                                <td class="tc bold">
                                    @if($isIncompleteSubj || $rowCount === 0)
                                        —
                                    @else
                                        {{ number_format($rowTotal, 0) }}
                                    @endif
                                </td>
                                <td class="tc bold">
                                    @if($isIncompleteSubj)
                                        —
                                    @elseif(isset($subj['average']) && $subj['average'] !== null && $subj['average'] !== '')
                                        {{ number_format((float)$subj['average'], 0) }}
                                    @elseif($rowCount > 0)
                                        {{ number_format($rowTotal / $rowCount, 0) }}
                                    @elseif(isset($subj['marks']) && $subj['marks'] !== null && $subj['marks'] !== '')
                                        {{ number_format((float)$subj['marks'], 0) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="daraja-cell">{{ $subjGrade }}</td>
                                <td class="tc remark-cell {{ $remarkInfo['class'] }}">{{ $remarkInfo['text'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Summary: layout kama ripoti — mchepuo (subclass) + kidato (main class) --}}
                @php
                    $sumDivisionFull = (string) ($detailedSingleData['division'] ?? '');
                    $sumDivisionRoman = $sumDivisionFull;
                    if (preg_match('/^([0IVX]+)/i', $sumDivisionFull, $dm)) {
                        $sumDivisionRoman = strtoupper($dm[1]);
                    }
                    $sumGradeLetter = $detailedSingleData['gradeLetter']
                        ?? $detailedSingleData['grade']
                        ?? '—';
                    if ($isSecondaryPdf && preg_match('/^[0IVX]/i', (string) $sumGradeLetter)) {
                        $sumGradeLetter = '—';
                    }
                    $mchepuoPos = $detailedSingleData['subclassPosition'] ?? $detailedSingleData['position'] ?? '—';
                    $mchepuoTotal = $detailedSingleData['subclassTotal'] ?? $detailedSingleData['totalStudentsCount'] ?? '—';
                    $kidatoPos = $detailedSingleData['classPosition'] ?? $detailedSingleData['position'] ?? '—';
                    $kidatoTotal = $detailedSingleData['classTotal'] ?? $detailedSingleData['totalStudentsCount'] ?? '—';
                @endphp
                <table class="data summary-grid">
                    <tr>
                        <td>
                            <span class="sg-k">Jumla ya Alama</span> :
                            <span class="sg-v">{{ number_format($detailedSingleData['totalMarks'] ?? 0, 1) }}</span>
                        </td>
                        <td>
                            <span class="sg-k">Wastani</span> :
                            <span class="sg-v">{{ number_format($detailedSingleData['averageMarks'] ?? 0, 0) }}</span>
                        </td>
                        <td>
                            <span class="sg-k">Daraja</span> :
                            <span class="sg-v">{{ $sumGradeLetter }}</span>
                        </td>
                        <td>
                            <span class="sg-k">Nafasi (Mchepuo)</span> :
                            <span class="sg-v">{{ $mchepuoPos }}</span>
                        </td>
                        <td>
                            <span class="sg-k">Kati ya Wanafunzi</span> :
                            <span class="sg-v">{{ $mchepuoTotal }}</span>
                        </td>
                    </tr>
                    <tr>
                        @if($isSecondaryPdf)
                            <td>
                                <span class="sg-k">Daraja</span> :
                                <span class="sg-v">{{ $sumDivisionRoman !== '' ? $sumDivisionRoman : '—' }}</span>
                            </td>
                            <td>
                                <span class="sg-k">Pointi</span> :
                                <span class="sg-v">{{ $detailedSingleData['totalPoints'] ?? '—' }}</span>
                            </td>
                        @else
                            <td>
                                <span class="sg-k">Daraja</span> :
                                <span class="sg-v">{{ $detailedSingleData['grade'] ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="sg-k">Masomo</span> :
                                <span class="sg-v">{{ $detailedSingleData['subjectCount'] ?? count($detailedSingleData['subjects'] ?? []) }}</span>
                            </td>
                        @endif
                        <td>
                            @if($isSecondaryPdf)
                                <span class="sg-k">Masomo</span> :
                                <span class="sg-v">{{ $detailedSingleData['subjectCount'] ?? count($detailedSingleData['subjects'] ?? []) }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="sg-k">Nafasi (Kidato)</span> :
                            <span class="sg-v">{{ $kidatoPos }}</span>
                        </td>
                        <td>
                            <span class="sg-k">Kati ya Wanafunzi</span> :
                            <span class="sg-v">{{ $kidatoTotal }}</span>
                        </td>
                    </tr>
                </table>

                {{-- Grade key — colspan/rowspan layout kama report card --}}
                @if($isSecondaryPdf)
                    @php
                        if ($isALevelPdf) {
                            $gradeKeyRows = [
                                ['A', '80–100', '1', 'Bora sana', 'I', '3–9', 'A', 'Bora sana'],
                                ['B', '70–79', '2', 'Nzuri sana', 'II', '10–12', 'B', 'Nzuri sana'],
                                ['C', '60–69', '3', 'Nzuri', 'III', '13–17', 'C', 'Nzuri'],
                                ['D', '50–59', '4', 'Inaridhisha', 'IV', '18–19', 'D', 'Inaridhisha'],
                                ['E', '40–49', '5', 'Tosheleza', '0', '20–21', 'F', 'Mbaya'],
                                ['S', '35–39', '6', 'Subsidiary', '—', '—', '—', '—'],
                                ['F', '0–34', '7', 'Mbaya', '—', '—', '—', '—'],
                            ];
                        } else {
                            $gradeKeyRows = [
                                ['A', '75–100', '1', 'Bora sana', 'I', '7–17', 'A', 'Bora sana'],
                                ['B', '65–74', '2', 'Nzuri sana', 'II', '18–21', 'B', 'Nzuri sana'],
                                ['C', '45–64', '3', 'Nzuri', 'III', '22–25', 'C', 'Nzuri'],
                                ['D', '30–44', '4', 'Inaridhisha', 'IV', '26–33', 'D', 'Inaridhisha'],
                                ['F', '0–29', '5', 'Mbaya', '0', '34–35', 'F', 'Mbaya'],
                            ];
                        }
                    @endphp
                    <table class="data term-report-card" style="margin-top:10px;">
                        <thead>
                            <tr>
                                <th class="exam-group" colspan="8">Maelezo ya Daraja</th>
                            </tr>
                            <tr>
                                <th class="exam-group" colspan="6">Masomo</th>
                                <th class="exam-group" colspan="2">Tabia na Mwenendo</th>
                            </tr>
                            <tr>
                                <th class="exam-sub">Gredi</th>
                                <th class="exam-sub">Alama</th>
                                <th class="exam-sub">Uzito wa Gredi Pointi</th>
                                <th class="exam-sub">Maoni</th>
                                <th class="exam-sub">Daraja</th>
                                <th class="exam-sub">Pointi</th>
                                <th class="exam-sub">Gredi</th>
                                <th class="exam-sub">Maoni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gradeKeyRows as $ki => $gRow)
                                <tr class="{{ $ki % 2 == 0 ? '' : 'even' }}">
                                    <td class="tc bold">{{ $gRow[0] }}</td>
                                    <td class="tc">{{ $gRow[1] }}</td>
                                    <td class="tc">{{ $gRow[2] }}</td>
                                    <td class="tc">{{ $gRow[3] }}</td>
                                    <td class="tc bold">{{ $gRow[4] }}</td>
                                    <td class="tc">{{ $gRow[5] }}</td>
                                    <td class="tc bold">{{ $gRow[6] }}</td>
                                    <td class="tc">{{ $gRow[7] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @else
                {{-- Exam (simple, no CA breakdown) — existing layout --}}
                <table class="data">
                    <thead>
                        <tr>
                            <th>Somo</th>
                            @php
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
                            @endphp

                            @if($hasBreakdown)
                                @foreach($examHeaders as $h)
                                    <th class="tc">{{ $h }}</th>
                                @endforeach
                            @endif
                            <th class="tc">Wastani</th>
                            <th class="tc">{{ $isSecondaryPdf ? 'Daraja' : 'Daraja' }}</th>
                            <th class="tc">Maoni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detailedSingleData['subjects'] as $i => $subj)
                            @php
                                $isIncompleteSubj = !empty($subj['is_incomplete']) || strtoupper(trim((string)($subj['grade'] ?? ''))) === 'INCOMPLETE';
                                $subjGradeRaw = $isIncompleteSubj ? 'Incomplete' : ($subj['grade'] ?? 'N/A');
                                $subjGrade = pdfDisplayGrade($subjGradeRaw);
                                $remarkInfo = getSubjectRemarkInfo($subjGradeRaw, $isSecondaryPdf, $isALevelPdf);
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
                                                    <small class="badge-pale" style="padding:0 2px; border:none; background:none;">({{ pdfDisplayGrade($exResult['grade']) }})</small>
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
                                <td class="daraja-cell">{{ $subjGrade }}</td>
                                <td class="tc remark-cell {{ $remarkInfo['class'] }}">{{ $remarkInfo['text'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="result-bar">
                    <strong>Jumla:</strong> {{ number_format($detailedSingleData['totalMarks'] ?? 0, 0) }} &nbsp;|&nbsp;
                    <strong>Wastani:</strong> {{ number_format($detailedSingleData['averageMarks'] ?? 0, 0) }} &nbsp;|&nbsp;
                    <strong>Daraja:</strong> {{ $detailedSingleData['grade'] ?? 'N/A' }}
                    @if(isset($detailedSingleData['division']))
                        &nbsp;|&nbsp; <strong>Divisheni:</strong> {{ $detailedSingleData['division'] }}
                    @endif
                    @if(isset($detailedSingleData['position']))
                        &nbsp;|&nbsp; <strong>Nafasi:</strong> {{ $detailedSingleData['position'] }} / {{ $detailedSingleData['totalStudentsCount'] ?? 0 }}
                    @endif
                </div>
            @endif

            @if(isset($detailedSingleData['remarks']))
            <div style="margin-top: 10px; border: 1px dashed #e0cdcd; padding: 6px; font-size: 14px;">
                <strong>Maoni:</strong> {{ $detailedSingleData['remarks'] }}
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
                <div class="remark-color-legend-title">Maelezo ya Maoni</div>
                @if($isSecondaryExamPdf)
                <div class="remark-color-blocks secondary-legend">
                    <span class="remark-color-block remark-excellent">A - Bora sana</span>
                    <span class="remark-color-block remark-pass">B - Nzuri sana</span>
                    <span class="remark-color-block remark-good">C - Nzuri</span>
                    <span class="remark-color-block remark-avg">D - Inaridhisha</span>
                    @if($isALevelExamPdf)
                        <span class="remark-color-block remark-avg">E - Tosheleza</span>
                        <span class="remark-color-block remark-avg">S - Subsidiary</span>
                    @endif
                    <span class="remark-color-block remark-fail">F - Mbaya</span>
                    <span class="remark-color-block remark-inc">Haijakamilika</span>
                </div>
                @else
                <div class="remark-color-blocks">
                    <span class="remark-color-block remark-pass">Amefaulu</span>
                    <span class="remark-color-block remark-fail">Mbaya</span>
                    <span class="remark-color-block remark-inc">Haijakamilika</span>
                </div>
                @endif
            </div>
            @foreach($result as $examResult)
                <div class="section-title">{{ $examResult['exam']->exam_name ?? 'N/A' }} - {{ $examResult['exam']->start_date ?? '' }}</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>Somo</th>
                            <th class="tc">Alama</th>
                            <th class="tc">{{ $isSecondaryExamPdf ? 'Daraja' : 'Daraja' }}</th>
                            <th class="tc">Maoni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($examResult['subjects'] as $i => $subject)
                            @php
                                $examSubjGradeRaw = $subject['grade'] ?? 'N/A';
                                $examSubjGrade = pdfDisplayGrade($examSubjGradeRaw);
                                $examRemarkInfo = getSubjectRemarkInfo($examSubjGradeRaw, $isSecondaryExamPdf, $isALevelExamPdf);
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
                                            <div><strong>Jumla: {{ isset($subject['marks']) ? number_format((float)$subject['marks'], 0) : 'N/A' }}%</strong></div>
                                        </div>
                                    @endif
                                </td>
                                <td class="tc bold">{{ isset($subject['marks']) ? number_format((float)$subject['marks'], 0) : 'N/A' }}</td>
                                <td class="daraja-cell">{{ $examSubjGrade }}</td>
                                <td class="tc remark-cell {{ $examRemarkInfo['class'] }}">{{ $examRemarkInfo['text'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="result-bar">
                    <strong>Jumla:</strong> {{ number_format($examResult['total_marks'], 0) }} &nbsp;|&nbsp;
                    <strong>Wastani:</strong> {{ number_format($examResult['average_marks'], 0) }} &nbsp;|&nbsp;
                    <strong>Daraja:</strong> {{ $examResult['grade'] ?? 'N/A' }} &nbsp;|&nbsp;
                    <strong>Divisheni:</strong> {{ $examResult['division'] ?? 'N/A' }}
                </div>
            @endforeach
        @endif
    @else
        <p style="color:#940000; font-style:italic; margin-top:10px;">No result data found for this student.</p>
    @endif

    {{-- Character & Behavioral Assessment --}}
    @if(isset($characterAssessments) && isset($characterAssessments[$student->studentID]) && count($characterAssessments[$student->studentID]) > 0)
        <div class="section-title">Tathmini ya Tabia na Mwenendo</div>
        <table class="data character-assessment-table" style="margin-bottom: 45px;">
            <thead>
                <tr>
                    <th style="width: 40%;">Kategoria</th>
                    <th style="width: 60%;" class="tc">Alama / Chaguo</th>
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

    {{-- ADA + TAARIFA (kama ripoti ya shule; ada/madeni kutoka view_payments) --}}
    @php
        $feeSum = ($studentFeeSummaries ?? [])[$student->studentID] ?? null;
        $hasFeeBill = !empty($feeSum['has_bill']);
        $feeMadeni = $hasFeeBill ? (float) ($feeSum['madeni'] ?? 0) : null;
        $feeAda = $hasFeeBill ? (float) ($feeSum['ada'] ?? 0) : null;
    @endphp
    <table class="ada-taarifa-table">
        <tr>
            <td>
                <div class="at-head">Ada</div>
                <div class="at-line">
                    (i) Madeni ya mwanafunzi ni
                    @if($feeMadeni !== null)
                        <strong>Tsh {{ number_format($feeMadeni, 0) }}</strong>
                    @else
                        <span class="at-dots"></span>
                    @endif
                </div>
                <div class="at-line">
                    (ii) Ada ni Tsh
                    @if($feeAda !== null)
                        <strong>{{ number_format($feeAda, 0) }}</strong>
                    @else
                        <span class="at-dots"></span>
                    @endif
                </div>
            </td>
            <td>
                <div class="at-head">Taarifa</div>
                <div class="at-line">
                    (i) Tarehe ya kufunga shule ni <span class="at-uline"></span>
                    siku ya <span class="at-uline"></span>
                </div>
                <div class="at-line">
                    (ii) Tarehe ya kufungua shule ni <span class="at-uline"></span>
                    siku ya <span class="at-uline"></span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Maoni + Sahihi: kila mtu kwenye row yake yenye border --}}
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

    <table class="official-remarks-table">
        {{-- 1. Mwalimu wa Darasa — kwanza --}}
        <tr>
            <td style="width:62%;">
                <div class="orm-title-dark">Maoni ya Mwalimu wa Darasa</div>
                <div class="orm-body">{{ $teacherComment }}</div>
                <div class="orm-meta">Jina: {{ $teacherName }}</div>
            </td>
            <td style="width:38%;">
                <div class="orm-title-dark">Sahihi ya Mwalimu wa Darasa</div>
                <div style="min-height:36px; margin-bottom:4px;">
                    @if($teacherSignBase64)
                        <img src="{{ $teacherSignBase64 }}" alt="Sahihi" style="max-height:34px; max-width:140px;">
                    @endif
                </div>
                <div class="sig-dot-line"></div>
            </td>
        </tr>
        {{-- 2. Mkuu wa Shule — mwisho --}}
        <tr>
            <td>
                <div class="orm-title">Maoni ya Mkuu wa Shule</div>
                <div class="dotted-lines">
                    <div class="dot-line"></div>
                    <div class="dot-line"></div>
                    <div class="dot-line"></div>
                </div>
            </td>
            <td>
                <div class="orm-title">Sahihi</div>
                <div style="min-height:42px; margin-bottom:4px;">
                    @if($schoolSignBase64)
                        <img src="{{ $schoolSignBase64 }}" alt="Sahihi" style="max-height:38px; max-width:150px;">
                    @endif
                </div>
                <div class="sig-dot-line"></div>
                <div class="orm-sign-label">Mkuu wa Shule</div>
            </td>
        </tr>
    </table>

    {{-- Stamp (left) + Signature (right) — both must show on individual PDF --}}
    @if($schoolStampBase64 || $schoolSignBase64)
    <table class="sig-table">
        <tr>
            <td class="stamp-cell">
                @if($schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}" alt="Muhuri">
                @endif
            </td>
            <td class="sign-cell">
                @if($schoolSignBase64)
                    <div class="school-signature-img">
                        <img src="{{ $schoolSignBase64 }}" alt="Sahihi">
                    </div>
                @endif
                <span class="sig-line"></span>
                <span class="sig-label">Mkuu wa Shule</span>
            </td>
        </tr>
    </table>
    @endif

    @php $idxCount++; @endphp
    @if($idxCount < $studentsToIterate->count())
        <div style="page-break-after: always;"></div>
    @endif
@endforeach

{{-- ====================================================
     CLASS / SUBCLASS / ALL STUDENTS — Excel-like board (landscape)
==================================================== --}}
@elseif(!empty($useBoardPdf) && !empty($boardClasses))
    @include('Admin.pdf.partials.results_board')
@else
    <div class="header">
        @if(!empty($orgLogoBase64))
            <img class="org-logo-top" src="{{ $orgLogoBase64 }}" alt="Organization Logo">
        @endif
        @if(!empty($schoolLogoBase64))
            <img class="school-logo-top" src="{{ $schoolLogoBase64 }}" alt="Logo">
        @endif
        @if(!empty($orgName))
            <div class="org-name">{{ $orgName }}</div>
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div class="report-title">{{ $title ?? 'Results' }}</div>
    </div>
    <p style="text-align:center;color:#666;">No board results available for the selected filters.</p>

@endif
<div class="footer">
    Generated by <span class="brand">ShuleXpert</span> (school management system), powered by emca techonology
</div>

</body>
</html>
