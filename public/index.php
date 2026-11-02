<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Apache internal rewrite stores Authorization as REDIRECT_HTTP_AUTHORIZATION.
// Without this, school-agent verify sees no Bearer token and the desktop app logs out.
if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
    foreach (['REDIRECT_HTTP_AUTHORIZATION', 'REDIRECT_REDIRECT_HTTP_AUTHORIZATION'] as $authKey) {
        if (!empty($_SERVER[$authKey]) && is_string($_SERVER[$authKey])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER[$authKey];
            break;
        }
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
