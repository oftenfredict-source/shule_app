<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_deadlines', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_deadlines', 'amount_type')) {
                $table->enum('amount_type', ['fixed', 'percent'])->default('fixed')->after('end_month');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_deadlines', function (Blueprint $table) {
            if (Schema::hasColumn('fee_deadlines', 'amount_type')) {
                $table->dropColumn('amount_type');
            }
        });
    }
};
