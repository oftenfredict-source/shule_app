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
        Schema::table('paper_approval_logs', function (Blueprint $table) {
            // Check if foreign key exists first
            $hasForeignKey = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'paper_approval_logs' AND COLUMN_NAME = 'approved_by' AND CONSTRAINT_SCHEMA = DATABASE()"))->isNotEmpty();
            if ($hasForeignKey) {
                // For MySQL it's table_column_foreign by standard Laravel convention
                $table->dropForeign(['approved_by']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paper_approval_logs', function (Blueprint $table) {
            $table->foreign('approved_by')->references('id')->on('teachers')->onDelete('set null');
        });
    }
};
