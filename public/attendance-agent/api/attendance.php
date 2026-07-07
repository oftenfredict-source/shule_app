<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/api_client.php';

header('Content-Type: application/json');

if (!sx_is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$verify = sx_agent_verify_token((string) $_SESSION['agent_token']);
if (!$verify['ok']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = (string) ($input['action'] ?? '');

switch ($action) {
    case 'ingest_teacher_staff_live':
        $deviceIp = trim((string) ($input['device_ip'] ?? ''));
        $punches = $input['punches'] ?? [];
        if (!is_array($punches) || count($punches) < 1) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'No punches to send.']);
            exit;
        }

        $result = sx_agent_ingest_teacher_staff_live($deviceIp, $punches);
        echo json_encode([
            'success' => $result['ok'],
            'message' => $result['message'],
            'events' => is_array($result['data']['events'] ?? null) ? $result['data']['events'] : [],
            'data' => $result['data'] ?? null,
        ]);
        break;

    case 'sync_teacher_staff_from_device':
        set_time_limit(600);
        $deviceIp = trim((string) ($input['ip'] ?? $input['device_ip'] ?? ''));
        $deviceLabel = trim((string) ($input['device_label'] ?? ''));
        $port = (int) ($input['port'] ?? 4370);
        $commKey = (string) ($input['comm_key'] ?? '0');

        if ($deviceIp === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Device IP is required.']);
            exit;
        }

        require_once dirname(__DIR__) . '/includes/zkteco_device.php';

        $pull = sx_zkteco_pull_all_attendance($deviceIp, $port, $commKey);
        if (empty($pull['success'])) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $pull['message'] ?? 'Failed to read device logs.',
            ]);
            exit;
        }

        $records = $pull['records'] ?? [];
        if (!is_array($records) || count($records) < 1) {
            echo json_encode([
                'success' => true,
                'message' => 'No attendance logs found on device.',
                'synced' => 0,
                'skipped_existing' => 0,
                'updated_checkout' => 0,
                'device_days_matched' => 0,
                'records_pulled' => 0,
            ]);
            exit;
        }

        $batchSize = 1200;
        $totals = [
            'synced' => 0,
            'skipped_existing' => 0,
            'updated_checkout' => 0,
            'device_days_matched' => 0,
        ];
        $batches = array_chunk($records, $batchSize);
        $batchCount = count($batches);

        foreach ($batches as $index => $batch) {
            $result = sx_agent_sync_teacher_staff($deviceIp, $deviceLabel, $batch);
            if (!$result['ok']) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $result['message'] . ' (batch ' . ($index + 1) . '/' . $batchCount . ')',
                    'records_pulled' => count($records),
                    'batches_sent' => $index,
                ]);
                exit;
            }

            $data = $result['data'] ?? [];
            $totals['synced'] += (int) ($data['synced'] ?? 0);
            $totals['skipped_existing'] += (int) ($data['skipped_existing'] ?? 0);
            $totals['updated_checkout'] += (int) ($data['updated_checkout'] ?? 0);
            $totals['device_days_matched'] += (int) ($data['device_days_matched'] ?? 0);
        }

        $message = 'Synced to live system. ' . $totals['synced'] . ' new day(s) saved.';
        if ($totals['updated_checkout'] > 0) {
            $message .= ' ' . $totals['updated_checkout'] . ' check-out time(s) updated.';
        }
        if ($totals['skipped_existing'] > 0) {
            $message .= ' ' . $totals['skipped_existing'] . ' already complete (skipped).';
        }

        echo json_encode([
            'success' => true,
            'message' => $message,
            'synced' => $totals['synced'],
            'skipped_existing' => $totals['skipped_existing'],
            'updated_checkout' => $totals['updated_checkout'],
            'device_days_matched' => $totals['device_days_matched'],
            'records_pulled' => count($records),
            'batches_sent' => $batchCount,
        ]);
        break;

    case 'ingest_student_live':
        $deviceIp = trim((string) ($input['device_ip'] ?? ''));
        $punches = $input['punches'] ?? [];
        if (!is_array($punches) || count($punches) < 1) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'No punches to send.']);
            exit;
        }

        $result = sx_agent_ingest_student_live($deviceIp, $punches);
        echo json_encode([
            'success' => $result['ok'],
            'message' => $result['message'],
            'events' => is_array($result['data']['events'] ?? null) ? $result['data']['events'] : [],
            'data' => $result['data'] ?? null,
        ]);
        break;

    case 'sync_student_from_device':
        set_time_limit(600);
        $deviceIp = trim((string) ($input['ip'] ?? $input['device_ip'] ?? ''));
        $deviceLabel = trim((string) ($input['device_label'] ?? ''));
        $port = (int) ($input['port'] ?? 4370);
        $commKey = (string) ($input['comm_key'] ?? '0');

        if ($deviceIp === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Device IP is required.']);
            exit;
        }

        require_once dirname(__DIR__) . '/includes/zkteco_device.php';

        $pull = sx_zkteco_pull_all_attendance($deviceIp, $port, $commKey);
        if (empty($pull['success'])) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $pull['message'] ?? 'Failed to read device logs.',
            ]);
            exit;
        }

        $records = $pull['records'] ?? [];
        if (!is_array($records) || count($records) < 1) {
            echo json_encode([
                'success' => true,
                'message' => 'No attendance logs found on device.',
                'synced' => 0,
                'skipped_existing' => 0,
                'updated_checkout' => 0,
                'device_days_matched' => 0,
                'records_pulled' => 0,
            ]);
            exit;
        }

        $batchSize = 1200;
        $totals = [
            'synced' => 0,
            'skipped_existing' => 0,
            'updated_checkout' => 0,
            'device_days_matched' => 0,
        ];
        $batches = array_chunk($records, $batchSize);
        $batchCount = count($batches);

        foreach ($batches as $index => $batch) {
            $result = sx_agent_sync_student($deviceIp, $deviceLabel, $batch);
            if (!$result['ok']) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $result['message'] . ' (batch ' . ($index + 1) . '/' . $batchCount . ')',
                    'records_pulled' => count($records),
                    'batches_sent' => $index,
                ]);
                exit;
            }

            $data = $result['data'] ?? [];
            $totals['synced'] += (int) ($data['synced'] ?? 0);
            $totals['skipped_existing'] += (int) ($data['skipped_existing'] ?? 0);
            $totals['updated_checkout'] += (int) ($data['updated_checkout'] ?? 0);
            $totals['device_days_matched'] += (int) ($data['device_days_matched'] ?? 0);
        }

        $message = 'Synced to live system. ' . $totals['synced'] . ' new day(s) saved.';
        if ($totals['updated_checkout'] > 0) {
            $message .= ' ' . $totals['updated_checkout'] . ' check-out time(s) updated.';
        }
        if ($totals['skipped_existing'] > 0) {
            $message .= ' ' . $totals['skipped_existing'] . ' already complete (skipped).';
        }

        echo json_encode([
            'success' => true,
            'message' => $message,
            'synced' => $totals['synced'],
            'skipped_existing' => $totals['skipped_existing'],
            'updated_checkout' => $totals['updated_checkout'],
            'device_days_matched' => $totals['device_days_matched'],
            'records_pulled' => count($records),
            'batches_sent' => $batchCount,
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
