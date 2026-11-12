<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    protected $table = 'ai_conversations';

    protected $fillable = [
        'schoolID',
        'user_id',
        'user_type',
        'guest_token',
        'locale',
        'title',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'schoolID' => 'integer',
        'user_id' => 'integer',
        'last_message_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id')->orderBy('id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(AiSupportFeedback::class, 'conversation_id');
    }
}
