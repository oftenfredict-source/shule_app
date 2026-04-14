<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterAssessmentTimetable extends Model
{
    use HasFactory;

    protected $primaryKey = 'timetableID';

    protected $fillable = [
        'schoolID',
        'academic_yearID',
        'term',
        'start_date',
        'end_date',
        'target_classes'
    ];

    protected $casts = [
        'target_classes' => 'array'
    ];

    public function assignments()
    {
        return $this->hasMany(CharacterAssessmentAssignment::class, 'timetableID', 'timetableID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }
}
