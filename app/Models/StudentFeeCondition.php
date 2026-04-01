<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeeCondition extends Model
{
    use HasFactory;

    protected $table = 'student_fee_conditions';
    protected $primaryKey = 'studentConditionID';

    protected $fillable = [
        'schoolID',
        'studentID',
        'conditionID',
        'feeID',
        'academic_yearID',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function condition()
    {
        return $this->belongsTo(FeeCondition::class, 'conditionID', 'conditionID');
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
