<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id('bankID');
            $table->string('bank_name', 150);
            $table->string('bank_code', 50)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->unique('bank_name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
