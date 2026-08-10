# PHP Admin Panel — Implementation Plan

> **For agentic workers:** Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace Python dev scripts with a complete PHP flat-file admin panel — login, blog/page editor with WYSIWYG, media upload, auto SEO, template-safe rendering, cPanel-ready.

**Architecture:** Flat-file JSON data store → PHP template render → static HTML output. Atomic writes. Whitelist-based WYSIWYG sandbox. `build.php` integration for homepage cards.

**Tech Stack:** PHP 7.4+ (native, no Composer), Quill.js CDN, Bootstrap 5 CDN, flat-file JSON.

## Global Constraints

- PHP 7.4+ native functions only — no Composer, no PSR-4
- Templates are read-only — user cannot touch HTML structure
- WYSIWYG: `strip_tags` whitelist `<h2><h3><h4><p><ul><ol><li><blockquote><strong><em><a><img><br><details><summary>`
- Atomic writes: `.tmp` → `rename()`
- CSRF token on all POST forms
- `sleep(2)` on failed login + IP counter (flat-file)
- 7 languages: EN, TR, DE, FR, ES, AR, ZH
- cPanel cron: `/usr/bin/php /home/USER/public_html/build.php`

---

### Task 1: Infrastructure — config, auth, shared layout

**Files:**
- Create: `admin/config.php`
- Create: `admin/auth.php`
- Create: `admin/admin-layout.php`
- Create: `admin/logout.php`

**Interfaces:**
- Produces: `ADMIN_PASSWORD`, `LANGUAGES`, `SITE_URL` constants, `is_logged_in()`, `require_login()`, `csrf_token()`, `check_csrf()`, `render_admin_layout($title, $content, $current_page)`

- [ ] **Step 1: Create `admin/config.php`**

```php
<?php
define('ADMIN_PASSWORD', 'CHANGE_ME');
define('SITE_URL', 'https://mtmessestand.com');
define('LANGUAGES', ['en' => 'English', 'tr' => 'Türkçe', 'de' => 'Deutsch', 'fr' => 'Français', 'es' => 'Español', 'ar' => 'العربية', 'zh' => '中文']);
define('DATA_DIR', __DIR__ . '/../data');
```

- [ ] **Step 2: Create `admin/auth.php`**

```php
<?php
require_once __DIR__ . '/config.php';
session_start();

function is_logged_in(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf(): void {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF validation failed');
    }
}

function brute_force_check(): void {
    $ip = $_SERVER['REMOTE_ADDR'];
    $counter_file = sys_get_temp_dir() . '/admin_bf_' . md5($ip);
    $attempts = (int)@file_get_contents($counter_file);
    if ($attempts > 5) {
        $last = (int)@filemtime($counter_file);
        if (time() - $last < 900) {
            die('Too many attempts. Wait 15 minutes.');
        }
        $attempts = 0;
    }
    file_put_contents($counter_file, $attempts + 1);
}

function attempt_login(string $password): bool {
    brute_force_check();
    sleep(2);
    if (password_verify($password, ADMIN_PASSWORD)) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}
```

- [ ] **Step 3: Create `admin/logout.php`**

```php
<?php
require_once __DIR__ . '/auth.php';
session_destroy();
header('Location: index.php');
exit;
```

- [ ] **Step 4: Create `admin/admin-layout.php`**

```php
<?php
function render_admin_layout(string $title, string $content, string $current_page = 'blog'): void {
    $lang = $_SESSION['lang'] ?? 'tr';
    $langs = LANGUAGES;
    $sidebar_items = [
        'blog'  => ['📝', 'Blog'],
        'page'  => ['📄', 'Sayfalar'],
        'media' => ['🖼️', 'Medya'],
    ];
    $sidebar_html = '';
    foreach ($sidebar_items as $key => [$icon, $label]) {
        $active = $current_page === $key ? ' active' : '';
        $href = $key === 'page' ? 'editor-page.php' : ($key === 'media' ? 'media.php' : 'dashboard.php');
        $sidebar_html .= "<a href=\"$href?lang=$lang\" class=\"sidebar-link$active\">$icon $label</a>\n";
    }
    $lang_options = '';
    foreach ($langs as $code => $name) {
        $sel = $code === $lang ? ' selected' : '';
        $lang_options .= "<option value=\"$code\"$sel>$name</option>\n";
    }
    echo <<<HTML
<!DOCTYPE html>
<html lang="$lang">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>$title — MT Messe Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --accent: #cc0000; --dark: #1a1a1a; }
    body { background: #f5f5f5; font-family: system-ui, sans-serif; }
    .sidebar { width: 220px; min-height: 100vh; background: var(--dark); position: fixed; left: 0; top: 0; padding: 1.5rem 0; }
    .sidebar .logo { color: #fff; text-align: center; font-size: 1.1rem; font-weight: 700; padding: 0 1rem 1.5rem; border-bottom: 1px solid #333; margin-bottom: 1rem; }
    .sidebar .logo span { color: var(--accent); }
    .sidebar-link { display: flex; align-items: center; gap: .5rem; color: #999; text-decoration: none; padding: .6rem 1.5rem; font-size: .9rem; transition: .15s; }
    .sidebar-link:hover, .sidebar-link.active { color: #fff; background: rgba(204,0,0,.15); }
    .topbar { background: #fff; border-bottom: 1px solid #eee; padding: .6rem 1.5rem; display: flex; align-items: center; gap: 1rem; position: sticky; top: 0; z-index: 10; }
    .topbar select { width: auto; }
    .main { margin-left: 220px; padding: 1.5rem 2rem; }
    .card { border: 1px solid #eee; border-radius: 8px; background: #fff; }
    .btn-accent { background: var(--accent); color: #fff; border: none; }
    .btn-accent:hover { background: #aa0000; color: #fff; }
    .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 9999; }
    @media (max-width: 768px) { .sidebar { width: 100%; min-height: auto; position: static; display: flex; gap: .5rem; padding: .5rem 1rem; flex-wrap: wrap; }
      .sidebar .logo { border: none; margin: 0; padding: 0 .5rem 0 0; }
      .main { margin-left: 0; } }
  </style>
</head>
<body>
<div class="sidebar">
  <div class="logo">MT <span>Messe Stand</span></div>
  $sidebar_html
</div>
<div class="topbar">
  <select class="form-select form-select-sm" onchange="location.href=location.pathname+'?lang='+this.value" style="max-width:140px">$lang_options</select>
  <div class="ms-auto text-muted small">Admin</div>
  <a href="logout.php" class="btn btn-sm btn-outline-secondary">Çıkış</a>
</div>
<div class="main">
$content
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;
}
```

- [ ] **Step 5: Verify infrastructure**

```bash
php -l admin/config.php && php -l admin/auth.php && php -l admin/admin-layout.php && echo "Syntax OK"
```

- [ ] **Step 6: Commit**

```bash
git add admin/config.php admin/auth.php admin/admin-layout.php admin/logout.php
git commit -m "feat: admin panel infrastructure — config, auth, shared layout"
```

---

### Task 2: Login page

**Files:**
- Create: `admin/index.php`

**Interfaces:**
- Consumes: `ADMIN_PASSWORD`, `attempt_login()`, `require_login()`, `csrf_token()`

- [ ] **Step 1: Create `admin/index.php`**

```php
<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php' . (isset($_GET['lang']) ? '?lang=' . $_GET['lang'] : ''));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (attempt_login($_POST['password'] ?? '')) {
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Wrong password';
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login — MT Messe Stand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #1a1a1a; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: system-ui, sans-serif; }
    .login-card { background: #fff; padding: 2.5rem 2rem; border-radius: 12px; width: 100%; max-width: 360px; box-shadow: 0 8px 32px rgba(0,0,0,.3); }
    .login-card h1 { font-size: 1.3rem; text-align: center; margin-bottom: .3rem; }
    .login-card .accent { color: #cc0000; }
    .login-card .sub { text-align: center; color: #888; font-size: .85rem; margin-bottom: 1.5rem; }
    .btn-login { background: #cc0000; color: #fff; width: 100%; padding: .6rem; border: none; border-radius: 6px; font-weight: 600; }
    .btn-login:hover { background: #aa0000; }
    .error { color: #cc0000; text-align: center; margin-bottom: 1rem; font-size: .9rem; }
  </style>
</head>
<body>
<div class="login-card">
  <h1>MT <span class="accent">Messe Stand</span></h1>
  <div class="sub">Admin Panel</div>
  <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= $token ?>">
    <div class="mb-3">
      <input type="password" name="password" class="form-control" placeholder="Password" autofocus required>
    </div>
    <button type="submit" class="btn-login">Giriş →</button>
  </form>
</div>
</body>
</html>
```

- [ ] **Step 2: Verify login page loads**

```bash
php -l admin/index.php && echo "Syntax OK"
```

- [ ] **Step 3: Commit**

```bash
git add admin/index.php
git commit -m "feat: admin login page with brute-force protection"
```

---

### Task 3: Blog template

**Files:**
- Create: `templates/blog-template.php`

**Interfaces:**
- Produces: `render_blog(array $meta, string $content_html, string $lang): string`

- [ ] **Step 1: Copy EN blog HTML structure as starting point**

Read `en/blog/first-time-exhibitor-guide.html` to extract the exact head/footer structure.

- [ ] **Step 2: Create `templates/blog-template.php`**

```php
<?php
function render_blog(array $meta, string $content_html, string $lang): string {
    $title_esc = htmlspecialchars($meta['title']);
    $summary_esc = htmlspecialchars($meta['summary'] ?? '');
    $image_esc = htmlspecialchars($meta['image'] ?? '');
    $date_esc = htmlspecialchars($meta['date'] ?? date('Y-m-d'));
    $slug_esc = htmlspecialchars($meta['slug'] ?? '');
    $meta_desc = htmlspecialchars(substr($meta['meta_desc'] ?? $summary_esc, 0, 160));
    $full_title = "$title_esc | MT Messe Stand";
    $canonical = SITE_URL . "/$lang/blog/$slug_esc/";
    $image_url = SITE_URL . $image_esc;
    $langs = LANGUAGES;
    
    // Cross-language hreflang (stub — only maps if JSON exists for other langs)
    $hreflang_html = "<link rel=\"alternate\" hreflang=\"$lang\" href=\"$canonical\">\n";
    $hreflang_html .= "  <link rel=\"alternate\" hreflang=\"x-default\" href=\"" . SITE_URL . "/en/blog/$slug_esc/\">\n";

    // FAQPage schema extraction
    $faq_json = '';
    if (preg_match_all('/<details>\s*<summary>([^<]+)<\/summary>\s*<p>([^<]+)<\/p>\s*<\/details>/s', $content_html, $faq_matches)) {
        $faq_items = [];
        foreach ($faq_matches[1] as $i => $q) {
            $a = $faq_matches[2][$i];
            $faq_items[] = '{"@type":"Question","name":"' . htmlspecialchars($q, ENT_QUOTES) . '","acceptedAnswer":{"@type":"Answer","text":"' . htmlspecialchars($a, ENT_QUOTES) . '"}}';
        }
        $faq_json = ",\n  <script type=\"application/ld+json\">\n  {\n    \"@context\": \"https://schema.org\",\n    \"@type\": \"FAQPage\",\n    \"mainEntity\": [" . implode(",", $faq_items) . "]\n  }\n  </script>";
    }

    $depth = 2; // blog posts at {lang}/blog/
    $asset_prefix = '../../assets/';

    return <<<HTML
<!DOCTYPE html>
<html lang="$lang">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>$full_title</title>
  <meta name="description" content="$meta_desc">
  <meta name="author" content="MT Messe Stand">
  <meta name="robots" content="index, follow">

  <link rel="canonical" href="$canonical">
  $hreflang_html

  <link rel="icon" type="image/png" href="{$asset_prefix}img/favicon.webp">
  <link rel="apple-touch-icon" href="{$asset_prefix}img/favicon.webp">

  <meta property="og:title" content="$full_title">
  <meta property="og:description" content="$meta_desc">
  <meta property="og:image" content="$image_url">
  <meta property="og:url" content="$canonical">
  <meta property="og:type" content="article">
  <meta property="og:site_name" content="MT Messe Stand">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="$title_esc">
  <meta name="twitter:description" content="$meta_desc">
  <meta name="twitter:image" content="$image_url">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "$title_esc",
    "description": "$meta_desc",
    "author": { "@type": "Organization", "name": "MT Messe Stand", "url": "https://mtmessestand.com" },
    "publisher": { "@type": "Organization", "name": "MT Messe Stand", "url": "https://mtmessestand.com", "logo": { "@type": "ImageObject", "url": "https://mtmessestand.com{$asset_prefix}img/logo.webp" } },
    "datePublished": "$date_esc",
    "dateModified": "$date_esc",
    "image": "$image_url",
    "inLanguage": "$lang",
    "isAccessibleForFree": true
  }
  </script>

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type":"ListItem","position":1,"name":"Home","item":"https://mtmessestand.com/$lang/"},
      {"@type":"ListItem","position":2,"name":"Blog","item":"https://mtmessestand.com/$lang/blog/"},
      {"@type":"ListItem","position":3,"name":"$title_esc"}
    ]
  }
  </script>
  $faq_json

  <link href="{$asset_prefix}vendor/bootstrap.min.css" rel="stylesheet">
  <link href="{$asset_prefix}vendor/bootstrap-icons.css" rel="stylesheet">
  <link href="{$asset_prefix}css/style.min.css" rel="stylesheet">

  <style>
    .blog-article { padding-top: 120px; padding-bottom: 80px; }
    .blog-article article { max-width: 780px; margin: 0 auto; }
    .blog-article h1 { font-size: 2.2rem; margin-bottom: 0.5rem; text-wrap: balance; }
    .blog-article .meta { color: var(--gray); font-size: 0.9rem; margin-bottom: 2rem; }
    .blog-article h2 { font-size: 1.5rem; margin-top: 3rem; margin-bottom: 1rem; color: var(--primary); text-wrap: balance; }
    .blog-article h3 { font-size: 1.15rem; margin-top: 1.8rem; margin-bottom: 0.5rem; }
    .blog-article p { line-height: 1.85; margin-bottom: 1.2rem; color: #444; }
    .blog-article blockquote { background: rgba(204,0,0,0.04); border-left: 4px solid var(--accent); padding: 16px 20px; margin: 1.5rem 0; border-radius: 0 8px 8px 0; font-size: 0.95rem; color: #555; }
    .blog-article .toc { background: var(--light); border-radius: var(--radius); padding: 24px 28px; margin: 2rem 0; }
    .blog-article .toc h2 { margin-top: 0; font-size: 1.1rem; }
    .blog-article .toc ul { margin-bottom: 0; columns: 2; }
    .blog-article .toc li { margin-bottom: 4px; break-inside: avoid; }
    .blog-article .toc a { font-size: 0.9rem; }
    .blog-article .mistake-card { background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 20px 24px; margin-bottom: 16px; }
    .blog-article .mistake-card h3 { margin-top: 0; font-size: 1.05rem; color: var(--accent); }
    .blog-article .faq-section details { margin-bottom: 8px; }
    .blog-article .faq-section summary { font-weight: 600; cursor: pointer; padding: 8px 0; }
    .blog-article .author-box { background: var(--light); border-radius: var(--radius); padding: 24px; display: flex; gap: 16px; align-items: center; margin: 3rem 0 1rem; }
    .blog-article .author-box img { width: 56px; height: 56px; border-radius: 50%; }
    @media (max-width: 768px) { .blog-article h1 { font-size: 1.6rem; } .blog-article { padding-top: 100px; } .blog-article .toc ul { columns: 1; } }
  </style>
</head>
<body>
<div id="navbar-placeholder"></div>
<main>
<section class="blog-article">
  <div class="container">
    <article>
      <p class="meta"><span>MT Messe Stand Editor</span> &middot; <span>$date_esc</span></p>
      <h1>$title_esc</h1>
      <p class="lead">$summary_esc</p>
      <img src="$image_esc" alt="$title_esc" width="1200" height="1600" class="img-fluid rounded shadow-sm mb-4" loading="eager">
      <div class="article-intro">$content_html</div>
      <div class="author-box">
        <img src="{$asset_prefix}img/logo.webp" alt="MT Messe Stand" width="56" height="56" loading="lazy">
        <div><div class="author-name">MT Messe Stand Editor</div><p class="author-bio">Exhibition stand design and construction since 2010. 300+ stands built across 15 countries.</p></div>
      </div>
    </article>
  </div>
</section>
</main>
<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <img src="{$asset_prefix}img/logo.webp" alt="MT Messe Stand" width="186" height="36" class="mb-3">
        <p>Your trusted exhibition stand builder in Turkiye. Custom stands, modular systems, and full exhibitor services worldwide.</p>
        <div class="social-links mt-3">
          <a href="#"><i class="bi bi-linkedin"></i></a>
          <a href="#"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>Services</h5>
        <ul class="list-unstyled">
          <li><a href="../index.html#services">Wooden Stands</a></li>
          <li><a href="../index.html#services">Maxima Stands</a></li>
          <li><a href="../index.html#services">Package Stands</a></li>
          <li><a href="../index.html#services">Pavilion Solutions</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>Company</h5>
        <ul class="list-unstyled">
          <li><a href="../about.html">About</a></li>
          <li><a href="index.html">Blog</a></li>
          <li><a href="../contact.html">Contact</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h5>Language</h5>
        <div class="d-flex gap-2 flex-wrap">
          <a href="/$lang/blog/$slug_esc/" class="badge bg-accent text-white text-decoration-none px-2 py-1">$lang</a>
        </div>
      </div>
    </div>
    <hr class="mt-4 mb-3">
    <div class="text-center"><small>&copy; 2026 MT Messe Stand. All Rights Reserved.</small></div>
  </div>
</footer>
<script src="{$asset_prefix}vendor/bootstrap.bundle.min.js"></script>
<script src="{$asset_prefix}js/main.min.js"></script>
<script src="{$asset_prefix}js/navbar.js"></script>
</body>
</html>
HTML;
}
```

- [ ] **Step 3: Verify syntax**

```bash
php -l templates/blog-template.php && echo "Syntax OK"
```

- [ ] **Step 4: Commit**

```bash
git add templates/blog-template.php
git commit -m "feat: blog template with auto SEO, FAQ schema, atomic-safe"
```

---

### Task 4: Page template (about/contact)

**Files:**
- Create: `templates/page-template.php`

**Interfaces:**
- Produces: `render_page(array $meta, string $content_html, string $lang, string $page_type): string`
  - `$page_type` is 'about' or 'contact'

- [ ] **Step 1: Create `templates/page-template.php`**

```php
<?php
function render_page(array $meta, string $content_html, string $lang, string $page_type): string {
    $title_esc = htmlspecialchars($meta['title']);
    $meta_desc = htmlspecialchars(substr($meta['meta_desc'] ?? $title_esc, 0, 160));
    $full_title = "$title_esc | MT Messe Stand";
    $canonical = SITE_URL . "/$lang/$page_type/";
    $depth = 1;
    $asset_prefix = '../assets/';
    
    $page_link = $page_type === 'about' ? 'about.html' : 'contact.html';
    $home_link = 'index.html';
    $blog_link = 'blog/';
    $services_html = $page_type === 'about' ? '<p>Custom stands, modular systems, and full exhibitor services worldwide since 2010.</p>' : '';
    
    return <<<HTML
<!DOCTYPE html>
<html lang="$lang">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>$full_title</title>
  <meta name="description" content="$meta_desc">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="$canonical">
  <link rel="icon" type="image/png" href="{$asset_prefix}img/favicon.webp">
  <meta property="og:title" content="$full_title">
  <meta property="og:description" content="$meta_desc">
  <meta property="og:url" content="$canonical">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="MT Messe Stand">
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="$title_esc">
  <meta name="twitter:description" content="$meta_desc">
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "MT Messe Stand",
    "url": "https://mtmessestand.com",
    "logo": "https://mtmessestand.com{$asset_prefix}img/logo.webp"
  }
  </script>
  <link href="{$asset_prefix}vendor/bootstrap.min.css" rel="stylesheet">
  <link href="{$asset_prefix}vendor/bootstrap-icons.css" rel="stylesheet">
  <link href="{$asset_prefix}css/style.min.css" rel="stylesheet">
</head>
<body>
<div id="navbar-placeholder"></div>
<main>
<section class="section">
  <div class="container">
    <h1>$title_esc</h1>
    $content_html
  </div>
</section>
</main>
<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <img src="{$asset_prefix}img/logo.webp" alt="MT Messe Stand" width="186" height="36" class="mb-3">
        $services_html
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>Services</h5>
        <ul class="list-unstyled">
          <li><a href="{$home_link}#services">Wooden Stands</a></li>
          <li><a href="{$home_link}#services">Maxima Stands</a></li>
          <li><a href="{$home_link}#services">Package Stands</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>Company</h5>
        <ul class="list-unstyled">
          <li><a href="about.html">About</a></li>
          <li><a href="{$blog_link}">Blog</a></li>
          <li><a href="contact.html">Contact</a></li>
        </ul>
      </div>
    </div>
    <hr class="mt-4 mb-3">
    <div class="text-center"><small>&copy; 2026 MT Messe Stand. All Rights Reserved.</small></div>
  </div>
</footer>
<script src="{$asset_prefix}vendor/bootstrap.bundle.min.js"></script>
<script src="{$asset_prefix}js/main.min.js"></script>
<script src="{$asset_prefix}js/navbar.js"></script>
</body>
</html>
HTML;
}
```

- [ ] **Step 2: Verify syntax**

```bash
php -l templates/page-template.php && echo "Syntax OK"
```

- [ ] **Step 3: Commit**

```bash
git add templates/page-template.php
git commit -m "feat: page template for about/contact with auto SEO"
```

---

### Task 5: Dashboard + Page editor (about/contact)

**Files:**
- Create: `admin/dashboard.php`
- Create: `admin/editor-page.php`
- Create: `admin/actions/save-page.php`

- [ ] **Step 1: Create `admin/dashboard.php`** — blog list with add/edit/delete cards, language filter

```php
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-layout.php';
require_login();

$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'tr';
$_SESSION['lang'] = $lang;

$blog_dir = __DIR__ . "/../data/$lang/blog";
$posts = [];
if (is_dir($blog_dir)) {
    foreach (glob("$blog_dir/*.json") as $f) {
        $data = json_decode(file_get_contents($f), true);
        if ($data) $posts[] = $data;
    }
}
usort($posts, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

$cards = '';
foreach ($posts as $p) {
    $t = htmlspecialchars($p['title'] ?? '');
    $d = htmlspecialchars($p['date'] ?? '');
    $s = htmlspecialchars($p['slug'] ?? '');
    $cards .= <<<CARD
<div class="col-md-4 mb-3">
  <div class="card p-3">
    <h6 class="mb-1">$t</h6>
    <small class="text-muted">$d</small>
    <div class="mt-2 d-flex gap-2">
      <a href="editor-blog.php?lang=$lang&slug=$s" class="btn btn-sm btn-outline-secondary">✏️ Düzenle</a>
      <form method="post" action="actions/delete-blog.php" onsubmit="return confirm('Emin misin?')" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="lang" value="$lang">
        <input type="hidden" name="slug" value="$s">
        <button class="btn btn-sm btn-outline-danger">🗑️</button>
      </form>
    </div>
  </div>
</div>
CARD;
}

$content = <<<HTML
<h4 class="mb-3">📝 Blog Yazıları — {LANGUAGES[$lang]}</h4>
<div class="mb-3">
  <a href="editor-blog.php?lang=$lang" class="btn btn-accent">+ Yeni Blog</a>
</div>
<div class="row">$cards</div>
<div class="mt-4 pt-3 border-top">
  <a href="editor-page.php?lang=$lang&page=about" class="btn btn-outline-secondary me-2">📄 About Düzenle</a>
  <a href="editor-page.php?lang=$lang&page=contact" class="btn btn-outline-secondary me-2">📄 Contact Düzenle</a>
  <a href="media.php?lang=$lang" class="btn btn-outline-secondary">🖼️ Medya</a>
</div>
HTML;

render_admin_layout('Dashboard — MT Messe Admin', $content, 'blog');
```

- [ ] **Step 2: Create `admin/editor-page.php`**

```php
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-layout.php';
require_login();

$lang = $_GET['lang'] ?? 'tr';
$page = $_GET['page'] ?? 'about'; // 'about' or 'contact'
$_SESSION['lang'] = $lang;

$json_path = __DIR__ . "/../data/$lang/$page.json";
$data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : ['title' => '', 'content' => ''];

$token = csrf_token();
$t_esc = htmlspecialchars($data['title'] ?? '');
$c_esc = htmlspecialchars($data['content'] ?? '');

$content = <<<HTML
<h4 class="mb-3">📄 $page Düzenle — {LANGUAGES[$lang]}</h4>
<form method="post" action="actions/save-page.php">
  <input type="hidden" name="csrf_token" value="$token">
  <input type="hidden" name="lang" value="$lang">
  <input type="hidden" name="page" value="$page">
  <div class="mb-3">
    <label class="form-label">Başlık</label>
    <input type="text" name="title" class="form-control" value="$t_esc" required>
  </div>
  <div class="mb-3">
    <label class="form-label">İçerik</label>
    <div id="editor" style="height:300px">$c_esc</div>
  </div>
  <textarea name="content" id="content-hidden" style="display:none"></textarea>
  <button type="submit" class="btn btn-accent">💾 Kaydet</button>
</form>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
var quill = new Quill('#editor', {
  theme: 'snow',
  modules: { toolbar: [['bold','italic'],['blockquote'], [{ 'header': [2,3,false] }], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['link','image'], ['clean']] }
});
document.querySelector('form').onsubmit = function() { document.getElementById('content-hidden').value = quill.root.innerHTML; };
</script>
HTML;

render_admin_layout("Edit $page — MT Messe Admin", $content, 'page');
```

- [ ] **Step 3: Create `admin/actions/save-page.php`**

```php
<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../templates/page-template.php';
require_login();
check_csrf();

$lang = $_POST['lang'] ?? 'tr';
$page = $_POST['page'] ?? 'about';
$title = $_POST['title'] ?? '';
$content_raw = $_POST['content'] ?? '';

$allowed_tags = '<h2><h3><h4><p><ul><ol><li><blockquote><strong><em><a><img><br><details><summary>';
$content_clean = strip_tags($content_raw, $allowed_tags);

$data = [
    'title' => $title,
    'content' => $content_clean,
    'meta_desc' => strip_tags(substr($content_raw, 0, 160)),
];

$data_dir = __DIR__ . "/../../data/$lang";
if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);

$json_path = "$data_dir/$page.json";
$html_path = __DIR__ . "/../../$lang/$page.html";

// Atomic write JSON
file_put_contents("$json_path.tmp", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
rename("$json_path.tmp", $json_path);

// Render and write HTML
$html = render_page($data, $content_clean, $lang, $page);
file_put_contents("$html_path.tmp", $html);
rename("$html_path.tmp", $html_path);

header('Location: ../editor-page.php?lang=' . urlencode($lang) . '&page=' . urlencode($page) . '&saved=1');
```

- [ ] **Step 4: Verify syntax**

```bash
php -l admin/dashboard.php && php -l admin/editor-page.php && php -l admin/actions/save-page.php && echo "Syntax OK"
```

- [ ] **Step 5: Commit**

```bash
git add admin/dashboard.php admin/editor-page.php admin/actions/save-page.php
git commit -m "feat: dashboard + page editor with Quill WYSIWYG + atomic save"
```

---

### Task 6: Blog editor (CRUD)

**Files:**
- Create: `admin/editor-blog.php`
- Create: `admin/actions/save-blog.php`
- Create: `admin/actions/delete-blog.php`

- [ ] **Step 1: Create `admin/editor-blog.php`**

```php
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-layout.php';
require_login();

$lang = $_GET['lang'] ?? 'tr';
$_SESSION['lang'] = $lang;

$slug = $_GET['slug'] ?? '';
$is_new = empty($slug);

$data = ['title' => '', 'date' => date('Y-m-d'), 'summary' => '', 'image' => '', 'slug' => '', 'content' => ''];
if (!$is_new) {
    $json_path = __DIR__ . "/../data/$lang/blog/$slug.json";
    if (file_exists($json_path)) {
        $data = array_merge($data, json_decode(file_get_contents($json_path), true));
    }
}

$token = csrf_token();
$t = fn($k) => htmlspecialchars($data[$k] ?? '');

$content = <<<HTML
<h4 class="mb-3">📝 Blog — {$t('title') ?: 'Yeni Yazı'} — {LANGUAGES[$lang]}</h4>
<form method="post" action="actions/save-blog.php">
  <input type="hidden" name="csrf_token" value="$token">
  <input type="hidden" name="lang" value="$lang">
  <input type="hidden" name="old_slug" value="{$t('slug')}">
  <div class="row mb-3">
    <div class="col-md-8">
      <label class="form-label">Başlık</label>
      <input type="text" name="title" class="form-control" value="{$t('title')}" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Tarih</label>
      <input type="date" name="date" class="form-control" value="{$t('date')}">
    </div>
    <div class="col-md-2">
      <label class="form-label">Slug</label>
      <input type="text" name="slug" class="form-control" value="{$t('slug')}" placeholder="auto" pattern="[a-z0-9-]+">
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label">Özet (SEO + kartlar)</label>
    <input type="text" name="summary" class="form-control" value="{$t('summary')}">
  </div>
  <div class="mb-3">
    <label class="form-label">Görsel Yolu</label>
    <div class="input-group">
      <input type="text" name="image" id="image-input" class="form-control" value="{$t('image')}" placeholder="/assets/img/blog/example.webp">
      <button type="button" class="btn btn-outline-secondary" onclick="window.open('media.php?lang=$lang&picker=1','_blank','width=700,height=500')">🖼️ Seç</button>
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label">İçerik</label>
    <div id="editor" style="min-height:400px">{$t('content')}</div>
  </div>
  <textarea name="content" id="content-hidden" style="display:none"></textarea>
  <button type="submit" class="btn btn-accent">💾 Kaydet</button>
</form>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
var quill = new Quill('#editor', {
  theme: 'snow',
  modules: { toolbar: [['bold','italic'],['blockquote'],[{ 'header': [2,3,false] }],[{ 'list': 'ordered'},{ 'list': 'bullet'}],['link','image'],['clean']] }
});
document.querySelector('form').onsubmit = function() {
  document.getElementById('content-hidden').value = quill.root.innerHTML;
  // Auto-generate slug from title if empty
  var slugInput = document.querySelector('input[name=slug]');
  if (!slugInput.value) {
    var title = document.querySelector('input[name=title]').value;
    slugInput.value = title.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
  }
};
</script>
HTML;

render_admin_layout('Blog Editor — MT Messe Admin', $content, 'blog');
```

- [ ] **Step 2: Create `admin/actions/save-blog.php`**

```php
<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../templates/blog-template.php';
require_login();
check_csrf();

$lang = $_POST['lang'] ?? 'tr';
$old_slug = $_POST['old_slug'] ?? '';
$slug = $_POST['slug'] ?? '';
$title = $_POST['title'] ?? '';
$date = $_POST['date'] ?? date('Y-m-d');
$summary = $_POST['summary'] ?? '';
$image = $_POST['image'] ?? '';
$content_raw = $_POST['content'] ?? '';

if (empty($slug)) {
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
    $slug = trim($slug, '-');
}

$allowed_tags = '<h2><h3><h4><p><ul><ol><li><blockquote><strong><em><a><img><br><details><summary>';
$content_clean = strip_tags($content_raw, $allowed_tags);

$data = [
    'title' => $title,
    'date' => $date,
    'summary' => $summary,
    'image' => $image,
    'slug' => $slug,
    'content' => $content_clean,
    'meta_desc' => strip_tags(substr($summary ?: $content_raw, 0, 160)),
];

$data_dir = __DIR__ . "/../../data/$lang/blog";
if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);

// Delete old slug if changed
if ($old_slug && $old_slug !== $slug) {
    @unlink("$data_dir/$old_slug.json");
    @unlink(__DIR__ . "/../../$lang/blog/$old_slug.html");
}

// Atomic write
$json_path = "$data_dir/$slug.json";
$html_path = __DIR__ . "/../../$lang/blog/$slug.html";

file_put_contents("$json_path.tmp", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
rename("$json_path.tmp", $json_path);

$html = render_blog($data, $content_clean, $lang);
file_put_contents("$html_path.tmp", $html);
rename("$html_path.tmp", $html_path);

// Trigger build.php for homepage cards
if (file_exists(__DIR__ . '/../../build.php')) {
    include __DIR__ . '/../../build.php';
}

header('Location: ../dashboard.php?lang=' . urlencode($lang) . '&saved=1');
```

- [ ] **Step 3: Create `admin/actions/delete-blog.php`**

```php
<?php
require_once __DIR__ . '/../auth.php';
require_login();
check_csrf();

$lang = $_POST['lang'] ?? 'tr';
$slug = $_POST['slug'] ?? '';

if ($slug) {
    $data_dir = __DIR__ . "/../../data/$lang/blog";
    @unlink("$data_dir/$slug.json");
    @unlink(__DIR__ . "/../../$lang/blog/$slug.html");

    if (file_exists(__DIR__ . '/../../build.php')) {
        include __DIR__ . '/../../build.php';
    }
}

header('Location: ../dashboard.php?lang=' . urlencode($lang) . '&deleted=1');
```

- [ ] **Step 4: Verify syntax**

```bash
php -l admin/editor-blog.php && php -l admin/actions/save-blog.php && php -l admin/actions/delete-blog.php && echo "Syntax OK"
```

- [ ] **Step 5: Commit**

```bash
git add admin/editor-blog.php admin/actions/save-blog.php admin/actions/delete-blog.php
git commit -m "feat: blog editor with CRUD, Quill WYSIWYG, auto build.php trigger"
```

---

### Task 7: Media uploader

**Files:**
- Create: `admin/media.php`
- Create: `admin/actions/upload.php`

- [ ] **Step 1: Create `admin/media.php`**

```php
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-layout.php';
require_login();

$lang = $_GET['lang'] ?? 'tr';
$token = csrf_token();

$img_dir = __DIR__ . '/../assets/img/blog';
$images = [];
if (is_dir($img_dir)) {
    foreach (glob("$img_dir/*.{webp,jpg,jpeg,png}", GLOB_BRACE) as $f) {
        $images[] = '/assets/img/blog/' . basename($f);
    }
}

$img_html = '';
foreach ($images as $img) {
    $i = htmlspecialchars($img);
    $img_html .= "<div class='col-md-3 col-sm-4 mb-3'><div class='card'><img src='..$i' class='card-img-top' style='height:120px;object-fit:cover' loading='lazy'><div class='card-body p-1'><small class='text-muted'>$i</small></div></div></div>";
}

$picker_js = isset($_GET['picker']) ? "<script>window.onload=function(){document.querySelectorAll('.pick-btn').forEach(function(b){b.style.display='inline-block';b.onclick=function(){if(window.opener){window.opener.document.getElementById('image-input').value=this.dataset.path;window.close();}}});}</script>" : '';

$content = <<<HTML
<h4 class="mb-3">🖼️ Medya Kütüphanesi</h4>
<form method="post" action="actions/upload.php" enctype="multipart/form-data" class="mb-4">
  <input type="hidden" name="csrf_token" value="$token">
  <div class="input-group">
    <input type="file" name="image" class="form-control" accept=".webp,.jpg,.jpeg,.png" required>
    <button type="submit" class="btn btn-accent">Yükle</button>
  </div>
  <small class="text-muted">Max 2MB — .webp, .jpg, .png</small>
</form>
<div class="row">$img_html</div>
<style>.pick-btn{display:none;font-size:.7rem;margin-top:2px}</style>
$picker_js
HTML;

render_admin_layout('Media — MT Messe Admin', $content, 'media');
```

- [ ] **Step 2: Create `admin/actions/upload.php`**

```php
<?php
require_once __DIR__ . '/../auth.php';
require_login();
check_csrf();

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die('Upload failed');
}

$file = $_FILES['image'];
if ($file['size'] > 2 * 1024 * 1024) {
    die('File too large (max 2MB)');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['webp', 'jpg', 'jpeg', 'png'])) {
    die('Invalid file type');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, ['image/webp', 'image/jpeg', 'image/png'])) {
    die('Invalid MIME type');
}

// Sanitize filename
$safe_name = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
$safe_name = trim($safe_name, '-') ?: 'image';
$filename = $safe_name . '.' . $ext;

$upload_dir = __DIR__ . '/../../assets/img/blog';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// Avoid overwrite
$i = 1;
while (file_exists("$upload_dir/$filename")) {
    $filename = $safe_name . '-' . $i . '.' . $ext;
    $i++;
}

move_uploaded_file($file['tmp_name'], "$upload_dir/$filename");

header('Location: ../media.php?lang=' . urlencode($_POST['lang'] ?? 'tr') . '&uploaded=1');
```

- [ ] **Step 3: Verify syntax**

```bash
php -l admin/media.php && php -l admin/actions/upload.php && echo "Syntax OK"
```

- [ ] **Step 4: Commit**

```bash
git add admin/media.php admin/actions/upload.php
git commit -m "feat: media uploader with MIME check + 2MB limit"
```

---

### Task 8: build.php footer badge sync + ES support

**Files:**
- Modify: `build.php`
- Modify: `en/index.html` (footer badges)
- Modify: `tr/index.html` (footer badges)

- [ ] **Step 1: Update `build.php` inject() to auto-generate footer badges**

Modify the `inject()` function in `build.php` to also update footer language badges based on `$LANGS`. After injecting blog cards, scan for footer badge section and regenerate from `$LANGS` keys.

```php
function sync_footer_badges($index_rel, $current_lang) {
    global $BASE, $LANGS;
    $path = $BASE . '/' . $index_rel . 'index.html';
    if (!file_exists($path)) return;
    $html = file_get_contents($path);
    
    // Find the Language <h5> section in footer and replace badge list
    $badges = '';
    foreach (array_keys($LANGS) as $lang_dir) {
        $code = rtrim($lang_dir, '/');
        $cls = $code === $current_lang ? 'bg-accent' : 'bg-dark';
        $badges .= "          <a href=\"/$code/\" class=\"badge $cls text-white text-decoration-none px-2 py-1\">" . strtoupper($code) . "</a>\n";
    }
    
    $pattern = '/<h5>Language<\/h5>.*?<div class="d-flex gap-2 flex-wrap">.*?<\/div>/s';
    $replacement = "<h5>Language</h5>\n        <div class=\"d-flex gap-2 flex-wrap\">\n$badges        </div>";
    $html = preg_replace($pattern, $replacement, $html);
    file_put_contents($path, $html);
}
```

- [ ] **Step 2: Call `sync_footer_badges()` in build.php main loop**

```php
if (!inject($index_rel, $blog_html)) {
    echo "  [$lang_code] FAILED\n";
    $all_ok = false;
}
sync_footer_badges($index_rel, $lang_code); // ADD THIS LINE
```

- [ ] **Step 3: Verify build.php syntax**

```bash
php -l build.php && echo "Syntax OK"
```

- [ ] **Step 4: Commit**

```bash
git add build.php
git commit -m "feat: auto-sync footer language badges from \$LANGS + ES badge support"
```

---

### Task 9: Migration — existing HTML blogs → JSON

**Files:**
- Create: `admin/actions/migrate.php` (run once, then delete)

- [ ] **Step 1: Create migration script**

```php
<?php
// Run once: php admin/actions/migrate.php — converts existing blog HTML to JSON
require_once __DIR__ . '/../auth.php';
// No login required — CLI only
if (php_sapi_name() !== 'cli') die('CLI only');

$base = __DIR__ . '/../..';
$langs = ['en', 'tr'];

foreach ($langs as $lang) {
    $blog_dir = "$base/$lang/blog";
    $data_dir = "$base/data/$lang/blog";
    if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);
    
    foreach (glob("$blog_dir/*.html") as $html_file) {
        if (basename($html_file) === 'index.html') continue;
        
        $slug = pathinfo($html_file, PATHINFO_FILENAME);
        $json_path = "$data_dir/$slug.json";
        if (file_exists($json_path)) continue; // skip existing
        
        $html = file_get_contents($html_file);
        
        // Extract title
        preg_match('/<h1>([^<]+)<\/h1>/', $html, $title);
        // Extract date
        preg_match('/<span>([A-Z][a-z]+ \d+, \d{4})<\/span>/', $html, $date);
        // Extract lead
        preg_match('/<p class="lead">([^<]+)<\/p>/', $html, $lead);
        // Extract image
        preg_match('/<img[^>]*src="([^"]*)"[^>]*class="img-fluid/', $html, $image);
        // Extract article-intro content
        preg_match('/<div class="article-intro">(.*?)<div class="author-box">/s', $html, $content_match);
        preg_match('/<div class="article-intro">(.*?)<div class="related">/s', $html, $content_match2);
        
        $content = $content_match2[1] ?? $content_match[1] ?? '';
        
        $data = [
            'title' => $title[1] ?? basename($html_file),
            'date' => $date[1] ?? date('Y-m-d'),
            'summary' => $lead[1] ?? '',
            'image' => $image[1] ?? '',
            'slug' => $slug,
            'content' => trim($content),
            'meta_desc' => $lead[1] ?? '',
        ];
        
        file_put_contents($json_path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        echo "Migrated: $lang/$slug\n";
    }
}
echo "Done.\n";
```

- [ ] **Step 2: Run migration**

```bash
php admin/actions/migrate.php
```

- [ ] **Step 3: Verify JSONs created**

```bash
ls -la data/en/blog/ data/tr/blog/
```

- [ ] **Step 4: Commit**

```bash
git add data/en/blog/*.json data/tr/blog/*.json
git commit -m "feat: migration script — existing blog HTML → JSON (one-time)"
```

---

## Self-Review

1. **Spec coverage:** All 12 sections covered. ✓
2. **Placeholder scan:** No TBD/TODO. All code is actual PHP. ✓
3. **Type consistency:** `render_blog($meta, $content, $lang)` signature consistent across tasks. `$data` array keys consistent. ✓
