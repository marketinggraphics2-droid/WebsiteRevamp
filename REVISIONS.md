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

- [ ] **B1** "Make your business Run Easier with SAP Business One 10.0" — section too empty. Row 1.
- [ ] **B2** "SAP Business One" — right column empty. Row 2.
- [ ] **B3** "The IQ Suite" — header bleeds into card section; no left padding on text. Row 3.

## C. SAP Business One (`/products/sap-business-one-philippines/`)

- [ ] **C1** "Why Choose DynamIQ as your SAP Premier Partner?" — trust signals need icons and a
      trust-signal design, not a plain list. Row 4.
- [ ] **C2** "Contact Us Today!" — missing contact form. Row 5.

## D. Product page template (screenshots filed under IQ Tax Module)

- [ ] **D1** Copy under H2s must align with the placement and length of the heading above —
      not just left-aligned. Rows 6-10 (5 sections).
- [ ] **D2** H3 subsections must be cards (as in the previous design), not listed out. Rows 6-10.
- [ ] **D3** Closing CTA section side padding too big. Row 11. (same as A4)

## E. Product pages awaiting approved copy

- [?] **E1** Needs the *approved product page additional copies* applied. Rows 12-18:
      IQ Barcoding, IQ Link, IQ Real Estate Management, IQ Ai for SAP B1, IQ Desk,
      IQ Ecom Platform, IQ Portal. **Blocked: the approved copy document is not in the repo.**

## F. Landing pages — SEO/SEM (the big one)

Pages: it-solutions-company-philippines, bir-cas-provider-philippines,
sap-business-one-provider-philippines, accounting-system-philippines,
erp-solutions-philippines, barcode-inventory-system-philippines, sap-software-philippines,
bir-cas-philippines, promo/bir-cas-solution, promo/accounting-inventory-system, promo/erp-system.

- [ ] **F1** "This has no proper design" — landing pages need a real designed template covering
      every content section, not long-form article body. Rows 25, 29, 32, 37, 41, 45, 49, 53, 57, 62, 67.
- [ ] **F2** Missing mid-page contact form / CTA section. Rows 27, 30, 33, 39, 43, 47, 51, 55.
- [ ] **F3** Original SEO/SEM landing CTA heading + copy must be retained (the two CTA links —
      free business analysis + phone — may stay). Rows 40, 44, 48, 52, 56, 61, 66, 71.
- [ ] **F4** Copy missing in-text internal links. Rows 26, 38, 42, 46, 50, 54.
- [ ] **F5** "SAP Business One - Gold Partner" → "SAP Business One Premier Partner". Rows 34, 36.
- [ ] **F6** Fix run-together words in "Why Choose DynamIQ as Your IT Solutions Provider…". Row 28.
- [ ] **F7** Promo pages: missing CTA buttons in the H1 section. Rows 58, 63, 68.
- [ ] **F8** Promo pages: missing client logos. Rows 60, 65, 70.

## G. Utility pages

- [ ] **G1** Application Form — not formatted properly, missing contact form. Row 72.
- [ ] **G2** Application Form — remove the bottom CTA/contact band. Row 73.
- [ ] **G3** Thank You (Ad) — not formatted/designed properly. Row 74.
- [ ] **G4** Thank You (Ad) — remove the bottom CTA band. Row 75.
- [ ] **G5** Thank You (Accounting) — not formatted/designed properly. Row 76.
- [ ] **G6** Thank You (Accounting) — remove the bottom CTA band. Row 77.
- [x] **G7** 404 — fix padding between the two buttons. Row 78.
- [x] **G8** 404 — centre-align all elements. Row 79.
