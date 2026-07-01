<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherStaffWorkTimeSetting extends Model
{
    use HasFactory;

    protected $table = 'teacher_staff_work_time_settings';

    protected $fillable = [
        'schoolID',
        'work_start_time',
        'work_end_time',
    ];

    public static function forSchool(int $schoolID): self
    {
        return static::firstOrCreate(
            ['schoolID' => $schoolID],
            [
                'work_start_time' => '08:00:00',
                'work_end_time' => '15:00:00',
            ]
        );
    }
}
