<?php

use App\Enums\AdminRole;
use App\Mail\PartnershipInquiryConfirmation;
use App\Models\User;
use App\Notifications\NewPartnershipInquiryNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

function validPartnershipPayload(array $overrides = []): array
{
    return array_merge([
        'organization_name' => 'Acme Foundation',
        'contact_name' => 'Pat Partner',
        'email' => 'pat@acmefoundation.org',
        'details' => 'We would like to explore a corporate giving partnership.',
        'consent' => true,
    ], $overrides);
}

test('a valid partnership inquiry is stored, confirmed, and notified', function () {
    Mail::fake();
    Notification::fake();
    $admin = User::factory()->create(['admin_role' => AdminRole::SuperAdministrator]);

    $response = $this->post(route('partnership.store'), validPartnershipPayload());

    $response->assertRedirect();
    $this->assertDatabaseHas('partnership_inquiries', ['email' => 'pat@acmefoundation.org']);
    Mail::assertQueued(PartnershipInquiryConfirmation::class);
    Notification::assertSentTo($admin, NewPartnershipInquiryNotification::class);
});

test('partnership inquiry requires an organization name', function () {
    $response = $this->post(route('partnership.store'), validPartnershipPayload(['organization_name' => '']));

    $response->assertSessionHasErrors('organization_name');
});

test('a honeypot-triggered partnership inquiry is silently discarded', function () {
    Mail::fake();

    $response = $this->post(route('partnership.store'), validPartnershipPayload(['website' => 'https://spam.example']));

    $response->assertRedirect();
    $this->assertDatabaseCount('partnership_inquiries', 0);
    Mail::assertNothingQueued();
});
