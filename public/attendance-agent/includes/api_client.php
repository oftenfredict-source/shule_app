<?php

declare(strict_types=1);

/**
 * @return array{ok:bool,status:int,data:array<string,mixed>,raw:string,url:string}
 */
function sx_api_request(string $method, string $endpoint, array $payload = [], ?string $token = null, ?string $apiBase = null, int $timeout = 45): array
{
    $base = rtrim($apiBase ?? sx_active_api_base(), '/');
    $url = $base . '/' . ltrim($endpoint, '/');

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

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
        CURLOPT_SSL_VERIFYPEER => $sslVerify,
        CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ]);

    if (!empty($payload)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $raw = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === '' && $curlError !== '') {
        return [
            'ok' => false,
            'status' => 0,
            'data' => [
                'message' => 'Cannot reach ShuleXpert (' . sx_active_server_label() . '). Check your internet connection.',
            ],
            'raw' => '',
            'url' => $url,
        ];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = [
            'message' => 'Unable to connect to ShuleXpert. Please try again.',
        ];
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
    ]);

    $data = $response['data'];
    $message = (string) ($data['message'] ?? 'Login failed.');

    if (!$response['ok'] || empty($data['success'])) {
        return ['ok' => false, 'message' => $message];
    }

    return ['ok' => true, 'message' => $message, 'data' => $data];
}

/**
 * @return array{ok:bool,message:string,data?:array<string,mixed>}
 */
function sx_agent_verify_token(string $token): array
{
    $response = sx_api_request('GET', 'school-agent/verify', [], $token);
    $data = $response['data'];
    $message = (string) ($data['message'] ?? 'Token verification failed.');

    if (!$response['ok'] || empty($data['success'])) {
        return ['ok' => false, 'message' => $message];
    }

    return ['ok' => true, 'message' => $message, 'data' => $data];
}

/**
 * @return array{ok:bool,message:string,label:string,api_base:string}
 */
function sx_ping_server(string $serverKey): array
{
    $server = sx_server_config($serverKey);
    if ($server === null) {
        return ['ok' => false, 'message' => 'Server haijulikani.', 'label' => $serverKey, 'api_base' => ''];
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

    $message = (string) ($data['message'] ?? 'Server haipatikani.');
    if ($response['status'] === 0) {
        $message = (string) ($data['message'] ?? 'Connection failed.');
    }

    return [
        'ok' => false,
        'message' => $message,
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
