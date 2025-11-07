<?php

/**
 * ==============================================================
 * Laravel Incremental Deploy File Indexer (for FTP Deployer)
 * ==============================================================
 *
 * Author : Fahmi Fauzi Rahman
 * Version: 1.0
 *
 * Description:
 * --------------------------------------------------------------
 * Script ini dijalankan di sisi server (public/deploy-list.php)
 * untuk menghasilkan daftar semua file yang relevan dengan hash,
 * timestamp (mtime), dan ukuran. Hasilnya akan digunakan oleh
 * client deployer (deploy.php) untuk melakukan incremental upload
 * berdasarkan perubahan file.
 *
 * Fitur Utama:
 * - Menjamin direktori penting Laravel selalu ada dan writable
 * - Mengembalikan daftar file (recursive) dalam format JSON
 * - Mendukung daftar include & exclude path
 * - Aman dengan token autentikasi
 * ==============================================================
 */

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

@set_time_limit(0);
@ignore_user_abort(true);

/**
 * ----------------------------------------------------------------
 * 1. Inisialisasi dan Validasi Token
 * ----------------------------------------------------------------
 */

$base_path = dirname(__DIR__);

if (file_exists($base_path . '/.env')) {
    $dotenv = Dotenv::createImmutable($base_path);
    $dotenv->safeLoad();
}

$secret   = $_GET['token'] ?? '';
$expected = env('APP_PATCH_TOKEN');

if (empty($expected)) {
    patch_log('PATCH: expected token empty — check .env for APP_PATCH_TOKEN');
}

if ($secret !== $expected) {
    http_response_code(403);
    patch_log('PATCH: Unauthorized access attempt from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    exit('Unauthorized. 403');
}

/**
 * ----------------------------------------------------------------
 * 2. Daftar direktori wajib (Laravel)
 * ----------------------------------------------------------------
 */
$must_exists_dirs = [
    'storage',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

/**
 * ----------------------------------------------------------------
 * 3. Helper Functions
 * ----------------------------------------------------------------
 */

/**
 * Pastikan direktori ada dan memiliki permission yang aman.
 *
 * @param string $path Path direktori yang ingin dipastikan.
 * @param int $mode Mode permission (default: 0775).
 * @return void
 */
function ensureDirectoryExists(string $path, int $mode = 0775): void
{
    if (!is_dir($path)) {
        @mkdir($path, $mode, true);
    }
    @chmod($path, $mode);
}

/**
 * Pastikan semua direktori wajib Laravel tersedia.
 *
 * @param array $dirs Daftar direktori yang harus ada.
 * @param string $base_path Path dasar proyek Laravel.
 * @return void
 */
function ensureMustExistDirs(array $dirs, string $base_path): void
{
    foreach ($dirs as $dir) {
        $full = rtrim($base_path . '/' . $dir, '/');
        ensureDirectoryExists($full);
    }
}

/**
 * Rekursif membaca file dari direktori dan menghasilkan daftar file
 * beserta hash, ukuran, dan waktu modifikasinya.
 *
 * @param string $base_path Path dasar proyek.
 * @param string $rel Path relatif yang sedang diproses.
 * @param array $exclude Daftar path yang harus dikecualikan.
 * @return array<string, array{mtime:int, hash:string, size:int}>
 */
function listFiles(string $base_path, string $rel = '', array $exclude = []): array
{
    $result = [];
    $dir = rtrim($base_path . '/' . $rel, '/');
    if (!is_dir($dir)) return $result;

    $items = array_diff(scandir($dir), ['.', '..']);

    foreach ($items as $item) {
        $path = $dir . '/' . $item;
        $relPath = ltrim(($rel ? "$rel/" : '') . $item, '/');

        // Skip jika masuk daftar exclude
        foreach ($exclude as $ex) {
            if (strpos($relPath, $ex) === 0) continue 2;
        }

        if (is_dir($path)) {
            $result += listFiles($base_path, $relPath, $exclude);
        } elseif (is_file($path)) {
            $result[$relPath] = [
                'mtime' => filemtime($path),
                'hash'  => sha1_file($path),
                'size'  => filesize($path)
            ];
        }
    }

    return $result;
}

/**
 * ----------------------------------------------------------------
 * 4. Jalankan pemeriksaan & pembentukan daftar file
 * ----------------------------------------------------------------
 */

// Pastikan direktori wajib tersedia
ensureMustExistDirs($must_exists_dirs, $base_path);

// Path yang akan disertakan
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

// Path yang akan dikecualikan
$exclude = [
    'public/hot',
    'public/uploads',
    'storage/debugbar',
    'storage/framework/views',
    'storage/logs'
];

/**
 * ----------------------------------------------------------------
 * 5. Generate daftar file dan hasilkan JSON
 * ----------------------------------------------------------------
 */

$result = [];

foreach ($include as $path) {
    $fullPath = $base_path . '/' . $path;

    if (is_file($fullPath)) {
        $result[$path] = [
            'mtime' => filemtime($fullPath),
            'hash'  => sha1_file($fullPath),
            'size'  => filesize($fullPath)
        ];
    } elseif (is_dir($fullPath)) {
        $result += listFiles($base_path, $path, $exclude);
    }
}

header('Content-Type: application/json');
echo json_encode($result);
