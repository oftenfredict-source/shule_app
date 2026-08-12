<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentHomePackageNotification extends Model
{
    protected $table = 'parent_home_package_notifications';

    protected $fillable = [
        'schoolID',
        'parentID',
        'home_packageID',
        'is_viewed',
    ];

    protected $casts = [
        'is_viewed' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parentID', 'parentID');
    }

    public function homePackage()
    {
        return $this->belongsTo(HomePackage::class, 'home_packageID', 'home_packageID');
    }
}
