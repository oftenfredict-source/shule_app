<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardCommitteeMember extends Model
{
    use HasFactory;

    protected $table = 'school_board_committee_members';
    protected $primaryKey = 'id';

    protected $fillable = [
        'schoolID',
        'committeeID',
        'memberID',
        'committee_role',
        'assigned_date',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

    public function committee()
    {
        return $this->belongsTo(SchoolBoardCommittee::class, 'committeeID', 'committeeID');
    }

    public function member()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'memberID', 'memberID');
    }
}
