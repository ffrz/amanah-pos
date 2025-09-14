<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * 
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * 
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * 
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InjectLicense extends Command
{
    protected $signature = 'license:inject';
    protected $description = 'Inject license header into PHP files in specific directories';

    // Direktori target
    protected array $targetDirs = [
        'app',
        'bootstrap',
        'config',
        'database',
        'routes',
        'tests',
        'modules',
    ];

    public function handle()
    {
        $this->info('Injecting license headers...');

        // Baca file LICENSE
        $licensePath = base_path('LICENSE_HEADER');
        if (!File::exists($licensePath)) {
            $this->error('LICENSE file not found!');
            return 1;
        }

        $licenseText = File::get($licensePath);
        $licenseBlock = $this->wrapAsComment($licenseText);

        foreach ($this->targetDirs as $dir) {
            $path = base_path($dir);
            if (!File::isDirectory($path)) {
                $this->warn("Directory not found: $dir");
                continue;
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $this->processFile($file->getRealPath(), $licenseBlock);
            }
        }

        $this->info('License injection complete.');
        return 0;
    }

    private function wrapAsComment(string $text): string
    {
        // Bungkus LICENSE jadi komentar PHP
        $lines = explode("\n", trim($text));
        $commented = "/**\n";
        foreach ($lines as $line) {
            $commented .= " * " . rtrim($line) . "\n";
        }
        $commented .= " */\n\n";
        return $commented;
    }

    private function processFile(string $filePath, string $licenseBlock): void
    {
        $contents = File::get($filePath);

        // Pastikan file diawali dengan <?php
        if (strpos($contents, '<?php') !== 0) {
            return;
        }

        // Hapus komentar lama di awal file (jika ada)
        $pattern = '/^<\?php\s+(\/\*.*?\*\/\s+)?/s';
        $newContents = preg_replace($pattern, "<?php\n\n" . $licenseBlock, $contents, 1);

        if ($newContents !== null) {
            File::put($filePath, $newContents);
            $this->line("Updated: $filePath");
        }
    }
}
