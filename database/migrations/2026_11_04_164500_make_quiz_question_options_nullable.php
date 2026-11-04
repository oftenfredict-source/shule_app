<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_portal_quiz_questions')) {
            return;
        }

        // Matching / fill / short / essay do not use classic MCQ option columns.
        DB::statement('ALTER TABLE `student_portal_quiz_questions` MODIFY `option_a` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `student_portal_quiz_questions` MODIFY `option_b` VARCHAR(255) NULL');
        // Widen beyond char(1) so true/false and fill answers can store properly.
        DB::statement('ALTER TABLE `student_portal_quiz_questions` MODIFY `correct_option` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_portal_quiz_questions')) {
            return;
        }

        DB::statement("UPDATE `student_portal_quiz_questions` SET `option_a` = '' WHERE `option_a` IS NULL");
        DB::statement("UPDATE `student_portal_quiz_questions` SET `option_b` = '' WHERE `option_b` IS NULL");
        DB::statement("UPDATE `student_portal_quiz_questions` SET `correct_option` = 'A' WHERE `correct_option` IS NULL OR `correct_option` = ''");
        DB::statement('ALTER TABLE `student_portal_quiz_questions` MODIFY `option_a` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `student_portal_quiz_questions` MODIFY `option_b` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `student_portal_quiz_questions` MODIFY `correct_option` CHAR(1) NOT NULL');
    }
};
