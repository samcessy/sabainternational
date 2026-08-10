# Product Requirements — Feature Matrix & Prioritization

**Phase:** 1 — Product Strategy
**Status:** Draft, pending stakeholder review
**Inputs:** saba.md §5–26, `docs/project-overview.md`, `docs/audit/*`

---

## 1. Prioritization Principle

saba.md itself says not to build all of this in V1 (§1.4) and to ship the Donor Trust Loop first (§3.2). This document is the concrete cut. The test applied to every feature below:

> **Does this feature sit on the path Homepage → Transparency Center → Donate → Receipt → Impact Update → Recurring Giving, or does it directly fix a Critical/High finding from the audit? If not, it's V2 or Future — even if saba.md describes it in full.**

Three ratings:
- **V1 — Must-Have.** Ships before launch. Missing it means the Trust Loop is broken or a Critical/High audit finding stays open.
- **V2 — Next Quarter.** Real value, not launch-blocking. Matches saba.md §1.4's "iterate quarterly."
- **Future.** Explicitly deferred; needs volume/evidence/resourcing this org doesn't have yet (saba.md §34.3 lists several of these itself as post-launch).

---

## 2. Trust & Transparency (saba.md §9) — the highest-leverage category

| Feature | Rating | Why |
|---|---|---|
| Transparency Center page (EIN, tax-exempt letter, governance, key policies) | **V1** | Audit F-10: zero trust signals exist today; this is the single largest credibility gap for Donors and Partners. |
| Annual report archive (structured, downloadable) | **V1** | Ships with real empty state if no reports exist yet (content-inventory.md) — the page and upload mechanism must exist even if the first report is still `CONTENT REQUIRED`. |
| "Where Your Money Goes" breakdown | **V1 — conditional** | Only publishes once verified by Saba's financial team (saba.md §9.3); the page structure ships in V1, the content may launch as `CONTENT REQUIRED` if verification isn't done in time. |
| Board governance structure + bios | **V1** | Blocked today on Sammy Tongoi's real bio (audit F-9) — CMS must refuse to publish a placeholder, per content-model.md §2.3. |
| Google for Nonprofits / Guidestar / Wikidata registration | **V2** | Requires the Transparency Center content to exist first (chicken-and-egg); also an external-process task, not a build task. |

## 3. Donation & Fundraising (saba.md §8)

| Feature | Rating | Why |
|---|---|---|
| One-time donation, Stripe Elements (not a bare Checkout redirect) | **V1** | Core of the Trust Loop; current implementation is a near-empty shell with one amount and broken spam protection (audit F-1, F-7). |
| Recurring donation (monthly) | **V1** | Directly tied to the "Recurring donor rate" KPI in project-overview.md §4. |
| Recurring donation (quarterly/annual) | **V2** | Monthly covers the sustainability goal; quarterly/annual add checkout complexity for marginal V1 value. |
| Suggested amounts ($25/$50/$100/$250/$500/Custom) | **V1** | The current site has exactly one visible amount — this is a direct, cheap fix to a Critical finding. |
| Donation designation (General/New Dawn/Bethel/Nest/Hunter) | **V1** | Content-model.md §2.6; no evidence this is hard to build, high trust value. |
| Digital receipt (emailed) | **V1** | Non-negotiable — "Receipt" is a named step in the Trust Loop itself. |
| Donor abandonment recovery email | **V2** | Real value, but depends on donation-start tracking being solid first; not launch-blocking. |
| Multi-currency (KES/GBP/EUR) + geo-detection | **Future** | saba.md itself marks KES/GBP/EUR as "future" (§8.2) and MPesa as post-launch (§34.3). USD-only does not block the Trust Loop for the primary Donor audience. |
| Full campaign management UI (admin-created campaigns with goals/timelines) | **V2** | V1 ships one seeded "General Fund" campaign (content-model.md §2.5); a campaign *authoring* UI is not needed until there's a second campaign to create. |
| Payment gateway abstraction (Stripe/PayPal/MPesa/Future interfaces) | **V1 — Stripe only** | Build the `PaymentGatewayInterface` per saba.md §8.3 so PayPal/MPesa are additive later, but only implement `StripeGateway` now. Building unused gateway adapters violates the "don't build for hypothetical futures" principle. |

## 4. Content & Storytelling (saba.md §7, §16)

| Feature | Rating | Why |
|---|---|---|
| Story content type with governance/consent fields | **V1** | content-model.md §2.4 — the CMS must structurally prevent another "TBD" or unconsented sensitive-content incident (audit F-9, content-inventory.md consent flags). |
| 3–5 seeded real stories at launch | **V1** | Audit F-8: launching with the same one-story problem the current site has defeats the purpose of the rebuild. This is a content task, not just a feature — flagged here because it's launch-blocking. |
| Editorial calendar template + quarterly publishing cadence | **V1** | saba.md §3.3's Content Sustainability Mandate — directly prevents the failure mode this project exists to fix. |
| CMS "content freshness" dashboard | **V1** | Same rationale; also a tracked KPI (project-overview.md §4). |
| Auto-archive workflow for stories >3 years without verification | **V2** | Valuable but not needed until the content library is old enough for it to matter — V1 launches with fresh content by construction. |
| Dignified photography policy + "photographed with consent" badge | **V1** | Direct implementation of saba.md §7.3's public trust feature; cheap to build, high trust value, and closes a real governance gap the audit found (no consent metadata exists today). |
| Full 9-type story taxonomy (Story of Change/Program Update/News/Volunteer/Donor/Partner/Founder/Youth/Community) | **V1 — schema only** | The field exists in content-model.md §2.4 from day one so categorization is never a later migration, but not all 9 types need seeded content at launch — only as many as real verified stories support. |

## 5. SEO & Findability (saba.md §15)

| Feature | Rating | Why |
|---|---|---|
| Per-page metadata (title/description/OG/canonical) as required CMS fields | **V1** | Audit F-2: currently zero pages have any of this — Critical finding, cheap fix, huge leverage on the "make the site findable" goal. |
| `/sitemap.xml` auto-generated | **V1** | Audit F-3: currently 404. |
| `robots.txt` rebuilt deliberately (disallow admin/API) | **V1** | Audit F-4/F-6 security overlap — current file permits everything with nothing to hide, but the new site has an admin panel and API that must not be crawlable. |
| Structured data (Organization, Article, BreadcrumbList, DonateAction, FAQPage) | **V1** | Low marginal cost once the content model exists; directly supports Google for Nonprofits eligibility (a V2 external registration) and AEO. |
| 301 redirects from old URLs | **V1** | Only a handful of real old URLs exist (audit Route Inventory: `/`, `/posts`, `/posts/{slug}`, `/categories/{slug}`, `/difference/create`) — small, bounded task, prevents losing what little search equity exists. |
| AI Search / AEO answer pages (What is Saba, How can I support, etc.) | **V2** | Genuinely useful but not Trust-Loop-blocking; do after the core content types (Program, Story, Transparency) exist to answer these questions accurately. |
| External registrations (Google for Nonprofits, Wikidata, Guidestar) | **V2** | See §2 above — sequenced after Transparency Center content is real. |

## 6. CMS / Admin (saba.md §10)

| Feature | Rating | Why |
|---|---|---|
| Content management for Pages, Stories, Programs, Team, Media | **V1** | The whole point of the rebuild is that the board can update content without a developer (project-overview.md §1). |
| RBAC: Super Admin, Administrator, Editor, Content Manager, Finance Manager, Communications Manager, Viewer | **V1 — reduced set** | Build Super Admin, Editor, Finance Manager, Viewer for V1 (covers the actual separation that matters: content vs. money vs. read-only). Content Manager/Communications Manager as further splits of Editor are **V2** — a board-run nonprofit with a handful of admin users doesn't need 7 roles distinguished on day one, and Spatie Permission makes adding roles later cheap. |
| Admin MFA (TOTP) | **V1** | saba.md §35 rule #5 equivalent for security — non-negotiable for any account touching donor data. |
| Audit log | **V1 — core actions only** | Publish/unpublish, delete media, change donation status, modify permissions. Full saba.md §10.3 list (export donor data, campaign settings) can extend in V2 once those admin features themselves exist. |
| Publishing workflow (draft → review → published) | **V1** | Already required structurally by the Story/TeamMember consent-gating in content-model.md — not extra work, it's the same mechanism. |
| Dashboard: content freshness alerts, pending approvals, recent donations | **V1** | Directly supports the "make content survivable" goal. |
| Fundraising admin: Campaigns/Donations/Transactions/Supporters | **V1 — read/manage only, not full campaign authoring** | Admins need to see and manage donations from day one; campaign *creation* UI is V2 per §3 above (one seeded campaign is enough at launch). |

## 7. Engagement: Forms, Newsletter, Contact (saba.md §21–23)

| Feature | Rating | Why |
|---|---|---|
| Contact form (real page, not a 405-only POST endpoint) | **V1** | Audit: `/contact` currently returns 405 with no GET-able page — a basic, cheap fix. |
| Newsletter signup with real consent capture (timestamp + IP) | **V1** | Audit F-5's GDPR gap; also a tracked KPI. |
| Volunteer application form | **V1** | Named CTA in the sitemap; low build cost. |
| Partnership inquiry form | **V1** | Same. |
| Spam protection that actually works (honeypot + reCAPTCHA v3 with a real site key) | **V1** | Audit F-7: current reCAPTCHA ships with an empty site key — non-functional. This is a direct, specific regression to fix, not a new feature. |
| Rate limiting (contact: 3/IP/hour, donation: 5/IP/hour) | **V1** | saba.md §8.3/§23.2 — cheap, standard, closes a real gap since nothing suggests current rate limiting works either. |
| Transactional emails (receipt, confirmations, admin notifications) | **V1** | Required for the Trust Loop's "Receipt" step and for forms to be useful to admins at all. |
| Donor abandonment recovery email | **V2** | Per §3 above. |
| Double opt-in newsletter (if jurisdiction requires) | **V2** | Needs a legal/jurisdiction answer from stakeholders first; single opt-in + clear consent capture is defensible for V1 launch. |

## 8. Search (saba.md §17)

| Feature | Rating | Why |
|---|---|---|
| Database full-text search (MySQL/Scout database driver) | **V2** | saba.md's own content volume ("small board-run nonprofit," audit: currently one blog post total) doesn't justify search infrastructure at launch — a handful of nav links covers V1's actual content. Build once Stories/Programs/Reports reach enough volume that browsing stops working. |
| Meilisearch upgrade | **Future** | Explicitly gated in saba.md §17 on content volume that doesn't exist yet. |

## 9. Analytics (saba.md §18)

| Feature | Rating | Why |
|---|---|---|
| Privacy-first analytics (Plausible/Fathom, or GA4 + IP anonymization + consent) | **V1** | Audit F-5: current GA4 fires unconditionally with no consent — a real compliance gap that must not carry into the rebuild. |
| Conversion event tracking (donation_started/completed, newsletter_signup, etc.) | **V1** | Every KPI in project-overview.md §4 depends on this existing from day one. |
| Funnel dashboards | **V2** | The events need to exist and accumulate data before a funnel view is useful. |

## 10. Cross-Cutting Non-Negotiables (not "features," apply to everything above)

These aren't separate line items — they're acceptance criteria on every V1 feature above, per saba.md §14/§19/§24 and the Definition of Done (§27):

- **Accessibility (WCAG 2.2 AA):** every V1 page/form, not a subset.
- **Performance (Core Web Vitals targets):** every V1 page, especially the donation flow.
- **Security (OWASP ASVS Level 1, CSRF/XSS/SQLi prevention, secure headers including the HSTS/CSP the audit found missing — technical-audit.md §6):** every V1 endpoint.
- **Responsive (320px–1440px+):** every V1 page.

A feature "ships" in V1 only when it meets these, per saba.md §27 — a donation flow that works but fails a screen-reader test is not V1-complete.

## 11. Explicitly Future (from saba.md §34.3, unchanged — no audit finding argues for pulling these forward)

Donor Portal, Public Impact Dashboard, Interactive Program Map, Partner Portal, Volunteer Portal, MPesa integration, AI Assistant, Semantic Search, WhatsApp newsletter.

---

## 12. Summary Table

| Category | V1 Must-Have | V2 Next Quarter | Future |
|---|---|---|---|
| Trust & Transparency | 4 | 1 | 0 |
| Donation & Fundraising | 6 | 3 | 1 |
| Content & Storytelling | 5 | 1 | 0 |
| SEO & Findability | 5 | 2 | 0 |
| CMS / Admin | 6 | 1 | 0 |
| Engagement | 6 | 2 | 0 |
| Search | 0 | 1 | 1 |
| Analytics | 2 | 1 | 0 |
| **Total** | **34** | **12** | **2** (+9 from saba.md §34.3) |

V1 is still substantial — 34 items — but every one of them either sits directly on the Donor Trust Loop or closes a Critical/High finding from the audit. Nothing in V1 is speculative.
