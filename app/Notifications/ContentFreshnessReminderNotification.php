<?php

namespace App\Notifications;

use App\Models\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentFreshnessReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, Story>  $staleStories
     */
    public function __construct(public Collection $staleStories) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Quarterly content freshness reminder')
            ->line('The following published stories have not been updated in over 3 years and may need review or re-verification (saba.md §16.3):');

        foreach ($this->staleStories as $story) {
            $editUrl = route('admin.stories.edit', $story);
            $message->line("- [{$story->title}]({$editUrl}) — last updated {$story->updated_at?->diffForHumans()}");
        }

        return $message->line('Reviewing and updating a story (even just confirming it\'s still accurate) resets this clock.');
    }
}
