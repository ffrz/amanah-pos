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
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use App\Constants\AppPermissions;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes permissions from code to the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting permissions synchronization...');

        // Dapatkan semua izin yang terdefinisi dalam kelas AppPermissions.
        // Asumsi metode `all()` mengembalikan array multi-dimensi dengan kategori.
        $allPermissions = AppPermissions::all();

        // Koleksi untuk melacak izin yang ada dan baru
        $existingPermissions = Permission::all()->pluck('name')->toArray();
        $permissionsToCreate = [];
        $permissionsInCode = []; // List of all permission names in the code

        foreach ($allPermissions as $category => $permissions) {
            foreach ($permissions as $permissionName => $permissionLabel) {
                // Tambahkan nama izin ke array untuk pelacakan izin di kode
                $permissionsInCode[] = $permissionName;

                // Tambahkan izin ke array untuk dibuat jika belum ada di database
                if (!in_array($permissionName, $existingPermissions)) {
                    $permissionsToCreate[] = [
                        'name' => $permissionName,
                        'label' => $permissionLabel,
                        'category' => $category,
                        'guard_name' => 'web', // Sesuaikan dengan guard Anda
                    ];
                }
            }
        }

        // Buat izin yang belum ada
        if (!empty($permissionsToCreate)) {
            Permission::insert($permissionsToCreate);
            $this->info(count($permissionsToCreate) . ' new permissions created.');
        } else {
            $this->info('No new permissions to create.');
        }

        // Hapus izin di database yang tidak lagi ada di kode
        $permissionsToDelete = array_diff($existingPermissions, $permissionsInCode);

        if (!empty($permissionsToDelete)) {
            Permission::whereIn('name', $permissionsToDelete)->delete();
            $this->warn(count($permissionsToDelete) . ' permissions have been deleted from the database.');
        } else {
            $this->info('No permissions to delete.');
        }

        // Reset cache Spatie setelah sinkronisasi
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('Permissions synchronized successfully!');
    }
}
