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
        Schema::create('student_fee_conditions', function (Blueprint $table) {
            $table->id('studentConditionID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('studentID');
            $table->unsignedBigInteger('conditionID');
            $table->unsignedBigInteger('feeID'); // Explicitly link to fee for easier lookup
            $table->unsignedBigInteger('academic_yearID')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
            
            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('conditionID')->references('conditionID')->on('fee_conditions')->onDelete('cascade');
            $table->foreign('feeID')->references('feeID')->on('fees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fee_conditions');
    }
};
