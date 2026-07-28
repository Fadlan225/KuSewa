<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_chat_id',
        'sender_id',
        'is_read',
        'message_type',
        'message',
        'reply_to_id'
    ];

    public function roomChats(){
        return $this->belongsTo(room_chat::class);
    }

    public function sender(){
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(){
        return $this->hasMany(MessageAttachment::class);
    }

    public function replyTo()
    {
        return $this->belongsTo(message::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(message::class, 'reply_to_id');
    }
}
