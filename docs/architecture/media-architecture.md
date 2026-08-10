# Media Architecture

**Phase:** 4 — Architecture
**Status:** Decided
**Inputs:** saba.md §20, §24.3, §7.3; `docs/content-model.md` §2.10; `docs/architecture/database-erd.md` §2

---

## 1. Upload → Variant → CDN Pipeline

```
Admin uploads (drag-drop, multi-file) in the CMS media library
  → File validated: MIME type checked against actual file content (not just the
    extension), extension whitelist (images: jpg/jpeg/png/webp; documents: pdf
    only), size limit enforced, executable extensions rejected outright
    regardless of MIME sniffing result (saba.md §24.3)
  → Original stored to S3-compatible storage, NOT the web root — no file under
    this pipeline is ever directly executable even if a validation bug let
    something malicious through
  → Media row created (status effectively "processing" until variants exist)
  → Queued Job (`GenerateMediaVariants`) picks it up:
      - EXIF extracted and stored in media.exif_data (admin-visible, for
        photographer attribution / date — saba.md §20.1)
      - GPS/location EXIF data is STRIPPED from every variant that gets served
        publicly (see §3 — this is a privacy requirement, not just an
        optimization one, given saba.md §7.3's "never publish exact
        vulnerable-person locations" rule)
      - 6 variants generated per saba.md §20.2: thumbnail (150×150, crop),
        small (400×300, fit), medium (800×600, fit), large (1200×800, fit),
        hero (1920×1080, fit), social (1200×630, crop)
      - Crop variants (thumbnail, social) use media.focal_point_x/y (new
        columns, see §2) rather than a naive center-crop, so a subject isn't
        cut out of frame — cheap to capture at upload time (a click-to-set
        point on the image in the admin UI), meaningfully better result
      - Each variant encoded as WebP (see §4 for format decision)
      - Variant rows written to media_variants (docs/architecture/database-erd.md §2)
  → CDN serves variants from S3 origin (docs/architecture/system-architecture.md §2)
```

**Why a queued job, not inline processing:** saba.md §20 implies real image work (multiple resizes, format conversion) on every upload — doing this synchronously in the upload request would make the admin UI hang on every image, and a large batch upload would risk request timeouts. This is already the pattern the rest of the app follows (`docs/architecture/system-architecture.md` §2's `Jobs` component handles email the same way) — media processing is just another entry in that same queue.

---

## 2. Schema Refinement (vs. `database-erd.md` §2)

Two columns needed for the pipeline above, added to `media`:
```
focal_point_x  decimal, nullable, default 0.5   -- 0.0–1.0, fraction of image width
focal_point_y  decimal, nullable, default 0.5   -- 0.0–1.0, fraction of image height
```
Defaulting to center (0.5, 0.5) means media works correctly even before an admin manually sets a focal point — this is a progressive-enhancement field, not a required one, so it doesn't block the "alt_text required to publish" rule already established in `docs/content-model.md` §2.10 from being the only hard publish gate on this table.

---

## 3. Privacy: EXIF Stripping Is Not Optional

This is worth calling out on its own because it's easy to treat as a pure image-optimization detail when it's actually a governance requirement. Photos of children at New Dawn, Bethel Kibera School, or especially The Nest (children of incarcerated mothers — flagged repeatedly since `docs/audit/content-inventory.md`'s "Consent-sensitive content" section) can carry GPS coordinates in their EXIF data if taken on a phone with location services on. saba.md §7.3 is explicit: *"Never publish... exact vulnerable-person locations."*

**Rule:** every publicly-served variant (all 6 in `media_variants`) has GPS/location EXIF fields stripped during generation, regardless of what's in the original. The **original** file (S3, not public-facing, not one of the 6 served variants) retains full EXIF in case it's ever needed for legitimate internal/legal purposes — but nothing a website visitor's browser downloads carries that data. This needs to be a variant-generation step that's structurally impossible to skip, not a checklist item a busy admin has to remember.

---

## 4. Format Decision: WebP Only in V1, Not AVIF

saba.md §19.2/§20 mention WebP/AVIF together. V1 generates **WebP only**. AVIF encoding is slower and less universally supported by the image-processing tooling likely to be used (Intervention Image / GD vs. Imagick availability varies by host), and the marginal file-size win over WebP doesn't justify the added encoding complexity and processing time for a site whose whole audience isn't bandwidth-constrained enough to make that difference decisive. This is the same phased-execution logic as everywhere else in this project: WebP already gets the bulk of the Core Web Vitals benefit (`docs/product-requirements.md` §10) at a fraction of the implementation cost; AVIF is a V2 addition if real performance data ever shows it's needed.

---

## 5. CDN / Transformation Service Decision

saba.md §20.2: *"Use a CDN image transformation service (Cloudflare Images or Imgix) **if budget allows**."* Given the resource-constraint principle (`docs/project-overview.md` §1, saba.md §1.4), V1 does **not** adopt a paid real-time transformation service — those bill per transformation/request, an ongoing operating cost for a small nonprofit. Instead: the 6 fixed variants (§1) are pre-generated once at upload time and served as static files from S3 through a standard CDN cache layer (`docs/architecture/system-architecture.md` §2's `CDN` component). This covers every variant size saba.md actually specifies without a recurring per-image bill. If a real need for arbitrary on-the-fly sizing ever emerges (e.g., a future responsive-design requirement the fixed 6 sizes don't cover), that's the trigger to revisit Cloudflare Images/Imgix — not before.

---

## 6. Public vs. Private Files — Two Different Things Live Under "Media"

Not everything the system stores is meant to be publicly fetchable, and conflating them would be a mistake:

| File type | Access | Why |
|---|---|---|
| Program/story/team photos, annual reports, financial documents, policy PDFs | **Public**, served via CDN | This is the Transparency Center's entire purpose (`docs/information-architecture.md` §3) — these documents exist specifically to be found and downloaded. |
| Donation receipt PDFs | **Private** | Contains a donor's name, amount, and (for non-anonymous gifts) identifying info. Generated and emailed as an attachment (saba.md §21.1); if retained for admin reference, served only through an authenticated Laravel controller route gated by the Finance Manager/Super Administrator policy (`docs/architecture/authorization-model.md` §3) — never a bare public S3/CDN URL, per saba.md §24.3's "serve private files via Laravel controller, not direct URL." |
| Original (pre-variant, full-EXIF) media files | **Admin-only** | Not one of the 6 served variants (§3); accessible from the CMS media library to logged-in Editors/Super Administrators for re-cropping or download, not linked anywhere on the public site. |

The `media`/`media_variants` tables in `docs/architecture/database-erd.md` §2 model the public case. Receipts don't go through this pipeline at all — they're generated fresh per transaction (`docs/architecture/payment-architecture.md` §5's webhook handler) and are a Mail attachment concern, not a Media Library concern.

---

## 7. Upload Security (saba.md §24.3)

- MIME type validated against actual file content (e.g., Laravel's `File` validation rule with `mimes:`/`mimetypes:`), not the client-supplied filename extension alone — a `.jpg` that's actually a PHP script must fail validation on content inspection, not just extension matching.
- Extension whitelist, not blacklist: only `jpg/jpeg/png/webp` (images) and `pdf` (documents) are ever accepted; everything else — including any executable extension — is rejected by default rather than enumerated as forbidden.
- Size limits enforced server-side (specific limits set per content type during Phase 5 implementation; not fabricated here as a number without a real hosting-cost basis).
- **ClamAV virus scanning — deferred to V2/Future**, not V1. saba.md §24.3 lists it as "if possible." Running a ClamAV daemon is real operational overhead (a persistent service to patch and monitor) that a small nonprofit's hosting setup (see `docs/architecture/deployment-architecture.md`) may not support without added cost. V1's realistic threat model — a handful of trusted admin users uploading images and PDFs, not an open public upload surface — is adequately covered by strict MIME/extension whitelisting and size limits. Revisit if/when a managed cloud scanning API becomes affordable, or if the upload surface is ever opened to untrusted (public) submitters.
