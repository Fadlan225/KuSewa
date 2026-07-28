<?php

namespace App\Events;

use App\Models\message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $roomId;

    public function __construct(message $message, $roomId)
    {
        $this->message = $message;
        $this->roomId = $roomId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->roomId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'isEdited' => true,
            ]
        ];
    }
}
