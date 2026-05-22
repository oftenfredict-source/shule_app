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
        Schema::table('student_permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('student_permissions', 'current_approval_order')) {
                $table->integer('current_approval_order')->default(0)->after('final_status');
            }
            if (!Schema::hasColumn('student_permissions', 'is_chain_completed')) {
                $table->boolean('is_chain_completed')->default(false)->after('current_approval_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('student_permissions', 'current_approval_order')) {
                $table->dropColumn('current_approval_order');
            }
            if (Schema::hasColumn('student_permissions', 'is_chain_completed')) {
                $table->dropColumn('is_chain_completed');
            }
        });
    }
};
