<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolFingerprintAttendanceSmsSchedule extends Model
{
    public const SCHEDULE_NONE = 'none';
    public const SCHEDULE_DAILY = 'daily';
    public const SCHEDULE_WEEKLY = 'weekly';
    public const SCHEDULE_MONTHLY = 'monthly';
    public const SCHEDULE_YEARLY = 'yearly';

    public const METHOD_MANUAL = 'manual';
    public const METHOD_AUTO = 'auto';

    protected $table = 'school_fingerprint_attendance_sms_schedules';

    protected $fillable = [
        'schoolID',
        'schedule',
        'send_method',
        'send_time',
        'send_day_of_week',
        'send_day_of_month',
        'send_month',
        'last_auto_period_key',
        'last_auto_run_at',
        'updated_by',
    ];

    protected $casts = [
        'send_day_of_week' => 'integer',
        'send_day_of_month' => 'integer',
        'send_month' => 'integer',
        'last_auto_run_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}
