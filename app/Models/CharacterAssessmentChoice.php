<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterAssessmentChoice extends Model
{
    use HasFactory;

    protected $table = 'character_assessment_choices';
    protected $primaryKey = 'choiceID';

    protected $fillable = [
        'schoolID',
        'choice_name',
        'weight'
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}
