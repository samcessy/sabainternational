<?php

use App\Enums\AdminRole;
use App\Enums\SubscriberStatus;
use App\Models\NewsletterSubscriber;
use Inertia\Testing\AssertableInertia as Assert;

test('an editor can view the newsletter subscribers index', function () {
    NewsletterSubscriber::factory()->create(['email' => 'subscriber@example.com']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.newsletter-subscribers.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/newsletter-subscribers/Index')
        ->has('subscribers.data', 1)
        ->where('subscribers.data.0.email', 'subscriber@example.com')
        ->where('totals.subscribed_count', 1)
    );
});

test('a viewer can view but not manage subscribers', function () {
    $subscriber = NewsletterSubscriber::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.newsletter-subscribers.index'))->assertOk();
    $this->actingAs($viewer)
        ->post(route('admin.newsletter-subscribers.unsubscribe', $subscriber))
        ->assertForbidden();
    $this->actingAs($viewer)
        ->delete(route('admin.newsletter-subscribers.destroy', $subscriber))
        ->assertForbidden();
});

test('a finance manager cannot view newsletter subscribers', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->get(route('admin.newsletter-subscribers.index'))->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.newsletter-subscribers.index'))->assertRedirect(route('login'));
});

test('an editor can unsubscribe a subscriber and it is audit logged', function () {
    $subscriber = NewsletterSubscriber::factory()->create(['status' => SubscriberStatus::Subscribed]);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.newsletter-subscribers.unsubscribe', $subscriber));

    $response->assertRedirect();
    $subscriber->refresh();
    expect($subscriber->status)->toBe(SubscriberStatus::Unsubscribed)
        ->and($subscriber->unsubscribed_at)->not->toBeNull();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'unsubscribe',
        'entity_type' => 'newsletter_subscriber',
        'entity_id' => $subscriber->id,
    ]);
});

test('an editor can delete a subscriber and it is audit logged', function () {
    $subscriber = NewsletterSubscriber::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.newsletter-subscribers.destroy', $subscriber));

    $response->assertRedirect();
    $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $subscriber->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'newsletter_subscriber',
        'entity_id' => $subscriber->id,
    ]);
});
