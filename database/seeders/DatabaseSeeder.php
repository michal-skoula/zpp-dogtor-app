<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Dr. House',
            'email' => 'doctor@example.com',
            'password' => bcrypt('doctor'),
            'role' => UserRole::Doctor,
        ]);

        User::factory()->create([
            'name' => 'John Patient',
            'email' => 'patient@example.com',
            'password' => bcrypt('patient'),
            'role' => UserRole::Patient,
        ]);

        $this->call([
            DrugSeeder::class,
            PathSeeder::class,
            JourneySeeder::class,
        ]);
    }
}
