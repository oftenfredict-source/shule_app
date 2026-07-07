<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FingerprintAttendanceSmsLog extends Model
{
    protected $table = 'fingerprint_attendance_sms_logs';

    protected $fillable = [
        'schoolID',
        'studentID',
        'schedule_type',
        'period_key',
        'phone',
        'status',
        'message',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
