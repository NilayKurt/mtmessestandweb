# PHP Admin Panel — Design Spec

**Date:** 2026-08-10  
**Status:** Draft  
**Goal:** cPanel-ready, flat-file, multi-language admin panel for MT Messe Stand

---

## 1. Architecture

```
admin/                       ← all admin files
├── index.php                ← login page
├── dashboard.php            ← main panel (sidebar + content area)
├── editor-blog.php          ← blog CRUD
├── editor-page.php          ← about/contact edit
├── media.php                ← image upload/manage
├── config.php               ← password, languages, site URL
├── auth.php                 ← session + login check
├── logout.php
├── admin-layout.php         ← shared sidebar + topbar skeleton
└── actions/
    ├── save-blog.php        ← JSON write → HTML render → trigger build.php
    ├── save-page.php        ← JSON write → HTML render
    ├── delete-blog.php      ← delete JSON + HTML → trigger build.php
    └── upload.php           ← .webp/.jpg/.png, max 2MB

templates/
├── blog-template.php        ← render_blog($meta, $content_html, $lang)
├── page-template.php        ← render_page($meta, $content_html, $lang)

data/                        ← flat-file content (gitignored, created on setup)
└── {lang}/
    ├── blog/*.json
    ├── about.json
    └── contact.json
```

**Key rule:** User NEVER edits templates. Templates are read-only. User only writes to `data/` JSON fields. Page structure (navbar, footer, grid, CSS classes) is immutable.

---

## 2. Data Format

### Blog JSON (`data/tr/blog/slug.json`)
```json
{
  "title": "Blog Title",
  "date": "2026-08-10",
  "summary": "Short summary (used for SEO meta + blog cards)",
  "image": "/assets/img/blog/example.webp",
  "slug": "blog-slug",
  "content": "<h2>Section</h2><p>Text...</p>",
  "meta_desc": "SEO description (auto-generated from summary if empty)"
}
```

### Page JSON (`data/tr/about.json`, `data/tr/contact.json`)
```json
{
  "title": "About Us",
  "content": "<p>...</p>",
  "meta_desc": "SEO description"
}
```

---

## 3. Template System (CSS Never Breaks)

Three-layer render:

```
┌──────────────────────────────────┐
│  HEADER (from template)          │  ← IMMUTABLE
│  <!DOCTYPE...> <head> all metas  │
│  navbar-placeholder              │
├──────────────────────────────────┤
│  CONTENT (from JSON → template)  │  ← user WYSIWYG output
│  <h1>title</h1>                 │
│  <img src="image">              │
│  <article>content</article>     │
├──────────────────────────────────┤
│  FOOTER (from template)          │  ← IMMUTABLE
│  <footer>...</footer>           │
│  <script src="navbar.js">       │
│  </body></html>                 │
└──────────────────────────────────┘
```

**WYSIWYG sandbox:** Quill.js output is filtered via `strip_tags($html, '<h2><h3><h4><p><ul><ol><li><blockquote><strong><em><a><img><br><details><summary>')`. No `style=""`, no `class=""`. Template adds structure classes (`class="mistake-card"`, `class="blog-article"`) automatically — user never touches them.

**Atomic write:** Save to `.tmp` file first, then `rename()`. If PHP crashes mid-write, existing HTML stays intact. Page never breaks.

---

## 4. Auto SEO

User inputs: title, summary, image, content. Template generates:

| SEO Element | Source |
|-------------|--------|
| `<title>` | title + " | MT Messe Stand" |
| `meta description` | summary (truncated 160 chars) |
| `og:title` | title |
| `og:description` | summary |
| `og:image` | full URL from image field |
| `og:url` | lang + slug |
| `twitter:*` | mirrors og: |
| `canonical` | lang + slug |
| `hreflang` | cross-language mapping if exists |
| `Article schema` (JSON-LD) | headline, description, image, date, author, inLanguage |
| `FAQPage schema` (JSON-LD) | auto-extracted from `<details>` blocks |
| `BreadcrumbList` | Home → Blog → Title |
| `robots` | "index, follow" |
| `image alt` | derived from title |

---

## 5. Admin Panel UI

MT Messe branded (red #cc0000, dark #1a1a1a). Bootstrap 5 CDN.

**Layout:** Sidebar (logo + Blog/Page/Media icons) + topbar (language selector + logout) + content area.

**Language selector:** Persistent in topbar. Carried via `?lang=tr` + session. 7 languages: EN, TR, DE, FR, ES, AR, ZH.

**WYSIWYG:** Quill.js CDN. Minimal toolbar: bold, italic, H2, H3, bullet list, numbered list, blockquote, link, image. Output filtered to allowed tags only.

**Notifications:** Toast, top-right, 3 seconds, green success / red error.

**Responsive:** Sidebar collapses to topbar on mobile.

---

## 6. Security

| Layer | Implementation |
|-------|---------------|
| Authentication | Single password in `config.php`, session-based |
| Brute-force | `sleep(2)` + 5 attempts → 15min block (flat-file IP counter) |
| CSRF | Token per form, validated on POST |
| Input | `strip_tags` whitelist, `htmlspecialchars` for attributes |
| Upload | Extension whitelist (.webp/.jpg/.png), MIME check via `finfo`, 2MB max, filename sanitized |
| JSON | `json_encode`/`json_decode` — no injection vector |
| Directory | `data/` write-only for PHP, not web-accessible |

---

## 7. Atomic Operations

| Operation | Steps |
|-----------|-------|
| Save blog | 1. Validate input 2. Write `data/{lang}/blog/{slug}.json.tmp` 3. Render HTML to `{lang}/blog/{slug}.html.tmp` 4. `rename()` both 5. `include build.php` |
| Delete blog | 1. Check exists 2. `unlink()` JSON + HTML 3. `include build.php` |
| Save page | 1. Validate input 2. Write `data/{lang}/about.json.tmp` 3. Render HTML to `{lang}/about.html.tmp` 4. `rename()` both |
| Upload media | 1. Validate extension + MIME + size 2. Generate unique filename 3. `move_uploaded_file()` to `assets/img/blog/` |

---

## 8. build.php Integration

`save-blog.php` and `delete-blog.php` call `include '../build.php'` after file operations. build.php regenerates homepage blog cards for all languages. Footer language badges are auto-synced with `$LANGS` array.

---

## 9. Language Support (7 languages)

EN, TR, DE, FR, ES, AR, ZH. ES added 2026-08-10.

- `navbar.js`: ES in `allLangs` array + `langMatch` regex
- `build.php`: ES in `$LANGS` + `$LABELS` with Spanish labels
- `admin/config.php`: `LANGUAGES` constant
- Footer badges: auto-generated from `$LANGS` via `build.php`

---

## 10. Migration (one-time)

Existing blog HTML files (EN + TR) are parsed and converted to JSON on first admin panel load. Migration script:
1. Scan `{lang}/blog/*.html`
2. Extract `<h1>`, date, content from known HTML structure
3. Generate `data/{lang}/blog/{slug}.json`
4. Skip if JSON already exists (idempotent)

---

## 11. Implementation Order

1. `config.php` + `auth.php` + `admin-layout.php` (infrastructure)
2. `templates/blog-template.php` + `templates/page-template.php`
3. `editor-page.php` + `save-page.php` (simpler, tests templates)
4. `editor-blog.php` + `save-blog.php` + `delete-blog.php`
5. `media.php` + `upload.php`
6. `build.php` integration + footer badge auto-sync
7. Migration script (HTML → JSON)
8. Add ES footer badges to existing homepages

---

## 12. What This Replaces

| Old (dev-only Python) | New (PHP, production) |
|----------------------|----------------------|
| `scripts/build_blog_tr.py` | `actions/save-blog.php` + `templates/blog-template.php` |
| `scripts/audit_links.py` | Not needed — templates guarantee valid output |
| `admin/index.html` (Decap CMS stub) | `admin/index.php` (functional login) |
| Desktop `blogtr.txt` workflow | WYSIWYG editor in admin panel |
