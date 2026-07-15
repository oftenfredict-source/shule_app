<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_breakdowns', function (Blueprint $table) {
            $table->id('breakdownID');
            $table->unsignedBigInteger('feeID');
            $table->string('item_name', 150);
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->foreign('feeID')->references('feeID')->on('fees')->onDelete('cascade');
            $table->index('feeID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_breakdowns');
    }
};
