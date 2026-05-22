<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif; font-size: 14px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #940000; padding-bottom: 8px; }
        .school-name { font-size: 16px; font-weight: bold; color: #940000; text-transform: uppercase; margin-top: 5px; }
        .report-title { font-size: 12px; font-weight: bold; margin-top: 8px; background: #f8f9fa; padding: 5px; text-transform: uppercase; border: 1px solid #ddd; }

        .info-section { margin-bottom: 10px; border: 1px solid #ddd; padding: 8px; border-radius: 3px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 4px; border-bottom: 1px dashed #eee; vertical-align: top; }
        .info-table tr:last-child td { border-bottom: none; }
        .label { font-weight: bold; color: #555; width: 25%; }

        .section-title { font-size: 16px; font-weight: bold; color: #940000; margin-bottom: 5px; border-bottom: 1px solid #940000; padding-bottom: 3px; }

        .description-box {
            background: #f9f9f9;
            border: 1px solid #eee;
            padding: 5px;
            min-height: auto;
            margin-bottom: 8px;
            white-space: pre-wrap;
        }

        table.chain-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.chain-table th, table.chain-table td { border: 1px solid #ddd; padding: 3px; text-align: left; font-size: 13px; }
        table.chain-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; }

        /* ── SIGNATURE AND STAMP ─────────────────── */
        .sig-table { width: 100%; margin-top: 20px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { border: none; padding: 0; vertical-align: bottom; }
        .stamp-cell { width: 40%; text-align: center; vertical-align: middle; }
        .stamp-cell img { max-width: 240px; max-height: 240px; opacity: 1; }
        .sign-cell { width: 45%; text-align: center; padding-bottom: 5px; }
        .school-signature-img { height: 60px; margin-bottom: 4px; }
        .school-signature-img img { max-width: 200px; max-height: 60px; }
        .sig-line   { border-top: 1.5px solid #940000; width: 160px; margin: 0 auto; display: block; }
        .sig-label  { font-size: 10px; color: #940000; font-weight: bold; padding-top: 4px; display: block; text-transform: uppercase;}

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }

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
            z-index: -1000;
            text-transform: uppercase;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .status-approved { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-rejected { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
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
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 80px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'Shule' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Simu: {{ $school->phone ?? '' }} | Barua pepe: {{ $school->email ?? '' }}</div>
        <div class="report-title">MAELEZO YA OMBI LA RUHUSA KWA MWANAFUNZI</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Jina la Mwanafunzi:</td>
                <td><strong>{{ $studentName }}</strong></td>
                <td class="label">Darasa:</td>
                <td>{{ $className }}</td>
            </tr>
            <tr>
                <td class="label">Aliyeomba:</td>
                <td><strong>{{ $requesterName }}</strong></td>
                <td class="label">Nafasi:</td>
                <td>{{ $requesterInfo }}</td>
            </tr>
            <tr>
                <td class="label">Tarehe ya Ombi:</td>
                <td>{{ $permission->created_at ? \Carbon\Carbon::parse($permission->created_at)->format('d M Y H:i') : 'N/A' }}</td>
                <td class="label">Hali ya Sasa:</td>
                <td>
                    <span class="status-badge status-{{ strtolower($permission->status) }}">
                        {{ $permission->status === 'approved' ? 'IMEIDHINISHWA' : ($permission->status === 'rejected' ? 'IMEKATALIWA' : 'INASUBIRI') }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">Kundi la Sababu:</td>
                <td colspan="3">{{ ucfirst($permission->reason_type) }}</td>
            </tr>

            @if($permission->time_mode === 'days')
            <tr>
                <td class="label">Muda:</td>
                <td>{{ $permission->days_count }} Siku</td>
                <td class="label">Tarehe ya Kurudi:</td>
                <td>{{ $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->addDay()->format('d M Y') : 'Hapana' }}</td>
            </tr>
            <tr>
                <td class="label">Kipindi:</td>
                <td colspan="3">
                    {{ $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('d M Y') : 'N/A' }}
                    <strong>mpaka</strong>
                    {{ $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('d M Y') : 'N/A' }}
                </td>
            </tr>
            @else
            <tr>
                <td class="label">Muda wa Saa:</td>
                <td colspan="3">{{ $permission->start_time }} - {{ $permission->end_time }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section-title">Maelezo ya Sababu</div>
    <div class="description-box">
        {{ $permission->reason_description ?: 'Hakuna maelezo yamepatikana.' }}
    </div>

    <div class="section-title">Msimu wa Idhinisho na Hali</div>
    @if($chain->count() > 0)
        <table class="chain-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Hatua</th>
                    <th style="width: 30%;">Nafasi Inayohitajika</th>
                    <th style="width: 30%;">Maoni</th>
                    <th style="width: 15%;">Hali</th>
                    <th style="width: 15%;">Tarehe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chain as $index => $step)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $step->role_name }}</td>
                        <td>
                            @if($permission->current_approval_order > $index + 1)
                                Imeidhinishwa
                            @elseif($permission->current_approval_order == $index + 1)
                                Inasubiri
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($permission->current_approval_order > $index + 1)
                                <span class="status-badge status-approved">Imeidhinishwa</span>
                            @elseif($permission->current_approval_order == $index + 1)
                                <span class="status-badge status-pending">Inasubiri</span>
                            @else
                                <span class="status-badge status-pending">Inasubiri</span>
                            @endif
                        </td>
                        <td>
                            @if($permission->current_approval_order > $index + 1)
                                {{ $permission->updated_at ? \Carbon\Carbon::parse($permission->updated_at)->format('d M Y') : '-' }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="description-box" style="min-height: auto; margin-bottom: 10px;">
            <em>Hakuna msimu wa idhinisho umewekwa.</em>
        </div>
    @endif

    @if(strtolower($permission->status) === 'approved')
    <table class="sig-table">
        <tr>
            <td class="stamp-cell">
                @if($schoolStampBase64)
                    <img src="{{ $schoolStampBase64 }}">
                @endif
            </td>
            <td class="sign-cell">
                <div class="school-signature-img">
                    @if($schoolSignBase64)
                        <img src="{{ $schoolSignBase64 }}">
                    @endif
                </div>
                <span class="sig-line"></span>
                <span class="sig-label">SAINI ILIYOIDHINISHWA</span>
            </td>
        </tr>
    </table>
    @endif

    <div class="footer">
        Imetengenezwa na ShuleXpert (Mfumo wa Usimamizi wa Shule), Inaendeshwa na EmCa Technology &copy; {{ date('Y') }}
    </div>
</body>
</html>
