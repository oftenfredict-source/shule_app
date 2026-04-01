<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FILTERED PAYMENTS LIST - {{ $year }}</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 20px; font-weight: bold; color: #940000; text-transform: uppercase; margin-bottom: 5px; }
        .report-title { font-size: 16px; font-weight: bold; margin-top: 10px; background: #940000; color: #fff; padding: 10px; text-transform: uppercase; letter-spacing: 1px; }

        .filters { margin-bottom: 20px; padding: 10px; background: #fdf2f2; border-left: 5px solid #940000; font-size: 10px; }

        .student-section { margin-bottom: 25px; page-break-inside: avoid; border: 1px solid #eee; }
        .student-header { background: #fdf2f2; border-bottom: 2px solid #940000; padding: 10px; font-weight: bold; font-size: 11px; color: #940000; display: table; width: 100%; box-sizing: border-box; }
        .student-header > div { display: table-cell; vertical-align: middle; }
        
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; border-bottom: 1px solid #940000; padding: 8px; text-align: left; text-transform: uppercase; font-size: 8.5px; }
        table.data-table td { border: 1px solid #eee; padding: 8px; font-size: 9px; }
        
        .amount-col { text-align: right; font-weight: bold; }
        .total-row { background-color: #fdf2f2; font-weight: bold; }
        
        .badge { display: inline-block; padding: 2px 5px; font-size: 8px; text-transform: uppercase; border: 1px solid #eee; font-weight: bold; }
        .badge-success { color: #28a745; border-color: #28a745; }
        .badge-info { color: #17a2b8; border-color: #17a2b8; }
        .badge-warning { color: #ffc107; border-color: #ffc107; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }

        /* ── SIGNATURE AND STAMP ─────────────────── */
        .sig-table { width: 100%; margin-top: 40px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 50%; text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 100px; max-height: 100px; opacity: 0.85; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 5px; }
        .school-signature-img img { max-width: 120px; max-height: 40px; }
        .sig-line   { border-top: 1.5px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 8.5px; color: #940000; font-weight: bold; padding-top: 5px; display: block; text-transform: uppercase; }

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
        <div class="report-title">PAYMENTS STATUS REPORT - {{ $year }}</div>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Applied Filters:</strong> 
        @if(data_get($filters, 'class')) Class: {{ data_get($filters, 'class') }} | @endif
        @if(data_get($filters, 'subclass')) Subclass: {{ data_get($filters, 'subclass') }} | @endif
        @if(data_get($filters, 'status')) Student Status: {{ data_get($filters, 'status') }} | @endif
        @if(data_get($filters, 'payment_status')) Payment: {{ data_get($filters, 'payment_status') }} @endif
    </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Student Details</th>
                <th style="width: 20%;">Payment Type</th>
                <th style="width: 20%;">Bill Summary (TZS)</th>
                <th style="width: 20%;">Balance & Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $studentData)
            @php
                $payment = $studentData['payment'];
                
                $required = number_format($studentData['totalRequired'] ?? 0, 0);
                $paid = number_format($studentData['totalPaid'] ?? 0, 0);
                $balance = number_format($studentData['totalBalance'] ?? 0, 0);
                $status = $payment ? $payment->payment_status : 'Pending';
                
                $colorStatus = '#940000';
                if($status == 'Paid') $colorStatus = '#28a745';
                if($status == 'Incomplete Payment' || $status == 'Partial') $colorStatus = '#fd7e14';
                
                $sponsorPercent = data_get($studentData, 'student.sponsorship_percentage', 0);
                $sponsorName = data_get($studentData, 'student.sponsor_name', 'Sponsor');
                $middleName = data_get($studentData, 'student.middle_name') ? substr(data_get($studentData, 'student.middle_name'), 0, 1) . '.' : '';
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ data_get($studentData, 'student.first_name') }} {{ $middleName }} {{ data_get($studentData, 'student.last_name') }}</strong><br>
                    <span style="color: #666; font-size: 9px;">{{ data_get($studentData, 'student.admission_number') }} | {{ data_get($studentData, 'student.class') }}</span><br>
                    <span style="color: #666; font-size: 9px;">{{ $year }}</span>
                </td>
                <td>
                    @if($sponsorPercent > 0)
                        <span style="color: #17a2b8; font-weight: bold; font-size: 10px;">Sponsored</span><br>
                        <span style="font-size: 8px; color: #555;">{{ $sponsorPercent }}% Cover</span><br>
                        <span style="font-size: 8px; color: #333;">{{ $sponsorName }}</span>
                    @else
                        <span style="color: #6c757d; font-weight: bold; font-size: 10px;">Own Payment</span>
                    @endif
                </td>
                <td>
                    <span style="color: #444; font-size: 9px;">Required: {{ $required }}</span><br>
                    <span style="color: #444; font-size: 9px;">Paid: {{ $paid }}</span>
                </td>
                <td>
                    <strong style="font-size: 11px;">{{ $balance }}/=</strong><br>
                    <span style="color: {{ $colorStatus }}; font-weight: bold; font-size: 9px;">{{ $status }}</span>
                </td>
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
        Generated on {{ date('d/m/Y H:i:s') }} by ShuleXpert.<br>
        Powered by EmCa Techonology &copy; {{ $year }}
    </div>
</body>
</html>
