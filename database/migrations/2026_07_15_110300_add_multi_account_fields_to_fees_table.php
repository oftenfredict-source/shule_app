<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            if (!Schema::hasColumn('fees', 'account_mode')) {
                $table->enum('account_mode', ['single', 'multi'])->default('single')->after('status');
            }
            if (!Schema::hasColumn('fees', 'bank_account_id')) {
                $table->unsignedBigInteger('bank_account_id')->nullable()->after('account_mode');
            }
            if (!Schema::hasColumn('fees', 'has_breakdown')) {
                $table->boolean('has_breakdown')->default(false)->after('bank_account_id');
            }
        });

        Schema::table('fees', function (Blueprint $table) {
            if (Schema::hasColumn('fees', 'bank_account_id')) {
                $table->foreign('bank_account_id')
                    ->references('accountID')
                    ->on('school_bank_accounts')
                    ->nullOnDelete();
                $table->index('account_mode');
                $table->index('bank_account_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            if (Schema::hasColumn('fees', 'bank_account_id')) {
                $table->dropForeign(['bank_account_id']);
            }
            $cols = [];
            foreach (['account_mode', 'bank_account_id', 'has_breakdown'] as $col) {
                if (Schema::hasColumn('fees', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
