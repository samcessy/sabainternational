# Current Website Audit — sabainternational.org

**Audit date:** 2026-08-10
**Method:** Remote inspection only — HTTP requests, HTML source review, and rendered-content extraction against the live production site. No CMS/admin access, no analytics access, no server access. Findings are limited to what is observable from the outside.
**Scope:** All routes discovered via navigation, sitemap probing, and direct path guessing (see §Route Inventory).

> This document satisfies saba.md §2.1. It is an input to Phase 1 (Product Strategy), not a build plan. Nothing here should be treated as verified organizational fact — see `docs/audit/content-inventory.md` for content-level Keep/Rewrite/Verify flags and the stakeholder verification list at the bottom of this file.

---

## Route Inventory

| Path | Status | Notes |
|---|---|---|
| `/` | 200 | Homepage (single-page, anchor-linked sections) |
| `/posts` | 200 | "Our Stories" — blog/news index |
| `/posts/bethel-kibera-school-website` | 200 | Only individual post found |
| `/categories/education` | 200 | Filtered post list, same generic page title |
| `/categories/nutrition` | 200 | Filtered post list, same generic page title |
| `/categories/shelter` | 200 | Filtered post list, same generic page title |
| `/difference/create` | 200 | Donation page ("Make a Difference") |
| `/admin` | 302 | Redirects — an admin panel exists and is reachable at a guessable URL |
| `/contact` | 405 Method Not Allowed | A POST-only endpoint exists; there is no GET-able `/contact` page |
| `/login`, `/register`, `/about`, `/donate`, `/faq`, `/volunteer`, `/partners`, `/events`, `/team`, `/dashboard` | 404 | Do not exist as standalone pages |
| `/sitemap.xml` | 404 | Does not exist |
| `/robots.txt` | 200 | Exists, permits all crawling (see Finding T-1) |

The entire public site is effectively **one page** (`/`) plus a thin blog and a donation form. There is no About, Team, Contact, FAQ, Volunteer, or Partner page as a distinct URL — "About Us" and "Our Team" are anchor sections (`/#about`, `/#team`) on the homepage, not navigable subpages. This confirms saba.md §1.2's framing of the current site as a single-page brochure.

---

## Findings

### F-1: No donation payment flow is actually usable
- **Finding:** `/difference/create` renders a donation form with a Stripe Checkout integration referenced in the page source, a Once/Monthly frequency toggle, and a single visible suggested amount (**$25**) — but no tiered amount options, no program designation selector, no currency selector, and no visible confirmation of what happens post-payment.
- **Evidence:** `grep` of page source shows `stripe`/`Stripe`/`checkout` tokens and fields `first_name`, `last_name`, `email`; only one dollar amount (`$25`) appears anywhere in the markup; no `<select>` elements for designation or currency were found.
- **Severity:** Critical
- **Impact:** This is the single most important conversion path on the entire site (the "Donor Trust Loop" in saba.md §3.2) and it is minimally built. A donor cannot choose $50/$100/$250, cannot designate a specific program, and has no visible reassurance (security badges, receipt promise, tax-deductibility statement) before entering payment.
- **Recommendation:** Treat the donation flow as the V1 priority per saba.md §8. Do not reuse this implementation as a starting point beyond confirming the existing Stripe account/credentials are usable.
- **Proposed Solution:** Rebuild per saba.md §8.1–8.3: suggested-amount grid, frequency, designation, multi-currency-ready (USD only in V1), Stripe Elements (not a bare Checkout redirect, to control branding/trust), and a receipt + confirmation email.

### F-2: No SEO metadata anywhere on the site
- **Finding:** Every single page — homepage, posts, category filters, donation page — serves the identical, generic `<title>Saba, International</title>`. There is no `<meta name="description">`, no Open Graph tags, no Twitter Card tags, no canonical URL, and no JSON-LD structured data anywhere in the HTML source.
- **Evidence:** `grep -iE 'og:|description|twitter:|application/ld\+json|canonical'` against the homepage and category pages returns zero matches. Verified across `/`, `/categories/education`, `/categories/nutrition`, `/categories/shelter`, `/difference/create`.
- **Severity:** Critical
- **Impact:** The site is effectively invisible to search engines beyond its raw text content. Every page looks identical in search results and social shares (same title, no preview image, no description snippet). This directly undermines the "Researchers/Media" and organic-donor-acquisition audiences in saba.md §3.1.
- **Recommendation:** Implement per-page metadata as a hard requirement for every route, per saba.md §15.1.
- **Proposed Solution:** SEO metadata as a first-class CMS field (title/description/OG/canonical/JSON-LD) on every content type, enforced at publish time.

### F-3: No sitemap.xml
- **Finding:** `/sitemap.xml` returns HTTP 404.
- **Evidence:** Direct request confirmed 404.
- **Severity:** High
- **Impact:** Search engines have no authoritative discovery mechanism for the site's (small number of) pages.
- **Recommendation:** Auto-generate and cache per saba.md §15.2.
- **Proposed Solution:** Laravel scheduled job regenerating `/sitemap.xml` daily from published content.

### F-4: robots.txt permits everything, including things that shouldn't be indexed
- **Finding:** `robots.txt` contains only `User-agent: *` / `Disallow:` (nothing disallowed).
- **Evidence:** Raw fetch of `/robots.txt`.
- **Severity:** Low (today) / Medium (post-rebuild if left unchanged)
- **Impact:** Not harmful today since there's little to hide, but if carried forward unmodified into the rebuilt admin/API surface, it would invite indexing of `/admin`, `/api`, and internal tooling.
- **Recommendation:** Rebuild `robots.txt` deliberately alongside the new IA; explicitly disallow admin and API routes.
- **Proposed Solution:** Generate `robots.txt` from route metadata rather than hand-maintaining it.

### F-5: No cookie/privacy consent despite third-party tracking
- **Finding:** Google Analytics (`gtag.js`, measurement ID `G-4BXY4L25CX`) loads unconditionally on every page load. No cookie banner, consent mechanism, or privacy policy link was found anywhere in the homepage source or navigation.
- **Evidence:** `gtag('config', 'G-4BXY4L25CX')` present in page source; no `cookie`/`consent`/`gdpr` string matches anywhere on the homepage; no Privacy Policy link in the nav or footer content extracted.
- **Severity:** High
- **Impact:** GDPR/CCPA exposure for an org courting an international (including EU-adjacent diaspora) donor base, and directly contradicts saba.md §1.2's "no trust signals" and §9.1's requirement for a data-protection policy.
- **Recommendation:** Do not carry the current unconditional GA tag forward as-is. Adopt saba.md §18.1's privacy-first analytics guidance (Plausible/Fathom, or GA4 with IP anonymization + a real consent/privacy policy).
- **Proposed Solution:** Ship a Privacy Policy page and, if GA4 is retained, a consent mechanism before any non-essential tracking script fires.

### F-6: No security headers beyond the Laravel defaults
- **Finding:** Response headers include `x-frame-options`, `x-xss-protection`, and `x-content-type-options`, but **no** `Strict-Transport-Security` (HSTS), **no** `Content-Security-Policy`, and **no** `Referrer-Policy`.
- **Evidence:** `curl -I https://sabainternational.org/` header dump.
- **Severity:** Medium
- **Impact:** HTTP→HTTPS redirect works (confirmed 301), but without HSTS a user's first request in a session can still be intercepted before the redirect. No CSP means no defense-in-depth against injected scripts.
- **Recommendation:** Add HSTS, CSP, and Referrer-Policy per saba.md §24.2.
- **Proposed Solution:** Set via middleware/Nginx config in the new deployment; validate with securityheaders.com post-launch.

### F-7: reCAPTCHA is configured with an empty site key
- **Finding:** The donation page loads `https://www.google.com/recaptcha/api.js?render=explicit` and initializes `grecaptcha.render(this.$el, { sitekey: '', ... })` — the site key is a literal empty string.
- **Evidence:** Verbatim from page source: `sitekey: ''`.
- **Severity:** Medium
- **Impact:** Bot/spam protection on the donation (and likely contact) form is non-functional; the reCAPTCHA widget cannot validate against a blank key. This is either a broken deployment or a misconfigured environment variable.
- **Recommendation:** Do not assume any existing anti-spam protection carries forward. Rebuild rate limiting + honeypot/reCAPTCHA v3 per saba.md §23.2 from scratch and test it end-to-end before launch.
- **Proposed Solution:** Configure reCAPTCHA v3 (or honeypot + rate limiting as primary, reCAPTCHA as secondary) with real, environment-scoped keys, verified in staging.

### F-8: Stale content — one blog post, dated "3 years ago"
- **Finding:** `/posts` shows exactly one article ("Bethel Kibera School Website"), and both the index and the article itself display its date only as the relative string "3 years ago" (no absolute date rendered).
- **Evidence:** WebFetch extraction of `/posts` and `/posts/bethel-kibera-school-website`.
- **Severity:** High
- **Impact:** Confirms saba.md §1.2's "stale content" finding directly. A single multi-year-old post signals organizational inactivity to any donor or partner who clicks through, undermining trust exactly where the Donor Trust Loop needs it most.
- **Recommendation:** This is a content/editorial problem, not just a technical one — see saba.md §3.3 (Content Sustainability Mandate) and §16.3.
- **Proposed Solution:** Do not launch the new site with a similarly bare stories section. Seed at least 3–5 verified stories before go-live, and put the quarterly publishing cadence and freshness dashboard in place before declaring V1 done.

### F-9: Incomplete team profile ("TBD")
- **Finding:** The "Our Team" section lists Sammy Tongoi (role: Advisor) with bio text literally reading "TBD".
- **Evidence:** WebFetch extraction of the homepage team section.
- **Severity:** Medium
- **Impact:** Publicly visible placeholder text undermines credibility exactly where a Partner or Donor audience is evaluating organizational legitimacy.
- **Recommendation:** Must be resolved via stakeholder verification (see §2.4 of saba.md) before this bio migrates to the new site. Do not carry "TBD" forward — either get a real bio or omit the team member until one exists.
- **Proposed Solution:** Add a CMS-level publish guard: a team member record cannot be published with an empty/placeholder bio field.

### F-10: No trust/transparency signals anywhere on the site
- **Finding:** No mention of 501(c)(3) status, EIN, tax-exempt verification, annual reports, audited financials, board governance structure, or a "where your money goes" breakdown anywhere in the homepage, donation page, or any discovered subpage.
- **Evidence:** Full-text review of homepage extraction and donation page extraction; no matches for "501", "EIN", "tax", "annual report", "financial", or "governance".
- **Severity:** Critical
- **Impact:** This is the single largest credibility gap for the Donor and Partner audiences (saba.md §3.1) and is a hard blocker for Google for Nonprofits, Guidestar/Candid Seal of Transparency, and any diligence-minded institutional donor.
- **Recommendation:** Build the Transparency Center as specified in saba.md §9 as part of V1, not a later phase — it is foundational to the Donor Trust Loop, not an enhancement to it.
- **Proposed Solution:** Stakeholder verification (§2.4) must produce the actual EIN, tax-exempt letter, and any existing financial statements before this section can ship with real content; until then, fields should be marked `CONTENT REQUIRED` rather than fabricated or omitted silently.

### F-11: Mission statement mismatch with the rebuild brief
- **Finding:** The live site's mission statement is *"Supporting education, nutrition and shelter for underprivileged youth and their families in East Africa."* This differs from the mission statement specified in saba.md §5.1.2: *"Creating pathways to a stronger future for children, youth and families in East Africa."*
- **Evidence:** WebFetch extraction of the homepage "Our Story"/mission area, compared against saba.md line 190.
- **Severity:** Medium
- **Impact:** These are not the same claim — the current one is programmatic (what Saba does), the proposed one is aspirational (what Saba enables). Shipping the new copy without sign-off would mean putting words in the organization's mouth that were never verified with the board.
- **Recommendation:** Flag explicitly for stakeholder confirmation (§2.4) — do not silently adopt either version.
- **Proposed Solution:** Add both to the stakeholder interview agenda below; whichever is chosen becomes the canonical mission statement across every page and structured-data block.

### F-12: Partner program category labels are inconsistent with saba.md's program groupings
- **Finding:** On the live site, partner cards are tagged: New Dawn = Education + Nutrition; Bethel Kibera School = Nutrition only; The Nest = Shelter; The Hunter Initiative = Education. saba.md §1.1 groups them differently (New Dawn & Bethel under Education; feeding programs under Nutrition as a cross-cutting category; The Nest under Shelter & Family Support; Hunter Initiative under Youth Economic Empowerment, not Education).
- **Evidence:** Live site category tags per WebFetch extraction vs. saba.md §1.1 and §4.1 IA.
- **Severity:** Low
- **Impact:** Minor, but worth resolving deliberately rather than by accident — it affects the "Our Work" IA and any category-filtered SEO pages.
- **Recommendation:** Confirm final category taxonomy with stakeholders rather than inheriting either source uncritically.
- **Proposed Solution:** Lock taxonomy in the Phase 1 content model before building `program_categories`.

### F-13: Admin panel is reachable at a predictable path
- **Finding:** `/admin` returns a 302 redirect (consistent with a login-gated admin panel existing at that exact path).
- **Evidence:** `curl -o /dev/null -w "%{http_code}"` against `/admin`.
- **Severity:** Low
- **Impact:** Not a vulnerability by itself (redirecting to login is correct behavior), but worth noting for the security audit baseline — confirms an admin surface exists and is internet-reachable.
- **Recommendation:** No action required on the current site; ensure the rebuilt admin panel enforces MFA (saba.md §10.4) and is rate-limited against brute force.
- **Proposed Solution:** N/A for current site — carried into Phase 9 security testing checklist for the new build.

### F-14: No social media presence linked from the site
- **Finding:** No social media links (Facebook, Instagram, X/Twitter, LinkedIn, YouTube) appear anywhere on the homepage or footer content extracted.
- **Evidence:** WebFetch extraction explicitly returned "None present" for social media links.
- **Severity:** Low
- **Impact:** Missed channel for the "Existing Supporters" and "Stories & News" engagement loop (saba.md §3.1, §16).
- **Recommendation:** Confirm with stakeholders whether social accounts exist but simply aren't linked, or don't exist at all.
- **Proposed Solution:** Add to stakeholder interview agenda.

---

## Positive Observations (do not rebuild these away)

- HTTP→HTTPS redirect is correctly configured (301).
- Basic Laravel security headers (`X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`) are present.
- The site is genuinely server-rendered (not a thin SPA shell) — meaningful HTML content is present in the raw response, which is a better SEO starting point than a client-only render would be.
- A Stripe integration already exists in some form, which may mean an active Stripe account/merchant relationship the rebuild can reuse rather than establish from scratch (needs stakeholder confirmation).
- Partner organizations (New Dawn, Bethel Kibera School, The Nest) each have their own independently linked websites, suggesting existing digital relationships to preserve as outbound links.

---

## Stakeholder Verification Required (blocks Phase 1 sign-off)

Per saba.md §2.4, the following cannot be resolved by remote inspection and require a direct conversation with Tim/Cathy Woller or board:

1. Which mission statement is canonical (see F-11) — current site copy or the saba.md proposed copy, or a new one.
2. The Hunter Initiative: official Saba program or independent partner? (Its "View site" link on the live site points back to `sabainternational.org` itself, not an external site like the other three partners — this is itself worth surfacing to stakeholders as a possible content bug or intentional signal that it's more tightly integrated.)
3. Exact legal names: "New Dawn Educational Centre" vs. what the live site calls it ("New Dawn" only, "New Dawn Educational Center" per its own external domain `newdawneducationcenter.newdawnkenya.com`) — note the **Center/Centre spelling discrepancy** between saba.md and the partner's own domain.
4. "Bethel Kibera School" vs. "Bethel Outreach Children's Center" (saba.md §2.4) vs. the live site's consistent use of "Bethel Kibera School" — which is correct?
5. Sammy Tongoi's actual bio (currently "TBD" — F-9).
6. 501(c)(3) EIN and tax-exempt documentation location (F-10).
7. Whether existing Stripe credentials/account should be reused (F-1 positive observation).
8. Whether social media accounts exist under another handle (F-14).
9. Board governance structure and any existing financial reports.
10. Verified founding/operational dates for cross-check against the ones already displayed (2009 Saba founded; 1997 The Nest; 2006 New Dawn & Bethel Kibera) — the live site is internally consistent, but none of these are sourced to a verifiable document.

**Rule carried forward from saba.md §2.4 and §35.1:** every fact above must be either verified through this interview or marked `CONTENT REQUIRED` in the CMS — never fabricated.
