<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

unset(
    $_SESSION['agent_token'],
    $_SESSION['api_server'],
    $_SESSION['api_base'],
    $_SESSION['server_label'],
    $_SESSION['web_url'],
    $_SESSION['remote_server_name'],
    $_SESSION['remote_server_host'],
    $_SESSION['schoolID'],
    $_SESSION['school_name'],
    $_SESSION['registration_number'],
    $_SESSION['user_name'],
    $_SESSION['user_email'],
    $_SESSION['token_expires_in']
);

sx_flash('success', 'You have signed out successfully.');
sx_redirect('index.php');
