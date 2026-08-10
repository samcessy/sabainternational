# Content Model — Saba International

**Phase:** 1 — Product Strategy
**Status:** Draft, pending stakeholder review
**Inputs:** saba.md §6–9, §12, §20; `docs/audit/content-inventory.md`, `docs/audit/current-website-audit.md`
**Feeds into:** `docs/architecture/database-erd.md` (Phase 4) — this document defines entities and fields at the product level; Phase 4 turns them into actual schema/migrations.

---

## 1. Design Principles Driving This Model

Three audit findings shape every entity below, not just the obvious ones:

1. **The CMS must make irresponsible publishing structurally harder than responsible publishing** (saba.md §35.8). Concretely: Sammy Tongoi's bio is currently public with literal placeholder text "TBD" (audit F-9). That's not a copy problem, it's a schema problem — `TeamMember.bio` must be a required field the record cannot be published without, not an optional field that happens to be empty today.
2. **Some content types need a relationship type that isn't fixed yet.** The Hunter Initiative's relationship to Saba is unresolved (audit: stakeholder verification item 2). Rather than hard-coding "program" or "partner" as separate tables, `Program` carries a `relationship_type` field so this can be corrected via CMS edit, not a migration, once verified.
3. **Sensitive-subject content needs governance fields from day one**, not bolted on later. The Nest's description involves children of incarcerated mothers (content-inventory.md flags this explicitly); Scott Organ's bio names adopted children who are minors. Every entity that can reference a person gets the consent/sensitivity fields from saba.md §7.3, even in V1.

---

## 2. Core Entities

### 2.1 Page
Generic CMS-managed static page (About, Our Approach, FAQ, etc.) — for content that isn't a Program, Story, or Team Member.
```
id, title, slug, body (rich text), seo_title, seo_description, og_image,
status (draft/review/published/archived), published_at, updated_at, author_id
```

### 2.2 Program
Represents both "Our Work" pillars and the specific Programs & Partners entries (New Dawn, Bethel Kibera School, The Nest, The Hunter Initiative). One table, not two, because the audit found the current site's own boundary between "program" and "partner" is blurry (Hunter's "View site" link points back to Saba's own homepage rather than an external site — technical-audit.md §5).

```
id, name, legal_name (nullable — PENDING VERIFICATION for New Dawn's
  Centre/Center spelling, Bethel's exact legal name), slug, category
  (Education/Nutrition/Shelter & Family Support/Youth Economic Empowerment
  — see information-architecture.md §2), relationship_type
  (official_program/independent_partner/unconfirmed — defaults to
  'unconfirmed' until stakeholder verification resolves it),
external_url (nullable — New Dawn/Bethel/Nest have real external sites
  today; Hunter Initiative does not per the audit and should NOT default
  to linking back to Saba's own homepage — that's a content bug worth
  fixing, not replicating),
founded_year, location, short_description, overview (rich text: history,
  local context, Saba's relationship — per the Problem→Context→Role
  narrative structure, saba.md §6.1),
what_happens_here (rich text),
sensitive_content_classification (none/moderate/high — required field;
  The Nest defaults to at least 'moderate' given its subject matter),
status, published_at, updated_at
```
Relationships: `has many` ImpactMetric, Story, Document, Media (via `program_id`).

### 2.3 TeamMember
```
id, name, role, bio (rich text, REQUIRED — cannot be null or a
  placeholder string; publish is blocked if empty, closing audit F-9),
photo_media_id (nullable), board_member (boolean),
joined_date (nullable — PENDING VERIFICATION for exact dates beyond
  what the audit found: Scott Organ 2011, Ryan Shaw 2015, Helen Kahl 2007),
consent_to_publish (boolean, required — especially relevant for bios
  that reference minors or sensitive personal history, e.g. Scott Organ's
  adoption story), display_order, status, published_at, updated_at
```
**Publish rule:** a `TeamMember` record cannot transition to `published` while `bio` is null, empty, or matches a placeholder-detection pattern (e.g., "TBD", "Coming soon"). This is a direct structural fix for audit F-9.

### 2.4 Story
Per saba.md §7.2, extended with the governance fields from §7.3 (these are not optional extras — every field below is required for any story that depicts a real person):
```
id, title, slug, excerpt, body (rich text, supports images/video embeds),
featured_image_media_id, author_id, published_at, updated_at,
category, program_id (nullable), location, tags,
seo_title, seo_description, og_image,
status (draft/review/published/archived), featured (boolean),
story_type (story_of_change/program_update/news/volunteer_story/
  donor_story/partner_story/founder_story/youth_story/community_story)

-- Governance fields, required whenever the story depicts an identifiable person:
consent_status (yes/no/guardian/not_required),
image_consent (yes/no/anonymized),
guardian_consent (nullable — required if subject is a minor),
anonymity_requested (boolean),
sensitive_content_classification (none/moderate/high),
approval_stage (draft/editor_review/admin_approval/published),
attribution (photographer, source, date)
```
**Publish rule:** cannot transition to `published` while `consent_status` is unset, mirroring the `TeamMember.bio` rule above. This directly implements saba.md §7.3's "responsible publishing easier than irresponsible publishing" principle and closes the gap the audit found around The Nest's and Scott Organ's content (content-inventory.md, "Consent-sensitive content" section).

**Content-migration note:** the single existing blog post ("Bethel Kibera School Website," audit F-8) migrates as `story_type = news`, `status = archived` per the content inventory's Archive flag, not as a featured V1 launch story.

### 2.5 Campaign
Minimal V1 version — see `docs/product-requirements.md` for why full campaign management is deferred:
```
id, name, slug, description, goal_amount (nullable), currency (USD only
  in V1), start_date, end_date (nullable), featured_image_media_id,
impact_statement, suggested_amounts (array), status
```
**Rule carried from saba.md §8.5:** never imply a fixed donation amount produces a guaranteed outcome unless verified — `impact_statement` copy must be reviewed against this before publish.

### 2.6 Supporter / Donation / DonationTransaction
```
Supporter: id, name, email, communication_preferences (opt-in/opt-out),
  created_at

Donation: id, supporter_id, campaign_id (nullable — defaults to a
  single 'General Fund' campaign in V1, see product-requirements.md),
  program_designation (nullable — General/New Dawn/Bethel/Nest/Hunter,
  per saba.md §8.2), amount, currency (USD only, V1), frequency
  (one_time/monthly/quarterly/annual), anonymous (boolean), status

DonationTransaction: id, donation_id, gateway (stripe — V1; see
  product-requirements.md for gateway abstraction scope), gateway_reference,
  status (pending/succeeded/failed/refunded), receipt_sent_at, created_at
```
No raw card data anywhere in this model, per saba.md §8.3/§24 — payment tokenization happens entirely at the gateway.

### 2.7 Document (Annual Reports, Financial Documents, Policies)
```
id, title, document_type (annual_report/financial_report/policy/other),
year (nullable), summary, file_media_id, cover_image_media_id,
published_at, status
```
**V1 reality check (content-inventory.md):** zero documents currently exist on the live site. This entity ships with a real empty state, not fabricated placeholder reports — see saba.md §35.1.

### 2.8 ImpactMetric / ImpactMetricValue
Structured per saba.md §6.3's governance requirement — every value carries its own provenance, it isn't just a number on a page:
```
ImpactMetric: id, program_id (nullable), name, unit

ImpactMetricValue: id, metric_id, value, time_period, data_source,
  verification_status (verified/unverified/estimated), last_updated_at
```
**Rule:** a metric with no `ImpactMetricValue` in `verified` status renders as a qualitative statement on the frontend instead (saba.md §6.3's fallback), never a fabricated number. Given the audit found zero existing verified statistics on the current site, expect most V1 program pages to launch in qualitative mode.

### 2.9 Event
```
id, title, slug, description, start_at, end_at, location,
featured_image_media_id, status
```
V2 scope per information-architecture.md — modeled now so it isn't a rushed schema addition later, but no dedicated events UI ships in V1.

### 2.10 Media
```
id, filename, alt_text (REQUIRED — cannot publish without it, saba.md
  §14.1), caption, photographer, copyright_license,
consent_status (yes/no/anonymized — linked to Story/TeamMember consent
  where applicable), program_id (nullable), story_id (nullable),
exif_data (nullable), variants (thumbnail/small/medium/large/hero/social
  — generated on upload per saba.md §20.2)
```

### 2.11 Engagement Entities
```
NewsletterSubscriber: id, email, consent_timestamp, consent_ip,
  frequency_preference, status (subscribed/unsubscribed), unsubscribed_at

ContactSubmission: id, name, email, country, organization, subject
  (general/donation/partnership/volunteer/media), message,
  status (new/in_progress/responded/closed/spam), created_at

VolunteerApplication: id, name, email, details, status, created_at

PartnershipInquiry: id, organization_name, contact_name, email,
  details, status, created_at
```
All four carry a required consent checkbox at submission time (saba.md §23.1) — modeled at the form/validation layer, not as a stored field, since consent-to-be-contacted is implicit in submitting the form itself; GDPR export/deletion applies to `NewsletterSubscriber` specifically per saba.md §22.

---

## 3. Entities Explicitly Deferred (modeled in saba.md, not in V1)

Per `docs/product-requirements.md`'s prioritization, these exist in saba.md's full schema (§12.1) but are not needed until V2/Future and are omitted here to avoid building schema for features that won't ship: `redirects` (needed once real URL migration happens — Phase 12, not Phase 1), multi-gateway payment tables beyond Stripe, donor-portal-specific tables, partner-portal submission tables.

---

## 4. Open Questions Blocking Full Schema Lock (Phase 4)

1. Final program taxonomy sign-off (information-architecture.md §2).
2. Hunter Initiative's `relationship_type` — resolves whether it needs partner-specific fields (e.g., a real `external_url`) that don't currently apply.
3. Legal name verification for `Program.legal_name` (New Dawn, Bethel Kibera School).
4. Whether "Fundraise" (peer-to-peer, saba.md §8.5's implied use) is ever wanted — affects whether `Campaign` needs a `created_by_supporter_id` field in the future, deferred for now.
