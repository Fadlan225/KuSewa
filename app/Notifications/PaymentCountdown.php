<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

/**
 * Notifikasi pengingat countdown pembayaran untuk penyewa.
 * Dipakai untuk peringatan 10 menit dan 5 menit.
 */
class PaymentCountdown extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $bookingId,
        public readonly string $assetName,
        public readonly int    $minutesLeft, // 10 atau 5
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'      => "⏰ Segera Bayar! Sisa {$this->minutesLeft} Menit",
            'message'    => "Booking Anda untuk \"{$this->assetName}\" akan dibatalkan otomatis jika tidak dibayar dalam {$this->minutesLeft} menit.",
            'action_url' => "/aktivitas/transaksi",
            'booking_id' => $this->bookingId,
            'type'       => 'payment_countdown',
            'minutes_left' => $this->minutesLeft,
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
            ->action('Bayar Sekarang', $data['action_url']);
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
