<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaDefinition extends Model
{
    use HasFactory;

    protected $table = 'ca_definitions';

    protected $fillable = [
        'schoolID',
        'year',
        'term',
        'examID',
        'test_ids',
        'use_percent_division',
        'exam_weights',
        'level_scope',
        'created_by',
    ];

    protected $casts = [
        'test_ids' => 'array',
        'exam_weights' => 'array',
        'use_percent_division' => 'boolean',
        'year' => 'integer',
        'examID' => 'integer',
    ];

    public function mainExam()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
