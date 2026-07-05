<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreStockUnit extends Model
{
    protected $table = 'store_stock_units';
    protected $primaryKey = 'stock_unitID';

    protected $fillable = [
        'stock_entryID',
        'schoolID',
        'itemID',
        'store_method',
        'packageID',
        'package_specID',
        'unitID',
        'label',
        'requisition_status',
        'stock_status',
        'initial_items',
        'remaining_items',
    ];

    protected $casts = [
        'initial_items' => 'float',
        'remaining_items' => 'float',
    ];

    public function stockEntry()
    {
        return $this->belongsTo(StoreItemStockEntry::class, 'stock_entryID', 'stock_entryID');
    }

    public function item()
    {
        return $this->belongsTo(StoreItem::class, 'itemID', 'itemID');
    }

    public function package()
    {
        return $this->belongsTo(StorePackage::class, 'packageID', 'packageID');
    }

    public function unit()
    {
        return $this->belongsTo(StoreUnitOfMeasure::class, 'unitID', 'unitID');
    }

    public function requisition()
    {
        return $this->hasOne(StoreRequisition::class, 'stock_unitID', 'stock_unitID')
            ->where('status', 'issued');
    }
}
