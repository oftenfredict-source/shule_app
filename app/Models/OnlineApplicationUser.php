<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class OnlineApplicationUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'online_application_users';

    protected $fillable = [
        'phone',
        'first_name',
        'middle_name',
        'last_name',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
    }

    public function admissions()
    {
        return $this->hasMany(OnlineAdmission::class, 'online_application_userID');
    }

    public function isActive(): bool
    {
        return strcasecmp((string) $this->status, 'Active') === 0;
    }
}
