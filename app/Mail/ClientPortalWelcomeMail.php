<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\ClientPortalUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientPortalWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public ClientPortalUser $portalUser,
        public string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your client portal access — '.$this->client->organization->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-portal-welcome',
            with: [
                'client' => $this->client,
                'portalUser' => $this->portalUser,
                'plainPassword' => $this->plainPassword,
            ],
        );
    }
}
