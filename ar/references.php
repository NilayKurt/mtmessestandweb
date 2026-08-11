<?php
$lang = 'ar';
$page_type = 'references';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/footer.php';

$refsFile = __DIR__ . '/../data/references.json';
$refs = file_exists($refsFile) ? json_decode(file_get_contents($refsFile), true) : [];
if (!is_array($refs)) $refs = [];

usort($refs, function($a, $b) { return ($a['position'] ?? 999) - ($b['position'] ?? 999); });

$total = count($refs);

$navbar = render_navbar('ar', 'references');
$footer = render_footer('ar', 1, 'references');

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
    $alt = htmlspecialchars($ref['alt_ar'] ?? $ref['alt'] ?? 'MT Messe Stand exhibition stand reference');
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
            'caption' => $ref['alt_ar'] ?? '',
        ];
    }
}

// Pagination
if (empty($cards)) {
    $cards = '<div class="col-12 text-center py-5"><p class="text-muted">معرض المراجع قيد التحضير.</p></div>';
}

// Build schema
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'name' => 'Exhibition Stand References | MT Messe Stand',
            'description' => 'Portfolio gallery of custom exhibition stands, modular booths, and country pavilions built across 28+ countries.',
            'url' => 'https://mtmessestand.com/ar/references/',
            'inLanguage' => 'en',
            'isAccessibleForFree' => true,
            'publisher' => ['@type' => 'Organization', 'name' => 'MT Messe Stand', 'url' => 'https://mtmessestand.com'],
            'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => $imageObjects],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://mtmessestand.com/en/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'References', 'item' => 'https://mtmessestand.com/ar/references/'],
            ],
        ],
    ],
];
$schema_json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?><!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>المراجع — محفظة الأجنحة | MT Messe Stand</title>
  <meta name="description" content="محفظة أجنحة MT Messe Stand — أكثر من 300 جناح خشبي ونظام ماكسيما المعياري في 28+ دولة. — 300+ custom wooden, Maxima modular, and country pavilion stands built for clients across 28+ countries. Real projects, real results.">
  <meta name="author" content="MT Messe Stand">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://mtmessestand.com/ar/references/">
  <link rel="alternate" hreflang="x-default" href="https://mtmessestand.com/ar/references/">
  <link rel="alternate" hreflang="en" href="https://mtmessestand.com/ar/references/">
  <link rel="alternate" hreflang="tr" href="https://mtmessestand.com/tr/referanslar/">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <link rel="apple-touch-icon" href="../assets/img/favicon.png">

  <meta property="og:title" content="المراجع — محفظة الأجنحة | MT Messe Stand">
  <meta property="og:description" content="محفظة أجنحة MT Messe Stand — أكثر من 300 جناح خشبي ونظام ماكسيما المعياري في 28+ دولة.">
  <?php if ($ogImage): ?>
  <meta property="og:image" content="<?= $ogImage ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <?php endif; ?>
  <meta property="og:url" content="https://mtmessestand.com/ar/references/">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="MT Messe Stand">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="المراجع — محفظة الأجنحة | MT Messe Stand">
  <meta name="twitter:description" content="محفظة أجنحة MT Messe Stand — أكثر من 300 جناح خشبي ونظام ماكسيما المعياري في 28+ دولة.">
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
      <h1>محفظة أجنحة المعارض</h1>
      <p class="lead">كل جناح يروي قصة شراكة وحرفية ودخول إلى السوق.</p>
    </div>

    <div class="row mb-5" data-aos="fade-up">
      <div class="col-lg-6">
        <h2>أجنحة تفتح الأسواق</h2>
        <p>منذ عام 2010، قامت MT Messe Stand ببناء أكثر من 300 جناح مخصص في 28+ دولة.</p>
      </div>
      <div class="col-lg-6">
        <h2>عملاء سعداء، شراكات قوية</h2>
        <p>يعود عملاؤنا كل عام — أسعار معقولة، تركيب في الوقت المحدد، دعم واتساب.</p>
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
