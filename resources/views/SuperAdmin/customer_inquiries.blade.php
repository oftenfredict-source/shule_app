@extends('includes.superadmin_nav')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<style>
    :root { --primary-red: #940000; }
    .ci-hero {
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        border: 1px solid #f0dada;
        padding: 20px 24px;
        margin-bottom: 20px;
    }
    .ci-stat {
        background: #fff;
        border: 1px solid #f0dada;
        padding: 14px 16px;
        min-width: 140px;
    }
    .ci-stat strong { display:block; font-size:1.4rem; color:#222; }
    .ci-stat span { font-size:.75rem; text-transform:uppercase; color:#888; letter-spacing:.04em; }
    .ci-view-btn.active { background:#940000 !important; color:#fff !important; border-color:#940000 !important; }
    .ci-grid-card {
        background:#fff;
        border:1px solid #f0dada;
        box-shadow:0 6px 18px rgba(148,0,0,.06);
        height:100%;
        transition:transform .2s ease, box-shadow .2s ease;
    }
    .ci-grid-card:hover { transform:translateY(-3px); box-shadow:0 10px 24px rgba(148,0,0,.1); }
    .ci-grid-card.unread { border-left:4px solid #940000; background:#fffafa; }
    .ci-grid-card .ci-subject { font-weight:700; color:#333; }
    .ci-grid-card .ci-meta { font-size:.82rem; color:#777; }
    .ci-badge-unread {
        background:#940000;
        color:#fff;
        font-size:.68rem;
        font-weight:700;
        letter-spacing:.05em;
        text-transform:uppercase;
        padding:3px 8px;
    }
    #inquiriesTable tbody tr.unread-row { background:#fffafa; font-weight:600; }
    #inquiriesTable tbody tr.unread-row td:first-child { border-left:4px solid #940000; }
    .card, .btn, .form-control, .form-select, .alert, .badge, .modal-content { border-radius:0 !important; }
</style>

<div class="container-fluid mt-3">
    <div class="ci-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--primary-red);">Customer Inquiries</h4>
            <p class="text-muted small mb-0">Messages submitted from the public contact page</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="ci-stat text-center">
                <strong id="summaryUnread">{{ $customerInquiryUnread ?? 0 }}</strong>
                <span>Unread</span>
            </div>
            <div class="ci-stat text-center">
                <strong id="summaryTotal">{{ $customerInquiryTotal ?? 0 }}</strong>
                <span>Total</span>
            </div>
            <a href="{{ route('superAdminDashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
        </div>
    </div>

    <div class="card border mb-3">
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Period</label>
                    <select id="filterPeriod" class="form-select form-select-sm">
                        <option value="all">All time</option>
                        <option value="today">Today</option>
                        <option value="month">This month</option>
                        <option value="year">This year</option>
                        <option value="range">Date range</option>
                    </select>
                </div>
                <div class="col-md-2 range-field d-none">
                    <label class="form-label small fw-bold">From</label>
                    <input type="date" id="filterStart" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 range-field d-none">
                    <label class="form-label small fw-bold">To</label>
                    <input type="date" id="filterEnd" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="all">All</option>
                        <option value="unread">Unread only</option>
                        <option value="read">Read only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Search</label>
                    <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Name, email, phone, subject...">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" id="applyFilters"><i class="bx bx-filter-alt me-1"></i>Apply</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">Reset</button>
                    <div class="btn-group ms-auto">
                        <button type="button" class="btn btn-outline-dark btn-sm ci-view-btn active" data-view="list"><i class="bx bx-list-ul"></i></button>
                        <button type="button" class="btn btn-outline-dark btn-sm ci-view-btn" data-view="grid"><i class="bx bx-grid-alt"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="listView" class="card border">
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover align-middle mb-0 w-100" id="inquiriesTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Received</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="gridView" class="d-none">
        <div class="row g-3" id="gridContainer"></div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="gridPrev" disabled>Previous</button>
            <span class="small text-muted" id="gridPageInfo"></span>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="gridNext">Next</button>
        </div>
    </div>
</div>

<div class="modal fade" id="inquiryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#ed9999 0%,#940000 100%);">
                <h5 class="modal-title"><i class="bx bx-envelope me-2"></i>Customer Inquiry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="inquiryModalBody">
                <div class="text-center py-4 text-muted">Loading...</div>
            </div>
            <div class="modal-footer">
                <a href="#" id="inquiryReplyEmail" class="btn btn-primary btn-sm"><i class="bx bx-reply me-1"></i>Reply by Email</a>
                <a href="#" id="inquiryCallPhone" class="btn btn-outline-primary btn-sm d-none"><i class="bx bx-phone me-1"></i>Call</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
(function () {
    const routes = {
        data: @json(route('superadmin.customer_inquiries.data')),
        show: @json(url('super-admin/customer-inquiries')),
    };

    let currentView = 'list';
    let gridPage = 0;
    const gridPageSize = 12;
    let gridLastFiltered = 0;
    let table = null;

    function filterParams() {
        return {
            period: $('#filterPeriod').val(),
            start_date: $('#filterStart').val(),
            end_date: $('#filterEnd').val(),
            status: $('#filterStatus').val(),
            search: $('#filterSearch').val(),
        };
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function updateSummary(summary) {
        if (!summary) return;
        $('#summaryUnread').text(summary.unread ?? 0);
        $('#summaryTotal').text(summary.total ?? 0);
        if (window.refreshCustomerInquiryNotifications) {
            window.refreshCustomerInquiryNotifications();
        }
    }

    function renderModal(data) {
        $('#inquiryModalBody').html(
            '<div class="row g-3 mb-3">' +
                '<div class="col-md-6"><div class="small text-muted">Customer</div><div class="fw-bold">' + escapeHtml(data.name) + '</div></div>' +
                '<div class="col-md-6"><div class="small text-muted">Received</div><div>' + escapeHtml(data.created_at) + '</div></div>' +
                '<div class="col-md-6"><div class="small text-muted">Email</div><div><a href="mailto:' + escapeHtml(data.email) + '">' + escapeHtml(data.email) + '</a></div></div>' +
                '<div class="col-md-6"><div class="small text-muted">Phone</div><div>' + escapeHtml(data.phone || '-') + '</div></div>' +
                '<div class="col-12"><div class="small text-muted">Subject</div><div class="fw-bold">' + escapeHtml(data.subject) + '</div></div>' +
            '</div>' +
            '<div class="border p-3" style="background:#fffafa; white-space:pre-wrap;">' + escapeHtml(data.message) + '</div>'
        );
        $('#inquiryReplyEmail').attr('href', 'mailto:' + encodeURIComponent(data.email) + '?subject=' + encodeURIComponent('Re: ' + data.subject));
        if (data.phone) {
            $('#inquiryCallPhone').removeClass('d-none').attr('href', 'tel:' + data.phone);
        } else {
            $('#inquiryCallPhone').addClass('d-none');
        }
    }

    window.openCustomerInquiry = function (id) {
        const modal = new bootstrap.Modal(document.getElementById('inquiryModal'));
        $('#inquiryModalBody').html('<div class="text-center py-4 text-muted">Loading...</div>');
        modal.show();

        $.get(routes.show + '/' + id, function (res) {
            if (!res.success) return;
            renderModal(res.data);
            if (currentView === 'list' && table) table.ajax.reload(null, false);
            else if (currentView === 'grid') loadGrid();
            updateSummary({ unread: Math.max(0, parseInt($('#summaryUnread').text(), 10) - (res.data.is_read ? 0 : 1)), total: $('#summaryTotal').text() });
        });
    };

    function initTable() {
        table = $('#inquiriesTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: true,
            pageLength: 10,
            order: [],
            ajax: {
                url: routes.data,
                data: function (d) {
                    const f = filterParams();
                    d.period = f.period;
                    d.start_date = f.start_date;
                    d.end_date = f.end_date;
                    d.status = f.status;
                    d.filter_search = f.search;
                },
                dataSrc: function (json) {
                    updateSummary(json.summary);
                    return json.data;
                }
            },
            columns: [
                {
                    data: 'name',
                    render: function (data, type, row) {
                        const badge = row.is_read ? '' : '<span class="ci-badge-unread ms-1">Unread</span>';
                        return '<div>' + escapeHtml(data) + badge + '</div>';
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return '<div class="small">' + escapeHtml(row.email) + '</div><div class="small text-muted">' + escapeHtml(row.phone || '-') + '</div>';
                    }
                },
                { data: 'subject', render: function (d) { return escapeHtml(d); } },
                { data: 'message_preview', render: function (d) { return '<span class="text-muted small">' + escapeHtml(d) + '</span>'; } },
                {
                    data: null,
                    render: function (data, type, row) {
                        return '<div class="small">' + escapeHtml(row.created_at) + '</div><div class="text-muted smaller">' + escapeHtml(row.created_human) + '</div>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return '<button type="button" class="btn btn-sm btn-outline-primary" onclick="openCustomerInquiry(' + row.id + ')"><i class="bx bx-show"></i> View</button>';
                    }
                }
            ],
            createdRow: function (row, data) {
                if (!data.is_read) $(row).addClass('unread-row');
            }
        });
    }

    function loadGrid() {
        const start = gridPage * gridPageSize;
        const f = filterParams();
        $.get(routes.data, {
            start: start,
            length: gridPageSize,
            period: f.period,
            start_date: f.start_date,
            end_date: f.end_date,
            status: f.status,
            filter_search: f.search,
        }, function (res) {
            if (!res.success) return;
            updateSummary(res.summary);
            gridLastFiltered = res.recordsFiltered;
            const $container = $('#gridContainer').empty();

            if (!res.data.length) {
                $container.html('<div class="col-12"><div class="alert alert-light border text-center mb-0">No inquiries found for the selected filters.</div></div>');
            } else {
                res.data.forEach(function (row) {
                    const unreadClass = row.is_read ? '' : ' unread';
                    const badge = row.is_read ? '' : '<span class="ci-badge-unread">Unread</span>';
                    $container.append(
                        '<div class="col-md-6 col-xl-4">' +
                            '<div class="ci-grid-card p-3' + unreadClass + '">' +
                                '<div class="d-flex justify-content-between align-items-start gap-2 mb-2">' +
                                    '<div class="ci-subject">' + escapeHtml(row.subject) + '</div>' + badge +
                                '</div>' +
                                '<div class="ci-meta mb-2"><strong>' + escapeHtml(row.name) + '</strong></div>' +
                                '<div class="ci-meta mb-1"><i class="bx bx-envelope"></i> ' + escapeHtml(row.email) + '</div>' +
                                '<div class="ci-meta mb-2"><i class="bx bx-phone"></i> ' + escapeHtml(row.phone || '-') + '</div>' +
                                '<p class="small text-muted mb-3">' + escapeHtml(row.message_preview) + '</p>' +
                                '<div class="d-flex justify-content-between align-items-center">' +
                                    '<span class="small text-muted">' + escapeHtml(row.created_at) + '</span>' +
                                    '<button type="button" class="btn btn-sm btn-outline-primary" onclick="openCustomerInquiry(' + row.id + ')">View more</button>' +
                                '</div>' +
                            '</div>' +
                        '</div>'
                    );
                });
            }

            const totalPages = Math.max(1, Math.ceil(gridLastFiltered / gridPageSize));
            $('#gridPageInfo').text('Page ' + (gridPage + 1) + ' of ' + totalPages);
            $('#gridPrev').prop('disabled', gridPage <= 0);
            $('#gridNext').prop('disabled', (gridPage + 1) >= totalPages);
        });
    }

    function switchView(view) {
        currentView = view;
        $('.ci-view-btn').removeClass('active');
        $('.ci-view-btn[data-view="' + view + '"]').addClass('active');
        if (view === 'grid') {
            $('#listView').addClass('d-none');
            $('#gridView').removeClass('d-none');
            loadGrid();
        } else {
            $('#gridView').addClass('d-none');
            $('#listView').removeClass('d-none');
            if (!table) initTable();
            else table.ajax.reload();
        }
    }

    $('#filterPeriod').on('change', function () {
        $('.range-field').toggleClass('d-none', $(this).val() !== 'range');
    });

    $('#applyFilters').on('click', function () {
        gridPage = 0;
        if (currentView === 'grid') loadGrid();
        else if (table) table.ajax.reload();
    });

    $('#resetFilters').on('click', function () {
        $('#filterPeriod').val('all');
        $('#filterStart, #filterEnd, #filterSearch').val('');
        $('#filterStatus').val('all');
        $('.range-field').addClass('d-none');
        gridPage = 0;
        if (currentView === 'grid') loadGrid();
        else if (table) table.ajax.reload();
    });

    $('.ci-view-btn').on('click', function () {
        switchView($(this).data('view'));
    });

    $('#gridPrev').on('click', function () {
        if (gridPage > 0) { gridPage--; loadGrid(); }
    });
    $('#gridNext').on('click', function () {
        gridPage++;
        loadGrid();
    });

    initTable();

    const openId = new URLSearchParams(window.location.search).get('open');
    if (openId) {
        window.openCustomerInquiry(openId);
    }
})();
</script>
@endpush
