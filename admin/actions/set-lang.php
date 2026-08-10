<?php
require_once __DIR__ . '/../auth.php';
$lang = $_POST['lang'] ?? 'tr';
if (isset(LANGUAGES[$lang])) {
    $_SESSION['lang'] = $lang;
}
header('Location: ../dashboard.php');
