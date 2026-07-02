<?php

declare(strict_types=1);

function sx_resolve_ca_bundle(): ?string
{
    $configured = sx_config('ca_bundle');
    if (is_string($configured) && $configured !== '' && is_file($configured)) {
        return $configured;
    }

    $candidates = [
        'C:/laragon/etc/ssl/cacert.pem',
        'C:\\laragon\\etc\\ssl\\cacert.pem',
        dirname(__DIR__, 5) . '/etc/ssl/cacert.pem',
        dirname(__DIR__, 5) . '\\etc\\ssl\\cacert.pem',
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return null;
}

function sx_map_connection_error_message(int $errno, string $error): string
{
    if ($errno === 60 || stripos($error, 'SSL certificate') !== false) {
        return 'Secure connection failed. Update PHP CA certificates on this PC or contact support.';
    }
    if ($errno === 6 || $errno === 7) {
        return 'Cannot reach the selected environment. Check internet or local server status.';
    }
    if ($errno === 28) {
        return 'Connection timed out. The server took too long to respond.';
    }

    return 'Unable to connect. Check your network and try again.';
}

function sx_format_login_error(array $response): string
{
    $status = (int) ($response['status'] ?? 0);
    $data = is_array($response['data'] ?? null) ? $response['data'] : [];
    $message = trim((string) ($data['message'] ?? ''));

    if ($status === 0) {
        if ($message !== '') {
            return $message;
        }
        return 'Unable to connect. Check your network and try again.';
    }

    if ($status === 404) {
        return 'This feature is not available on the selected environment yet. Please update the server.';
    }

    if ($status === 401) {
        if ($message !== '' && (stripos($message, 'token') !== false || stripos($message, 'session') !== false)) {
            return $message;
        }
        return 'Incorrect username or password.';
    }

    if ($status === 403) {
        return $message !== '' ? $message : 'Access denied for this account.';
    }

    if ($status === 422) {
        return 'Please enter both username and password.';
    }

    if ($status === 429) {
        return $message !== '' ? $message : 'Too many login attempts. Please wait and try again.';
    }

    if ($message !== '') {
        return $message;
    }

    return 'Sign-in failed. Please try again.';
}

/**
 * @return array{ok:bool,status:int,data:array<string,mixed>,raw:string,url:string}
 */
function sx_api_request(string $method, string $endpoint, array $payload = [], ?string $token = null, ?string $apiBase = null, int $timeout = 30): array
{
    $base = rtrim($apiBase ?? sx_active_api_base(), '/');
    $url = $base . '/' . ltrim($endpoint, '/');
    $method = strtoupper($method);

    $ch = curl_init($url);
    if ($ch === false) {
        return [
            'ok' => false,
            'status' => 0,
            'data' => ['message' => 'Failed to initialize request.'],
            'raw' => '',
            'url' => $url,
        ];
    }

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'User-Agent: ShuleXpert-Attendance-Agent/1.0',
        'X-Requested-With: XMLHttpRequest',
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $sslVerify = (bool) sx_config('ssl_verify', true);
    $curlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(8, $timeout),
        CURLOPT_SSL_VERIFYPEER => $sslVerify,
        CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ];

    if ($sslVerify) {
        $caBundle = sx_resolve_ca_bundle();
        if ($caBundle !== null) {
            $curlOptions[CURLOPT_CAINFO] = $caBundle;
        }
    }

    if ($method === 'POST') {
        $curlOptions[CURLOPT_POST] = true;
        $curlOptions[CURLOPT_POSTFIELDS] = json_encode($payload);
    } elseif ($method === 'GET') {
        // default
    } else {
        $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        if (!empty($payload)) {
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($payload);
        }
    }

    curl_setopt_array($ch, $curlOptions);

    $raw = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);

    if ($raw === '' && $curlError !== '') {
        return [
            'ok' => false,
            'status' => 0,
            'data' => [
                'message' => sx_map_connection_error_message($curlErrno, $curlError),
            ],
            'raw' => '',
            'url' => $url,
        ];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        if ($status === 404) {
            $data = ['message' => 'Sign-in is not available on this environment yet.'];
        } else {
            $data = ['message' => 'Unexpected response from server. Please try again.'];
        }
    }

    return [
        'ok' => $status >= 200 && $status < 300,
        'status' => $status,
        'data' => $data,
        'raw' => $raw,
        'url' => $url,
    ];
}

/**
 * @return array{ok:bool,message:string,data?:array<string,mixed>}
 */
function sx_agent_login(string $username, string $password, ?string $serverKey = null): array
{
    if ($serverKey !== null) {
        sx_set_active_server($serverKey);
    }

    $response = sx_api_request('POST', 'school-agent/login', [
        'username' => $username,
        'password' => $password,
    ], null, null, 25);

    $data = $response['data'];

    if (!$response['ok'] || empty($data['success'])) {
        return ['ok' => false, 'message' => sx_format_login_error($response)];
    }

    return ['ok' => true, 'message' => (string) ($data['message'] ?? 'Login successful'), 'data' => $data];
}

/**
 * @return array{ok:bool,message:string,data?:array<string,mixed>}
 */
function sx_agent_verify_token(string $token): array
{
    $response = sx_api_request('GET', 'school-agent/verify', [], $token, null, 20);
    $data = $response['data'];

    if (!$response['ok'] || empty($data['success'])) {
        return [
            'ok' => false,
            'message' => sx_format_login_error($response) ?: 'Session verification failed.',
        ];
    }

    return ['ok' => true, 'message' => (string) ($data['message'] ?? 'Token is valid'), 'data' => $data];
}

function sx_agent_token(): ?string
{
    $token = $_SESSION['agent_token'] ?? null;
    return is_string($token) && $token !== '' ? $token : null;
}

/**
 * @return array{ok:bool,status:int,data:array<string,mixed>}
 */
function sx_agent_api(string $method, string $endpoint, array $payload = [], int $timeout = 30): array
{
    $token = sx_agent_token();
    if (!$token) {
        return [
            'ok' => false,
            'status' => 401,
            'data' => ['success' => false, 'message' => 'Session expired. Please sign in again.'],
        ];
    }

    return sx_api_request($method, $endpoint, $payload, $token, null, $timeout);
}

/**
 * @return array{ok:bool,message:string,devices?:array<int,array<string,mixed>>}
 */
function sx_agent_list_devices(): array
{
    $response = sx_agent_api('GET', 'school-agent/devices');
    $data = $response['data'];

    if (!$response['ok'] || empty($data['success'])) {
        return ['ok' => false, 'message' => sx_format_login_error($response)];
    }

    return [
        'ok' => true,
        'message' => 'Devices loaded.',
        'devices' => is_array($data['devices'] ?? null) ? $data['devices'] : [],
    ];
}

/**
 * @return array{ok:bool,message:string,device?:array<string,mixed>}
 */
function sx_agent_save_device(array $fields, ?int $id = null): array
{
    $endpoint = 'school-agent/devices' . ($id ? '/' . $id : '');
    $method = $id ? 'PUT' : 'POST';
    $response = sx_agent_api($method, $endpoint, $fields);
    $data = $response['data'];

    if (!$response['ok'] || empty($data['success'])) {
        $message = sx_format_login_error($response);
        if (!empty($data['errors']) && is_array($data['errors'])) {
            $first = reset($data['errors']);
            if (is_array($first) && isset($first[0])) {
                $message = (string) $first[0];
            }
        }
        return ['ok' => false, 'message' => $message];
    }

    return [
        'ok' => true,
        'message' => (string) ($data['message'] ?? 'Saved.'),
        'device' => is_array($data['device'] ?? null) ? $data['device'] : null,
    ];
}

/**
 * @return array{ok:bool,message:string}
 */
function sx_agent_delete_device(int $id): array
{
    $response = sx_agent_api('DELETE', 'school-agent/devices/' . $id);
    $data = $response['data'];

    if (!$response['ok'] || empty($data['success'])) {
        return ['ok' => false, 'message' => sx_format_login_error($response)];
    }

    return ['ok' => true, 'message' => (string) ($data['message'] ?? 'Device deleted.')];
}

/**
 * @return array{ok:bool,message:string,device?:array<string,mixed>}
 */
function sx_agent_get_device(int $id): array
{
    $response = sx_agent_api('GET', 'school-agent/devices/' . $id);
    $data = $response['data'];

    if (!$response['ok'] || empty($data['success'])) {
        return ['ok' => false, 'message' => sx_format_login_error($response)];
    }

    return [
        'ok' => true,
        'message' => 'Device loaded.',
        'device' => is_array($data['device'] ?? null) ? $data['device'] : null,
    ];
}

/**
 * @return array{ok:bool,message:string,label:string,api_base:string}
 */
function sx_ping_server(string $serverKey): array
{
    $server = sx_server_config($serverKey);
    if ($server === null) {
        return ['ok' => false, 'message' => 'Unknown environment.', 'label' => $serverKey, 'api_base' => ''];
    }

    $response = sx_api_request('GET', 'health', [], null, rtrim($server['api_base'], '/'), 8);
    $data = $response['data'];
    $label = (string) $server['label'];
    $apiBase = (string) $server['api_base'];

    if ($response['ok'] && !empty($data['success'])) {
        return [
            'ok' => true,
            'message' => 'Connected',
            'label' => $label,
            'api_base' => $apiBase,
        ];
    }

    return [
        'ok' => false,
        'message' => sx_format_login_error($response),
        'label' => $label,
        'api_base' => $apiBase,
    ];
}

/**
 * @return array<string, array{ok:bool,message:string,label:string,api_base:string}>
 */
function sx_ping_all_servers(): array
{
    $results = [];
    foreach (sx_server_keys() as $key) {
        $results[$key] = sx_ping_server($key);
    }
    return $results;
}

/**
 * @param array<int, array<string, mixed>> $punches
 * @return array{ok:bool,message:string,data?:array<string,mixed>}
 */
function sx_agent_ingest_teacher_staff_live(string $deviceIp, array $punches): array
{
    $response = sx_agent_api('POST', 'school-agent/attendance/teacher-staff-live', [
        'device_ip' => $deviceIp,
        'punches' => $punches,
    ], 45);

    $data = $response['data'];

    if (!$response['ok'] || empty($data['success'])) {
        return ['ok' => false, 'message' => sx_format_login_error($response)];
    }

    return [
        'ok' => true,
        'message' => (string) ($data['message'] ?? 'Sent to live system.'),
        'data' => $data,
    ];
}
