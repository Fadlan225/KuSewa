<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class BookingStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $bookingId,
        public readonly string $assetName,
        public readonly string $status, // 'approved' | 'rejected' | 'cancelled'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        $messages = [
            'approved'  => ['title' => 'Booking Disetujui! 🎉', 'message' => "Booking Anda untuk \"{$this->assetName}\" telah disetujui oleh pemilik."],
            'rejected'  => ['title' => 'Booking Ditolak', 'message' => "Maaf, booking Anda untuk \"{$this->assetName}\" ditolak oleh pemilik."],
            'cancelled' => ['title' => 'Booking Dibatalkan', 'message' => "Booking Anda untuk \"{$this->assetName}\" telah dibatalkan."],
        ];

        $content = $messages[$this->status] ?? ['title' => 'Status Booking Berubah', 'message' => "Status booking Anda untuk \"{$this->assetName}\" telah diperbarui."];

        return [
            'title'      => $content['title'],
            'message'    => $content['message'],
            'action_url' => "/aktivitas/transaksi",
            'booking_id' => $this->bookingId,
            'type'       => 'booking_status',
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
            ->action('Lihat Detail', $data['action_url']);
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
