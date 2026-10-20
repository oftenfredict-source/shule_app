<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardRole extends Model
{
    use HasFactory;

    protected $table = 'school_board_roles';
    protected $primaryKey = 'boardRoleID';

    protected $fillable = [
        'schoolID',
        'role_name',
        'role_code',
        'description',
        'is_system',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function permissions()
    {
        return $this->hasMany(SchoolBoardRolePermission::class, 'boardRoleID', 'boardRoleID');
    }

    public function members()
    {
        return $this->belongsToMany(
            SchoolBoardMember::class,
            'school_board_member_roles',
            'boardRoleID',
            'memberID'
        )->withPivot(['id', 'schoolID'])->withTimestamps();
    }
}
