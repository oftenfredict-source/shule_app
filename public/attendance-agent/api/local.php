<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/api_client.php';
require_once dirname(__DIR__) . '/includes/zkteco_device.php';

header('Content-Type: application/json');

if (!sx_is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = (string) ($input['action'] ?? $_GET['action'] ?? '');
$ip = trim((string) ($input['ip'] ?? ''));
$port = (int) ($input['port'] ?? 4370);
$commKey = (string) ($input['comm_key'] ?? $input['password'] ?? '0');

switch ($action) {
    case 'test_connection':
        $result = sx_zkteco_test_connection($ip, $port, $commKey);
        echo json_encode($result);
        break;

    case 'device_info':
        $result = sx_zkteco_device_info($ip, $port, $commKey);
        echo json_encode($result);
        break;

    case 'environment_check':
        echo json_encode([
            'success' => true,
            'message' => 'Environment check complete.',
            'diagnostics' => sx_zkteco_environment_diagnostics(),
        ]);
        break;

    case 'pull_today_attendance':
        $result = sx_zkteco_pull_today_attendance($ip, $port, $commKey);
        echo json_encode($result);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
