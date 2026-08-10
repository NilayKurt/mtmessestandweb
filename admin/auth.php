<?php
require_once __DIR__ . '/config.php';
session_start();

// ── Error handling ──
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '0'); // We handle logging ourselves

function log_error(string $message): void {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) @mkdir($log_dir, 0755, true);
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $line = "[$timestamp] [$ip] ERROR: $message\n";
    @file_put_contents("$log_dir/error.log", $line, FILE_APPEND | LOCK_EX);
}

function log_action(string $action, string $result, array $details = []): void {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) @mkdir($log_dir, 0755, true);
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $detail_str = $details ? ' ' . json_encode($details, JSON_UNESCAPED_UNICODE) : '';
    $line = "[$timestamp] [$ip] $action: $result$detail_str\n";
    @file_put_contents("$log_dir/audit.log", $line, FILE_APPEND | LOCK_EX);
}

set_exception_handler(function (Throwable $e) {
    log_error($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    die('Internal server error. Check logs.');
});

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
