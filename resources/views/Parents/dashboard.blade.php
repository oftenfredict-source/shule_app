@extends('layouts.parent_corp')

@section('title', 'Parent Dashboard - ShuleXpert')

@section('content')
<style>
    /* Content Specific Customizations */
    .stat-card {
        background: white;
        padding: 24px;
        border-left: 5px solid #940000;
        box-shadow: 0 4px 15px rgba(148,0,0,0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.2s;
        border-radius: 0 !important;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        font-size: 32px;
        color: #940000;
        margin-bottom: 15px;
    }
    .stat-number {
        font-size: 28px;
        font-weight: 800;
        color: #333;
        line-height: 1;
    }
    .stat-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #777;
        margin-top: 5px;
        font-weight: 700;
    }

    .parent-profile-bar {
        background: white;
        padding: 25px;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        border-radius: 0 !important;
    }
    .profile-img-big {
        width: 100px;
        height: 100px;
        border: 3px solid #940000;
        object-fit: cover;
        border-radius: 0 !important;
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

    .student-item {
        background: white;
        padding: 15px;
        border: 1px solid #eee;
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        transition: border-color 0.3s;
        border-radius: 0 !important;
    }
    .student-item:hover {
        border-color: #940000;
    }
    .student-img {
        width: 60px;
        height: 60px;
        border: 2px solid #eee;
        object-fit: cover;
        border-radius: 0 !important;
    }

    .table th {
        background: #fcfcfc;
        color: #940000;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        font-weight: 800;
        border-radius: 0 !important;
    }
    .card {
        border-radius: 0 !important;
    }
    .badge {
        border-radius: 0 !important;
    }
    @media (max-width: 767.98px) {
        .stat-card {
            padding: 14px 12px;
        }
        .stat-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .stat-number {
            font-size: 22px;
        }
        .stat-label {
            font-size: 9px;
            letter-spacing: 0.5px;
        }
    }
</style>

<div class="row">
    <!-- Success Message -->
    @if (session('success'))
        <div class="col-12">
            <div class="alert alert-success d-flex align-items-center" role="alert" style="border-radius: 0;">
                <i class="bx bx-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- Parent Profile Header -->
    <div class="col-12">
        <div class="parent-profile-bar">
            <div class="row align-items-center">
                <div class="col-md-auto mb-3 mb-md-0">
                    @if($parent->photo)
                        <img src="{{ asset('userImages/' . $parent->photo) }}" class="profile-img-big" alt="Parent">
                    @else
                        <div class="profile-img-big d-flex align-items-center justify-content-center bg-light">
                            <i class="bx bx-user" style="font-size: 50px; color: #ccc;"></i>
                        </div>
                    @endif
                </div>
                <div class="col">
                    <h3 class="mb-1" style="font-weight: 800; color: #333; font-family: 'Century Gothic', sans-serif;">{{ strtoupper($parent->first_name . ' ' . ($parent->middle_name ?? '') . ' ' . $parent->last_name) }}</h3>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                        <span class="text-muted"><i class="bx bx-phone"></i> {{ $parent->phone }}</span>
                        @if($parent->email)<span class="text-muted"><i class="bx bx-envelope"></i> {{ $parent->email }}</span>@endif
                        <span class="text-muted"><i class="bx bx-hash"></i> Parent ID: {{ $parent->parentID }}</span>
                        @if($school)<span class="text-primary font-weight-bold"><i class="bx bx-building-house"></i> {{ $school->school_name }}</span>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Result Notifications -->
<div class="row">
    @if(isset($resultNotifications) && $resultNotifications->count() > 0)
        @foreach($resultNotifications as $rn)
            <div class="col-12 mb-3">
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-0" role="alert" style="border-radius: 0; border-left: 5px solid #940000; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <i class="bx bx-megaphone me-3" style="font-size: 24px; color: #940000;"></i>
                    <div style="color: #333;">
                        @if($rn->type == 'exam')
                            Matokeo ya <strong>{{ $rn->examination->exam_name ?? 'Mtihani' }}</strong> kwa mwanafunzi <strong>{{ $rn->student->first_name }} {{ $rn->student->last_name }}</strong> yametoka.
                        @else
                            Ripoti ya <strong>Muhula ({{ $rn->term == 'first_term' ? 'Muhula wa 1' : 'Muhula wa 2' }})</strong> kwa mwanafunzi <strong>{{ $rn->student->first_name }} {{ $rn->student->last_name }}</strong> imetoka.
                        @endif
                        <a href="{{ route('parentResults') }}" class="alert-link ms-2" style="color: #940000; text-decoration: underline;">Bofya hapa kutazama</a>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Statistics Row -->
<div class="row g-3 g-md-4 mb-4">
    <div class="col-6 col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bx bx-group"></i></div>
            <div class="stat-number">{{ number_format($totalStudents) }}</div>
            <div class="stat-label">Total Children</div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bx bx-user-check"></i></div>
            <div class="stat-number">{{ number_format($activeStudents) }}</div>
            <div class="stat-label">Active Students</div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bx bx-chart"></i></div>
            <div class="stat-number">{{ $recentResults->count() }}</div>
            <div class="stat-label">Recent Results</div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bx bx-alarm-exclamation"></i></div>
            <div class="stat-number">{{ $upcomingExams->count() }}</div>
            <div class="stat-label">Upcoming Exams</div>
        </div>
    </div>
</div>

<!-- Mahudhurio Quick Access -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-top: 4px solid #940000 !important;">
            <div class="card-body">
                <div class="section-title mb-3" style="margin-bottom: 12px !important;"><i class="bx bx-calendar-check"></i> Mahudhurio ya Watoto</div>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="small text-uppercase fw-bold text-muted">Chagua Aina</label>
                        <select class="form-select" id="dashboardAttendanceMode">
                            <option value="{{ route('parentAttendance') }}">Mahudhurio ya Mfumo</option>
                            <option value="{{ route('parentFingerprintAttendance') }}">Alama za Vidole (Fingerprint)</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <p class="small text-muted mb-2">Bofya hapa chini kuona mahudhurio ya kila mtoto kwa kalenda ya mwezi.</p>
                        <a href="{{ route('parentAttendance') }}" class="btn btn-primary">
                            <i class="bx bx-show"></i> Angalia Mahudhurio ya Mfumo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Children List -->
    <div class="col-lg-4">
        <div class="section-title"><i class="bx bx-child"></i> My Children</div>
        <div class="mb-4">
            @foreach($students as $student)
                <div class="student-item">
                    @if($student->photo)
                        <img src="{{ asset('userImages/' . $student->photo) }}" class="student-img" alt="Student">
                    @else
                        <div class="student-img d-flex align-items-center justify-content-center bg-light"><i class="bx bx-user"></i></div>
                    @endif
                    <div>
                        <div style="font-weight: 700; color: #333;">{{ $student->first_name }} {{ $student->last_name }}</div>
                        <div style="font-size: 12px; color: #777;">
                            {{ $student->subclass->class->class_name ?? '' }} {{ $student->subclass->subclass_name ?? '' }}
                        </div>
                        <div class="mt-1">
                            <span class="badge {{ $student->status == 'Active' ? 'bg-success' : 'bg-secondary' }}" style="font-size: 9px; border-radius: 0;">{{ strtoupper($student->status) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="section-title mt-4"><i class="bx bx-pie-chart-alt-2"></i> Gender Distribution</div>
        <div class="card p-3 mb-4 shadow-sm border-0">
            <canvas id="genderChart" height="200"></canvas>
        </div>
    </div>

    <!-- Main Activity Section -->
    <div class="col-lg-8">
        <!-- Recent Results -->
        <div class="section-title"><i class="bx bx-award"></i> Latest Examination Results</div>
        <div class="card mb-5 border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Exam</th>
                            <th class="text-center">Marks</th>
                            <th class="text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentResults as $result)
                        <tr>
                            <td style="font-weight: 700;">{{ $result->student->first_name }}</td>
                            <td>{{ $result->classSubject->subject->subject_name ?? 'N/A' }}</td>
                            <td style="font-size: 12px;">{{ $result->examination->exam_name ?? 'N/A' }}</td>
                            <td class="text-center"><span class="badge bg-primary" style="border-radius: 0;">{{ $result->marks }}</span></td>
                            <td class="text-center"><span class="badge bg-success" style="border-radius: 0;">{{ $result->grade }}</span></td>
                        </tr>
                        @endforeach
                        @if($recentResults->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No recent results found</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attendance -->
        <div class="section-title"><i class="bx bx-calendar-event"></i> Recent Attendance</div>
        <div class="card mb-5 border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAttendance as $attendance)
                        <tr>
                            <td style="font-weight: 700;">{{ $attendance->student->first_name }}</td>
                            <td>{{ $attendance->attendance_date->format('d M, Y') }}</td>
                            <td>
                                @php
                                    $color = match($attendance->status) {
                                        'Present' => 'success',
                                        'Wait' => 'warning',
                                        'Absent' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $color }}" style="border-radius: 0;">{{ strtoupper($attendance->status) }}</span>
                            </td>
                            <td style="font-size: 12px; color: #777;">{{ $attendance->remark ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($totalStudents > 0)
        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [{{ $maleStudents }}, {{ $femaleStudents }}],
                    backgroundColor: ['#940000', '#f0f0f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        @endif
    });
    document.getElementById('dashboardAttendanceMode')?.addEventListener('change', function () {
        if (this.value) window.location.href = this.value;
    });
</script>
@endpush
