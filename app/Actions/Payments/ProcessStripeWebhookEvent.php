<?php

namespace App\Actions\Payments;

use App\Enums\AdminPermission;
use App\Enums\DonationStatus;
use App\Enums\PaymentGateway;
use App\Enums\TransactionStatus;
use App\Mail\DonationFailed;
use App\Mail\DonationReceipt;
use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\User;
use App\Notifications\NewDonationNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Stripe\Event;

/**
 * The webhook is the single source of truth for donation state — never the
 * client-side confirmation callback. See
 * docs/architecture/payment-architecture.md §3, §5.
 *
 * Event payload objects (PaymentIntent/Invoice/Subscription/Charge) are
 * converted to plain arrays via StripeObject::toArray() rather than
 * accessed as untyped magic properties — Stripe's generic StripeObject
 * doesn't statically declare which fields exist on which event type, so
 * array access is both safer and more honest about what's actually known
 * at compile time.
 */
class ProcessStripeWebhookEvent
{
    public function handle(Event $event): void
    {
        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($event),
            'invoice.paid' => $this->handleInvoicePaid($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            'charge.refunded' => $this->handleChargeRefunded($event),
            default => null,
        };
    }

    private function handlePaymentIntentSucceeded(Event $event): void
    {
        $paymentIntent = $event->data->object->toArray();
        $donation = $this->findDonationByMetadata($paymentIntent);

        if (! $donation) {
            return;
        }

        $donation->update(['status' => DonationStatus::Succeeded]);

        $this->recordTransactionAndSendReceipt($donation, (string) $paymentIntent['id'], TransactionStatus::Succeeded);
        $this->notifyAdminsOfNewDonation($donation);
    }

    private function handlePaymentIntentFailed(Event $event): void
    {
        $paymentIntent = $event->data->object->toArray();
        $donation = $this->findDonationByMetadata($paymentIntent);

        if (! $donation) {
            return;
        }

        $donation->update(['status' => DonationStatus::Failed]);

        Mail::to($donation->supporter->email)->queue(new DonationFailed($donation));
    }

    private function handleInvoicePaid(Event $event): void
    {
        $invoice = $event->data->object->toArray();
        $donation = $this->findDonationBySubscription($invoice['subscription'] ?? null);

        if (! $donation) {
            return;
        }

        $donation->update(['status' => DonationStatus::Succeeded]);

        $reference = (string) ($invoice['payment_intent'] ?? $invoice['id']);
        $this->recordTransactionAndSendReceipt($donation, $reference, TransactionStatus::Succeeded);
        $this->notifyAdminsOfNewDonation($donation);
    }

    private function handleInvoicePaymentFailed(Event $event): void
    {
        $invoice = $event->data->object->toArray();
        $donation = $this->findDonationBySubscription($invoice['subscription'] ?? null);

        if (! $donation) {
            return;
        }

        Mail::to($donation->supporter->email)->queue(new DonationFailed($donation));
    }

    private function handleSubscriptionDeleted(Event $event): void
    {
        $subscription = $event->data->object->toArray();
        $donation = $this->findDonationBySubscription($subscription['id'] ?? null);

        $donation?->update(['status' => DonationStatus::Cancelled]);
    }

    private function handleChargeRefunded(Event $event): void
    {
        $charge = $event->data->object->toArray();
        $paymentIntentId = $charge['payment_intent'] ?? null;

        if (! $paymentIntentId) {
            return;
        }

        DonationTransaction::query()
            ->where('gateway_reference', $paymentIntentId)
            ->update(['status' => TransactionStatus::Refunded]);
    }

    /**
     * @param  array<string, mixed>  $paymentIntent
     */
    private function findDonationByMetadata(array $paymentIntent): ?Donation
    {
        $donationId = $paymentIntent['metadata']['donation_id'] ?? null;

        if (! $donationId) {
            return null;
        }

        return Donation::with(['supporter', 'program'])->where('id', $donationId)->first();
    }

    private function findDonationBySubscription(?string $subscriptionId): ?Donation
    {
        if (! $subscriptionId) {
            return null;
        }

        return Donation::with(['supporter', 'program'])
            ->where('stripe_subscription_id', $subscriptionId)
            ->first();
    }

    /**
     * Idempotent — a webhook Stripe retries after a non-2xx response must
     * not create a duplicate transaction or send a duplicate receipt.
     */
    private function recordTransactionAndSendReceipt(Donation $donation, string $reference, TransactionStatus $status): void
    {
        $transaction = DonationTransaction::query()->firstOrCreate(
            ['gateway_reference' => $reference],
            [
                'donation_id' => $donation->id,
                'gateway' => PaymentGateway::Stripe,
                'status' => $status,
            ],
        );

        if ($transaction->wasRecentlyCreated) {
            Mail::to($donation->supporter->email)->queue(new DonationReceipt($donation, $transaction));
            $transaction->update(['receipt_sent_at' => now()]);
        }
    }

    private function notifyAdminsOfNewDonation(Donation $donation): void
    {
        Notification::send(
            User::withPermission(AdminPermission::ViewFundraising),
            new NewDonationNotification($donation),
        );
    }
}
