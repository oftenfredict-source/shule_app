<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_staff_work_time_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID')->unique();
            $table->time('work_start_time')->default('08:00:00');
            $table->time('work_end_time')->default('15:00:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_staff_work_time_settings');
    }
};
