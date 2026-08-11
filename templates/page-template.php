<?php
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/navbar.php';
require_once __DIR__ . '/footer.php';

function render_page(array $meta, string $content_html, string $lang, string $page_type): string {
    $title_esc = htmlspecialchars($meta['title'] ?? '');
    $meta_desc = htmlspecialchars(substr($meta['meta_desc'] ?? $title_esc, 0, 160));
    $full_title = "$title_esc | MT Messe Stand";
    $canonical = SITE_URL . "/$lang/$page_type/";
    $asset_prefix = '../assets/';
    $navbar_html = render_navbar($lang, $page_type);
    $footer_html = render_footer($lang, 1, $page_type);

    // About image (rendered by template, not in JSON content)
    $about_image = '';
    if ($page_type === 'about') {
        $about_image = '
    <div class="row mt-5"><div class="col-lg-6"></div><div class="col-lg-6 ps-lg-4 text-end">
      <img src="' . $asset_prefix . 'img/about.webp" alt="MT Messe Stand" class="img-fluid rounded-4 shadow" width="400" height="204" loading="lazy">
    </div></div>';
    }

    $html = <<<HTML
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
  <link rel="icon" type="image/png" href="{$asset_prefix}img/favicon.png">
  <link rel="apple-touch-icon" href="{$asset_prefix}img/favicon.png">
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
$navbar_html
<main style="padding-top:52px">
<section class="section">
  <div class="container">
    <h1 class="mb-4">$title_esc</h1>
    <div class="page-content">$content_html</div>
    $about_image
  </div>
</section>
</main>
$footer_html
<script src="{$asset_prefix}vendor/bootstrap.bundle.min.js"></script>
<script src="{$asset_prefix}js/main.min.js"></script>
</body>
</html>
HTML;
    return $html;
}
