<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Payment Details - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; background: #fff; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }

        .info-section { margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 25%; }

        .section-title { font-size: 12px; font-weight: bold; color: #940000; border-bottom: 1px solid #940000; padding-bottom: 5px; margin-top: 20px; margin-bottom: 10px; }

        .stats-grid { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .stats-grid td { text-align: center; padding: 10px; border: 1px solid #eee; }
        .stat-val { font-size: 14px; font-weight: bold; color: #333; }
        .stat-label { font-size: 9px; color: #777; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 6px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; }
        table.data-table td.amount-col { text-align: right; font-weight: bold; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; background: #fff; }

        /* ── SIGNATURE AND STAMP ─────────────────── */
        .sig-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 50%; text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 120px; max-height: 120px; opacity: 0.85; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 150px; max-height: 40px; }
        .sig-line   { border-top: 1.5px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 8.5px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase; }

        /* ── WATERMARK ─────────────────── */
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

        $prevDebtTotal = ($previousYearDebt['school_fee_balance'] ?? 0) + ($previousYearDebt['other_contribution_balance'] ?? 0);
        $billBalance = $billBalance ?? max(0, ($totalBalance ?? 0) - $prevDebtTotal);
        $totalOutstanding = $totalOutstanding ?? ($totalBalance ?? 0);
        $isMultiAccount = !empty($multiAccounts) && count($multiAccounts) > 0;
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">STUDENT PAYMENT DETAILS — {{ $year }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Student Name:</td>
                <td>{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</td>
                <td class="label">Reg. Number:</td>
                <td>{{ $student->admission_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Class:</td>
                <td>
                    @if($student->subclass && $student->subclass->class)
                        {{ $student->subclass->class->class_name }} {{ $student->subclass->subclass_name }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
            </tr>
            @if(!$isMultiAccount && $payment && !empty($payment->control_number))
            <tr>
                <td class="label">Control Number:</td>
                <td colspan="3"><strong>{{ $payment->control_number }}</strong></td>
            </tr>
            @endif
            @if($student->parent)
            <tr>
                <td class="label">Parent/Guardian:</td>
                <td colspan="3">{{ $student->parent->first_name }} {{ $student->parent->last_name }} ({{ $student->parent->phone }})</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section-title">Payment Summary</div>
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-val">{{ number_format($totalRequired ?? 0, 0) }}/=</div>
                <div class="stat-label">Fee Required</div>
            </td>
            <td>
                <div class="stat-val" style="color: #28a745;">{{ number_format($totalPaid ?? 0, 0) }}/=</div>
                <div class="stat-label">Total Paid</div>
            </td>
            <td>
                <div class="stat-val">{{ number_format($billBalance, 0) }}/=</div>
                <div class="stat-label">Bill Balance</div>
            </td>
            <td>
                <div class="stat-val">{{ number_format($prevDebtTotal, 0) }}/=</div>
                <div class="stat-label">Prev. Year Debt</div>
            </td>
            <td>
                <div class="stat-val" style="color: #940000;">{{ number_format($totalOutstanding, 0) }}/=</div>
                <div class="stat-label">Total Outstanding</div>
            </td>
        </tr>
    </table>

    @if($isMultiAccount)
    <div class="section-title">Multi Account Control Numbers</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Fee</th>
                <th>Account</th>
                <th>Control Number</th>
                <th class="amount-col">Required</th>
                <th class="amount-col">Paid</th>
                <th class="amount-col">Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($multiAccounts as $ma)
            <tr>
                <td><strong>{{ $ma['fee_name'] ?? '—' }}</strong></td>
                <td>{{ $ma['account_label'] ?? '—' }}</td>
                <td>{{ $ma['control_number'] ?? '—' }}</td>
                <td class="amount-col">{{ number_format($ma['amount_required'] ?? 0, 0) }}</td>
                <td class="amount-col" style="color: #28a745;">{{ number_format($ma['amount_paid'] ?? 0, 0) }}</td>
                <td class="amount-col" style="color: #940000;">{{ number_format($ma['balance'] ?? 0, 0) }}</td>
                <td>{{ $ma['payment_status'] ?? 'Pending' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($feeBreakdown && $feeBreakdown->count())
    <div class="section-title">Detailed Fees Breakdown</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Fee Name</th>
                <th class="amount-col">Required</th>
                <th class="amount-col">Paid</th>
                <th class="amount-col">Balance (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeBreakdown as $index => $fee)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $fee->fee_name }}</strong></td>
                <td class="amount-col">{{ number_format($fee->fee_total_amount, 0) }}</td>
                <td class="amount-col" style="color: #28a745;">{{ number_format($fee->amount_paid, 0) }}</td>
                <td class="amount-col" style="color: #940000;">{{ number_format($fee->balance, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($prevDebtTotal > 0)
    <div class="section-title">Previous Year Outstanding Debt</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount-col">Balance (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @if(($previousYearDebt['school_fee_balance'] ?? 0) > 0)
            <tr>
                <td>Outstanding School Fees (Previous Academic Year)</td>
                <td class="amount-col">{{ number_format($previousYearDebt['school_fee_balance'], 0) }}</td>
            </tr>
            @endif
            @if(($previousYearDebt['other_contribution_balance'] ?? 0) > 0)
            <tr>
                <td>Outstanding Other Contributions (Previous Academic Year)</td>
                <td class="amount-col">{{ number_format($previousYearDebt['other_contribution_balance'], 0) }}</td>
            </tr>
            @endif
        </tbody>
    </table>
    @endif

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
