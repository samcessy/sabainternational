# API Architecture

**Phase:** 4 — Architecture
**Status:** Decided
**Inputs:** saba.md §11; `docs/architecture/adr-001-inertia-vs-api-spa.md`; `docs/architecture/authentication.md`

---

## 1. Scope Correction From saba.md §11.1 (important — read before implementing)

saba.md §11.1 lists both `GET` endpoints (pages/programs/stories/team/campaigns) and `POST` endpoints (contact, newsletter/subscribe, donations, payments/webhook) under one "Public API." That list was written assuming a decoupled SPA where *the website itself* calls this API for everything, including form submissions.

Under ADR-001, the website doesn't work that way anymore: the site's own contact form, newsletter signup, and donation flow are **Inertia page components submitting to ordinary CSRF-protected web routes** (`routes/web.php`), validated with Laravel Form Requests, same as any server-rendered Laravel app. They do not call `/api/v1/...` at all.

**This means the public API in V1 is scoped to only what a genuine external consumer would need:**

| saba.md §11.1 endpoint | V1 API status | Why |
|---|---|---|
| `GET /api/v1/pages`, `/programs`, `/stories`, `/team`, `/campaigns` (+ `/{slug}` variants) | **Built** — this is the real external-consumption surface (AEO tooling, a future partner integration, content syndication) | Genuinely useful to expose read-only, no reason not to. |
| `POST /api/v1/contact` | **Not built** — served by a web route instead | No external consumer submits contact forms on Saba's behalf; this was always meant for the site's own form. |
| `POST /api/v1/newsletter/subscribe` | **Not built** — served by a web route instead | Same reasoning. |
| `POST /api/v1/donations` | **Not built** — served by a web route instead | The donation flow is now an Inertia page; it doesn't need a separate API contract with itself. |
| `POST /api/v1/payments/webhook` | **Built, but not "public" in the request-origin sense** | This one's different in kind: it's Stripe calling *us*, not a browser client. It has to be a real HTTP endpoint regardless of frontend architecture — webhooks are inherently server-to-server. Verified via Stripe signature, not user auth (`docs/architecture/authentication.md` §4). |

If a genuine need for API-driven form submission ever appears (e.g., a partner's own site wants to embed a Saba donation widget), that's a deliberate V2/Future addition made when that consumer is real — not spec'd speculatively now.

---

## 2. V1 Public API Surface

```
GET /api/v1/pages
GET /api/v1/pages/{slug}
GET /api/v1/programs
GET /api/v1/programs/{slug}
GET /api/v1/stories
GET /api/v1/stories/{slug}
GET /api/v1/team
GET /api/v1/campaigns
GET /api/v1/campaigns/{slug}

POST /api/v1/payments/webhook   -- Stripe → us, signature-verified, not "public" in the auth sense
```

Only content that's already `published` is returned — draft/review-stage content (per `docs/content-model.md`'s approval workflow) never appears in the public API, same rule as the public website itself.

---

## 3. Response Standards (saba.md §11.3, unchanged)

```json
{
  "data": {},
  "meta": { "current_page": 1, "per_page": 20, "total": 47 },
  "links": { "next": "...", "prev": null }
}
```
- Laravel API Resources for every response — never raw Eloquent model serialization.
- Pagination on every list endpoint (`pages`, `programs`, `stories`, `campaigns`).
- Standardized error shape: `{ "error": { "code": "...", "message": "..." } }`, consistent HTTP status codes.
- Every endpoint has a feature test (saba.md §11.3, §26.2) — including the "draft content never leaks" rule above, which is exactly the kind of thing that needs an explicit regression test, not just a code review glance.

---

## 4. Auth & Rate Limiting

Per `docs/architecture/authentication.md` §4:

| Tier | Applies to | Limit |
|---|---|---|
| Public (unauthenticated) | All `GET /api/v1/*` endpoints | 60 req/min per IP (saba.md §11.3) |
| Authenticated (Sanctum token) | Reserved for future partner/consumer tokens — **no V1 consumer uses this tier** | 120 req/min per token |
| Webhook | `POST /api/v1/payments/webhook` | Not rate-limited by IP (Stripe's IPs are trusted once signature verifies); protected instead by signature verification + idempotency on `gateway_reference` (`docs/architecture/database-erd.md` §3) |

No Sanctum installation is required for V1 to ship — the entire V1 public API is unauthenticated reads plus one signature-verified webhook. Sanctum gets added to `composer.json` at the point a real token consumer exists, not before.

---

## 5. Versioning & Discoverability

- URI versioning: `/api/v1/...`, per saba.md §11.1.
- `robots.txt` explicitly disallows `/api/` (closing `docs/audit/current-website-audit.md` F-4's finding that the current `robots.txt` disallows nothing).
- No public API documentation site is built in V1 — with a single external-facing resource group (5 read endpoints) and zero known external consumers today, a full OpenAPI/Swagger doc is speculative work; a well-commented `docs/api-documentation.md` (saba.md §31's required doc) is sufficient until an actual integration partner asks for more.

---

## 6. Admin API

saba.md §11.2 describes `/api/v1/admin/...` as a separate authorized surface. Under ADR-001, the admin CMS is Inertia-rendered, not a JSON API the admin frontend consumes — so there is **no `/api/v1/admin/*` namespace in V1**. Admin actions (publish a story, update a donation status, manage users) are Inertia form submissions to `routes/web.php` controllers, authorized via the Policies described in `docs/architecture/authorization-model.md` §5, same session-based auth as the rest of the admin panel. This isn't a gap relative to saba.md §11.2's intent (admin actions must be authorized and never publicly exposed) — it's the same requirement, met by a different, simpler mechanism than a separate authenticated API would need.
