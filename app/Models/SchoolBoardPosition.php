<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardPosition extends Model
{
    use HasFactory;

    protected $table = 'school_board_positions';
    protected $primaryKey = 'positionID';

    protected $fillable = [
        'schoolID',
        'position_name',
        'position_code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function members()
    {
        return $this->hasMany(SchoolBoardMember::class, 'positionID', 'positionID');
    }
}
