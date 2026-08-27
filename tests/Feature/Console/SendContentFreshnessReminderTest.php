<?php

use App\Enums\AdminRole;
use App\Enums\ContentStatus;
use App\Models\Story;
use App\Models\User;
use App\Notifications\ContentFreshnessReminderNotification;
use Illuminate\Support\Facades\Notification;

test('it notifies editors and super admins when stale content exists', function () {
    Notification::fake();

    $stale = Story::factory()->published()->create(['title' => 'Old Story']);
    $stale->forceFill(['updated_at' => now()->subYears(4)])->saveQuietly();

    $editor = User::factory()->create(['admin_role' => AdminRole::Editor]);
    $superAdmin = User::factory()->create(['admin_role' => AdminRole::SuperAdministrator]);
    $viewer = User::factory()->create(['admin_role' => AdminRole::Viewer]);
    $financeManager = User::factory()->create(['admin_role' => AdminRole::FinanceManager]);

    $this->artisan('content:freshness-reminder')->assertSuccessful();

    Notification::assertSentTo($editor, ContentFreshnessReminderNotification::class);
    Notification::assertSentTo($superAdmin, ContentFreshnessReminderNotification::class);
    Notification::assertNotSentTo($viewer, ContentFreshnessReminderNotification::class);
    Notification::assertNotSentTo($financeManager, ContentFreshnessReminderNotification::class);
});

test('it only counts published stories untouched for over 3 years', function () {
    Notification::fake();

    $stale = Story::factory()->published()->create(['title' => 'Old Story']);
    $stale->forceFill(['updated_at' => now()->subYears(4)])->saveQuietly();

    $fresh = Story::factory()->published()->create(['title' => 'Fresh Story']);

    $staleDraft = Story::factory()->create(['title' => 'Old Draft', 'status' => ContentStatus::Draft]);
    $staleDraft->forceFill(['updated_at' => now()->subYears(4)])->saveQuietly();

    $editor = User::factory()->create(['admin_role' => AdminRole::Editor]);

    $this->artisan('content:freshness-reminder');

    Notification::assertSentTo(
        $editor,
        function (ContentFreshnessReminderNotification $notification) use ($fresh) {
            $titles = $notification->staleStories->pluck('title');

            return $titles->contains('Old Story')
                && ! $titles->contains($fresh->title)
                && ! $titles->contains('Old Draft');
        }
    );
});

test('it sends nothing when there is no stale content', function () {
    Notification::fake();

    Story::factory()->published()->create();
    User::factory()->create(['admin_role' => AdminRole::Editor]);

    $this->artisan('content:freshness-reminder')->assertSuccessful();

    Notification::assertNothingSent();
});
