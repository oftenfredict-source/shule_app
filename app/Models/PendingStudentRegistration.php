<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingStudentRegistration extends Model
{
    use HasFactory;

    protected $table = 'pending_student_registrations';
    protected $primaryKey = 'pendingID';

    protected $fillable = [
        'schoolID',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'previous_level',
        'previous_school',
        'intended_classID',
        'intended_subclassID',
        'intended_academic_yearID',
        'parent_first_name',
        'parent_middle_name',
        'parent_last_name',
        'parent_gender',
        'parent_phone',
        'parent_email',
        'parent_relationship',
        'phone',
        'notes',
        'pay_amount',
        'payment_reference',
        'payment_method',
        'payment_recorded_at',
        'status',
        'confirmed_studentID',
        'confirmed_at',
        'created_by',
        'created_by_name',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'confirmed_at' => 'datetime',
        'pay_amount' => 'decimal:2',
        'payment_recorded_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    public function intendedClass()
    {
        return $this->belongsTo(ClassModel::class, 'intended_classID', 'classID');
    }

    public function intendedSubclass()
    {
        return $this->belongsTo(Subclass::class, 'intended_subclassID', 'subclassID');
    }

    public function intendedAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'intended_academic_yearID', 'academic_yearID');
    }

    public function confirmedStudent()
    {
        return $this->belongsTo(Student::class, 'confirmed_studentID', 'studentID');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }
}
