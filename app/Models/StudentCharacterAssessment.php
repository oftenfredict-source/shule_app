<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCharacterAssessment extends Model
{
    protected $primaryKey = 'assessmentID';

    protected $fillable = [
        'schoolID',
        'studentID',
        'year',
        'timetableID',
        'categoryID',
        'choiceID',
        'description',
        'assessed_by'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function category()
    {
        return $this->belongsTo(CharacterAssessmentCategory::class, 'categoryID', 'categoryID');
    }

    public function choice()
    {
        return $this->belongsTo(CharacterAssessmentChoice::class, 'choiceID', 'choiceID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'assessed_by', 'id');
    }
}
