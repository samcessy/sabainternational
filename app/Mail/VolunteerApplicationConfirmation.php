<?php

namespace App\Mail;

use App\Models\VolunteerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VolunteerApplicationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public VolunteerApplication $volunteerApplication) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thanks for your interest in volunteering — Saba International',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.volunteer-application-confirmation',
        );
    }
}
