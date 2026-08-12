@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css" />

@php
    $isSecondary = $school && strcasecmp((string) $school->school_type, 'Secondary') === 0;
@endphp

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, .form-control, .form-select, .modal-content {
        border-radius: 0 !important;
    }
    .bg-primary-custom { background-color: #940000 !important; }
    .text-primary-custom { color: #940000 !important; }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    .section-title {
        font-weight: 600;
        margin-bottom: 12px;
        color: #940000;
        font-size: 1.05rem;
    }
    .ay-current-badge {
        display: inline-block;
        background: #28a745;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 10px;
    }
    .ay-badge-muted {
        display: inline-block;
        background: #6c757d;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 10px;
    }
    .ay-swal-popup {
        font-family: "Century Gothic", Arial, sans-serif !important;
        border-radius: 0 !important;
    }
    .ay-swal-confirm { border-radius: 0 !important; }
    .ay-level-box { display: none; }
    .ay-level-box.is-visible { display: block; }
    .ay-view-toggle .btn.active {
        background: #940000;
        color: #fff;
        border-color: #940000;
    }
    .ay-data-wrapper .ay-list-view { display: none; }
    .ay-data-wrapper .ay-grid-view { display: flex; flex-wrap: wrap; }
    @media (min-width: 768px) {
        .ay-data-wrapper:not([data-active-view="grid"]) .ay-list-view { display: block; }
        .ay-data-wrapper:not([data-active-view="grid"]) .ay-grid-view { display: none; }
    }
    .ay-data-wrapper[data-active-view="list"] .ay-list-view { display: block !important; }
    .ay-data-wrapper[data-active-view="list"] .ay-grid-view { display: none !important; }
    .ay-data-wrapper[data-active-view="grid"] .ay-list-view { display: none !important; }
    .ay-data-wrapper[data-active-view="grid"] .ay-grid-view { display: flex !important; flex-wrap: wrap; }
    .ay-card-item {
        border: 1px solid #eee;
        border-left: 4px solid #940000;
        padding: 14px;
        margin-bottom: 12px;
        background: #fff;
        height: 100%;
    }
    .ay-card-item .ay-card-year {
        font-weight: 700;
        font-size: 1.05rem;
        color: #333;
        margin-bottom: 6px;
    }
    .ay-card-item .ay-card-meta {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 10px;
    }
    .ay-level-badge {
        display: inline-block;
        background: #fff5f5;
        color: #940000;
        border: 1px solid #f0d0d0;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 10px;
    }
    .ay-grid-search-wrap {
        width: 100%;
        margin-bottom: 12px;
    }
    .ay-grid-search-wrap .form-control {
        width: 100%;
    }
    .ay-list-view .dataTables_wrapper .dataTables_filter input,
    .ay-list-view .dataTables_wrapper .dataTables_length select {
        border-radius: 0 !important;
        border: 1px solid #ced4da;
    }
    .ay-list-view .dataTables_wrapper .dataTables_filter input:focus,
    .ay-list-view .dataTables_wrapper .dataTables_length select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    .ay-list-view .dataTables_wrapper .page-item.active .page-link {
        background-color: #940000;
        border-color: #940000;
    }
    .ay-list-view .dataTables_wrapper .page-link {
        color: #940000;
        border-radius: 0 !important;
    }
    .ay-list-view .dataTables_info,
    .ay-list-view .dataTables_length,
    .ay-list-view .dataTables_filter,
    .ay-list-view .dataTables_paginate {
        font-size: 0.85rem;
        margin-top: 8px;
        margin-bottom: 8px;
    }
    @media (max-width: 767.98px) {
        .ay-list-view .dataTables_wrapper .dataTables_filter,
        .ay-list-view .dataTables_wrapper .dataTables_length {
            text-align: left !important;
            float: none !important;
            width: 100%;
        }
        .ay-list-view .dataTables_wrapper .dataTables_filter label,
        .ay-list-view .dataTables_wrapper .dataTables_length label {
            width: 100%;
            display: block;
        }
        .ay-list-view .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin: 6px 0 0 0 !important;
            display: block;
        }
        .ay-list-view .dataTables_wrapper .dataTables_length select {
            width: 100% !important;
            margin-top: 6px;
        }
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title"><h1>Academic Years</h1></div>
        </div>
    </div>
</div>

<div class="content mt-3">
    @if(session('error') || session('require_academic_year') || $academicYears->isEmpty())
        <div class="alert alert-danger" style="border-radius:0;border-left:5px solid #940000;">
            <strong><i class="fa fa-exclamation-triangle"></i> Academic Year required</strong>
            <div class="mt-1">
                @if($academicYears->isEmpty())
                    This school has no Academic Year yet. Register at least one Academic Year before using the system (students, exams, attendance, fees, etc.).
                @else
                    {{ session('error') }}
                @endif
            </div>
            @if($academicYears->isEmpty() && session('user_type') === 'Admin')
                <button type="button" class="btn btn-primary-custom btn-sm mt-2" data-toggle="modal" data-target="#registerYearModal">
                    <i class="fa fa-plus"></i> Register Academic Year now
                </button>
            @endif
        </div>
    @endif
    <div class="card">
        <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
            <strong><i class="fa fa-calendar-check-o me-1"></i> Academic Years Management</strong>
            <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#registerYearModal">
                <i class="fa fa-plus"></i> Register Academic Year
            </button>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
                <div class="section-title mb-0">Registered Academic Years</div>
                <div class="btn-group btn-group-sm ay-view-toggle" data-wrapper="academicYearsWrapper">
                    <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i></button>
                    <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i></button>
                </div>
            </div>

            <div class="ay-data-wrapper" id="academicYearsWrapper">
                <div class="ay-list-view">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="academicYearsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Year Name</th>
                                    @if($isSecondary)
                                        <th>Level</th>
                                    @endif
                                    <th>Current</th>
                                    <th width="160">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="academicYearsBody">
                                @foreach($academicYears as $index => $year)
                                    @php
                                        $isALevel = str_contains((string) ($year->year_name ?: ''), '/');
                                        $levelLabel = $isALevel ? 'A Level' : 'O Level';
                                    @endphp
                                    <tr data-id="{{ $year->academic_yearID }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="font-weight-bold">{{ $year->year_name ?: $year->year }}</td>
                                        @if($isSecondary)
                                            <td><span class="ay-level-badge">{{ $levelLabel }}</span></td>
                                        @endif
                                        <td>
                                            @if($year->is_current)
                                                <span class="ay-current-badge">Current</span>
                                            @else
                                                <span class="ay-badge-muted">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-view-year" data-id="{{ $year->academic_yearID }}" title="View">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-year" data-id="{{ $year->academic_yearID }}" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-year" data-id="{{ $year->academic_yearID }}" data-name="{{ $year->year_name ?: $year->year }}" title="Delete" @if($year->is_current) disabled @endif>
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ay-grid-view row" id="academicYearsGrid">
                    <div class="col-12 ay-grid-search-wrap">
                        <input type="text" id="ayGridSearch" class="form-control" placeholder="Search academic year...">
                    </div>
                    @forelse($academicYears as $year)
                        @php
                            $isALevel = str_contains((string) ($year->year_name ?: ''), '/');
                            $levelLabel = $isALevel ? 'A Level' : 'O Level';
                            $searchText = strtolower(trim(($year->year_name ?: $year->year) . ' ' . ($isSecondary ? $levelLabel : '') . ' ' . ($year->is_current ? 'current' : '')));
                        @endphp
                        <div class="col-12 col-sm-6 col-lg-4 mb-3 ay-grid-item" data-search="{{ $searchText }}">
                            <div class="ay-card-item">
                                <div class="ay-card-year">{{ $year->year_name ?: $year->year }}</div>
                                <div class="ay-card-meta">
                                    @if($isSecondary)
                                        <span class="ay-level-badge">{{ $levelLabel }}</span>
                                        &nbsp;
                                    @endif
                                    @if($year->is_current)
                                        <span class="ay-current-badge">Current</span>
                                    @else
                                        <span class="ay-badge-muted">No</span>
                                    @endif
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-view-year" data-id="{{ $year->academic_yearID }}" title="View">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-year" data-id="{{ $year->academic_yearID }}" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-year" data-id="{{ $year->academic_yearID }}" data-name="{{ $year->year_name ?: $year->year }}" title="Delete" @if($year->is_current) disabled @endif>
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 ay-grid-empty">
                            <div class="alert alert-light border text-center text-muted mb-0">No academic years registered yet.</div>
                        </div>
                    @endforelse
                    <div class="col-12 ay-grid-no-results d-none">
                        <div class="alert alert-light border text-center text-muted mb-0">No matching academic years.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Register Modal --}}
<div class="modal fade" id="registerYearModal" tabindex="-1" role="dialog" aria-labelledby="registerYearModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="registerYearModalLabel"><i class="fa fa-plus"></i> Register Academic Year</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="registerYearForm">
                <div class="modal-body">
                    @if($isSecondary)
                        <div class="form-group">
                            <label class="font-weight-bold">Academic Level <span class="text-danger">*</span></label>
                            <select name="level_type" id="reg_level_type" class="form-control" required>
                                <option value="">-- Select Level --</option>
                                <option value="O_Level">O Level</option>
                                <option value="A_Level">A Level</option>
                            </select>
                        </div>

                        <div id="regOLevelBox" class="ay-level-box">
                            <div class="form-group">
                                <label class="font-weight-bold">Year <span class="text-danger">*</span></label>
                                <input type="number" name="year" id="reg_year" class="form-control" min="1990" max="2100" step="1" value="2026" placeholder="e.g. 2026">
                                <small class="text-muted">Enter year only (e.g. 2026)</small>
                            </div>
                        </div>

                        <div id="regALevelBox" class="ay-level-box">
                            <div class="form-group">
                                <label class="font-weight-bold">Start Year <span class="text-danger">*</span></label>
                                <input type="number" name="start_year" id="reg_start_year" class="form-control" min="1990" max="2100" step="1" value="2025" placeholder="e.g. 2025">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">End Year <span class="text-danger">*</span></label>
                                <input type="number" name="end_year" id="reg_end_year" class="form-control" min="1990" max="2100" step="1" value="2026" placeholder="e.g. 2026">
                                <small class="text-muted">A-Level year will be saved as Start/End (e.g. 2025/2026)</small>
                            </div>
                        </div>
                    @else
                        <div class="form-group">
                            <label class="font-weight-bold">Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" id="reg_year" class="form-control" min="1990" max="2100" step="1" value="2026" placeholder="e.g. 2026" required>
                            <small class="text-muted">Enter year only (e.g. 2026)</small>
                        </div>
                    @endif

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="reg_is_current" name="is_current" value="1">
                            <label class="custom-control-label" for="reg_is_current">Set as current academic year</label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            @if($isSecondary)
                                O-Level and A-Level can each have one current year (both may be current together).
                            @else
                                Only one current academic year is allowed for primary schools.
                            @endif
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editYearModal" tabindex="-1" role="dialog" aria-labelledby="editYearModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editYearModalLabel"><i class="fa fa-edit"></i> Edit Academic Year</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editYearForm">
                <input type="hidden" id="edit_academic_yearID" name="academic_yearID">
                <div class="modal-body">
                    @if($isSecondary)
                        <div class="form-group">
                            <label class="font-weight-bold">Academic Level <span class="text-danger">*</span></label>
                            <select name="level_type" id="edit_level_type" class="form-control" required>
                                <option value="">-- Select Level --</option>
                                <option value="O_Level">O Level</option>
                                <option value="A_Level">A Level</option>
                            </select>
                        </div>

                        <div id="editOLevelBox" class="ay-level-box">
                            <div class="form-group">
                                <label class="font-weight-bold">Year <span class="text-danger">*</span></label>
                                <input type="number" name="year" id="edit_year" class="form-control" min="1990" max="2100" step="1">
                            </div>
                        </div>

                        <div id="editALevelBox" class="ay-level-box">
                            <div class="form-group">
                                <label class="font-weight-bold">Start Year <span class="text-danger">*</span></label>
                                <input type="number" name="start_year" id="edit_start_year" class="form-control" min="1990" max="2100" step="1">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">End Year <span class="text-danger">*</span></label>
                                <input type="number" name="end_year" id="edit_end_year" class="form-control" min="1990" max="2100" step="1">
                            </div>
                        </div>
                    @else
                        <div class="form-group">
                            <label class="font-weight-bold">Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" id="edit_year" class="form-control" min="1990" max="2100" step="1" required>
                        </div>
                    @endif

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_is_current" name="is_current" value="1">
                            <label class="custom-control-label" for="edit_is_current">Set as current academic year</label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            @if($isSecondary)
                                O-Level and A-Level can each have one current year (both may be current together).
                            @else
                                Only one current academic year is allowed for primary schools.
                            @endif
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="viewYearModal" tabindex="-1" role="dialog" aria-labelledby="viewYearModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewYearModalLabel"><i class="fa fa-eye"></i> Academic Year Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th width="40%">Year Name</th>
                        <td id="view_year_name">-</td>
                    </tr>
                    <tr>
                        <th>Current</th>
                        <td id="view_is_current">-</td>
                    </tr>
                    @if($isSecondary)
                    <tr>
                        <th>Level</th>
                        <td id="view_level_type">-</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Created</th>
                        <td id="view_created_at">-</td>
                    </tr>
                    <tr>
                        <th>Updated</th>
                        <td id="view_updated_at">-</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
(function ($) {
    var isSecondary = @json($isSecondary);
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var routes = {
        store: @json(route('admin.academicYears.store')),
        show: @json(url('admin/academic-years')),
        update: @json(url('admin/academic-years')),
        destroy: @json(url('admin/academic-years'))
    };
    var ayTable = null;
    var actionsCol = isSecondary ? 4 : 3;

    function swalOpts(extra) {
        return Object.assign({
            customClass: {
                popup: 'ay-swal-popup',
                confirmButton: 'ay-swal-confirm'
            }
        }, extra || {});
    }

    function initAyDataTable() {
        if (!$.fn.DataTable) return;
        if ($.fn.DataTable.isDataTable('#academicYearsTable')) {
            ayTable = $('#academicYearsTable').DataTable();
            return;
        }
        ayTable = $('#academicYearsTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            order: [[1, 'desc']],
            columnDefs: [
                { orderable: false, targets: [actionsCol] },
                { searchable: false, targets: [0, actionsCol] }
            ],
            language: {
                emptyTable: 'No academic years registered yet.',
                search: 'Search:',
                lengthMenu: 'Show _MENU_',
                info: 'Showing _START_ to _END_ of _TOTAL_',
                zeroRecords: 'No matching academic years'
            }
        });
    }

    function adjustAyDataTable() {
        if (ayTable) {
            setTimeout(function () {
                ayTable.columns.adjust();
            }, 50);
        }
    }

    function filterAyGrid(term) {
        term = (term || '').toLowerCase().trim();
        var visible = 0;
        $('#academicYearsGrid .ay-grid-item').each(function () {
            var hay = ($(this).attr('data-search') || '').toLowerCase();
            var show = !term || hay.indexOf(term) !== -1;
            $(this).toggle(show);
            if (show) visible++;
        });
        var hasItems = $('#academicYearsGrid .ay-grid-item').length > 0;
        $('.ay-grid-no-results').toggleClass('d-none', !hasItems || visible > 0);
    }

    function syncAyViewToggleButtons() {
        $('.ay-view-toggle').each(function () {
            var wrapperId = $(this).data('wrapper');
            var $wrapper = $('#' + wrapperId);
            var forced = $wrapper.attr('data-active-view');
            var view = forced || (window.matchMedia('(min-width: 768px)').matches ? 'list' : 'grid');
            $(this).find('[data-view]').each(function () {
                $(this).toggleClass('active', $(this).attr('data-view') === view);
            });
            if (view === 'list') {
                adjustAyDataTable();
            }
        });
    }

    $(document).on('click', '.ay-view-toggle [data-view]', function () {
        var view = $(this).attr('data-view');
        var wrapperId = $(this).closest('.ay-view-toggle').data('wrapper');
        var $wrapper = $('#' + wrapperId);
        $wrapper.attr('data-active-view', view);
        $(this).closest('.ay-view-toggle').find('[data-view]').removeClass('active');
        $(this).addClass('active');
        if (view === 'list') {
            adjustAyDataTable();
        }
    });

    $('#ayGridSearch').on('input', function () {
        filterAyGrid($(this).val());
    });

    initAyDataTable();
    syncAyViewToggleButtons();
    $(window).on('resize', syncAyViewToggleButtons);

    function toggleLevelBoxes(prefix, levelType) {
        var oBox = $('#' + prefix + 'OLevelBox');
        var aBox = $('#' + prefix + 'ALevelBox');
        oBox.removeClass('is-visible');
        aBox.removeClass('is-visible');
        if (levelType === 'O_Level') {
            oBox.addClass('is-visible');
        } else if (levelType === 'A_Level') {
            aBox.addClass('is-visible');
        }
    }

    function collectPayload($form, isEdit) {
        var payload = {
            is_current: $form.find('[name="is_current"]').is(':checked') ? 1 : 0,
            _token: csrfToken
        };

        if (isSecondary) {
            var levelType = $form.find('[name="level_type"]').val();
            payload.level_type = levelType;
            if (levelType === 'O_Level') {
                payload.year = $form.find('[name="year"]').val();
            } else if (levelType === 'A_Level') {
                payload.start_year = $form.find('[name="start_year"]').val();
                payload.end_year = $form.find('[name="end_year"]').val();
            }
        } else {
            payload.year = $form.find('[name="year"]').val();
        }

        return payload;
    }

    function reloadPageList() {
        window.location.reload();
    }

    if (isSecondary) {
        $('#reg_level_type').on('change', function () {
            toggleLevelBoxes('reg', $(this).val());
        });
        $('#edit_level_type').on('change', function () {
            toggleLevelBoxes('edit', $(this).val());
        });
        $('#reg_start_year').on('input', function () {
            var start = parseInt($(this).val(), 10);
            if (!isNaN(start)) {
                $('#reg_end_year').val(start + 1);
            }
        });
        $('#edit_start_year').on('input', function () {
            var start = parseInt($(this).val(), 10);
            if (!isNaN(start)) {
                $('#edit_end_year').val(start + 1);
            }
        });
    }

    $('#registerYearForm').on('submit', function (e) {
        e.preventDefault();
        var payload = collectPayload($(this), false);

        $.ajax({
            url: routes.store,
            method: 'POST',
            data: payload,
            success: function (res) {
                $('#registerYearModal').modal('hide');
                Swal.fire(swalOpts({
                    icon: 'success',
                    title: 'Saved',
                    text: res.message || 'Academic year registered successfully.'
                })).then(function () {
                    reloadPageList();
                });
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to register academic year.';
                Swal.fire(swalOpts({ icon: 'error', title: 'Error', text: msg }));
            }
        });
    });

    $('#registerYearModal').on('hidden.bs.modal', function () {
        $('#registerYearForm')[0].reset();
        $('#reg_year').val('2026');
        if (isSecondary) {
            $('#reg_start_year').val('2025');
            $('#reg_end_year').val('2026');
            toggleLevelBoxes('reg', '');
        }
    });

    $('#registerYearModal').on('show.bs.modal', function () {
        $('#reg_year').val('2026');
        if (isSecondary) {
            $('#reg_start_year').val('2025');
            $('#reg_end_year').val('2026');
        }
    });

    $(document).on('click', '.btn-view-year', function () {
        var id = $(this).data('id');
        $.get(routes.show + '/' + id, function (res) {
            if (!res.success) {
                Swal.fire(swalOpts({ icon: 'error', title: 'Error', text: res.message || 'Not found' }));
                return;
            }
            var y = res.year;
            $('#view_year_name').text(y.year_name || y.year);
            $('#view_is_current').html(y.is_current ? '<span class="ay-current-badge">Current</span>' : '<span class="ay-badge-muted">No</span>');
            if (isSecondary) {
                $('#view_level_type').text(y.level_type === 'A_Level' ? 'A Level' : 'O Level');
            }
            $('#view_created_at').text(y.created_at || '-');
            $('#view_updated_at').text(y.updated_at || '-');
            $('#viewYearModal').modal('show');
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load academic year.';
            Swal.fire(swalOpts({ icon: 'error', title: 'Error', text: msg }));
        });
    });

    $(document).on('click', '.btn-edit-year', function () {
        var id = $(this).data('id');
        $.get(routes.show + '/' + id, function (res) {
            if (!res.success) {
                Swal.fire(swalOpts({ icon: 'error', title: 'Error', text: res.message || 'Not found' }));
                return;
            }
            var y = res.year;
            $('#edit_academic_yearID').val(y.academic_yearID);
            $('#edit_is_current').prop('checked', !!y.is_current);

            if (isSecondary) {
                $('#edit_level_type').val(y.level_type || '');
                toggleLevelBoxes('edit', y.level_type || '');
                if (y.level_type === 'A_Level') {
                    $('#edit_start_year').val(y.start_year || y.year);
                    $('#edit_end_year').val(y.end_year || '');
                } else {
                    $('#edit_year').val(y.year);
                }
            } else {
                $('#edit_year').val(y.year);
            }

            $('#editYearModal').modal('show');
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load academic year.';
            Swal.fire(swalOpts({ icon: 'error', title: 'Error', text: msg }));
        });
    });

    $('#editYearForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#edit_academic_yearID').val();
        var payload = collectPayload($(this), true);

        $.ajax({
            url: routes.update + '/' + id + '/update',
            method: 'POST',
            data: payload,
            success: function (res) {
                $('#editYearModal').modal('hide');
                Swal.fire(swalOpts({
                    icon: 'success',
                    title: 'Updated',
                    text: res.message || 'Academic year updated successfully.'
                })).then(function () {
                    reloadPageList();
                });
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update academic year.';
                Swal.fire(swalOpts({ icon: 'error', title: 'Error', text: msg }));
            }
        });
    });

    $(document).on('click', '.btn-delete-year', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire(swalOpts({
            icon: 'warning',
            title: 'Delete Academic Year?',
            text: 'Delete "' + name + '"? This cannot be undone.',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            confirmButtonColor: '#940000',
            cancelButtonText: 'Cancel'
        })).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: routes.destroy + '/' + id + '/delete',
                method: 'POST',
                data: { _token: csrfToken },
                success: function (res) {
                    Swal.fire(swalOpts({
                        icon: 'success',
                        title: 'Deleted',
                        text: res.message || 'Academic year deleted successfully.'
                    })).then(function () {
                        reloadPageList();
                    });
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete academic year.';
                    Swal.fire(swalOpts({ icon: 'error', title: 'Error', text: msg }));
                }
            });
        });
    });
})(jQuery);
</script>
