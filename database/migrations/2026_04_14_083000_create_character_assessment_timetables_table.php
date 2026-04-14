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
        Schema::create('character_assessment_timetables', function (Blueprint $table) {
            $table->id('timetableID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('academic_yearID');
            $table->enum('term', ['first_term', 'second_term', 'all_terms']);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('target_classes')->nullable(); // JSON list of main class IDs
            $table->timestamps();

            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('academic_yearID')->references('academic_yearID')->on('academic_years')->onDelete('cascade');
        });

        Schema::create('character_assessment_assignments', function (Blueprint $table) {
            $table->id('assignmentID');
            $table->unsignedBigInteger('timetableID');
            $table->unsignedBigInteger('classID');
            $table->unsignedBigInteger('subclassID')->nullable();
            $table->unsignedBigInteger('teacherID');
            $table->timestamps();

            $table->foreign('timetableID')->references('timetableID')->on('character_assessment_timetables')->onDelete('cascade');
            $table->foreign('classID')->references('classID')->on('classes')->onDelete('cascade');
            $table->foreign('subclassID')->references('subclassID')->on('subclasses')->onDelete('cascade');
            $table->foreign('teacherID')->references('id')->on('teachers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_assessment_assignments');
        Schema::dropIfExists('character_assessment_timetables');
    }
};
