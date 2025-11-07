<?php

use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

// Optional: allow script run longer (tergantung host)
@set_time_limit(0);
@ignore_user_abort(true);

// Muat .env manual (berguna kalau config dicache sebelumnya)
$dotEnvPath = dirname(__DIR__);
if (file_exists($dotEnvPath . '/.env')) {
    $dotenv = Dotenv::createImmutable($dotEnvPath);
    $dotenv->safeLoad();
}

$app = require_once __DIR__ . '/../bootstrap/app.php';

/**
 * Pastikan kernel di-boot supaya facades dan service provider siap.
 * Kita memakai Console Kernel contract.
 */
$kernel = $app->make(Kernel::class);

// bootstrap the application (this sets up facades, providers, etc)
if (method_exists($kernel, 'bootstrap')) {
    $kernel->bootstrap();
}

// Safety: set facade application explicitly (garansi ganda)
Facade::setFacadeApplication($app);

// Simple logger
function patch_log($message)
{
    $logFile = __DIR__ . '/../storage/logs/patch.log';
    $time = date('Y-m-d H:i:s');
    $entry = "[$time] " . $message . PHP_EOL;
    // Pastikan direktori storage/logs punya permission tulis
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

// Security: token (prefer config, tahan config cache)
$secret = $_GET['token'] ?? '';
// Prefer config('app.patch_token') — lebih aman terhadap config:cache
$expected = env('APP_PATCH_TOKEN');

if (empty($expected)) {
    patch_log('PATCH: expected token empty — check config/app.php or .env for APP_PATCH_TOKEN');
}

if ($secret !== $expected) {
    http_response_code(403);
    patch_log('PATCH: Unauthorized access attempt from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    exit('Unauthorized. 403');
}

patch_log('PATCH: Authorized. Starting upgrade process.');

// Helper to run artisan command and log output
function run_cmd($cmd, $args = [])
{
    try {
        // If args passed as array, pass to Artisan::call
        if (!empty($args)) {
            $code = Artisan::call($cmd, $args);
        } else {
            $code = Artisan::call($cmd);
        }
        $output = Artisan::output();
        patch_log("CMD: php artisan {$cmd} => exit {$code}\nOUTPUT:\n" . trim($output));
        return ['code' => $code, 'output' => $output];
    } catch (Throwable $e) {
        patch_log("ERROR running {$cmd}: " . $e->getMessage());
        return ['code' => 1, 'output' => $e->getMessage()];
    }
}

// Clear & rebuild caches (urutan penting: clear sebelum cache)
run_cmd('optimize:clear');
run_cmd('config:clear');
run_cmd('route:clear');

// Run commands (migrate harus pakai --force)
run_cmd('migrate', ['--force' => true]);
run_cmd('permissions:sync');

run_cmd('optimize');
run_cmd('config:cache');   // rebuild config cache
run_cmd('route:cache');    // rebuild routes cache
// Optional view:cache may not exist on older laravel, handle silently
try {
    run_cmd('view:cache');
} catch (Throwable $ignored) {
}

patch_log('PATCH: Upgrade finished at ' . date('Y-m-d H:i:s'));

echo json_encode([
    'status' => 'ok',
    'message' => 'Patch Complete',
    'time' => date('c'),
]);

// End
