
@extends('layouts.vali')

@section('title', 'Fingerprint Device Settings')

@section('content')

<style>
    .fp-settings {
        --fp-white: #FFFFFF;
        --fp-red: #940000;
        --fp-soft: #FFF5F5;
        --fp-header-gradient: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }
    .fp-settings *:not(.fa) {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }
    .fp-settings .fa {
        font-family: FontAwesome !important;
    }
    .fp-settings .card {
        background: var(--fp-white);
        border: 1px solid #f0e0e0;
    }
    .fp-settings .card-header {
        background: var(--fp-header-gradient);
        border-bottom: 1px solid #f0e0e0;
    }
    .fp-settings .card-title {
        color: var(--fp-red);
        font-weight: 600;
    }
    .fp-settings .tab-content {
        padding: 20px;
        background: var(--fp-white);
        border-radius: 0 0 5px 5px;
        margin-top: 0;
        border: 1px solid #f0e0e0;
        border-top: none;
        width: 100%;
    }
    .fp-settings .nav-tabs {
        border-bottom: 1px solid #f0e0e0;
        display: flex;
        width: 100%;
    }
    .fp-settings .fp-tabs-two .nav-item {
        flex: 1;
    }
    .fp-settings .fp-tabs-two .nav-link {
        width: 100%;
        text-align: center;
    }
    .fp-settings .nav-tabs .nav-link {
        color: var(--fp-red);
        border-color: transparent;
        background: var(--fp-white);
    }
    .fp-settings .nav-tabs .nav-link:hover {
        background: var(--fp-soft);
        color: var(--fp-red);
        border-color: #f0e0e0 #f0e0e0 transparent;
    }
    .fp-settings .nav-tabs .nav-link.active {
        background: var(--fp-header-gradient);
        color: var(--fp-red);
        border-color: #f0e0e0 #f0e0e0 var(--fp-white);
        font-weight: 600;
    }
    .fp-settings .btn-primary,
    .fp-settings .device-view-toggle .btn.active {
        background-color: var(--fp-red);
        border-color: var(--fp-red);
        color: var(--fp-white);
    }
    .fp-settings .btn-primary:hover,
    .fp-settings .device-view-toggle .btn.active:hover {
        background-color: #7a0000;
        border-color: #7a0000;
        color: var(--fp-white);
    }
    .fp-settings .device-view-toggle .btn-outline-secondary {
        color: var(--fp-red);
        border-color: #e8d4d4;
    }
    .fp-settings .device-view-toggle .btn-outline-secondary:hover {
        background: var(--fp-soft);
        color: var(--fp-red);
        border-color: var(--fp-red);
    }
    .fp-settings .fp-section-title {
        color: var(--fp-red);
        font-weight: 600;
    }
    .fp-settings .fp-devices-table {
        background: var(--fp-white);
        margin-bottom: 0;
    }
    .fp-settings .fp-devices-table thead th {
        background: var(--fp-header-gradient);
        color: var(--fp-red);
        border-color: #f0e0e0;
        font-weight: 600;
        vertical-align: middle;
    }
    .fp-settings .fp-devices-table tbody tr:hover {
        background: var(--fp-soft);
    }
    .fp-settings .fp-devices-table td {
        vertical-align: middle;
        border-color: #f0e0e0;
    }
    .fp-settings .device-dots-btn {
        background: transparent;
        border: none;
        padding: 4px 8px;
        line-height: 1;
        color: var(--fp-red);
    }
    .fp-settings .device-dots-btn:hover,
    .fp-settings .device-dots-btn:focus {
        background: var(--fp-soft);
        outline: none;
        box-shadow: none;
    }
    .fp-settings .device-dots {
        display: inline-flex;
        flex-direction: column;
        gap: 3px;
        align-items: center;
        justify-content: center;
        width: 18px;
    }
    .fp-settings .device-dots span {
        display: block;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: var(--fp-red);
    }
    .fp-settings .device-actions-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        z-index: 1050;
        background: #FFFFFF;
        border: 1px solid #f0e0e0;
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.08);
        min-width: 140px;
        border-radius: 4px;
        padding: 4px 0;
    }
    .fp-settings .device-actions-menu.show {
        display: block;
    }
    .fp-settings #deviceModal .modal-body {
        position: relative;
    }
    .fp-settings .device-form-ajax-loading {
        position: absolute;
        inset: 0;
        z-index: 10;
        background: rgba(255, 255, 255, 0.88);
        display: none;
        align-items: center;
        justify-content: center;
    }
    .fp-settings .device-form-ajax-loading-inner {
        text-align: center;
        color: var(--fp-red);
        font-weight: 600;
    }
    .fp-settings .device-form-ajax-loading .spinner-border {
        color: var(--fp-red) !important;
        border-color: var(--fp-red);
        border-right-color: transparent;
    }
    .fp-settings .device-actions-menu .dropdown-item {
        display: block;
        width: 100%;
        text-align: left;
        border: none;
        background: transparent;
        padding: 8px 16px;
        cursor: pointer;
        color: #333;
        font-size: 14px;
    }
    .fp-settings .device-actions-menu .dropdown-item:hover {
        background: var(--fp-soft);
        color: var(--fp-red);
    }
    .fp-settings .device-actions-menu .dropdown-item.text-danger {
        color: #b30000 !important;
    }
    .fp-settings .device-actions-menu .dropdown-item.text-danger:hover {
        background: var(--fp-soft);
        color: #940000 !important;
    }
    .fp-settings .device-actions-menu .dropdown-item i {
        width: 18px;
        margin-right: 8px;
        color: var(--fp-red);
        text-align: center;
    }
    .fp-settings .device-actions-menu .dropdown-item.text-danger i {
        color: #b30000;
    }
    .fp-settings .device-row-actions {
        position: relative;
        text-align: center;
    }
    .step {
        background: var(--fp-white);
        border-left: 4px solid #940000;
        padding: 20px;
        margin: 20px 0;
        border-radius: 5px;
        border: 1px solid #f0e0e0;
        border-left: 4px solid #940000;
    }
    .step h3 {
        color: #940000;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .step-number {
        background: #940000;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .info-box {
        background: #FFF5F5;
        border-left: 4px solid #940000;
        padding: 15px;
        margin: 15px 0;
        border-radius: 4px;
    }
    .config-box {
        background: #FFF5F5;
        border: 1px solid #f0e0e0;
        padding: 20px;
        margin: 20px 0;
        border-radius: 5px;
    }
    .config-item {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        background: white;
        margin: 5px 0;
        border-radius: 3px;
        border: 1px solid #f0e0e0;
    }
    .config-label {
        font-weight: bold;
        color: #333;
    }
    .config-value {
        font-family: 'Courier New', monospace;
        color: #940000;
        font-weight: bold;
    }
    .copy-btn {
        background: #940000;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
        margin-left: 10px;
    }
    .copy-btn:hover {
        background: #7a0000;
    }
    .endpoint {
        background: #FFF5F5;
        padding: 15px;
        margin: 15px 0;
        border-radius: 5px;
        border-left: 4px solid #940000;
    }
    .result {
        margin-top: 20px;
        padding: 15px;
        border-radius: 5px;
        display: none;
    }
    .result.show {
        display: block;
    }
    .result.success {
        background: #f6fff8;
        border: 1px solid #d4edda;
        color: #155724;
    }
    .result.error {
        background: #FFF5F5;
        border: 1px solid #f0e0e0;
        color: #940000;
    }
    .result.info {
        background: #FFF5F5;
        border: 1px solid #f0e0e0;
        color: #333;
    }
    .device-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }
    .device-card {
        border: 1px solid #f0e0e0;
        border-radius: 6px;
        padding: 16px;
        background: #FFFFFF;
        position: relative;
        transition: background 0.2s ease;
    }
    .device-card:hover {
        background: #FFF5F5;
    }
    .device-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 8px;
    }
    .device-card h5 {
        margin-bottom: 0;
        color: #940000;
        font-weight: 600;
        flex: 1;
    }
    .device-card .meta {
        font-size: 13px;
        color: #555;
        margin-bottom: 4px;
    }
    .device-card .meta strong {
        color: #940000;
    }
    .device-status-active {
        color: #940000;
        font-weight: 600;
    }
    .device-status-inactive {
        color: #999;
        font-weight: 600;
    }
    .fp-settings #devicesEmptyState {
        background: #FFF5F5;
        border: 1px solid #f0e0e0;
        color: #333;
    }
    #testerDeviceSelect {
        width: 100%;
    }
    .select2-container {
        width: 100% !important;
    }
</style>

<div class="container-fluid fp-settings">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Fingerprint Device Settings</h3>
                </div>
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs fp-tabs-two" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#schoolDevices" role="tab">
                                Devices
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#deviceTester" role="tab">
                                Device Tester
                            </a>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <!-- School Devices Tab -->
                        <div class="tab-pane fade show active" id="schoolDevices" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
                                <div>
                                    <h3 class="mb-1 fp-section-title">School Fingerprint Devices</h3>
                                    <p class="text-muted mb-0">Register and manage biometric devices for this school.</p>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="openDeviceModal()">Register New Device</button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2" style="gap:10px;">
                                <input type="text" id="deviceSearchInput" class="form-control flex-grow-1" style="min-width:200px;" placeholder="Search devices by label, IP, brand...">
                                <div class="btn-group device-view-toggle" role="group">
                                    <button type="button" class="btn btn-outline-secondary active" id="deviceListViewBtn" onclick="setDeviceView('list')">List</button>
                                    <button type="button" class="btn btn-outline-secondary" id="deviceGridViewBtn" onclick="setDeviceView('grid')">Grid</button>
                                </div>
                            </div>

                            <div id="devicesListView">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0 fp-devices-table">
                                        <thead>
                                            <tr>
                                                <th>Label</th>
                                                <th>Brand</th>
                                                <th>IP Address</th>
                                                <th>Status</th>
                                                <th style="width:70px;text-align:center;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="devicesTableBody">
                                            <tr><td colspan="5" class="text-center text-muted">Loading devices...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="devicesGridView" class="device-grid" style="display:none;"></div>
                            <div id="devicesEmptyState" class="alert alert-info mt-3" style="display:none;">
                                No devices registered yet. Click <strong>Register New Device</strong> to add one.
                            </div>
                        </div>

                        <!-- Device Tester Tab -->
                        <div class="tab-pane fade" id="deviceTester" role="tabpanel">
                            <div class="tile">
                                <h3 class="tile-title">Device Tester</h3>
                                <div class="alert alert-info">
                                    <strong>Note:</strong> Select a registered school device below. Connection details are loaded from the database for this school.
                                    <br><br>
                                    <strong>ShuleXpert server</strong> (this website): <code>{{ request()->getSchemeAndHttpHost() }}</code>
                                    <br>
                                    <strong>Fingerprint device</strong> (ZKTeco machine): IP/port shown after you select a device — usually port <code>4370</code>, not <code>8002</code>.
                                </div>

                                <form id="deviceForm">
                                    <div class="form-group">
                                        <label for="testerDeviceSelect">Select Device</label>
                                        <select id="testerDeviceSelect" class="form-control">
                                            <option value="">-- Select a registered device --</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip">Device IP Address</label>
                                        <input type="text" id="ip" name="ip" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="port">Port</label>
                                        <input type="number" id="port" name="port" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Comm Key (Password)</label>
                                        <input type="text" id="password" name="password" class="form-control" readonly>
                                        <small class="form-text text-muted">Loaded from the selected registered device</small>
                                    </div>
                                    <div class="btn-group btn-group-justified" role="group" style="margin-top: 15px;">
                                        <button type="button" class="btn btn-primary" onclick="testConnection()">Test Connection</button>
                                        <button type="button" class="btn btn-success" onclick="getDeviceInfo()">Get Device Info</button>
                                        <button type="button" class="btn btn-info" onclick="getAttendance()">Get Attendance</button>
                                    </div>
                                </form>

                                <div id="loading" class="text-center" style="display:none; margin-top: 20px;">
                                    <p>Connecting to device...</p>
                                </div>

                                <div id="result" style="margin-top: 20px; display:none;"></div>
                            </div>
                        </div>

                            </div>
                            </div>
                                        </div>
                                        </div>
                                        </div>
                                    </div>
                                    
<!-- Device Register / Edit Modal -->
<div class="modal fade" id="deviceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deviceModalTitle">Register New Device</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                                    </div>
            <div class="modal-body" id="deviceModalBody">
                <div id="deviceFormAjaxLoading" class="device-form-ajax-loading" aria-hidden="true">
                    <div class="device-form-ajax-loading-inner">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                                </div>
                        <p class="mb-0 mt-2" id="deviceFormLoadingText">Saving device...</p>
                            </div>
                            </div>
                <form id="deviceRegisterForm">
                    <input type="hidden" id="deviceFormId" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deviceLabel">Device Label <span class="text-danger">*</span></label>
                                <input type="text" id="deviceLabel" class="form-control" maxlength="120" placeholder="e.g. Students Gate, DEV A" required>
                                </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deviceBrand">Brand <span class="text-danger">*</span></label>
                                <select id="deviceBrand" class="form-control" required>
                                    <option value="ZKTeco" selected>ZKTeco</option>
                                    <option value="Anviz">Anviz</option>
                                    <option value="Suprema">Suprema</option>
                                    <option value="Other">Other</option>
                                </select>
                                </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deviceIp">IP Address <span class="text-danger">*</span></label>
                                <input type="text" id="deviceIp" class="form-control" placeholder="192.168.100.108" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="devicePort">Port <span class="text-danger">*</span></label>
                                <input type="number" id="devicePort" class="form-control" value="4370" min="1" max="65535" required>
                            </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deviceCommKey">Comm Key</label>
                                <input type="text" id="deviceCommKey" class="form-control" value="0" maxlength="16">
                            </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deviceUsageType">Usage / Category</label>
                                <select id="deviceUsageType" class="form-control">
                                    <option value="">General</option>
                                    <option value="students">Students</option>
                                    <option value="teachers">Teachers</option>
                                    <option value="staff">Staff</option>
                                    <option value="classroom">Classroom</option>
                                    <option value="other">Other</option>
                                </select>
                        </div>
                            </div>
                        <div class="col-md-6">
                                <div class="form-group">
                                <label for="deviceLocation">Location</label>
                                <input type="text" id="deviceLocation" class="form-control" maxlength="150" placeholder="e.g. Main Gate, Block A">
                                </div>
                                </div>
                        <div class="col-md-6">
                                <div class="form-group">
                                <label for="deviceSerial">Serial Number</label>
                                <input type="text" id="deviceSerial" class="form-control" maxlength="80">
                                </div>
                                </div>
                        <div class="col-md-6">
                                <div class="form-group">
                                <label for="deviceStatus">Status</label>
                                <select id="deviceStatus" class="form-control">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                </div>
                            </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="deviceNotes">Notes</label>
                                <textarea id="deviceNotes" class="form-control" rows="2" maxlength="2000"></textarea>
                            </div>
                        </div>
                            </div>
                </form>
                            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDeviceBtn" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveDeviceBtn" onclick="saveDevice()">Save Device</button>
                            </div>
                                </div>
                                </div>
                            </div>

<!-- Device View Modal -->
<div class="modal fade" id="deviceViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Device Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
            <div class="modal-body" id="deviceViewBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
    </div>
</div>

@include('includes.footer')

@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const deviceRoutes = {
        index: '{{ route("zkteco.devices.index") }}',
        store: '{{ route("zkteco.devices.store") }}',
        show: '{{ url("zkteco/devices") }}',
        update: '{{ url("zkteco/devices") }}',
        destroy: '{{ url("zkteco/devices") }}'
    };

    let schoolDevices = [];
    let deviceViewMode = 'list';
    let deviceModalMode = 'create';

    function getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatUsageType(value) {
        if (!value) return 'General';
        return value.charAt(0).toUpperCase() + value.slice(1);
    }

    function getFilteredDevices() {
        const q = (document.getElementById('deviceSearchInput')?.value || '').trim().toLowerCase();
        if (!q) return schoolDevices;
        return schoolDevices.filter(function (d) {
            return [d.label, d.brand, d.ip, d.usage_type, d.location, d.status]
                .join(' ')
                .toLowerCase()
                .includes(q);
        });
    }

    function setDeviceView(mode) {
        deviceViewMode = mode;
        document.getElementById('deviceListViewBtn').classList.toggle('active', mode === 'list');
        document.getElementById('deviceGridViewBtn').classList.toggle('active', mode === 'grid');
        document.getElementById('devicesListView').style.display = mode === 'list' ? 'block' : 'none';
        document.getElementById('devicesGridView').style.display = mode === 'grid' ? 'grid' : 'none';
        renderDevices();
    }

    function deviceActionsMenu(id) {
        return '<div class="dropdown device-row-actions">' +
            '<button type="button" class="btn btn-sm device-dots-btn" title="Actions" onclick="toggleDeviceActionsMenu(event, this)">' +
                '<span class="device-dots"><span></span><span></span><span></span></span>' +
            '</button>' +
            '<div class="dropdown-menu dropdown-menu-right device-actions-menu">' +
                '<button type="button" class="dropdown-item" onclick="closeDeviceActionMenus(); viewDevice(' + id + ')"><i class="fa fa-eye"></i> View</button>' +
                '<button type="button" class="dropdown-item" onclick="closeDeviceActionMenus(); openDeviceModal(' + id + ')"><i class="fa fa-pencil"></i> Edit</button>' +
                '<button type="button" class="dropdown-item text-danger" onclick="closeDeviceActionMenus(); deleteDevice(' + id + ')"><i class="fa fa-trash"></i> Delete</button>' +
            '</div>' +
        '</div>';
    }

    function closeDeviceActionMenus() {
        document.querySelectorAll('.device-actions-menu.show').forEach(function (menu) {
            menu.classList.remove('show');
        });
    }

    function toggleDeviceActionsMenu(event, button) {
        event.preventDefault();
        event.stopPropagation();
        const menu = button.nextElementSibling;
        const willOpen = !menu.classList.contains('show');
        closeDeviceActionMenus();
        if (willOpen) {
            menu.classList.add('show');
        }
    }

    function renderDevices() {
        const devices = getFilteredDevices();
        const tbody = document.getElementById('devicesTableBody');
        const grid = document.getElementById('devicesGridView');
        const empty = document.getElementById('devicesEmptyState');

        if (!devices.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No devices found.</td></tr>';
            grid.innerHTML = '';
            empty.style.display = schoolDevices.length ? 'none' : 'block';
            return;
        }

        empty.style.display = 'none';

        tbody.innerHTML = devices.map(function (d) {
            const statusClass = d.status === 'active' ? 'device-status-active' : 'device-status-inactive';
            return '<tr>' +
                '<td>' + escapeHtml(d.label) + '</td>' +
                '<td>' + escapeHtml(d.brand) + '</td>' +
                '<td>' + escapeHtml(d.ip) + '</td>' +
                '<td class="' + statusClass + '">' + escapeHtml(d.status) + '</td>' +
                '<td>' + deviceActionsMenu(d.id) + '</td>' +
            '</tr>';
        }).join('');

        grid.innerHTML = devices.map(function (d) {
            const statusClass = d.status === 'active' ? 'device-status-active' : 'device-status-inactive';
            return '<div class="device-card">' +
                '<div class="device-card-header">' +
                    '<h5>' + escapeHtml(d.label) + '</h5>' +
                    deviceActionsMenu(d.id) +
                '</div>' +
                '<div class="meta"><strong>Brand:</strong> ' + escapeHtml(d.brand) + '</div>' +
                '<div class="meta"><strong>IP:</strong> ' + escapeHtml(d.ip) + ':' + escapeHtml(d.port) + '</div>' +
                '<div class="meta"><strong>Status:</strong> <span class="' + statusClass + '">' + escapeHtml(d.status) + '</span></div>' +
                '<div class="meta"><strong>Usage:</strong> ' + escapeHtml(formatUsageType(d.usage_type)) + '</div>' +
            '</div>';
        }).join('');
    }

    function populateTesterDeviceSelect() {
        const $select = $('#testerDeviceSelect');
        const current = $select.val();
        $select.empty().append('<option value="">-- Select a registered device --</option>');

        schoolDevices.filter(function (d) { return d.status === 'active'; }).forEach(function (d) {
            const text = d.label + ' (' + d.ip + ')';
            $select.append('<option value="' + d.id + '">' + escapeHtml(text) + '</option>');
        });

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.val(current).trigger('change');
            } else {
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Search and select a device'
            });
            $select.val(current).trigger('change');
        }
    }

    function applySelectedTesterDevice() {
        const id = $('#testerDeviceSelect').val();
        const device = schoolDevices.find(function (d) { return String(d.id) === String(id); });

        document.getElementById('ip').value = device ? device.ip : '';
        document.getElementById('port').value = device ? device.port : '';
        document.getElementById('password').value = device ? (device.comm_key || '0') : '';
    }

    async function loadSchoolDevices() {
        try {
            const response = await fetch(deviceRoutes.index, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Failed to load devices');

            schoolDevices = data.devices || [];
            renderDevices();
            populateTesterDeviceSelect();
        } catch (error) {
            document.getElementById('devicesTableBody').innerHTML =
                '<tr><td colspan="5" class="text-center text-danger">' + escapeHtml(error.message) + '</td></tr>';
        }
    }

    function resetDeviceForm() {
        document.getElementById('deviceFormId').value = '';
        document.getElementById('deviceLabel').value = '';
        document.getElementById('deviceBrand').value = 'ZKTeco';
        document.getElementById('deviceIp').value = '';
        document.getElementById('devicePort').value = '4370';
        document.getElementById('deviceCommKey').value = '0';
        document.getElementById('deviceUsageType').value = '';
        document.getElementById('deviceLocation').value = '';
        document.getElementById('deviceSerial').value = '';
        document.getElementById('deviceStatus').value = 'active';
        document.getElementById('deviceNotes').value = '';
    }

    function fillDeviceForm(device) {
        document.getElementById('deviceFormId').value = device.id;
        document.getElementById('deviceLabel').value = device.label || '';
        document.getElementById('deviceBrand').value = device.brand || 'ZKTeco';
        document.getElementById('deviceIp').value = device.ip || '';
        document.getElementById('devicePort').value = device.port || 4370;
        document.getElementById('deviceCommKey').value = device.comm_key || '0';
        document.getElementById('deviceUsageType').value = device.usage_type || '';
        document.getElementById('deviceLocation').value = device.location || '';
        document.getElementById('deviceSerial').value = device.serial_number || '';
        document.getElementById('deviceStatus').value = device.status || 'active';
        document.getElementById('deviceNotes').value = device.notes || '';
    }

    function collectDevicePayload() {
        return {
            label: document.getElementById('deviceLabel').value.trim(),
            brand: document.getElementById('deviceBrand').value,
            ip: document.getElementById('deviceIp').value.trim(),
            port: parseInt(document.getElementById('devicePort').value, 10) || 4370,
            comm_key: document.getElementById('deviceCommKey').value.trim() || '0',
            usage_type: document.getElementById('deviceUsageType').value || null,
            location: document.getElementById('deviceLocation').value.trim() || null,
            serial_number: document.getElementById('deviceSerial').value.trim() || null,
            status: document.getElementById('deviceStatus').value,
            notes: document.getElementById('deviceNotes').value.trim() || null
        };
    }

    function openDeviceModal(id = null) {
        deviceModalMode = id ? 'edit' : 'create';
        resetDeviceForm();
        setDeviceFormSaving(false);
        document.getElementById('deviceModalTitle').textContent = id ? 'Edit Device' : 'Register New Device';
        const saveBtn = document.getElementById('saveDeviceBtn');
        saveBtn.textContent = id ? 'Update Device' : 'Save Device';
        saveBtn.dataset.defaultHtml = saveBtn.textContent;

        if (id) {
            const device = schoolDevices.find(function (d) { return d.id === id; });
            if (device) fillDeviceForm(device);
        }

        $('#deviceModal').modal('show');
    }

    function setDeviceFormSaving(isSaving) {
        const btn = document.getElementById('saveDeviceBtn');
        const cancelBtn = document.getElementById('cancelDeviceBtn');
        const overlay = document.getElementById('deviceFormAjaxLoading');
        const loadingText = document.getElementById('deviceFormLoadingText');
        const closeBtn = document.querySelector('#deviceModal .close');
        const id = document.getElementById('deviceFormId').value;
        const isUpdate = !!id;
        const savingLabel = isUpdate ? 'Updating device...' : 'Saving device...';
        const btnIdleLabel = isUpdate ? 'Update Device' : 'Save Device';

        if (overlay) {
            overlay.style.display = isSaving ? 'flex' : 'none';
            overlay.setAttribute('aria-hidden', isSaving ? 'false' : 'true');
        }
        if (loadingText) {
            loadingText.textContent = savingLabel;
        }

        document.querySelectorAll('#deviceRegisterForm input, #deviceRegisterForm select, #deviceRegisterForm textarea')
            .forEach(function (el) {
                el.disabled = isSaving;
            });

        if (btn) {
            if (isSaving) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> ' +
                    (isUpdate ? 'Updating...' : 'Saving...');
            } else {
                btn.disabled = false;
                btn.textContent = btn.dataset.defaultHtml || btnIdleLabel;
            }
        }

        if (cancelBtn) {
            cancelBtn.disabled = isSaving;
        }
        if (closeBtn) {
            closeBtn.disabled = isSaving;
            closeBtn.style.opacity = isSaving ? '0.45' : '1';
        }
    }

    async function saveDevice() {
        const payload = collectDevicePayload();
        if (!payload.label || !payload.ip) {
            alert('Please fill in required fields.');
            return;
        }

        const id = document.getElementById('deviceFormId').value;
        const url = id ? (deviceRoutes.update + '/' + id) : deviceRoutes.store;
        const method = id ? 'PUT' : 'POST';

        setDeviceFormSaving(true);

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to save device');
            }

            $('#deviceModal').modal('hide');
            await loadSchoolDevices();
        } catch (error) {
            alert(error.message);
        } finally {
            setDeviceFormSaving(false);
        }
    }

    async function viewDevice(id) {
        try {
            const response = await fetch(deviceRoutes.show + '/' + id, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Failed to load device');

            const d = data.device;
            document.getElementById('deviceViewBody').innerHTML =
                '<table class="table table-sm table-bordered mb-0">' +
                '<tr><th>Label</th><td>' + escapeHtml(d.label) + '</td></tr>' +
                '<tr><th>Brand</th><td>' + escapeHtml(d.brand) + '</td></tr>' +
                '<tr><th>IP Address</th><td>' + escapeHtml(d.ip) + '</td></tr>' +
                '<tr><th>Port</th><td>' + escapeHtml(d.port) + '</td></tr>' +
                '<tr><th>Comm Key</th><td>' + escapeHtml(d.comm_key) + '</td></tr>' +
                '<tr><th>Usage</th><td>' + escapeHtml(formatUsageType(d.usage_type)) + '</td></tr>' +
                '<tr><th>Location</th><td>' + escapeHtml(d.location || '-') + '</td></tr>' +
                '<tr><th>Serial Number</th><td>' + escapeHtml(d.serial_number || '-') + '</td></tr>' +
                '<tr><th>Status</th><td>' + escapeHtml(d.status) + '</td></tr>' +
                '<tr><th>Notes</th><td>' + escapeHtml(d.notes || '-') + '</td></tr>' +
                '</table>';
            $('#deviceViewModal').modal('show');
        } catch (error) {
            alert(error.message);
        }
    }

    async function deleteDevice(id) {
        const device = schoolDevices.find(function (d) { return d.id === id; });
        if (!device) return;
        if (!confirm('Delete device "' + device.label + '"?')) return;

        try {
            const response = await fetch(deviceRoutes.destroy + '/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to delete device');
            }
            await loadSchoolDevices();
            applySelectedTesterDevice();
        } catch (error) {
            alert(error.message);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadSchoolDevices();
        document.getElementById('deviceSearchInput').addEventListener('input', renderDevices);
        document.addEventListener('click', closeDeviceActionMenus);
        $('#testerDeviceSelect').on('change', applySelectedTesterDevice);
        $('a[href="#deviceTester"]').on('shown.bs.tab', function () {
            populateTesterDeviceSelect();
        });
    });

    // Device Tester Functions
    function showLoading(show) {
        var el = document.getElementById('loading');
        if (!el) return;
        el.style.display = show ? 'block' : 'none';
    }

    function showResult(success, message, data = null) {
        const resultDiv = document.getElementById('result');
        if (!resultDiv) return;
        resultDiv.style.display = 'block';
        
        let content = '<div class="alert ' + (success ? 'alert-success' : 'alert-danger') + '">';
        content += '<strong>' + (success ? 'Success' : 'Error') + '</strong><br>';
        content += message;
        content += '</div>';
        
        if (data) {
            content += '<pre class="small" style="background:#f8f9fa; padding:10px; border-radius:4px; margin-top:10px;">' +
                JSON.stringify(data, null, 2) + '</pre>';
        }
        
        resultDiv.innerHTML = content;
    }

    function getFormData() {
        const selectedId = $('#testerDeviceSelect').val();
        if (!selectedId) {
            return null;
        }
        return {
            ip: document.getElementById('ip').value.trim(),
            port: parseInt(document.getElementById('port').value, 10) || 4370,
            password: document.getElementById('password').value.trim() || '0',
            device_id: selectedId
        };
    }

    function requireSelectedDevice() {
        if (!getFormData()) {
            showResult(false, 'Please select a registered device first.');
            return false;
        }
        return true;
    }

    async function testConnection() {
        showLoading(true);
        document.getElementById('result').style.display = 'none';

        const formData = getFormData();
        if (!formData) {
            showResult(false, 'Please select a registered device first.');
            showLoading(false);
            return;
        }
        if (!formData.ip) {
            showResult(false, 'Please enter device IP address');
            showLoading(false);
            return;
        }

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(function () { controller.abort(); }, 20000);
            
            const response = await fetch('{{ route("test_device_connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData),
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            const result = await response.json();

            if (!response.ok && !result.message) {
                showResult(false, 'Request failed (HTTP ' + response.status + ')');
                return;
            }

            if (result.success) {
                let message = 'Device connection successful!';
                if (result.connection_method) {
                    message += ' (' + result.connection_method + ')';
                }
                if (result.device_info) {
                    const info = result.device_info;
                    if (info.device_name) message += '<br><strong>Device:</strong> ' + info.device_name;
                    if (info.serial_number) message += '<br><strong>Serial:</strong> ' + info.serial_number;
                    if (info.firmware_version) message += '<br><strong>Firmware:</strong> ' + info.firmware_version;
                }
                if (result.server_config && result.server_config.server_url) {
                    message += '<br><br><strong>ShuleXpert server:</strong> ' + result.server_config.server_url;
                }
                showResult(true, message, result);
        } else {
                let errMsg = result.message || 'Failed to connect to device';
                if (result.server_config && result.server_config.server_url && errMsg.indexOf('ShuleXpert server') === -1) {
                    errMsg += '<br><br><strong>ShuleXpert server:</strong> ' + result.server_config.server_url;
                }
                showResult(false, errMsg, result);
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                showResult(false, 'Connection test timed out after 20 seconds. Check device IP, port, Comm Key, and that the device is on the same network as this server.');
            } else {
                showResult(false, 'Error: ' + error.message);
            }
        } finally {
            showLoading(false);
        }
    }

    async function getDeviceInfo() {
        if (!requireSelectedDevice()) return;
        showLoading(true);
        document.getElementById('result').classList.remove('show');

        try {
            const formData = getFormData();
            const response = await fetch('{{ route("zkteco.device_info") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            showResult(result.success, result.success ? 'Device information retrieved successfully!' : result.message, result);
        } catch (error) {
            showResult(false, 'Error: ' + error.message);
        } finally {
            showLoading(false);
        }
    }

    async function getAttendance() {
        if (!requireSelectedDevice()) return;
        showLoading(true);
        document.getElementById('result').classList.remove('show');

        try {
            const formData = getFormData();
            const response = await fetch('{{ route("zkteco.attendance") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            const message = result.success 
                ? `Retrieved ${result.count || 0} attendance record(s)` 
                : result.message;
            showResult(result.success, message, result);
        } catch (error) {
            showResult(false, 'Error: ' + error.message);
        } finally {
            showLoading(false);
        }
    }
</script>
@endpush
