<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_subject_component_teachers', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('class_subjectID');
            $table->unsignedBigInteger('componentID');
            $table->unsignedBigInteger('teacherID')->nullable();
            $table->timestamps();

            $table->foreign('class_subjectID', 'csct_class_subject_fk')
                ->references('class_subjectID')
                ->on('class_subjects')
                ->onDelete('cascade');

            $table->foreign('componentID', 'csct_component_fk')
                ->references('componentID')
                ->on('school_subject_components')
                ->onDelete('cascade');

            $table->foreign('teacherID', 'csct_teacher_fk')
                ->references('id')
                ->on('teachers')
                ->onDelete('set null');

            $table->unique(['class_subjectID', 'componentID'], 'csct_class_subject_component_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subject_component_teachers');
    }
};
