(function () {
    'use strict';

    var schoolDevices = [];
    var deviceModalMode = 'create';

    function $(id) { return document.getElementById(id); }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function postJson(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        }).then(function (r) {
            return r.text().then(function (text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Server returned an invalid response. Please sign in again or contact support.');
                }
            });
        });
    }

    function showAlert(message, type) {
        var box = document.createElement('div');
        box.className = 'alert alert-' + (type === 'error' ? 'danger' : 'success');
        box.textContent = message;
        var body = document.querySelector('.dashboard-body');
        if (body) {
            body.insertBefore(box, body.firstChild);
            setTimeout(function () { box.remove(); }, 5000);
        }
    }

    function showGlobalLoading(text) {
        var el = $('globalLoading');
        var txt = $('globalLoadingText');
        if (txt) txt.textContent = text || 'Please wait...';
        if (el) {
            el.classList.add('show');
            el.setAttribute('aria-hidden', 'false');
        }
    }

    function hideGlobalLoading() {
        var el = $('globalLoading');
        if (el) {
            el.classList.remove('show');
            el.setAttribute('aria-hidden', 'true');
        }
    }

    function showModalLoading(show, text) {
        var el = $('deviceModalLoading');
        if (!el) return;
        var inner = el.querySelector('div');
        if (inner && text) {
            inner.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + escapeHtml(text);
        }
        el.style.display = show ? 'flex' : 'none';
    }

    function setButtonsDisabled(ids, disabled) {
        ids.forEach(function (id) {
            var btn = $(id);
            if (btn) btn.disabled = disabled;
        });
    }

    function initTabs() {
        document.querySelectorAll('.agent-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = btn.getAttribute('data-tab');
                document.querySelectorAll('.agent-tab-btn').forEach(function (b) { b.classList.remove('active'); });
                document.querySelectorAll('.agent-tab-panel').forEach(function (p) { p.classList.remove('active'); });
                btn.classList.add('active');
                var panel = $('tab-' + tab);
                if (panel) panel.classList.add('active');
            });
        });

        document.querySelectorAll('.agent-subtab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var sub = btn.getAttribute('data-subtab');
                var panelRoot = btn.closest('.agent-tab-panel');
                if (!panelRoot) return;

                panelRoot.querySelectorAll('.agent-subtab-btn').forEach(function (b) { b.classList.remove('active'); });
                panelRoot.querySelectorAll('.agent-subpanel').forEach(function (p) { p.classList.remove('active'); });
                btn.classList.add('active');
                var panel = $('subpanel-' + sub);
                if (panel) panel.classList.add('active');
            });
        });
    }

    function getFilteredDevices() {
        var q = ($('deviceSearchInput')?.value || '').trim().toLowerCase();
        if (!q) return schoolDevices;
        return schoolDevices.filter(function (d) {
            return [d.label, d.brand, d.ip, d.usage_type, d.location, d.status]
                .join(' ').toLowerCase().includes(q);
        });
    }

    function closeDeviceMenus() {
        document.querySelectorAll('.device-menu.show').forEach(function (m) {
            m.classList.remove('show');
        });
    }

    document.addEventListener('click', closeDeviceMenus);

    function deviceActionsMenu(id) {
        return '<div class="device-actions">' +
            '<button type="button" class="device-dots-btn" title="Actions" data-menu-btn="' + id + '"><i class="fa fa-ellipsis-v"></i></button>' +
            '<div class="device-menu" id="device-menu-' + id + '">' +
                '<button type="button" data-view="' + id + '"><i class="fa fa-eye"></i> View</button>' +
                '<button type="button" data-edit="' + id + '"><i class="fa fa-pencil"></i> Edit</button>' +
                '<button type="button" class="danger" data-delete="' + id + '"><i class="fa fa-trash"></i> Delete</button>' +
            '</div></div>';
    }

    function renderDevices() {
        var devices = getFilteredDevices();
        var tbody = $('devicesTableBody');
        var empty = $('devicesEmptyState');
        if (!tbody) return;

        if (!devices.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888;">No devices found.</td></tr>';
            if (empty) empty.style.display = schoolDevices.length ? 'none' : 'block';
            return;
        }

        if (empty) empty.style.display = 'none';

        tbody.innerHTML = devices.map(function (d) {
            var statusClass = d.status === 'active' ? 'active' : 'inactive';
            return '<tr>' +
                '<td>' + escapeHtml(d.label) + '</td>' +
                '<td>' + escapeHtml(d.brand) + '</td>' +
                '<td>' + escapeHtml(d.ip) + '</td>' +
                '<td><span class="status-pill ' + statusClass + '">' + escapeHtml(d.status) + '</span></td>' +
                '<td style="text-align:center;">' + deviceActionsMenu(d.id) + '</td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('[data-menu-btn]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var id = btn.getAttribute('data-menu-btn');
                var menu = $('device-menu-' + id);
                var open = menu && menu.classList.contains('show');
                closeDeviceMenus();
                if (menu && !open) menu.classList.add('show');
            });
        });

        tbody.querySelectorAll('[data-view]').forEach(function (btn) {
            btn.addEventListener('click', function () { viewDevice(parseInt(btn.getAttribute('data-view'), 10)); });
        });
        tbody.querySelectorAll('[data-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () { openDeviceModal(parseInt(btn.getAttribute('data-edit'), 10)); });
        });
        tbody.querySelectorAll('[data-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () { deleteDevice(parseInt(btn.getAttribute('data-delete'), 10)); });
        });
    }

    function populateTesterSelect() {
        var select = $('testerDeviceSelect');
        if (!select) return;
        var current = select.value;
        select.innerHTML = '<option value="">-- Select a registered device --</option>';
        schoolDevices.filter(function (d) { return d.status === 'active'; }).forEach(function (d) {
            var opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.label + ' (' + d.ip + ')';
            select.appendChild(opt);
        });
        if (current) select.value = current;
        applyTesterDevice();
    }

    function applyTesterDevice() {
        var id = $('testerDeviceSelect')?.value;
        var device = schoolDevices.find(function (d) { return String(d.id) === String(id); });
        if ($('testerIp')) $('testerIp').value = device ? device.ip : '';
        if ($('testerPort')) $('testerPort').value = device ? device.port : '';
        if ($('testerCommKey')) $('testerCommKey').value = device ? (device.comm_key || '0') : '';
    }

    function loadDevices() {
        showGlobalLoading('Loading devices...');
        return postJson(window.SX_AGENT.remoteApi, { action: 'list_devices' })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Failed to load devices');
                schoolDevices = data.devices || [];
                renderDevices();
                populateTesterSelect();
            })
            .catch(function (err) {
                var tbody = $('devicesTableBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#a12626;">' + escapeHtml(err.message) + '</td></tr>';
                }
            })
            .finally(function () {
                hideGlobalLoading();
            });
    }

    function resetDeviceForm() {
        $('deviceFormId').value = '';
        $('deviceLabel').value = '';
        $('deviceBrand').value = 'ZKTeco';
        $('deviceIp').value = '';
        $('devicePort').value = '4370';
        $('deviceCommKey').value = '0';
        $('deviceUsageType').value = '';
        $('deviceLocation').value = '';
        $('deviceSerial').value = '';
        $('deviceStatus').value = 'active';
        $('deviceNotes').value = '';
    }

    function fillDeviceForm(device) {
        $('deviceFormId').value = device.id;
        $('deviceLabel').value = device.label || '';
        $('deviceBrand').value = device.brand || 'ZKTeco';
        $('deviceIp').value = device.ip || '';
        $('devicePort').value = device.port || 4370;
        $('deviceCommKey').value = device.comm_key || '0';
        $('deviceUsageType').value = device.usage_type || '';
        $('deviceLocation').value = device.location || '';
        $('deviceSerial').value = device.serial_number || '';
        $('deviceStatus').value = device.status || 'active';
        $('deviceNotes').value = device.notes || '';
    }

    function openDeviceModal(id) {
        deviceModalMode = id ? 'edit' : 'create';
        $('deviceModalTitle').textContent = id ? 'Edit Device' : 'Register Device';
        resetDeviceForm();
        showModalLoading(false);
        if (id) {
            var device = schoolDevices.find(function (d) { return d.id === id; });
            if (device) {
                fillDeviceForm(device);
            } else {
                showModalLoading(true, 'Loading device...');
                postJson(window.SX_AGENT.remoteApi, { action: 'get_device', id: id })
                    .then(function (data) {
                        if (data.success && data.device) fillDeviceForm(data.device);
                    })
                    .catch(function (err) {
                        showAlert(err.message, 'error');
                    })
                    .finally(function () {
                        showModalLoading(false);
                    });
            }
        }
        $('deviceModalBackdrop').classList.add('show');
    }

    function closeDeviceModal() {
        $('deviceModalBackdrop').classList.remove('show');
    }

    function saveDevice() {
        var id = $('deviceFormId').value ? parseInt($('deviceFormId').value, 10) : null;
        var payload = {
            action: 'save_device',
            id: id,
            label: $('deviceLabel').value.trim(),
            brand: $('deviceBrand').value,
            ip: $('deviceIp').value.trim(),
            port: parseInt($('devicePort').value, 10) || 4370,
            comm_key: $('deviceCommKey').value || '0',
            usage_type: $('deviceUsageType').value,
            location: $('deviceLocation').value,
            serial_number: $('deviceSerial').value,
            status: $('deviceStatus').value,
            notes: $('deviceNotes').value
        };

        $('btnSaveDevice').disabled = true;
        showModalLoading(true, 'Saving device...');
        postJson(window.SX_AGENT.remoteApi, payload)
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Save failed');
                showAlert(data.message || 'Device saved.', 'success');
                closeDeviceModal();
                return loadDevices();
            })
            .catch(function (err) {
                showAlert(err.message, 'error');
            })
            .finally(function () {
                $('btnSaveDevice').disabled = false;
                showModalLoading(false);
            });
    }

    function deleteDevice(id) {
        if (!confirm('Delete this device?')) return;
        showGlobalLoading('Deleting device...');
        postJson(window.SX_AGENT.remoteApi, { action: 'delete_device', id: id })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Delete failed');
                showAlert(data.message || 'Device deleted.', 'success');
                return loadDevices();
            })
            .catch(function (err) { showAlert(err.message, 'error'); })
            .finally(function () { hideGlobalLoading(); });
    }

    function viewDevice(id) {
        closeDeviceMenus();
        var device = schoolDevices.find(function (d) { return d.id === id; });
        if (!device) return;
        $('deviceViewBody').innerHTML =
            '<p><strong>Label:</strong> ' + escapeHtml(device.label) + '</p>' +
            '<p><strong>Brand:</strong> ' + escapeHtml(device.brand) + '</p>' +
            '<p><strong>IP:</strong> ' + escapeHtml(device.ip) + ':' + escapeHtml(device.port) + '</p>' +
            '<p><strong>Comm Key:</strong> ' + escapeHtml(device.comm_key) + '</p>' +
            '<p><strong>Usage:</strong> ' + escapeHtml(device.usage_type || 'General') + '</p>' +
            '<p><strong>Location:</strong> ' + escapeHtml(device.location || '—') + '</p>' +
            '<p><strong>Serial:</strong> ' + escapeHtml(device.serial_number || '—') + '</p>' +
            '<p><strong>Status:</strong> ' + escapeHtml(device.status) + '</p>' +
            '<p><strong>Notes:</strong> ' + escapeHtml(device.notes || '—') + '</p>';
        $('deviceViewBackdrop').classList.add('show');
    }

    function showTesterResult(html, ok) {
        var box = $('testerResult');
        if (!box) return;
        box.className = 'tester-result show ' + (ok ? 'success' : 'error');
        box.innerHTML = html;
    }

    function localDeviceRequest(action) {
        var ip = $('testerIp')?.value || '';
        var port = parseInt($('testerPort')?.value || '4370', 10);
        var commKey = $('testerCommKey')?.value || '0';
        if (!ip) {
            showAlert('Please select a device first.', 'error');
            return Promise.resolve();
        }
        $('testerLoading').style.display = 'block';
        $('testerResult').className = 'tester-result';
        setButtonsDisabled(['btnTestConnection', 'btnDeviceInfo'], true);
        return postJson(window.SX_AGENT.localApi, {
            action: action,
            ip: ip,
            port: port,
            comm_key: commKey
        })
            .then(function (data) {
                if (!data.success) {
                    var html = '<strong>Failed</strong><br>' + escapeHtml(data.message);
                    if (data.hints && data.hints.length) {
                        html += '<ul style="margin:10px 0 0 18px;padding:0;font-size:13px;">';
                        data.hints.forEach(function (h) {
                            html += '<li>' + escapeHtml(h) + '</li>';
                        });
                        html += '</ul>';
                    }
                    if (data.diagnostics) {
                        html += '<pre style="margin-top:10px;white-space:pre-wrap;font-size:12px;background:#f8f8f8;padding:10px;border-radius:6px;">' +
                            escapeHtml(JSON.stringify(data.diagnostics, null, 2)) + '</pre>';
                    }
                    showTesterResult(html, false);
                    return;
                }
                var html = '<strong>' + escapeHtml(data.message) + '</strong>';
                if (data.device_info) {
                    html += '<pre style="margin-top:10px;white-space:pre-wrap;font-size:12px;">' +
                        escapeHtml(JSON.stringify(data.device_info, null, 2)) + '</pre>';
                }
                if (data.device_time) {
                    html += '<p style="margin-top:8px;"><strong>Device time:</strong> ' + escapeHtml(data.device_time) + '</p>';
                }
                showTesterResult(html, true);
            })
            .catch(function (err) {
                showTesterResult('<strong>Error</strong><br>' + escapeHtml(err.message), false);
            })
            .finally(function () {
                $('testerLoading').style.display = 'none';
                setButtonsDisabled(['btnTestConnection', 'btnDeviceInfo'], false);
            });
    }

    function loadSchoolSettings() {
        if (!window.SX_AGENT.settingsApi) return Promise.resolve();
        return postJson(window.SX_AGENT.settingsApi, { action: 'get' })
            .then(function (data) {
                if (!data.success || !data.settings) return;
                if ($('settingLanIp')) $('settingLanIp').value = data.settings.lan_server_ip || '';
                if ($('settingApiPort')) $('settingApiPort').value = data.settings.api_port || '';
                if ($('settingApiPath')) $('settingApiPath').value = data.settings.api_path || 'api';
                if ($('settingSchoolNote')) $('settingSchoolNote').value = data.settings.school_note || '';
                var resolved = $('settingResolvedUrl');
                if (resolved) {
                    var base = data.settings.local_api_base || '';
                    if (base) {
                        resolved.style.display = 'block';
                        resolved.textContent = 'Local API: ' + base;
                    } else {
                        resolved.style.display = 'none';
                    }
                }
            })
            .catch(function () { /* optional on first load */ });
    }

    function openSettingsModal() {
        loadSchoolSettings().then(function () {
            $('settingsModalBackdrop')?.classList.add('show');
        });
    }

    function closeSettingsModal() {
        $('settingsModalBackdrop')?.classList.remove('show');
    }

    function saveSchoolSettings() {
        var ip = ($('settingLanIp')?.value || '').trim();
        if (!ip) {
            showAlert('Enter the LAN server IP for this school PC.', 'error');
            return;
        }
        setButtonsDisabled(['btnSaveSettings'], true);
        showGlobalLoading('Saving LAN settings...');
        postJson(window.SX_AGENT.settingsApi, {
            action: 'save',
            lan_server_ip: ip,
            api_port: ($('settingApiPort')?.value || '').trim(),
            api_path: ($('settingApiPath')?.value || 'api').trim(),
            school_note: ($('settingSchoolNote')?.value || '').trim()
        })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Save failed');
                showAlert(data.message || 'Settings saved. Use Local environment on next login if needed.', 'success');
                closeSettingsModal();
            })
            .catch(function (err) {
                showAlert(err.message, 'error');
            })
            .finally(function () {
                setButtonsDisabled(['btnSaveSettings'], false);
                hideGlobalLoading();
            });
    }

    function init() {
        initTabs();
        loadSchoolSettings();
        loadDevices();

        if ($('deviceSearchInput')) {
            $('deviceSearchInput').addEventListener('input', renderDevices);
        }
        if ($('btnOpenDeviceModal')) {
            $('btnOpenDeviceModal').addEventListener('click', function () { openDeviceModal(null); });
        }
        if ($('btnCloseDeviceModal')) {
            $('btnCloseDeviceModal').addEventListener('click', closeDeviceModal);
        }
        if ($('btnSaveDevice')) {
            $('btnSaveDevice').addEventListener('click', saveDevice);
        }
        if ($('btnCloseViewModal')) {
            $('btnCloseViewModal').addEventListener('click', function () {
                $('deviceViewBackdrop').classList.remove('show');
            });
        }
        if ($('btnOpenSettings')) {
            $('btnOpenSettings').addEventListener('click', openSettingsModal);
        }
        if ($('btnCloseSettings')) {
            $('btnCloseSettings').addEventListener('click', closeSettingsModal);
        }
        if ($('btnSaveSettings')) {
            $('btnSaveSettings').addEventListener('click', saveSchoolSettings);
        }
        if ($('testerDeviceSelect')) {
            $('testerDeviceSelect').addEventListener('change', applyTesterDevice);
        }
        if ($('btnTestConnection')) {
            $('btnTestConnection').addEventListener('click', function () {
                localDeviceRequest('test_connection');
            });
        }
        if ($('btnDeviceInfo')) {
            $('btnDeviceInfo').addEventListener('click', function () {
                localDeviceRequest('device_info');
            });
        }

        $('deviceModalBackdrop')?.addEventListener('click', function (e) {
            if (e.target === $('deviceModalBackdrop')) closeDeviceModal();
        });
        $('deviceViewBackdrop')?.addEventListener('click', function (e) {
            if (e.target === $('deviceViewBackdrop')) $('deviceViewBackdrop').classList.remove('show');
        });
        $('settingsModalBackdrop')?.addEventListener('click', function (e) {
            if (e.target === $('settingsModalBackdrop')) closeSettingsModal();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
