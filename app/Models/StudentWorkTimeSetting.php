<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentWorkTimeSetting extends Model
{
    use HasFactory;

    protected $table = 'student_work_time_settings';

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
                'work_start_time' => '07:30:00',
                'work_end_time' => '14:30:00',
            ]
        );
    }
}
