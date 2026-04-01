<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeCondition extends Model
{
    use HasFactory;

    protected $table = 'fee_conditions';
    protected $primaryKey = 'conditionID';

    protected $fillable = [
        'schoolID',
        'feeID',
        'condition_name',
        'discount_amount',
        'discount_type',
        'description',
        'status',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'feeID', 'feeID');
    }

    public function studentConditions()
    {
        return $this->hasMany(StudentFeeCondition::class, 'conditionID', 'conditionID');
    }
}
