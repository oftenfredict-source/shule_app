<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_fingerprint_attendance_sms_schedules')) {
            Schema::create('school_fingerprint_attendance_sms_schedules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schoolID')->unique();
                $table->enum('schedule', ['none', 'daily', 'weekly', 'monthly', 'yearly'])->default('none');
                $table->enum('send_method', ['manual', 'auto'])->default('manual');
                $table->time('send_time')->nullable();
                $table->unsignedTinyInteger('send_day_of_week')->nullable();
                $table->unsignedTinyInteger('send_day_of_month')->nullable();
                $table->unsignedTinyInteger('send_month')->nullable();
                $table->string('last_auto_period_key', 32)->nullable();
                $table->timestamp('last_auto_run_at')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index('schedule');
            });
        }

        if (! Schema::hasTable('fingerprint_attendance_sms_logs')) {
            Schema::create('fingerprint_attendance_sms_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('studentID');
                $table->string('schedule_type', 20);
                $table->string('period_key', 32);
                $table->string('phone', 30)->nullable();
                $table->string('status', 20)->default('sent');
                $table->text('message')->nullable();
                $table->unsignedBigInteger('sent_by')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->unique(['schoolID', 'studentID', 'schedule_type', 'period_key'], 'fp_sms_unique_period');
                $table->index(['schoolID', 'period_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerprint_attendance_sms_logs');
        Schema::dropIfExists('school_fingerprint_attendance_sms_schedules');
    }
};
