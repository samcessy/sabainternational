<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDonationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Donation $donation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->donation->amount_cents / 100, 2);
        $donor = $this->donation->anonymous ? 'Anonymous' : $this->donation->supporter->name;

        return (new MailMessage)
            ->subject("New {$this->donation->frequency->value} donation — \${$amount}")
            ->line("{$donor} just gave \${$amount} ({$this->donation->frequency->value}).");
    }
}
