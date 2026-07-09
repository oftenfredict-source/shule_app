<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('black_book_reports', function (Blueprint $table) {
            $table->index(['schoolID', 'status'], 'bb_reports_school_status_idx');
            $table->index(['schoolID', 'created_at'], 'bb_reports_school_created_idx');
        });

        Schema::table('black_book_problem_categories', function (Blueprint $table) {
            $table->index(['schoolID', 'status'], 'bb_problem_cats_school_status_idx');
        });

        Schema::table('black_book_punishment_categories', function (Blueprint $table) {
            $table->index(['schoolID', 'status'], 'bb_punish_cats_school_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('black_book_reports', function (Blueprint $table) {
            $table->dropIndex('bb_reports_school_status_idx');
            $table->dropIndex('bb_reports_school_created_idx');
        });

        Schema::table('black_book_problem_categories', function (Blueprint $table) {
            $table->dropIndex('bb_problem_cats_school_status_idx');
        });

        Schema::table('black_book_punishment_categories', function (Blueprint $table) {
            $table->dropIndex('bb_punish_cats_school_status_idx');
        });
    }
};
