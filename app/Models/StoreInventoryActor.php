<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreInventoryActor extends Model
{
    protected $table = 'store_inventory_actors';
    protected $primaryKey = 'actorID';

    protected $fillable = [
        'schoolID',
        'actor_type',
        'actor_ref_id',
        'actor_name',
        'category_scope',
        'category_ids',
        'privileges',
        'status',
        'granted_by',
    ];

    protected $casts = [
        'category_ids' => 'array',
        'privileges' => 'array',
    ];

    public static function defaultPrivileges(): array
    {
        return [
            'register_items' => false,
            'manage_stock' => false,
            'issue_items' => false,
            'approve_requests' => false,
            'manage_incidents' => false,
            'manage_categories' => false,
            'manage_units' => false,
            'manage_packages' => false,
            'view_reports' => false,
        ];
    }

    public static function privilegeLabels(): array
    {
        return [
            'register_items' => 'Register / Add Items',
            'manage_stock' => 'Manage Item Stock',
            'issue_items' => 'Issue Items (Requisition)',
            'approve_requests' => 'Approve Item Requests',
            'manage_incidents' => 'Manage Damaged / Lost',
            'manage_categories' => 'Manage Categories',
            'manage_units' => 'Manage Units of Measure',
            'manage_packages' => 'Manage Packages',
            'view_reports' => 'View Reports',
        ];
    }

    public static function adminPrivileges(): array
    {
        return array_fill_keys(array_keys(self::defaultPrivileges()), true);
    }
}
