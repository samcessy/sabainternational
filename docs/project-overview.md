# Project Overview — Saba International Digital Platform

**Phase:** 1 — Product Strategy
**Status:** Draft, pending stakeholder review
**Inputs:** `docs/audit/current-website-audit.md`, `docs/audit/technical-audit.md`, `docs/audit/content-inventory.md`, `saba.md`

---

## 1. What This Is

A rebuild of Saba International's public website and supporting CMS/donation infrastructure, replacing a single-page brochure site that has no donation flow, no trust signals, and effectively one piece of content published in three years (audit F-1, F-8, F-10). The rebuild is scoped for a small, board-run nonprofit — phased, not a big-bang relaunch (saba.md §1.4).

This document sets the vision and goals. It does not restate the audits — read those first for the evidence behind every priority below.

---

## 2. Product Vision

> **Give Saba International a website that a first-time visitor trusts enough to donate to within one visit, and that the board can keep current without a developer.**

Two failure modes define what "done" means here, both drawn directly from the audits:

1. **The trust gap.** A donor or partner arriving today finds no EIN, no financials, no governance info, a "TBD" team bio, and a donation form with one dollar amount and broken spam protection (audit F-1, F-7, F-9, F-10). The rebuild's first job is closing that gap — not adding features on top of it.
2. **The staleness trap.** The current site's last real content update was three years ago (audit F-8). A beautiful relaunch that goes stale again in a year has failed by the same measure as the current site. Every content-producing feature ships with a mechanism that makes staleness visible (freshness dashboard, quarterly audit reminders — saba.md §16.3) rather than assuming good intentions will keep it current.

Everything else — design system, search, multi-currency, AI SEO pages — is in service of those two problems or is explicitly deferred (see `docs/product-requirements.md`).

---

## 3. Goals

### 3.1 Primary Goal: Make the Donor Trust Loop Work End-to-End
```
Homepage → Transparency Center → Donate → Receipt → Impact Update → Recurring Giving
```
(saba.md §3.2). This loop currently breaks at almost every step (audit F-1, F-10). V1 is not "done" until a real donor can complete it without encountering a placeholder, a broken widget, or an unanswered "is this organization real?" question.

### 3.2 Secondary Goals
- **Make the site findable.** Current state: zero per-page SEO metadata, no sitemap, identical page titles everywhere (audit F-2, F-3, F-4). Goal: every indexable page has real metadata and the site is submitted to Search Console before launch.
- **Make content survivable.** Editorial calendar + freshness dashboard in place before declaring V1 done, not as a V2 nice-to-have (saba.md §3.3).
- **Make privacy defensible.** Current site loads GA4 unconditionally with no consent mechanism (audit F-5) — a real gap for an org courting international and diaspora donors. Goal: no non-essential tracking fires without consent, and a real Privacy Policy exists.
- **Don't regress what's already correct.** HTTPS redirect, basic security headers, and server-rendered HTML are already right (technical-audit.md §8, "Positive Observations"). The rebuild should preserve these properties, not accidentally lose them in a framework switch.

### 3.3 Explicit Non-Goals for V1
Per saba.md §1.4's resource-constraint acknowledgment, the following are deliberately out of scope for V1 even though saba.md describes them — see `docs/product-requirements.md` for the full V1/V2/Future breakdown:
- MPesa / diaspora local payments
- Donor portal, partner portal, volunteer portal
- AI assistant, semantic search
- Multi-currency beyond USD
- Full campaign management UI (a single active "General Fund" designation is enough to start)

---

## 4. KPIs — Framework, Not Fabricated Targets

**No baseline data exists.** The current site runs GA4 (measurement ID `G-4BXY4L25CX`, per technical-audit.md §1), but this audit had no access to that account's historical data, and the current donation flow may not even reliably capture conversions given its incomplete state. Per saba.md's "never invent statistics" rule (§35.9), the KPIs below are defined as **what to measure**, not target numbers — targets get set once 60–90 days of real post-launch data exists, or once the current GA4 account's historical data is obtained from stakeholders.

| KPI | Why it matters | How it's measured |
|---|---|---|
| Donation conversion rate | Direct measure of whether the Trust Loop works | `donation_started` → `donation_completed` funnel (saba.md §18.2, §18.4) |
| Donation completion rate | Detects checkout friction specifically | `donation_completed` / `donation_started` |
| Recurring donor rate | Sustainability signal — one-time gifts don't fund ongoing programs | % of completed donations with `frequency = monthly/quarterly/annual` |
| Newsletter conversion rate | Cheapest engagement signal; feeds the "Existing Supporters" journey | `newsletter_signup` / sessions |
| Partner inquiry rate | Tracks whether the Partner audience journey (saba.md §3.1) actually converts | `partnership_inquiry` submissions / sessions on Partner-related pages |
| Volunteer conversion rate | Same, for the Volunteer audience | `volunteer_application` / sessions |
| Organic search traffic | Direct measure of the SEO gap closing (currently ~zero technical SEO exists — audit F-2) | Search Console + analytics, tracked from a pre-launch zero baseline |
| Content freshness | Leading indicator against the staleness trap (§3.3 above) | CMS dashboard: % of published content updated within the last 12 months |
| Story engagement | Whether "Stories of Change" actually gets read | Time on page, scroll depth on story pages |
| Returning visitor rate | Whether the site earns repeat engagement vs. one-and-done | Analytics session data |

**First real target-setting exercise:** once V1 ships and 90 days of data exist, or once historical GA4 data from the current site is obtained — whichever comes first.

---

## 5. Audiences (carried from saba.md §3.1, unchanged — no audit evidence contradicts this framing)

Donors, Potential Partners, Volunteers, Existing Supporters, Beneficiary Communities, Researchers/Media, Kenyan Diaspora. See `docs/information-architecture.md` for how each audience's primary journey maps to the sitemap.

---

## 6. Open Decisions Requiring Stakeholder Input

Carried forward from `docs/audit/current-website-audit.md` — Phase 1 planning proceeds without these being resolved, but V1 cannot ship without them:

1. Canonical mission statement (current site's programmatic phrasing vs. saba.md's aspirational phrasing — audit F-11). **Working assumption for this planning phase:** use the current, actually-published site copy as the source of truth per saba.md §1.3 ("the existing website is the source of truth for organizational facts"), and treat the saba.md phrasing as a copywriting proposal pending sign-off — not the other way around.
2. The Hunter Initiative's official relationship to Saba (program vs. independent partner) — affects IA and content model (see `docs/content-model.md` §Partners).
3. Exact legal program names (Centre/Center; Bethel Kibera School naming).
4. 501(c)(3) EIN, tax-exempt letter, financial reports — hard blocker for the Transparency Center, not a nice-to-have.
5. Sammy Tongoi's real bio.

---

## 7. Engineering Context Note — Resolved in Phase 4

**Decided:** see `docs/architecture/adr-001-inertia-vs-api-spa.md`. The application is built as an Inertia app (not a decoupled API+SPA), with a separate thin public JSON API for saba.md §11.1's external-consumer requirements. The existing Teams/multi-tenancy RBAC scaffold is *not* reused as-is (it's a multi-tenant workspace model, not a fit for a single-organization CMS) — that's tracked as a still-open Phase 4 item in the ADR's §5.
