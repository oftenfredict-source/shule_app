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
        Schema::create('parent_result_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('parentID');
            $table->unsignedBigInteger('studentID');
            $table->string('type'); // 'exam' or 'report'
            $table->unsignedBigInteger('examID')->nullable();
            $table->string('term')->nullable();
            $table->string('year')->nullable();
            $table->boolean('is_viewed')->default(false);
            $table->timestamps();

            $table->index(['parentID', 'is_viewed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_result_notifications');
    }
};
