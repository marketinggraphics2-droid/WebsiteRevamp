# SEO Hacker review — "For Revision (IQ Team)" items

Source: `DynamIQ - Website Redesign - Sheet1.csv` (END AS OF SEPTEMBER 08, 2026).
81 rows marked **For Revision (IQ Team)**. Rows marked *For Clarification from IQ Team*
and *No SH Concerns* are out of scope here.

Legend: `[ ]` open · `[x]` done · `[?]` blocked on input from IQ/client

---

## A. Cross-page / shared components

- [x] **A1** Card alignment — titles, excerpts and Read more must line up across a row
      regardless of copy length. Rows 20 (Blogs), 22 (News & Events), 80 (Search results).
      → `template-parts/post-card.php`, `.post-card*` in `assets/css/main.css`
- [x] **A2** Job listing cards — same alignment for job title, location, description, Read more.
      Row 24. → `page-career.php`, `.career-card`
- [x] **A3** Share Article icons on one row. Rows 21 (blog post), 23 (news post).
      → `single.php`, `.post-share`
- [x] **A4** CTA panel side padding too big. Rows 11 (product CTA), 19 (Our Services CTA).
      → `.cta-panel`
- [x] **A5** CTA button text colour unreadable ("Ready to get started?"). Rows 31, 35, 59, 64, 69.
      → `.dynamiq-cta a` / `.cta-panel .btn`
- [x] **A6** Search results: 9 posts per page so no orphan row. Row 81.

## B. Our Products (`/products/`)

- [x] **B1** "Make your business Run Easier with SAP Business One 10.0" — section too empty. Row 1.
- [x] **B2** "SAP Business One" — right column empty. Row 2.
- [x] **B3** Product-list section header — bleeds into card section; no left padding on text. Row 3.

## C. SAP Business One (`/products/sap-business-one-philippines/`)

- [x] **C1** "Why Choose DynamIQ as your SAP Premier Partner?" — trust signals need icons and a
      trust-signal design, not a plain list. Row 4.
- [x] **C2** "Contact Us Today!" — missing contact form. Row 5.

## D. Product page template (screenshots filed under IQ Tax Module)

- [x] **D1** Copy under H2s must align with the placement and length of the heading above —
      not just left-aligned. Rows 6-10 (5 sections).
- [x] **D2** H3 subsections must be cards (as in the previous design), not listed out. Rows 6-10.
- [x] **D3** Closing CTA section side padding too big. Row 11. (same as A4)

## E. Product pages awaiting approved copy

- [?] **E1** Needs the *approved product page additional copies* applied. Rows 12-18:
      IQ Barcoding, IQ Link, IQ Real Estate Management, IQ Ai for SAP B1, IQ Desk,
      IQ Ecom Platform, IQ Portal.
      **Blocked.** The copy is in neither the repo nor the live site: the h1-h3 outline of
      each of the seven local pages diffs clean against `dynamiqes.com/products/<slug>/`, so
      the staging pages already carry everything the live pages do. This needs the approved
      copy document from the IQ team before anything can be applied.

## F. Landing pages — SEO/SEM (the big one)

Pages: it-solutions-company-philippines, bir-cas-provider-philippines,
sap-business-one-provider-philippines, accounting-system-philippines,
erp-solutions-philippines, barcode-inventory-system-philippines, sap-software-philippines,
bir-cas-philippines, promo/bir-cas-solution, promo/accounting-inventory-system, promo/erp-system.

- [x] **F1** "This has no proper design" — landing pages need a real designed template covering
      every content section, not long-form article body. Rows 25, 29, 32, 37, 41, 45, 49, 53, 57, 62, 67.
- [x] **F2** Missing mid-page contact form / CTA section. Rows 27, 30, 33, 39, 43, 47, 51, 55.
- [x] **F3** Original SEO/SEM landing CTA heading + copy must be retained (the two CTA links —
      free business analysis + phone — may stay). Rows 40, 44, 48, 52, 56, 61, 66, 71.
- [x] **F4** Copy missing in-text internal links. Rows 26, 38, 42, 46, 50, 54.
- [x] **F5** "SAP Business One - Gold Partner" → "SAP Business One Premier Partner". Rows 34, 36.
- [x] **F6** Fix run-together words in "Why Choose DynamIQ as Your IT Solutions Provider…". Row 28.
- [x] **F7** Promo pages: missing CTA buttons in the H1 section. Rows 58, 63, 68.
- [x] **F8** Promo pages: missing client logos. Rows 60, 65, 70.

## G. Utility pages

- [x] **G1** Application Form — not formatted properly, missing contact form. Row 72.
- [x] **G2** Application Form — remove the bottom CTA/contact band. Row 73.
- [x] **G3** Thank You (Ad) — not formatted/designed properly. Row 74.
- [x] **G4** Thank You (Ad) — remove the bottom CTA band. Row 75.
- [x] **G5** Thank You (Accounting) — not formatted/designed properly. Row 76.
- [x] **G6** Thank You (Accounting) — remove the bottom CTA band. Row 77.
- [x] **G7** 404 — fix padding between the two buttons. Row 78.
- [x] **G8** 404 — centre-align all elements. Row 79.

## H. Card icons and client logos (raised by the client, 2026-09-10)

Not a row on the review sheet — the client spotted that art the live pages carry was absent
from the rebuild ("theres no logos/icons in the new build"). It was a systematic gap: every H3
card on the live product and landing pages has a small branded icon, and the original scrape
dropped all of them. 198 icons in total.

- [x] **H1** Product pages: 41 card icons mirrored into `assets/products/icons/` and attached
      per item in `inc/product-content.php` (`'icon' => …`), rendered by `dq_product_item_icon()`.
      Where an item has no icon on live, the drawn SVG from `dq_trust_icon()` still stands in.
- [x] **H2** Product pages: the four per-section illustrations the SAP page shows beside its
      sections (`assets/products/sections/`). `feature_image` was empty for SAP, so its sections
      had no art at all — sections now take their own `'image'`.
- [x] **H3** Landing / promo pages: 157 card icons kept at import time instead of being
      stripped. The importer parks the icon on its heading as `data-icon` and
      `inc/landing-sections.php` renders it. Three separate filters had been dropping them:
      the icon-name pattern, the small-size test, and an outright `.svg` reject (the promo
      pages ship every icon as SVG).
- [x] **H4** Sections where every card has its own art and there is no separate section photo
      keep all of it (the count test in the importer: images exactly equal to cards).
- [x] **H5** Careers: the four core-value icons and the culture photo
      (`assets/pages/career/`), plus the live page's own "Join Us" photo in place of the
      borrowed SAP product shot.
- [x] **H6** SEO/SEM logo bands use the campaign pages' own wider logo set
      (`assets/trust/sem/`, 11 logos) rather than the home marquee's nine.

Residual, deliberately or knowingly left:

- The **"SAP Silver Partner" seal** in the two provider pages' heroes is *not* restored: item F5
  has us renaming the tier to "Premier Partner" throughout, so re-adding a Silver badge would
  contradict the client's own instruction. Needs a decision, and a Premier-tier asset.
- `trusted-brand-seal.png` (both provider heroes) and the BIR seal on
  `/bir-cas-provider-philippines/` are hero trust seals the importer does not capture.
- Three single images where the importer's section-photo-vs-card-art count is off by one:
  `erp-solutions-image-3` on `/erp-solutions-philippines/`, and `barcode-and-qr-icon` plus
  `barcode-inventory-system-image-5` on `/barcode-inventory-system-philippines/`.
- `implementation-website` / `implementation-mobile-and-tablet` on `/our-services/`.
- Product hero and overview art differs from live by design (our own mockups), as do the
  testimonial logos (`.webp` renditions of the same brands).

## I. One-screen compositions (raised by the client, 2026-09-10)

"Use 100dvh for better viewing per chunk of information per section so that all information is
viewed in just one view", plus a pass over smaller viewports. This extends the composition rule
the homepage and Our Services already follow to everything built in this round: on desktop
(>=901px) each band takes the viewport left under the sticky nav, `min-height` never `height`,
content grid-centred, and the type and supporting art are height-aware so the content actually
fits rather than merely being given a taller box. Narrow layouts keep their natural height.

- [x] **I1** A stale hardcoded chrome height was making *every* one-screen section on the
      product pages ~26px too tall: `min-height:calc(100dvh - 63px)` while the nav measures
      89px. All the hardcoded numbers now use the measured `--nav-h` / `--hf-h`, including
      `--products-nav-h` on the listing. This also fixed Our Services and the products page,
      which were 25px over.
- [x] **I2** Landing / promo sections (`.lp-*`), the landing hero, and the in-page CTA panels.
- [x] **I3** Product detail: the cards row moved out of the narrow copy column to span the
      section under the head + illustration row — eight benefit cards in a 2-up column ran to
      2.5 screens. Four wide columns, icon inline with the title.
- [x] **I4** Careers: the culture band (two heads + photo + four values) went from 2.0 screens
      to 1.3; the openings grid is four columns of height-aware cards.
- [x] **I5** Application Form, Thank You pages, and the shared closing CTA / contact bands on
      sub-pages.
- [x] **I6** Short laptops (1280x720, 1366x768): a `max-height:820px` refinement gives height
      back through padding, gaps and art only — the type is already at the 13px floor from the
      design guide and is not reduced below it.
- [x] **I7** Smaller viewports: media capped against the viewport below 901px and again below
      600px so a portrait illustration cannot take the screen before its copy; 13px copy floor
      and 44px touch targets on the new components; the CTA panel's secondary link became a
      ghost button so two solid orange buttons no longer stack on mobile; full-width submit.
      No horizontal scroll at 1440, 1366, 1280, 1024, 820, 768, 390 or 360.

- [x] **I8** Follow-ups the client spotted after the first pass:
      - A stale `.product-detail-page .features.has-showcase .feature-grid` rule capped those
        sections at two columns. It made sense when the cards sat inside the narrow copy
        column, but they now span the section, so four cards ran as two rows in half the
        width. Removed (it outranked the newer four-column rule on specificity).
      - `.lp-checklist` was a fixed two columns at ~618px each, putting six short phrases on
        three rows; it is `auto-fit` now.
      - The product hero image carried a `box-shadow`, which paints a rectangular panel behind
        a transparent cut-out — the "border" around the SAP monitor. It is a `drop-shadow`
        filter instead, which follows the silhouette on a cut-out and is indistinguishable
        from a box-shadow on the opaque screenshots. The corner radius stays for those.
      A grid audit across 1440/820/768/390 confirmed every card, signal, checklist and form
      grid now steps 4 -> 2 -> 1 with no other stale caps.

Where a section still exceeds one screen it is because the copy genuinely does not fit — the
worst cases are the 7-9 card sections at ~1.3 screens on a 900px-tall window. The rule is
`min-height`, so those grow rather than clip, which is what the design guide requires.

**Not changed: the homepage.** Its sections measure 0.3-1.5 screens and it is the locked
benchmark every other page is judged against, so it was left alone. Say the word if you want
the same treatment applied there.

---

## Re-import required on staging

The importer changes (F4 in-text links, F5 the partner tier, F6 word spacing, and the icon
size probe) rewrite what is *stored* for each landing page, so they only take effect after
**Appearance → DynamIQ Setup → Import landing pages** is run again on staging. The section
design (F1/F2/F3/F7/F8) is template-side and applies immediately.

---

# SEO Hacker pre-live check — "0928 Pre Live Checks" (2026-09-28)

Source: `DynamIQ - Website Redesign.xlsx`, sheet **0928 Pre Live Checks**. 81 rows marked
**Rejected**; every row carries an IQ remark in column F. All fixed in **Theme 3.0**.

## J. Headings and tags

- [x] **J1** About — DRIVEN / DEPENDABLE / DEDICATED / DATA SECURITY are H3, not H4. Rows 3–6.
      → `page-about-us.php`, `.about-page .about-values .feature-group h3`
- [x] **J2** "Ready To Get Started?" CTA kicker is a P, not an H3 — landing pages and product pages.
      Rows 8, 17, 39, 84, 141, 210, 229. → `inc/landing-sections.php`, `single-dq_product.php`
- [x] **J3** /blogs/ card titles are H2. Row 42. → `index.php`
- [x] **J4** /news-events/ card titles are H3. Row 167. → `page-news-events.php`
- [x] **J5** /products/ product names are H2. Rows 173–183. → `archive-dq_product.php`, `.products-page .product-copy h2`
- [x] **J6** /our-services/ hidden "Consult with our SAP Business One Specialist today!" H3 removed. Row 169.

## K. Title tags and meta descriptions

- [x] **K1** Brand spelling: every title tag / description that read "DynamIQes" or "Dynamiqes" now reads
      "DynamIQ" (site name in the DB is "DynamIQes"; Yoast `%%sitename%%`). Rows 16, 36–38, 44–45, 50, 54, 83,
      132, 160–161, 165–166, 168, 170, 184–186, 201–203, 228, 234–238, 242–245, 247.
      → `dq_seo_brand()` in `inc/seo.php`, hooked on `wpseo_title` / `wpseo_metadesc` (+ OG/Twitter) and the theme's own tags
- [x] **K2** Testimonials without a Yoast title get "Client Testimonials - <client> - DynamIQ". Rows 239, 249.
- [x] **K3** /barcode-inventory-system/ title tag "How Barcoding Improves Inventory Tracking and Business Operations"
      (post has no Yoast title; a Yoast title typed later wins). Row 18. → `dq_seo_title_overrides()`

## L. Listings and contact

- [x] **L1** /blogs/ — featured story + 9 cards on page 1, 9 cards on every later page (was 10). Row 40.
      → `inc/setup.php` (offset + `found_posts`), `index.php`
- [x] **L2** Blog card category chip links to the category page. Row 41. → `template-parts/post-card.php`
- [x] **L3** Address links to the Google Maps listing (contact block + footer); URL in Customizer → Contact details → Map link.
      Rows 43, 77. → `template-parts/contact-section.php`, `footer.php`, `dq_contact_info()['map']`

## M. Product copy (`inc/product-content.php`)

- [x] **M1** IQ Barcode — 2nd "Technical Specifications" → "Feature List". Row 187.
- [x] **M2** IQ Link — 2nd "Features" → "Named Compatible Platforms"; 2nd "Generic REST API Compatibility" →
      "Implementation Timeline" with the supplied copy. Rows 188–190.
- [x] **M3** IQ REM — "Implementation" → "Payment Plan"; new copy under "Unit Owner / Buyer Information List Details";
      2nd "Unit Owner / Buyer Information List" → "Payment Plan Details" with the payment-plan copy. Rows 192–195.
- [x] **M4** IQ People — 1st "Executive Dashboard and Analytics" copy completed; 2nd → "Time & Attendance". Rows 197–198.
- [x] **M5** IQ Desk — 1st "License Structure" → "Helpdesk and Workflow Features"; "Self-Service Portal" card → "SLA Management";
      2nd "License Structure" → "Knowledge Base and Self-Service Portal"; closing copy under AI Usage, Deployment Model and
      Reporting and Analytics not bold (`'closing_style' => 'plain'`). Rows 204–209.

Product sections live in code (not post meta), so M1–M5 apply as soon as the theme is deployed; no re-import needed.

---

# SEO Hacker pre-live check — "0929 Pre Live Checks" (2026-09-29)

Source: `DynamIQ - Website Redesign (2).xlsx`, sheet **0929 Pre Live Checks**. 22 rows, all **Rejected**,
follow-ups to the 0928 round. All fixed in **Theme 3.1**; IQ remarks in column F.

## N. Follow-ups

- [x] **N1** "Ready To Get Started?" kicker not centred on the landing / product CTA form. Rows 2–8.
      The `<p>` (since 3.0) was picking up the `.lp-cta / .product-cta .cta-panel p` paragraph rules.
      → `.cta-section .cta-panel p.cta-kicker` at the end of `assets/css/main.css`
- [x] **N2** Homepage "Our Products" card names are H3s. Rows 9–20. → `front-page.php` (`h3.iq-title`), `.iq-title{margin:0}`
- [x] **N3** /bir-cas-philippines/ description still "DynamIQes." — the brand regex spared any following dot
      (meant for dynamiqes.com). Now only a real TLD is spared. Row 21. → `dq_seo_brand()`
- [x] **N4** /barcode-inventory-system/ title tag — the post has a Yoast title equal to its post title, which the
      override respected. It now also applies in that case. Row 22. → `dq_seo_title_hygiene()`
- [x] **N5** Category chips missing on /blogs/ page 2+ — 186 of 192 posts are Uncategorized in the live DB.
      New `inc/post-categories.php`: once per version, on the first admin load, files each Uncategorized post
      under one of the eight existing blog categories by title keywords (catch-all Business Growth & Industry
      Insights), sets `_dq_auto_category`, shows a summary notice. Editor-filed posts are never touched. Row 23.
      **IQ team: review the automatic filing under Posts.**
- [x] **N6** (3.1.1) The brand is **DynamIQ**, never "DynamIQes". The site name on staging is already
      "DynamIQ Enterprise Solution"; the wrong spelling came from Yoast titles typed per page and from titles the
      theme seeded (products, careers, Contact Us, Book a Free Demo). Seeded titles corrected at source, stored
      `_dq_seo_title` / `_dq_seo_description` corrected in place on the version refresh, and Yoast's schema graph
      (WebPage name etc.) now goes through `dq_seo_brand()` too. → `inc/seo.php`, `inc/live-urls.php`, seeders
- [x] **N7** (3.1.2) Without Yoast (local, or any site without an SEO plugin) the title tag still said
      "DynamIQes": core skips the `document_title` filter when `pre_get_document_title` returns a title, so the
      hygiene now runs inside that filter. Stored `_dq_seo_title` / `_dq_seo_description` are normalised on write
      (`update_post_metadata`) and once per version for existing rows. → `inc/seo.php`
- [x] **N8** (3.1.3) Pages without a Yoast title of their own fell back to Yoast's template and ended in
      " - DynamIQ Enterprise Solution" (accounting-system, it-solutions-company, contact-us, career, application-form,
      thank-you-ad, thank-you-accounting); SEO Hacker wants " - DynamIQ". `dq_seo_title_brand()` shortens the company
      name to the brand in title tags (and page/article names in the schema graph); descriptions and the Organization
      schema keep the legal name. → `inc/seo.php`
- [x] **N9** (3.1.4) Home page title tag and meta description "missing": the front page is a theme-made page with
      no Yoast title/description, so Yoast printed "<site name> - Home" and no description. Yoast now takes the
      Customizer values (defaults set to SEO Hacker's copy: "SAP System Services Philippines | SAP Premier Partner |
      DynamIQ" / "Get expert SAP system services in the Philippines. Our team helps with implementation and
      optimization for improved efficiency and growth.") unless the page is given its own in Yoast. → `inc/seo.php`,
      `inc/customizer.php`
