<?php

namespace Database\Seeders;

use App\Models\Path;
use Illuminate\Database\Seeder;

class PathSeeder extends Seeder
{
    public function run(): void
    {
        $paths = [
            [
                'name' => 'Pokoj 101',
                'description' => 'Doručení do pokoje 101 na konci hlavní chodby.',
                'going_moves' => 'forward:1',
                'button_press_moves' => 'sit:1',
                'return_moves' => 'backward:1',
            ],
            [
                'name' => 'Sesterna',
                'description' => 'Cesta na sesternu — odbočit doprava u recepce.',
                'going_moves' => 'forward:1',
                'button_press_moves' => 'sit:1,wait:3',
                'return_moves' => 'backward:1',
            ],
            [
                'name' => 'Čekárna',
                'description' => 'Krátká trasa do čekárny u vstupu.',
                'going_moves' => 'forward:1',
                'button_press_moves' => 'sit:1',
                'return_moves' => 'backward:1',
            ],
        ];

        foreach ($paths as $path) {
            Path::firstOrCreate(['name' => $path['name']], $path);
        }
    }
}
