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
        Schema::create('student_character_assessments', function (Blueprint $table) {
            $table->id('assessmentID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('studentID');
            $table->unsignedBigInteger('academic_yearID');
            $table->unsignedBigInteger('timetableID');
            $table->unsignedBigInteger('categoryID');
            $table->unsignedBigInteger('choiceID')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('assessed_by'); // teacherID
            $table->timestamps();

            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('academic_yearID')->references('academic_yearID')->on('academic_years')->onDelete('cascade');
            $table->foreign('timetableID')->references('timetableID')->on('character_assessment_timetables')->onDelete('cascade');
            $table->foreign('categoryID')->references('categoryID')->on('character_assessment_categories')->onDelete('cascade');
            $table->foreign('choiceID')->references('choiceID')->on('character_assessment_choices')->onDelete('cascade');
            $table->foreign('assessed_by')->references('id')->on('teachers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_character_assessments');
    }
};
