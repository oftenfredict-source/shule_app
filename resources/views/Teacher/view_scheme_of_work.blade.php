<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>View Scheme of Work</title>
    
    <link rel="stylesheet" href="{{ asset('vendors/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
    <style>
        * {
            font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
        }
        
        .fa, .fa:before, i.fa, [class*="fa-"]:before, [class^="fa-"]:before {
            font-family: 'FontAwesome' !important;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .header-section {
            background: linear-gradient(135deg, #940000 0%, #b30000 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .content-wrapper {
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn-primary-custom {
            background-color: #940000;
            border-color: #940000;
            color: #ffffff;
        }
        
        .btn-primary-custom:hover {
            background-color: #b30000;
            border-color: #b30000;
            color: #ffffff;
        }
        
        .scheme-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .scheme-table th,
        .scheme-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .scheme-table th {
            background-color: #940000;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .month-header {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            width: 30px;
            background-color: #940000;
            color: white;
            font-weight: bold;
        }
        
        .month-cell {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            width: 30px;
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .holiday-row {
            background-color: #ffe6e6 !important;
        }
        
        .holiday-cell {
            background-color: #ffcccc;
            text-align: center;
            font-weight: bold;
            color: #cc0000;
            padding: 15px !important;
        }
        
        .row-number {
            width: 40px;
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .done-tick {
            color: #28a745;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .objectives-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .info-section {
            background-color: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-0">
                        <i class="fa fa-eye"></i> View Scheme of Work
                    </h3>
                    <p class="mb-0 mt-2">
                        Subject: <strong>{{ $scheme->classSubject->subject->subject_name ?? 'N/A' }}</strong>
                        @if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class)
                            | Class: <strong>{{ $scheme->classSubject->subclass->class->class_name }} {{ $scheme->classSubject->subclass->subclass_name }}</strong>
                        @elseif($scheme->classSubject->class)
                            | Class: <strong>{{ $scheme->classSubject->class->class_name }}</strong>
                        @endif
                        | Year: <strong>{{ $scheme->year }}</strong>
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="d-inline-block mr-2">
                        <select id="languageSelector" class="form-control form-control-sm" style="width: auto; display: inline-block;" onchange="changeLanguage(this.value)">
                            <option value="en">English</option>
                            <option value="sw">Swahili</option>
                        </select>
                    </div>
                    <a href="{{ route('teacher.schemeOfWork') }}" class="btn btn-light">
                        <i class="fa fa-arrow-left"></i> Go Back
                    </a>
                    <button type="button" class="btn btn-danger ml-2" id="exportPdfBtn" onclick="downloadPDF()">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-success ml-2" onclick="downloadExcel()">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="content-wrapper">
            <!-- School and Teacher Info -->
            <div class="info-section" id="infoSection">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong data-translate="nameOfSchool">NAME OF SCHOOL:</strong> {{ $school->school_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong data-translate="teacherName">TEACHER'S NAME:</strong> {{ $scheme->createdBy->first_name ?? '' }} {{ $scheme->createdBy->last_name ?? '' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong data-translate="subject">SUBJECT:</strong> {{ strtoupper($scheme->classSubject->subject->subject_name ?? 'N/A') }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong data-translate="year">YEAR:</strong> {{ $scheme->year }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong data-translate="class">CLASS:</strong> 
                            @if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class)
                                {{ strtoupper($scheme->classSubject->subclass->class->class_name . ' ' . $scheme->classSubject->subclass->subclass_name) }}
                            @elseif($scheme->classSubject->class)
                                {{ strtoupper($scheme->classSubject->class->class_name) }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Learning Objectives Section -->
            @if($scheme->learningObjectives && $scheme->learningObjectives->count() > 0)
            <div class="objectives-section" id="objectivesSection">
                @php
                    $schoolType = $school->school_type ?? 'Primary';
                    if($schoolType === 'Primary') {
                        $objectivesTitle = "Objectives of Primary Education";
                    } else {
                        $objectivesTitle = "Learning Objectives";
                    }
                @endphp
                <h5 class="mb-3">
                    <i class="fa fa-list"></i> <span data-translate="objectivesTitle">{{ $objectivesTitle }}</span>
                </h5>
                @php
                    $schoolType = $school->school_type ?? 'Primary';
                    $subjectName = $scheme->classSubject->subject->subject_name ?? '';
                    $mainClassName = '';
                    if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class) {
                        $mainClassName = $scheme->classSubject->subclass->class->class_name;
                    } elseif($scheme->classSubject->class) {
                        $mainClassName = $scheme->classSubject->class->class_name;
                    }
                    
                    // Determine objectives intro text
                    if($schoolType === 'Primary') {
                        // Check if main class starts with "Standard 3" or similar (Standard III, Standard IV, Standard V, Standard VI, Standard 7)
                        $mainClassNameLower = strtolower(trim($mainClassName));
                        if(preg_match('/^standard\s*(3|iii|iv|v|vi|4|5|6|7)/i', $mainClassNameLower)) {
                            // Standard 3-7 - use fixed format
                            $objectivesIntro = "The objectives of Primary Education Standard III – VI are to:";
                        } else {
                            // Other primary classes (Baby class, Standard 1, Standard 2) - use fixed format for lower classes
                            $objectivesIntro = "The objectives of Primary Education Standard Baby class – standard2 are to:";
                        }
                    } else {
                        // Secondary school - just "Learning Objectives" (no intro text)
                        $objectivesIntro = "";
                    }
                @endphp
                @if(!empty($objectivesIntro))
                <p class="mb-3"><strong data-translate="objectivesIntro">{{ $objectivesIntro }}</strong></p>
                @endif
                <ol id="objectivesList">
                    @foreach($scheme->learningObjectives as $objective)
                        <li style="margin-bottom: 10px;" data-objective-id="{{ $objective->objective_id }}">{{ $objective->objective_text }}</li>
                    @endforeach
                </ol>
            </div>
            @endif

            <!-- Scheme of Work Table -->
            <div class="mt-4">
                <h5 class="mb-3">
                    <i class="fa fa-table"></i> <span data-translate="schemeOfWorkTable">Scheme of Work Table</span>
                </h5>

                <div style="overflow-x: auto;">
                    <table class="scheme-table" id="schemeTable">
                        <thead>
                            <tr>
                                <th class="row-number">#</th>
                                <th data-translate="mainCompetence">Main Competence</th>
                                <th data-translate="specificCompetences">Specific Competences</th>
                                <th data-translate="learningActivities">Learning Activities</th>
                                <th data-translate="specificActivities">Specific Activities</th>
                                <th class="month-header" data-translate="month">Month</th>
                                <th data-translate="week">Week</th>
                                <th data-translate="numberOfPeriods">Number of Periods</th>
                                <th data-translate="teachingMethods">Teaching and Learning Methods</th>
                                <th data-translate="teachingResources">Teaching and Learning Resources</th>
                                <th data-translate="assessmentTools">Assessment Tools</th>
                                <th data-translate="references">References</th>
                                <th data-translate="remarks">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $rowCount = 1;
                                $itemsByMonth = $scheme->items->groupBy('month');
                                
                                // Define month order
                                $monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 
                                             'July', 'August', 'September', 'October', 'November', 'December'];
                                
                                // Sort months by order
                                $sortedMonths = collect($monthOrder)->filter(function($month) use ($itemsByMonth) {
                                    return $itemsByMonth->has($month);
                                })->merge($itemsByMonth->keys()->diff($monthOrder))->toArray();
                            @endphp
                            
                            @foreach($sortedMonths as $month)
                                @if($itemsByMonth->has($month))
                                    @php
                                        $monthItems = $itemsByMonth->get($month);
                                        $monthRowspan = $monthItems->count();
                                        $isFirstRow = true;
                                    @endphp
                                    
                                    @foreach($monthItems as $item)
                                        <tr data-month="{{ $month }}">
                                            <td class="row-number">{{ $rowCount }}</td>
                                            <td>{{ $item->main_competence ?? '' }}</td>
                                            <td>{{ $item->specific_competences ?? '' }}</td>
                                            <td>{{ $item->learning_activities ?? '' }}</td>
                                            <td>{{ $item->specific_activities ?? '' }}</td>
                                            @if($isFirstRow)
                                                <td class="month-cell" rowspan="{{ $monthRowspan }}">{{ $month }}</td>
                                                @php $isFirstRow = false; @endphp
                                            @endif
                                            <td>{{ $item->week ?? '' }}</td>
                                            <td>{{ $item->number_of_periods ?? '' }}</td>
                                            <td>{{ $item->teaching_methods ?? '' }}</td>
                                            <td>{{ $item->teaching_resources ?? '' }}</td>
                                            <td>{{ $item->assessment_tools ?? '' }}</td>
                                            <td>{{ $item->references ?? '' }}</td>
                                            <td style="text-align: center;">
                                                @if(!empty($item->remarks) && strtolower(trim($item->remarks)) === 'done')
                                                    <span class="done-tick">✓</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @php $rowCount++; @endphp
                                    @endforeach
                                    
                                    {{-- Add separator row after each month (except last) --}}
                                    @if(!$loop->last)
                                        <tr class="month-separator">
                                            <td colspan="13" style="background-color: #e0e0e0; height: 2px; padding: 0; border: none;"></td>
                                        </tr>
                                    @endif
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 for notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- SheetJS for Excel export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    <script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    
    <script>
        // Ensure jsPDF is available globally
        if (typeof window.jspdf !== 'undefined' && !window.jsPDF) {
            window.jsPDF = window.jspdf.jsPDF;
        }
        
        // Language translations
        const translations = {
            en: {
                schemeOfWork: 'SCHEME OF WORK',
                subject: 'SUBJECT:',
                year: 'YEAR:',
                class: 'CLASS:',
                teacherName: 'TEACHER\'S NAME:',
                objectivesTitle: 'Objectives of Primary Education The objectives of Primary Education',
                areTo: 'are to:',
                schemeOfWorkTable: 'Scheme of Work Table',
                poweredBy: 'Powered by: EmCa Technologies LTD',
                mainCompetence: 'Main Competence',
                specificCompetences: 'Specific Competences',
                learningActivities: 'Learning Activities',
                specificActivities: 'Specific Activities',
                month: 'Month',
                week: 'Week',
                numberOfPeriods: 'Number of Periods',
                teachingMethods: 'Teaching and Learning Methods',
                teachingResources: 'Teaching and Learning Resources',
                assessmentTools: 'Assessment Tools',
                references: 'References',
                remarks: 'Remarks',
                months: {
                    'January': 'January',
                    'February': 'February',
                    'March': 'March',
                    'April': 'April',
                    'May': 'May',
                    'June': 'June',
                    'July': 'July',
                    'August': 'August',
                    'September': 'September',
                    'October': 'October',
                    'November': 'November',
                    'December': 'December'
                }
            },
            sw: {
                schemeOfWork: 'RATIBA YA KAZI',
                subject: 'SOMO:',
                year: 'MWAKA:',
                class: 'DARASA:',
                teacherName: 'JINA LA MWALIMU:',
                objectivesTitle: 'Malengo ya Elimu ya Msingi Malengo ya Elimu ya Msingi',
                areTo: 'ni:',
                schemeOfWorkTable: 'Jedwali la Ratiba ya Kazi',
                poweredBy: 'Inaendeshwa na: EmCa Technologies LTD',
                mainCompetence: 'Uwezo Mkuu',
                specificCompetences: 'Uwezo Maalum',
                learningActivities: 'Shughuli za Kujifunza',
                specificActivities: 'Shughuli Maalum',
                month: 'Mwezi',
                week: 'Wiki',
                numberOfPeriods: 'Idadi ya Vipindi',
                teachingMethods: 'Mbinu za Kufundisha na Kujifunza',
                teachingResources: 'Rasilimali za Kufundisha na Kujifunza',
                assessmentTools: 'Zana za Tathmini',
                references: 'Marejeo',
                remarks: 'Maelezo',
                months: {
                    'January': 'Januari',
                    'February': 'Februari',
                    'March': 'Machi',
                    'April': 'Aprili',
                    'May': 'Mei',
                    'June': 'Juni',
                    'July': 'Julai',
                    'August': 'Agosti',
                    'September': 'Septemba',
                    'October': 'Oktoba',
                    'November': 'Novemba',
                    'December': 'Desemba'
                }
            }
        };
        
        // Get current language
        function getCurrentLanguage() {
            const selector = document.getElementById('languageSelector');
            return selector ? selector.value : 'en';
        }
        
        // Get translation
        function t(key) {
            const lang = getCurrentLanguage();
            return translations[lang][key] || translations.en[key] || key;
        }
        
        // Function to translate month name
        function translateMonth(monthName) {
            const lang = getCurrentLanguage();
            const monthTranslations = translations[lang].months || translations.en.months;
            return monthTranslations[monthName] || monthName;
        }
        
        // Change language function with AJAX and Google Translate
        function changeLanguage(lang) {
            console.log('Changing language to:', lang);
            
            // Update all static text on the page
            document.querySelectorAll('[data-translate]').forEach(function(element) {
                const key = element.getAttribute('data-translate');
                const translation = t(key);
                if (element.tagName === 'STRONG' || element.tagName === 'SPAN') {
                    element.textContent = translation;
                } else {
                    element.textContent = translation;
                }
            });
            
            // Update month names in table
            document.querySelectorAll('.month-cell').forEach(function(cell) {
                const monthName = cell.textContent.trim();
                if (monthName) {
                    cell.textContent = translateMonth(monthName);
                }
            });
            
            // Translate dynamic content using MyMemory Translation API (free alternative to Google Translate)
            if (lang === 'sw') {
                translateDynamicContent(lang);
            } else {
                restoreOriginalContent();
            }
        }
        
        // Translate dynamic content using MyMemory Translation API
        function translateDynamicContent(targetLang) {
            if (targetLang === 'en') {
                restoreOriginalContent();
                return;
            }
            
            // Translate objectives
            const objectives = document.querySelectorAll('#objectivesList li');
            objectives.forEach(function(li) {
                const originalText = li.getAttribute('data-original-text') || li.textContent.trim();
                if (!li.getAttribute('data-original-text')) {
                    li.setAttribute('data-original-text', originalText);
                }
                
                // Use MyMemory API (free, no API key needed)
                const apiUrl = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(originalText)}&langpair=en|sw`;
                
                fetch(apiUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.responseData && data.responseData.translatedText) {
                            // Preserve number prefix if exists
                            const match = originalText.match(/^(\d+\.)\s*(.+)$/);
                            if (match) {
                                li.textContent = match[1] + ' ' + data.responseData.translatedText;
                            } else {
                                li.textContent = data.responseData.translatedText;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Translation error:', error);
                    });
            });
            
            // Translate table content (main competence, specific competences, etc.)
            const tableRows = document.querySelectorAll('#schemeTable tbody tr:not(.month-separator)');
            tableRows.forEach(function(row) {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 5) {
                    // Main Competence (index 1)
                    if (cells[1]) translateCell(cells[1], targetLang);
                    // Specific Competences (index 2)
                    if (cells[2]) translateCell(cells[2], targetLang);
                    // Learning Activities (index 3)
                    if (cells[3]) translateCell(cells[3], targetLang);
                    // Specific Activities (index 4)
                    if (cells[4]) translateCell(cells[4], targetLang);
                    // Teaching Methods (index 8)
                    if (cells[8]) translateCell(cells[8], targetLang);
                    // Teaching Resources (index 9)
                    if (cells[9]) translateCell(cells[9], targetLang);
                    // Assessment Tools (index 10)
                    if (cells[10]) translateCell(cells[10], targetLang);
                    // References (index 11)
                    if (cells[11]) translateCell(cells[11], targetLang);
                }
            });
        }
        
        // Translate individual cell
        function translateCell(cell, targetLang) {
            const originalText = cell.getAttribute('data-original-text') || cell.textContent.trim();
            if (!originalText || originalText === '') return;
            
            if (!cell.getAttribute('data-original-text')) {
                cell.setAttribute('data-original-text', originalText);
            }
            
            if (targetLang === 'en') {
                // Restore original
                cell.textContent = originalText;
                return;
            }
            
            // Use MyMemory API (free, no API key needed)
            const apiUrl = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(originalText)}&langpair=en|sw`;
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.responseData && data.responseData.translatedText) {
                        cell.textContent = data.responseData.translatedText;
                    }
                })
                .catch(error => {
                    console.error('Translation error:', error);
                });
        }
        
        // Restore original content
        function restoreOriginalContent() {
            document.querySelectorAll('[data-original-text]').forEach(function(element) {
                const original = element.getAttribute('data-original-text');
                if (original) {
                    element.textContent = original;
                }
            });
        }
            // ── SERVER-SIDE PDF DOWNLOAD (Subject Analysis Method) ──────────
        function downloadPDF() {
            const schemeID = '{{ $scheme->scheme_of_workID }}';
            const pdfUrl   = '{{ route("teacher.exportSchemeOfWorkPDF", ["schemeOfWorkID" => $scheme->scheme_of_workID]) }}';

            // Show progress modal
            $('#pdfProgressModal').modal('show');
            $('#schemePdfBar').css('width', '0%').attr('aria-valuenow', 0);
            $('#schemePdfPercent').text('0%');

            let progress = 0;
            const interval = setInterval(function() {
                if (progress < 95) {
                    // Slower increment as it gets higher to look more natural
                    let inc = 2 + Math.random() * 5;
                    if (progress > 50) inc = 1 + Math.random() * 2;
                    if (progress > 80) inc = 0.5 + Math.random();
                    
                    progress += inc;
                    if (progress > 95) progress = 95;
                    
                    $('#schemePdfBar').css('width', progress + '%');
                    $('#schemePdfPercent').text(Math.round(progress) + '%');
                }
            }, 400);

            fetch(pdfUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Server error: ' + response.statusText);
                    return response.blob();
                })
                .then(blob => {
                    clearInterval(interval);
                    $('#schemePdfBar').css('width', '100%');
                    $('#schemePdfPercent').text('100%');
                    
                    setTimeout(() => {
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = downloadUrl;
                        a.download = `Scheme_of_Work_{{ str_replace(' ', '_', $scheme->classSubject->subject->subject_name ?? 'Subject') }}_{{ $scheme->year }}.pdf`;
                        
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);
                        
                        // Small delay before hide
                        setTimeout(() => {
                            $('#pdfProgressModal').modal('hide');
                            // Reset for next time
                            setTimeout(() => { 
                                $('#schemePdfBar').css('width', '0%'); 
                                $('#schemePdfPercent').text('0%'); 
                            }, 500);
                        }, 800);
                    }, 500);
                })
                .catch(error => {
                    clearInterval(interval);
                    console.error('PDF generation failed:', error);
                    $('#pdfProgressModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Export Failed',
                        text: 'Unable to generate PDF. Please try again or contact support.'
                    });
                });
        }


        function downloadExcel() {
            console.log('Excel download clicked');
            
            // Check if XLSX is available
            if (typeof XLSX === 'undefined') {
                console.error('XLSX library not found');
                alert('Excel library not loaded. Please refresh the page.');
                return;
            }
            
            console.log('XLSX library found, starting export...');
            
            try {
                const wb = XLSX.utils.book_new();
                const data = [];
                
                // Get current language for Excel
                const currentLang = getCurrentLanguage();
                const t_excel = translations[currentLang] || translations.en;
                
                // Header rows
                data.push(['{{ strtoupper(addslashes($school->school_name ?? "N/A")) }}']);
                data.push([t_excel.schemeOfWork]);
                data.push([]); // Empty row
                
                // Info table
                data.push([t_excel.subject, '{{ strtoupper(addslashes($scheme->classSubject->subject->subject_name ?? "N/A")) }}', '', t_excel.year, '{{ $scheme->year }}', '', t_excel.class, '{{ strtoupper(addslashes(($scheme->classSubject->subclass && $scheme->classSubject->subclass->class ? $scheme->classSubject->subclass->class->class_name . " " . $scheme->classSubject->subclass->subclass_name : ($scheme->classSubject->class ? $scheme->classSubject->class->class_name : "N/A")))) }}']);
                data.push([t_excel.teacherName, '{{ addslashes(($scheme->createdBy->first_name ?? "") . " " . ($scheme->createdBy->last_name ?? "")) }}']);
                data.push([]); // Empty row
                
                // Learning Objectives
                @if($scheme->learningObjectives && $scheme->learningObjectives->count() > 0)
                @php
                    $schoolType = $school->school_type ?? 'Primary';
                    $subjectName = $scheme->classSubject->subject->subject_name ?? '';
                    $mainClassName = '';
                    if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class) {
                        $mainClassName = $scheme->classSubject->subclass->class->class_name;
                    } elseif($scheme->classSubject->class) {
                        $mainClassName = $scheme->classSubject->class->class_name;
                    }
                    
                    // Determine objectives title text
                    if($schoolType === 'Primary') {
                        // Check if main class starts with "Standard 3" or similar (Standard III, Standard IV, Standard V, Standard VI, Standard 7)
                        $mainClassNameLower = strtolower(trim($mainClassName));
                        if(preg_match('/^standard\s*(3|iii|iv|v|vi|4|5|6|7)/i', $mainClassNameLower)) {
                            // Standard 3-7 - use fixed format
                            $objectivesTitleText = "Objectives of Primary Education The objectives of Primary Education Standard III – VI are to:";
                        } else {
                            // Other primary classes (Baby class, Standard 1, Standard 2) - use fixed format for lower classes
                            $objectivesTitleText = "Objectives of Primary Education The objectives of Primary Education Standard Baby class – standard2 are to:";
                        }
                    } else {
                        // Secondary school - just "Learning Objectives"
                        $objectivesTitleText = "Learning Objectives";
                    }
                @endphp
                data.push(['{{ addslashes($objectivesTitleText) }}']);
                @foreach($scheme->learningObjectives as $objective)
                    data.push(['{{ $loop->iteration }}.', '{{ addslashes($objective->objective_text) }}']);
                @endforeach
                data.push([]); // Empty row
                @endif
                
                // Table headers
                data.push(['#', t_excel.mainCompetence, t_excel.specificCompetences, t_excel.learningActivities, t_excel.specificActivities, 
                          t_excel.month, t_excel.week, t_excel.numberOfPeriods, t_excel.teachingMethods, 
                          t_excel.teachingResources, t_excel.assessmentTools, t_excel.references, t_excel.remarks]);
                
                // Table data (ordered by months)
                @php
                    $rowNum = 1;
                    $itemsByMonth = $scheme->items->groupBy('month');
                    
                    // Define month order
                    $monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 
                                 'July', 'August', 'September', 'October', 'November', 'December'];
                    
                    // Sort months by order
                    $sortedMonths = collect($monthOrder)->filter(function($month) use ($itemsByMonth) {
                        return $itemsByMonth->has($month);
                    })->merge($itemsByMonth->keys()->diff($monthOrder))->toArray();
                @endphp
                
                @foreach($sortedMonths as $month)
                    @if($itemsByMonth->has($month))
                        @php
                            $monthItems = $itemsByMonth->get($month);
                            $isFirstRow = true;
                        @endphp
                        
                        @foreach($monthItems as $item)
                            @php
                                $isFirst = $isFirstRow;
                                if($isFirstRow) {
                                    $isFirstRow = false;
                                }
                            @endphp
                            data.push([
                                {{ $rowNum }},
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->main_competence ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->specific_competences ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->learning_activities ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->specific_activities ?? "")) }}',
                                @if($isFirst)
                                    translateMonth('{{ addslashes($month) }}')
                                @else
                                    ''
                                @endif,
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->week ?? "")) }}',
                                '{{ $item->number_of_periods ?? "" }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->teaching_methods ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->teaching_resources ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->assessment_tools ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->references ?? "")) }}',
                                '{{ strtolower(trim($item->remarks ?? "")) === "done" ? "done" : addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->remarks ?? "")) }}'
                            ]);
                            @php $rowNum++; @endphp
                        @endforeach
                    @endif
                @endforeach
                
                // Create worksheet
                const ws = XLSX.utils.aoa_to_sheet(data);
                
                // Set column widths
                ws['!cols'] = [
                    { wch: 5 },   // #
                    { wch: 30 },  // Main Competence
                    { wch: 30 },  // Specific Competences
                    { wch: 30 },  // Learning Activities
                    { wch: 30 },  // Specific Activities
                    { wch: 15 },  // Month
                    { wch: 15 },  // Week
                    { wch: 12 },  // Number of Periods
                    { wch: 35 },  // Teaching Methods
                    { wch: 30 },  // Teaching Resources
                    { wch: 30 },  // Assessment Tools
                    { wch: 35 },  // References
                    { wch: 15 }   // Remarks
                ];
                
                // Style header row (row with table headers)
                @php
                    $headerRowIndex = 0;
                    if($scheme->learningObjectives && $scheme->learningObjectives->count() > 0) {
                        $headerRowIndex = 3 + 1 + $scheme->learningObjectives->count() + 1; // School, Title, empty, info rows, objectives, empty
                    } else {
                        $headerRowIndex = 3 + 1; // School, Title, empty, info rows
                    }
                @endphp
                const headerRowIndex = {{ $headerRowIndex }};
                
                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(wb, ws, 'Scheme of Work');
                
                // Generate filename
                const filename = 'Scheme_of_Work_{{ str_replace(" ", "_", $scheme->classSubject->subject->subject_name ?? "Subject") }}_{{ $scheme->year }}.xlsx';
                
                // Save file
                console.log('Saving Excel file:', filename);
                XLSX.writeFile(wb, filename);
                console.log('Excel file saved successfully');
                
            } catch (error) {
                console.error('Excel Export Error:', error);
                console.error('Error stack:', error.stack);
                alert('Failed to export Excel: ' + error.message);
            }
        }
        
    </script>

    <!-- PDF Progress Modal -->
    <div class="modal fade" id="pdfProgressModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 420px;">
            <div class="modal-content" style="border-radius: 0; border-top: 4px solid #940000;">
                <div class="modal-header" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%); border-bottom: 1px solid #eee; padding: 15px 20px;">
                    <h5 class="modal-title" style="color: #940000; font-weight: bold;">
                        <i class="fa fa-file-pdf-o"></i> Generating Scheme of Work PDF...
                    </h5>
                </div>
                <div class="modal-body" style="padding: 25px 20px;">
                    <p class="text-muted mb-3" style="font-size: 0.9rem;">Please wait while we generate your professional PDF document.</p>
                    <div class="progress" style="height: 12px; border-radius: 2px; background: #f5f5f5; border: 1px solid #eee;">
                        <div id="schemePdfBar" class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                             style="width: 0%; background-color: #940000;"></div>
                    </div>
                    <div class="text-center mt-2" style="color: #940000; font-weight: bold; font-size: 0.85rem;">
                        <span id="schemePdfPercent">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
