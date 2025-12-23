<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GenerateInitialDb extends Command
{
    // Nama command yang akan dipanggil
    protected $signature = 'db:generate-initial';

    // Deskripsi command
    protected $description = 'Migrate, Seed, and Export Initial Database to SQL file';

    public function handle()
    {
        $fileName = 'database/shiftech_pos_initial_db.sql';

        // $this->info("1. Running migrate:fresh...");
        // Artisan::call('migrate:fresh', ['--force' => true]);

        // $this->info("2. Seeding InitialDatabaseSeeder...");
        // Artisan::call('db:seed', [
        //     '--class' => 'InitialDatabaseSeeder',
        //     '--force' => true
        // ]);

        $this->info("3. Exporting database to {$fileName}...");

        // Ambil konfigurasi database dari .env secara otomatis
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host');

        // Tentukan path penyimpanan (biasanya di root project atau folder database)
        $path = base_path($fileName);

        // Command mysqldump
        // -p langsung diikuti password tanpa spasi. Jika password kosong, sesuaikan logicnya.
        $command = sprintf(
            'mysqldump -h %s -u %s %s %s > %s',
            $host,
            $username,
            $password ? "-p'{$password}'" : '',
            $database,
            escapeshellarg($path)
        );

        // Eksekusi shell command
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error("Gagal mengekspor database. Pastikan 'mysqldump' terinstal di sistem Anda.");
            return Command::FAILURE;
        }

        $this->info("Success! File saved at: " . $path);
        return Command::SUCCESS;
    }
}
