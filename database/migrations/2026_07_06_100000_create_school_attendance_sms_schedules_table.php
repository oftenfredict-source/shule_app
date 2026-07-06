<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_attendance_sms_schedules')) {
            Schema::create('school_attendance_sms_schedules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schoolID')->unique();
                $table->enum('schedule', ['none', 'daily', 'weekly', 'monthly', 'yearly'])->default('none');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index('schedule');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_attendance_sms_schedules');
    }
};
