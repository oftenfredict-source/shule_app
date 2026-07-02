<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->boolean('has_working_days')->default(false)->after('type');
            $table->unsignedSmallInteger('working_days_count')->nullable()->after('has_working_days');
        });
    }

    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn(['has_working_days', 'working_days_count']);
        });
    }
};

