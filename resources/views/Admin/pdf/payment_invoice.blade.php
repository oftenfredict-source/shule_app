<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FEES PAYMENT INVOICE - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 20px; font-weight: bold; color: #940000; text-transform: uppercase; margin-bottom: 5px; }
        .report-title { font-size: 16px; font-weight: bold; margin-top: 10px; background: #940000; color: #fff; padding: 10px; text-transform: uppercase; letter-spacing: 1px; }

        .invoice-meta { width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #eee; }
        .invoice-meta td { padding: 8px; border: 1px solid #eee; }
        .label { font-weight: bold; color: #940000; background: #fdf2f2; width: 20%; }

        .section-title { font-size: 13px; font-weight: bold; color: #940000; border-bottom: 2px solid #940000; padding-bottom: 5px; margin-top: 25px; margin-bottom: 10px; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; border-bottom: 2px solid #940000; padding: 8px; text-align: left; text-transform: uppercase; font-size: 9px; }
        table.data-table td { border: 1px solid #eee; padding: 8px; font-size: 10px; }
        
        .amount-col { text-align: right; font-weight: bold; }
        .total-highlight { background-color: #fdf2f2; font-weight: bold; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }

        /* ── SIGNATURE AND STAMP ─────────────────── */
        .sig-table { width: 100%; margin-top: 40px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 50%; text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 120px; max-height: 120px; opacity: 0.85; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 50px; margin-bottom: 5px; }
        .school-signature-img img { max-width: 150px; max-height: 50px; }
        .sig-line   { border-top: 2px solid #940000; width: 180px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 9px; color: #940000; font-weight: bold; padding-top: 5px; display: block; text-transform: uppercase; }

        .watermark {
            position: fixed; top: 45%; left: 5%; width: 100%; text-align: center;
            opacity: 0.05; transform: rotate(-45deg); font-size: 120px; font-weight: bold;
            color: #000; z-index: -1; text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="watermark">SHULEXPERT</div>
    
    @php
        if (!function_exists('getSmartBase64')) {
            function getSmartBase64($path) {
                if (!$path) return null;
                $path = ltrim($path, '/');
                $possibilities = [
                    public_path($path),
                    public_path('uploads/' . $path),
                    base_path($path),
                    base_path('public/' . $path),
                ];
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
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 70px; margin-bottom: 10px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
        <div style="font-size: 10px;">{{ $school->address ?? '' }}</div>
        <div style="font-size: 10px;">Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">FEES PAYMENT INVOICE - {{ $year }}</div>
    </div>

    <table class="invoice-meta">
        <tr>
            <td class="label">STUDENT NAME</td>
            <td>{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</td>
            <td class="label">REG. NUMBER</td>
            <td>{{ $student->admission_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">CLASS</td>
            <td>
                @if($student->subclass && $student->subclass->class)
                    {{ $student->subclass->class->class_name }} {{ $student->subclass->subclass_name }}
                @else
                    N/A
                @endif
            </td>
            <td class="label">DATE GENERATED</td>
            <td>{{ date('d M Y, h:i A') }}</td>
        </tr>
        @if($student->parent)
        <tr>
            <td class="label">PARENT/GUARDIAN</td>
            <td colspan="3">{{ $student->parent->first_name }} {{ $student->parent->last_name }} ({{ $student->parent->phone }})</td>
        </tr>
        @endif
    </table>

    <div class="section-title">PAYMENT SUMMARY</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40%;">ITEM DESCRIPTION</th>
                <th style="width: 20%;" class="text-right">TOTAL REQUIRED</th>
                <th style="width: 20%;" class="text-right">AMOUNT PAID</th>
                <th style="width: 20%;" class="text-right">BALANCE (TZS)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="total-highlight">
                <td style="font-size: 13px; color: #940000;">OVERALL ACCOUNT STATUS</td>
                <td class="amount-col">{{ number_format($totalRequired, 0) }}</td>
                <td class="amount-col" style="color: #28a745;">{{ number_format($totalPaid, 0) }}</td>
                <td class="amount-col" style="color: #940000; font-size: 14px;">{{ number_format($totalBalance, 0) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">DETAILED FEES BREAKDOWN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>FEE NAME</th>
                <th class="text-right">REQUIRED</th>
                <th class="text-right">PAID</th>
                <th class="text-right">BALANCE (TZS)</th>
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

    @if($previousYearDebt['school_fee_balance'] > 0 || $previousYearDebt['other_contribution_balance'] > 0)
    <div class="section-title">PREVIOUS YEAR OUTSTANDING DEBT</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>DESCRIPTION</th>
                <th class="text-right">BALANCE (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @if($previousYearDebt['school_fee_balance'] > 0)
            <tr>
                <td>Outstanding School Fees (Previous Academic Year)</td>
                <td class="amount-col">{{ number_format($previousYearDebt['school_fee_balance'], 0) }}</td>
            </tr>
            @endif
            @if($previousYearDebt['other_contribution_balance'] > 0)
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
        This is an official document. Generated on {{ date('d/m/Y H:i:s') }} by ShuleXpert.<br>
        Powered by EmCa Techonology &copy; {{ $year }}
    </div>
</body>
</html>
