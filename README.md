# DynamIQ Enterprise Solution — WordPress theme

A 1:1 WordPress rebuild of https://dynamiq-site.vercel.app/ (home, products listing and the nine product pages), with the UI unified and modernised where the static site had drifted, and SEO built in.

```
wp-content/themes/dynamiqes/      ← the theme (copy this folder into your WordPress install)
dist/dynamiqes-theme.zip           ← 4.5 MB: code + small assets — upload through Appearance → Themes → Upload
dist/dynamiqes-media-images.zip    ← 15 MB: assets/products, assets/news, testimonial photos — extract over wp-content/themes/ (same dynamiqes/ root)
dist/dynamiqes-media-videos.zip    ← 145 MB: assets/video + assets/testimonials/video — extract over wp-content/themes/ (FTP / host file manager)
assests/                           ← your original source assets (untouched)
```

## Install

1. Copy `wp-content/themes/dynamiqes` into your site's `wp-content/themes/` (FTP, SFTP, or your host's file manager). The folder is ~62 MB because it bundles the two videos, so uploading the folder is more reliable than a zip through **Appearance → Themes → Upload**. If you must zip it, remove `assets/video/contact-gradient.mp4` first (40 MB) and set a smaller video later.
2. **Appearance → Themes → Activate "DynamIQ Enterprise Solution".**
   On activation the theme automatically:
   - creates the 9 products, 12 testimonials and 4 news posts;
   - creates the **Home** and **News & Events** pages and sets them as front page / posts page;
   - builds the **Primary Menu** (same structure as the live site) and assigns it;
   - sets the site title, tagline and `/%postname%/` permalinks.
3. Open **Appearance → DynamIQ Setup**. If anything was skipped, click **Run content import**. If `/products/` gives a 404, click **Flush permalinks**.
4. Optional but recommended: click **Import images to Media Library**. This copies the product screenshots, news photos and testimonial logos into `/wp-content/uploads/…` and sets them as Featured Images, so every image gets a normal WordPress URL that you can replace from WP Admin without touching theme files.

## URL map (matches the live site — dynamiqes.com's sitemap is the benchmark)

| Page | URL |
|---|---|
| Home | `/` |
| Products listing | `/products/` |
| SAP Business One | `/products/sap-business-one-philippines/` |
| IQ Portal / Tax / Barcode / Link / REM / Ai / Desk / Ecom | `/products/dynamiq-portal/`, `/products/dynamiq-tax-module/`, `/products/dynamiq-barcoding/`, `/products/dynamiq-iq-link/`, `/products/dynamiq-real-estate-management/`, `/products/dynamiq-ai-sap-b1/`, `/products/sap-b1-it-desk/`, `/products/sap-b1-ecom-platform/` — the live slugs. The theme's older short slugs (`/products/sap-business-one/`, `/products/dynamiq-tax/` …) 301 to them (`inc/live-urls.php`). |
| Blogs (WordPress posts index) | `/blogs/` (`/blog/` 301s there) |
| News & Events | `/news-events/` page (H1 "Latest Updates & Events: Stay Connected with DynamIQ", H2 "Featured News and Events" + H3, H2 "News and Events"); items come from the site's news post type when it has one (the live `news-events` items at `/news-event/<slug>/`), else from the news categories |
| About Us / Privacy Policy / Client Testimonials | `/about-us/`, `/privacy-policy/`, `/client-testimonials/` + `/testimonials/<client>/` - slug templates that reproduce the live pages' heading structure and copy (`inc/page-content.php`); the testimonial single serves the live `customer_testimonial` post type |
| SEM / campaign pages | `/bir-cas-provider-philippines/`, `/sap-business-one-provider-philippines/`, `/promo/*`, `/application-form/`, `/thank-you-*` - imported by the landing-page importer like the six Blogs-dropdown pages |
| Blogs dropdown (SEO landing pages) | `/accounting-system-philippines/`, `/erp-solutions-philippines/`, `/it-solutions-company-philippines/`, `/sap-software-philippines/`, `/barcode-inventory-system-philippines/`, `/bir-cas-philippines/` — linked automatically when those pages exist (they do on dynamiqes.com); otherwise the items fall back to the blog / product page |
| Our Services | `/our-services/` (dedicated page, as on the live site) |
| Contact Us | `/contact-us/` (nav target, as on the live site; `page-contact-us.php` — H1 + intro, then the shared enquiry form under H2 "Get in Touch"; the home page keeps its own `#contact` section) |
| Book a FREE DEMO | `/book-free-demo/` |
| Careers | `/career/` (nav target, as on the live site; `page-career.php` lists the openings) and `/careers/<slug>/` per job opening (`dq_career` post type, same slugs as dynamiqes.com). Created automatically on the first admin load; descriptions are pulled from the live pages when reachable. |
| Sitemap | `/wp-sitemap.xml` (WordPress core, products included). With Yoast active, Yoast's `sitemap_index.xml` is used and matches the live index: when the theme runs on a copy of the live database, the copied product *pages* keep their `page-sitemap.xml` entries and the `dq_product` posts rendering at the same URLs are left out, so no URL appears twice. Those products also inherit the shadowed page's Yoast title / meta description. |

Home-page sections have stable anchors used by the menu: `#products`, `#news-events`, `#about`, `#services`, `#testimonials`, `#contact`.

## Live structure parity (SEO)

dynamiqes.com is the SEO benchmark. Every page except the home page keeps the live page's HTML outline (H1-H6 order and text), URL, title tag and meta description; only the presentation is the new design system. What that means in the code:

- **Products** render the live copy and section order from `inc/product-content.php` (generated from the live pages by `scrape_products.py`; the catalogue in `inc/product-data.php` stays as the fallback for images, cards and menu labels). Each product has ordered `sections` (overview, H2 groups with H3 items, in-page CTAs, FAQ) editable in Products -> Product details -> *Page sections*. Live pages without a FAQ or a features block (IQ Barcode, IQ Link, IQ REM, IQ Ai ...) render without one, as on live.
- **Headings the design shows but the live page does not have** are plain elements styled like headings: the CTA band title (`.cta-title`), card titles on listings (`.card-title`), "The IQ Suite" line on /products/, the contact block's city, "Ready to apply?" on /career/. Where the live page *does* have the heading it is passed in: blog posts end with H2 "Have a Question or Need a Demo?" then H4 "You May Also Like"; news items with H4 "Share Article:" and H2 "Other News and Events"; /products/ and /our-services/ close with H2 "We'd Like To Hear From You" + H3 "Get in Touch".
- **Landing-page importer** (`inc/landing-import.php`) keeps the live in-content CTA block (H3 "Ready To Get Started?" + heading), FAQ questions as H3 when the live page marks them up so, repeated headings across sections, and `<br>` spacing.
- **Audit**: `scratchpad/outline_diff.py` (see git history / this README's commit) compares the H1-H4 outline of every page type on dynamiqes.com with staging or localhost:8787. Known, deliberate deltas: category/tag/author archives (the live theme renders the home page there), the duplicated job list on live /career/, and a duplicated H3 on the live barcode landing page.
- **Upgrade path**: on the next admin load after deploying, `inc/live-urls.php` renames product slugs, fixes the Blogs page, resets product fields still holding the older catalogue copy so the live copy shows, and seeds the live title tags. Also available under Appearance -> DynamIQ Setup -> *Adopt live URLs*.

## Where to edit content

| What | Where in WP Admin |
|---|---|
| Product hero text, screenshots, overview, feature groups, FAQs | **Products → (product) → Product details** box. Image fields accept a Media Library URL (use **Select**) or a theme path like `assets/products/main/portal.png`. |
| Product page order / menu label | Product details → *Menu label*; order via **Page Attributes → Order** |
| Testimonials (quote, logo, role, "Read … story" link, client video + poster) | **Testimonials** (on the live install: **Customer Testimonials** → "Client video" box) |
| News cards + News & Events page | **Posts** (category = the tag on the card; set a Featured Image) |
| Hero headline/lede/video, top bar, trust line, badges, footer credit | **Appearance → Customize → DynamIQ Theme** |
| Contact details, notification e-mail, socials | **Appearance → Customize → DynamIQ Theme → Contact / Social** |
| Menus and footer link lists | **Appearance → Menus** (locations: Primary, Footer: Quick Links, Footer: Explore) |
| Footer video wall clips | **Media → Add New** (upload the video; optional Featured Image = poster). The strip plays a small 240p preview and only loads the full clip when a tile is clicked — see *Video wall previews* below. |
| Job openings (Careers page cards + `/careers/<slug>/` pages) | **Careers** — title, Excerpt = the short "Job Description" on the card, editor = Job Responsibilities / Qualifications, Location + Employment type in the *Job details* box. Unpublish to remove from the page. |
| Contact form submissions | **Inquiries** (also e-mailed to the address set in Customizer) |
| Per-page SEO title / description / share image / noindex | **SEO** box on pages, posts and products |
| Search Console / Bing verification, GA4/GTM snippet | **Customize → DynamIQ Theme → SEO defaults** |

## Image URLs (for SEO and later updates)

All theme images resolve to absolute URLs under the theme folder, e.g.

```
https://YOUR-DOMAIN/wp-content/themes/dynamiqes/assets/logos/DynamIQ_Logo_blk.svg
https://YOUR-DOMAIN/wp-content/themes/dynamiqes/assets/products/main/portal.png
https://YOUR-DOMAIN/wp-content/themes/dynamiqes/assets/trust/MacroAsia-Corporation-Logo-1.webp
```

After **Import images to Media Library**, product heroes, news photos and testimonial logos move to `https://YOUR-DOMAIN/wp-content/uploads/YYYY/MM/…` and the templates use those automatically (featured image wins over the theme path). Every image has alt text; Open Graph and JSON-LD use the same absolute URLs.

| Asset group | Theme path | Replace via |
|---|---|---|
| Brand logos | `assets/logos/` | Customize → Brand & header (nav + footer logo) |
| Product logos (dark/white) | `assets/products/iq-*.svg`, `iq-*-wht.svg` | Product details → Product logo fields |
| Product hero screenshots | `assets/products/main/*.png` | Product → Featured Image (or Hero screenshot field) |
| Product home-card monitor art | `assets/products/official/*.png` | Product details → Home card artwork |
| Product hover photos / hero backgrounds | `assets/products/photos/*.jpg` | Product details → Hero background / Home card hover photo |
| Overview + feature images | `assets/products/site-media/*` | Product details → Overview image / Features image |
| Client logos (trust marquee) | `assets/trust/*.webp` | `dq_trust_logos` filter or replace the files |
| Testimonial logos | `assets/testimonials/*`, `assets/trust/*.png` | Testimonial → Featured Image or Client logo field |
| Testimonial videos + posters | `assets/testimonials/*-thumbnail.jpg`, `assets/testimonials/video/*-720p.mp4` (Vimeo clients embed from Vimeo); defaults per client in `inc/testimonial-media.php` | Testimonial → Client video (MP4 / Vimeo / YouTube link) + Video poster |
| News photos | `assets/news/*.jpg` | Post → Featured Image |
| Service step photos | `assets/services/*.jpg` | `dq_services` filter, or hide with Customize → Home sections |
| SAP logo + Premier Partner badge | `assets/brand/` | Customize → Home sections |
| Hero video + poster | `assets/video/hero-banner.mp4`, `hero-poster.jpg` | Customize → Home hero |
| Contact background video | `assets/video/contact-gradient.mp4` (40 MB, consider compressing to ~5 MB) | replace the file |

### Video wall previews (240p)

The footer video strip never streams the full clips. For every clip `<name>.mp4` it looks for two small siblings in the same folder and plays them muted in the tiles: `<name>-240p.webm` (VP9, preferred) and `<name>-240p.mp4` (H.264 fallback for browsers without WebM). The full 1080p file is only requested when a tile is clicked and the lightbox opens. If the siblings are missing the tile falls back to the full clip, so nothing breaks — it is just slower.

Build the siblings with ffmpeg on your machine, then upload them next to the original (FTP, or the host's file manager — they do not need to be added to the Media Library):

```powershell
.\make-video-previews.ps1                    # theme assets/video + the local WP uploads
.\make-video-previews.ps1 -Path C:\clips     # any folder, recursively
```

A 3-minute 1080p demo (60–100 MB) comes out at roughly 1–2 MB per rendition.

## SEO built in (no plugin required)

- Unique `<title>` per page (custom per-post override), meta description with smart fallbacks, canonical URLs.
- Open Graph + Twitter Card tags with absolute image URLs.
- JSON-LD: `Organization` (address, phones, socials), `WebSite`, `BreadcrumbList`, `Article` on posts, and `SoftwareApplication` + `FAQPage` on every product page (the FAQ accordions become rich results).
- Robots: `max-image-preview:large`, noindex on search/attachment/date/author pages, per-post noindex toggle.
- Core XML sitemap includes products, excludes internal types and users.
- One `<h1>` per page, semantic sections, alt text everywhere, lazy loading, `fetchpriority="high"` on hero images, async image decoding, head clean-up (no generator/emoji/RSD).
- If you later install Yoast / Rank Math / AIOSEO / SEOPress, the theme steps aside automatically (keeps only the product FAQ/software schema).

## What changed vs. the static site ("modern UI" pass)

- One header, nav and footer across all pages (the static site had three slightly different implementations). Footer now has the Contact column and social icons everywhere.
- Logos are real `<img>` elements with alt text (were CSS backgrounds), swappable from the Customizer.
- Skip link, visible focus states, labelled form fields, Esc closes the mobile menu, reduced-motion respected.
- Lenis smooth-scroll is bundled locally instead of loaded from unpkg; no dependency on dynamiqes.com for any image or video.
- Working contact form (AJAX + no-JS fallback, honeypot, nonce, rate limit, e-mail + stored inquiries). Contact Form 7 can still be used if preferred.
- "About Us" now scrolls to the "Why Choose DynamIQ" section (`#about`) instead of reloading the home page.
- Service steps can show the photos from `assets/services/` (toggle in Customizer).
- Blog index, single post, generic page, search and 404 templates in the same design language (the static site had none).

## Requirements

WordPress 6.2+, PHP 7.4+. No plugins required. Recommended: a caching plugin and an SMTP plugin (e.g. WP Mail SMTP) so contact-form e-mails are delivered reliably.

## Run locally without installing anything (portable preview)

The repo carries a self-contained WordPress under `.local-wp/` (portable PHP 8.3, WordPress, SQLite database drop-in; the theme folder is junction-linked, so edits show immediately). Nothing is installed system-wide.

```powershell
.\run-local.ps1
```

Then open http://localhost:8787/ — admin at http://localhost:8787/wp-admin/ (user `admin`, password `admin`). Stop with Ctrl+C.

First-time setup on another machine: put the three downloads in `.local-wp/dl/` — `php.zip` (PHP 8.3 NTS x64 from https://windows.php.net/download/), `wordpress.zip` (https://wordpress.org/latest.zip) and `sqlite-database-integration.zip` (https://downloads.wordpress.org/plugin/sqlite-database-integration.zip) — keep `.local-wp/php/php.ini`, `router.php`, `bootstrap.php` and `seed.php`, then run `.
un-local.ps1 -Setup`. It unzips everything, writes the SQLite `db.php` drop-in, junction-links the theme, installs WordPress (admin / admin) and seeds the demo content.
