<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin PetitChef',
            'email' => 'admin@petitchef.test',
            'phone' => '900000001',
            'role' => 'admin',
            'approval_status' => 'approved',
        ]);

        User::factory()->create([
            'name' => 'Client PetitChef',
            'email' => 'client@petitchef.test',
            'phone' => '900000002',
            'role' => 'client',
            'approval_status' => 'approved',
        ]);

        User::factory()->create([
            'name' => 'Cuisinier PetitChef',
            'email' => 'cook@petitchef.test',
            'phone' => '900000003',
            'role' => 'cook',
            'approval_status' => 'pending',
        ]);
    }
}
