<?php

namespace App\Events;

use App\Models\message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatMessage;
    public $roomId;

    /**
     * Create a new event instance.
     */
    public function __construct(message $chatMessage, $roomId)
    {
        $this->chatMessage = $chatMessage;
        $this->roomId = $roomId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->roomId),
        ];
    }

    public function broadcastWith(): array
    {
        $hasAttachments = $this->chatMessage->attachments && $this->chatMessage->attachments->count() > 0;
        $attachments = $hasAttachments ? $this->chatMessage->attachments->map(function($att) {
            return [
                'id' => $att->id,
                'file_url' => asset('storage/' . $att->file_path),
                'file_name' => basename($att->file_path)
            ];
        })->toArray() : [];

        $isOldImage = $this->chatMessage->message_type === 'image' && !$hasAttachments;
        $fileUrl = $isOldImage ? asset('storage/' . $this->chatMessage->message) : null;
        $fileName = $isOldImage ? basename($this->chatMessage->message) : null;

        $text = ($this->chatMessage->message_type === 'text' || $hasAttachments) ? $this->chatMessage->message : null;
        if ($hasAttachments && !empty($attachments) && $text === $this->chatMessage->attachments->first()->file_path) {
            $text = null;
        }

        return [
            'id' => $this->chatMessage->id,
            'message' => $text,
            'type' => $this->chatMessage->message_type,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'attachments' => $attachments,
            'sender_id' => $this->chatMessage->sender_id,
            'is_read' => $this->chatMessage->is_read,
            'created_at' => $this->chatMessage->created_at->format('H:i'),
            'room_chat_id' => $this->chatMessage->room_chat_id,
            'isEdited' => false,
            'isDeleted' => false,
            'replyTo' => $this->chatMessage->replyTo ? [
                'id' => $this->chatMessage->replyTo->id,
                'text' => $this->chatMessage->replyTo->trashed() ? 'Pesan ini telah dihapus' : ($this->chatMessage->replyTo->message_type === 'image' ? 'Foto' : ($this->chatMessage->replyTo->message_type === 'file' ? 'File' : $this->chatMessage->replyTo->message)),
                'sender_name' => $this->chatMessage->replyTo->sender->name ?? 'Lawan bicara',
                'isSelf' => false // For receiver, they will parse this
            ] : null
        ];
    }
}
