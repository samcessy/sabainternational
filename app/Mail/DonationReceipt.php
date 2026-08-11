<?php

namespace App\Mail;

use App\Models\Donation;
use App\Models\DonationTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Donation $donation,
        public DonationTransaction $transaction,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your donation receipt — Saba International',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.donation-receipt',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $donation = $this->donation;
        $transaction = $this->transaction;

        return [
            Attachment::fromData(
                fn () => Pdf::loadView('pdf.donation-receipt', [
                    'donation' => $donation,
                    'transaction' => $transaction,
                ])->output(),
                'receipt.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
