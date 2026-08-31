@php
    $user_type = $user_type ?? session('user_type', 'Teacher');
    $permissionContext = $permissionContext ?? ($user_type === 'Staff' ? 'staff' : 'teacher');
    $permissionLabel = $permissionContext === 'staff' ? 'Staff' : 'Teacher';
    $permissionListRoute = $permissionContext === 'staff' ? 'staff.permissions' : 'teacher.permissions';
    $permissionStoreRoute = $permissionContext === 'staff' ? 'staff.permissions.store' : 'teacher.permissions.store';
@endphp

@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, div, .form-control, .form-select {
        border-radius: 0 !important;
    }
    .bg-primary-custom { background-color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .permission-tabs .nav-link { cursor: pointer; color: #940000; border-radius: 0; }
    .permission-tabs .nav-link.active { background: #fff5f5; color: #940000; font-weight: 600; border-color: #940000; }
    .section-title { font-weight: 600; margin-bottom: 12px; }
    .badge-count { background: #940000; color: #fff; border-radius: 10px; padding: 2px 6px; font-size: 0.75rem; margin-left: 6px; }
    .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: progressSlide 1.1s linear infinite;
    }
    @keyframes progressSlide {
        0% { left: -40%; }
        100% { left: 100%; }
    }
    .form-control {
        border: 1px solid #ced4da;
        padding: 10px 15px;
        height: auto;
        font-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 3px rgba(148, 0, 0, 0.1);
        outline: none;
    }
    label {
        font-weight: 600;
        color: #555;
        font-size: 13px;
        margin-bottom: 6px;
        display: block;
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Permission Requests</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>{{ $permissionLabel }} Permissions</strong>
        </div>
        <div class="card-body">
            <div class="form-loading" id="teacherTabLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Loading...</span>
                <div class="form-progress"></div>
            </div>
            <ul class="nav nav-tabs permission-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'request' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'request']) }}">
                        Request Permission
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'pending']) }}">
                        Pending Requests
                        @if($unreadPendingCount > 0)
                            <span class="badge-count">{{ $unreadPendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'approved']) }}">
                        Completed Requests
                        @if($unreadApprovedCount > 0)
                            <span class="badge-count">{{ $unreadApprovedCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'rejected' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'rejected']) }}">
                        Rejected Requests
                        @if($unreadRejectedCount > 0)
                            <span class="badge-count">{{ $unreadRejectedCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            @if($activeTab !== 'request')
            <div class="row mb-3">
                <div class="col-md-12">
                    <form id="permissionFilterForm" method="GET" action="{{ route($permissionListRoute) }}" class="p-3 bg-light border shadow-sm">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="font-weight-bold small">Filter By:</label>
                                <select name="filter_type" id="filter_type" class="form-control form-control-sm" onchange="toggleFilterInputs()">
                                    <option value="all" {{ ($filterType ?? '') == 'all' ? 'selected' : '' }}>All Records</option>
                                    <option value="range" {{ ($filterType ?? '') == 'range' ? 'selected' : '' }}>Date Range</option>
                                    <option value="month" {{ ($filterType ?? '') == 'month' ? 'selected' : '' }}>Specific Month</option>
                                    <option value="year" {{ ($filterType ?? '') == 'year' ? 'selected' : '' }}>Specific Year</option>
                                </select>
                            </div>

                            <div id="range_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'range' ? 'flex' : 'none' }}; gap: 10px;">
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">From:</label>
                                    <input type="date" name="date_from" id="filterDateFrom" class="form-control form-control-sm" value="{{ $dateFrom ?? '' }}">
                                </div>
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">To:</label>
                                    <input type="date" name="date_to" id="filterDateTo" class="form-control form-control-sm" value="{{ $dateTo ?? '' }}">
                                </div>
                            </div>

                            <div id="month_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'month' ? 'flex' : 'none' }}; gap: 10px;">
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">Month:</label>
                                    <select name="filter_month" class="form-control form-control-sm">
                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $monthName)
                                            <option value="{{ $index + 1 }}" {{ ($filterMonth ?? date('n')) == ($index + 1) ? 'selected' : '' }}>{{ $monthName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="font-weight-bold small">Year:</label>
                                    <select name="filter_month_year" class="form-control form-control-sm">
                                        @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                            <option value="{{ $y }}" {{ ($filterMonthYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div id="year_inputs" class="col-md-3 filter-input-group" style="display: {{ ($filterType ?? '') == 'year' ? 'block' : 'none' }};">
                                <label class="font-weight-bold small">Year:</label>
                                <select name="filter_year" class="form-control form-control-sm">
                                    @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                        <option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary-custom btn-sm w-100 mb-1">
                                    <i class="fa fa-search"></i> Apply Filter
                                </button>
                                <button type="button" onclick="resetTeacherFilters()" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($activeTab === 'request')
                @isset($editPermission)
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius:0; border-left: 4px solid #ff9800;">
                    <i class="fa fa-edit text-warning me-2" style="font-size:18px;"></i>
                    <div><strong>Editing Rejected Request</strong>: Make your corrections below and click <em>Update & Resubmit</em> to send it again for approval.</div>
                    <a href="{{ url()->current() }}?tab=request" class="btn btn-sm btn-outline-secondary ms-auto" style="border-radius:0;"><i class="fa fa-times"></i> Cancel</a>
                </div>
                @endisset
                <div class="section-title">{{ isset($editPermission) ? 'Edit & Resubmit Request' : 'Request Permission' }}</div>
                <form method="POST" action="{{ route($permissionStoreRoute) }}" id="teacherPermissionForm">
                    @csrf
                    @isset($editPermission)
                    <input type="hidden" name="permission_id" value="{{ $editPermission->permissionID }}">
                    @endisset
                    <div class="form-group mb-3">
                        <label>Time Mode</label>
                        <select class="form-control" name="time_mode" id="timeMode" required>
                            <option value="">-- Select --</option>
                            <option value="days" {{ isset($editPermission) && $editPermission->time_mode === 'days' ? 'selected' : '' }}>Days (Maximum 7 days)</option>
                            <option value="hours" {{ isset($editPermission) && $editPermission->time_mode === 'hours' ? 'selected' : '' }}>Hours</option>
                        </select>
                    </div>

                    <div id="daysFields" style="display:none;">
                        <input type="hidden" name="days_count" id="teacherDaysCount" value="">
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label>From Date</label>
                                <input type="date" class="form-control" name="start_date" id="teacherStartDate"
                                    value="{{ isset($editPermission) && $editPermission->start_date ? \Carbon\Carbon::parse($editPermission->start_date)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>To Date</label>
                                <input type="date" class="form-control" name="end_date" id="teacherEndDate"
                                    value="{{ isset($editPermission) && $editPermission->end_date ? \Carbon\Carbon::parse($editPermission->end_date)->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label>Total Days (max 7)</label>
                            <input type="text" class="form-control" id="teacherDaysDisplay" readonly>
                            <small class="text-danger d-none" id="teacherDaysError">Days exceed limit. Use 7 days or less.</small>
                        </div>
                    </div>

                    <div id="hoursFields" style="display:none;">
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label>Start Time (24h)</label>
                                <input type="time" class="form-control" name="start_time" value="{{ isset($editPermission) ? $editPermission->start_time : '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>End Time (24h)</label>
                                <input type="time" class="form-control" name="end_time" value="{{ isset($editPermission) ? $editPermission->end_time : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Reason Type</label>
                        <select class="form-control" name="reason_type" required>
                            <option value="medical" {{ isset($editPermission) && $editPermission->reason_type === 'medical' ? 'selected' : '' }}>Medical</option>
                            <option value="official" {{ isset($editPermission) && $editPermission->reason_type === 'official' ? 'selected' : '' }}>Official</option>
                            <option value="professional" {{ isset($editPermission) && $editPermission->reason_type === 'professional' ? 'selected' : '' }}>Professional</option>
                            <option value="emergency" {{ isset($editPermission) && $editPermission->reason_type === 'emergency' ? 'selected' : '' }}>Emergency</option>
                            <option value="other" {{ isset($editPermission) && $editPermission->reason_type === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Reason Description</label>
                        <textarea class="form-control" name="reason_description" rows="3" required>{{ isset($editPermission) ? $editPermission->reason_description : '' }}</textarea>
                    </div>

                    <button class="btn btn-primary-custom" type="submit" id="teacherPermissionSubmit">
                        @isset($editPermission)
                            <i class="fa fa-refresh"></i> Update & Resubmit
                        @else
                            <i class="fa fa-paper-plane"></i> Send Request
                        @endisset
                    </button>
                </form>
            @endif

            @if($activeTab === 'pending')
                <div class="section-title">Pending Requests</div>
                @include('Teacher.partials.permission_table', ['permissions' => $pendingPermissions])
            @endif

            @if($activeTab === 'approved')
                <div class="section-title">Completed Requests</div>
                @include('Teacher.partials.permission_table', ['permissions' => $approvedPermissions])
            @endif

            @if($activeTab === 'rejected')
                <div class="section-title">Rejected Requests</div>
                @include('Teacher.partials.permission_table', ['permissions' => $rejectedPermissions])
            @endif
        </div>
    </div>
</div>

@include('partials.permission_progress_modal')

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function initPermViewToggle() {
        var isMobile = window.innerWidth <= 991;
        var storageKey = 'teacher_perm_view_pref';
        var savedView = sessionStorage.getItem(storageKey);
        var activeView = savedView || (isMobile ? 'grid' : 'list');

        function applyView(view) {
            var listEl = document.getElementById('perm-list-view');
            var gridEl = document.getElementById('perm-grid-view');
            var btns = document.querySelectorAll('.perm-view-btn');
            if (!listEl || !gridEl) return;
            listEl.style.display = view === 'list' ? 'block' : 'none';
            gridEl.style.display = view === 'grid' ? 'block' : 'none';
            btns.forEach(function(btn) {
                var isActive = btn.getAttribute('data-view') === view;
                btn.classList.toggle('active', isActive);
                btn.classList.toggle('btn-secondary', isActive);
                btn.classList.toggle('btn-outline-secondary', !isActive);
            });
            sessionStorage.setItem(storageKey, view);
        }

        applyView(activeView);

        document.querySelectorAll('.perm-view-btn').forEach(function(btn) {
            btn.removeEventListener('click', btn._permViewHandler);
            btn._permViewHandler = function() { applyView(this.getAttribute('data-view')); };
            btn.addEventListener('click', btn._permViewHandler);
        });
    }

    function initTeacherPermissionForm() {
        const timeMode = document.getElementById('timeMode');
        const daysFields = document.getElementById('daysFields');
        const hoursFields = document.getElementById('hoursFields');
        const startDateInput = document.getElementById('teacherStartDate');
        const endDateInput = document.getElementById('teacherEndDate');
        const daysDisplay = document.getElementById('teacherDaysDisplay');
        const daysCountInput = document.getElementById('teacherDaysCount');
        const daysError = document.getElementById('teacherDaysError');

        function calculateDays() {
            if (!startDateInput || !endDateInput || !daysDisplay || !daysCountInput || !daysError) return;
            const start = startDateInput.value ? new Date(startDateInput.value) : null;
            const end = endDateInput.value ? new Date(endDateInput.value) : null;
            if (!start || !end) {
                daysDisplay.value = '';
                daysCountInput.value = '';
                daysError.classList.add('d-none');
                return;
            }
            
            let count = 0;
            let current = new Date(start);
            // Ensure time is reset to compare dates only
            current.setHours(0, 0, 0, 0);
            let endCopy = new Date(end);
            endCopy.setHours(0, 0, 0, 0);

            while (current <= endCopy) {
                const dayOfWeek = current.getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) { // 0 = Sunday, 6 = Saturday
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }

            daysDisplay.value = count > 0 ? count : '';
            daysCountInput.value = count > 0 ? count : '';
            if (count > 7) {
                daysError.classList.remove('d-none');
            } else {
                daysError.classList.add('d-none');
            }
        }

        function toggleTimeMode() {
            if (!timeMode) return;
            if (timeMode.value === 'days') {
                daysFields.style.display = 'block';
                hoursFields.style.display = 'none';
                calculateDays();
            } else if (timeMode.value === 'hours') {
                hoursFields.style.display = 'block';
                daysFields.style.display = 'none';
            } else {
                daysFields.style.display = 'none';
                hoursFields.style.display = 'none';
            }
        }

        if (timeMode) {
            timeMode.addEventListener('change', toggleTimeMode);
            // Trigger on load to show correct fields for pre-filled edit mode
            toggleTimeMode();
        }
        if (startDateInput) startDateInput.addEventListener('change', calculateDays);
        if (endDateInput) endDateInput.addEventListener('change', calculateDays);
        // Calculate days on load if both dates are pre-filled
        if (startDateInput && startDateInput.value && endDateInput && endDateInput.value) {
            calculateDays();
        }

        const permissionForm = document.getElementById('teacherPermissionForm');
        if (permissionForm) {
            permissionForm.addEventListener('submit', function(e) {
                if (timeMode && timeMode.value === 'days') {
                    calculateDays();
                    const days = parseInt(daysCountInput.value || '0', 10);
                    if (days > 7) {
                        e.preventDefault();
                        Swal.fire({ icon: 'error', title: 'Failed', text: 'Days exceed limit. Use 7 days or less.', confirmButtonColor: '#940000' });
                        return;
                    }
                }
                e.preventDefault();
                const submitBtn = document.getElementById('teacherPermissionSubmit');
                if (submitBtn) submitBtn.disabled = true;
                Swal.fire({ icon: 'info', title: 'Sending...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const formData = new FormData(permissionForm);
                fetch(permissionForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Success', text: data.message, confirmButtonColor: '#940000' });
                        permissionForm.reset();
                        if (daysFields) daysFields.style.display = 'none';
                        if (hoursFields) hoursFields.style.display = 'none';
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Failed to send request.', confirmButtonColor: '#940000' });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Failed', text: 'Failed to send request.', confirmButtonColor: '#940000' });
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        }
    }

    function loadTeacherTab(url) {
        const cardBody = document.querySelector('.card .card-body');
        if (!cardBody) return;
        const loadingBar = cardBody.querySelector('#teacherTabLoading');
        if (loadingBar) loadingBar.style.display = 'flex';
        cardBody.style.opacity = '0.6';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newBody = doc.querySelector('.card .card-body');
                if (newBody) {
                    cardBody.innerHTML = newBody.innerHTML;
                    bindTeacherTabLinks();
                    initTeacherPermissionForm();
                    initPermViewToggle();
                    const newLoadingBar = cardBody.querySelector('#teacherTabLoading');
                    if (newLoadingBar) newLoadingBar.style.display = 'none';
                }
            })
            .finally(() => {
                cardBody.style.opacity = '1';
                const currentLoadingBar = cardBody.querySelector('#teacherTabLoading');
                if (currentLoadingBar) currentLoadingBar.style.display = 'none';
            });
    }

    function toggleFilterInputs() {
        const type = $('#filter_type').val();
        $('.filter-input-group').hide();
        if (type === 'range') {
            $('#range_inputs').css('display', 'flex');
        } else if (type === 'month') {
            $('#month_inputs').css('display', 'flex');
        } else if (type === 'year') {
            $('#year_inputs').show();
        }
    }

    function resetTeacherFilters() {
        window.location.href = "{{ route($permissionListRoute) }}?tab={{ $activeTab }}";
    }

    function bindTeacherTabLinks() {
        document.querySelectorAll('.permission-tabs .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                loadTeacherTab(this.href);
            });
        });
 
        const filterForm = document.getElementById('permissionFilterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const btn = this.querySelector('button[type="submit"]');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Filtering...';
                btn.disabled = true;
 
                const url = new URL(this.action);
                url.searchParams.set('tab', this.querySelector('input[name="tab"]').value);
                
                // Get all filter fields
                const formData = new FormData(this);
                for (let [key, value] of formData.entries()) {
                    if (value) url.searchParams.set(key, value);
                }
                
                loadTeacherTab(url.toString());
            });
        }
    }

    bindTeacherTabLinks();
    initTeacherPermissionForm();
    initPermViewToggle();

    // Fix for dropdowns being cut off inside table-responsive
    $(document).on('show.bs.dropdown', '.table-responsive', function () {
        $(this).css('overflow', 'inherit');
    });

    $(document).on('hide.bs.dropdown', '.table-responsive', function () {
        $(this).css('overflow', 'auto');
    });

    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", confirmButtonColor: '#940000' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Failed', text: "{{ session('error') }}", confirmButtonColor: '#940000' });
    @endif
</script>

@include('partials.permission_details_modal')
