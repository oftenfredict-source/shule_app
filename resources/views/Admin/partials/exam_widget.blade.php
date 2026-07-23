@php
    $isWeeklyTest = ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week');
    $isMonthlyTest = ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month');
    
    $daysUntilStart = null;
    $daysUntilEnd = null;
    if (!$isWeeklyTest && !$isMonthlyTest) {
        try {
            $today = now()->startOfDay();
            $startDate = \Carbon\Carbon::parse($exam->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($exam->end_date)->startOfDay();
            $daysUntilStart = $today->diffInDays($startDate, false);
            $daysUntilEnd = $today->diffInDays($endDate, false);
        } catch (\Exception $e) {
            $daysUntilStart = null;
            $daysUntilEnd = null;
        }
    }
    
    $expectedStudents = $exam->expected_students ?? 0;
    $statusMessage = '';
    $statusClass = 'info';
    
    if ($isWeeklyTest) {
        $statusMessage = 'Every week';
    } elseif ($isMonthlyTest) {
        $statusMessage = 'Every month in a term';
    } elseif (($exam->status ?? '') == 'scheduled' || ($exam->status ?? '') == 'wait_approval') {
        if ($daysUntilStart !== null && $daysUntilStart >= 0) {
            $statusMessage = $daysUntilStart == 0 ? 'Starts today' : ($daysUntilStart == 1 ? '1 day to start' : $daysUntilStart . ' days to start');
        }
    } elseif (($exam->status ?? '') == 'ongoing') {
        if ($daysUntilEnd !== null && $daysUntilEnd >= 0) {
            $statusMessage = $daysUntilEnd == 0 ? 'Ends today' : ($daysUntilEnd == 1 ? '1 day remaining' : $daysUntilEnd . ' days remaining');
            $statusClass = 'warning';
        }
    } elseif (($exam->status ?? '') == 'awaiting_results' || ($exam->status ?? '') == 'results_available') {
        $statusMessage = 'Completed';
        $statusClass = 'success';
    }
@endphp

<div class="card exam-grid-card h-100">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="pr-2">
                <h6 class="mb-1 exam-widget-title">{{ $exam->exam_name }}</h6>
                <small class="exam-widget-meta">
                    @if($exam->term){{ ucfirst(str_replace('_', ' ', $exam->term)) }} • @endif{{ $exam->displayYearLabelAll() ?: $exam->year }}
                </small>
            </div>
            @include('Admin.partials.exam_actions_dropdown', ['exam' => $exam])
        </div>

        @if(isset($exam->test_end_date_range) && $exam->test_end_date_range)
        <div class="mb-2">
            <button class="btn btn-link p-0 d-flex align-items-center cursor-pointer text-primary-custom text-decoration-none w-100 collapsed test-breakdown-toggle"
                style="font-size: 0.85rem; font-weight: 600;" type="button"
                data-target="#breakdown_{{ $exam->examID }}" aria-expanded="false">
                <i class="bi bi-calendar-check me-1"></i> Final Week: {{ $exam->test_end_date_range }}
                <i class="bi bi-chevron-down ms-auto transition-transform" style="font-size: 0.7rem;"></i>
            </button>
            @if(isset($exam->test_breakdown) && count($exam->test_breakdown) > 0)
            <div class="mt-2" id="breakdown_{{ $exam->examID }}" style="display: none;">
                <div class="p-2 rounded border" style="background-color: #fafafa; border-color: #f1d7d7 !important;">
                    <ul class="list-unstyled mb-0" style="font-size: 0.75rem; color: #444;">
                        @foreach($exam->test_breakdown as $item)
                        <li class="mb-1 d-flex justify-content-between align-items-start border-bottom pb-1" style="border-bottom-style: dotted !important;">
                            <span class="fw-bold text-dark" style="max-width: 60%;">{{ $item['name'] }}</span>
                            <div class="text-end">
                                <div class="fw-bold">Week {{ $item['week'] }}</div>
                                <div class="text-muted" style="font-size: 0.65rem;">{{ $item['date_range'] }}</div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
        @elseif(isset($exam->total_weeks_count) && $exam->total_weeks_count > 0)
        <small class="text-info d-block mb-2"><i class="bi bi-calendar-range"></i> Total Weeks: {{ $exam->total_weeks_count }}</small>
        @endif

        <div class="exam-grid-meta mb-2">
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">Expected Students</span>
                <strong>{{ $expectedStudents }}</strong>
            </div>
        </div>

        @if($statusMessage)
        <div class="mb-2">
            <span class="badge exam-widget-status w-100 py-2">
                <i class="bi bi-{{ $statusClass == 'warning' ? 'hourglass-split' : ($statusClass == 'success' ? 'check-circle' : 'calendar-event') }}"></i>
                {{ $statusMessage }}
            </span>
        </div>
        @endif

        @if(($exam->approval_status ?? 'Pending') == 'Pending')
        <span class="badge bg-warning text-dark w-100 py-2 mb-1 d-block"><i class="bi bi-clock-history"></i> Pending Approval</span>
        @elseif(($exam->approval_status ?? 'Pending') == 'Rejected')
        <span class="badge bg-danger text-white w-100 py-2 mb-1 d-block"><i class="bi bi-x-circle"></i> Rejected</span>
        @endif
    </div>
</div>
