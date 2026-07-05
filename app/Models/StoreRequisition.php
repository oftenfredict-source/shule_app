<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequisition extends Model
{
    protected $table = 'store_requisitions';
    protected $primaryKey = 'requisitionID';

    protected $fillable = [
        'schoolID',
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
        'quantity_taken',
        'quantity_returned',
        'recipient_type',
        'recipient_id',
        'recipient_name',
        'requisition_status',
        'expected_return_date',
        'status',
        'issued_by',
        'notes',
        'issued_at',
        'returned_at',
        'return_description',
        'return_condition',
        'bad_description',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
        'expected_return_date' => 'date',
        'quantity_taken' => 'float',
        'quantity_returned' => 'float',
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
