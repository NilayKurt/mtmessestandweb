<?php
$lang = 'ru';
$page_type = 'references';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/footer.php';

$refsFile = __DIR__ . '/../data/references.json';
$refs = file_exists($refsFile) ? json_decode(file_get_contents($refsFile), true) : [];
if (!is_array($refs)) $refs = [];

usort($refs, function($a, $b) { return ($a['position'] ?? 999) - ($b['position'] ?? 999); });

$total = count($refs);

$navbar = render_navbar('ru', 'references');
$footer = render_footer('ru', 1, 'references');

// OG Image: first portfolio image
$ogImage = '';
foreach ($refs as $r) {
    if (!empty($r['src'])) { $ogImage = 'https://mtmessestand.com' . $r['src']; break; }
}

// Build image cards
$cards = '';
$imageObjects = [];
$imgIdx = 0;
foreach ($refs as $ref) {
    $src = htmlspecialchars($ref['src'] ?? '');
    $alt = htmlspecialchars($ref['alt_ru'] ?? $ref['alt'] ?? 'MT Messe Stand exhibition stand reference');
    if (empty($src)) continue;
    $imgIdx++;
    $cards .= <<<HTML
      <div class="col-lg-3 col-md-4 col-6" role="listitem">
        <a href="{$src}" class="glightbox portfolio-item d-block" data-gallery="references" data-glightbox="width:80vw;" aria-label="View enlarged reference image">
          <img src="{$src}" alt="{$alt}" class="img-fluid rounded-3 shadow-sm" loading="lazy">
        </a>
      </div>
HTML;
    // ImageObject schema for first 10
    if ($imgIdx <= 10) {
        $imageObjects[] = [
            '@type' => 'ImageObject',
            'contentUrl' => 'https://mtmessestand.com' . ($ref['src'] ?? ''),
            'license' => 'https://mtmessestand.com/rights/',
            'acquireLicensePage' => 'https://mtmessestand.com/contact.html',
            'creditText' => 'MT Messe Stand',
            'creator' => ['@type' => 'Organization', 'name' => 'MT Messe Stand'],
            'copyrightNotice' => '© MT Messe Stand',
            'caption' => $ref['alt_ru'] ?? '',
        ];
    }
}

// Pagination
if (empty($cards)) {
    $cards = '<div class="col-12 text-center py-5"><p class="text-muted">Галерея готовится к показу.</p></div>';
}

// Build schema
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'name' => 'Exhibition Stand References | MT Messe Stand',
            'description' => 'Portfolio gallery of custom exhibition stands, modular booths, and country pavilions built across 28+ countries.',
            'url' => 'https://mtmessestand.com/ru/references/',
            'inLanguage' => 'en',
            'isAccessibleForFree' => true,
            'publisher' => ['@type' => 'Organization', 'name' => 'MT Messe Stand', 'url' => 'https://mtmessestand.com'],
            'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => $imageObjects],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://mtmessestand.com/en/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'References', 'item' => 'https://mtmessestand.com/ru/references/'],
            ],
        ],
    ],
];
$schema_json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Портфолио — Выставочные Стенды | MT Messe Stand</title>
  <meta name="description" content="Портфолио выставочных стендов MT Messe Stand — более 300 стендов в 28+ странах. — 300+ custom wooden, Maxima modular, and country pavilion stands built for clients across 28+ countries. Real projects, real results.">
  <meta name="author" content="MT Messe Stand">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://mtmessestand.com/ru/references/">
  <link rel="alternate" hreflang="x-default" href="https://mtmessestand.com/ru/references/">
  <link rel="alternate" hreflang="en" href="https://mtmessestand.com/ru/references/">
  <link rel="alternate" hreflang="tr" href="https://mtmessestand.com/tr/referanslar/">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <link rel="apple-touch-icon" href="../assets/img/favicon.png">

  <meta property="og:title" content="Портфолио — Выставочные Стенды | MT Messe Stand">
  <meta property="og:description" content="Более 300 стендов в 28+ странах. Custom wooden, modular Maxima, and country pavilion projects.">
  <?php if ($ogImage): ?>
  <meta property="og:image" content="<?= $ogImage ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <?php endif; ?>
  <meta property="og:url" content="https://mtmessestand.com/ru/references/">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="MT Messe Stand">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Портфолио | MT Messe Stand">
  <meta name="twitter:description" content="Более 300 стендов в 28+ странах. Custom wooden, modular, pavilion projects.">
  <?php if ($ogImage): ?>
  <meta name="twitter:image" content="<?= $ogImage ?>">
  <?php endif; ?>

  <script type="application/ld+json">
  <?= $schema_json ?>
  </script>

  <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/glightbox.min.css" rel="stylesheet">
  <link href="../assets/css/style.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <style>
    .portfolio-item { overflow: hidden; border-radius: 0.5rem; transition: transform 0.3s ease; }
    .portfolio-item:hover { transform: scale(1.03); }
    .portfolio-item img { display: block; width: 100%; object-fit: contain; background: #f8f9fa; }
  </style>
</head>
<body>
<?= $navbar ?>

<main style="padding-top:65px">
<section class="section">
  <div class="container">
    <div class="section-title" data-aos="fade-up">
      <h1>Портфолио Выставочных Стендов</h1>
      <p class="lead">Каждый стенд — это история партнерства, мастерства и выхода на новые рынки. От Стамбула до Франкфурта, от Дубая до Москвы.</p>
    </div>

    <div class="row mb-5" data-aos="fade-up">
      <div class="col-lg-6">
        <h2>Стенды, Которые Открывают Рынки</h2>
        <p>С 2010 года MT Messe Stand построила более <strong>300 выставочных стендов</strong> в <strong>28+ странах</strong>. Our clients range from Japanese automotive manufacturers to German industrial exporters, Italian design brands to Chinese technology firms. Each project is a partnership — we handle design, production, logistics, installation, and dismantling, so you focus on making connections.</p>
      </div>
      <div class="col-lg-6">
        <h2>Довольные Клиенты, Крепкие Партнерства</h2>
        <p>Наши клиенты возвращаются каждый год — доступные цены, установка точно в срок, поддержка WhatsApp на протяжении всей выставки. Many of our customers have expanded into new markets using the connections they made in our stands — and that's the real measure of our work.</p>
      </div>
    </div>

    <div class="row g-4" data-aos="fade-up">
      <?= $cards ?>
    </div>

  </div>
</section>
</main>

<?= $footer ?>
<script src="../assets/vendor/bootstrap.bundle.min.js" defer></script>
<script src="../assets/vendor/glightbox.min.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
  });
</script>
<script src="../assets/js/main.min.js" defer></script>
</body>
</html>
