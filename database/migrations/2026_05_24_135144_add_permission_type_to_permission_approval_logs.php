<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permission_approval_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('permission_approval_logs', 'permission_type')) {
                $table->string('permission_type')->default('teacher')->after('permissionID');
                $table->index(['permissionID', 'permission_type']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_approval_logs', function (Blueprint $table) {
            if (Schema::hasColumn('permission_approval_logs', 'permission_type')) {
                $table->dropColumn('permission_type');
            }
        });
    }
};
