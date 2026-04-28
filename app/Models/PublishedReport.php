<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishedReport extends Model
{
    protected $fillable = [
        'schoolID',
        'classID',
        'term',
        'year',
        'status',
    ];}
