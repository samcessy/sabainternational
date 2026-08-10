# Technical Audit — sabainternational.org

**Audit date:** 2026-08-10
**Method:** Black-box inspection via `curl` (headers, redirects, raw HTML source) and remote content rendering. No server, DNS panel, hosting, or repository access. This is deliberately narrower than saba.md §2.3 asks for — items requiring server-side access (mobile device lab testing, Lighthouse/WebPageTest runs, actual DB/stack introspection) are listed as **Not Assessable Remotely** rather than guessed at.

---

## 1. Stack Fingerprint

| Signal | Observation |
|---|---|
| Web server | `nginx` (from `Server` response header) |
| Application framework | Laravel — confirmed via `csrf-token` meta tag, `XSRF-TOKEN` / `saba_international_session` cookies (Laravel's default session cookie naming convention), and Laravel-style 419/error conventions implied by CSRF token presence |
| Rendering | Server-rendered HTML (not a client-only SPA shell) — meaningful markup present in the raw `curl` response before any JavaScript executes |
| Frontend JS | Uses Alpine.js-style directives (`x-data`-like patterns inferred from the reCAPTCHA init script structure) — not confirmed as Alpine specifically without page source access to `<script>` bundles |
| Analytics | Google Analytics 4 via `gtag.js`, measurement ID `G-4BXY4L25CX`, loaded unconditionally with no consent gate |
| Spam protection | Google reCAPTCHA (`recaptcha/api.js?render=explicit`) — configured with an **empty site key** (`sitekey: ''`), i.e., non-functional (see current-website-audit.md F-7) |
| Payments | Stripe references present in `/difference/create` page source (`stripe`, `Stripe`, `checkout` tokens) |

This is notable context for the rebuild: the current site is **already Laravel**. This lowers migration risk for content/data if database access becomes available during Phase 11, but the target architecture (saba.md §11–13: API-first Laravel + separate Vue 3 SPA) is a genuine rebuild, not an upgrade — the current site's frontend is server-rendered Blade/Alpine, not an SPA consuming a JSON API.

---

## 2. HTTP & TLS

```
$ curl -I https://sabainternational.org/
HTTP/2 200
server: nginx
content-type: text/html; charset=UTF-8
vary: Accept-Encoding
cache-control: no-cache, private
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
x-content-type-options: nosniff
```

```
$ curl -I http://sabainternational.org/
HTTP/1.1 301 Moved Permanently
Location: https://sabainternational.org/
```

**Findings:**
- HTTP/2 is in use. ✅
- HTTP→HTTPS redirect is correctly configured (301). ✅
- **Missing headers:** `Strict-Transport-Security` (HSTS), `Content-Security-Policy`, `Referrer-Policy`, `Permissions-Policy`. ❌
- `cache-control: no-cache, private` is set globally, including presumably on the homepage — this prevents any CDN/browser caching of largely-static marketing content, which will hurt TTFB/LCP at scale. Should be reviewed; static/marketing pages should be cacheable.
- **Not Assessable Remotely:** SSL Labs grade (requires a full TLS handshake analysis tool, not just `curl`), actual TLS version/cipher suite negotiated, HTTP/3 support.

---

## 3. Performance (Surface-Level Only)

```
$ curl -o /dev/null -w "TTFB: %{time_starttransfer}s | Total: %{time_total}s | Size: %{size_download} bytes" https://sabainternational.org/
TTFB: 1.031s | Total: 1.279s | Size: 48,455 bytes
```

- **TTFB of ~1.03s** is well above the saba.md §19.1 target of <600ms. A single `curl` sample from one location isn't conclusive, but it's a clear signal to re-measure once real infra is chosen.
- Homepage HTML payload alone (before assets) is ~48KB, which is not excessive.
- Raw HTML contains only **one** `<svg>` and **one** `background-image` reference — no `<picture>`, `<source srcset>`, or `<img>` tags were found in the static markup, meaning either (a) images are injected via JavaScript/component templates not visible in the initial response, or (b) the homepage is genuinely image-light. This needs a rendered-DOM check (headless browser) to resolve conclusively — flagged as **Not Assessable Remotely** with `curl` alone.
- 5 external `<script src>` tags and 7 stylesheet `<link>` tags load on the homepage. Not excessive, but no evidence of code-splitting, deferred loading, or `font-display: swap` was checked (requires asset inspection).
- **Not Assessable Remotely:** actual Core Web Vitals (LCP/INP/CLS) — require a real browser session (Lighthouse/PageSpeed Insights/CrUX data), which is out of scope for header/HTML inspection.

---

## 4. SEO Technical Baseline

| Check | Result |
|---|---|
| `<title>` unique per page | ❌ — identical `Saba, International` on every route checked (`/`, `/categories/education`, `/categories/nutrition`, `/categories/shelter`, `/difference/create`) |
| `<meta name="description">` | ❌ Absent on all checked pages |
| Open Graph tags | ❌ Absent |
| Twitter Card tags | ❌ Absent |
| Canonical URL | ❌ Absent |
| JSON-LD structured data | ❌ Absent |
| `robots.txt` | ✅ Present, permits all crawling (`Disallow:` empty) |
| `sitemap.xml` | ❌ 404 |
| Favicon / manifest `<link>` tags | ❌ None found in `<head>` |
| Clean URLs | ⚠️ Mixed — `/categories/education` and `/posts/bethel-kibera-school-website` are clean; but most "pages" are homepage anchors (`/#about`, `/#team`) rather than real routes, so there's little URL structure to evaluate either way |

This corroborates current-website-audit.md F-2, F-3, F-4 with the underlying technical evidence.

---

## 5. Broken Links / Duplicate Content

- **The Hunter Initiative's "View site" CTA links to `https://sabainternational.org/`** — i.e., back to itself — while the other three partner cards (New Dawn, Bethel Kibera School, The Nest) link to genuinely external partner domains. This is either a content bug (missing external URL) or an intentional signal that Hunter Initiative is more tightly integrated into Saba itself rather than an independent partner. Flagged in the stakeholder verification list in current-website-audit.md (item 2).
- Category pages (`/categories/education`, `/categories/nutrition`, `/categories/shelter`) all render with the same generic `<title>` — not broken, but functionally duplicate from a search-engine perspective (no unique title/description per filter view).
- No 404s were found among the linked-to internal paths (nav, posts, categories, donation) — the small link graph that exists is internally consistent.
- Route probing found several **expected-but-missing** paths returning 404 (`/about`, `/contact` as a GET page, `/donate`, `/faq`, `/volunteer`, `/partners`, `/events`, `/team`) — consistent with current-website-audit.md's finding that these are homepage anchors, not real pages.

---

## 6. Security Headers Summary

| Header | Present? |
|---|---|
| `X-Frame-Options` | ✅ `SAMEORIGIN` |
| `X-Content-Type-Options` | ✅ `nosniff` |
| `X-XSS-Protection` | ✅ `1; mode=block` (legacy header, harmless but no longer meaningful in modern browsers — CSP is the real replacement) |
| `Strict-Transport-Security` | ❌ Missing |
| `Content-Security-Policy` | ❌ Missing |
| `Referrer-Policy` | ❌ Missing |
| `Permissions-Policy` | ❌ Missing |
| Session cookie flags | `httponly`, `samesite=lax` present on `saba_international_session`; **no `Secure` flag visible in the `Set-Cookie` string itself** (though served only over HTTPS in practice via the 301 redirect) |

**Not Assessable Remotely:** actual OWASP ASVS Level 1 control testing (auth bypass, injection, rate-limit effectiveness) — this requires either credentialed access or active security testing, which is out of scope for a passive audit and would need explicit authorization to perform (per saba.md §26.6, this belongs in Phase 9/10 of the rebuild, against the *new* application, not the live production site).

---

## 7. Mobile Behavior

- `<meta name="viewport" content="width=device-width, initial-scale=1">` is present — correct baseline for responsive rendering.
- **Not Assessable Remotely:** actual responsive layout behavior, touch target sizing, hamburger menu functionality — these require rendering in a real or emulated viewport, not header/source inspection. Recommend a follow-up pass using a browser automation tool (e.g., the `claude-in-chrome` skill) if a true device-emulation sweep is wanted before Phase 2 wireframing.

---

## 8. Summary of Gaps Against saba.md Targets

| Area | Target (saba.md) | Current State |
|---|---|---|
| LCP | < 2.5s | Not measurable via `curl`; TTFB alone already exceeds target (§19.1) |
| TTFB | < 600ms | ~1.03s observed (single sample) |
| HSTS | Required (§24.2) | Absent |
| CSP | Required (§24.2) | Absent |
| Sitemap | Required (§15.2) | Absent (404) |
| Per-page metadata | Required (§15.1) | Absent on every page checked |
| MFA on admin | Required (§10.4) | Not assessable remotely; admin panel exists and redirects to login (positive baseline) |
| Cookie consent for analytics | Implied by §18.1 privacy-first stance | Absent despite unconditional GA4 |

---

## Not Assessable Remotely — Requires Follow-Up

These items from saba.md §2.3 need either server/hosting access, a real browser rendering pass, or an active (authorized) security scan — none of which are appropriate to run against a live production nonprofit site without coordination:

1. Actual Core Web Vitals (LCP/INP/CLS) via Lighthouse or CrUX data.
2. SSL Labs TLS configuration grade.
3. Real device/viewport testing across the breakpoints in saba.md §13.4.
4. Database schema, hosting provider, backup configuration, deployment pipeline.
5. Authenticated security testing (auth bypass, rate-limit effectiveness, file upload abuse) — explicitly requires authorization before running against production, per this environment's security-testing guardrails.
6. Server response time under load (this audit used single-request sampling only).

Recommend scheduling these as part of Phase 4 (Architecture) once hosting/access decisions are made, rather than attempting them against the current live site.
