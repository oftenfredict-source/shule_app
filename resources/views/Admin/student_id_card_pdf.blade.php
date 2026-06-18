<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { size: 85.6mm 53.98mm; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { margin: 0; padding: 0; font-family: Helvetica, Arial, sans-serif; color: #222; }

        .id-page {
            width: 85.6mm;
            height: 53.98mm;
            overflow: hidden;
            background: #fff;
            position: relative;
        }
        .id-page-front { page-break-after: always; }
        .id-page-back-after { page-break-after: always; }

        .corner-accent {
            position: absolute;
            top: 0;
            right: 0;
            width: 13mm;
            height: 6mm;
            background: {{ $primaryColor ?? '#940000' }};
            border-top-right-radius: 1.5mm;
            border-bottom-left-radius: 5.5mm;
            z-index: 2;
        }
        .dot-grid {
            position: absolute;
            top: 7mm;
            right: 2mm;
            z-index: 2;
        }
        .dot-cell { width: 1mm; height: 1mm; background: #d5d5d5; border-radius: 50%; }

        .logo-watermark {
            position: absolute;
            left: 25.8mm;
            top: 13mm;
            width: 34mm;
            z-index: 0;
        }
        .logo-watermark img {
            width: 34mm;
            height: auto;
            opacity: 0.08;
        }

        .card-table {
            width: 85.6mm;
            height: 53.98mm;
            table-layout: fixed;
            border-collapse: collapse;
            position: relative;
            z-index: 1;
        }

        .hdr-row { height: 13.5mm; }
        .hdr-row > td {
            text-align: left;
            vertical-align: middle;
            padding: 1.2mm 14mm 0.5mm 2mm;
        }
        .hdr-inner { border-collapse: collapse; width: auto; }
        .hdr-inner td { vertical-align: middle; text-align: left; }
        .hdr-logo-cell { width: 10mm; padding: 0 1mm 0 0; }
        .hdr-text-cell { padding: 0; text-align: left; }
        .logo-img { width: 9.5mm; height: 9.5mm; object-fit: contain; display: block; }
        .school-name {
            font-size: 7.5pt;
            font-weight: bold;
            color: {{ $primaryColor ?? '#940000' }};
            text-transform: uppercase;
            line-height: 1.15;
            text-align: left;
        }
        .school-sub {
            font-size: 5.1pt;
            color: #888;
            margin-top: 0.3mm;
            line-height: 1.1;
            text-align: left;
        }

        .body-row { height: 38.98mm; }
        .body-row td { padding: 1mm 2.5mm 2mm; vertical-align: middle; }
        .photo-wrap {
            width: 23mm;
            height: 29mm;
            border: 0.4mm solid #b8d4f0;
            border-radius: 2mm;
            overflow: hidden;
            background: #e8f2fc;
        }
        .photo-wrap img { width: 23mm; height: 29mm; object-fit: cover; display: block; }

        .student-name {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1a1a1a;
            line-height: 1.05;
            margin-bottom: 0.8mm;
        }
        .name-line {
            height: 0.35mm;
            background: {{ $primaryColor ?? '#940000' }};
            width: 100%;
            margin-bottom: 1.5mm;
        }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 0.55mm 0; vertical-align: middle; font-size: 5.8pt; }
        .info-icon-img {
            width: 4.8mm;
            height: 4.8mm;
            display: block;
        }
        .info-label { color: {{ $primaryColor ?? '#940000' }}; font-weight: bold; }
        .info-value { color: #222; }

        .footer-bar-abs {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 85.6mm;
            height: 2mm;
            z-index: 5;
        }
        .footer-track {
            width: 100%;
            height: 2mm;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .footer-fill {
            width: 78%;
            height: 2mm;
            background: {{ $primaryColor ?? '#940000' }};
            border-radius: 0 2mm 2mm 0;
        }
        .footer-rest {
            width: 22%;
            height: 2mm;
            background: #e6e6e6;
        }

        .back-logo-row { height: 14.5mm; text-align: center; vertical-align: middle; }
        .back-logo-row td { padding: 2mm 3mm 1mm; }
        .back-logo-row img { max-height: 11mm; max-width: 38mm; object-fit: contain; }

        .back-banner-row { height: 18.5mm; }
        .back-banner-row td { padding: 0; }
        .back-banner {
            background: {{ $primaryColor ?? '#940000' }};
            color: #fff;
            text-align: center;
            vertical-align: middle;
            height: 18.5mm;
            padding: 2mm 3mm;
        }
        .banner-line { font-size: 4.6pt; line-height: 1.35; }
        .banner-school {
            font-size: 6.8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0.8mm 0;
            line-height: 1.15;
        }

        .back-contact-row { height: 20.98mm; }
        .back-contact-row td { text-align: center; vertical-align: middle; padding: 1mm 3mm 2mm; }
        .back-contact { font-size: 5.2pt; line-height: 1.4; color: #222; }
        .contact-name-table { margin: 0 auto 1mm; border-collapse: collapse; }
        .contact-name-table td { vertical-align: middle; padding: 0 0.5mm; }
        .back-school-name {
            font-size: 5.8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1a1a1a;
        }
        .contact-phone-table { margin: 0.8mm auto 0; border-collapse: collapse; }
        .contact-phone-table td { vertical-align: middle; padding: 0 0.5mm; }
        .contact-pin-img { width: 3.2mm; height: 3.2mm; display: block; }
        .phone-icon-img {
            width: 4.8mm;
            height: 4.8mm;
            display: block;
        }
    </style>
</head>
<body>
@include('Admin.partials.student_id_card_helpers')

@php $pc = $primaryColor ?? '#940000'; $iconPx = idCardIconPixelSize(); @endphp

@foreach($students as $student)
    @php
        $schoolData = $student->school ?? $school;
        $logoPath = $student->id_card_logo_path ?? null;
        $logoBase64 = $logoPath ? idCardGetSmartBase64($logoPath) : null;
        $placeholder = strtolower($student->gender ?? 'male') === 'female' ? 'images/female.png' : 'images/male.png';
        $photoPath = ($student->photo && file_exists(public_path('userImages/' . $student->photo)))
            ? 'userImages/' . $student->photo
            : $placeholder;
        $photoBase64 = idCardGetSmartBase64($photoPath);
        $schoolName = $schoolData->school_name ?? 'School';
        $schoolHeaderName = idCardSchoolHeaderName($schoolName);
        $backPageClass = 'id-page' . (!$loop->last ? ' id-page-back-after' : '');
    @endphp

    <div class="id-page id-page-front">
        @if($logoBase64)
        <div class="logo-watermark"><img src="{{ $logoBase64 }}"></div>
        @endif
        <div class="corner-accent"></div>
        <table cellpadding="0" cellspacing="0.5mm" class="dot-grid" align="right">
            @for($r = 0; $r < 3; $r++)
            <tr>
                @for($c = 0; $c < 7; $c++)
                <td class="dot-cell"></td>
                @endfor
            </tr>
            @endfor
        </table>
        <table class="card-table" cellpadding="0" cellspacing="0">
            <tr class="hdr-row">
                <td align="left">
                    <table class="hdr-inner" cellpadding="0" cellspacing="0" align="left">
                        <tr>
                            <td class="hdr-logo-cell" align="left" valign="middle">
                                @if($logoBase64)<img src="{{ $logoBase64 }}" class="logo-img">@endif
                            </td>
                            <td class="hdr-text-cell" align="left" valign="middle">
                                <div class="school-name">{{ $schoolHeaderName }}</div>
                                <div class="school-sub">Student Identity Card</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="body-row">
                <td>
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width:26mm; vertical-align:middle;">
                                <div class="photo-wrap">
                                    @if($photoBase64)<img src="{{ $photoBase64 }}">@endif
                                </div>
                            </td>
                            <td style="padding-left:2.5mm; vertical-align:middle;">
                                <div class="student-name">{{ $student->id_card_full_name }}</div>
                                <div class="name-line"></div>
                                <table class="info-table">
                                    <tr>
                                        <td style="width:6mm;"><img src="{{ idCardIconDataUri('id', $pc) }}" class="info-icon-img" width="{{ $iconPx }}" height="{{ $iconPx }}"></td>
                                        <td><span class="info-label">Adm No:</span> <span class="info-value">{{ $student->id_card_admission }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><img src="{{ idCardIconDataUri('class', $pc) }}" class="info-icon-img" width="{{ $iconPx }}" height="{{ $iconPx }}"></td>
                                        <td><span class="info-label">Class:</span> <span class="info-value">{{ $student->id_card_main_class }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><img src="{{ idCardIconDataUri('sex', $pc) }}" class="info-icon-img" width="{{ $iconPx }}" height="{{ $iconPx }}"></td>
                                        <td><span class="info-label">Sex:</span> <span class="info-value">{{ $student->id_card_sex }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><img src="{{ idCardIconDataUri('expire', $pc) }}" class="info-icon-img" width="{{ $iconPx }}" height="{{ $iconPx }}"></td>
                                        <td><span class="info-label">Expire:</span> <span class="info-value">{{ $student->id_card_expire_date }}</span></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="footer-bar-abs">
            <table class="footer-track" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="footer-fill" style="width:78%;">&nbsp;</td>
                    <td class="footer-rest" style="width:22%;">&nbsp;</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="id-page {{ $backPageClass }}">
        @if($logoBase64)
        <div class="logo-watermark"><img src="{{ $logoBase64 }}"></div>
        @endif
        <table class="card-table" cellpadding="0" cellspacing="0">
            <tr class="back-logo-row">
                <td>@if($logoBase64)<img src="{{ $logoBase64 }}">@endif</td>
            </tr>
            <tr class="back-banner-row">
                <td class="back-banner">
                    <div class="banner-line">This identity card is the property of</div>
                    <div class="banner-school">{{ strtoupper($schoolName) }}</div>
                    <div class="banner-line">and is not transferable. This card must be returned to the Headmaster</div>
                    <div class="banner-line">upon termination of studies. If found please return to address below:</div>
                </td>
            </tr>
            <tr class="back-contact-row">
                <td>
                    <div class="back-contact">
                        <table class="contact-name-table" align="center" cellpadding="0" cellspacing="0">
                            <tr>
                                <td><img src="{{ idCardIconDataUri('pin', $pc) }}" class="contact-pin-img" width="{{ $iconPx }}" height="{{ $iconPx }}"></td>
                                <td class="back-school-name">{{ strtoupper($schoolName) }}</td>
                            </tr>
                        </table>
                        @if(!empty($schoolData->address)){{ $schoolData->address }}<br>@endif
                        @if(!empty($schoolData->district) || !empty($schoolData->region))
                            {{ trim(($schoolData->district ?? '') . ($schoolData->region ? ', ' . $schoolData->region : '')) }}<br>
                        @endif
                        @if(!empty($schoolData->phone))
                        <table class="contact-phone-table" align="center" cellpadding="0" cellspacing="0">
                            <tr>
                                <td><img src="{{ idCardIconDataUri('phone', $pc) }}" class="phone-icon-img" width="{{ $iconPx }}" height="{{ $iconPx }}"></td>
                                <td>Tel: {{ $schoolData->phone }}</td>
                            </tr>
                        </table>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
        <div class="footer-bar-abs">
            <table class="footer-track" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="footer-fill" style="width:78%;">&nbsp;</td>
                    <td class="footer-rest" style="width:22%;">&nbsp;</td>
                </tr>
            </table>
        </div>
    </div>
@endforeach
</body>
</html>
