<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentResultNotification extends Model
{
    protected $fillable = [
        'schoolID',
        'parentID',
        'studentID',
        'type',
        'examID',
        'term',
        'year',
        'is_viewed',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parentID', 'parentID');
    }

    public function examination()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }}
