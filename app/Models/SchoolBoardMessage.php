<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardMessage extends Model
{
    use HasFactory;

    protected $table = 'school_board_messages';
    protected $primaryKey = 'messageID';

    protected $fillable = [
        'schoolID',
        'sender_memberID',
        'parent_messageID',
        'subject',
        'body',
        'audience',
        'recipient_count',
    ];

    public function sender()
    {
        return $this->belongsTo(SchoolBoardMember::class, 'sender_memberID', 'memberID');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_messageID', 'messageID');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_messageID', 'messageID');
    }

    public function recipients()
    {
        return $this->hasMany(SchoolBoardMessageRecipient::class, 'messageID', 'messageID');
    }

    public function recipientMembers()
    {
        return $this->belongsToMany(
            SchoolBoardMember::class,
            'school_board_message_recipients',
            'messageID',
            'memberID'
        )->withPivot(['id', 'schoolID', 'read_at'])
            ->withTimestamps();
    }
}
