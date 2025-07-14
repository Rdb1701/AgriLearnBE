<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendStudentsEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $classroomName;
    public $inviteLink;

    /**
     * Create a new message instance.
     */
    public function __construct($classroomName, $inviteLink)
    {
        $this->classroomName = $classroomName;
        $this->inviteLink = $inviteLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join {$this->classroomName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invite-student', // We'll use markdown format
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
