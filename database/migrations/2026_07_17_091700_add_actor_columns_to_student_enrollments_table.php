<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_enrollments')) {
            return;
        }

        Schema::table('student_enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('student_enrollments', 'enrolled_by')) {
                $table->unsignedBigInteger('enrolled_by')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('student_enrollments', 'enrolled_by_name')) {
                $table->string('enrolled_by_name', 150)->nullable()->after('enrolled_by');
            }
            if (!Schema::hasColumn('student_enrollments', 'closed_by')) {
                $table->unsignedBigInteger('closed_by')->nullable()->after('enrolled_by_name');
            }
            if (!Schema::hasColumn('student_enrollments', 'closed_by_name')) {
                $table->string('closed_by_name', 150)->nullable()->after('closed_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_enrollments')) {
            return;
        }

        Schema::table('student_enrollments', function (Blueprint $table) {
            foreach (['closed_by_name', 'closed_by', 'enrolled_by_name', 'enrolled_by'] as $col) {
                if (Schema::hasColumn('student_enrollments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
