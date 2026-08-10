<?php
ob_start();
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../templates/page-template.php';
require_login();
check_csrf();

$lang = $_SESSION['lang'] ?? 'tr';
$page = $_POST['page'] ?? 'about';
$title = trim($_POST['title'] ?? '');
$content_raw = $_POST['content'] ?? '';

if (empty($title)) die('Title required');

$allowed_tags = '<h2><h3><h4><p><ul><ol><li><blockquote><strong><em><a><br>';
$content_clean = strip_tags($content_raw, $allowed_tags);

$data = [
    'title' => $title,
    'content' => $content_clean,
    'meta_desc' => substr(strip_tags($content_raw), 0, 160),
];

$data_dir = __DIR__ . "/../../data/$lang";
if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);

$json_path = "$data_dir/$page.json";
$html_path = __DIR__ . "/../../$lang/$page.html";

file_put_contents("$json_path.tmp", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
rename("$json_path.tmp", $json_path);

try {
    $html = render_page($data, $content_clean, $lang, $page);
    file_put_contents("$html_path.tmp", $html);
    rename("$html_path.tmp", $html_path);
    log_action('save_page', 'success', ['lang' => $lang, 'page' => $page]);
} catch (Throwable $e) {
    log_error('save_page: ' . $e->getMessage());
    @unlink("$json_path.tmp");
    ob_end_clean();
    die('Render failed. Check logs.');
}

if (file_exists(__DIR__ . '/../../build.php')) {
    include __DIR__ . '/../../build.php';
}

ob_end_clean();
header('Location: ../editor-page.php?page=' . urlencode($page) . '&saved=1');
