<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/api_client.php';

sx_require_login();

$verify = sx_agent_verify_token((string) $_SESSION['agent_token']);
if (!$verify['ok']) {
    sx_flash('error', 'Your session has expired. Please sign in again.');
    header('Location: logout.php');
    exit;
}

$appName = (string) sx_config('app_name', 'ShuleXpert Fingerprint Attendance System');
$poweredBy = (string) sx_config('powered_by', 'EmCa Techonologies');
$schoolName = (string) ($_SESSION['school_name'] ?? 'School');
$userName = (string) ($_SESSION['user_name'] ?? 'Admin');
$serverLabel = (string) ($_SESSION['server_label'] ?? sx_active_server_label());
$activeTab = (string) ($_GET['tab'] ?? 'devices');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= htmlspecialchars($appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@600;700&family=Open+Sans&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-wrap">
    <div class="dashboard-top">
        <div>
            <h1><?= htmlspecialchars($appName) ?></h1>
            <div class="meta"><?= htmlspecialchars($schoolName) ?> — <?= htmlspecialchars($userName) ?> · <?= htmlspecialchars($serverLabel) ?></div>
        </div>
        <a href="logout.php" class="btn-outline-light"><i class="fa fa-sign-out-alt"></i> Sign Out</a>
    </div>

    <div class="dashboard-body">
        <?php if ($msg = sx_flash('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = sx_flash('error')): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="agent-tabs" role="tablist">
            <button type="button" class="agent-tab-btn <?= $activeTab === 'devices' ? 'active' : '' ?>" data-tab="devices">
                <i class="fa fa-fingerprint"></i> Devices
            </button>
            <button type="button" class="agent-tab-btn <?= $activeTab === 'teacher_staff' ? 'active' : '' ?>" data-tab="teacher_staff">
                <i class="fa fa-chalkboard-teacher"></i> Teacher &amp; Staff
            </button>
            <button type="button" class="agent-tab-btn <?= $activeTab === 'students' ? 'active' : '' ?>" data-tab="students">
                <i class="fa fa-user-graduate"></i> Student Attendance
            </button>
        </div>

        <!-- Devices -->
        <div class="agent-tab-panel <?= $activeTab === 'devices' ? 'active' : '' ?>" id="tab-devices">
            <div class="agent-subtabs">
                <button type="button" class="agent-subtab-btn active" data-subtab="register">Register Devices</button>
                <button type="button" class="agent-subtab-btn" data-subtab="tester">Test Connection</button>
            </div>

            <div class="agent-subpanel active" id="subpanel-register">
                <div class="fp-toolbar">
                    <div>
                        <h3 class="fp-section-title">School Fingerprint Devices</h3>
                        <p class="text-muted" style="margin:0;font-size:13px;color:#666;">Register and manage devices for your school.</p>
                    </div>
                    <button type="button" class="btn-brand-sm" id="btnOpenDeviceModal"><i class="fa fa-plus"></i> Register Device</button>
                </div>
                <div class="fp-toolbar">
                    <input type="text" id="deviceSearchInput" class="form-control" style="flex:1;min-width:200px;border:1px solid #d9dee3;border-radius:6px;padding:9px 11px;" placeholder="Search by label, IP, brand...">
                </div>
                <div class="table-responsive">
                    <table class="fp-table">
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
                            <tr><td colspan="5" class="text-center" style="text-align:center;color:#888;">Loading devices...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="devicesEmptyState" class="alert alert-info" style="display:none;margin-top:14px;">No devices registered yet.</div>
            </div>

            <div class="agent-subpanel" id="subpanel-tester">
                <h3 class="fp-section-title">Device Tester</h3>
                <p style="font-size:13px;color:#666;margin-bottom:16px;">
                    Select a registered device and test the connection from this PC on the school LAN.
                </p>
                <div class="sx-form-grid">
                    <div class="sx-form-group full">
                        <label for="testerDeviceSelect">Select Device</label>
                        <select id="testerDeviceSelect">
                            <option value="">-- Select a registered device --</option>
                        </select>
                    </div>
                    <div class="sx-form-group">
                        <label for="testerIp">Device IP</label>
                        <input type="text" id="testerIp" readonly>
                    </div>
                    <div class="sx-form-group">
                        <label for="testerPort">Port</label>
                        <input type="number" id="testerPort" readonly>
                    </div>
                    <div class="sx-form-group full">
                        <label for="testerCommKey">Comm Key</label>
                        <input type="text" id="testerCommKey" readonly>
                    </div>
                </div>
                <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" class="btn-brand-sm" id="btnTestConnection"><i class="fa fa-plug"></i> Test Connection</button>
                    <button type="button" class="btn-outline-brand" id="btnDeviceInfo"><i class="fa fa-info-circle"></i> Get Device Info</button>
                </div>
                <div id="testerLoading" style="display:none;margin-top:14px;color:var(--brand);font-weight:600;">
                    <i class="fa fa-spinner fa-spin"></i> Connecting to device...
                </div>
                <div id="testerResult" class="tester-result"></div>
            </div>
        </div>

        <!-- Teacher & Staff placeholder -->
        <div class="agent-tab-panel <?= $activeTab === 'teacher_staff' ? 'active' : '' ?>" id="tab-teacher_staff">
            <div class="coming-soon-box">
                <div><i class="fa fa-chalkboard-teacher"></i></div>
                <h3 class="fp-section-title">Teacher &amp; Staff Attendance</h3>
                <p>Sync and view teacher/staff attendance via ShuleXpert — coming in the next step.</p>
            </div>
        </div>

        <!-- Students placeholder -->
        <div class="agent-tab-panel <?= $activeTab === 'students' ? 'active' : '' ?>" id="tab-students">
            <div class="coming-soon-box">
                <div><i class="fa fa-user-graduate"></i></div>
                <h3 class="fp-section-title">Student Attendance</h3>
                <p>Sync and view student attendance via ShuleXpert — coming in the next step.</p>
            </div>
        </div>
    </div>
</div>

<!-- Device modal -->
<div class="sx-modal-backdrop" id="deviceModalBackdrop">
    <div class="sx-modal" role="dialog" aria-modal="true">
        <div class="sx-modal-header">
            <h3 id="deviceModalTitle" style="margin:0;color:var(--brand);">Register Device</h3>
        </div>
        <div class="sx-modal-body">
            <form id="deviceForm">
                <input type="hidden" id="deviceFormId" value="">
                <div class="sx-form-grid">
                    <div class="sx-form-group">
                        <label for="deviceLabel">Device Label *</label>
                        <input type="text" id="deviceLabel" maxlength="120" required placeholder="e.g. Main Gate">
                    </div>
                    <div class="sx-form-group">
                        <label for="deviceBrand">Brand *</label>
                        <select id="deviceBrand" required>
                            <option value="ZKTeco" selected>ZKTeco</option>
                            <option value="Anviz">Anviz</option>
                            <option value="Suprema">Suprema</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="sx-form-group">
                        <label for="deviceIp">IP Address *</label>
                        <input type="text" id="deviceIp" required placeholder="192.168.100.109">
                    </div>
                    <div class="sx-form-group">
                        <label for="devicePort">Port *</label>
                        <input type="number" id="devicePort" value="4370" min="1" max="65535" required>
                    </div>
                    <div class="sx-form-group">
                        <label for="deviceCommKey">Comm Key</label>
                        <input type="text" id="deviceCommKey" value="0" maxlength="16">
                    </div>
                    <div class="sx-form-group">
                        <label for="deviceUsageType">Usage</label>
                        <select id="deviceUsageType">
                            <option value="">General</option>
                            <option value="students">Students</option>
                            <option value="teachers">Teachers</option>
                            <option value="staff">Staff</option>
                            <option value="classroom">Classroom</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="sx-form-group">
                        <label for="deviceLocation">Location</label>
                        <input type="text" id="deviceLocation" maxlength="150">
                    </div>
                    <div class="sx-form-group">
                        <label for="deviceSerial">Serial Number</label>
                        <input type="text" id="deviceSerial" maxlength="80">
                    </div>
                    <div class="sx-form-group">
                        <label for="deviceStatus">Status</label>
                        <select id="deviceStatus">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="sx-form-group full">
                        <label for="deviceNotes">Notes</label>
                        <textarea id="deviceNotes" rows="2" maxlength="2000"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="sx-modal-footer">
            <button type="button" class="btn-outline-brand" id="btnCloseDeviceModal">Cancel</button>
            <button type="button" class="btn-brand-sm" id="btnSaveDevice">Save Device</button>
        </div>
    </div>
</div>

<!-- View modal -->
<div class="sx-modal-backdrop" id="deviceViewBackdrop">
    <div class="sx-modal" role="dialog" style="max-width:520px;">
        <div class="sx-modal-header"><h3 style="margin:0;color:var(--brand);">Device Details</h3></div>
        <div class="sx-modal-body" id="deviceViewBody"></div>
        <div class="sx-modal-footer">
            <button type="button" class="btn-outline-brand" id="btnCloseViewModal">Close</button>
        </div>
    </div>
</div>

<script>
window.SX_AGENT = {
    remoteApi: 'api/remote.php',
    localApi: 'api/local.php'
};
</script>
<script src="assets/js/devices.js"></script>
</body>
</html>
