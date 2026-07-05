<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_item_categories', function (Blueprint $table) {
            $table->bigIncrements('categoryID');
            $table->unsignedBigInteger('schoolID');
            $table->string('category_name');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('store_measurement_types', function (Blueprint $table) {
            $table->bigIncrements('measurement_typeID');
            $table->unsignedBigInteger('schoolID');
            $table->string('type_name');
            $table->boolean('is_system')->default(false);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('store_units_of_measure', function (Blueprint $table) {
            $table->bigIncrements('unitID');
            $table->unsignedBigInteger('measurement_typeID');
            $table->string('unit_name');
            $table->string('abbreviation')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('store_items', function (Blueprint $table) {
            $table->bigIncrements('itemID');
            $table->unsignedBigInteger('schoolID');
            $table->string('item_name');
            $table->unsignedBigInteger('categoryID');
            $table->unsignedBigInteger('measurement_typeID');
            $table->unsignedBigInteger('unitID');
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_items');
        Schema::dropIfExists('store_units_of_measure');
        Schema::dropIfExists('store_measurement_types');
        Schema::dropIfExists('store_item_categories');
    }
};
