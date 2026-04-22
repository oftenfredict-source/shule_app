@extends('layouts.parent_corp')

@section('title', 'Examination Results - ShuleXpert')

@section('content')
<style>
    .filter-card {
        background: white;
        padding: 25px;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .results-card {
        background: white;
        padding: 0;
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
    .table th {
        background: #fcfcfc;
        color: #940000;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        font-weight: 800;
    }
    .exam-header {
        background: #fcfcfc;
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
    }
    .summary-box {
        background: #fcfcfc;
        padding: 15px;
        border: 1px solid #eee;
        text-align: center;
    }
    .summary-value {
        font-size: 20px;
        font-weight: 800;
        color: #940000;
    }
    .summary-label {
        font-size: 10px;
        text-transform: uppercase;
        color: #777;
        font-weight: 700;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="section-title"><i class="bx bx-trophy"></i> Examination Results</div>
    </div>

    <!-- Filters -->
    <div class="col-12">
        <div class="filter-card">
            <form method="GET" action="{{ route('parentResults') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-uppercase small font-weight-bold">Year</label>
                        <select name="year" class="form-select" id="yearFilter" required>
                            <option value="">Select Year</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $yearFilter == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase small font-weight-bold">Child</label>
                        <select name="student" class="form-select" id="studentFilter" required>
                            <option value="">Select Child</option>
                            @foreach($students as $student)
                                <option value="{{ $student->studentID }}" {{ $studentFilter == $student->studentID ? 'selected' : '' }}>
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase small font-weight-bold">Term</label>
                        <select name="term" class="form-select" id="termFilter" required>
                            <option value="">Select Term</option>
                            @foreach($terms as $term)
                                <option value="{{ $term }}" {{ $termFilter == $term ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $term)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase small font-weight-bold">Type</label>
                        <select name="type" class="form-select" id="typeFilter" required>
                            <option value="exam" {{ ($typeFilter ?? 'exam') == 'exam' ? 'selected' : '' }}>Examination</option>
                            <option value="report" {{ ($typeFilter ?? '') == 'report' ? 'selected' : '' }}>Terminal Report</option>
                        </select>
                    </div>
                    <div class="col-12" id="examSelectionRow" style="display: {{ ($typeFilter ?? 'exam') == 'exam' ? 'block' : 'none' }};">
                        <label class="form-label text-uppercase small font-weight-bold">Select Specific Exam</label>
                        <select name="exam" class="form-select" id="examFilter">
                            <option value="">Choose Exam...</option>
                            @foreach($examinations as $exam)
                                <option value="{{ $exam->examID }}" {{ $examFilter == $exam->examID ? 'selected' : '' }}>
                                    {{ $exam->exam_name }} ({{ $exam->year }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="button" class="btn btn-primary px-4" id="filterBtn"><i class="bx bx-search"></i> Filter Results</button>
                        <a href="{{ route('parentResults') }}" class="btn btn-outline-secondary px-4"><i class="bx bx-reset"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Display -->
    <div class="col-12 mt-4" id="resultsContainer">
        @if(isset($resultsWithDivisions) && count($resultsWithDivisions) > 0)
            @foreach($resultsWithDivisions as $examKey => $group)
                <div class="results-card border-0 mb-5">
                    @php
                        $isReportType = isset($group['type']) && $group['type'] === 'report';
                        $resultsCount = count($group['results'] ?? []);
                        $totalMarks = $group['total_marks'] ?? 0;
                        $avg = $resultsCount > 0 ? $totalMarks / $resultsCount : 0;
                    @endphp
                    
                    <div class="exam-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0" style="font-weight: 800; color: #333;">
                                    @if($isReportType)
                                        TERMINAL REPORT: {{ strtoupper($group['term']) }} {{ $group['year'] }}
                                    @else
                                        {{ strtoupper($group['exam']->exam_name ?? 'EXAMINATION') }}
                                    @endif
                                </h5>
                                <div class="small text-muted">{{ $group['student']->first_name }} {{ $group['student']->last_name }} | {{ $group['student']->admission_number }}</div>
                            </div>
                            <div class="col-md-auto mt-3 mt-md-0">
                                <div class="d-flex gap-2">
                                    <div class="summary-box">
                                        <div class="summary-value">{{ number_format($avg, 1) }}</div>
                                        <div class="summary-label">Average</div>
                                    </div>
                                    <div class="summary-box" style="border-left: 3px solid #940000;">
                                        <div class="summary-value">{{ $group['overall_grade'] ?? $group['overall_division'] ?? 'N/A' }}</div>
                                        <div class="summary-label">Grade/Div</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Subject</th>
                                    <th class="text-center">Marks</th>
                                    <th class="text-center">Grade</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group['results'] as $i => $result)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td style="font-weight: 700;">{{ $result->classSubject->subject->subject_name ?? 'N/A' }}</td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $result->marks }}</span></td>
                                    <td class="text-center"><span class="badge bg-success">{{ $result->grade }}</span></td>
                                    <td class="text-muted small">{{ $result->remark ?: 'Continuous Academic Progress' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-5 bg-white shadow-sm">
                <i class="bx bx-info-circle text-muted" style="font-size: 60px;"></i>
                <h5 class="mt-3 text-muted">No results found for the current selection.</h5>
                <p class="text-muted small">Please use the filters above to view academic records.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#typeFilter').on('change', function() {
            if ($(this).val() === 'exam') {
                $('#examSelectionRow').fadeIn();
            } else {
                $('#examSelectionRow').fadeOut();
            }
        });

        $('#filterBtn').on('click', function() {
            const formData = $('#filterForm').serialize();
            
            $('#resultsContainer').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Fetching Records...</div></div>');

            $.ajax({
                url: '{{ route("parentResults") }}',
                method: 'GET',
                data: formData,
                success: function(response) {
                    const html = $(response).find('#resultsContainer').html();
                    $('#resultsContainer').html(html);
                },
                error: function() {
                    $('#resultsContainer').html('<div class="alert alert-danger">An error occurred while loading results.</div>');
                }
            });
        });
    });
</script>
@endpush
