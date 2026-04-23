<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    protected $table = 'logbooks';
    protected $primaryKey = 'logbookID';

    protected $fillable = [
        'class_subjectID',
        'main_topic',
        'sub_topic',
        'content_covered',
        'date_started',
        'date_ended',
        'teacher_comment',
        'hod_comment',
        'hm_comment',
        'schoolID',
        'year',
        'status'
    ];

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subjectID', 'class_subjectID');
    }
}
