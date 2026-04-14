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
        Schema::table('book_borrows', function (Blueprint $table) {
            $table->unsignedBigInteger('staffID')->nullable()->after('teacherID');
            $table->foreign('staffID')->references('id')->on('other_staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_borrows', function (Blueprint $table) {
            $table->dropForeign(['staffID']);
            $table->dropColumn('staffID');
        });
    }
};
