<?php
$lang = 'tr';
$page_type = 'references';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/footer.php';

$refsFile = __DIR__ . '/../data/references.json';
$refs = file_exists($refsFile) ? json_decode(file_get_contents($refsFile), true) : [];
if (!is_array($refs)) $refs = [];

usort($refs, function($a, $b) { return ($a['position'] ?? 999) - ($b['position'] ?? 999); });

$total = count($refs);

$navbar = render_navbar('tr', 'references');
$footer = render_footer('tr', 1, 'references');

$ogImage = '';
foreach ($refs as $r) {
    if (!empty($r['src'])) { $ogImage = 'https://mtmessestand.com' . $r['src']; break; }
}

$cards = '';
$imageObjects = [];
$imgIdx = 0;
foreach ($refs as $ref) {
    $src = htmlspecialchars($ref['src'] ?? '');
    $alt = htmlspecialchars($ref['alt_tr'] ?? $ref['alt_en'] ?? $ref['alt'] ?? 'MT Messe Stand fuar standı referansı');
    if (empty($src)) continue;
    $imgIdx++;
    $cards .= <<<HTML
      <div class="col-lg-3 col-md-4 col-6" role="listitem">
        <a href="{$src}" class="glightbox portfolio-item d-block" data-gallery="references" data-glightbox="width:80vw;" aria-label="Büyütülmüş referans görseli">
          <img src="{$src}" alt="{$alt}" class="img-fluid rounded-3 shadow-sm" width="400" height="300" loading="lazy">
        </a>
      </div>
HTML;
    if ($imgIdx <= 10) {
        $imageObjects[] = [
            '@type' => 'ImageObject',
            'contentUrl' => 'https://mtmessestand.com' . ($ref['src'] ?? ''),
            'license' => 'https://mtmessestand.com/rights/',
            'acquireLicensePage' => 'https://mtmessestand.com/contact.html',
            'creditText' => 'MT Messe Stand',
            'creator' => ['@type' => 'Organization', 'name' => 'MT Messe Stand'],
            'copyrightNotice' => '© MT Messe Stand',
            'caption' => $ref['alt_tr'] ?? '',
        ];
    }
}

if (empty($cards)) {
    $cards = '<div class="col-12 text-center py-5"><p class="text-muted">Referans galerimiz hazırlanıyor. Yakında burada olacak.</p></div>';
}

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'name' => 'Fuar Standı Referansları | MT Messe Stand',
            'description' => '28+ ülkede inşa edilen özel fuar standları, modüler sistemler ve ülke pavyonları portföy galerisi.',
            'url' => 'https://mtmessestand.com/tr/referanslar/',
            'inLanguage' => 'tr',
            'isAccessibleForFree' => true,
            'publisher' => ['@type' => 'Organization', 'name' => 'MT Messe Stand', 'url' => 'https://mtmessestand.com'],
            'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => $imageObjects],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => 'https://mtmessestand.com/tr/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Referanslar', 'item' => 'https://mtmessestand.com/tr/referanslar/'],
            ],
        ],
    ],
];
$schema_json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fuar Standı Portföyü — Referanslar | MT Messe Stand</title>
  <meta name="description" content="MT Messe Stand fuar standı portföyü — 28+ ülkede 300+ özel ahşap, Maxima modüler ve ülke pavyonu standı. Gerçek projeler, gerçek sonuçlar.">
  <meta name="author" content="MT Messe Stand">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://mtmessestand.com/tr/referanslar/">
  <link rel="alternate" hreflang="x-default" href="https://mtmessestand.com/en/references/">
  <link rel="alternate" hreflang="en" href="https://mtmessestand.com/en/references/">
  <link rel="alternate" hreflang="tr" href="https://mtmessestand.com/tr/referanslar/">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <link rel="apple-touch-icon" href="../assets/img/favicon.png">

  <meta property="og:title" content="Fuar Standı Portföyü — Referanslar | MT Messe Stand">
  <meta property="og:description" content="28+ ülkede 300+ stand. Özel ahşap, modüler Maxima ve ülke pavyonu projelerimiz.">
  <?php if ($ogImage): ?>
  <meta property="og:image" content="<?= $ogImage ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <?php endif; ?>
  <meta property="og:url" content="https://mtmessestand.com/tr/referanslar/">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="MT Messe Stand">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Fuar Standı Portföyü | MT Messe Stand">
  <meta name="twitter:description" content="28+ ülkede 300+ stand. Özel, modüler ve pavyon projeleri.">
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
      <h1>Fuar Standı Portföyümüz</h1>
      <p class="lead">Her stand bir ortaklık, zanaat ve yeni pazarlara açılma hikayesidir. İstanbul'dan Frankfurt'a, Dubai'den Moskova'ya — sadece stand değil, varlığınızı inşa ediyoruz.</p>
    </div>

    <div class="row mb-5" data-aos="fade-up">
      <div class="col-lg-6">
        <h2>Pazarlara Açılan Standlar</h2>
        <p>2010'dan bu yana <strong>28+ ülkede 300+ özel fuar standı</strong> inşa ettik. Japon otomotiv üreticilerinden Alman sanayi ihracatçılarına, İtalyan tasarım markalarından Çinli teknoloji firmalarına kadar geniş bir müşteri portföyüne hizmet verdik. Her proje bir ortaklıktır — tasarım, üretim, lojistik, kurulum ve sökümü biz hallederiz, siz bağlantılarınıza odaklanırsınız.</p>
      </div>
      <div class="col-lg-6">
        <h2>Mutlu İş İnsanları, Güçlü Ortaklıklar</h2>
        <p>Müşterilerimiz her yıl bize geri döner çünkü üç sözümüzü tutarız: kaliteden ödün vermeden <strong>uygun fiyat</strong>, dünyanın her yerinde <strong>zamanında kurulum</strong> ve fuar boyunca <strong>WhatsApp destek hattı</strong>. Birçok müşterimiz bizim standlarımızda kurduğu bağlantılarla yeni pazarlara açıldı — işte işimizin gerçek ölçüsü budur.</p>
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
