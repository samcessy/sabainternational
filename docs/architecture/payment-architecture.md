# Payment Architecture

**Phase:** 4 — Architecture
**Status:** Decided
**Inputs:** saba.md §8.3, §24.2; `docs/product-requirements.md` §3; `docs/architecture/database-erd.md` §3; `docs/architecture/adr-001-inertia-vs-api-spa.md`

---

## 1. Why Elements, Not a Checkout Redirect

The current live site's donation page already references Stripe (`docs/audit/current-website-audit.md` F-1) but the audit couldn't confirm which integration mode — likely a bare Checkout redirect given how minimal the page is. `docs/product-requirements.md` §3 already decided against reusing that shape: **Stripe Elements, embedded in the Inertia donation page, not a redirect to a Stripe-hosted page.**

Why this matters beyond preference: the entire premise of the Donor Trust Loop (`docs/project-overview.md` §3.1) is that a first-time visitor trusts the *organization* enough to give. Bouncing them to a generic Stripe-branded page mid-flow, then back, is a worse trust experience than a donation form that never leaves Saba's own branded UI. Elements achieves this while keeping the same PCI posture as a redirect (see §4).

---

## 2. Gateway Abstraction

Per saba.md §8.3, a real interface — implemented once (Stripe), extendable later (PayPal/MPesa are V2/Future per `docs/product-requirements.md` §3, not built now):

```php
interface PaymentGatewayInterface
{
    public function createOneTimePaymentIntent(Donation $donation): PaymentIntentResult;
    public function createOrUpdateSubscription(Donation $donation, Supporter $supporter): SubscriptionResult;
    public function refund(DonationTransaction $transaction): RefundResult;
    public function handleWebhookEvent(WebhookEvent $event): void;
}
```
`StripeGateway implements PaymentGatewayInterface` is the only implementation in V1. The interface exists so `PayPalGateway`/`MPesaGateway` are additive later (saba.md §8.3's own structure) — but no stub classes for those get created now; an unused interface implementation is exactly the kind of speculative scaffolding the project's guiding principles warn against.

---

## 3. Flow

### 3.1 One-time donation
```
Donor submits form (amount, designation, donor info)
  → Web route creates Donation (status=pending) + Supporter (find-or-create by email)
  → StripeGateway::createOneTimePaymentIntent() — server creates a Stripe PaymentIntent
    with amount, currency=usd, metadata={donation_id}, using an idempotency key
    derived from the Donation's id (protects against double-submit/retry creating
    two PaymentIntents for one donor action)
  → Client confirms the PaymentIntent via Stripe.js Elements (Payment Element) —
    card data goes directly to Stripe, never touches Saba's server
  → Stripe webhook (payment_intent.succeeded) is the SOURCE OF TRUTH for completion,
    not the client-side confirmation callback — the client redirect can fail/close
    without Stripe having actually failed the payment, or vice versa; only the
    webhook is trusted to flip Donation.status to succeeded
  → Webhook handler creates/updates DonationTransaction, queues the receipt email job
```

### 3.2 Recurring donation (monthly — the only frequency in V1 per `docs/product-requirements.md` §3)
```
Donor submits form with frequency=monthly
  → Web route creates Donation (status=pending) + Supporter + Stripe Customer
    (find-or-create by Supporter.email — one Stripe Customer per Supporter, reused
    across all their donations, not created fresh each time)
  → StripeGateway::createOrUpdateSubscription() creates a Subscription with an
    inline `price_data` (recurring, interval=month, amount) rather than a
    pre-created Stripe Price object — avoids needing to pre-provision a Price
    for every possible custom donation amount
  → payment_behavior=default_incomplete, expand latest_invoice.payment_intent
  → Client confirms that PaymentIntent via Elements, same as the one-time flow
  → invoice.paid webhook confirms each billing cycle (first and subsequent);
    invoice.payment_failed / customer.subscription.deleted handle failure/cancellation
```

Both flows converge on the same principle: **the webhook confirms state, the client UI only initiates it.** This is standard Stripe guidance and it's what makes the abandonment-recovery feature (`docs/product-requirements.md` §3, V2) trivial to add later — a `Donation` stuck in `pending` with no webhook confirmation after 30 minutes is already exactly the signal that feature needs; no re-architecture required when it's built.

---

## 4. PCI Scope

Card data (PAN, CVC, expiry) is entered into Stripe.js Elements iframes and transmitted directly to Stripe — it never reaches a Saba-controlled server or gets logged anywhere in this application. This keeps the integration on Stripe's **SAQ-A** pathway (saba.md §24.2), the lightest PCI compliance tier, same as a Checkout redirect would provide — Elements doesn't trade away PCI simplicity for branding control, which is exactly why it's the right choice here rather than a tradeoff.

**Never stored:** card number, CVC, expiry.
**Stored:** Stripe `payment_intent_id` / `subscription_id` / `customer_id` (all non-sensitive references) in `donation_transactions.gateway_reference` and `supporters` (a `stripe_customer_id` column, refining `docs/architecture/database-erd.md` §3's `supporters` table).

---

## 5. Webhook Handling

`POST /api/v1/payments/webhook` (per `docs/architecture/api-architecture.md` §1–2):

| Stripe event | Action |
|---|---|
| `payment_intent.succeeded` | Mark `Donation.status = succeeded`, create/update `DonationTransaction`, queue receipt email |
| `payment_intent.payment_failed` | Mark `Donation.status = failed`, queue admin notification (saba.md §21.1) |
| `invoice.paid` | Recurring cycle succeeded — create a new `DonationTransaction` linked to the same `Donation`/subscription, queue receipt |
| `invoice.payment_failed` | Queue donor notification per saba.md §21.1's "donation failure notification" |
| `customer.subscription.deleted` | Mark the recurring `Donation`'s status reflecting cancellation — recurring giving stops, no further transactions expected |
| `charge.refunded` | Confirms a refund initiated via §6 below; sets `DonationTransaction.status = refunded` |

**Verification:** Laravel middleware validates the `Stripe-Signature` header against `STRIPE_WEBHOOK_SECRET` (env-scoped per environment — local/staging/production each get their own Stripe webhook endpoint and secret, never shared) using Stripe's SDK, against the **raw** request body — this route is explicitly excluded from Laravel's default JSON-parsing/CSRF pipeline since it's not a browser-originated request (`docs/architecture/authentication.md` §4 already establishes this isn't user-auth territory).

**Idempotency:** `donation_transactions.gateway_reference` is unique (`docs/architecture/database-erd.md` §3) — keyed off Stripe's event/PaymentIntent/Invoice id. Stripe retries webhooks on non-2xx responses; a retried event that already has a matching `gateway_reference` is a no-op, not a duplicate transaction.

---

## 6. Refunds

Per `docs/architecture/authorization-model.md` §3, Finance Manager and Super Administrator can "change donation/transaction status (e.g., mark refunded)." Concretely: a refund is **initiated** from the admin panel (calls Stripe's refund API for the relevant `payment_intent_id`), and **confirmed** by the `charge.refunded` webhook — the admin action doesn't directly flip the status itself, it triggers the same source-of-truth webhook path as every other state change in this document. This keeps exactly one code path responsible for donation-status transitions, rather than admin actions and webhooks both being able to write conflicting state.

---

## 7. Rate Limiting & Abuse Prevention

- Max 5 donation-initiation attempts per IP per hour (saba.md §8.3) — applied to the web route that creates the PaymentIntent/Subscription, not to the webhook (which is Stripe-originated and protected by signature verification instead, per §5).
- Amounts are validated server-side (min/max bounds, currency fixed to USD in V1) regardless of what the client sends — never trust the amount from the browser beyond what the Form Request validates.

---

## 8. Schema Refinement (vs. `database-erd.md`)

Two additions surfaced while designing the actual flow, folded back into the ERD's intent:
- `supporters.stripe_customer_id` (nullable, unique) — needed so recurring donors map to one reusable Stripe Customer (§3.2).
- `donations.amount` should store **integer cents**, not decimal dollars — avoids floating-point rounding drift and matches Stripe's own amount representation exactly (Stripe's API is cents-denominated), removing a whole class of "off by a cent" reconciliation bugs between Saba's records and Stripe's.
