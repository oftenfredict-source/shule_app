<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItemIncident extends Model
{
    protected $table = 'store_item_incidents';
    protected $primaryKey = 'incidentID';

    protected $fillable = [
        'schoolID',
        'incident_type',
        'stock_unitID',
        'itemID',
        'label',
        'store_method',
        'packageID',
        'unitID',
        'package_unit_display',
        'take_level',
        'take_packageID',
        'take_display',
        'quantity_affected',
        'responsible_type',
        'responsible_id',
        'responsible_name',
        'reason',
        'payment_status',
        'payment_amount',
        'resolve_status',
        'resolve_method',
        'resolved_at',
        'resolved_by',
        'requisitionID',
        'recorded_by',
        'notes',
        'status',
    ];

    protected $casts = [
        'quantity_affected' => 'float',
        'payment_amount' => 'float',
        'resolved_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(StoreItem::class, 'itemID', 'itemID');
    }

    public function stockUnit()
    {
        return $this->belongsTo(StoreStockUnit::class, 'stock_unitID', 'stock_unitID');
    }

    public function package()
    {
        return $this->belongsTo(StorePackage::class, 'packageID', 'packageID');
    }

    public function unit()
    {
        return $this->belongsTo(StoreUnitOfMeasure::class, 'unitID', 'unitID');
    }
}
