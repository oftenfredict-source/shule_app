<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolVisitor extends Model
{
    use HasFactory;

    protected $table = 'school_visitors';
    protected $primaryKey = 'visitorID';

    protected $fillable = [
        'schoolID',
        'visit_date',
        'name',
        'contact',
        'occupation',
        'reason',
        'office_visited',
        'signature',
        'signed_in_at',
        'signed_out_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'signed_in_at' => 'datetime',
        'signed_out_at' => 'datetime',
    ];
}
