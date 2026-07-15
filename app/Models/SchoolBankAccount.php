<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBankAccount extends Model
{
    use HasFactory;

    protected $table = 'school_bank_accounts';
    protected $primaryKey = 'accountID';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'schoolID',
        'bankID',
        'account_number',
        'account_name',
        'status',
        'rejection_reason',
        'otp_verified_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'otp_verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bankID', 'bankID');
    }

    public function fees()
    {
        return $this->hasMany(Fee::class, 'bank_account_id', 'accountID');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForSchool($query, int $schoolID)
    {
        return $query->where('schoolID', $schoolID);
    }

    public function displayLabel(): string
    {
        $bankCode = $this->bank->bank_code ?: ($this->bank->bank_name ?? 'BANK');
        return $this->account_number . ' (' . $bankCode . ')';
    }
}
