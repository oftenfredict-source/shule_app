<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMoveBatch extends Model
{
    protected $table = 'student_move_batches';
    protected $primaryKey = 'batchID';

    protected $fillable = [
        'schoolID',
        'to_academic_yearID',
        'examID',
        'moved_by',
        'moved_count',
        'graduated_count',
        'status',
        'undone_at',
        'undone_by',
        'notes',
    ];

    protected $casts = [
        'undone_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(StudentMoveBatchItem::class, 'batchID', 'batchID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'to_academic_yearID', 'academic_yearID');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}
