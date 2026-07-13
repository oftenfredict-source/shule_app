<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE student_move_batch_items MODIFY COLUMN action ENUM('Promoted', 'Graduated', 'Internal', 'Reverted', 'Moved') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE student_move_batch_items MODIFY COLUMN action ENUM('Promoted', 'Graduated') NOT NULL");
    }
};
