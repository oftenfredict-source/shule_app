<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolRegistrationRequest extends Model
{
    protected $table = 'school_registration_requests';

    protected $fillable = [
        'school_name',
        'registration_number',
        'exam_center_number',
        'school_type',
        'ownership',
        'established_year',
        'region',
        'district',
        'ward',
        'email',
        'phone',
        'address',
        'number_of_students',
        'contact_person',
        'notes',
        'status',
        'phone_verified_at',
        'verified_at',
        'verified_by',
        'admin_notes',
    ];

    protected $casts = [
        'number_of_students' => 'integer',
        'phone_verified_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}
