<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSmsLog extends Model
{
    protected $table = 'attendance_sms_logs';

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

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }
}
