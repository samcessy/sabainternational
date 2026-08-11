<?php

use App\Actions\Payments\ProcessStripeWebhookEvent;
use App\Enums\AdminRole;
use App\Enums\DonationStatus;
use App\Enums\TransactionStatus;
use App\Mail\DonationFailed;
use App\Mail\DonationReceipt;
use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\Supporter;
use App\Models\User;
use App\Notifications\NewDonationNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Stripe\Event;

function fakeStripeEvent(string $type, array $object): Event
{
    return Event::constructFrom([
        'id' => 'evt_test',
        'type' => $type,
        'data' => ['object' => $object],
    ]);
}

test('payment_intent.succeeded marks the donation succeeded, records a transaction, notifies admins, and sends a receipt', function () {
    Mail::fake();
    Notification::fake();
    $admin = User::factory()->create(['admin_role' => AdminRole::SuperAdministrator]);
    $donation = Donation::factory()->for(Supporter::factory())->create(['status' => DonationStatus::Pending]);

    (new ProcessStripeWebhookEvent)->handle(fakeStripeEvent('payment_intent.succeeded', [
        'id' => 'pi_abc',
        'metadata' => ['donation_id' => (string) $donation->id],
    ]));

    expect($donation->fresh()->status)->toBe(DonationStatus::Succeeded);
    $this->assertDatabaseHas('donation_transactions', [
        'donation_id' => $donation->id,
        'gateway_reference' => 'pi_abc',
        'status' => TransactionStatus::Succeeded->value,
    ]);
    Mail::assertQueued(DonationReceipt::class);
    Notification::assertSentTo($admin, NewDonationNotification::class);
});

test('a retried payment_intent.succeeded webhook does not send a duplicate receipt', function () {
    Mail::fake();
    $donation = Donation::factory()->for(Supporter::factory())->create();
    $event = fakeStripeEvent('payment_intent.succeeded', [
        'id' => 'pi_retry',
        'metadata' => ['donation_id' => (string) $donation->id],
    ]);

    $action = new ProcessStripeWebhookEvent;
    $action->handle($event);
    $action->handle($event);

    $this->assertDatabaseCount('donation_transactions', 1);
    Mail::assertQueued(DonationReceipt::class, 1);
});

test('payment_intent.payment_failed marks the donation failed and emails the donor', function () {
    Mail::fake();
    $donation = Donation::factory()->for(Supporter::factory())->create(['status' => DonationStatus::Pending]);

    (new ProcessStripeWebhookEvent)->handle(fakeStripeEvent('payment_intent.payment_failed', [
        'id' => 'pi_failed',
        'metadata' => ['donation_id' => (string) $donation->id],
    ]));

    expect($donation->fresh()->status)->toBe(DonationStatus::Failed);
    Mail::assertQueued(DonationFailed::class);
});

test('invoice.paid records a new transaction for each recurring billing cycle', function () {
    Mail::fake();
    $donation = Donation::factory()->for(Supporter::factory())->create([
        'stripe_subscription_id' => 'sub_recurring',
    ]);

    (new ProcessStripeWebhookEvent)->handle(fakeStripeEvent('invoice.paid', [
        'id' => 'in_cycle_1',
        'subscription' => 'sub_recurring',
        'payment_intent' => 'pi_cycle_1',
    ]));
    (new ProcessStripeWebhookEvent)->handle(fakeStripeEvent('invoice.paid', [
        'id' => 'in_cycle_2',
        'subscription' => 'sub_recurring',
        'payment_intent' => 'pi_cycle_2',
    ]));

    $this->assertDatabaseCount('donation_transactions', 2);
    Mail::assertQueued(DonationReceipt::class, 2);
});

test('customer.subscription.deleted marks the donation cancelled', function () {
    $donation = Donation::factory()->for(Supporter::factory())->create([
        'stripe_subscription_id' => 'sub_to_cancel',
        'status' => DonationStatus::Succeeded,
    ]);

    (new ProcessStripeWebhookEvent)->handle(fakeStripeEvent('customer.subscription.deleted', [
        'id' => 'sub_to_cancel',
    ]));

    expect($donation->fresh()->status)->toBe(DonationStatus::Cancelled);
});

test('charge.refunded marks the matching transaction refunded', function () {
    $transaction = DonationTransaction::factory()->create([
        'gateway_reference' => 'pi_to_refund',
        'status' => TransactionStatus::Succeeded,
    ]);

    (new ProcessStripeWebhookEvent)->handle(fakeStripeEvent('charge.refunded', [
        'id' => 'ch_1',
        'payment_intent' => 'pi_to_refund',
    ]));

    expect($transaction->fresh()->status)->toBe(TransactionStatus::Refunded);
});

test('an unrecognized event type is ignored without error', function () {
    expect(fn () => (new ProcessStripeWebhookEvent)->handle(fakeStripeEvent('customer.updated', ['id' => 'cus_1'])))
        ->not->toThrow(Exception::class);
});

test('a webhook event with no matching donation is ignored without error', function () {
    expect(fn () => (new ProcessStripeWebhookEvent)->handle(fakeStripeEvent('payment_intent.succeeded', [
        'id' => 'pi_orphan',
        'metadata' => ['donation_id' => '999999'],
    ])))->not->toThrow(Exception::class);
});
