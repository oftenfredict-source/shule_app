<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_fingerprint_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staffID')->comment('Other staff ID');
            $table->string('user_id', 50)->nullable();
            $table->string('user_name', 255)->nullable();
            $table->string('enroll_id', 50)->nullable();
            $table->date('attendance_date');
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('verify_mode', 50)->nullable();
            $table->string('device_ip', 50)->nullable();
            $table->bigInteger('external_id')->nullable();
            $table->timestamps();

            $table->unique(['staffID', 'attendance_date'], 'unique_staff_date');
            $table->index('staffID');
            $table->index('attendance_date');
            $table->index(['staffID', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_fingerprint_attendance');
    }
};
