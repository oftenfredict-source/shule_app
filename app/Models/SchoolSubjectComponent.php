<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSubjectComponent extends Model
{
    use HasFactory;

    protected $table = 'school_subject_components';
    protected $primaryKey = 'componentID';

    protected $fillable = [
        'subjectID',
        'component_name',
        'max_marks',
        'sort_order',
    ];

    protected $casts = [
        'max_marks' => 'integer',
        'sort_order' => 'integer',
    ];

    public function schoolSubject()
    {
        return $this->belongsTo(SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function classSubjectTeachers()
    {
        return $this->hasMany(ClassSubjectComponentTeacher::class, 'componentID', 'componentID');
    }
}
