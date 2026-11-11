<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledSms extends Model
{
    protected $table = 'scheduled_sms';

    protected $fillable = [
        'schoolID',
        'recipient_type',
        'message',
        'selected_ids',
        'classID',
        'scheduled_at',
        'status',
        'result_summary',
        'created_by',
        'created_by_name',
    ];

    protected $casts = [
        'selected_ids' => 'array',
        'scheduled_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}
