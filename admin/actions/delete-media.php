<?php
ob_start();
try {
require_once __DIR__ . '/../auth.php';
require_login();
check_csrf();

$file = $_POST['file'] ?? '';
if ($file && strpos($file, '/assets/img/blog/') === 0) {
    $path = __DIR__ . '/../../' . ltrim($file, '/');
    if (file_exists($path)) {
        unlink($path);
        log_action('delete_media', 'success', ['file' => $file]);
    }
}
ob_end_clean();
header('Location: ../media.php?deleted=1');
} catch (Throwable $e) {
    log_error('delete_media: ' . $e->getMessage());
    ob_end_clean();
    die('Delete failed');
}
