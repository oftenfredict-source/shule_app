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
        Schema::create('student_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('requester_id');
            $table->string('requester_type'); // 'teacher' or 'staff'
            $table->string('time_mode'); // 'days' or 'hours'
            $table->integer('days_count')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('start_date_hours')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('reason_type');
            $table->text('reason_description');
            $table->string('attachment')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->string('hod_status')->default('pending');
            $table->string('principal_status')->default('pending');
            $table->string('final_status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_permissions');
    }
};
