<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_deadlines', function (Blueprint $table) {
            $table->id('deadlineID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('academic_yearID');
            $table->unsignedBigInteger('feeID');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('academic_yearID')->references('academic_yearID')->on('academic_years')->onDelete('cascade');
            $table->foreign('feeID')->references('feeID')->on('fees')->onDelete('cascade');
            $table->index(['schoolID', 'academic_yearID'], 'fd_school_year_idx');
            $table->index(['feeID', 'academic_yearID', 'status'], 'fd_fee_year_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_deadlines');
    }
};
