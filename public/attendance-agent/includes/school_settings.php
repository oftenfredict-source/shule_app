<?php

declare(strict_types=1);

function sx_school_settings_dir(): string
{
    $dir = dirname(__DIR__) . '/data';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

function sx_school_settings_path(?int $schoolId = null): string
{
    $sid = $schoolId ?? (int) ($_SESSION['schoolID'] ?? 0);
    return sx_school_settings_dir() . '/school_' . $sid . '.json';
}

/**
 * @return array<string, mixed>
 */
function sx_school_settings(?int $schoolId = null): array
{
    $path = sx_school_settings_path($schoolId);
    if (!is_file($path)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

/**
 * @param array<string, mixed> $settings
 */
function sx_save_school_settings(array $settings, ?int $schoolId = null): bool
{
    $sid = $schoolId ?? (int) ($_SESSION['schoolID'] ?? 0);
    if ($sid <= 0) {
        return false;
    }

    $existing = sx_school_settings($sid);
    $merged = array_merge($existing, $settings);
    $merged['schoolID'] = $sid;
    $merged['updated_at'] = date('c');

    return file_put_contents(
        sx_school_settings_path($sid),
        json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    ) !== false;
}

function sx_build_local_api_base_from_settings(array $settings): ?string
{
    $ip = trim((string) ($settings['lan_server_ip'] ?? ''));
    if ($ip === '') {
        return null;
    }

    $port = trim((string) ($settings['api_port'] ?? ''));
    $path = trim((string) ($settings['api_path'] ?? 'api'), '/');
    $host = $ip . ($port !== '' ? ':' . $port : '');

    return 'http://' . $host . '/' . $path;
}

function sx_resolved_local_api_base(): ?string
{
    return sx_build_local_api_base_from_settings(sx_school_settings());
}

function sx_apply_school_settings_to_config(): void
{
    global $config;

    $localApi = sx_resolved_local_api_base();
    if ($localApi && isset($config['servers']['local'])) {
        $config['servers']['local']['api_base'] = rtrim($localApi, '/');
        $settings = sx_school_settings();
        $ip = trim((string) ($settings['lan_server_ip'] ?? ''));
        $port = trim((string) ($settings['api_port'] ?? ''));
        $path = trim((string) ($settings['api_path'] ?? ''), '/');
        $webPath = $path !== '' ? dirname($path) : '';
        $webPath = $webPath === '.' ? '' : $webPath;
        $host = $ip . ($port !== '' ? ':' . $port : '');
        $config['servers']['local']['web_url'] = 'http://' . $host . ($webPath !== '' ? '/' . trim($webPath, '/') . '/' : '/');
        $config['servers']['local']['label'] = 'Local (' . $ip . ')';
    }
}
