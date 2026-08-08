<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('term_report_definitions', function (Blueprint $table) {
            if (!Schema::hasColumn('term_report_definitions', 'school_closing_date')) {
                $table->date('school_closing_date')->nullable()->after('level_scope');
            }
            if (!Schema::hasColumn('term_report_definitions', 'school_opening_date')) {
                $table->date('school_opening_date')->nullable()->after('school_closing_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('term_report_definitions', function (Blueprint $table) {
            foreach (['school_opening_date', 'school_closing_date'] as $col) {
                if (Schema::hasColumn('term_report_definitions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
