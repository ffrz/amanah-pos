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

        // Ambil seluruh permission yang didefinisikan di AppPermissions
        $allPermissions = AppPermissions::all();

        // Ambil semua permission yang sudah ada di DB (keyBy name agar mudah diakses)
        $existingPermissions = Permission::all()->keyBy('name');

        $permissionsToCreate = [];
        $permissionsToUpdate = [];
        $permissionsInCode = [];

        foreach ($allPermissions as $category => $permissions) {
            foreach ($permissions as $permissionName => $permissionLabel) {
                $permissionsInCode[] = $permissionName;

                if (!isset($existingPermissions[$permissionName])) {
                    // Belum ada → buat baru
                    $permissionsToCreate[] = [
                        'name' => $permissionName,
                        'label' => $permissionLabel,
                        'category' => $category,
                        'guard_name' => 'web',
                    ];
                } else {
                    // Sudah ada → cek apakah label / kategori perlu diperbarui
                    $perm = $existingPermissions[$permissionName];
                    if (
                        $perm->label !== $permissionLabel ||
                        $perm->category !== $category
                    ) {
                        $permissionsToUpdate[] = [
                            'id' => $perm->id,
                            'label' => $permissionLabel,
                            'category' => $category,
                        ];
                    }
                }
            }
        }

        // Buat izin baru
        if (!empty($permissionsToCreate)) {
            Permission::insert($permissionsToCreate);
            $this->info(count($permissionsToCreate) . ' new permissions created.');
        } else {
            $this->info('No new permissions to create.');
        }

        // Update izin yang berubah label/category
        if (!empty($permissionsToUpdate)) {
            foreach ($permissionsToUpdate as $update) {
                Permission::where('id', $update['id'])->update([
                    'label' => $update['label'],
                    'category' => $update['category'],
                ]);
            }
            $this->info(count($permissionsToUpdate) . ' permissions updated.');
        } else {
            $this->info('No permissions to update.');
        }

        // Tidak ada penghapusan data (permission lama dibiarkan)
        $this->comment('Skipped deletion step — old permissions retained.');

        // Reset cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('Permissions synchronized successfully!');
    }
}
