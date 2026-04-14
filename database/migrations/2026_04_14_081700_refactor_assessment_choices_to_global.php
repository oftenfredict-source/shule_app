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
        Schema::table('character_assessment_choices', function (Blueprint $table) {
            $table->dropForeign(['categoryID']);
            $table->dropColumn('categoryID');
            $table->unsignedBigInteger('schoolID')->after('choiceID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_assessment_choices', function (Blueprint $table) {
            $table->dropColumn('schoolID');
            $table->unsignedBigInteger('categoryID')->after('choiceID');
            $table->foreign('categoryID')->references('categoryID')->on('character_assessment_categories')->onDelete('cascade');
        });
    }
};
