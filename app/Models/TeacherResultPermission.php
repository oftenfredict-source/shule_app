<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherResultPermission extends Model
{
    use HasFactory;

    protected $table = 'teacher_result_permissions';
    protected $primaryKey = 'permissionID';

    protected $fillable = [
        'schoolID',
        'result_button_status',
    ];

    protected $casts = [
        'result_button_status' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}
