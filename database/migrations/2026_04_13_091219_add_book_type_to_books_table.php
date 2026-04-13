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
        Schema::table('books', function (Blueprint $table) {
            $table->enum('book_type', ['Class', 'Other'])->default('Class')->after('subjectID');
            $table->unsignedBigInteger('classID')->nullable()->change();
            $table->unsignedBigInteger('subjectID')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('book_type');
            $table->unsignedBigInteger('classID')->nullable(false)->change();
            $table->unsignedBigInteger('subjectID')->nullable(false)->change();
        });
    }
};
