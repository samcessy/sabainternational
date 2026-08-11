<?php

namespace App\Notifications;

use App\Models\VolunteerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVolunteerApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public VolunteerApplication $volunteerApplication) {}

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
            ->subject('New volunteer application')
            ->line("New application from {$this->volunteerApplication->name} ({$this->volunteerApplication->email}).")
            ->line($this->volunteerApplication->details ?? '');
    }
}
