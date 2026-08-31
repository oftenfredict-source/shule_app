@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
        --att-border: #e8e4e4;
        --att-surface: #ffffff;
        --att-muted: #6b7280;
    }

    body {
        background-color: #f4f5f7;
    }

    /* ── Page header ── */
    .att-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--att-border);
    }

    .att-page-header h2 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .att-page-header h2 i {
        color: var(--primary-color);
        font-size: 1.5rem;
    }

    /* ── Mode switch (replaces nav-tabs) ── */
    .att-mode-switch {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 22px;
        padding: 6px;
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    }

    .att-mode-btn {
        flex: 1 1 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 14px 18px;
        border: 2px solid transparent;
        background: #f8f9fa;
        color: #4b5563;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
        min-height: 52px;
    }

    .att-mode-btn .att-mode-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(148, 0, 0, 0.08);
        color: var(--primary-color);
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .att-mode-btn:hover:not(.active) {
        background: #fff5f5;
        border-color: rgba(148, 0, 0, 0.2);
        color: var(--primary-color);
    }

    .att-mode-btn.active {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.28);
    }

    .att-mode-btn.active .att-mode-icon {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    /* ── Filter panel ── */
    .att-filter-panel {
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        margin-bottom: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    }

    .att-filter-panel-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        background: linear-gradient(90deg, #940000 0%, #b30000 100%);
        color: #fff;
    }

    .att-filter-panel-head i {
        font-size: 1.25rem;
        opacity: 0.95;
    }

    .att-filter-panel-head h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .att-filter-panel-head small {
        opacity: 0.85;
        font-size: 0.78rem;
        margin-left: auto;
        display: none;
    }

    @media (min-width: 768px) {
        .att-filter-panel-head small {
            display: block;
        }
    }

    .att-filter-panel-body {
        padding: 20px;
    }

    .att-filter-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 16px 20px;
    }

    .att-filter-item {
        flex: 1 1 200px;
        min-width: 0;
        max-width: 100%;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .att-filter-item.att-filter-item--narrow {
        flex: 0 1 140px;
    }

    .att-filter-item.att-filter-item--medium {
        flex: 1 1 220px;
    }

    .att-filter-item label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #374151;
        margin: 0;
        line-height: 1.2;
    }

    .att-filter-item .form-control,
    .att-filter-item .form-select {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 0;
        font-size: 0.9rem;
        padding: 8px 12px;
    }

    .att-filter-item .form-control:focus,
    .att-filter-item .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(148, 0, 0, 0.12);
    }

    .att-filter-hint {
        font-size: 0.72rem;
        color: var(--att-muted);
        line-height: 1.35;
        min-height: 1.35em;
    }

    .att-filter-action {
        flex: 0 0 150px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding-top: calc(0.78rem * 1.2 + 6px);
    }

    .att-filter-btn {
        height: 42px;
        width: 100%;
        border: none;
        background: var(--primary-color);
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: background 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 8px rgba(148, 0, 0, 0.25);
    }

    .att-filter-btn:hover {
        background: #b30000;
        color: #fff;
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.35);
    }

    /* SMS toolbar */
    .att-sms-strip {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 12px 16px;
        padding: 14px 16px;
        margin-bottom: 16px;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-left: 4px solid #f59e0b;
    }

    .att-sms-strip label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #92400e;
        margin-bottom: 4px;
    }

    .att-sms-strip .form-select {
        height: 38px;
        min-width: 220px;
        border-radius: 0;
    }

    .search-card {
        background: white;
        border-radius: 4px;
        padding: 25px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
    }

    .search-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .search-title i {
        margin-right: 10px;
        color: var(--primary-color);
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 6px;
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .form-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 4px;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15);
    }

    .btn-search {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        padding: 10px 24px;
        border-radius: 4px;
        font-weight: 500;
    }

    .btn-search:hover {
        background: #b30000;
        border-color: #b30000;
        color: white;
    }

    .stats-card {
        background: white;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }

    .stat-item {
        text-align: center;
        padding: 15px;
        border-radius: 4px;
        background: #f8f9fa;
        margin-bottom: 10px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #212529;
        margin: 5px 0;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
    }

    .chart-card {
        background: white;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 15px;
    }

    .data-table-card {
        background: white;
        border-radius: 4px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }

    .table {
        font-size: 0.9rem;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    .badge-present {
        background-color: #10b981;
        color: white;
    }

    .badge-absent {
        background-color: #ef4444;
        color: white;
    }

    .badge-late {
        background-color: #f59e0b;
        color: white;
    }

    .badge-excused {
        background-color: #6366f1;
        color: white;
    }

    .btn-view {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .btn-view:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .btn-print {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
    }

    .btn-print:hover {
        background: #e9ecef;
        border-color: #dee2e6;
    }

    .section-header {
        font-size: 1.4rem;
        font-weight: 600;
        color: #212529;
        margin: 25px 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .section-header--legacy {
        display: none;
    }

    .hidden {
        display: none !important;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }

    @media (max-width: 576px) {
        .att-mode-btn {
            flex: 1 1 100%;
        }

        .att-filter-action {
            flex: 1 1 100%;
            padding-top: 0;
        }

        .att-sms-strip {
            flex-direction: column;
            align-items: stretch;
        }

        .att-sms-strip .form-select {
            min-width: 0;
            width: 100%;
        }
    }
</style>

<div class="container-fluid" style="padding: 20px;">

    <div class="att-page-header no-print">
        <h2><i class="bi bi-calendar-check"></i> Attendance Management</h2>
    </div>

    <!-- Mode switch: System / Fingerprint / Exam -->
    <div class="att-mode-switch no-print" id="attendanceTabs" role="tablist">
        <button type="button" class="att-mode-btn active" id="system-attendance-tab" onclick="showAttendanceTab('system')">
            <span class="att-mode-icon"><i class="bi bi-grid-1x2-fill"></i></span>
            <span class="att-mode-text">System Attendance</span>
        </button>
        <button type="button" class="att-mode-btn" id="fingerprint-attendance-tab" onclick="showAttendanceTab('fingerprint')">
            <span class="att-mode-icon"><i class="bi bi-fingerprint"></i></span>
            <span class="att-mode-text">Fingerprint Attendance</span>
        </button>
        @if(!isset($isCoordinatorView) || !$isCoordinatorView)
        <button type="button" class="att-mode-btn" id="exam-attendance-tab" onclick="showAttendanceTab('exam')">
            <span class="att-mode-icon"><i class="bi bi-journal-check"></i></span>
            <span class="att-mode-text">Exam Attendance</span>
        </button>
        @endif
    </div>

    <!-- System Attendance Section (existing logic) -->
    <div id="systemAttendanceSection">
        <div class="att-sms-strip no-print" id="attendanceSmsToolbar">
            <div class="flex-grow-1" style="min-width: 200px;">
                @if(isset($isCoordinatorView) && $isCoordinatorView)
                    <label for="smsSubclassPicker">Subclass for SMS</label>
                    <select class="form-select form-select-sm" id="smsSubclassPicker">
                        <option value="">Select subclass for SMS...</option>
                        @foreach($coordinatorSubclasses as $subclass)
                            <option value="{{ $subclass->subclassID }}">{{ $subclass->display_name }}</option>
                        @endforeach
                    </select>
                @else
                    <label for="smsSubclassPicker">Subclass for SMS</label>
                    <select class="form-select form-select-sm" id="smsSubclassPicker">
                        <option value="">Select subclass for SMS...</option>
                        @foreach($subclasses as $subclass)
                            <option value="{{ $subclass->subclassID }}">{{ $subclass->display_name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div>
                <button type="button" class="btn btn-primary-custom btn-sm px-4" id="btnOpenAttendanceSms" style="height: 38px;">
                    <i class="bi bi-chat-dots"></i> Send Attendance SMS
                </button>
            </div>
        </div>

        <div class="att-filter-panel no-print">
            <div class="att-filter-panel-head">
                <i class="bi bi-sliders"></i>
                <h5>Search System Attendance</h5>
                <small>Filter by class, stream, date, month, year + status</small>
            </div>
            <div class="att-filter-panel-body">
                <form id="searchAttendanceForm">
                    <div class="att-filter-row">
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Search Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="search_type" name="search_type" required>
                                <option value="date" selected>By Date (Day)</option>
                                <option value="date_range">Date Range</option>
                                <option value="month">By Month</option>
                                <option value="year">By Year</option>
                            </select>
                            <span class="att-filter-hint">How to search attendance</span>
                        </div>
                        <div class="att-filter-item att-filter-item--medium" id="date_field" style="display: flex;">
                            <label>Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}">
                            <span class="att-filter-hint">Specific day</span>
                        </div>
                        <div class="att-filter-item att-filter-item--medium" id="from_date_field" style="display: none;">
                            <label>From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date">
                            <span class="att-filter-hint">Range start</span>
                        </div>
                        <div class="att-filter-item att-filter-item--medium" id="to_date_field" style="display: none;">
                            <label>To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date">
                            <span class="att-filter-hint">Range end</span>
                        </div>
                        <div class="att-filter-item att-filter-item--narrow" id="year_field" style="display: none;">
                            <label>Year</label>
                            <select class="form-select" id="year" name="year">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <span class="att-filter-hint">Calendar year</span>
                        </div>
                        <div class="att-filter-item att-filter-item--narrow" id="month_field" style="display: none;">
                            <label>Month</label>
                            <select class="form-select" id="month" name="month">
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                            <span class="att-filter-hint">Month name</span>
                        </div>
                        @if(isset($isCoordinatorView) && $isCoordinatorView && $selectedClass)
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Main Class</label>
                            <input type="text" class="form-control" value="{{ $selectedClass->class_name }}" readonly style="background:#f3f4f6;cursor:not-allowed;">
                            <input type="hidden" name="classID" id="lockedClassID" value="{{ $classIDParam }}">
                            <span class="att-filter-hint"><i class="bi bi-lock-fill"></i> Coordinator class (locked)</span>
                        </div>
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Subclass / Stream</label>
                            <select class="form-select" id="subclassFilter" name="subclassID">
                                <option value="">All Streams</option>
                                @foreach($coordinatorSubclasses as $subclass)
                                    <option value="{{ $subclass->subclassID }}">{{ $subclass->display_name }}</option>
                                @endforeach
                            </select>
                            <span class="att-filter-hint">All streams or one stream</span>
                        </div>
                        @else
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Main Class</label>
                            <select class="form-select" id="sysMainClassFilter" name="classID">
                                <option value="">All Classes</option>
                                @foreach(($mainClasses ?? []) as $mc)
                                    <option value="{{ $mc->classID }}">{{ $mc->class_name }}</option>
                                @endforeach
                            </select>
                            <span class="att-filter-hint">All or one main class</span>
                        </div>
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Subclass / Stream</label>
                            <select class="form-select" id="sysSubclassFilter" name="subclassID">
                                <option value="">All Streams</option>
                                @foreach($subclasses as $subclass)
                                    <option value="{{ $subclass->subclassID }}" data-class-id="{{ $subclass->classID ?? '' }}">{{ $subclass->display_name }}</option>
                                @endforeach
                            </select>
                            <span class="att-filter-hint">All streams or one stream</span>
                        </div>
                        @endif
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Attendance Status</label>
                            <select class="form-select" id="attendance_status" name="attendance_status">
                                <option value="all" selected>All Statuses</option>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Sick">Sick</option>
                                <option value="Excused">Permission</option>
                            </select>
                            <span class="att-filter-hint">Filter students by status</span>
                        </div>
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Enrollment Status</label>
                            <select class="form-select" id="enrollment_status" name="enrollment_status">
                                <option value="Active" selected>Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Dropped">Dropped</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="all">All enrollments</option>
                            </select>
                            <span class="att-filter-hint">Header academic year: <strong>{{ $academicYearLabel ?? '-' }}</strong></span>
                        </div>
                        <div class="att-filter-action">
                            <button type="submit" class="att-filter-btn">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" style="display: none;">
            
            <!-- Attendance by Class with Students -->
            <div class="row mb-4" id="attendanceByClassSection">
                <div class="col-12">
                    <div class="data-table-card">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h4 class="mb-0">
                                <i class="bi bi-bar-chart"></i> Attendance Report
                            </h4>
                            <div class="d-flex gap-3 align-items-center flex-wrap">
                                <div style="min-width: 250px; flex: 0 0 auto;">
                                    <select class="form-select form-select-sm" id="searchClasses">
                                        <option value="">All Classes</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary-custom btn-sm" id="btnSendStatusFilterSms" title="Send SMS to parents of filtered students" style="display: none;">
                                    <i class="bi bi-chat-dots"></i> Send SMS
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="exportAttendanceReportPdfBtn" title="Download full attendance report PDF" style="display: none;">
                                    <i class="bi bi-file-earmark-pdf"></i> Download Full PDF
                                </button>
                            </div>
                        </div>
                        <div id="attendanceByClassContent">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fingerprint Attendance Section (external API) -->
    <div id="fingerprintAttendanceSection" style="display: none;">
        <div class="att-filter-panel no-print">
            <div class="att-filter-panel-head">
                <i class="bi bi-fingerprint"></i>
                <h5>Search Fingerprint Attendance</h5>
                <small>Device check-in / check-out records</small>
            </div>
            <div class="att-filter-panel-body">
                <form id="searchFingerprintAttendanceForm">
                    <div class="att-filter-row">
                        @if(isset($isCoordinatorView) && $isCoordinatorView && $selectedClass)
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Main Class</label>
                            <input type="text" class="form-control" value="{{ $selectedClass->class_name }}" readonly style="background:#f3f4f6;cursor:not-allowed;">
                            <input type="hidden" name="classID" id="fingerprintLockedClassID" value="{{ $classIDParam }}">
                            <span class="att-filter-hint"><i class="bi bi-lock-fill"></i> Coordinator class (locked)</span>
                        </div>
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Subclass</label>
                            <select class="form-select" id="fingerprintSubclassFilter" name="subclassID">
                                <option value="">All Subclasses</option>
                                @foreach($coordinatorSubclasses as $subclass)
                                    <option value="{{ $subclass->subclassID }}">{{ $subclass->display_name }}</option>
                                @endforeach
                            </select>
                            <span class="att-filter-hint">Optional subclass filter</span>
                        </div>
                        @endif
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Search Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="fingerprint_search_type" name="search_type" required>
                                <option value="">Select Type</option>
                                <option value="date">By Date</option>
                                <option value="month">By Month</option>
                                <option value="year">By Year</option>
                            </select>
                            <span class="att-filter-hint">How to search attendance</span>
                        </div>
                        <div class="att-filter-item att-filter-item--medium">
                            <label>Enrollment Status</label>
                            <select class="form-select" id="fingerprint_enrollment_status" name="enrollment_status">
                                <option value="Active" selected>Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Dropped">Dropped</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="all">All enrollments</option>
                            </select>
                            <span class="att-filter-hint">Header year: <strong>{{ $academicYearLabel ?? '-' }}</strong></span>
                        </div>
                        <div class="att-filter-item att-filter-item--medium" id="fingerprint_date_field" style="display: none;">
                            <label>Date</label>
                            <input type="date" class="form-control" id="fingerprint_date" name="date">
                            <span class="att-filter-hint">Specific day</span>
                        </div>
                        <div class="att-filter-item att-filter-item--narrow" id="fingerprint_year_field" style="display: none;">
                            <label>Year</label>
                            <select class="form-select" id="fingerprint_year" name="year">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <span class="att-filter-hint">Calendar year</span>
                        </div>
                        <div class="att-filter-item att-filter-item--narrow" id="fingerprint_month_field" style="display: none;">
                            <label>Month</label>
                            <select class="form-select" id="fingerprint_month" name="month">
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                            <span class="att-filter-hint">Month name</span>
                        </div>
                        <div class="att-filter-action">
                            <button type="submit" class="att-filter-btn">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Section -->
        <div id="fingerprintResultsSection" style="display: none;">
            <!-- Attendance by Class with Students -->
            <div class="row mb-4" id="fingerprintAttendanceByClassSection">
                <div class="col-12">
                    <div class="data-table-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">
                                <i class="bi bi-people"></i> Fingerprint Attendance by Class
                            </h4>
                            <div class="d-flex gap-3 align-items-center flex-wrap">
                                <div style="min-width: 250px; flex: 0 0 auto;">
                                    <select class="form-select form-select-sm" id="searchFingerprintClasses" style="width: 100%;">
                                        <option value="">All Classes</option>
                                    </select>
                                </div>
                                <div class="btn-group" role="group" style="flex: 0 0 auto;">
                                    <button type="button" class="btn btn-success btn-sm" id="exportFingerprintExcelBtn" title="Export to Excel" style="display: inline-block;">
                                        <i class="bi bi-file-earmark-excel"></i> Excel
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" id="exportFingerprintPdfBtn" title="Export to PDF" style="display: inline-block;">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="fingerprintAttendanceByClassContent">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Attendance Section -->
    <div id="examAttendanceSection" style="display: none;">
        <div class="att-filter-panel no-print">
            <div class="att-filter-panel-head">
                <i class="bi bi-journal-check"></i>
                <h5>Filter Exam Attendance</h5>
                <small>Select exam, class, and subject</small>
            </div>
            <div class="att-filter-panel-body">
                <div class="att-filter-row">
                    <div class="att-filter-item att-filter-item--narrow">
                        <label>Year <span class="text-danger">*</span></label>
                        <select class="form-select" id="examFilterYear">
                            <option value="">Select Year</option>
                            @php
                                $currentYear = date('Y');
                                for($y = $currentYear; $y >= 2020; $y--) {
                                    echo "<option value=\"{$y}\">{$y}</option>";
                                }
                            @endphp
                        </select>
                        <span class="att-filter-hint">Academic year</span>
                    </div>
                    <div class="att-filter-item att-filter-item--narrow">
                        <label>Term</label>
                        <select class="form-select" id="examFilterTerm" disabled>
                            <option value="">Select Term</option>
                        </select>
                        <span class="att-filter-hint">After year</span>
                    </div>
                    <div class="att-filter-item att-filter-item--medium">
                        <label>Exam Name</label>
                        <select class="form-select" id="examFilterExam" disabled>
                            <option value="">Select Exam</option>
                        </select>
                        <span class="att-filter-hint">Examination</span>
                    </div>
                    <div class="att-filter-item att-filter-item--medium">
                        <label>Main Class</label>
                        <select class="form-select" id="examFilterMainClass">
                            <option value="">All Classes</option>
                        </select>
                        <span class="att-filter-hint">Optional filter</span>
                    </div>
                    <div class="att-filter-item att-filter-item--medium">
                        <label>Subclass</label>
                        <select class="form-select" id="examFilterSubclass" disabled>
                            <option value="">All Subclasses</option>
                        </select>
                        <span class="att-filter-hint">After main class</span>
                    </div>
                    <div class="att-filter-item att-filter-item--medium">
                        <label>Subject</label>
                        <select class="form-select" id="examFilterSubject">
                            <option value="">All Subjects</option>
                        </select>
                        <span class="att-filter-hint">Optional filter</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Statistics -->
        <div id="examAttendanceStats" style="display: none;">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white text-center clickable-stat-exam" data-filter="present" style="cursor: pointer; transition: all 0.2s;">
                        <div class="card-body">
                            <h6 class="mb-0">Total Present</h6>
                            <h4 class="mb-0" id="examTotalPresent">0</h4>
                            <small class="d-block mt-1">Click to view</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white text-center clickable-stat-exam" data-filter="absent" style="cursor: pointer; transition: all 0.2s;">
                        <div class="card-body">
                            <h6 class="mb-0">Total Absent</h6>
                            <h4 class="mb-0" id="examTotalAbsent">0</h4>
                            <small class="d-block mt-1">Click to view</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white text-center">
                        <div class="card-body">
                            <h6 class="mb-0">Total Students</h6>
                            <h4 class="mb-0" id="examTotalStudents">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div id="examStudentsTableContainer" style="display: none;">
            <div class="card" style="border: 1px solid #e9ecef; border-radius: 4px;">
                <div class="card-header" style="background-color: #940000; color: white; padding: 15px 20px;">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>Students Attendance
                    </h5>
                </div>
                <div class="card-body" style="padding: 25px;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="examStudentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Subjects Taken</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="examStudentsTableBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Select filters to view attendance</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 2px solid #e9ecef; padding: 20px 25px;">
                <h5 class="modal-title" id="studentDetailsModalLabel" style="font-weight: 600; font-size: 1.25rem;">
                    <i class="bi bi-person me-2"></i>Student Attendance Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="studentDetailsContent" style="padding: 25px;">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer border-top" id="studentDetailsModalFooter" style="display: none;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="exportStudentAttendancePdfBtn">
                    <i class="bi bi-file-earmark-pdf"></i> Download PDF Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Bootstrap 5 JS Bundle -->
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SheetJS for Excel export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
    let studentChart = null;
    let currentAttendanceSearchParams = null;
    let currentModalStudentID = null;
    let currentModalSearchType = 'month';

    // Toggle between System Attendance, Fingerprint Attendance, and Exam Attendance tabs
    function showAttendanceTab(tab) {
        console.log('Switching to tab:', tab);
        
        const systemTab = document.getElementById('system-attendance-tab');
        const fingerprintTab = document.getElementById('fingerprint-attendance-tab');
        const examTab = document.getElementById('exam-attendance-tab');
        const systemSection = document.getElementById('systemAttendanceSection');
        const fingerprintSection = document.getElementById('fingerprintAttendanceSection');
        const examSection = document.getElementById('examAttendanceSection');

        // Check if required elements exist
        if (!systemTab || !fingerprintTab || !systemSection || !fingerprintSection) {
            console.error('Required elements not found:', {
                systemTab: !!systemTab,
                fingerprintTab: !!fingerprintTab,
                systemSection: !!systemSection,
                fingerprintSection: !!fingerprintSection
            });
            return;
        }

        // Remove active class from all tabs
        if (systemTab) systemTab.classList.remove('active');
        if (fingerprintTab) fingerprintTab.classList.remove('active');
        if (examTab) examTab.classList.remove('active');
        
        // Hide all sections
        if (systemSection) systemSection.style.display = 'none';
        if (fingerprintSection) fingerprintSection.style.display = 'none';
        if (examSection) examSection.style.display = 'none';

        if (tab === 'fingerprint') {
            window.location.href = '{{ route("manageStudentFingerprintAttendance") }}';
            return;
        } else if (tab === 'exam') {
            console.log('Showing exam section');
            if (examTab) examTab.classList.add('active');
            if (examSection) examSection.style.display = 'block';
        } else {
            console.log('Showing system section');
            if (systemTab) systemTab.classList.add('active');
            if (systemSection) systemSection.style.display = 'block';
        }
    }

    // Handle search type change for System Attendance
    document.getElementById('search_type').addEventListener('change', function() {
        const searchType = this.value;
        
        // Hide all fields
        document.getElementById('date_field').style.display = 'none';
        document.getElementById('from_date_field').style.display = 'none';
        document.getElementById('to_date_field').style.display = 'none';
        document.getElementById('year_field').style.display = 'none';
        document.getElementById('month_field').style.display = 'none';

        // Show relevant fields
        switch(searchType) {
            case 'date':
                document.getElementById('date_field').style.display = 'flex';
                break;
            case 'date_range':
                document.getElementById('from_date_field').style.display = 'flex';
                document.getElementById('to_date_field').style.display = 'flex';
                break;
            case 'year':
                document.getElementById('year_field').style.display = 'flex';
                break;
            case 'month':
                document.getElementById('year_field').style.display = 'flex';
                document.getElementById('month_field').style.display = 'flex';
                break;
        }
    });

    // Handle search type change for Fingerprint Attendance
    const fingerprintSearchType = document.getElementById('fingerprint_search_type');
    if (fingerprintSearchType) {
        fingerprintSearchType.addEventListener('change', function() {
            const searchType = this.value;
            
            // Hide all fields
            document.getElementById('fingerprint_date_field').style.display = 'none';
            document.getElementById('fingerprint_year_field').style.display = 'none';
            document.getElementById('fingerprint_month_field').style.display = 'none';

            // Show relevant fields
            switch(searchType) {
                case 'date':
                    document.getElementById('fingerprint_date_field').style.display = 'flex';
                    break;
                case 'year':
                    document.getElementById('fingerprint_year_field').style.display = 'flex';
                    break;
                case 'month':
                    document.getElementById('fingerprint_year_field').style.display = 'flex';
                    document.getElementById('fingerprint_month_field').style.display = 'flex';
                    break;
            }
        });
    }

    // Search form submission
    document.getElementById('searchAttendanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // For coordinator view, add classID and coordinator flag
        @if(isset($isCoordinatorView) && $isCoordinatorView && $classIDParam)
            const lockedClassID = document.getElementById('lockedClassID');
            if (lockedClassID) {
                formData.append('classID', lockedClassID.value);
                formData.append('coordinator', 'true');
            }
        @endif
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Searching...';

        fetch('{{ route("search_attendance") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok && (data.error_code === 'no_academic_year' || response.status === 422)) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Academic Year',
                    text: data.message || 'Select an academic year in the header before viewing attendance.',
                    confirmButtonColor: '#940000'
                });
                return;
            }
            if (data.success) {
                currentAttendanceSearchParams = Object.fromEntries(formData.entries());
                @if(isset($isCoordinatorView) && $isCoordinatorView && $classIDParam)
                if (!currentAttendanceSearchParams.classID) {
                    const lockedClassID = document.getElementById('lockedClassID');
                    if (lockedClassID) {
                        currentAttendanceSearchParams.classID = lockedClassID.value;
                        currentAttendanceSearchParams.coordinator = 'true';
                    }
                }
                @endif
                displayAttendanceResults(data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'An error occurred',
                    confirmButtonColor: '#940000'
                });
            }
        })
        .catch(error => {
            console.error('Attendance search error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: (error && error.message) ? error.message : 'An error occurred while searching',
                confirmButtonColor: '#940000'
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // Main class → subclass/stream options
    (function () {
        const mainSel = document.getElementById('sysMainClassFilter');
        const subSel = document.getElementById('sysSubclassFilter');
        if (!mainSel || !subSel) return;
        const allOptions = Array.from(subSel.querySelectorAll('option[data-class-id]')).map(function (opt) {
            return { value: opt.value, text: opt.textContent, classId: String(opt.getAttribute('data-class-id') || '') };
        });
        function refreshSubclassOptions() {
            const classId = String(mainSel.value || '');
            const current = subSel.value;
            subSel.innerHTML = '<option value="">All Streams</option>';
            allOptions.forEach(function (o) {
                if (!classId || o.classId === classId) {
                    const opt = document.createElement('option');
                    opt.value = o.value;
                    opt.textContent = o.text;
                    opt.setAttribute('data-class-id', o.classId);
                    if (o.value === current) opt.selected = true;
                    subSel.appendChild(opt);
                }
            });
        }
        mainSel.addEventListener('change', refreshSubclassOptions);
    })();

    function displayAttendanceResults(data) {
        // Show results section
        document.getElementById('resultsSection').style.display = 'block';

        const pdfBtn = document.getElementById('exportAttendanceReportPdfBtn');
        const statusFilterEarly = (currentAttendanceSearchParams && currentAttendanceSearchParams.attendance_status)
            ? currentAttendanceSearchParams.attendance_status
            : 'all';
        if (pdfBtn) {
            pdfBtn.style.display = (data.attendance_by_class && data.attendance_by_class.length > 0) ? 'inline-block' : 'none';
            const pdfLabels = {
                Absent: '<i class="bi bi-file-earmark-pdf"></i> Download Watoro PDF',
                Present: '<i class="bi bi-file-earmark-pdf"></i> Download Present PDF',
                Sick: '<i class="bi bi-file-earmark-pdf"></i> Download Sick PDF',
                Excused: '<i class="bi bi-file-earmark-pdf"></i> Download Permission PDF'
            };
            pdfBtn.innerHTML = pdfLabels[statusFilterEarly] || '<i class="bi bi-file-earmark-pdf"></i> Download Full PDF';
            pdfBtn.title = statusFilterEarly === 'Absent'
                ? 'Download Ripoti ya Watoro PDF'
                : 'Download attendance report PDF';
        }

        const statusSmsBtn = document.getElementById('btnSendStatusFilterSms');
        const statusFilter = statusFilterEarly;
        let filteredStudentCount = 0;
        if (data.attendance_by_class) {
            data.attendance_by_class.forEach(function (cls) {
                if (cls.students) filteredStudentCount += cls.students.length;
            });
        }
        if (statusSmsBtn) {
            const showSms = statusFilter && statusFilter !== 'all' && filteredStudentCount > 0;
            statusSmsBtn.style.display = showSms ? 'inline-block' : 'none';
            statusSmsBtn.dataset.status = statusFilter;
            statusSmsBtn.dataset.count = String(filteredStudentCount);
        }

        // Display attendance by class if available
        if (data.attendance_by_class && data.attendance_by_class.length > 0) {
            displayAttendanceByClass(data.attendance_by_class);
        } else {
            document.getElementById('attendanceByClassContent').innerHTML = '<div class="alert alert-info">No attendance data found.</div>';
        }

        // Scroll to results
        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
    }

    // Search form submission for Fingerprint Attendance
    const searchFingerprintAttendanceForm = document.getElementById('searchFingerprintAttendanceForm');
    if (searchFingerprintAttendanceForm) {
        searchFingerprintAttendanceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // For coordinator view, add classID and coordinator flag
            @if(isset($isCoordinatorView) && $isCoordinatorView && $classIDParam)
                const fingerprintLockedClassID = document.getElementById('fingerprintLockedClassID');
                if (fingerprintLockedClassID) {
                    formData.append('classID', fingerprintLockedClassID.value);
                    formData.append('coordinator', 'true');
                }
            @endif
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Searching...';

            fetch('{{ route("search_fingerprint_attendance") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok && (data.error_code === 'no_academic_year' || response.status === 422)) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Academic Year',
                        text: data.message || 'Select an academic year in the header before viewing fingerprint attendance.',
                        confirmButtonColor: '#940000'
                    });
                    return;
                }
                if (data.success) {
                    displayFingerprintAttendanceResults(data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'An error occurred',
                        confirmButtonColor: '#940000'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while searching',
                    confirmButtonColor: '#940000'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    function displayFingerprintAttendanceResults(data) {
        // Show results section
        document.getElementById('fingerprintResultsSection').style.display = 'block';

        // Display attendance by class if available
        if (data.attendance_by_class && data.attendance_by_class.length > 0) {
            displayFingerprintAttendanceByClass(data.attendance_by_class);
        } else {
            document.getElementById('fingerprintAttendanceByClassContent').innerHTML = '<div class="alert alert-info">No fingerprint attendance data found.</div>';
        }

        // Scroll to results
        document.getElementById('fingerprintResultsSection').scrollIntoView({ behavior: 'smooth' });
    }

    // Store current fingerprint attendance data for export
    let currentFingerprintAttendanceData = null;
    let currentFingerprintSearchType = null;
    let currentFingerprintSearchMonth = null;
    let currentFingerprintSearchYear = null;
    let currentFingerprintSearchDate = null;

    function displayFingerprintAttendanceByClass(attendanceByClass) {
        const content = document.getElementById('fingerprintAttendanceByClassContent');
        const searchType = document.getElementById('fingerprint_search_type').value;
        const month = document.getElementById('fingerprint_month') ? document.getElementById('fingerprint_month').value : '';
        const year = document.getElementById('fingerprint_year') ? document.getElementById('fingerprint_year').value : '';
        const date = document.getElementById('fingerprint_date') ? document.getElementById('fingerprint_date').value : '';
        
        // Store data for export
        currentFingerprintAttendanceData = attendanceByClass;
        currentFingerprintSearchType = searchType;
        currentFingerprintSearchMonth = (searchType === 'month' && year && month) ? `${year}-${month}` : null;
        currentFingerprintSearchYear = (searchType === 'year' && year) ? year : null;
        currentFingerprintSearchDate = (searchType === 'date' && date) ? date : null;
        
        // Show/hide export buttons based on search type (show for all types now)
        document.getElementById('exportFingerprintExcelBtn').style.display = 'inline-block';
        document.getElementById('exportFingerprintPdfBtn').style.display = 'inline-block';
        
        // Populate class dropdown
        const classDropdown = document.getElementById('searchFingerprintClasses');
        if (classDropdown) {
            classDropdown.innerHTML = '<option value="">All Classes</option>';
            attendanceByClass.forEach((classData) => {
                const option = document.createElement('option');
                option.value = classData.subclassID;
                const displayName = classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name;
                option.textContent = displayName;
                classDropdown.appendChild(option);
            });
            
            // Add realtime filtering event listener
            classDropdown.addEventListener('change', function() {
                const selectedClassID = this.value;
                filterFingerprintClasses(selectedClassID);
            });
        }
        
        let html = '';
        
        attendanceByClass.forEach((classData) => {
            if (!classData.has_attendance) {
                // No attendance collected - get specific period message
                let periodMessage = '';
                if (searchType === 'month') {
                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    const month = document.getElementById('fingerprint_month') ? document.getElementById('fingerprint_month').value : '';
                    const year = document.getElementById('fingerprint_year') ? document.getElementById('fingerprint_year').value : '';
                    if (month && year) {
                        const monthName = monthNames[parseInt(month) - 1];
                        periodMessage = `in ${monthName} ${year}`;
                    } else {
                        periodMessage = 'for the selected period';
                    }
                } else if (searchType === 'year') {
                    const year = document.getElementById('fingerprint_year') ? document.getElementById('fingerprint_year').value : '';
                    if (year) {
                        periodMessage = `in ${year}`;
                    } else {
                        periodMessage = 'for the selected period';
                    }
                } else if (searchType === 'date') {
                    const date = document.getElementById('fingerprint_date') ? document.getElementById('fingerprint_date').value : '';
                    if (date) {
                        const dateObj = new Date(date);
                        periodMessage = `on ${dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`;
                    } else {
                        periodMessage = 'for the selected date';
                    }
                } else {
                    periodMessage = 'for the selected period';
                }
                
                html += `
                    <div class="card mb-4 border-secondary" data-subclass-id="${classData.subclassID}">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-folder"></i> ${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> No fingerprint attendance collected ${periodMessage} for this class.
                            </div>
                        </div>
                    </div>
                `;
                return;
            }

            // Has attendance - show statistics
            html += `
                <div class="card mb-4" style="border-color: #940000; border-width: 2px;" data-subclass-id="${classData.subclassID}">
                    <div class="card-header text-white" style="background-color: #940000;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-folder"></i> ${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}
                            </h5>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-success export-class-excel-btn" data-subclass-id="${classData.subclassID}" data-subclass-name="${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}" title="Export to Excel">
                                        <i class="bi bi-file-earmark-excel"></i> Excel
                                    </button>
                                    <button class="btn btn-sm btn-danger export-class-pdf-btn" data-subclass-id="${classData.subclassID}" data-subclass-name="${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}" title="Export to PDF">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </button>
                                </div>
                                <button class="btn btn-sm btn-light toggle-subclass-btn" data-subclass-id="${classData.subclassID}">
                                    <i class="bi bi-chevron-down"></i> View Students
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
            `;

            // Show widgets based on search type
            if (searchType === 'month' || searchType === 'year') {
                // Show percentage widgets for month/year
                html += `
                            <div class="col-md-6">
                                <div class="card bg-success">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.present_percentage}%</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Present (${classData.present} days)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-danger">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.absent_percentage}%</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Absent (${classData.absent} days)</p>
                                    </div>
                                </div>
                            </div>
                `;
            } else {
                // Show count widgets for date
                html += `
                            <div class="col-md-6">
                                <div class="card bg-success">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.present || 0}</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Present</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-danger">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.absent || 0}</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Absent</p>
                                    </div>
                                </div>
                            </div>
                `;
            }

            html += `
                        </div>
                        
                        <div class="subclass-students" id="fingerprint-subclass-students-${classData.subclassID}" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover" id="fingerprintStudentsTable_${classData.subclassID}">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Admission No.</th>
                                            ${searchType === 'date' ? `
                                            <th>Check In Time</th>
                                            <th>Check Out Time</th>
                                            ` : `
                                            <th>Gender</th>
                                            <th>Days Present</th>
                                            <th>Days Absent</th>
                                            <th>Total Days</th>
                                            `}
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;
            
            // Calculate working days for this class
            let workingDays = 0;
            if (searchType === 'month' && year && month) {
                const startDate = new Date(parseInt(year), parseInt(month) - 1, 1);
                let endDate = new Date(parseInt(year), parseInt(month), 0);
                if (endDate > new Date()) endDate = new Date();
                let current = new Date(startDate);
                while (current <= endDate) {
                    if (current.getDay() !== 0 && current.getDay() !== 6) {
                        workingDays++;
                    }
                    current.setDate(current.getDate() + 1);
                }
            } else if (searchType === 'year' && year) {
                const startDate = new Date(parseInt(year), 0, 1);
                let endDate = new Date(parseInt(year), 11, 31);
                if (endDate > new Date()) endDate = new Date();
                let current = new Date(startDate);
                while (current <= endDate) {
                    if (current.getDay() !== 0 && current.getDay() !== 6) {
                        workingDays++;
                    }
                    current.setDate(current.getDate() + 1);
                }
            } else {
                workingDays = 1; // For date search
            }
            
            if (classData.students && classData.students.length > 0) {
                classData.students.forEach((student, studentIndex) => {
                    const isActiveEnrolled = !student.enrollment_status || student.enrollment_status === 'Active';
                    const enrBadge = student.enrollment_status
                        ? `<span class="badge ${isActiveEnrolled ? 'bg-success' : 'bg-secondary'} ms-1">${student.enrollment_status}</span>`
                        : '';
                    const rowStyle = isActiveEnrolled ? '' : 'opacity:0.55;background:#f5f5f5;';
                    if (searchType === 'date') {
                        // For date view, show check in/out times
                        const checkInBadge = student.check_in_time 
                            ? '<span class="badge bg-success">' + student.check_in_time + '</span>' 
                            : '<span class="text-muted">--</span>';
                        const checkOutBadge = student.check_out_time 
                            ? '<span class="badge bg-primary">' + student.check_out_time + '</span>' 
                            : '<span class="text-muted">--</span>';
                        
                        html += `
                            <tr style="${rowStyle}">
                                <td>${studentIndex + 1}</td>
                                <td>${student.first_name} ${student.middle_name || ''} ${student.last_name}${enrBadge}</td>
                                <td>${student.admission_number || 'N/A'}</td>
                                <td>${checkInBadge}</td>
                                <td>${checkOutBadge}</td>
                                <td>
                                    <button class="btn btn-sm btn-info view-fingerprint-student-btn" 
                                            data-student-id="${student.studentID}" 
                                            data-student-name="${student.first_name} ${student.middle_name || ''} ${student.last_name}"
                                            data-subclass-id="${classData.subclassID}"
                                            title="View More Details">
                                        <i class="bi bi-eye"></i> View More
                                    </button>
                                </td>
                            </tr>
                        `;
                    } else {
                        // For month/year view, show stats
                        html += `
                            <tr style="${rowStyle}">
                                <td>${studentIndex + 1}</td>
                                <td>${student.first_name} ${student.middle_name || ''} ${student.last_name}${enrBadge}</td>
                                <td>${student.admission_number || 'N/A'}</td>
                                <td>${student.gender || 'N/A'}</td>
                                <td><span class="badge bg-success">${student.days_present || 0}</span></td>
                                <td><span class="badge bg-danger">${student.days_absent || 0}</span></td>
                                <td>${workingDays}</td>
                                <td>
                                    <button class="btn btn-sm btn-info view-fingerprint-student-btn" 
                                            data-student-id="${student.studentID}" 
                                            data-student-name="${student.first_name} ${student.middle_name || ''} ${student.last_name}"
                                            data-subclass-id="${classData.subclassID}"
                                            title="View More Details">
                                        <i class="bi bi-eye"></i> View More
                                    </button>
                                </td>
                            </tr>
                        `;
                    }
                });
            } else {
                html += '<tr><td colspan="8" class="text-center">No enrollments found for the selected academic year / status.</td></tr>';
            }

            html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        content.innerHTML = html;

        // Add toggle functionality for subclass students
        document.querySelectorAll('.toggle-subclass-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subclassID = this.getAttribute('data-subclass-id');
                const studentsDiv = document.getElementById(`fingerprint-subclass-students-${subclassID}`);
                const icon = this.querySelector('i');
                
                if (studentsDiv.style.display === 'none') {
                    studentsDiv.style.display = 'block';
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-up');
                } else {
                    studentsDiv.style.display = 'none';
                    icon.classList.remove('bi-chevron-up');
                    icon.classList.add('bi-chevron-down');
                }
            });
        });

        // Add export functionality for each class
        document.querySelectorAll('.export-class-excel-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subclassId = this.getAttribute('data-subclass-id');
                const subclassName = this.getAttribute('data-subclass-name');
                exportClassFingerprintAttendanceToExcel(subclassId, subclassName);
            });
        });

        document.querySelectorAll('.export-class-pdf-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subclassId = this.getAttribute('data-subclass-id');
                const subclassName = this.getAttribute('data-subclass-name');
                exportClassFingerprintAttendanceToPdf(subclassId, subclassName);
            });
        });

        // Add event listeners for View More buttons
        document.querySelectorAll('.view-fingerprint-student-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const studentId = this.getAttribute('data-student-id');
                const studentName = this.getAttribute('data-student-name');
                const searchType = document.getElementById('fingerprint_search_type').value;
                const month = document.getElementById('fingerprint_month') ? document.getElementById('fingerprint_month').value : '';
                const year = document.getElementById('fingerprint_year') ? document.getElementById('fingerprint_year').value : '';
                const date = document.getElementById('fingerprint_date') ? document.getElementById('fingerprint_date').value : '';
                
                viewFingerprintStudentDetails(studentId, studentName, searchType, month, year, date);
            });
        });
        
        // Initialize realtime filtering for class dropdown
        initializeFingerprintClassFilter();
    }
    
    // Function to filter fingerprint classes in realtime
    function filterFingerprintClasses(selectedClassID) {
        const container = document.getElementById('fingerprintAttendanceByClassContent');
        if (!container) {
            console.warn('Fingerprint attendance container not found');
            return;
        }
        
        // Get all cards - both with and without border-secondary
        const allCards = container.querySelectorAll('.card.mb-4[data-subclass-id]');
        
        console.log('Filtering classes. Selected:', selectedClassID, 'Total cards:', allCards.length);
        
        if (selectedClassID === '' || selectedClassID === null || selectedClassID === undefined) {
            // Show all classes
            allCards.forEach(function(card) {
                card.style.display = '';
            });
        } else {
            // Show only selected class - hide all others
            let foundMatch = false;
            allCards.forEach(function(card) {
                const subclassID = card.getAttribute('data-subclass-id');
                console.log('Card subclassID:', subclassID, 'Selected:', selectedClassID, 'Match:', subclassID == selectedClassID);
                if (subclassID && String(subclassID) === String(selectedClassID)) {
                    card.style.display = '';
                    foundMatch = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // If no match found, show all cards (fallback)
            if (!foundMatch) {
                console.warn('No matching class found for ID:', selectedClassID);
                // Show all cards as fallback
                allCards.forEach(function(card) {
                    card.style.display = '';
                });
            }
        }
    }
    
    // Initialize fingerprint class filter
    function initializeFingerprintClassFilter() {
        const classDropdown = document.getElementById('searchFingerprintClasses');
        if (classDropdown) {
            // Remove any existing event listeners by cloning
            const newDropdown = classDropdown.cloneNode(true);
            classDropdown.parentNode.replaceChild(newDropdown, classDropdown);
            
            // Add change event listener for realtime filtering
            newDropdown.addEventListener('change', function(e) {
                const selectedClassID = this.value;
                console.log('Class dropdown changed:', selectedClassID);
                filterFingerprintClasses(selectedClassID);
            });
            
            // Also add direct event listener as backup
            $(newDropdown).off('change').on('change', function() {
                const selectedClassID = $(this).val();
                console.log('jQuery change event:', selectedClassID);
                filterFingerprintClasses(selectedClassID);
            });
        } else {
            console.warn('searchFingerprintClasses dropdown not found');
        }
    }

    // View Fingerprint Student Details
    function viewFingerprintStudentDetails(studentID, studentName, searchType, month, year, date) {
        const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
        const modalContent = document.getElementById('studentDetailsContent');
        const modalTitle = document.getElementById('studentDetailsModalLabel');
        const modalFooter = document.getElementById('studentDetailsModalFooter');

        if (modalFooter) modalFooter.style.display = 'none';
        
        modalTitle.innerHTML = '<i class="bi bi-person me-2"></i>Fingerprint Attendance Details - ' + studentName;
        
        modalContent.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Loading attendance details...</p>
            </div>
        `;
        
        modal.show();
        
        // Build query parameters
        let url = `{{ url('student_fingerprint_attendance_details') }}/${studentID}?search_type=${searchType}`;
        if (month && year) {
            url += `&month=${month}&year=${year}`;
        } else if (year) {
            url += `&year=${year}`;
        } else if (date) {
            url += `&date=${date}`;
        }
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFingerprintStudentDetails(data);
            } else {
                modalContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${data.message || 'Failed to load attendance details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error(error);
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> An error occurred while loading attendance details
                </div>
            `;
        });
    }

    // Store current student data for export
    let currentStudentFingerprintData = null;

    function displayFingerprintStudentDetails(data) {
        const modalContent = document.getElementById('studentDetailsContent');
        const student = data.student;
        const stats = data.statistics;
        const records = data.attendance_records;
        const searchPeriod = data.search_period;
        const searchType = data.search_type;
        
        // Store data for export
        currentStudentFingerprintData = {
            student: student,
            stats: stats,
            records: records,
            searchPeriod: searchPeriod,
            searchType: searchType
        };
        
        let html = `
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Student Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> ${student.full_name}</p>
                                    <p><strong>Admission Number:</strong> ${student.admission_number || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Period:</strong> ${searchPeriod}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${stats.days_present}</h3>
                            <p class="mb-0">Days Present</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${stats.days_absent}</h3>
                            <p class="mb-0">Days Absent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${stats.total_days}</h3>
                            <p class="mb-0">Total Days</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${stats.total_records}</h3>
                            <p class="mb-0">Total Records</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Attendance Records</h6>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-success" id="exportStudentFingerprintExcelBtn" title="Export to Excel">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </button>
                                <button class="btn btn-sm btn-danger" id="exportStudentFingerprintPdfBtn" title="Export to PDF">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Check In Time</th>
                                            <th>Check Out Time</th>
                                            <th>Verify Mode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
        `;
        
        if (records && records.length > 0) {
            records.forEach((record, index) => {
                const checkInBadge = record.check_in_time 
                    ? '<span class="badge bg-success">' + record.check_in_time + '</span>' 
                    : '<span class="text-muted">--</span>';
                const checkOutBadge = record.check_out_time 
                    ? '<span class="badge bg-primary">' + record.check_out_time + '</span>' 
                    : '<span class="text-muted">--</span>';
                
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${record.attendance_date_formatted}</td>
                        <td>${checkInBadge}</td>
                        <td>${checkOutBadge}</td>
                        <td>${record.verify_mode || 'N/A'}</td>
                    </tr>
                `;
            });
        } else {
            html += `
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        <i class="bi bi-info-circle"></i> No attendance records found for this period
                    </td>
                </tr>
            `;
        }
        
        html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        modalContent.innerHTML = html;
        
        // Add event listeners for export buttons
        const excelBtn = document.getElementById('exportStudentFingerprintExcelBtn');
        const pdfBtn = document.getElementById('exportStudentFingerprintPdfBtn');
        
        if (excelBtn) {
            // Remove existing listeners
            const newExcelBtn = excelBtn.cloneNode(true);
            excelBtn.parentNode.replaceChild(newExcelBtn, excelBtn);
            newExcelBtn.addEventListener('click', function() {
                exportStudentFingerprintDetailsToExcel();
            });
        }
        
        if (pdfBtn) {
            // Remove existing listeners
            const newPdfBtn = pdfBtn.cloneNode(true);
            pdfBtn.parentNode.replaceChild(newPdfBtn, pdfBtn);
            newPdfBtn.addEventListener('click', function() {
                exportStudentFingerprintDetailsToPdf();
            });
        }
    }

    // Export Student Fingerprint Details to Excel
    function exportStudentFingerprintDetailsToExcel() {
        if (typeof XLSX === 'undefined') {
            Swal.fire('Error', 'Excel export library not loaded', 'error');
            return;
        }

        if (!currentStudentFingerprintData) {
            Swal.fire('Error', 'No data to export.', 'error');
            return;
        }

        const student = currentStudentFingerprintData.student;
        const stats = currentStudentFingerprintData.stats;
        const records = currentStudentFingerprintData.records;
        const searchPeriod = currentStudentFingerprintData.searchPeriod;
        const schoolName = '{{ $school_details->school_name ?? "School" }}';
        
        const reportTitle = 'FINGERPRINT ATTENDANCE DETAILS - ' + student.full_name.toUpperCase() + ' - ' + searchPeriod.toUpperCase();

        // Create workbook
        const wb = XLSX.utils.book_new();
        const wsData = [];

        // Header rows
        wsData.push([schoolName]);
        wsData.push([reportTitle]);
        wsData.push([]);
        wsData.push(['Student Name:', student.full_name]);
        wsData.push(['Admission Number:', student.admission_number || 'N/A']);
        wsData.push(['Period:', searchPeriod]);
        wsData.push(['Days Present:', stats.days_present]);
        wsData.push(['Days Absent:', stats.days_absent]);
        wsData.push(['Total Days:', stats.total_days]);
        wsData.push([]);
        wsData.push(['Date', 'Check In Time', 'Check Out Time', 'Verify Mode']);

        // Attendance records
        records.forEach(function(record) {
            wsData.push([
                record.attendance_date_formatted,
                record.check_in_time || '--',
                record.check_out_time || '--',
                record.verify_mode || 'N/A'
            ]);
        });

        const ws = XLSX.utils.aoa_to_sheet(wsData);
        if (!ws['!merges']) ws['!merges'] = [];
        ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: 3}});
        ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: 3}});

        XLSX.utils.book_append_sheet(wb, ws, 'Attendance Details');
        XLSX.writeFile(wb, 'Fingerprint_Attendance_' + student.full_name.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.xlsx');
    }

    // Export Student Fingerprint Details to PDF
    function exportStudentFingerprintDetailsToPdf() {
        if (typeof window.jspdf === 'undefined') {
            Swal.fire('Error', 'PDF export library not loaded', 'error');
            return;
        }

        if (!currentStudentFingerprintData) {
            Swal.fire('Error', 'No data to export.', 'error');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        const student = currentStudentFingerprintData.student;
        const stats = currentStudentFingerprintData.stats;
        const records = currentStudentFingerprintData.records;
        const searchPeriod = currentStudentFingerprintData.searchPeriod;
        const schoolName = '{{ $school_details->school_name ?? "School" }}';
        const schoolLogoUrl = '{{ $school_details->school_logo ? asset($school_details->school_logo) : "" }}';
        
        const reportTitle = 'FINGERPRINT ATTENDANCE DETAILS - ' + student.full_name.toUpperCase() + ' - ' + searchPeriod.toUpperCase();

        function drawHeaderAndTable(logoImg) {
            const pageWidth = doc.internal.pageSize.getWidth();
            const centerX = pageWidth / 2;

            // Logo on the left if available
            if (logoImg) {
                try {
                    doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                } catch (e) {
                    console.warn('Failed to add logo to PDF:', e);
                }
            }

            // School name and report title centered
            doc.setFontSize(16);
            doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
            doc.setFontSize(12);
            doc.text(reportTitle, centerX, 26, { align: 'center' });

            // Student info
            doc.setFontSize(10);
            doc.text('Student Name: ' + student.full_name, 14, 36);
            doc.text('Admission Number: ' + (student.admission_number || 'N/A'), 14, 42);
            doc.text('Period: ' + searchPeriod, 14, 48);
            doc.text('Days Present: ' + stats.days_present + ' | Days Absent: ' + stats.days_absent + ' | Total Days: ' + stats.total_days, 14, 54);

            // Prepare table data
            const tableData = [];
            records.forEach(function(record) {
                tableData.push([
                    record.attendance_date_formatted,
                    record.check_in_time || '--',
                    record.check_out_time || '--',
                    record.verify_mode || 'N/A'
                ]);
            });

            // Add table
            doc.autoTable({
                startY: 60,
                head: [['Date', 'Check In Time', 'Check Out Time', 'Verify Mode']],
                body: tableData,
                theme: 'striped',
                headStyles: { fillColor: [148, 0, 0] },
                didDrawPage: function (data) {
                    // Footer on each page
                    const pageHeight = doc.internal.pageSize.getHeight();
                    doc.setFontSize(9);
                    doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                }
            });

            // Save PDF
            doc.save('Fingerprint_Attendance_' + student.full_name.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf');
        }

        if (schoolLogoUrl) {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = function() {
                drawHeaderAndTable(img);
            };
            img.onerror = function() {
                console.warn('Failed to load school logo image for PDF header.');
                drawHeaderAndTable(null);
            };
            img.src = schoolLogoUrl;
        } else {
            drawHeaderAndTable(null);
        }
    }

    // Load fingerprint attendance from external API and display all records
    function loadFingerprintAttendance(page = 1) {
        const container = document.getElementById('fingerprintAttendanceContent');

        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Loading attendance records from biometric system...</p>
            </div>
        `;

        fetch('{{ route("api.attendance.external_list") }}?page=' + page, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load external attendance');
            }

            const records = data.data || [];
            const pagination = data.pagination || null;

            if (records.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No attendance records found from the biometric system.
                    </div>
                `;
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm" id="fingerprintAttendanceTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Enroll ID (Fingerprint ID)</th>
                                <th>Attendance Date</th>
                                <th>Check In Time</th>
                                <th>Check Out Time</th>
                                <th>Status</th>
                                <th>Verify Mode</th>
                                <th>Device IP</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            records.forEach((rec, index) => {
                const user = rec.user || {};
                const status = rec.status === '1' ? 'Present' : (rec.status || '');

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${user.id ?? ''}</td>
                        <td>${user.name ?? ''}</td>
                        <td>${user.enroll_id ?? ''}</td>
                        <td>${rec.attendance_date ?? ''}</td>
                        <td>${rec.check_in_time ?? ''}</td>
                        <td>${rec.check_out_time ?? ''}</td>
                        <td>${status}</td>
                        <td>${rec.verify_mode ?? ''}</td>
                        <td>${rec.device_ip ?? ''}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            // Pagination info (simple display)
            if (pagination) {
                html += `
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">
                            Page ${pagination.current_page} of ${pagination.last_page}, Total: ${pagination.total}
                        </small>
                        <div>
                `;

                if (pagination.current_page > 1) {
                    html += `
                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="loadFingerprintAttendance(${pagination.current_page - 1})">
                            <i class="bi bi-chevron-left"></i> Prev
                        </button>
                    `;
                }

                if (pagination.current_page < pagination.last_page) {
                    html += `
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadFingerprintAttendance(${pagination.current_page + 1})">
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    `;
                }

                html += `
                        </div>
                    </div>
                `;
            }

            container.innerHTML = html;

            // Initialize DataTable for fingerprint table
            if ($('#fingerprintAttendanceTable').length) {
                $('#fingerprintAttendanceTable').DataTable({
                    pageLength: 25,
                    order: [[4, 'desc'], [6, 'desc']],
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "No entries found",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        zeroRecords: "No matching records found"
                    },
                    responsive: true
                });
            }
        })
        .catch(error => {
            console.error(error);
            container.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle"></i> ${error.message || 'Failed to load external attendance.'}
                </div>
            `;
        });
    }

    function displayAttendanceByClass(attendanceByClass) {
        const content = document.getElementById('attendanceByClassContent');
        const searchType = document.getElementById('search_type').value;
        const statusFilter = (currentAttendanceSearchParams && currentAttendanceSearchParams.attendance_status)
            ? currentAttendanceSearchParams.attendance_status
            : 'all';

        let periodTitle = 'SYSTEM ATTENDANCE REPORT';
        if (searchType === 'month') {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const month = document.getElementById('month')?.value || '';
            const year = document.getElementById('year')?.value || '';
            if (month && year) periodTitle = 'SYSTEM ATTENDANCE, ' + monthNames[parseInt(month, 10) - 1].toUpperCase() + ' ' + year;
        } else if (searchType === 'year') {
            const year = document.getElementById('year')?.value || '';
            if (year) periodTitle = 'SYSTEM ATTENDANCE, YEAR ' + year;
        } else if (searchType === 'date') {
            const date = document.getElementById('date')?.value || '';
            if (date) periodTitle = 'SYSTEM ATTENDANCE, ' + date;
        } else if (searchType === 'date_range') {
            const fromDate = document.getElementById('from_date')?.value || '';
            const toDate = document.getElementById('to_date')?.value || '';
            if (fromDate && toDate) periodTitle = 'SYSTEM ATTENDANCE, ' + fromDate + ' TO ' + toDate;
        }
        if (statusFilter && statusFilter !== 'all') {
            const statusTitle = {
                Present: 'WALIOHUDHURIA',
                Absent: 'WATORO',
                Sick: 'WAGONJWA',
                Excused: 'RUHUSA'
            };
            periodTitle += ' · ' + (statusTitle[statusFilter] || statusFilter.toUpperCase()) + ' ONLY';
        }

        let totalPresent = 0, totalAbsent = 0, totalSick = 0, totalExcused = 0;
        const flatRows = [];
        (attendanceByClass || []).forEach((classData) => {
            const className = classData.display_name || ((classData.class_name || '') + ' ' + (classData.subclass_name || '')).trim();
            (classData.students || []).forEach((student) => {
                const daysPresent = Number(student.days_present || 0);
                const daysAbsent = Number(student.days_absent || 0);
                const daysSick = Number(student.days_sick || 0);
                const daysExcused = Number(student.days_excused || 0);
                totalPresent += daysPresent;
                totalAbsent += daysAbsent;
                totalSick += daysSick;
                totalExcused += daysExcused;
                flatRows.push({
                    studentID: student.studentID,
                    name: [student.first_name, student.middle_name, student.last_name].filter(Boolean).join(' '),
                    admission: student.admission_number || 'N/A',
                    gender: student.gender || 'N/A',
                    class_name: className,
                    subclassID: classData.subclassID,
                    enrollment_status: student.enrollment_status || '',
                    days_present: daysPresent,
                    days_absent: daysAbsent,
                    days_sick: daysSick,
                    days_excused: daysExcused,
                });
            });
        });

        // Use jQuery explicitly, global $ is often overwritten on this layout
        const $jq = window.jQuery;
        const classDropdown = document.getElementById('searchClasses');
        if (classDropdown) {
            classDropdown.innerHTML = '<option value="">All Classes</option>';
            (attendanceByClass || []).forEach((classData) => {
                const displayName = classData.display_name || ((classData.class_name || '') + ' ' + (classData.subclass_name || '')).trim();
                const opt = document.createElement('option');
                opt.value = classData.subclassID;
                opt.textContent = displayName;
                classDropdown.appendChild(opt);
            });
        }

        const statusMeta = {
            Present: { label: 'Present', sw: 'Waliohudhuria', color: 'text-success', daysKey: 'days_present', daysLabel: 'Total present days' },
            Absent: { label: 'Absent (Watoro)', sw: 'Watoro', color: 'text-danger', daysKey: 'days_absent', daysLabel: 'Total absent days' },
            Sick: { label: 'Sick', sw: 'Wagonjwa', color: 'text-warning', daysKey: 'days_sick', daysLabel: 'Total sick days' },
            Excused: { label: 'Permission', sw: 'Ruhusa', color: 'text-info', daysKey: 'days_excused', daysLabel: 'Total permission days' }
        };
        const filteredMeta = statusMeta[statusFilter] || null;
        const filteredDaysTotal = filteredMeta
            ? flatRows.reduce((sum, r) => sum + Number(r[filteredMeta.daysKey] || 0), 0)
            : 0;

        let html = '<div class="sf-report-title text-center mb-2" style="font-size:1.05rem;font-weight:700;color:#940000;text-transform:uppercase;">' + periodTitle + '</div>';
        html += '<div class="card border-0 shadow-sm mb-3"><div class="card-body py-2 px-3">';

        if (filteredMeta) {
            html += '<div class="row text-center small g-2">';
            html += '<div class="col-md-4"><strong>' + filteredMeta.sw + '</strong><br><span class="fs-5 ' + filteredMeta.color + '">' + flatRows.length + '</span><div class="text-muted">students</div></div>';
            html += '<div class="col-md-4"><strong>' + filteredMeta.daysLabel + '</strong><br><span class="fs-5 ' + filteredMeta.color + '">' + filteredDaysTotal + '</span></div>';
            html += '<div class="col-md-4"><strong>Classes</strong><br><span class="fs-5">' + (attendanceByClass || []).length + '</span></div>';
            html += '</div>';
            html += '<div class="text-center small text-muted mt-2 pt-2 border-top">Filter: <strong>' + filteredMeta.label + '</strong> only</div>';
        } else {
            html += '<div class="row text-center small g-2">';
            html += '<div class="col-md-3"><strong>Present</strong><br><span class="text-success fs-5">' + totalPresent + '</span></div>';
            html += '<div class="col-md-3"><strong>Absent</strong><br><span class="text-danger fs-5">' + totalAbsent + '</span></div>';
            html += '<div class="col-md-3"><strong>Sick</strong><br><span class="text-warning fs-5">' + totalSick + '</span></div>';
            html += '<div class="col-md-3"><strong>Permission</strong><br><span class="text-info fs-5">' + totalExcused + '</span></div>';
            html += '</div><div class="row text-center small g-2 mt-2 pt-2 border-top">';
            html += '<div class="col-md-4"><strong>Students shown</strong><br><span class="fs-5">' + flatRows.length + '</span></div>';
            html += '<div class="col-md-4"><strong>Classes</strong><br><span class="fs-5">' + (attendanceByClass || []).length + '</span></div>';
            html += '<div class="col-md-4"><strong>Status filter</strong><br>All</div>';
            html += '</div>';
        }
        html += '</div></div>';

        if (!flatRows.length) {
            html += '<div class="alert alert-info mb-0">No students found for this filter.</div>';
            if ($jq && $jq.fn.DataTable && $jq.fn.DataTable.isDataTable('#systemAttendanceReportTable')) {
                try { $jq('#systemAttendanceReportTable').DataTable().destroy(); } catch (e) {}
            }
            content.innerHTML = html;
            return;
        }

        const showAllDayCols = !filteredMeta;
        html += '<div class="table-responsive"><table id="systemAttendanceReportTable" class="table table-striped table-hover table-sm mb-0 w-100">';
        html += '<thead style="background:#940000;color:#fff;"><tr>';
        html += '<th>#</th><th>Name</th><th>Admission</th><th>Class</th><th>Gender</th>';
        if (showAllDayCols) {
            html += '<th>Present</th><th>Absent</th><th>Sick</th><th>Permission</th>';
        } else if (statusFilter === 'Present') {
            html += '<th>Present days</th>';
        } else if (statusFilter === 'Absent') {
            html += '<th>Absent days</th>';
        } else if (statusFilter === 'Sick') {
            html += '<th>Sick days</th>';
        } else if (statusFilter === 'Excused') {
            html += '<th>Permission days</th>';
        }
        html += '<th>Actions</th>';
        html += '</tr></thead><tbody>';

        flatRows.forEach((row, idx) => {
            const dim = row.enrollment_status && row.enrollment_status !== 'Active' ? 'opacity:0.55;' : '';
            html += '<tr style="' + dim + '" data-subclass-id="' + row.subclassID + '">';
            html += '<td>' + (idx + 1) + '</td>';
            html += '<td>' + row.name + '</td>';
            html += '<td>' + row.admission + '</td>';
            html += '<td>' + row.class_name + '</td>';
            html += '<td>' + row.gender + '</td>';
            if (showAllDayCols) {
                html += '<td><span class="badge bg-success">' + row.days_present + '</span></td>';
                html += '<td><span class="badge bg-danger">' + row.days_absent + '</span></td>';
                html += '<td><span class="badge bg-warning text-dark">' + row.days_sick + '</span></td>';
                html += '<td><span class="badge bg-info">' + row.days_excused + '</span></td>';
            } else if (statusFilter === 'Present') {
                html += '<td><span class="badge bg-success">' + row.days_present + '</span></td>';
            } else if (statusFilter === 'Absent') {
                html += '<td><span class="badge bg-danger">' + row.days_absent + '</span></td>';
            } else if (statusFilter === 'Sick') {
                html += '<td><span class="badge bg-warning text-dark">' + row.days_sick + '</span></td>';
            } else if (statusFilter === 'Excused') {
                html += '<td><span class="badge bg-info">' + row.days_excused + '</span></td>';
            }
            html += '<td><div class="btn-group btn-group-sm">';
            html += '<button class="btn btn-sm btn-view" onclick="viewStudentDetails(' + row.studentID + ', \'' + searchType + '\')"><i class="bi bi-eye"></i></button>';
            html += '<button class="btn btn-sm btn-danger" onclick="downloadStudentAttendancePdf(' + row.studentID + ')" title="PDF"><i class="bi bi-file-earmark-pdf"></i></button>';
            html += '</div></td></tr>';
        });
        html += '</tbody></table></div>';

        // Destroy previous DataTable BEFORE replacing DOM (avoids DataTables "get" TypeError)
        if ($jq && $jq.fn.DataTable && $jq.fn.DataTable.isDataTable('#systemAttendanceReportTable')) {
            try { $jq('#systemAttendanceReportTable').DataTable().destroy(); } catch (e) {}
        }
        content.innerHTML = html;

        const actionsCol = showAllDayCols ? 9 : 6;
        if ($jq && $jq.fn.DataTable) {
            try {
                $jq('#systemAttendanceReportTable').DataTable({
                    pageLength: 25,
                    order: [[1, 'asc']],
                    language: {
                        search: 'Search:',
                        lengthMenu: 'Show _MENU_',
                        info: 'Showing _START_ to _END_ of _TOTAL_',
                        zeroRecords: 'No matching records'
                    },
                    columnDefs: [{ orderable: false, targets: actionsCol }]
                });
            } catch (e) {
                console.error('DataTable init error:', e);
            }
        }

        if ($jq) {
            $jq('#searchClasses').off('change.sysReport').on('change.sysReport', function () {
                const selected = String($jq(this).val() || '');
                const $rows = $jq('#systemAttendanceReportTable tbody tr');
                if (!selected) {
                    $rows.show();
                    return;
                }
                $rows.each(function () {
                    if (String($jq(this).attr('data-subclass-id')) === selected) {
                        $jq(this).show();
                    } else {
                        $jq(this).hide();
                    }
                });
            });
        }
    }

    function getPeriodLabel(searchType) {
        const labels = {
            'month': 'in this month',
            'year': 'in this year',
            'date': 'on this date',
            'date_range': 'in this date range'
        };
        return labels[searchType] || '';
    }

    function buildAttendancePdfQueryString(extraParams = {}) {
        if (!currentAttendanceSearchParams) {
            Swal.fire({
                icon: 'warning',
                title: 'No search data',
                text: 'Please search attendance first before downloading PDF.',
                confirmButtonColor: '#940000'
            });
            return null;
        }

        const params = new URLSearchParams();
        const merged = { ...currentAttendanceSearchParams, ...extraParams };

        ['search_type', 'date', 'from_date', 'to_date', 'year', 'month', 'classID', 'coordinator', 'subclassID', 'enrollment_status', 'attendance_status'].forEach(key => {
            if (merged[key]) {
                params.append(key, merged[key]);
            }
        });

        return params.toString();
    }

    function downloadAttendanceReportPdf(subclassID = null) {
        const extra = subclassID ? { subclassID } : {};
        const query = buildAttendancePdfQueryString(extra);
        if (!query) return;

        window.location.href = `{{ route('export_attendance_report_pdf') }}?${query}`;
    }

    function downloadStudentAttendancePdf(studentID) {
        if (!studentID) return;

        const searchType = document.getElementById('search_type') ? document.getElementById('search_type').value : 'month';
        const year = document.getElementById('year') ? document.getElementById('year').value : new Date().getFullYear();
        const month = document.getElementById('month') ? document.getElementById('month').value : String(new Date().getMonth() + 1).padStart(2, '0');
        const date = document.getElementById('date') ? document.getElementById('date').value : '';
        const fromDate = document.getElementById('from_date') ? document.getElementById('from_date').value : '';
        const toDate = document.getElementById('to_date') ? document.getElementById('to_date').value : '';

        const params = new URLSearchParams();
        params.append('search_type', searchType);
        params.append('year', year);
        if (searchType === 'month') {
            params.append('month', month);
        } else if (searchType === 'date' && date) {
            params.append('date', date);
        } else if (searchType === 'date_range') {
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);
        }

        window.location.href = `{{ url('export_student_attendance_pdf') }}/${studentID}?${params.toString()}`;
    }

    document.getElementById('exportAttendanceReportPdfBtn')?.addEventListener('click', function() {
        downloadAttendanceReportPdf();
    });

    document.getElementById('exportStudentAttendancePdfBtn')?.addEventListener('click', function() {
        if (currentModalStudentID) {
            downloadStudentAttendancePdf(currentModalStudentID);
        }
    });


    function viewStudentDetails(studentID, searchType = 'month') {
        const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
        const content = document.getElementById('studentDetailsContent');
        const modalFooter = document.getElementById('studentDetailsModalFooter');

        currentModalStudentID = studentID;
        currentModalSearchType = searchType;
        if (modalFooter) modalFooter.style.display = 'none';
        
        const currentYear = document.getElementById('year') ? document.getElementById('year').value : new Date().getFullYear();
        const currentMonth = document.getElementById('month') ? document.getElementById('month').value : String(new Date().getMonth() + 1).padStart(2, '0');
        const currentWeek = document.getElementById('week') ? document.getElementById('week').value : null;
        const currentDate = document.getElementById('date') ? document.getElementById('date').value : null;
        
        content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-2">Loading...</p></div>';
        modal.show();

        // Build URL based on search type
        let url = `{{ url('student_attendance_details') }}/${studentID}?search_type=${searchType}&year=${currentYear}`;
        
        if (searchType === 'month') {
            url += `&month=${currentMonth}`;
        } else if (searchType === 'week' && currentWeek) {
            url += `&week=${currentWeek}`;
        } else if (searchType === 'date' && currentDate) {
            url += `&date=${currentDate}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayStudentDetails(data, searchType);
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Error loading student details</div>';
                }
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-danger">Error loading student details</div>';
            });
    }

    function displayStudentDetails(data, searchType = 'month') {
        const content = document.getElementById('studentDetailsContent');
        const modalFooter = document.getElementById('studentDetailsModalFooter');
        const student = data.student;
        const stats = data.stats;
        const chartData = data.chart_data;

        const currentYear = document.getElementById('year') ? document.getElementById('year').value : new Date().getFullYear();
        const currentMonth = document.getElementById('month') ? document.getElementById('month').value : String(new Date().getMonth() + 1).padStart(2, '0');
        const currentDate = document.getElementById('date') ? document.getElementById('date').value : null;

        // Determine period label for overview
        let overviewLabel = '';
        if (searchType === 'date' && currentDate) {
            const dateObj = new Date(currentDate);
            overviewLabel = `Student Attendance Overview in ${dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`;
        } else if (searchType === 'month' && currentYear && currentMonth) {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const monthName = monthNames[parseInt(currentMonth) - 1];
            overviewLabel = `Student Attendance Overview in ${monthName} ${currentYear}`;
        } else if (searchType === 'year' && currentYear) {
            overviewLabel = `Student Attendance Overview in ${currentYear}`;
        } else {
            overviewLabel = 'Student Attendance Overview';
        }

        // Widget cards like classManagement attendance overview
        content.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-12">
                    <h4 class="mb-2">${student.first_name} ${student.middle_name || ''} ${student.last_name}</h4>
                    <p class="text-muted mb-2">Admission No: ${student.admission_number || 'N/A'}</p>
                    <h5 class="text-primary mb-3">${overviewLabel}</h5>
                </div>
            </div>
            
            <!-- Statistics Widgets -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-success">
                        <div class="card-body text-center" style="color: #ffffff;">
                            <h4 style="color: #ffffff; font-weight: bold;">${stats.present || 0}</h4>
                            <p class="mb-0" style="color: #ffffff; font-weight: 500;">Present</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger">
                        <div class="card-body text-center" style="color: #ffffff;">
                            <h4 style="color: #ffffff; font-weight: bold;">${stats.absent || 0}</h4>
                            <p class="mb-0" style="color: #ffffff; font-weight: 500;">Absent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning">
                        <div class="card-body text-center" style="color: #000000;">
                            <h4 style="color: #000000; font-weight: bold;">${stats.sick || 0}</h4>
                            <p class="mb-0" style="color: #000000; font-weight: 500;">Sick</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info">
                        <div class="card-body text-center" style="color: #ffffff;">
                            <h4 style="color: #ffffff; font-weight: bold;">${stats.excused || 0}</h4>
                            <p class="mb-0" style="color: #ffffff; font-weight: 500;">Permission</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card mb-3">
                <div class="card-header text-white" style="background-color: #940000;">
                    <h6 class="mb-0">Summary</h6>
                </div>
                <div class="card-body">
                    <p><strong>Total Days:</strong> ${stats.total_days || 0}</p>
                    <p><strong>Total Present:</strong> ${stats.present || 0}</p>
                    <p><strong>Total Absent:</strong> ${stats.absent || 0}</p>
                    <p><strong>Total Sick:</strong> ${stats.sick || 0}</p>
                    <p><strong>Total Permission:</strong> ${stats.excused || 0}</p>
                </div>
            </div>

            <!-- Charts -->
            ${searchType !== 'date' ? `
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header text-white" style="background-color: #940000;">
                            <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Attendance Percentages</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="studentChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Attendance History -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header text-white" style="background-color: #940000;">
                            <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Attendance History</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.attendances && data.attendances.length > 0 ? data.attendances.map(att => {
                                            const statusClass = {
                                                'Present': 'bg-success',
                                                'Absent': 'bg-danger',
                                                'Sick': 'bg-warning',
                                                'Excused': 'bg-info'
                                            }[att.status] || 'bg-secondary';
                                            return `
                                                <tr>
                                                    <td>${new Date(att.attendance_date).toLocaleDateString()}</td>
                                                    <td><span class="badge ${statusClass}">${att.status === 'Excused' ? 'Permission' : att.status}</span></td>
                                                    <td>${att.remark || '-'}</td>
                                                </tr>
                                            `;
                                        }).join('') : '<tr><td colspan="3" class="text-center">No attendance records found</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (modalFooter) modalFooter.style.display = 'flex';

        // Display student chart (only for month and year, not for date)
        if (searchType !== 'date') {
            setTimeout(() => {
                displayStudentChart(data, searchType);
            }, 100);
        }
    }

    function displayStudentChart(data, searchType) {
        const ctx = document.getElementById('studentChart');
        if (!ctx) return;
        
        const chartCtx = ctx.getContext('2d');
        
        if (studentChart) {
            studentChart.destroy();
        }

        const stats = data.stats;
        const totalDays = stats.total_days || 0;

        // Calculate percentages
        let presentPercentage = 0;
        let absentPercentage = 0;
        let sickPercentage = 0;
        let excusedPercentage = 0;

        if (totalDays > 0) {
            presentPercentage = ((stats.present || 0) / totalDays * 100).toFixed(2);
            absentPercentage = ((stats.absent || 0) / totalDays * 100).toFixed(2);
            sickPercentage = ((stats.sick || 0) / totalDays * 100).toFixed(2);
            excusedPercentage = ((stats.excused || 0) / totalDays * 100).toFixed(2);
        }

        const labels = ['Present', 'Absent', 'Sick', 'Permission'];
        const dataValues = [
            parseFloat(presentPercentage),
            parseFloat(absentPercentage),
            parseFloat(sickPercentage),
            parseFloat(excusedPercentage)
        ];

        studentChart = new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Attendance Percentage (%)',
                    data: dataValues,
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8'],
                    borderColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Export Fingerprint Attendance to Excel
    function exportFingerprintAttendanceToExcel() {
        if (typeof XLSX === 'undefined') {
            Swal.fire('Error', 'Excel export library not loaded', 'error');
            return;
        }

        if (!currentFingerprintAttendanceData || currentFingerprintAttendanceData.length === 0) {
            Swal.fire('Error', 'No data to export. Please search attendance first.', 'error');
            return;
        }

        // Get school name and build title
        var schoolName = '{{ $school_details->school_name ?? "School" }}';
        var reportTitle = '';
        if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0];
        } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + currentFingerprintSearchYear;
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            var dateObj = new Date(currentFingerprintSearchDate);
            var dateFormatted = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE ON ' + dateFormatted.toUpperCase();
        } else {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE';
        }

        // Calculate working days
        var startDate, endDate;
        if (currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
            endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchYear) {
            startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
            endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            // For date view, single day
            startDate = endDate = new Date(currentFingerprintSearchDate);
        } else {
            Swal.fire('Error', 'No valid search period selected.', 'error');
            return;
        }
        
        // For date view, export students with check in/out times
        if (currentFingerprintSearchType === 'date') {
            // Collect all students from all classes with check in/out times
            var studentList = [];
            currentFingerprintAttendanceData.forEach(function(classData) {
                if (classData.students && classData.students.length > 0) {
                    classData.students.forEach(function(student) {
                        var fullName = (student.first_name || '') + ' ' +
                                       (student.middle_name ? student.middle_name + ' ' : '') +
                                       (student.last_name || '');
                        fullName = fullName.trim() || 'N/A';
                        
                        studentList.push({
                            name: fullName,
                            admissionNumber: student.admission_number || 'N/A',
                            className: student.subclass_name || (classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name) || 'N/A',
                            checkInTime: student.check_in_time || '--',
                            checkOutTime: student.check_out_time || '--'
                        });
                    });
                }
            });
            
            studentList.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });
            
            // Create workbook
            var wb = XLSX.utils.book_new();
            var wsData = [];
            
            // Header rows
            wsData.push([schoolName]);
            wsData.push([reportTitle]);
            wsData.push([]);
            wsData.push(['Student Name', 'Class', 'Admission Number', 'Check In Time', 'Check Out Time']);
            
            // Student data
            studentList.forEach(function(student) {
                wsData.push([student.name, student.className, student.admissionNumber, student.checkInTime, student.checkOutTime]);
            });
            
            var ws = XLSX.utils.aoa_to_sheet(wsData);
            if (!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: 4}});
            ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: 4}});
            
            XLSX.utils.book_append_sheet(wb, ws, 'Fingerprint Attendance');
            var fileName = 'Fingerprint_Attendance_' + currentFingerprintSearchDate.replace(/-/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.xlsx';
            XLSX.writeFile(wb, fileName);
            return;
        }

        var workingDays = 0;
        var current = new Date(startDate);
        while (current <= endDate) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        // Get selected class
        var selectedClassId = document.getElementById('searchFingerprintClasses') ? document.getElementById('searchFingerprintClasses').value : '';
        var selectedClassName = '';
        
        // Collect students from selected class only
        var studentMap = {};
        currentFingerprintAttendanceData.forEach(function(classData) {
            // If a class is selected, only export that class
            if (selectedClassId && classData.subclassID != selectedClassId) {
                return;
            }
            
            // Store selected class name for report title
            if (selectedClassId && classData.subclassID == selectedClassId) {
                selectedClassName = classData.subclass_name;
            }
            
            if (classData.students && classData.students.length > 0) {
                classData.students.forEach(function(student) {
                    var studentId = student.studentID || '';
                    if (!studentId) return;

                    var fullName = (student.first_name || '') + ' ' +
                                   (student.middle_name ? student.middle_name + ' ' : '') +
                                   (student.last_name || '');
                    fullName = fullName.trim() || 'N/A';

                    if (!studentMap[studentId]) {
                        studentMap[studentId] = {
                            name: fullName,
                            className: student.subclass_name || (classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name) || 'N/A',
                            daysPresent: 0,
                            daysAbsent: 0
                        };
                    }
                    studentMap[studentId].daysPresent = student.days_present || 0;
                    studentMap[studentId].daysAbsent = student.days_absent || 0;
                });
            }
        });
        
        // Update report title if class is selected
        if (selectedClassName) {
            reportTitle = reportTitle + ' - ' + selectedClassName.toUpperCase();
        }

        var studentList = Object.values(studentMap);
        studentList.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });

        // Create workbook
        var wb = XLSX.utils.book_new();
        var wsData = [];

        // Header rows
        wsData.push([schoolName]);
        wsData.push([reportTitle]);
        wsData.push([]);
        wsData.push(['Student Name', 'Class', 'Days Present', 'Days Absent', 'Total Days']);

        // Student data
        studentList.forEach(function(student) {
            wsData.push([student.name, student.className, student.daysPresent, student.daysAbsent, workingDays]);
        });

        var ws = XLSX.utils.aoa_to_sheet(wsData);
        if (!ws['!merges']) ws['!merges'] = [];
        ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: 4}});
        ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: 4}});

        XLSX.utils.book_append_sheet(wb, ws, 'Fingerprint Attendance');
        var fileName = 'Fingerprint_Attendance_';
        if (currentFingerprintSearchMonth) {
            fileName += currentFingerprintSearchMonth.replace(/-/g, '_');
        } else if (currentFingerprintSearchYear) {
            fileName += currentFingerprintSearchYear;
        } else if (currentFingerprintSearchDate) {
            fileName += currentFingerprintSearchDate.replace(/-/g, '_');
        } else {
            fileName += 'Report';
        }
        fileName += '_' + new Date().toISOString().split('T')[0] + '.xlsx';
        XLSX.writeFile(wb, fileName);
    }

    // Export Fingerprint Attendance to PDF
    function exportFingerprintAttendanceToPdf() {
        if (typeof window.jspdf === 'undefined') {
            Swal.fire('Error', 'PDF export library not loaded', 'error');
            return;
        }

        if (!currentFingerprintAttendanceData || currentFingerprintAttendanceData.length === 0) {
            Swal.fire('Error', 'No data to export. Please search attendance first.', 'error');
            return;
        }

        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('landscape');

        // Get school name and build title
        var schoolName = '{{ $school_details->school_name ?? "School" }}';
        var reportTitle = '';
        if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0];
        } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + currentFingerprintSearchYear;
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            var dateObj = new Date(currentFingerprintSearchDate);
            var dateFormatted = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE ON ' + dateFormatted.toUpperCase();
        } else {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE';
        }

        // Calculate working days
        var startDate, endDate;
        if (currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
            endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchYear) {
            startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
            endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            // For date view, single day
            startDate = endDate = new Date(currentFingerprintSearchDate);
        } else {
            Swal.fire('Error', 'No valid search period selected.', 'error');
            return;
        }
        
        // For date view, export students with check in/out times
        if (currentFingerprintSearchType === 'date') {
            // Collect all students from all classes with check in/out times
            var studentList = [];
            currentFingerprintAttendanceData.forEach(function(classData) {
                if (classData.students && classData.students.length > 0) {
                    classData.students.forEach(function(student) {
                        var fullName = (student.first_name || '') + ' ' +
                                       (student.middle_name ? student.middle_name + ' ' : '') +
                                       (student.last_name || '');
                        fullName = fullName.trim() || 'N/A';
                        
                        studentList.push({
                            name: fullName,
                            admissionNumber: student.admission_number || 'N/A',
                            className: student.subclass_name || (classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name) || 'N/A',
                            checkInTime: student.check_in_time || '--',
                            checkOutTime: student.check_out_time || '--'
                        });
                    });
                }
            });
            
            studentList.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });
            
            // Add header with logo and centered titles
            var schoolLogoUrl = '{{ $school_details->school_logo ? asset($school_details->school_logo) : "" }}';
            
            function drawHeaderAndTable(logoImg) {
                var pageWidth = doc.internal.pageSize.getWidth();
                var centerX = pageWidth / 2;
                
                // Logo on the left if available
                if (logoImg) {
                    try {
                        doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                    } catch (e) {
                        console.warn('Failed to add logo to PDF:', e);
                    }
                }
                
                // School name and report title centered
                doc.setFontSize(16);
                doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
                doc.setFontSize(12);
                doc.text(reportTitle, centerX, 26, { align: 'center' });
                
                // Prepare table data
                var tableData = [];
                studentList.forEach(function(student) {
                    tableData.push([student.name, student.className, student.admissionNumber, student.checkInTime, student.checkOutTime]);
                });
                
                // Add table
                doc.autoTable({
                    startY: 36,
                    head: [['Student Name', 'Class', 'Admission Number', 'Check In Time', 'Check Out Time']],
                    body: tableData,
                    theme: 'striped',
                    headStyles: { fillColor: [148, 0, 0] },
                    didDrawPage: function (data) {
                        // Footer on each page
                        var pageHeight = doc.internal.pageSize.getHeight();
                        doc.setFontSize(9);
                        doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                    }
                });
                
                // Save PDF
                var fileName = 'Fingerprint_Attendance_' + currentFingerprintSearchDate.replace(/-/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf';
                doc.save(fileName);
            }
            
            if (schoolLogoUrl) {
                var img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    drawHeaderAndTable(img);
                };
                img.onerror = function() {
                    console.warn('Failed to load school logo image for PDF header.');
                    drawHeaderAndTable(null);
                };
                img.src = schoolLogoUrl;
            } else {
                drawHeaderAndTable(null);
            }
            return;
        }

        var workingDays = 0;
        var current = new Date(startDate);
        while (current <= endDate) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        // Get selected class
        var selectedClassId = document.getElementById('searchFingerprintClasses') ? document.getElementById('searchFingerprintClasses').value : '';
        var selectedClassName = '';
        
        // Collect students from selected class only
        var studentMap = {};
        currentFingerprintAttendanceData.forEach(function(classData) {
            // If a class is selected, only export that class
            if (selectedClassId && classData.subclassID != selectedClassId) {
                return;
            }
            
            // Store selected class name for report title
            if (selectedClassId && classData.subclassID == selectedClassId) {
                selectedClassName = classData.subclass_name;
            }
            
            if (classData.students && classData.students.length > 0) {
                classData.students.forEach(function(student) {
                    var studentId = student.studentID || '';
                    if (!studentId) return;

                    var fullName = (student.first_name || '') + ' ' +
                                   (student.middle_name ? student.middle_name + ' ' : '') +
                                   (student.last_name || '');
                    fullName = fullName.trim() || 'N/A';

                    if (!studentMap[studentId]) {
                        studentMap[studentId] = {
                            name: fullName,
                            className: student.subclass_name || (classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name) || 'N/A',
                            daysPresent: 0,
                            daysAbsent: 0
                        };
                    }
                    studentMap[studentId].daysPresent = student.days_present || 0;
                    studentMap[studentId].daysAbsent = student.days_absent || 0;
                });
            }
        });
        
        // Update report title if class is selected
        if (selectedClassName) {
            reportTitle = reportTitle + ' - ' + selectedClassName.toUpperCase();
        }

        var studentList = Object.values(studentMap);
        studentList.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });

        // Add header with logo and centered titles
        var schoolLogoUrl = '{{ $school_details->school_logo ? asset($school_details->school_logo) : "" }}';

        function drawHeaderAndTable(logoImg) {
            var pageWidth = doc.internal.pageSize.getWidth();
            var centerX = pageWidth / 2;

            // Logo on the left if available
            if (logoImg) {
                try {
                    doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                } catch (e) {
                    console.warn('Failed to add logo to PDF:', e);
                }
            }

            // School name and report title centered
            doc.setFontSize(16);
            doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
            doc.setFontSize(12);
            doc.text(reportTitle, centerX, 26, { align: 'center' });

            // Prepare table data
            var tableData = [];
            studentList.forEach(function(student) {
                tableData.push([student.name, student.className, student.daysPresent, student.daysAbsent, workingDays]);
            });

            // Add table
            doc.autoTable({
                startY: 36,
                head: [['Student Name', 'Class', 'Days Present', 'Days Absent', 'Total Days']],
                body: tableData,
                theme: 'striped',
                headStyles: { fillColor: [148, 0, 0] },
                didDrawPage: function (data) {
                    // Footer on each page
                    var pageHeight = doc.internal.pageSize.getHeight();
                    doc.setFontSize(9);
                    doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                }
            });

            // Save PDF
            doc.save('Fingerprint_Attendance_' + (currentFingerprintSearchMonth || currentFingerprintSearchYear || 'Report') + '_' + new Date().toISOString().split('T')[0] + '.pdf');
        }

        if (schoolLogoUrl) {
            var img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = function() {
                drawHeaderAndTable(img);
            };
            img.onerror = function() {
                console.warn('Failed to load school logo image for PDF header.');
                drawHeaderAndTable(null);
            };
            img.src = schoolLogoUrl;
        } else {
            drawHeaderAndTable(null);
        }
    }

    // Bind export buttons
    document.getElementById('exportFingerprintExcelBtn').addEventListener('click', function() {
        exportFingerprintAttendanceToExcel();
    });

    document.getElementById('exportFingerprintPdfBtn').addEventListener('click', function() {
        exportFingerprintAttendanceToPdf();
    });

    // Export specific class fingerprint attendance to Excel
    function exportClassFingerprintAttendanceToExcel(subclassId, subclassName) {
        if (typeof XLSX === 'undefined') {
            Swal.fire('Error', 'Excel export library not loaded', 'error');
            return;
        }

        if (!currentFingerprintAttendanceData || currentFingerprintAttendanceData.length === 0) {
            Swal.fire('Error', 'No data to export. Please search attendance first.', 'error');
            return;
        }

        // Find the class data
        var classData = currentFingerprintAttendanceData.find(function(c) {
            return c.subclassID == subclassId;
        });

        if (!classData || !classData.students || classData.students.length === 0) {
            Swal.fire('Error', 'No students found for this class.', 'error');
            return;
        }

        // Get school name and build title
        var schoolName = '{{ $school_details->school_name ?? "School" }}';
        var reportTitle = '';
        if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0] + ' - ' + subclassName.toUpperCase();
        } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + currentFingerprintSearchYear + ' - ' + subclassName.toUpperCase();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            var dateObj = new Date(currentFingerprintSearchDate);
            var dateFormatted = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE ON ' + dateFormatted.toUpperCase() + ' - ' + subclassName.toUpperCase();
        } else {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE - ' + subclassName.toUpperCase();
        }

        // Calculate working days
        var startDate, endDate;
        if (currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
            endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchYear) {
            startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
            endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            // For date view, single day
            startDate = endDate = new Date(currentFingerprintSearchDate);
        } else {
            Swal.fire('Error', 'No valid search period selected.', 'error');
            return;
        }

        var workingDays = 0;
        var current = new Date(startDate);
        while (current <= endDate) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        // Prepare student list
        var studentList = [];
        if (currentFingerprintSearchType === 'date') {
            // For date view, include check in/out times
            studentList = classData.students.map(function(student) {
                var fullName = (student.first_name || '') + ' ' +
                               (student.middle_name ? student.middle_name + ' ' : '') +
                               (student.last_name || '');
                return {
                    name: fullName.trim() || 'N/A',
                    admissionNumber: student.admission_number || 'N/A',
                    checkInTime: student.check_in_time || '--',
                    checkOutTime: student.check_out_time || '--'
                };
            });
        } else {
            // For month/year view, include days present/absent
            studentList = classData.students.map(function(student) {
                var fullName = (student.first_name || '') + ' ' +
                               (student.middle_name ? student.middle_name + ' ' : '') +
                               (student.last_name || '');
                return {
                    name: fullName.trim() || 'N/A',
                    daysPresent: student.days_present || 0,
                    daysAbsent: student.days_absent || 0
                };
            });
        }

        studentList.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });

        // Create workbook
        var wb = XLSX.utils.book_new();
        var wsData = [];

        // Header rows
        wsData.push([schoolName]);
        wsData.push([reportTitle]);
        wsData.push([]);
        
        if (currentFingerprintSearchType === 'date') {
            wsData.push(['Student Name', 'Admission Number', 'Check In Time', 'Check Out Time']);
            // Student data
            studentList.forEach(function(student) {
                wsData.push([student.name, student.admissionNumber, student.checkInTime, student.checkOutTime]);
            });
        } else {
            wsData.push(['Student Name', 'Days Present', 'Days Absent', 'Total Days']);
            // Student data
            studentList.forEach(function(student) {
                wsData.push([student.name, student.daysPresent, student.daysAbsent, workingDays]);
            });
        }

        var ws = XLSX.utils.aoa_to_sheet(wsData);
        if (!ws['!merges']) ws['!merges'] = [];
        var colCount = currentFingerprintSearchType === 'date' ? 3 : 3;
        ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: colCount}});
        ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: colCount}});

        XLSX.utils.book_append_sheet(wb, ws, 'Fingerprint Attendance');
        var fileName = 'Fingerprint_Attendance_' + subclassName.replace(/\s+/g, '_') + '_';
        if (currentFingerprintSearchMonth) {
            fileName += currentFingerprintSearchMonth.replace(/-/g, '_');
        } else if (currentFingerprintSearchYear) {
            fileName += currentFingerprintSearchYear;
        } else if (currentFingerprintSearchDate) {
            fileName += currentFingerprintSearchDate.replace(/-/g, '_');
        } else {
            fileName += 'Report';
        }
        fileName += '_' + new Date().toISOString().split('T')[0] + '.xlsx';
        XLSX.writeFile(wb, fileName);
    }

    // Export specific class fingerprint attendance to PDF
    function exportClassFingerprintAttendanceToPdf(subclassId, subclassName) {
        if (typeof window.jspdf === 'undefined') {
            Swal.fire('Error', 'PDF export library not loaded', 'error');
            return;
        }

        if (!currentFingerprintAttendanceData || currentFingerprintAttendanceData.length === 0) {
            Swal.fire('Error', 'No data to export. Please search attendance first.', 'error');
            return;
        }

        // Find the class data
        var classData = currentFingerprintAttendanceData.find(function(c) {
            return c.subclassID == subclassId;
        });

        if (!classData || !classData.students || classData.students.length === 0) {
            Swal.fire('Error', 'No students found for this class.', 'error');
            return;
        }

        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('landscape');

        // Get school name and build title
        var schoolName = '{{ $school_details->school_name ?? "School" }}';
        var reportTitle = '';
        if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0] + ' - ' + subclassName.toUpperCase();
        } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + currentFingerprintSearchYear + ' - ' + subclassName.toUpperCase();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            var dateObj = new Date(currentFingerprintSearchDate);
            var dateFormatted = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE ON ' + dateFormatted.toUpperCase() + ' - ' + subclassName.toUpperCase();
        } else {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE - ' + subclassName.toUpperCase();
        }

        // Calculate working days
        var startDate, endDate;
        if (currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
            endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchYear) {
            startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
            endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            // For date view, single day
            startDate = endDate = new Date(currentFingerprintSearchDate);
        } else {
            Swal.fire('Error', 'No valid search period selected.', 'error');
            return;
        }

        var workingDays = 0;
        var current = new Date(startDate);
        while (current <= endDate) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        // Prepare student list
        var studentList = [];
        if (currentFingerprintSearchType === 'date') {
            // For date view, include check in/out times
            studentList = classData.students.map(function(student) {
                var fullName = (student.first_name || '') + ' ' +
                               (student.middle_name ? student.middle_name + ' ' : '') +
                               (student.last_name || '');
                return {
                    name: fullName.trim() || 'N/A',
                    admissionNumber: student.admission_number || 'N/A',
                    checkInTime: student.check_in_time || '--',
                    checkOutTime: student.check_out_time || '--'
                };
            });
        } else {
            // For month/year view, include days present/absent
            studentList = classData.students.map(function(student) {
                var fullName = (student.first_name || '') + ' ' +
                               (student.middle_name ? student.middle_name + ' ' : '') +
                               (student.last_name || '');
                return {
                    name: fullName.trim() || 'N/A',
                    daysPresent: student.days_present || 0,
                    daysAbsent: student.days_absent || 0
                };
            });
        }

        studentList.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });

        // Add header with logo and centered titles
        var schoolLogoUrl = '{{ $school_details->school_logo ? asset($school_details->school_logo) : "" }}';

        function drawHeaderAndTable(logoImg) {
            var pageWidth = doc.internal.pageSize.getWidth();
            var centerX = pageWidth / 2;

            // Logo on the left if available
            if (logoImg) {
                try {
                    doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                } catch (e) {
                    console.warn('Failed to add logo to PDF:', e);
                }
            }

            // School name and report title centered
            doc.setFontSize(16);
            doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
            doc.setFontSize(12);
            doc.text(reportTitle, centerX, 26, { align: 'center' });

            // Prepare table data
            var tableData = [];
            if (currentFingerprintSearchType === 'date') {
                studentList.forEach(function(student) {
                    tableData.push([student.name, student.admissionNumber, student.checkInTime, student.checkOutTime]);
                });
            } else {
                studentList.forEach(function(student) {
                    tableData.push([student.name, student.daysPresent, student.daysAbsent, workingDays]);
                });
            }

            // Add table
            var tableHeaders = currentFingerprintSearchType === 'date' 
                ? [['Student Name', 'Admission Number', 'Check In Time', 'Check Out Time']]
                : [['Student Name', 'Days Present', 'Days Absent', 'Total Days']];
            
            doc.autoTable({
                startY: 36,
                head: tableHeaders,
                body: tableData,
                theme: 'striped',
                headStyles: { fillColor: [148, 0, 0] },
                didDrawPage: function (data) {
                    // Footer on each page
                    var pageHeight = doc.internal.pageSize.getHeight();
                    doc.setFontSize(9);
                    doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                }
            });

            // Save PDF
            var fileName = 'Fingerprint_Attendance_' + subclassName.replace(/\s+/g, '_') + '_';
            if (currentFingerprintSearchMonth) {
                fileName += currentFingerprintSearchMonth.replace(/-/g, '_');
            } else if (currentFingerprintSearchYear) {
                fileName += currentFingerprintSearchYear;
            } else if (currentFingerprintSearchDate) {
                fileName += currentFingerprintSearchDate.replace(/-/g, '_');
            } else {
                fileName += 'Report';
            }
            fileName += '_' + new Date().toISOString().split('T')[0] + '.pdf';
            doc.save(fileName);
            doc.save('Fingerprint_Attendance_' + subclassName.replace(/\s+/g, '_') + '_' + (currentFingerprintSearchMonth || currentFingerprintSearchYear || 'Report') + '_' + new Date().toISOString().split('T')[0] + '.pdf');
        }

        if (schoolLogoUrl) {
            var img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = function() {
                drawHeaderAndTable(img);
            };
            img.onerror = function() {
                console.warn('Failed to load school logo image for PDF header.');
                drawHeaderAndTable(null);
            };
            img.src = schoolLogoUrl;
        } else {
            drawHeaderAndTable(null);
        }
    }

    // ==================== EXAM ATTENDANCE SECTION ====================
    let examStudentsTable = null;
    let examAllStudentsData = [];

    // Load main classes when page loads
    $(document).ready(function() {
        loadExamMainClasses();
    });

    // Load main classes
    function loadExamMainClasses() {
        $.get('/admin/get-classes')
            .done(function(response) {
                if (response.success && response.classes) {
                    let options = '<option value="">All Classes</option>';
                    response.classes.forEach(function(cls) {
                        options += `<option value="${cls.classID}">${cls.class_name}</option>`;
                    });
                    $('#examFilterMainClass').html(options);
                }
            })
            .fail(function() {
                console.error('Error loading classes');
            });
    }

    // When year is selected, load terms from database and enable term
    $('#examFilterYear').on('change', function() {
        const year = $(this).val();
        if (year) {
            loadTermsForYear(year);
        } else {
            $('#examFilterTerm').prop('disabled', true).html('<option value="">Select Term</option>');
            $('#examFilterExam').prop('disabled', true).html('<option value="">Select Exam</option>');
            hideExamAttendanceData();
        }
    });

    // Load terms for year from database
    function loadTermsForYear(year) {
        $.get('/admin/get-terms-for-year', { year: year })
            .done(function(response) {
                if (response.success && response.terms) {
                    let options = '<option value="">Select Term</option>';
                    response.terms.forEach(function(term) {
                        // Format term: first_term -> First Term
                        const termDisplay = term.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                        options += `<option value="${term}">${termDisplay}</option>`;
                    });
                    $('#examFilterTerm').html(options).prop('disabled', false);
                } else {
                    $('#examFilterTerm').html('<option value="">No terms available</option>').prop('disabled', false);
                }
            })
            .fail(function() {
                $('#examFilterTerm').html('<option value="">Error loading terms</option>').prop('disabled', false);
            });
    }

    // When term is selected, load exams
    $('#examFilterTerm').on('change', function() {
        const year = $('#examFilterYear').val();
        const term = $(this).val();
        if (year && term) {
            $('#examFilterExam').prop('disabled', false);
            loadExamsForYearTerm(year, term);
        } else {
            $('#examFilterExam').prop('disabled', true).html('<option value="">Select Exam</option>');
            hideExamAttendanceData();
        }
    });

    // When main class is selected, load subclasses and auto-load data if exam is selected
    $('#examFilterMainClass').on('change', function() {
        const classID = $(this).val();
        if (classID) {
            $('#examFilterSubclass').prop('disabled', false);
            loadSubclassesForClass(classID);
        } else {
            $('#examFilterSubclass').prop('disabled', true).html('<option value="">All Subclasses</option>');
        }
        
        // Auto-load data if exam is already selected
        const examID = $('#examFilterExam').val();
        if (examID) {
            loadExamAttendanceData();
        }
    });

    // When exam is selected, load subjects and attendance
    $('#examFilterExam').on('change', function() {
        const examID = $(this).val();
        if (examID) {
            loadSubjectsForClass();
            loadExamAttendanceData();
        } else {
            $('#examFilterSubject').html('<option value="">All Subjects</option>');
            hideExamAttendanceData();
        }
    });

    // When class or subclass changes, reload subjects
    $('#examFilterMainClass, #examFilterSubclass').on('change', function() {
        const examID = $('#examFilterExam').val();
        if (examID) {
            loadSubjectsForClass();
        }
    });

    // When subject is selected, reload attendance
    $('#examFilterSubject').on('change', function() {
        const examID = $('#examFilterExam').val();
        if (examID) {
            loadExamAttendanceData();
        }
    });

    // Load subjects for selected class
    function loadSubjectsForClass() {
        const mainClassID = $('#examFilterMainClass').val();
        const subclassID = $('#examFilterSubclass').val();
        const examID = $('#examFilterExam').val();

        if (!examID) {
            $('#examFilterSubject').html('<option value="">All Subjects</option>');
            return;
        }

        $.get('/admin/get-subjects-for-class', {
            examID: examID,
            mainClassID: mainClassID || '',
            subclassID: subclassID || ''
        })
            .done(function(response) {
                if (response.success && response.subjects) {
                    let options = '<option value="">All Subjects</option>';
                    response.subjects.forEach(function(subject) {
                        options += `<option value="${subject.subjectID}">${subject.subject_name}${subject.subject_code ? ' (' + subject.subject_code + ')' : ''}</option>`;
                    });
                    $('#examFilterSubject').html(options);
                } else {
                    $('#examFilterSubject').html('<option value="">All Subjects</option>');
                }
            })
            .fail(function() {
                $('#examFilterSubject').html('<option value="">All Subjects</option>');
            });
    }

    // When subclass is selected, auto-load data if exam is selected
    $('#examFilterSubclass').on('change', function() {
        const examID = $('#examFilterExam').val();
        if (examID) {
            loadExamAttendanceData();
        }
    });

    // Load exams for year and term
    function loadExamsForYearTerm(year, term) {
        $.get('/admin/get-exams-for-year-term', {
            year: year,
            term: term
        })
        .done(function(response) {
            if (response.success && response.exams) {
                let options = '<option value="">Select Exam</option>';
                response.exams.forEach(function(exam) {
                    options += `<option value="${exam.examID}">${exam.exam_name}</option>`;
                });
                $('#examFilterExam').html(options);
            }
        })
        .fail(function() {
            $('#examFilterExam').html('<option value="">Error loading exams</option>');
        });
    }

    // Load subclasses for class
    function loadSubclassesForClass(classID) {
        $.get('/admin/get-subclasses-for-class', { classID: classID })
            .done(function(response) {
                if (response.success && response.subclasses) {
                    let options = '<option value="">All Subclasses</option>';
                    response.subclasses.forEach(function(sub) {
                        options += `<option value="${sub.subclassID}">${sub.subclass_name}</option>`;
                    });
                    $('#examFilterSubclass').html(options);
                }
            })
            .fail(function() {
                console.error('Error loading subclasses');
            });
    }

    // Load exam attendance data
    function loadExamAttendanceData() {
        const examID = $('#examFilterExam').val();
        const mainClassID = $('#examFilterMainClass').val();
        const subclassID = $('#examFilterSubclass').val();
        const subjectID = $('#examFilterSubject').val();

        if (!examID) {
            hideExamAttendanceData();
            return;
        }

        // Show loading
        $('#examStudentsTableBody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div></td></tr>');
        $('#examAttendanceStats').show();
        $('#examStudentsTableContainer').show();

        $.get('/admin/get-exam-attendance-data', {
            examID: examID,
            mainClassID: mainClassID || '',
            subclassID: subclassID || '',
            subjectID: subjectID || ''
        })
        .done(function(response) {
            if (response.success && response.data) {
                displayExamAttendanceData(response.data);
            } else {
                $('#examStudentsTableBody').html('<tr><td colspan="6" class="text-center text-muted">No attendance data available</td></tr>');
                hideExamAttendanceStats();
            }
        })
        .fail(function() {
            $('#examStudentsTableBody').html('<tr><td colspan="6" class="text-center text-danger">Error loading attendance data</td></tr>');
            hideExamAttendanceStats();
        });
    }

    // Display exam attendance data
    function displayExamAttendanceData(data) {
        // Store all students data with subjects information
        examAllStudentsData = (data.students || []).map(function(student) {
            return {
                studentID: student.studentID,
                name: student.name || 'N/A',
                class_display: student.class_display || (student.class_name + ' - ' + student.subclass_name) || 'N/A',
                status: student.status || 'Absent',
                subjects_taken: student.subjects_taken || 0,
                total_subjects: student.total_subjects || 0,
                missed_subjects: student.missed_subjects || []
            };
        });

        // Calculate statistics
        let totalPresent = 0;
        let totalAbsent = 0;
        let totalStudents = examAllStudentsData.length;

        examAllStudentsData.forEach(function(student) {
            if (student.status === 'Present' || student.status === 'present') {
                totalPresent++;
            } else {
                totalAbsent++;
            }
        });

        // Update statistics widgets
        $('#examTotalPresent').text(totalPresent);
        $('#examTotalAbsent').text(totalAbsent);
        $('#examTotalStudents').text(totalStudents);

        // Show statistics
        $('#examAttendanceStats').show();

        // Display all students
        displayExamStudentsTable('all');
    }

    // Display students table
    function displayExamStudentsTable(filter) {
        // Destroy existing DataTable
        if (examStudentsTable) {
            examStudentsTable.destroy();
            examStudentsTable = null;
        }

        // Filter students
        let filteredStudents = examAllStudentsData;
        if (filter === 'present') {
            filteredStudents = examAllStudentsData.filter(s => s.status === 'Present' || s.status === 'present');
        } else if (filter === 'absent') {
            filteredStudents = examAllStudentsData.filter(s => {
                const status = (s.status || '').toLowerCase();
                return status !== 'present';
            });
        }

        const tbody = $('#examStudentsTableBody');
        tbody.empty();

        if (filteredStudents.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center text-muted">No students found</td></tr>');
            $('#examStudentsTableContainer').show();
            return;
        }

        filteredStudents.forEach(function(student, index) {
            // Check if student has taken any subjects
            const subjectsTaken = student.subjects_taken || 0;
            const totalSubjects = student.total_subjects || 0;
            const hasTakenAnySubject = subjectsTaken > 0;
            
            // Highlight students who haven't taken any subject (red background)
            const rowClass = !hasTakenAnySubject ? 'bg-danger text-white' : '';
            const statusBadge = hasTakenAnySubject 
                ? `<span class="badge bg-success">${subjectsTaken}/${totalSubjects} Subjects</span>` 
                : '<span class="badge bg-danger">0/' + totalSubjects + ' Subjects</span>';
            
            // View More button (only show if student has missed subjects)
            const viewMoreBtn = student.missed_subjects && student.missed_subjects.length > 0
                ? `<button class="btn btn-sm btn-info" onclick="viewStudentMissedSubjects(${student.studentID}, '${student.name.replace(/'/g, "\\'")}')" title="View missed subjects">
                    <i class="bi bi-eye"></i> View More
                   </button>`
                : '<span class="text-muted">-</span>';

            const row = `
                <tr class="${rowClass}">
                    <td>${index + 1}</td>
                    <td><strong>${student.name || 'N/A'}</strong></td>
                    <td>${student.class_display || 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>${hasTakenAnySubject ? '<span class="badge bg-success">Present</span>' : '<span class="badge bg-danger">Absent</span>'}</td>
                    <td>${viewMoreBtn}</td>
                </tr>
            `;
            tbody.append(row);
        });

        // Initialize DataTable
        examStudentsTable = $('#examStudentsTable').DataTable({
            responsive: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            order: [[1, 'asc']]
        });

        $('#examStudentsTableContainer').show();
    }

    // Click handlers for statistics widgets
    $(document).on('click', '.clickable-stat-exam', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const filter = $(this).data('filter');
        const examID = $('#examFilterExam').val();
        
        if (!examID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Select Exam First',
                    text: 'Please select an exam to view attendance data.',
                    icon: 'info',
                    confirmButtonColor: '#940000'
                });
            }
            return;
        }

        if (examAllStudentsData.length === 0) {
            // Load data first
            loadExamAttendanceData();
            // Wait for data to load, then filter
            setTimeout(function() {
                if (examAllStudentsData.length > 0) {
                    displayExamStudentsTable(filter);
                }
            }, 1000);
        } else {
            // Show loading
            $('#examStudentsTableBody').html('<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div></td></tr>');
            $('#examStudentsTableContainer').show();
            
            // Scroll to table
            $('html, body').animate({
                scrollTop: $('#examStudentsTableContainer').offset().top - 100
            }, 500);
            
            // Display filtered data
            setTimeout(function() {
                displayExamStudentsTable(filter);
            }, 300);
        }
    });

    // Hide exam attendance data
    function hideExamAttendanceData() {
        $('#examAttendanceStats').hide();
        $('#examStudentsTableContainer').hide();
        if (examStudentsTable) {
            examStudentsTable.destroy();
            examStudentsTable = null;
        }
        examAllStudentsData = [];
    }

    function hideExamAttendanceStats() {
        $('#examAttendanceStats').hide();
    }

    // View student missed subjects
    function viewStudentMissedSubjects(studentID, studentName) {
        const examID = $('#examFilterExam').val();
        
        if (!examID) {
            Swal.fire({
                title: 'Error',
                text: 'Please select an exam first',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            return;
        }

        // Show loading
        $('#studentMissedSubjectsModalBody').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div></div>');
        $('#studentMissedSubjectsModal').modal('show');
        $('#studentMissedSubjectsModalLabel').html(`<i class="bi bi-person"></i> ${studentName} - Missed Subjects`);

        // Load missed subjects
        $.get('/admin/get-student-missed-subjects', {
            studentID: studentID,
            examID: examID
        })
        .done(function(response) {
            if (response.success && response.missed_subjects) {
                let html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
                html += '<thead class="table-light"><tr><th>#</th><th>Subject Name</th><th>Subject Code</th><th>Status</th></tr></thead><tbody>';
                
                if (response.missed_subjects.length > 0) {
                    response.missed_subjects.forEach(function(subject, index) {
                        html += `
                            <tr class="bg-danger text-white">
                                <td>${index + 1}</td>
                                <td><strong>${subject.subject_name || 'N/A'}</strong></td>
                                <td>${subject.subject_code || 'N/A'}</td>
                                <td><span class="badge bg-danger">Not Taken</span></td>
                            </tr>
                        `;
                    });
                } else {
                    html += '<tr><td colspan="4" class="text-center text-muted">No missed subjects</td></tr>';
                }
                
                html += '</tbody></table></div>';
                $('#studentMissedSubjectsModalBody').html(html);
            } else {
                $('#studentMissedSubjectsModalBody').html('<div class="alert alert-info">No missed subjects found</div>');
            }
        })
        .fail(function() {
            $('#studentMissedSubjectsModalBody').html('<div class="alert alert-danger">Error loading missed subjects</div>');
        });
    }
</script>

<!-- Student Missed Subjects Modal -->
<div class="modal fade" id="studentMissedSubjectsModal" tabindex="-1" aria-labelledby="studentMissedSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="studentMissedSubjectsModalLabel">
                    <i class="bi bi-person"></i> Missed Subjects
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="studentMissedSubjectsModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@include('partials.attendance_sms_modal')

<!-- Status-filter Attendance SMS Modal -->
<div class="modal fade" id="attendanceStatusSmsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-chat-dots"></i> Send SMS, Filtered Attendance
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="attStatusSmsDisabled" class="alert alert-warning d-none"></div>
                <div id="attStatusSmsContent" class="d-none">
                    <div class="alert alert-info border-0 shadow-sm mb-3">
                        <div><strong>Status:</strong> <span id="attStatusSmsStatusLabel">-</span></div>
                        <div><strong>Period:</strong> <span id="attStatusSmsPeriodLabel">-</span></div>
                        <div class="small text-muted mt-1">Parents of students matching this filter. SMS lists each matching day.</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-bold mb-0"><i class="bi bi-chat-left-text"></i> SMS Sample</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="attStatusSmsResetTemplate">Reset</button>
                        </div>
                        <div class="small text-muted mb-2">
                            Unaweza kuhariri maandishi. <strong>Values zilizofungwa</strong> (jina, period, siku) haziwezi kuhaririwa, zinajaza automatic kwa kila mwanafunzi.
                        </div>
                        <div id="attStatusSmsEditor"
                             class="form-control"
                             contenteditable="true"
                             style="min-height: 140px; white-space: pre-wrap; line-height: 1.5; border-radius: 0; font-size: 0.92rem;"></div>
                        <style>
                            #attStatusSmsEditor .sms-locked {
                                display: inline;
                                background: #fff3cd;
                                border: 1px solid #f0d78c;
                                color: #7a5b00;
                                padding: 0 4px;
                                margin: 0 1px;
                                border-radius: 2px;
                                font-weight: 600;
                                cursor: default;
                                user-select: none;
                                white-space: pre-wrap;
                            }
                        </style>
                        <div id="attStatusSmsTemplateError" class="text-danger small mt-1 d-none"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="attStatusSmsSelectAll">
                            <label class="custom-control-label font-weight-bold" for="attStatusSmsSelectAll">Select All</label>
                        </div>
                        <span class="badge badge-success" id="attStatusSmsSelectedCount" style="font-size: 1rem;">Receivers: 0</span>
                    </div>
                    <div class="table-responsive" style="max-height: 320px;">
                        <table class="table table-sm table-hover">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Parent Of</th>
                                    <th>Days</th>
                                    <th>Phone</th>
                                    <th class="text-center" style="width: 120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="attStatusSmsList"></tbody>
                        </table>
                    </div>
                    <div id="attStatusSmsProgressArea" class="mt-4 d-none">
                        <hr>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="font-weight-bold text-primary-custom">Sending Progress</span>
                            <span id="attStatusSmsProgressText" class="small">0 / 0</span>
                        </div>
                        <div class="progress" style="height: 22px;">
                            <div id="attStatusSmsProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;">0%</div>
                        </div>
                        <div id="attStatusSmsDeliverySummary" class="text-center small mt-2"></div>
                    </div>
                </div>
                <div id="attStatusSmsLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small mb-0">Loading parents...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="attStatusSmsCancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="attStatusSmsStartBtn" disabled>
                    <i class="bi bi-send"></i> Start Sending SMS
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnOpenAttendanceSms');
    if (!btn) return;

    btn.addEventListener('click', function () {
        let subclassID = document.getElementById('smsSubclassPicker')?.value || '';
        if (!subclassID) {
            const coordFilter = document.getElementById('subclassFilter')?.value;
            if (coordFilter) subclassID = coordFilter;
        }
        if (!subclassID) {
            alert('Tafadhali chagua darasa (subclass) kwanza.');
            return;
        }

        let referenceDate = document.getElementById('date')?.value || null;
        const searchType = document.getElementById('search_type')?.value;
        if (!referenceDate && searchType === 'month') {
            const y = document.getElementById('year')?.value;
            const m = document.getElementById('month')?.value;
            if (y && m) referenceDate = y + '-' + m + '-01';
        } else if (!referenceDate && searchType === 'year') {
            const y = document.getElementById('year')?.value;
            if (y) referenceDate = y + '-01-01';
        } else if (!referenceDate && searchType === 'date_range') {
            referenceDate = document.getElementById('from_date')?.value || null;
        }

        if (typeof openAttendanceSmsModal === 'function') {
            openAttendanceSmsModal({ subclassID: subclassID, referenceDate: referenceDate });
        }
    });

    fetch('{{ route("attendance_sms.schedule_info") }}', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(info => {
            if (info && info.success && info.auto_enabled) {
                const hint = document.getElementById('attendanceSmsAutoHint');
                if (hint) hint.textContent = 'Auto SMS is on, you can still send manually here.';
            }
        })
        .catch(() => {});
});

(function ($) {
    if (typeof $ !== 'function') return;

    let attStatusSmsSending = false;
    let attStatusSmsFilter = null;
    let attStatusSmsSample = null;

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function lockedSpan(token, value) {
        return '<span class="sms-locked" contenteditable="false" data-token="' + escapeHtml(token) + '">' +
            escapeHtml(value).replace(/\n/g, '<br>') +
            '</span>';
    }

    function renderSmsSampleEditor(sample) {
        const editor = document.getElementById('attStatusSmsEditor');
        if (!editor || !sample) return;
        const tokens = sample.sample_tokens || {};
        let html = escapeHtml(sample.template || '');
        Object.keys(tokens).forEach(function (key) {
            const token = '{' + key + '}';
            html = html.split(escapeHtml(token)).join(lockedSpan(key, tokens[key]));
        });
        html = html.replace(/\n/g, '<br>');
        editor.innerHTML = html;
        $('#attStatusSmsTemplateError').addClass('d-none').text('');
    }

    function serializeSmsSampleTemplate() {
        const editor = document.getElementById('attStatusSmsEditor');
        if (!editor) return '';

        function walk(node) {
            let out = '';
            node.childNodes.forEach(function (child) {
                if (child.nodeType === Node.TEXT_NODE) {
                    out += child.nodeValue;
                    return;
                }
                if (child.nodeType !== Node.ELEMENT_NODE) return;
                const tag = child.tagName.toLowerCase();
                if (child.classList && child.classList.contains('sms-locked')) {
                    out += '{' + (child.getAttribute('data-token') || '') + '}';
                    return;
                }
                if (tag === 'br') {
                    out += '\n';
                    return;
                }
                if (tag === 'div' || tag === 'p') {
                    if (out && !out.endsWith('\n')) out += '\n';
                    out += walk(child);
                    if (!out.endsWith('\n')) out += '\n';
                    return;
                }
                out += walk(child);
            });
            return out;
        }

        return walk(editor).replace(/\n{3,}/g, '\n\n').trim();
    }

    function validateSmsSampleTemplate(template) {
        const required = (attStatusSmsSample && attStatusSmsSample.required_tokens)
            ? attStatusSmsSample.required_tokens
            : ['STUDENT_NAME', 'PERIOD', 'STATUS_LINE', 'DAY_LIST'];
        const missing = required.filter(function (t) {
            return template.indexOf('{' + t + '}') === -1;
        });
        if (missing.length) {
            const msg = 'Usifute values zilizofungwa: ' + missing.map(function (t) { return '{' + t + '}'; }).join(', ');
            $('#attStatusSmsTemplateError').removeClass('d-none').text(msg);
            return false;
        }
        $('#attStatusSmsTemplateError').addClass('d-none').text('');
        return true;
    }

    function attStatusSmsUpdateCount() {
        const count = $('#attStatusSmsList .att-status-sms-cb:checked:not(:disabled)').length;
        $('#attStatusSmsSelectedCount').text('Receivers: ' + count);
        $('#attStatusSmsStartBtn').prop('disabled', count === 0 || attStatusSmsSending);
    }

    function openStatusFilterSmsModal() {
        if (!currentAttendanceSearchParams) {
            Swal.fire({ icon: 'warning', title: 'Search first', text: 'Filter attendance first, then send SMS.', confirmButtonColor: '#940000' });
            return;
        }
        const status = currentAttendanceSearchParams.attendance_status;
        if (!status || status === 'all') {
            Swal.fire({ icon: 'info', title: 'Select status', text: 'Chagua Attendance Status (eg Absent) kisha search tena.', confirmButtonColor: '#940000' });
            return;
        }

        attStatusSmsFilter = { ...currentAttendanceSearchParams };
        attStatusSmsSample = null;
        $('#attStatusSmsDisabled').addClass('d-none').text('');
        $('#attStatusSmsContent').addClass('d-none');
        $('#attStatusSmsLoading').removeClass('d-none');
        $('#attStatusSmsProgressArea').addClass('d-none');
        $('#attStatusSmsStartBtn').prop('disabled', true);
        $('#attStatusSmsList').empty();
        $('#attStatusSmsEditor').empty();
        $('#attendanceStatusSmsModal').modal('show');

        $.get('{{ route("attendance_status_sms.recipients") }}', attStatusSmsFilter)
            .done(function (res) {
                $('#attStatusSmsLoading').addClass('d-none');
                if (!res.success) {
                    $('#attStatusSmsDisabled').removeClass('d-none').text(res.message || 'Unable to load recipients.');
                    return;
                }
                $('#attStatusSmsContent').removeClass('d-none');
                $('#attStatusSmsStatusLabel').text(res.status_label || status);
                $('#attStatusSmsPeriodLabel').text(res.period_label || '');

                attStatusSmsSample = res.sample || null;
                if (attStatusSmsSample) {
                    renderSmsSampleEditor(attStatusSmsSample);
                }

                const list = $('#attStatusSmsList');
                list.empty();
                (res.recipients || []).forEach(function (row) {
                    const phone = String(row.parentPhone || '').trim();
                    const hasPhone = phone && !['null', 'undefined', 'n/a'].includes(phone.toLowerCase());
                    const cbId = 'att_status_sms_' + row.studentID;
                    const disabled = !hasPhone;
                    let statusHtml = '<span class="text-muted small"><i class="bi bi-hourglass"></i> Pending</span>';
                    if (!hasPhone) {
                        statusHtml = '<span class="text-danger small"><i class="bi bi-telephone-x"></i> No Phone</span>';
                    }
                    list.append(`
                        <tr data-student-id="${row.studentID}">
                            <td class="text-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input att-status-sms-cb" id="${cbId}" ${disabled ? 'disabled' : ''}>
                                    <label class="custom-control-label" for="${cbId}">&nbsp;</label>
                                </div>
                            </td>
                            <td>Parent of ${row.studentName}</td>
                            <td><span class="badge bg-danger">${row.days_count || 0}</span></td>
                            <td>${hasPhone ? phone : '<span class="text-danger small">No Phone</span>'}</td>
                            <td class="text-center att-status-sms-col">${statusHtml}</td>
                        </tr>
                    `);
                });
                if (!(res.recipients || []).length) {
                    list.append('<tr><td colspan="5" class="text-center text-muted">No parents found for this filter.</td></tr>');
                }
                $('#attStatusSmsSelectAll').prop('checked', false);
                attStatusSmsUpdateCount();
            })
            .fail(function (xhr) {
                $('#attStatusSmsLoading').addClass('d-none');
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load recipients.';
                $('#attStatusSmsDisabled').removeClass('d-none').text(msg);
            });
    }

    $(document).on('click', '#btnSendStatusFilterSms', function () {
        openStatusFilterSmsModal();
    });

    $(document).on('click', '#attStatusSmsResetTemplate', function () {
        if (attStatusSmsSample) renderSmsSampleEditor(attStatusSmsSample);
    });

    $(document).on('keydown', '#attStatusSmsEditor', function (e) {
        if (e.key !== 'Backspace' && e.key !== 'Delete') return;
        const sel = window.getSelection();
        if (!sel || !sel.rangeCount) return;
        const range = sel.getRangeAt(0);
        const node = range.startContainer;
        const el = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
        if (el && el.closest && el.closest('.sms-locked')) {
            e.preventDefault();
            return;
        }
        if (e.key === 'Backspace' && range.collapsed) {
            let prev = null;
            if (node.nodeType === Node.TEXT_NODE && range.startOffset === 0) {
                prev = node.previousSibling;
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                prev = node.childNodes[range.startOffset - 1] || null;
            }
            if (prev && prev.classList && prev.classList.contains('sms-locked')) {
                e.preventDefault();
            }
        }
        if (e.key === 'Delete' && range.collapsed) {
            let next = null;
            if (node.nodeType === Node.TEXT_NODE && range.startOffset === (node.nodeValue || '').length) {
                next = node.nextSibling;
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                next = node.childNodes[range.startOffset] || null;
            }
            if (next && next.classList && next.classList.contains('sms-locked')) {
                e.preventDefault();
            }
        }
    });

    $(document).on('change', '#attStatusSmsSelectAll', function () {
        const checked = $(this).is(':checked');
        $('#attStatusSmsList .att-status-sms-cb:not(:disabled)').prop('checked', checked);
        attStatusSmsUpdateCount();
    });

    $(document).on('change', '.att-status-sms-cb', attStatusSmsUpdateCount);

    $(document).on('click', '#attStatusSmsStartBtn', async function () {
        const rows = $('#attStatusSmsList .att-status-sms-cb:checked').closest('tr');
        if (!rows.length || attStatusSmsSending || !attStatusSmsFilter) return;

        const messageTemplate = serializeSmsSampleTemplate();
        if (!validateSmsSampleTemplate(messageTemplate)) {
            Swal.fire({
                icon: 'warning',
                title: 'SMS sample incomplete',
                text: 'Usifute values zilizofungwa kwenye sample ya SMS.',
                confirmButtonColor: '#940000'
            });
            return;
        }

        const confirmed = typeof Swal !== 'undefined'
            ? await Swal.fire({
                title: 'Send SMS?',
                text: 'Send to ' + rows.length + ' parent(s)?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, Send',
            })
            : { isConfirmed: confirm('Send SMS to ' + rows.length + ' parent(s)?') };
        if (!confirmed.isConfirmed) return;

        attStatusSmsSending = true;
        $('#attStatusSmsStartBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');
        $('#attStatusSmsCancelBtn').prop('disabled', true);
        $('.att-status-sms-cb, #attStatusSmsSelectAll').prop('disabled', true);
        $('#attStatusSmsEditor').attr('contenteditable', 'false');
        $('#attStatusSmsProgressArea').removeClass('d-none');

        const total = rows.length;
        let delivered = 0;
        let failed = 0;

        for (let i = 0; i < total; i++) {
            const row = $(rows[i]);
            const statusCol = row.find('.att-status-sms-col');
            statusCol.html('<span class="spinner-border spinner-border-sm text-primary"></span> Sending...');

            const payload = Object.assign({}, attStatusSmsFilter, {
                studentID: row.data('student-id'),
                message_template: messageTemplate,
                _token: $('meta[name="csrf-token"]').attr('content'),
            });

            try {
                const response = await $.ajax({
                    url: '{{ route("attendance_status_sms.send") }}',
                    type: 'POST',
                    data: payload,
                });
                if (response.success) {
                    statusCol.html('<span class="text-success small font-weight-bold"><i class="bi bi-check-circle-fill"></i> Delivered</span>');
                    delivered++;
                } else {
                    statusCol.html('<span class="text-danger small"><i class="bi bi-x-circle"></i> ' + (response.error || 'Failed') + '</span>');
                    failed++;
                }
            } catch (err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Error';
                statusCol.html('<span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> ' + msg + '</span>');
                failed++;
            }

            const done = delivered + failed;
            const pct = Math.round((done / total) * 100);
            $('#attStatusSmsProgressBar').css('width', pct + '%').text(pct + '%');
            $('#attStatusSmsProgressText').text(done + ' / ' + total);
            $('#attStatusSmsDeliverySummary').html('<span class="text-success">' + delivered + ' Delivered</span> | <span class="text-danger">' + failed + ' Failed</span>');
        }

        attStatusSmsSending = false;
        $('#attStatusSmsStartBtn').html('<i class="bi bi-send"></i> Start Sending SMS');
        $('#attStatusSmsCancelBtn').prop('disabled', false);
        $('#attStatusSmsEditor').attr('contenteditable', 'true');
        $('#attStatusSmsList .att-status-sms-cb').each(function () {
            const r = $(this).closest('tr');
            if (r.find('.bi-check-circle-fill').length) {
                $(this).prop('checked', false).prop('disabled', true);
            } else if (!r.find('.bi-telephone-x').length) {
                $(this).prop('disabled', false);
            }
        });
        attStatusSmsUpdateCount();
    });
})(window.jQuery);
</script>

@include('includes.footer')
