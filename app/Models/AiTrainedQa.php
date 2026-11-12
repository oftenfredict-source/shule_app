<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTrainedQa extends Model
{
    protected $table = 'ai_trained_qa';

    protected $fillable = [
        'question',
        'answer',
        'locale',
        'keywords',
        'priority',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
        'created_by' => 'integer',
    ];

    /**
     * @return list<string>
     */
    public function keywordList(): array
    {
        if (!$this->keywords) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($k) => trim(mb_strtolower($k)),
            explode(',', $this->keywords)
        )));
    }
}
