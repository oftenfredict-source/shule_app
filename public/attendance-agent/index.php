<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (sx_is_logged_in()) {
    sx_redirect('dashboard.php');
}

$appName = (string) sx_config('app_name', 'ShuleXpert Fingerprint Attendance System');
$poweredBy = (string) sx_config('powered_by', 'EmCa Techonologies');
$error = sx_flash('error');
$success = sx_flash('success');
$servers = sx_servers();
$defaultServer = sx_active_server_key();
$selectedServer = (string) ($_GET['server'] ?? $defaultServer);
if (!sx_is_valid_server_key($selectedServer)) {
    $selectedServer = $defaultServer;
}
$biometricImage = sx_biometric_image_url($selectedServer);
$biometricImagesByServer = sx_biometric_images_by_server();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@600;700&family=Open+Sans&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(sx_asset('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<div id="splash-screen">
    <div class="splash-content">
        <div class="splash-icon">
            <i class="fa fa-fingerprint"></i>
        </div>
        <div class="splash-text brand-font">
            ShuleXpert<br>Fingerprint Attendance System
        </div>
        <div class="splash-loader"></div>
        <div class="splash-powered">Powered by <?= htmlspecialchars($poweredBy) ?></div>
    </div>
</div>

<div id="login-page" class="login-page">
    <div class="login-shell">
        <div class="login-visual">
            <img id="loginBiometricImage"
                 src="<?= htmlspecialchars($biometricImage) ?>"
                 alt="Biometric Attendance"
                 referrerpolicy="no-referrer"
                 onerror="this.onerror=null;this.src='<?= htmlspecialchars(sx_server_asset_url('test')) ?>';">
            <div class="login-visual-overlay">
                <h2 class="brand-font">School LAN Sync</h2>
                <p>Read attendance from your school device on the local network and sync securely with ShuleXpert.</p>
            </div>
        </div>

        <div class="login-panel">
            <div class="login-brand brand-font">ShuleXpert</div>
            <div class="login-subtitle">Sign in as <strong>School Admin</strong> to run the sync agent.</div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= htmlspecialchars(sx_url('login.php')) ?>" id="loginForm" autocomplete="off">
                <label class="form-label" for="server">Environment</label>
                <div class="input-wrap">
                    <i class="fa fa-globe"></i>
                    <select class="form-control" id="server" name="server" required style="padding-left:42px;">
                        <?php foreach ($servers as $key => $server): ?>
                            <?php
                                $envLabel = (string) ($server['label'] ?? ucfirst($key));
                                $envUrl = rtrim((string) ($server['web_url'] ?? ''), '/');
                                $optionText = $envUrl !== '' ? $envLabel . ' (' . $envUrl . ')' : $envLabel;
                            ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $key === $selectedServer ? 'selected' : '' ?>>
                                <?= htmlspecialchars($optionText) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <label class="form-label" for="username">Username (Admin)</label>
                <div class="input-wrap">
                    <i class="fa fa-user"></i>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Registration number / username" required>
                </div>

                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <i class="fa fa-lock"></i>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Your password" required>
                </div>

                <button type="submit" class="btn-brand" id="loginBtn">
                    <i class="fa fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="login-footer">
                Powered by <?= htmlspecialchars($poweredBy) ?>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var biometricImages = <?= json_encode($biometricImagesByServer, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;
    var splash = document.getElementById('splash-screen');
    var loginPage = document.getElementById('login-page');
    var form = document.getElementById('loginForm');
    var loginBtn = document.getElementById('loginBtn');

    setTimeout(function () {
        if (splash) splash.classList.add('hide');
        if (loginPage) loginPage.classList.add('show');
        setTimeout(function () {
            if (splash) splash.style.display = 'none';
        }, 500);
    }, 1400);

    if (form && loginBtn) {
        form.addEventListener('submit', function () {
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Signing in...';
        });
    }

    var serverSelect = document.getElementById('server');
    var loginImg = document.getElementById('loginBiometricImage');
    if (serverSelect && loginImg) {
        serverSelect.addEventListener('change', function () {
            if (biometricImages[serverSelect.value]) {
                loginImg.src = biometricImages[serverSelect.value];
            }
        });
    }
})();
</script>
</body>
</html>
