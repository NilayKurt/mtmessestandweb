<?php
require_once __DIR__ . '/../auth.php';
require_login();
check_csrf();

$lang = $_POST['lang'] ?? 'tr';
$slug = $_POST['slug'] ?? '';

if ($slug) {
    $data_dir = __DIR__ . "/../../data/$lang/blog";
    $blog_dir = __DIR__ . "/../../$lang/blog";
    
    if (unlink("$data_dir/$slug.json") || unlink("$blog_dir/$slug.html")) {
        log_action('delete_blog', 'success', ['lang' => $lang, 'slug' => $slug]);
    } else {
        log_error("delete_blog failed: $lang/$slug");
    }

    if (file_exists(__DIR__ . '/../../build.php')) {
        include __DIR__ . '/../../build.php';
    }
}

header('Location: ../dashboard.php?lang=' . urlencode($lang) . '&deleted=1');
