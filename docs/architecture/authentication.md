# Authentication Architecture

**Phase:** 4 — Architecture
**Status:** Decided
**Inputs:** saba.md §10.4, §24.2; `docs/architecture/adr-001-inertia-vs-api-spa.md`; `docs/architecture/authorization-model.md`

---

## 1. Two Separate Auth Contexts

Per ADR-001, this application has two genuinely different things that need "authentication," and they use different mechanisms deliberately — conflating them is what tends to produce awkward hybrid systems:

| Context | Who | Mechanism |
|---|---|---|
| Admin panel (CMS) | A handful of Saba staff/board admin users | **Session-based, via Fortify** — already scaffolded |
| Public JSON API (external consumers, if/when any exist) | Third parties (future partner integrations, AEO tooling) | **Sanctum personal access tokens** — not yet needed, built when a real consumer exists |

The public website itself (marketing pages, donation flow, contact/newsletter/volunteer forms) requires **no authentication at all** — it's rendered via Inertia and submitted via standard CSRF-protected web routes, same as any Laravel web app. There is no "visitor account" in V1 (no donor portal — explicitly deferred, `docs/product-requirements.md` §11).

---

## 2. Admin Session Authentication

Reuses the existing Fortify scaffold (`app/Providers/FortifyServiceProvider.php`) with two changes from its current SaaS-starter-kit defaults:

1. **Public registration is disabled.** See `docs/architecture/authorization-model.md` §4 — admin accounts are provisioned by a Super Administrator, never self-served.
2. **Team-scoped views are re-pointed.** The current `Fortify::loginView`/`registerView` closures pass `teamInvitation` context tied to the `TeamInvitation` model being dropped (authorization-model.md §1) — these get updated to the renamed `UserInvitation` flow instead, not deleted outright (invitation-based provisioning is still the primary path for adding admins).

Everything else about the existing Fortify configuration is sound and gets kept as-is:
- Login rate limiting: 5/minute per (username + IP) — already configured, satisfies saba.md §24.2's login rate-limiting requirement.
- Session cookie: `httponly`, `samesite=lax` — already correct (confirmed in `docs/audit/technical-audit.md` §6); add `Secure` explicitly for production (paired with the HSTS fix in `docs/audit/current-website-audit.md` F-6).
- Password hashing: Laravel's default bcrypt — satisfies saba.md §24.2's "min 12 rounds" via Laravel's `BCRYPT_ROUNDS` config, verify it's set to ≥12 in production `.env`.

---

## 3. MFA — Mandatory, Not Optional, for Every Admin Role

saba.md §10.4: *"Administrative users MUST use MFA... Do not rely solely on passwords for privileged accounts."* This applies to **all four** roles in `docs/architecture/authorization-model.md` §2, including Viewer — a read-only account still exposes donor/contact data if compromised.

**Enforcement mechanism:**
1. Fortify's TOTP two-factor feature is already fully wired (`TwoFactorSetupModal.vue`, `ManageTwoFactor.vue`, `TwoFactorRecoveryCodes.vue`, `two_factor_confirmed_at` column) — this is kept as-is.
2. **New middleware**, `EnsureTwoFactorEnabled`, applied to every admin route group: if `auth()->user()->two_factor_confirmed_at` is null, redirect to a forced-setup screen instead of the requested admin page. This is the piece that makes MFA *mandatory* rather than a self-service option a busy admin might skip — the current starter kit has the feature built but doesn't force enrollment, which would leave saba.md §10.4 unmet even though the UI exists.
3. **Passkeys** (`@laravel/passkeys`, already installed) are offered as an additional, optional authentication factor/passwordless login — a genuine security upgrade beyond saba.md's ask, kept because it's already implemented and costs nothing to retain.
4. Recovery codes (already implemented) are the account-recovery path if a TOTP device is lost — no separate "forgot MFA" flow needs building.

**Rate limiting on 2FA challenge and passkey attempts** is already configured in `FortifyServiceProvider` (5/min for two-factor, 10/min for passkeys) — satisfies saba.md §24.2's rate-limiting requirement for auth flows without new work.

---

## 4. Public API Authentication (Sanctum)

Per `docs/architecture/adr-001-inertia-vs-api-spa.md` §5 and `docs/architecture/api-architecture.md`, the public JSON API's read endpoints (`GET /api/v1/pages`, `/programs`, `/stories`, etc.) require **no authentication** — they're public content, rate-limited by IP (saba.md §11.3: 60 req/min public tier).

If/when a real authenticated consumer materializes (a partner integration, a future mobile app — none exist today, see ADR-001 §3.3), Sanctum **personal access tokens** (not Sanctum's SPA cookie mode) are the mechanism: a Super Administrator issues a scoped token from the admin panel, the consumer sends it as a `Bearer` header, rate-limited at the authenticated tier (120 req/min per saba.md §11.3). This is explicitly **not built in V1** — Sanctum is added to `composer.json` only when a real consumer needs it, per the "don't build for hypothetical futures" principle. The architecture accommodates it without needing a redesign later: token auth is additive to the existing public API surface, not a replacement for it.

**Stripe webhook** (`POST /api/v1/payments/webhook` or similar) is a distinct case — it's not "authenticated" via user credentials at all. It's verified via **Stripe's webhook signing secret** (HMAC signature check on the raw request body, per Stripe's standard webhook verification), which is a payment-architecture concern, not a user-authentication one. No Sanctum token, no session — just signature verification middleware specific to that one route.

---

## 5. What Does NOT Need Authentication in V1

Explicitly calling this out because it's easy to over-build: contact form, newsletter signup, volunteer application, and partnership inquiry submissions are all **anonymous, unauthenticated POST requests to web routes**, protected by CSRF tokens (Laravel default for Inertia/session-based forms), rate limiting, and honeypot/reCAPTCHA v3 (per `docs/product-requirements.md` §7) — not by any auth mechanism. There is no "supporter login" in V1; the `Supporter` entity (`docs/content-model.md` §2.6) is a CRM-style record created from donation/newsletter data, not an authenticable account.

---

## 6. Summary Table

| Flow | Mechanism | Status |
|---|---|---|
| Admin login | Fortify session + rate limiting | Already scaffolded, reused as-is |
| Admin MFA | Fortify TOTP, now **enforced** via new middleware | Scaffolded, enforcement is new work |
| Admin passwordless | Passkeys (WebAuthn) | Already scaffolded, kept as bonus |
| Admin provisioning | Invitation-based (renamed from `TeamInvitation`), no public registration | Adapted from existing pattern |
| Public site forms | CSRF-protected web routes, no auth | Standard Laravel/Inertia, no new mechanism |
| Public API reads | None (rate-limited by IP) | New — thin public API layer |
| Public API writes (future) | Sanctum personal access tokens | Deferred until a real consumer exists |
| Stripe webhook | Signature verification, not user auth | New — payment-architecture concern |
