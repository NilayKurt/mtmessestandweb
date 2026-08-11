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

// Rewrite references .html → .php (cPanel .htaccess equivalent)
$ref_map = [
    '/en/references.html'   => '/en/references.php',
    '/tr/referanslar.html'  => '/tr/referanslar.php',
    '/de/referenzen.html'   => '/de/referenzen.php',
    '/fr/references.html'   => '/fr/references.php',
    '/es/referencias.html'  => '/es/referencias.php',
    '/ar/references.html'   => '/ar/references.php',
    '/zh/references.html'   => '/zh/references.php',
    '/ru/references.html'   => '/ru/references.php',
];
if (isset($ref_map[$path]) && is_file(__DIR__ . $ref_map[$path])) {
    $_SERVER['SCRIPT_NAME'] = $ref_map[$path];
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . $ref_map[$path];
    include $_SERVER['SCRIPT_FILENAME'];
    return true;
}

// 404
http_response_code(404);
include __DIR__ . '/404.html';
return true;
