<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;

class DatabaseBackup extends Command
{
    /**
     * Nama dan tanda tangan perintah konsol.
     */
    protected $signature = 'db:backup';

    /**
     * Deskripsi perintah konsol.
     */
    protected $description = 'Melakukan backup database untuk semua skema tenant.';

    /**
     * Jalankan perintah konsol.
     */
    public function handle()
    {
        $this->info('Memulai proses backup database...');

        // Asumsi: Kita hanya fokus pada satu skema utama (seperti skema tenant yang sedang aktif).
        // Dalam implementasi multi-tenant yang sebenarnya, Anda perlu mengulanginya
        // untuk setiap skema. Karena ini adalah contoh, kita backup skema yang terhubung.

        $databaseName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $filename = 'backup-' . $databaseName . '-' . now()->format('Y-m-d_His') . '.sql';
        $path = storage_path('app/backups/');

        // Pastikan direktori backup ada
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // --- SOLUSI KHUSUS SHARED HOSTING ---
        // Mencoba menjalankan mysqldump melalui PHP shell_exec (jika diizinkan)
        try {
            // Perintah mysqldump.
            // PENTING: Pastikan path ke mysqldump tersedia di shared hosting Anda,
            // atau cukup panggil 'mysqldump' jika sudah ada di PATH.
            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($databaseName),
                escapeshellarg($path . $filename)
            );

            $this->comment("Menjalankan: $command");

            // Eksekusi perintah
            $output = shell_exec($command);

            if (file_exists($path . $filename) && filesize($path . $filename) > 0) {
                $this->info("Backup berhasil dibuat: " . $filename);

                // --- Langkah Opsional: Unggah ke Cloud ---
                // Di sini Anda bisa menambahkan kode untuk mengunggah $path.$filename ke S3, 
                // Google Drive, atau Dropbox menggunakan Laravel Filesystem.

            } else {
                $this->error("Backup gagal atau file kosong. Mungkin `mysqldump` diblokir.");
                $this->error("Output: " . $output);

                // Jika gagal, berikan saran backup manual
                $this->manualBackupInstruction($databaseName, $path . $filename);
            }
        } catch (Exception $e) {
            $this->error('Terjadi kesalahan saat menjalankan backup: ' . $e->getMessage());
            $this->manualBackupInstruction($databaseName, $path . $filename);
        }

        return Command::SUCCESS;
    }

    /**
     * Memberikan instruksi jika mysqldump gagal
     */
    protected function manualBackupInstruction($databaseName, $filePath)
    {
        $this->warn("\n--- SOLUSI ALTERNATIF ---");
        $this->warn("Jika 'mysqldump' diblokir, Anda harus:");
        $this->warn("1. Masuk ke cPanel/Plesk Anda.");
        $this->warn("2. Gunakan alat 'Backup' atau 'phpMyAdmin'.");
        $this->warn("3. Ekspor skema '$databaseName' secara manual.");
        $this->warn("4. Simpan file SQL-nya sebagai '$filePath'");
    }
}
