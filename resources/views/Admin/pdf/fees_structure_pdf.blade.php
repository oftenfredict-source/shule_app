<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; margin-bottom: 5px; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #940000; color: #fff; padding: 10px; text-transform: uppercase; letter-spacing: 1px; }

        .info-section { margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 25%; }

        .class-section { margin-bottom: 30px; page-break-inside: avoid; }
        .class-header { background: #fdf2f2; border-left: 5px solid #940000; padding: 10px; margin-bottom: 15px; }
        .class-title { font-size: 14px; font-weight: bold; color: #940000; margin: 0; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 10px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; border-bottom: 2px solid #940000; text-transform: uppercase; font-size: 10px; }
        
        .amount-col { text-align: right; font-weight: bold; }
        .total-row { background-color: #940000 !important; color: #fff !important; font-weight: bold; }
        .total-row td { border-color: #940000 !important; }

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
            opacity: 0.05; transform: rotate(-45deg); font-size: 100px; font-weight: bold;
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
        <div class="report-title">OFFICIAL FEES STRUCTURE - {{ $year }}</div>
    </div>

    @foreach($classes as $class)
        <div class="class-section">
            <div class="class-header">
                <h3 class="class-title">CLASS: {{ $class->class_name }}</h3>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">#</th>
                        <th style="width: 50%;">FEE DESCRIPTION</th>
                        <th style="width: 20%;">FREQUENCY</th>
                        <th style="width: 20%;" class="text-right">AMOUNT (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach($class->fees as $index => $fee)
                        @php $total += $fee->amount; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $fee->fee_name }}</strong>
                                @if($fee->is_required)
                                    <span style="color: #940000; font-size: 8px;">(REQUIRED)</span>
                                @endif
                                <br>
                                <small style="color: #666;">{{ $fee->fee_description }}</small>
                            </td>
                            <td>{{ $fee->frequency ?? 'Annual' }}</td>
                            <td class="amount-col">{{ number_format($fee->amount, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Annual Fee</td>
                        <td class="amount-col">{{ number_format($total, 0) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach

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
        Generated by ShuleXpert, Powered By EmCa Techonology &copy; {{ $year }}
    </div>
</body>
</html>
