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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE paper_approval_logs MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator', 'admin') NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE paper_approval_chains MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator', 'admin') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE paper_approval_logs MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator') NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE paper_approval_chains MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator') NULL");
    }
};
