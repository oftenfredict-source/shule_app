@extends('layouts.parent_corp')

@section('title', __('parent.perm_title') . ' - ShuleXpert')

@section('content')
<style>
    .selection-card {
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
    .permission-tabs .nav-link {
        border-radius: 0 !important;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        color: #777;
        padding: 12px 20px;
    }
    .permission-tabs .nav-link.active {
        background: #fff5f5 !important;
        color: #940000 !important;
        border-color: #940000 !important;
        border-bottom: 3px solid #940000 !important;
    }
    .permission-tabs button.nav-link {
        background: transparent;
        border-top: 0;
        border-left: 0;
        border-right: 0;
        cursor: pointer;
    }
    .parent-unread-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        margin-left: 6px;
        background: #940000;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        line-height: 1;
        vertical-align: middle;
    }
    .parent-unread-badge i {
        font-size: 12px;
    }
    .perm-pane { display: none; }
    .perm-pane.is-active { display: block; }
    @media (max-width: 991px) {
        .perm-list-view { display: none !important; }
        .perm-grid-view { display: block !important; }
        .permission-tabs {
            display: flex;
            flex-wrap: nowrap !important;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }
        .permission-tabs .nav-item {
            flex: 0 0 auto;
        }
        .permission-tabs .nav-link {
            white-space: nowrap;
        }
    }
    .tab-content-area {
        background: white;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    .form-control {
        border: 1px solid #ced4da !important;
        padding: 12px 15px !important;
        font-size: 14px !important;
        background-color: #fff !important;
    }
    .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 4px rgba(148, 0, 0, 0.05) !important;
    }
    .form-label {
        color: #444 !important;
        margin-bottom: 10px !important;
    }
    #parentPermissionForm {
        padding: 8px 6px 4px;
    }
    #parentPermissionForm .request-field-row {
        --bs-gutter-x: 1.5rem;
        --bs-gutter-y: 0;
    }
    #parentPermissionForm .request-field-row > [class*="col-"] {
        padding-top: 6px;
        padding-bottom: 18px;
        margin-bottom: 6px;
    }
    #parentPermissionForm small {
        display: block;
        margin-top: 8px;
    }
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
</style>

<div class="row">
    <div class="col-12">
        <div class="section-title"><i class="bx bx-check-shield"></i> {{ __('parent.perm_title') }}</div>
    </div>

    <div class="col-12">
        <div class="card border-0">
            <div class="card-header bg-transparent p-0">
                <ul class="nav nav-tabs permission-tabs border-0 bg-white shadow-sm">
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-perm-tab {{ $activeTab === 'request' ? 'active' : '' }}" data-tab="request">{{ __('parent.request_permission') }}</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-perm-tab {{ $activeTab === 'pending' ? 'active' : '' }}" data-tab="pending">{{ __('parent.pending_tab') }}
                            <span class="parent-unread-badge" data-unread-for="pending" @if($unreadPendingCount < 1) style="display:none;" @endif><i class="bx bx-bell"></i> <span class="parent-unread-count">{{ $unreadPendingCount }}</span></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-perm-tab {{ $activeTab === 'approved' ? 'active' : '' }}" data-tab="approved">{{ __('parent.approved_tab') }}
                            <span class="parent-unread-badge" data-unread-for="approved" @if($unreadApprovedCount < 1) style="display:none;" @endif><i class="bx bx-bell"></i> <span class="parent-unread-count">{{ $unreadApprovedCount }}</span></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-perm-tab {{ $activeTab === 'rejected' ? 'active' : '' }}" data-tab="rejected">{{ __('parent.rejected_tab') }}
                            <span class="parent-unread-badge" data-unread-for="rejected" @if($unreadRejectedCount < 1) style="display:none;" @endif><i class="bx bx-bell"></i> <span class="parent-unread-count">{{ $unreadRejectedCount }}</span></span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0 mt-4 tab-content-area">
                <div class="perm-pane {{ $activeTab === 'request' ? 'is-active' : '' }}" data-pane="request">
                    @isset($editPermission)
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3 px-3" style="border-radius:0; border-left: 4px solid #ff9800;">
                        <i class="bx bx-edit text-warning me-2" style="font-size:20px;"></i>
                        <div>{{ __('parent.editing_rejected') }}</div>
                        <a href="{{ route('parent.permissions') }}" class="btn btn-sm btn-outline-secondary ms-auto" style="border-radius:0;"><i class="bx bx-x"></i> {{ __('parent.cancel') }}</a>
                    </div>
                    @endisset
                    @php
                        $editStudentId = isset($editPermission) ? ($editPermission->student_id ?? $editPermission->studentID ?? '') : '';
                        $editReasonRaw = strtolower((string) data_get($editPermission, 'reason_type', ''));
                        $editReason = in_array($editReasonRaw, ['sick', 'medical'], true) ? 'sick' : 'permission';
                    @endphp
                    <h6 class="font-weight-bold mb-4" style="color:#940000; border-left:4px solid #940000; padding-left:15px;">{{ isset($editPermission) ? __('parent.edit_resubmit') : __('parent.new_permission') }}</h6>
                    <form method="POST" action="{{ route('parent.permissions.store') }}" enctype="multipart/form-data" id="parentPermissionForm">
                        @csrf
                        @isset($editPermission)
                        <input type="hidden" name="permission_id" value="{{ $editPermission->permissionID ?? $editPermission->id }}">
                        @endisset
                        <div class="row request-field-row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.student') }}</label>
                                <select class="form-control" name="student_id" required>
                                    <option value="">{{ __('parent.choose_child') }}</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->studentID }}" {{ (string)$editStudentId === (string)$student->studentID ? 'selected' : '' }}>{{ $student->first_name }} {{ $student->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.leave_date') }}</label>
                                <input type="date" class="form-control" name="start_date" id="parentStartDate"
                                    value="{{ isset($editPermission) && $editPermission->start_date ? \Carbon\Carbon::parse($editPermission->start_date)->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.return_date') }}</label>
                                <input type="date" class="form-control" name="end_date" id="parentEndDate"
                                    value="{{ isset($editPermission) && $editPermission->end_date ? \Carbon\Carbon::parse($editPermission->end_date)->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.total_permission_days') }}</label>
                                <input type="text" class="form-control" id="parentDaysDisplay" readonly style="background-color: #f8f9fa; font-weight: bold; color: #940000;">
                                <small class="text-danger d-none" id="parentDaysError" style="font-weight: bold;">{{ __('parent.days_exceeded') }}</small>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.permission_type') }}</label>
                                <select class="form-control" name="reason_type" id="parentReasonType" required>
                                    <option value="">{{ __('parent.choose_type_opt') }}</option>
                                    <option value="permission" {{ isset($editPermission) && $editReason === 'permission' ? 'selected' : '' }}>{{ __('parent.permission') }}</option>
                                    <option value="sick" {{ isset($editPermission) && $editReason === 'sick' ? 'selected' : '' }}>{{ __('parent.sick') }}</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-2" id="medicalAttachmentField">
                                <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.attachment_optional') }}</label>
                                <input type="file" class="form-control" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">{{ __('parent.attachment_hint') }}</small>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.details') }}</label>
                                <textarea class="form-control" name="reason_description" rows="4" placeholder="{{ __('parent.details_placeholder') }}" required>{{ isset($editPermission) ? $editPermission->reason_description : '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary px-5" type="submit" id="parentPermissionSubmit">
                                    @isset($editPermission)
                                        <i class="bx bx-refresh"></i> {{ __('parent.update_resubmit') }}
                                    @else
                                        <i class="bx bx-paper-plane"></i> {{ __('parent.submit_request') }}
                                    @endisset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="parentListShell" style="display: {{ $activeTab === 'request' ? 'none' : 'block' }};">
                    <div class="form-loading" id="parentFilterLoading">
                        <span><i class="bx bx-loader-alt bx-spin text-danger"></i> {{ __('parent.filtering') }}</span>
                        <div class="form-progress"></div>
                    </div>
                    <div class="row mb-3 px-3">
                        <div class="col-md-12">
                            <form id="parentFilterForm" method="GET" action="{{ route('parent.permissions') }}" class="p-3 bg-light border shadow-sm">
                                <input type="hidden" name="tab" value="{{ $activeTab === 'request' ? 'pending' : $activeTab }}">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.filter_by') }}:</label>
                                        <select name="filter_type" id="filter_type" class="form-control" onchange="toggleFilterInputs()">
                                            <option value="all" {{ ($filterType ?? '') == 'all' ? 'selected' : '' }}>{{ __('parent.all_records') }}</option>
                                            <option value="range" {{ ($filterType ?? '') == 'range' ? 'selected' : '' }}>{{ __('parent.date_range') }}</option>
                                            <option value="month" {{ ($filterType ?? '') == 'month' ? 'selected' : '' }}>{{ __('parent.specific_month') }}</option>
                                            <option value="year" {{ ($filterType ?? '') == 'year' ? 'selected' : '' }}>{{ __('parent.specific_year') }}</option>
                                        </select>
                                    </div>

                                    <div id="range_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'range' ? 'flex' : 'none' }}; gap: 10px;">
                                        <div class="flex-grow-1">
                                            <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.from') }}:</label>
                                            <input type="date" name="date_from" id="filterDateFrom" class="form-control" value="{{ $dateFrom ?? '' }}">
                                        </div>
                                        <div class="flex-grow-1">
                                            <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.to') }}:</label>
                                            <input type="date" name="date_to" id="filterDateTo" class="form-control" value="{{ $dateTo ?? '' }}">
                                        </div>
                                    </div>

                                    <div id="month_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'month' ? 'flex' : 'none' }}; gap: 10px;">
                                        <div class="flex-grow-1">
                                            <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.month') }}:</label>
                                            <select name="filter_month" class="form-control">
                                                @foreach(__('parent.months') as $index => $monthName)
                                                    <option value="{{ $index + 1 }}" {{ ($filterMonth ?? date('n')) == ($index + 1) ? 'selected' : '' }}>{{ $monthName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex-grow-1">
                                            <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.year') }}:</label>
                                            <select name="filter_month_year" class="form-control">
                                                @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                                    <option value="{{ $y }}" {{ ($filterMonthYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div id="year_inputs" class="col-md-3 filter-input-group" style="display: {{ ($filterType ?? '') == 'year' ? 'block' : 'none' }};">
                                        <label class="form-label small font-weight-bold text-uppercase">{{ __('parent.year') }}:</label>
                                        <select name="filter_year" class="form-control">
                                            @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                                <option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-md-5 d-flex align-items-end gap-2">
                                        <button class="btn btn-outline-danger flex-fill" style="border-radius: 0; font-weight: bold; height: 48px;" type="submit"><i class="bx bx-filter"></i> {{ __('parent.filter') }}</button>
                                        <button class="btn btn-secondary flex-fill" style="border-radius: 0; font-weight: bold; height: 48px;" type="button" onclick="resetParentFilters()"><i class="bx bx-undo"></i> {{ __('parent.clear') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="perm-pane {{ $activeTab === 'pending' ? 'is-active' : '' }}" data-pane="pending">
                        @include('Parent.partials.permission_table', ['permissions' => $pendingPermissions, 'studentNames' => $studentNames])
                    </div>
                    <div class="perm-pane {{ $activeTab === 'approved' ? 'is-active' : '' }}" data-pane="approved">
                        @include('Parent.partials.permission_table', ['permissions' => $approvedPermissions, 'studentNames' => $studentNames])
                    </div>
                    <div class="perm-pane {{ $activeTab === 'rejected' ? 'is-active' : '' }}" data-pane="rejected">
                        @include('Parent.partials.permission_table', ['permissions' => $rejectedPermissions, 'studentNames' => $studentNames])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.permission_progress_modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const P = @json(trans('parent'));
        function initPermViewToggle(root) {
            var scope = root || document.querySelector('.perm-pane.is-active') || document;
            var isMobile = window.innerWidth <= 991;
            var storageKey = 'parent_perm_view_pref';
            var savedView = sessionStorage.getItem(storageKey);
            var activeView = savedView || (isMobile ? 'grid' : 'list');

            function applyView(view) {
                var listEl = scope.querySelector('.perm-list-view');
                var gridEl = scope.querySelector('.perm-grid-view');
                var btns = scope.querySelectorAll('.perm-view-btn');
                if (!listEl || !gridEl) return;
                if (window.innerWidth > 991) {
                    listEl.style.display = view === 'list' ? 'block' : 'none';
                    gridEl.style.display = view === 'grid' ? 'block' : 'none';
                }
                btns.forEach(function(btn) {
                    var isActive = btn.getAttribute('data-view') === view;
                    btn.classList.toggle('active', isActive);
                    btn.classList.toggle('btn-secondary', isActive);
                    btn.classList.toggle('btn-outline-secondary', !isActive);
                });
                sessionStorage.setItem(storageKey, view);
            }

            applyView(activeView);

            scope.querySelectorAll('.perm-view-btn').forEach(function(btn) {
                btn.removeEventListener('click', btn._permViewHandler);
                btn._permViewHandler = function() { applyView(this.getAttribute('data-view')); };
                btn.addEventListener('click', btn._permViewHandler);
            });
        }

        window.showParentPermissionTab = function(tab) {
            document.querySelectorAll('.parent-perm-tab').forEach(function(btn) {
                btn.classList.toggle('active', btn.getAttribute('data-tab') === tab);
            });
            document.querySelectorAll('.perm-pane').forEach(function(pane) {
                pane.classList.toggle('is-active', pane.getAttribute('data-pane') === tab);
            });
            var shell = document.getElementById('parentListShell');
            if (shell) shell.style.display = tab === 'request' ? 'none' : 'block';
            var tabInput = document.querySelector('#parentFilterForm input[name="tab"]');
            if (tabInput && tab !== 'request') tabInput.value = tab;
            if (window.history && window.history.replaceState) {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url.pathname + (url.search || ''));
            }
            var activePane = document.querySelector('.perm-pane.is-active');
            if (activePane) initPermViewToggle(activePane);
            if (tab !== 'request') {
                var badge = document.querySelector('.parent-unread-badge[data-unread-for="' + tab + '"]');
                if (badge) badge.style.display = 'none';
                $.post("{{ route('parent.permissions.read') }}", {
                    _token: "{{ csrf_token() }}",
                    status: tab
                });
            }
        };

        document.querySelectorAll('.parent-perm-tab').forEach(function(btn) {
            btn.addEventListener('click', function() {
                showParentPermissionTab(this.getAttribute('data-tab'));
            });
        });

        initPermViewToggle(document.querySelector('.perm-pane.is-active'));

        const startDateInput = $('#parentStartDate');
        const endDateInput = $('#parentEndDate');
        const daysDisplay = $('#parentDaysDisplay');
        const daysError = $('#parentDaysError');

        function calculateParentDays() {
            const startVal = startDateInput.val();
            const endVal = endDateInput.val();
            if (!startVal || !endVal) {
                daysDisplay.val('');
                daysError.addClass('d-none');
                return;
            }

            const start = new Date(startVal);
            const end = new Date(endVal);
            
            let count = 0;
            let current = new Date(start);
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

            daysDisplay.val(count > 0 ? count : '');
            if (count > 7) {
                daysError.removeClass('d-none');
            } else {
                daysError.addClass('d-none');
            }
        }

        startDateInput.on('change', calculateParentDays);
        endDateInput.on('change', calculateParentDays);
        // Recalculate on load if dates are pre-filled (edit mode)
        if (startDateInput.val() && endDateInput.val()) {
            calculateParentDays();
        }

        $('#parentReasonType').on('change', function() {
            // Kiambatanisho ni hiari kwa aina zote mbili
        });

        $('#parentPermissionForm').on('submit', function(e) {
            const daysCount = parseInt(daysDisplay.val() || '0', 10);
            if (daysCount > 7) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: P.days_exceeded_title, text: P.days_exceeded, confirmButtonColor: '#940000' });
                return;
            }

            e.preventDefault();
            const btn = $('#parentPermissionSubmit');
            const defaultHtml = btn.data('default-html') || btn.html();
            btn.data('default-html', defaultHtml);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>' + P.sending);
            
            let formData = new FormData(this);
            formData.append('days_count', daysCount);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        Swal.fire(P.sent, res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire(P.error, res.message, 'error');
                        btn.prop('disabled', false).html(defaultHtml);
                    }
                },
                error: function(xhr) {
                    let msg = P.submit_failed;
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire(P.error, msg, 'error');
                    btn.prop('disabled', false).html(defaultHtml);
                }
            });
        });

        const parentFilterForm = $('#parentFilterForm');
        if (parentFilterForm.length) {
            parentFilterForm.on('submit', function() {
                const loadingBar = document.getElementById('parentFilterLoading');
                if (loadingBar) loadingBar.style.display = 'flex';
                const btn = $(this).find('button[type="submit"]');
                btn.html('<i class="bx bx-loader-alt bx-spin"></i> ' + P.filtering);
                btn.prop('disabled', true);
            });
        }

        window.toggleFilterInputs = function() {
            const type = $('#filter_type').val();
            $('.filter-input-group').hide();
            if (type === 'range') {
                $('#range_inputs').css('display', 'flex');
            } else if (type === 'month') {
                $('#month_inputs').css('display', 'flex');
            } else if (type === 'year') {
                $('#year_inputs').show();
            }
        };

        window.resetParentFilters = function() {
            var tabInput = document.querySelector('#parentFilterForm input[name="tab"]');
            var tab = tabInput && tabInput.value ? tabInput.value : 'pending';
            window.location.href = "{{ route('parent.permissions') }}?tab=" + encodeURIComponent(tab);
        };

        // Fix for dropdowns being cut off inside table-responsive
        $(document).on('show.bs.dropdown', '.table-responsive', function () {
            $(this).css('overflow', 'inherit');
        });

        $(document).on('hide.bs.dropdown', '.table-responsive', function () {
            $(this).css('overflow', 'auto');
        });
    });
</script>
@endpush

@include('partials.permission_details_modal')
