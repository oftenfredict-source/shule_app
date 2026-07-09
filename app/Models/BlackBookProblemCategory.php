<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackBookProblemCategory extends Model
{
    protected $table = 'black_book_problem_categories';
    protected $primaryKey = 'categoryID';

    protected $fillable = [
        'schoolID',
        'name',
        'weight',
        'status',
    ];

    public function reports()
    {
        return $this->hasMany(BlackBookReport::class, 'problem_categoryID', 'categoryID');
    }
}
