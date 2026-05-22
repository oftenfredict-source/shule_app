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
        if (Schema::hasTable('permission_approval_chains')) {
            Schema::table('permission_approval_chains', function (Blueprint $table) {
                if (!Schema::hasColumn('permission_approval_chains', 'is_admin')) {
                    $table->boolean('is_admin')->default(false)->after('profession_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permission_approval_chains')) {
            Schema::table('permission_approval_chains', function (Blueprint $table) {
                if (Schema::hasColumn('permission_approval_chains', 'is_admin')) {
                    $table->dropColumn('is_admin');
                }
            });
        }
    }
};
