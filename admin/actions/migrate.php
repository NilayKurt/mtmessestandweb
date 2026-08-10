<?php
// Run once via CLI: php admin/actions/migrate.php
// Converts existing blog HTML → JSON for admin panel
if (php_sapi_name() !== 'cli') die('CLI only');

$base = __DIR__ . '/../..';
$langs = ['en', 'tr'];

foreach ($langs as $lang) {
    $blog_dir = "$base/$lang/blog";
    $data_dir = "$base/data/$lang/blog";
    if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);
    if (!is_dir($blog_dir)) continue;

    foreach (glob("$blog_dir/*.html") as $html_file) {
        if (basename($html_file) === 'index.html') continue;

        $slug = pathinfo($html_file, PATHINFO_FILENAME);
        $json_path = "$data_dir/$slug.json";
        if (file_exists($json_path)) {
            echo "SKIP $lang/$slug (already exists)\n";
            continue;
        }

        $html = file_get_contents($html_file);

        preg_match('/<h1>([^<]+)<\/h1>/', $html, $title);
        preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $og_img);
        preg_match('/<p class="lead">([^<]+)<\/p>/', $html, $lead);
        preg_match('/<span>([A-Z][a-z]+ \d+, \d{4})<\/span>/', $html, $date_span);
        preg_match('/<meta name="description" content="([^"]+)"/', $html, $meta_desc);

        // Extract content: everything after h1+lead until author-box or footer
        $content = '';
        if (preg_match('/<div class="article-intro">(.*?)<div class="author-box">/s', $html, $cm)) {
            $content = trim($cm[1]);
        } elseif (preg_match('/<p class="lead">[^<]*<\/p>\s*(.*?)<div class="author-box">/s', $html, $cm)) {
            $content = trim($cm[1]);
        } elseif (preg_match('/<h1>[^<]*<\/h1>\s*<p class="lead">[^<]*<\/p>\s*(.*?)<footer/s', $html, $cm)) {
            $content = trim($cm[1]);
        }

        $data = [
            'title' => $title[1] ?? basename($html_file),
            'date' => isset($date_span[1]) ? date('Y-m-d', strtotime($date_span[1])) : date('Y-m-d'),
            'summary' => $lead[1] ?? '',
            'image' => isset($og_img[1]) ? parse_url($og_img[1], PHP_URL_PATH) : '',
            'slug' => $slug,
            'content' => trim($content),
            'meta_desc' => $meta_desc[1] ?? $lead[1] ?? '',
        ];

        file_put_contents($json_path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        echo "OK $lang/$slug\n";
    }
}
echo "Done.\n";
