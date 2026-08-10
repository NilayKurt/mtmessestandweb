<?php
require_once __DIR__ . '/../admin/config.php';

function render_blog(array $meta, string $content_html, string $lang): string {
    $title_esc = htmlspecialchars($meta['title'] ?? '');
    $summary_esc = htmlspecialchars($meta['summary'] ?? '');
    $image_esc = htmlspecialchars($meta['image'] ?? '');
    $date_esc = htmlspecialchars($meta['date'] ?? date('Y-m-d'));
    $slug_esc = htmlspecialchars($meta['slug'] ?? '');
    $meta_desc = htmlspecialchars(substr($meta['meta_desc'] ?? $summary_esc, 0, 160));
    $full_title = "$title_esc | MT Messe Stand";
    $canonical = SITE_URL . "/$lang/blog/$slug_esc/";
    $image_url = SITE_URL . $image_esc;

    // Cross-language hreflang
    $hreflang_html = "  <link rel=\"alternate\" hreflang=\"$lang\" href=\"$canonical\">\n";
    $hreflang_html .= "  <link rel=\"alternate\" hreflang=\"x-default\" href=\"" . SITE_URL . "/en/blog/$slug_esc/\">\n";

    // FAQPage schema auto-extract
    $faq_json = '';
    if (preg_match_all('/<details>\s*<summary>([^<]+)<\/summary>\s*<p>([^<]+)<\/p>\s*<\/details>/s', $content_html, $faq_matches, PREG_SET_ORDER)) {
        $faq_items = [];
        foreach ($faq_matches as $m) {
            $q = htmlspecialchars(trim($m[1]), ENT_QUOTES);
            $a = htmlspecialchars(trim($m[2]), ENT_QUOTES);
            $faq_items[] = '{"@type":"Question","name":"' . $q . '","acceptedAnswer":{"@type":"Answer","text":"' . $a . '"}}';
        }
        $faq_json = ",\n  <script type=\"application/ld+json\">\n  {\n    \"@context\": \"https://schema.org\",\n    \"@type\": \"FAQPage\",\n    \"mainEntity\": [" . implode(",", $faq_items) . "]\n  }\n  </script>";
    }

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
    .blog-article blockquote { background: rgba(204,0,0,0.04); border-left: 4px solid #cc0000; padding: 16px 20px; margin: 1.5rem 0; border-radius: 0 8px 8px 0; font-size: 0.95rem; color: #555; }
    .blog-article ul, .blog-article ol { margin-bottom: 1.2rem; }
    .blog-article .author-box { background: #f8f9fa; border-radius: 8px; padding: 24px; display: flex; gap: 16px; align-items: center; margin: 3rem 0 1rem; }
    .blog-article .author-box img { width: 56px; height: 56px; border-radius: 50%; }
    .blog-article .faq-section details { margin-bottom: 8px; }
    .blog-article .faq-section summary { font-weight: 600; cursor: pointer; padding: 8px 0; }
    @media (max-width: 768px) { .blog-article h1 { font-size: 1.6rem; } .blog-article { padding-top: 100px; } }
  </style>
</head>
<body>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
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
        <div><div class="author-name fw-bold">MT Messe Stand Editor</div><p class="m-0 small text-muted">Exhibition stand design and construction since 2010. 300+ stands built across 15 countries.</p></div>
      </div>
    </article>
  </div>
</section>
</main>
<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <img src="{$asset_prefix}img/logo.webp" alt="MT Messe Stand — Exhibition Stand Builder" width="186" height="36" class="mb-3">
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
HTML;
    $footer_langs = '';
    foreach (LANGUAGES as $code => $name) {
        $active = $code === $lang ? ' bg-accent' : ' bg-secondary';
        $footer_langs .= "          <a href=\"/$code/blog/\" class=\"badge{$active} text-white text-decoration-none px-2 py-1\">" . strtoupper($code) . "</a>\n";
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
