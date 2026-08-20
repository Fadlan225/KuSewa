<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class NewBookingReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $bookingId,
        public readonly string $assetName,
        public readonly string $renterName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'       => 'Booking Baru Masuk! 🔔',
            'message'     => "\"{$this->renterName}\" memesan aset \"{$this->assetName}\" Anda. Segera tinjau pesanan.",
            'action_url'  => "/owner/bookings/{$this->bookingId}",
            'booking_id'  => $this->bookingId,
            'type'        => 'new_booking',
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
            ->action('Lihat Pesanan', $data['action_url']);
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
