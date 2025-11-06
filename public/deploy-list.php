<?php

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

@set_time_limit(0);
@ignore_user_abort(true);

$base_path = dirname(__DIR__);
if (file_exists($base_path . '/.env')) {
    $dotenv = Dotenv::createImmutable($base_path);
    $dotenv->safeLoad();
}

$secret = $_GET['token'] ?? '';
$expected = env('APP_PATCH_TOKEN');

if (empty($expected)) {
    patch_log('PATCH: expected token empty — check config/app.php or .env for APP_PATCH_TOKEN');
}

if ($secret !== $expected) {
    http_response_code(403);
    patch_log('PATCH: Unauthorized access attempt from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    exit('Unauthorized. 403');
}

$include = [
    'app',
    'bootstrap',
    'config',
    'database',
    'modules',
    'resources',
    'routes',
    'public',
    'storage',
    'artisan',
    'LICENSE',
    'VERSION',
    'composer.json',
    'composer.lock',
    'vite.config.js'
];

$exclude = [
    'public/hot',
    'public/uploads',
    'storage/debugbar',
    'storage/framework/views',
    'storage/logs'
];

$result = [];

function listFiles($base_path, $rel = '', $exclude = [])
{
    $files = [];
    $dir = rtrim($base_path . '/' . $rel, '/');
    if (!is_dir($dir)) return $files;

    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $path = $dir . '/' . $item;
        $relPath = ltrim(($rel ? "$rel/" : '') . $item, '/');

        // Skip jika match dengan exclude
        foreach ($exclude as $ex) {
            if (strpos($relPath, $ex) === 0) continue 2;
        }

        if (is_dir($path)) {
            $files += listFiles($base_path, $relPath, $exclude);
        } else {
            $files[$relPath] = filemtime($path);
        }
    }
    return $files;
}

foreach ($include as $path) {
    $fullPath = $base_path . '/' . $path;
    if (is_file($fullPath)) {
        $result[$path] = filemtime($fullPath);
    } elseif (is_dir($fullPath)) {
        $result += listFiles($base_path, $path, $exclude);
    }
}

header('Content-Type: application/json');

echo json_encode($result);
