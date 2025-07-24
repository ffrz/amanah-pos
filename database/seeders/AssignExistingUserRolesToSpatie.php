<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Import model User Anda
use Spatie\Permission\Models\Role; // Import model Role Spatie

class AssignExistingUserRolesToSpatie extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan peran-peran dasar Spatie sudah ada
        // (opsional, karena sudah dilakukan di CreateSpatieRolesSeeder)
        Role::firstOrCreate(['name' => User::Role_Admin, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => User::Role_Cashier, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => User::Role_Owner, 'guard_name' => 'web']);

        // Iterasi semua pengguna dan tetapkan peran Spatie berdasarkan kolom 'role' lama
        User::chunk(100, function ($users) { // Gunakan chunk untuk database besar
            foreach ($users as $user) {
                // Pastikan user memiliki nilai di kolom 'role' dan belum memiliki peran Spatie yang sesuai
                if ($user->role && !$user->hasRole($user->role)) {
                    $user->assignRole($user->role);
                    $this->command->info("Assigned role '{$user->role}' to user ID: {$user->id}");
                }
            }
        });

        $this->command->info('Existing user roles synchronized with Spatie roles.');
    }
}
