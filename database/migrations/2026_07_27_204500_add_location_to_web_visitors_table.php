<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_visitors', function (Blueprint $table) {
            $table->string('country')->nullable()->after('user_agent');
            $table->string('region')->nullable()->after('country');
            $table->string('city')->nullable()->after('region');
            $table->decimal('latitude', 10, 7)->nullable()->after('city');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('web_visitors', function (Blueprint $table) {
            $table->dropColumn(['country', 'region', 'city', 'latitude', 'longitude']);
        });
    }
};
