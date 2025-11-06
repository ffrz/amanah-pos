<?php

/**
 * Incremental Multi-Tenant FTP Deployer
 * --------------------------------------
 * Author: Fahmi Fauzi Rahman
 * Features:
 *  - Incremental upload via deploy-list.php (mtime compare)
 *  - Recursive FTP sync + cleanup
 *  - Exclude/include paths from config.json
 *  - Auto call patch endpoint after deployment
 *  - Auto force-upload deploy-list.php to public/ if missing
 */

function logMessage(string $msg): void
{
    echo "[" . date('H:i:s') . "] " . $msg . PHP_EOL;
}

/**
 * Pastikan direktori remote ada, buat jika belum
 */
function ftpEnsureDir($ftp, string $remoteDir): bool
{
    $parts = explode('/', trim($remoteDir, '/'));
    $path = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        $path .= '/' . $part;
        if (@ftp_chdir($ftp, $path)) continue;
        if (!@ftp_mkdir($ftp, $path)) {
            logMessage("❌ Failed to create directory: $path");
            return false;
        }
    }
    return true;
}

/**
 * Force upload deploy-list.php ke /public jika belum ada
 */
function ensureDeployListExists($ftp, string $localFile, string $remoteRoot): void
{
    $remotePath = rtrim($remoteRoot, '/') . '/public/deploy-list.php';
    $remoteDir = dirname($remotePath);

    $list = @ftp_nlist($ftp, $remoteDir);
    $exists = false;
    if ($list) {
        foreach ($list as $file) {
            if (basename($file) === 'deploy-list.php') {
                $exists = true;
                break;
            }
        }
    }

    if (!$exists) {
        logMessage("📤 deploy-list.php not found in remote, uploading...");
        ftpEnsureDir($ftp, $remoteDir);
        if (!ftp_put($ftp, $remotePath, $localFile, FTP_BINARY)) {
            logMessage("❌ Failed to upload deploy-list.php");
        } else {
            logMessage("✅ Uploaded deploy-list.php to /public");
            sleep(3);
        }
    } else {
        logMessage("✅ deploy-list.php already exists in remote/public");
    }
}

/**
 * Ambil daftar file + timestamp dari remote (via deploy-list.php)
 */
function fetchRemoteFileList(string $domain, string $token): array
{
    $url = "https://{$domain}/deploy-list.php?token={$token}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if (!$response) {
        logMessage("⚠️ Failed to fetch remote file list from $domain ($err)");
        return [];
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        logMessage("⚠️ Invalid JSON response from deploy-list.php");
        return [];
    }

    logMessage("✅ Remote file list fetched (" . count($data) . " files)");
    return $data;
}

/**
 * Sinkronisasi direktori lokal ke remote berdasarkan daftar file remote
 */
function ftpSyncIncremental($ftp, string $projectRoot, string $baseRemote, array $include, array $exclude, array $remoteList): void
{
    $projectRoot = realpath($projectRoot) ?: $projectRoot;
    $projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');

    foreach ($include as $path) {
        $includeReal = realpath($path);
        if (!$includeReal || !file_exists($includeReal)) {
            logMessage("⚠️ Skipped (not found): $path");
            continue;
        }
        $includeReal = str_replace('\\', '/', $includeReal); // normalized

        if (is_dir($includeReal)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($includeReal, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) continue;

                $filePath = str_replace('\\', '/', $file->getPathname());

                // compute relative path: prefer relative to project root, fallback to include root
                if (strpos($filePath, $projectRoot . '/') === 0) {
                    $relPath = substr($filePath, strlen($projectRoot) + 1);
                } else {
                    // fallback: relative to include folder
                    $relPath = substr($filePath, strlen($includeReal) + 1);
                }
                $relPath = ltrim($relPath, '/');

                // safety: skip if empty
                if ($relPath === '') continue;

                // Exclude check
                $skip = false;
                foreach ($exclude as $ex) {
                    if (strpos($relPath, $ex) === 0) {
                        $skip = true;
                        break;
                    }
                }
                if ($skip) continue;

                $localPath = $file->getPathname();
                $remotePath = rtrim($baseRemote, '/') . '/' . $relPath;

                $localMTime = filemtime($localPath);
                $remoteMTime = $remoteList[$relPath] ?? 0;

                if ($remoteMTime < $localMTime) {
                    ftpEnsureDir($ftp, dirname($remotePath));
                    logMessage("[*] Uploading: $relPath");
                    if (!ftp_put($ftp, $remotePath, $localPath, FTP_BINARY)) {
                        logMessage("❌ Upload failed: $relPath");
                    }
                } else {
                    logMessage("[=] Up-to-date: $relPath");
                }
            }
        } else {
            // single file in include list
            $filePath = str_replace('\\', '/', realpath($includeReal));
            if (strpos($filePath, $projectRoot . '/') === 0) {
                $relPath = substr($filePath, strlen($projectRoot) + 1);
            } else {
                $relPath = basename($filePath);
            }
            $relPath = ltrim($relPath, '/');

            // Exclude check
            $skip = false;
            foreach ($exclude as $ex) {
                if (strpos($relPath, $ex) === 0) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            $localMTime = filemtime($filePath);
            $remoteMTime = $remoteList[$relPath] ?? 0;
            $remotePath = rtrim($baseRemote, '/') . '/' . $relPath;

            if ($remoteMTime < $localMTime) {
                ftpEnsureDir($ftp, dirname($remotePath));
                logMessage("[*] Uploading file: $relPath");
                if (!ftp_put($ftp, $remotePath, $filePath, FTP_BINARY)) {
                    logMessage("❌ Upload failed: $relPath");
                }
            } else {
                logMessage("[=] Up-to-date: $relPath");
            }
        }
    }
}


/**
 * Jalankan patch endpoint setelah upload
 */
function patch(string $domain, string $token): void
{
    $url = "https://{$domain}/patch-apply.php?token={$token}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    logMessage("🧩 Patch: " . ($response ?: "No response"));
}

/**
 * Deploy satu tenant
 */
function deployTenant(string $name, array $tenantCfg, array $globalCfg): void
{
    logMessage("=== Deploying [$name] ===");

    $ftp = @ftp_connect($tenantCfg['host']);
    if (!$ftp) {
        logMessage("❌ Failed to connect: {$tenantCfg['host']}");
        return;
    }

    if (!@ftp_login($ftp, $tenantCfg['user'], $tenantCfg['pass'])) {
        logMessage("❌ Login failed for {$tenantCfg['user']}");
        ftp_close($ftp);
        return;
    }

    ftp_pasv($ftp, true);
    logMessage("🔗 Connected to {$tenantCfg['host']}");

    $remoteRoot = rtrim($tenantCfg['remote_path'], '/');
    ftpEnsureDir($ftp, $remoteRoot);

    $token = $globalCfg['app_patch_token'];
    $domain = $tenantCfg['domain'];
    $includes = $globalCfg['include_path'] ?? [];
    $excludes = $globalCfg['exclude_path'] ?? [];

    // Pastikan deploy-list.php tersedia
    ensureDeployListExists($ftp, dirname(__DIR__) . '/public/deploy-list.php', $remoteRoot);

    // Ambil file list dari remote
    $remoteList = fetchRemoteFileList($domain, $token);

    // Sinkronisasi
    // use project root (one level up from deploy folder)
    $projectRoot = realpath(__DIR__ . '/..') ?: __DIR__;
    ftpSyncIncremental($ftp, $projectRoot, $remoteRoot, $includes, $excludes, $remoteList);


    ftp_close($ftp);
    logMessage("✅ Finished deployment for [$name]");
}

/**
 * Entry point
 */
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
        logMessage("❌ Invalid config.json");
        exit(1);
    }

    $target = $argv[1] ?? null;
    if ($target) {
        if (!isset($cfg['tenants'][$target])) {
            logMessage("❌ Tenant [$target] not found");
            exit(1);
        }
        $tenantCfg = $cfg['tenants'][$target];
        deployTenant($target, $tenantCfg, $cfg);
        patch($tenantCfg['domain'], $cfg['app_patch_token']);
    } else {
        foreach ($cfg['tenants'] as $name => $tenantCfg) {
            deployTenant($name, $tenantCfg, $cfg);
            patch($tenantCfg['domain'], $cfg['app_patch_token']);
        }
    }

    logMessage("🏁 All deployments completed.");
}

main($argv);
