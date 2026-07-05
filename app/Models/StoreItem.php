<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItem extends Model
{
    protected $table = 'store_items';
    protected $primaryKey = 'itemID';

    protected $fillable = [
        'schoolID',
        'item_name',
        'categoryID',
        'measurement_typeID',
        'unitID',
        'has_packages',
        'status',
    ];

    protected $casts = [
        'has_packages' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(StoreItemCategory::class, 'categoryID', 'categoryID');
    }

    public function measurementType()
    {
        return $this->belongsTo(StoreMeasurementType::class, 'measurement_typeID', 'measurement_typeID');
    }

    /** @deprecated Use units() — kept for legacy single-unit reference */
    public function unit()
    {
        return $this->belongsTo(StoreUnitOfMeasure::class, 'unitID', 'unitID');
    }

    public function units()
    {
        return $this->belongsToMany(
            StoreUnitOfMeasure::class,
            'store_item_units',
            'itemID',
            'unitID',
            'itemID',
            'unitID'
        )->orderBy('store_units_of_measure.unit_name');
    }

    public function packageSpecs()
    {
        return $this->hasMany(StoreItemPackageSpec::class, 'itemID', 'itemID')
            ->with(['package', 'unit']);
    }

    public function stockUnits()
    {
        return $this->hasMany(StoreStockUnit::class, 'itemID', 'itemID');
    }

    public function requisitions()
    {
        return $this->hasMany(StoreRequisition::class, 'itemID', 'itemID');
    }
}
