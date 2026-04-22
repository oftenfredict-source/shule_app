<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Try to drop from shuleLink (just in case)
    DB::statement('ALTER TABLE books DROP INDEX books_school_isbn_unique');
    echo "Successfully dropped index from shuleLink\n";
} catch (\Exception $e) {
    echo "Error on shuleLink: " . $e->getMessage() . "\n";
}

try {
    // Try to connect to shulexpert_school and drop it
    Config::set('database.connections.mysql.database', 'shulexpert_school');
    DB::purge('mysql');
    DB::reconnect('mysql');
    
    DB::statement('ALTER TABLE books DROP INDEX books_school_isbn_unique');
    echo "Successfully dropped index from shulexpert_school\n";
} catch (\Exception $e) {
    echo "Error on shulexpert_school: " . $e->getMessage() . "\n";
}
