<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_attendance_sms_schedules')) {
            return;
        }

        Schema::table('school_attendance_sms_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('school_attendance_sms_schedules', 'send_method')) {
                $table->enum('send_method', ['manual', 'auto'])->default('manual')->after('schedule');
            }
            if (!Schema::hasColumn('school_attendance_sms_schedules', 'send_time')) {
                $table->time('send_time')->nullable()->after('send_method');
            }
            if (!Schema::hasColumn('school_attendance_sms_schedules', 'send_day_of_week')) {
                // 1=Monday ... 7=Sunday
                $table->unsignedTinyInteger('send_day_of_week')->nullable()->after('send_time');
            }
            if (!Schema::hasColumn('school_attendance_sms_schedules', 'send_day_of_month')) {
                $table->unsignedTinyInteger('send_day_of_month')->nullable()->after('send_day_of_week');
            }
            if (!Schema::hasColumn('school_attendance_sms_schedules', 'send_month')) {
                $table->unsignedTinyInteger('send_month')->nullable()->after('send_day_of_month');
            }
            if (!Schema::hasColumn('school_attendance_sms_schedules', 'last_auto_period_key')) {
                $table->string('last_auto_period_key', 20)->nullable()->after('send_month');
            }
            if (!Schema::hasColumn('school_attendance_sms_schedules', 'last_auto_run_at')) {
                $table->timestamp('last_auto_run_at')->nullable()->after('last_auto_period_key');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('school_attendance_sms_schedules')) {
            return;
        }

        Schema::table('school_attendance_sms_schedules', function (Blueprint $table) {
            $cols = [
                'send_method',
                'send_time',
                'send_day_of_week',
                'send_day_of_month',
                'send_month',
                'last_auto_period_key',
                'last_auto_run_at',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('school_attendance_sms_schedules', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
