<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeDeadline extends Model
{
    use HasFactory;

    protected $table = 'fee_deadlines';
    protected $primaryKey = 'deadlineID';

    public const AMOUNT_FIXED = 'fixed';
    public const AMOUNT_PERCENT = 'percent';

    protected $fillable = [
        'schoolID',
        'academic_yearID',
        'feeID',
        'deadline_type',
        'start_date',
        'end_date',
        'start_month',
        'end_month',
        'amount_type',
        'amount',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_month' => 'integer',
        'end_month' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function isPercent(): bool
    {
        return ($this->amount_type ?? self::AMOUNT_FIXED) === self::AMOUNT_PERCENT;
    }

    /**
     * Resolve how much of $effectiveAmount this deadline requires for one student.
     * Percent rows are period portions of the student's effective fee.
     * Fixed rows are TZS amounts, scaled when the student has discount/agreement.
     */
    public function requiredForAmount(float $effectiveAmount, float $standardAmount): float
    {
        if ($effectiveAmount <= 0) {
            return 0;
        }

        if ($this->isPercent()) {
            $percent = max(0, min(100, (float) $this->amount));

            return max(0, min($effectiveAmount, $effectiveAmount * ($percent / 100)));
        }

        $fixed = (float) $this->amount;
        if ($standardAmount > 0 && $effectiveAmount < $standardAmount) {
            // Scale fixed TZS deadlines for discount / agreement students.
            $fixed = $fixed * ($effectiveAmount / $standardAmount);
        }

        return max(0, min($effectiveAmount, $fixed));
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'feeID', 'feeID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }
}
