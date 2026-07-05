<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_requisitions') || !Schema::hasColumn('store_requisitions', 'return_condition')) {
            return;
        }

        DB::table('store_requisitions')
            ->where('return_condition', 'bad')
            ->update(['return_condition' => 'damaged']);

        DB::statement("ALTER TABLE store_requisitions MODIFY COLUMN return_condition ENUM('good', 'damaged', 'lost') NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('store_requisitions') || !Schema::hasColumn('store_requisitions', 'return_condition')) {
            return;
        }

        DB::table('store_requisitions')
            ->where('return_condition', 'lost')
            ->update(['return_condition' => 'damaged']);

        DB::table('store_requisitions')
            ->where('return_condition', 'damaged')
            ->update(['return_condition' => 'bad']);

        DB::statement("ALTER TABLE store_requisitions MODIFY COLUMN return_condition ENUM('good', 'bad') NULL");
    }
};
