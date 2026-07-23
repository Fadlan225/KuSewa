<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyOwnerEmail extends Notification
{
    use Queueable;

    public function __construct()
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Buat Signed URL yang aman & berlaku selama 60 menit
        $verificationUrl = URL::temporarySignedRoute(
            'owner.register.verify',
            now()->addMinutes(60),
            ['id' => $notifiable->id]
        );

        return (new MailMessage)
            ->subject('Konfirmasi Pendaftaran Owner - kusewa.id')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Terima kasih telah mendaftar sebagai Owner di kusewa.id.')
            ->line('Silakan klik tombol di bawah ini untuk mengonfirmasi email dan menyelesaikan pendaftaran akun Owner kamu:')
            ->action('Konfirmasi Email Owner', $verificationUrl)
            ->line('Link konfirmasi ini hanya berlaku selama 60 menit.')
            ->line('Jika kamu tidak pernah melakukan pengajuan ini, silakan abaikan email ini.');
    }
}