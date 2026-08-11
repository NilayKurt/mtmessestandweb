<?php
// templates/sitemap-generator.php — Regenerate sitemap-images.xml from references.json

function generate_references_sitemap(): void {
    $json_file = __DIR__ . '/../data/references.json';
    $sitemap_file = __DIR__ . '/../sitemap-images.xml';
    $site_url = defined('SITE_URL') ? SITE_URL : 'https://mtmessestand.com';

    $refs = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];
    if (!is_array($refs)) $refs = [];
    if (empty($refs)) {
        @unlink($sitemap_file);
        return;
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

    // EN references page
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$site_url}/en/references/</loc>\n";
    foreach ($refs as $r) {
        $src = htmlspecialchars($r['src'] ?? '', ENT_XML1);
        $caption = htmlspecialchars(substr($r['alt_en'] ?? ($r['alt_ar'] ?? ''), 0, 160), ENT_XML1);
        $title = htmlspecialchars($r['city'] . ' ' . $r['fair'] . ' ' . $r['year'] ?? '', ENT_XML1);
        $xml .= "    <image:image>\n";
        $xml .= "      <image:loc>{$site_url}{$src}</image:loc>\n";
        if ($caption) $xml .= "      <image:caption>{$caption}</image:caption>\n";
        if ($title)   $xml .= "      <image:title>{$title}</image:title>\n";
        $xml .= "    </image:image>\n";
    }
    $xml .= "  </url>\n";

    // TR references page
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$site_url}/tr/referanslar/</loc>\n";
    foreach ($refs as $r) {
        $src = htmlspecialchars($r['src'] ?? '', ENT_XML1);
        $caption = htmlspecialchars(substr($r['alt_tr'] ?? ($r['alt_en'] ?? ''), 0, 160), ENT_XML1);
        $xml .= "    <image:image>\n";
        $xml .= "      <image:loc>{$site_url}{$src}</image:loc>\n";
        if ($caption) $xml .= "      <image:caption>{$caption}</image:caption>\n";
        $xml .= "    </image:image>\n";
    }
    $xml .= "  </url>\n";

    $xml .= '</urlset>';

    file_put_contents($sitemap_file, $xml);
}
