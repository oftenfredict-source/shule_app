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

$schoolId = (int) ($_SESSION['schoolID'] ?? 0);
$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_GET;
}

$action = (string) ($input['action'] ?? 'get');

if ($action === 'get') {
    $settings = sx_school_settings($schoolId);
    echo json_encode([
        'success' => true,
        'settings' => [
            'lan_server_ip' => (string) ($settings['lan_server_ip'] ?? ''),
            'api_port' => (string) ($settings['api_port'] ?? ''),
            'api_path' => (string) ($settings['api_path'] ?? 'api'),
            'school_note' => (string) ($settings['school_note'] ?? ''),
            'local_api_base' => sx_resolved_local_api_base() ?? '',
        ],
    ]);
    exit;
}

if ($action === 'save') {
    $ip = trim((string) ($input['lan_server_ip'] ?? ''));
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Enter a valid LAN server IP for this school PC.']);
        exit;
    }

    $port = trim((string) ($input['api_port'] ?? ''));
    if ($port !== '' && (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'API port must be between 1 and 65535.']);
        exit;
    }

    $saved = sx_save_school_settings([
        'lan_server_ip' => $ip,
        'api_port' => $port,
        'api_path' => trim((string) ($input['api_path'] ?? 'api'), '/') ?: 'api',
        'school_note' => trim((string) ($input['school_note'] ?? '')),
    ], $schoolId);

    if (!$saved) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not save settings.']);
        exit;
    }

    sx_apply_school_settings_to_config();

    echo json_encode([
        'success' => true,
        'message' => 'School LAN settings saved.',
        'settings' => [
            'lan_server_ip' => $ip,
            'api_port' => $port,
            'api_path' => trim((string) ($input['api_path'] ?? 'api'), '/'),
            'school_note' => trim((string) ($input['school_note'] ?? '')),
            'local_api_base' => sx_resolved_local_api_base() ?? '',
        ],
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown action.']);
