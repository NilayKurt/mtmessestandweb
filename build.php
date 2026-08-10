<?php
/**
 * build.php — Multi-language blog card injector (PHP version)
 * cPanel Cron Job: her gun 14:00
 * Komut: /usr/bin/php /home/KULLANICI/public_html/build.php
 */

$BASE = __DIR__;

$LANGS = [
    'en/'  => 'en/blog',
    'tr/' => 'tr/blog',
    'de/' => 'de/blog',
    'fr/' => 'fr/blog',
    'es/' => 'es/blog',
    'ar/' => 'ar/blog',
    'zh/' => 'zh/blog',
];

$LABELS = [
    'en' => ['Latest Insights', 'Exhibition stand guides, operational costs, and country-specific regulations.', 'Read More →', 'View All Articles →', 'en/blog/'],
    'tr' => ['Son Yazılar', 'Fuar standı rehberleri, operasyonel maliyetler ve ülke bazlı düzenlemeler.', 'Okumaya Devam Et →', 'Tüm Yazılar →', 'tr/blog/'],
    'de' => ['Neueste Beiträge', 'Messestand-Ratgeber und länderspezifische Vorschriften.', 'Weiterlesen →', 'Alle Artikel →', 'de/blog/'],
    'fr' => ['Derniers Articles', 'Guides de stands et réglementations par pays.', 'Lire la suite →', 'Voir tous les articles →', 'fr/blog/'],
    'es' => ['Últimos Artículos', 'Guías de stands de exhibición, costos operativos y regulaciones por país.', 'Leer Más →', 'Ver Todos →', 'es/blog/'],
    'ar' => ['آخر المقالات', 'أدلة منصات المعارض والتكاليف التشغيلية.', 'اقرأ المزيد ←', 'عرض جميع المقالات ←', 'ar/blog/'],
    'zh' => ['最新文章', '展台指南、运营成本和国家特定法规。', '阅读更多 →', '查看所有文章 →', 'zh/blog/'],
];

/**
 * Parse blog posts from blog/LANG/index.html
 */
function parse_blog_posts($blog_dir) {
    global $BASE;
    $path = $BASE . '/' . $blog_dir . '/index.html';
    if (!file_exists($path)) return [];

    $html = file_get_contents($path);
    $posts = [];

    // Match card divs
    preg_match_all('/<div class="card post-card[^"]*">(.*?)<\/div>\s*<\/div>\s*<\/div>/s', $html, $matches);

    foreach ($matches[1] as $card) {
        // Skip placeholders
        if (strpos($card, 'bg-light') !== false) continue;
        if (strpos($card, 'Yakında') !== false) continue;
        if (strpos($card, 'Coming Soon') !== false) continue;
        if (strpos($card, 'قريبًا') !== false) continue;
        if (strpos($card, '即将推出') !== false) continue;

        preg_match('/<a[^>]*>([^<]+)<\/a>/', $card, $title);
        preg_match('/<p class="card-text[^"]*">([^<]+)<\/p>/s', $card, $desc);
        preg_match('/<small[^>]*>([^<]+)<\/small>/', $card, $date);
        preg_match('/href="([^"]+)"/', $card, $link);

        if ($title && $link) {
            $posts[] = [
                'title' => trim($title[1]),
                'desc' => isset($desc[1]) ? trim($desc[1]) : '',
                'date' => isset($date[1]) ? trim($date[1]) : '',
                'link' => $link[1]
            ];
        }
    }

    return array_slice($posts, 0, 3);
}

/**
 * Build HTML for 3 blog cards
 */
function build_cards_html($posts, $lang_code, $blog_path) {
    global $LABELS;
    $L = isset($LABELS[$lang_code]) ? $LABELS[$lang_code] : $LABELS['en'];

    if (empty($posts)) {
        return '<!-- build.php: no posts yet for ' . $lang_code . ' -->';
    }

    $blog_link_path = basename($blog_path); // 'en/blog' -> 'blog'
    
    $cards = '';
    foreach ($posts as $post) {
        $cards .= '        <div class="col-lg-4 col-md-6" data-aos="fade-up">
          <div class="service-card">
            <small class="text-muted">' . htmlspecialchars($post['date']) . '</small>
            <h4><a href="' . $blog_link_path . '/' . $post['link'] . '" class="text-decoration-none">' . htmlspecialchars($post['title']) . '</a></h4>
            <p>' . htmlspecialchars($post['desc']) . '</p>
            <a href="' . $blog_link_path . '/' . $post['link'] . '" class="btn btn-sm btn-outline-primary mt-2">' . $L[2] . '</a>
          </div>
        </div>
';
    }

    return '<!-- ===== BLOG ===== -->
<section id="blog" class="section bg-light">
  <div class="container">
    <div class="section-title" data-aos="fade-up">
      <h2>' . $L[0] . '</h2>
      <p>' . $L[1] . '</p>
    </div>
    <div class="row g-4">
' . $cards . '    </div>
    <div class="text-center mt-4">
      <a href="' . $blog_link_path . '/" class="btn btn-outline-primary">' . $L[3] . '</a>
    </div>
  </div>
</section>
';
}

/**
 * Inject blog HTML into index page
 */
function inject($index_rel, $blog_html) {
    global $BASE;
    $path = $index_rel ? $BASE . '/' . $index_rel . 'index.html' : $BASE . '/index.html';

    if (!file_exists($path)) return false;

    $html = file_get_contents($path);
    $placeholder = '<!-- BLOG_CARDS -->';

    if (strpos($html, $placeholder) === false) return false;

    $html = str_replace($placeholder, $blog_html, $html);
    file_put_contents($path, $html);
    return true;
}

// === MAIN ===
echo "[build.php] " . date('Y-m-d H:i:s') . " — Multi-language blog check\n";

$all_ok = true;
foreach ($LANGS as $index_rel => $blog_dir) {
    $index_path = $index_rel ? $BASE . '/' . $index_rel . 'index.html' : $BASE . '/index.html';
    if (!file_exists($index_path)) continue;

    $lang_code = dirname($blog_dir); // 'en/blog' -> 'en'
    $posts = parse_blog_posts($blog_dir);
    echo "  [$lang_code] " . count($posts) . " posts\n";

    $blog_html = build_cards_html($posts, $lang_code, $blog_dir);
    if (!inject($index_rel, $blog_html)) {
        echo "  [$lang_code] FAILED\n";
        $all_ok = false;
    }
}

echo "[build.php] " . ($all_ok ? "✓ All languages updated" : "✗ Some languages failed") . "\n";
