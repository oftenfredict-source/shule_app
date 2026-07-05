<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_item_package_specs', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_specID')->nullable()->after('itemID');
            $table->boolean('has_sub_package')->default(false)->after('packageID');
        });

        DB::statement('ALTER TABLE store_item_package_specs MODIFY unitID BIGINT UNSIGNED NULL');

        Schema::table('store_item_package_specs', function (Blueprint $table) {
            $table->dropUnique(['itemID', 'packageID']);
        });
    }

    public function down(): void
    {
        Schema::table('store_item_package_specs', function (Blueprint $table) {
            $table->dropColumn(['parent_specID', 'has_sub_package']);
        });

        DB::statement('ALTER TABLE store_item_package_specs MODIFY unitID BIGINT UNSIGNED NOT NULL');

        Schema::table('store_item_package_specs', function (Blueprint $table) {
            $table->unique(['itemID', 'packageID']);
        });
    }
};
