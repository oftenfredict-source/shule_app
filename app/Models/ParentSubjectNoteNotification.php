<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentSubjectNoteNotification extends Model
{
    protected $table = 'parent_subject_note_notifications';

    protected $fillable = [
        'schoolID',
        'parentID',
        'studentID',
        'noteID',
        'is_viewed',
    ];

    protected $casts = [
        'is_viewed' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parentID', 'parentID');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function note()
    {
        return $this->belongsTo(SubjectNote::class, 'noteID', 'noteID');
    }
}
