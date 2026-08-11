<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class NewsletterSubscriptionConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public NewsletterSubscriber $newsletterSubscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're subscribed — Saba International",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter-subscription-confirmation',
            with: [
                'unsubscribeUrl' => URL::signedRoute('newsletter.unsubscribe', [
                    'newsletterSubscriber' => $this->newsletterSubscriber->id,
                ]),
            ],
        );
    }
}
