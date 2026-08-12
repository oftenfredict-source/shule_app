<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePackage extends Model
{
    protected $table = 'home_packages';
    protected $primaryKey = 'home_packageID';

    protected $fillable = [
        'schoolID',
        'academic_yearID',
        'holidayID',
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

    public function holiday()
    {
        return $this->belongsTo(Holiday::class, 'holidayID', 'holidayID');
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
        return $this->hasMany(ParentHomePackageNotification::class, 'home_packageID', 'home_packageID');
    }

    public function levelLabel(): string
    {
        return match ($this->level) {
            'A_Level' => 'A Level',
            'Primary' => 'Primary',
            default => 'O Level',
        };
    }
}
