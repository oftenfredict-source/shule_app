<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'schoolID',
        'examID',
        'studentID',
        'subjectID',
        'status',
        'marked_by'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function subject()
    {
        return $this->belongsTo(SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function exam()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }
}
