<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('scheme_of_work_items')) {
            return;
        }

        // UI accepts labels like "Week 1" / "1 week", not only integers.
        DB::statement('ALTER TABLE `scheme_of_work_items` MODIFY `week` VARCHAR(50) NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('scheme_of_work_items')) {
            return;
        }

        // Best-effort rollback: keep leading digits where possible.
        DB::statement("UPDATE `scheme_of_work_items` SET `week` = NULL WHERE `week` IS NOT NULL AND `week` NOT REGEXP '^[0-9]+$'");
        DB::statement('ALTER TABLE `scheme_of_work_items` MODIFY `week` INT NULL');
    }
};
