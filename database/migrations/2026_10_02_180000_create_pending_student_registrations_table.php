<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pending_student_registrations')) {
            Schema::create('pending_student_registrations', function (Blueprint $table) {
                $table->bigIncrements('pendingID');
                $table->unsignedBigInteger('schoolID');
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->string('last_name');
                $table->string('gender', 20)->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('previous_level', 50)->nullable(); // Std 7, Form 4, Other
                $table->string('previous_school')->nullable();
                $table->unsignedBigInteger('intended_classID')->nullable();
                $table->unsignedBigInteger('intended_subclassID')->nullable();
                $table->unsignedBigInteger('intended_academic_yearID')->nullable();
                $table->string('parent_first_name')->nullable();
                $table->string('parent_middle_name')->nullable();
                $table->string('parent_last_name')->nullable();
                $table->string('parent_gender', 20)->nullable();
                $table->string('parent_phone', 40)->nullable();
                $table->string('parent_email')->nullable();
                $table->string('parent_relationship', 50)->nullable();
                $table->string('phone', 40)->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('Pending'); // Pending, Confirmed, Cancelled
                $table->unsignedBigInteger('confirmed_studentID')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('created_by_name')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'status']);
                $table->index(['schoolID', 'intended_classID']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_student_registrations');
    }
};
