<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

        return count($rows) > 0;
    }

    public function up(): void
    {
        if (! $this->indexExists('black_book_reports', 'bb_reports_school_status_idx')) {
            Schema::table('black_book_reports', function (Blueprint $table) {
                $table->index(['schoolID', 'status'], 'bb_reports_school_status_idx');
            });
        }

        if (! $this->indexExists('black_book_reports', 'bb_reports_school_created_idx')) {
            Schema::table('black_book_reports', function (Blueprint $table) {
                $table->index(['schoolID', 'created_at'], 'bb_reports_school_created_idx');
            });
        }

        // status is VARCHAR(255) — full column exceeds MySQL index length on utf8mb4
        if (! $this->indexExists('black_book_problem_categories', 'bb_problem_cats_school_status_idx')) {
            DB::statement('CREATE INDEX bb_problem_cats_school_status_idx ON black_book_problem_categories (schoolID, status(20))');
        }

        if (! $this->indexExists('black_book_punishment_categories', 'bb_punish_cats_school_status_idx')) {
            DB::statement('CREATE INDEX bb_punish_cats_school_status_idx ON black_book_punishment_categories (schoolID, status(20))');
        }
    }

    public function down(): void
    {
        if ($this->indexExists('black_book_reports', 'bb_reports_school_status_idx')) {
            Schema::table('black_book_reports', function (Blueprint $table) {
                $table->dropIndex('bb_reports_school_status_idx');
            });
        }

        if ($this->indexExists('black_book_reports', 'bb_reports_school_created_idx')) {
            Schema::table('black_book_reports', function (Blueprint $table) {
                $table->dropIndex('bb_reports_school_created_idx');
            });
        }

        if ($this->indexExists('black_book_problem_categories', 'bb_problem_cats_school_status_idx')) {
            DB::statement('DROP INDEX bb_problem_cats_school_status_idx ON black_book_problem_categories');
        }

        if ($this->indexExists('black_book_punishment_categories', 'bb_punish_cats_school_status_idx')) {
            DB::statement('DROP INDEX bb_punish_cats_school_status_idx ON black_book_punishment_categories');
        }
    }
};
