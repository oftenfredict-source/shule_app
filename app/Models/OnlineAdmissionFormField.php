<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineAdmissionFormField extends Model
{
    protected $fillable = [
        'schoolID',
        'classID',
        'section_id',
        'field_key',
        'label',
        'field_type',
        'options',
        'is_required',
        'is_system',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function section()
    {
        return $this->belongsTo(OnlineAdmissionFormSection::class, 'section_id');
    }
}
