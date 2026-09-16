<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeOTP extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $type;
    public $newEmail;
    public $ipAddress;
    public $device;
    public $date;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $type, $newEmail = null, $ipAddress = null, $device = null, $date = null)
    {
        $this->otp = $otp;
        $this->type = $type;
        $this->newEmail = $newEmail;
        $this->ipAddress = $ipAddress ?: request()->ip();
        $this->device = $device ?: request()->header('User-Agent');
        $this->date = $date ?: now()->format('d M Y, H:i');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode OTP Verifikasi Ubah Email - KitaSewa',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change-otp',
            with: [
                'otp' => $this->otp,
                'type' => $this->type,
                'newEmail' => $this->newEmail,
                'ipAddress' => $this->ipAddress,
                'device' => $this->device,
                'date' => $this->date,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
