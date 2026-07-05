<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_item_units', function (Blueprint $table) {
            $table->bigIncrements('item_unitID');
            $table->unsignedBigInteger('itemID');
            $table->unsignedBigInteger('unitID');
            $table->timestamps();
            $table->unique(['itemID', 'unitID']);
        });

        if (Schema::hasTable('store_items') && Schema::hasColumn('store_items', 'unitID')) {
            $items = DB::table('store_items')
                ->whereNotNull('unitID')
                ->where('unitID', '>', 0)
                ->get(['itemID', 'unitID']);

            foreach ($items as $item) {
                DB::table('store_item_units')->insert([
                    'itemID' => $item->itemID,
                    'unitID' => $item->unitID,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_item_units');
    }
};
