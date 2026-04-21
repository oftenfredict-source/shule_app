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
        Schema::table('book_losses', function (Blueprint $table) {
            $table->unsignedBigInteger('teacherID')->nullable()->after('studentID');
            $table->unsignedBigInteger('staffID')->nullable()->after('teacherID');
        });

        Schema::table('book_damages', function (Blueprint $table) {
            $table->unsignedBigInteger('teacherID')->nullable()->after('studentID');
            $table->unsignedBigInteger('staffID')->nullable()->after('teacherID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_losses', function (Blueprint $table) {
            $table->dropColumn(['teacherID', 'staffID']);
        });

        Schema::table('book_damages', function (Blueprint $table) {
            $table->dropColumn(['teacherID', 'staffID']);
        });
    }
};
