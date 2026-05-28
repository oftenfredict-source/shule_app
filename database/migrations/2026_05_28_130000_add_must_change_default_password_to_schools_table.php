<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('schools', 'must_change_default_password')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('must_change_default_password')
                    ->default(false)
                    ->after('two_factor_enabled');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('schools', 'must_change_default_password')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('must_change_default_password');
            });
        }
    }
};
