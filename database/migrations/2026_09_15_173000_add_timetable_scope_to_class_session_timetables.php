<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_session_timetables', function (Blueprint $table) {
            if (!Schema::hasColumn('class_session_timetables', 'timetable_scope')) {
                $table->string('timetable_scope', 20)->default('subclass')->after('notes')
                    ->comment('subclass = one stream; main_class = whole class timetable');
                $table->index('timetable_scope');
            }
        });
    }

    public function down(): void
    {
        Schema::table('class_session_timetables', function (Blueprint $table) {
            if (Schema::hasColumn('class_session_timetables', 'timetable_scope')) {
                $table->dropIndex(['timetable_scope']);
                $table->dropColumn('timetable_scope');
            }
        });
    }
};
