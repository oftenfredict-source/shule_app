<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Official Fees Structure' }}</title>
    <style>
        @page { margin: 12mm 10mm 16mm 10mm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10.5px; color: #333; line-height: 1.45; background: #fff; margin: 0; padding: 0 0 28px 0; }
        .header { text-align: center; margin-bottom: 14px; border-bottom: 2.5px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 17px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 13px; font-weight: bold; margin-top: 8px; background: #f8f9fa; padding: 7px 8px; border: 1.5px solid #ccc; }

        .info-section { margin-bottom: 12px; width: 100%; }
        .info-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .info-table td { padding: 7px 8px; border: 1px solid #ccc; width: 25%; vertical-align: middle; }
        .label { font-weight: bold; color: #444; background: #f8f9fa; }

        .section-title { font-size: 11.5px; font-weight: bold; color: #940000; border-bottom: 1.5px solid #940000; padding-bottom: 4px; margin-top: 14px; margin-bottom: 8px; }

        /* Overview widgets, equal width, full page balance */
        .stats-grid { width: 100%; margin-bottom: 14px; border-collapse: collapse; table-layout: fixed; }
        .stats-grid td {
            width: 20%;
            text-align: center;
            padding: 12px 6px;
            border: 1px solid #ccc;
            vertical-align: middle;
            background: #fff;
        }
        .stat-val { font-size: 16px; font-weight: bold; color: #222; line-height: 1.2; }
        .stat-label { font-size: 8.5px; color: #666; text-transform: uppercase; margin-top: 4px; letter-spacing: 0.3px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; table-layout: fixed; border: 1.5px solid #ccc; }
        table.data-table th, table.data-table td {
            border: 1px solid #ccc;
            padding: 7px 6px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #940000;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        table.data-table td.amount-col { text-align: right; font-weight: bold; }
        table.data-table td.class-cell { text-align: center; background: #f7f7f7; vertical-align: middle; font-weight: bold; }
        table.data-table tr.sub-row td { background: #fcfcfc; }
        table.data-table th.amount-col,
        table.data-table td.amount-col { text-align: right; }
        .muted { color: #666; font-size: 8.5px; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; text-align: center; font-size: 8.5px; color: #666; border-top: 1px solid #ccc; padding-top: 5px; background: #fff; }

        .sig-table { width: 100%; margin-top: 28px; border-collapse: collapse; page-break-inside: avoid; table-layout: fixed; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; width: 50%; }
        .stamp-cell { text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 110px; max-height: 110px; opacity: 0.85; }
        .sign-cell { text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 150px; max-height: 40px; }
        .sig-line   { border-top: 2px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 8.5px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase; }

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
        }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

    @php
        if (!function_exists('getSmartBase64')) {
            function getSmartBase64($path) {
                if (!$path) return null;
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

        $schoolLogoBase64  = getSmartBase64($school->school_logo ?? '');
        $schoolStampBase64 = getSmartBase64($school->school_stamp ?? '');
        $schoolSignBase64  = getSmartBase64($school->school_signature ?? '');
        $yearLabel = $yearLabel ?? $year ?? date('Y');
        $classes = $classes ?? collect();

        $allFees = $classes->flatMap(fn ($c) => $c->fees ?? collect());
        $totalFeesCount = $allFees->count();
        $singleCount = $allFees->filter(fn ($f) => ($f->account_mode ?? 'single') !== 'multi')->count();
        $multiCount = $allFees->filter(fn ($f) => ($f->account_mode ?? 'single') === 'multi')->count();
        $breakdownCount = $allFees->filter(fn ($f) => $f->has_breakdown && ($f->breakdowns?->count() ?? 0) > 0)->count();
        $grandTotal = (float) $allFees->sum('amount');
        $classesWithFees = $classes->filter(fn ($c) => ($c->fees ?? collect())->count() > 0)->count();

        // Flatten rows for one professional table with rowspan helpers
        $tableRows = [];
        foreach ($classes as $class) {
            $fees = ($class->fees ?? collect())->values();
            if ($fees->isEmpty()) {
                $tableRows[] = [
                    'type' => 'empty',
                    'class_name' => $class->class_name,
                    'class_total' => 0,
                    'class_rowspan' => 1,
                    'is_first_of_class' => true,
                ];
                continue;
            }

            $classRows = [];
            foreach ($fees as $fee) {
                $isMulti = ($fee->account_mode ?? 'single') === 'multi';
                $accountLabel = ($fee->bankAccount && method_exists($fee->bankAccount, 'displayLabel'))
                    ? $fee->bankAccount->displayLabel()
                    : ($isMulti ? '-' : 'Shared / Single');
                $breakdowns = ($fee->has_breakdown && $fee->breakdowns && $fee->breakdowns->count())
                    ? $fee->breakdowns->values()
                    : collect();

                if ($breakdowns->isNotEmpty()) {
                    $bdCount = $breakdowns->count();
                    foreach ($breakdowns as $bi => $bd) {
                        $classRows[] = [
                            'type' => 'fee_breakdown',
                            'fee' => $fee,
                            'is_multi' => $isMulti,
                            'account_label' => $accountLabel,
                            'breakdown' => $bd,
                            'is_first_breakdown' => $bi === 0,
                            'fee_rowspan' => $bdCount,
                        ];
                    }
                } else {
                    $classRows[] = [
                        'type' => 'fee_direct',
                        'fee' => $fee,
                        'is_multi' => $isMulti,
                        'account_label' => $accountLabel,
                        'fee_rowspan' => 1,
                        'is_first_breakdown' => true,
                    ];
                }
            }

            $classTotal = (float) $fees->sum('amount');
            $classRowspan = count($classRows);
            foreach ($classRows as $i => $row) {
                $row['class_name'] = $class->class_name;
                $row['class_total'] = $classTotal;
                $row['class_rowspan'] = $classRowspan;
                $row['is_first_of_class'] = ($i === 0);
                $tableRows[] = $row;
            }
        }
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->formattedPhones() }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">{{ $reportTitle ?? ('OFFICIAL FEES STRUCTURE, ' . ($scopeLabel ?? 'ALL CLASSES') . ' | ' . $yearLabel) }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Report:</td>
                <td>Official Fees Structure</td>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Academic Year:</td>
                <td>{{ $yearLabel }}</td>
                <td class="label">Scope:</td>
                <td>{{ $scopeLabel ?? (($classes->count() === 1) ? ($classes->first()->class_name ?? 'Selected Class') : 'All Classes') }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Structure Overview</div>
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-val">{{ $classesWithFees }}</div>
                <div class="stat-label">Classes</div>
            </td>
            <td>
                <div class="stat-val">{{ $totalFeesCount }}</div>
                <div class="stat-label">Total Fees</div>
            </td>
            <td>
                <div class="stat-val">{{ $singleCount }}</div>
                <div class="stat-label">Single Account</div>
            </td>
            <td>
                <div class="stat-val">{{ $multiCount }}</div>
                <div class="stat-label">Multi Account</div>
            </td>
            <td>
                <div class="stat-val">{{ $breakdownCount }}</div>
                <div class="stat-label">With Sub-Fees</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Fees Structure Details</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 13%;">Class</th>
                <th style="width: 18%;">Fee Name</th>
                <th style="width: 9%;">Mode</th>
                <th style="width: 17%;">Account</th>
                <th style="width: 18%;">Sub-Fee / Breakdown</th>
                <th class="amount-col" style="width: 12%;">Sub Amount</th>
                <th class="amount-col" style="width: 13%;">Fee Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tableRows as $row)
                @if(($row['type'] ?? '') === 'empty')
                    <tr>
                        <td class="class-cell">{{ $row['class_name'] }}</td>
                        <td colspan="6" class="muted" style="text-align:center;">No active fees configured for this class.</td>
                    </tr>
                @else
                    @php
                        $fee = $row['fee'];
                        $feeRowspan = (int) ($row['fee_rowspan'] ?? 1);
                    @endphp
                    <tr class="{{ ($row['type'] === 'fee_breakdown' && empty($row['is_first_breakdown'])) ? 'sub-row' : '' }}">
                        @if(!empty($row['is_first_of_class']))
                            <td class="class-cell" rowspan="{{ (int) $row['class_rowspan'] }}">
                                {{ $row['class_name'] }}
                                <div class="muted" style="margin-top:6px;">Total</div>
                                <div style="color:#940000;">{{ number_format((float) $row['class_total'], 0) }}/=</div>
                            </td>
                        @endif

                        @if(!empty($row['is_first_breakdown']))
                            <td rowspan="{{ $feeRowspan }}">
                                <strong>{{ $fee->fee_name }}</strong>
                                @if(!empty($fee->must_start_pay))
                                    <div class="muted">Must start</div>
                                @endif
                                @if(!empty($fee->description))
                                    <div class="muted">{{ $fee->description }}</div>
                                @endif
                                @php
                                    $deadlineAmt = $fee->payment_deadline_amount !== null ? (float) $fee->payment_deadline_amount : null;
                                @endphp
                                @if($deadlineAmt !== null && $deadlineAmt > 0)
                                    <div class="muted">Deadline: {{ number_format($deadlineAmt, 0) }}/=
                                        @if(!empty($fee->payment_deadline_date))
                                            ({{ $fee->payment_deadline_date->format('d/m/Y') }})
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td rowspan="{{ $feeRowspan }}">{{ !empty($row['is_multi']) ? 'Multi' : 'Single' }}</td>
                            <td rowspan="{{ $feeRowspan }}">{{ $row['account_label'] ?? '-' }}</td>
                        @endif

                        @if(($row['type'] ?? '') === 'fee_breakdown')
                            <td>{{ $row['breakdown']->item_name ?? '-' }}</td>
                            <td class="amount-col">{{ number_format((float) ($row['breakdown']->amount ?? 0), 0) }}</td>
                        @else
                            <td class="muted">Direct amount</td>
                            <td class="amount-col muted">-</td>
                        @endif

                        @if(!empty($row['is_first_breakdown']))
                            <td class="amount-col" rowspan="{{ $feeRowspan }}" style="color:#940000; vertical-align:middle;">
                                {{ number_format((float) $fee->amount, 0) }}
                            </td>
                        @endif
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" class="muted" style="text-align:center;">No classes / fees found for this export.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

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

    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Techonology &copy; {{ date('Y') }}
    </div>
</body>
</html>
