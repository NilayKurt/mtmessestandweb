<?php
function render_navbar(string $lang, string $page_type, string $slug = ''): string {
    // Menu labels per language
    $labels = [
        'en' => ['Home', 'About', 'Blog', 'References', 'Contact'],
        'tr' => ['Anasayfa', 'Hakkımızda', 'Blog', 'Referanslar', 'İletişim'],
        'de' => ['Start', 'Über uns', 'Blog', 'Referenzen', 'Kontakt'],
        'fr' => ['Accueil', 'À propos', 'Blog', 'Références', 'Contact'],
        'es' => ['Inicio', 'Nosotros', 'Blog', 'Referencias', 'Contacto'],
        'ar' => ['الرئيسية', 'من نحن', 'المدونة', 'مراجع', 'اتصل بنا'],
        'zh' => ['首页', '关于我们', '博客', '参考', '联系我们'],
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

    // === AUTO-BUILD blog slug map from JSON data files ===
    $blogMap = [];
    $dataBase = __DIR__ . '/../data';
    foreach (['en', 'tr', 'de', 'fr', 'es', 'ar', 'zh'] as $lc) {
        $blogDir = "$dataBase/$lc/blog";
        if (!is_dir($blogDir)) continue;
        foreach (glob("$blogDir/*.json") as $jsonFile) {
            $bn = basename($jsonFile);
            if (strpos($bn, '.html-') !== false) continue; // skip translation extracts
            $d = json_decode(file_get_contents($jsonFile), true);
            if (!$d || empty($d['slug'])) continue;
            $sl = $d['slug'];
            if (!isset($blogMap[$sl])) $blogMap[$sl] = [];
            $blogMap[$sl][$lc] = $sl;
        }
    }

    // Only needed when EN and TR use DIFFERENT slugs for the same blog
    $slugPairs = [
        'germany-hidden-costs'       => 'almanya-hidden-costs',
        'first-time-exhibitor-guide' => 'ilk-kez-katilacaklar-rehberi',
    ];
    foreach ($slugPairs as $enSlug => $trSlug) {
        $blogMap[$enSlug] = ($blogMap[$enSlug] ?? []) + ['en' => $enSlug, 'tr' => $trSlug];
        $blogMap[$trSlug] = ($blogMap[$trSlug] ?? []) + ['en' => $enSlug, 'tr' => $trSlug];
    }

    // References page name per language
    $refPages = [
        'en' => 'references.html',
        'tr' => 'referanslar.html',
        'de' => 'referenzen.html',
        'fr' => 'references.html',
        'es' => 'referencias.html',
        'ar' => 'references.html',
        'zh' => 'references.html',
    ];
    $refPage = $refPages[$lang] ?? 'references.html';

    // Active class helper
    $a = function($t) use ($page_type) {
        return $page_type === $t ? ' active' : '';
    };

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
        } elseif ($page_type === 'references') {
            $targetSlug = '/' . ($refPages[$l['code']] ?? 'references.html');
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
        <img src="/assets/img/logo.webp" alt="MT Messe Stand" width="100" height="42">
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
          <li class="nav-item"><a class="nav-link' . $a('references') . '" href="' . $navBase . $refPage . '">' . $L[3] . '</a></li>
          <li class="nav-item"><a class="nav-link' . $a('contact') . '" href="' . $navBase . 'contact.html">' . $L[4] . '</a></li>
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
