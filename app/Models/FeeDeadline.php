<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeDeadline extends Model
{
    use HasFactory;

    protected $table = 'fee_deadlines';
    protected $primaryKey = 'deadlineID';

    protected $fillable = [
        'schoolID',
        'academic_yearID',
        'feeID',
        'deadline_type',
        'start_date',
        'end_date',
        'start_month',
        'end_month',
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

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'feeID', 'feeID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }
}
