{{-- Excel-like class board (landscape). Styles extend results.blade.php --}}
@php
    if (!function_exists('pdfBoardVerticalLabel')) {
        /** Excel-style vertical header, centred in cell (DomPDF-safe) */
        function pdfBoardVerticalLabel(?string $text): string {
            $text = strtoupper(trim((string) $text));
            if ($text === '') {
                return '';
            }
            $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            // Nested table + rotate: DomPDF centres better than absolute left:0
            return '<table class="v-inner"><tr><td>'
                . '<div class="v-box"><div class="v-rot">' . $safe . '</div></div>'
                . '</td></tr></table>';
        }
    }
@endphp
<style>
    .board-wrap { page-break-after: always; }
    .board-wrap:last-child,
    .board-wrap.board-wrap-last { page-break-after: auto; }

    /* Header â€” same clean layout as Subject Performance Summary */
    .board-page-header {
        margin: 0 0 10px 0;
        border-bottom: 2px solid #940000;
        padding-bottom: 8px;
        text-align: center;
    }
    .board-header-inner {
        width: auto;
        max-width: 78%;
        margin: 0 auto;
        border-collapse: collapse;
    }
    .board-header-inner td {
        border: none !important;
        padding: 0;
        vertical-align: middle;
        background: transparent;
    }
    .board-logo-side { width: 100px; }
    .board-logo-side img {
        display: block;
        max-height: 88px;
        max-width: 96px;
        margin: 0;
        border: none;
        outline: none;
    }
    .board-logo-side.left { text-align: left; padding-right: 14px; }
    .board-logo-side.right { text-align: right; padding-left: 14px; }
    .board-logo-side.right img { margin-left: auto; }
    .board-header-center { text-align: center; padding: 0 6px; }
    .board-header-center .org-name {
        font-size: 12px;
        font-weight: bold;
        color: #555;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .board-header-center .school-name {
        font-size: 16px;
        font-weight: bold;
        color: #940000;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .board-header-center .school-sub {
        font-size: 10px;
        color: #555;
        margin: 1px 0;
    }
    .board-report-title {
        font-size: 12px;
        font-weight: bold;
        margin-top: 10px;
        background: #f8f9fa;
        padding: 6px 8px;
        color: #940000;
        text-transform: uppercase;
        text-align: center;
        border: none;
    }
    .board-report-subtitle {
        font-size: 10px;
        font-weight: bold;
        margin-top: 4px;
        color: #555;
        text-transform: uppercase;
        text-align: center;
    }
    .board-info-section { margin: 8px 0 12px 0; }
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
        margin: 0 0 12px 0;
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
        word-wrap: break-word;
        overflow: hidden;
        font-size: 14px;
        line-height: 1.15;
    }
    /* Do NOT repeat column titles on following pages â€” first page only */
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
    /* Vertical titles â€” base size; per-board height expands with longest subject name */
    table.board-results th.v,
    table.board-results th.v-subj {
        height: 78px;
        padding: 2px 2px;
        vertical-align: middle;
        text-align: center;
        overflow: visible;
    }
    table.board-results th.v { width: 2.5%; }
    /* COMBIE/COMBINATION: vertical header like SEX/POINT; column wide enough for horizontal codes (PMC) */
    table.board-results th.v-stream {
        width: 4.2%;
        font-size: 14px;
        vertical-align: bottom;
        padding: 2px 1px 0 1px;
        height: 78px;
        overflow: visible;
    }
    table.board-results th.v-trail { width: 4%; }
    table.board-results th.v-trail.v-total { width: 5.8%; }
    table.board-results th.v-trail.v-avg { width: 4.6%; }
    table.board-results th.v-subj { width: auto; }

    table.board-results table.v-inner {
        width: 100%;
        height: 72px;
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
    table.board-results .v-box {
        width: 12px;
        height: 70px;
        margin: 0 auto;
        position: relative;
        overflow: visible;
    }
    table.board-results th.v-subj .v-box {
        width: 14px;
    }
    table.board-results .v-rot {
        position: absolute;
        left: 6px;
        bottom: 0;
        width: 70px;
        white-space: nowrap;
        font-size: 11px;
        font-weight: bold;
        color: #940000;
        line-height: 12px;
        text-align: center;
        letter-spacing: 0.1px;
        transform: rotate(-90deg);
        transform-origin: left bottom;
    }
    table.board-results th.sn-h {
        width: 3%;
        vertical-align: middle;
        font-size: 14px;
    }
    table.board-results th.name-h {
        width: 13%;
        vertical-align: middle;
        font-size: 14px;
        white-space: nowrap !important;
    }
    table.board-results th.index-h {
        width: 8%;
        vertical-align: middle;
        font-size: 14px;
    }
    table.board-results td {
        vertical-align: middle;
        font-size: 14px;
        padding: 1px 1px;
    }
    table.board-results td.name-cell {
        text-align: left;
        padding-left: 3px;
        padding-right: 2px;
        font-weight: bold;
        font-size: 12px;
        line-height: 1.1;
        white-space: nowrap !important;
        word-wrap: normal !important;
        word-break: keep-all !important;
        overflow: hidden !important;
    }
    table.board-results td.name-cell .name-keep {
        white-space: nowrap !important;
        display: inline-block;
        max-width: 100%;
    }
    table.board-results td.stream-cell {
        font-size: 11px;
        font-weight: bold;
        line-height: 1.1;
        padding: 1px 2px;
        text-align: center;
        white-space: nowrap !important;
        word-wrap: normal !important;
        word-break: keep-all !important;
        overflow: hidden !important;
    }
    table.board-results td.stream-cell .stream-keep {
        white-space: nowrap !important;
        display: inline-block;
        letter-spacing: 0.3px;
    }
    /* TOTAL / AVERAGE — same font as marks; TOTAL wider so long values don't overlap */
    table.board-results td.board-num-cell {
        white-space: nowrap !important;
        word-wrap: normal !important;
        overflow: hidden !important;
        font-size: 14px !important;
        line-height: 1.15 !important;
        letter-spacing: 0;
        padding: 1px 2px !important;
        text-align: center !important;
    }
    table.board-results td.board-num-cell.board-total-cell {
        font-size: 12px !important;
        padding: 1px 1px !important;
    }
    table.board-results td.board-num-cell .board-num-keep {
        white-space: nowrap !important;
        display: inline-block;
        max-width: 100%;
    }
    table.board-results td.board-total-cell .board-num-keep {
        font-size: 12px !important;
    }
    table.board-results td.board-avg-cell .board-num-keep {
        font-size: 14px !important;
    }
    table.board-results td.empty-subj { background: #f5f5f5; }
    table.board-results .sub-h {
        font-size: 5.5px;
        background: #fafafa;
        color: #555;
        vertical-align: middle;
        height: auto;
        padding: 2px 0;
    }
    /* Nested Subject Performance Statistics â€” force same 14px as SEX/REG overview
       (DomPDF often under-sizes nested tables inside board-results) */
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

        // Header row height from longest vertical title (subjects + fixed labels)
        $maxVertLen = 0;
        foreach (['SEX', 'POINT', 'DIVISION', 'TOTAL', 'AVERAGE', 'POSITION', 'GRADE', 'COMBINATION', 'INDEX NO'] as $fixedLabel) {
            $maxVertLen = max($maxVertLen, strlen($fixedLabel));
        }
        foreach ($subjects as $subj) {
            $maxVertLen = max(
                $maxVertLen,
                mb_strlen(strtoupper(trim((string) ($subj['subject_name'] ?? ''))), 'UTF-8'),
            );
        }
        // ~7.2px per character at 11px bold vertical titles + padding; clamp for DomPDF page fit
        $vHeaderH = (int) min(180, max(90, round($maxVertLen * 7.2) + 12));
        $vInnerH = max(78, $vHeaderH - 6);
        $vBoxH = max(76, $vHeaderH - 8);
        $boardCssId = 'board-dyn-' . (int) $boardIdx;
    @endphp

    <style>
        .{{ $boardCssId }} table.board-results th.v,
        .{{ $boardCssId }} table.board-results th.v-subj {
            height: {{ $vHeaderH }}px !important;
        }
        .{{ $boardCssId }} table.board-results table.v-inner {
            height: {{ $vInnerH }}px !important;
        }
        .{{ $boardCssId }} table.board-results .v-box {
            height: {{ $vBoxH }}px !important;
        }
        .{{ $boardCssId }} table.board-results .v-rot {
            width: {{ $vBoxH }}px !important;
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
                    $perfCount = count($board['perfRows']);
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
                        @if($loop->first)
                            <td class="gpa-cell" rowspan="{{ $perfCount }}">{{ $gpaVal }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Student results board --}}
        <table class="board-results">
            <thead>
                <tr>
                    <th class="sn-h" style="width:3%;">S/N</th>
                    @if($board['showName'] ?? true)
                        <th class="name-h" style="width:{{ ($board['showIndex'] ?? true) ? '14%' : '20%' }};">STUDENT NAME</th>
                    @endif
                    @if($board['showIndex'] ?? true)
                        <th class="index-h" style="width:{{ ($board['showName'] ?? true) ? '9%' : '14%' }};">INDEX NO</th>
                    @endif
                    <th class="v">{!! pdfBoardVerticalLabel('SEX') !!}</th>
                    @if(!empty($board['showStream']))
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
                        @if($board['showName'] ?? true)
                            <td class="name-cell"><span class="name-keep">{{ $r['name'] }}</span></td>
                        @endif
                        @if($board['showIndex'] ?? true)
                            <td>{{ $r['index'] }}</td>
                        @endif
                        <td>{{ $r['sex'] }}</td>
                        @if(!empty($board['showStream']))
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
                        <td>{{ $r['position'] }}</td>
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
