<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_installments', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_yearID')->nullable()->after('feeID');
            $table->foreign('academic_yearID')
                ->references('academic_yearID')
                ->on('academic_years')
                ->onDelete('cascade');
            $table->index(['feeID', 'academic_yearID'], 'fi_fee_year_idx');
        });
    }

    public function down(): void
    {
        Schema::table('fee_installments', function (Blueprint $table) {
            $table->dropForeign(['academic_yearID']);
            $table->dropIndex('fi_fee_year_idx');
            $table->dropColumn('academic_yearID');
        });
    }
};
