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
        Schema::create('teacher_result_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('teacherID');
            $table->unsignedBigInteger('classID');
            $table->unsignedBigInteger('subclassID')->nullable();
            $table->json('subject_ids'); // Storing multiple subjects as JSON
            $table->unsignedBigInteger('examID');
            $table->boolean('status')->default(true); // true = Enabled, false = Disabled
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('teacherID')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('classID')->references('classID')->on('classes')->onDelete('cascade');
            $table->foreign('subclassID')->references('subclassID')->on('subclasses')->onDelete('set null');
            $table->foreign('examID')->references('examID')->on('examinations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_result_assignments');
    }
};
