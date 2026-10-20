<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPortalQuizQuestion extends Model
{
    protected $table = 'student_portal_quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question_type',
        'section_number',
        'section_title',
        'item_label',
        'points',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'config' => 'array',
        'points' => 'float',
        'section_number' => 'integer',
        'sort_order' => 'integer',
    ];

    public function quiz()
    {
        return $this->belongsTo(StudentPortalQuiz::class, 'quiz_id');
    }

    public function isAutoGraded(): bool
    {
        return !in_array($this->question_type ?: 'multiple_choice', ['short_answer', 'essay'], true);
    }
}
