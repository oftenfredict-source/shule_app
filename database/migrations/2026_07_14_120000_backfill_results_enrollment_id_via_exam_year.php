<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill results.enrollmentID using examinations.academic_yearID + student_enrollments
 * so manageResults can show historical / completed students via enrollment.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('results')
            || !Schema::hasColumn('results', 'enrollmentID')
            || !Schema::hasTable('student_enrollments')
            || !Schema::hasColumn('examinations', 'academic_yearID')) {
            return;
        }

        // Prefer matching enrollment for the exam's academic year
        DB::statement("
            UPDATE results r
            INNER JOIN examinations e ON e.examID = r.examID
            INNER JOIN (
                SELECT se1.studentID, se1.schoolID, se1.academic_yearID, MIN(se1.enrollmentID) AS enrollmentID
                FROM student_enrollments se1
                GROUP BY se1.studentID, se1.schoolID, se1.academic_yearID
            ) se ON se.studentID = r.studentID
                AND se.schoolID = e.schoolID
                AND se.academic_yearID = e.academic_yearID
            SET r.enrollmentID = se.enrollmentID
            WHERE r.enrollmentID IS NULL
              AND e.academic_yearID IS NOT NULL
        ");

        // Fallback: Active enrollment for student (any year) if still null
        DB::statement("
            UPDATE results r
            INNER JOIN (
                SELECT se1.studentID, MAX(se1.enrollmentID) AS enrollmentID
                FROM student_enrollments se1
                WHERE se1.status = 'Active'
                GROUP BY se1.studentID
            ) se ON se.studentID = r.studentID
            SET r.enrollmentID = se.enrollmentID
            WHERE r.enrollmentID IS NULL
        ");
    }

    public function down(): void
    {
        // Non-destructive: keep enrollmentIDs
    }
};
