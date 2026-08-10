<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../templates/blog-template.php';
require_login();
check_csrf();

$lang = $_POST['lang'] ?? 'tr';
$old_slug = $_POST['old_slug'] ?? '';
$slug = $_POST['slug'] ?? '';
$title = trim($_POST['title'] ?? '');
$date = $_POST['date'] ?? date('Y-m-d');
$summary = trim($_POST['summary'] ?? '');
$image = trim($_POST['image'] ?? '');
$content_raw = $_POST['content'] ?? '';

if (empty($title)) die('Title required');

if (empty($slug)) {
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
    $slug = trim($slug, '-');
}

$allowed_tags = '<h2><h3><h4><p><ul><ol><li><blockquote><strong><em><a><img><br><details><summary>';
$content_clean = strip_tags($content_raw, $allowed_tags);

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

// Delete old slug if changed
if ($old_slug && $old_slug !== $slug) {
    @unlink("$data_dir/$old_slug.json");
    @unlink("$blog_dir/$old_slug.html");
}

// Atomic write
$json_path = "$data_dir/$slug.json";
$html_path = "$blog_dir/$slug.html";

file_put_contents("$json_path.tmp", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
rename("$json_path.tmp", $json_path);

try {
    $html = render_blog($data, $content_clean, $lang);
    file_put_contents("$html_path.tmp", $html);
    rename("$html_path.tmp", $html_path);
    log_action('save_blog', 'success', ['lang' => $lang, 'slug' => $slug]);
} catch (Exception $e) {
    log_error('save_blog failed: ' . $e->getMessage());
    @unlink("$json_path.tmp");
    die('Render failed. Check logs.');
}

// Trigger build.php for homepage cards
if (file_exists(__DIR__ . '/../../build.php')) {
    include __DIR__ . '/../../build.php';
}

header('Location: ../dashboard.php?lang=' . urlencode($lang) . '&saved=1');
