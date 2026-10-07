<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_portal_books') && !Schema::hasColumn('student_portal_books', 'external_url')) {
            Schema::table('student_portal_books', function (Blueprint $table) {
                $table->string('external_url', 500)->nullable()->after('original_name');
            });
        }

        if (Schema::hasTable('student_portal_notes') && !Schema::hasColumn('student_portal_notes', 'external_url')) {
            Schema::table('student_portal_notes', function (Blueprint $table) {
                $table->string('external_url', 500)->nullable()->after('original_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_portal_books', 'external_url')) {
            Schema::table('student_portal_books', function (Blueprint $table) {
                $table->dropColumn('external_url');
            });
        }
        if (Schema::hasColumn('student_portal_notes', 'external_url')) {
            Schema::table('student_portal_notes', function (Blueprint $table) {
                $table->dropColumn('external_url');
            });
        }
    }
};
