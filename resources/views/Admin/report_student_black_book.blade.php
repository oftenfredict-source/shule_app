@php
    $bbUserType = Session::get('user_type');
    $bbUserHasManage = app(\App\Services\BlackBookAccessService::class)->currentUserHasManageAccess((int) Session::get('schoolID'));
@endphp
@if(strtolower((string)$bbUserType) === 'teacher')
    @include('includes.teacher_nav')
@else
    @include('includes.Admin_nav')
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
    .bb-status-pending { color: #f39c12; font-weight: 600; }
    .bb-status-viewed { color: #3498db; font-weight: 600; }
    .bb-status-resolved { color: #27ae60; font-weight: 600; }
    .bb-status-monitored { color: #8e44ad; font-weight: 600; }
    .student-result-item { cursor: pointer; }
    .student-result-item:hover { background: #fff5f5; }
    .bb-parent-sms-bar {
        background: #fff5f5;
        border: 1px solid #f0d0d0;
        padding: 12px 16px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
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
    }
    .bb-ajax-bar.is-active .bb-ajax-bar-fill {
        animation: bbBarRun 1.1s ease-in-out infinite, bbBarShine 1.2s linear infinite;
        width: 70%;
    }
    .bb-ajax-bar.is-finishing .bb-ajax-bar-fill { animation: none; width: 100% !important; }
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
    .bb-ajax-orbit span:nth-child(2) { inset: 8px; border-top-color: #c44; animation-duration: 0.7s; animation-direction: reverse; }
    .bb-ajax-orbit span:nth-child(3) { inset: 16px; border-top-color: #f0a0a0; animation-duration: 0.5s; }
    @keyframes bbOrbit { to { transform: rotate(360deg); } }
    .bb-ajax-text { font-weight: 700; color: #940000; font-size: 0.95rem; }
    .bb-ajax-sub { font-size: 0.75rem; color: #888; margin-top: 4px; text-transform: uppercase; }
    body.bb-ajax-busy { cursor: progress; }
    body.bb-ajax-busy #submitReportBtn { pointer-events: none; opacity: 0.75; }
    .bb-view-toggle .btn.active { background: #940000; color: #fff; border-color: #940000; }
    /* Mobile-first: grid default; desktop: list default (unless user toggles) */
    .bb-reports-wrapper .bb-reports-list-view { display: none; }
    .bb-reports-wrapper .bb-reports-grid-view { display: flex; flex-wrap: wrap; }
    @media (min-width: 768px) {
        .bb-reports-wrapper:not([data-active-view="grid"]) .bb-reports-list-view { display: block; }
        .bb-reports-wrapper:not([data-active-view="grid"]) .bb-reports-grid-view { display: none; }
    }
    .bb-reports-wrapper[data-active-view="list"] .bb-reports-list-view { display: block !important; }
    .bb-reports-wrapper[data-active-view="list"] .bb-reports-grid-view { display: none !important; }
    .bb-reports-wrapper[data-active-view="grid"] .bb-reports-list-view { display: none !important; }
    .bb-reports-wrapper[data-active-view="grid"] .bb-reports-grid-view { display: flex !important; flex-wrap: wrap; }
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
    .bb-report-card .bb-report-resolution {
        font-size: 0.82rem; color: #555; background: #f9f9f9;
        padding: 6px 8px; margin-top: 8px;
    }
    .select2-container--open { z-index: 10060 !important; }
    .select2-dropdown { z-index: 10061 !important; }
    .select2-container--bootstrap-5 .select2-selection { border-radius: 0 !important; min-height: 38px; }
    .select2-search--dropdown .select2-search__field { border-radius: 0 !important; }
</style>

<div id="bbAjaxBar" class="bb-ajax-bar" aria-hidden="true"><div class="bb-ajax-bar-fill"></div></div>
<div id="bbAjaxOverlay" class="bb-ajax-overlay" aria-hidden="true">
    <div class="bb-ajax-card">
        <div class="bb-ajax-orbit"><span></span><span></span><span></span></div>
        <div class="bb-ajax-text" id="bbAjaxText">Working...</div>
        <div class="bb-ajax-sub">Please wait</div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center flex-wrap">
            <strong><i class="fa fa-exclamation-triangle"></i> Report Student Behaviour</strong>
            <div>
                @if($bbUserHasManage || strtolower((string)$bbUserType) === 'admin')
                <a href="{{ route('manage_black_book') }}" class="btn btn-light btn-sm"><i class="fa fa-book"></i> Black Book</a>
                @endif
                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#reportStudentModal">
                    <i class="fa fa-plus"></i> Report Student
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="reportAlert" class="alert d-none"></div>

            <div class="bb-parent-sms-bar">
                <div>
                    <strong class="text-primary-custom"><i class="fa fa-envelope"></i> Notify Parent via SMS</strong>
                    <div class="small text-muted">Ikiwa ON, mzazi atapokea SMS kwa Kiswahili unapomuandikia mwanafunzi.</div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="notifyParentSmsToggle">
                    <label class="custom-control-label font-weight-bold" for="notifyParentSmsToggle" id="notifyParentSmsLabel">OFF</label>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
                <ul class="nav nav-tabs mb-0" id="myReportsTabs">
                    <li class="nav-item"><a class="nav-link active" data-status="" href="#">All</a></li>
                    <li class="nav-item"><a class="nav-link" data-status="pending" href="#">Pending</a></li>
                    <li class="nav-item"><a class="nav-link" data-status="viewed" href="#">Viewed</a></li>
                    <li class="nav-item"><a class="nav-link" data-status="monitored" href="#">Monitored</a></li>
                    <li class="nav-item"><a class="nav-link" data-status="resolved" href="#">Resolved</a></li>
                </ul>
                <div class="btn-group btn-group-sm bb-view-toggle" id="reportsViewToggle">
                    <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i></button>
                    <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i></button>
                </div>
            </div>

            <div class="bb-reports-wrapper" id="reportsDataWrapper">
                <div class="bb-reports-list-view">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Resolution</th>
                                </tr>
                            </thead>
                            <tbody id="myReportsBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="bb-reports-grid-view row" id="myReportsGrid"></div>
            </div>
        </div>
    </div>
</div>

{{-- Report Modal --}}
<div class="modal fade" id="reportStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:0;">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Report Student</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Search Student</label>
                    <input type="text" id="studentSearchInput" class="form-control" placeholder="Type name or admission number...">
                    <small class="text-muted d-block mt-1">Only <strong>Active enrollments</strong> for the academic year selected in the header can be reported.</small>
                    <input type="hidden" id="selectedStudentID">
                    <div id="studentSearchResults" class="list-group mt-2" style="max-height:200px;overflow:auto;"></div>
                    <div id="selectedStudentDisplay" class="mt-2 p-2 bg-light border d-none"></div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Problem Category</label>
                    <select id="problemCategorySelect" class="form-control bb-search-select">
                        <option value="">Search problem category...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->categoryID }}">{{ $cat->name }} (Weight: {{ $cat->weight }}%)</option>
                        @endforeach
                    </select>
                    @if($categories->isEmpty())
                    <small class="text-danger">No problem categories yet. <a href="{{ route('manage_black_book', ['section' => 'categories']) }}">Add categories</a> first.</small>
                    @endif
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Description of Problem</label>
                    <textarea id="reportDescription" class="form-control" rows="4" placeholder="Describe what happened..."></textarea>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="notifyParentSmsModal">
                    <label class="custom-control-label" for="notifyParentSmsModal">Tuma SMS kwa mzazi unapowasilisha ripoti hii</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="submitReportBtn">
                    <i class="fa fa-save"></i> Submit Report
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const searchUrl = @json(route('black_book.students.search'));
    const storeUrl = @json(route('black_book.reports.store'));
    const listUrl = @json(route('black_book.reports.index'));

    function initProblemCategorySelect() {
        const $ = window.jQuery;
        if (!$ || !$.fn.select2) return;
        const $sel = $('#problemCategorySelect');
        if (!$sel.length) return;
        if ($sel.hasClass('select2-hidden-accessible')) {
            try { $sel.select2('destroy'); } catch (e) {}
        }
        $sel.select2({
            theme: 'bootstrap-5',
            placeholder: 'Search problem category...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#reportStudentModal'),
            minimumResultsForSearch: 0,
            language: {
                noResults: function() { return 'No category found'; },
                searching: function() { return 'Searching...'; }
            }
        });
    }

    const PARENT_SMS_KEY = 'black_book_notify_parent_sms';

    function isParentSmsEnabled() {
        return localStorage.getItem(PARENT_SMS_KEY) === '1';
    }

    function setParentSmsEnabled(on) {
        localStorage.setItem(PARENT_SMS_KEY, on ? '1' : '0');
        syncParentSmsToggles();
    }

    function syncParentSmsToggles() {
        const on = isParentSmsEnabled();
        const pageToggle = document.getElementById('notifyParentSmsToggle');
        const modalToggle = document.getElementById('notifyParentSmsModal');
        const label = document.getElementById('notifyParentSmsLabel');
        if (pageToggle) pageToggle.checked = on;
        if (modalToggle) modalToggle.checked = on;
        if (label) {
            label.textContent = on ? 'ON' : 'OFF';
            label.style.color = on ? '#27ae60' : '#888';
        }
    }

    document.getElementById('notifyParentSmsToggle').addEventListener('change', function() {
        setParentSmsEnabled(this.checked);
    });
    document.getElementById('notifyParentSmsModal').addEventListener('change', function() {
        setParentSmsEnabled(this.checked);
    });
    $('#reportStudentModal').on('show.bs.modal', syncParentSmsToggles);
    $('#reportStudentModal').on('shown.bs.modal', initProblemCategorySelect);
    syncParentSmsToggles();

    let bbAjaxActive = 0;
    let bbAjaxFinishTimer = null;

    function bbLoadingStart(message) {
        bbAjaxActive += 1;
        clearTimeout(bbAjaxFinishTimer);
        document.getElementById('bbAjaxBar').classList.add('is-active');
        document.getElementById('bbAjaxText').textContent = message || 'Working...';
        document.getElementById('bbAjaxOverlay').classList.add('is-active');
        document.body.classList.add('bb-ajax-busy');
    }

    function bbLoadingStop() {
        bbAjaxActive = Math.max(0, bbAjaxActive - 1);
        if (bbAjaxActive > 0) return;
        const bar = document.getElementById('bbAjaxBar');
        bar.classList.add('is-finishing');
        document.getElementById('bbAjaxOverlay').classList.remove('is-active');
        document.body.classList.remove('bb-ajax-busy');
        bbAjaxFinishTimer = setTimeout(function() {
            bar.classList.remove('is-active', 'is-finishing');
        }, 220);
    }

    async function bbApi(url, method, body, loadingOption) {
        const skipLoad = loadingOption === false;
        if (!skipLoad) bbLoadingStart(typeof loadingOption === 'string' ? loadingOption : (method === 'POST' ? 'Reporting student...' : 'Loading...'));
        try {
            const opts = { method: method || 'GET', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } };
            if (body) {
                opts.method = method || 'POST';
                opts.headers['Content-Type'] = 'application/json';
                opts.body = JSON.stringify(body);
            }
            const res = await fetch(url, opts);
            return await res.json();
        } finally {
            if (!skipLoad) bbLoadingStop();
        }
    }

    let searchTimer;
    let allReports = @json($myReports);
    let filterStatus = '';

    function isMobile() { return window.matchMedia('(max-width: 767.98px)').matches; }

    function getReportsEffectiveView() {
        const wrapper = document.getElementById('reportsDataWrapper');
        const forced = wrapper.getAttribute('data-active-view');
        if (forced === 'grid' || forced === 'list') return forced;
        return isMobile() ? 'grid' : 'list';
    }

    function syncReportsViewToggle() {
        const view = getReportsEffectiveView();
        document.getElementById('reportsViewToggle').querySelectorAll('button').forEach(b => {
            b.classList.toggle('active', b.getAttribute('data-view') === view);
        });
    }

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function getResolutionText(r) {
        if (r.status !== 'resolved' && r.status !== 'monitored') return '-';
        let resolution = r.resolve_method === 'parent_call'
            ? 'Parent Call, ' + (r.resolved_by_name || '')
            : ((r.punishment_category || 'Punishment') + ', ' + (r.punishment_description || '').substring(0, 80));
        if (r.status === 'monitored') {
            resolution += ' [Monitoring until ' + (r.monitoring_deadline || '-') + ']';
        }
        return resolution;
    }

    document.getElementById('reportsViewToggle').querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            const wrapper = document.getElementById('reportsDataWrapper');
            wrapper.setAttribute('data-active-view', view);
            wrapper.setAttribute('data-user-view', '1');
            syncReportsViewToggle();
        });
    });

    window.addEventListener('resize', function() {
        const wrapper = document.getElementById('reportsDataWrapper');
        if (!wrapper.hasAttribute('data-user-view')) {
            wrapper.removeAttribute('data-active-view');
        }
        syncReportsViewToggle();
    });

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

    function renderReports() {
        const rows = filterStatus ? allReports.filter(r => r.status === filterStatus) : allReports;
        const tbody = document.getElementById('myReportsBody');
        const grid = document.getElementById('myReportsGrid');
        const emptyMsg = 'You have not reported any student yet.';

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">' + (filterStatus ? 'No reports found.' : emptyMsg) + '</td></tr>';
            grid.innerHTML = '<div class="col-12 text-center text-muted py-4">' + (filterStatus ? 'No reports found.' : emptyMsg) + '</div>';
            return;
        }

        tbody.innerHTML = rows.map(r => {
            const resolution = getResolutionText(r);
            return '<tr><td>' + escapeHtml(r.created_at) + '</td><td>' + escapeHtml(r.student_name) + '</td><td>' + escapeHtml(r.class_name) + '</td>' +
                '<td>' + escapeHtml(r.problem_category) + ' (' + r.problem_weight + '%)</td><td>' + escapeHtml((r.description || '').substring(0, 60)) + '</td>' +
                '<td>' + statusBadge(r.status) + '</td><td>' + escapeHtml(resolution) + '</td></tr>';
        }).join('');

        grid.innerHTML = rows.map(r => {
            const resolution = getResolutionText(r);
            return '<div class="col-md-6 col-lg-4 col-sm-12">' +
                '<div class="bb-report-card">' +
                '<div class="bb-report-meta"><i class="fa fa-clock-o"></i> ' + escapeHtml(r.created_at) + '</div>' +
                '<div class="bb-report-student">' + escapeHtml(r.student_name) + '</div>' +
                '<div class="bb-report-class"><i class="fa fa-graduation-cap"></i> ' + escapeHtml(r.class_name) + '</div>' +
                '<div class="mt-2"><span class="badge badge-light border">' + escapeHtml(r.problem_category) + ' (' + r.problem_weight + '%)</span></div>' +
                '<div class="mt-2">' + statusBadge(r.status) + '</div>' +
                '<div class="bb-report-desc">' + escapeHtml(r.description || '') + '</div>' +
                (resolution !== '-' ? '<div class="bb-report-resolution"><strong>Resolution:</strong> ' + escapeHtml(resolution) + '</div>' : '') +
                '</div></div>';
        }).join('');
    }

    syncReportsViewToggle();
    renderReports();

    document.querySelectorAll('#myReportsTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('#myReportsTabs .nav-link').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterStatus = this.getAttribute('data-status');
            renderReports();
        });
    });

    document.getElementById('studentSearchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        document.getElementById('selectedStudentID').value = '';
        document.getElementById('selectedStudentDisplay').classList.add('d-none');
        if (q.length < 2) {
            document.getElementById('studentSearchResults').innerHTML = '';
            return;
        }
        searchTimer = setTimeout(async () => {
            const res = await bbApi(searchUrl + '?search=' + encodeURIComponent(q) + '&for_report=1', 'GET', null, false);
            const box = document.getElementById('studentSearchResults');
            if (res.success === false && (res.error_code === 'no_academic_year' || res.error_code === 'no_active_enrollments')) {
                box.innerHTML = '<div class="list-group-item text-muted small">' + (res.message || 'No Active enrollments for the selected academic year.') + '</div>';
                return;
            }
            const students = (res.students || []).filter(s => s.can_report !== false);
            if (!students.length) {
                box.innerHTML = '<div class="list-group-item text-muted small">No Active enrolled students matched.</div>';
                return;
            }
            box.innerHTML = students.map(s =>
                '<button type="button" class="list-group-item list-group-item-action student-result-item" data-id="' + s.studentID + '" data-display="' + String(s.display || '').replace(/"/g, '&quot;') + '">' + escapeHtml(s.display) + '</button>'
            ).join('');
            box.querySelectorAll('.student-result-item').forEach(btn => {
                btn.onclick = () => {
                    document.getElementById('selectedStudentID').value = btn.getAttribute('data-id');
                    document.getElementById('selectedStudentDisplay').textContent = btn.getAttribute('data-display');
                    document.getElementById('selectedStudentDisplay').classList.remove('d-none');
                    document.getElementById('studentSearchResults').innerHTML = '';
                    document.getElementById('studentSearchInput').value = '';
                };
            });
        }, 300);
    });

    document.getElementById('submitReportBtn').onclick = async () => {
        const studentID = document.getElementById('selectedStudentID').value;
        const problem_categoryID = window.jQuery ? window.jQuery('#problemCategorySelect').val() : document.getElementById('problemCategorySelect').value;
        const description = document.getElementById('reportDescription').value.trim();
        if (!studentID) return Swal.fire('Error', 'Select a student', 'error');
        if (!problem_categoryID) return Swal.fire('Error', 'Select problem category', 'error');
        if (description.length < 5) return Swal.fire('Error', 'Enter a description (min 5 characters)', 'error');

        const btn = document.getElementById('submitReportBtn');
        btn.disabled = true;
        const res = await bbApi(storeUrl, 'POST', {
            studentID,
            problem_categoryID,
            description,
            send_parent_sms: isParentSmsEnabled(),
        }, 'Reporting student...');
        btn.disabled = false;

        if (res.success) {
            Swal.fire('Success', res.message, 'success');
            $('#reportStudentModal').modal('hide');
            document.getElementById('selectedStudentID').value = '';
            document.getElementById('reportDescription').value = '';
            document.getElementById('selectedStudentDisplay').classList.add('d-none');
            const listRes = await bbApi(listUrl + '?mine=1', 'GET', null, 'Refreshing list...');
            if (listRes.success) { allReports = listRes.reports; renderReports(); }
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    };
})();
</script>
