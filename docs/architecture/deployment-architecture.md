# Deployment Architecture

**Phase:** 4 — Architecture
**Status:** Decided (design) — provisioning is a separate action item, not done by this document
**Inputs:** saba.md §25, §29, §30; AGENTS.md; existing `.github/workflows/tests.yml`, `composer.json`, `.env.example`

---

## 1. What Already Exists (don't rebuild this)

The repository already has real CI infrastructure, not a blank slate:

- **`.github/workflows/tests.yml`** runs on every push to `main` and every PR: checks out code, sets up PHP 8.3 + Node 22, runs `composer setup` (installs deps, copies `.env`, generates app key, migrates, `npm install && npm run build`), then `composer ci:check`.
- **`composer.json`'s `ci:check` script** already chains `npm run lint:check` (ESLint), `npm run format:check` (Prettier), `npm run types:check` (`vue-tsc` for the frontend, `phpstan analyse` for PHP — Larastan is already a dev dependency at level per `phpstan.neon`), and `php artisan test` (Pest).
- **`.github/dependabot.yml`** already watches GitHub Actions dependencies for updates weekly.
- **`.gitignore`** already correctly excludes `.env` and other secrets-bearing files — confirmed no leakage risk today.
- **`.env.example`** already sets `BCRYPT_ROUNDS=12`, satisfying saba.md §24.2's password-hashing requirement out of the box.

This means saba.md §29's "lint → static analysis → unit tests → feature tests → build frontend" pipeline stages are **already implemented**, just not yet extended to cover security auditing or an actual deploy step. The work here is additive, not a rebuild.

## 2. Gaps Against saba.md §29's Full Pipeline

| saba.md §29 stage | Current state | Gap |
|---|---|---|
| Install deps, lint, static analysis, unit/feature tests, build frontend | ✅ Done (`tests.yml` + `ci:check`) | None |
| Security checks (`composer audit`, `npm audit`) | ❌ Not in the workflow | Add as a `ci:check` step or a separate CI job |
| Merge to `develop` → deploy to staging | ❌ No `develop` branch workflow, no staging deploy | Needs a second workflow, gated on the `develop` branch |
| E2E tests on staging | ❌ Not present (no E2E suite exists yet at all — saba.md §26.3) | Depends on Phase 10 (Testing) building the E2E suite first; the deploy pipeline just needs a step to run it once it exists |
| Merge to `main` → deploy to production → smoke tests | ❌ No production deploy workflow | Needs a third workflow (or an extension of the staging one, gated by branch/environment) |

**Recommendation:** extend `tests.yml` with a `composer audit && npm audit --audit-level=high` step now (cheap, no dependency on anything else being built first) rather than waiting for the full deploy pipeline to add it.

---

## 3. Environments

Per saba.md §30, four environments, mapped to this project's actual branch strategy (saba.md §28):

| Environment | Branch | Purpose | Database |
|---|---|---|---|
| Local | any `feature/*`/`fix/*` | Developer machines | SQLite (already the `.env.example` default — fine for local dev, matches saba.md's "never use production credentials locally" rule trivially since there's no shared credential involved at all) |
| Testing | CI (`tests.yml`) | Automated test runs | SQLite in-memory or a throwaway MySQL service container — decide at Phase 5 implementation time based on whether any MySQL-specific SQL (full-text search, JSON column behavior) makes SQLite an unsafe stand-in |
| Staging | `develop` | Pre-production, stakeholder UAT (saba.md §32 Phase 13) | MySQL, mirroring production version |
| Production | `main` | Live site | MySQL |

**Staging must mirror production** (saba.md §30) — same PHP version (8.3+), same MySQL version, same extensions. This matters concretely for this project because Phase 13's UAT (saba.md §32) is where Tim/Cathy Woller and the board actually validate the Transparency Center, donation flow, and content before go-live — if staging drifts from production, that validation doesn't actually de-risk launch.

---

## 4. Hosting: Laravel Cloud (proposed default, not yet provisioned)

AGENTS.md already states the intended host: *"Laravel can be deployed using Laravel Cloud, which is the fastest way to deploy and scale production Laravel applications."* This aligns with ADR-001's single-deploy-unit architecture (`docs/architecture/system-architecture.md` §1) — Laravel Cloud natively runs the app server, queue workers, and scheduler for one Laravel application without needing separately orchestrated infrastructure (no Kubernetes, no hand-rolled supervisor config for queue workers), which fits a small board-run nonprofit with no dedicated ops person.

**What Laravel Cloud would provide out of the box, mapped to this project's needs:**
- App hosting with zero-downtime deploys (satisfies part of saba.md §29's "deploy to production" stage)
- Managed queue workers (needed for `docs/architecture/system-architecture.md` §2's `Jobs` component — email, media variants)
- Managed scheduler (needed for saba.md §16.3's quarterly content-audit reminders, sitemap regeneration per `docs/product-requirements.md` §5, and the backup job in §6 below)
- Environment variable management per environment (satisfies saba.md §30's "environment-specific `.env` files managed securely" without needing a separate secrets tool like 1Password for this specifically)
- Managed Redis (needed for sessions/cache/queue per `docs/architecture/system-architecture.md` §2 — currently `.env.example` defaults to `database` for session/cache/queue, which is fine for local dev but should move to Redis in staging/production)

**This is a recommendation, not a completed action.** Actually creating a Laravel Cloud account, provisioning environments, and wiring billing is a real-world action with cost and access implications — that needs to happen with whoever holds organizational billing authority (likely Tim/Cathy Woller or the board), not autonomously as part of this documentation pass. If Laravel Cloud turns out not to be viable (budget, or a preference for infrastructure the org already has elsewhere), the architecture in `docs/architecture/system-architecture.md` doesn't assume Laravel Cloud specifically — any host that can run a Laravel app + MySQL + Redis + queue workers + a scheduler satisfies it (e.g., a traditional VPS with Laravel Forge, which saba.md §30 also gestures at via "Laravel Forge env manager").

---

## 5. CI/CD Pipeline (target state)

Building on what exists (§1) rather than replacing it:

```
Push to feature/fix branch
  → [existing] composer setup, composer ci:check (lint, format, types, tests)
  → [new] composer audit, npm audit --audit-level=high
  → PR review required (saba.md §28) before merge

Merge to develop
  → [new] Deploy to staging (Laravel Cloud staging environment, or equivalent)
  → [new, once Phase 10 builds it] E2E test suite runs against staging

Merge to main (requires develop → main PR, passing staging E2E)
  → [new] Deploy to production
  → [new] Production smoke tests (saba.md §32 Phase 14's checklist — homepage,
    donation flow in Stripe test mode, contact form, newsletter, admin login+MFA,
    HTTPS/HSTS, sitemap/robots.txt reachable)
```

The E2E-on-staging step is explicitly gated on Phase 10 (Testing) existing first — sequencing this before an E2E suite exists would just be a no-op step; it's listed here so the pipeline shape is right when that phase lands, not built as a stub now.

---

## 6. Backup & Disaster Recovery (saba.md §25)

| Requirement | Design |
|---|---|
| Database: daily automated dump, 30-day retention | Scheduled job (Laravel's scheduler, per §4) triggers a `mysqldump` (or Laravel Cloud's managed backup feature, if it provides one) to the off-site target below |
| Media: daily sync to off-site storage | `docs/architecture/system-architecture.md` §2's S3-compatible storage **is** the primary media store, not a secondary copy — so "off-site" here means the DB backup and an `.env`/config backup joining media in that same S3-compatible bucket, in a **separate geographic region** from the primary app/database host (saba.md §25.1's explicit requirement) |
| Configuration: encrypted `.env` backup, stored separately | Application secrets exported and encrypted (e.g., via `age` or GPG) as part of the same backup job, stored alongside but logically separate from the DB dump — never committed to git regardless |
| Restore tested quarterly | A documented, actually-executed runbook (saba.md §25.2: *"a backup never tested is not verified"*) — this needs to be a recurring calendar item for whoever owns operations post-launch, not just a document that exists once. Tracked as an operational commitment in `docs/operations-manual.md` (saba.md §31's required doc, not yet written — a Phase 5+ deliverable once there's an actual running system to write an operations manual for) |
| RTO < 4 hours, RPO < 24 hours | Directly follows from "daily backup + a documented restore procedure" — no additional infrastructure needed to hit these targets at this site's scale, but the restore procedure has to actually exist and be rehearsed for the RTO target to be real rather than aspirational |

---

## 7. Monitoring & Error Tracking (saba.md §29 Phase 8, referenced from `docs/architecture/system-architecture.md` §2)

Not yet fully specified — two lightweight V1 choices, deferred to Phase 5/8 implementation for actual account setup (same "documented recommendation, not provisioned" caveat as §4):
- **Uptime monitoring:** a simple external ping-based checker (e.g., a free/low-cost uptime monitor) against the homepage and the donation page specifically — those two are non-negotiable for the Donor Trust Loop, so their availability is worth monitoring separately from a generic "site is up" check.
- **Error tracking:** Sentry or Flare (saba.md §8's Phase 8 list) — either integrates cleanly with Laravel via a single package install; the choice between them isn't architecturally significant (both give exception capture + alerting), so it's fine to leave as a Phase 8 implementation-time decision rather than one that needs to be litigated now.
