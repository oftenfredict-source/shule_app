<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $table = 'sms_logs';

    protected $fillable = [
        'schoolID',
        'message_id',
        'message_type',
        'recipient_name',
        'recipient_phone',
        'recipient_role',
        'sent_by_name',
        'sent_by_role',
        'message',
        'status',
        'sent_date',
        'sent_time',
    ];

    protected $casts = [
        'sent_date' => 'date',
    ];

    /**
     * Human-readable message type labels
     */
    public static function messageTypeLabel(string $type): string
    {
        return match($type) {
            'parent'        => 'Parent SMS',
            'class_parent'  => 'Class Parent SMS',
            'teacher'       => 'Teacher SMS',
            'staff'         => 'Staff SMS',
            'all_users'     => 'All Users SMS',
            'fee'           => 'Fee SMS',
            'result'        => 'Result SMS',
            default         => ucwords(str_replace('_', ' ', $type)) . ' SMS',
        };
    }
}
