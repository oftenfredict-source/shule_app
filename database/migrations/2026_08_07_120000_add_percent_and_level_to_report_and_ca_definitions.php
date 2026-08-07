<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('term_report_definitions', function (Blueprint $table) {
            $table->boolean('use_percent_division')->default(false)->after('exam_ids');
            $table->json('exam_weights')->nullable()->after('use_percent_division');
            $table->string('level_scope', 20)->default('all')->after('exam_weights');
        });

        // Allow separate A-Level / O-Level / All definitions for same year+term
        try {
            Schema::table('term_report_definitions', function (Blueprint $table) {
                $table->dropUnique('school_year_term_unique');
            });
        } catch (\Throwable $e) {
            // Index name may differ on some DBs
        }

        Schema::table('term_report_definitions', function (Blueprint $table) {
            $table->unique(
                ['schoolID', 'year', 'term', 'level_scope'],
                'school_year_term_level_unique',
            );
        });

        // Backfill existing rows
        DB::table('term_report_definitions')
            ->whereNull('level_scope')
            ->orWhere('level_scope', '')
            ->update(['level_scope' => 'all']);

        Schema::table('ca_definitions', function (Blueprint $table) {
            $table->boolean('use_percent_division')->default(false)->after('test_ids');
            $table->json('exam_weights')->nullable()->after('use_percent_division');
            $table->string('level_scope', 20)->default('all')->after('exam_weights');
        });

        DB::table('ca_definitions')
            ->whereNull('level_scope')
            ->orWhere('level_scope', '')
            ->update(['level_scope' => 'all']);
    }

    public function down(): void
    {
        try {
            Schema::table('term_report_definitions', function (Blueprint $table) {
                $table->dropUnique('school_year_term_level_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('term_report_definitions', function (Blueprint $table) {
            $table->dropColumn(['use_percent_division', 'exam_weights', 'level_scope']);
            $table->unique(['schoolID', 'year', 'term'], 'school_year_term_unique');
        });

        Schema::table('ca_definitions', function (Blueprint $table) {
            $table->dropColumn(['use_percent_division', 'exam_weights', 'level_scope']);
        });
    }
};
