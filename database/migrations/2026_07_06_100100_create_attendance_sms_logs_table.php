<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendance_sms_logs')) {
            Schema::create('attendance_sms_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('studentID');
                $table->string('schedule_type', 20);
                $table->string('period_key', 20);
                $table->string('phone', 30)->nullable();
                $table->enum('status', ['sent', 'failed'])->default('sent');
                $table->text('message')->nullable();
                $table->unsignedBigInteger('sent_by')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['schoolID', 'studentID', 'schedule_type', 'period_key'],
                    'attendance_sms_logs_unique_period'
                );
                $table->index(['schoolID', 'schedule_type', 'period_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sms_logs');
    }
};
