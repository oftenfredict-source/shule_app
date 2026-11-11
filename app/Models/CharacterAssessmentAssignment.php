<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterAssessmentAssignment extends Model
{
    use HasFactory;

    protected $primaryKey = 'assignmentID';

    protected $fillable = [
        'timetableID',
        'classID',
        'subclassID',
        'teacherID',
        'selected_categories'
    ];

    protected $casts = [
        'selected_categories' => 'array'
    ];

    public function timetable()
    {
        return $this->belongsTo(CharacterAssessmentTimetable::class, 'timetableID', 'timetableID');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    /**
     * Assignments a teacher should see in nav/task list:
     * any timetable for their school that has not ended yet (active + upcoming).
     */
    public function scopeVisibleToTeacher(Builder $query, int $teacherID, int $schoolID): Builder
    {
        $today = Carbon::today()->toDateString();

        return $query->where('teacherID', $teacherID)
            ->whereHas('timetable', function ($q) use ($schoolID, $today) {
                $q->where('schoolID', $schoolID)
                    ->where(function ($dates) use ($today) {
                        $dates->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $today);
                    });
            });
    }

    public static function teacherHasVisibleAssignment(int $teacherID, int $schoolID): bool
    {
        return static::query()
            ->visibleToTeacher($teacherID, $schoolID)
            ->exists();
    }
}
