<?php

namespace App\Notifications;

use App\Models\PartnershipInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPartnershipInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PartnershipInquiry $partnershipInquiry) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New partnership inquiry — '.$this->partnershipInquiry->organization_name)
            ->line("New inquiry from {$this->partnershipInquiry->contact_name} at {$this->partnershipInquiry->organization_name} ({$this->partnershipInquiry->email}).")
            ->line($this->partnershipInquiry->details ?? '');
    }
}
