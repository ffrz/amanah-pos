<?php

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

        // Dapatkan semua izin yang terdefinisi dalam kode
        $allPermissions = AppPermissions::all();

        // Koleksi untuk melacak izin yang ada dan baru
        $existingPermissions = Permission::all()->pluck('name')->toArray();
        $permissionsToCreate = [];

        foreach ($allPermissions as $category => $permissions) {
            foreach ($permissions as $permissionData) {
                // Tambahkan izin ke array untuk dibuat jika belum ada di database
                if (!in_array($permissionData['name'], $existingPermissions)) {
                    $permissionsToCreate[] = [
                        'name' => $permissionData['name'],
                        'label' => $permissionData['label'],
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
        $permissionsInCode = collect($allPermissions)->flatten(1)->pluck('name')->all();
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
