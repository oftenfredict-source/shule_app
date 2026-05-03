<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttendanceAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'schoolID',
        'examID',
        'teacherID',
        'classID',
        'subclassID',
        'subject_ids',
        'status',
        'created_by'
    ];

    protected $casts = [
        'subject_ids' => 'array',
        'status' => 'boolean'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }

    public function exam()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }
}
