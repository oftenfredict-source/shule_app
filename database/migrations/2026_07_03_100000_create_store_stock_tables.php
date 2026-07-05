<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_item_stock_entries', function (Blueprint $table) {
            $table->bigIncrements('stock_entryID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('itemID');
            $table->enum('store_method', ['package', 'unit']);
            $table->unsignedBigInteger('packageID')->nullable();
            $table->unsignedBigInteger('unitID');
            $table->decimal('content_per_unit', 15, 4)->nullable();
            $table->unsignedInteger('quantity');
            $table->enum('requisition_status', ['can_return', 'cant_return']);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('store_stock_units', function (Blueprint $table) {
            $table->bigIncrements('stock_unitID');
            $table->unsignedBigInteger('stock_entryID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('itemID');
            $table->enum('store_method', ['package', 'unit']);
            $table->unsignedBigInteger('packageID')->nullable();
            $table->unsignedBigInteger('unitID');
            $table->string('label');
            $table->enum('requisition_status', ['can_return', 'cant_return']);
            $table->string('stock_status')->default('in_stock');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_stock_units');
        Schema::dropIfExists('store_item_stock_entries');
    }
};
