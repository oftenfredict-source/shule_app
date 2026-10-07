<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardResolution extends Model
{
    use HasFactory;

    protected $table = 'school_board_resolutions';
    protected $primaryKey = 'resolutionID';

    protected $fillable = [
        'schoolID',
        'meetingID',
        'resolution_number',
        'title',
        'description',
        'proposed_by',
        'seconded_by',
        'assigned_to',
        'resolution_date',
        'implementation_deadline',
        'status',
        'notes',
    ];

    protected $casts = [
        'resolution_date' => 'date',
        'implementation_deadline' => 'date',
    ];

    public function meeting()
    {
        return $this->belongsTo(SchoolBoardMeeting::class, 'meetingID', 'meetingID');
    }

    public function proposer()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'proposed_by', 'memberID');
    }

    public function seconder()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'seconded_by', 'memberID');
    }

    public function assignee()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'assigned_to', 'memberID');
    }

    public function actionItems()
    {
        return $this->hasMany(SchoolBoardActionItem::class, 'resolutionID', 'resolutionID');
    }
}
