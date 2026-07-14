<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('teachers', 'tsc_number')) {
                $table->string('tsc_number', 100)->nullable()->after('employee_number');
            }
            if (!Schema::hasColumn('teachers', 'establishment_rank')) {
                $table->string('establishment_rank', 150)->nullable()->after('position');
            }
            if (!Schema::hasColumn('teachers', 'salary_scale')) {
                $table->string('salary_scale', 100)->nullable()->after('establishment_rank');
            }
            if (!Schema::hasColumn('teachers', 'date_confirmed')) {
                $table->date('date_confirmed')->nullable()->after('date_hired');
            }
            if (!Schema::hasColumn('teachers', 'date_current_rank')) {
                $table->date('date_current_rank')->nullable()->after('date_confirmed');
            }
            if (!Schema::hasColumn('teachers', 'date_of_retirement')) {
                $table->date('date_of_retirement')->nullable()->after('date_current_rank');
            }
            if (!Schema::hasColumn('teachers', 'subjects_teaching')) {
                $table->text('subjects_teaching')->nullable()->after('specialization');
            }
            if (!Schema::hasColumn('teachers', 'work_station')) {
                $table->string('work_station', 255)->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $cols = [
                'tsc_number',
                'establishment_rank',
                'salary_scale',
                'date_confirmed',
                'date_current_rank',
                'date_of_retirement',
                'subjects_teaching',
                'work_station',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('teachers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
