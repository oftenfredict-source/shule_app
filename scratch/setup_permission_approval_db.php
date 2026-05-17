<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Create permission_approval_chains table
if (!Schema::hasTable('permission_approval_chains')) {
    Schema::create('permission_approval_chains', function (Blueprint $table) {
        $table->id('chainID');
        $table->unsignedBigInteger('schoolID');
        $table->string('permission_type'); // student, teacher, staff
        $table->integer('approval_order');
        $table->unsignedBigInteger('role_id')->nullable(); // Spatie role ID
        $table->unsignedBigInteger('profession_id')->nullable(); // Staff position ID
        $table->string('role_name')->nullable();
        $table->timestamps();
    });
    echo "Table permission_approval_chains created.\n";
}

// 2. Create permission_approval_logs table
if (!Schema::hasTable('permission_approval_logs')) {
    Schema::create('permission_approval_logs', function (Blueprint $table) {
        $table->id('logID');
        $table->unsignedBigInteger('permissionID');
        $table->unsignedBigInteger('approver_id')->nullable();
        $table->string('approver_role')->nullable();
        $table->integer('approval_order');
        $table->string('status')->default('pending'); // pending, approved, rejected
        $table->text('admin_response')->nullable();
        $table->timestamps();
    });
    echo "Table permission_approval_logs created.\n";
}

// 3. Add columns to permission_requests
Schema::table('permission_requests', function (Blueprint $table) {
    if (!Schema::hasColumn('permission_requests', 'current_approval_order')) {
        $table->integer('current_approval_order')->default(1);
    }
    if (!Schema::hasColumn('permission_requests', 'is_chain_completed')) {
        $table->boolean('is_chain_completed')->default(false);
    }
});
echo "permission_requests table updated.\n";
