{{-- Excel-like class board (landscape). Styles extend results.blade.php --}}
@php
    if (!function_exists('pdfBoardShortLabel')) {
        /** Compact vertical header: width/height follow this text, not the full subject name. */
        function pdfBoardShortLabel(?string $text): string {
            $text = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $text)));
            if ($text === '') {
                return '';
            }
            $map = [
                'ACADEMIC COMMUNICATION' => 'ACAD/COM',
                'A/MATHS' => 'ADV/MATHS',
                'A-MATHS' => 'ADV/MATHS',
                'ADVANCED MATHEMATICS' => 'ADV/MATHS',
                'BASIC APPLIED MATHEMATICS' => 'BAM',
                'COMPUTER SCIENCE' => 'COMP/SCI',
                'ENGLISH LANGUAGE' => 'ENG/LANG',
                'GENERAL STUDIES' => 'GS',
                'ISLAMIC KNOWLEDGE' => 'IS/KNOW',
                'LITERATURE IN ENGLISH' => 'LIT/ENG',
                'DEVELOPMENT STUDIES' => 'DEV/STU',
            ];
            if (isset($map[$text])) {
                return $map[$text];
            }
            if (str_contains($text, 'COMMUNICATION')) {
                return 'ACAD/COM';
            }
            if (str_contains($text, 'MATH') && (str_contains($text, 'ADV') || str_contains($text, 'A/') || str_contains($text, 'A-'))) {
                return 'ADV/MATHS';
            }
            if (str_contains($text, 'COMPUTER')) {
                return 'COMP/SCI';
            }
            if (str_contains($text, 'ENGLISH') && str_contains($text, 'LANG')) {
                return 'ENG/LANG';
            }
            if (str_contains($text, 'GEOGRAPH')) {
                return 'GEOG';
            }
            if (str_contains($text, 'BIOLOG')) {
                return 'BIOLOGY';
            }
            if (str_contains($text, 'CHEMIST')) {
                return 'CHEM';
            }
            if (str_contains($text, 'PHYSIC')) {
                return 'PHY';
            }
            if (str_contains($text, 'KISWAH')) {
                return 'KISW';
            }
            if (str_contains($text, 'HISTOR')) {
                return 'HIST';
            }
            if (str_contains($text, 'ECONOM')) {
                return 'ECON';
            }
            if (mb_strlen($text, 'UTF-8') <= 11) {
                return $text;
            }
            $parts = explode(' ', $text);
            if (count($parts) >= 2) {
                return mb_substr($parts[0], 0, 4, 'UTF-8') . '/' . mb_substr($parts[count($parts) - 1], 0, 4, 'UTF-8');
            }

            return mb_substr($text, 0, 10, 'UTF-8');
        }
    }
    if (!function_exists('pdfBoardVerticalLabel')) {
        /** Excel-style vertical header, centred in cell (DomPDF-safe) */
        function pdfBoardVerticalLabel(?string $text): string {
            $text = pdfBoardShortLabel($text);
            if ($text === '') {
                return '';
            }
            $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            return '<table class="v-inner"><tr><td>'
                . '<div class="v-box"><div class="v-rot">' . $safe . '</div></div>'
                . '</td></tr></table>';
        }
    }
    if (!function_exists('pdfBoardMaxLen')) {
        function pdfBoardMaxLen(int $current, $value): int {
            return max($current, mb_strlen(trim((string) $value), 'UTF-8'));
        }
    }
    if (!function_exists('pdfBoardDataColPct')) {
        /**
         * Column % from longest DATA string at a given font (Helvetica-ish).
         * 78.907 at 14px must get a cell wide enough for every glyph + padding.
         */
        function pdfBoardDataColPct(int $len, float $fontPx = 14.0, float $padPx = 6.0): float {
            $len = max(1, $len);
            $px = ($len * $fontPx * 0.56) + $padPx;
            $tablePx = 810.0;

            return round(max(2.0, ($px / $tablePx) * 100), 2);
        }
    }
@endphp
<style>
    .board-wrap { page-break-after: always; }
    .board-wrap:last-child,
    .board-wrap.board-wrap-last { page-break-after: auto; }

    /* Header: compact centred block — logos sit beside titles, not page edges */
    .board-page-header {
        margin: 0 0 6px 0;
        border-bottom: 2px solid #940000;
        padding-bottom: 6px;
        text-align: center;
    }
    .board-header-inner {
        width: auto;
        margin: 0 auto;
        border-collapse: collapse;
    }
    .board-header-inner td {
        border: none !important;
        padding: 0;
        vertical-align: middle;
        background: transparent;
    }
    .board-logo-side {
        width: 70px;
        vertical-align: middle !important;
    }
    .board-logo-side img {
        display: block;
        height: 70px;
        width: auto;
        max-width: 74px;
        max-height: 70px;
        margin: 0 auto;
        border: none;
        outline: none;
    }
    .board-logo-side.left { text-align: right; padding-right: 10px; }
    .board-logo-side.right { text-align: left; padding-left: 10px; }
    .board-header-center { text-align: center; padding: 0 8px; white-space: nowrap; }
    .board-header-center .org-name {
        font-size: 11px;
        font-weight: bold;
        color: #333 !important;
        text-transform: uppercase;
        margin-bottom: 1px;
        line-height: 1.2;
    }
    .board-header-center .school-name {
        font-size: 16px;
        font-weight: bold;
        color: #940000 !important;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .board-header-center .school-sub {
        font-size: 9px;
        color: #444 !important;
        margin: 1px 0;
        line-height: 1.2;
    }
    .board-report-title {
        font-size: 11px;
        font-weight: bold;
        margin-top: 6px;
        background: #f8f9fa;
        padding: 4px 8px;
        color: #940000;
        text-transform: uppercase;
        text-align: center;
        border: none;
    }
    .board-report-subtitle {
        font-size: 10px;
        font-weight: bold;
        margin-top: 2px;
        color: #555;
        text-transform: uppercase;
        text-align: center;
    }
    .board-info-section { margin: 4px 0 6px 0; }
    .board-info-table {
        width: 100%;
        border-collapse: collapse;
    }
    .board-info-table td {
        padding: 5px 8px;
        border: none;
        border-bottom: 1px solid #eee;
        font-size: 10px;
        text-align: left;
        vertical-align: middle;
        background: transparent;
    }
    .board-info-table td.label {
        font-weight: bold;
        color: #555;
        width: 16%;
    }
    table.board-perf > thead {
        display: table-row-group;
    }
    table.board-perf {
        width: 100%;
        margin: 0 0 8px 0;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 14px;
    }
    table.board-perf th, table.board-perf td {
        border: 1px solid #bbb;
        padding: 4px 6px;
        text-align: center;
        vertical-align: middle;
    }
    table.board-perf th {
        background: #f0f0f0;
        color: #940000;
        font-weight: bold;
        font-size: 14px;
    }
    table.board-perf td.gpa-cell {
        font-weight: bold;
        color: #940000;
        vertical-align: middle;
        font-size: 14px;
    }
    table.board-results {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 14px;
        margin-bottom: 0;
    }
    table.board-results th, table.board-results td {
        border: 1px solid #bbb;
        padding: 1px 1px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        word-wrap: normal;
        overflow: hidden;
        font-size: 14px;
        line-height: 1.15;
    }
    table.board-results thead th {
        overflow: visible;
    }
    /* Do NOT repeat column titles on following pages â€” first page only */
    table.board-results tr.board-col-sync th {
        height: 0 !important;
        max-height: 0 !important;
        padding: 0 !important;
        border: none !important;
        font-size: 0 !important;
        line-height: 0 !important;
        overflow: hidden !important;
        color: transparent !important;
    }
    table.board-results > thead {
        display: table-row-group;
    }
    /* Keep legend/stats in-flow under students */
    table.board-results tr.board-cont-row td.board-cont-cell {
        border: none !important;
        padding: 2px 0 0 0 !important;
        margin: 0;
        text-align: left;
        vertical-align: top;
        background: #fff !important;
        overflow: visible;
    }
    table.board-results tr.board-cont-row {
        page-break-before: auto;
        page-break-inside: auto;
        page-break-after: auto;
    }
    table.board-results thead th {
        background: #f5f5f5;
        color: #940000;
        font-weight: bold;
        font-size: 14px;
    }
    /* Vertical titles: height comes from longest short-label (per-board CSS) */
    table.board-results th.v,
    table.board-results th.v-subj,
    table.board-results th.v-stream,
    table.board-results th.v-trail {
        padding: 2px 0;
        vertical-align: middle;
        text-align: center;
        overflow: visible;
    }
    table.board-results th.v-subj { width: auto; }

    table.board-results table.v-inner {
        width: 100%;
        border-collapse: collapse;
        margin: 0 auto;
    }
    table.board-results table.v-inner td {
        border: none !important;
        padding: 0 !important;
        margin: 0;
        text-align: center;
        vertical-align: middle;
        background: transparent;
        overflow: visible;
    }
    table.board-results table.h-inner {
        width: 100%;
        height: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    table.board-results table.h-inner td {
        border: none !important;
        padding: 0 2px !important;
        text-align: center;
        vertical-align: middle;
        background: transparent;
        color: #940000 !important;
        font-weight: bold;
        font-size: 14px;
    }
    table.board-results .v-box {
        width: 100%;
        margin: 0 auto;
        position: relative;
        overflow: hidden;
    }
    table.board-results .v-rot {
        position: absolute;
        top: 50%;
        left: 50%;
        bottom: auto;
        white-space: nowrap;
        font-size: 12px;
        font-weight: bold;
        color: #940000 !important;
        line-height: 1.15;
        text-align: center;
        letter-spacing: 0.2px;
        transform: rotate(-90deg);
        transform-origin: center center;
    }
    table.board-results th.sn-h,
    table.board-results th.name-h,
    table.board-results th.index-h {
        vertical-align: middle;
        text-align: center;
        font-size: 14px;
    }
    table.board-results td {
        vertical-align: middle;
        font-size: 14px;
        padding: 1px 1px;
    }
    table.board-results th.name-h {
        white-space: normal;
        word-wrap: break-word;
        overflow: hidden;
        line-height: 1.15;
    }
    table.board-results td.name-cell {
        text-align: left !important;
        padding-left: 2px !important;
        padding-right: 2px !important;
        font-weight: bold;
        font-size: 12px;
        line-height: 1.1;
        vertical-align: middle;
        white-space: nowrap !important;
        word-wrap: normal !important;
        overflow: hidden;
    }
    table.board-results td.name-cell .name-keep {
        display: block;
        text-align: left !important;
        width: 100%;
    }
    table.board-results td.stream-cell {
        font-size: 11px;
        font-weight: bold;
        line-height: 1.1;
        padding: 1px 2px;
        text-align: center;
        white-space: nowrap !important;
        overflow: hidden !important;
    }
    table.board-results td.stream-cell .stream-keep {
        white-space: nowrap !important;
        display: inline-block;
        letter-spacing: 0;
        padding: 0 1px;
    }
    table.board-results td.board-num-cell {
        white-space: nowrap !important;
        overflow: hidden !important;
        font-size: 14px !important;
        line-height: 1.15 !important;
        padding: 1px 1px !important;
        text-align: center !important;
    }
    table.board-results td.board-num-cell.board-total-cell {
        font-size: 12px !important;
        padding: 1px 1px !important;
    }
    table.board-results td.board-num-cell .board-num-keep {
        white-space: nowrap !important;
        display: inline-block;
    }
    table.board-results td.empty-subj {
        background-color: #c5c5c5 !important;
        color: transparent !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    table.board-results td.board-pos-cell {
        white-space: nowrap !important;
        overflow: hidden !important;
        padding: 1px 1px !important;
        text-align: center !important;
    }
    table.board-results .sub-h {
        font-size: 5.5px;
        background: #fafafa;
        color: #555;
        vertical-align: middle;
        height: auto;
        padding: 2px 0;
    }
    /* Nested stats match board 14px type */
    table.board-results tr.board-cont-row td.board-cont-cell {
        font-size: 14px !important;
    }
    table.board-results .board-cont-cell .board-stats-title {
        font-size: 14px !important;
    }
    table.board-results .board-cont-cell table.board-subj-stats {
        font-size: 14px !important;
        table-layout: auto;
        width: 100%;
    }
    table.board-results .board-cont-cell table.board-subj-stats th,
    table.board-results .board-cont-cell table.board-subj-stats td {
        font-size: 14px !important;
        line-height: 1.2 !important;
        padding: 3px 2px !important;
        overflow: visible !important;
        word-wrap: break-word;
    }
    table.board-results .board-cont-cell table.board-subj-stats th.board-stats-banner {
        font-size: 14px !important;
    }
    table.board-results .board-cont-cell table.board-subj-stats td.subj-name {
        font-size: 14px !important;
    }
    table.board-results .board-cont-cell table.board-subj-stats.board-legend td,
    table.board-results .board-cont-cell .legend-row td {
        font-size: 14px !important;
    }

    .board-stats-title {
        font-size: 14px;
        font-weight: bold;
        color: #940000;
        margin: 2px 0 1px 0;
        border-bottom: 1px solid #940000;
        padding-bottom: 1px;
        text-transform: uppercase;
        page-break-after: avoid;
    }
    .board-stats-section {
        width: 100%;
        margin: 2px 0 0 0;
        page-break-inside: auto;
        page-break-before: auto;
    }
    table.board-subj-stats {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px !important;
        margin: 0;
        page-break-inside: auto;
    }
    table.board-subj-stats th, table.board-subj-stats td {
        border: 1px solid #ddd;
        padding: 3px 2px;
        text-align: center;
        vertical-align: middle;
        font-size: 14px !important;
        line-height: 1.2;
        overflow: visible;
    }
    table.board-subj-stats thead th {
        background: #f8f9fa;
        color: #940000;
        font-weight: bold;
        font-size: 14px !important;
    }
    table.board-subj-stats th.board-stats-banner {
        background: #940000 !important;
        color: #fff !important;
        font-size: 14px !important;
        text-transform: uppercase;
        padding: 4px 2px;
        text-align: center;
        border: 1px solid #940000;
    }
    table.board-subj-stats thead { display: table-row-group; }
    table.board-subj-stats tr { page-break-inside: auto; }
    table.board-subj-stats td.subj-name { text-align: left; font-weight: bold; padding-left: 4px; font-size: 14px !important; }
    table.board-subj-stats.board-legend { margin-bottom: 2px; }
    .legend-row td { font-weight: bold; font-size: 14px !important; padding: 3px; }
    .remark-excellent { background-color: #28a745 !important; color: #ffffff !important; }
    .remark-pass { background-color: #d4edda !important; color: #155724 !important; }
    .remark-good { background-color: #fff3cd !important; color: #856404 !important; }
    .remark-avg { background-color: #ffe8d6 !important; color: #a04000 !important; }
    .remark-fail { background-color: #f8d7da !important; color: #721c24 !important; }
    .remark-inc { background-color: #d1ecf1 !important; color: #0c5460 !important; }
    .legend-row td,
    table.board-subj-stats td.remark-cell,
    table.board-results td.remark-excellent,
    table.board-results td.remark-pass,
    table.board-results td.remark-good,
    table.board-results td.remark-avg,
    table.board-results td.remark-fail,
    table.board-results td.remark-inc {
        font-weight: bold;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Headmaster sign + stamp â€” side by side, no boxes */
    .board-head-official {
        width: 55%;
        margin: 6px 0 0 auto;
        border-collapse: collapse;
        page-break-inside: avoid;
    }
    .board-head-official td {
        border: none !important;
        background: transparent !important;
        vertical-align: bottom;
        text-align: center;
        padding: 0 10px;
    }
    .board-head-sign {
        text-align: center;
        padding: 0;
    }
    .board-head-sign img {
        max-width: 160px;
        max-height: 45px;
    }
    .board-head-sign .board-sig-line {
        display: block;
        border-bottom: 1px solid #333;
        width: 70%;
        margin: 2px auto 2px auto;
    }
    .board-head-sign .board-sig-label,
    .board-head-stamp .board-sig-label {
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
        color: #333;
        margin-top: 2px;
    }
    .board-head-stamp {
        text-align: center;
        padding: 0;
        margin: 0;
    }
    .board-head-stamp img {
        max-width: 140px;
        max-height: 140px;
        opacity: 0.95;
    }
</style>

@foreach($boardClasses as $boardIdx => $board)
    @php
        $isSecondary = ($board['schoolType'] ?? 'Secondary') === 'Secondary';
        $isALevel = !empty($board['isALevel']);
        $subjects = $board['subjects'] ?? [];
        $gradeKeys = $isSecondary
            ? ($isALevel ? ['A','B','C','D','E','S','F'] : ['A','B','C','D','F'])
            : ['A','B','C','D','E','F'];
        $ptMap = $isSecondary
            ? ($isALevel
                ? ['A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'S'=>6,'F'=>7]
                : ['A'=>1,'B'=>2,'C'=>3,'D'=>4,'F'=>5])
            : ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'F'=>0];

        $showNameCol = $board['showName'] ?? true;
        $showIndexCol = $board['showIndex'] ?? true;
        $showStreamCol = !empty($board['showStream']);
        $nSubj = count($subjects);
        $nTrail = count($board['trailingHeaders'] ?? []);

        $bodyFs = 14;
        $nameFs = 12;
        $vFont = 12;

        $vertLabels = ['SEX', 'POINT', 'DIVISION', 'TOTAL', 'AVERAGE', 'POSITION', 'GRADE'];
        $streamLabel = pdfBoardShortLabel($board['streamHeader'] ?? 'COMBINATION');
        if ($streamLabel !== '') {
            $vertLabels[] = $streamLabel;
        }
        foreach ($subjects as $subj) {
            $vertLabels[] = pdfBoardShortLabel($subj['subject_name'] ?? '');
        }
        $maxVertLen = 1;
        foreach ($vertLabels as $vl) {
            $maxVertLen = max($maxVertLen, mb_strlen($vl, 'UTF-8'));
        }
        // Header height from longest short label at 12px — not from full subject names
        $vHeaderH = (int) round($maxVertLen * 7.2 + 16);
        $vInnerH = max(1, $vHeaderH - 4);
        $vBoxH = max(1, $vHeaderH - 8);
        $vLine = 14;
        $vRotMarginLeft = (int) round($vBoxH / 2);
        $vRotMarginTop = 7;

        $rowsScan = $board['rows'] ?? [];
        $lenSn = 1;
        $lenName = 1;
        $lenIndex = 1;
        $lenSex = 1;
        $lenStream = 1;
        $lenPoint = 1;
        $lenDiv = 1;
        $lenTotal = 1;
        $lenAvg = 1;
        $lenPGrade = 1;
        $lenPos = 1;
        $lenMarks = array_fill(0, max(0, $nSubj), 1);
        $lenGrades = array_fill(0, max(0, $nSubj), 1);
        foreach ($rowsScan as $nr) {
            $lenSn = pdfBoardMaxLen($lenSn, $nr['sn'] ?? '');
            $lenName = pdfBoardMaxLen($lenName, $nr['name'] ?? '');
            $lenIndex = pdfBoardMaxLen($lenIndex, $nr['index'] ?? '');
            $lenSex = pdfBoardMaxLen($lenSex, $nr['sex'] ?? '');
            $lenStream = pdfBoardMaxLen($lenStream, preg_replace('/\s+/', '', (string) ($nr['stream'] ?? '')));
            $lenPoint = pdfBoardMaxLen($lenPoint, $nr['point'] ?? '');
            $lenDiv = pdfBoardMaxLen($lenDiv, $nr['division'] ?? '');
            $lenTotal = pdfBoardMaxLen($lenTotal, $nr['total'] ?? '');
            $lenAvg = pdfBoardMaxLen($lenAvg, $nr['average'] ?? '');
            $lenPGrade = pdfBoardMaxLen($lenPGrade, $nr['grade'] ?? '');
            $lenPos = pdfBoardMaxLen($lenPos, $nr['position'] ?? '');
            foreach ($nr['subjects'] ?? [] as $si => $cell) {
                if (!isset($lenMarks[$si])) {
                    $lenMarks[$si] = 1;
                    $lenGrades[$si] = 1;
                }
                $lenMarks[$si] = pdfBoardMaxLen($lenMarks[$si], $cell['marks'] ?? '');
                $lenGrades[$si] = pdfBoardMaxLen($lenGrades[$si], $cell['grade'] ?? '');
            }
        }

        $colW = [];
        $protect = [];
        $wStreamCol = 0;
        $wTotalCol = 0;
        $wAvgCol = 0;
        $wPosCol = 0;

        $colW[] = pdfBoardDataColPct($lenSn, 14, 6);
        $wName = 0;
        $nameColIdx = null;
        if ($showNameCol) {
            $nameColIdx = count($colW);
            $wName = pdfBoardDataColPct($lenName, 12, 6);
            $colW[] = $wName;
            $protect[] = $nameColIdx;
        }
        if ($showIndexCol) {
            $colW[] = pdfBoardDataColPct($lenIndex, 14, 6);
        }
        $colW[] = pdfBoardDataColPct($lenSex, 14, 6);
        if ($showStreamCol) {
            $protect[] = count($colW);
            $wStreamCol = pdfBoardDataColPct(max($lenStream, 3), 11, 6);
            $colW[] = $wStreamCol;
        }
        if ($isSecondary) {
            $colW[] = pdfBoardDataColPct(max($lenPoint, 2), 14, 6);
            $colW[] = pdfBoardDataColPct(max($lenDiv, 1), 14, 6);
        }
        for ($si = 0; $si < $nSubj; $si++) {
            $colW[] = pdfBoardDataColPct((int) ($lenMarks[$si] ?? 1), 14, 6);
            $colW[] = pdfBoardDataColPct((int) ($lenGrades[$si] ?? 1), 14, 6);
        }
        foreach ($board['trailingHeaders'] ?? [] as $th) {
            $thUp = strtoupper(trim((string) $th));
            $idx = count($colW);
            if ($thUp === 'TOTAL') {
                $protect[] = $idx;
                $wTotalCol = pdfBoardDataColPct(max($lenTotal, 3), 12, 6);
                $colW[] = $wTotalCol;
            } elseif ($thUp === 'AVERAGE') {
                $protect[] = $idx;
                $wAvgCol = pdfBoardDataColPct(max($lenAvg, 5), 14, 6);
                $colW[] = $wAvgCol;
            } elseif ($thUp === 'GRADE') {
                $colW[] = pdfBoardDataColPct($lenPGrade, 14, 6);
            } else {
                $protect[] = $idx;
                $wPosCol = pdfBoardDataColPct(max($lenPos, 2), 14, 6);
                $colW[] = $wPosCol;
            }
        }

        $sumW = array_sum($colW);
        if ($sumW > 100 && $sumW > 0) {
            $overflow = $sumW - 100;
            $flex = [];
            foreach ($colW as $ci => $cw) {
                if (in_array($ci, $protect, true)) {
                    continue;
                }
                $flex[] = $ci;
            }
            $flexSum = 0;
            foreach ($flex as $ci) {
                $flexSum += $colW[$ci];
            }
            if ($flexSum > 0 && $overflow > 0.05) {
                foreach ($flex as $ci) {
                    $colW[$ci] = round($colW[$ci] * (1 - ($overflow / $flexSum)), 2);
                }
            }
        }
        if ($nameColIdx !== null) {
            $wName = $colW[$nameColIdx];
        }
        $boardCssId = 'board-dyn-' . (int) $boardIdx;
    @endphp

    <style>
        .{{ $boardCssId }} table.board-results {
            font-size: {{ $bodyFs }}px !important;
        }
        .{{ $boardCssId }} table.board-results th,
        .{{ $boardCssId }} table.board-results td {
            font-size: {{ $bodyFs }}px !important;
            white-space: nowrap !important;
        }
        .{{ $boardCssId }} table.board-results th.name-h {
            white-space: normal !important;
            width: {{ $wName }}% !important;
            word-wrap: break-word !important;
            overflow: hidden !important;
        }
        .{{ $boardCssId }} table.board-results td.name-cell {
            font-size: {{ $nameFs }}px !important;
            text-align: left !important;
            white-space: nowrap !important;
            width: {{ $wName }}% !important;
        }
        @if($wStreamCol > 0)
        .{{ $boardCssId }} table.board-results td.stream-cell,
        .{{ $boardCssId }} table.board-results th.v-stream { width: {{ $wStreamCol }}% !important; }
        @endif
        @if($wTotalCol > 0)
        .{{ $boardCssId }} table.board-results td.board-total-cell,
        .{{ $boardCssId }} table.board-results th.v-total { width: {{ $wTotalCol }}% !important; }
        @endif
        @if($wAvgCol > 0)
        .{{ $boardCssId }} table.board-results td.board-avg-cell,
        .{{ $boardCssId }} table.board-results th.v-avg { width: {{ $wAvgCol }}% !important; }
        @endif
        @if($wPosCol > 0)
        .{{ $boardCssId }} table.board-results td.board-pos-cell { width: {{ $wPosCol }}% !important; }
        @endif
        .{{ $boardCssId }} table.board-results th.v,
        .{{ $boardCssId }} table.board-results th.v-subj,
        .{{ $boardCssId }} table.board-results th.v-stream,
        .{{ $boardCssId }} table.board-results th.v-trail {
            height: {{ $vHeaderH }}px !important;
            vertical-align: middle !important;
            text-align: center !important;
            padding-top: 0 !important;
            overflow: hidden !important;
        }
        .{{ $boardCssId }} table.board-results th.sn-h,
        .{{ $boardCssId }} table.board-results th.name-h,
        .{{ $boardCssId }} table.board-results th.index-h {
            height: {{ $vHeaderH }}px !important;
            vertical-align: middle !important;
            text-align: center !important;
            padding-top: 0 !important;
            overflow: hidden !important;
        }
        .{{ $boardCssId }} table.board-results table.v-inner {
            width: 100% !important;
            height: {{ $vInnerH }}px !important;
        }
        .{{ $boardCssId }} table.board-results .v-box {
            height: {{ $vBoxH }}px !important;
            width: 100% !important;
            overflow: hidden !important;
        }
        .{{ $boardCssId }} table.board-results .v-rot {
            width: {{ $vBoxH }}px !important;
            font-size: {{ $vFont }}px !important;
            line-height: {{ $vLine }}px !important;
            top: 50% !important;
            left: 50% !important;
            margin-left: -{{ $vRotMarginLeft }}px !important;
            margin-top: -{{ $vRotMarginTop }}px !important;
            transform: rotate(-90deg) !important;
            transform-origin: center center !important;
            color: #940000 !important;
            text-align: center !important;
        }
    </style>

    <div class="board-wrap {{ $boardCssId }}{{ $loop->last ? ' board-wrap-last' : '' }}">
        @php
            $boardTitle = $board['titleLine'] ?? ($title ?? 'Results');
            $boardClass = $board['className'] ?? '';
            $yearDisp = $filters['year'] ?? '';
            $termRaw = (string) ($filters['term'] ?? '');
            $termDisp = $termRaw !== ''
                ? ucwords(str_replace('_', ' ', $termRaw))
                : '';
        @endphp
        <div class="board-page-header">
            <table class="board-header-inner">
                <tr>
                    <td class="board-logo-side left">
                        @if($orgLogoBase64)
                            <img src="{{ $orgLogoBase64 }}" alt="Organization Logo">
                        @elseif($schoolLogoBase64)
                            <img src="{{ $schoolLogoBase64 }}" alt="School Logo">
                        @endif
                    </td>
                    <td class="board-header-center">
                        @if($orgName)
                            <div class="org-name">{{ $orgName }}</div>
                        @endif
                        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
                        @if($school)
                            <div class="school-sub">{{ $school->address ?? '' }}</div>
                            <div class="school-sub">Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
                        @endif
                    </td>
                    <td class="board-logo-side right">
                        @if($orgLogoBase64 && $schoolLogoBase64)
                            <img src="{{ $schoolLogoBase64 }}" alt="School Logo">
                        @endif
                    </td>
                </tr>
            </table>
            <div class="board-report-title">{{ $boardTitle }}</div>
            @if($boardClass !== '')
                <div class="board-report-subtitle">{{ $boardClass }}</div>
            @endif
        </div>

        <div class="board-info-section">
            <table class="board-info-table">
                <tr>
                    <td class="label">Academic Year:</td>
                    <td>{{ $yearDisp }}</td>
                    <td class="label">Term:</td>
                    <td>{{ $termDisp }}</td>
                </tr>
                <tr>
                    <td class="label">Date Generated:</td>
                    <td>{{ date('d M Y H:i') }}</td>
                    <td class="label">Class/Grade:</td>
                    <td>{{ $boardClass }}</td>
                </tr>
            </table>
        </div>

        {{-- Class performance overview (Excel-like) --}}
        <table class="board-perf">
            <thead>
                <tr>
                    <th>SEX</th>
                    <th>REG.</th>
                    <th>ABS</th>
                    <th>INC.</th>
                    <th>I</th>
                    <th>II</th>
                    <th>III</th>
                    <th>IV</th>
                    <th>0</th>
                    <th>I-III</th>
                    <th>GPA</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $gpaVal = '';
                    foreach ($board['perfRows'] as $x) {
                        if (($x['sex'] ?? '') === 'T') {
                            $gpaVal = $x['gpa'] ?? '';
                            break;
                        }
                    }
                @endphp
                @foreach($board['perfRows'] as $pr)
                    <tr>
                        <td class="bold">{{ $pr['sex'] }}</td>
                        <td>{{ $pr['reg'] }}</td>
                        <td>{{ $pr['abs'] }}</td>
                        <td>{{ $pr['inc'] }}</td>
                        <td>{{ $pr['I'] }}</td>
                        <td>{{ $pr['II'] }}</td>
                        <td>{{ $pr['III'] }}</td>
                        <td>{{ $pr['IV'] }}</td>
                        <td>{{ $pr['0'] }}</td>
                        <td>{{ $pr['i_iii'] }}</td>
                        <td class="gpa-cell">{{ ($pr['sex'] ?? '') === 'T' ? $gpaVal : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Student results board: marks | grade as separate columns --}}
        <table class="board-results">
            <colgroup>
                @foreach($colW as $cw)
                    <col style="width: {{ $cw }}%;">
                @endforeach
            </colgroup>
            <thead>
                <tr class="board-col-sync">
                    @foreach($colW as $cw)
                        <th style="width: {{ $cw }}%;">&nbsp;</th>
                    @endforeach
                </tr>
                <tr>
                    <th class="sn-h">S/N</th>
                    @if($showNameCol)
                        <th class="name-h">STUDENT<br>NAME</th>
                    @endif
                    @if($showIndexCol)
                        <th class="index-h">INDEX NO</th>
                    @endif
                    <th class="v">{!! pdfBoardVerticalLabel('SEX') !!}</th>
                    @if($showStreamCol)
                        <th class="v v-stream">{!! pdfBoardVerticalLabel($board['streamHeader'] ?? 'COMBINATION') !!}</th>
                    @endif
                    @if($isSecondary)
                        <th class="v">{!! pdfBoardVerticalLabel('POINT') !!}</th>
                        <th class="v">{!! pdfBoardVerticalLabel('DIVISION') !!}</th>
                    @endif
                    @foreach($subjects as $subj)
                        <th class="v-subj" colspan="2">{!! pdfBoardVerticalLabel($subj['subject_name'] ?? '') !!}</th>
                    @endforeach
                    @foreach($board['trailingHeaders'] ?? [] as $th)
                        @php
                            $thUp = strtoupper(trim((string) $th));
                            $trailExtra = $thUp === 'TOTAL' ? 'v-total' : ($thUp === 'AVERAGE' ? 'v-avg' : '');
                        @endphp
                        <th class="v v-trail {{ $trailExtra }}">{!! pdfBoardVerticalLabel($th) !!}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($board['rows'] as $r)
                    <tr>
                        <td>{{ $r['sn'] }}</td>
                        @if($showNameCol)
                            <td class="name-cell">{{ $r['name'] }}</td>
                        @endif
                        @if($showIndexCol)
                            <td>{{ $r['index'] }}</td>
                        @endif
                        <td>{{ $r['sex'] }}</td>
                        @if($showStreamCol)
                            <td class="stream-cell"><span class="stream-keep">{{ preg_replace('/\s+/', '', (string) ($r['stream'] ?? '')) }}</span></td>
                        @endif
                        @if($isSecondary)
                            <td>{{ $r['point'] }}</td>
                            <td>{{ $r['division'] }}</td>
                        @endif
                        @foreach($r['subjects'] as $cell)
                            <td class="{{ !empty($cell['empty']) ? 'empty-subj' : '' }}">{{ $cell['marks'] }}</td>
                            <td class="{{ !empty($cell['empty']) ? 'empty-subj' : '' }}">{{ $cell['grade'] }}</td>
                        @endforeach
                        <td class="board-num-cell board-total-cell"><span class="board-num-keep">{{ $r['total'] ?? '' }}</span></td>
                        <td class="board-num-cell board-avg-cell {{ $r['average_class'] ?? 'remark-inc' }}"><span class="board-num-keep">{{ $r['average'] }}</span></td>
                        @if(!$isSecondary)
                            <td>{{ $r['grade'] }}</td>
                        @endif
                        <td class="board-pos-cell">{{ $r['position'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $statsColSpan = 1 + (count($gradeKeys) * 3) + 3;
            $showBoardSign = $loop->last && (!empty($schoolSignBase64) || !empty($schoolStampBase64));
        @endphp

        {{-- Outside student table: DomPDF can fill leftover page space (no giant empty gap) --}}
        @if(!empty($board['subjectStats']))
            <div class="board-stats-section">
                <div class="board-stats-title">Remark Color Definition</div>
                <table class="board-subj-stats board-legend">
                    <tr class="legend-row">
                        @if($isSecondary)
                            <td class="remark-excellent">A - Excellent</td>
                            <td class="remark-pass">B - Very Good</td>
                            <td class="remark-good">C - Good</td>
                            <td class="remark-avg">D - {{ $isALevel ? 'Average' : 'Satisfactory' }}</td>
                            @if($isALevel)
                                <td class="remark-avg">E - Satisfactory</td>
                                <td class="remark-avg">S - Subsidiary</td>
                            @endif
                            <td class="remark-fail">F - Fail</td>
                            <td class="remark-inc">Incomplete</td>
                        @else
                            <td class="remark-pass">Pass</td>
                            <td class="remark-fail">Failed</td>
                            <td class="remark-inc">Incomplete</td>
                        @endif
                    </tr>
                </table>

                <table class="board-subj-stats">
                    <thead>
                        <tr>
                            <th class="board-stats-banner" colspan="{{ $statsColSpan }}">Subject Performance Statistics</th>
                        </tr>
                        <tr>
                            <th rowspan="2">SUBJECTS</th>
                            @foreach($gradeKeys as $gk)
                                <th colspan="3">{{ $gk }}</th>
                            @endforeach
                            <th rowspan="2">GPA</th>
                            <th rowspan="2">RANKS</th>
                            <th rowspan="2">COMPETENCY LEVEL</th>
                        </tr>
                        <tr>
                            @foreach($gradeKeys as $gk)
                                <th>F</th><th>M</th><th>T</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rankItems = [];
                            foreach ($board['subjectStats'] as $rn => $rg) {
                                $rt = 0; $rp = 0;
                                foreach ($gradeKeys as $gk) {
                                    $c = (int) ($rg[$gk]['total'] ?? 0);
                                    $rt += $c;
                                    $rp += $c * ($ptMap[$gk] ?? 0);
                                }
                                $rankItems[] = [
                                    'name' => $rn,
                                    'gpa' => $rt > 0 ? round($rp / $rt, 4) : 0.0,
                                ];
                            }
                            usort($rankItems, function ($a, $b) use ($isSecondary) {
                                return $isSecondary
                                    ? ($a['gpa'] <=> $b['gpa'])
                                    : ($b['gpa'] <=> $a['gpa']);
                            });
                            $rankMap = [];
                            $rankTotal = max(1, count($rankItems));
                            foreach ($rankItems as $i => $it) {
                                $rankMap[$it['name']] = ($i + 1) . '/' . $rankTotal;
                            }
                        @endphp
                        @foreach($board['subjectStats'] as $sName => $grades)
                            @php
                                $sTotal = 0; $sPoints = 0;
                                foreach ($gradeKeys as $gk) {
                                    $c = (int) ($grades[$gk]['total'] ?? 0);
                                    $sTotal += $c;
                                    $sPoints += $c * ($ptMap[$gk] ?? 0);
                                }
                                $sGPA = $sTotal > 0 ? round($sPoints / $sTotal, 4) : 0.0;
                                if ($isSecondary) {
                                    if ($sTotal === 0) $sg = 'Incomplete';
                                    elseif ($sGPA <= 1.5) $sg = 'A';
                                    elseif ($sGPA <= 2.5) $sg = 'B';
                                    elseif ($sGPA <= 3.5) $sg = 'C';
                                    elseif ($sGPA <= 4.5) $sg = 'D';
                                    elseif ($isALevel && $sGPA <= 5.5) $sg = 'E';
                                    elseif ($isALevel && $sGPA <= 6.5) $sg = 'S';
                                    else $sg = 'F';
                                } else {
                                    if ($sTotal === 0) $sg = 'Incomplete';
                                    elseif ($sGPA >= 4.5) $sg = 'A';
                                    elseif ($sGPA >= 3.5) $sg = 'B';
                                    elseif ($sGPA >= 2.5) $sg = 'C';
                                    elseif ($sGPA >= 1.5) $sg = 'D';
                                    elseif ($sGPA >= 0.5) $sg = 'E';
                                    else $sg = 'F';
                                }
                                $remarkClass = 'remark-inc';
                                $competency = 'Incomplete';
                                if ($sTotal > 0) {
                                    if ($isSecondary) {
                                        $map = [
                                            'A' => ['remark-excellent', 'Grade A (Excellent)'],
                                            'B' => ['remark-pass', 'Grade B (Very Good)'],
                                            'C' => ['remark-good', 'Grade C (Good)'],
                                            'D' => ['remark-avg', 'Grade D (' . ($isALevel ? 'Average' : 'Satisfactory') . ')'],
                                            'E' => ['remark-avg', 'Grade E (Satisfactory)'],
                                            'S' => ['remark-avg', 'Grade S (Subsidiary)'],
                                            'F' => ['remark-fail', 'Grade F (Fail)'],
                                        ];
                                        if (isset($map[$sg])) {
                                            $remarkClass = $map[$sg][0];
                                            $competency = $map[$sg][1];
                                        }
                                    } else {
                                        if (in_array($sg, ['A','B','C','D'], true)) {
                                            $remarkClass = 'remark-pass';
                                            $competency = "Grade {$sg} (Pass)";
                                        } else {
                                            $remarkClass = 'remark-fail';
                                            $competency = "Grade {$sg} (Fail)";
                                        }
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="subj-name">{{ strtoupper($sName) }}</td>
                                @foreach($gradeKeys as $gk)
                                    <td>{{ (int) ($grades[$gk]['female'] ?? 0) }}</td>
                                    <td>{{ (int) ($grades[$gk]['male'] ?? 0) }}</td>
                                    <td style="font-weight:bold;">{{ (int) ($grades[$gk]['total'] ?? 0) }}</td>
                                @endforeach
                                <td>{{ number_format($sGPA, 4) }}</td>
                                <td>{{ $rankMap[$sName] ?? '-' }}</td>
                                <td class="remark-cell {{ $remarkClass }}">{{ $competency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($showBoardSign)
            <table class="board-head-official">
                <tr>
                    <td style="width:50%;">
                        <div class="board-head-sign">
                            @if(!empty($schoolSignBase64))
                                <img src="{{ $schoolSignBase64 }}" alt="Sahihi">
                            @endif
                            <span class="board-sig-line"></span>
                            <div class="board-sig-label">Mkuu wa Shule</div>
                        </div>
                    </td>
                    <td style="width:50%;">
                        <div class="board-head-stamp">
                            @if(!empty($schoolStampBase64))
                                <img src="{{ $schoolStampBase64 }}" alt="Stamp">
                            @endif
                            <div class="board-sig-label">Stamp</div>
                        </div>
                    </td>
                </tr>
            </table>
        @endif
    </div>
@endforeach
