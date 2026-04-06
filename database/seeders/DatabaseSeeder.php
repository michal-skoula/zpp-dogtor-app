<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Drug;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $doctor = User::factory()->create([
            'name' => 'Dr. House',
            'email' => 'doctor@example.com',
            'role' => UserRole::Doctor,
        ]);

        $patient = User::factory()->create([
            'name' => 'John Patient',
            'email' => 'patient@example.com',
            'role' => UserRole::Patient,
        ]);

        Drug::create([
            'name' => 'Ibuprofen',
            'generic_name' => 'Ibuprofenum',
            'form' => 'tablet',
            'strength' => '400mg',
            'is_active' => true,
        ]);
    }
}
