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

// Apache / PHP-FPM often drops Authorization. The desktop app sends only
// Bearer, so copy every place the header can land before Laravel boots.
if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $authorization = null;
    foreach (['REDIRECT_HTTP_AUTHORIZATION', 'REDIRECT_REDIRECT_HTTP_AUTHORIZATION'] as $authKey) {
        if (!empty($_SERVER[$authKey]) && is_string($_SERVER[$authKey])) {
            $authorization = $_SERVER[$authKey];
            break;
        }
    }
    if ($authorization === null) {
        foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $envKey) {
            $fromEnv = getenv($envKey);
            if (is_string($fromEnv) && $fromEnv !== '') {
                $authorization = $fromEnv;
                break;
            }
        }
    }
    if ($authorization === null && function_exists('getallheaders')) {
        $headers = getallheaders();
        if (is_array($headers)) {
            foreach ($headers as $name => $value) {
                if (strcasecmp((string) $name, 'Authorization') === 0 && is_string($value) && $value !== '') {
                    $authorization = $value;
                    break;
                }
            }
        }
    }
    if (is_string($authorization) && $authorization !== '') {
        $_SERVER['HTTP_AUTHORIZATION'] = $authorization;
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
