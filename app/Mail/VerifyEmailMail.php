<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $firstName,
        public string $verifyUrl,
        public bool $isNewEmail = false
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isNewEmail
            ? 'Verify your new email address — ' . config('app.name')
            : 'Verify your email — ' . config('app.name');

        return new Envelope(
            subject: $subject,
            to: [$this->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
        );
    }
}
