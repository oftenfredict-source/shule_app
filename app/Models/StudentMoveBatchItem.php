<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMoveBatchItem extends Model
{
    protected $table = 'student_move_batch_items';
    protected $primaryKey = 'itemID';

    protected $fillable = [
        'batchID',
        'studentID',
        'old_enrollmentID',
        'new_enrollmentID',
        'action',
        'old_classID',
        'old_subclassID',
        'new_classID',
        'new_subclassID',
        'old_student_status',
        'new_student_status',
    ];

    public function batch()
    {
        return $this->belongsTo(StudentMoveBatch::class, 'batchID', 'batchID');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function oldEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'old_enrollmentID', 'enrollmentID');
    }

    public function newEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'new_enrollmentID', 'enrollmentID');
    }
}
