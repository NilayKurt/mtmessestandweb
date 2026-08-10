# Merkezi Navbar & Footer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Navbar ve footer'ı tek PHP fonksiyon kaynağından render et, kod tekrarını bitir.

**Architecture:** `templates/navbar.php` ve `templates/footer.php` render fonksiyonlarını içerir. Statik sayfalar (ana sayfa, blog index) `<!-- NAVBAR -->` / `<!-- FOOTER -->` placeholder'ları ile build.php tarafından enjekte edilir. Template sayfalar (about, contact, blog yazıları) fonksiyonları doğrudan çağırır.

**Tech Stack:** PHP 7+, HTML, CSS. cPanel uyumlu. Statik .html uzantılar korunur.

## Global Constraints

- `.html` uzantılı sayfalar değişmez, SEO korunur
- navbar.js KALDIRILACAK, footer-data.php KALDIRILACAK
- Tüm çeviriler fonksiyon içinde inline dizilerde
- CSS (`style.min.css`) değişmeyecek
- 7 dil: en, tr, de, fr, es, ar, zh

---

### Task 1: templates/navbar.php — render_navbar()

**Files:**
- Create: `templates/navbar.php`

**Interfaces:**
- Produces: `function render_navbar(string $lang, string $page_type, string $slug = ''): string`

- [ ] **Step 1: Dosyayı oluştur**

```php
<?php
function render_navbar(string $lang, string $page_type, string $slug = ''): string {
    // Menu labels per language
    $labels = [
        'en' => ['Home', 'About', 'Blog', 'Contact'],
        'tr' => ['Anasayfa', 'Hakkımızda', 'Blog', 'İletişim'],
        'de' => ['Start', 'Über uns', 'Blog', 'Kontakt'],
        'fr' => ['Accueil', 'À propos', 'Blog', 'Contact'],
        'es' => ['Inicio', 'Nosotros', 'Blog', 'Contacto'],
        'ar' => ['الرئيسية', 'من نحن', 'المدونة', 'اتصل بنا'],
        'zh' => ['首页', '关于我们', '博客', '联系我们'],
    ];
    $L = $labels[$lang] ?? $labels['en'];

    // All languages
    $allLangs = [
        ['code' => 'en', 'name' => 'EN', 'available' => true],
        ['code' => 'tr', 'name' => 'TR', 'available' => true],
        ['code' => 'de', 'name' => 'DE', 'available' => false],
        ['code' => 'fr', 'name' => 'FR', 'available' => false],
        ['code' => 'es', 'name' => 'ES', 'available' => true],
        ['code' => 'ar', 'name' => 'AR', 'available' => false],
        ['code' => 'zh', 'name' => 'ZH', 'available' => false],
    ];

    // Blog filename map
    $blogMap = [
        'germany-hidden-costs' => ['en' => 'germany-hidden-costs', 'tr' => 'almanya-hidden-costs'],
        'first-time-exhibitor-guide' => ['en' => 'first-time-exhibitor-guide', 'tr' => 'ilk-kez-katilacaklar-rehberi'],
    ];

    // Active class helper
    $a = fn($t) => $page_type === $t ? ' active' : '';

    // Base path for blog pages
    $navBase = ($page_type === 'blog_post' || $page_type === 'blog_list') ? '../' : '';

    // Blog link
    $blogHref = ($page_type === 'blog_post' || $page_type === 'blog_list') ? './' : 'blog/';

    // Language dropdown
    $langOptions = '';
    foreach ($allLangs as $l) {
        if ($l['code'] === $lang) continue;
        $targetSlug = '';
        if ($page_type === 'blog_post' && $slug) {
            $mapped = $blogMap[$slug][$l['code']] ?? $slug;
            $targetSlug = "/blog/$mapped.html";
        } elseif ($page_type === 'blog_list') {
            $targetSlug = '/blog/';
        } elseif ($page_type === 'about') {
            $targetSlug = '/about.html';
        } elseif ($page_type === 'contact') {
            $targetSlug = '/contact.html';
        }
        $target = $l['available'] ? '/' . $l['code'] . $targetSlug : '#';
        $label = $l['name'] . ($l['available'] ? '' : ' (soon)');
        $cls = $l['available'] ? '' : ' text-muted';
        $langOptions .= '<li><a class="dropdown-item' . $cls . '" href="' . $target . '">' . $label . '</a></li>' . "\n";
    }

    // Current language label
    $currentLabel = 'EN';
    foreach ($allLangs as $l) {
        if ($l['code'] === $lang) { $currentLabel = $l['name']; break; }
    }

    return '
<header id="header" class="fixed-top">
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="' . $navBase . 'index.html">
        <img src="/assets/img/logo.webp" alt="MT Messe Stand" width="140" height="27">
        <span class="brand-text">MT Messe Stand</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link' . $a('home') . '" href="' . $navBase . 'index.html">' . $L[0] . '</a></li>
          <li class="nav-item"><a class="nav-link' . $a('about') . '" href="' . $navBase . 'about.html">' . $L[1] . '</a></li>
          <li class="nav-item"><a class="nav-link' . $a('blog_list') . $a('blog_post') . '" href="' . $blogHref . '">' . $L[2] . '</a></li>
          <li class="nav-item"><a class="nav-link' . $a('contact') . '" href="' . $navBase . 'contact.html">' . $L[3] . '</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">' . $currentLabel . '</a>
            <ul class="dropdown-menu">
' . $langOptions . '            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>';
}
```

- [ ] **Step 2: PHP syntax kontrolü**

Run: `php -l templates/navbar.php`
Expected: "No syntax errors"

- [ ] **Step 3: Commit**

```bash
git add templates/navbar.php
git commit -m "feat: add render_navbar() PHP function"
```

---

### Task 2: templates/footer.php — render_footer()

**Files:**
- Create: `templates/footer.php`

**Interfaces:**
- Produces: `function render_footer(string $lang, int $depth, string $page_type): string`

- [ ] **Step 1: Dosyayı oluştur**

```php
<?php
function render_footer(string $lang, int $depth, string $page_type): string {
    // Asset prefix by depth
    $ap = [0 => 'assets/', 1 => '../assets/', 2 => '../../assets/'][$depth];

    // Homepage index path
    $hp = [0 => '#', 1 => 'index.html', 2 => '../../index.html'][$depth] . '#services';
    $hp_projects = [0 => '#services', 1 => 'index.html#services', 2 => '../../index.html#services'][$depth];
    $about_href = [0 => 'about.html', 1 => 'about.html', 2 => '../../about.html'][$depth];
    $contact_href = [0 => '#contact', 1 => 'contact.html', 2 => '../../contact.html'][$depth];

    // Translations
    $t = [
        'en' => [
            'tagline'   => 'MT MesseStand &mdash; Your trade fair partner across three continents. Custom stands, modular systems, and full exhibitor services worldwide.',
            'standards' => 'Built to international trade fair standards.',
            'services_h'=> 'Services',
            'services'  => ['Custom Wooden', 'Maxima Modular', 'Package Upgrade', 'Country Pavilions', 'Exhibitor Services', 'Organizer Services'],
            'company_h' => 'Company',
            'company'   => ['About', 'Projects', 'Contact'],
            'lang_h'    => 'Language',
            'copyright' => '&copy; 2026 MT MesseStand. All Rights Reserved.',
        ],
        'tr' => [
            'tagline'   => 'MT MesseStand &mdash; Üç kıtadaki fuar çözüm ortağınız. Dünya çapında özel ahşap stantlar, modüler sistemler ve eksiksiz katılımcı hizmetleri.',
            'standards' => 'Uluslararası fuarcılık standartlarında üretim.',
            'services_h'=> 'Hizmetlerimiz',
            'services'  => ['Özel Ahşap Stantlar', 'Maxima Modüler', 'Stant Geliştirme', 'Ülke Pavyonları', 'Katılımcı Hizmetleri', 'Organizatör Hizmetleri'],
            'company_h' => 'Kurumsal',
            'company'   => ['Hakkımızda', 'Projelerimiz', 'İletişim'],
            'lang_h'    => 'Dil',
            'copyright' => '&copy; 2026 MT MesseStand. Tüm Hakları Saklıdır.',
        ],
        'de' => [
            'tagline'   => 'MT MesseStand &mdash; Ihr Messepartner auf drei Kontinenten. Maßgefertigte Stände, modulare Systeme und umfassende Ausstellerservices weltweit.',
            'standards' => 'Gefertigt nach internationalen Messestandards.',
            'services_h'=> 'Leistungen',
            'services'  => ['Maßanfertigungen', 'Maxima Modular', 'Paket-Upgrade', 'Länderpavillons', 'Ausstellerservice', 'Veranstalterservice'],
            'company_h' => 'Unternehmen',
            'company'   => ['Über uns', 'Projekte', 'Kontakt'],
            'lang_h'    => 'Sprache',
            'copyright' => '&copy; 2026 MT MesseStand. Alle Rechte vorbehalten.',
        ],
        'fr' => [
            'tagline'   => 'MT MesseStand &mdash; Votre partenaire salons sur trois continents. Stands sur mesure, systèmes modulaires et services exposants complets dans le monde entier.',
            'standards' => 'Fabriqué selon les normes internationales des salons.',
            'services_h'=> 'Services',
            'services'  => ['Sur Mesure', 'Maxima Modulaire', 'Pack Amélioration', 'Pavillons Nationaux', 'Services Exposants', 'Services Organisateurs'],
            'company_h' => 'Entreprise',
            'company'   => ['À Propos', 'Projets', 'Contact'],
            'lang_h'    => 'Langue',
            'copyright' => '&copy; 2026 MT MesseStand. Tous droits réservés.',
        ],
        'es' => [
            'tagline'   => 'MT MesseStand &mdash; Su socio ferial en tres continentes. Stands a medida, sistemas modulares y servicios completos para expositores en todo el mundo.',
            'standards' => 'Fabricado según estándares feriales internacionales.',
            'services_h'=> 'Servicios',
            'services'  => ['A Medida', 'Maxima Modular', 'Mejora de Paquete', 'Pabellones Nacionales', 'Servicios al Expositor', 'Servicios al Organizador'],
            'company_h' => 'Empresa',
            'company'   => ['Nosotros', 'Proyectos', 'Contacto'],
            'lang_h'    => 'Idioma',
            'copyright' => '&copy; 2026 MT MesseStand. Todos los derechos reservados.',
        ],
        'ar' => [
            'tagline'   => 'MT MesseStand &mdash; شريكك في المعارض عبر ثلاث قارات. منصات مخصصة وأنظمة معيارية وخدمات عارضين شاملة حول العالم.',
            'standards' => 'مصنوع وفقًا لمعايير المعارض الدولية.',
            'services_h'=> 'الخدمات',
            'services'  => ['منصات مخصصة', 'ماكسيما المعيارية', 'ترقية الباقة', 'أجنحة الدول', 'خدمات العارضين', 'خدمات المنظمين'],
            'company_h' => 'الشركة',
            'company'   => ['من نحن', 'المشاريع', 'اتصل بنا'],
            'lang_h'    => 'اللغة',
            'copyright' => '&copy; 2026 MT MesseStand. جميع الحقوق محفوظة.',
        ],
        'zh' => [
            'tagline'   => 'MT MesseStand &mdash; 您在三大洲的展会合作伙伴。全球定制展台、模块化系统和全方位参展商服务。',
            'standards' => '按照国际展会标准建造。',
            'services_h'=> '服务',
            'services'  => ['定制木制', 'Maxima模块化', '套餐升级', '国家馆', '参展商服务', '主办方服务'],
            'company_h' => '公司',
            'company'   => ['关于我们', '项目', '联系我们'],
            'lang_h'    => '语言',
            'copyright' => '&copy; 2026 MT MesseStand. 版权所有。',
        ],
    ];
    $ft = $t[$lang] ?? $t['en'];
    $s = $ft['services'];
    $c = $ft['company'];

    // Language badges
    $allLangs = ['en' => 'EN', 'tr' => 'TR', 'de' => 'DE', 'fr' => 'FR', 'es' => 'ES', 'ar' => 'AR', 'zh' => 'ZH'];
    $badges = '';
    foreach ($allLangs as $code => $name) {
        $active = $code === $lang ? ' bg-accent' : ' bg-dark';
        // Badge href by page_type
        if ($page_type === 'blog' || $page_type === 'blog_index') {
            $bhref = '/' . $code . '/blog/';
        } elseif ($page_type === 'about') {
            $bhref = '/' . $code . '/about/';
        } elseif ($page_type === 'contact') {
            $bhref = '/' . $code . '/contact/';
        } else {
            $bhref = '/' . $code . '/';
        }
        $badges .= '              <a href="' . $bhref . '" class="badge' . $active . ' text-white text-decoration-none px-2 py-1">' . $name . '</a>' . "\n";
    }

    return '
<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <img src="' . $ap . 'img/logo.webp" alt="MT Messe Stand" width="186" height="36" class="mb-3">
        <p>' . $ft['tagline'] . '</p>
        <p class="small">' . $ft['standards'] . ' <a href="https://www.auma.de/en" target="_blank" rel="noopener">AUMA</a> · <a href="https://www.dguv.de" target="_blank" rel="noopener">DGUV</a></p>
        <div class="social-links mt-3">
          <a href="https://www.linkedin.com/company/mt-messe-stand" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="https://www.instagram.com/mtmessestand" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>' . $ft['services_h'] . '</h5>
        <ul class="list-unstyled">
          <li><a href="' . $hp . '">' . $s[0] . '</a></li>
          <li><a href="' . $hp . '">' . $s[1] . '</a></li>
          <li><a href="' . $hp . '">' . $s[2] . '</a></li>
          <li><a href="' . $hp . '">' . $s[3] . '</a></li>
          <li><a href="' . $hp . '">' . $s[4] . '</a></li>
          <li><a href="' . $hp . '">' . $s[5] . '</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>' . $ft['company_h'] . '</h5>
        <ul class="list-unstyled">
          <li><a href="' . $about_href . '">' . $c[0] . '</a></li>
          <li><a href="' . $hp_projects . '">' . $c[1] . '</a></li>
          <li><a href="' . $contact_href . '">' . $c[2] . '</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h5>' . $ft['lang_h'] . '</h5>
        <div class="d-flex gap-2">
' . $badges . '        </div>
      </div>
    </div>
    <div class="text-center">
      <small>' . $ft['copyright'] . '</small>
    </div>
  </div>
</footer>';
}
```

- [ ] **Step 2: PHP syntax kontrolü**

Run: `php -l templates/footer.php`
Expected: "No syntax errors"

- [ ] **Step 3: Commit**

```bash
git add templates/footer.php
git commit -m "feat: add render_footer() PHP function"
```

---

### Task 3: templates/page-template.php — navbar ve footer fonksiyonlara geçiş

**Files:**
- Modify: `templates/page-template.php`

**Interfaces:**
- Consumes: `render_navbar($lang, $page_type)`, `render_footer($lang, 1, $page_type)`

- [ ] **Step 1: Navbar placeholder ve footer manual HTML'i fonksiyon çağrısıyla değiştir**

Fonksiyon başında değişkenleri hesapla:
```php
$navbar_html = render_navbar($lang, $page_type);
$footer_html = render_footer($lang, 1, $page_type);
```

Heredoc içinde:
- `<div id="navbar-placeholder"></div>` → `$navbar_html`
- `<footer class="footer">...</footer>` bloğu → `$footer_html`
- `<script src="{$asset_prefix}js/navbar.js"></script>` → kaldır

- [ ] **Step 2: footer-data.php require'ını kaldır**

Line 12: `$all_ft = require __DIR__ . '/footer-data.php';` ve sonraki `$ft`, `$s0...$s5`, `$c0...$c2` satırlarını kaldır.

- [ ] **Step 3: Render test**

```bash
php -r '
$base = "/home/nilay/projects/mtmesse";
require "$base/admin/config.php";
require "$base/templates/page-template.php";
$d = json_decode(file_get_contents("$base/data/en/about.json"), true);
$h = render_page($d, $d["content"], "en", "about");
echo strlen($h) . " bytes, navbar.js refs: " . substr_count($h, "navbar.js");
'
```
Expected: `navbar.js refs: 0` (artık PHP render ediyor)

- [ ] **Step 4: Commit**

```bash
git add templates/page-template.php
git commit -m "refactor: page-template uses render_navbar/render_footer"
```

---

### Task 4: templates/blog-template.php — navbar ve footer fonksiyonlara geçiş

**Files:**
- Modify: `templates/blog-template.php`

**Interfaces:**
- Consumes: `render_navbar($lang, 'blog_post', $slug)`, `render_footer($lang, 2, 'blog')`

- [ ] **Step 1: Aynı değişiklikleri uygula**

Fonksiyon başında:
```php
$navbar_html = render_navbar($lang, 'blog_post', $slug_esc);
$footer_html = render_footer($lang, 2, 'blog');
```

Heredoc içinde:
- `<div id="navbar-placeholder"></div>` → `$navbar_html`
- `<footer class="footer">...</footer>` bloğu → `$footer_html`
- `<script src="{$asset_prefix}js/navbar.js"></script>` → kaldır

footer-data.php require ve değişkenlerini kaldır.

- [ ] **Step 2: Render test**

```bash
php -r '
$base = "/home/nilay/projects/mtmesse";
require "$base/admin/config.php";
require "$base/templates/blog-template.php";
$meta = json_decode(file_get_contents("$base/data/en/blog/germany-hidden-costs.json"), true);
$h = render_blog($meta, $meta["content"], "en");
echo strlen($h) . " bytes";
'
```

- [ ] **Step 3: Commit**

```bash
git add templates/blog-template.php
git commit -m "refactor: blog-template uses render_navbar/render_footer"
```

---

### Task 5: build.php — navbar ve footer injection ekle

**Files:**
- Modify: `build.php`

**Interfaces:**
- Consumes: `render_navbar()`, `render_footer()`

- [ ] **Step 1: build.php başına require ekle**

```php
require_once __DIR__ . '/templates/navbar.php';
require_once __DIR__ . '/templates/footer.php';
```

- [ ] **Step 2: Her dil döngüsünde navbar ve footer inject et**

`inject()` fonksiyonundan sonra, her homepage HTML'ine:

```php
$html = str_replace('<!-- NAVBAR -->', render_navbar($lang_code, 'home'), $html);
$html = str_replace('<!-- FOOTER -->',  render_footer($lang_code, 0, 'home'), $html);
```

Aynı şekilde blog index sayfaları için:

```php
$html = str_replace('<!-- NAVBAR -->', render_navbar($lang_code, 'blog_list'), $html);
$html = str_replace('<!-- FOOTER -->',  render_footer($lang_code, 2, 'blog_index'), $html);
```

- [ ] **Step 3: Commit**

```bash
git add build.php
git commit -m "feat: build.php injects navbar and footer via render functions"
```

---

### Task 6: Ana sayfa ve blog index HTML'leri — placeholder ekle

**Files:**
- Modify: `en/index.html`, `tr/index.html`
- Modify: `en/blog/index.html`, `tr/blog/index.html`

- [ ] **Step 1: Ana sayfalarda navbar ve footer HTML'i placeholder ile değiştir**

Mevcut `<header id="header"...>...</header>` bloğunu `<!-- NAVBAR -->` ile değiştir.
Mevcut `<footer class="footer">...</footer>` bloğunu `<!-- FOOTER -->` ile değiştir.
`<script src="...navbar.js"></script>` satırını kaldır.

- [ ] **Step 2: Blog index sayfalarında aynı değişiklik**

Aynı placeholder değişimi. navbar.js referansını kaldır.

- [ ] **Step 3: Commit**

```bash
git add en/index.html tr/index.html en/blog/index.html tr/blog/index.html
git commit -m "refactor: replace manual navbar/footer with placeholders"
```

---

### Task 7: Render ve doğrulama

**Files:**
- Tüm sayfaları yeniden render et

- [ ] **Step 1: build.php çalıştır**

```bash
cd /home/nilay/projects/mtmesse && php build.php
```

- [ ] **Step 2: Template sayfaları render et**

```bash
php /tmp/render_pages.php  # about/contact
php -r '...'               # blog posts (existing script)
```

- [ ] **Step 3: Doğrulama script'i çalıştır**

```bash
cd /home/nilay/projects/mtmesse && python3 scripts/audit_links.py
```
Expected: 0 errors

- [ ] **Step 4: Her dilde navbar ve footer kontrolü**

```bash
# EN should have English navbar
curl -s http://localhost:8765/en/about.html | grep "Home\|About"
# TR should have Turkish navbar
curl -s http://localhost:8765/tr/about.html | grep "Anasayfa\|Hakkımızda"
```

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "chore: render all pages with centralized navbar/footer"
```

---

### Task 8: Temizlik — eski dosyaları kaldır

**Files:**
- Delete: `assets/js/navbar.js`
- Delete: `templates/footer-data.php`

- [ ] **Step 1: Dosyaları sil ve navbar.js referanslarını kontrol et**

```bash
rm assets/js/navbar.js
rm templates/footer-data.php
grep -r "navbar.js" --include="*.html" --include="*.php" . | grep -v node_modules
```
Expected: no results

- [ ] **Step 2: Son doğrulama**

```bash
cd /home/nilay/projects/mtmesse && python3 scripts/audit_links.py
```

- [ ] **Step 3: Commit**

```bash
git add -A
git commit -m "chore: remove deprecated navbar.js and footer-data.php"
```
