<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow students to be quick-registered before enrollment (no class yet).
     */
    public function up(): void
    {
        // Index exists but FK may be missing on some DBs - drop safely if present
        $fk = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'students'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME = 'students_subclassid_foreign'
        "))->first();

        if ($fk) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['subclassID']);
            });
        }

        DB::statement('ALTER TABLE `students` MODIFY `subclassID` BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `students` MODIFY `subclassID` BIGINT UNSIGNED NOT NULL');
    }
};
