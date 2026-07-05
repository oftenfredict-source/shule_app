<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItemStockEntry extends Model
{
    protected $table = 'store_item_stock_entries';
    protected $primaryKey = 'stock_entryID';

    protected $fillable = [
        'schoolID',
        'itemID',
        'store_method',
        'packageID',
        'unitID',
        'content_per_unit',
        'quantity',
        'requisition_status',
        'status',
    ];

    protected $casts = [
        'content_per_unit' => 'float',
        'quantity' => 'integer',
    ];

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

    public function stockUnits()
    {
        return $this->hasMany(StoreStockUnit::class, 'stock_entryID', 'stock_entryID');
    }

    public function availableStockUnits()
    {
        return $this->hasMany(StoreStockUnit::class, 'stock_entryID', 'stock_entryID')
            ->where('stock_status', 'in_stock');
    }
}
