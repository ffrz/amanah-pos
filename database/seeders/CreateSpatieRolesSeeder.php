<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class CreateSpatieRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat peran-peran dari konstanta User::Roles jika belum ada
        foreach (User::Roles as $roleName => $roleDisplayName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            // 'guard_name' biasanya 'web' untuk aplikasi berbasis sesi
            // Sesuaikan jika Anda punya guard lain
        }

        $this->command->info('Default Spatie roles created from User::Roles constants.');
    }
}
