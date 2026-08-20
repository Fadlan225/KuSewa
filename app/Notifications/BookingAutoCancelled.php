<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class BookingAutoCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $bookingId,
        public readonly string $assetName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'      => 'Booking Dibatalkan Otomatis',
            'message'    => "Booking Anda untuk \"{$this->assetName}\" telah dibatalkan secara otomatis karena melewati batas waktu pembayaran.",
            'action_url' => "/aktivitas/transaksi",
            'booking_id' => $this->bookingId,
            'type'       => 'booking_auto_cancelled',
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
            ->action('Lihat Aktivitas', $data['action_url']);
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
