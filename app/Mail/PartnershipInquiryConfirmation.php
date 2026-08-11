<?php

namespace App\Mail;

use App\Models\PartnershipInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnershipInquiryConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public PartnershipInquiry $partnershipInquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thanks for reaching out — Saba International',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.partnership-inquiry-confirmation',
        );
    }
}
