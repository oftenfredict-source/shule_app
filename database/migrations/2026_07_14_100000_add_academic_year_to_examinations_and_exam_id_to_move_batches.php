<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            if (!Schema::hasColumn('examinations', 'academic_yearID')) {
                $table->unsignedBigInteger('academic_yearID')->nullable()->after('schoolID');
                $table->foreign('academic_yearID')
                    ->references('academic_yearID')
                    ->on('academic_years')
                    ->onDelete('set null');
                $table->index(['schoolID', 'academic_yearID'], 'exam_school_year_idx');
            }
        });

        Schema::table('student_move_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('student_move_batches', 'examID')) {
                $table->unsignedBigInteger('examID')->nullable()->after('to_academic_yearID');
                $table->foreign('examID')
                    ->references('examID')
                    ->on('examinations')
                    ->onDelete('set null');
                $table->index(['examID', 'status'], 'smb_exam_status_idx');
            }
        });

        // Backfill exams with each school's current academic year
        $schools = DB::table('schools')->pluck('schoolID');
        foreach ($schools as $schoolID) {
            $years = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('is_current', 1)
                ->orderByDesc('academic_yearID')
                ->get();

            if ($years->isEmpty()) {
                $years = DB::table('academic_years')
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->orderByDesc('academic_yearID')
                    ->limit(1)
                    ->get();
            }

            if ($years->isEmpty()) {
                continue;
            }

            // Prefer O-level / primary current year when multiple currents exist
            $pick = $years->first(function ($y) {
                $label = strtolower((string) ($y->year_name ?? $y->year ?? ''));
                return !preg_match('/\ba[\s\-_]?level\b|\bform[\s\-_]?[56]\b/', $label);
            }) ?: $years->first();

            $yearId = (int) $pick->academic_yearID;
            $calYear = is_numeric($pick->year)
                ? (int) $pick->year
                : (int) preg_replace('/\D.*$/', '', (string) ($pick->year ?? date('Y')));

            DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->whereNull('academic_yearID')
                ->update([
                    'academic_yearID' => $yearId,
                    'year' => $calYear ?: (int) date('Y'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('student_move_batches', function (Blueprint $table) {
            if (Schema::hasColumn('student_move_batches', 'examID')) {
                $table->dropForeign(['examID']);
                $table->dropIndex('smb_exam_status_idx');
                $table->dropColumn('examID');
            }
        });

        Schema::table('examinations', function (Blueprint $table) {
            if (Schema::hasColumn('examinations', 'academic_yearID')) {
                $table->dropForeign(['academic_yearID']);
                $table->dropIndex('exam_school_year_idx');
                $table->dropColumn('academic_yearID');
            }
        });
    }
};
