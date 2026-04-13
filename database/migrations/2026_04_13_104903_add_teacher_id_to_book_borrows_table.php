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
            $table->foreignId('teacherID')->nullable()->after('studentID')->constrained('teachers', 'id')->onDelete('cascade');
            $table->unsignedBigInteger('studentID')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_borrows', function (Blueprint $table) {
            $table->dropForeign(['teacherID']);
            $table->dropColumn('teacherID');
            $table->unsignedBigInteger('studentID')->nullable(false)->change();
        });
    }
};
