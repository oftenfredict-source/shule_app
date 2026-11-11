<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pending_student_registrations')) {
            return;
        }

        Schema::table('pending_student_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('pending_student_registrations', 'pay_amount')) {
                $table->decimal('pay_amount', 15, 2)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('pending_student_registrations', 'payment_reference')) {
                $table->string('payment_reference', 120)->nullable()->after('pay_amount');
            }
            if (!Schema::hasColumn('pending_student_registrations', 'payment_method')) {
                $table->string('payment_method', 40)->nullable()->after('payment_reference');
            }
            if (!Schema::hasColumn('pending_student_registrations', 'payment_recorded_at')) {
                $table->timestamp('payment_recorded_at')->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pending_student_registrations')) {
            return;
        }

        Schema::table('pending_student_registrations', function (Blueprint $table) {
            foreach (['pay_amount', 'payment_reference', 'payment_method', 'payment_recorded_at'] as $column) {
                if (Schema::hasColumn('pending_student_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
