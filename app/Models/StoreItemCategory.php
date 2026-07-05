<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItemCategory extends Model
{
    protected $table = 'store_item_categories';
    protected $primaryKey = 'categoryID';

    protected $fillable = [
        'schoolID',
        'category_name',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(StoreItem::class, 'categoryID', 'categoryID');
    }
}
