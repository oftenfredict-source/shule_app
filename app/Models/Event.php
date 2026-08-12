<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'eventID';
    
    protected $fillable = [
        'schoolID',
        'academic_yearID',
        'event_name',
        'event_date',
        'start_time',
        'end_time',
        'description',
        'type',
        'is_non_working_day'
    ];
    
    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_non_working_day' => 'boolean',
    ];
    
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }
}
