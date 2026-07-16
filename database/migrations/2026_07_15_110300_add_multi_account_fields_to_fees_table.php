<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $index): bool
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT 1 FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?
             LIMIT 1',
            [$db, $table, $index]
        );

        return count($rows) > 0;
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT 1 FROM information_schema.table_constraints
             WHERE constraint_schema = ? AND table_name = ? AND constraint_name = ? AND constraint_type = ?
             LIMIT 1',
            [$db, $table, $constraint, 'FOREIGN KEY']
        );

        return count($rows) > 0;
    }

    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            if (! Schema::hasColumn('fees', 'account_mode')) {
                $table->enum('account_mode', ['single', 'multi'])->default('single')->after('status');
            }
            if (! Schema::hasColumn('fees', 'bank_account_id')) {
                $table->unsignedBigInteger('bank_account_id')->nullable()->after('account_mode');
            }
            if (! Schema::hasColumn('fees', 'has_breakdown')) {
                $table->boolean('has_breakdown')->default(false)->after('bank_account_id');
            }
        });

        // Ensure type matches school_bank_accounts.accountID (bigint unsigned)
        if (Schema::hasColumn('fees', 'bank_account_id')) {
            DB::statement('ALTER TABLE `fees` MODIFY `bank_account_id` BIGINT UNSIGNED NULL');
        }

        if (
            Schema::hasTable('school_bank_accounts')
            && Schema::hasColumn('fees', 'bank_account_id')
            && ! $this->foreignKeyExists('fees', 'fees_bank_account_id_foreign')
        ) {
            // FK alone (creates its own index). Do not add a second index in the same ALTER —
            // MariaDB often returns errno 150 when both are combined.
            Schema::table('fees', function (Blueprint $table) {
                $table->foreign('bank_account_id', 'fees_bank_account_id_foreign')
                    ->references('accountID')
                    ->on('school_bank_accounts')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('fees', 'account_mode') && ! $this->indexExists('fees', 'fees_account_mode_index')) {
            Schema::table('fees', function (Blueprint $table) {
                $table->index('account_mode', 'fees_account_mode_index');
            });
        }
    }

    public function down(): void
    {
        if ($this->foreignKeyExists('fees', 'fees_bank_account_id_foreign')) {
            Schema::table('fees', function (Blueprint $table) {
                $table->dropForeign('fees_bank_account_id_foreign');
            });
        }

        if ($this->indexExists('fees', 'fees_account_mode_index')) {
            Schema::table('fees', function (Blueprint $table) {
                $table->dropIndex('fees_account_mode_index');
            });
        }

        Schema::table('fees', function (Blueprint $table) {
            $cols = [];
            foreach (['account_mode', 'bank_account_id', 'has_breakdown'] as $col) {
                if (Schema::hasColumn('fees', $col)) {
                    $cols[] = $col;
                }
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
