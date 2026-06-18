<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 0;
            size: 243pt 153pt;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', sans-serif;
            width: 243pt;
            height: 153pt;
            background: #ffffff;
            color: #333333;
            overflow: hidden;
            box-sizing: border-box;
            border: 1.5pt solid {{ $cardColor }};
            position: relative;
        }
        
        /* Colored header strip */
        .card-header-bar {
            height: 4.5pt;
            background: {{ $cardColor }};
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
        }
        
        /* Main Header containing School Logo & School Name */
        .card-header {
            position: absolute;
            top: 4.5pt;
            left: 0;
            width: 100%;
            height: 33pt;
            border-bottom: 1px solid #e9ecef;
            padding: 2.5pt 6pt;
            box-sizing: border-box;
        }
        
        .logo-box {
            position: absolute;
            top: 2.5pt;
            left: 6pt;
            width: 26pt;
            height: 26pt;
            text-align: center;
        }
        
        .logo-box img {
            max-width: 26pt;
            max-height: 26pt;
            object-fit: contain;
        }
        
        .school-info-text {
            margin-left: 32pt;
            margin-right: 6pt;
            text-align: left;
        }
        
        .school-name {
            font-size: 8pt;
            font-weight: bold;
            color: {{ $cardColor }};
            margin: 0;
            line-height: 1.1;
            text-transform: uppercase;
        }
        
        .school-reg {
            font-size: 5pt;
            color: #666666;
            margin: 1.5pt 0 0 0;
            font-weight: 500;
        }

        /* Body Section */
        .card-body {
            position: absolute;
            top: 38pt;
            left: 0;
            width: 100%;
            height: 90pt;
            padding: 4pt 6pt;
            box-sizing: border-box;
        }
        
        /* Left: Student Photo */
        .photo-container {
            position: absolute;
            top: 4pt;
            left: 6pt;
            width: 58pt;
            height: 70pt;
            border: 1.2pt solid {{ $cardColor }};
            background: #f8f9fa;
            overflow: hidden;
            box-sizing: border-box;
        }
        
        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Right: Student Details */
        .details-container {
            margin-left: 70pt;
            height: 74pt;
            position: relative;
        }
        
        .card-title {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 4.5pt 0;
            color: #111111;
            letter-spacing: 0.3px;
        }
        
        .info-row {
            margin-bottom: 3pt;
            font-size: 6.5pt;
            line-height: 1.1;
        }
        
        .info-label {
            display: inline-block;
            width: 48pt;
            font-weight: bold;
            color: #555555;
        }
        
        .info-value {
            display: inline-block;
            color: #111111;
            font-weight: 600;
        }

        /* Signature and Stamp */
        .signature-section {
            position: absolute;
            right: 6pt;
            bottom: 13pt;
            text-align: center;
            width: 72pt;
        }
        
        .signature-img {
            height: 16pt;
            margin-bottom: 1pt;
            text-align: center;
        }
        
        .signature-img img {
            max-width: 65pt;
            max-height: 16pt;
            display: inline-block;
        }
        
        .signature-line {
            border-top: 0.8px solid {{ $cardColor }};
            width: 68pt;
            margin: 0 auto;
            display: block;
        }
        
        .signature-label {
            font-size: 4.2pt;
            font-weight: bold;
            color: {{ $cardColor }};
            text-transform: uppercase;
            margin-top: 1.5pt;
            display: block;
            letter-spacing: 0.2px;
        }

        /* Stamp overlay */
        .stamp-container {
            position: absolute;
            right: 48pt;
            bottom: 12pt;
            width: 26pt;
            height: 26pt;
            opacity: 0.75;
            z-index: 5;
        }
        
        .stamp-container img {
            width: 26pt;
            height: 26pt;
            object-fit: contain;
        }

        /* Footer text bar */
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 11pt;
            background: #f8f9fa;
            border-top: 0.5px solid #e9ecef;
            text-align: center;
            line-height: 11pt;
            box-sizing: border-box;
        }
        
        .footer-text {
            font-size: 4.8pt;
            color: #666666;
            margin: 0;
            font-style: italic;
            font-weight: 500;
        }
    </style>
</head>
<body>
    @php
        if (!function_exists('getSmartBase64')) {
            function getSmartBase64($path) {
                if (!$path) return null;
                
                $path = ltrim($path, '/');
                $base = base_path();
                $parent = dirname($base);
                
                $possibilities = [
                    public_path($path),                             // Standard Laravel Public
                    public_path('uploads/' . $path),                // Standard Laravel Public Uploads
                    $parent . '/public_html/' . $path,              // cPanel / Shared Hosting
                    $parent . '/public_html/uploads/' . $path,      // cPanel / Shared Hosting Uploads
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
        
        // Resolve images to base64
        $schoolLogoBase64  = getSmartBase64($school->school_logo ?? '');
        $schoolStampBase64 = getSmartBase64($school->school_stamp ?? '');
        $schoolSignBase64  = getSmartBase64($school->school_signature ?? '');
        
        $studentPhotoPath = $student->photo ? (strpos($student->photo, 'userImages/') !== false ? $student->photo : 'userImages/' . $student->photo) : '';
        $studentPhotoBase64 = getSmartBase64($studentPhotoPath);
        if (!$studentPhotoBase64) {
            $fallbackPath = $student->gender == 'Female' ? 'images/female.png' : 'images/male.png';
            $studentPhotoBase64 = getSmartBase64($fallbackPath);
        }
        
        // Shorten class name if needed
        $className = 'N/A';
        if ($student->subclass) {
            $className = $student->subclass->class->class_name ?? 'N/A';
        }
    @endphp

    <!-- Card Top Border Strip -->
    <div class="card-header-bar"></div>

    <!-- Header Section -->
    <div class="card-header">
        <div class="logo-box">
            @if($schoolLogoBase64)
                <img src="{{ $schoolLogoBase64 }}">
            @endif
        </div>
        <div class="school-info-text">
            <h4 class="school-name">{{ $school->school_name ?? 'School Name' }}</h4>
            <p class="school-reg">Reg No: {{ $school->registration_number ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- Body Section -->
    <div class="card-body">
        <!-- Photo Container -->
        <div class="photo-container">
            @if($studentPhotoBase64)
                <img src="{{ $studentPhotoBase64 }}" alt="Photo">
            @endif
        </div>

        <!-- Details Container -->
        <div class="details-container">
            <h3 class="card-title">Student Identity Card</h3>
            
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $student->first_name }} {{ $student->middle_name ? $student->middle_name . ' ' : '' }}{{ $student->last_name }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Admin No:</span>
                <span class="info-value">{{ $student->admission_number }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Class:</span>
                <span class="info-value">{{ $className }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Gender:</span>
                <span class="info-value">{{ $student->gender }}</span>
            </div>
        </div>
        
        <!-- Stamp Overlay -->
        @if($schoolStampBase64)
            <div class="stamp-container">
                <img src="{{ $schoolStampBase64 }}">
            </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-img">
                @if($schoolSignBase64)
                    <img src="{{ $schoolSignBase64 }}">
                @endif
            </div>
            <span class="signature-line"></span>
            <span class="signature-label">HEADMASTER</span>
        </div>
    </div>

    <!-- Footer Strip -->
    <div class="card-footer">
        <p class="footer-text">This card remains the property of {{ $school->school_name ?? 'School Name' }}</p>
    </div>
</body>
</html>
