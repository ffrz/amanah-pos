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
            foreach ($permissions as $permissionName => $permissionLabel) {
                Permission::firstOrCreate(
                    ['name' => $permissionName],
                    [
                        'label' => $permissionLabel,
                        'category' => $category,
                        'guard_name' => 'web'
                    ]
                );
            }
        }

        // Daftar semua izin yang akan diberikan ke peran 'kasir'
        $cashier_permissions = [
            'admin.cashier-session.index',
            'admin.cashier-session.detail',
            'admin.cashier-session.open',
            'admin.cashier-session.close',

            'admin.cashier-terminal.index',
            'admin.cashier-terminal.detail',

            'admin.customer.index',
            'admin.customer.detail',
            'admin.customer.add',
            'admin.customer.edit',

            'admin.product.index',
            'admin.product.detail',

            'admin.product-category.index',
            'admin.stock-movement.index',

            // 'admin.finance-account.detail',

            'admin.finance-transaction.index',
            'admin.finance-transaction.detail',
            'admin.finance-transaction.add',

            'admin.operational-cost.index',
            'admin.operational-cost.detail',
            'admin.operational-cost.add',

            'admin.operational-cost-category.index',
            'admin.operational-cost-category.add',

            'admin.sales-order.index',
            'admin.sales-order.detail',
            'admin.sales-order.edit',

            'admin.user.detail',
        ];

        // Pastikan semua izin di dalam array sudah ada di database.
        foreach ($cashier_permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Cari atau buat peran 'kasir'.
        $role = Role::firstOrCreate(['name' => 'Kasir']);

        // Mass assign izin-izin dari array ke peran 'kasir'.
        // syncPermissions() akan menambahkan izin baru dan menghapus yang tidak ada di array.
        $role->syncPermissions($cashier_permissions);

        // Cari atau buat user 'Kasir 1'.
        $kasirUser = User::firstOrCreate(
            ['username' => 'kasir1'],
            [
                'name' => 'Kasir 1',
                'password' => Hash::make('password'),
                'type' => User::Type_StandardUser,
                'active' => true
            ]
        );

        // Tetapkan peran 'kasir' ke user.
        $kasirUser->assignRole($role);

        $kasirUser = User::firstOrCreate(
            ['username' => 'kasir2'],
            [
                'name' => 'Kasir 2',
                'password' => Hash::make('password'),
                'type' => User::Type_StandardUser,
                'active' => true
            ]
        );

        // Tetapkan peran 'kasir' ke user.
        $kasirUser->assignRole($role);
    }
}
