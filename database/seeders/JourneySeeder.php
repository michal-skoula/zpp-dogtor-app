<?php

namespace Database\Seeders;

use App\Models\Drug;
use App\Models\Journey;
use App\Models\Path;
use Illuminate\Database\Seeder;

class JourneySeeder extends Seeder
{
    public function run(): void
    {
        $pokoj101 = Path::where('name', 'Pokoj 101')->first();
        $sesterna = Path::where('name', 'Sesterny')->first() ?? Path::where('name', 'Sesterna')->first();
        $cekarna = Path::where('name', 'Čekárna')->first();

        $ibalgin = Drug::where('name', 'Ibalgin 400')->first();
        $paralen = Drug::where('name', 'Paralen 500')->first();
        $nurofen = Drug::where('name', 'Nurofen')->first();
        $noSpa = Drug::where('name', 'No-Spa')->first();

        $journeys = [
            [
                'path' => $pokoj101,
                'dispatched_at' => now()->subDays(3)->setTime(9, 15),
                'status' => 'success',
                'drugs' => [
                    [$ibalgin, 2],
                    [$paralen, 1],
                ],
            ],
            [
                'path' => $sesterna,
                'dispatched_at' => now()->subDays(2)->setTime(14, 30),
                'status' => 'success',
                'drugs' => [
                    [$nurofen, 3],
                ],
            ],
            [
                'path' => $cekarna,
                'dispatched_at' => now()->subDay()->setTime(11, 0),
                'status' => 'error',
                'drugs' => [
                    [$paralen, 2],
                    [$noSpa, 1],
                ],
            ],
            [
                'path' => $pokoj101,
                'dispatched_at' => now()->subHours(2),
                'status' => 'success',
                'drugs' => [
                    [$ibalgin, 1],
                ],
            ],
        ];

        foreach ($journeys as $data) {
            if (! $data['path']) {
                continue;
            }

            $journey = Journey::create([
                'path_id' => $data['path']->id,
                'dispatched_at' => $data['dispatched_at'],
                'status' => $data['status'],
            ]);

            foreach ($data['drugs'] as [$drug, $quantity]) {
                if ($drug) {
                    $journey->drugs()->attach($drug->id, ['quantity' => $quantity]);
                }
            }
        }
    }
}
