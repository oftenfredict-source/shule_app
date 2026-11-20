<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Logbook</title>
    <style>
        @page {
            margin: 2cm;
            size: A4 landscape;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.4;
            font-size: 11px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #940000;
            padding-bottom: 10px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #940000;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            width: 15%;
        }
        .value {
            border-bottom: 1px dotted #ccc;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .main-table th {
            background-color: #940000;
            color: white;
            padding: 8px;
            font-size: 10px;
            border: 1px solid #7b0000;
            text-transform: uppercase;
        }
        .main-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer-col {
            width: 33%;
            float: left;
            text-align: center;
        }
        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #333;
            display: inline-block;
            width: 80%;
        }
        .watermark {
            position: fixed;
            top: 30%;
            left: 20%;
            font-size: 80px;
            color: rgba(148, 0, 0, 0.05);
            transform: rotate(-45deg);
            z-index: -1;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="watermark">{{ $school->school_name ?? 'LOGBOOK' }}</div>

    <div class="header">
        <div class="school-name">{{ $school->school_name ?? 'SCHOOL NAME' }}</div>
        <div class="report-title">TEACHING PROGRESS LOGBOOK</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">SUBJECT:</td>
            <td class="value">{{ $classSubject->subject->subject_name ?? 'N/A' }}</td>
            <td class="label">CLASS:</td>
            <td class="value">{{ ($classSubject->subclass?->class?->class_name ?? $classSubject->class?->class_name ?? '') }} {{ ($classSubject->subclass?->subclass_name ?? '') }}</td>
        </tr>
        <tr>
            <td class="label">TEACHER:</td>
            <td class="value">{{ $teacherName }}</td>
            <td class="label">YEAR:</td>
            <td class="value">{{ $year }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 30px;">SN</th>
                <th>MAIN TOPIC</th>
                <th>SUB TOPIC</th>
                <th>CONTENT COVERED</th>
                <th>DATE STARTED</th>
                <th>DATE ENDED</th>
                <th>TEACHER'S COMMENT</th>
                <th>H.O.D COMMENT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logbookEntries as $index => $entry)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $entry->main_topic }}</td>
                    <td>{{ $entry->sub_topic }}</td>
                    <td>{{ $entry->content_covered }}</td>
                    <td>{{ $entry->date_started }}</td>
                    <td>{{ $entry->date_ended }}</td>
                    <td>{{ $entry->teacher_comment }}</td>
                    <td><i>{{ $entry->hod_comment ?? 'Pending...' }}</i></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="padding: 30px; color: #999;">No records found for this subject and class.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-col">
            <div class="report-title">Subject Teacher</div>
            <div class="signature-line"></div>
            <p>Signature & Date</p>
        </div>
        <div class="footer-col">
            <div class="report-title">Head of Department</div>
            <div class="signature-line"></div>
            <p>Signature & Date</p>
        </div>
        <div class="footer-col">
            <div class="report-title">Head of School</div>
            <div class="signature-line"></div>
            <p>Signature & Date</p>
        </div>
    </div>
</body>
</html>
