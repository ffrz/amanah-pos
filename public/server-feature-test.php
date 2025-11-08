<?php

/**
 * NATIVE PHP BACKUP TEST UTILITY
 * ------------------------------
 * Skrip ini digunakan untuk menguji fungsionalitas:
 * 1. Ketersediaan fungsi shell_exec() di Shared Hosting.
 * 2. Ketersediaan perintah mysqldump.
 * 3. Koneksi database menggunakan kredensial dari file .env Laravel.
 *
 * CARA PAKAI:
 * 1. Simpan file ini di direktori 'public' Laravel Anda.
 * 2. Akses melalui browser: yourdomain.com/backup_test.php
 * 3. HAPUS FILE INI SETELAH PENGUJIAN SELESAI untuk alasan keamanan!
 */

// Tampilkan semua error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Uji Coba Fungsi Backup Database (PHP Native)</h1>";
echo "<h2>1. Uji Ketersediaan Fungsi shell_exec()</h2>";

// --- 1. UJI shell_exec() ---
if (function_exists('shell_exec')) {
    echo "<p style='color:green;'>✅ **shell_exec()** tersedia.</p>";
} else {
    echo "<p style='color:red;'>❌ **shell_exec()** tidak tersedia. Backup via PHP Shell tidak mungkin.</p>";
    echo "<p>Coba kontak support hosting Anda untuk mengaktifkannya, atau gunakan phpMyAdmin/cPanel.</p>";
    exit; // Berhenti jika fungsi shell_exec tidak ada
}

// --- 2. AMBIL KONFIGURASI DB DARI .env ---
echo "<h2>2. Membaca Konfigurasi Database (.env)</h2>";

$envPath = dirname(__DIR__) . '/.env';
$config = [];

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_EMPTY_LINES | FILE_SKIP_NEW_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $config[trim($name)] = trim($value, "\" \t\n\r\0\x0B"); // Hapus spasi dan quotes
    }
    echo "<p style='color:green;'>✅ File **.env** ditemukan dan berhasil dibaca.</p>";
} else {
    echo "<p style='color:red;'>❌ File **.env** tidak ditemukan di path: " . $envPath . ". Harap periksa path.</p>";
    exit;
}

// Ambil kredensial
$databaseName = $config['DB_DATABASE'] ?? null;
$username = $config['DB_USERNAME'] ?? null;
$password = $config['DB_PASSWORD'] ?? null;
$host = $config['DB_HOST'] ?? 'localhost'; // Default host

if (!$databaseName || !$username) {
    echo "<p style='color:red;'>❌ Kredensial database **DB_DATABASE** atau **DB_USERNAME** tidak ditemukan di .env.</p>";
    exit;
}

$time = date('Y-m-d_His');
$filename = "test-backup-{$databaseName}-{$time}.sql";
$backupDir = dirname(__DIR__) . '/storage/app/backups_test/';

// --- 3. UJI shell_exec() DENGAN DUMP ---
echo "<h2>3. Uji Coba Eksekusi mysqldump</h2>";

// Pastikan direktori backup ada
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
    echo "<p style='color:blue;'>ℹ️ Direktori backup dibuat: " . $backupDir . "</p>";
}

// Perintah mysqldump
// Kita gunakan escapeshellarg untuk keamanan, meskipun ini hanya tes
$command = sprintf(
    'mysqldump -h%s -u%s -p%s %s > %s 2>&1', // 2>&1 mengarahkan error ke output
    escapeshellarg($host),
    escapeshellarg($username),
    escapeshellarg($password),
    escapeshellarg($databaseName),
    escapeshellarg($backupDir . $filename)
);

echo "<p>Perintah yang akan dieksekusi:</p>";
echo "<pre style='background:#eee; padding:10px; border:1px solid #ccc;'>" . htmlspecialchars($command) . "</pre>";

$startTime = microtime(true);
$output = shell_exec($command);
$endTime = microtime(true);

echo "<p>Waktu Eksekusi: " . round($endTime - $startTime, 3) . " detik</p>";

// --- 4. ANALISIS HASIL ---
echo "<h2>4. Analisis Hasil</h2>";

if ($output !== null && trim($output) !== '') {
    // Jika ada output, kemungkinan besar itu adalah error (mysqldump biasanya diam jika sukses)
    echo "<p style='color:red;'>❌ **mysqldump GAGAL dieksekusi atau menghasilkan error.**</p>";
    echo "<p>Pesan Error:</p>";
    echo "<pre style='background:#fdd; padding:10px; border:1px solid red;'>" . htmlspecialchars($output) . "</pre>";

    if (strpos($output, 'command not found') !== false) {
        echo "<p>Penyebab Paling Mungkin: Perintah **mysqldump tidak ditemukan (PATH tidak terdeteksi)**.</p>";
    } elseif (strpos($output, 'Access denied') !== false) {
        echo "<p>Penyebab Paling Mungkin: **DB_USERNAME/DB_PASSWORD** di .env salah atau tidak memiliki hak akses.</p>";
    }
} else {
    // Cek keberadaan file
    if (file_exists($backupDir . $filename) && filesize($backupDir . $filename) > 0) {
        $filesizeMB = round(filesize($backupDir . $filename) / 1048576, 2);
        echo "<p style='color:green;'>✅ **SUCCESS!** Backup berhasil dibuat.</p>";
        echo "<p>File: **{$filename}**</p>";
        echo "<p>Ukuran File: **{$filesizeMB} MB**</p>";

        // Hapus file tes
        unlink($backupDir . $filename);
        echo "<p style='color:blue;'>ℹ️ File backup tes berhasil dihapus.</p>";
    } else {
        echo "<p style='color:red;'>❌ **mysqldump GAGAL.** Tidak ada error yang dicatat, tetapi file backup tidak dibuat (ukuran 0 byte).</p>";
        echo "<p>Penyebab Paling Mungkin: Perintah tidak diizinkan dieksekusi oleh sistem keamanan PHP, meskipun `shell_exec` tersedia.</p>";
    }
}

echo "<h2>⚠️ PEMBERITAHUAN KEAMANAN KRITIS</h2>";
echo "<p style='color:red; font-weight:bold;'>HAPUS FILE INI (backup_test.php) SEGERA SETELAH PENGUJIAN SELESAI.</p>";
echo "<p>Skrip ini mengekspos kredensial dan fungsionalitas server.</p>";
