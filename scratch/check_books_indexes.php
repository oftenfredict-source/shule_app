<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if ($tableName == 'books') {
        echo "Table: $tableName\n";
        $indexes = DB::select("SHOW INDEX FROM $tableName");
        foreach ($indexes as $index) {
            echo "  Index: {$index->Key_name}, Unique: " . ($index->Non_unique ? 'No' : 'Yes') . "\n";
        }
    }
}
