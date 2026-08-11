<?php
// admin/actions/reorder-references.php
try {
    require_once __DIR__ . '/../auth.php';
    require_login();
    check_csrf();

    $id = (int)($_POST['id'] ?? -1);
    $direction = $_POST['direction'] ?? '';
    if ($id < 0 || !in_array($direction, ['up', 'down'])) {
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

    $swap_idx = $direction === 'up' ? $id - 1 : $id + 1;
    if ($swap_idx < 0 || $swap_idx >= count($refs)) {
        header('Location: ../editor-references.php');
        exit;
    }

    // Swap positions
    $tmp = $refs[$id]['position'];
    $refs[$id]['position'] = $refs[$swap_idx]['position'];
    $refs[$swap_idx]['position'] = $tmp;

    // Atomic write
    $tmp_file = $json_file . '.tmp';
    file_put_contents($tmp_file, json_encode($refs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    rename($tmp_file, $json_file);

    log_action('reorder_reference', 'success', ['id' => $id, 'direction' => $direction]);
    header('Location: ../editor-references.php?reordered=1#ref-' . $id);

} catch (Throwable $e) {
    log_error('Reorder reference: ' . $e->getMessage());
    header('Location: ../editor-references.php?error=system_error');
}
