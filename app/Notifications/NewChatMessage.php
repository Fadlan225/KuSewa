<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class NewChatMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $roomId,
        public readonly string $senderName,
        public readonly string $messagePreview, // Potongan 60 karakter pertama pesan
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'          => "Pesan dari {$this->senderName}",
            'message'        => $this->messagePreview,
            'action_url'     => "/chat/{$this->roomId}",
            'room_id'        => $this->roomId,
            'sender_name'    => $this->senderName,
            'type'           => 'chat_message', // Kunci untuk fitur Quick Reply di frontend
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        $data = $this->toDatabase($notifiable);
        return (new WebPushMessage)
            ->title($data['title'])
            ->body($data['message'])
            ->icon('/kitasewa-logo.png')
            ->action('Balas', $data['action_url']);
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
