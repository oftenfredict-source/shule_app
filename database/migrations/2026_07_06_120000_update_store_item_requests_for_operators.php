<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_item_requests')) {
            return;
        }

        Schema::table('store_item_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('store_item_requests', 'is_read_by_operator')) {
                $table->boolean('is_read_by_operator')->default(false)->after('status');
            }
        });

        DB::table('store_item_requests')->where('status', 'pending')->update(['status' => 'requested']);

        DB::statement("ALTER TABLE store_item_requests MODIFY COLUMN status ENUM('requested','approved','rejected','cancelled') NOT NULL DEFAULT 'requested'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('store_item_requests')) {
            return;
        }

        DB::table('store_item_requests')->where('status', 'requested')->update(['status' => 'pending']);

        DB::statement("ALTER TABLE store_item_requests MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('store_item_requests', function (Blueprint $table) {
            if (Schema::hasColumn('store_item_requests', 'is_read_by_operator')) {
                $table->dropColumn('is_read_by_operator');
            }
        });
    }
};
