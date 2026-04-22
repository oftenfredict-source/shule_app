@extends('layouts.parent_corp')

@section('title', 'Attendance Records - ShuleXpert')

@section('content')
<style>
    .filter-card {
        background: white;
        padding: 25px;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 800;
        color: #940000;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 20px;
        border-bottom: 2px solid rgba(148,0,0,0.1);
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .stat-box {
        padding: 20px;
        text-align: center;
        border: 1px solid #eee;
    }
    .stat-value {
        font-size: 24px;
        font-weight: 800;
    }
    .stat-label {
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 700;
        color: #777;
    }
    .record-item {
        background: white;
        padding: 15px 20px;
        border-left: 5px solid #eee;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .record-item.present { border-left-color: #28a745; }
    .record-item.absent { border-left-color: #dc3545; }
    .record-item.late { border-left-color: #ffc107; }
</style>

<div class="row">
    <div class="col-12">
        <div class="section-title"><i class="bx bx-calendar-check"></i> Attendance Information</div>
    </div>

    <!-- Filters -->
    <div class="col-12">
        <div class="filter-card">
            <form method="GET" action="{{ route('parentAttendance') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-uppercase small font-weight-bold">Child</label>
                        <select name="student" class="form-select" required>
                            <option value="">Select Child...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->studentID }}" {{ $studentFilter == $student->studentID ? 'selected' : '' }}>
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase small font-weight-bold">Search By</label>
                        <select name="search_type" class="form-select" id="searchType">
                            <option value="month" {{ $searchType == 'month' ? 'selected' : '' }}>Month</option>
                            <option value="year" {{ $searchType == 'year' ? 'selected' : '' }}>Year</option>
                            <option value="date" {{ $searchType == 'date' ? 'selected' : '' }}>Specific Date</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="monthField" style="display: {{ $searchType == 'month' ? 'block' : 'none' }};">
                        <label class="form-label text-uppercase small font-weight-bold">Month</label>
                        <select name="month" class="form-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $monthFilter == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3" id="dateField" style="display: {{ $searchType == 'date' ? 'block' : 'none' }};">
                        <label class="form-label text-uppercase small font-weight-bold">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $dateFilter }}">
                    </div>
                    <div class="col-md-2" id="yearField">
                        <label class="form-label text-uppercase small font-weight-bold">Year</label>
                        <select name="year" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $yearFilter == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4"><i class="bx bx-search"></i> Apply Filters</button>
                        <a href="{{ route('parentAttendance') }}" class="btn btn-outline-secondary px-4"><i class="bx bx-reset"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($overviewData)
    <div class="col-12 mt-4">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="bg-white stat-box">
                    <div class="stat-value text-success">{{ $overviewData['present'] }}</div>
                    <div class="stat-label">Days Present</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white stat-box">
                    <div class="stat-value text-danger">{{ $overviewData['absent'] }}</div>
                    <div class="stat-label">Days Absent</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white stat-box">
                    <div class="stat-value text-warning">{{ $overviewData['late'] }}</div>
                    <div class="stat-label">Days Late</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white stat-box" style="border-bottom: 4px solid #940000;">
                    <div class="stat-value">{{ $overviewData['attendance_rate'] }}%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
        </div>

        <div class="section-title"><i class="bx bx-list-check"></i> Daily Log</div>
        <div class="mb-5">
            @foreach($dailyRecords as $record)
                @php
                    $status = strtolower($record->status);
                    $itemClass = match($status) {
                        'present' => 'present',
                        'absent' => 'absent',
                        'late' => 'late',
                        default => ''
                    };
                @endphp
                <div class="record-item {{ $itemClass }}">
                    <div>
                        <div style="font-weight: 700;">{{ $record->attendance_date->format('l, d M Y') }}</div>
                        <div class="small text-muted">{{ $record->remark ?: 'Regular attendance recorded' }}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge {{ $status == 'present' ? 'bg-success' : ($status == 'absent' ? 'bg-danger' : 'bg-warning') }} text-uppercase" style="font-size: 10px; border-radius: 0;">
                            {{ $record->status }}
                        </span>
                    </div>
                </div>
            @endforeach
            @if($dailyRecords->isEmpty())
                <div class="text-center py-5 bg-white shadow-sm text-muted">
                    No individual records found for this period.
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#searchType').on('change', function() {
            const val = $(this).val();
            $('#monthField, #dateField').hide();
            if (val === 'month') $('#monthField').show();
            if (val === 'date') $('#dateField').show();
        });
    });
</script>
@endpush
