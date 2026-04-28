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
        Schema::create('published_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('classID');
            $table->string('term');
            $table->string('year');
            $table->string('status')->default('disabled'); // enabled/disabled
            $table->timestamps();

            $table->index(['schoolID', 'classID', 'term', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('published_reports');
    }
};
