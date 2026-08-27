<?php

namespace App\Console\Commands;

use App\Enums\AdminPermission;
use App\Enums\ContentStatus;
use App\Models\Story;
use App\Models\User;
use App\Notifications\ContentFreshnessReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * saba.md §16.3 — "Quarterly content audit reminder sent to editors" and
 * §3.3's content sustainability mandate. Scheduled quarterly in
 * routes/console.php. The 3-year staleness threshold matches
 * DashboardController's own content-freshness alert so the email and the
 * admin dashboard never disagree about what counts as stale.
 */
class SendContentFreshnessReminder extends Command
{
    protected $signature = 'content:freshness-reminder';

    protected $description = 'Email editors a reminder listing published stories that have not been updated in 3+ years';

    public function handle(): int
    {
        $staleStories = Story::query()
            ->where('status', ContentStatus::Published)
            ->where('updated_at', '<', now()->subYears(3))
            ->orderBy('updated_at')
            ->get(['id', 'title', 'slug', 'updated_at']);

        if ($staleStories->isEmpty()) {
            $this->info('No stale content to report.');

            return self::SUCCESS;
        }

        Notification::send(
            User::withPermission(AdminPermission::ManageContent),
            new ContentFreshnessReminderNotification($staleStories),
        );

        $this->info("Sent a content freshness reminder listing {$staleStories->count()} stale stories.");

        return self::SUCCESS;
    }
}
