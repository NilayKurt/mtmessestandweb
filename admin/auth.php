<?php
require_once __DIR__ . '/config.php';
session_start();

function is_logged_in(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf(): void {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF validation failed');
    }
}

function brute_force_check(): void {
    $ip = $_SERVER['REMOTE_ADDR'];
    $counter_file = sys_get_temp_dir() . '/admin_bf_' . md5($ip);
    $attempts = (int) @file_get_contents($counter_file);
    if ($attempts > 5) {
        $last = (int) @filemtime($counter_file);
        if (time() - $last < 900) {
            die('Too many attempts. Wait 15 minutes.');
        }
        $attempts = 0;
    }
    file_put_contents($counter_file, $attempts + 1);
}

function attempt_login(string $password): bool {
    brute_force_check();
    sleep(2);
    if (password_verify($password, ADMIN_PASSWORD)) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}
