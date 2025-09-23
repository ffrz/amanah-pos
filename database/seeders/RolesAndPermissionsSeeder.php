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

namespace Database\Seeders;

use App\Constants\AppPermissions;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsByCategories = AppPermissions::all();

        foreach ($permissionsByCategories as $category => $permissions) {
            foreach ($permissions as $permissionData) {
                Permission::firstOrCreate(
                    ['name' => $permissionData['name']],
                    [
                        'label' => $permissionData['label'],
                        'category' => $category,
                        'guard_name' => 'web'
                    ]
                );
            }
        }

        // Daftar semua izin yang akan diberikan ke peran 'kasir'
        $cashier_permissions = [
            'admin.cashier-session.start',
            'admin.cashier-session.detail',
            'admin.product.index',
        ];

        // Pastikan semua izin di dalam array sudah ada di database.
        foreach ($cashier_permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Cari atau buat peran 'kasir'.
        $role = Role::firstOrCreate(['name' => 'kasir']);

        // Mass assign izin-izin dari array ke peran 'kasir'.
        // syncPermissions() akan menambahkan izin baru dan menghapus yang tidak ada di array.
        $role->syncPermissions($cashier_permissions);

        // Cari atau buat user 'Kasir 1'.
        $kasirUser = User::firstOrCreate(
            ['username' => 'kasir1'],
            [
                'name' => 'Kasir 1',
                'password' => Hash::make('password'),
                'role' => User::Role_Cashier, // masih butuh role karena belum integrasi penuh
                'active' => true
            ]
        );

        // Tetapkan peran 'kasir' ke user.
        $kasirUser->assignRole($role);
    }
}
