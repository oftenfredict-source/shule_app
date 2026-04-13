@include('includes.Admin_nav')

{{-- Fetching Additional Data for Dashboard --}}
@php
    $schoolID = Session::get('schoolID');
    $currentDate = date('Y-m-d');
    
    // 1. Notification Counts
    $pendingExams = DB::table('exam_papers')
        ->join('examinations', 'exam_papers.examID', '=', 'examinations.examID')
        ->where('examinations.schoolID', $schoolID)
        ->where('exam_papers.status', 'wait_approval')
        ->count();

    $pendingResults = DB::table('result_approvals')
        ->join('examinations', 'result_approvals.examID', '=', 'examinations.examID')
        ->where('examinations.schoolID', $schoolID)
        ->where('result_approvals.status', 'Pending')
        ->count();

    // 2. Financial Summary
    $allPayments = DB::table('payments')->where('schoolID', $schoolID);
    
    $studentsWithDebtCount = (clone $allPayments)->where('balance', '>', 0)->count();
    $clearedFeesCount = (clone $allPayments)->where('balance', '<=', 0)->count();

    $todayCollection = DB::table('payment_records')
        ->join('payments', 'payment_records.paymentID', '=', 'payments.paymentID')
        ->where('payments.schoolID', $schoolID)
        ->whereDate('payment_records.created_at', $currentDate)
        ->sum('payment_records.paid_amount') ?? 0;

    $yearTotalCollection = DB::table('payment_records')
        ->join('payments', 'payment_records.paymentID', '=', 'payments.paymentID')
        ->where('payments.schoolID', $schoolID)
        ->whereYear('payment_records.created_at', date('Y'))
        ->sum('payment_records.paid_amount') ?? 0;

    $yearTotalExpenses = DB::table('expenses')
        ->where('schoolID', $schoolID)
        ->whereYear('date', date('Y'))
        ->where('status', 'Approved') // Assuming only approved expenses count
        ->sum('amount') ?? 0;

    // 3. Gender Distribution
    $genderStats = DB::table('students')
        ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
        ->join('classes', 'subclasses.classID', '=', 'classes.classID')
        ->where('classes.schoolID', $schoolID)
        ->where('students.status', 'Active')
        ->select('students.gender', DB::raw('count(*) as total'))
        ->groupBy('students.gender')
        ->get();
        
    $maleCount = $genderStats->where('gender', 'Male')->first()->total ?? 0;
    $femaleCount = $genderStats->where('gender', 'Female')->first()->total ?? 0;

    // 4. Monthly Collection (Last 6 months)
    $monthlyCollections = DB::table('payment_records')
        ->join('payments', 'payment_records.paymentID', '=', 'payments.paymentID')
        ->where('payments.schoolID', $schoolID)
        ->where('payment_records.created_at', '>=', now()->subMonths(6))
        ->select(DB::raw('MONTHNAME(payment_records.created_at) as month'), DB::raw('SUM(payment_records.paid_amount) as total'), DB::raw('MONTH(payment_records.created_at) as month_num'))
        ->groupBy('month', 'month_num')
        ->orderBy('month_num')
        ->get();

    // 5. Recent Activities
    $recentStudents = DB::table('students')
        ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
        ->join('classes', 'subclasses.classID', '=', 'classes.classID')
        ->where('classes.schoolID', $schoolID)
        ->select('students.first_name', 'students.last_name', 'students.created_at', DB::raw("'New Student Registered' as activity_type"))
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();

    $recentPayments = DB::table('payment_records')
        ->join('payments', 'payment_records.paymentID', '=', 'payments.paymentID')
        ->join('students', 'payments.studentID', '=', 'students.studentID')
        ->where('payments.schoolID', $schoolID)
        ->select(DB::raw("CONCAT('Fee Payment from ', students.first_name) as activity_title"), 'payment_records.created_at', DB::raw("'Payment Received' as activity_type"))
        ->orderBy('payment_records.created_at', 'desc')
        ->limit(3)
        ->get();

    $activities = collect($recentStudents)->merge($recentPayments)->sortByDesc('created_at')->take(10);
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --corporate-red: #940000;
        --accent-soft-red: #FFF5F5;
        --primary-white: #FFFFFF;
        --text-dark: #2d3436;
        --text-muted: #636e72;
        --pale-gradient: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);
        --success-green: #2ecc71;
        --warning-orange: #f39c12;
    }

    body {
        background-color: var(--primary-white);
        font-family: 'Century Gothic', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: var(--text-dark);
    }

    .dashboard-container {
        padding: 1.5rem;
    }

    /* Welcome Header */
    .welcome-banner {
        background: var(--pale-gradient);
        border: 1px solid var(--accent-soft-red);
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 10px rgba(148, 0, 0, 0.03);
        border-left: 5px solid var(--corporate-red);
    }

    /* Summary Cards */
    .stat-widget {
        background: var(--accent-soft-red);
        border: none;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 1rem;
        height: 100%;
    }

    .stat-widget:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(148, 0, 0, 0.08);
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        background: var(--primary-white);
        color: var(--corporate-red);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 1.1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .stat-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.8px;
    }

    .stat-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--corporate-red);
        margin-top: 1px;
    }

    /* Notification Center */
    .section-header {
        font-weight: 800;
        color: var(--corporate-red);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1.2rem;
        display: flex;
        align-items: center;
    }

    .pill-item {
        background: var(--primary-white);
        border: 1px solid var(--accent-soft-red);
        padding: 1rem 1.2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
        border-left: 4px solid var(--corporate-red);
    }
    
    .pill-item:hover {
        background: var(--accent-soft-red);
        border-color: var(--corporate-red);
    }
    
    .pill-count {
        background: var(--corporate-red);
        color: white;
        padding: 2px 10px;
        font-weight: 800;
        font-size: 0.85rem;
    }

    /* Charts */
    .chart-card {
        background: var(--primary-white);
        padding: 1rem; /* Reduced padding */
        margin-bottom: 1.5rem;
        border: 1px solid var(--accent-soft-red);
    }
    
    .chart-wrapper {
        position: relative;
        height: 180px; /* Further reduced height for compactness */
        width: 100%;
    }

    /* Activities */
    .activity-feed {
        list-style: none;
        padding: 0;
    }
    
    .activity-item {
        padding: 1rem 0;
        border-bottom: 1px solid var(--accent-soft-red);
        display: flex;
        gap: 15px;
    }

    .activity-point {
        width: 10px;
        height: 10px;
        background: var(--corporate-red);
        margin-top: 6px;
        flex-shrink: 0;
    }

    .hover-shadow:hover {
        background: var(--accent-soft-red);
        border-color: var(--corporate-red) !important;
    }

    .no-radius {
        border-radius: 0 !important;
    }
    
    .text-corporate {
        color: var(--corporate-red) !important;
    }

    #extraActivities {
        display: none;
    }
</style>

<div class="dashboard-container">
    {{-- Header Banner --}}
    <div class="card welcome-banner no-radius shadow-sm mb-4">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h2 class="fw-bold mb-2 text-corporate">Welcome back, Administrator</h2>
                <p class="mb-0 text-muted">Here is a summary of the latest activities and system statistics for your school today.</p>
            </div>
            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                <a href="{{ route('manage_student') }}" class="btn btn-outline-danger shadow-sm no-radius px-4 py-2 fw-bold" style="border-width: 2px;">
                    <i class="bi bi-person-plus-fill me-2"></i> Register Student
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left: Stats & Charts --}}
        <div class="col-lg-9">
            
            {{-- Summary Widgets: Row 1 (Clean Grid Style) --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="p-4 bg-white border text-center hover-shadow h-100 transition no-radius d-flex flex-column justify-content-center">
                        <i class="bi bi-people text-corporate fs-3 mb-2"></i>
                        <div class="stat-label mb-1">Total Students</div>
                        <div class="stat-value text-dark">{{ number_format($dashboardStats['students_count'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 bg-white border text-center hover-shadow h-100 transition no-radius d-flex flex-column justify-content-center">
                        <i class="bi bi-person-badge text-corporate fs-3 mb-2"></i>
                        <div class="stat-label mb-1">Total Teachers</div>
                        <div class="stat-value text-dark">{{ number_format($dashboardStats['teachers_count'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 bg-white border text-center hover-shadow h-100 transition no-radius d-flex flex-column justify-content-center">
                        <i class="bi bi-journal-check text-corporate fs-3 mb-2"></i>
                        <div class="stat-label mb-1">Weekly Sessions</div>
                        <div class="stat-value text-dark">{{ $dashboardStats['sessions_per_week'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 bg-white border text-center hover-shadow h-100 transition no-radius d-flex flex-column justify-content-center">
                        <i class="bi bi-calendar-day text-corporate fs-3 mb-2"></i>
                        <div class="stat-label mb-1">Today's Collection</div>
                        <div class="stat-value text-dark" style="font-size: 1.1rem;">TZS {{ number_format($todayCollection) }}</div>
                    </div>
                </div>
            </div>

            {{-- Summary Widgets: Row 2 (Financials - Clean Grid Style) --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="p-4 bg-white border text-center hover-shadow h-100 transition no-radius d-flex flex-column justify-content-center">
                        <i class="bi bi-exclamation-circle text-danger fs-3 mb-2"></i>
                        <div class="stat-label mb-1">Unpaid Debtors</div>
                        <div class="stat-value text-dark">{{ number_format($studentsWithDebtCount) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 bg-white border text-center hover-shadow h-100 transition no-radius d-flex flex-column justify-content-center">
                        <i class="bi bi-patch-check text-success fs-3 mb-2"></i>
                        <div class="stat-label mb-1">Cleared Students</div>
                        <div class="stat-value text-dark">{{ number_format($clearedFeesCount) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 bg-white border text-center hover-shadow h-100 transition no-radius d-flex flex-column justify-content-center">
                        <i class="bi bi-cart-x text-corporate fs-3 mb-2"></i>
                        <div class="stat-label mb-1">{{ date('Y') }} Expenses</div>
                        <div class="stat-value text-dark" style="font-size: 1.1rem;">TZS {{ number_format($yearTotalExpenses) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 bg-white border text-center hover-shadow h-100 transition no-radius d-flex flex-column justify-content-center">
                        <i class="bi bi-piggy-bank text-corporate fs-3 mb-2"></i>
                        <div class="stat-label mb-1">{{ date('Y') }} Collection</div>
                        <div class="stat-value text-dark" style="font-size: 1.1rem;">TZS {{ number_format($yearTotalCollection) }}</div>
                    </div>
                </div>
            </div>

            {{-- Visualization --}}
            <div class="row mt-3">
                <div class="col-md-8">
                    <div class="card chart-card no-radius shadow-sm h-100">
                        <div class="section-header border-bottom mb-1 pb-1">
                            <i class="bi bi-graph-up-arrow me-2"></i> Fee Collection Trend (6 Months)
                        </div>
                        <div class="chart-wrapper mt-2" style="height: 220px;"> {{-- Balanced height --}}
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card chart-card no-radius shadow-sm h-100">
                        <div class="section-header border-bottom mb-1 pb-1">
                            <i class="bi bi-pie-chart-fill me-2"></i> Gender Distribution
                        </div>
                        <div class="chart-wrapper mt-2" style="height: 180px;">
                            <canvas id="genderPieChart"></canvas>
                        </div>
                        <div class="text-center mt-3 pt-2">
                            <span class="small text-muted"><i class="bi bi-circle-fill text-primary me-1"></i> Male: {{ $maleCount }}</span>
                            <span class="small text-muted ms-3"><i class="bi bi-circle-fill text-danger me-1"></i> Female: {{ $femaleCount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Navigation --}}
            <div class="mt-4">
                <div class="section-header mb-3"><i class="bi bi-grid-fill me-2"></i> Quick Access Modules</div>
                <div class="row row-cols-4 g-3">
                    @php
                        $navs = [
                            ['label' => 'Exams', 'icon' => 'bi-pencil-square', 'route' => route('manageExamination'), 'color' => 'text-primary'],
                            ['label' => 'Classes', 'icon' => 'bi-layers', 'route' => route('manageClasses'), 'color' => 'text-success'],
                            ['label' => 'Attendance', 'icon' => 'bi-calendar-check', 'route' => route('manageAttendance'), 'color' => 'text-warning'],
                            ['label' => 'Lesson Tasks', 'icon' => 'bi-list-check', 'route' => route('taskManagement'), 'color' => 'text-info'],
                        ];
                    @endphp
                    @foreach($navs as $nav)
                    <div class="col">
                        <a href="{{ $nav['route'] }}" class="text-decoration-none">
                            <div class="p-4 bg-white border text-center hover-shadow transition h-100">
                                <i class="bi {{ $nav['icon'] }} fs-3 {{ $nav['color'] }} mb-2"></i>
                                <div class="fw-bold text-dark small">{{ $nav['label'] }}</div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Right: Feeds --}}
        <div class="col-lg-3">
            
            {{-- Notifications --}}
            <div class="mb-4">
                <div class="section-header"><i class="bi bi-bell-fill me-2"></i> Alerts & Approvals</div>
                <div class="notification-pills">
                    <a href="{{ route('manageExamination') }}" class="text-decoration-none">
                        <div class="pill-item no-radius">
                            <span class="small fw-bold text-dark">Papers Pending Approval</span>
                            <span class="pill-count">{{ $pendingExams }}</span>
                        </div>
                    </a>
                    <a href="{{ route('manage_fees') }}" class="text-decoration-none">
                        <div class="pill-item no-radius">
                            <span class="small fw-bold text-dark">Outstanding Fee Dues</span>
                            <span class="pill-count">{{ $studentsWithDebtCount }}</span>
                        </div>
                    </a>
                    <a href="{{ route('manageResults') }}" class="text-decoration-none">
                        <div class="pill-item no-radius">
                            <span class="small fw-bold text-dark">Result Submissions</span>
                            <span class="pill-count">{{ $pendingResults }}</span>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="card no-radius shadow-sm border" style="border-top: 3px solid var(--corporate-red) !important;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold small text-corporate"><i class="bi bi-clock-history me-2"></i> RECENT ACTIVITIES</h6>
                </div>
                <div class="card-body py-1">
                    <ul class="activity-feed">
                        @foreach($activities as $index => $activity)
                            @if($index == 4)
                                <div id="extraActivities">
                            @endif
                            <li class="activity-item">
                                <div class="activity-point"></div>
                                <div>
                                    <div class="small fw-bold text-dark">{{ $activity->first_name ?? '' }} {{ $activity->last_name ?? ($activity->activity_title ?? 'Activity Recorded') }}</div>
                                    <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($activity->created_at)->locale('en')->diffForHumans() }}</div>
                                </div>
                            </li>
                        @endforeach
                        @if($activities->count() > 4)
                            </div>
                        @endif

                        @if($activities->isEmpty())
                        <li class="p-4 text-center text-muted small">No recent activity detected.</li>
                        @endif
                    </ul>
                </div>
                <div class="card-footer bg-white border-top-0 text-center pb-3">
                    <a href="javascript:void(0)" id="toggleActivities" class="small text-decoration-none text-corporate fw-bold">View All Activities <i class="bi bi-chevron-down ms-1"></i></a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function toggleRecentActivities() {
        const extra = document.getElementById('extraActivities');
        const btn = document.getElementById('toggleActivities');
        if (!extra) return;

        if (extra.style.display === 'block') {
            extra.style.display = 'none';
            btn.innerHTML = 'View All Activities <i class="bi bi-chevron-down ms-1"></i>';
        } else {
            extra.style.display = 'block';
            btn.innerHTML = 'Show Less <i class="bi bi-chevron-up ms-1"></i>';
        }
    }

    document.getElementById('toggleActivities').addEventListener('click', toggleRecentActivities);

    function initDashboardCharts() {
        if (typeof Chart === 'undefined') return;

        ['revenueChart', 'genderPieChart'].forEach(id => {
            const chart = Chart.getChart(id);
            if (chart) chart.destroy();
        });

        // Revenue Trend
        const ctxRev = document.getElementById('revenueChart');
        if (ctxRev) {
            new Chart(ctxRev.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlyCollections->pluck('month')) !!},
                    datasets: [{
                        label: 'Collection (TZS)',
                        data: {!! json_encode($monthlyCollections->pluck('total')) !!},
                        borderColor: '#940000',
                        backgroundColor: 'rgba(148, 0, 0, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#940000',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { left: 5, right: 15, top: 10, bottom: 5 } },
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: '#f1f1f1' }, 
                            ticks: { font: { size: 9 }, padding: 2 } 
                        },
                        x: { 
                            grid: { display: false }, 
                            ticks: { font: { size: 9 }, padding: 2 } 
                        }
                    }
                }
            });
        }

        // Gender Chart
        const ctxGen = document.getElementById('genderPieChart');
        if (ctxGen) {
            new Chart(ctxGen.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Male', 'Female'],
                    datasets: [{
                        data: [{{ $maleCount }}, {{ $femaleCount }}],
                        backgroundColor: ['#0d6efd', '#dc3545'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    layout: { padding: 0 },
                    plugins: { legend: { display: false } }
                }
            });
        }
    }

    if (document.readyState === 'complete') initDashboardCharts();
    else window.addEventListener('load', initDashboardCharts);
</script>

@include('includes.footer')
