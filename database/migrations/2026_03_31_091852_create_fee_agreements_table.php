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
        Schema::create('fee_agreements', function (Blueprint $table) {
            $table->id('agreementID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('studentID');
            $table->unsignedBigInteger('feeID');
            $table->unsignedBigInteger('academic_yearID');
            $table->decimal('agreed_amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();

            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('feeID')->references('feeID')->on('fees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_agreements');
    }
};
