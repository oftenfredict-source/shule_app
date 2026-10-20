<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardRolePermission extends Model
{
    use HasFactory;

    protected $table = 'school_board_role_permissions';

    protected $fillable = [
        'boardRoleID',
        'name',
        'permission_category',
    ];

    public function role()
    {
        return $this->belongsTo(SchoolBoardRole::class, 'boardRoleID', 'boardRoleID');
    }
}
