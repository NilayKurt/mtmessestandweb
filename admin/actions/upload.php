<?php
try {
require_once __DIR__ . '/../auth.php';
require_login();
check_csrf();

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    log_error('Upload: no file or error ' . ($_FILES['image']['error'] ?? 'unknown'));
    die('Upload failed');
}

$file = $_FILES['image'];
if ($file['size'] > 2 * 1024 * 1024) {
    log_error('Upload: too large ' . round($file['size']/1024, 1) . 'KB');
    die('File too large (max 2MB)');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['webp', 'jpg', 'jpeg', 'png'])) {
    log_error('Upload: invalid extension ' . $ext);
    die('Invalid file type');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, ['image/webp', 'image/jpeg', 'image/png'])) {
    log_error('Upload: invalid MIME ' . $mime);
    die('Invalid MIME type');
}

$safe_name = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
$safe_name = trim($safe_name, '-') ?: 'image';
$filename = $safe_name . '.' . $ext;

$upload_dir = __DIR__ . '/../../assets/img/blog';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$i = 1; $base = $safe_name;
while (file_exists("$upload_dir/$filename")) {
    $filename = $base . '-' . $i . '.' . $ext;
    $i++;
}

if (move_uploaded_file($file['tmp_name'], "$upload_dir/$filename")) {
    log_action('upload', 'success', ['file' => $filename]);
} else {
    log_error('Upload: move_uploaded_file failed');
    die('Failed to save file');
}

header('Location: ../media.php?uploaded=1');
} catch (Throwable $e) {
    log_error('Upload: ' . $e->getMessage());
    die('Upload error. Check logs.');
}
