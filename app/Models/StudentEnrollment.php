<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $table = 'student_enrollments';
    protected $primaryKey = 'enrollmentID';

    /** Valid promotion_status values (movement / year outcome reporting). */
    public const PROMOTION_STATUSES = [
        'Pending',
        'Promoted',
        'Repeated',
        'Graduated',
        'Transferred',
        'Truancy',
        'Dropout',
    ];

    public const PROMOTION_STATUS_LABELS = [
        'Pending' => 'Pending',
        'Promoted' => 'Promoted',
        'Repeated' => 'Repeated',
        'Graduated' => 'Graduated',
        'Transferred' => 'Transferred',
        'Truancy' => 'Truancy (Long-term absentee)',
        'Dropout' => 'Dropout',
    ];

    protected $fillable = [
        'studentID',
        'schoolID',
        'academic_yearID',
        'classID',
        'subclassID',
        'roll_number',
        'status',
        'promotion_status',
        'joined_date',
        'left_date',
        'notes',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'left_date' => 'date',
    ];

    public static function promotionStatusLabel(?string $status): string
    {
        if ($status === null || $status === '') {
            return '-';
        }

        return self::PROMOTION_STATUS_LABELS[$status] ?? $status;
    }

    public function getPromotionStatusLabelAttribute(): string
    {
        return self::promotionStatusLabel($this->promotion_status);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }
}
