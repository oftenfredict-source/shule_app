<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admission_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID');
            $table->string('field_key', 80);
            $table->string('label', 200);
            $table->string('field_type', 30);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['schoolID', 'field_key']);
            $table->index(['schoolID', 'is_active', 'sort_order']);
        });

        Schema::create('online_admission_spaces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('classID');
            $table->unsignedBigInteger('academic_yearID');
            $table->unsignedInteger('spaces')->default(0);
            $table->timestamps();

            $table->unique(['schoolID', 'classID', 'academic_yearID'], 'oa_spaces_school_class_year_unique');
            $table->index(['schoolID', 'academic_yearID']);
        });

        Schema::create('online_admissions', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 80)->unique();
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('classID');
            $table->unsignedBigInteger('academic_yearID');
            $table->string('status', 20)->default('Pending');
            $table->string('student_first_name', 100);
            $table->string('student_middle_name', 100)->nullable();
            $table->string('student_last_name', 100);
            $table->string('student_gender', 10);
            $table->string('student_photo', 255)->nullable();
            $table->string('parent_phone', 20);
            $table->string('parent_first_name', 100)->nullable();
            $table->string('parent_middle_name', 100)->nullable();
            $table->string('parent_last_name', 100)->nullable();
            $table->string('parent_gender', 10)->nullable();
            $table->string('parent_photo', 255)->nullable();
            $table->unsignedBigInteger('parentID')->nullable();
            $table->unsignedBigInteger('studentID')->nullable();
            $table->json('custom_responses')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['schoolID', 'status', 'created_at']);
            $table->index(['schoolID', 'classID', 'academic_yearID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admissions');
        Schema::dropIfExists('online_admission_spaces');
        Schema::dropIfExists('online_admission_form_fields');
    }
};
