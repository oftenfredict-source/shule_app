<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectNote extends Model
{
    protected $table = 'subject_notes';
    protected $primaryKey = 'noteID';

    protected $fillable = [
        'schoolID',
        'academic_yearID',
        'subjectID',
        'class_subjectID',
        'teacherID',
        'level',
        'title',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }

    public function subject()
    {
        return $this->belongsTo(SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subjectID', 'class_subjectID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(ParentSubjectNoteNotification::class, 'noteID', 'noteID');
    }
}
