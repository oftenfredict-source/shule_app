<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardMeetingAttendee extends Model
{
    use HasFactory;

    protected $table = 'school_board_meeting_attendees';
    protected $primaryKey = 'id';

    protected $fillable = [
        'schoolID',
        'meetingID',
        'memberID',
        'attendance_status',
    ];

    public function meeting()
    {
        return $this->belongsTo(SchoolBoardMeeting::class, 'meetingID', 'meetingID');
    }

    public function member()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'memberID', 'memberID');
    }
}
