<?php
ob_start();
try {
require_once __DIR__ . '/../auth.php';
require_login();
check_csrf();

$lang = $_SESSION['lang'] ?? 'tr';
$slug = $_POST['slug'] ?? '';

if ($slug) {
    $data_dir = __DIR__ . "/../../data/$lang/blog";
    $blog_dir = __DIR__ . "/../../$lang/blog";
    $deleted = @unlink("$data_dir/$slug.json");
    @unlink("$blog_dir/$slug.html");
    if ($deleted) {
        log_action('delete_blog', 'success', ['lang' => $lang, 'slug' => $slug]);
    }
    if (file_exists(__DIR__ . '/../../build.php')) {
        include __DIR__ . '/../../build.php';
    }
}
ob_end_clean();
header('Location: ../dashboard.php?deleted=1');
} catch (Throwable $e) {
    log_error('delete_blog: ' . $e->getMessage());
    ob_end_clean();
    header('Location: ../dashboard.php?error=' . urlencode('Silme başarısız. Log\'ları kontrol edin.'));
    exit;
}
