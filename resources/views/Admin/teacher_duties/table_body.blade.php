@php 
    $groupedDuties = $duties->groupBy(function($item) {
        return $item->start_date . '_' . $item->end_date;
    });
    $weekCounter = 1;
    $today = \Carbon\Carbon::now();
@endphp

@foreach($groupedDuties as $key => $weekGroup)
    @php
        $startDate = \Carbon\Carbon::parse($weekGroup[0]->start_date);
        $endDate = \Carbon\Carbon::parse($weekGroup[0]->end_date);
        $assignedTeacherIds = $weekGroup->pluck('teacherID')->toArray();
        $isActive = $today->between($startDate, $endDate);
    @endphp
    <tr id="duty-row-{{ $startDate->format('Y-m-d') }}" class="{{ $isActive ? 'active-week' : '' }} expandable-row border-bottom" data-toggle="collapse" data-target="#details-{{ $startDate->format('Y-m-d') }}" style="cursor: pointer; {{ $isActive ? 'background-color: #FFF5F5;' : '' }}">
        <td class="py-4 pl-4 align-middle">
            <div class="d-flex align-items-center">
                <i class="fa fa-chevron-right mr-3 text-muted transition-icon" style="font-size: 10px;"></i>
                <span class="badge badge-pill px-3 py-2" style="background-color: {{ $isActive ? '#940000' : '#f8f9fa' }}; color: {{ $isActive ? '#ffffff' : '#940000' }}; font-weight: 800;">
                    WEEK {{ ($weekCounter++) }}
                </span>
            </div>
        </td>
        <td class="py-4 align-middle">
            <div class="d-flex align-items-center">
                <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; background: #940000; font-weight: 800; font-size: 11px;">
                    {{ count($weekGroup) }}
                </div>
                <div style="font-weight: 700; color: #2f2f2f;">
                    @foreach($weekGroup as $duty)
                        <span class="mr-2">{{ $duty->teacher ? $duty->teacher->first_name . ' ' . $duty->teacher->last_name : 'N/A' }}{{ !$loop->last ? ',' : '' }}</span>
                    @endforeach
                </div>
            </div>
        </td>
        <td class="py-4 align-middle" style="font-weight: 600; color: #6c757d;">
            {{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}
        </td>
        <td class="py-4 pr-4 align-middle text-right">
            <div class="btn-group shadow-sm" style="border-radius: 0; overflow: hidden;">
                <button class="btn btn-sm edit-duty" 
                        style="background: #ffffff; color: #940000; border: 1px solid #eee; font-weight: 700;"
                        data-start="{{ $startDate->format('Y-m-d') }}" 
                        data-teachers="{{ json_encode($assignedTeacherIds) }}">
                    <i class="fa fa-pencil"></i> EDIT
                </button>
                <button class="btn btn-sm delete-duty" 
                        style="background: #ffffff; color: #dc3545; border: 1px solid #eee; font-weight: 700;"
                        data-start="{{ $startDate->format('Y-m-d') }}" 
                        data-end="{{ $endDate->format('Y-m-d') }}">
                    <i class="fa fa-trash"></i>
                </button>
                <button class="btn btn-sm text-white" 
                        style="background: #2f2f2f; border: none; font-weight: 700;"
                        type="button" data-toggle="collapse" data-target="#details-{{ $startDate->format('Y-m-d') }}">
                    <i class="fa fa-eye"></i> LOGS
                </button>
            </div>
        </td>
    </tr>
    <tr id="details-{{ $startDate->format('Y-m-d') }}" class="collapse bg-light">
        <td colspan="4" class="p-0">
            <div class="p-3">
                <h6 class="mb-3"><i class="fa fa-calendar"></i> Daily Reports for the Week</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover bg-white mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Status</th>
                                <th>Teacher</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $current = $startDate->copy();
                            @endphp
                            @while($current->lte($endDate))
                                @php
                                    $dateStr = $current->format('Y-m-d');
                                    // Multiple teachers might have reports if it's a shared week, 
                                    // but usually they share one report or have separate.
                                    // Given the logic, it seems they might share or one fills it.
                                    $dayReports = $reports->get($dateStr) ?? collect();
                                @endphp
                                <tr>
                                    <td>{{ $current->format('d/m/Y') }}</td>
                                    <td>{{ $current->format('l') }}</td>
                                    <td>
                                        @if($dayReports->isEmpty())
                                            <span class="badge badge-secondary">Pending</span>
                                        @else
                                            @foreach($dayReports as $rep)
                                                @php
                                                    $statusClass = 'badge-secondary';
                                                    if($rep->status === 'Sent') $statusClass = 'badge-warning';
                                                    if($rep->status === 'Approved') $statusClass = 'badge-success';
                                                    if($rep->status === 'Draft') $statusClass = 'badge-info';
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $rep->status }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @if($dayReports->isEmpty())
                                            <span class="text-muted small">Not submitted</span>
                                        @else
                                            @foreach($dayReports as $rep)
                                                <span class="small">{{ $rep->teacher ? $rep->teacher->first_name . ' ' . $rep->teacher->last_name : 'N/A' }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @foreach($dayReports as $rep)
                                            @if($rep->status === 'Sent')
                                                <button class="btn btn-xs btn-primary view-sign-report" 
                                                        data-id="{{ $rep->reportID }}" 
                                                        data-date="{{ $dateStr }}">
                                                    <i class="fa fa-pencil"></i> View & Sign
                                                </button>
                                            @elseif($rep->status === 'Approved')
                                                <button class="btn btn-xs btn-success view-report-admin" 
                                                        data-id="{{ $rep->reportID }}" 
                                                        data-date="{{ $dateStr }}">
                                                    <i class="fa fa-eye"></i> View
                                                </button>
                                                <a href="{{ route('teacher.duty_book.export_report', ['date' => $dateStr, 'teacherID' => $rep->teacherID]) }}" class="btn btn-xs btn-danger">
                                                    <i class="fa fa-file-pdf-o"></i> PDF
                                                </a>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                                @php $current->addDay(); @endphp
                            @endwhile
                        </tbody>
                    </table>
                </div>
            </div>
        </td>
    </tr>
@endforeach

@if($groupedDuties->isEmpty())
    <tr>
        <td colspan="4" class="text-center py-5">
            <div class="p-4 mb-3 rounded-circle mx-auto" style="background-color: #FFF5F5; width: fit-content;">
                <i class="fa fa-calendar-times-o fa-3x" style="color: #940000;"></i>
            </div>
            <h5 class="font-weight-bold" style="color: #2f2f2f;">No Duty Assignments Found</h5>
            <p class="text-muted small">Try adjusting your date filters or assign new duties above.</p>
        </td>
    </tr>
@endif
