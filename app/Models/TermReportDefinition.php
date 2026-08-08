<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermReportDefinition extends Model
{
    use HasFactory;

    protected $table = 'term_report_definitions';

    protected $fillable = [
        'schoolID',
        'year',
        'term',
        'exam_ids',
        'use_percent_division',
        'exam_weights',
        'level_scope',
        'school_closing_date',
        'school_opening_date',
        'created_by',
    ];

    protected $casts = [
        'exam_ids' => 'array',
        'exam_weights' => 'array',
        'use_percent_division' => 'boolean',
        'year' => 'integer',
        'school_closing_date' => 'date',
        'school_opening_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}
