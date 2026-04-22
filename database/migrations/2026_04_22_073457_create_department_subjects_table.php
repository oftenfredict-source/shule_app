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
        Schema::create('department_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departmentID')->constrained('departments', 'departmentID')->onDelete('cascade');
            $table->foreignId('subjectID')->constrained('school_subjects', 'subjectID')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_subjects');
    }
};
