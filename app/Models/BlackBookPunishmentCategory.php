<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackBookPunishmentCategory extends Model
{
    protected $table = 'black_book_punishment_categories';
    protected $primaryKey = 'punishmentCategoryID';

    protected $fillable = [
        'schoolID',
        'name',
        'punishment_type',
        'requires_monitoring',
        'status',
    ];

    protected $casts = [
        'requires_monitoring' => 'boolean',
    ];

    public function reports()
    {
        return $this->hasMany(BlackBookReport::class, 'punishment_categoryID', 'punishmentCategoryID');
    }
}
