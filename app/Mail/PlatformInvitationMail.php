<?php

namespace App\Mail;

use App\Models\PlatformInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlatformInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PlatformInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve been invited to join '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform-invitation',
        );
    }
}
