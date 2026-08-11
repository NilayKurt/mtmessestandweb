<?php
$lang = 'tr';
$page_type = 'references';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/footer.php';

// Read references data
$refsFile = __DIR__ . '/../data/references.json';
$refs = file_exists($refsFile) ? json_decode(file_get_contents($refsFile), true) : [];
if (!is_array($refs)) $refs = [];

// Sort by position
usort($refs, function($a, $b) { return ($a['position'] ?? 999) - ($b['position'] ?? 999); });

$perPage = 20;
$total = count($refs);
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$items = array_slice($refs, $offset, $perPage);

$navbar = render_navbar('tr', 'references');
$footer = render_footer('tr', 1, 'references');

$cards = '';
foreach ($items as $ref) {
    $src = htmlspecialchars($ref['src'] ?? '');
    $alt = htmlspecialchars($ref['alt_tr'] ?? $ref['alt_en'] ?? $ref['alt'] ?? 'MT Messe Stand fuar standı referansı');
    if (empty($src)) continue;
    $cards .= <<<HTML
      <div class="col-lg-3 col-md-4 col-6">
        <a href="{$src}" class="glightbox portfolio-item d-block" data-gallery="references" data-glightbox="width:80vw;">
          <img src="{$src}" alt="{$alt}" class="img-fluid rounded-3 shadow-sm" width="400" height="300" loading="lazy">
        </a>
      </div>
HTML;
}

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

if (empty($cards)) {
    $cards = '<div class="col-12 text-center py-5"><p class="text-muted">Referans galerimiz hazırlanıyor. Yakında burada olacak.</p></div>';
}
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Referanslar | MT Messe Stand</title>
  <meta name="description" content="MT Messe Stand fuar standı referansları — 28'den fazla ülkede müşterilerimiz için inşa ettiğimiz özel ahşap ve modüler standlar. En iyi fuar projelerimizin portföy galerisi.">
  <meta name="author" content="MT Messe Stand">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://mtmessestand.com/tr/referanslar/">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <link rel="apple-touch-icon" href="../assets/img/favicon.png">

  <meta property="og:title" content="Referanslar | MT Messe Stand">
  <meta property="og:description" content="Fuar standı portföyü — dünya çapında müşterilerimiz için inşa ettiğimiz özel ahşap ve modüler standlar.">
  <meta property="og:url" content="https://mtmessestand.com/tr/referanslar/">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="MT Messe Stand">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Referanslar | MT Messe Stand">
  <meta name="twitter:description" content="Fuar standı portföyü — dünya çapında müşterilerimiz için inşa ettiğimiz özel ahşap ve modüler standlar.">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "Referanslar | MT Messe Stand",
    "description": "Fuar standı portföy galerisi",
    "url": "https://mtmessestand.com/tr/referanslar/",
    "inLanguage": "tr",
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
    .portfolio-item img { display: block; width: 100%; height: 100%; object-fit: cover; aspect-ratio: 4/3; }
  </style>
</head>
<body>
<?= $navbar ?>

<main style="padding-top:65px">
<section class="section">
  <div class="container">
    <div class="section-title" data-aos="fade-up">
      <h1>Referanslarımız</h1>
      <p>Dünya çapında fuarlarda yaptığımız işleri inceleyin. Özel standlar, modüler sistemler ve ülke pavyonları — 28'den fazla ülkedeki müşterilerimiz için inşa edildi.</p>
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
<script src="../assets/js/main.min.js"></script>
</body>
</html>
