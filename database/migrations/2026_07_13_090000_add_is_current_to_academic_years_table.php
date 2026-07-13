<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_years', 'is_current')) {
                $table->boolean('is_current')->default(false)->after('status');
            }
        });

        // Allow O-Level and A-Level years in the same school without colliding on integer year
        try {
            Schema::table('academic_years', function (Blueprint $table) {
                $table->dropUnique('unique_school_year');
            });
        } catch (\Throwable $e) {
            // Index name may differ across environments
        }

        Schema::table('academic_years', function (Blueprint $table) {
            $table->unique(['schoolID', 'year_name'], 'unique_school_year_name');
            $table->index(['schoolID', 'is_current'], 'academic_years_school_current_idx');
        });

        // Mark existing Active years as current (one per school)
        $schools = DB::table('academic_years')
            ->select('schoolID')
            ->distinct()
            ->pluck('schoolID');

        foreach ($schools as $schoolID) {
            $active = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderByDesc('year')
                ->first();

            if ($active) {
                DB::table('academic_years')
                    ->where('schoolID', $schoolID)
                    ->update(['is_current' => false]);

                DB::table('academic_years')
                    ->where('academic_yearID', $active->academic_yearID)
                    ->update(['is_current' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            try {
                $table->dropUnique('unique_school_year_name');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('academic_years_school_current_idx');
            } catch (\Throwable $e) {
            }
        });

        Schema::table('academic_years', function (Blueprint $table) {
            $table->unique(['schoolID', 'year'], 'unique_school_year');
            if (Schema::hasColumn('academic_years', 'is_current')) {
                $table->dropColumn('is_current');
            }
        });
    }
};
