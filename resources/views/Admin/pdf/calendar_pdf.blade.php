<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }

        .info-section { margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 25%; }

        .stats-grid { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .stats-grid td { width: 25%; text-align: center; padding: 10px; border: 1px solid #eee; }
        .stat-val { font-size: 16px; font-weight: bold; color: #333; }
        .stat-label { font-size: 9px; color: #777; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 8px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; }
        table.data-table tr { page-break-inside: avoid; page-break-after: auto; }

        .section-title { font-size: 12px; font-weight: bold; color: #940000; border-bottom: 1px solid #940000; padding-bottom: 3px; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }

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

        .type-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            color: #fff;
            font-weight: bold;
        }
        .type-holiday { background-color: #dc3545; }
        .type-event { background-color: #007bff; }
        .type-public { background-color: #6c757d; }

        .description { font-style: italic; color: #666; font-size: 10px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="watermark">ShuleLink</div>
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
        $schoolLogoBase64 = getSmartBase64($school->school_logo ?? '');
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">SCHOOL ACADEMIC CALENDAR - {{ $currentYear }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Academic Year:</td>
                <td>{{ $currentYear }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Annual Summary Statistics</div>
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-val">{{ $stats['working_days'] }}</div>
                <div class="stat-label">Working Days</div>
            </td>
            <td>
                <div class="stat-val">{{ $stats['holiday_days'] }}</div>
                <div class="stat-label">Holiday Days</div>
            </td>
            <td>
                <div class="stat-val">{{ $stats['weekend_days'] }}</div>
                <div class="stat-label">Weekend Days</div>
            </td>
            <td>
                <div class="stat-val">{{ $stats['event_days'] }}</div>
                <div class="stat-label">School Events</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Monthly Working Days Breakdown</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Month</th>
                <th>Working Days</th>
                <th>Holidays</th>
                <th>Weekends</th>
                <th>Total Days</th>
            </tr>
        </thead>
        <tbody>
            @php
                $months = [
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ];
            @endphp
            @foreach($monthlyStats as $monthNum => $mStat)
                <tr>
                    <td>{{ $months[$monthNum] }}</td>
                    <td>{{ $mStat['working_days'] }}</td>
                    <td>{{ $mStat['holiday_days'] }}</td>
                    <td>{{ $mStat['weekend_days'] }}</td>
                    <td>{{ $mStat['total_days'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="page-break-after: always;"></div>

    <div class="section-title">School Defined Holidays</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Holiday Name</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($holidays as $holiday)
                <tr>
                    <td><strong>{{ $holiday->holiday_name }}</strong></td>
                    <td style="white-space: nowrap;">{{ strtolower(\Carbon\Carbon::parse($holiday->start_date)->format('d M Y')) }}</td>
                    <td style="white-space: nowrap;">{{ strtolower(\Carbon\Carbon::parse($holiday->end_date)->format('d M Y')) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Academic & School Events</div>
    @if($events->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Working Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr>
                        <td><strong>{{ $event->event_name }}</strong></td>
                        <td style="white-space: nowrap;">{{ strtolower(\Carbon\Carbon::parse($event->event_date)->format('d M Y')) }}</td>
                        <td style="white-space: nowrap;">{{ strtolower(\Carbon\Carbon::parse($event->event_date)->format('d M Y')) }}</td>
                        <td style="text-align: center;">
                            @if($event->is_non_working_day)
                                <span style="font-weight: bold;">no</span>
                            @else
                                <span style="font-weight: bold;">yes</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No school events scheduled for this year.</p>
    @endif

    <div class="section-title">Auto-Detected Public Holidays</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Holiday Name</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($autoHolidays as $holiday)
                <tr>
                    <td><strong>{{ $holiday['name'] }}</strong></td>
                    <td style="white-space: nowrap;">{{ strtolower($holiday['date']->format('d M Y')) }}</td>
                    <td style="white-space: nowrap;">{{ strtolower($holiday['date']->format('d M Y')) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated by ShuleLink (School Management System), Powered By EmCa Techonology &copy; {{ date('Y') }}
    </div>
</body>
</html>
