@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.create_scheme').' - ShuleXpert')
@section('page_title', __('teacher.pages.create_scheme'))
@section('page_breadcrumb', __('teacher.pages.create_scheme_bc'))

@section('page_actions')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn btn-primary btn-sm waves-effect text-white" onclick="saveScheme()">
            <i class="fa-solid fa-floppy-disk me-1 text-white"></i> <span class="text-white" data-translate="saveChanges">{{ __('teacher.save_changes') }}</span>
        </button>
        <a href="{{ route('teacher.schemeOfWork') }}" class="btn btn-outline-primary btn-sm waves-effect">
            <i class="fa-solid fa-arrow-left me-1"></i> <span data-translate="goBack">{{ __('teacher.scheme.go_back') }}</span>
        </a>
    </div>
@endsection

@push('styles')
<style>
        .sx-create-scheme .btn-primary-custom,
        .sx-create-scheme .btn-primary-custom:hover,
        .sx-create-scheme .btn-primary-custom:focus,
        .sx-create-scheme .btn-primary-custom:disabled,
        .btn.btn-primary.btn-sm.waves-effect {
            background-color: #940000 !important;
            border-color: #940000 !important;
            color: #fff !important;
            font-weight: 800;
            border-radius: 0 !important;
        }
        .sx-create-scheme .btn-primary-custom:hover,
        .btn.btn-primary.btn-sm.waves-effect:hover {
            background-color: #7b0000 !important;
            border-color: #7b0000 !important;
            color: #fff !important;
        }
        /* Force white label/icon on maroon buttons (layout may force black text) */
        .sx-create-scheme .btn-primary-custom,
        .sx-create-scheme .btn-primary-custom *,
        .sx-create-scheme .btn-primary-custom span,
        .sx-create-scheme .btn-primary-custom i,
        .btn.btn-primary,
        .btn.btn-primary *,
        .btn.btn-primary span,
        .btn.btn-primary i,
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-create-scheme .btn-primary-custom,
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-create-scheme .btn-primary-custom *,
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .btn.btn-primary,
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .btn.btn-primary * {
            color: #fff !important;
            -webkit-text-fill-color: #fff !important;
        }
        .sx-create-scheme .btn,
        .sx-create-scheme .form-control,
        .sx-create-scheme .form-select {
            border-radius: 0 !important;
        }
        .sx-create-scheme .sx-scheme-meta,
        .sx-create-scheme .objectives-section,
        .sx-create-scheme .sx-scheme-table-wrap {
            background: #fff !important;
            border: 1px solid #e6e6e6 !important;
            border-radius: 0 !important;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06) !important;
            border-top: 4px solid #940000 !important;
        }
        .sx-create-scheme .sx-scheme-meta {
            padding: 0.9rem 1.1rem;
            margin-bottom: 0.85rem;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .sx-create-scheme .sx-scheme-meta strong { color: #940000 !important; }
        .sx-create-scheme .sx-panel-title {
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #940000 !important;
            margin: 0;
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
        
        .scheme-table input,
        .scheme-table textarea {
            width: 100%;
            border: none;
            padding: 5px;
            font-size: 0.85rem;
        }
        
        .scheme-table textarea {
            resize: vertical;
            min-height: 40px;
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
        
        .holiday-row input,
        .holiday-row textarea {
            pointer-events: none;
            background-color: #f5f5f5;
            opacity: 0.6;
        }
        
        .row-number {
            width: 40px;
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .add-row-btn {
            margin: 10px 0;
        }
        
        .objectives-section {
            margin-bottom: 0.85rem;
            padding: 1rem 1.1rem;
        }
        .sx-create-scheme .sx-scheme-table-wrap {
            padding: 1rem 1.1rem 1.15rem;
        }
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-create-scheme,
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-create-scheme p,
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-create-scheme td,
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-create-scheme label,
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-create-scheme input,
        html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-create-scheme textarea {
            color: #000 !important;
        }
        
        .objective-item {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }
        
        .objective-item input {
            flex: 1;
            margin-right: 10px;
        }
        
        
        .remove-row-btn {
            background-color: #dc3545;
            color: #ffffff;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        
        .remove-row-btn:hover {
            background-color: #c82333;
            color: #ffffff;
        }
        
        .remove-objective-btn {
            background-color: #dc3545;
            color: #ffffff;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        
        .remove-objective-btn:hover {
            background-color: #c82333;
            color: #ffffff;
        }
        
        .scheme-table th:last-child,
        .scheme-table td:last-child {
            text-align: center;
        }

        /* Dark mode */
        html[data-sx-theme="dark"] .sx-create-scheme .sx-scheme-meta,
        html[data-bs-theme="dark"] .sx-create-scheme .sx-scheme-meta,
        html[data-sx-theme="dark"] .sx-create-scheme .objectives-section,
        html[data-bs-theme="dark"] .sx-create-scheme .objectives-section,
        html[data-sx-theme="dark"] .sx-create-scheme .sx-scheme-table-wrap,
        html[data-bs-theme="dark"] .sx-create-scheme .sx-scheme-table-wrap {
            background: #24181c !important;
            border-color: #3a2a2e !important;
            box-shadow: none !important;
            color: #f3e9eb !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .sx-scheme-meta strong,
        html[data-bs-theme="dark"] .sx-create-scheme .sx-scheme-meta strong,
        html[data-sx-theme="dark"] .sx-create-scheme .sx-panel-title,
        html[data-bs-theme="dark"] .sx-create-scheme .sx-panel-title,
        html[data-sx-theme="dark"] .sx-create-scheme .sx-panel-title i,
        html[data-bs-theme="dark"] .sx-create-scheme .sx-panel-title i {
            color: #ffb4b4 !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .form-control,
        html[data-bs-theme="dark"] .sx-create-scheme .form-control,
        html[data-sx-theme="dark"] .sx-create-scheme .form-select,
        html[data-bs-theme="dark"] .sx-create-scheme .form-select,
        html[data-sx-theme="dark"] .sx-create-scheme .objective-item input,
        html[data-bs-theme="dark"] .sx-create-scheme .objective-item input,
        html[data-sx-theme="dark"] .sx-create-scheme .scheme-table input,
        html[data-bs-theme="dark"] .sx-create-scheme .scheme-table input,
        html[data-sx-theme="dark"] .sx-create-scheme .scheme-table textarea,
        html[data-bs-theme="dark"] .sx-create-scheme .scheme-table textarea {
            background: #1c1417 !important;
            border-color: #4a383c !important;
            color: #f3e9eb !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .scheme-table,
        html[data-bs-theme="dark"] .sx-create-scheme .scheme-table {
            color: #f3e9eb !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .scheme-table th,
        html[data-bs-theme="dark"] .sx-create-scheme .scheme-table th {
            background-color: #940000 !important;
            color: #fff !important;
            border-color: #6e0000 !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .scheme-table td,
        html[data-bs-theme="dark"] .sx-create-scheme .scheme-table td {
            background-color: #1c1417 !important;
            border-color: #3a2a2e !important;
            color: #f3e9eb !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .row-number,
        html[data-bs-theme="dark"] .sx-create-scheme .row-number,
        html[data-sx-theme="dark"] .sx-create-scheme .month-cell,
        html[data-bs-theme="dark"] .sx-create-scheme .month-cell {
            background-color: #2a1c20 !important;
            color: #ffb4b4 !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .holiday-row,
        html[data-bs-theme="dark"] .sx-create-scheme .holiday-row {
            background-color: #3a1a1a !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .holiday-cell,
        html[data-bs-theme="dark"] .sx-create-scheme .holiday-cell {
            background-color: #4a2020 !important;
            color: #ffb4b4 !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .holiday-row input,
        html[data-bs-theme="dark"] .sx-create-scheme .holiday-row input,
        html[data-sx-theme="dark"] .sx-create-scheme .holiday-row textarea,
        html[data-bs-theme="dark"] .sx-create-scheme .holiday-row textarea {
            background-color: #2a2226 !important;
            color: #a8989c !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .scheme-table td[style*="background-color: #f9f9f9"],
        html[data-bs-theme="dark"] .sx-create-scheme .scheme-table td[style*="background-color: #f9f9f9"],
        html[data-sx-theme="dark"] .sx-create-scheme .add-row-control td,
        html[data-bs-theme="dark"] .sx-create-scheme .add-row-control td {
            background-color: #2a1c20 !important;
            color: #e8c9cd !important;
        }
        html[data-sx-theme="dark"] .sx-create-scheme .btn-outline-primary,
        html[data-bs-theme="dark"] .sx-create-scheme .btn-outline-primary {
            color: #ffb4b4 !important;
            border-color: #940000 !important;
        }
        html[data-sx-theme="dark"] .sx-page-actions .form-select,
        html[data-bs-theme="dark"] .sx-page-actions .form-select,
        html[data-sx-theme="dark"] .page-actions .form-select,
        html[data-bs-theme="dark"] .page-actions .form-select,
        html[data-sx-theme="dark"] #languageSelector,
        html[data-bs-theme="dark"] #languageSelector {
            background: #1c1417 !important;
            border-color: #4a383c !important;
            color: #f3e9eb !important;
        }
        html[data-sx-theme="dark"] .btn.btn-outline-primary,
        html[data-bs-theme="dark"] .btn.btn-outline-primary {
            color: #ffb4b4 !important;
            border-color: #940000 !important;
        }
</style>
@endpush

@section('content')
<div class="sx-create-scheme">
    <div class="sx-scheme-meta">
        <span data-translate="createSchemeOfWork">{{ __('teacher.scheme.create_scheme_of_work') }}</span>
        · {{ __('teacher.subject') }}: <strong>{{ $classSubject->subject->subject_name ?? 'N/A' }}</strong>
        @if($classSubject->subclass && $classSubject->subclass->class)
            · {{ __('teacher.class') }}: <strong>{{ $classSubject->subclass->class->class_name }} {{ $classSubject->subclass->subclass_name }}</strong>
        @elseif($classSubject->class)
            · {{ __('teacher.class') }}: <strong>{{ $classSubject->class->class_name }}</strong>
        @endif
        · {{ __('teacher.academic_year') }}: <strong>{{ $currentYear }}</strong>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('teacher.close') }}"></button>
        </div>
    @endif

    <div class="objectives-section">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="sx-panel-title mb-0">
                <i class="fa-solid fa-list"></i> <span data-translate="defineLearningObjectives">{{ __('teacher.scheme.define_objectives') }}</span>
            </h5>
            <button type="button" class="btn btn-primary-custom btn-sm text-white" onclick="addObjective()">
                <i class="fa-solid fa-plus text-white"></i> <span class="text-white" data-translate="addObjective">{{ __('teacher.scheme.add_objective') }}</span>
            </button>
        </div>
        <div id="objectivesContainer"></div>
    </div>

    <div class="sx-scheme-table-wrap">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="sx-panel-title mb-0">
                <i class="fa-solid fa-table"></i> <span data-translate="schemeOfWorkTable">{{ __('teacher.scheme.table_title') }}</span>
            </h5>
            <button type="button" class="btn btn-primary-custom btn-sm text-white" onclick="saveScheme()">
                <i class="fa-solid fa-floppy-disk text-white"></i> <span class="text-white" data-translate="saveChanges">{{ __('teacher.save_changes') }}</span>
            </button>
        </div>
        <div class="table-responsive">
            <table class="scheme-table" id="schemeTable">
                <thead>
                    <tr>
                        <th class="row-number">#</th>
                        <th data-translate="mainCompetence">{{ __('teacher.scheme.main_competence') }}</th>
                        <th data-translate="specificCompetences">{{ __('teacher.scheme.specific_competences') }}</th>
                        <th data-translate="learningActivities">{{ __('teacher.scheme.learning_activities') }}</th>
                        <th data-translate="specificActivities">{{ __('teacher.scheme.specific_activities') }}</th>
                        <th class="month-header" data-translate="month">{{ __('teacher.scheme.month') }}</th>
                        <th data-translate="week">{{ __('teacher.scheme.week') }}</th>
                        <th data-translate="numberOfPeriods">{{ __('teacher.scheme.number_of_periods') }}</th>
                        <th data-translate="teachingMethods">{{ __('teacher.scheme.teaching_methods') }}</th>
                        <th data-translate="teachingResources">{{ __('teacher.scheme.teaching_resources') }}</th>
                        <th data-translate="assessmentTools">{{ __('teacher.scheme.assessment_tools') }}</th>
                        <th data-translate="references">{{ __('teacher.scheme.references') }}</th>
                        <th style="width: 60px;" data-translate="action">{{ __('teacher.scheme.action') }}</th>
                    </tr>
                </thead>
                <tbody id="schemeTableBody"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
        const holidays = @json($holidays ?? []);
        const nonWorkingEvents = @json($nonWorkingEvents ?? []);
        const allHolidays = @json($allHolidays ?? []);
        const classSubjectID = {{ $classSubject->class_subjectID }};
        const year = {{ $currentYear }};
        const i18nScheme = @json(__('teacher.scheme'));
        
        let objectiveCount = 0;
        let rowCount = 0;
        let monthRows = {}; // Track rows per month
        
        // Initialize table with months
        $(document).ready(function() {
            initializeTable();
        });
        
        function initializeTable() {
            const tbody = $('#schemeTableBody');
            tbody.empty();
            monthRows = {};
            rowCount = 0;
            
            months.forEach((month, monthIndex) => {
                // Check for holidays in this month
                const monthHolidays = getHolidaysForMonth(monthIndex + 1);
                
                // Add holiday rows first if they exist
                if (monthHolidays.length > 0) {
                    const holidayRows = createHolidayRow(month, monthHolidays);
                    tbody.append(holidayRows);
                }
                
                // Add first data row for this month (WITH month cell and rowspan=1)
                const lastHolidayRow = $(`.holiday-row[data-holiday-month="${month}"]`).last();
                if (lastHolidayRow.length > 0) {
                    addFirstRowForMonth(month, lastHolidayRow);
                } else {
                    addFirstRowForMonth(month);
                }
            });
        }
        
        // Add first row for month (with month cell)
        function addFirstRowForMonth(month, insertAfterRow = null) {
            if (!monthRows[month]) {
                monthRows[month] = 0;
            }
            monthRows[month]++;
            rowCount++;
            
            const rowOrder = monthRows[month] - 1;
            
            // First row has month cell with rowspan=1 (will be updated when more rows are added)
            const row = $(`
                <tr data-month="${month}" data-row-order="${rowOrder}" data-row-id="${rowCount}">
                    <td class="row-number">${rowCount}</td>
                    <td><input type="text" name="items[${rowCount}][main_competence]" class="form-control" /></td>
                    <td><textarea name="items[${rowCount}][specific_competences]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][learning_activities]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][specific_activities]" class="form-control" rows="2"></textarea></td>
                    <td class="month-cell" rowspan="1">${month}</td>
                    <td><input type="text" name="items[${rowCount}][week]" class="form-control" placeholder="${i18nScheme.week_ph}" /></td>
                    <td><input type="number" name="items[${rowCount}][number_of_periods]" class="form-control" min="1" /></td>
                    <td><textarea name="items[${rowCount}][teaching_methods]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][teaching_resources]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][assessment_tools]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][references]" class="form-control" rows="2"></textarea></td>
                    <td>
                        <button type="button" class="btn btn-sm remove-row-btn" onclick="removeRow(${rowCount}, '${month}')" title="${i18nScheme.remove_row}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
            
            // Add hidden fields
            row.append(
                `<input type="hidden" name="items[${rowCount}][month]" value="${month}" />` +
                `<input type="hidden" name="items[${rowCount}][row_order]" value="${rowOrder}" />`
            );
            
            // Insert row
            if (insertAfterRow && insertAfterRow.length > 0) {
                insertAfterRow.after(row);
            } else {
                // Find position based on month order
                let insertAfter = null;
                const monthIndex = months.indexOf(month);
                for (let i = monthIndex - 1; i >= 0; i--) {
                    const prevMonthRows = $(`tr[data-month="${months[i]}"]:not(.holiday-row)`);
                    if (prevMonthRows.length > 0) {
                        insertAfter = prevMonthRows.last();
                        break;
                    }
                    const prevMonthHoliday = $(`.holiday-row[data-holiday-month="${months[i]}"]`);
                    if (prevMonthHoliday.length > 0) {
                        insertAfter = prevMonthHoliday;
                        break;
                    }
                }
                if (insertAfter && insertAfter.length > 0) {
                    insertAfter.after(row);
                } else {
                    $('#schemeTableBody').append(row);
                }
            }
            
            // Update row numbers
            updateRowNumbers();
        }
        
        function getHolidaysForMonth(monthIndex) {
            const monthName = months[monthIndex - 1];
            const monthHolidays = [];
            
            // Check all holidays (including non-working events)
            allHolidays.forEach(holiday => {
                const startDate = new Date(holiday.start_date);
                const endDate = new Date(holiday.end_date);
                const startMonth = startDate.getMonth() + 1;
                const endMonth = endDate.getMonth() + 1;
                
                // Check if holiday falls in this month
                if (startMonth === monthIndex || endMonth === monthIndex || 
                    (startMonth < monthIndex && endMonth > monthIndex)) {
                    monthHolidays.push(holiday);
                }
            });
            
            return monthHolidays;
        }
        
        function createHolidayRow(month, monthHolidays) {
            let holidayRows = '';
            
            monthHolidays.forEach(holiday => {
                const startDate = new Date(holiday.start_date);
                const endDate = new Date(holiday.end_date);
                const startDateStr = formatDate(holiday.start_date);
                const endDateStr = formatDate(holiday.end_date);
                
                let dateRange = startDateStr;
                if (startDateStr !== endDateStr) {
                    dateRange = `${startDateStr} - ${endDateStr}`;
                }
                
                holidayRows += `
                    <tr class="holiday-row" data-holiday-month="${month}" data-holiday-start="${holiday.start_date}" data-holiday-end="${holiday.end_date}">
                        <td class="row-number"></td>
                        <td class="holiday-cell" colspan="11">
                            <strong>${holiday.name}</strong> - ${dateRange}
                            <input type="hidden" name="holiday_${holiday.start_date}_${holiday.end_date}" value="1" />
                        </td>
                        <td></td>
                    </tr>
                `;
            });
            
            return holidayRows;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            const day = date.getDate();
            const month = date.getMonth() + 1;
            return `${day}.${month}`;
        }
        
        function addRowForMonth(month, insertAfterRow = null) {
            if (!monthRows[month]) {
                monthRows[month] = 0;
            }
            monthRows[month]++;
            rowCount++;
            
            const rowOrder = monthRows[month] - 1;
            
            // Count existing rows for this month to calculate rowspan (ONLY for month column)
            const existingRows = $(`tr[data-month="${month}"]:not(.holiday-row)`);
            const newRowspan = existingRows.length + 1;
            
            // Update ONLY the first row's month cell rowspan (month column only, NOT week)
            const firstRow = existingRows.first();
            if (firstRow.length > 0) {
                const monthCell = firstRow.find('.month-cell');
                if (monthCell.length > 0) {
                    monthCell.attr('rowspan', newRowspan);
                }
            }
            
            // Create new row WITHOUT month cell (since it's already in first row with rowspan)
            const row = $(`
                <tr data-month="${month}" data-row-order="${rowOrder}" data-row-id="${rowCount}">
                    <td class="row-number">${rowCount}</td>
                    <td><input type="text" name="items[${rowCount}][main_competence]" class="form-control" /></td>
                    <td><textarea name="items[${rowCount}][specific_competences]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][learning_activities]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][specific_activities]" class="form-control" rows="2"></textarea></td>
                    <td><input type="text" name="items[${rowCount}][week]" class="form-control" placeholder="${i18nScheme.week_ph}" /></td>
                    <td><input type="number" name="items[${rowCount}][number_of_periods]" class="form-control" min="1" /></td>
                    <td><textarea name="items[${rowCount}][teaching_methods]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][teaching_resources]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][assessment_tools]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][references]" class="form-control" rows="2"></textarea></td>
                    <td>
                        <button type="button" class="btn btn-sm remove-row-btn" onclick="removeRow(${rowCount}, '${month}')" title="${i18nScheme.remove_row}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
            
            // Add hidden fields
            row.append(
                `<input type="hidden" name="items[${rowCount}][month]" value="${month}" />` +
                `<input type="hidden" name="items[${rowCount}][row_order]" value="${rowOrder}" />`
            );
            
            // Insert row
            if (insertAfterRow && insertAfterRow.length > 0) {
                insertAfterRow.after(row);
            } else {
                // Find where to insert
                const lastMonthRow = $(`tr[data-month="${month}"]:not(.holiday-row)`).last();
                if (lastMonthRow.length > 0) {
                    lastMonthRow.after(row);
                } else {
                    // Find position based on month order
                    let insertAfter = null;
                    const monthIndex = months.indexOf(month);
                    for (let i = monthIndex - 1; i >= 0; i--) {
                        const prevMonthRows = $(`tr[data-month="${months[i]}"]:not(.holiday-row)`);
                        if (prevMonthRows.length > 0) {
                            insertAfter = prevMonthRows.last();
                            break;
                        }
                        // Check for holiday rows
                        const prevMonthHoliday = $(`.holiday-row[data-holiday-month="${months[i]}"]`);
                        if (prevMonthHoliday.length > 0) {
                            insertAfter = prevMonthHoliday;
                            break;
                        }
                    }
                    if (insertAfter && insertAfter.length > 0) {
                        insertAfter.after(row);
                    } else {
                        $('#schemeTableBody').append(row);
                    }
                }
            }
            
            // Update row numbers
            updateRowNumbers();
        }
        
        function updateRowNumbers() {
            let counter = 1;
            $('#schemeTableBody tr[data-month]:not(.holiday-row)').each(function() {
                $(this).find('.row-number').text(counter);
                counter++;
            });
        }
        
        function addObjective() {
            objectiveCount++;
            const objectiveHtml = `
                <div class="objective-item" data-objective-id="${objectiveCount}">
                    <input type="text" name="learning_objectives[]" class="form-control" placeholder="${i18nScheme.objective_ph}" />
                    <button type="button" class="btn btn-sm remove-objective-btn" onclick="removeObjective(${objectiveCount})">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            `;
            $('#objectivesContainer').append(objectiveHtml);
        }
        
        function removeObjective(id) {
            $(`.objective-item[data-objective-id="${id}"]`).remove();
        }
        
        function saveScheme() {
            const formData = {
                class_subjectID: classSubjectID,
                year: year,
                learning_objectives: [],
                items: [],
                _token: '{{ csrf_token() }}'
            };
            
            // Collect learning objectives
            $('input[name="learning_objectives[]"]').each(function() {
                const value = $(this).val().trim();
                if (value) {
                    formData.learning_objectives.push(value);
                }
            });
            
            // Collect table items (skip holiday rows)
            $('tr[data-month]:not(.holiday-row)').each(function() {
                const month = $(this).data('month');
                const rowOrder = $(this).data('row-order');
                const rowId = $(this).data('row-id');
                
                if (!rowId || !month) return; // Skip if no row ID or month
                
                const item = {
                    month: month,
                    row_order: rowOrder,
                    main_competence: $(this).find('input[name*="[main_competence]"]').val() || null,
                    specific_competences: $(this).find('textarea[name*="[specific_competences]"]').val() || null,
                    learning_activities: $(this).find('textarea[name*="[learning_activities]"]').val() || null,
                    specific_activities: $(this).find('textarea[name*="[specific_activities]"]').val() || null,
                    week: $(this).find('input[name*="[week]"]').val() || null,
                    number_of_periods: $(this).find('input[name*="[number_of_periods]"]').val() || null,
                    teaching_methods: $(this).find('textarea[name*="[teaching_methods]"]').val() || null,
                    teaching_resources: $(this).find('textarea[name*="[teaching_resources]"]').val() || null,
                    assessment_tools: $(this).find('textarea[name*="[assessment_tools]"]').val() || null,
                    references: $(this).find('textarea[name*="[references]"]').val() || null
                };
                
                // Include item if at least one field is filled
                if (item.main_competence || item.specific_competences || item.learning_activities || 
                    item.specific_activities || item.week || item.number_of_periods) {
                    formData.items.push(item);
                }
            });
            
            // Validate
            if (formData.items.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: i18nScheme.warning,
                    text: i18nScheme.need_one_row,
                    confirmButtonColor: '#940000',
                    confirmButtonText: i18nScheme.ok
                });
                return;
            }
            
            // Show loading
            const saveBtn = $('button[onclick="saveScheme()"]');
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <span data-translate="saving">' + i18nScheme.saving + '</span>');
            
            // Submit
            $.ajax({
                url: '{{ route("teacher.storeSchemeOfWork") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: i18nScheme.success,
                            text: i18nScheme.created_ok,
                            confirmButtonColor: '#940000',
                            confirmButtonText: i18nScheme.ok
                        }).then(() => {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.href = '{{ route("teacher.schemeOfWork") }}';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: i18nScheme.error,
                            text: response.message || i18nScheme.fail_save,
                            confirmButtonColor: '#940000',
                            confirmButtonText: i18nScheme.ok
                        });
                        saveBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || i18nScheme.fail_save;
                    Swal.fire({
                        icon: 'error',
                        title: i18nScheme.error,
                        text: errorMsg,
                        confirmButtonColor: '#940000',
                        confirmButtonText: i18nScheme.ok
                    });
                    saveBtn.prop('disabled', false).html(originalText);
                }
            });
        }
        
        // Add row button functionality - will be added per month section
        function addRowToMonth(month) {
            const lastRow = $(`tr[data-month="${month}"]:not(.holiday-row)`).last();
            addRowForMonth(month, lastRow);
            // Refresh add buttons after adding row
            setTimeout(refreshAddButtons, 200);
        }
        
        // Remove row functionality
        function removeRow(rowId, month) {
            const row = $(`tr[data-row-id="${rowId}"]`);
            if (row.length === 0) return;
            
            // Check if this is the last row for this month
            const monthRows = $(`tr[data-month="${month}"]:not(.holiday-row)`);
            if (monthRows.length <= 1) {
                Swal.fire({
                    icon: 'warning',
                    title: i18nScheme.warning,
                    text: i18nScheme.cannot_remove_last,
                    confirmButtonColor: '#940000',
                    confirmButtonText: i18nScheme.ok
                });
                return;
            }
            
            // Remove the row
            row.remove();
            
            // Update rowspan for remaining rows in the same month (ONLY first row has month cell)
            const remainingRows = $(`tr[data-month="${month}"]:not(.holiday-row)`);
            if (remainingRows.length > 0) {
                const newRowspan = remainingRows.length;
                const firstRow = remainingRows.first();
                const monthCell = firstRow.find('.month-cell');
                if (monthCell.length > 0) {
                    monthCell.attr('rowspan', newRowspan);
                }
            }
            
            // Update row numbers
            updateRowNumbers();
            
            // Refresh add buttons
            setTimeout(refreshAddButtons, 100);
        }
        
        // Add month sections with add row buttons
        function addMonthSectionControls() {
            months.forEach(month => {
                const monthDataRows = $(`tr[data-month="${month}"]:not(.holiday-row)`);
                if (monthDataRows.length > 0) {
                    const lastRow = monthDataRows.last();
                    // Check if add button already exists
                    if (lastRow.next('.add-row-control').length === 0) {
                        const addLabel = (i18nScheme.add_row_to || 'Add Row to :month').replace(':month', month);
                        const addBtnRow = $(`
                            <tr class="add-row-control" data-control-month="${month}">
                                <td colspan="12" style="padding: 10px; background-color: #f9f9f9; text-align: center;">
                                    <button type="button" class="btn btn-sm btn-primary-custom text-white" onclick="addRowToMonth('${month}')">
                                        <i class="fa fa-plus text-white"></i> <span class="text-white">${addLabel}</span>
                                    </button>
                                </td>
                            </tr>
                        `);
                        lastRow.after(addBtnRow);
                    } else {
                        // Move button to after last row
                        const existingBtn = lastRow.next('.add-row-control');
                        if (existingBtn.length > 0 && existingBtn.data('control-month') === month) {
                            // Button is in correct position
                        } else {
                            existingBtn.remove();
                            const addLabel = (i18nScheme.add_row_to || 'Add Row to :month').replace(':month', month);
                            const addBtnRow = $(`
                                <tr class="add-row-control" data-control-month="${month}">
                                    <td colspan="12" style="padding: 10px; background-color: #f9f9f9; text-align: center;">
                                        <button type="button" class="btn btn-sm btn-primary-custom text-white" onclick="addRowToMonth('${month}')">
                                            <i class="fa fa-plus text-white"></i> <span class="text-white">${addLabel}</span>
                                        </button>
                                    </td>
                                </tr>
                            `);
                            lastRow.after(addBtnRow);
                        }
                    }
                }
            });
        }
        
        // Re-initialize add buttons after adding rows
        function refreshAddButtons() {
            $('.add-row-control').remove();
            setTimeout(addMonthSectionControls, 100);
        }
        
        // Initialize add buttons after table is ready
        setTimeout(addMonthSectionControls, 500);
        
        // Language translations (synced with lang/{locale}/teacher.php scheme keys)
        const translations = {
            en: {
                createSchemeOfWork: @json(__('teacher.scheme.create_scheme_of_work', [], 'en')),
                goBack: @json(__('teacher.scheme.go_back', [], 'en')),
                defineLearningObjectives: @json(__('teacher.scheme.define_objectives', [], 'en')),
                addObjective: @json(__('teacher.scheme.add_objective', [], 'en')),
                schemeOfWorkTable: @json(__('teacher.scheme.table_title', [], 'en')),
                saveChanges: @json(__('teacher.save_changes', [], 'en')),
                mainCompetence: @json(__('teacher.scheme.main_competence', [], 'en')),
                specificCompetences: @json(__('teacher.scheme.specific_competences', [], 'en')),
                learningActivities: @json(__('teacher.scheme.learning_activities', [], 'en')),
                specificActivities: @json(__('teacher.scheme.specific_activities', [], 'en')),
                month: @json(__('teacher.scheme.month', [], 'en')),
                week: @json(__('teacher.scheme.week', [], 'en')),
                numberOfPeriods: @json(__('teacher.scheme.number_of_periods', [], 'en')),
                teachingMethods: @json(__('teacher.scheme.teaching_methods', [], 'en')),
                teachingResources: @json(__('teacher.scheme.teaching_resources', [], 'en')),
                assessmentTools: @json(__('teacher.scheme.assessment_tools', [], 'en')),
                references: @json(__('teacher.scheme.references', [], 'en')),
                addRow: @json(__('teacher.scheme.add_row', [], 'en')),
                remove: @json(__('teacher.scheme.remove', [], 'en')),
                action: @json(__('teacher.scheme.action', [], 'en')),
                saving: @json(__('teacher.scheme.saving', [], 'en'))
            },
            sw: {
                createSchemeOfWork: @json(__('teacher.scheme.create_scheme_of_work', [], 'sw')),
                goBack: @json(__('teacher.scheme.go_back', [], 'sw')),
                defineLearningObjectives: @json(__('teacher.scheme.define_objectives', [], 'sw')),
                addObjective: @json(__('teacher.scheme.add_objective', [], 'sw')),
                schemeOfWorkTable: @json(__('teacher.scheme.table_title', [], 'sw')),
                saveChanges: @json(__('teacher.save_changes', [], 'sw')),
                mainCompetence: @json(__('teacher.scheme.main_competence', [], 'sw')),
                specificCompetences: @json(__('teacher.scheme.specific_competences', [], 'sw')),
                learningActivities: @json(__('teacher.scheme.learning_activities', [], 'sw')),
                specificActivities: @json(__('teacher.scheme.specific_activities', [], 'sw')),
                month: @json(__('teacher.scheme.month', [], 'sw')),
                week: @json(__('teacher.scheme.week', [], 'sw')),
                numberOfPeriods: @json(__('teacher.scheme.number_of_periods', [], 'sw')),
                teachingMethods: @json(__('teacher.scheme.teaching_methods', [], 'sw')),
                teachingResources: @json(__('teacher.scheme.teaching_resources', [], 'sw')),
                assessmentTools: @json(__('teacher.scheme.assessment_tools', [], 'sw')),
                references: @json(__('teacher.scheme.references', [], 'sw')),
                addRow: @json(__('teacher.scheme.add_row', [], 'sw')),
                remove: @json(__('teacher.scheme.remove', [], 'sw')),
                action: @json(__('teacher.scheme.action', [], 'sw')),
                saving: @json(__('teacher.scheme.saving', [], 'sw'))
            }
        };
        
        // Sync page-local data-translate labels with app locale (header SW switch)
        const appLocale = @json(app()->getLocale() === 'sw' ? 'sw' : 'en');
        let currentLanguage = appLocale;

        function getCurrentLanguage() {
            return currentLanguage || appLocale || 'en';
        }
        
        // Get translation
        function t(key) {
            const lang = getCurrentLanguage();
            return translations[lang][key] || translations.en[key] || key;
        }
        
        // Change language function
        function changeLanguage(lang) {
            currentLanguage = (lang === 'sw') ? 'sw' : 'en';
            
            // Update all static text on the page
            document.querySelectorAll('[data-translate]').forEach(function(element) {
                const key = element.getAttribute('data-translate');
                const translation = t(key);
                element.textContent = translation;
            });
        }

        // Apply app locale on load (no page-local language dropdown)
        changeLanguage(appLocale);
</script>
@endpush

