<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSupportFeedback extends Model
{
    protected $table = 'ai_support_feedback';

    protected $fillable = [
        'conversation_id',
        'message_id',
        'rating',
        'comment',
        'user_id',
        'schoolID',
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'message_id' => 'integer',
        'rating' => 'integer',
        'user_id' => 'integer',
        'schoolID' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(AiMessage::class, 'message_id');
    }
}
