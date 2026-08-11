<?php
$lang = 'en';
$page_type = 'references';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/footer.php';

$refsFile = __DIR__ . '/../data/references.json';
$refs = file_exists($refsFile) ? json_decode(file_get_contents($refsFile), true) : [];
if (!is_array($refs)) $refs = [];

usort($refs, function($a, $b) { return ($a['position'] ?? 999) - ($b['position'] ?? 999); });

$perPage = 20;
$total = count($refs);
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$items = array_slice($refs, $offset, $perPage);

$navbar = render_navbar('en', 'references');
$footer = render_footer('en', 1, 'references');

// OG Image: first portfolio image
$ogImage = '';
foreach ($refs as $r) {
    if (!empty($r['src'])) { $ogImage = 'https://mtmessestand.com' . $r['src']; break; }
}

// Build image cards
$cards = '';
$imageObjects = [];
$imgIdx = 0;
foreach ($items as $ref) {
    $src = htmlspecialchars($ref['src'] ?? '');
    $alt = htmlspecialchars($ref['alt_en'] ?? $ref['alt'] ?? 'MT Messe Stand exhibition stand reference');
    if (empty($src)) continue;
    $imgIdx++;
    $cards .= <<<HTML
      <div class="col-lg-3 col-md-4 col-6" role="listitem">
        <a href="{$src}" class="glightbox portfolio-item d-block" data-gallery="references" data-glightbox="width:80vw;" aria-label="View enlarged reference image">
          <img src="{$src}" alt="{$alt}" class="img-fluid rounded-3 shadow-sm" width="400" height="300" loading="lazy">
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
            'caption' => $ref['alt_en'] ?? '',
        ];
    }
}

// Pagination
$pagination = '';
if ($total > $perPage) {
    $totalPages = ceil($total / $perPage);
    $pagination = '<nav class="mt-5" aria-label="Page navigation"><ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $page ? ' active' : '';
        $pagination .= "<li class=\"page-item{$active}\"><a class=\"page-link\" href=\"?page={$i}\">{$i}</a></li>";
    }
    $pagination .= '</ul></nav>';
}

if (empty($cards)) {
    $cards = '<div class="col-12 text-center py-5"><p class="text-muted">Our reference gallery is being prepared. Check back soon.</p></div>';
}

// Build schema
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'name' => 'Exhibition Stand References | MT Messe Stand',
            'description' => 'Portfolio gallery of custom exhibition stands, modular booths, and country pavilions built across 28+ countries.',
            'url' => 'https://mtmessestand.com/en/references/',
            'inLanguage' => 'en',
            'isAccessibleForFree' => true,
            'publisher' => ['@type' => 'Organization', 'name' => 'MT Messe Stand', 'url' => 'https://mtmessestand.com'],
            'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => $imageObjects],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://mtmessestand.com/en/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'References', 'item' => 'https://mtmessestand.com/en/references/'],
            ],
        ],
    ],
];
$schema_json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Exhibition Stand Portfolio — References | MT Messe Stand</title>
  <meta name="description" content="Browse our exhibition stand portfolio — 300+ custom wooden, Maxima modular, and country pavilion stands built for clients across 28+ countries. Real projects, real results.">
  <meta name="author" content="MT Messe Stand">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://mtmessestand.com/en/references/">
  <link rel="alternate" hreflang="x-default" href="https://mtmessestand.com/en/references/">
  <link rel="alternate" hreflang="en" href="https://mtmessestand.com/en/references/">
  <link rel="alternate" hreflang="tr" href="https://mtmessestand.com/tr/referanslar/">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <link rel="apple-touch-icon" href="../assets/img/favicon.png">

  <meta property="og:title" content="Exhibition Stand Portfolio — References | MT Messe Stand">
  <meta property="og:description" content="300+ stands built across 28+ countries. Custom wooden, modular Maxima, and country pavilion projects.">
  <?php if ($ogImage): ?>
  <meta property="og:image" content="<?= $ogImage ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <?php endif; ?>
  <meta property="og:url" content="https://mtmessestand.com/en/references/">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="MT Messe Stand">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Exhibition Stand Portfolio | MT Messe Stand">
  <meta name="twitter:description" content="300+ stands across 28+ countries. Custom wooden, modular, pavilion projects.">
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
    .portfolio-item img { display: block; width: 100%; height: 100%; object-fit: contain; background: #f8f9fa; }
  </style>
</head>
<body>
<?= $navbar ?>

<main style="padding-top:65px">
<section class="section">
  <div class="container">
    <div class="section-title" data-aos="fade-up">
      <h1>Exhibition Stand Portfolio — Our References</h1>
      <p class="lead">Each stand tells a story of partnership, craftsmanship, and market entry. From Istanbul to Frankfurt, Dubai to Moscow — we build more than stands. We build your presence.</p>
    </div>

    <div class="row mb-5" data-aos="fade-up">
      <div class="col-lg-6">
        <h2>Stands That Open Markets</h2>
        <p>Since 2010, MT Messe Stand has delivered over <strong>300 custom exhibition stands</strong> across <strong>28+ countries</strong>. Our clients range from Japanese automotive manufacturers to German industrial exporters, Italian design brands to Chinese technology firms. Each project is a partnership — we handle design, production, logistics, installation, and dismantling, so you focus on making connections.</p>
      </div>
      <div class="col-lg-6">
        <h2>Happy Business Owners, Strong Partnerships</h2>
        <p>Our clients return year after year because we deliver on three promises: <strong>affordable pricing</strong> without quality compromise, <strong>on-time installation</strong> at any venue worldwide, and <strong>dedicated WhatsApp support</strong> throughout your fair. Many of our customers have expanded into new markets using the connections they made in our stands — and that's the real measure of our work.</p>
      </div>
    </div>

    <div class="row g-4" data-aos="fade-up">
      <?= $cards ?>
    </div>

    <?= $pagination ?>
  </div>
</section>
</main>

<?= $footer ?>
<script src="../assets/vendor/bootstrap.bundle.min.js" defer></script>
<script src="../assets/vendor/glightbox.min.js" defer></script>
<script defer>
  const lightbox = GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
</script>
<script src="../assets/js/main.min.js" defer></script>
</body>
</html>
