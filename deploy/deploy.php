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
 * @param array $exclude Array daftar item (nama file/folder) yang harus dikecualikan di level direktori saat ini
 * @param string $baseRemotePath Path root remote untuk perhitungan pengecualian di langkah 2
 * @param string $baseLocalPath Path root lokal untuk perhitungan pengecualian di langkah 2
 */
function ftpSyncAndClean($ftp, string $localPath, string $remotePath, array $exclude = [], string $baseRemotePath = '', string $baseLocalPath = '', $makedir = true): void
{

    // Inisialisasi base path untuk panggilan rekursif pertama
    if (empty($baseRemotePath)) $baseRemotePath = $remotePath;
    if (empty($baseLocalPath)) $baseLocalPath = $localPath;

    // Pastikan remote path adalah direktori
    if ($makedir) {
        @ftp_mkdir($ftp, $remotePath);
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

    // **Perbaikan 2.1: Periksa item yang akan diunggah/diperbarui apakah masuk daftar exclude**
    if (is_dir($localPath)) {
        foreach ($localItems as $name) {
            // Check exclude hanya berdasarkan nama item untuk recursive call
            if (in_array($name, $exclude, true)) {
                logMessage("🚫 Skipped (excluded): $remotePath/$name");
                unset($remoteItems[$name]); // Jangan hapus di cleanup
                continue;
            }

            $localFile = rtrim($localPath, '/') . '/' . $name;
            $remoteFile = rtrim($remotePath, '/') . '/' . $name;

            if (is_dir($localFile)) {
                // Tambahkan base paths saat panggilan rekursif
                ftpSyncAndClean($ftp, $localFile, $remoteFile, $exclude, $baseRemotePath, $baseLocalPath);
            } else {
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

            unset($remoteItems[$name]); // Sudah di-handle
        }

        // **Perbaikan 2.2: Hapus yang tidak ada di lokal, LEWATKAN yang masuk daftar exclude**
        foreach ($remoteItems as $name => $info) {
            // Periksa item yang ada di remote tapi tidak ada di lokal.
            // Jika nama item ini masuk daftar exclude, JANGAN HAPUS.
            var_dump($name, $exclude, $baseRemotePath);
            exit;
            // $name = "test"
            //
            // $remotePath = "/public_html/_apps/shiftech-pos/shiftcom/public"

            // exclude ini harus diterjemahkan, misa dari nama direktori semisa "public/uploads"
            // tapi saat dijalankan ternyata $remote_path/$name mungkin /public_html/...../public/uploads/namafile"
            // logikanya kita butuh nerjemahin "public/upoads ditambah prefix $remote_path
            if (in_array($current_dir, $exclude)) {
                continue;
            }
            if (in_array($name, $exclude, true)) {
                logMessage("🚫 Skipped (remote excluded from cleanup): $remotePath/$name");
                continue;
            }

            $remoteItemPath = rtrim($remotePath, '/') . '/' . $name;
            if ($info['type'] === 'dir') {
                ftpDeleteDir($ftp, $remoteItemPath);
            } else {
                @ftp_delete($ftp, $remoteItemPath);
                logMessage("🗑️ Deleted remote file: $remoteItemPath");
            }
        }
    } else {
        // Logika ini seharusnya tidak tercapai jika dipanggil dari deployTenant dengan path yang merupakan file.
        // Jika $localPath file, akan di-handle di `deployTenant` agar tidak rekursif.
        // Logika di bawah ini tidak perlu diubah, tapi perhatikan bagaimana `deployTenant` memanggil fungsi ini.

        // Kalau $localPath file biasa (ini kasus yang berbeda dari $localPath direktori)

        // $remoteDir = dirname($remotePath);
        // ftpEnsureDir($ftp, $remoteDir);

        $localMTime = filemtime($localPath);
        $remoteMTime = 0;
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
            ftpSyncAndClean($ftp, $path, $remoteTarget, $excludes);
        } else {
            logMessage("⬆️ Uploading file: $path -> $remoteTarget");
            ftpSyncAndClean($ftp, $path, $remoteTarget, $excludes, makedir: false);
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
