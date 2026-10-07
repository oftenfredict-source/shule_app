<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_visitors')) {
            return;
        }

        Schema::table('school_visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('school_visitors', 'office_visited')) {
                $table->string('office_visited')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('school_visitors', 'signed_in_at')) {
                $table->dateTime('signed_in_at')->nullable()->after('office_visited');
            }
            if (!Schema::hasColumn('school_visitors', 'signed_out_at')) {
                $table->dateTime('signed_out_at')->nullable()->after('signed_in_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('school_visitors')) {
            return;
        }

        Schema::table('school_visitors', function (Blueprint $table) {
            if (Schema::hasColumn('school_visitors', 'signed_out_at')) {
                $table->dropColumn('signed_out_at');
            }
            if (Schema::hasColumn('school_visitors', 'signed_in_at')) {
                $table->dropColumn('signed_in_at');
            }
            if (Schema::hasColumn('school_visitors', 'office_visited')) {
                $table->dropColumn('office_visited');
            }
        });
    }
};
