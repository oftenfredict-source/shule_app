<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subject_notes')) {
            Schema::create('subject_notes', function (Blueprint $table) {
                $table->id('noteID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('academic_yearID')->nullable();
                $table->unsignedBigInteger('subjectID')->nullable();
                $table->unsignedBigInteger('class_subjectID');
                $table->unsignedBigInteger('teacherID');
                $table->string('level', 20)->nullable();
                $table->string('title', 255);
                $table->string('file_path', 500);
                $table->string('original_filename', 255);
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'class_subjectID']);
                $table->index(['schoolID', 'academic_yearID']);
                $table->index('teacherID');
            });
        }

        if (!Schema::hasTable('parent_subject_note_notifications')) {
            Schema::create('parent_subject_note_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('parentID');
                $table->unsignedBigInteger('studentID')->nullable();
                $table->unsignedBigInteger('noteID');
                $table->boolean('is_viewed')->default(false);
                $table->timestamps();

                $table->unique(['parentID', 'studentID', 'noteID'], 'psnn_parent_student_note_unique');
                $table->index(['parentID', 'is_viewed']);
                $table->index('schoolID');
                $table->index('noteID');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_subject_note_notifications');
        Schema::dropIfExists('subject_notes');
    }
};
