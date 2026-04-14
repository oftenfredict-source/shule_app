<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterAssessmentCategory extends Model
{
    use HasFactory;

    protected $table = 'character_assessment_categories';
    protected $primaryKey = 'categoryID';

    protected $fillable = [
        'schoolID',
        'name',
        'type'
    ];

    public function choices()
    {
        return $this->hasMany(CharacterAssessmentChoice::class, 'categoryID', 'categoryID');
    }
}
