<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPortalNote extends Model
{
    protected $table = 'student_portal_notes';

    protected $fillable = [
        'schoolID', 'teacherID', 'classID', 'subclassID', 'subjectID',
        'title', 'body', 'file_path', 'original_name', 'external_url', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}
