<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorePackage extends Model
{
    protected $table = 'store_packages';
    protected $primaryKey = 'packageID';

    protected $fillable = [
        'schoolID',
        'package_categoryID',
        'package_name',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(StorePackageCategory::class, 'package_categoryID', 'package_categoryID');
    }

    public function itemSpecs()
    {
        return $this->hasMany(StoreItemPackageSpec::class, 'packageID', 'packageID');
    }
}
