<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItemPackageSpec extends Model
{
    protected $table = 'store_item_package_specs';
    protected $primaryKey = 'specID';

    protected $fillable = [
        'itemID',
        'parent_specID',
        'packageID',
        'has_sub_package',
        'unitID',
        'quantity',
        'content_quantity',
    ];

    protected $casts = [
        'quantity' => 'float',
        'content_quantity' => 'float',
        'has_sub_package' => 'boolean',
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

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_specID', 'specID');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_specID', 'specID')->orderBy('specID');
    }
}
