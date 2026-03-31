<?php

namespace App\Mail;

use App\Models\BoardInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoardInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BoardInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->invitation->inviter->name . ' invited you to "' . $this->invitation->board->name . '" on FlowBoard',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.board-invitation',
        );
    }
}
