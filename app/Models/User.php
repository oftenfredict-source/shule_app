<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'user_type',
        'password',
        'fingerprint_id',
        'schoolID',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            static $hasSchoolId = null;
            if ($hasSchoolId === null) {
                $hasSchoolId = Schema::hasColumn($user->getTable(), 'schoolID');
            }
            if (!$hasSchoolId) {
                unset($user->schoolID);
            }
        });
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'employee_number', 'name');
    }

    public function staff()
    {
        return $this->hasOne(OtherStaff::class, 'employee_number', 'name');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function boardMember()
    {
        return $this->hasOne(SchoolBoardMember::class, 'user_id', 'id');
    }
}
