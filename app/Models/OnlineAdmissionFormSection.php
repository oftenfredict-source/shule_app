<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineAdmissionFormSection extends Model
{
    protected $fillable = [
        'schoolID',
        'classID',
        'section_key',
        'title',
        'description',
        'sort_order',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function fields()
    {
        return $this->hasMany(OnlineAdmissionFormField::class, 'section_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function activeFields()
    {
        return $this->fields()->where('is_active', true)->where('field_type', '!=', 'section');
    }
}
