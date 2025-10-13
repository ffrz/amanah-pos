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
    $parts = explode('/', trim($remoteDir, '/'));
    $path = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        $path .= '/' . $part;
        if (@ftp_chdir($ftp, $path)) {
            ftp_chdir($ftp, '/'); // go back root after test
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

function ftpSyncAndClean($ftp, string $localPath, string $remotePath, array $exclude = []): void
{
    @ftp_mkdir($ftp, $remotePath);

    $remoteItems = [];
    $list = @ftp_mlsd($ftp, $remotePath);
    if ($list !== false) {
        foreach ($list as $item) {
            if ($item['name'] === '.' || $item['name'] === '..') continue;
            $remoteItems[$item['name']] = $item;
        }
    }

    $localItems = is_dir($localPath) ? array_diff(scandir($localPath), ['.', '..']) : [];

    // Upload or update
    if (is_dir($localPath)) {
        foreach ($localItems as $name) {
            if (in_array($name, $exclude, true)) continue;

            $localFile = rtrim($localPath, '/') . '/' . $name;
            $remoteFile = rtrim($remotePath, '/') . '/' . $name;

            if (is_dir($localFile)) {
                ftpSyncAndClean($ftp, $localFile, $remoteFile, $exclude);
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

            unset($remoteItems[$name]); // sudah di-handle
        }

        // Hapus yang tidak ada di lokal
        foreach ($remoteItems as $name => $info) {
            if (in_array($name, $exclude, true)) continue;
            $remoteItemPath = rtrim($remotePath, '/') . '/' . $name;
            if ($info['type'] === 'dir') {
                ftpDeleteDir($ftp, $remoteItemPath);
            } else {
                @ftp_delete($ftp, $remoteItemPath);
                logMessage("🗑️ Deleted remote file: $remoteItemPath");
            }
        }
    } else {
        // Kalau $localPath file biasa
        $remoteDir = dirname($remotePath);
        ftpEnsureDir($ftp, $remoteDir);

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

    // ✅ Buat direktori root tenant otomatis jika belum ada
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
        ftpSyncAndClean($ftp, $path, $remoteTarget, $excludes);
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
