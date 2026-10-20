<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPortalQuiz extends Model
{
    protected $table = 'student_portal_quizzes';

    protected $fillable = [
        'schoolID', 'teacherID', 'classID', 'subclassID', 'subjectID',
        'title', 'instructions', 'is_published', 'opens_at', 'closes_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
    ];

    public function isScheduled(): bool
    {
        return $this->opens_at !== null && $this->closes_at !== null;
    }

    public function windowStatus(): string
    {
        if (!$this->isScheduled()) {
            return 'open';
        }

        $now = now();
        if ($now->lt($this->opens_at)) {
            return 'upcoming';
        }
        if ($now->gt($this->closes_at)) {
            return 'closed';
        }

        return 'open';
    }

    public function questions()
    {
        return $this->hasMany(StudentPortalQuizQuestion::class, 'quiz_id')
            ->orderBy('section_number')
            ->orderBy('sort_order');
    }

    public function attempts()
    {
        return $this->hasMany(StudentPortalQuizAttempt::class, 'quiz_id')->latest('submitted_at');
    }
}
