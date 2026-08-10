# ADR-001: Inertia (server-driven) vs. API-First Decoupled Vue SPA

**Phase:** 4 — Architecture
**Status:** Decided
**Date:** 2026-08-10
**Deciders:** Architecture recommendation for stakeholder ratification (this is a technical call, not one that needs board sign-off, but should be confirmed with whoever owns delivery before Phase 5 backend work starts)

---

## 1. Context

saba.md §11.1 names the target architecture as "API-first Laravel backend + Vue 3 SPA" with a versioned REST/JSON API (`/api/v1/...`) as the contract between backend and frontend. `docs/project-overview.md` §7 flagged this as an open decision because the codebase already sitting in this repository is not that — it's Laravel's own official `laravel/vue-starter-kit` (confirmed by `composer.json`'s `name` field), which is an **Inertia** application: Vue components rendered server-side-routed via `Inertia::render()`, sharing a session with the backend, no token-based API layer.

Concretely, the existing scaffold already includes:

| Already present | Where |
|---|---|
| Laravel Fortify authentication (login, registration, password reset) | `app/Providers/FortifyServiceProvider.php`, `app/Actions/Fortify/` |
| **TOTP two-factor auth**, fully wired (setup modal, recovery codes, challenge view) | `resources/js/components/TwoFactorSetupModal.vue`, `ManageTwoFactor.vue`, `TwoFactorRecoveryCodes.vue`, migration `2025_08_14_170933_add_two_factor_columns_to_users_table.php` |
| **Passkey (WebAuthn) auth** — beyond what saba.md even asks for | `@laravel/passkeys` dependency, `PasskeyRegister.vue`, `PasskeyVerify.vue`, migration `2024_01_01_000000_create_passkeys_table.php` |
| Inertia v3 with **automatic SSR** (no separate Node server needed in dev; `build:ssr` script for production) | `package.json` scripts, `vite.config.ts`, AGENTS.md's Inertia v3 boost rules |
| Wayfinder — generates typed TS wrappers for Laravel routes/controllers | `@laravel/vite-plugin-wayfinder`, `resources/js/actions/`, `resources/js/routes/` |
| A Teams/multi-tenancy scaffold (Owner/Admin/Member roles, invitations) | `app/Models/Team.php`, `Membership.php`, `app/Enums/TeamRole.php`, `TeamPermission.php` |

Notably **absent**: `laravel/sanctum`, `routes/api.php`, any Spatie Permission package, and any custom Saba business logic. `routes/web.php` is the stock starter-kit file (Welcome page, team-scoped dashboard, invitation accept/decline) — nothing has been built on top of it yet. There is no git history and no sunk cost either direction; this decision is genuinely open, not a rationalization of work already done.

---

## 2. Decision

**Build the public site and the admin CMS as an Inertia application (server-driven Vue, not a decoupled SPA consuming a JSON API for its own rendering). Separately, implement saba.md §11.1's public read-only JSON API as a thin, genuinely stateless `routes/api.php` surface for external/programmatic consumers — not as the mechanism the website itself uses to render pages.**

This is a hybrid, not a rejection of saba.md's API requirement. saba.md's public API list (`GET /pages`, `/programs`, `/stories`, `/team`, `/campaigns`, plus the `POST` form endpoints) still gets built — it just isn't what powers the primary user-facing site.

---

## 3. Reasoning

### 3.1 saba.md's own top SEO priority argues *for* Inertia, not against it
The audit (`docs/audit/current-website-audit.md` F-2, F-3) found the current site has **zero** per-page metadata, no sitemap, and identical titles everywhere — and `docs/product-requirements.md` §5 makes fixing this a V1 must-have. saba.md's spec of a plain "Vue 3 SPA" says nothing about server-side rendering; a client-only SPA would need a bespoke SSR/prerendering setup bolted on afterward to avoid making the SEO problem *worse* (crawlers hitting an empty shell before hydration). Inertia v3 gives SSR automatically, with no separate Node process to operate — this directly serves the project's own stated top priority at zero extra infrastructure cost.

### 3.2 saba.md's resource-constraint principle argues for the simpler operational model
saba.md §1.4: *"Saba International is a small board-run nonprofit... realistic resourcing."* An API-first split means: two deployable artifacts, CORS configuration, token issuance/rotation/revocation, duplicated validation logic (API validation for the SPA to consume, plus whatever the admin panel needs), and a second place authorization bugs can hide (API policy vs. web policy drift). Inertia collapses this to one Laravel app, one deploy, one session-based auth model, one set of Form Request validators. For a team without a dedicated platform engineer, this is a meaningful, compounding operational simplification — directly in the spirit of §1.4, even though §1.4 doesn't literally address this stack question.

### 3.3 saba.md never names a consumer that actually requires decoupling
Re-reading saba.md end to end: no mobile app is mentioned, no third-party integration partner is named, no requirement says "the frontend and backend must deploy independently" or "must scale independently." The API-first framing in §11 reads as a default modern-stack choice, not one justified by a concrete requirement elsewhere in the document. The one place a real API consumer *is* implied — external content consumption (§15.4 AEO pages, possible future partner/donor portals in §34.3) — is exactly what §3.2's separate public API surface still serves.

### 3.4 The existing scaffold gives real, non-trivial functionality for free
MFA for admin accounts is a **hard requirement**, not optional (saba.md §10.4, §24.2, §35 rule area). Fortify's TOTP flow is already fully wired end-to-end in this codebase, including recovery codes — that's real implementation work saba.md asks for that already exists. Rebuilding this from scratch under a token-based API+SPA model (where 2FA challenge flows are meaningfully more complex to coordinate between a stateless API and a client SPA) would mean throwing away working, correctly-configured functionality to satisfy an architecture label. Passkey support is a bonus beyond what was even asked for.

### 3.5 Security surface is smaller with session auth
API+SPA architectures commonly end up storing bearer/API tokens in `localStorage` or similar client-side storage to talk to the API, which is a real XSS-exposure difference from httpOnly session cookies (Laravel Sanctum's SPA mode avoids this by using cookies too, but at that point the "decoupled API" is already using the same session-cookie mechanism Inertia uses natively — the decoupling stops buying anything). The current site's session cookie is already correctly configured (`httponly`, `samesite=lax` — technical-audit.md §6); Inertia preserves that property without extra design work.

### 3.6 What the Teams scaffold does *not* resolve
The existing `Team`/`Membership`/`TeamRole`/`TeamPermission` scaffold is a **multi-tenant workspace** model (Owner/Admin/Member per team, invitation-based membership) — appropriate for a SaaS product with many customer organizations, not for a single-organization nonprofit CMS with one fixed set of admin users. This scaffold is **not** a good fit for saba.md §10.2's RBAC needs (Super Admin/Editor/Finance Manager/Viewer separating content access from financial data access) as-is. This is a separate, still-open decision — tracked in `docs/architecture/authorization-model.md` (Phase 4, not yet written) — and is **not** resolved by this ADR. Keeping Inertia does not mean keeping the Teams multi-tenancy concept; it means keeping the Inertia+Fortify+Vue application shell.

---

## 4. Alternatives Considered

| Option | Rejected because |
|---|---|
| Full API-first: separate Laravel API + standalone Vue 3 SPA (saba.md's literal spec) | No named consumer requires the decoupling (§3.3); throws away working MFA/passkey implementation (§3.4); requires bolting on SSR separately to avoid regressing the project's own top SEO priority (§3.1); adds real operational overhead for a resource-constrained team (§3.2). |
| Full Inertia, no public API at all | Would violate saba.md §11.1's explicit, specific requirement for a public JSON API — and forecloses future external consumers (AEO tooling, a possible future Partner/Donor Portal per §34.3) without a documented reason to do so. |
| **Hybrid: Inertia for the app, thin stateless `routes/api.php` for external consumers (chosen)** | Satisfies saba.md's API requirement literally, keeps the SEO/MFA/operational benefits of Inertia for the actual product. |

---

## 5. Consequences

**What this unblocks:**
- Phase 5 (Laravel Backend) can proceed using `Inertia::render()` for all public/admin pages, matching the AGENTS.md conventions already established for this repo.
- Phase 6 ("Vue Frontend") is reframed as Inertia page components under `resources/js/pages/`, not a standalone SPA project — the `resources/frontend/` structure in saba.md §13.2 does not apply; use the existing `resources/js/` structure instead.
- Admin MFA (saba.md §10.4) is largely already solved; Phase 7 (CMS) work should extend the existing Fortify 2FA flow rather than build a new one.
- The saba.md §11.1 public API endpoints (`GET /api/v1/pages`, `/programs`, `/stories`, etc.) still get built, as a separate `routes/api.php`, likely with Sanctum for any authenticated portions and public rate limiting per §11.3 for the open ones. This is now a **Phase 5 API task**, not the primary rendering path.

**What still needs a decision (tracked separately, not resolved here):**
- Whether to keep, replace, or strip the Teams/multi-tenancy scaffold in favor of a single-organization RBAC model (§3.6 above) — needed before Phase 5's RBAC/policy work starts.
- Sanctum configuration for the separate public API (token issuance for any authenticated API consumers, if/when one materializes).

**What this changes in prior Phase 1 documents:**
- `docs/project-overview.md` §7's "Engineering Context Note" is superseded by this ADR — update it to point here rather than describing the question as open.
- `docs/content-model.md` and `docs/product-requirements.md` needed no changes — both were written at the product/data level and are stack-agnostic; this decision only affects how they get implemented, not what gets built.
