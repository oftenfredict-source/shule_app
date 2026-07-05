<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_package_categories', function (Blueprint $table) {
            $table->bigIncrements('package_categoryID');
            $table->unsignedBigInteger('schoolID');
            $table->string('category_name');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('store_packages', function (Blueprint $table) {
            $table->bigIncrements('packageID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('package_categoryID');
            $table->string('package_name');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::table('store_items', function (Blueprint $table) {
            $table->boolean('has_packages')->default(false)->after('unitID');
        });

        Schema::create('store_item_package_specs', function (Blueprint $table) {
            $table->bigIncrements('specID');
            $table->unsignedBigInteger('itemID');
            $table->unsignedBigInteger('packageID');
            $table->unsignedBigInteger('unitID');
            $table->decimal('quantity', 15, 4);
            $table->timestamps();
            $table->unique(['itemID', 'packageID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_item_package_specs');
        Schema::table('store_items', function (Blueprint $table) {
            $table->dropColumn('has_packages');
        });
        Schema::dropIfExists('store_packages');
        Schema::dropIfExists('store_package_categories');
    }
};
