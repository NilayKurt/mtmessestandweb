<?php
require_once __DIR__ . '/../admin/config.php';

function render_page(array $meta, string $content_html, string $lang, string $page_type): string {
    $title_esc = htmlspecialchars($meta['title'] ?? '');
    $meta_desc = htmlspecialchars(substr($meta['meta_desc'] ?? $title_esc, 0, 160));
    $full_title = "$title_esc | MT Messe Stand";
    $canonical = SITE_URL . "/$lang/$page_type/";
    $asset_prefix = '../assets/';

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
  <link rel="icon" type="image/png" href="{$asset_prefix}img/favicon.webp">
  <link rel="apple-touch-icon" href="{$asset_prefix}img/favicon.webp">

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
    <h1 class="mb-4">$title_esc</h1>
    <div class="page-content">$content_html</div>
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
          <li><a href="index.html#services">Wooden Stands</a></li>
          <li><a href="index.html#services">Maxima Stands</a></li>
          <li><a href="index.html#services">Package Stands</a></li>
          <li><a href="index.html#services">Pavilion Solutions</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>Company</h5>
        <ul class="list-unstyled">
          <li><a href="about.html">About</a></li>
          <li><a href="blog/">Blog</a></li>
          <li><a href="contact.html">Contact</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h5>Language</h5>
        <div class="d-flex gap-2 flex-wrap">
HTML;
    $footer_langs = '';
    foreach (LANGUAGES as $code => $name) {
        $active = $code === $lang ? ' bg-accent' : ' bg-secondary';
        $page_href = ($page_type === 'about') ? "/$code/about/" : "/$code/contact/";
        $footer_langs .= "          <a href=\"$page_href\" class=\"badge{$active} text-white text-decoration-none px-2 py-1\">" . strtoupper($code) . "</a>\n";
    }
    $footer_html = $footer_langs . <<<HTML
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
