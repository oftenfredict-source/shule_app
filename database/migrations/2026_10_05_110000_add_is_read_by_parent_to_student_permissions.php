<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('student_permissions', 'is_read_by_parent')) {
                $table->boolean('is_read_by_parent')->default(false)->after('is_chain_completed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('student_permissions', 'is_read_by_parent')) {
                $table->dropColumn('is_read_by_parent');
            }
        });
    }
};
