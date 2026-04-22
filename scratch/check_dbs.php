<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$databases = DB::select('SHOW DATABASES');
foreach ($databases as $db) {
    echo "DB: {$db->Database}\n";
}
echo "Current DB: " . DB::connection()->getDatabaseName() . "\n";
