<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorePackageCategory extends Model
{
    protected $table = 'store_package_categories';
    protected $primaryKey = 'package_categoryID';

    protected $fillable = [
        'schoolID',
        'category_name',
        'status',
    ];

    public function packages()
    {
        return $this->hasMany(StorePackage::class, 'package_categoryID', 'package_categoryID')
            ->where('status', 'Active')
            ->orderBy('package_name');
    }
}
