<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('home_packages')) {
            return;
        }

        Schema::table('home_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('home_packages', 'subjectID')) {
                $table->unsignedBigInteger('subjectID')->nullable()->after('holidayID');
            }
            if (!Schema::hasColumn('home_packages', 'class_subjectID')) {
                $table->unsignedBigInteger('class_subjectID')->nullable()->after('subjectID');
            }
        });

        // Replace unique(teacher, holiday) with unique(teacher, holiday, class_subject)
        try {
            Schema::table('home_packages', function (Blueprint $table) {
                $table->dropUnique('home_packages_teacher_holiday_unique');
            });
        } catch (\Throwable $e) {
            // index may already be gone
        }

        Schema::table('home_packages', function (Blueprint $table) {
            $table->unique(
                ['teacherID', 'holidayID', 'class_subjectID'],
                'home_packages_teacher_holiday_subject_unique'
            );
            $table->index(['schoolID', 'subjectID'], 'home_packages_school_subject_idx');
            $table->index('class_subjectID', 'home_packages_class_subject_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('home_packages')) {
            return;
        }

        try {
            Schema::table('home_packages', function (Blueprint $table) {
                $table->dropUnique('home_packages_teacher_holiday_subject_unique');
            });
        } catch (\Throwable $e) {
        }

        Schema::table('home_packages', function (Blueprint $table) {
            if (Schema::hasColumn('home_packages', 'class_subjectID')) {
                $table->dropIndex('home_packages_class_subject_idx');
            }
            if (Schema::hasColumn('home_packages', 'subjectID')) {
                $table->dropIndex('home_packages_school_subject_idx');
            }
            if (Schema::hasColumn('home_packages', 'class_subjectID')) {
                $table->dropColumn('class_subjectID');
            }
            if (Schema::hasColumn('home_packages', 'subjectID')) {
                $table->dropColumn('subjectID');
            }
            $table->unique(['teacherID', 'holidayID'], 'home_packages_teacher_holiday_unique');
        });
    }
};
