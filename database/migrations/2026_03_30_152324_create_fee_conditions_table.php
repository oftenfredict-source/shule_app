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
        Schema::create('fee_conditions', function (Blueprint $table) {
            $table->id('conditionID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('feeID');
            $table->string('condition_name');
            $table->decimal('discount_amount', 15, 2);
            $table->enum('discount_type', ['Fixed', 'Percentage'])->default('Fixed');
            $table->text('description')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
            
            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('feeID')->references('feeID')->on('fees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_conditions');
    }
};
