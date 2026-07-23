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
            if (!Schema::hasColumn('examinations', 'academic_year_ids')) {
                $table->json('academic_year_ids')->nullable()->after('academic_yearID');
            }
        });

        // Backfill from existing academic_yearID
        if (Schema::hasColumn('examinations', 'academic_year_ids')) {
            DB::table('examinations')
                ->whereNotNull('academic_yearID')
                ->whereNull('academic_year_ids')
                ->orderBy('examID')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('examinations')
                            ->where('examID', $row->examID)
                            ->update([
                                'academic_year_ids' => json_encode([(int) $row->academic_yearID]),
                            ]);
                    }
                }, 'examID');
        }
    }

    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            if (Schema::hasColumn('examinations', 'academic_year_ids')) {
                $table->dropColumn('academic_year_ids');
            }
        });
    }
};
