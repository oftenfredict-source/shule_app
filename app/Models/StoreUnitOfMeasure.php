<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreUnitOfMeasure extends Model
{
    protected $table = 'store_units_of_measure';
    protected $primaryKey = 'unitID';

    protected $fillable = [
        'measurement_typeID',
        'unit_name',
        'abbreviation',
        'is_system',
        'status',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function measurementType()
    {
        return $this->belongsTo(StoreMeasurementType::class, 'measurement_typeID', 'measurement_typeID');
    }

    public function items()
    {
        return $this->hasMany(StoreItem::class, 'unitID', 'unitID');
    }
}
