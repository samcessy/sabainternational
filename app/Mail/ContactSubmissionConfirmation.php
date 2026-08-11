<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactSubmissionConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactSubmission $contactSubmission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your message — Saba International',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-submission-confirmation',
        );
    }
}
