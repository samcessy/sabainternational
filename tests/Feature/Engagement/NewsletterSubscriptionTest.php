<?php

use App\Enums\SubscriberStatus;
use App\Mail\NewsletterSubscriptionConfirmation;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

function validNewsletterPayload(array $overrides = []): array
{
    return array_merge([
        'email' => 'subscriber@example.com',
        'consent' => true,
    ], $overrides);
}

test('a valid newsletter subscription is stored with consent metadata and confirmed', function () {
    Mail::fake();

    $response = $this->post(route('newsletter.subscribe'), validNewsletterPayload());

    $response->assertRedirect();
    $subscriber = NewsletterSubscriber::query()->where('email', 'subscriber@example.com')->firstOrFail();
    expect($subscriber->status)->toBe(SubscriberStatus::Subscribed)
        ->and($subscriber->consent_timestamp)->not->toBeNull()
        ->and($subscriber->consent_ip)->not->toBeNull();
    Mail::assertQueued(NewsletterSubscriptionConfirmation::class);
});

test('newsletter subscription requires consent', function () {
    $response = $this->post(route('newsletter.subscribe'), validNewsletterPayload(['consent' => false]));

    $response->assertSessionHasErrors('consent');
    $this->assertDatabaseCount('newsletter_subscribers', 0);
});

test('resubscribing after unsubscribing reactivates the same record', function () {
    Mail::fake();
    $existing = NewsletterSubscriber::factory()->create([
        'email' => 'subscriber@example.com',
        'status' => SubscriberStatus::Unsubscribed,
        'unsubscribed_at' => now(),
    ]);

    $this->post(route('newsletter.subscribe'), validNewsletterPayload());

    expect(NewsletterSubscriber::query()->count())->toBe(1);
    expect($existing->fresh()->status)->toBe(SubscriberStatus::Subscribed)
        ->and($existing->fresh()->unsubscribed_at)->toBeNull();
});

test('a honeypot-triggered newsletter subscription is silently discarded', function () {
    Mail::fake();

    $response = $this->post(route('newsletter.subscribe'), validNewsletterPayload(['website' => 'https://spam.example']));

    $response->assertRedirect();
    $this->assertDatabaseCount('newsletter_subscribers', 0);
    Mail::assertNothingQueued();
});

test('the unsubscribe link sets status to unsubscribed', function () {
    $subscriber = NewsletterSubscriber::factory()->create(['status' => SubscriberStatus::Subscribed]);

    $url = URL::signedRoute('newsletter.unsubscribe', ['newsletterSubscriber' => $subscriber->id]);

    $this->get($url)->assertRedirect();

    expect($subscriber->fresh()->status)->toBe(SubscriberStatus::Unsubscribed)
        ->and($subscriber->fresh()->unsubscribed_at)->not->toBeNull();
});

test('an unsigned unsubscribe url is rejected', function () {
    $subscriber = NewsletterSubscriber::factory()->create();

    $this->get("/newsletter/unsubscribe/{$subscriber->id}")->assertForbidden();

    expect($subscriber->fresh()->status)->toBe(SubscriberStatus::Subscribed);
});
