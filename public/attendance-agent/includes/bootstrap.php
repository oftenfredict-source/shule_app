<?php

declare(strict_types=1);

$configPath = dirname(__DIR__) . '/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    echo 'Missing config.php. Copy config.example.php to config.php and set your API URL.';
    exit;
}

$config = require $configPath;

require_once __DIR__ . '/school_settings.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($config['session_name'] ?? 'sx_fingerprint_agent');
    $cookiePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/attendance-agent'));
    if ($cookiePath === '/' || $cookiePath === '.' || $cookiePath === '') {
        $cookiePath = '/';
    }
    session_set_cookie_params([
        'lifetime' => (int) ($config['session_lifetime'] ?? 43200),
        'path' => $cookiePath,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

sx_apply_school_settings_to_config();

function sx_config(string $key, $default = null)
{
    global $config;
    return $config[$key] ?? $default;
}

function sx_web_path(): string
{
    $configured = trim((string) sx_config('web_path', ''), '/');
    if ($configured !== '') {
        return '/' . $configured;
    }

    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '/attendance-agent/index.php');
    $dir = str_replace('\\', '/', dirname($script));
    $dir = rtrim($dir, '/');

    if ($dir === '' || $dir === '.' || $dir === '/') {
        return '/attendance-agent';
    }

    return $dir;
}

function sx_base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . sx_web_path();
}

function sx_asset(string $path): string
{
    return rtrim(sx_base_url(), '/') . '/' . ltrim($path, '/');
}

/** Internal link within attendance-agent (index.php, dashboard.php, api/...). */
function sx_url(string $path): string
{
    return sx_asset($path);
}

function sx_is_logged_in(): bool
{
    return !empty($_SESSION['agent_token']) && !empty($_SESSION['schoolID']);
}

function sx_require_login(): void
{
    if (!sx_is_logged_in()) {
        sx_redirect('index.php');
    }
}

function sx_redirect(string $path): void
{
    if (!preg_match('#^https?://#i', $path)) {
        $path = sx_url($path);
    }
    header('Location: ' . $path);
    exit;
}

function sx_flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

/**
 * @return array<string, array{label:string,api_base:string,web_url:string}>
 */
function sx_servers(): array
{
    $servers = sx_config('servers', []);
    return is_array($servers) ? $servers : [];
}

function sx_server_keys(): array
{
    return array_keys(sx_servers());
}

/**
 * @return array{label:string,api_base:string,web_url:string}|null
 */
function sx_server_config(string $key): ?array
{
    $servers = sx_servers();
    if (!isset($servers[$key]) || !is_array($servers[$key])) {
        return null;
    }

    $server = $servers[$key];
    if (empty($server['api_base'])) {
        return null;
    }

    return $server;
}

function sx_is_valid_server_key(string $key): bool
{
    return sx_server_config($key) !== null;
}

function sx_active_server_key(): string
{
    $sessionKey = (string) ($_SESSION['api_server'] ?? '');
    if ($sessionKey !== '' && sx_is_valid_server_key($sessionKey)) {
        return $sessionKey;
    }

    $default = (string) sx_config('default_server', 'test');
    if (sx_is_valid_server_key($default)) {
        return $default;
    }

    $keys = sx_server_keys();
    return $keys[0] ?? 'test';
}

function sx_active_api_base(): string
{
    $server = sx_server_config(sx_active_server_key());
    return rtrim((string) ($server['api_base'] ?? ''), '/');
}

function sx_active_server_label(): string
{
    $server = sx_server_config(sx_active_server_key());
    return (string) ($server['label'] ?? 'ShuleXpert');
}

function sx_active_web_url(): string
{
    $server = sx_server_config(sx_active_server_key());
    return (string) ($server['web_url'] ?? '');
}

function sx_set_active_server(string $key): bool
{
    if (!sx_is_valid_server_key($key)) {
        return false;
    }
    $_SESSION['api_server'] = $key;
    return true;
}

/**
 * Public asset URL on a ShuleXpert server (images served from web root, not API).
 */
function sx_server_asset_url(string $serverKey, ?string $relativePath = null): string
{
    $path = $relativePath ?? (string) sx_config('biometric_image_path', 'images/biometric.jpg');
    $path = ltrim($path, '/');

    $server = sx_server_config($serverKey);
    if ($server !== null && !empty($server['web_url'])) {
        return rtrim((string) $server['web_url'], '/') . '/' . $path;
    }

    $fallback = (string) sx_config('assets_fallback_web_url', 'https://test.shulexpert.com/');
    return rtrim($fallback, '/') . '/' . $path;
}

function sx_biometric_image_url(?string $serverKey = null): string
{
    $key = $serverKey ?? sx_active_server_key();
    if ($key === 'local') {
        return sx_server_asset_url((string) sx_config('assets_server', 'test'));
    }
    return sx_server_asset_url($key);
}

/**
 * @return array<string, string>
 */
function sx_biometric_images_by_server(): array
{
    $map = [];
    foreach (sx_server_keys() as $key) {
        $map[$key] = sx_biometric_image_url($key);
    }
    return $map;
}
