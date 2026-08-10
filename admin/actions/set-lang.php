<?php
require_once __DIR__ . '/../auth.php';
require_login();
check_csrf();
$_SESSION['lang'] = $_POST['lang'] ?? 'tr';
header('Location: ../dashboard.php');
