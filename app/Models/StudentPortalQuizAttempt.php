<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPortalQuizAttempt extends Model
{
    protected $table = 'student_portal_quiz_attempts';

    protected $fillable = [
        'quiz_id', 'studentID', 'schoolID', 'score', 'total', 'answers', 'submitted_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'submitted_at' => 'datetime',
    ];
}
