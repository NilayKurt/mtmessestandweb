<?php
// admin/actions/move-reference.php — move item to a specific position, shift others
try {
    require_once __DIR__ . '/../auth.php';
    require_login();
    check_csrf();

    $id = (int)($_POST['id'] ?? -1);
    $new_pos = (int)($_POST['new_pos'] ?? 0);
    if ($id < 0 || $new_pos < 1) {
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

    $new_pos = min($new_pos, count($refs)); // clamp to max

    // Move item: remove from current position, insert at new position
    $item = array_splice($refs, $id, 1)[0];
    array_splice($refs, $new_pos - 1, 0, [$item]);

    // Renumber positions
    foreach ($refs as $i => &$r) {
        $r['position'] = $i + 1;
    }
    unset($r);

    // Atomic write
    $tmp_file = $json_file . '.tmp';
    file_put_contents($tmp_file, json_encode($refs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    rename($tmp_file, $json_file);

    log_action('move_reference', 'success', ['id' => $id, 'to' => $new_pos]);
    header('Location: ../editor-references.php?reordered=1');

} catch (Throwable $e) {
    log_error('Move reference: ' . $e->getMessage());
    header('Location: ../editor-references.php?error=system_error');
}
