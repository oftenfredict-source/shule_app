<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = 'holidays';
    protected $primaryKey = 'holidayID';
    
    protected $fillable = [
        'schoolID',
        'academic_yearID',
        'holiday_name',
        'start_date',
        'end_date',
        'description',
        'type',
        'has_working_days',
        'working_days_count',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'has_working_days' => 'boolean',
        'working_days_count' => 'integer',
    ];
    
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }

    public function homePackages()
    {
        return $this->hasMany(HomePackage::class, 'holidayID', 'holidayID');
    }
}
