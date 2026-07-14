<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add Truancy + Dropout to enrollment.promotion_status for school movement reporting.
     */
    public function up(): void
    {
        if (!Schema::hasTable('student_enrollments')) {
            return;
        }

        // MySQL / MariaDB enum widen (preserves existing values)
        DB::statement("ALTER TABLE student_enrollments MODIFY COLUMN promotion_status ENUM(
            'Pending',
            'Promoted',
            'Repeated',
            'Graduated',
            'Transferred',
            'Truancy',
            'Dropout'
        ) NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_enrollments')) {
            return;
        }

        // Reset new values before narrowing enum
        DB::table('student_enrollments')
            ->whereIn('promotion_status', ['Truancy', 'Dropout'])
            ->update(['promotion_status' => 'Pending']);

        DB::statement("ALTER TABLE student_enrollments MODIFY COLUMN promotion_status ENUM(
            'Pending',
            'Promoted',
            'Repeated',
            'Graduated',
            'Transferred'
        ) NOT NULL DEFAULT 'Pending'");
    }
};
