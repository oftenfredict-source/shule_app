<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'attendances',
        'student_session_attendance',
        'student_fingerprint_attendance',
        'payments',
        'student_fee_payments',
        'book_borrows',
        'results',
        'black_book_reports',
        'store_requisitions',
        'student_permissions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if (!Schema::hasColumn($tableName, 'enrollmentID')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('enrollmentID')->nullable();
                    $table->index('enrollmentID');
                });
            }

            try {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->foreign('enrollmentID', substr($tableName, 0, 20) . '_enroll_fk')
                        ->references('enrollmentID')
                        ->on('student_enrollments')
                        ->onDelete('set null');
                });
            } catch (\Throwable $e) {
                // ignore duplicate FK
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'enrollmentID')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                try {
                    $table->dropForeign(substr($tableName, 0, 20) . '_enroll_fk');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex([$tableName . '_enrollment_id_index']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('enrollmentID');
            });
        }
    }
};
