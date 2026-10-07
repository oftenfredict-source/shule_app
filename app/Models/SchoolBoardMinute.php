<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardMinute extends Model
{
    use HasFactory;

    protected $table = 'school_board_minutes';
    protected $primaryKey = 'minuteID';

    protected $fillable = [
        'schoolID',
        'meetingID',
        'title',
        'content',
        'decisions_summary',
        'prepared_by',
        'approved_by',
        'status',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(SchoolBoardMeeting::class, 'meetingID', 'meetingID');
    }

    public function preparer()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'prepared_by', 'memberID');
    }

    public function approver()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'approved_by', 'memberID');
    }
}
