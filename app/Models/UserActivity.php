<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_log_id',
        'url',
        'method',
        'route_name',
        'payload',
    ];

    public function log()
    {
        return $this->belongsTo(UserLog::class, 'user_log_id');
    }
}
