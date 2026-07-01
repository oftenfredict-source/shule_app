<?php

declare(strict_types=1);

set_time_limit(60);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/api_client.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sx_redirect('index.php');
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$serverKey = trim((string) ($_POST['server'] ?? ''));

if ($username === '' || $password === '') {
    sx_flash('error', 'Username and password are required.');
    sx_redirect('index.php');
}

if ($serverKey === '' || !sx_is_valid_server_key($serverKey)) {
    sx_flash('error', 'Please select a valid environment.');
    sx_redirect('index.php');
}

sx_set_active_server($serverKey);

$result = sx_agent_login($username, $password, $serverKey);

if (!$result['ok']) {
    sx_flash('error', $result['message']);
    sx_redirect('index.php');
}

$data = $result['data'] ?? [];
$token = (string) ($data['token'] ?? '');
$school = is_array($data['school'] ?? null) ? $data['school'] : [];
$user = is_array($data['user'] ?? null) ? $data['user'] : [];

if ($token === '') {
    sx_flash('error', 'Sign-in could not be completed. Please try again.');
    sx_redirect('index.php');
}

$_SESSION['agent_token'] = $token;
$_SESSION['api_server'] = $serverKey;
$_SESSION['api_base'] = sx_active_api_base();
$_SESSION['server_label'] = sx_active_server_label();
$_SESSION['web_url'] = sx_active_web_url();
$serverInfo = is_array($data['server'] ?? null) ? $data['server'] : [];
$_SESSION['remote_server_name'] = (string) ($serverInfo['name'] ?? $serverKey);
$_SESSION['remote_server_host'] = (string) ($serverInfo['host'] ?? '');
$_SESSION['schoolID'] = (int) ($school['schoolID'] ?? 0);
$_SESSION['school_name'] = (string) ($school['school_name'] ?? '');
$_SESSION['registration_number'] = (string) ($school['registration_number'] ?? '');
$_SESSION['user_name'] = (string) ($user['name'] ?? $username);
$_SESSION['user_email'] = (string) ($user['email'] ?? '');
$_SESSION['token_expires_in'] = (int) ($data['expires_in'] ?? 0);

sx_flash('success', 'Welcome. You have signed in successfully.');
sx_redirect('dashboard.php');
