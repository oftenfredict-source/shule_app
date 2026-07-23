<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSubjectComponentTeacher extends Model
{
    use HasFactory;

    protected $table = 'class_subject_component_teachers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'class_subjectID',
        'componentID',
        'teacherID',
    ];

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subjectID', 'class_subjectID');
    }

    public function component()
    {
        return $this->belongsTo(SchoolSubjectComponent::class, 'componentID', 'componentID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }
}
