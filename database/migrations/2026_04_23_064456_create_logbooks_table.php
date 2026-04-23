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
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id('logbookID');
            $table->unsignedBigInteger('class_subjectID');
            $table->string('main_topic')->nullable();
            $table->string('sub_topic')->nullable();
            $table->text('content_covered')->nullable();
            $table->date('date_started')->nullable();
            $table->date('date_ended')->nullable();
            $table->text('teacher_comment')->nullable();
            $table->text('hod_comment')->nullable();
            $table->text('hm_comment')->nullable();
            $table->unsignedBigInteger('schoolID');
            $table->integer('year');
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};
