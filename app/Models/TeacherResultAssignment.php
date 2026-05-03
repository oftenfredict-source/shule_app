<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherResultAssignment extends Model
{
    protected $table = 'teacher_result_assignments';

    protected $fillable = [
        'schoolID',
        'teacherID',
        'classID',
        'subclassID',
        'subject_ids',
        'examID',
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
