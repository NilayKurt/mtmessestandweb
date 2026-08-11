<?php
$lang = 'en';
$page_type = 'references';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/footer.php';

// Read references data
$refsFile = __DIR__ . '/../data/references.json';
$refs = file_exists($refsFile) ? json_decode(file_get_contents($refsFile), true) : [];
if (!is_array($refs)) $refs = [];

// Sort by position
usort($refs, function($a, $b) { return ($a['position'] ?? 999) - ($b['position'] ?? 999); });

// Pagination: 20 per page
$perPage = 20;
$total = count($refs);
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$items = array_slice($refs, $offset, $perPage);

$navbar = render_navbar('en', 'references');
$footer = render_footer('en', 1, 'references');

// Build image cards
$cards = '';
foreach ($items as $ref) {
    $src = htmlspecialchars($ref['src'] ?? '');
    $alt = htmlspecialchars($ref['alt_en'] ?? $ref['alt'] ?? 'MT Messe Stand exhibition stand reference');
    if (empty($src)) continue;
    $cards .= <<<HTML
      <div class="col-lg-3 col-md-4 col-6">
        <a href="{$src}" class="glightbox portfolio-item d-block" data-gallery="references" data-glightbox="width:80vw;">
          <img src="{$src}" alt="{$alt}" class="img-fluid rounded-3 shadow-sm" width="400" height="300" loading="lazy">
        </a>
      </div>
HTML;
}

// Pagination HTML
$pagination = '';
if ($total > $perPage) {
    $totalPages = ceil($total / $perPage);
    $pagination = '<nav class="mt-5"><ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $page ? ' active' : '';
        $pagination .= "<li class=\"page-item{$active}\"><a class=\"page-link\" href=\"?page={$i}\">{$i}</a></li>";
    }
    $pagination .= '</ul></nav>';
}

// Empty state
if (empty($cards)) {
    $cards = '<div class="col-12 text-center py-5"><p class="text-muted">Our reference gallery is being prepared. Check back soon.</p></div>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>References | MT Messe Stand</title>
  <meta name="description" content="MT Messe Stand exhibition stand references — custom wooden and modular stands built for clients across 28+ countries. Portfolio gallery of our best trade fair projects.">
  <meta name="author" content="MT Messe Stand">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://mtmessestand.com/en/references/">
  <link rel="alternate" hreflang="x-default" href="https://mtmessestand.com/en/references/">
  <link rel="alternate" hreflang="en" href="https://mtmessestand.com/en/references/">
  <link rel="alternate" hreflang="tr" href="https://mtmessestand.com/tr/referanslar/">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <link rel="apple-touch-icon" href="../assets/img/favicon.png">

  <meta property="og:title" content="References | MT Messe Stand">
  <meta property="og:description" content="Exhibition stand portfolio — custom wooden and modular stands built for clients worldwide.">
  <meta property="og:url" content="https://mtmessestand.com/en/references/">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="MT Messe Stand">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="References | MT Messe Stand">
  <meta name="twitter:description" content="Exhibition stand portfolio — custom wooden and modular stands built for clients worldwide.">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "References | MT Messe Stand",
    "description": "Exhibition stand portfolio gallery",
    "url": "https://mtmessestand.com/en/references/",
    "inLanguage": "en",
    "isAccessibleForFree": true,
    "publisher": { "@type": "Organization", "name": "MT Messe Stand", "url": "https://mtmessestand.com" }
  }
  </script>

  <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/glightbox.min.css" rel="stylesheet">
  <link href="../assets/css/style.min.css" rel="stylesheet">
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
      <h1>References</h1>
      <p>Take a look at our work across exhibitions worldwide. Custom stands, modular systems, and country pavilions — built for clients from 28+ countries.</p>
    </div>

    <div class="row g-4" data-aos="fade-up">
      <?= $cards ?>
    </div>

    <?= $pagination ?>
  </div>
</section>
</main>

<?= $footer ?>
<script src="../assets/vendor/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/glightbox.min.js"></script>
<script>
  const lightbox = GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
</script>
<script src="../assets/js/main.min.js"></script>
</body>
</html>
