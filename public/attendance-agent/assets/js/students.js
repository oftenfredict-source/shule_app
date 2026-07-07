(function () {
    'use strict';

    var LIVE_POLL_GAP_MS = 400;
    var stDevices = [];
    var livePollTimer = null;
    var seenPunches = {};
    var liveFeedItems = [];
    var pollInFlight = false;
    var liveStopped = false;
    var sendQueue = [];
    var sendInFlight = false;

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
                    throw new Error('Invalid server response.');
                }
            });
        });
    }

    function todayKey() {
        return new Date().toISOString().slice(0, 10);
    }

    function storageKey() {
        return 'sx_st_seen_' + todayKey();
    }

    function loadSeenPunches() {
        try {
            var raw = sessionStorage.getItem(storageKey());
            seenPunches = raw ? JSON.parse(raw) : {};
        } catch (e) {
            seenPunches = {};
        }
    }

    function saveSeenPunches() {
        try {
            sessionStorage.setItem(storageKey(), JSON.stringify(seenPunches));
        } catch (e) { /* ignore */ }
    }

    function punchKey(userId, recordTime) {
        return String(userId) + '|' + String(recordTime);
    }

    function isStLivePanelActive() {
        var panel = $('tab-students');
        return panel && panel.classList.contains('active');
    }

    function isStLiveSubtabActive() {
        var sub = $('subpanel-st-live');
        return sub && sub.classList.contains('active');
    }

    function updateLiveBadge(state, text) {
        var badge = $('stLiveStatusBadge');
        if (!badge) return;

        badge.className = 'live-status-badge ' + state;
        var label = text || 'Select device';
        if (state === 'listening') {
            badge.innerHTML = '<i class="fa fa-circle"></i> ' + escapeHtml(label);
        } else if (state === 'error') {
            badge.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + escapeHtml(label);
        } else {
            badge.innerHTML = '<i class="fa fa-circle"></i> ' + escapeHtml(label);
        }
    }

    function getSelectedDevice() {
        var id = $('stLiveDeviceSelect')?.value;
        if (!id) return null;
        return stDevices.find(function (d) { return String(d.id) === String(id); }) || null;
    }

    function showWaiting(message) {
        var waiting = $('stLiveWaiting');
        var feed = $('stLiveFeed');
        if (waiting) {
            waiting.style.display = 'block';
            waiting.innerHTML = '<i class="fa fa-fingerprint"></i><p>' + escapeHtml(message) + '</p>';
        }
        if (feed) feed.style.display = 'none';
    }

    function showListening() {
        var waiting = $('stLiveWaiting');
        var feed = $('stLiveFeed');
        if (waiting) {
            waiting.style.display = 'block';
            waiting.innerHTML = '<i class="fa fa-fingerprint fa-pulse"></i><p>Waiting for student fingerprint signal...</p>';
        }
        if (feed && liveFeedItems.length) {
            feed.style.display = 'flex';
            waiting.style.display = 'none';
        }
    }

    function renderLiveFeed() {
        var feed = $('stLiveFeed');
        var waiting = $('stLiveWaiting');
        if (!feed) return;

        if (!liveFeedItems.length) {
            feed.style.display = 'none';
            if (waiting && getSelectedDevice() && !liveStopped) {
                waiting.style.display = 'block';
            }
            return;
        }

        feed.style.display = 'flex';
        if (waiting) waiting.style.display = 'none';

        feed.innerHTML = liveFeedItems.map(function (item, index) {
            var cls = 'ts-live-event' + (index === 0 ? ' latest' : '');
            if (item.action === 'Unknown' || item.matched === false) cls += ' unknown';
            if (item.action === 'Ready') cls += ' ready';
            var sentCls = item.sent_ok === false ? ' failed' : '';
            var actionLabel = item.action || 'Sign In';
            var statusMsg = item.message || 'Sent to live system';
            if (actionLabel === 'Sign In') statusMsg = 'Signed in successfully.';
            if (actionLabel === 'Sign Out') statusMsg = 'Signed out successfully.';
            if (actionLabel === 'Ready') statusMsg = 'This student already signed in and signed out.';
            if (actionLabel === 'Unknown' || item.matched === false) {
                statusMsg = item.message || 'Enroll ID not found in school students.';
            }
            return '<div class="' + cls + '">' +
                '<div class="ts-live-event-icon"><i class="fa fa-fingerprint"></i></div>' +
                '<div class="ts-live-event-body">' +
                    '<div class="ts-live-event-name">' + escapeHtml(item.user_name || ('ID ' + item.user_id)) + '</div>' +
                    '<div class="ts-live-event-meta">' +
                        escapeHtml(actionLabel) + ' &bull; ' + escapeHtml(item.record_time || '') +
                        (item.class_name ? ' &bull; ' + escapeHtml(item.class_name) : '') +
                    '</div>' +
                    '<div class="ts-live-event-sent' + sentCls + '">' +
                        '<i class="fa fa-' + (item.sent_ok === false ? 'times' : 'check') + '-circle"></i> ' +
                        escapeHtml(statusMsg) +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function prependFeedItem(item) {
        liveFeedItems.unshift(item);
        if (liveFeedItems.length > 30) {
            liveFeedItems = liveFeedItems.slice(0, 30);
        }
        renderLiveFeed();
    }

    function getSelectedSyncDevice() {
        var id = $('stSyncDeviceSelect') ? $('stSyncDeviceSelect').value : '';
        if (!id) return null;
        return stDevices.find(function (d) { return String(d.id) === String(id); }) || null;
    }

    function renderSyncResult(html) {
        var box = $('stSyncResultContent');
        if (box) box.innerHTML = html;
    }

    function syncAllToLive() {
        var device = getSelectedSyncDevice();
        if (!device) {
            renderSyncResult('<div class="alert-info-box alert-warn"><i class="fa fa-exclamation-circle"></i> Select a fingerprint device first.</div>');
            return;
        }

        var btn = $('stSyncFromDeviceBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Syncing...';
        }

        renderSyncResult(
            '<div class="alert-info-box"><i class="fa fa-spinner fa-spin"></i> ' +
            'Reading all logs from device and sending student attendance to live system...</div>'
        );

        postJson(window.SX_AGENT.attendanceApi, {
            action: 'sync_student_from_device',
            ip: device.ip,
            port: parseInt(device.port, 10) || 4370,
            comm_key: device.comm_key || '0',
            device_label: device.label || device.ip
        }).then(function (data) {
            if (!data.success) {
                renderSyncResult('<div class="alert-info-box alert-warn"><i class="fa fa-times-circle"></i> ' +
                    escapeHtml(data.message || 'Sync failed.') + '</div>');
                return;
            }

            var html = '<div class="alert-info-box alert-success"><i class="fa fa-check-circle"></i> ' +
                escapeHtml(data.message || 'Sync completed.') + '</div>';
            html += '<div class="ts-sync-stats">' +
                '<div><strong>' + (data.synced || 0) + '</strong><span>New saved</span></div>' +
                '<div><strong>' + (data.updated_checkout || 0) + '</strong><span>Check-out updated</span></div>' +
                '<div><strong>' + (data.skipped_existing || 0) + '</strong><span>Already complete</span></div>' +
                '<div><strong>' + (data.device_days_matched || 0) + '</strong><span>Matched days</span></div>' +
                '</div>';
            if (data.records_pulled) {
                html += '<p class="ts-sync-meta">Pulled ' + data.records_pulled + ' punch(es) in ' +
                    (data.batches_sent || 1) + ' batch(es).</p>';
            }
            renderSyncResult(html);
        }).catch(function () {
            renderSyncResult('<div class="alert-info-box alert-warn"><i class="fa fa-times-circle"></i> Connection error during sync.</div>');
        }).finally(function () {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-cloud-upload-alt"></i> Sync All to Live';
            }
        });
    }

    function loadStDevices() {
        return postJson(window.SX_AGENT.remoteApi, { action: 'list_devices' })
            .then(function (data) {
                var select = $('stLiveDeviceSelect');
                var syncSelect = $('stSyncDeviceSelect');
                if (!select && !syncSelect) return;

                stDevices = (data.devices || []).filter(function (d) { return d.status === 'active'; });
                var current = select ? select.value : '';
                var syncCurrent = syncSelect ? syncSelect.value : '';
                var options = '<option value="">-- Select device --</option>';

                if (!stDevices.length) {
                    options = '<option value="">No active devices — register one first</option>';
                    if (select) select.innerHTML = options;
                    if (syncSelect) syncSelect.innerHTML = options;
                    showWaiting('Register a device under the Devices tab first.');
                    return;
                }

                stDevices.forEach(function (d) {
                    options += '<option value="' + d.id + '">' + escapeHtml(d.label) + ' (' + escapeHtml(d.ip) + ')</option>';
                });

                if (select) {
                    select.innerHTML = options;
                    if (current && select.querySelector('option[value="' + current + '"]')) {
                        select.value = current;
                    }
                }

                if (syncSelect) {
                    syncSelect.innerHTML = options;
                    if (syncCurrent && syncSelect.querySelector('option[value="' + syncCurrent + '"]')) {
                        syncSelect.value = syncCurrent;
                    } else if (stDevices.length === 1) {
                        syncSelect.value = String(stDevices[0].id);
                    }
                }
            })
            .catch(function () {
                var fail = '<option value="">Failed to load devices</option>';
                var select = $('stLiveDeviceSelect');
                var syncSelect = $('stSyncDeviceSelect');
                if (select) select.innerHTML = fail;
                if (syncSelect) syncSelect.innerHTML = fail;
            });
    }

    function queuePunchesForLive(device, punches) {
        punches.forEach(function (p) {
            prependFeedItem({
                user_id: p.user_id,
                user_name: 'Enroll ID ' + p.user_id,
                record_time: p.record_time,
                action: 'Detected',
                sent_ok: true,
                message: 'Sending to live system...'
            });
            sendQueue.push({ device: device, punch: p });
        });
        drainSendQueue();
    }

    function drainSendQueue() {
        if (sendInFlight || !sendQueue.length) {
            return;
        }

        sendInFlight = true;
        var item = sendQueue.shift();

        sendPunchesToLive(item.device, [item.punch])
            .catch(function () { /* feed already shows attempt */ })
            .finally(function () {
                sendInFlight = false;
                drainSendQueue();
            });
    }

    function sendPunchesToLive(device, punches) {
        return postJson(window.SX_AGENT.attendanceApi, {
            action: 'ingest_student_live',
            device_ip: device.ip,
            punches: punches
        }).then(function (data) {
            if (!data.success) {
                punches.forEach(function (p) {
                    prependFeedItem({
                        user_id: p.user_id,
                        user_name: 'Enroll ID ' + p.user_id,
                        record_time: p.record_time,
                        action: 'Sign In',
                        matched: false,
                        sent_ok: false,
                        message: data.message || 'Failed to send to live system'
                    });
                });
                return;
            }

            var events = data.events || [];
            if (events.length) {
                events.forEach(function (ev) {
                    if (ev.action === 'Ignored') {
                        return;
                    }
                    prependFeedItem({
                        user_id: ev.user_id,
                        user_name: ev.user_name || ('ID ' + ev.user_id),
                        record_time: ev.record_time,
                        action: ev.action || 'Sign In',
                        class_name: ev.class_name,
                        matched: ev.matched !== false,
                        sent_ok: ev.matched !== false,
                        message: ev.message || 'Sent to live system'
                    });
                });
            }
        });
    }

    function pollDeviceOnce() {
        if (pollInFlight || liveStopped || !isStLivePanelActive() || !isStLiveSubtabActive()) {
            return;
        }

        var device = getSelectedDevice();
        if (!device) return;

        pollInFlight = true;
        updateLiveBadge('listening', 'Listening...');

        postJson(window.SX_AGENT.localApi, {
            action: 'pull_today_attendance',
            ip: device.ip,
            port: parseInt(device.port, 10) || 4370,
            comm_key: device.comm_key || '0'
        })
            .then(function (data) {
                if (!data.success) {
                    updateLiveBadge('error', 'Device error');
                    showListening();
                    return;
                }

                updateLiveBadge('listening', 'Waiting for signal');
                showListening();

                var records = data.records || [];
                var newPunches = [];

                records.forEach(function (rec) {
                    var key = punchKey(rec.user_id, rec.record_time);
                    if (seenPunches[key]) return;

                    seenPunches[key] = true;
                    newPunches.push({
                        user_id: rec.user_id,
                        record_time: rec.record_time,
                        type: rec.type || 'Fingerprint',
                        device_ip: rec.device_ip || device.ip
                    });
                });

                saveSeenPunches();

                if (newPunches.length) {
                    queuePunchesForLive(device, newPunches);
                }
            })
            .catch(function () {
                updateLiveBadge('error', 'Connection error');
            })
            .finally(function () {
                pollInFlight = false;
                scheduleNextPoll();
            });
    }

    function seedSeenFromDevice() {
        var device = getSelectedDevice();
        if (!device) return Promise.resolve();

        return postJson(window.SX_AGENT.localApi, {
            action: 'pull_today_attendance',
            ip: device.ip,
            port: parseInt(device.port, 10) || 4370,
            comm_key: device.comm_key || '0'
        }).then(function (data) {
            if (!data.success) return;
            (data.records || []).forEach(function (rec) {
                seenPunches[punchKey(rec.user_id, rec.record_time)] = true;
            });
            saveSeenPunches();
        });
    }

    function scheduleNextPoll() {
        if (livePollTimer) {
            clearTimeout(livePollTimer);
            livePollTimer = null;
        }

        if (liveStopped || !isStLivePanelActive() || !isStLiveSubtabActive()) {
            return;
        }

        livePollTimer = setTimeout(function () {
            pollDeviceOnce();
        }, LIVE_POLL_GAP_MS);
    }

    function stopLiveListening() {
        liveStopped = true;
        if (livePollTimer) {
            clearTimeout(livePollTimer);
            livePollTimer = null;
        }
        sendQueue = [];
        sendInFlight = false;
        updateLiveBadge('waiting', 'Stopped');
        var stopBtn = $('stStopLiveBtn');
        if (stopBtn) stopBtn.style.display = 'none';
    }

    function startLiveListening() {
        var device = getSelectedDevice();
        if (!device) {
            showWaiting('Select a fingerprint device to start live student attendance.');
            updateLiveBadge('waiting', 'Select device');
            return;
        }

        liveStopped = false;
        liveFeedItems = [];
        renderLiveFeed();

        var stopBtn = $('stStopLiveBtn');
        if (stopBtn) stopBtn.style.display = 'inline-flex';

        updateLiveBadge('listening', 'Starting...');
        showListening();

        seedSeenFromDevice().then(function () {
            pollDeviceOnce();
        });
    }

    function onDeviceChange() {
        stopLiveListening();
        loadSeenPunches();
        if (getSelectedDevice()) {
            startLiveListening();
        } else {
            showWaiting('Select a fingerprint device to start live student attendance.');
            updateLiveBadge('waiting', 'Select device');
        }
    }

    function init() {
        if (!$('tab-students')) return;

        loadSeenPunches();
        loadStDevices();

        if ($('stLiveDeviceSelect')) {
            $('stLiveDeviceSelect').addEventListener('change', onDeviceChange);
        }

        if ($('stStopLiveBtn')) {
            $('stStopLiveBtn').addEventListener('click', function () {
                stopLiveListening();
                showWaiting('Live listening stopped. Select device to start again.');
            });
        }

        if ($('stSyncFromDeviceBtn')) {
            $('stSyncFromDeviceBtn').addEventListener('click', syncAllToLive);
        }

        document.querySelectorAll('.agent-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.getAttribute('data-tab') !== 'students') {
                    stopLiveListening();
                }
            });
        });

        document.querySelectorAll('#tab-students .agent-subtab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.getAttribute('data-subtab') !== 'st-live') {
                    stopLiveListening();
                } else if (getSelectedDevice() && !liveStopped) {
                    startLiveListening();
                }
            });
        });

        if (isStLivePanelActive() && isStLiveSubtabActive() && getSelectedDevice()) {
            startLiveListening();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
