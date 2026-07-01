<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/api_client.php';

sx_require_login();

$verify = sx_agent_verify_token((string) $_SESSION['agent_token']);
if (!$verify['ok']) {
    sx_flash('error', 'Your session has expired. Please sign in again.');
    header('Location: logout.php');
    exit;
}

$appName = (string) sx_config('app_name', 'ShuleXpert Fingerprint Attendance System');
$poweredBy = (string) sx_config('powered_by', 'EmCa Techonologies');
$schoolName = (string) ($_SESSION['school_name'] ?? 'School');
$userName = (string) ($_SESSION['user_name'] ?? 'Admin');
$serverLabel = (string) ($_SESSION['server_label'] ?? sx_active_server_label());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= htmlspecialchars($appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@600;700&family=Open+Sans&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-wrap">
    <div class="dashboard-top">
        <div>
            <h1><?= htmlspecialchars($appName) ?></h1>
            <div class="meta"><?= htmlspecialchars($schoolName) ?> — <?= htmlspecialchars($userName) ?></div>
        </div>
        <a href="logout.php" class="btn-outline-light"><i class="fa fa-sign-out-alt"></i> Sign Out</a>
    </div>

    <div class="dashboard-body">
        <?php if ($msg = sx_flash('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = sx_flash('error')): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="dashboard-card">
            <h2><i class="fa fa-fingerprint"></i> School Sync Agent</h2>
            <p>You are signed in. Next step: connect your UF200-S device on the school LAN and start syncing attendance with ShuleXpert.</p>
            <ul>
                <li><strong>School:</strong> <?= htmlspecialchars($schoolName) ?></li>
                <li><strong>Admin:</strong> <?= htmlspecialchars($userName) ?></li>
                <li><strong>Environment:</strong> <?= htmlspecialchars($serverLabel) ?></li>
                <li><strong>Connection:</strong> <span style="color:#1f6b3f;">Connected</span></li>
            </ul>
            <p class="login-footer" style="margin-top:24px;">Powered by <?= htmlspecialchars($poweredBy) ?></p>
        </div>
    </div>
</div>
</body>
</html>
