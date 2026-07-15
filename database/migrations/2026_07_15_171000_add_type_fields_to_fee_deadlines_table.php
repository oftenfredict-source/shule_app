<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_deadlines', function (Blueprint $table) {
            $table->enum('deadline_type', ['date', 'month'])->default('date')->after('feeID');
            $table->unsignedTinyInteger('start_month')->nullable()->after('end_date');
            $table->unsignedTinyInteger('end_month')->nullable()->after('start_month');
        });
    }

    public function down(): void
    {
        Schema::table('fee_deadlines', function (Blueprint $table) {
            $table->dropColumn(['deadline_type', 'start_month', 'end_month']);
        });
    }
};
