<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('character_assessment_timetables', function (Blueprint $table) {
            $table->dropForeign(['academic_yearID']);
            $table->dropColumn('academic_yearID');
            $table->integer('year')->after('schoolID');
        });

        Schema::table('student_character_assessments', function (Blueprint $table) {
            $table->dropForeign(['academic_yearID']);
            $table->dropColumn('academic_yearID');
            $table->integer('year')->after('schoolID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_assessments', function (Blueprint $table) {
            //
        });
    }
};
