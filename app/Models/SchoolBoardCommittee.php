<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardCommittee extends Model
{
    use HasFactory;

    protected $table = 'school_board_committees';
    protected $primaryKey = 'committeeID';

    protected $fillable = [
        'schoolID',
        'committee_name',
        'committee_code',
        'description',
        'status',
        'formed_date',
    ];

    protected $casts = [
        'formed_date' => 'date',
    ];

    public function members()
    {
        return $this->belongsToMany(
            SchoolBoardMember::class,
            'school_board_committee_members',
            'committeeID',
            'memberID'
        )->withPivot(['id', 'committee_role', 'assigned_date', 'schoolID'])
            ->withTimestamps();
    }

    public function committeeMembers()
    {
        return $this->hasMany(SchoolBoardCommitteeMember::class, 'committeeID', 'committeeID');
    }

    public function meetings()
    {
        return $this->hasMany(SchoolBoardMeeting::class, 'committeeID', 'committeeID');
    }
}
