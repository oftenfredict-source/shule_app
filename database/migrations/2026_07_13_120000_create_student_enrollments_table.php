<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id('enrollmentID');
            $table->unsignedBigInteger('studentID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('academic_yearID');
            $table->unsignedBigInteger('classID');
            $table->unsignedBigInteger('subclassID');
            $table->string('roll_number', 50)->nullable();
            $table->enum('status', ['Active', 'Completed', 'Transferred', 'Dropped', 'Cancelled'])->default('Active');
            $table->enum('promotion_status', ['Pending', 'Promoted', 'Repeated', 'Graduated', 'Transferred'])->default('Pending');
            $table->date('joined_date')->nullable();
            $table->date('left_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('academic_yearID')->references('academic_yearID')->on('academic_years')->onDelete('cascade');
            $table->foreign('classID')->references('classID')->on('classes')->onDelete('cascade');
            $table->foreign('subclassID')->references('subclassID')->on('subclasses')->onDelete('cascade');

            $table->index(['studentID', 'academic_yearID'], 'se_student_year_idx');
            $table->index(['schoolID', 'academic_yearID'], 'se_school_year_idx');
            $table->index(['classID', 'subclassID'], 'se_class_subclass_idx');
            $table->index('status', 'se_status_idx');
            $table->unique(['studentID', 'academic_yearID', 'subclassID'], 'unique_student_year_subclass');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
