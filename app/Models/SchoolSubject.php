<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSubject extends Model
{
    use HasFactory;

    protected $table = 'school_subjects';
    protected $primaryKey = 'subjectID';

    protected $fillable = [
        'schoolID',
        'subject_name',
        'subject_code',
        'status',
        'is_duo',
    ];

    protected $casts = [
        'status' => 'string',
        'is_duo' => 'boolean',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'subjectID', 'subjectID');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_subjects', 'subjectID', 'departmentID');
    }

    /** A-Level multi-paper parts e.g. PHY1, PHY2, PHY3 */
    public function components()
    {
        return $this->hasMany(SchoolSubjectComponent::class, 'subjectID', 'subjectID')
            ->orderBy('sort_order')
            ->orderBy('componentID');
    }
}
