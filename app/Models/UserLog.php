<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'user_type',
        'role',
        'ip_address',
        'latitude',
        'longitude',
        'device_type',
        'device_brand',
        'browser',
        'os',
        'user_agent',
        'photo_path',
        'schoolID',
        'login_at',
        'logout_at',
    ];

    public function activities()
    {
        return $this->hasMany(UserActivity::class, 'user_log_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}
