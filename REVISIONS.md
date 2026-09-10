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
- [x] **B3** "The IQ Suite" — header bleeds into card section; no left padding on text. Row 3.

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
