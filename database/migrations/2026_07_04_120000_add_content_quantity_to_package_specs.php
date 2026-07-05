<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_item_package_specs', function (Blueprint $table) {
            $table->decimal('content_quantity', 15, 4)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('store_item_package_specs', function (Blueprint $table) {
            $table->dropColumn('content_quantity');
        });
    }
};
