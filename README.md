# DynamIQ Enterprise Solution — WordPress theme

A 1:1 WordPress rebuild of https://dynamiq-site.vercel.app/ (home, products listing and the nine product pages), with the UI unified and modernised where the static site had drifted, and SEO built in.

```
wp-content/themes/dynamiqes/      ← the theme (copy this folder into your WordPress install)
dist/dynamiqes-theme-full.zip      ← same theme zipped (63 MB, includes both videos)
dist/dynamiqes-theme-lite.zip      ← 24 MB, without the 40 MB contact-section video (upload-friendly)
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

## URL map (matches the live site)

| Page | URL |
|---|---|
| Home | `/` |
| Products listing | `/products/` |
| SAP Business One | `/products/sap-business-one/` |
| IQ Portal / Tax / Barcode / Link / REM / Ai / Desk / Ecom | `/products/dynamiq-portal/` … `/products/dynamiq-ecom/` |
| Blogs (WordPress posts index) | `/blog/` |
| News & Events | your news post type archive if the site has one, otherwise `/news-events/` (posts in the categories set under Customize → Content sources, default Events / Community / News) |
| Blogs dropdown (SEO landing pages) | `/accounting-system-philippines/`, `/erp-solutions-philippines/`, `/it-solutions-company-philippines/`, `/sap-software-philippines/`, `/barcode-inventory-system-philippines/`, `/bir-cas-philippines/` — linked automatically when those pages exist (they do on dynamiqes.com); otherwise the items fall back to the blog / product page |
| Sitemap | `/wp-sitemap.xml` (WordPress core, products included) |

Home-page sections have stable anchors used by the menu: `#products`, `#news-events`, `#about`, `#services`, `#testimonials`, `#contact`.

## Where to edit content

| What | Where in WP Admin |
|---|---|
| Product hero text, screenshots, overview, feature groups, FAQs | **Products → (product) → Product details** box. Image fields accept a Media Library URL (use **Select**) or a theme path like `assets/products/main/portal.png`. |
| Product page order / menu label | Product details → *Menu label*; order via **Page Attributes → Order** |
| Testimonials (quote, logo, role, "Read … story" link) | **Testimonials** |
| News cards + News & Events page | **Posts** (category = the tag on the card; set a Featured Image) |
| Hero headline/lede/video, top bar, trust line, badges, footer credit | **Appearance → Customize → DynamIQ Theme** |
| Contact details, notification e-mail, socials | **Appearance → Customize → DynamIQ Theme → Contact / Social** |
| Menus and footer link lists | **Appearance → Menus** (locations: Primary, Footer: Quick Links, Footer: Explore) |
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
| News photos | `assets/news/*.jpg` | Post → Featured Image |
| Service step photos | `assets/services/*.jpg` | `dq_services` filter, or hide with Customize → Home sections |
| SAP logo + Premier Partner badge | `assets/brand/` | Customize → Home sections |
| Hero video + poster | `assets/video/hero-banner.mp4`, `hero-poster.jpg` | Customize → Home hero |
| Contact background video | `assets/video/contact-gradient.mp4` (40 MB, consider compressing to ~5 MB) | replace the file |

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

First-time setup on another machine (already done here): unzip the three downloads in `.local-wp/dl/` as `php/`, `www/`, and `www/wp-content/plugins/sqlite-database-integration/`, copy the plugin's `db.copy` to `www/wp-content/db.php`, then run `php bootstrap.php` and `php seed.php` from `.local-wp/`.
