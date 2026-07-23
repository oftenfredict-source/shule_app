<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Duo paper results: one row per paper (componentID).
     * Non-duo subjects keep componentID = 0.
     */
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            if (!Schema::hasColumn('results', 'componentID')) {
                $table->unsignedBigInteger('componentID')->default(0)->after('class_subjectID');
            }
        });

        Schema::table('results', function (Blueprint $table) {
            try {
                $table->dropUnique('student_test_week_unique');
            } catch (\Throwable $e) {
                // Index may already be missing on some environments
            }
        });

        Schema::table('results', function (Blueprint $table) {
            $table->unique(
                ['studentID', 'examID', 'class_subjectID', 'test_week', 'componentID'],
                'student_exam_subject_week_component_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            try {
                $table->dropUnique('student_exam_subject_week_component_unique');
            } catch (\Throwable $e) {
                //
            }
        });

        Schema::table('results', function (Blueprint $table) {
            $table->unique(
                ['studentID', 'examID', 'class_subjectID', 'test_week'],
                'student_test_week_unique'
            );
        });

        Schema::table('results', function (Blueprint $table) {
            if (Schema::hasColumn('results', 'componentID')) {
                $table->dropColumn('componentID');
            }
        });
    }
};
