<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('holidays') && !Schema::hasColumn('holidays', 'academic_yearID')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->unsignedBigInteger('academic_yearID')->nullable()->after('schoolID');
                $table->index(['schoolID', 'academic_yearID'], 'holidays_school_year_idx');
            });
        }

        if (Schema::hasTable('events') && !Schema::hasColumn('events', 'academic_yearID')) {
            Schema::table('events', function (Blueprint $table) {
                $table->unsignedBigInteger('academic_yearID')->nullable()->after('schoolID');
                $table->index(['schoolID', 'academic_yearID'], 'events_school_year_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('holidays') && Schema::hasColumn('holidays', 'academic_yearID')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropIndex('holidays_school_year_idx');
                $table->dropColumn('academic_yearID');
            });
        }

        if (Schema::hasTable('events') && Schema::hasColumn('events', 'academic_yearID')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropIndex('events_school_year_idx');
                $table->dropColumn('academic_yearID');
            });
        }
    }
};
