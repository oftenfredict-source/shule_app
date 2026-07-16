<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payments Status Report — {{ $yearLabel ?? $year }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; background: #fff; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }

        .info-section { margin-bottom: 16px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 22%; }

        .section-title { font-size: 12px; font-weight: bold; color: #940000; border-bottom: 1px solid #940000; padding-bottom: 5px; margin-top: 16px; margin-bottom: 10px; }

        .stats-grid { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .stats-grid td { text-align: center; padding: 10px; border: 1px solid #eee; }
        .stat-val { font-size: 14px; font-weight: bold; color: #333; }
        .stat-label { font-size: 9px; color: #777; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 6px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; }
        table.data-table td.amount-col { text-align: right; font-weight: bold; }
        table.data-table tr.total-row td { background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #940000; }
        .muted { color: #777; font-size: 9px; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; background: #fff; }

        .sig-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 50%; text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 120px; max-height: 120px; opacity: 0.85; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 40px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 150px; max-height: 40px; }
        .sig-line   { border-top: 1.5px solid #940000; width: 160px; margin: 0 auto; display: block; }
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
        $students = $students ?? [];
        $summary = $summary ?? [];

        $totalStudents = (int) ($summary['total_students'] ?? count($students));
        $sumRequired = (float) ($summary['total_required'] ?? 0);
        $sumPaid = (float) ($summary['total_paid'] ?? 0);
        $sumBalance = (float) ($summary['total_balance'] ?? 0);
        $deadlineUnpaid = (int) ($summary['deadline_unpaid'] ?? 0);
        $deadlinePaid = (int) ($summary['deadline_paid'] ?? 0);
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">{{ $reportTitle ?? ('PAYMENTS STATUS REPORT — ' . ($yearLabel ?? $year)) }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Academic Year:</td>
                <td>{{ $yearLabel }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Applied Filters:</td>
                <td colspan="3">
                    @php
                        $parts = [];
                        if (!empty(data_get($filters, 'class'))) $parts[] = data_get($filters, 'class');
                        if (!empty(data_get($filters, 'subclass'))) $parts[] = data_get($filters, 'subclass');
                        if (!empty(data_get($filters, 'fee'))) $parts[] = data_get($filters, 'fee');
                        if (!empty(data_get($filters, 'status'))) $parts[] = 'Enrollment: ' . data_get($filters, 'status');
                        if (!empty(data_get($filters, 'payment_status'))) $parts[] = 'Payment: ' . data_get($filters, 'payment_status');
                        if (!empty(data_get($filters, 'deadline_filter'))) $parts[] = 'Deadline: ' . data_get($filters, 'deadline_filter');
                        if (!empty(data_get($filters, 'sponsorship'))) $parts[] = 'Sponsorship: ' . data_get($filters, 'sponsorship');
                    @endphp
                    {{ !empty($parts) ? implode(' | ', $parts) : 'None (all matching records)' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Overview</div>
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-val">{{ $totalStudents }}</div>
                <div class="stat-label">Students</div>
            </td>
            <td>
                <div class="stat-val">{{ number_format($sumRequired, 0) }}/=</div>
                <div class="stat-label">Fee Required</div>
            </td>
            <td>
                <div class="stat-val" style="color: #28a745;">{{ number_format($sumPaid, 0) }}/=</div>
                <div class="stat-label">Total Paid</div>
            </td>
            <td>
                <div class="stat-val" style="color: #940000;">{{ number_format($sumBalance, 0) }}/=</div>
                <div class="stat-label">Outstanding</div>
            </td>
            <td>
                <div class="stat-val" style="color: #dc3545;">{{ $deadlineUnpaid }}</div>
                <div class="stat-label">Deadline Unpaid</div>
            </td>
            <td>
                <div class="stat-val" style="color: #28a745;">{{ $deadlinePaid }}</div>
                <div class="stat-label">Deadline Paid</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Student Payment Details</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 26%;">Student</th>
                <th style="width: 14%;">Class</th>
                <th class="amount-col" style="width: 12%;">Required</th>
                <th class="amount-col" style="width: 12%;">Paid</th>
                <th class="amount-col" style="width: 12%;">Balance</th>
                <th style="width: 10%;">Deadline</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $row)
                @php
                    $status = data_get($row, 'payment_status', 'Pending');
                    $statusColor = '#940000';
                    if ($status === 'Paid') $statusColor = '#28a745';
                    elseif ($status === 'Overpaid') $statusColor = '#17a2b8';
                    elseif (stripos((string) $status, 'Incomplete') !== false || $status === 'Partial') $statusColor = '#fd7e14';

                    $dReq = (float) data_get($row, 'deadline_required', 0);
                    $dPaidAmt = (float) data_get($row, 'deadline_paid', 0);
                    $dMet = $dReq > 0 ? ($dPaidAmt + 0.00001 >= $dReq) : true;
                    $middle = data_get($row, 'student.middle_name')
                        ? substr((string) data_get($row, 'student.middle_name'), 0, 1) . '.'
                        : '';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ data_get($row, 'student.first_name') }} {{ $middle }} {{ data_get($row, 'student.last_name') }}</strong>
                        <div class="muted">{{ data_get($row, 'student.admission_number') }}</div>
                        @if((float) data_get($row, 'student.sponsorship_percentage', 0) > 0)
                            <div class="muted">Sponsored {{ data_get($row, 'student.sponsorship_percentage') }}% — {{ data_get($row, 'student.sponsor_name', 'Sponsor') }}</div>
                        @endif
                    </td>
                    <td>
                        {{ data_get($row, 'student.class', 'N/A') }}
                        <div class="muted">{{ data_get($row, 'student.enrollment_status') ?: data_get($row, 'student.status') }}</div>
                    </td>
                    <td class="amount-col">{{ number_format((float) data_get($row, 'totalRequired', 0), 0) }}</td>
                    <td class="amount-col" style="color: #28a745;">{{ number_format((float) data_get($row, 'totalPaid', 0), 0) }}</td>
                    <td class="amount-col" style="color: #940000;">{{ number_format((float) data_get($row, 'totalBalance', 0), 0) }}</td>
                    <td>
                        @if($dReq > 0 && !$dMet)
                            <span style="color: #dc3545; font-weight: bold;">{{ number_format($dPaidAmt, 0) }} / {{ number_format($dReq, 0) }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td style="color: {{ $statusColor }}; font-weight: bold;">{{ $status }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">Totals</td>
                <td class="amount-col">{{ number_format($sumRequired, 0) }}</td>
                <td class="amount-col" style="color: #28a745;">{{ number_format($sumPaid, 0) }}</td>
                <td class="amount-col" style="color: #940000;">{{ number_format($sumBalance, 0) }}</td>
                <td colspan="2"></td>
            </tr>
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
