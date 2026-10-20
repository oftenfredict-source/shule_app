<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_portal_quiz_attempts')) {
            return;
        }

        Schema::table('student_portal_quiz_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('student_portal_quiz_attempts', 'manual_scores')) {
                $table->json('manual_scores')->nullable()->after('answers');
            }
            if (!Schema::hasColumn('student_portal_quiz_attempts', 'teacher_feedback')) {
                $table->json('teacher_feedback')->nullable()->after('manual_scores');
            }
            if (!Schema::hasColumn('student_portal_quiz_attempts', 'graded_at')) {
                $table->timestamp('graded_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('student_portal_quiz_attempts', 'graded_by')) {
                $table->unsignedBigInteger('graded_by')->nullable()->after('graded_at');
            }
        });

        // Allow half-points from teacher marking / matching partial credit
        try {
            DB::statement('ALTER TABLE student_portal_quiz_attempts MODIFY score DECIMAL(8,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE student_portal_quiz_attempts MODIFY total DECIMAL(8,2) NOT NULL DEFAULT 0');
        } catch (\Throwable $e) {
            // Non-MySQL or already decimal — ignore
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_portal_quiz_attempts')) {
            return;
        }

        Schema::table('student_portal_quiz_attempts', function (Blueprint $table) {
            foreach (['manual_scores', 'teacher_feedback', 'graded_at', 'graded_by'] as $col) {
                if (Schema::hasColumn('student_portal_quiz_attempts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
