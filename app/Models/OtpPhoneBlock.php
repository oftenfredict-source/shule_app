<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpPhoneBlock extends Model
{
    public const CONTEXT_SCHOOL_REGISTRATION = 'school_registration';

    /** Max OTP sends before phone is blocked until admin release. */
    public const MAX_OTP_SENDS = 10;

    protected $table = 'otp_phone_blocks';

    protected $fillable = [
        'phone',
        'context',
        'otp_send_count',
        'is_blocked',
        'last_otp_at',
        'blocked_at',
        'released_at',
        'released_by',
        'block_reason',
    ];

    protected $casts = [
        'otp_send_count' => 'integer',
        'is_blocked' => 'boolean',
        'last_otp_at' => 'datetime',
        'blocked_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    public function scopeForContext($query, string $context)
    {
        return $query->where('context', $context);
    }
}
