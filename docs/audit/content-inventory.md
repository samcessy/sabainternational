# Content Inventory — sabainternational.org

**Audit date:** 2026-08-10
**Method:** Catalog of every piece of text, image, video, and downloadable resource discoverable via remote inspection of the live site. Per saba.md §2.2, each item is flagged: **Keep / Rewrite / Update / Archive / Remove / Needs Verification**.

> Images could not be individually cataloged — the homepage's raw HTML contains only one `<svg>` and one CSS `background-image` reference, with no `<img>`/`<picture>` tags in the static markup (see technical-audit.md §3). A full image inventory requires a rendered-DOM pass (headless browser), which is recommended as a fast follow-up before Phase 11 content migration.

---

## Text Content

| Content Item | Current Text / Summary | Flag | Notes |
|---|---|---|---|
| Mission statement | "Supporting education, nutrition and shelter for underprivileged youth and their families in East Africa." | **Needs Verification** | Conflicts with saba.md's proposed mission statement (see current-website-audit.md F-11). Must be resolved with stakeholders before either version ships. |
| Organization name origin ("Our Name") | "Saba is a hebrew word meaning to fill - up to overflowing" | **Keep** | Distinctive, authentic brand story element; no reason to rewrite pending stakeholder confirmation of accuracy. |
| Founding story ("Our Story") | Est. 2009 by Tim & Cathy Woller following USAF service in Kenya (2005–2008) | **Update** | Factually plausible and specific, but rewrite for narrative quality per saba.md §5/§7 storytelling structure; verify dates with stakeholders. |
| New Dawn partner description | Est. 2006; educational center + mentorship; academics, counseling, spiritual guidance, meals; categories Education + Nutrition | **Rewrite** | Needs the full Problem→Context→Role→Partner→Intervention→Outcomes structure required by saba.md §6.1 — current text is a one-paragraph summary only. |
| Bethel Kibera School partner description | Est. 2006; began as 3-child daycare for HIV-affected children, now full primary education + food + mentorship; 16 years of service per the one blog post | **Rewrite** | Same structural gap as above. Note: blog post says "16 years" as of ~3 years ago, founder named as Mary Adinda — this detail doesn't appear on the homepage card itself and should be reconciled. |
| The Nest partner description | Est. 1997; "rescues affected children and improves their living conditions during imprisonment of their mothers"; rehabilitation + family reintegration | **Rewrite** | Sensitive subject matter (children of incarcerated mothers) — must be reviewed against saba.md §7.3 ethical storytelling rules (no identifiable sensitive circumstances without documented consent) before expanding this content. |
| The Hunter Initiative description | Software development training for economically disadvantaged youth | **Needs Verification** | Its "View site" link points back to the homepage itself rather than an external site (see technical-audit.md §5) — need to confirm whether it's an official Saba program or independent partner (saba.md §2.4 asks this explicitly). |
| Team: Tim & Cathy Woller | Founders; established org 2009 after USAF assignment | **Update** | Solid factual core; expand into a fuller founder story per saba.md's "Founder Story" content type (§7.1). |
| Team: Scott Organ | Ambassador; joined board 2011; adopted twins from The Nest | **Update** | Personal/emotional connection to the org — good material for a Donor/Partner Story, pending consent to publish the adoption detail (sensitive — involves minors; needs explicit consent verification per §7.3). |
| Team: Ryan Shaw | Ambassador; joined board 2015 | **Update** | Thin bio; needs expansion with actual verified role/responsibilities. |
| Team: Sammy Tongoi | Advisor; bio reads **"TBD"** | **Needs Verification** | Cannot migrate as-is — placeholder text (current-website-audit.md F-9). Do not publish until a real bio is supplied; CMS must block publish on placeholder content. |
| Team: Helen Kahl | Treasurer; librarian; involved since 2007; donated uniforms | **Update** | Fine core detail; needs a proper title/role confirmation ("Treasurer" — verify this is her current, accurate title). |
| Team: Samuel Chege | Software Engineer; New Dawn graduate 2010; Kenya Methodist University; trained by Saba | **Keep** | Strong "Youth Story"/outcomes narrative — a beneficiary who became staff. High-value content for the Impact section (saba.md §6) once consent to publish is confirmed. |
| Blog post: "Bethel Kibera School Website" | Announces a sponsored website build for Bethel Kibera School | **Archive** | Dated ~3 years ago (relative-only, no absolute date shown — itself a defect, see below). Low ongoing relevance; archive rather than migrate as a "latest" story, per saba.md §16.3's 3-year auto-flag rule. |
| Category taxonomy (Education / Nutrition / Shelter) | Applied inconsistently across partner cards (see current-website-audit.md F-12) | **Needs Verification** | Reconcile against saba.md §1.1's four-pillar structure (adds Youth Economic Empowerment as a distinct pillar) before building `program_categories`. |
| Newsletter signup copy | Minimal — email field + "Subscribe me to the newsletter" checkbox text | **Rewrite** | Functionally fine concept; needs consent-language review for GDPR/CCPA compliance (saba.md §22) and visual/UX rebuild. |
| Donation page copy | Minimal — Once/Monthly toggle, $25 shown, first/last/email fields | **Rewrite** | See current-website-audit.md F-1 — this is a near-empty shell relative to saba.md §8's requirements. No "why give" narrative, no trust signals, no designation options found in source. |

---

## Images

| Item | Status |
|---|---|
| Homepage hero/section imagery | **Needs Verification** — not enumerable from static HTML; likely present but rendered via JS/component templates not visible in the raw response. Requires a headless-browser DOM snapshot to catalog properly (alt text, file names, dimensions). |
| Bethel Kibera School blog post images | Two images referenced per WebFetch extraction: a school logo/branding image and a screenshot of the sponsored website. **Needs Verification** for alt text, consent status, and usable resolution/licensing. |
| Team member photos | Not confirmed present or absent — team section extraction returned names/bios but the tool did not confirm photo presence. **Needs Verification.** |

**Recommendation:** Before Phase 11 content migration, run a full page-render pass (e.g., via the `claude-in-chrome` skill or a headless browser script) to enumerate every actual `<img>` in the rendered DOM, its `alt` text (or lack thereof), and source file, then re-flag each with Keep/Rewrite/Archive/Remove per saba.md §20's media governance requirements (consent status, photographer attribution, program association — none of which can be assessed without seeing the actual images).

---

## Videos

None found. No `<video>` embeds, YouTube/Vimeo iframes, or video references were present in any page extracted (homepage, posts index, individual post, donation page).

---

## Downloadable Resources / Documents

None found. No PDF links, annual reports, financial statements, or downloadable documents of any kind were discovered anywhere on the site. This is consistent with current-website-audit.md F-10 (no transparency/governance documents exist on the current site at all) — there is nothing to migrate in this category; everything here is **Needs Verification / CONTENT REQUIRED** pending the stakeholder interview.

---

## Cross-Cutting Flags

- **`CONTENT REQUIRED` (per saba.md §2.4 rule):** 501(c)(3) EIN, tax-exempt letter, annual reports, financial statements, board governance structure/bios beyond the 5 people listed, Sammy Tongoi's bio, confirmation of The Hunter Initiative's organizational relationship to Saba, exact legal program names (Centre vs. Center; Bethel Kibera School vs. any other name), verified impact statistics (none currently exist on the site to migrate — every impact number will need to be sourced fresh, per §6.3's qualitative-statement fallback rule).
- **Consent-sensitive content requiring explicit sign-off before migration:** The Nest's description (children of incarcerated mothers), Scott Organ's bio (adopted children from The Nest — involves minors), any future expansion of Samuel Chege's story (named individual, verifiable biography — confirm he consents to continued/expanded public use).
- **Absolute dates missing:** every date on the current site (blog post, "3 years ago") is relative-only in the rendered display; the underlying absolute dates must be pulled from the CMS/database if access becomes available, or re-verified with stakeholders, before migration — do not carry forward relative date strings into the new CMS's `published_at` field.

This inventory should be treated as a starting checklist for the Phase 1 content model and Phase 11 migration — not a complete substitute for direct CMS/database access, which would allow a much more exhaustive catalog (all posts regardless of index-page visibility, unpublished drafts, full media library).
