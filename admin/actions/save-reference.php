<?php
// admin/actions/save-reference.php — update reference entry fields
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

    $fields = ['alt_en','alt_tr','alt_de','alt_fr','alt_es','alt_ru','alt_zh','alt_ar','sector','fair','city','country','year'];
    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            $refs[$id][$f] = trim($_POST[$f]);
        }
    }

    // Atomic write
    $tmp_file = $json_file . '.tmp';
    file_put_contents($tmp_file, json_encode($refs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    rename($tmp_file, $json_file);

    log_action('save_reference', 'success', ['id' => $id]);
    header('Location: ../editor-references.php?saved=1');

} catch (Throwable $e) {
    log_error('Save reference: ' . $e->getMessage());
    header('Location: ../editor-references.php?error=system_error');
}
