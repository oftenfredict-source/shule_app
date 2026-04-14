<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterAssessmentAssignment extends Model
{
    use HasFactory;

    protected $primaryKey = 'assignmentID';

    protected $fillable = [
        'timetableID',
        'classID',
        'subclassID',
        'teacherID'
    ];

    public function timetable()
    {
        return $this->belongsTo(CharacterAssessmentTimetable::class, 'timetableID', 'timetableID');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }
}
