<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPortalBook extends Model
{
    protected $table = 'student_portal_books';

    protected $fillable = [
        'schoolID', 'teacherID', 'classID', 'subclassID', 'subjectID',
        'title', 'author', 'description', 'file_path', 'original_name', 'external_url', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}
