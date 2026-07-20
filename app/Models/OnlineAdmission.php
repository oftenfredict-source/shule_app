<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineAdmission extends Model
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_CONFIRMED = 'Confirmed';
    public const STATUS_REJECTED = 'Rejected';

    protected $fillable = [
        'application_number',
        'schoolID',
        'classID',
        'academic_yearID',
        'status',
        'student_first_name',
        'student_middle_name',
        'student_last_name',
        'student_gender',
        'student_photo',
        'parent_phone',
        'parent_first_name',
        'parent_middle_name',
        'parent_last_name',
        'parent_gender',
        'parent_photo',
        'parentID',
        'studentID',
        'custom_responses',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'online_application_userID',
    ];

    protected $casts = [
        'custom_responses' => 'array',
        'reviewed_at' => 'datetime',
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

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parentID', 'parentID');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function onlineApplicationUser()
    {
        return $this->belongsTo(OnlineApplicationUser::class, 'online_application_userID');
    }
}
