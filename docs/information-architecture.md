# Information Architecture — Saba International

**Phase:** 1 — Product Strategy
**Status:** Draft, pending stakeholder review
**Inputs:** saba.md §4, `docs/audit/current-website-audit.md`, `docs/audit/content-inventory.md`

---

## 1. Why This Differs From saba.md §4.1

saba.md's proposed sitemap is largely sound and directly fixes the current site's core defect: everything today is an anchor on one page, not a real navigable site (audit: Route Inventory table — every "page" except `/posts` and `/difference/create` is a homepage anchor). This document adopts that sitemap's shape, with three changes driven by audit findings:

1. **Every section is tagged V1 / V2 / Future**, per the resource-constraint principle in `docs/project-overview.md` §3.3 — saba.md's sitemap describes the eventual full site, not what ships first.
2. **The program taxonomy is reconciled**, not inherited uncritically from either source (audit F-12: the live site tags New Dawn/Bethel/Nest/Hunter inconsistently across Education/Nutrition/Shelter, and doesn't match saba.md's four-pillar grouping either).
3. **Ambiguous nodes are marked `CONTENT REQUIRED` or `PENDING VERIFICATION`** inline, per saba.md §2.4's rule — nothing here is fabricated as if resolved.

---

## 2. Program Taxonomy Decision

**Working decision for Phase 1 planning:** adopt saba.md §1.1's four-pillar structure as the V1 taxonomy:

- **Education** — New Dawn, Bethel Kibera School
- **Nutrition** — school feeding programs (cross-cutting, not partner-exclusive — both New Dawn and Bethel run feeding components per the audit's content inventory)
- **Shelter & Family Support** — The Nest
- **Youth Economic Empowerment** — The Hunter Initiative

**Rationale:** the live site's tagging is internally inconsistent (Bethel tagged Nutrition-only despite being a school; Hunter tagged Education despite being explicitly about economic empowerment) and looks like organic drift rather than a deliberate taxonomy. Four pillars also give Hunter Initiative a category that isn't "Education," which matters if question 2 below resolves it as economically-focused rather than academically-focused.

**This is a proposal, not a final answer** — flag for stakeholder sign-off alongside the mission-statement question in `docs/project-overview.md` §6.

---

## 3. Sitemap (V1 scope marked)

```
HOME                                                          [V1]
├── Hero: Mission + Primary CTA + Secondary CTA                [V1]
├── Trust Indicators (years active, programs, partner count —  [V1 — CONTENT REQUIRED for
│   verified numbers only, per saba.md §6.3)                    any number not already
│                                                                verified in the audit]
├── Our Mission (3–4 pillar cards)                              [V1]
├── Where We Work (Kenya — text/photo; interactive map deferred)[V1 text, map deferred V2]
├── Featured Programs (4 cards → program pages)                 [V1]
├── Stories of Change (latest 3)                                [V1 — needs seed content,
│                                                                 see product-requirements.md]
├── Impact Numbers (qualitative until verified — §6.3)          [V1]
├── How Your Support Helps (transparency teaser)                [V1]
├── Latest Updates (news + events)                              [V1, events feed can be empty]
├── Partner With Us (corporate/institutional CTA)                [V1]
├── Newsletter Signup                                            [V1]
└── Footer (contact, legal, accessibility statement)             [V1]

ABOUT                                                            [V1]
├── Our Story                                                    [V1]
├── Our Mission                                                  [V1 — pending mission
│                                                                  statement decision]
├── Our Approach                                                 [V1]
├── Our Leadership                                               [V1 — blocked on Sammy
│                                                                  Tongoi bio, audit F-9]
├── Governance                                                   [V1 — CONTENT REQUIRED,
│                                                                  audit F-10]
├── Financial Transparency                                      [V1 — CONTENT REQUIRED,
│                                                                  audit F-10; this is the
│                                                                  Transparency Center, see
│                                                                  §9 below — non-negotiable
│                                                                  for the Donor Trust Loop]
└── Frequently Asked Questions                                   [V1 — minimal set, doubles
                                                                   as AEO content, saba.md §15.4]

OUR WORK                                                         [V1]
├── Education                                                    [V1]
├── Nutrition                                                    [V1]
├── Shelter & Family Support                                     [V1]
└── Youth Economic Empowerment                                   [V1 — pending Hunter
                                                                   Initiative relationship
                                                                   confirmation]

PROGRAMS & PARTNERS                                              [V1]
├── New Dawn [Educational Centre/Center — PENDING VERIFICATION]  [V1]
├── Bethel Kibera School                                         [V1]
├── The Nest Children's Home                                     [V1 — sensitive content,
│                                                                  see content-model.md
│                                                                  consent fields]
└── The Hunter Initiative                                        [V1 — PENDING: program
                                                                   page or partner page
                                                                   template depends on
                                                                   relationship answer]

IMPACT                                                           [V1 reduced scope]
├── Impact Overview                                              [V1]
├── Stories of Change                                            [V1]
├── Impact Metrics                                                [V1 — qualitative-only
│                                                                   until verified metrics
│                                                                   exist, §6.3]
├── Annual Reports                                                [V1 — CONTENT REQUIRED;
│                                                                   ships as an empty-state
│                                                                   page if no reports exist
│                                                                   yet, not a fabricated one]
└── Where Your Support Goes                                       [V1 — CONTENT REQUIRED,
                                                                    audit F-10 §9.3]

GET INVOLVED                                                     [V1]
├── Give (Donation Flow)                                         [V1 — highest priority
│                                                                   page on the entire site]
├── Partner With Us                                               [V1]
├── Volunteer                                                     [V1 — form only, no
│                                                                   application-tracking
│                                                                   portal, see feature matrix]
├── Fundraise                                                     [V2 — peer-to-peer
│                                                                   fundraising is not a V1
│                                                                   trust-loop dependency]
├── Pray / Support                                                [V1 — low effort, keep]
└── Subscribe (Newsletter)                                        [V1]

STORIES & NEWS                                                   [V1 reduced scope]
├── Stories                                                       [V1]
├── News                                                          [V1]
├── Updates                                                       [V2 — fold into News for
│                                                                   V1 rather than a 4th
│                                                                   parallel content type]
├── Events                                                        [V2 — ships as empty-state
│                                                                   if no near-term events;
│                                                                   not worth a bespoke
│                                                                   calendar UI in V1]
└── Media                                                         [V2 — press kit, see
                                                                    Resources below]

RESOURCES                                                        [V1 reduced scope]
├── Annual Reports                                                [duplicate of Impact →
│                                                                   Annual Reports; V1 should
│                                                                   pick ONE location, not both
│                                                                   — recommend keeping it
│                                                                   under Impact only]
├── Financial Documents                                           [same as above — fold into
│                                                                   Financial Transparency]
├── FAQs                                                          [duplicate of About → FAQ;
│                                                                   V1 should have one FAQ
│                                                                   page, not two]
├── Brand / Media Kit                                             [V2/Future — no evidence
│                                                                   this is needed for V1
│                                                                   audiences]
└── Downloads                                                     [Future — folds into
                                                                    whichever document types
                                                                    actually exist once real
                                                                    content is verified]

CONTACT                                                          [V1]
```

**Change from saba.md's sitemap:** the `RESOURCES` top-level section is largely collapsed into `ABOUT → Financial Transparency` and `ABOUT → FAQ` for V1. saba.md's version creates duplicate destinations for the same content (Annual Reports and FAQs each appear twice — once under Impact/About, once under Resources). For a small site with limited content volume (audit: content-inventory.md found zero downloadable documents currently), maintaining two navigation paths to the same handful of documents adds maintenance burden without adding value. Revisit if the document library grows enough to justify a dedicated Resources hub (Future).

---

## 4. Navigation Principles (unchanged from saba.md §4.2 — no audit finding contradicts these)

- Simplify — don't overwhelm with navigation depth.
- Mobile-first: hamburger menu <768px, persistent CTA visible at all times.
- Breadcrumbs on all subpages.
- Skip-to-content link for keyboard users.

**One addition driven by the audit:** the current site's nav conflates real pages with same-page anchors (`/#about`, `/#team` vs. `/posts`, `/difference/create` — see current-website-audit.md Route Inventory). The rebuilt nav must not repeat this — every top-level nav item should resolve to a real, independently linkable, indexable URL. This is also an SEO fix (audit F-2/F-3): anchor-only sections can't be indexed or shared as distinct pages.

---

## 5. Audience → Journey → IA Mapping

| Audience | Primary Journey | Key IA Nodes |
|---|---|---|
| Donors | Home → Transparency → Give → Receipt | Home, About/Financial Transparency, Get Involved/Give |
| Partners | Home → Our Work → Programs → Partner With Us → Contact | Our Work, Programs & Partners, Get Involved/Partner |
| Volunteers | Home → Get Involved → Volunteer → Contact | Get Involved/Volunteer |
| Existing Supporters | Home → Stories & News → Subscribe | Stories & News, Get Involved/Subscribe |
| Beneficiary Communities | Programs & Partners (informational) | Programs & Partners, Our Work |
| Researchers/Media | About → Leadership/Financial Transparency → Downloads | About, Impact/Annual Reports |
| Kenyan Diaspora | Home → Get Involved/Give (USD only in V1 — see product-requirements.md) | Get Involved/Give |

This table is unchanged in substance from saba.md §3.1/§3.2 — the audits didn't surface evidence that these journeys are wrong, only that almost none of the pages they depend on currently exist.
