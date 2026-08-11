<?php
// admin/actions/delete-reference.php
try {
    require_once __DIR__ . '/../auth.php';
    require_login();
    check_csrf();

    $id = (int)($_POST['id'] ?? -1);
    if ($id < 0) {
        header('Location: ../editor-references.php?error=invalid_request');
        exit;
    }

    $json_file = __DIR__ . '/../../data/references.json';
    if (!file_exists($json_file)) {
        header('Location: ../editor-references.php');
        exit;
    }

    $refs = json_decode(file_get_contents($json_file), true);
    if (!is_array($refs) || !isset($refs[$id])) {
        header('Location: ../editor-references.php');
        exit;
    }

    $entry = $refs[$id];
    $img_path = __DIR__ . '/../../' . ltrim($entry['src'], '/');

    // Remove from array
    array_splice($refs, $id, 1);

    // Renumber positions
    foreach ($refs as $i => &$r) {
        $r['position'] = $i + 1;
    }
    unset($r);

    // Atomic write
    $tmp_file = $json_file . '.tmp';
    file_put_contents($tmp_file, json_encode($refs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    rename($tmp_file, $json_file);

    // Delete image file
    if (file_exists($img_path) && strpos(realpath($img_path), realpath(__DIR__ . '/../../assets/img/portfolio')) === 0) {
        @unlink($img_path);
    }

    // Regenerate sitemap
    require_once __DIR__ . '/../../templates/sitemap-generator.php';
    generate_references_sitemap();

    log_action('delete_reference', 'success', ['file' => $entry['filename'] ?? '']);
    header('Location: ../editor-references.php?deleted=1');

} catch (Throwable $e) {
    log_error('Delete reference: ' . $e->getMessage());
    header('Location: ../editor-references.php?error=system_error');
}
