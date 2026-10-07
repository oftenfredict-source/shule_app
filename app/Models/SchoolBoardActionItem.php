<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardActionItem extends Model
{
    use HasFactory;

    protected $table = 'school_board_action_items';
    protected $primaryKey = 'actionItemID';

    protected $fillable = [
        'schoolID',
        'meetingID',
        'resolutionID',
        'title',
        'description',
        'assigned_to',
        'due_date',
        'priority',
        'status',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(SchoolBoardMeeting::class, 'meetingID', 'meetingID');
    }

    public function resolution()
    {
        return $this->belongsTo(SchoolBoardResolution::class, 'resolutionID', 'resolutionID');
    }

    public function assignee()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'assigned_to', 'memberID');
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        if (in_array($this->status, ['Completed', 'Cancelled'], true)) {
            return false;
        }

        return $this->due_date->lt(now()->startOfDay());
    }
}
