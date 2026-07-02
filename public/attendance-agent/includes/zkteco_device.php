<?php

declare(strict_types=1);

function sx_load_composer_autoload(): bool
{
    static $loaded = false;
    if ($loaded) {
        return true;
    }

    $candidates = [
        dirname(__DIR__) . '/vendor/autoload.php',
        dirname(__DIR__, 3) . '/vendor/autoload.php',
        dirname(__DIR__, 4) . '/vendor/autoload.php',
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            require_once $path;
            $loaded = true;
            return true;
        }
    }

    return false;
}

function sx_zkteco_library_missing_message(): string
{
  return 'ZKTeco library not found. Open a terminal in the attendance-agent folder and run: composer install';
}

/**
 * @return array<string, mixed>
 */
function sx_zkteco_environment_diagnostics(): array
{
    $localVendor = dirname(__DIR__) . '/vendor/autoload.php';

    return [
        'php_version' => PHP_VERSION,
        'sockets_extension' => extension_loaded('sockets') && function_exists('socket_create'),
        'sockets_message' => sx_sockets_extension_message(),
        'zkteco_library' => sx_load_composer_autoload() && class_exists(\CodingLibs\ZktecoPhp\Libs\ZKTeco::class),
        'local_vendor' => is_file($localVendor),
        'local_vendor_path' => $localVendor,
        'agent_host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'server_addr' => $_SERVER['SERVER_ADDR'] ?? null,
    ];
}

function sx_sockets_extension_message(): ?string
{
    if (extension_loaded('sockets') && function_exists('socket_create')) {
        return null;
    }

    return 'PHP Sockets extension is disabled on this server. In Laragon: Menu → PHP → php.ini → enable extension=sockets, then restart Apache.';
}

/**
 * @return array{ok:bool,message:string}
 */
function sx_device_port_open(string $ip, int $port, int $timeout = 5): array
{
    $errno = 0;
    $errstr = '';
    $connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);

    if (is_resource($connection)) {
        fclose($connection);
        return ['ok' => true, 'message' => ''];
    }

    return [
        'ok' => false,
        'message' => 'Cannot reach ' . $ip . ':' . $port . ' on the LAN. '
            . 'Ensure this PC and the fingerprint device are on the same network, the device is powered on, '
            . 'and Windows Firewall allows outbound connections. (' . trim($errstr) . ')',
    ];
}

/**
 * @return array{client?:object,message:string}
 */
function sx_zkteco_connect(string $ip, int $port, int $password, bool $fast = false): array
{
    $lastMessage = 'Failed to connect to device.';
    $timeouts = $fast ? [8] : [20];
    $pingModes = $fast ? [false] : [false, true];

    foreach ($pingModes as $shouldPing) {
        foreach ($timeouts as $timeout) {
            try {
                $client = new \CodingLibs\ZktecoPhp\Libs\ZKTeco(
                    ip: $ip,
                    port: $port,
                    shouldPing: $shouldPing,
                    timeout: $timeout,
                    password: $password
                );

                if ($client->connect()) {
                    return ['client' => $client, 'message' => ''];
                }

                $lastMessage = 'Device did not accept the connection. Verify Comm Key (usually 0) and device IP.';
            } catch (\Throwable $e) {
                $lastMessage = $e->getMessage();
            }
        }
    }

    return ['message' => $lastMessage];
}

/**
 * @return array{success:bool,message:string,device_info?:array<string,mixed>,device_time?:string,warnings?:array<int,string>,hints?:array<int,string>}
 */
function sx_zkteco_test_connection(string $ip, int $port, string $commKey): array
{
    if ($msg = sx_sockets_extension_message()) {
        return [
            'success' => false,
            'message' => $msg,
            'diagnostics' => sx_zkteco_environment_diagnostics(),
        ];
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return ['success' => false, 'message' => 'Invalid device IP address.'];
    }

    if (!sx_load_composer_autoload()) {
        return [
            'success' => false,
            'message' => sx_zkteco_library_missing_message(),
            'diagnostics' => sx_zkteco_environment_diagnostics(),
        ];
    }

    $portCheck = sx_device_port_open($ip, $port);
    if (!$portCheck['ok']) {
        return [
            'success' => false,
            'message' => $portCheck['message'],
            'hints' => [
                'Ping the device IP from this PC (Command Prompt: ping ' . $ip . ').',
                'UF200-S default port is 4370.',
            ],
            'diagnostics' => sx_zkteco_environment_diagnostics(),
        ];
    }

    $password = is_numeric($commKey) ? (int) $commKey : 0;
    $connected = sx_zkteco_connect($ip, $port, $password);

    if (empty($connected['client'])) {
        return [
            'success' => false,
            'message' => $connected['message'],
            'hints' => [
                'Comm Key on device must match the value saved (default 0).',
                'On device: Menu → Comm → PC Connection → set Comm Key.',
                'Test from the school PC running this agent, not from live hosting.',
            ],
            'diagnostics' => sx_zkteco_environment_diagnostics(),
        ];
    }

    /** @var object $client */
    $client = $connected['client'];
    $deviceInfo = [];
    $warnings = [];

    foreach ([
        'device_name' => 'deviceName',
        'device_id' => 'deviceId',
        'serial_number' => 'serialNumber',
        'version' => 'version',
        'platform' => 'platform',
    ] as $key => $method) {
        try {
            if (method_exists($client, $method)) {
                $deviceInfo[$key] = $client->{$method}() ?: null;
            }
        } catch (\Throwable $e) {
            $warnings[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $e->getMessage();
        }
    }

    $deviceInfo['ip'] = $ip;
    $deviceInfo['port'] = $port;
    $deviceInfo['comm_key'] = $password;

    $deviceTime = null;
    try {
        if (method_exists($client, 'getTime')) {
            $deviceTime = $client->getTime();
        }
    } catch (\Throwable $e) {
        $warnings[] = 'Device time: ' . $e->getMessage();
    }

    try {
        if (method_exists($client, 'disconnect')) {
            $client->disconnect();
        }
    } catch (\Throwable $e) {
        $warnings[] = 'Disconnect: ' . $e->getMessage();
    }

    return [
        'success' => true,
        'message' => 'Device connection successful.',
        'device_info' => $deviceInfo,
        'device_time' => $deviceTime,
        'warnings' => $warnings,
    ];
}

/**
 * @return array{success:bool,message:string,device_info?:array<string,mixed>,device_time?:string}
 */
function sx_zkteco_device_info(string $ip, int $port, string $commKey): array
{
    $result = sx_zkteco_test_connection($ip, $port, $commKey);
    if (!$result['success']) {
        return $result;
    }

    return [
        'success' => true,
        'message' => 'Device information loaded.',
        'device_info' => $result['device_info'] ?? [],
        'device_time' => $result['device_time'] ?? null,
    ];
}

/**
 * Pull today's attendance logs from device (for live agent polling).
 *
 * @return array{success:bool,message:string,date?:string,records?:array<int,array<string,mixed>>}
 */
function sx_zkteco_pull_today_attendance(string $ip, int $port, string $commKey): array
{
    if ($msg = sx_sockets_extension_message()) {
        return ['success' => false, 'message' => $msg];
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return ['success' => false, 'message' => 'Invalid device IP address.'];
    }

    if (!sx_load_composer_autoload()) {
        return ['success' => false, 'message' => sx_zkteco_library_missing_message()];
    }

    $portCheck = sx_device_port_open($ip, $port, 4);
    if (!$portCheck['ok']) {
        return ['success' => false, 'message' => $portCheck['message']];
    }

    $password = is_numeric($commKey) ? (int) $commKey : 0;
    $connected = sx_zkteco_connect($ip, $port, $password, true);

    if (empty($connected['client'])) {
        return ['success' => false, 'message' => $connected['message']];
    }

    /** @var object $client */
    $client = $connected['client'];
    $today = date('Y-m-d');
    $records = [];

    try {
        if (!method_exists($client, 'getAttendances')) {
            return ['success' => false, 'message' => 'Device does not support attendance logs.'];
        }

        $raw = $client->getAttendances() ?? [];
        if (!is_array($raw)) {
            $raw = [];
        }

        foreach ($raw as $row) {
            if (!is_array($row) || empty($row['record_time'])) {
                continue;
            }

            $recordTime = (string) $row['record_time'];
            if (strpos($recordTime, $today) !== 0) {
                continue;
            }

            $userId = isset($row['user_id']) ? (int) $row['user_id'] : 0;
            if ($userId <= 0) {
                continue;
            }

            $records[] = [
                'user_id' => $userId,
                'record_time' => $recordTime,
                'type' => (string) ($row['type'] ?? 'Fingerprint'),
                'state' => $row['state'] ?? null,
                'device_ip' => (string) ($row['device_ip'] ?? $ip),
            ];
        }

        usort($records, function ($a, $b) {
            return strcmp($a['record_time'], $b['record_time']);
        });
    } catch (\Throwable $e) {
        return ['success' => false, 'message' => 'Failed to read attendance: ' . $e->getMessage()];
    } finally {
        try {
            if (method_exists($client, 'disconnect')) {
                $client->disconnect();
            }
        } catch (\Throwable $e) {
            // ignore disconnect errors
        }
    }

    return [
        'success' => true,
        'message' => count($records) ? 'Attendance loaded from device.' : 'No punches today on device yet.',
        'date' => $today,
        'records' => $records,
        'count' => count($records),
    ];
}
