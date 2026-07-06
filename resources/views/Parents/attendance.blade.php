@extends('layouts.parent_corp')

@section('title', 'Mahudhurio ya Mfumo - ShuleXpert')

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
        padding: 18px 12px;
        text-align: center;
        border: 1px solid #eee;
        background: white;
        height: 100%;
    }
    .stat-value {
        font-size: 22px;
        font-weight: 800;
    }
    .stat-label {
        font-size: 9px;
        text-transform: uppercase;
        font-weight: 700;
        color: #777;
        letter-spacing: 0.5px;
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
    .record-item.sick { border-left-color: #fd7e14; }
    .record-item.excused { border-left-color: #17a2b8; }
    .record-item.late { border-left-color: #ffc107; }
    .child-overview-card {
        background: white;
        border-top: 3px solid #940000;
        box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 16px;
        transition: box-shadow 0.2s;
    }
    .child-overview-card:hover {
        box-shadow: 0 5px 20px rgba(148,0,0,0.1);
    }
    .rate-ring {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: 4px solid #940000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        color: #940000;
        flex-shrink: 0;
    }
    .mini-stat {
        text-align: center;
        padding: 6px;
    }
    .mini-stat .val { font-weight: 800; font-size: 16px; }
    .mini-stat .lbl { font-size: 9px; text-transform: uppercase; color: #888; font-weight: 700; }
    .mode-badge {
        background: #fff5f5;
        color: #940000;
        border: 1px solid rgba(148,0,0,0.2);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 10px;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <div class="section-title mb-2" style="border: none; padding: 0;">
                    <i class="bx bx-clipboard"></i> Mahudhurio ya Mfumo
                </div>
                <p class="text-muted small mb-0">Fuatilia mahudhurio ya watoto wako yaliyorekodi na walimu darasani (System Attendance)</p>
            </div>
            <span class="mode-badge"><i class="bx bx-check-circle"></i> Mfumo / System</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="col-12">
        <div class="filter-card">
            <form method="GET" action="{{ route('parentAttendance') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-uppercase small font-weight-bold">Mtoto</label>
                        <select name="student" class="form-select">
                            <option value="">Wote (Muhtasari)</option>
                            @foreach($students as $student)
                                <option value="{{ $student->studentID }}" {{ (string)$studentFilter === (string)$student->studentID ? 'selected' : '' }}>
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase small font-weight-bold">Tafuta Kwa</label>
                        <select name="search_type" class="form-select" id="searchType">
                            <option value="month" {{ $searchType == 'month' ? 'selected' : '' }}>Mwezi</option>
                            <option value="year" {{ $searchType == 'year' ? 'selected' : '' }}>Mwaka</option>
                            <option value="date" {{ $searchType == 'date' ? 'selected' : '' }}>Tarehe Maalum</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="monthField" style="display: {{ $searchType == 'month' ? 'block' : 'none' }};">
                        <label class="form-label text-uppercase small font-weight-bold">Mwezi</label>
                        <select name="month" class="form-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $monthFilter == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->locale('sw')->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3" id="dateField" style="display: {{ $searchType == 'date' ? 'block' : 'none' }};">
                        <label class="form-label text-uppercase small font-weight-bold">Tarehe</label>
                        <input type="date" name="date" class="form-control" value="{{ $dateFilter }}">
                    </div>
                    <div class="col-md-2" id="yearField" style="display: {{ in_array($searchType, ['month', 'year']) ? 'block' : 'none' }};">
                        <label class="form-label text-uppercase small font-weight-bold">Mwaka</label>
                        <select name="year" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $yearFilter == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary px-4"><i class="bx bx-search"></i> Tafuta</button>
                        <a href="{{ route('parentAttendance') }}" class="btn btn-outline-secondary px-4"><i class="bx bx-reset"></i> Safisha</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($students->isEmpty())
        <div class="col-12">
            <div class="alert alert-warning">Hakuna watoto waliosajiliwa kwenye akaunti yako.</div>
        </div>
    @elseif(empty($studentFilter))
        {{-- Overview for all children --}}
        <div class="col-12">
            <div class="section-title"><i class="bx bx-group"></i> Muhtasari wa Watoto Wako</div>
            @php
                $periodLabel = match($searchType) {
                    'date' => $dateFilter ? \Carbon\Carbon::parse($dateFilter)->format('d M Y') : 'Tarehe iliyochaguliwa',
                    'year' => 'Mwaka ' . $yearFilter,
                    default => \Carbon\Carbon::create((int)$yearFilter, (int)$monthFilter, 1)->locale('sw')->isoFormat('MMMM Y'),
                };
            @endphp
            <p class="text-muted small mb-3">Kipindi: <strong>{{ $periodLabel }}</strong> — Bonyeza "Angalia Zaidi" kwa rekodi za kila siku.</p>

            @foreach($childrenOverview as $item)
                @php
                    $st = $item['student'];
                    $stats = $item['stats'];
                    $className = trim(($st->subclass->class->class_name ?? '') . ' ' . ($st->subclass->subclass_name ?? ''));
                @endphp
                <div class="child-overview-card">
                    <div class="row align-items-center g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rate-ring">{{ $stats['attendance_rate'] }}%</div>
                                <div>
                                    <div style="font-weight: 800; font-size: 15px;">{{ $st->first_name }} {{ $st->last_name }}</div>
                                    <div class="small text-muted">{{ $className ?: 'Darasa haijulikani' }}</div>
                                    <div class="small text-muted">Adm: {{ $st->admission_number ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-1">
                                <div class="col mini-stat">
                                    <div class="val text-success">{{ $stats['present'] }}</div>
                                    <div class="lbl">Present</div>
                                </div>
                                <div class="col mini-stat">
                                    <div class="val text-danger">{{ $stats['absent'] }}</div>
                                    <div class="lbl">Absent</div>
                                </div>
                                <div class="col mini-stat">
                                    <div class="val text-warning">{{ $stats['sick'] }}</div>
                                    <div class="lbl">Sick</div>
                                </div>
                                <div class="col mini-stat">
                                    <div class="val text-info">{{ $stats['excused'] }}</div>
                                    <div class="lbl">Ruhusa</div>
                                </div>
                                <div class="col mini-stat">
                                    <div class="val text-secondary">{{ $stats['total_days'] }}</div>
                                    <div class="lbl">Jumla</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-md-end">
                            <a href="{{ route('parentAttendance', array_merge(request()->query(), ['student' => $st->studentID])) }}" class="btn btn-sm btn-primary w-100">
                                <i class="bx bx-show"></i> Angalia Zaidi
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($overviewData && $selectedStudent)
        {{-- Individual child detail --}}
        <div class="col-12 mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-0" style="font-weight: 800;">
                        {{ $selectedStudent->first_name }} {{ $selectedStudent->last_name }}
                    </h5>
                    <small class="text-muted">
                        {{ trim(($selectedStudent->subclass->class->class_name ?? '') . ' ' . ($selectedStudent->subclass->subclass_name ?? '')) }}
                        &bull; Adm: {{ $selectedStudent->admission_number ?? '—' }}
                    </small>
                </div>
                <a href="{{ route('parentAttendance', request()->except('student')) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Rudi kwa Muhtasari
                </a>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md">
                    <div class="stat-box">
                        <div class="stat-value text-success">{{ $overviewData['present'] }}</div>
                        <div class="stat-label">Present</div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="stat-box">
                        <div class="stat-value text-danger">{{ $overviewData['absent'] }}</div>
                        <div class="stat-label">Absent</div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="stat-box">
                        <div class="stat-value text-warning">{{ $overviewData['sick'] }}</div>
                        <div class="stat-label">Sick</div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="stat-box">
                        <div class="stat-value text-info">{{ $overviewData['excused'] }}</div>
                        <div class="stat-label">Ruhusa</div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="stat-box" style="border-bottom: 4px solid #940000;">
                        <div class="stat-value">{{ $overviewData['attendance_rate'] }}%</div>
                        <div class="stat-label">Kiwango cha Mahudhurio</div>
                    </div>
                </div>
            </div>

            <div class="section-title"><i class="bx bx-list-check"></i> Rekodi za Kila Siku ({{ $overviewData['total_days'] }})</div>
            <div class="mb-5">
                @foreach($dailyRecords as $record)
                    @php
                        $status = strtolower($record->status);
                        $itemClass = match($status) {
                            'present' => 'present',
                            'absent' => 'absent',
                            'sick' => 'sick',
                            'excused', 'permission' => 'excused',
                            'late' => 'late',
                            default => ''
                        };
                        $badgeClass = match($status) {
                            'present' => 'bg-success',
                            'absent' => 'bg-danger',
                            'sick' => 'bg-warning text-dark',
                            'excused' => 'bg-info',
                            'late' => 'bg-secondary',
                            default => 'bg-secondary'
                        };
                        $statusLabel = $record->status === 'Excused' ? 'Ruhusa' : $record->status;
                    @endphp
                    <div class="record-item {{ $itemClass }}">
                        <div>
                            <div style="font-weight: 700;">{{ $record->attendance_date->locale('sw')->isoFormat('dddd, D MMM YYYY') }}</div>
                            <div class="small text-muted">{{ $record->remark ?: 'Mahudhurio yalirekodiwa na mwalimu' }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $badgeClass }} text-uppercase" style="font-size: 10px; border-radius: 0;">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>
                @endforeach
                @if($dailyRecords->isEmpty())
                    <div class="text-center py-5 bg-white shadow-sm text-muted">
                        Hakuna rekodi za mahudhurio kwa kipindi hiki.
                    </div>
                @endif
            </div>
        </div>
    @elseif($selectedStudent)
        <div class="col-12">
            <div class="alert alert-info">Hakuna rekodi za mahudhurio kwa kipindi kilichochaguliwa.</div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#searchType').on('change', function() {
            const val = $(this).val();
            $('#monthField, #dateField, #yearField').hide();
            if (val === 'month') {
                $('#monthField, #yearField').show();
            } else if (val === 'year') {
                $('#yearField').show();
            } else if (val === 'date') {
                $('#dateField').show();
            }
        });
    });
</script>
@endpush
