<?php
ob_start();
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../templates/blog-template.php';
require_login();
check_csrf();

$lang = $_SESSION['lang'] ?? 'tr';
$old_slug = $_POST['old_slug'] ?? '';
$slug = $_POST['slug'] ?? '';
$title = trim($_POST['title'] ?? '');
$date = $_POST['date'] ?? date('Y-m-d');
$summary = trim($_POST['summary'] ?? '');
$image = trim($_POST['image'] ?? '');
// Only accept paths under /assets/img/blog/
if ($image && strpos($image, '/assets/img/blog/') !== 0) {
    $image = '';
}
$content_raw = $_POST['content'] ?? '';

if (empty($title)) {
    ob_end_clean();
    header('Location: ../editor-blog.php?error=' . urlencode('Başlık zorunlu'));
    exit;
}

// Always regenerate slug from title (ignore JS/empty input)
$slug_raw = $slug ?: $title;
$slug = str_replace(
    ['ı','İ','ş','Ş','ğ','Ğ','ü','Ü','ö','Ö','ç','Ç','I'],
    ['i','i','s','s','g','g','u','u','o','o','c','c','i'],
    $slug_raw
);
$slug = strtolower($slug);
$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
$slug = trim($slug, '-');

$allowed_tags = '<h2><h3><h4><p><ul><ol><li><blockquote><strong><em><a><img><br><details><summary>';
$content_clean = strip_tags($content_raw, $allowed_tags);

// Clean layout artifacts from migrated content
$cut_at = strpos($content_clean, '<!-- ===== AUTHOR ===== -->');
if ($cut_at !== false) $content_clean = substr($content_clean, 0, $cut_at);
// Strip related-posts navigation blocks
$content_clean = preg_replace('/<div class="related-posts">.*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/s', '', $content_clean);
$content_clean = trim($content_clean);

$data = [
    'title' => $title,
    'date' => $date,
    'summary' => $summary,
    'image' => $image,
    'slug' => $slug,
    'content' => $content_clean,
    'meta_desc' => substr(strip_tags($summary ?: $content_raw), 0, 160),
];

$blog_dir = __DIR__ . "/../../$lang/blog";
$data_dir = __DIR__ . "/../../data/$lang/blog";
if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);
if (!is_dir($blog_dir)) mkdir($blog_dir, 0755, true);

if ($old_slug && $old_slug !== $slug) {
    @unlink("$data_dir/$old_slug.json");
    @unlink("$blog_dir/$old_slug.html");
}

$json_path = "$data_dir/$slug.json";
$html_path = "$blog_dir/$slug.html";

file_put_contents("$json_path.tmp", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
rename("$json_path.tmp", $json_path);

try {
    $html = render_blog($data, $content_clean, $lang);
    file_put_contents("$html_path.tmp", $html);
    rename("$html_path.tmp", $html_path);
    log_action('save_blog', 'success', ['lang' => $lang, 'slug' => $slug]);
} catch (Throwable $e) {
    log_error('save_blog: ' . $e->getMessage());
    @unlink("$json_path.tmp");
    ob_end_clean();
    header('Location: ../editor-blog.php?slug=' . urlencode($slug) . '&error=' . urlencode('Render başarısız. Log\'ları kontrol edin.'));
    exit;
}

if (file_exists(__DIR__ . '/../../build.php')) {
    include __DIR__ . '/../../build.php';
}

ob_end_clean();
header('Location: ../editor-blog.php?slug=' . urlencode($slug) . '&saved=1');
