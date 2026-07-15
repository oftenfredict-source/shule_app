<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $table = 'fees';
    protected $primaryKey = 'feeID';

    protected $fillable = [
        'schoolID',
        'classID',
        // 'fee_type', // REMOVED - no longer using Tuition/Other separation
        'fee_name',
        'amount',
        'must_start_pay', // NEW - priority payment flag
        'payment_deadline_amount', // NEW - amount required by deadline
        'payment_deadline_date', // NEW - deadline date
        'display_order', // NEW - for sorting priority
        'duration',
        'description',
        'sms_assignment_template', // Added
        'sms_reminder_template',   // Added
        'status',
        'account_mode',
        'bank_account_id',
        'has_breakdown',
        'allow_installments',
        'default_installment_type',
        'number_of_installments',
        'allow_partial_payment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'must_start_pay' => 'boolean',
        'payment_deadline_amount' => 'decimal:2',
        'payment_deadline_date' => 'date',
        'display_order' => 'integer',
        'allow_installments' => 'boolean',
        'allow_partial_payment' => 'boolean',
        'number_of_installments' => 'integer',
        'has_breakdown' => 'boolean',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function installments()
    {
        return $this->hasMany(FeeInstallment::class, 'feeID', 'feeID');
    }

    public function deadlines()
    {
        return $this->hasMany(FeeDeadline::class, 'feeID', 'feeID');
    }

    public function otherFeeDetails()
    {
        return $this->hasMany(OtherFeeDetail::class, 'feeID', 'feeID');
    }

    public function feeConditions()
    {
        return $this->hasMany(FeeCondition::class, 'feeID', 'feeID');
    }

    public function bankAccount()
    {
        return $this->belongsTo(SchoolBankAccount::class, 'bank_account_id', 'accountID');
    }

    public function breakdowns()
    {
        return $this->hasMany(FeeBreakdown::class, 'feeID', 'feeID')->orderBy('display_order');
    }

    public function isMultiAccount(): bool
    {
        return ($this->account_mode ?? 'single') === 'multi';
    }

    public function scopeSingleAccount($query)
    {
        return $query->where(function ($q) {
            $q->where('account_mode', 'single')->orWhereNull('account_mode');
        });
    }

    public function scopeMultiAccount($query)
    {
        return $query->where('account_mode', 'multi');
    }
}
