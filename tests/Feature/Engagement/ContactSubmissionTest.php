<?php

use App\Enums\AdminRole;
use App\Mail\ContactSubmissionConfirmation;
use App\Models\User;
use App\Notifications\NewContactSubmissionNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

function validContactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Donor',
        'email' => 'jane@example.com',
        'subject' => 'general',
        'message' => 'This is a long enough message to pass validation.',
        'consent' => true,
    ], $overrides);
}

test('a valid contact submission is stored, confirmed, and notified', function () {
    Mail::fake();
    Notification::fake();
    $admin = User::factory()->create(['admin_role' => AdminRole::SuperAdministrator]);

    $response = $this->post(route('contact.store'), validContactPayload());

    $response->assertRedirect();
    $this->assertDatabaseHas('contact_submissions', ['email' => 'jane@example.com']);
    Mail::assertQueued(ContactSubmissionConfirmation::class);
    Notification::assertSentTo($admin, NewContactSubmissionNotification::class);
});

test('contact submission requires consent', function () {
    $response = $this->post(route('contact.store'), validContactPayload(['consent' => false]));

    $response->assertSessionHasErrors('consent');
    $this->assertDatabaseCount('contact_submissions', 0);
});

test('contact submission requires a message of at least 20 characters', function () {
    $response = $this->post(route('contact.store'), validContactPayload(['message' => 'too short']));

    $response->assertSessionHasErrors('message');
});

test('a honeypot-triggered contact submission is silently discarded', function () {
    Mail::fake();
    Notification::fake();

    $response = $this->post(route('contact.store'), validContactPayload(['website' => 'https://spam.example']));

    $response->assertRedirect();
    $this->assertDatabaseCount('contact_submissions', 0);
    Mail::assertNothingQueued();
    Notification::assertNothingSent();
});

test('contact submissions are rate limited to 3 per hour per ip', function () {
    foreach (range(1, 3) as $_) {
        $this->post(route('contact.store'), validContactPayload(['email' => fake()->unique()->safeEmail()]))
            ->assertRedirect();
    }

    $this->post(route('contact.store'), validContactPayload())->assertStatus(429);
});
