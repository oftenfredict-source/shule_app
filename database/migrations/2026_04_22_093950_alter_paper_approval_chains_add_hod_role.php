<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Extend the special_role_type ENUM to include 'head_of_department'
     * for both paper_approval_chains and paper_approval_logs tables.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE paper_approval_chains MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator', 'admin', 'head_of_department') NULL");
        DB::statement("ALTER TABLE paper_approval_logs MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator', 'admin', 'head_of_department') NULL");
        DB::statement("ALTER TABLE result_approvals MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator', 'head_of_department') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE paper_approval_chains MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator', 'admin') NULL");
        DB::statement("ALTER TABLE paper_approval_logs MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator', 'admin') NULL");
        DB::statement("ALTER TABLE result_approvals MODIFY COLUMN special_role_type ENUM('class_teacher', 'coordinator') NULL");
    }
};
