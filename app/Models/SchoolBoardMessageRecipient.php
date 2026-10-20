<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardMessageRecipient extends Model
{
    use HasFactory;

    protected $table = 'school_board_message_recipients';

    protected $fillable = [
        'schoolID',
        'messageID',
        'memberID',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(SchoolBoardMessage::class, 'messageID', 'messageID');
    }

    public function member()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'memberID', 'memberID');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
