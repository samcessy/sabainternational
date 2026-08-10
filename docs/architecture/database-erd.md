# Database ERD

**Phase:** 4 — Architecture
**Status:** Decided, implementation-ready for Phase 5 migrations
**Inputs:** `docs/content-model.md`, `docs/architecture/authorization-model.md`, saba.md §12

This document turns `docs/content-model.md`'s product-level entities into table-level schema: keys, indexes, soft-delete policy, and relationships. Diagrams are grouped into three bounded contexts (Auth & System, Content & Media, Fundraising & Engagement) rather than one large ERD — at ~25 tables, a single diagram is unreadable; saba.md's own admin module grouping (§10.1) already implies these boundaries.

---

## 0. Deviations From saba.md §12.1's Table List

Each one is a deliberate simplification, not an oversight — rationale given so it isn't second-guessed later without context:

| saba.md §12.1 suggests | This ERD does instead | Why |
|---|---|---|
| Separate `roles`, `permissions`, `role_permission`, `user_role` tables | `admin_role` enum column on `users` | `docs/architecture/authorization-model.md` §2 — 4 fixed roles, no admin-configurable RBAC needed in V1. |
| `teams` (implied by the existing scaffold, not actually in saba.md's list) | Dropped entirely | authorization-model.md §1 — multi-tenancy doesn't fit a single-organization CMS. |
| Separate `seo_metadata` table (polymorphic) | SEO fields (`seo_title`, `seo_description`, `og_image`) inline on `pages`, `programs`, `stories`, `documents` | Only 4 content types need SEO metadata; a polymorphic table adds join complexity without a real payoff at this scale. |
| `program_media` join table | `media.program_id` direct nullable FK | Content-inventory found no evidence of media needing to belong to *multiple* programs simultaneously; a direct FK is simpler and can migrate to a pivot later if that need appears. |
| Implicit `program_designation` as a free-text/enum field | `donations.program_id` — a real nullable FK to `programs` | content-model.md §2.6 described this as an enum of program names; an FK avoids the enum drifting out of sync with the actual `programs` table as programs are added/renamed. |
| `partners` + `partner_programs` as separate from `programs` | Merged into one `programs` table with a `relationship_type` column | ADR-001 §3.6 / content-model.md §2.2 — the current live site's own boundary between "program" and "partner" is blurry (Hunter Initiative), so the schema doesn't force a premature split. |

Everything else below matches saba.md §12.1's entity list in substance, grouped differently only for diagram readability.

---

## 1. Auth & System

```mermaid
erDiagram
    USERS ||--o{ AUDIT_LOGS : performs
    USERS ||--o{ PASSKEYS : owns
    USERS ||--o{ USER_INVITATIONS : sends
    USERS ||--o{ USER_INVITATIONS : "redeemed by (nullable)"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum admin_role "super_administrator|editor|finance_manager|viewer"
        string two_factor_secret
        timestamp two_factor_confirmed_at
        timestamp created_at
        timestamp deleted_at "soft delete"
    }
    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        string entity_type
        bigint entity_id
        json old_values
        json new_values
        string ip_address
        string user_agent
        timestamp created_at
    }
    PASSKEYS {
        bigint id PK
        bigint user_id FK
        string credential_id UK
    }
    USER_INVITATIONS {
        bigint id PK
        bigint invited_by_user_id FK
        string email
        string code UK
        enum role
        timestamp expires_at
        timestamp accepted_at
    }
    SETTINGS {
        bigint id PK
        string key UK
        text value
    }
```

**Indexing:** `users.email` unique; `audit_logs.user_id`, `audit_logs.entity_type`+`entity_id` composite (audit lookups are almost always "show me the history of this record"); `user_invitations.code` unique; `settings.key` unique.

**Retention:** `audit_logs` is **never** soft-deleted or purged automatically — it's the record of privileged actions saba.md §10.3 requires. If retention limits are ever needed for storage reasons, that's an explicit archival job, not a default `deleted_at`.

---

## 2. Content & Media

```mermaid
erDiagram
    USERS ||--o{ PAGES : authors
    USERS ||--o{ STORIES : authors

    PROGRAMS ||--o{ STORIES : "relates to (nullable)"
    PROGRAMS ||--o{ IMPACT_METRICS : has
    PROGRAMS ||--o{ MEDIA : "tagged with (nullable)"

    MEDIA ||--o{ MEDIA_VARIANTS : has
    MEDIA ||--o{ TEAM_MEMBERS : "photo of (nullable)"
    MEDIA ||--o{ STORIES : "featured image (nullable)"
    MEDIA ||--o{ DOCUMENTS : "file / cover (nullable)"
    MEDIA ||--o{ EVENTS : "featured image (nullable)"

    STORIES ||--o{ MEDIA : "gallery (nullable)"
    STORIES }o--o{ STORY_TAGS : tagged

    IMPACT_METRICS ||--o{ IMPACT_METRIC_VALUES : has

    PAGES {
        bigint id PK
        string title
        string slug UK
        longtext body
        string seo_title
        string seo_description
        string og_image
        enum status "draft|review|published|archived"
        bigint author_id FK
        timestamp published_at
        timestamp deleted_at "soft delete"
    }
    PROGRAMS {
        bigint id PK
        string name
        string legal_name "nullable, PENDING VERIFICATION"
        string slug UK
        enum category "education|nutrition|shelter_family_support|youth_economic_empowerment"
        enum relationship_type "official_program|independent_partner|unconfirmed"
        string external_url "nullable"
        smallint founded_year "nullable"
        string location
        longtext overview
        longtext what_happens_here
        enum sensitive_content_classification "none|moderate|high"
        enum status
        timestamp published_at
        timestamp deleted_at "soft delete"
    }
    TEAM_MEMBERS {
        bigint id PK
        string name
        string role
        longtext bio "required to publish"
        bigint photo_media_id FK "nullable"
        boolean board_member
        boolean consent_to_publish
        smallint display_order
        enum status
        timestamp deleted_at "soft delete"
    }
    STORIES {
        bigint id PK
        string title
        string slug UK
        string excerpt
        longtext body
        bigint featured_image_media_id FK "nullable"
        bigint author_id FK
        bigint program_id FK "nullable"
        enum story_type
        enum consent_status "yes|no|guardian|not_required"
        enum image_consent "yes|no|anonymized"
        enum guardian_consent "nullable"
        boolean anonymity_requested
        enum sensitive_content_classification
        enum approval_stage "draft|editor_review|admin_approval|published"
        string seo_title
        enum status
        boolean featured
        timestamp published_at
        timestamp deleted_at "soft delete"
    }
    STORY_TAGS {
        bigint id PK
        string name
        string slug UK
    }
    MEDIA {
        bigint id PK
        string filename
        string path
        string alt_text "required to publish"
        string caption "nullable"
        string photographer "nullable"
        enum consent_status "yes|no|anonymized"
        bigint program_id FK "nullable"
        bigint story_id FK "nullable"
        json exif_data "nullable"
        timestamp created_at
    }
    MEDIA_VARIANTS {
        bigint id PK
        bigint media_id FK
        enum variant_type "thumbnail|small|medium|large|hero|social"
        string path
        smallint width
        smallint height
    }
    DOCUMENTS {
        bigint id PK
        string title
        enum document_type "annual_report|financial_report|policy|other"
        smallint year "nullable"
        text summary
        bigint file_media_id FK
        bigint cover_image_media_id FK "nullable"
        enum status
        timestamp published_at
    }
    IMPACT_METRICS {
        bigint id PK
        bigint program_id FK "nullable"
        string name
        string unit
    }
    IMPACT_METRIC_VALUES {
        bigint id PK
        bigint metric_id FK
        decimal value
        string time_period
        string data_source
        enum verification_status "verified|unverified|estimated"
        timestamp last_updated_at
    }
    EVENTS {
        bigint id PK
        string title
        string slug UK
        text description
        timestamp start_at
        timestamp end_at "nullable"
        string location
        bigint featured_image_media_id FK "nullable"
        enum status
    }
```

**Indexing:** every `slug` column (unique); every FK column; `stories.status` + `stories.published_at` composite (the "latest published stories" query runs on every homepage load); `media.program_id`, `media.story_id`; `impact_metric_values.metric_id` + `verification_status` (the "show qualitative fallback if nothing verified" query from content-model.md §2.8 depends on this).

**Soft deletes:** `pages`, `programs`, `team_members`, `stories` — the editorial content tables per saba.md §12.2's rule. `media`, `media_variants`, `story_tags`, `impact_metrics`, `impact_metric_values`, `documents`, `events` are **not** soft-deleted — they're either derived/dependent records (variants) or low-risk-to-hard-delete reference data; soft-deleting everything indiscriminately adds query complexity (`whereNull('deleted_at')` everywhere) without a matching recovery need.

**Publish guards** (enforced in the model/policy layer, not the schema — see `docs/content-model.md` §2.3/§2.4): `team_members.bio` and `stories.consent_status` block the `published` status transition when empty/unset.

---

## 3. Fundraising & Engagement

```mermaid
erDiagram
    CAMPAIGNS ||--o{ DONATIONS : receives
    SUPPORTERS ||--o{ DONATIONS : gives
    PROGRAMS ||--o{ DONATIONS : "designated to (nullable)"
    DONATIONS ||--o{ DONATION_TRANSACTIONS : "processed as"

    CAMPAIGNS {
        bigint id PK
        string name
        string slug UK
        text description
        decimal goal_amount "nullable"
        char currency "USD"
        date start_date
        date end_date "nullable"
        text impact_statement
        json suggested_amounts
        enum status
    }
    SUPPORTERS {
        bigint id PK
        string name
        string email UK
        json communication_preferences
        timestamp created_at
    }
    DONATIONS {
        bigint id PK
        bigint supporter_id FK
        bigint campaign_id FK "nullable, defaults to General Fund"
        bigint program_id FK "nullable"
        decimal amount
        char currency "USD"
        enum frequency "one_time|monthly|quarterly|annual"
        boolean anonymous
        enum status
        timestamp created_at
    }
    DONATION_TRANSACTIONS {
        bigint id PK
        bigint donation_id FK
        enum gateway "stripe"
        string gateway_reference UK
        enum status "pending|succeeded|failed|refunded"
        timestamp receipt_sent_at "nullable"
        timestamp created_at
    }
    NEWSLETTER_SUBSCRIBERS {
        bigint id PK
        string email UK
        timestamp consent_timestamp
        string consent_ip
        string frequency_preference
        enum status "subscribed|unsubscribed"
        timestamp unsubscribed_at "nullable"
    }
    CONTACT_SUBMISSIONS {
        bigint id PK
        string name
        string email
        string subject
        text message
        enum status "new|in_progress|responded|closed|spam"
        timestamp created_at
    }
    VOLUNTEER_APPLICATIONS {
        bigint id PK
        string name
        string email
        text details
        enum status
        timestamp created_at
    }
    PARTNERSHIP_INQUIRIES {
        bigint id PK
        string organization_name
        string contact_name
        string email
        text details
        enum status
        timestamp created_at
    }
    REDIRECTS {
        bigint id PK
        string from_path UK
        string to_path
        smallint status_code "301"
    }
```

**Indexing:** `supporters.email` unique (a returning donor should map to one record); `donations.status`, `donations.campaign_id`, `donations.supporter_id`; `donation_transactions.gateway_reference` unique (idempotency for Stripe webhook replay — a webhook retried by Stripe must not create a duplicate transaction record); `newsletter_subscribers.email` unique; `redirects.from_path` unique (this table is a lookup table hit on every request during the Phase 12 migration window, so it needs to be fast and collision-free).

**No soft deletes on this group.** These are transactional/log-like records (a donation, a submitted form, an audit-relevant redirect) — "deleting" a donation record should never actually remove financial history; that's a `status` change (e.g., `refunded`), not a row deletion. `Campaign` is the one editorial-ish table here and could reasonably get soft deletes if campaigns are ever archived mid-life; deferred until that's a real scenario (V1 ships one seeded campaign per `docs/product-requirements.md` §3).

---

## 4. Cross-Reference to Content Model

Every field here traces back to `docs/content-model.md` — this document only adds implementation-level decisions (keys, indexes, soft-delete policy, FK vs. enum) on top of that product-level definition. Where the two differ (§0 table above), this ERD wins for Phase 5 implementation purposes.
