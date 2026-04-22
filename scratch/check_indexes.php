<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$indexes = DB::select('SHOW INDEX FROM books');
foreach ($indexes as $index) {
    echo "Index: {$index->Key_name}, Unique: {$index->Non_unique}\n";
}
