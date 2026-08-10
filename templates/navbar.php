<?php
function render_navbar(string $lang, string $page_type, string $slug = ''): string {
    // Menu labels per language
    $labels = [
        'en' => ['Home', 'About', 'Blog', 'Contact'],
        'tr' => ['Anasayfa', 'Hakkımızda', 'Blog', 'İletişim'],
        'de' => ['Start', 'Über uns', 'Blog', 'Kontakt'],
        'fr' => ['Accueil', 'À propos', 'Blog', 'Contact'],
        'es' => ['Inicio', 'Nosotros', 'Blog', 'Contacto'],
        'ar' => ['الرئيسية', 'من نحن', 'المدونة', 'اتصل بنا'],
        'zh' => ['首页', '关于我们', '博客', '联系我们'],
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

    // Blog filename map
    $blogMap = [
        'germany-hidden-costs' => ['en' => 'germany-hidden-costs', 'tr' => 'almanya-hidden-costs'],
        'first-time-exhibitor-guide' => ['en' => 'first-time-exhibitor-guide', 'tr' => 'ilk-kez-katilacaklar-rehberi'],
    ];

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
        <img src="/assets/img/logo.webp" alt="MT Messe Stand" width="140" height="27">
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
          <li class="nav-item"><a class="nav-link' . $a('contact') . '" href="' . $navBase . 'contact.html">' . $L[3] . '</a></li>
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
