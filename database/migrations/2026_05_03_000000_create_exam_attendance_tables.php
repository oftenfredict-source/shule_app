<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exam_attendance_assignments', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('schoolID');
            $blueprint->unsignedBigInteger('examID');
            $blueprint->unsignedBigInteger('teacherID');
            $blueprint->unsignedBigInteger('classID');
            $blueprint->unsignedBigInteger('subclassID')->nullable();
            $blueprint->json('subject_ids');
            $blueprint->boolean('status')->default(true);
            $blueprint->unsignedBigInteger('created_by');
            $blueprint->timestamps();

            $blueprint->foreign('examID')->references('examID')->on('examinations')->onDelete('cascade');
        });

        Schema::create('exam_attendance_records', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('schoolID');
            $blueprint->unsignedBigInteger('examID');
            $blueprint->unsignedBigInteger('studentID');
            $blueprint->unsignedBigInteger('subjectID');
            $blueprint->enum('status', ['Present', 'Absent'])->default('Present');
            $blueprint->unsignedBigInteger('marked_by')->nullable();
            $blueprint->timestamps();

            $blueprint->unique(['examID', 'studentID', 'subjectID'], 'exam_student_subject_unique');
            $blueprint->foreign('examID')->references('examID')->on('examinations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_attendance_records');
        Schema::dropIfExists('exam_attendance_assignments');
    }
};
