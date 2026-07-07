<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_work_time_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID')->unique();
            $table->time('work_start_time')->default('07:30:00');
            $table->time('work_end_time')->default('14:30:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_work_time_settings');
    }
};
