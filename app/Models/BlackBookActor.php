<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackBookActor extends Model
{
    protected $table = 'black_book_actors';
    protected $primaryKey = 'actorID';

    protected $fillable = [
        'schoolID',
        'actor_type',
        'actor_ref_id',
        'actor_name',
        'privileges',
        'status',
        'granted_by',
    ];

    protected $casts = [
        'privileges' => 'array',
    ];

    public static function defaultPrivileges(): array
    {
        return [
            'view_dashboard' => false,
            'manage_problem_categories' => false,
            'manage_punishment_categories' => false,
            'manage_pending' => false,
            'view_resolved' => false,
            'view_reports' => false,
        ];
    }

    public static function privilegeLabels(): array
    {
        return [
            'view_dashboard' => 'View Dashboard',
            'manage_problem_categories' => 'Manage Problem Categories',
            'manage_punishment_categories' => 'Manage Punishment Categories',
            'manage_pending' => 'View & Resolve Pending Problems',
            'view_resolved' => 'View Resolved Problems',
            'view_reports' => 'View Reports & Download PDF',
        ];
    }

    public static function adminPrivileges(): array
    {
        return array_fill_keys(array_keys(self::defaultPrivileges()), true);
    }
}
