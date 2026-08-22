<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class OwnerVerificationStatusNotification extends Notification
{
    use Queueable;

    public $status;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($status, $message = null)
    {
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->status === 'verified' 
            ? 'Selamat! Pendaftaran Owner Anda Disetujui' 
            : 'Pemberitahuan Pendaftaran Owner';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.owner.verification-status', [
                'status' => $this->status,
                'reason' => $this->message,
                'date' => Carbon::now()->format('d M Y, H:i'),
                'notifiable' => $notifiable
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->status === 'verified' ? 'Pendaftaran Disetujui' : 'Pendaftaran Ditolak';
        
        return [
            'type' => 'owner_verification',
            'title' => $title,
            'message' => $this->status === 'verified' 
                ? 'Selamat! Akun Anda telah diverifikasi sebagai Owner.' 
                : 'Maaf, pendaftaran Anda belum disetujui. ' . ($this->message ?: 'Silakan cek email Anda untuk detail lebih lanjut.'),
            'status' => $this->status,
        ];
    }
}
