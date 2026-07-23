<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Main subjects for A-Level / custom combinations (max 3 per combie).
     */
    public function up(): void
    {
        if (!Schema::hasTable('combie_subjects')) {
            Schema::create('combie_subjects', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('combieID');
                $table->unsignedBigInteger('subjectID');
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('combieID')
                    ->references('combieID')
                    ->on('combies')
                    ->onDelete('cascade');

                $table->foreign('subjectID')
                    ->references('subjectID')
                    ->on('school_subjects')
                    ->onDelete('cascade');

                $table->unique(['combieID', 'subjectID']);
                $table->index(['combieID', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('combie_subjects');
    }
};
