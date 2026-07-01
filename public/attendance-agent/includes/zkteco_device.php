<?php

declare(strict_types=1);

function sx_load_composer_autoload(): bool
{
    static $loaded = false;
    if ($loaded) {
        return true;
    }

    $candidates = [
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

function sx_sockets_extension_message(): ?string
{
    if (extension_loaded('sockets') && function_exists('socket_create')) {
        return null;
    }

    return 'PHP Sockets extension is disabled. Enable extension=sockets in php.ini and restart the web server.';
}

/**
 * @return array{success:bool,message:string,device_info?:array<string,mixed>,device_time?:string,warnings?:array<int,string>}
 */
function sx_zkteco_test_connection(string $ip, int $port, string $commKey): array
{
    if ($msg = sx_sockets_extension_message()) {
        return ['success' => false, 'message' => $msg];
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return ['success' => false, 'message' => 'Invalid device IP address.'];
    }

    if (!sx_load_composer_autoload()) {
        return ['success' => false, 'message' => 'Device library is not available on this PC.'];
    }

    $password = is_numeric($commKey) ? (int) $commKey : 0;

    try {
        $client = new \CodingLibs\ZktecoPhp\Libs\ZKTeco(
            ip: $ip,
            port: $port,
            shouldPing: true,
            timeout: 15,
            password: $password
        );

        if (!$client->connect()) {
            return ['success' => false, 'message' => 'Failed to connect to device. Check IP, port, and Comm Key.'];
        }

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

        $client->disconnect();

        return [
            'success' => true,
            'message' => 'Device connection successful.',
            'device_info' => $deviceInfo,
            'device_time' => $deviceTime,
            'warnings' => $warnings,
        ];
    } catch (\Throwable $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
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
