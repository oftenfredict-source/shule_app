<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineAdmissionSpace extends Model
{
    protected $fillable = [
        'schoolID',
        'classID',
        'academic_yearID',
        'spaces',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }
}
