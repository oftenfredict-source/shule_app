<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreMeasurementType extends Model
{
    protected $table = 'store_measurement_types';
    protected $primaryKey = 'measurement_typeID';

    protected $fillable = [
        'schoolID',
        'type_name',
        'is_system',
        'status',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function units()
    {
        return $this->hasMany(StoreUnitOfMeasure::class, 'measurement_typeID', 'measurement_typeID')
            ->where('status', 'Active')
            ->orderBy('unit_name');
    }

    public function items()
    {
        return $this->hasMany(StoreItem::class, 'measurement_typeID', 'measurement_typeID');
    }
}
