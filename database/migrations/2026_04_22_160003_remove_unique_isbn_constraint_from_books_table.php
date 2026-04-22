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
        try {
            DB::statement('ALTER TABLE books DROP INDEX books_school_isbn_unique');
        } catch (\Exception $e) {
            // Already dropped or doesn't exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unique(['schoolID', 'isbn'], 'books_school_isbn_unique');
        });
    }
};
