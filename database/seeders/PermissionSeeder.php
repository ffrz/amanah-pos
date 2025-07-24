<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User; // Import model User Anda

/**
 * Class PermissionSeeder
 *
 * Seeder ini bertanggung jawab untuk:
 * 1. Membuat peran dasar (roles) di sistem Spatie.
 * 2. Membuat izin (permissions) yang sesuai dengan named routes aplikasi.
 * 3. Mengaitkan izin-izin tersebut ke peran yang relevan.
 * 4. Menetapkan peran Spatie kepada pengguna yang sudah ada berdasarkan kolom 'role' lama mereka.
 */
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Bagian 1: Buat Peran Dasar (Roles) ---
        $this->command->info('Creating default Spatie roles...');
        foreach (User::Roles as $roleName => $roleDisplayName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
        $this->command->info('Default Spatie roles created.');

        // Reset cache izin sebelum membuat izin baru
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definisikan izin-izin yang sesuai dengan NAMED ROUTES Anda
        $permissions = [
            // Produk
            'admin.product.index',
            'admin.product.data',
            'admin.product.add',
            'admin.product.duplicate',
            'admin.product.edit',
            'admin.product.save',
            'admin.product.delete',
            'admin.product.detail',

            'admin.product-category.index',
            'admin.product-category.data',
            'admin.product-category.add',
            'admin.product-category.duplicate',
            'admin.product-category.edit',
            'admin.product-category.save',
            'admin.product-category.delete',

            //

            // Tambahkan izin lain sesuai named routes aplikasi Anda
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('Permissions (matching named routes) created successfully.');

        // --- Tetapkan Role Spatie kepada Pengguna yang Sudah Ada ---
        $this->command->info('Synchronizing existing user roles with Spatie roles...');
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                // Pastikan user memiliki nilai di kolom 'role' lama
                // dan belum memiliki peran Spatie yang sesuai untuk menghindari duplikasi
                if ($user->role && !$user->hasRole($user->role)) {
                    $user->assignRole($user->role);
                    $this->command->info("Assigned role '{$user->role}' to user ID: {$user->id}");
                }
            }
        });
        $this->command->info('Existing user roles synchronized.');

        // --- Berikan Izin ke Peran Default ---

        // Peran Admin: Bypass semua (ditangani di middleware), tapi secara eksplisit bisa diberikan semua izin
        $adminRole = Role::firstOrCreate(['name' => User::Role_Admin, 'guard_name' => 'web']);
        $adminRole->givePermissionTo($permissions);

        // Role Kasir
        $cashierRole = Role::firstOrCreate(['name' => User::Role_Cashier, 'guard_name' => 'web']);
        $cashierRole->givePermissionTo([
            'admin.product.index',
            'admin.product.data',
            'admin.product.detail',

            'admin.product-category.index',
            'admin.product-category.data',
        ]);

        // Role Owner
        $ownerRole = Role::firstOrCreate(['name' => User::Role_Owner, 'guard_name' => 'web']);
        $ownerRole->givePermissionTo([
            'admin.product.index',
            'admin.product.data',
            'admin.product.add',
            'admin.product.duplicate',
            'admin.product.edit',
            'admin.product.save',
            'admin.product.delete',
            'admin.product.detail',

            'admin.product-category.index',
            'admin.product-category.data',
            'admin.product-category.add',
            'admin.product-category.duplicate',
            'admin.product-category.edit',
            'admin.product-category.save',
            'admin.product-category.delete',
        ]);

        $this->command->info('Permissions assigned to roles successfully.');
    }
}
