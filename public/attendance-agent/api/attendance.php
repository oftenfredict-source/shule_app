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

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
