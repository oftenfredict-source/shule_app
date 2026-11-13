<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackBookReport extends Model
{
    protected $table = 'black_book_reports';
    protected $primaryKey = 'reportID';

    protected $fillable = [
        'schoolID',
        'studentID',
        'enrollmentID',
        'problem_categoryID',
        'custom_problem_name',
        'description',
        'reported_by_type',
        'reported_by_id',
        'reported_by_name',
        'status',
        'viewed_at',
        'viewed_by_name',
        'resolve_method',
        'punishment_categoryID',
        'punishment_description',
        'punishment_type',
        'requires_monitoring',
        'monitoring_deadline',
        'monitoring_ended_at',
        'parent_sms_message',
        'parent_sms_sent',
        'resolved_at',
        'resolved_by_name',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'monitoring_ended_at' => 'datetime',
        'monitoring_deadline' => 'date',
        'parent_sms_sent' => 'boolean',
        'requires_monitoring' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollmentID', 'enrollmentID');
    }

    public function problemCategory()
    {
        return $this->belongsTo(BlackBookProblemCategory::class, 'problem_categoryID', 'categoryID');
    }

    public function punishmentCategory()
    {
        return $this->belongsTo(BlackBookPunishmentCategory::class, 'punishment_categoryID', 'punishmentCategoryID');
    }
}
