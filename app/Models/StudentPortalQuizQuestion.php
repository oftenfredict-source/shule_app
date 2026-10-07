<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPortalQuizQuestion extends Model
{
    protected $table = 'student_portal_quiz_questions';

    protected $fillable = [
        'quiz_id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d',
        'correct_option', 'sort_order',
    ];
}
