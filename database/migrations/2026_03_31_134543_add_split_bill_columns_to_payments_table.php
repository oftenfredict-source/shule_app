<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add split-bill tracking columns to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('sponsor_amount_required', 15, 2)->default(0)->after('balance');
            $table->decimal('sponsor_amount_paid', 15, 2)->default(0)->after('sponsor_amount_required');
            $table->decimal('sponsor_balance', 15, 2)->default(0)->after('sponsor_amount_paid');
            $table->decimal('parent_amount_required', 15, 2)->default(0)->after('sponsor_balance');
            $table->decimal('parent_amount_paid', 15, 2)->default(0)->after('parent_amount_required');
            $table->decimal('parent_balance', 15, 2)->default(0)->after('parent_amount_paid');
        });

        // Add payment_by column to payment_records table to track who paid
        Schema::table('payment_records', function (Blueprint $table) {
            $table->string('payment_by', 20)->default('Parent')->after('payment_source');
            // 'Parent' or 'Sponsor'
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'sponsor_amount_required', 'sponsor_amount_paid', 'sponsor_balance',
                'parent_amount_required', 'parent_amount_paid', 'parent_balance',
            ]);
        });

        Schema::table('payment_records', function (Blueprint $table) {
            $table->dropColumn('payment_by');
        });
    }
};
