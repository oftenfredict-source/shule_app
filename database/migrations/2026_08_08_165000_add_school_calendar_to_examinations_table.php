<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            if (!Schema::hasColumn('examinations', 'track_school_calendar')) {
                $table->boolean('track_school_calendar')->default(false)->after('allow_no_paper');
            }
            if (!Schema::hasColumn('examinations', 'school_closing_date')) {
                $table->date('school_closing_date')->nullable()->after('track_school_calendar');
            }
            if (!Schema::hasColumn('examinations', 'school_opening_date')) {
                $table->date('school_opening_date')->nullable()->after('school_closing_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            foreach (['school_opening_date', 'school_closing_date', 'track_school_calendar'] as $col) {
                if (Schema::hasColumn('examinations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
