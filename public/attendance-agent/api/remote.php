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

$action = (string) ($input['action'] ?? $_GET['action'] ?? '');

switch ($action) {
    case 'list_devices':
        $result = sx_agent_list_devices();
        echo json_encode(['success' => $result['ok'], 'message' => $result['message'], 'devices' => $result['devices'] ?? []]);
        break;

    case 'get_device':
        $id = (int) ($input['id'] ?? 0);
        $result = sx_agent_get_device($id);
        echo json_encode(['success' => $result['ok'], 'message' => $result['message'], 'device' => $result['device'] ?? null]);
        break;

    case 'save_device':
        $id = isset($input['id']) && $input['id'] !== '' ? (int) $input['id'] : null;
        $fields = [
            'label' => trim((string) ($input['label'] ?? '')),
            'brand' => trim((string) ($input['brand'] ?? 'ZKTeco')),
            'ip' => trim((string) ($input['ip'] ?? '')),
            'port' => (int) ($input['port'] ?? 4370),
            'comm_key' => (string) ($input['comm_key'] ?? '0'),
            'usage_type' => (string) ($input['usage_type'] ?? ''),
            'location' => (string) ($input['location'] ?? ''),
            'serial_number' => (string) ($input['serial_number'] ?? ''),
            'status' => (string) ($input['status'] ?? 'active'),
            'notes' => (string) ($input['notes'] ?? ''),
        ];
        $result = sx_agent_save_device($fields, $id);
        echo json_encode(['success' => $result['ok'], 'message' => $result['message'], 'device' => $result['device'] ?? null]);
        break;

    case 'delete_device':
        $id = (int) ($input['id'] ?? 0);
        $result = sx_agent_delete_device($id);
        echo json_encode(['success' => $result['ok'], 'message' => $result['message']]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
