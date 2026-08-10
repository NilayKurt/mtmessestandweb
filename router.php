<?php
// PHP built-in server router — serve static files, 404.html for missing
if (php_sapi_name() !== 'cli-server') return false;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

// Serve existing files directly
if (is_file($file)) return false;

// Serve directory index
if (is_dir($file)) {
    foreach (['index.html', 'index.php'] as $idx) {
        if (is_file("$file/$idx")) {
            $_SERVER['SCRIPT_NAME'] = "$path/$idx";
            return false;
        }
    }
}

// 404
http_response_code(404);
include __DIR__ . '/404.html';
return true;
