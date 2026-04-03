<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Profile - {{ $teacher->first_name }} {{ $teacher->last_name }}</title>
    <style>
        body {
            font-family: 'Century Gothic', Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #940000;
            padding-bottom: 15px;
            display: table;
        }
        .header-cell {
            display: table-cell;
            vertical-align: middle;
        }
        .school-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        .teacher-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 2px solid #940000;
        }
        .school-name {
            font-size: 18pt;
            font-weight: bold;
            color: #940000;
            text-align: center;
            margin: 0;
        }
        .report-title {
            font-size: 14pt;
            text-align: center;
            margin: 5px 0 0 0;
            color: #444;
            text-transform: uppercase;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .content-table th {
            background-color: #f8f9fa;
            color: #940000;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            width: 30%;
        }
        .content-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .footer {
            position: fixed;
            bottom: 30px;
            width: 100%;
            text-align: center;
            font-size: 9pt;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10pt;
        }
        .status-active {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-other {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .section-title {
            background: #940000;
            color: #fff;
            padding: 5px 10px;
            margin-top: 30px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-cell" style="width: 20%;">
            @if($school && $school->school_logo)
                <img src="{{ public_path($school->school_logo) }}" class="school-logo">
            @endif
        </div>
        <div class="header-cell" style="width: 60%; text-align: center;">
            <p class="school-name">{{ strtoupper($school->school_name ?? 'School Name') }}</p>
            <p class="report-title">Teacher Profile Report</p>
        </div>
        <div class="header-cell" style="width: 20%; text-align: right;">
            @php
                $teacherImg = $teacher->image 
                    ? public_path('userImages/' . $teacher->image) 
                    : ($teacher->gender == 'Female' ? public_path('images/female.png') : public_path('images/male.png'));
            @endphp
            @if(file_exists($teacherImg))
                <img src="{{ $teacherImg }}" class="teacher-photo">
            @endif
        </div>
    </div>

    <div class="section-title">Personal Information</div>
    <table class="content-table">
        <tr>
            <th>Full Name</th>
            <td>{{ strtoupper($teacher->first_name . ' ' . $teacher->middle_name . ' ' . $teacher->last_name) }}</td>
        </tr>
        <tr>
            <th>Gender</th>
            <td>{{ $teacher->gender }}</td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td>{{ $teacher->date_of_birth ? date('d M Y', strtotime($teacher->date_of_birth)) : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Address</th>
            <td>{{ $teacher->address ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <span class="status-badge {{ strtolower($teacher->status) == 'active' ? 'status-active' : 'status-other' }}">
                    {{ $teacher->status }}
                </span>
            </td>
        </tr>
    </table>

    <div class="section-title">Professional Details</div>
    <table class="content-table">
        <tr>
            <th>Employee Number</th>
            <td>{{ $teacher->employee_number }}</td>
        </tr>
        <tr>
            <th>Position</th>
            <td>{{ $teacher->position ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>National ID</th>
            <td>{{ $teacher->national_id }}</td>
        </tr>
        <tr>
            <th>Fingerprint ID</th>
            <td>{{ $teacher->fingerprint_id ?? 'Not Assigned' }}</td>
        </tr>
        <tr>
            <th>Qualification</th>
            <td>{{ $teacher->qualification ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Specialization</th>
            <td>{{ $teacher->specialization ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Experience</th>
            <td>{{ $teacher->experience ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Date Hired</th>
            <td>{{ $teacher->date_hired ? date('d M Y', strtotime($teacher->date_hired)) : 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Contact & Financial Details</div>
    <table class="content-table">
        <tr>
            <th>Email</th>
            <td>{{ $teacher->email }}</td>
        </tr>
        <tr>
            <th>Phone Number</th>
            <td>{{ $teacher->phone_number }}</td>
        </tr>
        <tr>
            <th>Bank Account Number</th>
            <td>{{ $teacher->bank_account_number ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="footer">
        Generated by ShuleLinkWeb powered by EmCa Technology | {{ date('d M Y H:i:s') }}
    </div>
</body>
</html>
