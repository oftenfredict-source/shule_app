<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPortalQuizAttempt extends Model
{
    protected $table = 'student_portal_quiz_attempts';

    protected $fillable = [
        'quiz_id', 'studentID', 'schoolID', 'score', 'total', 'answers',
        'manual_scores', 'teacher_feedback', 'submitted_at', 'graded_at', 'graded_by',
    ];

    protected $casts = [
        'answers' => 'array',
        'manual_scores' => 'array',
        'teacher_feedback' => 'array',
        'score' => 'float',
        'total' => 'float',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];
}
