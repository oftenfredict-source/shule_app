@php
    $bbRole = $bbAccess['role'] ?? 'admin';
    $bbPriv = $bbAccess['privileges'] ?? [];
    $canManageActors = !empty($bbAccess['can_manage_actors']);
@endphp
@if($bbRole === 'admin')
    @include('includes.Admin_nav')
@elseif(($bbAccess['actor_type'] ?? '') === 'teacher')
    @include('includes.teacher_nav')
@else
    @include('includes.staff_nav')
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    body, .content, .card, .btn, .form-control, .table { font-family: "Century Gothic", Arial, sans-serif; }
    .card, .btn, .form-control { border-radius: 0 !important; }
    .bg-primary-custom { background-color: #940000 !important; }
    .text-primary-custom { color: #940000 !important; }
    .btn-primary-custom { background: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background: #b30000; border-color: #b30000; color: #fff; }
    /* Force horizontal tabs, teacher_nav uses legacy Bootstrap without list-group-horizontal */
    .bb-menu {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap;
    }
    .bb-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
        text-align: center;
        flex: 1 1 auto;
        border-top-width: 1px;
        border-bottom-width: 1px;
        border-right-width: 0;
    }
    .bb-menu .list-group-item + .list-group-item {
        border-left-width: 0;
    }
    .bb-menu .list-group-item:first-child {
        border-left-width: 1px;
    }
    .bb-menu .list-group-item.active {
        border-left-color: #940000;
        background: #fff5f5;
        color: #940000;
        font-weight: 600;
        margin-top: 0;
    }
    .bb-section { display: none; }
    .bb-section.active { display: block; }
    .bb-stat { background: #fff5f5; border: 1px solid #f0d0d0; padding: 1rem; text-align: center; height: 100%; }
    .bb-stat h3 { color: #940000; margin: 0; font-weight: 700; }
    .bb-view-toggle .btn.active { background: #940000; color: #fff; border-color: #940000; }
    /* Mobile-first grid/list */
    .bb-data-wrapper .bb-list-view { display: none; }
    .bb-data-wrapper .bb-grid-view { display: flex; flex-wrap: wrap; }
    @media (min-width: 768px) {
        .bb-data-wrapper:not([data-active-view="grid"]) .bb-list-view { display: block; }
        .bb-data-wrapper:not([data-active-view="grid"]) .bb-grid-view { display: none; }
    }
    .bb-data-wrapper[data-active-view="list"] .bb-list-view { display: block !important; }
    .bb-data-wrapper[data-active-view="list"] .bb-grid-view { display: none !important; }
    .bb-data-wrapper[data-active-view="grid"] .bb-list-view { display: none !important; }
    .bb-data-wrapper[data-active-view="grid"] .bb-grid-view { display: flex !important; flex-wrap: wrap; }
    .bb-report-card {
        border: 1px solid #eee; padding: 14px; margin-bottom: 12px;
        background: #fff; height: 100%; border-left: 4px solid #940000;
    }
    .bb-report-card .bb-report-meta { font-size: 0.8rem; color: #888; margin-bottom: 6px; }
    .bb-report-card .bb-report-student { font-weight: 700; color: #333; font-size: 1rem; }
    .bb-report-card .bb-report-class { font-size: 0.85rem; color: #666; }
    .bb-report-card .bb-report-desc {
        font-size: 0.88rem; color: #444; margin: 8px 0;
        border-top: 1px dashed #eee; padding-top: 8px;
    }
    .bb-card-item { border: 1px solid #eee; padding: 12px; margin-bottom: 12px; background: #fff; height: 100%; }
    .bb-weight-badge { background: #940000; color: #fff; padding: 2px 8px; font-size: 0.75rem; }
    .bb-status-pending { color: #f39c12; font-weight: 600; }
    .bb-status-viewed { color: #3498db; font-weight: 600; }
    .bb-status-resolved { color: #27ae60; font-weight: 600; }
    .bb-status-monitored { color: #8e44ad; font-weight: 600; }
    .bb-monitoring-switch.disabled { opacity: 0.65; pointer-events: none; }
    .bb-draft-table th, .bb-draft-table td { vertical-align: middle; }
    .bb-draft-empty { color: #888; font-size: 0.9rem; }
    @media (max-width: 768px) {
        .bb-menu .list-group-item { font-size: 0.78rem; padding: 8px 4px; }
        .bb-menu .list-group-item i { display: block; margin-bottom: 2px; }
    }
    .select2-container--open { z-index: 10060 !important; }
    .select2-dropdown { z-index: 10061 !important; }
    .select2-container--bootstrap-5 .select2-selection { border-radius: 0 !important; min-height: 38px; }
    .select2-search--dropdown .select2-search__field { border-radius: 0 !important; }
    /* Black Book AJAX loader */
    .bb-ajax-bar {
        position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 20000;
        pointer-events: none; opacity: 0; transition: opacity 0.2s ease;
        background: rgba(148, 0, 0, 0.08);
    }
    .bb-ajax-bar.is-active { opacity: 1; }
    .bb-ajax-bar-fill {
        height: 100%; width: 0;
        background: linear-gradient(90deg, #940000, #ff4d4d, #940000);
        background-size: 200% 100%;
        box-shadow: 0 0 10px rgba(148, 0, 0, 0.55);
        transition: width 0.25s ease;
    }
    .bb-ajax-bar.is-active .bb-ajax-bar-fill {
        animation: bbBarRun 1.1s ease-in-out infinite, bbBarShine 1.2s linear infinite;
        width: 70%;
    }
    .bb-ajax-bar.is-finishing .bb-ajax-bar-fill {
        animation: none; width: 100% !important; transition: width 0.2s ease;
    }
    @keyframes bbBarRun {
        0% { margin-left: -40%; width: 30%; }
        50% { margin-left: 20%; width: 55%; }
        100% { margin-left: 100%; width: 30%; }
    }
    @keyframes bbBarShine {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .bb-ajax-overlay {
        position: fixed; inset: 0; z-index: 19999; display: none;
        align-items: center; justify-content: center;
        background: rgba(20, 0, 0, 0.28); backdrop-filter: blur(2px);
    }
    .bb-ajax-overlay.is-active { display: flex; }
    .bb-ajax-card {
        min-width: 220px; max-width: 320px; background: #fff;
        border: 2px solid #940000; box-shadow: 0 12px 40px rgba(148, 0, 0, 0.25);
        padding: 22px 24px 18px; text-align: center;
    }
    .bb-ajax-orbit { width: 52px; height: 52px; margin: 0 auto 12px; position: relative; }
    .bb-ajax-orbit span {
        position: absolute; inset: 0; border: 2px solid transparent;
        border-top-color: #940000; border-radius: 50%;
        animation: bbOrbit 0.9s linear infinite;
    }
    .bb-ajax-orbit span:nth-child(2) {
        inset: 8px; border-top-color: #c44;
        animation-duration: 0.7s; animation-direction: reverse;
    }
    .bb-ajax-orbit span:nth-child(3) {
        inset: 16px; border-top-color: #f0a0a0; animation-duration: 0.5s;
    }
    @keyframes bbOrbit { to { transform: rotate(360deg); } }
    .bb-ajax-text { font-weight: 700; color: #940000; font-size: 0.95rem; }
    .bb-ajax-sub {
        font-size: 0.75rem; color: #888; margin-top: 4px;
        letter-spacing: 0.04em; text-transform: uppercase;
    }
    body.bb-ajax-busy { cursor: progress; }
    body.bb-ajax-busy .btn-primary-custom,
    body.bb-ajax-busy #saveProblemCatBtn,
    body.bb-ajax-busy #savePunishCatBtn,
    body.bb-ajax-busy #confirmResolveBtn {
        pointer-events: none; opacity: 0.75;
    }
</style>

<div id="bbAjaxBar" class="bb-ajax-bar" aria-hidden="true">
    <div class="bb-ajax-bar-fill"></div>
</div>
<div id="bbAjaxOverlay" class="bb-ajax-overlay" aria-hidden="true">
    <div class="bb-ajax-card">
        <div class="bb-ajax-orbit"><span></span><span></span><span></span></div>
        <div class="bb-ajax-text" id="bbAjaxText">Working...</div>
        <div class="bb-ajax-sub">Please wait</div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
            <strong><i class="fa fa-book"></i> Black Book, Discipline Management</strong>
            <div class="d-flex flex-wrap" style="gap:8px;">
                @if($bbRole === 'admin' || ($bbAccess['actor_type'] ?? '') === 'teacher')
                <a href="{{ route('black_book.report_student') }}" class="btn btn-light btn-sm">
                    <i class="fa fa-exclamation-triangle"></i> Report Student
                </a>
                @endif
                @if($canManageActors)
                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#blackBookActorsModal">
                    <i class="fa fa-users"></i> Actors
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div id="bbAlert" class="alert d-none"></div>

            <div class="list-group list-group-horizontal bb-menu mb-3 flex-wrap">
                @if(!empty($bbPriv['view_dashboard']))
                <a class="list-group-item flex-fill {{ $section === 'dashboard' ? 'active' : '' }}" data-section="section-dashboard"><i class="fa fa-dashboard"></i> Dashboard</a>
                @endif
                @if(!empty($bbPriv['manage_problem_categories']))
                <a class="list-group-item flex-fill {{ $section === 'categories' ? 'active' : '' }}" data-section="section-categories"><i class="fa fa-tags"></i> Problem Categories</a>
                @endif
                @if(!empty($bbPriv['manage_punishment_categories']))
                <a class="list-group-item flex-fill {{ $section === 'punishments' ? 'active' : '' }}" data-section="section-punishments"><i class="fa fa-gavel"></i> Punishments</a>
                @endif
                @if(!empty($bbPriv['manage_pending']))
                <a class="list-group-item flex-fill {{ $section === 'pending' ? 'active' : '' }}" data-section="section-pending"><i class="fa fa-clock-o"></i> Pending <span class="badge badge-warning" id="pendingBadge">{{ $stats['pending'] }}</span></a>
                <a class="list-group-item flex-fill {{ $section === 'monitoring' ? 'active' : '' }}" data-section="section-monitoring"><i class="fa fa-eye"></i> Monitoring <span class="badge badge-purple" id="monitoringBadge" style="background:#8e44ad;">{{ $stats['monitored'] ?? 0 }}</span></a>
                @endif
                @if(!empty($bbPriv['view_resolved']))
                <a class="list-group-item flex-fill {{ $section === 'resolved' ? 'active' : '' }}" data-section="section-resolved"><i class="fa fa-check"></i> Resolved</a>
                @endif
                @if(!empty($bbPriv['view_reports']))
                <a class="list-group-item flex-fill {{ $section === 'reports' ? 'active' : '' }}" data-section="section-reports"><i class="fa fa-bar-chart"></i> Reports</a>
                @endif
            </div>

            {{-- Dashboard --}}
            <div id="section-dashboard" class="bb-section {{ $section === 'dashboard' ? 'active' : '' }}" @if(empty($bbPriv['view_dashboard'])) style="display:none;" @endif>
                <div class="row mb-3">
                    <div class="col-6 col-md-3 mb-2"><div class="bb-stat"><div class="small text-muted">Pending</div><h3 id="dashPending">{{ $stats['pending'] }}</h3></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="bb-stat"><div class="small text-muted">Viewed</div><h3 id="dashViewed">{{ $stats['viewed'] }}</h3></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="bb-stat"><div class="small text-muted">Resolved</div><h3 id="dashResolved">{{ $stats['resolved'] }}</h3></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="bb-stat"><div class="small text-muted">Total Reports</div><h3 id="dashTotal">{{ $stats['total'] }}</h3></div></div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <label class="font-weight-bold text-primary-custom">Search Student Report History</label>
                        <div class="input-group">
                            <input type="text" id="dashStudentSearch" class="form-control" placeholder="Search by name or admission number...">
                        </div>
                        <small class="text-muted">Students enrolled for the academic year selected in the header (Active preferred).</small>
                        <div id="dashStudentResults" class="list-group mt-2" style="max-height:200px;overflow:auto;"></div>
                        <div id="dashStudentSummary" class="mt-3 d-none"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:8px;">
                    <h6 class="text-primary-custom font-weight-bold mb-0">Most Reported Students</h6>
                    <div class="btn-group btn-group-sm bb-view-toggle" data-wrapper="dashboardTopWrapper">
                        <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i></button>
                    </div>
                </div>
                <div class="bb-data-wrapper" id="dashboardTopWrapper">
                    <div class="bb-list-view">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light"><tr><th>Student</th><th>Class</th><th>Times Reported</th><th></th></tr></thead>
                                <tbody id="topStudentsBody"><tr><td colspan="4" class="text-center text-muted">Loading...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="bb-grid-view row" id="dashboardTopGrid"></div>
                </div>
            </div>

            {{-- Problem Categories --}}
            <div id="section-categories" class="bb-section {{ $section === 'categories' ? 'active' : '' }}" @if(empty($bbPriv['manage_problem_categories'])) style="display:none;" @endif>
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
                    <h6 class="text-primary-custom font-weight-bold mb-0">Problem Categories</h6>
                    <div class="d-flex" style="gap:8px;">
                        <div class="btn-group btn-group-sm bb-view-toggle" data-wrapper="problemCatsWrapper">
                            <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i></button>
                            <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i></button>
                        </div>
                        <button class="btn btn-primary-custom btn-sm" data-toggle="collapse" data-target="#addProblemCatPanel"><i class="fa fa-plus"></i> Add Category</button>
                    </div>
                </div>
                <div id="addProblemCatPanel" class="collapse mb-3">
                    <div class="card card-body border">
                        <div class="row align-items-end">
                            <div class="col-md-5">
                                <label>Category Name</label>
                                <input type="text" id="newProblemName" class="form-control" placeholder="e.g. Conflict, Misbehaviour">
                            </div>
                            <div class="col-md-3">
                                <label>Weight (%)</label>
                                <input type="number" id="newProblemWeight" class="form-control" min="1" max="100" value="50" placeholder="100 = very serious">
                            </div>
                            <div class="col-md-4 d-flex flex-wrap" style="gap:6px;">
                                <button type="button" class="btn btn-outline-secondary" id="addProblemToDraftBtn"><i class="fa fa-plus"></i> Add Category</button>
                                <button type="button" class="btn btn-outline-secondary" id="addAnotherProblemBtn"><i class="fa fa-plus-circle"></i> Add Another</button>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="font-weight-bold text-primary-custom mb-2">Categories to Save</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm bb-draft-table mb-0">
                                    <thead class="bg-light">
                                        <tr><th style="width:50px;">#</th><th>Name</th><th style="width:100px;">Weight %</th><th style="width:80px;"></th></tr>
                                    </thead>
                                    <tbody id="problemDraftBody">
                                        <tr id="problemDraftEmpty"><td colspan="4" class="text-center bb-draft-empty">No categories added yet. Fill the form above and click Add Category.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            <small class="text-muted" id="problemDraftCount">0 categories ready to save</small>
                            <button type="button" class="btn btn-primary-custom" id="saveProblemCatBtn" disabled><i class="fa fa-save"></i> Save All Categories</button>
                        </div>
                    </div>
                </div>
                <div class="bb-data-wrapper" id="problemCatsWrapper">
                    <div class="bb-list-view"><div class="table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>Name</th><th>Weight %</th><th>Actions</th></tr></thead><tbody id="problemCatsListBody"></tbody></table></div></div>
                    <div class="bb-grid-view row" id="problemCatsGrid"></div>
                </div>
            </div>

            {{-- Punishment Categories --}}
            <div id="section-punishments" class="bb-section {{ $section === 'punishments' ? 'active' : '' }}" @if(empty($bbPriv['manage_punishment_categories'])) style="display:none;" @endif>
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
                    <h6 class="text-primary-custom font-weight-bold mb-0">Punishment Categories</h6>
                    <div class="d-flex" style="gap:8px;">
                        <div class="btn-group btn-group-sm bb-view-toggle" data-wrapper="punishCatsWrapper">
                            <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i></button>
                            <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i></button>
                        </div>
                        <button class="btn btn-primary-custom btn-sm" data-toggle="collapse" data-target="#addPunishCatPanel"><i class="fa fa-plus"></i> Add Punishment</button>
                    </div>
                </div>
                <div id="addPunishCatPanel" class="collapse mb-3">
                    <div class="card card-body border">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label>Category Name</label>
                                <input type="text" id="newPunishName" class="form-control" placeholder="e.g. Cleaning, Caning">
                            </div>
                            <div class="col-md-3">
                                <label>Punishment Type</label>
                                <select id="newPunishType" class="form-control">
                                    <option value="one_time">One Time</option>
                                    <option value="continuous">Continuous</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Monitoring</label>
                                <div class="custom-control custom-switch bb-monitoring-switch mt-2" id="newPunishMonitoringWrap">
                                    <input type="checkbox" class="custom-control-input" id="newPunishMonitoring">
                                    <label class="custom-control-label" for="newPunishMonitoring" id="newPunishMonitoringLabel">Off</label>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex flex-wrap align-items-end" style="gap:6px;">
                                <button type="button" class="btn btn-outline-secondary" id="addPunishToDraftBtn"><i class="fa fa-plus"></i> Add Category</button>
                                <button type="button" class="btn btn-outline-secondary" id="addAnotherPunishBtn"><i class="fa fa-plus-circle"></i> Add Another</button>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="font-weight-bold text-primary-custom mb-2">Punishments to Save</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm bb-draft-table mb-0">
                                    <thead class="bg-light">
                                        <tr><th style="width:50px;">#</th><th>Name</th><th>Type</th><th>Monitoring</th><th style="width:80px;"></th></tr>
                                    </thead>
                                    <tbody id="punishDraftBody">
                                        <tr id="punishDraftEmpty"><td colspan="5" class="text-center bb-draft-empty">No punishments added yet. Fill the form above and click Add Category.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            <small class="text-muted" id="punishDraftCount">0 punishments ready to save</small>
                            <button type="button" class="btn btn-primary-custom" id="savePunishCatBtn" disabled><i class="fa fa-save"></i> Save All Punishments</button>
                        </div>
                    </div>
                </div>
                <div class="bb-data-wrapper" id="punishCatsWrapper">
                    <div class="bb-list-view"><div class="table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>Name</th><th>Type</th><th>Monitoring</th><th>Actions</th></tr></thead><tbody id="punishCatsListBody"></tbody></table></div></div>
                    <div class="bb-grid-view row" id="punishCatsGrid"></div>
                </div>
            </div>

            {{-- Pending --}}
            <div id="section-pending" class="bb-section {{ $section === 'pending' ? 'active' : '' }}" @if(empty($bbPriv['manage_pending'])) style="display:none;" @endif>
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
                    <h6 class="text-primary-custom font-weight-bold mb-0">Pending & Viewed Problems</h6>
                    <div class="btn-group btn-group-sm bb-view-toggle" data-wrapper="pendingWrapper">
                        <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i></button>
                    </div>
                </div>
                <div class="bb-data-wrapper" id="pendingWrapper">
                    <div class="bb-list-view">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light"><tr><th>Date</th><th>Student</th><th>Class</th><th>Category</th><th>Reported By</th><th>Status</th><th>Actions</th></tr></thead>
                                <tbody id="pendingReportsBody"><tr><td colspan="7" class="text-center text-muted">Loading...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="bb-grid-view row" id="pendingReportsGrid"></div>
                </div>
            </div>

            {{-- Monitoring --}}
            <div id="section-monitoring" class="bb-section {{ $section === 'monitoring' ? 'active' : '' }}" @if(empty($bbPriv['manage_pending'])) style="display:none;" @endif>
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:8px;">
                    <div>
                        <h6 class="text-primary-custom font-weight-bold mb-0">Punishments Under Monitoring</h6>
                        <p class="text-muted small mb-0">Continuous punishments stay here until you click <strong>End Punishment</strong>.</p>
                    </div>
                    <div class="btn-group btn-group-sm bb-view-toggle" data-wrapper="monitoringWrapper">
                        <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i></button>
                    </div>
                </div>
                <div class="bb-data-wrapper" id="monitoringWrapper">
                    <div class="bb-list-view">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light"><tr><th>Date</th><th>Student</th><th>Class</th><th>Punishment</th><th>Deadline</th><th>Description</th><th>Actions</th></tr></thead>
                                <tbody id="monitoringReportsBody"><tr><td colspan="7" class="text-center text-muted">Loading...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="bb-grid-view row" id="monitoringReportsGrid"></div>
                </div>
            </div>

            {{-- Resolved --}}
            <div id="section-resolved" class="bb-section {{ $section === 'resolved' ? 'active' : '' }}" @if(empty($bbPriv['view_resolved'])) style="display:none;" @endif>
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
                    <h6 class="text-primary-custom font-weight-bold mb-0">Resolved Problems</h6>
                    <div class="btn-group btn-group-sm bb-view-toggle" data-wrapper="resolvedWrapper">
                        <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i></button>
                    </div>
                </div>
                <div class="bb-data-wrapper" id="resolvedWrapper">
                    <div class="bb-list-view">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light"><tr><th>Date</th><th>Student</th><th>Category</th><th>Resolved By</th><th>Method</th><th>Details</th></tr></thead>
                                <tbody id="resolvedReportsBody"><tr><td colspan="6" class="text-center text-muted">Loading...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="bb-grid-view row" id="resolvedReportsGrid"></div>
                </div>
            </div>

            {{-- Reports --}}
            <div id="section-reports" class="bb-section {{ $section === 'reports' ? 'active' : '' }}" @if(empty($bbPriv['view_reports'])) style="display:none;" @endif>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Report Type</label>
                        <select id="reportType" class="form-control">
                            <option value="all">All Students</option>
                            <option value="specific">Specific Student</option>
                            <option value="class">Class Students</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-none" id="reportStudentWrap">
                        <label>Search Student</label>
                        <input type="text" id="reportStudentSearch" class="form-control" placeholder="Search student...">
                        <input type="hidden" id="reportStudentID">
                        <div id="reportStudentResults" class="list-group mt-1" style="max-height:150px;overflow:auto;"></div>
                    </div>
                    <div class="col-md-3 d-none" id="reportClassWrap">
                        <label>Class</label>
                        <select id="reportClassID" class="form-control">
                            <option value="">Select class</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->classID }}">{{ $c->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary-custom btn-block" id="loadReportAnalyticsBtn"><i class="fa fa-search"></i> Load</button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="#" id="downloadReportPdfBtn" class="btn btn-outline-danger btn-block"><i class="fa fa-file-pdf-o"></i> PDF</a>
                    </div>
                </div>
                <div id="reportAnalyticsMsg" class="alert alert-info d-none"></div>
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:8px;">
                    <h6 class="text-primary-custom font-weight-bold mb-0">Report Results</h6>
                    <div class="btn-group btn-group-sm bb-view-toggle" data-wrapper="reportAnalyticsWrapper">
                        <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i></button>
                    </div>
                </div>
                <div class="bb-data-wrapper" id="reportAnalyticsWrapper">
                    <div class="bb-list-view">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light"><tr><th>Student</th><th>Class</th><th>Admission</th><th>Times Reported</th><th></th></tr></thead>
                                <tbody id="reportAnalyticsBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="bb-grid-view row" id="reportAnalyticsGrid"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($canManageActors)
    @include('Admin.partials.black_book_actors_modal')
@endif

{{-- Resolve Modal --}}
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:0;">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Resolve Problem</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="resolveReportID">
                <div id="resolveReportInfo" class="mb-3 p-2 bg-light border"></div>
                <div class="form-group">
                    <label>Resolve Method</label>
                    <select id="resolveMethod" class="form-control">
                        <option value="parent_call">Call Parent</option>
                        <option value="punishment">Punishment</option>
                    </select>
                </div>
                <div id="punishmentFields" class="d-none">
                    <div class="form-group">
                        <label>Punishment Category</label>
                        <select id="resolvePunishCategory" class="form-control bb-search-select">
                            <option value="">Search punishment category...</option>
                        </select>
                        <small class="text-muted" id="resolvePunishTypeHint"></small>
                    </div>
                    <div class="form-group d-none" id="resolveDeadlineWrap">
                        <label>Monitoring Deadline</label>
                        <input type="date" id="resolveMonitoringDeadline" class="form-control">
                        <small class="text-muted">Required for continuous punishments (daily/repeated tasks).</small>
                    </div>
                    <div class="form-group">
                        <label>Punishment Description</label>
                        <textarea id="resolvePunishDesc" class="form-control" rows="3" placeholder="e.g. Bring 50 bricks, Clean lab daily..."></textarea>
                    </div>
                </div>

                <div id="resolveParentSmsSection" class="border-top pt-3 mt-3">
                    <label class="font-weight-bold text-primary-custom d-block mb-2"><i class="fa fa-envelope"></i> Notify Parent via SMS</label>
                    <div class="alert alert-secondary py-2 mb-2" id="parentInfoBox"></div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold mb-1">SMS Message</label>
                        <textarea id="parentSmsMessage" class="form-control" rows="3" placeholder="Ujumbe wa SMS kwa mzazi (Kiswahili)..."></textarea>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="sendParentSms" checked>
                        <label class="custom-control-label font-weight-bold" for="sendParentSms" id="sendParentSmsLabel">Send SMS to parent, ON</label>
                    </div>
                    <small class="text-muted d-block mt-1">Turn OFF if you do not want the parent to receive an SMS for this resolution.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="confirmResolveBtn">Resolve</button>
            </div>
        </div>
    </div>
</div>

{{-- End Punishment Modal --}}
<div class="modal fade" id="endPunishmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:0;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa fa-check"></i> End Punishment</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="endPunishmentReportID">
                <div id="endPunishmentInfo" class="mb-3 p-2 bg-light border"></div>
                <p class="text-muted small mb-3">The student will be marked as <strong>fully resolved</strong> after you confirm.</p>
                <div class="border-top pt-3">
                    <label class="font-weight-bold text-primary-custom d-block mb-2"><i class="fa fa-envelope"></i> Notify Parent via SMS</label>
                    <div class="alert alert-secondary py-2 mb-2" id="endPunishmentParentInfo"></div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold mb-1">SMS Message</label>
                        <textarea id="endPunishmentSmsMessage" class="form-control" rows="3" placeholder="Ujumbe wa SMS kwa mzazi (Kiswahili)..."></textarea>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="endPunishmentSendSms" checked>
                        <label class="custom-control-label font-weight-bold" for="endPunishmentSendSms" id="endPunishmentSendSmsLabel">Send SMS to parent, ON</label>
                    </div>
                    <small class="text-muted d-block mt-1">Turn OFF if you do not want the parent to receive an SMS when punishment ends.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmEndPunishmentBtn"><i class="fa fa-check"></i> End Punishment</button>
            </div>
        </div>
    </div>
</div>

{{-- View More Modal --}}
<div class="modal fade" id="viewMoreModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:0;">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewMoreTitle">Student Reports</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="viewMoreBody"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const routes = {
        problemCats: @json(route('black_book.problem_categories.index')),
        storeProblem: @json(route('black_book.problem_categories.store')),
        updateProblem: (id) => @json(url('black_book/problem-categories')) + '/' + id + '/update',
        deleteProblem: (id) => @json(url('black_book/problem-categories')) + '/' + id + '/delete',
        punishCats: @json(route('black_book.punishment_categories.index')),
        storePunish: @json(route('black_book.punishment_categories.store')),
        updatePunish: (id) => @json(url('black_book/punishment-categories')) + '/' + id + '/update',
        deletePunish: (id) => @json(url('black_book/punishment-categories')) + '/' + id + '/delete',
        searchStudents: @json(route('black_book.students.search')),
        reports: @json(route('black_book.reports.index')),
        viewReport: (id) => @json(url('black_book/reports')) + '/' + id + '/view',
        resolveReport: (id) => @json(url('black_book/reports')) + '/' + id + '/resolve',
        endMonitoring: (id) => @json(url('black_book/reports')) + '/' + id + '/end-monitoring',
        studentSummary: @json(route('black_book.student_summary')),
        analytics: @json(route('black_book.report_analytics')),
        exportPdf: @json(route('black_book.export_pdf')),
        dashboardStats: @json(route('black_book.dashboard_stats')),
        bbActorsStore: @json(route('black_book.actors.store')),
        bbActorsUpdate: (id) => @json(url('black_book/actors')) + '/' + id + '/update',
        bbActorsDelete: (id) => @json(url('black_book/actors')) + '/' + id + '/delete',
    };

    const bbPriv = @json($bbPriv);
    const bbActorPickerTeachers = @json($actorPickerTeachers ?? []);
    const bbActorPickerStaff = @json($actorPickerStaff ?? []);
    let bbActorsState = @json($blackBookActors ?? []);
    let problemCategories = @json($problemCategories ?? []);
    let punishmentCategories = @json($punishmentCategories ?? []);
    let pendingReports = @json($initialPendingReports ?? []);
    let pendingReportsLoaded = pendingReports.length > 0;
    let problemCategoriesLoaded = problemCategories.length > 0;
    let punishmentCategoriesLoaded = punishmentCategories.length > 0;
    let currentResolveReport = null;
    let currentEndPunishmentReport = null;
    let monitoringReports = [];
    let resolvedReports = [];
    let reportAnalyticsRows = [];
    let topStudentsData = @json($stats['top_students'] ?? []);
    let problemDraft = [];
    let punishDraft = [];

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderProblemDraft() {
        const tbody = document.getElementById('problemDraftBody');
        const saveBtn = document.getElementById('saveProblemCatBtn');
        const countEl = document.getElementById('problemDraftCount');
        if (!problemDraft.length) {
            tbody.innerHTML = '<tr id="problemDraftEmpty"><td colspan="4" class="text-center bb-draft-empty">No categories added yet. Fill the form above and click Add Category.</td></tr>';
            saveBtn.disabled = true;
            countEl.textContent = '0 categories ready to save';
            return;
        }
        tbody.innerHTML = problemDraft.map((item, i) =>
            '<tr><td>' + (i + 1) + '</td><td>' + escapeHtml(item.name) + '</td><td>' + item.weight + '%</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-problem-draft" data-index="' + i + '"><i class="fa fa-times"></i></button></td></tr>'
        ).join('');
        saveBtn.disabled = false;
        countEl.textContent = problemDraft.length + ' categor' + (problemDraft.length === 1 ? 'y' : 'ies') + ' ready to save';
        tbody.querySelectorAll('.remove-problem-draft').forEach(btn => {
            btn.onclick = () => {
                problemDraft.splice(parseInt(btn.getAttribute('data-index'), 10), 1);
                renderProblemDraft();
            };
        });
    }

    function addProblemToDraft() {
        const name = document.getElementById('newProblemName').value.trim();
        const weight = parseInt(document.getElementById('newProblemWeight').value, 10);
        if (!name) return Swal.fire('Error', 'Enter category name', 'error');
        if (!weight || weight < 1 || weight > 100) return Swal.fire('Error', 'Weight must be between 1 and 100', 'error');
        if (problemDraft.some(c => c.name.toLowerCase() === name.toLowerCase())) {
            return Swal.fire('Error', 'This category is already in the list', 'error');
        }
        problemDraft.push({ name, weight });
        document.getElementById('newProblemName').value = '';
        document.getElementById('newProblemWeight').value = '50';
        renderProblemDraft();
        document.getElementById('newProblemName').focus();
    }

    document.getElementById('addProblemToDraftBtn').onclick = addProblemToDraft;
    document.getElementById('addAnotherProblemBtn').onclick = addProblemToDraft;
    document.getElementById('newProblemName').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); addProblemToDraft(); } });
    document.getElementById('newProblemWeight').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); addProblemToDraft(); } });

    function initResolvePunishSelect() {
        const $ = window.jQuery;
        if (!$ || !$.fn.select2) return;
        const $sel = $('#resolvePunishCategory');
        if (!$sel.length) return;
        if ($sel.hasClass('select2-hidden-accessible')) {
            try { $sel.select2('destroy'); } catch (e) {}
        }
        $sel.select2({
            theme: 'bootstrap-5',
            placeholder: 'Search punishment category...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#resolveModal'),
            minimumResultsForSearch: 0,
            language: {
                noResults: function() { return 'No punishment found'; },
                searching: function() { return 'Searching...'; }
            }
        });
        $sel.off('change.bbPunish').on('change.bbPunish', function() {
            updateResolvePunishmentUI();
            if (document.getElementById('resolveMethod').value === 'punishment') updateResolveSmsTemplate();
        });
    }

    function initBbSearchSelect($sel, options) {
        const $ = window.jQuery;
        if (!$ || !$.fn.select2 || !$sel || !$sel.length) return;
        if ($sel.hasClass('select2-hidden-accessible')) {
            try { $sel.select2('destroy'); } catch (e) {}
        }
        const defaults = {
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            language: {
                noResults: function() { return 'Hakuna matokeo'; },
                searching: function() { return 'Inatafuta...'; }
            }
        };
        $sel.select2(Object.assign(defaults, options || {}));
    }

    function initBbActorPersonTypeSelect() {
        initBbSearchSelect($('#bbActorPersonType'), {
            placeholder: 'Search and select type...',
            dropdownParent: $('#blackBookActorsModal'),
        });
    }

    function initBbActorPersonSelect() {
        const $sel = $('#bbActorPersonSelect');
        initBbSearchSelect($sel, {
            placeholder: $sel.prop('disabled') ? 'Select type first...' : 'Search and select person...',
            dropdownParent: $('#blackBookActorsModal'),
        });
    }

    function punishmentTypeLabel(type) {
        return type === 'continuous' ? 'Continuous' : 'One Time';
    }

    function syncPunishTypeMonitoringUI() {
        const type = document.getElementById('newPunishType').value;
        const monitoring = document.getElementById('newPunishMonitoring');
        const label = document.getElementById('newPunishMonitoringLabel');
        const wrap = document.getElementById('newPunishMonitoringWrap');
        if (type === 'continuous') {
            monitoring.checked = true;
            label.textContent = 'On';
            wrap.classList.add('disabled');
        } else {
            monitoring.checked = false;
            label.textContent = 'Off';
            wrap.classList.add('disabled');
        }
    }

    document.getElementById('newPunishType').addEventListener('change', syncPunishTypeMonitoringUI);
    syncPunishTypeMonitoringUI();

    function renderPunishDraft() {
        const tbody = document.getElementById('punishDraftBody');
        const saveBtn = document.getElementById('savePunishCatBtn');
        const countEl = document.getElementById('punishDraftCount');
        if (!punishDraft.length) {
            tbody.innerHTML = '<tr id="punishDraftEmpty"><td colspan="5" class="text-center bb-draft-empty">No punishments added yet. Fill the form above and click Add Category.</td></tr>';
            saveBtn.disabled = true;
            countEl.textContent = '0 punishments ready to save';
            return;
        }
        tbody.innerHTML = punishDraft.map((item, i) =>
            '<tr><td>' + (i + 1) + '</td><td>' + escapeHtml(item.name) + '</td><td>' + punishmentTypeLabel(item.punishment_type) + '</td>' +
            '<td>' + (item.requires_monitoring ? 'On' : 'Off') + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-punish-draft" data-index="' + i + '"><i class="fa fa-times"></i></button></td></tr>'
        ).join('');
        saveBtn.disabled = false;
        countEl.textContent = punishDraft.length + ' punishment' + (punishDraft.length === 1 ? '' : 's') + ' ready to save';
        tbody.querySelectorAll('.remove-punish-draft').forEach(btn => {
            btn.onclick = () => {
                punishDraft.splice(parseInt(btn.getAttribute('data-index'), 10), 1);
                renderPunishDraft();
            };
        });
    }

    function addPunishToDraft() {
        const name = document.getElementById('newPunishName').value.trim();
        const punishment_type = document.getElementById('newPunishType').value;
        const requires_monitoring = punishment_type === 'continuous';
        if (!name) return Swal.fire('Error', 'Enter punishment name', 'error');
        if (punishDraft.some(c => c.name.toLowerCase() === name.toLowerCase())) {
            return Swal.fire('Error', 'This punishment is already in the list', 'error');
        }
        punishDraft.push({ name, punishment_type, requires_monitoring });
        document.getElementById('newPunishName').value = '';
        document.getElementById('newPunishType').value = 'one_time';
        syncPunishTypeMonitoringUI();
        renderPunishDraft();
        document.getElementById('newPunishName').focus();
    }

    document.getElementById('addPunishToDraftBtn').onclick = addPunishToDraft;
    document.getElementById('addAnotherPunishBtn').onclick = addPunishToDraft;
    document.getElementById('newPunishName').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); addPunishToDraft(); } });

    function isMobile() { return window.matchMedia('(max-width: 767.98px)').matches; }

    function getBbEffectiveView(wrapperId) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return 'list';
        const forced = wrapper.getAttribute('data-active-view');
        if (forced === 'grid' || forced === 'list') return forced;
        return isMobile() ? 'grid' : 'list';
    }

    function syncBbViewToggle(toggleEl) {
        const wrapperId = toggleEl.getAttribute('data-wrapper');
        if (!wrapperId) return;
        const view = getBbEffectiveView(wrapperId);
        toggleEl.querySelectorAll('button').forEach(b => {
            b.classList.toggle('active', b.getAttribute('data-view') === view);
        });
    }

    function initBbViewToggles() {
        document.querySelectorAll('.bb-view-toggle').forEach(toggle => {
            const wrapperId = toggle.getAttribute('data-wrapper');
            const wrapper = wrapperId ? document.getElementById(wrapperId) : null;
            if (!wrapper) return;

            toggle.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', function() {
                    wrapper.setAttribute('data-active-view', this.getAttribute('data-view'));
                    wrapper.setAttribute('data-user-view', '1');
                    syncBbViewToggle(toggle);
                });
            });
            syncBbViewToggle(toggle);
        });

        window.addEventListener('resize', function() {
            document.querySelectorAll('.bb-data-wrapper').forEach(wrapper => {
                if (!wrapper.hasAttribute('data-user-view')) {
                    wrapper.removeAttribute('data-active-view');
                }
            });
            document.querySelectorAll('.bb-view-toggle').forEach(syncBbViewToggle);
        });
    }

    function bbReportCard(colHtml) {
        return '<div class="col-md-6 col-lg-4 col-12">' + colHtml + '</div>';
    }

    function getResolvedDetails(r) {
        let details = '-';
        if (r.resolve_method === 'parent_call') details = 'Parent called. SMS: ' + (r.parent_sms_sent ? 'Sent' : 'Not sent');
        if (r.resolve_method === 'punishment') {
            details = (r.punishment_category || '') + ', ' + (r.punishment_description || '');
            if (r.monitoring_ended_at) details += ' (Ended: ' + r.monitoring_ended_at + ')';
        }
        return details;
    }

    function bindViewStudentReports() {
        document.querySelectorAll('.view-student-reports').forEach(btn => {
            btn.onclick = () => loadStudentSummary(btn.getAttribute('data-id'));
        });
    }

    function renderDashboardTopStudents() {
        const list = document.getElementById('topStudentsBody');
        const grid = document.getElementById('dashboardTopGrid');
        if (!list || !grid) return;
        if (!topStudentsData.length) {
            list.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No reports yet.</td></tr>';
            grid.innerHTML = '<div class="col-12 text-center text-muted py-3">No reports yet.</div>';
            return;
        }
        list.innerHTML = topStudentsData.map(s =>
            '<tr><td>' + escapeHtml(s.student_name) + '</td><td>' + escapeHtml(s.class_name) + '</td>' +
            '<td><strong>' + s.report_count + '</strong></td>' +
            '<td><button class="btn btn-sm btn-outline-danger view-student-reports" data-id="' + s.studentID + '">View More</button></td></tr>'
        ).join('');
        grid.innerHTML = topStudentsData.map(s =>
            bbReportCard('<div class="bb-card-item"><div class="bb-report-student">' + escapeHtml(s.student_name) + '</div>' +
                '<div class="bb-report-class"><i class="fa fa-graduation-cap"></i> ' + escapeHtml(s.class_name) + '</div>' +
                '<div class="mt-2"><span class="bb-weight-badge">' + s.report_count + ' report' + (s.report_count === 1 ? '' : 's') + '</span></div>' +
                '<div class="mt-2"><button class="btn btn-sm btn-outline-danger view-student-reports" data-id="' + s.studentID + '">View More</button></div></div>')
        ).join('');
        bindViewStudentReports();
    }

    function showAlert(msg, type) {
        const el = document.getElementById('bbAlert');
        el.className = 'alert alert-' + (type || 'success');
        el.textContent = msg;
        el.classList.remove('d-none');
        setTimeout(() => el.classList.add('d-none'), 5000);
    }

    let bbAjaxActive = 0;
    let bbAjaxFinishTimer = null;
    let bbAjaxUseBarOnly = false;

    function isLightListUrl(url, method) {
        const m = (method || 'GET').toUpperCase();
        if (m !== 'GET') return false;
        const u = String(url || '').toLowerCase();
        return u.includes('problem-categories')
            || u.includes('punishment-categories')
            || (u.includes('/reports') && !u.includes('/resolve') && !u.includes('/view'))
            || u.includes('dashboard-stats')
            || u.includes('report-analytics');
    }

    function bbLoadingMessage(url, method) {
        const u = String(url || '').toLowerCase();
        if (u.includes('/delete')) return 'Deleting...';
        if (u.includes('/update')) return 'Updating...';
        if (u.includes('/resolve')) return 'Resolving problem...';
        if (u.includes('end-monitoring')) return 'Ending punishment...';
        if (u.includes('/view')) return 'Updating status...';
        if (u.includes('problem-categories')) return method === 'GET' ? 'Loading categories...' : 'Saving categories...';
        if (u.includes('punishment-categories')) return method === 'GET' ? 'Loading punishments...' : 'Saving punishments...';
        if (u.includes('/reports') && method === 'POST') return 'Submitting report...';
        if (u.includes('/reports')) return 'Loading reports...';
        if (u.includes('report-analytics') || u.includes('export-pdf')) return 'Loading report data...';
        if (u.includes('student-summary') || u.includes('students/search')) return 'Searching student...';
        if (u.includes('/actors')) return method === 'GET' ? 'Loading actors...' : 'Saving actor...';
        if (u.includes('dashboard-stats')) return 'Refreshing dashboard...';
        return method === 'GET' ? 'Loading...' : 'Saving...';
    }

    function bbLoadingStart(message, barOnly) {
        bbAjaxActive += 1;
        bbAjaxUseBarOnly = !!barOnly;
        clearTimeout(bbAjaxFinishTimer);
        const bar = document.getElementById('bbAjaxBar');
        const overlay = document.getElementById('bbAjaxOverlay');
        bar.classList.remove('is-finishing');
        bar.classList.add('is-active');
        bar.setAttribute('aria-hidden', 'false');
        document.getElementById('bbAjaxText').textContent = message || 'Working...';
        if (!bbAjaxUseBarOnly) {
            overlay.classList.add('is-active');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('bb-ajax-busy');
        }
    }

    function bbLoadingStop() {
        bbAjaxActive = Math.max(0, bbAjaxActive - 1);
        if (bbAjaxActive > 0) return;
        const barOnly = bbAjaxUseBarOnly;
        bbAjaxUseBarOnly = false;
        const bar = document.getElementById('bbAjaxBar');
        const overlay = document.getElementById('bbAjaxOverlay');
        bar.classList.add('is-finishing');
        if (!barOnly) {
            overlay.classList.remove('is-active');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('bb-ajax-busy');
        }
        bbAjaxFinishTimer = setTimeout(function() {
            bar.classList.remove('is-active', 'is-finishing');
            bar.setAttribute('aria-hidden', 'true');
        }, barOnly ? 120 : 220);
    }

    async function api(url, method, body, loadingOption) {
        const skipLoad = loadingOption === false;
        const barOnly = loadingOption === 'bar' || (!skipLoad && isLightListUrl(url, method || 'GET'));
        const msg = skipLoad ? null : (typeof loadingOption === 'string' ? loadingOption : bbLoadingMessage(url, method || 'GET'));
        if (!skipLoad) bbLoadingStart(msg, barOnly);
        try {
            const opts = { method: method || 'GET', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } };
            if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
            const res = await fetch(url, opts);
            return await res.json();
        } finally {
            if (!skipLoad) bbLoadingStop();
        }
    }

    function statusBadge(s) {
        const map = {
            resolved: ['bb-status-resolved', 'Resolved'],
            monitored: ['bb-status-monitored', 'Resolved (Monitored)'],
            viewed: ['bb-status-viewed', 'Viewed'],
            pending: ['bb-status-pending', 'Pending'],
        };
        const item = map[s] || map.pending;
        return '<span class="' + item[0] + '">' + item[1] + '</span>';
    }

    // Section tabs
    document.querySelectorAll('.bb-menu .list-group-item').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const sec = this.getAttribute('data-section');
            document.querySelectorAll('.bb-menu .list-group-item').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.bb-section').forEach(s => s.classList.remove('active'));
            document.getElementById(sec).classList.add('active');
            if (sec === 'section-pending') loadPendingReports();
            if (sec === 'section-monitoring') loadMonitoringReports();
            if (sec === 'section-resolved') loadResolvedReports();
            if (sec === 'section-categories') loadProblemCategories();
            if (sec === 'section-punishments') loadPunishmentCategories();
        });
    });

    // View toggle (all tabs)
    initBbViewToggles();

    function renderProblemCategories() {
        const grid = document.getElementById('problemCatsGrid');
        const list = document.getElementById('problemCatsListBody');
        grid.innerHTML = '';
        list.innerHTML = '';
        problemCategories.forEach(c => {
            const actions = '<button class="btn btn-sm btn-outline-primary edit-problem" data-id="' + c.categoryID + '">Edit</button> ' +
                '<button class="btn btn-sm btn-outline-danger del-problem" data-id="' + c.categoryID + '">Delete</button>';
            grid.innerHTML += '<div class="col-md-4 col-sm-6"><div class="bb-card-item"><strong>' + c.name + '</strong> <span class="bb-weight-badge">' + c.weight + '%</span><div class="mt-2">' + actions + '</div></div></div>';
            list.innerHTML += '<tr><td>' + c.name + '</td><td>' + c.weight + '%</td><td>' + actions + '</td></tr>';
        });
        bindProblemActions();
    }

    function bindProblemActions() {
        document.querySelectorAll('.edit-problem').forEach(btn => {
            btn.onclick = async () => {
                const id = btn.getAttribute('data-id');
                const cat = problemCategories.find(c => c.categoryID == id);
                const { value: form } = await Swal.fire({
                    title: 'Edit Problem Category',
                    html: '<input id="swal-name" class="swal2-input" value="' + cat.name + '"><input id="swal-weight" type="number" class="swal2-input" value="' + cat.weight + '" min="1" max="100">',
                    focusConfirm: false,
                    preConfirm: () => ({ name: document.getElementById('swal-name').value, weight: document.getElementById('swal-weight').value })
                });
                if (!form) return;
                const res = await api(routes.updateProblem(id), 'POST', form);
                if (res.success) { showAlert(res.message); loadProblemCategories(true); } else Swal.fire('Error', res.message, 'error');
            };
        });
        document.querySelectorAll('.del-problem').forEach(btn => {
            btn.onclick = async () => {
                const id = btn.getAttribute('data-id');
                const ok = await Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true });
                if (!ok.isConfirmed) return;
                const res = await api(routes.deleteProblem(id), 'POST');
                if (res.success) { showAlert(res.message); loadProblemCategories(true); } else Swal.fire('Error', res.message, 'error');
            };
        });
    }

    async function loadProblemCategories(force) {
        if (!force && problemCategoriesLoaded && problemCategories.length) {
            renderProblemCategories();
            return;
        }
        const res = await api(routes.problemCats);
        if (res.success) {
            problemCategories = res.categories;
            problemCategoriesLoaded = true;
            renderProblemCategories();
        }
    }

    document.getElementById('saveProblemCatBtn').onclick = async () => {
        const toSave = [...problemDraft];
        const pendingName = document.getElementById('newProblemName').value.trim();
        const pendingWeight = parseInt(document.getElementById('newProblemWeight').value, 10);
        if (pendingName) {
            if (!pendingWeight || pendingWeight < 1 || pendingWeight > 100) {
                return Swal.fire('Error', 'Weight must be between 1 and 100', 'error');
            }
            if (!toSave.some(c => c.name.toLowerCase() === pendingName.toLowerCase())) {
                toSave.push({ name: pendingName, weight: pendingWeight });
            }
        }
        if (!toSave.length) return Swal.fire('Error', 'Add at least one category first', 'error');
        const btn = document.getElementById('saveProblemCatBtn');
        btn.disabled = true;
        const res = await api(routes.storeProblem, 'POST', { categories: toSave });
        btn.disabled = false;
        if (res.success) {
            showAlert(res.message);
            problemDraft = [];
            renderProblemDraft();
            document.getElementById('newProblemName').value = '';
            document.getElementById('newProblemWeight').value = '50';
            $('#addProblemCatPanel').collapse('hide');
            loadProblemCategories(true);
        } else Swal.fire('Error', res.message, 'error');
    };

    function renderPunishmentCategories() {
        const grid = document.getElementById('punishCatsGrid');
        const list = document.getElementById('punishCatsListBody');
        grid.innerHTML = '';
        list.innerHTML = '';
        punishmentCategories.forEach(c => {
            const typeLabel = punishmentTypeLabel(c.punishment_type || 'one_time');
            const monLabel = c.requires_monitoring ? 'On' : 'Off';
            const actions = '<button class="btn btn-sm btn-outline-primary edit-punish" data-id="' + c.punishmentCategoryID + '">Edit</button> ' +
                '<button class="btn btn-sm btn-outline-danger del-punish" data-id="' + c.punishmentCategoryID + '">Delete</button>';
            grid.innerHTML += '<div class="col-md-4 col-sm-6"><div class="bb-card-item"><strong>' + escapeHtml(c.name) + '</strong><div class="small text-muted">' + typeLabel + ' · Monitoring: ' + monLabel + '</div><div class="mt-2">' + actions + '</div></div></div>';
            list.innerHTML += '<tr><td>' + escapeHtml(c.name) + '</td><td>' + typeLabel + '</td><td>' + monLabel + '</td><td>' + actions + '</td></tr>';
        });
        bindPunishActions();
    }

    function bindPunishActions() {
        document.querySelectorAll('.edit-punish').forEach(btn => {
            btn.onclick = async () => {
                const id = btn.getAttribute('data-id');
                const cat = punishmentCategories.find(c => c.punishmentCategoryID == id);
                const { value: form } = await Swal.fire({
                    title: 'Edit Punishment',
                    html: '<input id="swal-punish-name" class="swal2-input" placeholder="Name" value="' + escapeHtml(cat.name) + '">' +
                        '<select id="swal-punish-type" class="swal2-input"><option value="one_time"' + (cat.punishment_type === 'one_time' ? ' selected' : '') + '>One Time</option><option value="continuous"' + (cat.punishment_type === 'continuous' ? ' selected' : '') + '>Continuous</option></select>',
                    focusConfirm: false,
                    preConfirm: () => ({
                        name: document.getElementById('swal-punish-name').value,
                        punishment_type: document.getElementById('swal-punish-type').value,
                    }),
                });
                if (!form || !form.name) return;
                const res = await api(routes.updatePunish(id), 'POST', form);
                if (res.success) { showAlert(res.message); loadPunishmentCategories(true); } else Swal.fire('Error', res.message, 'error');
            };
        });
        document.querySelectorAll('.del-punish').forEach(btn => {
            btn.onclick = async () => {
                const id = btn.getAttribute('data-id');
                const ok = await Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true });
                if (!ok.isConfirmed) return;
                const res = await api(routes.deletePunish(id), 'POST');
                if (res.success) { showAlert(res.message); loadPunishmentCategories(true); } else Swal.fire('Error', res.message, 'error');
            };
        });
    }

    function populateResolvePunishOptions() {
        const sel = document.getElementById('resolvePunishCategory');
        sel.innerHTML = '<option value="">Search punishment category...</option>' + punishmentCategories.map(c =>
            '<option value="' + c.punishmentCategoryID + '" data-type="' + (c.punishment_type || 'one_time') + '">' +
            escapeHtml(c.name) + ' (' + punishmentTypeLabel(c.punishment_type || 'one_time') + ')</option>'
        ).join('');
        initResolvePunishSelect();
        updateResolvePunishmentUI();
    }

    async function loadPunishmentCategories(force) {
        if (!force && punishmentCategoriesLoaded && punishmentCategories.length) {
            renderPunishmentCategories();
            populateResolvePunishOptions();
            return;
        }
        const res = await api(routes.punishCats);
        if (res.success) {
            punishmentCategories = res.categories;
            punishmentCategoriesLoaded = true;
            renderPunishmentCategories();
            populateResolvePunishOptions();
        }
    }

    function updateResolvePunishmentUI() {
        const sel = document.getElementById('resolvePunishCategory');
        const val = sel.value;
        const opt = val ? sel.querySelector('option[value="' + val + '"]') : null;
        const type = opt ? (opt.getAttribute('data-type') || 'one_time') : 'one_time';
        const hint = document.getElementById('resolvePunishTypeHint');
        const deadlineWrap = document.getElementById('resolveDeadlineWrap');
        if (type === 'continuous') {
            hint.textContent = 'Continuous punishment, monitoring required until punishment ends.';
            deadlineWrap.classList.remove('d-none');
        } else {
            hint.textContent = 'One-time punishment, resolves immediately after assignment.';
            deadlineWrap.classList.add('d-none');
            document.getElementById('resolveMonitoringDeadline').value = '';
        }
    }

    $('#resolveModal').on('shown.bs.modal', initResolvePunishSelect);

    document.getElementById('savePunishCatBtn').onclick = async () => {
        const toSave = [...punishDraft];
        const pendingName = document.getElementById('newPunishName').value.trim();
        const punishment_type = document.getElementById('newPunishType').value;
        if (pendingName && !toSave.some(c => c.name.toLowerCase() === pendingName.toLowerCase())) {
            toSave.push({ name: pendingName, punishment_type, requires_monitoring: punishment_type === 'continuous' });
        }
        if (!toSave.length) return Swal.fire('Error', 'Add at least one punishment first', 'error');
        const btn = document.getElementById('savePunishCatBtn');
        btn.disabled = true;
        const res = await api(routes.storePunish, 'POST', { categories: toSave });
        btn.disabled = false;
        if (res.success) {
            showAlert(res.message);
            punishDraft = [];
            renderPunishDraft();
            document.getElementById('newPunishName').value = '';
            $('#addPunishCatPanel').collapse('hide');
            loadPunishmentCategories(true);
        } else Swal.fire('Error', res.message, 'error');
    };

    function renderPendingReportsTable() {
        const tbody = document.getElementById('pendingReportsBody');
        const grid = document.getElementById('pendingReportsGrid');
        const all = pendingReports;
        if (!all.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No pending problems.</td></tr>';
            grid.innerHTML = '<div class="col-12 text-center text-muted py-4">No pending problems.</div>';
            return;
        }
        tbody.innerHTML = all.map(r => '<tr>' +
            '<td>' + escapeHtml(r.created_at) + '</td><td>' + escapeHtml(r.student_name) + '</td><td>' + escapeHtml(r.class_name) + '</td>' +
            '<td>' + escapeHtml(r.problem_category) + ' (' + r.problem_weight + '%)</td><td>' + escapeHtml(r.reported_by_name) + '</td>' +
            '<td>' + statusBadge(r.status) + '</td>' +
            '<td><button class="btn btn-sm btn-outline-primary view-pending" data-id="' + r.reportID + '">View</button> ' +
            '<button class="btn btn-sm btn-primary-custom resolve-btn" data-id="' + r.reportID + '">Resolve</button></td></tr>').join('');
        grid.innerHTML = all.map(r => {
            const actions = '<button class="btn btn-sm btn-outline-primary view-pending" data-id="' + r.reportID + '">View</button> ' +
                '<button class="btn btn-sm btn-primary-custom resolve-btn" data-id="' + r.reportID + '">Resolve</button>';
            return bbReportCard('<div class="bb-report-card">' +
                '<div class="bb-report-meta"><i class="fa fa-clock-o"></i> ' + escapeHtml(r.created_at) + '</div>' +
                '<div class="bb-report-student">' + escapeHtml(r.student_name) + '</div>' +
                '<div class="bb-report-class"><i class="fa fa-graduation-cap"></i> ' + escapeHtml(r.class_name) + '</div>' +
                '<div class="mt-2"><span class="badge badge-light border">' + escapeHtml(r.problem_category) + ' (' + r.problem_weight + '%)</span></div>' +
                '<div class="small text-muted mt-1">By: ' + escapeHtml(r.reported_by_name) + '</div>' +
                '<div class="mt-2">' + statusBadge(r.status) + '</div>' +
                '<div class="bb-report-desc">' + escapeHtml((r.description || '').substring(0, 120)) + '</div>' +
                '<div class="mt-2">' + actions + '</div></div>');
        }).join('');
        bindPendingActions();
    }

    async function loadPendingReports(force) {
        if (!force && pendingReportsLoaded) {
            renderPendingReportsTable();
            return;
        }
        const res = await api(routes.reports + '?status=pending,viewed');
        if (!res.success) return;
        pendingReports = (res.reports || []).slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        pendingReportsLoaded = true;
        renderPendingReportsTable();
    }

    function bindPendingActions() {
        document.querySelectorAll('.view-pending').forEach(btn => {
            btn.onclick = async () => {
                const id = btn.getAttribute('data-id');
                await api(routes.viewReport(id), 'POST');
                pendingReportsLoaded = false;
                loadPendingReports(true);
            };
        });
        document.querySelectorAll('.resolve-btn').forEach(btn => {
            btn.onclick = () => openResolveModal(btn.getAttribute('data-id'));
        });
    }

    function renderMonitoringReports() {
        const tbody = document.getElementById('monitoringReportsBody');
        const grid = document.getElementById('monitoringReportsGrid');
        const rows = monitoringReports;
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No punishments under monitoring.</td></tr>';
            grid.innerHTML = '<div class="col-12 text-center text-muted py-4">No punishments under monitoring.</div>';
            return;
        }
        tbody.innerHTML = rows.map(r =>
            '<tr><td>' + escapeHtml(r.resolved_at || r.created_at) + '</td><td>' + escapeHtml(r.student_name) + '</td><td>' + escapeHtml(r.class_name) + '</td>' +
            '<td>' + escapeHtml(r.punishment_category || '-') + ' (' + punishmentTypeLabel(r.punishment_type) + ')</td>' +
            '<td>' + escapeHtml(r.monitoring_deadline || '-') + '</td><td>' + escapeHtml(r.punishment_description || '') + '</td>' +
            '<td><button class="btn btn-sm btn-success end-monitoring-btn" data-id="' + r.reportID + '"><i class="fa fa-check"></i> End Punishment</button></td></tr>'
        ).join('');
        grid.innerHTML = rows.map(r =>
            bbReportCard('<div class="bb-report-card">' +
                '<div class="bb-report-meta"><i class="fa fa-clock-o"></i> ' + escapeHtml(r.resolved_at || r.created_at) + '</div>' +
                '<div class="bb-report-student">' + escapeHtml(r.student_name) + '</div>' +
                '<div class="bb-report-class"><i class="fa fa-graduation-cap"></i> ' + escapeHtml(r.class_name) + '</div>' +
                '<div class="mt-2"><span class="badge badge-light border">' + escapeHtml(r.punishment_category || '-') + ' (' + punishmentTypeLabel(r.punishment_type) + ')</span></div>' +
                '<div class="small text-muted mt-1"><i class="fa fa-calendar"></i> Deadline: ' + escapeHtml(r.monitoring_deadline || '-') + '</div>' +
                '<div class="bb-report-desc">' + escapeHtml(r.punishment_description || '') + '</div>' +
                '<div class="mt-2"><button class="btn btn-sm btn-success end-monitoring-btn" data-id="' + r.reportID + '"><i class="fa fa-check"></i> End Punishment</button></div></div>')
        ).join('');
        document.querySelectorAll('.end-monitoring-btn').forEach(btn => {
            btn.onclick = () => openEndPunishmentModal(btn.getAttribute('data-id'));
        });
    }

    async function loadMonitoringReports() {
        const res = await api(routes.reports + '?status=monitored');
        monitoringReports = res.reports || [];
        renderMonitoringReports();
    }

    function syncEndPunishmentSmsToggle() {
        const on = document.getElementById('endPunishmentSendSms').checked;
        const label = document.getElementById('endPunishmentSendSmsLabel');
        const msg = document.getElementById('endPunishmentSmsMessage');
        if (label) label.textContent = on ? 'Send SMS to parent, ON' : 'Send SMS to parent, OFF';
        if (msg) msg.disabled = !on;
    }

    function bbParentGreeting(r) {
        if (r && r.parent_name) {
            const first = String(r.parent_name).trim().split(/\s+/)[0];
            if (first) return 'Habari ' + first;
        }
        return 'Habari mzazi';
    }

    function buildEndPunishmentSmsMessage(r) {
        const studentName = (r.student_name || '').split(' ')[0] || 'mtoto wako';
        const punishment = r.punishment_category || 'adhabu ya shule';
        let msg = bbParentGreeting(r) + ', adhabu ya (' + punishment + ') kwa mtoto wako ' + studentName + ' imekamilika. Kesi ya nidhamu imemalizika.';
        if (r.problem_category) msg += ' Tatizo lilikuwa: ' + r.problem_category + '.';
        msg += ' Tafadhali wasiliana na shule ikiwa una maswali.';
        return msg;
    }

    function openEndPunishmentModal(reportID) {
        const r = monitoringReports.find(x => x.reportID == reportID);
        if (!r) return;
        currentEndPunishmentReport = r;
        document.getElementById('endPunishmentReportID').value = reportID;
        document.getElementById('endPunishmentInfo').innerHTML =
            '<strong>' + escapeHtml(r.student_name) + '</strong> (' + escapeHtml(r.class_name) + ')<br>' +
            '<strong>Punishment:</strong> ' + escapeHtml(r.punishment_category || '-') + ' (' + punishmentTypeLabel(r.punishment_type) + ')<br>' +
            '<strong>Description:</strong> ' + escapeHtml(r.punishment_description || '-') + '<br>' +
            '<strong>Monitoring deadline:</strong> ' + escapeHtml(r.monitoring_deadline || '-');
        document.getElementById('endPunishmentParentInfo').innerHTML = r.parent_name
            ? '<strong>Parent:</strong> ' + escapeHtml(r.parent_name) + '<br><strong>Phone:</strong> ' + escapeHtml(r.parent_phone || 'Not available')
            : 'No parent record linked to this student.';
        document.getElementById('endPunishmentSendSms').checked = true;
        document.getElementById('endPunishmentSmsMessage').value = buildEndPunishmentSmsMessage(r);
        syncEndPunishmentSmsToggle();
        $('#endPunishmentModal').modal('show');
    }

    document.getElementById('endPunishmentSendSms').addEventListener('change', syncEndPunishmentSmsToggle);

    document.getElementById('confirmEndPunishmentBtn').onclick = async () => {
        const id = document.getElementById('endPunishmentReportID').value;
        const sendSms = document.getElementById('endPunishmentSendSms').checked;
        const smsMessage = document.getElementById('endPunishmentSmsMessage').value.trim();
        if (sendSms && !smsMessage) return Swal.fire('Error', 'Enter SMS message or turn OFF parent notification', 'error');
        const res2 = await api(routes.endMonitoring(id), 'POST', {
            send_parent_sms: sendSms,
            parent_sms_message: smsMessage,
        });
        if (res2.success) {
            $('#endPunishmentModal').modal('hide');
            showAlert(res2.message);
            loadMonitoringReports();
            loadResolvedReports();
            refreshDashboard();
        } else Swal.fire('Error', res2.message, 'error');
    };

    function renderResolvedReports() {
        const tbody = document.getElementById('resolvedReportsBody');
        const grid = document.getElementById('resolvedReportsGrid');
        const rows = resolvedReports;
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No resolved problems.</td></tr>';
            grid.innerHTML = '<div class="col-12 text-center text-muted py-4">No resolved problems.</div>';
            return;
        }
        tbody.innerHTML = rows.map(r => {
            const details = getResolvedDetails(r);
            const method = r.resolve_method === 'parent_call' ? 'Parent Call' : 'Punishment';
            return '<tr><td>' + escapeHtml(r.resolved_at || r.created_at) + '</td><td>' + escapeHtml(r.student_name) + '</td><td>' + escapeHtml(r.problem_category) + '</td>' +
                '<td>' + escapeHtml(r.resolved_by_name || '-') + '</td><td>' + method + '</td><td>' + escapeHtml(details) + '</td></tr>';
        }).join('');
        grid.innerHTML = rows.map(r => {
            const details = getResolvedDetails(r);
            const method = r.resolve_method === 'parent_call' ? 'Parent Call' : 'Punishment';
            return bbReportCard('<div class="bb-report-card">' +
                '<div class="bb-report-meta"><i class="fa fa-clock-o"></i> ' + escapeHtml(r.resolved_at || r.created_at) + '</div>' +
                '<div class="bb-report-student">' + escapeHtml(r.student_name) + '</div>' +
                '<div class="mt-2"><span class="badge badge-light border">' + escapeHtml(r.problem_category) + '</span></div>' +
                '<div class="small text-muted mt-1">Resolved by: ' + escapeHtml(r.resolved_by_name || '-') + '</div>' +
                '<div class="small mt-1"><strong>Method:</strong> ' + method + '</div>' +
                '<div class="bb-report-desc">' + escapeHtml(details) + '</div></div>');
        }).join('');
    }

    async function loadResolvedReports() {
        const res = await api(routes.reports + '?status=resolved');
        resolvedReports = res.reports || [];
        renderResolvedReports();
    }

    function syncResolveParentSmsToggle() {
        const on = document.getElementById('sendParentSms').checked;
        const label = document.getElementById('sendParentSmsLabel');
        const msg = document.getElementById('parentSmsMessage');
        if (label) label.textContent = on ? 'Send SMS to parent, ON' : 'Send SMS to parent, OFF';
        if (msg) msg.disabled = !on;
    }

    function updateResolveSmsTemplate() {
        if (!currentResolveReport) return;
        const r = currentResolveReport;
        const studentName = (r.student_name || '').split(' ')[0] || 'mtoto wako';
        const greeting = bbParentGreeting(r);
        const method = document.getElementById('resolveMethod').value;
        let msg = '';
        if (method === 'parent_call') {
            msg = greeting + ', mtoto wako ' + studentName + ' ameripotiwa kwa ' + r.problem_category + '. Tafadhali wasiliana na shule.';
        } else {
            const sel = document.getElementById('resolvePunishCategory');
            const opt = sel.options[sel.selectedIndex];
            const punLabel = opt && opt.value ? (opt.textContent.split(' (')[0] || 'adhabu ya shule') : 'adhabu ya shule';
            const desc = document.getElementById('resolvePunishDesc').value.trim();
            const deadline = document.getElementById('resolveMonitoringDeadline').value;
            msg = greeting + ', mtoto wako ' + studentName + ' ameripotiwa kwa ' + r.problem_category + '. Adhabu: ' + punLabel;
            if (desc) msg += ', ' + desc;
            if (deadline) msg += '. Utafatiliwa hadi ' + deadline + '.';
            msg += ' Tafadhali wasiliana na shule.';
        }
        document.getElementById('parentSmsMessage').value = msg;
    }

    function openResolveModal(reportID) {
        const r = pendingReports.find(x => x.reportID == reportID);
        if (!r) return;
        currentResolveReport = r;
        document.getElementById('resolveReportID').value = reportID;
        document.getElementById('resolveMethod').value = 'punishment';
        document.getElementById('punishmentFields').classList.remove('d-none');
        document.getElementById('resolvePunishDesc').value = '';
        document.getElementById('resolveMonitoringDeadline').value = '';
        document.getElementById('resolveReportInfo').innerHTML = '<strong>' + r.student_name + '</strong> (' + r.class_name + ')<br>Category: ' + r.problem_category + '<br>' + r.description;
        document.getElementById('parentInfoBox').innerHTML = r.parent_name
            ? '<strong>Parent:</strong> ' + r.parent_name + '<br><strong>Phone:</strong> ' + (r.parent_phone || 'Not available')
            : 'No parent record linked to this student.';
        document.getElementById('sendParentSms').checked = true;
        syncResolveParentSmsToggle();
        updateResolvePunishmentUI();
        updateResolveSmsTemplate();
        $('#resolveModal').modal('show');
    }

    document.getElementById('resolveMethod').onchange = function() {
        const isParent = this.value === 'parent_call';
        document.getElementById('punishmentFields').classList.toggle('d-none', isParent);
        updateResolveSmsTemplate();
    };

    document.getElementById('sendParentSms').addEventListener('change', syncResolveParentSmsToggle);
    document.getElementById('resolvePunishDesc').addEventListener('input', function() {
        if (document.getElementById('resolveMethod').value === 'punishment') updateResolveSmsTemplate();
    });
    document.getElementById('resolveMonitoringDeadline').addEventListener('change', function() {
        if (document.getElementById('resolveMethod').value === 'punishment') updateResolveSmsTemplate();
    });

    document.getElementById('confirmResolveBtn').onclick = async () => {
        const id = document.getElementById('resolveReportID').value;
        const method = document.getElementById('resolveMethod').value;
        const sendSms = document.getElementById('sendParentSms').checked;
        const smsMessage = document.getElementById('parentSmsMessage').value.trim();
        const payload = {
            resolve_method: method,
            send_parent_sms: sendSms,
            parent_sms_message: smsMessage,
        };
        if (sendSms && !smsMessage) return Swal.fire('Error', 'Enter SMS message or turn OFF parent notification', 'error');
        if (method === 'punishment') {
            payload.punishment_categoryID = document.getElementById('resolvePunishCategory').value;
            if (!payload.punishment_categoryID) return Swal.fire('Error', 'Select punishment category', 'error');
            payload.punishment_description = document.getElementById('resolvePunishDesc').value;
            const sel = document.getElementById('resolvePunishCategory');
            const opt = sel.querySelector('option[value="' + payload.punishment_categoryID + '"]');
            if (opt && opt.getAttribute('data-type') === 'continuous') {
                payload.monitoring_deadline = document.getElementById('resolveMonitoringDeadline').value;
                if (!payload.monitoring_deadline) return Swal.fire('Error', 'Enter monitoring deadline for continuous punishment', 'error');
            }
        }
        const res = await api(routes.resolveReport(id), 'POST', payload);
        if (res.success) {
            $('#resolveModal').modal('hide');
            showAlert(res.message);
            pendingReportsLoaded = false;
            loadPendingReports(true);
            loadMonitoringReports();
            loadResolvedReports();
            refreshDashboard();
        } else Swal.fire('Error', res.message, 'error');
    };

    // Student search (dashboard)
    let dashSearchTimer;
    document.getElementById('dashStudentSearch').oninput = function() {
        clearTimeout(dashSearchTimer);
        const q = this.value.trim();
        if (q.length < 2) { document.getElementById('dashStudentResults').innerHTML = ''; return; }
        dashSearchTimer = setTimeout(async () => {
            const res = await api(routes.searchStudents + '?search=' + encodeURIComponent(q), 'GET', null, false);
            const box = document.getElementById('dashStudentResults');
            if (res.success === false) {
                box.innerHTML = '<div class="list-group-item text-muted small">' + (res.message || 'Search failed') + '</div>';
                return;
            }
            box.innerHTML = (res.students || []).map(s => {
                const locked = s.can_report === false;
                const style = locked ? 'opacity:0.7;' : '';
                return '<button type="button" class="list-group-item list-group-item-action dash-pick-student" style="' + style + '" data-id="' + s.studentID + '">' +
                    escapeHtml(s.display) +
                    (s.enrollment_status ? ' <span class="badge badge-' + (s.can_report ? 'success' : 'secondary') + '">' + escapeHtml(s.enrollment_status) + '</span>' : '') +
                    '</button>';
            }).join('') || '<div class="list-group-item text-muted small">No enrolled students matched for the selected year.</div>';
            box.querySelectorAll('.dash-pick-student').forEach(b => {
                b.onclick = () => loadStudentSummary(b.getAttribute('data-id'));
            });
        }, 300);
    };

    async function loadStudentSummary(studentID) {
        const res = await api(routes.studentSummary + '?studentID=' + studentID);
        const box = document.getElementById('dashStudentSummary');
        if (!res.success) return;
        if (!res.student.report_count) {
            box.classList.remove('d-none');
            box.innerHTML = '<div class="alert alert-warning">This student has not been reported yet.</div>';
            return;
        }
        showViewMore(res.student.student_name, res.reports);
        box.classList.add('d-none');
    }

    function showViewMore(title, reports) {
        document.getElementById('viewMoreTitle').textContent = title + ', All Reports';
        document.getElementById('viewMoreBody').innerHTML = '<table class="table table-sm table-bordered"><thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Status</th><th>Resolution</th></tr></thead><tbody>' +
            reports.map(r => {
                let res = '-';
                if (r.status === 'resolved' || r.status === 'monitored') {
                    res = r.resolve_method === 'parent_call' ? 'Parent Call' : ('Punishment: ' + (r.punishment_category || '') + ' ' + (r.punishment_description || ''));
                    if (r.status === 'monitored') res += ' [Under Monitoring]';
                }
                return '<tr><td>' + r.created_at + '</td><td>' + r.problem_category + '</td><td>' + r.description + '</td><td>' + statusBadge(r.status) + '</td><td>' + res + '</td></tr>';
            }).join('') + '</tbody></table>';
        $('#viewMoreModal').modal('show');
    }

    // Reports analytics
    document.getElementById('reportType').onchange = function() {
        const v = this.value;
        document.getElementById('reportStudentWrap').classList.toggle('d-none', v !== 'specific');
        document.getElementById('reportClassWrap').classList.toggle('d-none', v !== 'class');
    };

    let reportSearchTimer;
    document.getElementById('reportStudentSearch').oninput = function() {
        clearTimeout(reportSearchTimer);
        const q = this.value.trim();
        if (q.length < 2) return;
        reportSearchTimer = setTimeout(async () => {
            const res = await api(routes.searchStudents + '?search=' + encodeURIComponent(q), 'GET', null, false);
            document.getElementById('reportStudentResults').innerHTML = (res.students || []).map(s =>
                '<button type="button" class="list-group-item list-group-item-action report-pick-student" data-id="' + s.studentID + '">' +
                escapeHtml(s.display) +
                (s.enrollment_status ? ' <span class="badge badge-' + (s.can_report ? 'success' : 'secondary') + '">' + escapeHtml(s.enrollment_status) + '</span>' : '') +
                '</button>'
            ).join('') || '<div class="list-group-item text-muted small">No enrolled students matched for the selected year.</div>';
            document.querySelectorAll('.report-pick-student').forEach(b => {
                b.onclick = () => {
                    document.getElementById('reportStudentID').value = b.getAttribute('data-id');
                    document.getElementById('reportStudentSearch').value = b.textContent;
                    document.getElementById('reportStudentResults').innerHTML = '';
                };
            });
        }, 300);
    };

    document.getElementById('loadReportAnalyticsBtn').onclick = loadReportAnalytics;

    function bindViewMoreAnalytics() {
        document.querySelectorAll('.view-more-analytics').forEach(btn => {
            btn.onclick = () => showViewMore(btn.getAttribute('data-name'), JSON.parse(btn.getAttribute('data-json')));
        });
    }

    function renderReportAnalytics() {
        const tbody = document.getElementById('reportAnalyticsBody');
        const grid = document.getElementById('reportAnalyticsGrid');
        const rows = reportAnalyticsRows;
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No data.</td></tr>';
            grid.innerHTML = '<div class="col-12 text-center text-muted py-4">No data.</div>';
            return;
        }
        tbody.innerHTML = rows.map(r => '<tr><td>' + escapeHtml(r.student_name) + '</td><td>' + escapeHtml(r.class_name) + '</td><td>' + escapeHtml(r.admission_number || '-') + '</td>' +
            '<td><strong>' + r.report_count + '</strong></td><td><button class="btn btn-sm btn-outline-danger view-more-analytics" data-json=\'' + JSON.stringify(r.reports).replace(/'/g, '&#39;') + '\' data-name="' + escapeHtml(r.student_name) + '">View More</button></td></tr>').join('');
        grid.innerHTML = rows.map(r =>
            bbReportCard('<div class="bb-card-item"><div class="bb-report-student">' + escapeHtml(r.student_name) + '</div>' +
                '<div class="bb-report-class"><i class="fa fa-graduation-cap"></i> ' + escapeHtml(r.class_name) + '</div>' +
                '<div class="small text-muted">Adm: ' + escapeHtml(r.admission_number || '-') + '</div>' +
                '<div class="mt-2"><span class="bb-weight-badge">' + r.report_count + ' report' + (r.report_count === 1 ? '' : 's') + '</span></div>' +
                '<div class="mt-2"><button class="btn btn-sm btn-outline-danger view-more-analytics" data-json=\'' + JSON.stringify(r.reports).replace(/'/g, '&#39;') + '\' data-name="' + escapeHtml(r.student_name) + '">View More</button></div></div>')
        ).join('');
        bindViewMoreAnalytics();
    }

    async function loadReportAnalytics() {
        const type = document.getElementById('reportType').value;
        let url = routes.analytics + '?type=' + type;
        if (type === 'specific') url += '&studentID=' + document.getElementById('reportStudentID').value;
        if (type === 'class') url += '&classID=' + document.getElementById('reportClassID').value;
        const res = await api(url);
        const msg = document.getElementById('reportAnalyticsMsg');
        if (res.message) { msg.textContent = res.message; msg.classList.remove('d-none'); } else msg.classList.add('d-none');
        reportAnalyticsRows = res.rows || [];
        renderReportAnalytics();
    }

    document.getElementById('downloadReportPdfBtn').onclick = function(e) {
        e.preventDefault();
        const type = document.getElementById('reportType').value;
        let url = routes.exportPdf + '?type=' + type;
        if (type === 'specific') url += '&studentID=' + document.getElementById('reportStudentID').value;
        if (type === 'class') url += '&classID=' + document.getElementById('reportClassID').value;
        window.open(url, '_blank');
    };

    async function refreshDashboard() {
        const badge = document.getElementById('pendingBadge');
        if (!badge) return;
        const res = await api(routes.dashboardStats);
        if (res.success) {
            document.getElementById('dashPending').textContent = res.stats.pending;
            document.getElementById('dashViewed').textContent = res.stats.viewed;
            document.getElementById('dashResolved').textContent = res.stats.resolved;
            document.getElementById('dashTotal').textContent = res.stats.total;
            document.getElementById('pendingBadge').textContent = res.stats.pending;
            const monBadge = document.getElementById('monitoringBadge');
            if (monBadge) monBadge.textContent = res.stats.monitored || 0;
            if (Array.isArray(res.stats.top_students)) {
                topStudentsData = res.stats.top_students;
                renderDashboardTopStudents();
            }
        }
    }

    // Init, use server-preloaded data when available (no extra API wait)
    const initSection = @json($section);
    if (problemCategories.length) renderProblemCategories();
    if (punishmentCategories.length) {
        renderPunishmentCategories();
        populateResolvePunishOptions();
    }
    if (pendingReports.length) renderPendingReportsTable();
    renderDashboardTopStudents();

    if (initSection === 'categories' && bbPriv.manage_problem_categories && !problemCategories.length) loadProblemCategories();
    if (initSection === 'punishments' && bbPriv.manage_punishment_categories && !punishmentCategories.length) loadPunishmentCategories();
    if (initSection === 'pending' && bbPriv.manage_pending && !pendingReports.length) loadPendingReports();
    if (initSection === 'monitoring' && bbPriv.manage_pending) loadMonitoringReports();
    if (initSection === 'resolved' && bbPriv.view_resolved) loadResolvedReports();
    if ((bbPriv.manage_punishment_categories || bbPriv.manage_pending) && !punishmentCategories.length) loadPunishmentCategories();
    renderProblemDraft();
    renderPunishDraft();

    @if($canManageActors)
    function getBbActorPrivileges() {
        const privileges = {};
        document.querySelectorAll('.bb-actor-privilege-check').forEach(cb => {
            privileges[cb.value] = cb.checked;
        });
        return privileges;
    }

    function setBbActorPrivileges(privileges) {
        document.querySelectorAll('.bb-actor-privilege-check').forEach(cb => {
            cb.checked = !!(privileges && privileges[cb.value]);
        });
    }

    function resetBbActorForm() {
        const $ = window.jQuery;
        document.getElementById('bbActorEditId').value = '';
        if ($ && $.fn.select2) {
            if ($('#bbActorPersonType').hasClass('select2-hidden-accessible')) {
                try { $('#bbActorPersonType').val('').trigger('change.select2'); } catch (e) {
                    document.getElementById('bbActorPersonType').value = '';
                }
            } else {
                document.getElementById('bbActorPersonType').value = '';
            }
        } else {
            document.getElementById('bbActorPersonType').value = '';
        }
        if ($ && $('#bbActorPersonSelect').hasClass('select2-hidden-accessible')) {
            try { $('#bbActorPersonSelect').select2('destroy'); } catch (e) {}
        }
        document.getElementById('bbActorPersonSelect').innerHTML = '<option value="">Select type first...</option>';
        document.getElementById('bbActorPersonSelect').disabled = true;
        document.getElementById('bbActorPersonFields').style.display = '';
        setBbActorPrivileges({});
        document.getElementById('btnSaveBbActor').innerHTML = '<i class="fa fa-save"></i> Save Actor Access';
        initBbActorPersonTypeSelect();
        initBbActorPersonSelect();
    }

    function populateBbActorPersonSelect(type) {
        const sel = document.getElementById('bbActorPersonSelect');
        const $ = window.jQuery;
        if ($ && $('#bbActorPersonSelect').hasClass('select2-hidden-accessible')) {
            try { $('#bbActorPersonSelect').select2('destroy'); } catch (e) {}
        }
        const list = type === 'teacher' ? bbActorPickerTeachers : (type === 'staff' ? bbActorPickerStaff : []);
        sel.innerHTML = '<option value="">Search and select person...</option>';
        list.forEach(p => {
            sel.innerHTML += '<option value="' + p.id + '">' + escapeHtml(p.name) + (p.employee_number ? ' (' + escapeHtml(p.employee_number) + ')' : '') + '</option>';
        });
        sel.disabled = !type;
        initBbActorPersonSelect();
    }

    $('#bbActorPersonType').on('change.bbActor', function() {
        populateBbActorPersonSelect(this.value);
    });

    document.getElementById('bbActorSelectAllPriv').onclick = function() {
        document.querySelectorAll('.bb-actor-privilege-check').forEach(cb => { cb.checked = true; });
    };
    document.getElementById('bbActorClearPriv').onclick = function() {
        document.querySelectorAll('.bb-actor-privilege-check').forEach(cb => { cb.checked = false; });
    };

    $('#blackBookActorsModal').on('shown.bs.modal', function() {
        document.getElementById('bbActorModalFooter').style.display = '';
        initBbActorPersonTypeSelect();
        initBbActorPersonSelect();
    });
    $('a[href="#bbActorTabAdd"], #bbActorTabAddLink').on('shown.bs.tab', function() {
        resetBbActorForm();
        document.getElementById('bbActorModalFooter').style.display = '';
    });
    $('a[href="#bbActorTabList"]').on('shown.bs.tab', function() {
        document.getElementById('bbActorModalFooter').style.display = 'none';
    });

    document.querySelectorAll('.btn-edit-bb-actor').forEach(btn => {
        btn.onclick = function() {
            const id = this.getAttribute('data-id');
            const actor = bbActorsState.find(a => a.actorID == id);
            if (!actor) return;
            document.getElementById('bbActorEditId').value = id;
            document.getElementById('bbActorPersonFields').style.display = 'none';
            setBbActorPrivileges(actor.privileges || {});
            document.getElementById('btnSaveBbActor').innerHTML = '<i class="fa fa-save"></i> Update Privileges';
            $('#bbActorTabAddLink').tab('show');
            document.getElementById('bbActorModalFooter').style.display = '';
        };
    });

    document.querySelectorAll('.btn-delete-bb-actor').forEach(btn => {
        btn.onclick = async function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const ok = await Swal.fire({ title: 'Remove access for ' + name + '?', icon: 'warning', showCancelButton: true });
            if (!ok.isConfirmed) return;
            const res = await api(routes.bbActorsDelete(id), 'POST');
            if (res.success) { showAlert(res.message); location.reload(); }
            else Swal.fire('Error', res.message, 'error');
        };
    });

    document.getElementById('blackBookActorForm').onsubmit = async function(e) {
        e.preventDefault();
        const editId = document.getElementById('bbActorEditId').value;
        const privileges = getBbActorPrivileges();
        let res;
        if (editId) {
            res = await api(routes.bbActorsUpdate(editId), 'POST', { privileges });
        } else {
            const actor_type = window.jQuery ? window.jQuery('#bbActorPersonType').val() : document.getElementById('bbActorPersonType').value;
            const actor_ref_id = window.jQuery ? window.jQuery('#bbActorPersonSelect').val() : document.getElementById('bbActorPersonSelect').value;
            if (!actor_type || !actor_ref_id) return Swal.fire('Error', 'Select a person', 'error');
            res = await api(routes.bbActorsStore, 'POST', { actor_type, actor_ref_id, privileges });
        }
        if (res.success) {
            Swal.fire('Success', res.message, 'success');
            location.reload();
        } else Swal.fire('Error', res.message, 'error');
    };
    @endif
})();
</script>
