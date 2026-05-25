<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $activationLink;

    public function __construct($user, $activationLink)
    {
        $this->user = $user;
        $this->activationLink = $activationLink;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aktivasi Akun HRIS Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation', // Kita akan buat file view ini di langkah selanjutnya
        );
    }
}