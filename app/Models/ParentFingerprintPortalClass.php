<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentFingerprintPortalClass extends Model
{
    protected $table = 'parent_fingerprint_portal_classes';

    protected $fillable = [
        'schoolID',
        'classID',
        'enabled',
    ];

    protected $casts = [
        'schoolID' => 'integer',
        'classID' => 'integer',
        'enabled' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }
}
