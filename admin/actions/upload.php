<?php
try {
require_once __DIR__ . '/../auth.php';
require_login();
check_csrf();

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    header('Location: ../media.php?error=' . urlencode('Yükleme hatası'));
    exit;
}

$file = $_FILES['image'];
if ($file['size'] > 2 * 1024 * 1024) {
    header('Location: ../media.php?error=' . urlencode('Dosya 2MB\'dan büyük'));
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['webp', 'jpg', 'jpeg', 'png'])) {
    header('Location: ../media.php?error=' . urlencode('Geçersiz dosya türü'));
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, ['image/webp', 'image/jpeg', 'image/png'])) {
    header('Location: ../media.php?error=' . urlencode('Geçersiz MIME türü'));
    exit;
}

$safe_name = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
$safe_name = trim($safe_name, '-') ?: 'image';
$filename = $safe_name . '.' . $ext;

$upload_dir = __DIR__ . '/../../assets/img/blog';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// Reject if same name exists
if (file_exists("$upload_dir/$filename")) {
    header('Location: ../media.php?error=' . urlencode("'$filename' zaten var. Farklı bir isim verin."));
    exit;
}

if (move_uploaded_file($file['tmp_name'], "$upload_dir/$filename")) {
    log_action('upload', 'success', ['file' => $filename]);
    header('Location: ../media.php?uploaded=1');
} else {
    header('Location: ../media.php?error=' . urlencode('Kaydetme başarısız'));
}
} catch (Throwable $e) {
    log_error('Upload: ' . $e->getMessage());
    header('Location: ../media.php?error=' . urlencode('Sistem hatası'));
}
