@php
    $isWeeklyTest = ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week');
    $isMonthlyTest = ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month');
    $statusLabel = ucfirst(str_replace('_', ' ', $exam->status ?? 'scheduled'));
    $approvalLabel = $exam->approval_status ?? 'Pending';
    $termYear = trim(($exam->term ? ucfirst(str_replace('_', ' ', $exam->term)) . ' • ' : '') . ($exam->displayYearLabelAll() ?: ($exam->year ?? '')));
    $examViewMore = [
        'title' => $exam->exam_name,
        'fields' => [
            ['label' => 'Term / Year', 'value' => $termYear ?: '-'],
            ['label' => 'Status', 'value' => $statusLabel],
            ['label' => 'Approval', 'value' => $approvalLabel],
            ['label' => 'Expected Students', 'value' => (string) ($exam->expected_students ?? 0)],
            ['label' => 'Enter Result', 'value' => ($exam->enter_result ?? false) ? 'Allowed' : 'Not Allowed'],
            ['label' => 'Publish Result', 'value' => ($exam->publish_result ?? false) ? 'Published' : 'Not Published'],
        ],
    ];
@endphp
<tr data-exam-id="{{ $exam->examID }}" data-view-more='@json($examViewMore)'>
    <td>{{ $rowIndex ?? 1 }}</td>
    <td class="font-weight-bold">{{ $exam->exam_name }}</td>
    <td>{{ $termYear ?: '-' }}</td>
    <td>
        <span class="exam-status-pill exam-status-{{ str_replace('_', '-', $exam->status ?? 'scheduled') }}">{{ $statusLabel }}</span>
        @if($approvalLabel === 'Pending')
            <br><small class="text-warning">Pending Approval</small>
        @elseif($approvalLabel === 'Rejected')
            <br><small class="text-danger">Rejected</small>
        @endif
    </td>
    <td class="store-actions-cell text-center">
        @include('Admin.partials.exam_actions_dropdown', ['exam' => $exam])
    </td>
</tr>
