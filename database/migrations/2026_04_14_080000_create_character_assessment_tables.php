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
        Schema::create('character_assessment_categories', function (Blueprint $table) {
            $table->id('categoryID');
            $table->unsignedBigInteger('schoolID');
            $table->string('name');
            $table->enum('type', ['text', 'choice'])->default('choice');
            $table->timestamps();
        });

        Schema::create('character_assessment_choices', function (Blueprint $table) {
            $table->id('choiceID');
            $table->unsignedBigInteger('categoryID');
            $table->string('choice_name');
            $table->integer('weight')->default(1);
            $table->timestamps();

            $table->foreign('categoryID')->references('categoryID')->on('character_assessment_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_assessment_choices');
        Schema::dropIfExists('character_assessment_categories');
    }
};
