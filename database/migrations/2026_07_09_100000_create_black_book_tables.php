<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('black_book_problem_categories', function (Blueprint $table) {
            $table->bigIncrements('categoryID');
            $table->unsignedBigInteger('schoolID');
            $table->string('name');
            $table->unsignedTinyInteger('weight')->default(50);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('black_book_punishment_categories', function (Blueprint $table) {
            $table->bigIncrements('punishmentCategoryID');
            $table->unsignedBigInteger('schoolID');
            $table->string('name');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('black_book_reports', function (Blueprint $table) {
            $table->bigIncrements('reportID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('studentID');
            $table->unsignedBigInteger('problem_categoryID');
            $table->text('description');
            $table->string('reported_by_type')->nullable();
            $table->unsignedBigInteger('reported_by_id')->nullable();
            $table->string('reported_by_name');
            $table->enum('status', ['pending', 'viewed', 'resolved'])->default('pending');
            $table->timestamp('viewed_at')->nullable();
            $table->string('viewed_by_name')->nullable();
            $table->enum('resolve_method', ['parent_call', 'punishment'])->nullable();
            $table->unsignedBigInteger('punishment_categoryID')->nullable();
            $table->text('punishment_description')->nullable();
            $table->text('parent_sms_message')->nullable();
            $table->boolean('parent_sms_sent')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('black_book_reports');
        Schema::dropIfExists('black_book_punishment_categories');
        Schema::dropIfExists('black_book_problem_categories');
    }
};
