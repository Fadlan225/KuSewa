<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthOTP extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $magicLink;
    public string $purposeStr;
    public string $ipAddress;
    public string $device;
    public string $date;
    
    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, string $magicLink, string $purposeStr, string $ipAddress = 'Unknown', string $device = 'Unknown Device')
    {
        $this->otp = $otp;
        $this->magicLink = $magicLink;
        $this->purposeStr = $purposeStr;
        $this->ipAddress = $ipAddress;
        $this->device = $device;
        $this->date = \Carbon\Carbon::now()->translatedFormat('l, d F Y H:i');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi KitaSewa Anda',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
