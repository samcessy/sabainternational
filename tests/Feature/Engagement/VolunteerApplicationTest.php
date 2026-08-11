<?php

use App\Enums\AdminRole;
use App\Mail\VolunteerApplicationConfirmation;
use App\Models\User;
use App\Notifications\NewVolunteerApplicationNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

function validVolunteerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ryan Volunteer',
        'email' => 'ryan@example.com',
        'details' => 'I would love to help with the Hunter Initiative on weekends.',
        'consent' => true,
    ], $overrides);
}

test('a valid volunteer application is stored, confirmed, and notified', function () {
    Mail::fake();
    Notification::fake();
    $editor = User::factory()->create(['admin_role' => AdminRole::Editor]);

    $response = $this->post(route('volunteer.store'), validVolunteerPayload());

    $response->assertRedirect();
    $this->assertDatabaseHas('volunteer_applications', ['email' => 'ryan@example.com']);
    Mail::assertQueued(VolunteerApplicationConfirmation::class);
    Notification::assertSentTo($editor, NewVolunteerApplicationNotification::class);
});

test('finance manager does not receive volunteer application notifications', function () {
    Mail::fake();
    Notification::fake();
    $financeManager = User::factory()->create(['admin_role' => AdminRole::FinanceManager]);

    $this->post(route('volunteer.store'), validVolunteerPayload());

    Notification::assertNotSentTo($financeManager, NewVolunteerApplicationNotification::class);
});

test('a honeypot-triggered volunteer application is silently discarded', function () {
    Mail::fake();

    $response = $this->post(route('volunteer.store'), validVolunteerPayload(['website' => 'https://spam.example']));

    $response->assertRedirect();
    $this->assertDatabaseCount('volunteer_applications', 0);
    Mail::assertNothingQueued();
});
