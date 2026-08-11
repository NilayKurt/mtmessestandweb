# Portfolio / References Page Implementation Plan

> **For agentic workers:** Implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a "References" gallery page (portfolio) with lightbox, 4-column grid, multi-language support, admin panel integration, and comprehensive SEO.

**Architecture:** Flat-file PHP project (~/projects/mtmesse). New `data/references.json` stores image entries. New admin editor (`admin/editor-references.php`) with Quill WYSIWYG for per-image descriptions. Page templates use existing `page-template.php` pattern. Portfolio pages are static HTML rendered via PHP CLI (same as about/contact).

**Tech Stack:** PHP 8.x, Bootstrap 5.3, Quill WYSIWYG, GLightbox, Pillow (Python for image processing), exiftool (XMP metadata)

## Global Constraints

- All file changes within ~/projects/mtmesse
- Follow existing patterns: `page-template.php` for page shell, flat-file JSON for data
- Navbar: single source (`templates/navbar.php`)
- Footer: single source (`templates/footer.php`)
- Images: WEBP only, quality 85, XMP metadata via exiftool
- Multi-language: EN + TR first, others as "coming soon"
- No passive links (`href="#"`) — create pages or mark "soon"
- Asset depth correct per page location (depth 1 → `../assets/`, depth 2 → `../../assets/`)
- `lp` (leading slash prefix) for absolute paths: `/assets/img/portfolio/`

---

### Task 1: Create `data/references.json` data structure

**Files:**
- Create: `data/references.json`

**Interfaces:**
- Produces: JSON array of `{slug, images: [{src, alt_en, alt_tr, ...}], sector, order}`

- [ ] **Step 1: Create initial JSON file**

```json
[
  {
    "slug": "placeholder",
    "order": 0,
    "sector": "",
    "images": []
  }
]
```

Write file: `data/references.json`

- [ ] **Step 2: Verify JSON is valid**

Run: `php -r "var_dump(json_decode(file_get_contents('data/references.json'), true));"`
Expected: Array output, no errors

- [ ] **Step 3: Commit**

```bash
git add data/references.json
git commit -m "feat: add references.json data structure"
```

---

### Task 2: Add "References" link to navbar template

**Files:**
- Modify: `templates/navbar.php`

**Interfaces:**
- Produces: Nav link "References" / "Referanslar" between Blog and Contact

- [ ] **Step 1: Open navbar.php and find the nav links section**

Read `templates/navbar.php` lines 103-108 (the nav items array). The current order is:
Home → About → Blog → Contact → Language dropdown.

- [ ] **Step 2: Add References link after Blog, before Contact**

In `templates/navbar.php`, find the label arrays `$L` for each language (they map to [Home, About, Blog, Contact]). Since the 7-language labels are inline in the function, add a 5th label for each language AFTER Blog:

```
EN: Home, About, Blog, References, Contact
TR: Ana Sayfa, Hakkımızda, Blog, Referanslar, İletişim
DE: Startseite, Über uns, Blog, Referenzen, Kontakt
FR: Accueil, À propos, Blog, Références, Contact
ES: Inicio, Nosotros, Blog, Referencias, Contacto
AR: الرئيسية, من نحن, المدونة, مراجع, اتصل بنا
ZH: 首页, 关于我们, 博客, 参考, 联系我们
```

Update the `$active` detection: add `'references'` to the possible page types.

- [ ] **Step 3: Add the `<li>` element in the nav**

After the Blog `<li>` and before the Contact `<li>`, insert:

```php
<li class="nav-item"><a class="nav-link' . $a('references') . '" href="' . $navBase . 'references.html">' . $L[3] . '</a></li>
```

Contact label index shifts from 3 to 4:
```php
<li class="nav-item"><a class="nav-link' . $a('contact') . '" href="' . $navBase . 'contact.html">' . $L[4] . '</a></li>
```

- [ ] **Step 4: Verify navbar renders correctly**

Run: `php -r "include 'templates/navbar.php'; echo substr(render_navbar('en','references'),0,600);"`
Expected: Contains "References" with `class="nav-link active"`, Contact shows `class="nav-link"`

- [ ] **Step 5: Commit**

```bash
git add templates/navbar.php
git commit -m "feat: add References link to navbar (7 languages)"
```

---

### Task 3: Create EN references page

**Files:**
- Create: `en/references.html`

**Interfaces:**
- Consumes: `templates/navbar.php`, `templates/footer.php`
- Produces: Full HTML page with 4-col grid, GLightbox, placeholder images

- [ ] **Step 1: Create the page using page-template pattern**

The page uses the same structure as `en/about.html` but with a 4-column image grid. Key points:

- `<html lang="en">`
- Head: title "References | MT Messe Stand", meta description, canonical `/en/references/`
- Navbar: `render_navbar('en', 'references')`
- Main: `<main style="padding-top:65px">` → `<section class="section">` → grid
- Footer: `render_footer('en', 1, 'references')`

- [ ] **Step 2: Build the image grid (4 cols × N rows)**

```html
<div class="container">
  <h1 class="mb-4">References</h1>
  <p class="lead mb-5">Take a look at our work across exhibitions worldwide.</p>
  
  <div class="row g-4" id="references-grid">
    <!-- Cards injected here -->
  </div>
</div>
```

Each card:
```html
<div class="col-lg-3 col-md-4 col-6">
  <a href="assets/img/portfolio/stand-01.webp" class="glightbox portfolio-item d-block" data-gallery="references" data-glightbox="width: 80vw;">
    <img src="assets/img/portfolio/stand-01.webp" 
         alt="Custom exhibition stand built for automotive client at Messe Frankfurt 2024" 
         class="img-fluid rounded-3 shadow-sm"
         width="400" height="300"
         loading="lazy">
  </a>
</div>
```

- [ ] **Step 3: Add CSS for hover effect**

Add inline `<style>` block before `</head>`:
```css
.portfolio-item { overflow: hidden; border-radius: 0.5rem; transition: transform 0.3s ease; }
.portfolio-item:hover { transform: scale(1.03); }
.portfolio-item img { transition: transform 0.3s ease; display: block; }
```

- [ ] **Step 4: Verify page structure**

Run: `php -l en/references.html`
Expected: No syntax errors

- [ ] **Step 5: Commit**

```bash
git add en/references.html
git commit -m "feat: add EN references page with 4-col grid + lightbox"
```

---

### Task 4: Create TR references page

**Files:**
- Create: `tr/referanslar.html`

- [ ] **Step 1: Copy EN page, translate to TR**

Key translations:
- Title: "Referanslar | MT Messe Stand"
- H1: "Referanslarımız"
- Lead: "Dünya çapında fuarlarda yaptığımız işleri inceleyin."
- Alt texts: Turkish versions of EN alt texts

- [ ] **Step 2: Update language attributes**

- `<html lang="tr">`
- Canonical: `/tr/referanslar/`
- hreflang: `tr` variant
- Footer: `render_footer('tr', 1, 'references')`

- [ ] **Step 3: Verify no English text remains**

Run: `grep -cP '[A-Za-z]{4,}' tr/referanslar.html | head -5`
Expected: Only code/URLs in English, no content text

- [ ] **Step 4: Commit**

```bash
git add tr/referanslar.html
git commit -m "feat: add TR referanslar sayfası"
```

---

### Task 5: Run audit and test locally

**Files:**
- None created, verification only

- [ ] **Step 1: Run internal link audit**

```bash
python3 scripts/audit_links.py
```
Expected: 0 errors, references page links resolve

- [ ] **Step 2: Start PHP server and test in browser**

```bash
php -S 0.0.0.0:8765 &
```
Open: `http://WSL_IP:8765/en/references.html`
Verify: Navbar shows "References" active, grid loads, lightbox works on click

- [ ] **Step 3: Test TR page**

Open: `http://WSL_IP:8765/tr/referanslar.html`
Verify: Navbar shows "Referanslar" active, all text in Turkish

- [ ] **Step 4: Test language switching from references pages**

From EN references page, switch to TR → should go to `/tr/referanslar.html`
From TR page, switch to EN → should go to `/en/references.html`

- [ ] **Step 5: Commit**

```bash
git commit -m "chore: audit pass after references pages"
```

---

### Task 6: Deploy

- [ ] **Step 1: Git push**

```bash
git push origin master
```

- [ ] **Step 2: Netlify deploy**

```bash
netlify deploy --dir=. --message "portfolio: references page EN+TR"
```

- [ ] **Step 3: Publish to production**

If `--prod` fails, use API restore:
```bash
DEPLOY_ID=$(netlify deploy --dir=. --message "portfolio" 2>&1 | grep -oP '[a-f0-9]{20,}' | head -1)
curl -X POST "https://api.netlify.com/api/v1/sites/SITE_ID/deploys/$DEPLOY_ID/restore" \
  -H "Authorization: Bearer TOKEN"
```

- [ ] **Step 4: Verify production**

```bash
curl -sI "https://mellow-souffle-1db250.netlify.app/en/references.html" | head -3
```
Expected: HTTP 200

---

## Post-Phase Tasks (admin panel + SEO)

### Task 7: Admin panel — references editor (2 sub-tasks)

**7A — Editor page:**
- `admin/editor-references.php` — grid view of all images with position numbers
- Each image shows: thumbnail, position input, ↑ ↓ arrow buttons, delete button
- "Add New" button → upload form (image + per-language alt texts)
- Position change: update number → AJAX save → others auto-shift
- `admin/actions/save-reference.php` — add/edit single reference
- `admin/actions/reorder-references.php` — swap positions, auto-shift others
- `admin/actions/delete-reference.php` — remove image + JSON cleanup
- Sidebar: "Referanslar" link (session lang-aware)

**7B — Page rebuild:**
- `build.php` update: inject references cards into references pages (like blog cards)
- Or simpler: references pages read `data/references.json` directly via PHP
- Pagination: 20 per page, "Next Page" / "Previous Page" buttons
- Image sitemap: `sitemap-images.xml`
- Per-image XMP metadata (exiftool)
- Schema `ImageObject` for each reference image
- hreflang chain for all language variants
- llms.txt update with references section
