<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserFactory::$defaultPassword = Hash::make('12345');
        User::factory()->create([
            'username' => 'admin',
            'name' => 'Administrator',
            'role' => User::Role_Admin,
            'active' => 1,
        ]);
        User::factory()->create([
            'username' => 'kasir1',
            'name' => 'Fahmi',
            'role' => User::Role_Cashier,
            'active' => 1,
        ]);
        User::factory(5)->create();
    }
}
