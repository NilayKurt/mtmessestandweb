<?php
/**
 * MT Messe Stand — Contact Form Handler
 * Saves messages to data/messages.json + sends email notification
 */

// --- Config ---
$TO_EMAIL = 'info@mtmessestand.com';
$DATA_FILE = __DIR__ . '/data/messages.json';
$SUBJECT_PREFIX = '[MT Messe Stand] New Contact Message';

// --- Only accept POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// --- Collect & sanitize ---
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$lang    = trim($_POST['lang'] ?? 'en');
$honeypot = trim($_POST['website'] ?? '');       // invisible field for bots

// --- Spam check: honeypot ---
if ($honeypot !== '') {
    // Bots fill hidden fields — silently accept but don't save
    header('Location: /' . $lang . '/contact.html?sent=1');
    exit;
}

// --- Validate ---
$errors = [];
if ($name === '')    $errors[] = 'name';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
if ($message === '') $errors[] = 'message';

// --- Redirect back on error ---
$referer = $_SERVER['HTTP_REFERER'] ?? '/' . $lang . '/contact.html';
if (!empty($errors)) {
    $qs = http_build_query(['error' => implode(',', $errors)]);
    header('Location: ' . $referer . '?' . $qs);
    exit;
}

// --- Build record ---
$record = [
    'name'      => $name,
    'email'     => $email,
    'message'   => $message,
    'lang'      => $lang,
    'ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
    'timestamp' => date('Y-m-d H:i:s'),
];

// --- Atomic write to JSON ---
$dir = dirname($DATA_FILE);
if (!is_dir($dir)) mkdir($dir, 0755, true);

$messages = [];
if (file_exists($DATA_FILE)) {
    $raw = file_get_contents($DATA_FILE);
    $messages = json_decode($raw, true);
    if (!is_array($messages)) $messages = [];
}
$messages[] = $record;

$tmp = $DATA_FILE . '.' . getmypid() . '.tmp';
file_put_contents($tmp, json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
rename($tmp, $DATA_FILE);

// --- Email notification ---
$subject = "$SUBJECT_PREFIX — $name";
$body = "Name: $name\nEmail: $email\nLanguage: $lang\nIP: {$_SERVER['REMOTE_ADDR']}\n\n$message";
$headers = "From: $TO_EMAIL\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";

@mail($TO_EMAIL, $subject, $body, $headers);

// --- Redirect on success ---
header('Location: /' . $lang . '/contact.html?sent=1');
exit;
