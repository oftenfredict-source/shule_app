<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_stock_units', function (Blueprint $table) {
            $table->decimal('initial_items', 15, 4)->default(1)->after('stock_status');
            $table->decimal('remaining_items', 15, 4)->default(1)->after('initial_items');
            $table->unsignedBigInteger('package_specID')->nullable()->after('packageID');
        });

        // Backfill from stock entries for package units.
        DB::statement('
            UPDATE store_stock_units u
            INNER JOIN store_item_stock_entries e ON e.stock_entryID = u.stock_entryID
            SET u.initial_items = COALESCE(e.content_per_unit, 1),
                u.remaining_items = CASE
                    WHEN u.stock_status = \'in_stock\' THEN COALESCE(e.content_per_unit, 1)
                    ELSE 0
                END
            WHERE u.store_method = \'package\'
        ');

        DB::statement("
            UPDATE store_stock_units
            SET initial_items = 1,
                remaining_items = CASE WHEN stock_status = 'in_stock' THEN 1 ELSE 0 END
            WHERE store_method = 'unit'
        ");

        Schema::table('store_requisitions', function (Blueprint $table) {
            $table->string('take_level')->default('full')->after('package_unit_display');
            $table->unsignedBigInteger('take_packageID')->nullable()->after('take_level');
            $table->string('take_display')->nullable()->after('take_packageID');
            $table->decimal('quantity_taken', 15, 4)->default(1)->after('take_display');
            $table->decimal('quantity_returned', 15, 4)->nullable()->after('quantity_taken');
        });
    }

    public function down(): void
    {
        Schema::table('store_requisitions', function (Blueprint $table) {
            $table->dropColumn([
                'take_level',
                'take_packageID',
                'take_display',
                'quantity_taken',
                'quantity_returned',
            ]);
        });

        Schema::table('store_stock_units', function (Blueprint $table) {
            $table->dropColumn(['initial_items', 'remaining_items', 'package_specID']);
        });
    }
};
