# SABA INTERNATIONAL — COMPLETE WEBSITE REBUILD PROMPT

## Master Execution Document
**Project:** Saba International Website & Digital Impact Platform  
**Current Website:** https://sabainternational.org/  
**Target Stack:** Laravel 13 + PHP 8.3+ + Vue 3 + TypeScript + REST/JSON API + MySQL  
**Architecture:** API-first Laravel backend + Vue 3 SPA  
**Primary Objective:** Rebuild Saba International's digital presence into a modern, trust-building, conversion-optimized international nonprofit platform.

---

## 1. EXECUTIVE CONTEXT

### 1.1 Organization Profile
Saba International (est. 2009) supports underprivileged youth and families in East Africa through:
- **Education** (New Dawn Educational Centre, Bethel Kibera School)
- **Nutrition** (school feeding programs)
- **Shelter & Family Support** (The Nest Children's Home)
- **Youth Economic Empowerment** (The Hunter Initiative — software development training)

### 1.2 Current Digital State (Critical)
The existing website (sabainternational.org) is functionally a single-page brochure with:
- No donation infrastructure
- No subpages or navigation hierarchy
- Stale content (last news post: 3+ years ago)
- No trust signals (financial transparency, 501(c)(3) verification, governance docs)
- No contact forms, newsletter signup, or volunteer pathways
- Incomplete team profiles ("TBD" placeholders)
- No SEO metadata, sitemap, or structured data
- No accessibility compliance
- No media governance or consent tracking

### 1.3 Guiding Principle
> **Authentic to Saba's history, engineered for Saba's future.**

The existing website is the **source of truth** for organizational facts, history, people, programs, and terminology. However, the current design, UX, information architecture, conversion strategy, SEO, accessibility, performance, and technical architecture must be critically rebuilt — not reproduced.

### 1.4 Resource Constraint Acknowledgment
Saba International is a small board-run nonprofit. This prompt is designed for **phased execution** with realistic resourcing. Do not attempt to build all capabilities in V1. Ship the donor trust loop first. Iterate quarterly.

---

## 2. AUDIT REQUIREMENTS (Phase 0)

Before writing production code, produce:

### 2.1 Website Audit
```
docs/audit/current-website-audit.md
```
Document every accessible page, navigation element, CTA, form, link, image, and metadata. Every finding must include: Finding, Evidence, Severity, Impact, Recommendation, Proposed Solution.

### 2.2 Content Inventory
```
docs/audit/content-inventory.md
```
Catalog all existing text, images, videos, and downloadable resources. Flag: Keep / Rewrite / Update / Archive / Remove / Needs Verification.

### 2.3 Technical Audit
```
docs/audit/technical-audit.md
```
Identify current stack, performance metrics, broken links, duplicate content, security headers, and mobile behavior.

### 2.4 Stakeholder Verification
Conduct a 30-minute stakeholder interview with Tim/Cathy Woller to lock down:
- Exact legal names of all programs (New Dawn Educational Centre vs. Center; Bethel Kibera School vs. Bethel Outreach Children's Center)
- Program boundaries and relationships (is The Hunter Initiative an official Saba program or an independent partner?)
- Verified dates, statistics, and financial figures
- Board governance structure
- 501(c)(3) EIN and tax-exempt documentation location

**Rule:** Where information is missing, create an admin/content field marked `CONTENT REQUIRED`. Never fabricate beneficiaries, statistics, financial figures, quotes, testimonials, or impact metrics.

---

## 3. AUDIENCES & USER JOURNEYS

### 3.1 Primary Audiences

| Audience | Primary Need | Primary CTA |
|----------|-------------|-------------|
| **Donors** | Legitimacy, impact evidence, financial transparency | **Give / Make a Difference** |
| **Potential Partners** | Credibility, program details, governance, contact | **Partner With Us** |
| **Volunteers** | Opportunities, requirements, application process | **Get Involved** |
| **Existing Supporters** | Updates, stories, impact reports, events | **Stay Connected** |
| **Beneficiary Communities** | Accessible info, partner visibility, program details | *(Informational)* |
| **Researchers / Media** | Mission, history, leadership, financials, downloads | *(Informational)* |
| **Kenyan Diaspora** | Local giving options (MPesa future), community connection | **Support From Abroad** |

### 3.2 Critical User Journey: The Donor Trust Loop
```
Homepage → Transparency Center → Donate → Receipt → Impact Update → Recurring Giving
```
This loop must function flawlessly before any other feature ships.

### 3.3 Content Sustainability Mandate
The most common nonprofit website failure is: beautiful launch, then 2+ years of stale content. Build:
- Editorial calendar template (minimum quarterly publishing)
- CMS "content freshness" dashboard showing last update dates
- Auto-archive workflow for stories older than 3 years without verification

---

## 4. INFORMATION ARCHITECTURE

### 4.1 Recommended Sitemap

```
HOME
├── Hero: Mission + Primary CTA + Secondary CTA
├── Impact/Trust Indicators
├── Our Mission
├── Where We Work
├── Areas of Impact
├── Featured Programs
├── Stories of Change
├── Impact Numbers (qualitative if verified metrics unavailable)
├── How Your Support Helps
├── Latest Updates
├── Partner With Us
├── Newsletter Signup
└── Footer

ABOUT
├── Our Story
├── Our Mission
├── Our Approach
├── Our Leadership
├── Governance
├── Financial Transparency
└── Frequently Asked Questions

OUR WORK
├── Education
├── Nutrition
├── Shelter & Family Support
└── Youth Economic Empowerment

PROGRAMS & PARTNERS
├── New Dawn Educational Centre
├── Bethel Kibera School
├── The Nest Children's Home
└── The Hunter Initiative

IMPACT
├── Impact Overview
├── Stories of Change
├── Impact Metrics
├── Annual Reports
└── Where Your Support Goes

GET INVOLVED
├── Give (Donation Flow)
├── Partner With Us
├── Volunteer
├── Fundraise
├── Pray / Support
└── Subscribe (Newsletter)

STORIES & NEWS
├── Stories
├── News
├── Updates
├── Events
└── Media

RESOURCES
├── Annual Reports
├── Financial Documents
├── FAQs
├── Brand / Media Kit
└── Downloads

CONTACT
```

### 4.2 Navigation Principles
- Simplify. Do not overwhelm users with navigation.
- Mobile-first: hamburger menu on <768px, persistent CTA button visible at all times.
- Breadcrumbs on all subpages.
- Skip-to-content link for keyboard users.

---

## 5. HOMEPAGE REQUIREMENTS

### 5.1 Above the Fold
1. **Saba International identity** — logo + tagline
2. **Mission statement:** *"Creating pathways to a stronger future for children, youth and families in East Africa."*
3. **Emotionally compelling, dignified imagery** — no poverty porn. Active, contextualized portraits.
4. **Primary CTA:** [Make a Difference]
5. **Secondary CTA:** [Explore Our Work]

### 5.2 Homepage Sections (in order)
- **Hero** (full-width, max 2 images lazy-loaded)
- **Impact / Trust Indicators** (years active, programs supported, partner count — only verified numbers)
- **Our Mission** (3-pillar cards: Education, Nutrition, Shelter)
- **Where We Work** (Kenya focus, map placeholder for future interactive map)
- **Featured Programs** (4 program cards linking to dedicated pages)
- **Stories of Change** (3 latest stories with images, excerpts, read-more)
- **Impact Numbers** — use qualitative statements if verified metrics unavailable. Never invent statistics.
- **How Your Support Helps** (transparency teaser linking to full breakdown)
- **Latest Updates** (news + events)
- **Partner With Us** (corporate/institutional CTA)
- **Newsletter Signup** (minimal: email + consent checkbox)
- **Footer** (contact, social links, legal, accessibility statement)

---

## 6. IMPACT-FIRST DESIGN

### 6.1 Program Page Template
Every program page must follow this narrative structure:

```
Problem → Context → Saba's Role → Local Partner → Intervention → 
People Served → Outcomes → Evidence → Stories → How to Help
```

### 6.2 Program Page Sections
- **Header:** Program name, location, category, hero image, short description
- **Overview:** History, mission, local context, Saba's relationship
- **What Happens Here:** Activities, services, beneficiaries, partnerships
- **Impact:** Verified metrics only (see §6.3)
- **Stories:** Related stories of change
- **Gallery:** Photographs + videos with consent metadata visible
- **Reports:** Downloadable documents
- **How You Can Help:** Donate, partner, volunteer, share
- **CTA:** `Support This Work`

### 6.3 Metric Governance
Every impact metric must include:
- Metric name
- Value + unit
- Time period
- Data source
- Verification status
- Last updated date

**If real numbers are unavailable, use qualitative impact statements.** Example: *"Students at New Dawn receive daily nutritious meals, enabling them to focus on their studies."* — until verified metrics are supplied.

---

## 7. STORYTELLING ENGINE

### 7.1 Story Types
- Story of Change
- Program Update
- News
- Volunteer Story
- Donor Story
- Partner Story
- Founder Story
- Youth Story
- Community Story

### 7.2 Story Content Model
```
title
slug
excerpt
body (rich text, supports images/video embeds)
featured_image
author
published_at
updated_at
category
program
location
tags
seo_title
seo_description
og_image
status (draft / review / published / archived)
featured (boolean)
```

### 7.3 Ethical Storytelling & Content Governance
Because Saba works with vulnerable children, implement strict CMS governance:

**Required Fields for Every Story with People:**
- Consent status (yes / no / guardian / not required)
- Image consent (yes / no / anonymized)
- Guardian consent (where applicable)
- Anonymity option (name replacement with pseudonym)
- Sensitive-content classification (none / moderate / high)
- Publication approval workflow (draft → editor review → admin approval → published)
- Takedown capability (one-click unpublish + archive)
- Attribution (photographer, source, date)

**Never Publish:**
- Unnecessary medical information
- Exact vulnerable-person locations
- Private contact details
- Confidential case information
- Identifiable sensitive circumstances without documented consent

**Dignified Photography Policy:**
- Subjects must be portrayed as active participants, not passive victims.
- Contextualize settings — avoid sensationalized poverty imagery.
- Prefer eye-level photography over俯视 angles.
- Include photographer ethics statement in media library.

**Public Trust Feature:** Display a small badge on story images: *"Photographed with consent"* or *"Identity protected per guardian request."*

---

## 8. DONATION & FUNDRAISING PLATFORM

### 8.1 Donor Journey
```
Landing Page → Why Give → Choose Amount → Choose Frequency → 
Donor Information → Payment → Confirmation → Thank You → Impact Communication
```

### 8.2 Donation Features (V1)
- One-time donations
- Recurring donations (monthly / quarterly / annual)
- Suggested amounts: $25, $50, $100, $250, $500, Custom
- Multi-currency support: USD (primary), KES (for diaspora/local), GBP/EUR (future)
- Geo-detected currency selector with localized suggested amounts
- Donor name + email
- Optional anonymous donation
- Donation designation (General / New Dawn / Bethel / The Nest / Hunter Initiative)
- Transaction reference + payment status
- Digital receipt (emailed PDF)
- Donor communication preferences (opt-in/opt-out)

### 8.3 Payment Architecture
**Payment Gateway Interface (abstraction layer):**
```
PaymentGatewayInterface
├── StripeGateway (primary V1)
├── PayPalGateway (V2)
├── MPesaGateway (future — diaspora/local Kenya)
└── FutureGateway
```

**Security Requirements:**
- Never store raw card information.
- Use Stripe Elements / hosted tokenized flows only.
- PCI compliance via Stripe's SAQ-A pathway.
- HTTPS only. HSTS in production.
- Rate limiting: max 5 donation attempts per IP per hour.

### 8.4 Donor Abandonment Recovery
If a donor enters an email on the donation form but does not complete payment within 30 minutes, queue a gentle follow-up email within 24 hours. Include a direct link back to their pre-filled donation form.

### 8.5 Campaigns
Administrators can create fundraising campaigns:
```
name, slug, description, goal_amount, currency, start_date, end_date,
featured_image, impact_statement, suggested_amounts, status
```

**Campaign Rule:** Never imply that a fixed donation amount produces a guaranteed outcome unless the organization has verified that relationship.

---

## 9. TRANSPARENCY CENTER

A dedicated section building immediate donor trust:

### 9.1 Required Content
- Organizational registration + 501(c)(3) EIN
- Tax-exempt verification letter
- Annual reports (2024, 2025, 2026+)
- Financial reports / audited statements
- Board governance structure + member bios
- Leadership team
- Key policies (Privacy, Safeguarding, Donation, Conflict of Interest, Code of Conduct, Child Protection)
- Data protection information (GDPR/CCPA compliant)

### 9.2 Annual Report Archive
Structured archive with:
```
year, title, summary, file (PDF), cover_image, published_at
```
Features: preview, download, share, SEO indexing.

### 9.3 "Where Your Money Goes" Visualization
Simple breakdown (e.g., 80% programs, 15% administration, 5% fundraising) — only if verified by Saba's financial team. If unavailable, mark `CONTENT REQUIRED`.

---

## 10. ADMINISTRATION / CMS

### 10.1 Admin Modules
```
Dashboard (content freshness alerts, pending approvals, recent donations)

Content
├── Pages
├── Stories
├── News
├── Events
├── Programs
├── Partners
├── Team
├── Media
└── Documents

Impact
├── Metrics
├── Reports
└── Stories of Change

Fundraising
├── Campaigns
├── Donations
├── Transactions
└── Supporters

Engagement
├── Newsletter Subscribers
├── Contact Messages
├── Volunteer Applications
└── Partnership Inquiries

System
├── Users
├── Roles
├── Permissions
├── Audit Logs
├── Settings
└── Integrations
```

### 10.2 RBAC (Role-Based Access Control)
Minimum roles:
- **Super Administrator** — full access
- **Administrator** — system + content + users
- **Editor** — content publishing
- **Content Manager** — content only, no financial data
- **Finance Manager** — donations + transactions + supporters, no content editing
- **Communications Manager** — stories + newsletter + contact, no financial data
- **Viewer** — read-only access

**Principle of Least Privilege:** Finance Manager cannot modify website content. Content Manager cannot access donor financial information.

### 10.3 Audit Log
Every sensitive administrative action logged:
```
user, action, entity, entity_id, old_values, new_values,
ip_address, user_agent, timestamp
```
Logged actions: publish story, delete media, change donation status, modify user permissions, export donor data, change campaign settings.

### 10.4 Administrator MFA
Administrative users MUST use MFA (TOTP via Google Authenticator/Authy). Do not rely solely on passwords for privileged accounts.

---

## 11. API ARCHITECTURE

### 11.1 Public API
```
GET    /api/v1/pages
GET    /api/v1/pages/{slug}
GET    /api/v1/programs
GET    /api/v1/programs/{slug}
GET    /api/v1/stories
GET    /api/v1/stories/{slug}
GET    /api/v1/team
GET    /api/v1/campaigns
GET    /api/v1/campaigns/{slug}
POST   /api/v1/contact
POST   /api/v1/newsletter/subscribe
POST   /api/v1/donations
POST   /api/v1/payments/webhook
```

### 11.2 Admin API
```
/api/v1/admin/...
```
All admin endpoints require authorization. Never expose administrative endpoints publicly.

### 11.3 API Standards
Every endpoint must have:
- Input validation (Laravel Form Request classes)
- Authorization (Laravel Policies)
- Consistent response structure:
  ```json
  { "data": {}, "meta": {}, "links": {} }
  ```
- Error handling (standardized error codes + messages)
- Pagination for list endpoints
- Rate limiting (public: 60 req/min, authenticated: 120 req/min)
- Request logging
- Automated tests (feature tests for every endpoint)

---

## 12. DATABASE DESIGN

### 12.1 Core Entities
```
users
roles
permissions
role_permission
user_role

pages
page_revisions

programs
program_categories
program_locations
program_media

partners
partner_programs

stories
story_categories
story_tags
story_tag (pivot)
story_media

media
media_folders
media_consents

team_members

events

impact_metrics
impact_metric_values

annual_reports

campaigns
campaign_updates

supporters
donations
donation_transactions

newsletter_subscribers

contact_submissions
volunteer_applications
partnership_inquiries

documents

seo_metadata

redirects

audit_logs

settings
```

### 12.2 Database Rules
- Every table must have documented: purpose, ownership, relationships, lifecycle, indexing strategy, privacy classification, retention consideration.
- Do not create tables "just in case."
- Use foreign key constraints.
- Index all slug columns, foreign keys, and frequently queried status fields.
- Soft deletes on all content tables.

### 12.3 ERD Requirement
Create `docs/architecture/database-erd.md` and generate a visual ERD before implementation begins.

---

## 13. FRONTEND SPECIFICATION

### 13.1 Tech Stack
- Vue 3 (Composition API)
- TypeScript (strict mode)
- Vite
- Vue Router 4
- Pinia (only where global state is genuinely needed)
- Axios for API communication

### 13.2 Project Structure
```
resources/
frontend/
├── components/        # Reusable UI primitives
│   ├── ui/           # Buttons, inputs, cards, badges
│   ├── layout/       # Header, footer, sidebar
│   └── content/      # Story cards, program cards, metric displays
├── layouts/          # Default, admin, minimal
├── pages/            # Route-level components
├── composables/      # Reusable logic (useAuth, useApi, useConsent)
├── stores/           # Pinia stores (auth, notifications, cart/donation)
├── services/         # API client modules
├── types/            # TypeScript interfaces
├── router/           # Route definitions + guards
├── utils/            # Helpers, formatters, validators
├── assets/           # Static assets
└── styles/           # Global styles, CSS variables
```

### 13.3 Design System
Define and document:
```
Brand Colors      # Primary, secondary, accent, success, warning, error, neutral scale
Typography        # Headings (serif or humanist sans for trust), body (clean sans), scale
Spacing           # 4px base grid (4, 8, 12, 16, 24, 32, 48, 64, 96)
Grid              # 12-column, max-width 1280px, gutters 24px
Border Radius     # 4px (buttons), 8px (cards), 16px (modals)
Shadows           # 3 levels (subtle, elevated, modal)
Buttons           # Primary, secondary, ghost, danger; sizes sm/md/lg
Forms             # Input, textarea, select, checkbox, radio, label, error state
Cards             # Program card, story card, team card, metric card
Navigation        # Desktop nav, mobile hamburger, footer nav
Alerts            # Info, success, warning, error, dismissible
Badges            # Status, category, tag
Tables            # Sortable, paginated, responsive
Modal             # Focus trap, ESC close, ARIA labeled
Drawer            # Mobile filters, mobile nav
Pagination        # Numbered + prev/next
Breadcrumbs       # Home > Section > Page
Empty States      # Illustration + message + CTA
Loading States    # Skeleton screens preferred over spinners
Error States      # 404, 500, offline, API failure
```

**Visual Language Must Communicate:** Trust, compassion, dignity, professionalism, African context, international credibility, hope, transparency.

**Avoid:** Generic "charity website" aesthetics, excessive emotional manipulation, poverty-porn imagery.

### 13.4 Mobile-First Breakpoints
```
320px+   (small mobile)
375px    (mobile)
390px    (large mobile)
430px    (extra large mobile)
768px    (tablet)
1024px   (small desktop)
1280px   (desktop)
1440px+  (large desktop)
```

Test navigation, donation flow, forms, galleries, stories, tables, and admin dashboard on all breakpoints.

---

## 14. ACCESSIBILITY (WCAG 2.2 AA)

### 14.1 Requirements
- Keyboard navigation (Tab, Shift+Tab, Enter, Escape, Arrow keys)
- Visible focus indicators (min 2px outline, 3:1 contrast against background)
- Semantic HTML (proper heading hierarchy h1→h6, landmarks, lists)
- Alt text for all images (required CMS field, cannot publish without it)
- Accessible forms (associated labels, error messages linked via aria-describedby)
- Sufficient color contrast (4.5:1 normal text, 3:1 large text/UI components)
- Screen-reader compatibility (ARIA labels where native semantics insufficient)
- Skip navigation link
- Accessible modals (focus trap, return focus, aria-modal)
- Accessible menus (ARIA expanded, current page indicator)
- Accessible carousels (pause control, keyboard navigation, live region announcements)
- Touch target sizing (min 44×44px)
- Reduced-motion support (`prefers-reduced-motion` media query)

### 14.2 Testing Requirements
- Automated: axe-core, Lighthouse accessibility audit
- Manual: keyboard-only navigation test, NVDA/VoiceOver screen reader test
- Color contrast verification on all text/background combinations

---

## 15. SEO + AI SEARCH OPTIMIZATION

### 15.1 Technical SEO (Every Indexable Page)
```
title (50-60 chars)
meta_description (150-160 chars)
canonical_url
robots (index/follow, noindex where appropriate)
og_title
og_description
og_image (1200×630px)
twitter_card (summary_large_image)
structured_data (JSON-LD)
```

### 15.2 Global SEO
- `/sitemap.xml` (auto-generated, cached daily)
- `/robots.txt`
- Clean URLs (`/programs/new-dawn` not `/programs?id=1`)
- Canonical URLs (self-referencing + cross-domain if needed)
- 301 redirects from old URLs (maintain search visibility)
- Internal linking strategy (breadcrumb, related content, contextual links)
- Image optimization (WebP/AVIF, responsive srcset, lazy loading)
- Semantic headings (one H1 per page, logical hierarchy)

### 15.3 Structured Data (Schema.org)
Implement where content supports:
```
Organization / NGO
Article / NewsArticle
Person
Event
BreadcrumbList
WebSite
WebPage
DonateAction
FAQPage
```

### 15.4 AI Search / Answer Engine Optimization (AEO)
Create authoritative pages answering:
- What is Saba International?
- What does Saba International do?
- Where does Saba International work?
- Who does Saba International support?
- How can I support Saba International?
- What programs does Saba International support?
- How can I partner with Saba International?
- How does Saba International use donations?

**Format:** Use `<details>`/`<summary>` expandable FAQ blocks with concise 40-60 word summaries. Mark up with `FAQPage` schema.

### 15.5 External Registrations
- **Google for Nonprofits** (Ad Grants eligibility — $10K/month free search ads)
- **Wikidata** (feeds Google's Knowledge Graph)
- **Guidestar/Candid** (Platinum Seal of Transparency)
- **Google Search Console** + **Bing Webmaster Tools**

---

## 16. CONTENT STRATEGY

### 16.1 Editorial Categories
Education, Nutrition, Shelter, Youth Empowerment, Community, Impact, Partnerships, Volunteer, Fundraising, News, Stories.

### 16.2 Story Quality Checklist
Every published story must answer at least one:
- What happened?
- Why does it matter?
- Who was affected?
- What changed?
- What happens next?
- How can someone help?

### 16.3 Content Freshness
- CMS dashboard shows "last updated" dates for all content.
- Stories older than 3 years auto-flag for review.
- Quarterly content audit reminder sent to editors.

---

## 17. SEARCH

Build website search indexing:
- Stories, programs, reports, pages, news, events
- Features: relevance ranking, category filters, pagination, empty states
- V1: Database full-text search (MySQL MATCH/AGAINST or Laravel Scout with database driver)
- V2: Meilisearch (self-hostable, typo-tolerant, fast)
- Do NOT add AI/semantic search in V1 unless justified by content volume.

---

## 18. ANALYTICS & CONVERSION TRACKING

### 18.1 Privacy-Conscious Analytics
Use Plausible Analytics or Fathom (privacy-first, GDPR-compliant, no cookie banner required) OR Google Analytics 4 with IP anonymization and minimal data retention.

### 18.2 Tracked Events
```
page_view
story_view
program_view
donation_started
donation_completed
newsletter_signup
contact_submission
volunteer_application
partnership_inquiry
report_download
campaign_view
campaign_share
```

### 18.3 Primary KPIs
- Donation conversion rate
- Donation completion rate
- Recurring donor rate
- Newsletter conversion rate
- Partner inquiry rate
- Volunteer conversion rate
- Story engagement (time on page, scroll depth)
- Program engagement
- Organic search traffic
- Returning visitor rate

### 18.4 Conversion Funnels
Define and monitor:
- Homepage → Program Page → Donation Start → Donation Complete
- Homepage → Newsletter Signup → Confirmed Subscription
- Homepage → Contact Form → Submission

---

## 19. PERFORMANCE

### 19.1 Core Web Vitals Targets
- **LCP** (Largest Contentful Paint): < 2.5s
- **INP** (Interaction to Next Paint): < 200ms
- **CLS** (Cumulative Layout Shift): < 0.1
- **TTFB** (Time to First Byte): < 600ms

### 19.2 Optimization Requirements
- Images: WebP/AVIF, responsive srcset, lazy loading, focal point cropping
- Fonts: Subset, preload critical fonts, font-display: swap
- JavaScript: Code-split routes, tree-shake, defer non-critical scripts
- CSS: Purge unused styles, critical CSS inline
- Caching: Laravel route/model caching, Redis for sessions/cache, CDN for static assets
- Database: Eager loading, query optimization, database indexing
- API: Paginated responses, compressed payloads, rate limiting

---

## 20. IMAGE & MEDIA MANAGEMENT

### 20.1 Media Library Features
- Upload (drag-drop, multi-file)
- Crop + resize (focal point selection)
- Alt text (required field)
- Caption
- Photographer attribution
- Copyright / license
- Consent status (linked to storytelling governance)
- Program association
- Story association
- Metadata extraction (EXIF)

### 20.2 Image Variants
Auto-generate responsive variants on upload:
- thumbnail (150×150, crop)
- small (400×300, fit)
- medium (800×600, fit)
- large (1200×800, fit)
- hero (1920×1080, fit)
- social (1200×630, crop)

Use a CDN image transformation service (Cloudflare Images or Imgix) if budget allows.

---

## 21. EMAIL SYSTEM

### 21.1 Transactional Emails (Queued Jobs)
- Contact form confirmation
- Donation receipt (PDF attachment)
- Donation failure notification
- Newsletter subscription confirmation
- Volunteer application confirmation
- Partnership inquiry confirmation
- Admin notifications (new donation, new contact, new volunteer)
- Donation abandonment recovery

### 21.2 Email Requirements
- Use Laravel queued jobs (never synchronous)
- HTML + plain text versions
- Branded template matching design system
- Unsubscribe links where required
- SPF/DKIM/DMARC configured on sending domain

---

## 22. NEWSLETTER SYSTEM

```
Subscribe → Validate email → Consent checkbox → 
Store subscriber → Send confirmation email
```

- Double opt-in if required by jurisdiction
- Unsubscribe link in every email (one-click)
- Record consent timestamp + IP
- Manage preferences (frequency, content types)
- GDPR-compliant data export/deletion

---

## 23. CONTACT SYSTEM

### 23.1 Contact Form Fields
```
name (required)
email (required, validated)
country
organization
subject (dropdown: General, Donation, Partnership, Volunteer, Media)
message (required, min 20 chars)
consent (required checkbox: "I agree to Saba International contacting me.")
```

### 23.2 Contact Security
- Server-side validation (never trust frontend)
- Spam protection: Honeypot field + Akismet or reCAPTCHA v3
- Rate limiting: 3 submissions per IP per hour
- Email notification to admin
- Database record with status tracking

### 23.3 Contact Statuses
```
New → In Progress → Responded → Closed
         ↓
       Spam
```

---

## 24. SECURITY

### 24.1 OWASP ASVS Baseline
Implement all Level 1 requirements from OWASP ASVS 4.0.

### 24.2 Required Controls
- HTTPS everywhere (TLS 1.2+)
- Secure cookies (Secure, HttpOnly, SameSite=Lax/Strict)
- CSRF protection on all state-changing routes
- XSS prevention (output escaping, Content-Security-Policy)
- SQL injection prevention (Eloquent/Query Builder, no raw SQL without parameterization)
- Authorization policies on all controllers
- Rate limiting (public API, contact forms, login attempts)
- Input validation (Form Request classes)
- Output escaping (Blade `{{ }}` auto-escape)
- Secure headers (X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP)
- HSTS in production
- Secure file uploads: MIME type validation, extension whitelist, size limits, virus scanning if possible
- Password hashing (bcrypt, min 12 rounds)
- MFA for all admin accounts (TOTP)
- Audit logs for sensitive actions
- Secrets management (never commit .env, use Laravel secrets or environment variables)
- Database backups (daily automated, encrypted, off-site)
- Security monitoring (failed login alerts, anomaly detection)

### 24.3 File Upload Security
- Never allow executable files (no .php, .exe, .js, .html uploads)
- Validate MIME type against extension
- Store uploads outside web root or with .htaccess/nginx deny
- Scan uploads with ClamAV if possible
- Serve private files via Laravel controller (not direct URL)

---

## 25. BACKUPS & DISASTER RECOVERY

### 25.1 Backup Strategy
- Database: Daily automated dump, retained for 30 days
- Media: Daily sync to off-site storage (AWS S3 / Backblaze B2)
- Configuration: Encrypted .env backup stored separately
- Off-site: Separate geographic region from primary server

### 25.2 Restore Procedure
- Document step-by-step restore process
- **Test restore quarterly.** A backup never tested is not verified.
- Maintain recovery time objective (RTO): < 4 hours
- Maintain recovery point objective (RPO): < 24 hours

---

## 26. TESTING STRATEGY

### 26.1 Unit Tests
Test: models, services, business logic, calculations, authorization logic.

### 26.2 Feature / API Tests
Test: authentication, permissions, CRUD, forms, donations, webhooks, newsletter subscriptions.

### 26.3 Browser / E2E Tests
Test: homepage, navigation, program browsing, story browsing, search, newsletter signup, contact form, donation flow, admin login, admin CRUD, media upload, publishing workflow.

### 26.4 Accessibility Tests
Automated (axe-core) + manual (keyboard, screen reader, contrast).

### 26.5 Performance Tests
Measure: TTFB, LCP, CLS, INP, API response time.

### 26.6 Security Tests
Test: authentication bypass, authorization escalation, SQL injection, XSS, CSRF, file upload abuse, rate limiting effectiveness.

---

## 27. DEFINITION OF DONE

A feature is complete only when:
```
Implemented
→ Code reviewed
→ Unit tests passing
→ Feature tests passing
→ API tests passing
→ Edge cases tested
→ Security tested
→ Accessibility tested (WCAG 2.2 AA)
→ Responsive tested (320px → 1440px+)
→ Performance tested (Core Web Vitals)
→ Documented
→ Checklist marked complete
```

---

## 28. GIT WORKFLOW

```
main      (production — protected, requires PR review)
develop   (integration branch)
feature/* (new features)
fix/*     (bug fixes)
chore/*   (maintenance, docs)
```

- Every meaningful feature in its own branch
- Clear commit messages: `feat:`, `fix:`, `test:`, `docs:`, `chore:`
- Never commit secrets, .env files, or API keys
- Pull requests require: passing CI, code review, linked issue

---

## 29. CI/CD PIPELINE

```
Push to feature branch
→ Install dependencies (Composer, NPM)
→ Lint (PHP CS Fixer, ESLint, Prettier)
→ Static analysis (PHPStan level 8)
→ Unit tests
→ Feature tests
→ Build frontend (Vite production build)
→ Security checks (composer audit, npm audit)
→ Merge to develop
→ Deploy to staging
→ E2E tests on staging
→ Merge to main
→ Deploy to production
→ Production smoke tests
```

Production deployment requires successful CI on `main`.

---

## 30. ENVIRONMENT MANAGEMENT

```
local      (developer machines)
testing    (CI environment)
staging    (pre-production, client-accessible)
production (live)
```

- Never use production credentials locally
- Staging must mirror production (same PHP version, same database version, same extensions)
- Environment-specific `.env` files managed securely (Laravel Forge env manager, 1Password, or similar)

---

## 31. DOCUMENTATION REQUIREMENTS

The project MUST contain:

```
README.md

/docs/
├── project-overview.md
├── product-requirements.md
├── current-website-audit.md
├── content-inventory.md
├── information-architecture.md
├── design-system.md
├── database-erd.md
├── api-documentation.md
├── authentication.md
├── authorization.md
├── security.md
├── accessibility.md
├── seo.md
├── analytics.md
├── testing.md
├── deployment.md
├── backup-and-recovery.md
├── content-management.md
├── editorial-workflow.md
├── donor-management.md
├── payment-integrations.md
├── troubleshooting.md
└── operations-manual.md
```

---

## 32. MASTER IMPLEMENTATION CHECKLIST

### Phase 0 — Discovery
- [ ] Audit current website (pages, nav, content, media, links, SEO, performance)
- [ ] Audit existing content (keep/rewrite/update/archive/remove)
- [ ] Stakeholder interview (program names, dates, governance, financials)
- [ ] Competitor/benchmark research (5 international nonprofits, 3 Africa-focused, 3 education, 3 child/family, 3 strong fundraising sites)
- [ ] Define user personas
- [ ] Define user journeys (donor, partner, volunteer, supporter, media)

### Phase 1 — Product Strategy
- [ ] Product vision statement
- [ ] Goals + KPIs
- [ ] Information architecture (sitemap)
- [ ] Content model
- [ ] Feature matrix + prioritization (V1 Must-Have vs. V2 Nice-to-Have vs. Future)

### Phase 2 — UX Design
- [ ] Wireframes (homepage, program page, story page, donation flow, contact)
- [ ] User journeys (donation, program discovery, story reading, newsletter signup)
- [ ] Mobile navigation flow
- [ ] Admin dashboard wireframes

### Phase 3 — UI Design
- [ ] Design system (colors, typography, spacing, components)
- [ ] High-fidelity homepage design
- [ ] High-fidelity program page design
- [ ] High-fidelity donation flow design
- [ ] High-fidelity story page design
- [ ] Admin dashboard design
- [ ] Responsive states (320px, 375px, 768px, 1024px, 1440px)
- [ ] Accessibility states (focus, error, loading, empty)
- [ ] Stakeholder approval on all core pages

### Phase 4 — Architecture
- [ ] System architecture diagram
- [ ] Database ERD (visual + markdown)
- [ ] API architecture (endpoints, auth, rate limits)
- [ ] Authentication architecture (Sanctum + MFA)
- [ ] Authorization model (RBAC matrix)
- [ ] Payment architecture (gateway abstraction)
- [ ] Media architecture (upload, variants, CDN)
- [ ] Deployment architecture (server, CI/CD, environments)

### Phase 5 — Laravel Backend
- [ ] Initialize Laravel 13 project
- [ ] Configure PHP 8.3+, MySQL, Vite
- [ ] Configure Sanctum authentication
- [ ] Configure roles + permissions (Spatie Permission)
- [ ] Create migrations (all core entities)
- [ ] Create models + relationships
- [ ] Create factories + seeders
- [ ] Create policies (authorization)
- [ ] Create API resources
- [ ] Create controllers (public + admin)
- [ ] Create services (business logic)
- [ ] Create jobs (queued emails, exports)
- [ ] Create notifications
- [ ] Create mailables
- [ ] Implement public pages API
- [ ] Implement program system API
- [ ] Implement stories API
- [ ] Implement news API
- [ ] Implement team API
- [ ] Implement events API
- [ ] Implement reports API
- [ ] Implement media library API
- [ ] Implement impact metrics API
- [ ] Implement CMS dashboard API
- [ ] Implement RBAC API
- [ ] Implement audit logs
- [ ] Implement publishing workflow (draft → review → published)

### Phase 6 — Vue Frontend
- [ ] Initialize Vue 3 + TypeScript + Vite
- [ ] Configure Vue Router + Pinia
- [ ] Build layout components (header, footer, nav)
- [ ] Build UI primitives (buttons, inputs, cards, badges, modals)
- [ ] Implement homepage
- [ ] Implement program pages
- [ ] Implement story pages
- [ ] Implement news listing + detail
- [ ] Implement impact section
- [ ] Implement reports archive
- [ ] Implement "Get Involved" pages
- [ ] Implement donation flow (frontend)
- [ ] Implement contact form
- [ ] Implement newsletter signup
- [ ] Implement search
- [ ] Implement 404 + error pages
- [ ] Implement accessibility features (skip link, focus management, ARIA)

### Phase 7 — CMS
- [ ] Admin login + MFA
- [ ] Admin dashboard (stats, alerts, content freshness)
- [ ] Content management (pages, stories, news, events, programs, team, media, documents)
- [ ] Impact management (metrics, reports)
- [ ] Fundraising management (campaigns, donations, transactions, supporters)
- [ ] Engagement management (subscribers, contacts, volunteers, partnerships)
- [ ] User management (CRUD + roles)
- [ ] Audit log viewer
- [ ] Settings + integrations

### Phase 8 — Integrations
- [ ] Email provider (Mailgun / Postmark / Amazon SES)
- [ ] Payment provider (Stripe — hosted elements)
- [ ] Webhook handling (Stripe webhooks for payment status)
- [ ] Analytics (Plausible or GA4)
- [ ] Error tracking (Sentry or Flare)
- [ ] Storage (S3-compatible for media backups)
- [ ] Monitoring (Uptime monitoring, server alerts)

### Phase 9 — Security
- [ ] OWASP ASVS Level 1 review
- [ ] Dependency audit (`composer audit`, `npm audit`)
- [ ] Authentication testing (brute force, session fixation)
- [ ] Authorization testing (role escalation, horizontal access)
- [ ] API security review
- [ ] File upload testing (malicious files, size abuse)
- [ ] Secrets review (no committed keys)
- [ ] Infrastructure review (firewall, SSH keys, server hardening)

### Phase 10 — Testing
- [ ] Unit tests (models, services, calculations)
- [ ] Feature tests (auth, CRUD, forms)
- [ ] API tests (all endpoints, validation, auth)
- [ ] Browser tests (critical journeys)
- [ ] E2E tests (donation flow, contact, newsletter, admin)
- [ ] Accessibility tests (automated + manual)
- [ ] Performance tests (Core Web Vitals)
- [ ] Security tests (OWASP ZAP or Burp Suite scan)

### Phase 11 — Content Migration
- [ ] Migrate verified content from old site
- [ ] Rewrite outdated content
- [ ] Archive obsolete content
- [ ] Verify all migrated images (alt text, consent, attribution)
- [ ] Verify all links
- [ ] Content owner training on CMS

### Phase 12 — SEO Migration
- [ ] Old URL → new URL mapping
- [ ] 301 redirects implemented
- [ ] Canonical URLs
- [ ] Sitemap submitted to Search Console
- [ ] Robots.txt
- [ ] Structured data validation (Google Rich Results Test)
- [ ] Open Graph validation

### Phase 13 — Staging
- [ ] Deploy to staging environment
- [ ] Full UAT with stakeholders
- [ ] Critical bugs = 0
- [ ] High-severity bugs = 0
- [ ] Security blockers = 0
- [ ] Accessibility blockers = 0
- [ ] Payment blockers = 0
- [ ] Broken critical journeys = 0

### Phase 14 — Production
- [ ] Deploy to production
- [ ] SSL verification (A+ on SSL Labs)
- [ ] DNS verification
- [ ] Homepage smoke test
- [ ] Navigation smoke test
- [ ] Program pages smoke test
- [ ] Stories smoke test
- [ ] Search smoke test
- [ ] Contact form smoke test
- [ ] Newsletter signup smoke test
- [ ] Donation flow smoke test (use Stripe test mode → verify receipt email)
- [ ] Email delivery verification
- [ ] Analytics events firing
- [ ] Admin login + MFA working
- [ ] HTTPS enforced
- [ ] Sitemap accessible
- [ ] Robots.txt accessible
- [ ] Backup verified
- [ ] Restore procedure tested
- [ ] Monitoring active
- [ ] Error reporting active
- [ ] Stakeholder approval obtained
- [ ] Handover complete

---

## 33. FINAL ACCEPTANCE GATE

Do NOT declare the project finished until ALL of the following are true:

### Product
- [ ] All V1 MVP requirements implemented
- [ ] All critical user journeys work end-to-end
- [ ] Donation journey works (test transaction + receipt)
- [ ] Contact form works + notifies admin
- [ ] Newsletter signup works + sends confirmation
- [ ] CMS allows non-technical content updates
- [ ] Board/stakeholder approval obtained
- [ ] First real donation successfully processed

### Engineering
- [ ] Laravel 13 + PHP 8.3+
- [ ] Vue 3 + TypeScript
- [ ] API functioning with consistent response format
- [ ] Database migrated + seeded
- [ ] Authentication functioning (Sanctum)
- [ ] Authorization functioning (RBAC)
- [ ] Admin MFA enforced

### Quality
- [ ] Unit tests passing (>80% coverage)
- [ ] Feature tests passing
- [ ] API tests passing
- [ ] E2E tests passing
- [ ] No critical bugs
- [ ] No high-severity unresolved bugs

### Accessibility
- [ ] WCAG 2.2 AA review completed
- [ ] Keyboard navigation verified
- [ ] Screen-reader testing completed
- [ ] Color contrast verified
- [ ] Forms accessible (labels, errors, focus)

### Security
- [ ] OWASP ASVS Level 1 review completed
- [ ] Dependency audit clean
- [ ] Authentication tested (brute force protection)
- [ ] Authorization tested (privilege escalation)
- [ ] File upload tested (malicious file rejection)
- [ ] Rate limiting tested
- [ ] CSRF protection verified
- [ ] XSS prevention verified
- [ ] SQL injection prevention verified
- [ ] HTTPS + HSTS verified
- [ ] Security headers verified
- [ ] Admin MFA verified

### SEO
- [ ] Metadata implemented on all indexable pages
- [ ] Sitemap implemented + submitted
- [ ] Robots.txt implemented
- [ ] Canonical URLs implemented
- [ ] Structured data implemented + validated
- [ ] 301 redirects from old URLs implemented
- [ ] Search indexing requested

### Performance
- [ ] Core Web Vitals tested (LCP < 2.5s, INP < 200ms, CLS < 0.1)
- [ ] Mobile performance tested
- [ ] Images optimized (WebP/AVIF, responsive)
- [ ] API performance tested (< 200ms for cached reads)
- [ ] Caching configured (Redis, route cache, config cache)

### Operations
- [ ] Production deployment documented
- [ ] Backup automated + tested
- [ ] Restore tested
- [ ] Monitoring configured (uptime + alerts)
- [ ] Error reporting configured (Sentry/Flare)
- [ ] Admin manual completed
- [ ] Technical documentation completed
- [ ] Content owner trained

**ONLY AFTER ALL CHECKBOXES ARE COMPLETE MAY THE PROJECT BE DECLARED:**

# ✅ PRODUCTION READY

---

## 34. POST-LAUNCH OPERATIONS

### 34.1 Quarterly Optimization Cycle
```
Measure → Analyze → Identify friction → Prioritize → Experiment → Implement → Measure again
```

### 34.2 Quarterly Review Checklist
- [ ] Donation conversion rate trend
- [ ] Traffic sources + organic search performance
- [ ] Top-performing content + bottom-performing content
- [ ] Newsletter growth + engagement
- [ ] Partner inquiry volume
- [ ] Volunteer application volume
- [ ] Accessibility regression check
- [ ] Performance regression check
- [ ] Security dependency audit
- [ ] Content freshness audit

### 34.3 V2 Roadmap (Post-Launch)
- [ ] Donor Portal (view donations, download receipts, manage recurring gifts)
- [ ] Public Impact Dashboard (visualization of verified metrics)
- [ ] Interactive Program Map (privacy-appropriate location display)
- [ ] Partner Portal (submit updates, upload reports, share media)
- [ ] Volunteer Portal (opportunities, onboarding, communication)
- [ ] MPesa integration (Kenyan diaspora/local donors)
- [ ] AI Assistant (trained only on approved organizational content)
- [ ] Semantic Search (Meilisearch or Laravel Scout upgrade)
- [ ] WhatsApp newsletter option (Kenyan diaspora engagement)

---

## 35. IMPORTANT RULES

1. **Never fabricate content.** Use `CONTENT REQUIRED` for missing information.
2. **Never store raw card data.** Use tokenized payment flows only.
3. **Never trust frontend validation.** Server-side validation on everything.
4. **Never commit secrets.** .env, API keys, payment secrets stay out of git.
5. **Never skip accessibility testing.** WCAG 2.2 AA is mandatory, not optional.
6. **Never deploy without tested backups.** Test restore quarterly.
7. **Never expose admin APIs publicly.** Authorization required on every admin endpoint.
8. **Never publish without consent verification.** The CMS must make responsible publishing easier than irresponsible publishing.
9. **Never invent statistics.** Use qualitative statements until verified metrics are supplied.
10. **Never declare completion without stakeholder approval and production smoke tests.**

---

*End of Complete Markdown Prompt*
