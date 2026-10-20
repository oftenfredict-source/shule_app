<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardMember extends Model
{
    use HasFactory;

    protected $table = 'school_board_members';
    protected $primaryKey = 'memberID';

    protected $fillable = [
        'schoolID',
        'positionID',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'phone',
        'email',
        'occupation',
        'organization',
        'national_id',
        'address',
        'appointed_date',
        'term_end_date',
        'status',
        'notes',
        'username',
        'user_id',
    ];

    protected $casts = [
        'appointed_date' => 'date',
        'term_end_date' => 'date',
    ];

    public function position()
    {
        return $this->belongsTo(SchoolBoardPosition::class, 'positionID', 'positionID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function committees()
    {
        return $this->belongsToMany(
            SchoolBoardCommittee::class,
            'school_board_committee_members',
            'memberID',
            'committeeID'
        )->withPivot(['id', 'committee_role', 'assigned_date', 'schoolID'])
            ->withTimestamps();
    }

    public function boardRoles()
    {
        return $this->belongsToMany(
            SchoolBoardRole::class,
            'school_board_member_roles',
            'memberID',
            'boardRoleID'
        )->withPivot(['id', 'schoolID'])->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    public function getPositionCodeAttribute(): string
    {
        return strtoupper((string) ($this->position->position_code ?? 'MEMBER'));
    }

    /**
     * Merged permission names from assigned board roles.
     *
     * @return list<string>
     */
    public function boardPermissionNames(): array
    {
        if (!$this->relationLoaded('boardRoles')) {
            $this->load(['boardRoles.permissions']);
        }

        $names = [];
        foreach ($this->boardRoles as $role) {
            if (!$role->is_active) {
                continue;
            }
            foreach ($role->permissions as $perm) {
                $names[] = (string) $perm->name;
            }
        }

        return array_values(array_unique($names));
    }

    public function hasBoardPermission(string $permission): bool
    {
        return in_array($permission, $this->boardPermissionNames(), true);
    }
}
