<?php

/**
 * Simple Multi-Tenant FTP Deployer (Incremental + Cleanup)
 * Author: Fahmi Fauzi Rahman
 * Updated to use config.json for include_path, exclude_path, tenants
 */

function logMessage(string $message): void
{
    echo "[" . date('H:i:s') . "] " . $message . PHP_EOL;
}

function ftpEnsureDir($ftp, string $remoteDir): bool
{
    // ... (Fungsi ini tetap sama)
    $parts = explode('/', trim($remoteDir, '/'));
    $path = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        $path .= '/' . $part;
        // Gunakan @ftp_chdir tanpa mencoba kembali ke root jika tidak perlu.
        if (@ftp_chdir($ftp, $path)) {
            // @ftp_chdir($ftp, '/'); // go back root after test - Dihapus/dikomentari karena tidak perlu
            continue;
        }
        if (!@ftp_mkdir($ftp, $path)) {
            logMessage("❌ Failed to create directory: $path");
            return false;
        }
    }
    return true;
}

function ftpDeleteDir($ftp, string $remoteDir): void
{
    // ... (Fungsi ini tetap sama)
    $items = @ftp_mlsd($ftp, $remoteDir);
    if ($items !== false) {
        foreach ($items as $item) {
            if ($item['name'] === '.' || $item['name'] === '..') continue;
            $path = "$remoteDir/{$item['name']}";
            if ($item['type'] === 'dir') {
                ftpDeleteDir($ftp, $path);
            } else {
                @ftp_delete($ftp, $path);
                logMessage("🗑️ Deleted remote file: $path");
            }
        }
    }
    @ftp_rmdir($ftp, $remoteDir);
    logMessage("🗑️ Removed directory: $remoteDir");
}

/**
 * Sinkronisasi rekursif antara lokal dan remote
 *
 * @param resource $ftp Koneksi FTP
 * @param string $localPath Path lokal saat ini
 * @param string $remotePath Path remote saat ini
 * @param array $exclude Array daftar item (path relatif) yang harus dikecualikan (ex: ['public/uploads', '.env'])
 * @param string $baseRemotePath Path root remote dari tenant
 * @param string $baseLocalPath Path root lokal dari folder yang di-include (folder yang pertama kali di-sync)
 * @param bool $makedir Apakah harus membuat direktori remote jika belum ada (hanya false untuk file tunggal)
 */
function ftpSyncAndClean($ftp, string $localPath, string $remotePath, array $exclude = [], string $baseRemotePath = '', string $baseLocalPath = '', bool $makedir = true): void
{
    // Inisialisasi base path untuk panggilan rekursif pertama
    if (empty($baseLocalPath)) {
        // baseLocalPath adalah folder induk dari path yang di-include (agar path relatif dihitung dengan benar)
        $baseLocalPath = dirname($localPath) === '.' ? '' : dirname($localPath);
    }

    // Fungsi untuk mendapatkan path relatif dari path lokal saat ini terhadap baseLocalPath
    $getRelativePath = function (string $path) use ($baseLocalPath) {
        $path = str_replace('\\', '/', $path); // Normalisasi path
        $base = rtrim(str_replace('\\', '/', $baseLocalPath), '/');
        if (empty($base) || $path === $base) {
            return ltrim($path, '/');
        }
        // Hapus prefix $baseLocalPath dari $path
        if (strpos($path, $base) === 0) {
            $relativePath = substr($path, strlen($base));
            return ltrim($relativePath, '/');
        }
        return ltrim($path, '/');
    };

    // Cek apakah direktori (localPath) saat ini sudah masuk daftar exclude
    $currentRelativeDir = $getRelativePath($localPath);
    if (!empty($currentRelativeDir) && in_array($currentRelativeDir, $exclude, true)) {
        logMessage("🚫 Skipped (excluded directory): $localPath");
        return; // Hentikan rekursi untuk direktori ini
    }

    // Pastikan remote path adalah direktori
    if ($makedir && is_dir($localPath)) {
        @ftp_mkdir($ftp, $remotePath);
    } elseif ($makedir && !is_dir($localPath)) {
        // Jika ini adalah file tunggal dari deployTenant, kita harus memastikan direktori induk ada
        $remoteDir = dirname($remotePath);
        ftpEnsureDir($ftp, $remoteDir);
    }


    $remoteItems = [];
    $list = @ftp_mlsd($ftp, $remotePath);
    if ($list !== false) {
        foreach ($list as $item) {
            if ($item['name'] === '.' || $item['name'] === '..') continue;
            $remoteItems[$item['name']] = $item;
        }
    }

    $localItems = is_dir($localPath) ? array_diff(scandir($localPath), ['.', '..']) : [];

    // Upload atau update
    if (is_dir($localPath)) {
        foreach ($localItems as $name) {

            $localFile = rtrim($localPath, '/') . '/' . $name;
            $remoteFile = rtrim($remotePath, '/') . '/' . $name;
            $relativeItemPath = $getRelativePath($localFile);

            // Perbaikan 2.1: Cek path relatif item saat ini
            if (in_array($relativeItemPath, $exclude, true)) {
                logMessage("🚫 Skipped (excluded): $localFile");
                unset($remoteItems[$name]); // Jangan hapus di cleanup
                continue;
            }

            if (is_dir($localFile)) {
                // Tambahkan base paths saat panggilan rekursif
                // Note: baseRemotePath sudah tidak terlalu krusial, tapi biarkan saja. Fokus pada $baseLocalPath
                ftpSyncAndClean($ftp, $localFile, $remoteFile, $exclude, $baseRemotePath, $baseLocalPath);
            } else {
                // ... (Logika upload file tetap sama)
                $localMTime = filemtime($localFile);
                $remoteMTime = 0;
                if (isset($remoteItems[$name]['modify'])) {
                    $remoteMTime = strtotime($remoteItems[$name]['modify']);
                }

                if ($remoteMTime < $localMTime) {
                    logMessage("[*] Uploading updated: $remoteFile");
                    if (!ftp_put($ftp, $remoteFile, $localFile, FTP_BINARY)) {
                        logMessage("❌ Upload failed: $localFile");
                    }
                } else {
                    logMessage("[=] Skipped (up-to-date): $remoteFile");
                }
            }

            unset($remoteItems[$name]); // Sudah di-handle (upload/skip)
        }

        // Perbaikan 2.2: Hapus yang tidak ada di lokal, LEWATKAN yang masuk daftar exclude
        foreach ($remoteItems as $name => $info) {
            // Karena item di $remoteItems tidak ada di lokal (localItems),
            // kita hanya perlu mengecek apakah item ini atau direktori induknya masuk daftar exclude.

            $remoteItemPath = rtrim($remotePath, '/') . '/' . $name;
            // Kita tidak punya full path lokal yang sesuai, jadi kita harus menggunakan logika path relatif sebelumnya:

            $currentRemoteRelativeDir = $getRelativePath($localPath);
            $remoteRelativeItemPath = empty($currentRemoteRelativeDir)
                ? $name
                : $currentRemoteRelativeDir . '/' . $name;

            // Jika item ini masuk daftar exclude, JANGAN HAPUS.
            if (in_array($remoteRelativeItemPath, $exclude, true)) {
                logMessage("🚫 Skipped (remote excluded from cleanup): $remoteItemPath");
                continue;
            }

            // Periksa juga jika direktori induk dari item yang dihapus sudah di-exclude (Ini harusnya sudah dihandle di awal fungsi ini, tapi kita ulang cek untuk berjaga-jaga)
            if (!empty($currentRelativeDir) && in_array($currentRelativeDir, $exclude, true)) {
                logMessage("🚫 Skipped (remote excluded directory cleanup): $remoteItemPath");
                continue;
            }


            if ($info['type'] === 'dir') {
                ftpDeleteDir($ftp, $remoteItemPath);
            } else {
                @ftp_delete($ftp, $remoteItemPath);
                logMessage("🗑️ Deleted remote file: $remoteItemPath");
            }
        }
    } else {
        // Kalau $localPath file biasa (logika upload file tunggal)
        // ... (Tidak diubah, karena sudah dipanggil dengan makedir=false dari deployTenant)

        $localMTime = filemtime($localPath);
        $remoteMTime = 0;
        $remoteDir = dirname($remotePath);

        $list = @ftp_mlsd($ftp, $remoteDir);
        if ($list !== false) {
            foreach ($list as $item) {
                if ($item['name'] === basename($localPath) && isset($item['modify'])) {
                    $remoteMTime = strtotime($item['modify']);
                    break;
                }
            }
        }

        if ($remoteMTime < $localMTime) {
            logMessage("[*] Uploading updated file: $remotePath");
            ftp_put($ftp, $remotePath, $localPath, FTP_BINARY);
        } else {
            logMessage("[=] Skipped (up-to-date): $remotePath");
        }
    }
}

function deployTenant(string $name, array $tenantCfg, array $globalCfg): void
{
    // ... (Logika koneksi dan root directory tidak diubah, dilewati untuk ringkasan)
    logMessage("=== Deploying [$name] ===");

    $ftp = @ftp_connect($tenantCfg['host']);
    if (!$ftp) {
        logMessage("❌ Failed to connect: {$tenantCfg['host']}");
        return;
    }
    if (!@ftp_login($ftp, $tenantCfg['user'], $tenantCfg['pass'])) {
        logMessage("❌ Login failed for user {$tenantCfg['user']}");
        ftp_close($ftp);
        return;
    }

    ftp_pasv($ftp, true);
    logMessage("🔗 Connected to {$tenantCfg['host']} as {$tenantCfg['user']}");

    $remoteRoot = rtrim($tenantCfg['remote_path'], '/');
    if (!ftpEnsureDir($ftp, $remoteRoot)) {
        logMessage("❌ Failed to ensure remote root directory for tenant [$name]", false);
        ftp_close($ftp);
        return;
    }

    $includes = $globalCfg['include_path'] ?? [];
    $excludes = $globalCfg['exclude_path'] ?? [];

    if (empty($includes) || !is_array($includes)) {
        logMessage("⚠️ include_path is empty or invalid in config.json");
        ftp_close($ftp);
        return;
    }

    foreach ($includes as $path) {
        $path = trim($path);
        if ($path === '') continue;

        if (!file_exists($path)) {
            logMessage("⚠️ Skipped (not found): $path");
            continue;
        }

        $remoteTarget = $remoteRoot . '/' . $path;

        if (is_dir($path)) {
            logMessage("🔄 Syncing directory: $path -> $remoteTarget");
            // Pass baseLocalPath sebagai folder yang pertama kali di-sync (folder induk dari $path)
            $baseLocalPath = dirname($path) === '.' ? '' : dirname($path);
            ftpSyncAndClean($ftp, $path, $remoteTarget, $excludes, $remoteRoot, $baseLocalPath);
        } else {
            logMessage("⬆️ Uploading file: $path -> $remoteTarget");
            // Untuk file tunggal, kita perlu tahu folder induknya untuk baseLocalPath
            $baseLocalPath = dirname($path) === '.' ? '' : dirname($path);
            ftpSyncAndClean($ftp, $path, $remoteTarget, $excludes, $remoteRoot, $baseLocalPath, makedir: false);
        }
    }

    ftp_close($ftp);
    logMessage("✅ Finished deployment for [$name]");
}

function main(array $argv): void
{
    $root = __DIR__;
    $configFile = "$root/config.json";

    if (!file_exists($configFile)) {
        logMessage("❌ Missing config.json at $configFile");
        exit(1);
    }

    $cfg = json_decode(file_get_contents($configFile), true);
    if (!$cfg) {
        logMessage("❌ Invalid config.json format");
        exit(1);
    }

    $target = $argv[1] ?? null;

    if ($target) {
        if (!isset($cfg['tenants'][$target])) {
            logMessage("❌ Tenant [$target] not found in config.json");
            exit(1);
        }
        deployTenant($target, $cfg['tenants'][$target], $cfg);
    } else {
        foreach ($cfg['tenants'] as $name => $tenantCfg) {
            deployTenant($name, $tenantCfg, $cfg);
        }
    }

    logMessage("🏁 All deployments completed.");
}

main($argv);
