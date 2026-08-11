# Portfolio / References Page — Complete Implementation Plan

> **For agentic workers:** Implement task-by-task. `- [ ]` checkbox syntax for tracking.

**Goal:** Complete references system: admin upload pipeline (JPG→WEBP with EXIF cleanup, AI scan, watermark, steganography, XMP), 8-language alt text editor, image sitemap, schema, hreflang, `.htaccess` security layers.

**Architecture:** Flat-file PHP (~/projects/mtmesse). PHP pages read `data/references.json`. Upload pipeline: PHP GD for conversion/resize/watermark/steganography. cPanel-only — `.htaccess` for rewrite, hotlink protection, cache, directory listing. No Netlify.

**Tech Stack:** PHP 8.x + GD, Bootstrap 5.3, GLightbox, exiftool (optional XMP)

## Global Constraints

- PHP GD must be available (cPanel standard, `imagewebp` PHP 7.1+)
- exiftool via `exec()` — graceful fallback if missing
- WEBP only, quality 85, originals deleted after conversion
- **No cropping**: `object-fit: contain` on all grid images
- 8 language alt text fields (en, tr, de, fr, es, ru, zh, ar)
- Atomic writes: `.tmp` → `rename()` for JSON
- Session-based auth + CSRF on all POST
- cPanel-only: all rules in `.htaccess`, no Netlify config files

---

## ✅ DONE: Phase 1 — Tasks 1–6

EN + TR pages live, navbar updated, deployed.

| Task | Status | What |
|---|---|---|
| 1 | ✅ | `data/references.json` empty array |
| 2 | ✅ | Navbar: 7-language "References" link |
| 3 | ✅ | `en/references.php` — 4-col grid, GLightbox, pagination |
| 4 | ✅ | `tr/referanslar.php` — Turkish version |
| 5 | ✅ | Audit: 0 errors |
| 6 | ✅ | Deployed (GitHub + cPanel ready) |

---

## Task 7: JSON schema — all fields

**Files:** Modify `data/references.json`

**Schema per entry:**

```json
{
  "position": 1,
  "filename": "exhibition-stand-frankfurt-01.webp",
  "src": "/assets/img/portfolio/exhibition-stand-frankfurt-01.webp",
  "sector": "automotive",
  "fair": "Messe Frankfurt",
  "city": "Frankfurt",
  "country": "Germany",
  "year": "2024",
  "alt_en": "Custom wooden stand built for automotive client at Messe Frankfurt 2024",
  "alt_tr": "Almanya Messe Frankfurt 2024'te otomotiv firması için özel ahşap stand",
  "alt_de": "",
  "alt_fr": "",
  "alt_es": "",
  "alt_ru": "",
  "alt_zh": "",
  "alt_ar": "",
  "watermarked": true,
  "stegano_hash": "abc123"
}
```

- [ ] Write `data/references.json` = `[]`
- [ ] Verify: `php -r "var_dump(json_decode(file_get_contents('data/references.json')));"` → empty array
- [ ] Commit

---

## Task 8: Upload pipeline — `admin/actions/upload-reference.php`

**Full pipeline (in order):**

1. Validate upload (JPG/PNG, MIME, 5MB max)
2. **EXIF cleanup** — PHP GD `imagecreatefromjpeg()` automatically strips EXIF/GPS
3. **AI marker scan** — filename + raw binary for midjourney/dalle/synthetic markers
4. Open source image via GD
5. **Resize** to 1200px wide, proportional height
6. **SEO filename** — lowercase, hyphens, no special chars
7. **Watermark** — logo.webp bottom-right corner, 30% opacity via `imagecopymerge()`
8. Save as WEBP, quality 85 via `imagewebp()`
9. **XMP metadata** — `exec('exiftool ...')` if available, skip silently if not
10. **Steganography** — embed `© MT Messe Stand YYYY` into first row LSB via GD
11. **JSON save** — atomic write to `data/references.json`
12. **Image sitemap** — regenerate `sitemap-images.xml`
13. Redirect to editor with toast

- [ ] Create `admin/actions/upload-reference.php` with pipeline code
- [ ] Verify PHP syntax: `php -l`
- [ ] Test upload → check WEBP output, EXIF stripped, watermark visible, XMP if exiftool, JSON entry complete
- [ ] Commit

---

## Task 9: Admin editor — `admin/editor-references.php`

**Files:** Create `admin/editor-references.php`, `admin/actions/reorder-references.php`, `admin/actions/delete-reference.php`. Modify `admin/admin-layout.php`.

**Editor features:**

- Grid of all images sorted by position
- Each shows: thumbnail (150px), position input, alt text preview, sector badge
- ↑ ↓ arrows for reorder (AJAX → swap positions in JSON)
- Edit button → inline form with all 8 language alt texts + metadata
- Delete button → confirmation → removes JSON entry + deletes file + regenerates sitemap
- "Add New" → upload form: file input + alt texts + sector/fair/city/country/year/seo_name
- Sidebar: "Referanslar" link

- [ ] Create editor + reorder + delete PHP files
- [ ] Add sidebar link in `admin-layout.php`
- [ ] Test full CRUD cycle
- [ ] Commit

---

## Task 10: SEO + security layers

**Files:** Modify `.htaccess`, `en/references.php`, `tr/referanslar.php`, `robots.txt`, `llms.txt`. Create `sitemap-images.xml`.

### 10A — Image sitemap

`sitemap-images.xml` reads `data/references.json`, generates:
```xml
<url>
  <loc>https://mtmessestand.com/en/references/</loc>
  <image:image>
    <image:loc>https://mtmessestand.com/assets/img/portfolio/file.webp</image:loc>
    <image:caption>alt_en text</image:caption>
  </image:image>
</url>
```
Regenerated after every upload/delete.

### 10B — Schema on page

Both `en/references.php` and `tr/referanslar.php`:
- `ImageObject` schema for first 10 images (creditText, creator, copyrightNotice, license)
- `BreadcrumbList`: Home → References
- `CollectionPage` with `inLanguage`

### 10C — hreflang chain

In `<head>` of both pages:
```html
<link rel="alternate" hreflang="x-default" href="https://mtmessestand.com/en/references/">
<link rel="alternate" hreflang="en" href="https://mtmessestand.com/en/references/">
<link rel="alternate" hreflang="tr" href="https://mtmessestand.com/tr/referanslar/">
```

### 10D — OG image

Dynamically set first portfolio image as `og:image` via PHP.

### 10E — `.htaccess` security layers

Add to `.htaccess`:
```apache
# Hotlink protection
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^https?://(www\.)?mtmessestand\.com [NC]
RewriteCond %{HTTP_REFERER} !^https?://(www\.)?mellow-souffle-1db250\.netlify\.app [NC]
RewriteRule \.(webp|jpg|png)$ - [F]

# Cache headers for images
<FilesMatch "\.(webp|png|jpg|css|js)$">
  Header set Cache-Control "public, max-age=31536000, immutable"
</FilesMatch>

# Disable directory listing
Options -Indexes
```

### 10F — robots.txt

Add: `Sitemap: https://mtmessestand.com/sitemap-images.xml`

### 10G — llms.txt

Add references section with link to portfolio page.

### 10H — ARIA

Grid items: `role="listitem"`. Lightbox links: `aria-label="View enlarged reference image"`.

- [ ] Create sitemap generator
- [ ] Add all schema to both pages
- [ ] Add hreflang chain
- [ ] Dynamic OG image
- [ ] Update `.htaccess` with all security rules
- [ ] Update `robots.txt` and `llms.txt`
- [ ] Add ARIA attributes
- [ ] Commit

---

## Task 11: `object-fit: contain` fix

**Files:** Modify `en/references.php`, `tr/referanslar.php`

Change CSS:
```css
.portfolio-item img { object-fit: contain; }
```

- [ ] Remove `aspect-ratio: 4/3` if present, keep `object-fit: contain`
- [ ] Add light gray background to image wrapper so empty areas blend: `background: #f8f9fa;`
- [ ] Commit

---

## Task 12: Final audit + deploy

- [ ] Run `python3 scripts/audit_links.py` → 0 errors
- [ ] End-to-end test: upload JPG → verify pipeline outputs
- [ ] Test both EN + TR pages in browser (grid, lightbox, navbar active)
- [ ] Test admin CRUD (upload, reorder, edit alt texts, delete)
- [ ] Verify `.htaccess` rules work on test server
- [ ] Git push
- [ ] cPanel `git pull` (user does this)
