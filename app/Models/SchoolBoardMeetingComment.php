<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardMeetingComment extends Model
{
    use HasFactory;

    protected $table = 'school_board_meeting_comments';
    protected $primaryKey = 'commentID';

    protected $fillable = [
        'schoolID',
        'meetingID',
        'memberID',
        'body',
        'was_absent',
    ];

    protected $casts = [
        'was_absent' => 'boolean',
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
