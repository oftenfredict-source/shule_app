<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardMeeting extends Model
{
    use HasFactory;

    protected $table = 'school_board_meetings';
    protected $primaryKey = 'meetingID';

    protected $fillable = [
        'schoolID',
        'committeeID',
        'title',
        'meeting_type',
        'meeting_date',
        'start_time',
        'end_time',
        'venue',
        'agenda',
        'status',
        'chair_memberID',
        'notes',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    public function committee()
    {
        return $this->belongsTo(SchoolBoardCommittee::class, 'committeeID', 'committeeID');
    }

    public function chair()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'chair_memberID', 'memberID');
    }

    public function attendees()
    {
        return $this->belongsToMany(
            SchoolBoardMember::class,
            'school_board_meeting_attendees',
            'meetingID',
            'memberID'
        )->withPivot(['id', 'attendance_status', 'schoolID'])
            ->withTimestamps();
    }

    public function meetingAttendees()
    {
        return $this->hasMany(SchoolBoardMeetingAttendee::class, 'meetingID', 'meetingID');
    }

    public function minute()
    {
        return $this->hasOne(SchoolBoardMinute::class, 'meetingID', 'meetingID');
    }

    public function resolutions()
    {
        return $this->hasMany(SchoolBoardResolution::class, 'meetingID', 'meetingID');
    }

    public function actionItems()
    {
        return $this->hasMany(SchoolBoardActionItem::class, 'meetingID', 'meetingID');
    }

    public function comments()
    {
        return $this->hasMany(SchoolBoardMeetingComment::class, 'meetingID', 'meetingID');
    }

    public function startsAt(): Carbon
    {
        $date = $this->meeting_date
            ? $this->meeting_date->format('Y-m-d')
            : now()->format('Y-m-d');
        $time = $this->start_time
            ? substr((string) $this->start_time, 0, 5)
            : '00:00';

        return Carbon::parse($date . ' ' . $time);
    }

    public function hasStarted(): bool
    {
        return now()->greaterThanOrEqualTo($this->startsAt());
    }

    public function isUpcoming(): bool
    {
        return $this->status === 'Scheduled' && !$this->hasStarted();
    }

    public function allowsMinutes(): bool
    {
        return in_array($this->status, ['Ongoing', 'Completed'], true);
    }

    /**
     * Promote Scheduled meetings to Ongoing once start time has arrived.
     */
    public static function syncOngoingStatusesForSchool(int $schoolID): void
    {
        $meetings = static::where('schoolID', $schoolID)
            ->where('status', 'Scheduled')
            ->get();

        foreach ($meetings as $meeting) {
            if ($meeting->hasStarted()) {
                $meeting->update(['status' => 'Ongoing']);
            }
        }
    }
}
