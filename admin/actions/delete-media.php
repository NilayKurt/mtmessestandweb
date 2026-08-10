<?php
ob_start();
try {
require_once __DIR__ . '/../auth.php';
require_login();
check_csrf();

$file = $_POST['file'] ?? '';
$allowed_base = '/assets/img/blog/';

// Path traversal protection: only allow files under assets/img/blog/
if (strpos($file, $allowed_base) !== 0 || strpos($file, '..') !== false) {
    log_error('delete_media: invalid path ' . $file);
    ob_end_clean();
    header('Location: ../media.php?error=' . urlencode('Geçersiz dosya yolu'));
    exit;
}

$path = __DIR__ . '/../../' . ltrim($file, '/');
if (file_exists($path) && unlink($path)) {
    log_action('delete_media', 'success', ['file' => $file]);
    ob_end_clean();
    header('Location: ../media.php?deleted=1');
} else {
    log_error('delete_media: file not found ' . $file);
    ob_end_clean();
    header('Location: ../media.php?error=' . urlencode('Dosya bulunamadı'));
}
} catch (Throwable $e) {
    log_error('delete_media: ' . $e->getMessage());
    ob_end_clean();
    header('Location: ../media.php?error=' . urlencode('Silme hatası'));
}
