<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLibrairieNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $librairie;
    public $userName;
    /**
     * Create a new message instance.
     */
    public function __construct($librairie, $userName)
    {
        //
        $this->librairie = $librairie;
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau Document Disponible dans la Bibliothèque Numérique',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.librairie-notification',
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
